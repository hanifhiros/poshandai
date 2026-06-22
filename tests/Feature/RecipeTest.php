<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Unit;
use App\Models\StockCategory;

class RecipeTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_access_recipe_create_page()
    {
        $store = \App\Models\Store::factory()->create();
        $user = User::factory()->create(['role' => 'Manager']);
        $this->actingAs($user)->withSession(['selected_store' => $store->id, 'user_role' => 'Manager']);

        $response = $this->get('/manager/inventory/recipes/create');
        $response->assertStatus(200);
        $response->assertViewIs('handai-manager.inventory.recipes.create');
    }

    public function test_quick_create_stock_api()
    {
        $store = \App\Models\Store::factory()->create();
        $user = User::factory()->create(['role' => 'Manager']);
        $this->actingAs($user)->withSession(['selected_store' => $store->id, 'user_role' => 'Manager']);

        Unit::create(['symbol' => 'kg', 'name' => 'Kilogram', 'unit_type' => 'weight']);
        StockCategory::create(['stock_category_name' => 'Bahan Baku']);

        $payload = [
            'name' => 'Bahan X',
            'unit_id' => Unit::first()->id,
            'stock_category_id' => StockCategory::first()->id,
        ];

        $response = $this->postJson('/manager/inventory/stock/quick-create', $payload);
        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure(['stock' => ['id', 'name', 'unit_type']]);
    }
}
