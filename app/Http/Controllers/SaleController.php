<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'user', 'items.product']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('sale_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('sold_at', [
                Carbon::parse($request->input('start_date'))->startOfDay(),
                Carbon::parse($request->input('end_date'))->endOfDay()
            ]);
        }

        $sales = $query->latest('sold_at')->paginate(10)->withQueryString();
        $customers = Customer::orderBy('name', 'asc')->get();

        return view('sales.index', compact('sales', 'customers'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name', 'asc')->get();
        $products = Product::where('stock', '>', 0)->orderBy('name', 'asc')->get();
        return view('sales.create', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'type' => ['required', 'in:rental,sale'],
            'rental_start_date' => ['nullable', 'required_if:type,rental', 'date'],
            'rental_end_date' => ['nullable', 'required_if:type,rental', 'date', 'after_or_equal:rental_start_date'],
            'payment_method' => ['required', 'string', 'in:cash,transfer,qris'],
            'sold_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ], [
            'customer_id.required' => 'Pelanggan rental kamera wajib dipilih.',
            'items.required' => 'Minimal harus menambahkan satu produk kamera / perlengkapan.',
            'items.*.product_id.required' => 'Produk wajib dipilih.',
            'items.*.quantity.min' => 'Jumlah kuantitas item minimal 1.',
            'rental_end_date.after_or_equal' => 'Tanggal selesai rental tidak boleh sebelum tanggal mulai rental.',
        ]);

        $sale = DB::transaction(function () use ($request) {
            $saleNumber = $this->generateSaleNumber();

            $sale = Sale::create([
                'customer_id' => $request->customer_id,
                'user_id' => auth()->id(),
                'sale_number' => $saleNumber,
                'type' => $request->type,
                'rental_start_date' => $request->type === 'rental' ? Carbon::parse($request->rental_start_date) : null,
                'rental_end_date' => $request->type === 'rental' ? Carbon::parse($request->rental_end_date) : null,
                'total' => 0,
                'status' => 'completed',
                'payment_method' => $request->payment_method,
                'payment_status' => 'paid',
                'notes' => $request->notes,
                'sold_at' => Carbon::parse($request->sold_at),
            ]);

            $total = 0;

            // Hitung durasi hari jika rental
            $rentalDays = 1;
            if ($request->type === 'rental' && $request->rental_start_date && $request->rental_end_date) {
                $start = Carbon::parse($request->rental_start_date);
                $end = Carbon::parse($request->rental_end_date);
                $rentalDays = max(1, $start->diffInDays($end) + 1);
            }

            foreach ($request->items as $itemData) {
                // Lock row produk di database untuk mencegah overselling pada transaksi konkuren
                $product = Product::lockForUpdate()->findOrFail($itemData['product_id']);
                $qty = (int) $itemData['quantity'];

                // Validasi stok
                if ($qty < 1 || $product->stock < $qty) {
                    throw ValidationException::withMessages([
                        'items' => "Stok untuk produk '{$product->name}' tidak mencukupi (Tersedia: {$product->stock}, Diminta: {$qty}).",
                    ]);
                }

                // Harga diambil langsung dari database server, bukan dipercaya dari client input!
                $unitPrice = (float) $product->selling_price;

                // Subtotal untuk rental dikalikan hari sewa
                $subtotal = $request->type === 'rental' 
                    ? ($unitPrice * $qty * $rentalDays)
                    : ($unitPrice * $qty);

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                // Stok berkurang hanya setelah transaksi berhasil
                $product->decrement('stock', $qty);

                $total += $subtotal;
            }

            // Total disimpan di server
            $sale->update(['total' => $total]);

            return $sale;
        });

        return redirect()->route('sales.show', $sale->id)
            ->with('success', 'Transaksi rental / penjualan kamera berhasil disimpan dan stok otomatis dikurangi.');
    }

    public function show(Sale $sale)
    {
        // Jika login sebagai Pelanggan, pastikan hanya dapat melihat notanya sendiri
        if (auth()->user()->isPelanggan()) {
            $customer = Customer::where('user_id', auth()->id())->first();
            if (!$customer || $sale->customer_id !== $customer->id) {
                abort(403, 'Akses Ditolak: Anda hanya dapat melihat nota sewa milik akun Anda sendiri.');
            }
        }

        $sale->load(['customer', 'user', 'items.product.category']);
        return view('sales.show', compact('sale'));
    }

    /**
     * Form pemesanan mandiri khusus Pelanggan (Self-Service Online Booking)
     */
    public function bookingCreate(Request $request)
    {
        $user = auth()->user();
        
        // Ambil atau buat profil Customer terkait akun ini jika belum ada
        $customer = Customer::firstOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '081234567890',
                'address' => 'Penyewa Terdaftar Online',
            ]
        );

        $selectedProductId = $request->input('product_id');
        $products = Product::where('stock', '>', 0)->orderBy('name', 'asc')->get();

        return view('sales.booking', compact('customer', 'products', 'selectedProductId'));
    }

    /**
     * Menyimpan pemesanan rental mandiri oleh Pelanggan
     */
    public function bookingStore(Request $request)
    {
        $user = auth()->user();
        $customer = Customer::where('user_id', $user->id)->first();

        if (!$customer) {
            $customer = Customer::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '081234567890',
                'address' => 'Penyewa Terdaftar Online',
            ]);
        }

        $request->validate([
            'rental_start_date' => ['required', 'date'],
            'rental_end_date' => ['required', 'date', 'after_or_equal:rental_start_date'],
            'payment_method' => ['required', 'string', 'in:cash,transfer,qris'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ], [
            'items.required' => 'Minimal harus memilih satu kamera / gear yang ingin disewa.',
            'rental_end_date.after_or_equal' => 'Tanggal selesai sewa tidak boleh sebelum tanggal mulai.',
        ]);

        $sale = DB::transaction(function () use ($request, $customer, $user) {
            $saleNumber = $this->generateSaleNumber();

            // Petugas pelayan dicatat akun staff/admin pertama sistem atau akun pembuat
            $staffUser = \App\Models\User::whereIn('role', ['admin', 'staff', 'owner'])->first();
            $handledByUserId = $staffUser ? $staffUser->id : $user->id;

            $sale = Sale::create([
                'customer_id' => $customer->id,
                'user_id' => $handledByUserId,
                'sale_number' => $saleNumber,
                'type' => 'rental',
                'rental_start_date' => Carbon::parse($request->rental_start_date),
                'rental_end_date' => Carbon::parse($request->rental_end_date),
                'total' => 0,
                'status' => 'completed',
                'payment_method' => $request->payment_method,
                'payment_status' => 'paid',
                'notes' => $request->notes,
                'sold_at' => Carbon::now(),
            ]);

            $start = Carbon::parse($request->rental_start_date);
            $end = Carbon::parse($request->rental_end_date);
            $rentalDays = max(1, $start->diffInDays($end) + 1);

            $total = 0;

            foreach ($request->items as $itemData) {
                $product = Product::lockForUpdate()->findOrFail($itemData['product_id']);
                $qty = (int) $itemData['quantity'];

                if ($qty < 1 || $product->stock < $qty) {
                    throw ValidationException::withMessages([
                        'items' => "Maaf, stok gear '{$product->name}' tidak mencukupi (Tersedia: {$product->stock}, Diminta: {$qty}).",
                    ]);
                }

                $unitPrice = (float) $product->selling_price;
                $subtotal = $unitPrice * $qty * $rentalDays;

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);

                // Kurangi stok unit di sistem
                $product->decrement('stock', $qty);

                $total += $subtotal;
            }

            $sale->update(['total' => $total]);

            return $sale;
        });

        return redirect()->route('sales.show', $sale->id)
            ->with('success', 'Pemesanan rental kamera Anda berhasil diproses! Silakan simpan / cetak nota sewa ini.');
    }

    /**
     * Membatalkan transaksi penjualan/rental (Hanya Admin & Owner).
     * Memulihkan kembali stok kamera secara konsisten via DB::transaction.
     */
    public function cancel(Sale $sale)
    {
        if ($sale->status === 'cancelled') {
            return back()->with('error', 'Transaksi ini sudah dalam status dibatalkan.');
        }

        DB::transaction(function () use ($sale) {
            foreach ($sale->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item->product_id);
                // Kembalikan stok yang dipinjam/dijual
                $product->increment('stock', $item->quantity);
            }

            $sale->update(['status' => 'cancelled']);
        });

        return redirect()->route('sales.show', $sale->id)
            ->with('success', 'Transaksi berhasil dibatalkan dan stok kamera/unit telah dikembalikan ke inventaris.');
    }

    private function generateSaleNumber(): string
    {
        $prefix = 'TRX-' . date('Ymd') . '-';
        $latest = Sale::where('sale_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->sale_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $newNumber;
    }
}
