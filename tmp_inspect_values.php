<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p = \App\Models\ProductionHistory::first();
if (!$p) {
    echo "no prod\n";
    exit;
}
$pv = $p->productVariants;

var_dump([
    'production_id' => $p->id,
    'product_variants_id' => $p->product_variants_id,
    'qty' => $p->quantity_produced,
    'pv_hpp' => $pv?->hpp,
    'product_hpp' => $pv?->product?->hpp,
    'product_wage_per_unit' => $pv?->product?->wage_per_unit,
]);
