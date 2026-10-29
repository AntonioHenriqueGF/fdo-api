<?php

use App\Modules\Imports\Models\ImportsModel;
use App\Modules\Imports\Services\ImportFileService;
use App\Modules\Transactions\Models\DailyBalancesModel;
use App\Modules\Transactions\Models\TransactionsModel;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->importsModel = Mockery::mock(ImportsModel::class);
    $this->dailyBalancesModel = Mockery::mock(DailyBalancesModel::class);
    $this->transactionsModel = Mockery::mock(TransactionsModel::class);

    $this->service = new ImportFileService(
        $this->importsModel,
        $this->dailyBalancesModel,
        $this->transactionsModel
    );

    // Runs the closure directly so no real database connection is needed.
    DB::shouldReceive('transaction')->andReturnUsing(fn (Closure $callback) => $callback());
});

describe('execute', function () {
    it('saves the import, daily balances and transactions', function () {
        $fileHash = md5('file-content');

        $this->importsModel->shouldReceive('verifyUniqueImport')
            ->once()
            ->with(1, hex2bin($fileHash))
            ->andReturnTrue();

        $this->importsModel->shouldReceive('saveImport')
            ->once()
            ->with(1, 'extrato.csv', hex2bin($fileHash))
            ->andReturn(10);

        $this->dailyBalancesModel->shouldReceive('saveDailyBalances')
            ->once()
            ->with(1, 10, [
                ['date_yyyymmdd' => '20260701', 'closing_balance' => 500.00],
            ]);

        $this->transactionsModel->shouldReceive('saveTransactions')
            ->once()
            ->with(1, 10, [
                [
                    'amount' => -20.00,
                    'date_yyyymmdd' => '20260701',
                    'description' => 'UBER',
                    'internal_code' => null,
                ],
            ]);

        $this->service->execute(1, [
            'fileHash' => $fileHash,
            'fileName' => 'extrato.csv',
            'normalized' => [
                [
                    'amount' => -20.00,
                    'date_yyyymmdd' => '20260701',
                    'description' => 'UBER',
                    'closing_balance' => 500.00,
                ],
            ],
        ]);
    });

    it('throws when the file hash is not valid hexadecimal', function () {
        $this->importsModel->shouldNotReceive('verifyUniqueImport');
        $this->importsModel->shouldNotReceive('saveImport');

        $this->service->execute(1, [
            'fileHash' => 'not-a-hex-hash',
            'fileName' => 'extrato.csv',
            'normalized' => [],
        ]);
    })->throws(InvalidArgumentException::class, 'Invalid file hash provided.');

    it('throws when the file has already been imported by the user', function () {
        $this->importsModel->shouldReceive('verifyUniqueImport')->once()->andReturnFalse();
        $this->importsModel->shouldNotReceive('saveImport');
        $this->dailyBalancesModel->shouldNotReceive('saveDailyBalances');
        $this->transactionsModel->shouldNotReceive('saveTransactions');

        $this->service->execute(1, [
            'fileHash' => md5('file-content'),
            'fileName' => 'extrato.csv',
            'normalized' => [],
        ]);
    })->throws(InvalidArgumentException::class, 'This file has already been imported by this user.');
});

describe('extractTransactionsAndBalances', function () {
    it('uses the amount field when present', function () {
        $result = $this->service->extractTransactionsAndBalances([
            ['amount' => 150.25, 'credit_only' => 999, 'date_yyyymmdd' => '20260701', 'description' => 'PIX', 'internal_code' => 'ABC'],
        ]);

        expect($result['singleValueTransactions'])->toBe([
            ['amount' => 150.25, 'date_yyyymmdd' => '20260701', 'description' => 'PIX', 'internal_code' => 'ABC'],
        ]);
    });

    it('uses credit_only as a positive amount', function () {
        $result = $this->service->extractTransactionsAndBalances([
            ['credit_only' => 300.00, 'date_yyyymmdd' => '20260701', 'description' => 'SALARIO'],
        ]);

        expect($result['singleValueTransactions'][0]['amount'])->toBe(300.00);
    });

    it('converts a positive debit_only into a negative amount', function () {
        $result = $this->service->extractTransactionsAndBalances([
            ['debit_only' => 45.90, 'date_yyyymmdd' => '20260701', 'description' => 'MERCADO'],
        ]);

        expect($result['singleValueTransactions'][0]['amount'])->toBe(-45.90);
    });

    it('keeps an already negative debit_only as is', function () {
        $result = $this->service->extractTransactionsAndBalances([
            ['debit_only' => -45.90, 'date_yyyymmdd' => '20260701', 'description' => 'MERCADO'],
        ]);

        expect($result['singleValueTransactions'][0]['amount'])->toBe(-45.90);
    });

    it('fills missing optional fields with null', function () {
        $result = $this->service->extractTransactionsAndBalances([
            ['amount' => 10.00],
        ]);

        expect($result['singleValueTransactions'])->toBe([
            ['amount' => 10.00, 'date_yyyymmdd' => null, 'description' => null, 'internal_code' => null],
        ]);
    });

    it('ignores rows without any amount field as transactions', function () {
        $result = $this->service->extractTransactionsAndBalances([
            ['date_yyyymmdd' => '20260701', 'closing_balance' => 1000.00],
        ]);

        expect($result['singleValueTransactions'])->toBe([])
            ->and($result['dailyBalances'])->toBe([
                ['date_yyyymmdd' => '20260701', 'closing_balance' => 1000.00],
            ]);
    });

    it('keeps only the last closing balance for each date', function () {
        $result = $this->service->extractTransactionsAndBalances([
            ['amount' => -10.00, 'date_yyyymmdd' => '20260701', 'closing_balance' => 990.00],
            ['amount' => -20.00, 'date_yyyymmdd' => '20260701', 'closing_balance' => 970.00],
            ['amount' => 50.00, 'date_yyyymmdd' => '20260702', 'closing_balance' => 1020.00],
        ]);

        expect($result['singleValueTransactions'])->toHaveCount(3)
            ->and($result['dailyBalances'])->toBe([
                ['date_yyyymmdd' => '20260701', 'closing_balance' => 970.00],
                ['date_yyyymmdd' => '20260702', 'closing_balance' => 1020.00],
            ]);
    });

    it('ignores closing balances without a date', function () {
        $result = $this->service->extractTransactionsAndBalances([
            ['amount' => 10.00, 'closing_balance' => 1000.00],
        ]);

        expect($result['dailyBalances'])->toBe([]);
    });

    it('returns empty lists for empty input', function () {
        expect($this->service->extractTransactionsAndBalances([]))->toBe([
            'singleValueTransactions' => [],
            'dailyBalances' => [],
        ]);
    });
});
