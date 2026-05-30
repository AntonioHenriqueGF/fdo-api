<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    // Method to handle the '/' route
    public function home()
    {
        return $this->successResponse('Welcome to the application!');
    }
}