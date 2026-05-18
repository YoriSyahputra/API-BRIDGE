<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app' => 'Middleware API Map System',
        'status' => 'Running securely'
    ]);
});
