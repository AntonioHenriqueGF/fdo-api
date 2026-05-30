<?php

namespace App\Modules\Imports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

// Model will serve as the eloquent class to interact with the database

class TransactionsModel extends Model
{
    // Define your model properties and methods here
    protected $table = 'transactions';

    /**
     * Saves a list of transactions for a given user and import.
     * @param int $userId
     * @param int $importId
     * @param array $transactions Array of transactions, each containing 'date_yyyymmdd', 'amount', and 'description'
     * @return void
     */
    public function saveTransactions($userId, $importId, $transactions)
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
}
