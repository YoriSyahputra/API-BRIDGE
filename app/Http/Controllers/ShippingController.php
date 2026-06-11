<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;
use App\Private\PrivateKeyScheme;

class ShippingController extends Controller
{
    
    private $perKmFee = ("......"); // Set Your Per Km Fee Here

    // Point 1
    public function calculateShipping(Request $request)
    {
        $perKmFee = $this->perKmFee;

        $validated = $request->validate([
            'origin_lat' => 'required|numeric',
            'origin_lng' => 'required|numeric',
            'dest_lat' => 'required|numeric',
            'dest_lng' => 'required|numeric',
            'pass' => 'nullable|string'
        ]);

        // point 2
        $idempotencyKey = $request->header('X-Idempotency-Key');
        if (!$idempotencyKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'Idempotency key is required for security purposes.'
            ], 400);
        }
        if (Cache::has($idempotencyKey)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaction has already been processed. Please do not click again.'
            ], 429);
        }
        Cache::put($idempotencyKey, true, 60); // Cache the key for 60 seconds to prevent duplicate processing
        
        
        // Point 3
        try {
            $realUrl = '(......)'; //Real .NET Map Server URL
            $jwtToken = '(......)'; // Set your JWT Token
            $mapResponse = Http::withToken($jwtToken)
                                ->timeout(10) // Set timeout for the request
                                ->post($realUrl, [
                                    'origin_lat' => $validated['origin_lat'],
                                    'origin_lng' => $validated['origin_lng'],
                                    'dest_lat' => $validated['dest_lat'],
                                    'dest_lng' => $validated['dest_lng']
                                ]);

        // Point 4
            if ($mapResponse->failed()) {
                throw new \Exception('Failed Getting response from Map Server .NET. ');
            }

        // Point 5
            $distanceKm = $mapResponse->json('distance_in_km');
            $shippingFee = $this->calculateFee($distanceKm);

        // Point 6
            $adminData = [
                'user_id' => $request->user() ? $request->user()->id : null,
                'origin_lat' => $validated['origin_lat'],
                'origin_lng' => $validated['origin_lng'],
                'dest_lat' => $validated['dest_lat'],
                'dest_lng' => $validated['dest_lng'],
                'distance_km' => (float) $distanceKm,
                'shipping_fee_total' => (float) $shippingFee,
                'per_km_fee' => (float) $perKmFee,
                'currency' => 'RM',
                'created_at' => now(),
                'updated_at' => now()
                ];

            $userData = [
                'shipping_fee_total' => 'RM ' . number_format($shippingFee, 2),
                'distance_km'  => (float) $distanceKm,
                'currency' => 'RM',

            ];

        // Point 7
        $inputPass = $request->input('pass');

            if ($inputPass === PrivateKeyScheme::getPrivateKey('default')){
                $finalData = $adminData;
            } elseif ($inputPass === PrivateKeyScheme::getPrivateKey('first')) {
                $finalData = $userData;
            } else {
                $finalData = Crypt::encryptString(json_encode($adminData));
            }
            

        // Point 8
            return response()->json([
                'status' => 'success',
                'data' => $finalData
            ], 200);

        // Point 9
        } catch (\Exception $e) {
            Log::error('Middleware Routing Error: ' . $e->getMessage());

        // Point 10
            return response()->json([
                'status'  => 'error',
                'message' => 'An error occurred while processing your request. Please try again later.',
            ], 503);
        }
    }

        // Point 11
    private function calculateFee($distance)
    {
        return (float) $distance * $this->perKmFee;
    }
}