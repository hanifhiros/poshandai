<?php

namespace App\Services\Marketing;

use App\Repositories\MarketingRepository;
use Illuminate\Support\Facades\Cache;

class MarketingMetricsService
{
    protected MarketingRepository $repo;

    public function __construct(MarketingRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getDashboardData(int $storeId, $start, $end, $prevStart, $prevEnd): array
    {
        $cacheKey = "marketing_dashboard_{$storeId}_{$start->toDateString()}_{$end->toDateString()}";

        return Cache::tags(['marketing', "store_{$storeId}"])
            ->remember($cacheKey, now()->addMinutes(10), function () use ($storeId, $start, $end, $prevStart, $prevEnd) {
                return $this->repo->fetchDashboardMetrics($storeId, $start, $end, $prevStart, $prevEnd);
            });
    }

    public function getRevenueMetrics(int $storeId, $start, $end): array
    {
        return $this->repo->fetchRevenueMetrics($storeId, $start, $end);
    }

    public function getRetentionData(int $storeId, $start, $end, $prevStart, $prevEnd): array
    {
        $cacheKey = "marketing_retention_{$storeId}_{$start->toDateString()}_{$end->toDateString()}";
        return Cache::tags(['marketing', "store_{$storeId}"])
            ->remember($cacheKey, now()->addMinutes(10), function () use ($storeId, $start, $end, $prevStart, $prevEnd) {
                return $this->repo->fetchRetentionMetrics($storeId, $start, $end, $prevStart, $prevEnd);
            });
    }

    public function getChurnData(int $storeId, $start, $end, $prevStart, $prevEnd): array
    {
        return $this->repo->fetchChurnMetrics($storeId, $start, $end, $prevStart, $prevEnd);
    }

    public function getProductContribution(int $storeId, $start, $end, int $limit = 5)
    {
        return $this->repo->fetchTopProductContribution($storeId, $start, $end, $limit);
    }

    public function getCustomerAnalytics(int $storeId, $start, $end, ?string $search = null)
    {
        return $this->repo->fetchCustomerAnalytics($storeId, $start, $end, $search);
    }
}
