@extends('layouts.app')

@section('title', 'Detail Produk Kamera - ' . $product->name)

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm p-4 text-center">
            <div class="p-4 rounded bg-light d-inline-flex justify-content-center align-items-center mb-3 text-primary">
                <i class="bi bi-camera fs-1"></i>
            </div>
            <span class="badge bg-secondary mb-2 align-self-center">{{ $product->category->name ?? 'Gear Kamera' }}</span>
            <h4 class="fw-bold text-dark mb-1">{{ $product->name }}</h4>
            <div class="font-monospace text-muted small mb-3">SKU: {{ $product->sku }}</div>

            <div class="border-top pt-3 text-start">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary small">Tarif Sewa/Jual:</span>
                    <strong class="text-primary fs-5">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</strong>
                </div>
                @if(!auth()->user()->isPelanggan())
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary small">Harga Modal Beli:</span>
                    <span class="text-muted fw-semibold">Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary small">Ketersediaan Stok:</span>
                    @if($product->stock <= 0)
                        <span class="badge bg-danger">Habis (0 {{ $product->unit }})</span>
                    @elseif($product->isLowStock())
                        <span class="badge bg-warning text-dark">{{ $product->stock }} {{ $product->unit }} (Menipis)</span>
                    @else
                        <span class="badge bg-success-subtle text-success">{{ $product->stock }} {{ $product->unit }} (Aman)</span>
                    @endif
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary small">Batas Minimum:</span>
                    <span class="text-dark">{{ $product->minimum_stock }} {{ $product->unit }}</span>
                </div>
            </div>

            @if(auth()->user()->isPelanggan())
                @if($product->stock > 0)
                <div class="d-grid gap-2 mt-4">
                    <a href="{{ route('booking.create', ['product_id' => $product->id]) }}" class="btn btn-primary btn-lg shadow-sm">
                        <i class="bi bi-cart-plus me-1"></i> Sewa Gear Ini Sekarang
                    </a>
                </div>
                @endif
            @else
            <div class="d-grid gap-2 mt-4">
                <a href="{{ route('products.edit', $product->id) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i> Ubah Data Produk
                </a>
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog
                </a>
            </div>
            @endif
        </div>
    </div>

    <div class="col-lg-8">
        <!-- Deskripsi Produk -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-info-circle text-primary me-2"></i>Spesifikasi & Keterangan</h6>
            </div>
            <div class="card-body">
                <p class="text-secondary mb-0">
                    {{ $product->description ?: 'Tidak ada keterangan tambahan untuk produk ini.' }}
                </p>
            </div>
        </div>

        @if(!auth()->user()->isPelanggan())
        <!-- Riwayat Rental / Penjualan Terkait -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-receipt text-success me-2"></i>Riwayat Transaksi Rental / Penjualan Produk Ini</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No. Transaksi</th>
                            <th>Pelanggan</th>
                            <th class="text-center">Kuantitas</th>
                            <th>Tarif/Hari</th>
                            <th>Subtotal</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($product->saleItems->take(5) as $saleItem)
                        <tr>
                            <td>
                                <a href="{{ route('sales.show', $saleItem->sale->id) }}" class="fw-bold font-monospace text-decoration-none">
                                    {{ $saleItem->sale->sale_number }}
                                </a>
                            </td>
                            <td>{{ $saleItem->sale->customer->name ?? 'Pelanggan Umum' }}</td>
                            <td class="text-center">{{ $saleItem->quantity }} {{ $product->unit }}</td>
                            <td>Rp {{ number_format($saleItem->unit_price, 0, ',', '.') }}</td>
                            <td class="fw-semibold">Rp {{ number_format($saleItem->subtotal, 0, ',', '.') }}</td>
                            <td class="text-muted small">{{ $saleItem->created_at->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">Belum ada riwayat rental/penjualan untuk produk ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Riwayat Pengadaan / Pembelian Terkait -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-box-arrow-in-down text-info me-2"></i>Riwayat Pengadaan / Pembelian Stok Produk Ini</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No. PO</th>
                            <th>Pemasok</th>
                            <th class="text-center">Qty Beli</th>
                            <th>Harga Beli Satuan</th>
                            <th>Subtotal</th>
                            <th>Tanggal Masuk</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($product->purchaseItems->take(5) as $purchaseItem)
                        <tr>
                            <td>
                                <a href="{{ route('purchases.show', $purchaseItem->purchase->id) }}" class="fw-bold font-monospace text-decoration-none">
                                    {{ $purchaseItem->purchase->purchase_number }}
                                </a>
                            </td>
                            <td>{{ $purchaseItem->purchase->supplier->name ?? '-' }}</td>
                            <td class="text-center">{{ $purchaseItem->quantity }} {{ $product->unit }}</td>
                            <td>Rp {{ number_format($purchaseItem->unit_cost, 0, ',', '.') }}</td>
                            <td class="fw-semibold">Rp {{ number_format($purchaseItem->subtotal, 0, ',', '.') }}</td>
                            <td class="text-muted small">{{ $purchaseItem->created_at->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">Belum ada riwayat pembelian untuk produk ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
