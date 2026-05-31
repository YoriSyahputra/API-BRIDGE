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
    private $perKmFee = 0.56; //Test case

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
            $mockUrl = 'http://127.0.0.1:8000/api/mock-net-map/distance'; //Test Case Mock .NET Map Server URL
            $jwtToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoxMjMsImlhdCI6MTY5ODQ4ODAwMH0.3s8n7j8mLh9vKZt3eV9u7q8w5x6y7z8a9b0c1d2e3f4g5h6i7j8k9l0m1n2o3p4q5r6s7t8u9v0w1x2y3z4a5b6c7d8e9f0g1h2i3j4k5l6m7n8o9p0q1r2s3t4u5v6w7x8y9z0a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6a7b8c9d0e1f2g3h4i5j6k7l8m9n0o1p2q3r4s5t';
            $mapResponse = Http::withToken($jwtToken)
                                ->timeout(5)
                                ->post($mockUrl);

        // Point 4 Test Case Response Mock .NET Map Server
            if ($mapResponse->failed()) {
                throw new \Exception('Failed Getting response from Map Server .NET (mock). ');
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
                'message' => 'Layanan kalkulasi ongkir sedang gangguan. Silakan coba lagi.',
            ], 503);
        }
    }

        // Point 11
    private function calculateFee($distance)
    {
        return (float) $distance * $this->perKmFee;
    }
}

