<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('it returns category transaction aggregates for the authenticated user', function () {
    $user = User::query()->create([
        'use_name' => 'Authenticated User',
        'use_email' => 'auth@example.com',
        'use_password' => Hash::make('password'),
    ]);

    $otherUser = User::query()->create([
        'use_name' => 'Other User',
        'use_email' => 'other@example.com',
        'use_password' => Hash::make('password'),
    ]);

    Sanctum::actingAs($user);

    $importId = DB::table('imports')->insertGetId([
        'imp_user_id' => $user->use_id,
        'imp_file_name' => 'import.csv',
        'imp_file_hash' => 'hash-1',
        'imp_imported_at' => now(),
    ]);

    $otherImportId = DB::table('imports')->insertGetId([
        'imp_user_id' => $otherUser->use_id,
        'imp_file_name' => 'other.csv',
        'imp_file_hash' => 'hash-2',
        'imp_imported_at' => now(),
    ]);

    $foodCategoryId = DB::table('categories')->insertGetId([
        'cat_user_id' => $user->use_id,
        'cat_description' => 'Food',
    ]);

    $rentCategoryId = DB::table('categories')->insertGetId([
        'cat_user_id' => $user->use_id,
        'cat_description' => 'Rent',
    ]);

    $otherCategoryId = DB::table('categories')->insertGetId([
        'cat_user_id' => $otherUser->use_id,
        'cat_description' => 'Other',
    ]);

    DB::table('transactions')->insert([
        [
            'tra_user_id' => $user->use_id,
            'tra_import_id' => $importId,
            'tra_category_id' => $foodCategoryId,
            'tra_matched_rule_id' => null,
            'tra_date' => '2026-07-01 10:00:00',
            'tra_description' => 'Lunch',
            'tra_amount' => 10.50,
        ],
        [
            'tra_user_id' => $user->use_id,
            'tra_import_id' => $importId,
            'tra_category_id' => $foodCategoryId,
            'tra_matched_rule_id' => null,
            'tra_date' => '2026-07-01 15:00:00',
            'tra_description' => 'Coffee',
            'tra_amount' => 5.00,
        ],
        [
            'tra_user_id' => $user->use_id,
            'tra_import_id' => $importId,
            'tra_category_id' => $rentCategoryId,
            'tra_matched_rule_id' => null,
            'tra_date' => '2026-07-02 09:00:00',
            'tra_description' => 'July rent',
            'tra_amount' => 1000.00,
        ],
        [
            'tra_user_id' => $user->use_id,
            'tra_import_id' => $importId,
            'tra_category_id' => $foodCategoryId,
            'tra_matched_rule_id' => null,
            'tra_date' => '2026-08-01 12:00:00',
            'tra_description' => 'August meal',
            'tra_amount' => 30.00,
        ],
        [
            'tra_user_id' => $otherUser->use_id,
            'tra_import_id' => $otherImportId,
            'tra_category_id' => $otherCategoryId,
            'tra_matched_rule_id' => null,
            'tra_date' => '2026-07-01 08:00:00',
            'tra_description' => 'Other user transaction',
            'tra_amount' => 999.00,
        ],
    ]);

    $query = http_build_query([
        'date_start' => '2026-07-01',
        'date_end' => '2026-07-31',
        'category_id' => [$foodCategoryId, $rentCategoryId],
    ]);

    $totalResponse = $this->getJson('/api/transactions/categories/total?' . $query);
    $totalResponse->assertSuccessful();

    expect(collect($totalResponse->json('data'))->keyBy('category_id')->all())
        ->toMatchArray([
            $foodCategoryId => [
                'category_id' => $foodCategoryId,
                'category_description' => 'Food',
                'total_amount' => '15.50',
            ],
            $rentCategoryId => [
                'category_id' => $rentCategoryId,
                'category_description' => 'Rent',
                'total_amount' => '1000.00',
            ],
        ]);

    $dailyResponse = $this->getJson('/api/transactions/categories/daily?' . $query);
    $dailyResponse->assertSuccessful();

    expect($dailyResponse->json('data'))->toMatchArray([
        [
            'category_id' => $foodCategoryId,
            'category_description' => 'Food',
            'date' => '2026-07-01',
            'total_amount' => '15.50',
        ],
        [
            'category_id' => $rentCategoryId,
            'category_description' => 'Rent',
            'date' => '2026-07-02',
            'total_amount' => '1000.00',
        ],
    ]);

    $monthlyResponse = $this->getJson('/api/transactions/categories/monthly?' . $query);
    $monthlyResponse->assertSuccessful();

    expect($monthlyResponse->json('data'))->toMatchArray([
        [
            'category_id' => $foodCategoryId,
            'category_description' => 'Food',
            'month' => '2026-07',
            'total_amount' => '15.50',
        ],
        [
            'category_id' => $rentCategoryId,
            'category_description' => 'Rent',
            'month' => '2026-07',
            'total_amount' => '1000.00',
        ],
    ]);
});
