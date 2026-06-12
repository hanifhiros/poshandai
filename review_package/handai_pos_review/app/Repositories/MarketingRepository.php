<?php

namespace App\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MarketingRepository
{
    public function forStore(int $storeId)
    {
        // leave for compatibility with possible future query builder chaining
        return DB::table('orders')->where('store_id', $storeId);
    }

    public function fetchDashboardMetrics(int $storeId, $start, $end, $prevStart, $prevEnd): array
    {
        // implement using scopes or raw queries
        // this is a stub; see earlier controller code for logic
        return [];
    }

    public function fetchRevenueMetrics(int $storeId, $start, $end): array
    {
        return [];
    }

    public function fetchRetentionMetrics(int $storeId, $start, $end, $prevStart, $prevEnd): array
    {
        return [];
    }

    public function fetchChurnMetrics(int $storeId, $start, $end, $prevStart, $prevEnd): array
    {
        return [];
    }

    public function fetchTopProductContribution(int $storeId, $start, $end, int $limit = 5)
    {
        return collect();
    }

    public function fetchCustomerAnalytics(int $storeId, $start, $end, ?string $search = null)
    {
        return collect();
    }
}
