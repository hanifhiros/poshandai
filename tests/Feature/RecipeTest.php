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
        $user = User::factory()->create();
        $this->actingAs($user)->withSession(['selected_store' => 1]);

        $response = $this->get('/manager/inventory/recipes/create');
        $response->assertStatus(200);
        $response->assertViewIs('handai-manager.inventory.recipes.create');
    }

    public function test_quick_create_stock_api()
    {
        $user = User::factory()->create();
        $this->actingAs($user)->withSession(['selected_store' => 1]);

        Unit::factory()->create(['unit_type' => 'weight']);
        StockCategory::factory()->create();

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
