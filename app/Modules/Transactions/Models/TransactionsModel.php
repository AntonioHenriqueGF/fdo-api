<?php

namespace App\Modules\Transactions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    /**
     * Saves a list of transactions for a given user and import.
     * @param int $userId
     * @param int $importId
     * @param array $transactions Array of transactions, each containing 'date_yyyymmdd', 'amount', and 'description'
     */
    public function saveTransactions(int $userId, int $importId, array $transactions): void
    {
        // Implement logic to save the transactions to the database
        // For example, you can iterate through the list of transactions and create a new record for each one in the transactions table
        foreach ($transactions as $transaction) {
            if (!isset($transaction['date_yyyymmdd']) || !isset($transaction['amount']) || !isset($transaction['description'])) {
                throw new \InvalidArgumentException('Each transaction must contain date_yyyymmdd, amount, and description.');
            }
            DB::table('transactions')->insert([
                'tra_user_id' => $userId,
                'tra_import_id' => $importId,
                'tra_date' => $transaction['date_yyyymmdd'],
                'tra_amount' => $transaction['amount'],
                'tra_description' => $transaction['description'],
            ]);
        }
    }

    /**
     * Retrieves the total balance of the daily transactions for a given user.
     * @param int $userId
     * @param string|null $dateStart
     * @param string|null $dateEnd
     * @return array
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
     * @param int $userId
     * @param string|null $dateStart
     * @param string|null $dateEnd
     * @return array
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

    /**
     * Retrieves the total balance of the daily transactions along with the closing balance for a given user.
     * @param int $userId
     * @param string|null $dateStart
     * @param string|null $dateEnd
     * @return array
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
}
