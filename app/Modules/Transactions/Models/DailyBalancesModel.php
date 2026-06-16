<?php

namespace App\Modules\Transactions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

// Model will serve as the eloquent class to interact with the database

class DailyBalancesModel extends Model
{
    // Define your model properties and methods here
    protected $table = 'daily_balances';

    /**
     * Saves a list of daily balances for a given user and import.
     * @param int $userId
     * @param int $importId
     * @param array $dailyBalances Array of daily balances, each containing 'date_yyyymmdd' and 'closing_balance'
     * @return void
     */
    public function saveDailyBalances($userId, $importId, $dailyBalances)
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
}
