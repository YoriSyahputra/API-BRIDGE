<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShippingController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Point 11
Route::post('/mock-net-map/distance', function (){
    return response()->json([
        'status' => 'success',
        'distance_in_km' => 15.72
    ]);
});

// Point 12
Route::post('/calculate-shipping', [ShippingController::class, 'calculateShipping']);