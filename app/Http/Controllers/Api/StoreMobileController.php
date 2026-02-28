<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;
class StoreMobileController extends Controller
{
    public function getNearbyStores(Request $request)
{
    $userLat = $request->input('latitude');
    $userLng = $request->input('longitude');

    $stores = Store::selectRaw('*, 
        (6371 * acos(cos(radians(?)) * cos(radians(latitude)) 
        * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) 
        AS distance', [$userLat, $userLng, $userLat])
        ->orderBy('distance')
        ->get();

    return response()->json([
        'status' => 'success',
        'stores' => $stores
    ]);
}
}
