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
            $separatedData = $this->separateTransactionsAndBalances($data['normalized']);

            $this->dailyBalancesModel->saveDailyBalances($userId, $importId, $separatedData['dailyBalances']);
            $this->transactionsModel->saveTransactions($userId, $importId, $separatedData['singleValueTransactions']);
        });
    }

    public function separateTransactionsAndBalances(array $normalizedData): array
    {
        $singleValueTransactions = [];
        $dailyBalances = [];

        foreach ($normalizedData as $item) {
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
