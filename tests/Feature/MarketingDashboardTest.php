<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MarketingDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_authentication()
    {
        $response = $this->get('/manager/marketing/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_manager_can_view_dashboard_with_store_selected()
    {
        $store = \App\Models\Store::factory()->create();
        $user = User::factory()->create(['role' => 'Manager']);
        // assign role and set session store
        $this->actingAs($user)->withSession(['selected_store' => $store->id, 'user_role' => 'Manager']);

        $response = $this->get('/manager/marketing/dashboard');
        $response->assertStatus(200);
        $response->assertViewIs('handai-manager.marketing.dashboard.index');
    }

    public function test_high_value_customers_limited_to_ten()
    {
        $store = \App\Models\Store::factory()->create();
        $user = User::factory()->create(['role' => 'Manager']);
        $this->actingAs($user)->withSession(['selected_store' => $store->id, 'user_role' => 'Manager']);

        // create 12 customers with orders of increasing spend
        foreach (range(1, 12) as $i) {
            $cust = \App\Models\Customer::factory()->create();
            \App\Models\Order::factory()->count($i)->create([
                'store_id' => $store->id,
                'customer_id' => $cust->id,
                'gross_amount' => 1000 * $i, // bigger spend for later customers
            ]);
        }

        $response = $this->get('/manager/marketing/customer-analytics');
        $response->assertStatus(200);
        $data = $response->viewData();
        $this->assertArrayHasKey('highValueCustomers', $data);
        $this->assertCount(10, $data['highValueCustomers']);
    }

    public function test_most_repurchased_products_show_nonzero_counts()
    {
        $store = \App\Models\Store::factory()->create();
        $user = User::factory()->create(['role' => 'Manager']);
        $this->actingAs($user)->withSession(['selected_store' => $store->id, 'user_role' => 'Manager']);

        // create two repeat customers buying the same product
        $cust1 = Customer::factory()->create();
        $cust2 = Customer::factory()->create();
        $product = \App\Models\Product::factory()->create();
        Order::factory()->create([ 'store_id' => $store->id, 'customer_id' => $cust1->id ])
            ->invoices()->create([ 'product_id' => $product->id, 'gross_amount' => 1000, 'quantity_bought' => 1, 'price' => 1000 ]);
        Order::factory()->create([ 'store_id' => $store->id, 'customer_id' => $cust1->id ])
            ->invoices()->create([ 'product_id' => $product->id, 'gross_amount' => 1000, 'quantity_bought' => 1, 'price' => 1000 ]);
        Order::factory()->create([ 'store_id' => $store->id, 'customer_id' => $cust2->id ])
            ->invoices()->create([ 'product_id' => $product->id, 'gross_amount' => 1000, 'quantity_bought' => 1, 'price' => 1000 ]);
        Order::factory()->create([ 'store_id' => $store->id, 'customer_id' => $cust2->id ])
            ->invoices()->create([ 'product_id' => $product->id, 'gross_amount' => 1000, 'quantity_bought' => 1, 'price' => 1000 ]);

        $response = $this->get('/manager/marketing/product-performance');
        $data = $response->viewData();
        $this->assertArrayHasKey('mostRepurchasedProducts', $data);
        $this->assertNotEmpty($data['mostRepurchasedProducts']);
        $this->assertGreaterThan(0, $data['mostRepurchasedProducts'][0]->repeat_buyer_count ?? 0);
    }
}
