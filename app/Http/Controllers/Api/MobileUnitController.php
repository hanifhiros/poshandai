<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

class MobileUnitController extends Controller
{
    public function index()
    {
        $units = Unit::all()->map(function ($unit) {
            return [
                'id' => $unit->id,
                'name' => $unit->name,
                'symbol' => $unit->symbol,
                'type' => $unit->unit_type, // e.g., mass, volume, count
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $units,
        ]);
    }
}
