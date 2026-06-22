<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Store;
use App\Models\Unit;
use App\Models\StockCategory;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    protected Store $store;
    protected User $user;
    protected Role $managerRole;
    protected Unit $unit;
    protected StockCategory $category;
    protected Supplier $supplier;
    protected Stock $stock;

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
            'symbol' => 'kg',
            'name' => 'Kilogram',
            'unit_type' => 'weight',
        ]);

        $this->category = StockCategory::create([
            'stock_category_name' => 'Bahan Baku',
        ]);

        $this->supplier = Supplier::create([
            'store_id' => $this->store->id,
            'name' => 'Supplier Utama',
            'is_active' => true,
            'payment_terms' => 'COD',
        ]);

        $this->stock = Stock::create([
            'name' => 'Kopi Arabika',
            'unit_id' => $this->unit->id,
            'stock_category_id' => $this->category->id,
            'store_id' => $this->store->id,
            'unit_qty' => 0,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_purchase_orders()
    {
        $response = $this->get(route('manager.operational.po.index'));
        $response->assertRedirect('/login');
    }

    public function test_manager_can_access_purchase_orders_index_and_create_pages()
    {
        $response = $this->actingAs($this->user)
            ->withSession([
                'selected_store' => $this->store->id,
                'user_role' => 'Manager',
            ])
            ->get(route('manager.operational.po.index'));

        $response->assertStatus(200);

        $response2 = $this->actingAs($this->user)
            ->withSession([
                'selected_store' => $this->store->id,
                'user_role' => 'Manager',
            ])
            ->get(route('manager.operational.po.create'));

        $response2->assertStatus(200);
    }

    public function test_manager_can_create_purchase_order()
    {
        $payload = [
            'supplier_id' => $this->supplier->id,
            'notes' => 'Catatan tes PO',
            'items' => [
                [
                    'stock_id' => $this->stock->id,
                    'unit_id' => $this->unit->id,
                    'quantity' => 10,
                    'unit_price' => 50000,
                ]
            ]
        ];

        $response = $this->actingAs($this->user)
            ->withSession([
                'selected_store' => $this->store->id,
                'user_role' => 'Manager',
            ])
            ->post(route('manager.operational.po.store'), $payload);

        $response->assertRedirect(route('manager.operational.po.index'));
        
        $this->assertDatabaseHas('purchase_orders', [
            'store_id' => $this->store->id,
            'supplier_id' => $this->supplier->id,
            'notes' => 'Catatan tes PO',
            'status' => 'pending',
            'total_amount' => 500000,
            'created_by' => $this->user->id,
        ]);

        $this->assertDatabaseHas('purchase_order_items', [
            'stock_id' => $this->stock->id,
            'unit_id' => $this->unit->id,
            'quantity' => 10,
            'unit_price' => 50000,
            'total_price' => 500000,
        ]);
    }

    public function test_manager_can_approve_purchase_order()
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-001',
            'supplier_id' => $this->supplier->id,
            'store_id' => $this->store->id,
            'status' => 'pending',
            'total_amount' => 500000,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->withSession([
                'selected_store' => $this->store->id,
                'user_role' => 'Manager',
            ])
            ->post(route('manager.operational.po.approve', $po->id));

        $response->assertRedirect(route('manager.operational.po.show', $po->id));
        $po->refresh();
        $this->assertEquals('approved', $po->status);
    }

    public function test_manager_can_receive_purchase_order_and_updates_inventory_and_accounting()
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-001',
            'supplier_id' => $this->supplier->id,
            'store_id' => $this->store->id,
            'status' => 'approved',
            'total_amount' => 500000,
            'created_by' => $this->user->id,
        ]);

        $item = PurchaseOrderItem::create([
            'purchase_order_id' => $po->id,
            'stock_id' => $this->stock->id,
            'unit_id' => $this->unit->id,
            'quantity' => 10,
            'unit_price' => 50000,
            'total_price' => 500000,
        ]);

        $response = $this->actingAs($this->user)
            ->withSession([
                'selected_store' => $this->store->id,
                'user_role' => 'Manager',
            ])
            ->post(route('manager.operational.po.receive', $po->id));

        $response->assertRedirect(route('manager.operational.po.show', $po->id));
        
        $po->refresh();
        $this->assertEquals('received', $po->status);

        // Verify stock quantity is updated
        $this->stock->refresh();
        $this->assertEquals(10, $this->stock->unit_qty);

        // Verify stock batch is created
        $this->assertDatabaseHas('stock_batches', [
            'stock_id' => $this->stock->id,
            'unit_qty' => 10,
            'cost' => 500000,
            'purchase_group' => $po->po_number,
        ]);

        // Verify inventory movement (stock_movements table) exists
        $this->assertDatabaseHas('stock_movements', [
            'stock_id' => $this->stock->id,
            'store_id' => $this->store->id,
            'movement_type' => 'PURCHASE_IN',
            'quantity' => 10,
        ]);

        // Verify accounting journals are created
        $this->assertDatabaseHas('journals', [
            'store_id' => $this->store->id,
            'source' => 'PURCHASE',
            'total_debit' => 500000,
            'total_credit' => 500000,
        ]);
    }

    public function test_manager_can_cancel_purchase_order()
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-001',
            'supplier_id' => $this->supplier->id,
            'store_id' => $this->store->id,
            'status' => 'pending',
            'total_amount' => 500000,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)
            ->withSession([
                'selected_store' => $this->store->id,
                'user_role' => 'Manager',
            ])
            ->post(route('manager.operational.po.cancel', $po->id));

        $response->assertRedirect(route('manager.operational.po.show', $po->id));
        $po->refresh();
        $this->assertEquals('cancelled', $po->status);
    }
}
