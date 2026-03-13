<?php
// debug_hpp.php — quick script to inspect BOM/HPP for a variant by product name
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProductVariants;
use App\Models\Bom;
use App\Helpers\ConversionHelper;

$productName = $argv[1] ?? 'Kopi susu gula aren';
echo "Searching variants for product name like: $productName\n";

$variant = ProductVariants::whereHas('product', function($q) use ($productName) {
    $q->where('name', 'like', "%$productName%");
})->first();

if (!$variant) {
    echo "Variant not found. Listing products and variants that match prefix...\n\n";
    $products = \App\Models\Product::where('name', 'like', "%$productName%")->with('variants')->get();
    foreach ($products as $p) {
        echo "Product: {$p->id} - {$p->name}\n";
        foreach ($p->variants as $vv) {
            echo "  Variant: {$vv->id} - {$vv->variantSummary()} - price: {$vv->price} - hpp: {$vv->hpp}\n";
        }
        echo "\n";
    }
    exit(1);
}

echo "Found variant id: {$variant->id}, variant: {$variant->variantSummary()}\n\n";

$boms = Bom::with('stock','unit')->where('product_variants_id', $variant->id)->get();
ConversionHelper::preloadAll();
$total = 0;
$rows = [];
foreach ($boms as $b) {
    $stock = $b->stock;
    $rate = ConversionHelper::getConversionRate($b->unit_id, $stock->unit_id) ?: 1;
    $price = $stock->price_per_unit ?? 0;
    $cost = $b->quantity_required * $rate * $price;
    $rows[] = [
        'stock_id' => $stock->id ?? null,
        'stock' => $stock->name ?? null,
        'qty_required' => $b->quantity_required,
        'bom_unit_id' => $b->unit_id,
        'bom_unit' => $b->unit?->symbol ?? null,
        'stock_unit_id' => $stock->unit_id ?? null,
        'stock_unit' => $stock->unit?->symbol ?? null,
        'price_per_unit' => $price,
        'conversion_rate' => $rate,
        'cost' => $cost,
    ];
    $total += $cost;
}

echo "Per-ingredient breakdown:\n";
foreach ($rows as $r) {
    echo json_encode($r, JSON_UNESCAPED_UNICODE) . "\n";
}

echo "\nTotal HPP computed: " . number_format($total, 2, '.', ',') . "\n";

exit(0);
