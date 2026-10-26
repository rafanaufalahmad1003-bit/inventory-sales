<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $admin;
    private User $staff;
    private User $pelanggan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->pelanggan = User::factory()->create(['role' => 'pelanggan']);
    }

    public function test_staff_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->staff)->get('/users');
        $response->assertStatus(403);
    }

    public function test_staff_cannot_delete_product(): void
    {
        $category = Category::create(['name' => 'Kategori', 'slug' => 'kategori']);
        $product = Product::create([
            'category_id' => $category->id,
            'sku' => 'CAM-AUTH-TEST',
            'name' => 'Sony FX3',
            'purchase_price' => 50000000,
            'selling_price' => 700000,
            'stock' => 2,
            'minimum_stock' => 1,
            'unit' => 'unit',
        ]);

        $response = $this->actingAs($this->staff)->delete("/products/{$product->id}");
        $response->assertStatus(403);
    }

    public function test_admin_and_owner_can_both_access_user_management(): void
    {
        // Admin dapat mengakses
        $responseAdmin = $this->actingAs($this->admin)->get('/users');
        $responseAdmin->assertStatus(200);

        // Owner juga dapat mengakses (keduanya memiliki wewenang penuh yang sama)
        $responseOwner = $this->actingAs($this->owner)->get('/users');
        $responseOwner->assertStatus(200);
    }

    public function test_staff_can_view_and_create_products(): void
    {
        $response = $this->actingAs($this->staff)->get('/products');
        $response->assertStatus(200);

        $responseCreate = $this->actingAs($this->staff)->get('/products/create/new');
        $responseCreate->assertStatus(200);
    }
}
