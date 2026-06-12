<?php

namespace App\Helpers;

class CacheHelper
{
    public static function marketingKey(int $storeId, string $suffix): string
    {
        return "marketing_store_{$storeId}_{$suffix}";
    }
}
