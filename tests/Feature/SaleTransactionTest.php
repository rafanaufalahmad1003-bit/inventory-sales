<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleTransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;
    private User $admin;
    private Customer $customer;
    private Product $camera;
    private Product $lens;

    protected function setUp(): void
    {
        parent::setUp();
        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        $category = Category::create(['name' => 'Kamera', 'slug' => 'kamera']);

        $this->customer = Customer::create([
            'nik' => '3271012304950001',
            'name' => 'Dimas Anggara',
            'phone' => '081234567890',
        ]);

        $this->camera = Product::create([
            'category_id' => $category->id,
            'sku' => 'CAM-TEST-A7',
            'name' => 'Sony A7 IV',
            'purchase_price' => 30000000,
            'selling_price' => 300000, // Rp 300.000 / hari
            'stock' => 5,
            'minimum_stock' => 2,
            'unit' => 'unit',
        ]);

        $this->lens = Product::create([
            'category_id' => $category->id,
            'sku' => 'LENS-TEST-2470',
            'name' => 'Sony 24-70mm GM',
            'purchase_price' => 25000000,
            'selling_price' => 200000, // Rp 200.000 / hari
            'stock' => 2,
            'minimum_stock' => 1,
            'unit' => 'unit',
        ]);
    }

    public function test_sale_successfully_decrements_stock_and_calculates_server_side_total(): void
    {
        // Rental 1 hari: 1 Kamera (300rb) + 1 Lensa (200rb) = Total 500rb
        $response = $this->actingAs($this->staff)->post('/sales', [
            'customer_id' => $this->customer->id,
            'type' => 'rental',
            'rental_start_date' => '2026-10-26',
            'rental_end_date' => '2026-10-26', // 1 hari
            'payment_method' => 'cash',
            'sold_at' => '2026-10-26 10:00:00',
            'items' => [
                ['product_id' => $this->camera->id, 'quantity' => 2],
                ['product_id' => $this->lens->id, 'quantity' => 1],
            ],
        ]);

        $response->assertSessionHasNoErrors();

        // Stok kamera berkurang dari 5 menjadi 3 (5 - 2)
        $this->camera->refresh();
        $this->assertEquals(3, $this->camera->stock);

        // Stok lensa berkurang dari 2 menjadi 1 (2 - 1)
        $this->lens->refresh();
        $this->assertEquals(1, $this->lens->stock);

        // Server menghitung total: (2 * 300.000) + (1 * 200.000) = 800.000
        $this->assertDatabaseHas('sales', [
            'customer_id' => $this->customer->id,
            'total' => 800000,
            'status' => 'completed',
        ]);
    }

    public function test_sale_is_rejected_when_stock_is_insufficient(): void
    {
        // Lensa hanya ada 2 unit, coba sewa 5 unit
        $response = $this->actingAs($this->staff)->post('/sales', [
            'customer_id' => $this->customer->id,
            'type' => 'rental',
            'rental_start_date' => '2026-10-26',
            'rental_end_date' => '2026-10-26',
            'payment_method' => 'cash',
            'sold_at' => '2026-10-26 10:00:00',
            'items' => [
                ['product_id' => $this->lens->id, 'quantity' => 5], // Melebihi stok (stok 2)
            ],
        ]);

        $response->assertSessionHasErrors('items');

        // Pastikan stok tidak berubah sama sekali
        $this->lens->refresh();
        $this->assertEquals(2, $this->lens->stock);

        // Pastikan tidak ada transaksi yang tersimpan
        $this->assertDatabaseMissing('sales', [
            'customer_id' => $this->customer->id,
        ]);
    }

    public function test_transaction_atomicity_rolls_back_entire_sale_if_one_item_fails(): void
    {
        // Kamera stok cukup (5), tapi lensa stok tidak cukup (stok 2 tapi diminta 10)
        $response = $this->actingAs($this->staff)->post('/sales', [
            'customer_id' => $this->customer->id,
            'type' => 'rental',
            'rental_start_date' => '2026-10-26',
            'rental_end_date' => '2026-10-26',
            'payment_method' => 'cash',
            'sold_at' => '2026-10-26 10:00:00',
            'items' => [
                ['product_id' => $this->camera->id, 'quantity' => 1], // valid
                ['product_id' => $this->lens->id, 'quantity' => 10],   // invalid oversell
            ],
        ]);

        $response->assertSessionHasErrors('items');

        // Kamera TIDAK boleh berkurang karena seluruh transaksi harus rollback secara atomik!
        $this->camera->refresh();
        $this->assertEquals(5, $this->camera->stock);

        $this->lens->refresh();
        $this->assertEquals(2, $this->lens->stock);

        // Tidak ada sale header yang tersimpan di DB
        $this->assertEquals(0, Sale::count());
    }

    public function test_admin_can_cancel_sale_and_restore_stock(): void
    {
        // Jalankan penjualan 1 kamera
        $this->actingAs($this->staff)->post('/sales', [
            'customer_id' => $this->customer->id,
            'type' => 'rental',
            'rental_start_date' => '2026-10-26',
            'rental_end_date' => '2026-10-26',
            'payment_method' => 'cash',
            'sold_at' => '2026-10-26 10:00:00',
            'items' => [
                ['product_id' => $this->camera->id, 'quantity' => 1],
            ],
        ]);

        $this->camera->refresh();
        $this->assertEquals(4, $this->camera->stock);

        $sale = Sale::latest()->first();

        // Admin membatalkan transaksi
        $response = $this->actingAs($this->admin)->post("/sales/{$sale->id}/cancel");
        $response->assertSessionHasNoErrors();

        // Stok kamera harus dipulihkan kembali ke 5
        $this->camera->refresh();
        $this->assertEquals(5, $this->camera->stock);

        $sale->refresh();
        $this->assertEquals('cancelled', $sale->status);
    }
}
