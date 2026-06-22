<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Store;
use App\Models\Unit;
use App\Models\StockCategory;
use App\Models\Stock;
use App\Models\StockBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    protected Store $store;
    protected User $user;
    protected Role $managerRole;
    protected Unit $unit;
    protected StockCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->store = Store::factory()->create();
        $this->user = User::factory()->create(['role' => 'Manager']);
        $this->managerRole = Role::create(['name' => 'Manager']);
        $this->user->roles()->attach($this->managerRole->id, [
            'store_id' => $this->store->id,
            'is_multistore' => 0,
        ]);

        $this->unit = Unit::create([
            'symbol' => 'gr',
            'name' => 'Gram',
            'unit_type' => 'weight',
        ]);

        $this->category = StockCategory::create([
            'stock_category_name' => 'Bahan Baku',
        ]);
    }

    public function test_manager_can_quick_create_stock()
    {
        $payload = [
            'name' => 'Kopi Mentah',
            'unit_id' => $this->unit->id,
            'stock_category_id' => $this->category->id,
        ];

        $response = $this->actingAs($this->user)
            ->withSession([
                'selected_store' => $this->store->id,
                'user_role' => 'Manager',
            ])
            ->postJson(route('manager.inventory.stock.quick-create'), $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseHas('stock', [
            'name' => 'Kopi Mentah',
            'store_id' => $this->store->id,
        ]);
    }

    public function test_manager_can_purchase_and_store_stock_batch()
    {
        $stock = Stock::create([
            'name' => 'Gula Pasir',
            'unit_id' => $this->unit->id,
            'stock_category_id' => $this->category->id,
            'store_id' => $this->store->id,
            'unit_qty' => 100,
        ]);

        $payload = [
            'supplier_name' => 'Supplier Gula',
            'buy_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'items' => [
                [
                    'stock_id' => $stock->id,
                    'unit_id' => $this->unit->id,
                    'unit_qty' => 50,
                    'unit_price' => 10,
                    'cost' => 500,
                ]
            ]
        ];

        $response = $this->actingAs($this->user)
            ->withSession([
                'selected_store' => $this->store->id,
                'user_role' => 'Manager',
            ])
            ->postJson(route('manager.inventory.stock.store'), $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('stock_batches', [
            'stock_id' => $stock->id,
            'unit_qty' => 50,
            'cost' => 500,
            'isStored' => 'ya',
        ]);
    }

    public function test_manager_can_reduce_expired_stock_batches()
    {
        $stock = Stock::create([
            'name' => 'Susu Segar',
            'unit_id' => $this->unit->id,
            'stock_category_id' => $this->category->id,
            'store_id' => $this->store->id,
            'unit_qty' => 100,
            'expired_duration' => 7, // 7 days expiration
        ]);

        // Create an expired batch (purchased 10 days ago)
        StockBatch::create([
            'stock_id' => $stock->id,
            'stock_name' => $stock->name,
            'unit_id' => $this->unit->id,
            'unit_qty' => 40,
            'cost' => 400,
            'buy_date' => now()->subDays(10)->toDateString(),
            'store_id' => $this->store->id,
            'isStored' => 'ya',
        ]);

        $response = $this->actingAs($this->user)
            ->withSession([
                'selected_store' => $this->store->id,
                'user_role' => 'Manager',
            ])
            ->postJson(route('manager.inventory.stock.reduceExpiredStored', ['id' => $stock->id]));

        $response->assertStatus(302); // Redirects back

        // The expired batch should now be marked as not stored ('tidak')
        $this->assertDatabaseHas('stock_batches', [
            'stock_id' => $stock->id,
            'isStored' => 'tidak',
        ]);

        // The stock unit_qty should be reduced from 100 to 60 (100 - 40)
        $stock->refresh();
        $this->assertEquals(60, $stock->unit_qty);
    }
}
