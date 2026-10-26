<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseTransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;
    private User $admin;
    private Supplier $supplier;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        $category = Category::create(['name' => 'Lensa', 'slug' => 'lensa']);
        $this->supplier = Supplier::create([
            'name' => 'PT Sony Authorized Indonesia',
            'phone' => '021-123456',
        ]);

        $this->product = Product::create([
            'category_id' => $category->id,
            'sku' => 'LENS-TEST-01',
            'name' => 'Sony FE 24-70mm GM',
            'purchase_price' => 25000000,
            'selling_price' => 250000,
            'stock' => 2, // Awal 2 unit
            'minimum_stock' => 1,
            'unit' => 'unit',
        ]);
    }

    public function test_purchase_successfully_increases_product_stock(): void
    {
        $response = $this->actingAs($this->staff)->post('/purchases', [
            'supplier_id' => $this->supplier->id,
            'purchased_at' => '2026-10-26',
            'notes' => 'Pengadaan stok batch 1',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 3, // Beli 3 unit
                    'unit_cost' => 25000000,
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();

        // Stok produk harus bertambah dari 2 menjadi 5 (2 + 3)
        $this->product->refresh();
        $this->assertEquals(5, $this->product->stock);

        // Header PO tersimpan dengan total benar dihitung server (3 * 25.000.000 = 75.000.000)
        $this->assertDatabaseHas('purchases', [
            'supplier_id' => $this->supplier->id,
            'total' => 75000000,
            'status' => 'completed',
        ]);
    }
}
