<?php

namespace App\Console\Commands;

use App\Models\Store;
use App\Services\StockAlertService;
use Illuminate\Console\Command;

class CheckStockAlerts extends Command
{
    protected $signature = 'app:check-stock-alerts';
    protected $description = 'Check stock levels and generate alerts for all stores';

    public function handle()
    {
        $stores = Store::all();

        foreach ($stores as $store) {
            StockAlertService::checkAllStockLevels($store->id);
            StockAlertService::checkExpiringItems($store->id);
            StockAlertService::generateReorderSuggestions($store->id);
        }

        $this->info('Stock alerts checked for ' . $stores->count() . ' stores.');
    }
}
