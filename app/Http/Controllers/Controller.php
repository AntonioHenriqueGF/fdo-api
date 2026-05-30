<?php

namespace App\Http\Controllers;

abstract class Controller
{
    // Function to return a basic success response with a message
    public function successResponse(string $message = 'Operation successful', $data = null, int $status = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $status);
    }

    // Function to return a basic error response with a message
    public function errorResponse(string $message = 'Operation failed', $data = null, int $status = 400)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data
        ], $status);
    }
}
