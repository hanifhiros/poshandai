<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Store;
use App\Models\Unit;
use App\Models\StockCategory;
use App\Models\Stock;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariants;
use App\Models\Employee;
use App\Models\Bom;
use App\Models\ProductionHistory;
use App\Models\ProductionStockUsage;
use App\Models\ProductionWage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class ProductionTest extends TestCase
{
    use RefreshDatabase;

    protected Store $store;
    protected User $user;
    protected Employee $employee;
    protected Unit $unit;
    protected StockCategory $stockCategory;
    protected Stock $stock;
    protected ProductCategory $productCategory;
    protected Product $product;
    protected ProductVariants $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Store::factory()->create();
        $this->user = User::factory()->create(['role' => 'Manager']);
        
        $this->employee = Employee::create([
            'name' => 'Koki Handal',
            'email' => 'koki@poshandai.com',
            'password' => Hash::make('password'),
            'store_id' => $this->store->id,
            'contact_number' => '08122334455',
            'position' => 'Production Staff',
            'salary' => 3000000,
        ]);

        $this->unit = Unit::create([
            'symbol' => 'gr',
            'name' => 'Gram',
            'unit_type' => 'weight',
        ]);

        $this->stockCategory = StockCategory::create([
            'stock_category_name' => 'Bahan Baku',
        ]);

        // Create raw material stock (e.g. coffee beans)
        $this->stock = Stock::create([
            'name' => 'Biji Kopi Gayo',
            'unit_id' => $this->unit->id,
            'stock_category_id' => $this->stockCategory->id,
            'store_id' => $this->store->id,
            'unit_qty' => 1000, // 1000 grams in stock
            'price_per_unit' => 100, // cost 100 rupiah per gram
        ]);

        $this->productCategory = ProductCategory::create(['category_name' => 'Beverage']);
        
        $this->product = Product::create([
            'name' => 'Espresso Single',
            'category_id' => $this->productCategory->id,
            'store_id' => $this->store->id,
            'wage_per_unit' => 1000, // 1000 wage per cup produced
            'hpp' => 1500,
        ]);

        $this->variant = ProductVariants::create([
            'product_id' => $this->product->id,
            'store_id' => $this->store->id,
            'price' => 15000,
            'quantity' => 0, // start with 0 cups in stock
            'hpp' => 1500,
        ]);

        // Create BOM: 1 cup of Espresso requires 15 grams of coffee beans
        Bom::create([
            'product_id' => $this->product->id,
            'product_variants_id' => $this->variant->id,
            'stock_id' => $this->stock->id,
            'unit_id' => $this->unit->id,
            'store_id' => $this->store->id,
            'quantity_required' => 15,
        ]);
    }

    public function test_production_flow_consumes_stock_and_increases_variant_qty()
    {
        $payload = [
            'production_date' => now()->toDateString(),
            'pic_id' => $this->employee->id,
            'prod_type' => 'finished',
            'product_variants_id' => $this->variant->id,
            'quantity_produced' => 10, // Produce 10 cups
            'use_bom' => 'yes',
            'store_id' => $this->store->id,
        ];

        // Call the production store endpoint
        $response = $this->withoutMiddleware()
            ->actingAs($this->user)
            ->postJson('/api/produksi-store', $payload);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        // 1. Assert production history was recorded
        $this->assertDatabaseHas('production_history', [
            'store_id' => $this->store->id,
            'product_variants_id' => $this->variant->id,
            'quantity_produced' => 10,
        ]);

        // 2. Assert stock was consumed (15 gr * 10 = 150 gr consumed)
        // 1000 - 150 = 850 grams remaining
        $this->stock->refresh();
        $this->assertEquals(850, $this->stock->unit_qty);

        // 3. Assert variant quantity was increased by 10
        $this->variant->refresh();
        $this->assertEquals(10, $this->variant->quantity);

        // 4. Assert stock usage details were recorded
        $this->assertDatabaseHas('production_stock_usage', [
            'stock_id' => $this->stock->id,
            'quantity' => 150, // 15 gr * 10
        ]);

        // 5. Assert production wages were recorded (10 * 1000 = 10000 total wage)
        $this->assertDatabaseHas('production_wages', [
            'store_id' => $this->store->id,
            'employee_id' => $this->employee->id,
            'total_wage' => 10000,
        ]);
    }
}
