<?php

namespace App\Modules\Transactions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

// Model will serve as the eloquent class to interact with the database

class DailyBalancesModel extends Model
{
    // Define your model properties and methods here
    protected $table = 'daily_balances';

    protected $fillable = [
        'dba_user_id',
        'dba_import_id',
        'dba_date',
        'dba_closing_balance',
    ];

    public $timestamps = false;

    /**
     * Saves a list of daily balances for a given user and import.
     * @param int $userId
     * @param int $importId
     * @param array $dailyBalances Array of daily balances, each containing 'date_yyyymmdd' and 'closing_balance'
     * @return void
     */
    public function saveDailyBalances(int $userId, int $importId, array $dailyBalances)
    {
        // Implement logic to save the daily balances to the database
        // For example, you can iterate through the list of daily balances and create a new record for each one in the daily_balances table
        foreach ($dailyBalances as $balance) {
            if (!isset($balance['date_yyyymmdd']) || !isset($balance['closing_balance'])) {
                throw new \InvalidArgumentException('Each daily balance must contain date_yyyymmdd and closing_balance.');
            }
            DB::table('daily_balances')->insert([
                'dba_user_id' => $userId,
                'dba_import_id' => $importId,
                'dba_date' => $balance['date_yyyymmdd'],
                'dba_closing_balance' => $balance['closing_balance'],
            ]);
        }
    }

    /**
     * Lists the daily balances for a given user.
     * @param int $userId
     * @return array Array of daily balances
     */
    public function listDailyBalances($userId)
    {
        return DB::table('daily_balances')
            ->where('dba_user_id', $userId)
            ->select('dba_date', 'dba_closing_balance')
            ->orderBy('dba_date', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Lists balance by month for a given user.
     * @param int $userId
     * @return array Array of monthly balances
     */
    public function listBalanceByMonth($userId)
    {
        $lastDatesofEachMonth = DB::table('daily_balances')
            ->selectRaw('
            DATE_FORMAT(dba_date, "%Y-%m") as month,
            MAX(dba_date) as last_date
        ')
            ->where('dba_user_id', $userId)
            ->groupByRaw('DATE_FORMAT(dba_date, "%Y-%m")');

        return DB::table('daily_balances as db')
            ->joinSub($lastDatesofEachMonth, 'last_balances', function ($join) {
                $join->on('db.dba_date', '=', 'last_balances.last_date');
            })
            ->where('db.dba_user_id', $userId)
            ->selectRaw('
            last_balances.month,
            db.dba_closing_balance as closing_balance
        ')
            ->orderBy('last_balances.month', 'asc')
            ->get()
            ->toArray();
    }

    public function createBalance(int $userId, array $data): array
    {

        $importId = DB::table('imports')
            ->where('imp_user_id', $userId)
            ->orderByDesc('imp_id')
            ->value('imp_id');

        if (! $importId) {
            throw new \InvalidArgumentException('No import found for authenticated user.');
        }
        // Implement logic to create a new balance record in the database
        // For example, you can use Eloquent's create method to insert a new record
        $balance = $this->create([
            'dba_user_id' => $userId,
            'dba_import_id' => $importId,
            'dba_date' => $data['dba_date'],
            'dba_closing_balance' => $data['dba_closing_balance'],
        ]);

        return $balance->toArray();
    }

    public function updateBalance(int $balanceId, array $data, int $userId): array
    {
        $updated = DB::table('daily_balances')->where('dba_id', $balanceId)->where('dba_user_id', $userId)->update($data);

        if ($updated === 0) {
            throw new \InvalidArgumentException("Balance with ID {$balanceId} not found or no changes made.");
        }

        $balance = DB::table('daily_balances')
            ->where('dba_id', $balanceId)
            ->where('dba_user_id', $userId)
            ->first();

        if (! $balance) {
            throw new \RuntimeException('Failed to retrieve the updated balance.');
        }

        return (array) $balance;
    }

    public function listBalances(int $userId, array $filters = []): array
    {
        // Implement logic to retrieve a list of balances based on the provided filters
        // For example, you can use Eloquent's where method to apply filters and retrieve the records
        $query = $this->where('dba_user_id', $userId);

        if (isset($filters['date_start'])) {
            $query->where('dba_date', '>=', $filters['date_start']);
        }

        if (isset($filters['date_end'])) {
            $query->where('dba_date', '<=', $filters['date_end']);
        }

        $result = [
            'total' => $query->count(),
        ];

        if (isset($filters['limitStart']) && isset($filters['limitEnd'])) {
            $query->offset($filters['limitStart'])->limit($filters['limitEnd']);
        }

        $result['rows'] = $query->orderBy('dba_date', 'asc')->get()->toArray();

        return $result;
    }

    public function deleteBalance(int $balanceId, int $userId): void
    {
        $deleted = DB::table('daily_balances')->where('dba_id', $balanceId)->where('dba_user_id', $userId)->delete();

        if ($deleted === 0) {
            throw new \InvalidArgumentException("Balance with ID {$balanceId} not found.");
        }
    }
}
