<?php

namespace App\Modules\Transactions\Models;

use App\Modules\Categories\Models\RulesModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Model will serve as the eloquent class to interact with the database

class TransactionsModel extends Model
{
    // Define your model properties and methods here
    protected $table = 'transactions';

    protected $primaryKey = 'tra_id';

    // Allows mass assignment for the specified fields
    protected $fillable = [
        'tra_matched_rule_id',
        'tra_category_id',
    ];

    public $timestamps = false;

    public function createTransaction(int $userId, array $data): array
    {
        $rules = RulesModel::where('rul_user_id', $userId)
            ->orderByDesc('rul_priority')
            ->get();

        [$matchedRuleId, $matchedCategoryId] = $this->matchTransactionByRules($rules, $data['tra_description'], $userId);

        $importId = DB::table('imports')
            ->where('imp_user_id', $userId)
            ->orderByDesc('imp_id')
            ->value('imp_id');

        if (! $importId) {
            throw new \InvalidArgumentException('No import found for authenticated user.');
        }

        $transactionId = DB::table('transactions')->insertGetId([
            'tra_user_id' => $userId,
            'tra_import_id' => $importId,
            'tra_date' => $data['tra_date'],
            'tra_amount' => $data['tra_amount'],
            'tra_description' => $data['tra_description'],
            'tra_matched_rule_id' => $matchedRuleId,
            'tra_category_id' => $matchedCategoryId,
        ]);

        $transaction = DB::table('transactions')
            ->select(
                'tra_id',
                'tra_user_id',
                'tra_import_id',
                'tra_date',
                'tra_amount',
                'tra_description',
                'tra_matched_rule_id',
                'tra_category_id',
                'cat_description'
            )
            ->leftJoin('categories', 'tra_category_id', '=', 'cat_id')
            ->where('tra_id', $transactionId)
            ->first();

        if (! $transaction) {
            throw new \RuntimeException('Failed to retrieve the created transaction.');
        }

        return (array) $transaction;
    }

    /**
     * Saves a list of transactions for a given user and import.
     *
     * @param  array  $transactions  Array of transactions, each containing 'date_yyyymmdd', 'amount', and 'description'
     */
    public function saveTransactions(int $userId, int $importId, array $transactions): void
    {
        $rules = RulesModel::where('rul_user_id', $userId)
            ->orderByDesc('rul_priority')
            ->get();

        // Implement logic to save the transactions to the database
        // For example, you can iterate through the list of transactions and create a new record for each one in the transactions table
        foreach ($transactions as $transaction) {
            if (! isset($transaction['date_yyyymmdd']) || ! isset($transaction['amount']) || ! isset($transaction['description'])) {
                throw new \InvalidArgumentException('Each transaction must contain date_yyyymmdd, amount, and description.');
            }

            [$matchedRuleId, $matchedCategoryId] = $this->matchTransactionByRules($rules, $transaction['description'], $userId);

            DB::table('transactions')->insert([
                'tra_user_id' => $userId,
                'tra_import_id' => $importId,
                'tra_date' => $transaction['date_yyyymmdd'],
                'tra_amount' => $transaction['amount'],
                'tra_description' => $transaction['description'],
                'tra_matched_rule_id' => $matchedRuleId,
                'tra_category_id' => $matchedCategoryId,
            ]);
        }
    }

    private function matchTransactionByRules(Collection $rules, string $description, int $userId): array
    {
        $matchedRuleId = null;
        $matchedCategoryId = null;

        foreach ($rules as $rule) {
            if (preg_match("/$rule->rul_pattern/", $description)) {
                Log::info("Transaction description '{$description}' matched rule pattern '{$rule->rul_pattern}' for user ID {$userId}.");
                $matchedRuleId = $rule->rul_id;
                $matchedCategoryId = $rule->rul_category_id;
                break;
            }
        }

        return [$matchedRuleId, $matchedCategoryId];
    }

    /**
     * Retrieves the total balance of the daily transactions for a given user.
     */
    public function getDailyTransactions(int $userId, ?string $dateStart = null, ?string $dateEnd = null): array
    {
        $query = DB::table('transactions')
            ->select('tra_date', DB::raw('SUM(tra_amount) as total_amount'))
            ->where('tra_user_id', $userId);

        if ($dateStart) {
            $query->where('tra_date', '>=', $dateStart);
        }

        if ($dateEnd) {
            $query->where('tra_date', '<=', $dateEnd);
        }

        return $query->groupBy('tra_date')
            ->orderBy('tra_date', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Retrieves the total balance of the monthly transactions for a given user.
     */
    public function getMonthlyTransactions(int $userId, ?string $dateStart = null, ?string $dateEnd = null): array
    {
        $query = DB::table('transactions')
            ->select(DB::raw('DATE_FORMAT(tra_date, "%Y-%m") as month'), DB::raw('SUM(tra_amount) as total_amount'))
            ->where('tra_user_id', $userId);

        if ($dateStart) {
            $query->where('tra_date', '>=', $dateStart);
        }

        if ($dateEnd) {
            $query->where('tra_date', '<=', $dateEnd);
        }

        return $query->groupBy(DB::raw('DATE_FORMAT(tra_date, "%Y-%m")'))
            ->orderBy(DB::raw('DATE_FORMAT(tra_date, "%Y-%m")'), 'asc')
            ->get()
            ->toArray();
    }

    public function getCategoryTransactions(int $userId, ?string $dateStart = null, ?string $dateEnd = null, array $categoryIds = []): array
    {
        return $this->categoryTransactionsBaseQuery($userId, $dateStart, $dateEnd, $categoryIds)
            ->select(
                'tra_category_id as category_id',
                'cat_description as category_description',
                DB::raw('SUM(tra_amount) as total_amount')
            )
            ->groupBy('tra_category_id', 'cat_description')
            ->orderBy('cat_description')
            ->get()
            ->toArray();
    }

    public function getDailyCategoryTransactions(int $userId, ?string $dateStart = null, ?string $dateEnd = null, array $categoryIds = []): array
    {
        return $this->categoryTransactionsBaseQuery($userId, $dateStart, $dateEnd, $categoryIds)
            ->select(
                'tra_category_id as category_id',
                'cat_description as category_description',
                DB::raw('DATE(tra_date) as date'),
                DB::raw('SUM(tra_amount) as total_amount')
            )
            ->groupBy('tra_category_id', 'cat_description', DB::raw('DATE(tra_date)'))
            ->orderBy('cat_description')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    public function getMonthlyCategoryTransactions(int $userId, ?string $dateStart = null, ?string $dateEnd = null, array $categoryIds = []): array
    {
        return $this->categoryTransactionsBaseQuery($userId, $dateStart, $dateEnd, $categoryIds)
            ->select(
                'tra_category_id as category_id',
                'cat_description as category_description',
                DB::raw('DATE_FORMAT(tra_date, "%Y-%m") as month'),
                DB::raw('SUM(tra_amount) as total_amount')
            )
            ->groupBy('tra_category_id', 'cat_description', DB::raw('DATE_FORMAT(tra_date, "%Y-%m")'))
            ->orderBy('cat_description')
            ->orderBy('month')
            ->get()
            ->toArray();
    }

    /**
     * Retrieves the total balance of the daily transactions along with the closing balance for a given user.
     */
    public function getTransactionsWithBalance(int $userId, ?string $dateStart = null, ?string $dateEnd = null): array
    {
        $query = DB::table('transactions')
            ->select('tra_date', DB::raw('SUM(tra_amount) as total_amount'), 'daily_balances.dba_closing_balance')
            ->join('daily_balances', 'transactions.tra_date', '=', 'daily_balances.dba_date')
            ->where('tra_user_id', $userId);

        if ($dateStart) {
            $query->where('tra_date', '>=', $dateStart);
        }

        if ($dateEnd) {
            $query->where('tra_date', '<=', $dateEnd);
        }

        return $query->groupBy('tra_date', 'daily_balances.dba_closing_balance')
            ->orderBy('tra_date', 'asc')
            ->get()
            ->toArray();
    }

    public function listTransactions(array $filters, int $userId): array
    {
        if (!$userId) {
            throw new \InvalidArgumentException('User ID is required to list transactions.');
        }
        $query = DB::table('transactions')
            ->select(
                'tra_id',
                'tra_user_id',
                'tra_import_id',
                'tra_date',
                'tra_amount',
                'tra_description',
                'tra_matched_rule_id',
                'tra_category_id',
                'cat_description'
            );

        $query->leftJoin('categories', 'tra_category_id', '=', 'cat_id');

        $query->where('tra_user_id', $userId);

        if (isset($filters['import_id'])) {
            $query->where('tra_import_id', $filters['import_id']);
        }

        if (isset($filters['date_start'])) {
            $query->where('tra_date', '>=', $filters['date_start']);
        }

        if (isset($filters['date_end'])) {
            $query->where('tra_date', '<=', $filters['date_end']);
        }

        if (isset($filters['category_id'])) {
            $query->where('tra_category_id', $filters['category_id']);
        }

        $result = [];

        $result['total'] = $query->count();

        if (isset($filters['limitStart']) && isset($filters['limitEnd'])) {
            $query->offset($filters['limitStart'])->limit($filters['limitEnd']);
        }

        $result['rows'] = $query->orderBy('tra_date', 'asc')
            ->get()
            ->toArray();

        return $result;
    }

    private function categoryTransactionsBaseQuery(int $userId, ?string $dateStart = null, ?string $dateEnd = null, array $categoryIds = []): Builder
    {
        $query = DB::table('transactions')
            ->join('categories', 'tra_category_id', '=', 'cat_id')
            ->where('tra_user_id', $userId)
            ->where('cat_user_id', $userId);

        if ($dateStart) {
            $query->where('tra_date', '>=', $dateStart);
        }

        if ($dateEnd) {
            $query->where('tra_date', '<=', $dateEnd);
        }

        if ($categoryIds !== []) {
            $query->whereIn('tra_category_id', $categoryIds);
        }

        return $query;
    }

    public function deleteTransaction(int $transactionId, int $userId): void
    {
        $deleted = DB::table('transactions')->where('tra_id', $transactionId)->where('tra_user_id', $userId)->delete();

        if ($deleted === 0) {
            throw new \InvalidArgumentException("Transaction with ID {$transactionId} not found.");
        }
    }

    public function updateTransaction(int $transactionId, array $data, int $userId): array
    {
        $updated = DB::table('transactions')->where('tra_id', $transactionId)->where('tra_user_id', $userId)->update($data);

        if ($updated === 0) {
            throw new \InvalidArgumentException("Transaction with ID {$transactionId} not found or no changes made.");
        }

        $transaction = DB::table('transactions')
            ->select(
                'tra_id',
                'tra_user_id',
                'tra_import_id',
                'tra_date',
                'tra_amount',
                'tra_description',
                'tra_matched_rule_id',
                'tra_category_id',
                'cat_description'
            )
            ->leftJoin('categories', 'tra_category_id', '=', 'cat_id')
            ->where('tra_id', $transactionId)
            ->first();

        if (! $transaction) {
            throw new \RuntimeException('Failed to retrieve the updated transaction.');
        }

        return (array) $transaction;
    }
}
