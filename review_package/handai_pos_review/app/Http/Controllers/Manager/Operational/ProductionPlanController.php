<?php

namespace App\Http\Controllers\Manager\Operational;

use App\Http\Controllers\Controller;
use App\Models\Bom;
use App\Models\Employee;
use App\Models\ProductionPlan;
use App\Models\ProductionPlanItem;
use App\Models\ProductVariants;
use App\Models\SemiFinishedProduct;
use App\Services\ProductionPlanningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionPlanController extends Controller
{
    public function index(Request $request)
    {
        $storeId = session('selected_store');
        $query = ProductionPlan::where('store_id', $storeId)
            ->withCount('items')
            ->latest('plan_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%{$s}%")->orWhere('plan_number', 'like', "%{$s}%"));
        }

        $plans = $query->paginate(20);

        return view('handai-manager.operational.production-plans.index', compact('plans'));
    }

    public function create()
    {
        $storeId = session('selected_store');

        // Get product variants that have BOM
        $variantIds = Bom::where('store_id', $storeId)->distinct()->pluck('product_variants_id')->filter();
        $variants = ProductVariants::whereIn('id', $variantIds)
            ->with(['product', 'options'])
            ->get()
            ->map(fn($v) => [
                'id'   => $v->id,
                'name' => ($v->product?->name ?? '') . ' - ' . ($v->options->pluck('value')->join(' / ') ?: $v->sku?->sku ?? ''),
                'type' => 'variant',
            ]);

        // Semi-finished products that have materials
        $semiFinished = SemiFinishedProduct::where('store_id', $storeId)
            ->whereHas('materials')
            ->get()
            ->map(fn($s) => [
                'id'   => $s->id,
                'name' => $s->name,
                'type' => 'semi_finished',
            ]);

        $products = $variants->merge($semiFinished);
        $employees = Employee::where('store_id', $storeId)->where('is_active', true)->get();

        return view('handai-manager.operational.production-plans.create', compact('products', 'employees'));
    }

    public function store(Request $request)
    {
        $storeId = session('selected_store');
        $request->validate([
            'name'                  => 'required|string|max:255',
            'start_date'            => 'required|date',
            'end_date'              => 'required|date|after_or_equal:start_date',
            'notes'                 => 'nullable|string',
            'items'                 => 'required|array|min:1',
            'items.*.type'          => 'required|in:variant,semi_finished',
            'items.*.product_id'    => 'required|integer',
            'items.*.quantity'      => 'required|numeric|min:0.001',
            'items.*.target_date'   => 'required|date',
            'items.*.assigned_to'   => 'nullable|integer|exists:employees,id',
        ]);

        DB::transaction(function () use ($request, $storeId) {
            $plan = ProductionPlan::create([
                'store_id'    => $storeId,
                'plan_number' => ProductionPlan::generateNumber($storeId),
                'name'        => $request->name,
                'plan_date'   => now()->toDateString(),
                'start_date'  => $request->start_date,
                'end_date'    => $request->end_date,
                'status'      => 'draft',
                'notes'       => $request->notes,
                'created_by'  => auth()->id(),
            ]);

            foreach ($request->items as $itemData) {
                $variantId = $itemData['type'] === 'variant' ? $itemData['product_id'] : null;
                $sfpId     = $itemData['type'] === 'semi_finished' ? $itemData['product_id'] : null;

                $itemName = '';
                if ($variantId) {
                    $v = ProductVariants::with(['product', 'options'])->find($variantId);
                    $itemName = ($v?->product?->name ?? '') . ' - ' . ($v?->options->pluck('value')->join(' / ') ?: '');
                } elseif ($sfpId) {
                    $itemName = SemiFinishedProduct::find($sfpId)?->name ?? '';
                }

                ProductionPlanItem::create([
                    'production_plan_id'       => $plan->id,
                    'store_id'                 => $storeId,
                    'product_variants_id'      => $variantId,
                    'semi_finished_product_id' => $sfpId,
                    'item_name'                => $itemName,
                    'planned_quantity'          => $itemData['quantity'],
                    'target_date'              => $itemData['target_date'],
                    'assigned_to'              => $itemData['assigned_to'] ?? null,
                ]);
            }

            // Auto-calculate MRP
            ProductionPlanningService::calculateMRP($plan);
        });

        return redirect()->route('manager.operational.production-plans.index')
            ->with('success', 'Production Plan berhasil dibuat & MRP dihitung.');
    }

    public function show(ProductionPlan $productionPlan)
    {
        abort_if($productionPlan->store_id != session('selected_store'), 403);

        $productionPlan->load(['items.assignee', 'items.productVariant.product', 'items.semiFinishedProduct']);

        $aggregated = ProductionPlanningService::getAggregatedRequirements($productionPlan);
        $shortages  = ProductionPlanningService::getShortages($productionPlan);

        return view('handai-manager.operational.production-plans.show', [
            'plan'       => $productionPlan,
            'aggregated' => $aggregated,
            'shortages'  => $shortages,
        ]);
    }

    public function confirm(ProductionPlan $productionPlan)
    {
        abort_if($productionPlan->store_id != session('selected_store'), 403);
        abort_if($productionPlan->status !== 'draft', 400, 'Hanya draft yang bisa dikonfirmasi.');

        $productionPlan->update(['status' => 'confirmed']);

        return back()->with('success', 'Plan dikonfirmasi.');
    }

    public function startProduction(ProductionPlan $productionPlan)
    {
        abort_if($productionPlan->store_id != session('selected_store'), 403);
        abort_if(!in_array($productionPlan->status, ['confirmed']), 400);

        $productionPlan->update(['status' => 'in_progress']);
        $productionPlan->items()->where('status', 'pending')->update(['status' => 'in_progress']);

        return back()->with('success', 'Produksi dimulai.');
    }

    public function recalculateMrp(ProductionPlan $productionPlan)
    {
        abort_if($productionPlan->store_id != session('selected_store'), 403);

        ProductionPlanningService::calculateMRP($productionPlan);

        return back()->with('success', 'MRP berhasil dihitung ulang.');
    }

    public function completeItem(Request $request, ProductionPlanItem $item)
    {
        $plan = $item->plan;
        abort_if($plan->store_id != session('selected_store'), 403);

        $request->validate([
            'produced_quantity' => 'required|numeric|min:0',
        ]);

        $item->update([
            'produced_quantity' => $item->produced_quantity + $request->produced_quantity,
            'status'            => ($item->produced_quantity + $request->produced_quantity >= $item->planned_quantity)
                ? 'completed' : 'in_progress',
        ]);

        // Check if all items completed
        $allCompleted = $plan->items()->where('status', '!=', 'completed')->where('status', '!=', 'cancelled')->doesntExist();
        if ($allCompleted) {
            $plan->update(['status' => 'completed']);
        }

        return back()->with('success', 'Produksi item diupdate.');
    }
}
