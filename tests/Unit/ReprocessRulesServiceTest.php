<?php

use App\Models\User;
use App\Modules\Categories\Services\ReprocessRulesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

/**
 * Creates a user with an import, returning both IDs.
 *
 * @return array{userId: int, importId: int}
 */
function createUserWithImport(string $email): array
{
    $user = User::query()->create([
        'use_name' => 'User ' . $email,
        'use_email' => $email,
        'use_password' => Hash::make('password'),
    ]);

    $importId = DB::table('imports')->insertGetId([
        'imp_user_id' => $user->use_id,
        'imp_file_name' => 'import.csv',
        'imp_file_hash' => 'hash-' . $email,
        'imp_imported_at' => now(),
    ]);

    return ['userId' => $user->use_id, 'importId' => $importId];
}

function createCategory(int $userId, string $description): int
{
    return DB::table('categories')->insertGetId([
        'cat_user_id' => $userId,
        'cat_description' => $description,
    ]);
}

function createRule(int $userId, int $categoryId, string $pattern, int $priority = 0): int
{
    return DB::table('rules')->insertGetId([
        'rul_user_id' => $userId,
        'rul_category_id' => $categoryId,
        'rul_pattern' => $pattern,
        'rul_priority' => $priority,
    ]);
}

/**
 * @param  array{userId: int, importId: int}  $owner
 */
function createTransaction(array $owner, string $description, ?int $categoryId = null): int
{
    return DB::table('transactions')->insertGetId([
        'tra_user_id' => $owner['userId'],
        'tra_import_id' => $owner['importId'],
        'tra_category_id' => $categoryId,
        'tra_date' => '2026-07-01 10:00:00',
        'tra_description' => $description,
        'tra_amount' => -10.00,
    ]);
}

function findTransaction(int $transactionId): object
{
    return DB::table('transactions')->where('tra_id', $transactionId)->first();
}

beforeEach(function () {
    $this->service = new ReprocessRulesService();
});

it('applies the matching rule category to the user transactions', function () {
    $owner = createUserWithImport('owner@example.com');
    $transportId = createCategory($owner['userId'], 'Transport');
    $ruleId = createRule($owner['userId'], $transportId, 'UBER');
    $transactionId = createTransaction($owner, 'UBER TRIP 123');

    $this->service->reprocessRulesForUser((string) $owner['userId']);

    $transaction = findTransaction($transactionId);

    expect($transaction->tra_category_id)->toBe($transportId)
        ->and($transaction->tra_matched_rule_id)->toBe($ruleId);
});

it('supports regular expression patterns', function () {
    $owner = createUserWithImport('owner@example.com');
    $foodId = createCategory($owner['userId'], 'Food');
    $ruleId = createRule($owner['userId'], $foodId, '^(IFOOD|RAPPI)');
    $ifoodId = createTransaction($owner, 'IFOOD *RESTAURANTE');
    $rappiId = createTransaction($owner, 'RAPPI BRASIL');
    $notAtStartId = createTransaction($owner, 'PAGAMENTO IFOOD');

    $this->service->reprocessRulesForUser((string) $owner['userId']);

    expect(findTransaction($ifoodId)->tra_matched_rule_id)->toBe($ruleId)
        ->and(findTransaction($rappiId)->tra_matched_rule_id)->toBe($ruleId)
        ->and(findTransaction($notAtStartId)->tra_matched_rule_id)->toBeNull();
});

it('applies the rule with the highest priority when several rules match', function () {
    $owner = createUserWithImport('owner@example.com');
    $transportId = createCategory($owner['userId'], 'Transport');
    $foodId = createCategory($owner['userId'], 'Food');
    createRule($owner['userId'], $transportId, 'UBER', 1);
    $highPriorityRuleId = createRule($owner['userId'], $foodId, 'UBER EATS', 10);
    $transactionId = createTransaction($owner, 'UBER EATS PEDIDO');

    $this->service->reprocessRulesForUser((string) $owner['userId']);

    $transaction = findTransaction($transactionId);

    expect($transaction->tra_category_id)->toBe($foodId)
        ->and($transaction->tra_matched_rule_id)->toBe($highPriorityRuleId);
});

it('overrides a previously assigned category when a rule matches', function () {
    $owner = createUserWithImport('owner@example.com');
    $oldCategoryId = createCategory($owner['userId'], 'Old');
    $newCategoryId = createCategory($owner['userId'], 'New');
    createRule($owner['userId'], $newCategoryId, 'NETFLIX');
    $transactionId = createTransaction($owner, 'NETFLIX.COM', $oldCategoryId);

    $this->service->reprocessRulesForUser((string) $owner['userId']);

    expect(findTransaction($transactionId)->tra_category_id)->toBe($newCategoryId);
});

it('leaves transactions untouched when no rule matches', function () {
    $owner = createUserWithImport('owner@example.com');
    $existingCategoryId = createCategory($owner['userId'], 'Existing');
    $transportId = createCategory($owner['userId'], 'Transport');
    createRule($owner['userId'], $transportId, 'UBER');
    $transactionId = createTransaction($owner, 'PADARIA', $existingCategoryId);

    $this->service->reprocessRulesForUser((string) $owner['userId']);

    $transaction = findTransaction($transactionId);

    expect($transaction->tra_category_id)->toBe($existingCategoryId)
        ->and($transaction->tra_matched_rule_id)->toBeNull();
});

it('does not apply rules or change transactions of other users', function () {
    $owner = createUserWithImport('owner@example.com');
    $other = createUserWithImport('other@example.com');

    $ownerCategoryId = createCategory($owner['userId'], 'Transport');
    createRule($owner['userId'], $ownerCategoryId, 'UBER');

    $otherCategoryId = createCategory($other['userId'], 'Other');
    createRule($other['userId'], $otherCategoryId, 'PADARIA');

    $ownerTransactionId = createTransaction($owner, 'PADARIA');
    $otherTransactionId = createTransaction($other, 'UBER TRIP');

    $this->service->reprocessRulesForUser((string) $owner['userId']);

    expect(findTransaction($ownerTransactionId)->tra_category_id)->toBeNull()
        ->and(findTransaction($otherTransactionId)->tra_category_id)->toBeNull();
});

it('processes transactions across multiple chunks', function () {
    $owner = createUserWithImport('owner@example.com');
    $transportId = createCategory($owner['userId'], 'Transport');
    createRule($owner['userId'], $transportId, 'UBER');

    DB::table('transactions')->insert(array_fill(0, 1001, [
        'tra_user_id' => $owner['userId'],
        'tra_import_id' => $owner['importId'],
        'tra_date' => '2026-07-01 10:00:00',
        'tra_description' => 'UBER TRIP',
        'tra_amount' => -10.00,
    ]));

    $this->service->reprocessRulesForUser((string) $owner['userId']);

    expect(DB::table('transactions')->where('tra_category_id', $transportId)->count())->toBe(1001);
});
