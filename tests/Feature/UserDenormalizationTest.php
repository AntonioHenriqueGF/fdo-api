<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('does not allow a user to access another user\'s transactions', function () {
  // User that tries to access the transaction of another user.
  $user = User::query()->create([
    'use_name' => 'Authenticated User',
    'use_email' => 'auth@example.com',
    'use_password' => Hash::make('password'),
  ]);

  // The other user.
  $otherUser = User::query()->create([
    'use_name' => 'Other User',
    'use_email' => 'other@example.com',
    'use_password' => Hash::make('password'),
  ]);

  // Create mock import and transaction for the other user.
  $importId = DB::table('imports')->insertGetId([
    'imp_user_id' => $otherUser->use_id,
    'imp_file_name' => 'file.csv',
    'imp_imported_at' => now(),
    'imp_file_hash' => 'hash_1',
  ]);

  DB::table('transactions')->insert([
    'tra_user_id' => $otherUser->use_id,
    'tra_import_id' => $importId,
    'tra_date' => '2026-01-01',
    'tra_description' => 'Transaction',
    'tra_amount' => 1234.56
  ]);

  // Create mock session for user.
  Sanctum::actingAs($user);

  $query = http_build_query([
    'date_start' => '2026-01-01',
    'date_end' => '2026-01-02',
  ]);

  # Legitimate request must return status 200 OK and no rows
  $legitimateRequest = $this->getJson('/api/transactions?' . $query);
  $legitimateRequest->assertSuccessful();

  expect($legitimateRequest->json('data'))->toMatchArray([
    'total' => 0,
    'rows' => [],
  ]);


  # Illegitimate request also must return status 200 OK and no rows
  $illegitimateQuery = http_build_query([
    'date_start' => '2026-01-01',
    'date_end' => '2026-01-01',
    'use_id' => $otherUser->use_id,
  ]);

  $illegitimateRequest = $this->getJson('/api/transactions?' . $illegitimateQuery);
  $illegitimateRequest->assertSuccessful();

  expect($illegitimateRequest->json('data'))->toMatchArray([
    'total' => 0,
    'rows' => [],
  ]);
});
