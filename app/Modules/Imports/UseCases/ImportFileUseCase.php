<?php

namespace App\Modules\Imports\UseCases;

use App\Modules\Imports\Models\ImportsModel;
use App\Modules\Transactions\Models\DailyBalancesModel;
use App\Modules\Transactions\Models\TransactionsModel;

class ImportFileUseCase
{
    public function __construct(
        private ImportsModel $importsModel,
        private DailyBalancesModel $dailyBalancesModel,
        private TransactionsModel $transactionsModel
    ) {
        // You can inject any dependencies here, such as repositories or services
    }

    /**
     * Execute the use case to process the imported file data.
     * @param array $data The validated data from the import file request.
     * @param string $data['fileName'] The name of the imported file.
     * @param string $data['fileHash'] The hash of the imported file.
     * @param array $data['normalized'] An array of normalized data, where each item contains 'amount', 'credit_only', 'debit_only', 'closing_balance', 'date_yyyymmdd', 'description', and 'date_ddmmyyyy'.
     * @return string A message indicating the result of the import process.
     */
    public function execute($data)
    {
        $userId = '1'; // Replace with actual user ID from authentication context

        $binHex = hex2bin($data['fileHash']);
        if ($binHex === false) {
            throw new \InvalidArgumentException('Invalid file hash provided.');
        }

        if (!$this->importsModel->verifyUniqueImport($userId, $binHex)) {
            throw new \InvalidArgumentException('This file has already been imported by this user.');
        }

        $importId = $this->importsModel->saveImport($userId, $data['fileName'], $binHex);

        // Implement the logic to process the imported data
        // For example, you can save it to the database or perform other operations
        $separatedData = $this->separateTransactionsAndBalances($data['normalized']);

        // Save the daily balances
        $this->dailyBalancesModel->saveDailyBalances($userId, $importId, $separatedData['dailyBalances']);

        // Save the transactions
        $this->transactionsModel->saveTransactions($userId, $importId, $separatedData['singleValueTransactions']);

        return 'File imported successfully!';
    }

    /**
     * This function takes an array of normalized data and separates it into two arrays: one for single value transactions (using positive and negative) and one for daily balances.
     * @param array $normalizedData An array of normalized data, where each item contains 'amount', 'credit_only', 'debit_only', 'closing_balance', 'date_yyyymmdd', 'description', and 'date_ddmmyyyy'.
     * @return array An array containing two arrays: 'singleValueTransactions' and 'dailyBalances'.
     * @author Antonio Henrique
     */
    public function separateTransactionsAndBalances(array $normalizedData): array
    {
        $singleValueTransactions = [];
        $dailyBalances = [];

        // Loop through each item in the normalized data, keeping amount as is and turning credit_only and debit_only into numeric values
        foreach ($normalizedData as $item) {
            // Check if the item has a non-zero amount (indicating it's a transaction)
            if (isset($item['amount']) || isset($item['credit_only']) || isset($item['debit_only'])) {
                $singleValueAmount = $item['amount'] ?? 0;

                if (isset($item['credit_only']) && $item['credit_only'] != 0 && $singleValueAmount == 0) {
                    $singleValueAmount = $item['credit_only'];
                }
                if (isset($item['debit_only']) && $item['debit_only'] != 0 && $singleValueAmount == 0) {
                    $singleValueAmount = $item['debit_only'];
                }
                $singleValueTransactions[] = [
                    'amount' => $singleValueAmount,
                    'date_yyyymmdd' => $item['date_yyyymmdd'] ?? null,
                    'description' => $item['description'] ?? null,
                ];
            }

            // Check if the item has a closing balance (indicating it's a daily balance)
            if (isset($item['closing_balance']) && $item['closing_balance'] != 0) {
                $dailyBalances[] = [
                    'closing_balance' => $item['closing_balance'],
                    'date_yyyymmdd' => $item['date_yyyymmdd'] ?? null,
                ];
            }
        }

        return [
            'singleValueTransactions' => $singleValueTransactions,
            'dailyBalances' => $dailyBalances,
        ];
    }
}
