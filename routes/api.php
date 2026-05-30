<?php

use App\Modules\Imports\Http\Controllers\ImportsController;
use App\Modules\Users\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::post('/import', [ImportsController::class, 'import']);

Route::post('/login', [LoginController::class, 'login']);
