<?php
// app/Http/Controllers/api/VariantAttributeController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VariantAttribute;

class VariantAttributeController extends Controller
{
    public function index()
    {
        $attributes = VariantAttribute::with('options')->get();

        return response()->json($attributes);
    }
}
