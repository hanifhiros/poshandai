<?php

namespace App\Http\Controllers\Manager\Operational;

use App\Http\Controllers\Controller;
use App\Models\StockAlert;
use App\Models\ReorderSuggestion;
use App\Models\Store;
use App\Services\StockAlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockAlertController extends Controller
{
    public function index(Request $request)
    {
        $storeId = session('selected_store');
        $selected_store = $storeId ? Store::find($storeId) : null;

        // Refresh alerts
        StockAlertService::checkAllStockLevels($storeId);
        StockAlertService::checkExpiringItems($storeId);

        $summary = StockAlertService::getAlertSummary($storeId);

        $query = StockAlert::where('store_id', $storeId)
            ->whereIn('status', ['active', 'acknowledged'])
            // emulate FIELD ordering with CASE for sqlite compatibility
            ->orderByRaw("CASE alert_type WHEN 'out_of_stock' THEN 1 WHEN 'expiring_soon' THEN 2 WHEN 'low_stock' THEN 3 WHEN 'reorder_point' THEN 4 ELSE 5 END")
            ->orderByDesc('created_at');

        if ($request->filled('alert_type')) {
            $query->where('alert_type', $request->alert_type);
        }

        if ($request->filled('alertable_type')) {
            $typeMap = [
                'stock'          => 'App\\Models\\Stock',
                'product'        => 'App\\Models\\ProductVariants',
                'semi_finished'  => 'App\\Models\\SemiFinishedProduct',
                'batch'          => 'App\\Models\\StockBatch',
            ];
            if (isset($typeMap[$request->alertable_type])) {
                $query->where('alertable_type', $typeMap[$request->alertable_type]);
            }
        }

        $alerts = $query->paginate(20)->withQueryString();

        // Load the related model names
        foreach ($alerts as $alert) {
            $alert->item_name = self::resolveItemName($alert);
        }

        return view('handai-manager.operational.stock-alerts.index', compact(
            'selected_store', 'alerts', 'summary'
        ));
    }

    public function acknowledge(Request $request, $id)
    {
        $alert = StockAlert::findOrFail($id);
        abort_if($alert->store_id != session('selected_store'), 403);

        StockAlertService::acknowledgeAlert($id, Auth::id());

        return redirect()->route('manager.operational.stock-alerts.index')
            ->with('success', 'Alert telah di-acknowledge.');
    }

    public function reorderSuggestions(Request $request)
    {
        $storeId = session('selected_store');
        $selected_store = $storeId ? Store::find($storeId) : null;

        StockAlertService::generateReorderSuggestions($storeId);

        $suggestions = ReorderSuggestion::with(['stock', 'supplier'])
            ->where('store_id', $storeId)
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('handai-manager.operational.stock-alerts.reorder-suggestions', compact(
            'selected_store', 'suggestions'
        ));
    }

    public function dismissSuggestion(Request $request, $id)
    {
        $suggestion = ReorderSuggestion::findOrFail($id);
        abort_if($suggestion->store_id != session('selected_store'), 403);

        $suggestion->update(['status' => 'dismissed']);

        return redirect()->route('manager.operational.stock-alerts.reorder')
            ->with('success', 'Saran pembelian telah di-dismiss.');
    }

    private static function resolveItemName(StockAlert $alert): string
    {
        $model = $alert->alertable_type::find($alert->alertable_id);
        if (!$model) return 'Item tidak ditemukan';

        return match ($alert->alertable_type) {
            'App\\Models\\Stock'                => $model->name,
            'App\\Models\\ProductVariants'      => ($model->product?->name ?? 'Produk') . ' - ' . ($model->variantLabel ?? 'Default'),
            'App\\Models\\SemiFinishedProduct'  => $model->name,
            'App\\Models\\StockBatch'           => ($model->stock?->name ?? 'Batch') . ' (Batch #' . $model->id . ')',
            default                             => 'Unknown',
        };
    }
}
