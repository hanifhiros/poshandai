<?php

namespace App\Helpers;

use App\Models\UnitConversion;
use Illuminate\Support\Facades\Cache;

class ConversionHelper
{
    /**
     * In-memory cache to avoid repeated DB queries within the same request.
     */
    protected static $cache = [];

    public static function getConversionRate($fromUnitId, $toUnitId)
    {
        // Jika satuan sama, langsung return 1
        if ($fromUnitId == $toUnitId) {
            return 1;
        }

        $cacheKey = "{$fromUnitId}_{$toUnitId}";

        // Check in-memory cache first (avoids repeated queries in loops)
        if (isset(static::$cache[$cacheKey])) {
            return static::$cache[$cacheKey];
        }

        $conversion = UnitConversion::where('from_unit_id', $fromUnitId)
            ->where('to_unit_id', $toUnitId)
            ->first();

        $rate = $conversion ? $conversion->conversion_rate : null;

        // Also check reverse conversion if forward not found
        if ($rate === null) {
            $reverse = UnitConversion::where('from_unit_id', $toUnitId)
                ->where('to_unit_id', $fromUnitId)
                ->first();
            if ($reverse && $reverse->conversion_rate > 0) {
                $rate = 1 / $reverse->conversion_rate;
            }
        }

        static::$cache[$cacheKey] = $rate;

        return $rate;
    }

    /**
     * Preload all conversions into memory cache. Call this before loops.
     */
    public static function preloadAll()
    {
        if (!empty(static::$cache)) {
            return;
        }

        $conversions = UnitConversion::all();
        foreach ($conversions as $conv) {
            $key = "{$conv->from_unit_id}_{$conv->to_unit_id}";
            static::$cache[$key] = $conv->conversion_rate;

            // Also cache reverse
            if ($conv->conversion_rate > 0) {
                $reverseKey = "{$conv->to_unit_id}_{$conv->from_unit_id}";
                if (!isset(static::$cache[$reverseKey])) {
                    static::$cache[$reverseKey] = 1 / $conv->conversion_rate;
                }
            }
        }
    }

    /**
     * Clear in-memory cache (useful for testing).
     */
    public static function clearCache()
    {
        static::$cache = [];
    }
}
