@extends('layouts.app')

@section('title', 'Katalog & Inventaris Kamera')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-camera text-primary me-2"></i>Katalog & Inventaris Gear Kamera
        </h3>
        <p class="text-secondary small mb-0">Kelola master data kamera, lensa, lighting, aksesoris, dan pantau ketersediaan stok fisik.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        @if(!auth()->user()->isPelanggan())
            <a href="{{ route('products.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-plus-circle"></i> Tambah Gear / Kamera Baru
            </a>
        @endif
    </div>
</div>

<!-- Filter & Search Card -->
<div class="card p-3 mb-4 bg-white border-0 shadow-sm">
    <form action="{{ route('products.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Cari SKU atau nama kamera..." value="{{ request('search') }}">
            </div>
        </div>

        <div class="col-md-3">
            <select name="category_id" class="form-select">
                <option value="">-- Semua Kategori --</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <select name="stock_status" class="form-select">
                <option value="">-- Semua Status Stok --</option>
                <option value="available" {{ request('stock_status') == 'available' ? 'selected' : '' }}>Tersedia (In Stock)</option>
                <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Stok Menipis (Low Stock)</option>
                <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Habis (Out of Stock)</option>
            </select>
        </div>

        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-funnel"></i> Filter
            </button>
            @if(request()->hasAny(['search', 'category_id', 'stock_status']))
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            @endif
        </div>
    </form>
</div>

<!-- Product Table Card -->
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 140px;">SKU</th>
                    <th>Nama Kamera / Gear</th>
                    <th>Kategori</th>
                    @if(!auth()->user()->isPelanggan())
                        <th class="text-end">Harga Beli</th>
                    @endif
                    <th class="text-end">Tarif Sewa/Jual</th>
                    <th class="text-center">Stok</th>
                    <th class="text-center">Status</th>
                    <th class="text-end" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td class="font-monospace fw-bold text-secondary">
                        {{ $product->sku }}
                    </td>
                    <td>
                        <div class="fw-semibold text-dark">{{ $product->name }}</div>
                        <div class="text-muted small text-truncate" style="max-width: 280px;">{{ $product->description }}</div>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">{{ $product->category->name ?? '-' }}</span>
                    </td>
                    @if(!auth()->user()->isPelanggan())
                        <td class="text-end text-muted small">
                            Rp {{ number_format($product->purchase_price, 0, ',', '.') }}
                        </td>
                    @endif
                    <td class="text-end fw-bold text-primary">
                        Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                        <span class="text-muted small fw-normal d-block">/hari</span>
                    </td>
                    <td class="text-center">
                        @if($product->stock <= 0)
                            <span class="badge bg-danger">0 {{ $product->unit }}</span>
                        @elseif($product->isLowStock())
                            <span class="badge bg-warning text-dark">{{ $product->stock }} {{ $product->unit }}</span>
                        @else
                            <span class="badge bg-success-subtle text-success">{{ $product->stock }} {{ $product->unit }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($product->stock <= 0)
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Habis</span>
                        @elseif($product->isLowStock())
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Menipis</span>
                        @else
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Tersedia</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('products.show', $product->id) }}" class="btn btn-outline-secondary" title="Detail Gear">
                                <i class="bi bi-eye"></i>
                            </a>

                            @if(!auth()->user()->isPelanggan())
                                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-outline-primary" title="Ubah">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                @can('delete-master')
                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk {{ $product->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Hapus Produk">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                        Tidak ditemukan data produk kamera yang sesuai filter.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
    <div class="card-footer bg-white py-3 border-top d-flex justify-content-end">
        {{ $products->links() }}
    </div>
    @endif
</div>
@endsection
