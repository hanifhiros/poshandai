<?php

namespace App\Http\Controllers\Api;

use App\Models\Store;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
class StoreController extends Controller
{
    /**
     * GET /api/stores
     * Public: kembalikan semua store beserta detailnya
     */
    public function index()
    {
        $stores = Store::select([
            'id',
            'store_name',
            'store_address',
            'is_open',
            'opening_time',
            'closing_time',
            'latitude',
            'longitude',
        ])->get();

        return response()->json($stores);
    }

    /**
     * GET /api/stores/nearby?lat={lat}&lng={lng}
     * Jika lat & lng diberikan, hitung jarak dan urutkan
     */
    public function nearby(Request $request)
    {
        $lat = $request->query('lat');
        $lng = $request->query('lng');

        if (!$lat || !$lng) {
            return response()->json([
                'error' => 'Parameter lat & lng wajib'
            ], 422);
        }

        // Haversine formula (jarak dalam km)
        $haversine = "(6371 * acos(
            cos(radians(?))
            * cos(radians(latitude))
            * cos(radians(longitude) - radians(?))
            + sin(radians(?))
            * sin(radians(latitude))
        ))";

        $stores = Store::select([
            'id',
            'store_name',
            'store_address',
            'is_open',
            'opening_time',
            'closing_time',
            'latitude',
            'longitude',
        ])
            ->selectRaw("$haversine AS distance", [$lat, $lng, $lat])
            ->orderBy('distance')
            ->get();

        return response()->json($stores);
    }
    public function show($id)
    {
        $store = Store::find($id);

        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'Store not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'store' => [
                'id' => $store->id,
                'store_name' => $store->store_name,
                'latitude' => $store->latitude,
                'longitude' => $store->longitude,
                'delivery_fee' => $store->delivery_fee, // per kilometer
                'rating' => $store->rating,
                'address' => $store->address,
            ]
        ]);
    }
}
