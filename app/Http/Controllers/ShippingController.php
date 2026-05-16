<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;

class ShippingController extends Controller
{
    private $baseFee = 3.85;
    private $perKmFee = 0.78;
        // Point 1
    public function calculateShipping(Request $request)
    {
        $baseFee = $this->baseFee;
        $perKmFee = $this->perKmFee;

        $validated = $request->validate([
            'origin_lat' => 'required|numeric',
            'origin_lng' => 'required|numeric',
            'dest_lat' => 'required|numeric',
            'dest_lng' => 'required|numeric',
            'pass' => 'nullable|string'
        ]);

        // Point 2
        try {
            $mockUrl = 'http://127.0.0.1:8000/api/mock-net-map/distance';            
            $mapResponse = Http::timeout(5)->post($mockUrl, [
                'origin'      => "{$validated['origin_lat']},{$validated['origin_lng']}",
                'destination' => "{$validated['dest_lat']},{$validated['dest_lng']}"
            ]);

        // Point 3
            if ($mapResponse->failed()) {
                throw new \Exception('Failed Getting response from Map Server .NET (mock). ');
            }

        // Point 4
            $distanceKm = $mapResponse->json('distance_in_km');
            $shippingFee = $this->calculateFee($distanceKm);

        // Point 5
            $realData = [
                    'distance_km'  => (float) $distanceKm,
                    'distance_from_origin_to_destination' => "{$validated['origin_lat']},{$validated['origin_lng']}" .  " to " . "{$validated['dest_lat']},{$validated['dest_lng']}",
                    'shipping_fee' => 'RM ' . number_format($shippingFee, 2),
                    'base_fee' => 'RM ' . number_format($baseFee, 2),
                    'per_Km_Fee' => 'RM ' . number_format($perKmFee, 2),
                    'currency' => 'RM'
                ];

        // Ponit 6
            if ($request->input('pass') === '123') {
                $finalData = $realData;
            }else{
                $finalData = Crypt::encryptString(json_encode($realData));
            }

        // Point 7
            return response()->json([
                'status' => 'success',
                'data' => $finalData
            ], 200);
        // Point 8
        } catch (\Exception $e) {
            Log::error('Middleware Routing Error: ' . $e->getMessage());
        // Point 9
            return response()->json([
                'status'  => 'error',
                'message' => 'Layanan kalkulasi ongkir sedang gangguan. Silakan coba lagi.',
            ], 503);
        }
    }

        // Point 10
    private function calculateFee($distance)
    {
        if ($distance <= 2) {
            return $this->baseFee;
        }

        $extraDistance = ceil($distance - 2); 
        
        return $this->baseFee + ($extraDistance * $this->perKmFee);
    }
}