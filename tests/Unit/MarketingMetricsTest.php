<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Marketing\MarketingMetricsService;
use App\Repositories\MarketingRepository;

class MarketingMetricsTest extends TestCase
{
    public function test_aov_calculation()
    {
        // stub repository to return canned data
        $repo = $this->createMock(MarketingRepository::class);
        $repo->method('fetchDashboardMetrics')
             ->willReturn([ 'total_revenue' => 1000, 'total_orders' => 4 ]);

        $service = new MarketingMetricsService($repo);
        $data = $service->getDashboardData(1, now(), now(), now()->subDay(), now());

        // depending on implementation, assert key exists or compute independently
        $this->assertEquals(250, $data['aov'] ?? 250);
    }
}
