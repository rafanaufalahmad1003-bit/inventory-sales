<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->category = Category::create([
            'name' => 'Kamera Mirrorless',
            'slug' => 'kamera-mirrorless',
        ]);
    }

    public function test_can_create_valid_camera_product(): void
    {
        $response = $this->actingAs($this->admin)->post('/products', [
            'sku' => 'CAM-TEST-001',
            'name' => 'Sony Alpha 7 IV Test',
            'category_id' => $this->category->id,
            'purchase_price' => 30000000,
            'selling_price' => 350000,
            'stock' => 5,
            'minimum_stock' => 2,
            'unit' => 'unit',
            'description' => 'Unit uji coba',
        ]);

        $response->assertRedirect('/products');
        $this->assertDatabaseHas('products', [
            'sku' => 'CAM-TEST-001',
            'stock' => 5,
        ]);
    }

    public function test_duplicate_sku_is_rejected(): void
    {
        Product::create([
            'sku' => 'CAM-DUPLICATE',
            'name' => 'Canon EOS R6',
            'category_id' => $this->category->id,
            'purchase_price' => 30000000,
            'selling_price' => 400000,
            'stock' => 3,
            'minimum_stock' => 1,
            'unit' => 'unit',
        ]);

        $response = $this->actingAs($this->admin)->post('/products', [
            'sku' => 'CAM-DUPLICATE',
            'name' => 'Canon EOS R6 Duplicate',
            'category_id' => $this->category->id,
            'purchase_price' => 25000000,
            'selling_price' => 350000,
            'stock' => 2,
            'minimum_stock' => 1,
            'unit' => 'unit',
        ]);

        $response->assertSessionHasErrors('sku');
    }

    public function test_negative_price_or_stock_is_rejected(): void
    {
        $response = $this->actingAs($this->admin)->post('/products', [
            'sku' => 'CAM-NEGATIVE',
            'name' => 'Nikon Z6 II',
            'category_id' => $this->category->id,
            'purchase_price' => -50000,
            'selling_price' => -10000,
            'stock' => -1,
            'minimum_stock' => 1,
            'unit' => 'unit',
        ]);

        $response->assertSessionHasErrors(['purchase_price', 'selling_price', 'stock']);
    }
}
