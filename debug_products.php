<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "VariantAttribute count: " . App\Models\VariantAttribute::count() . "\n";
echo "ProductCategory count: " . App\Models\ProductCategory::count() . "\n";

// Check if the view compiles
try {
    $view = view('handai-manager.inventory.create', [
        'categories' => App\Models\ProductCategory::all(),
        'selected_store' => null,
        'variantAttributes' => App\Models\VariantAttribute::with('options')->get(),
    ]);
    $html = $view->render();
    echo "View rendered OK, length: " . strlen($html) . "\n";
} catch (Throwable $e) {
    echo "View Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
