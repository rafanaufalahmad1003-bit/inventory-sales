<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        // Search SKU / Nama
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Filter Kategori
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Filter Status Stok
        if ($request->filled('stock_status')) {
            $status = $request->input('stock_status');
            if ($status === 'low') {
                $query->lowStock();
            } elseif ($status === 'out') {
                $query->where('stock', '<=', 0);
            } elseif ($status === 'available') {
                $query->where('stock', '>', 0);
            }
        }

        $products = $query->orderBy('name', 'asc')->paginate(10)->withQueryString();
        $categories = Category::orderBy('name', 'asc')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name', 'asc')->get();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'exists:categories,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'sku.required' => 'SKU produk wajib diisi.',
            'sku.unique' => 'SKU produk sudah terdaftar dalam sistem.',
            'name.required' => 'Nama produk wajib diisi.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'purchase_price.required' => 'Harga beli wajib diisi.',
            'purchase_price.min' => 'Harga beli tidak boleh negatif.',
            'selling_price.required' => 'Harga sewa/jual wajib diisi.',
            'selling_price.min' => 'Harga sewa/jual tidak boleh negatif.',
            'stock.required' => 'Stok awal wajib diisi.',
            'stock.min' => 'Stok tidak boleh bernilai negatif.',
            'minimum_stock.required' => 'Batas minimum stok wajib diisi.',
            'minimum_stock.min' => 'Minimum stok tidak boleh bernilai negatif.',
        ]);

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Produk kamera / perlengkapan berhasil ditambahkan.');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'purchaseItems.purchase.supplier', 'saleItems.sale.customer']);
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name', 'asc')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku,' . $product->id],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'exists:categories,id'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'sku.required' => 'SKU produk wajib diisi.',
            'sku.unique' => 'SKU produk sudah terdaftar dalam sistem.',
            'name.required' => 'Nama produk wajib diisi.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'purchase_price.required' => 'Harga beli wajib diisi.',
            'purchase_price.min' => 'Harga beli tidak boleh negatif.',
            'selling_price.required' => 'Harga sewa/jual wajib diisi.',
            'selling_price.min' => 'Harga sewa/jual tidak boleh negatif.',
            'stock.required' => 'Stok wajib diisi.',
            'stock.min' => 'Stok tidak boleh bernilai negatif.',
            'minimum_stock.required' => 'Batas minimum stok wajib diisi.',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Data produk kamera berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        // Business rule: Produk yang sudah ada riwayat transaksi dinonaktifkan via soft delete
        $hasTransactions = $product->purchaseItems()->exists() || $product->saleItems()->exists();

        if ($hasTransactions) {
            $product->delete(); // Soft delete
            return redirect()->route('products.index')->with('success', 'Produk dinonaktifkan (soft delete) karena memiliki riwayat transaksi.');
        }

        $product->forceDelete();
        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus secara permanen.');
    }
}
