<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'user', 'items.product']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('purchase_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->input('supplier_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('purchased_at', [
                Carbon::parse($request->input('start_date'))->startOfDay(),
                Carbon::parse($request->input('end_date'))->endOfDay()
            ]);
        }

        $purchases = $query->latest('purchased_at')->paginate(10)->withQueryString();
        $suppliers = Supplier::orderBy('name', 'asc')->get();

        return view('purchases.index', compact('purchases', 'suppliers'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name', 'asc')->get();
        $products = Product::orderBy('name', 'asc')->get();
        return view('purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchased_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ], [
            'supplier_id.required' => 'Pemasok / supplier wajib dipilih.',
            'items.required' => 'Minimal harus menambahkan satu produk untuk pembelian stok.',
            'items.*.product_id.required' => 'Produk wajib dipilih.',
            'items.*.quantity.min' => 'Jumlah kuantitas produk minimal 1.',
            'items.*.unit_cost.min' => 'Harga beli satuan tidak boleh bernilai negatif.',
        ]);

        $purchase = DB::transaction(function () use ($request) {
            $purchaseNumber = $this->generatePurchaseNumber();

            $purchase = Purchase::create([
                'supplier_id' => $request->supplier_id,
                'user_id' => auth()->id(),
                'purchase_number' => $purchaseNumber,
                'total' => 0,
                'status' => 'completed',
                'notes' => $request->notes,
                'purchased_at' => Carbon::parse($request->purchased_at),
            ]);

            $total = 0;

            foreach ($request->items as $itemData) {
                $qty = (int) $itemData['quantity'];
                $unitCost = (float) $itemData['unit_cost'];
                $subtotal = $qty * $unitCost;

                $product = Product::lockForUpdate()->findOrFail($itemData['product_id']);

                $purchase->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                ]);

                // Stok produk bertambah setelah transaksi berhasil
                $product->increment('stock', $qty);

                // Update juga harga beli terakhir di produk jika ada perubahan
                $product->update(['purchase_price' => $unitCost]);

                $total += $subtotal;
            }

            // Total dihitung di server
            $purchase->update(['total' => $total]);

            return $purchase;
        });

        return redirect()->route('purchases.show', $purchase->id)
            ->with('success', 'Transaksi pembelian/stock-in berhasil disimpan. Stok produk telah bertambah!');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'user', 'items.product.category']);
        return view('purchases.show', compact('purchase'));
    }

    /**
     * Membatalkan transaksi pembelian (Hanya Admin / Owner).
     * Stok akan ditarik kembali secara konsisten via DB::transaction.
     */
    public function cancel(Purchase $purchase)
    {
        if ($purchase->status === 'cancelled') {
            return back()->with('error', 'Transaksi pembelian ini sudah dalam status dibatalkan.');
        }

        DB::transaction(function () use ($purchase) {
            foreach ($purchase->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item->product_id);

                if ($product->stock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'error' => "Gagal membatalkan: Stok produk {$product->name} tersisa {$product->stock}, tidak mencukupi untuk menarik kembali {$item->quantity} unit.",
                    ]);
                }

                $product->decrement('stock', $item->quantity);
            }

            $purchase->update(['status' => 'cancelled']);
        });

        return redirect()->route('purchases.show', $purchase->id)
            ->with('success', 'Transaksi pembelian berhasil dibatalkan dan stok produk telah dikurangi kembali.');
    }

    private function generatePurchaseNumber(): string
    {
        $prefix = 'PO-' . date('Ymd') . '-';
        $latest = Purchase::where('purchase_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->purchase_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $newNumber;
    }
}
