<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait that adds a `forStore` scope to easily filter by the current store.
 * Usage: Model::forStore($storeId)->get();
 */
trait ForStoreScope
{
    /**
     * Scope a query to the given store id (if provided).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int|null  $storeId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForStore(Builder $query, $storeId = null)
    {
        if ($storeId) {
            return $query->where('store_id', $storeId);
        }
        return $query;
    }
}
