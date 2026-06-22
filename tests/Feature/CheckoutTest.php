<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Store;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariants;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Journal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected Store $store;
    protected User $user;
    protected Role $posRole;
    protected ProductCategory $category;
    protected Product $product;
    protected ProductVariants $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Store::factory()->create();
        $this->user = User::factory()->create(['role' => 'POS']);
        $this->posRole = Role::create(['name' => 'POS']);
        $this->user->roles()->attach($this->posRole->id, [
            'store_id' => $this->store->id,
            'is_multistore' => 0,
        ]);

        $this->category = ProductCategory::create(['category_name' => 'Coffee']);
        
        $this->product = Product::create([
            'name' => 'Kopi Aren',
            'category_id' => $this->category->id,
            'store_id' => $this->store->id,
            'hpp' => 5000,
        ]);

        $this->variant = ProductVariants::create([
            'product_id' => $this->product->id,
            'store_id' => $this->store->id,
            'price' => 12000,
            'quantity' => 10, // Stock set to 10
            'hpp' => 5000,
        ]);
    }

    public function test_checkout_succeeds_with_sufficient_stock()
    {
        $cart = [
            [
                'product_id' => $this->product->id,
                'variant_id' => $this->variant->id,
                'quantity' => 3,
                'price' => 12000,
            ]
        ];

        $response = $this->actingAs($this->user)
            ->withSession([
                'selected_store' => $this->store->id,
                'user_role' => 'POS',
                'cart' => $cart,
            ])
            ->postJson(route('pos.cart.checkoutCustomer'), [
                'payment_method' => 'tunai',
                'customer_type' => 'none',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Assert Order created
        $this->assertDatabaseHas('orders', [
            'store_id' => $this->store->id,
            'order_status' => 'terkirim',
            'gross_amount' => 36000,
        ]);

        // Assert Invoice entry created
        $this->assertDatabaseHas('invoice', [
            'product_id' => $this->product->id,
            'variant_id' => $this->variant->id,
            'quantity_bought' => 3,
            'price' => 12000,
        ]);

        // Assert Stock deducted
        $this->variant->refresh();
        $this->assertEquals(7, $this->variant->quantity);

        // Assert Accounting Journal exists
        $this->assertDatabaseHas('journals', [
            'store_id' => $this->store->id,
            'source' => 'POS',
            'total_debit' => 36000 + 15000, // Revenue (36000) + HPP (3 * 5000)
        ]);

        // Assert cart is cleared from session
        $this->assertNull(session('cart'));
    }

    public function test_checkout_fails_with_insufficient_stock()
    {
        $cart = [
            [
                'product_id' => $this->product->id,
                'variant_id' => $this->variant->id,
                'quantity' => 15, // Required quantity exceeds stock of 10
                'price' => 12000,
            ]
        ];

        $response = $this->actingAs($this->user)
            ->withSession([
                'selected_store' => $this->store->id,
                'user_role' => 'POS',
                'cart' => $cart,
            ])
            ->postJson(route('pos.cart.checkoutCustomer'), [
                'payment_method' => 'tunai',
                'customer_type' => 'none',
            ]);

        $response->assertStatus(500);
        $response->assertJson(['success' => false]);
        $response->assertJsonFragment(['message' => "Gagal menyimpan order: Stok 'Kopi Aren' tidak mencukupi. Dibutuhkan: 15, tersedia: 10"]);

        // Assert Order not created
        $this->assertEquals(0, Order::count());
        $this->assertEquals(0, Invoice::count());

        // Assert Stock untouched
        $this->variant->refresh();
        $this->assertEquals(10, $this->variant->quantity);
    }
}
