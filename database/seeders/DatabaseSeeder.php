<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Pengguna (Users)
        $owner = User::create([
            'name' => 'Budi Hartono (Owner)',
            'email' => 'owner@lensrent.com',
            'password' => Hash::make('password'),
            'role' => 'owner',
            'phone' => '08111222333',
        ]);

        $admin = User::create([
            'name' => 'Siti Rahma (Admin)',
            'email' => 'admin@lensrent.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '08222333444',
        ]);

        $staff = User::create([
            'name' => 'Rian Pratama (Staff Rental)',
            'email' => 'staff@lensrent.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'phone' => '08333444555',
        ]);

        $userPelanggan = User::create([
            'name' => 'Dimas Anggara (Customer)',
            'email' => 'pelanggan@lensrent.com',
            'password' => Hash::make('password'),
            'role' => 'pelanggan',
            'phone' => '081234567890',
        ]);

        // 2. Data Master Pelanggan (Customers)
        $customer1 = Customer::create([
            'user_id' => $userPelanggan->id,
            'nik' => '3271012304950001',
            'name' => 'Dimas Anggara',
            'phone' => '081234567890',
            'email' => 'pelanggan@lensrent.com',
            'address' => 'Jl. Kemang Raya No. 12, Mampang, Jakarta Selatan',
        ]);

        $customer2 = Customer::create([
            'user_id' => null,
            'nik' => '3271021408980002',
            'name' => 'Amanda Putri (Studio Wedding Bandung)',
            'phone' => '081398765432',
            'email' => 'amanda.wedding@gmail.com',
            'address' => 'Jl. Dago Asri No. 45, Coblong, Kota Bandung',
        ]);

        $customer3 = Customer::create([
            'user_id' => null,
            'nik' => '3174092512960003',
            'name' => 'Rizky Fauzan (Kreatif Sinema PH)',
            'phone' => '085711223344',
            'email' => 'rizky@kreatifsinema.id',
            'address' => 'Ruko Grand Puri Kembangan No. 8B, Jakarta Barat',
        ]);

        $customer4 = Customer::create([
            'user_id' => null,
            'nik' => '3204126701990004',
            'name' => 'Cindy Kartika (Vlogger Lifestyle)',
            'phone' => '081955443322',
            'email' => 'cindy.kartika@vlog.id',
            'address' => 'Cluster BSD Green Cove Blok B3, Tangerang',
        ]);

        // 3. Data Master Kategori (Categories)
        $catMirrorless = Category::create([
            'name' => 'Kamera Mirrorless',
            'slug' => 'kamera-mirrorless',
            'description' => 'Kamera mirrorless full-frame dan APS-C profesional (Sony, Canon, Fujifilm).',
        ]);

        $catCinema = Category::create([
            'name' => 'Kamera Cinema & Video',
            'slug' => 'kamera-cinema-video',
            'description' => 'Kamera lini sinematik untuk produksi video komersial dan film.',
        ]);

        $catLensa = Category::create([
            'name' => 'Lensa Kamera',
            'slug' => 'lensa-kamera',
            'description' => 'Lensa prime, zoom, dan cine f/1.4, f/2.8 kualitas prima.',
        ]);

        $catLighting = Category::create([
            'name' => 'Lighting & Flash Studio',
            'slug' => 'lighting-flash-studio',
            'description' => 'Lampu continuous LED video light, flash outdoor, dan softbox.',
        ]);

        $catGimbal = Category::create([
            'name' => 'Stabilizer & Tripod',
            'slug' => 'stabilizer-tripod',
            'description' => 'Gimbal elektronik DJI Ronin dan tripod heavy duty video head.',
        ]);

        $catAudio = Category::create([
            'name' => 'Audio & Microphone',
            'slug' => 'audio-microphone',
            'description' => 'Mic wireless clip-on, shotgun mic, dan field audio recorder.',
        ]);

        $catAksesoris = Category::create([
            'name' => 'Aksesoris & Baterai',
            'slug' => 'aksesoris-baterai',
            'description' => 'Memory card high-speed V60/V90, baterai cadangan, dan filter ND.',
        ]);

        // 4. Data Master Pemasok (Suppliers)
        $supSony = Supplier::create([
            'name' => 'PT Sony Authorized Distributor Indonesia',
            'phone' => '021-29938888',
            'email' => 'sales@distributor-sony.co.id',
            'address' => 'Wisma GKBI Lantai 23, Jl. Jend. Sudirman Kav. 28, Jakarta Pusat',
        ]);

        $supDatascrip = Supplier::create([
            'name' => 'PT Datascrip (Distributor Resmi Canon)',
            'phone' => '021-6544510',
            'email' => 'info@datascrip.co.id',
            'address' => 'Gedung Datascrip, Kawasan Niaga Selata Blok B-15, Kemayoran, Jakarta',
        ]);

        $supProgear = Supplier::create([
            'name' => 'Pro Gear Audio & Visual Supply',
            'phone' => '021-57901234',
            'email' => 'order@progear-supply.com',
            'address' => 'Komp. Ruko Mangga Dua Mall No. 18, Jakarta Pusat',
        ]);

        // 5. Data Master Produk Kamera & Gear (Products)
        $p1 = Product::create([
            'category_id' => $catMirrorless->id,
            'sku' => 'CAM-SNY-A7M4',
            'name' => 'Sony Alpha 7 IV (A7 IV) Body Only',
            'description' => 'Kamera hybrid 33MP Full-Frame, 4K 60p 10-bit 4:2:2, Real-time Eye AF.',
            'purchase_price' => 31000000,
            'selling_price' => 350000, // Harga sewa per hari
            'stock' => 5,
            'minimum_stock' => 2,
            'unit' => 'unit',
        ]);

        $p2 = Product::create([
            'category_id' => $catCinema->id,
            'sku' => 'CAM-SNY-FX3',
            'name' => 'Sony Cinema Line FX3 Full-Frame',
            'description' => 'Kamera cinema 12.1MP Full-Frame, S-Cinetone, 4K 120p, Handle XLR Audio.',
            'purchase_price' => 54000000,
            'selling_price' => 750000,
            'stock' => 3,
            'minimum_stock' => 1,
            'unit' => 'unit',
        ]);

        $p3 = Product::create([
            'category_id' => $catMirrorless->id,
            'sku' => 'CAM-CAN-R6M2',
            'name' => 'Canon EOS R6 Mark II Body',
            'description' => 'Kamera mirrorless 24.2MP, 40 fps burst, 6K oversampled 4K 60p.',
            'purchase_price' => 35000000,
            'selling_price' => 400000,
            'stock' => 4,
            'minimum_stock' => 2,
            'unit' => 'unit',
        ]);

        $p4 = Product::create([
            'category_id' => $catLensa->id,
            'sku' => 'LENS-SNY-2470',
            'name' => 'Sony FE 24-70mm f/2.8 GM II',
            'description' => 'Lensa zoom standar profesional G Master generasi ke-2, ringan dan tajam.',
            'purchase_price' => 29000000,
            'selling_price' => 250000,
            'stock' => 6,
            'minimum_stock' => 2,
            'unit' => 'unit',
        ]);

        $p5 = Product::create([
            'category_id' => $catLensa->id,
            'sku' => 'LENS-SNY-70200',
            'name' => 'Sony FE 70-200mm f/2.8 GM OSS II',
            'description' => 'Lensa telephoto profesional G Master generasi ke-2 dengan Optical SteadyShot.',
            'purchase_price' => 37000000,
            'selling_price' => 300000,
            'stock' => 4,
            'minimum_stock' => 2,
            'unit' => 'unit',
        ]);

        $p6 = Product::create([
            'category_id' => $catGimbal->id,
            'sku' => 'GIMB-DJI-RS3',
            'name' => 'DJI RS 3 Pro Gimbal Combo',
            'description' => 'Gimbal stabilizer carbon fiber, payload 4.5kg, auto axis locks, LiDAR focus support.',
            'purchase_price' => 13500000,
            'selling_price' => 200000,
            'stock' => 4,
            'minimum_stock' => 2,
            'unit' => 'unit',
        ]);

        $p7 = Product::create([
            'category_id' => $catLighting->id,
            'sku' => 'LIGHT-GDX-SL60',
            'name' => 'Godox SL-60W LED Continuous Video Light',
            'description' => 'Lampu LED daylight 5600K 60W dengan Bowens mount dan remote.',
            'purchase_price' => 1650000,
            'selling_price' => 75000,
            'stock' => 8,
            'minimum_stock' => 3,
            'unit' => 'set',
        ]);

        $p8 = Product::create([
            'category_id' => $catAudio->id,
            'sku' => 'AUD-RODE-WGO2',
            'name' => 'Rode Wireless GO II Dual Channel Mic',
            'description' => 'Sistem mikrofon wireless dual transmitter dengan perekaman onboard.',
            'purchase_price' => 4100000,
            'selling_price' => 100000,
            'stock' => 6,
            'minimum_stock' => 2,
            'unit' => 'set',
        ]);

        $p9 = Product::create([
            'category_id' => $catAksesoris->id,
            'sku' => 'ACC-SD-128',
            'name' => 'SanDisk Extreme PRO SDXC 128GB V30 UHS-I',
            'description' => 'Kartu memori SDXC kecepatan baca hingga 200MB/s untuk rekaman video 4K UHD.',
            'purchase_price' => 420000,
            'selling_price' => 35000,
            'stock' => 15,
            'minimum_stock' => 5,
            'unit' => 'unit',
        ]);

        // Produk Stok Rendah (Contoh Low Stock Alert di Dashboard)
        $p10 = Product::create([
            'category_id' => $catAksesoris->id,
            'sku' => 'ACC-BAT-FZ100',
            'name' => 'Baterai Sony NP-FZ100 Original',
            'description' => 'Baterai cadangan kamera Sony A7 IV / A7S III / FX3.',
            'purchase_price' => 1050000,
            'selling_price' => 40000,
            'stock' => 2, // Di bawah minimum_stock!
            'minimum_stock' => 5,
            'unit' => 'unit',
        ]);

        // 6. Transaksi Pembelian Awal (Purchase Stock-In Records)
        $po1 = Purchase::create([
            'supplier_id' => $supSony->id,
            'user_id' => $staff->id,
            'purchase_number' => 'PO-' . date('Ymd') . '-0001',
            'total' => 184000000,
            'status' => 'completed',
            'notes' => 'Pengadaan armada kamera Sony A7 IV dan lensa G Master tahap 1',
            'purchased_at' => Carbon::now()->subDays(5),
        ]);

        $po1->items()->createMany([
            ['product_id' => $p1->id, 'quantity' => 4, 'unit_cost' => 31000000, 'subtotal' => 124000000],
            ['product_id' => $p4->id, 'quantity' => 2, 'unit_cost' => 29000000, 'subtotal' => 58000000],
            ['product_id' => $p10->id, 'quantity' => 2, 'unit_cost' => 1000000, 'subtotal' => 2000000],
        ]);

        // 7. Transaksi Rental & Penjualan Awal (Sales / Rental Records)
        $trx1 = Sale::create([
            'customer_id' => $customer1->id,
            'user_id' => $staff->id,
            'sale_number' => 'TRX-' . date('Ymd') . '-0001',
            'type' => 'rental',
            'rental_start_date' => Carbon::today(),
            'rental_end_date' => Carbon::today()->addDays(2), // 3 hari rental
            'total' => 2400000,
            'status' => 'completed',
            'payment_method' => 'transfer',
            'payment_status' => 'paid',
            'notes' => 'Sewa paket video shooting wedding weekend (3 hari)',
            'sold_at' => Carbon::now()->subHours(4),
        ]);

        $trx1->items()->createMany([
            ['product_id' => $p1->id, 'quantity' => 1, 'unit_price' => 350000, 'subtotal' => 1050000],
            ['product_id' => $p4->id, 'quantity' => 1, 'unit_price' => 250000, 'subtotal' => 750000],
            ['product_id' => $p6->id, 'quantity' => 1, 'unit_price' => 200000, 'subtotal' => 600000],
        ]);

        $trx2 = Sale::create([
            'customer_id' => $customer2->id,
            'user_id' => $staff->id,
            'sale_number' => 'TRX-' . date('Ymd') . '-0002',
            'type' => 'rental',
            'rental_start_date' => Carbon::today()->subDays(2),
            'rental_end_date' => Carbon::today()->addDays(1),
            'total' => 1200000,
            'status' => 'completed',
            'payment_method' => 'qris',
            'payment_status' => 'paid',
            'notes' => 'Sewa gear dokumentasi event musik',
            'sold_at' => Carbon::now()->subDays(2),
        ]);

        $trx2->items()->createMany([
            ['product_id' => $p3->id, 'quantity' => 1, 'unit_price' => 400000, 'subtotal' => 800000],
            ['product_id' => $p8->id, 'quantity' => 2, 'unit_price' => 100000, 'subtotal' => 400000],
        ]);
    }
}
