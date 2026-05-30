<?php

namespace App\Modules\Imports\Models;

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
}
