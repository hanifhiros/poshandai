<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Str;
use App\Models\{StockBatch, Stock};
use App\Helpers\ConversionHelper;

class MobileStockBatchController extends Controller
{



    public function store(Request $request, $stock_id)
    {
        $validated = $request->validate([
            'unit_qty' => 'required|numeric|min:1',
            'unit_id' => 'required|exists:units,id',
            'cost' => 'required|numeric|min:0.01',
            'buy_date' => 'required|date',
            'store_id' => 'required|exists:store,id',
            'nota' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $notaFilename = 'belum ada gambar';
        if ($request->hasFile('nota')) {
            $file = $request->file('nota');
            $notaFilename = Str::random(20) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('assets/nota', $notaFilename, 'public');
        }

        $batch = StockBatch::create([
            'stock_id' => $stock_id,
            'unit_qty' => $validated['unit_qty'],
            'unit_id' => $validated['unit_id'],
            'cost' => $validated['cost'],
            'buy_date' => $validated['buy_date'],
            'store_id' => $validated['store_id'],
            'nota_url' => $notaFilename,
        ]);

        $stock = Stock::findOrFail($stock_id);
        $today = now();
        $startDate = $today->copy()->subDays($stock->expired_duration ?? 0);

        $validBatches = $stock->batches()
            ->whereDate('buy_date', '>=', $startDate)
            ->whereDate('buy_date', '<=', $today)
            ->get();

        $totalCost = 0;
        $totalQty = 0;

        foreach ($validBatches as $b) {
            $conversionRate = ConversionHelper::getConversionRate($b->unit_id, $stock->unit_id);
            if ($conversionRate === null)
                continue;

            $convertedQty = $b->unit_qty * $conversionRate;
            $totalQty += $convertedQty;
            $totalCost += $b->cost;
        }

        $stock->unit_qty = $totalQty;
        $stock->price_per_unit = $totalQty > 0 ? round($totalCost / $totalQty, 2) : 0;
        $stock->save();

        return response()->json(['message' => 'Batch stok berhasil ditambahkan!']);
    }


    public function index(Request $request)
    {
        $storeId = $request->input('store_id');

        if (!$storeId) {
            return response()->json(['message' => 'store_id is required'], 400);
        }

        $batches = StockBatch::with(['stock', 'unit'])
            ->where('store_id', $storeId)
            ->orderByDesc('buy_date')
            ->get()
            ->map(function ($batch) {
                return [
                    'id' => $batch->id,
                    'stock_name' => $batch->stock->name ?? '-',
                    'unit_qty' => $batch->unit_qty,
                    'unit' => $batch->unit->symbol ?? '-',
                    'cost' => $batch->cost,
                    'buy_date' => $batch->buy_date,
                    'nota_url' => $batch->nota_url,
                    'isStored' => $batch->isStored,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $batches,
        ]);
    }

}
