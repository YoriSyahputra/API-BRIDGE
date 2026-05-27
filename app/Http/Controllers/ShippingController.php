<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
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
        $inputPass = $request->input('pass');

        if ($inputPass === PrivateKeyScheme::getPrivateKey('second')) {
                $logPath = storage_path('logs/laravel.log');
                if (file_exists($logPath)){
                    $logContent = file_get_contents($logPath);
                    return response()->json([
                        'access_mode' => 'Admin Log error View',
                        'log_content' => $logContent
                    ], 200);
                } else {
                    return response()->json([
                        'status' => 'success',
                        'access_mode' => 'Admin Log error View',
                        'log_content' => 'Log file not found.'
                    ], 200);
                }
            }

        // Point 3
        try {
            $mockUrl = 'http://127.0.0.1:8000/api/mock-net-map/distance'; //Test Case Mock .NET Map Server URL
            $mapResponse = Http::timeout(5)->post($mockUrl, [
                'origin'      => "{$validated['origin_lat']},{$validated['origin_lng']}",
                'destination' => "{$validated['dest_lat']},{$validated['dest_lng']}"
            ]);

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