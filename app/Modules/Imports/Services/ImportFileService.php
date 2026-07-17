<?php

namespace App\Modules\Imports\Services;

use App\Modules\Imports\Models\ImportsModel;
use App\Modules\Transactions\Models\DailyBalancesModel;
use App\Modules\Transactions\Models\TransactionsModel;
use Illuminate\Support\Facades\DB;

class ImportFileService
{
    public function __construct(
        private ImportsModel $importsModel,
        private DailyBalancesModel $dailyBalancesModel,
        private TransactionsModel $transactionsModel
    ) {
        //
    }

    public function execute(int $userId, array $data): void
    {
        $binHex = hex2bin($data['fileHash']);

        if ($binHex === false) {
            throw new \InvalidArgumentException('Invalid file hash provided.');
        }

        DB::transaction(function () use ($userId, $data, $binHex) {
            if (! $this->importsModel->verifyUniqueImport($userId, $binHex)) {
                throw new \InvalidArgumentException('This file has already been imported by this user.');
            }

            $importId = $this->importsModel->saveImport($userId, $data['fileName'], $binHex);
            $separatedData = $this->extractTransactionsAndBalances($data['normalized']);

            $this->dailyBalancesModel->saveDailyBalances($userId, $importId, $separatedData['dailyBalances']);
            $this->transactionsModel->saveTransactions($userId, $importId, $separatedData['singleValueTransactions']);
        });
    }

    public function extractTransactionsAndBalances(array $normalizedData): array
    {
        $transactions = [];
        $dailyBalancesByDate = [];

        foreach ($normalizedData as $item) {

            if (isset($item['amount'])) {
                $amount = $item['amount'];
            } elseif (isset($item['credit_only'])) {
                $amount = $item['credit_only'];
            } elseif (isset($item['debit_only'])) {
                $amount = $item['debit_only'] > 0 ? -$item['debit_only'] : $item['debit_only'];
            } else {
                $amount = null;
            }

            if ($amount !== null) {
                $transactions[] = [
                    'amount' => $amount,
                    'date_yyyymmdd' => $item['date_yyyymmdd'] ?? null,
                    'description' => $item['description'] ?? null,
                    'internal_code' => $item['internal_code'] ?? null,
                ];
            }

            // Some banks provide daily balances in the same row as the transaction data, so we need to extract them separately.
            // We will use the date as the key to ensure that we only keep one daily balance per date.
            // If there is more than one daily balance for a given date, we will keep the last one we encounter in the data, which is usually the most recent one.
            if (isset($item['closing_balance'])) {
                $date = $item['date_yyyymmdd'] ?? null;

                if ($date !== null) {
                    $dailyBalancesByDate[$date] = [
                        'date_yyyymmdd' => $date,
                        'closing_balance' => $item['closing_balance'],
                    ];
                }
            }
        }

        return [
            'singleValueTransactions' => $transactions,
            'dailyBalances' => array_values($dailyBalancesByDate),
        ];
    }
}
