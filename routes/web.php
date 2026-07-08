<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'Welcome to the application!']);
});

Route::get('/contact', function () {
    return view('contact');
});

Route::get('/teste', function () {
    return 'teste';
});
