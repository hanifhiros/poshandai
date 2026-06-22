<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Store;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariants;
use App\Models\Order;
use App\Models\Invoice;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected Store $store;
    protected User $user;
    protected ProductCategory $category;
    protected Product $product;
    protected ProductVariants $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Store::factory()->create();
        $this->user = User::factory()->create();

        $this->category = ProductCategory::create(['category_name' => 'Tea']);
        $this->product = Product::create([
            'name' => 'Green Tea Latte',
            'category_id' => $this->category->id,
            'store_id' => $this->store->id,
            'hpp' => 4000,
        ]);

        $this->variant = ProductVariants::create([
            'product_id' => $this->product->id,
            'store_id' => $this->store->id,
            'price' => 10000,
            'quantity' => 15, // Initial stock of 15
            'hpp' => 4000,
        ]);
    }

    public function test_validate_cart_stock_returns_no_errors_for_sufficient_stock()
    {
        $cart = [
            [
                'variant_id' => $this->variant->id,
                'quantity' => 5,
            ]
        ];

        $errors = InventoryService::validateCartStock($cart);
        $this->assertEmpty($errors);
    }

    public function test_validate_cart_stock_returns_errors_for_insufficient_stock()
    {
        $cart = [
            [
                'variant_id' => $this->variant->id,
                'quantity' => 20, // 20 required, only 15 available
            ]
        ];

        $errors = InventoryService::validateCartStock($cart);
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString("tidak mencukupi", $errors[0]);
    }

    public function test_process_sale_deduction_deducts_quantity_successfully()
    {
        $cart = [
            [
                'variant_id' => $this->variant->id,
                'quantity' => 5,
            ]
        ];

        $order = Order::create([
            'store_id' => $this->store->id,
            'gross_amount' => 50000,
            'order_status' => 'terkirim',
        ]);

        $this->actingAs($this->user);

        DB::transaction(function () use ($cart, $order) {
            $totalHpp = InventoryService::processSaleDeduction(
                $cart, $order->id, $this->store->id
            );
            $this->assertEquals(20000, $totalHpp); // 5 * 4000 HPP
        });

        $this->variant->refresh();
        $this->assertEquals(10, $this->variant->quantity); // 15 - 5
    }

    public function test_restore_stock_on_cancel_adds_quantity_back_for_shipped_orders()
    {
        $this->actingAs($this->user);

        $order = Order::create([
            'store_id' => $this->store->id,
            'gross_amount' => 20000,
            'order_status' => 'cancelled',
        ]);

        // Invoice/bought records mapping the items to restore
        DB::table('invoice')->insert([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'variant_id' => $this->variant->id,
            'quantity_bought' => 5,
            'price' => 10000,
            'total_price' => 50000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Restore stock since previous state was 'terkirim'
        InventoryService::restoreStockOnCancel($order, 'terkirim');

        $this->variant->refresh();
        $this->assertEquals(20, $this->variant->quantity); // 15 + 5 restored
    }

    public function test_restore_stock_on_cancel_does_nothing_if_previous_status_was_not_shipped()
    {
        $order = Order::create([
            'store_id' => $this->store->id,
            'gross_amount' => 20000,
            'order_status' => 'cancelled',
        ]);

        DB::table('invoice')->insert([
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'variant_id' => $this->variant->id,
            'quantity_bought' => 5,
            'price' => 10000,
            'total_price' => 50000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Do not restore stock since previous state was not 'terkirim' (e.g. pending/belum terkirim)
        InventoryService::restoreStockOnCancel($order, 'belum terkirim');

        $this->variant->refresh();
        $this->assertEquals(15, $this->variant->quantity); // Unchanged
    }
}
