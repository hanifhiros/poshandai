<?php
// debug_hpp_map.php — compute HPP per variant across all BOMs (store-aware)
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Bom;
use App\Helpers\ConversionHelper;
use App\Models\ProductVariants;

$storeId = getenv('SELECTED_STORE') ?: null;
if ($argc > 1) $storeId = $argv[1];

$boms = Bom::with('stock')->when($storeId, function($q) use ($storeId) { return $q->where('store_id', $storeId); })->get();
ConversionHelper::preloadAll();

$hppMap = [];
foreach ($boms as $bom) {
    $vid = $bom->product_variants_id;
    if (!isset($hppMap[$vid])) $hppMap[$vid] = 0;
    $stock = $bom->stock;
    if (!$stock) continue;
    $rate = ConversionHelper::getConversionRate($bom->unit_id, $stock->unit_id) ?: 1;
    $cost = (float) $bom->quantity_required * $rate * (float) $stock->price_per_unit;
    $hppMap[$vid] += $cost;
}

// sort descending
arsort($hppMap);

echo "Top 30 variants by computed HPP:\n";
$idx = 0;
foreach ($hppMap as $vid => $val) {
    $variant = ProductVariants::find($vid);
    $name = $variant?->variantSummary() ?: '(unknown variant)';
    $pid = $variant?->product_id ?: '(unknown product)';
    printf("%3d) varId=%s productId=%s name=\"%s\" hpp=Rp %s\n", ++$idx, $vid, $pid, $name, number_format($val, 0, ',', '.'));
    if ($idx >= 30) break;
}

exit(0);
