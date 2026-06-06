<?php

use App\Modules\Imports\Http\Controllers\ImportsController;
use App\Modules\Users\Http\Controllers\LoginController;
use App\Modules\Users\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [LoginController::class, 'login']);

Route::post('/signup', [UserController::class, 'create']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/import', [ImportsController::class, 'import']);

    Route::get('/me', [LoginController::class, 'me']);

    Route::post('/logout', [LoginController::class, 'logout']);
});

Route::get('/test-auth', function (Request $request) {

    return response()->json([
        'authenticated' => auth(),
        'session_id' => $request->session()->getId(),
        'session' => session()->all(),
        'cookies' => $request->cookies->all(),
    ]);
});
