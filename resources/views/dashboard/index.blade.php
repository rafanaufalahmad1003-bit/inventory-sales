@extends('layouts.app')

@section('title', 'Dashboard Utama')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-speedometer2 text-primary me-2"></i>Dashboard Manajemen
        </h3>
        <p class="text-secondary small mb-0">Ringkasan status stok, inventaris rental kamera, dan performa transaksi real-time.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end flex-wrap">
        <a href="{{ route('sales.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-cart-plus"></i> Transaksi Rental Baru
        </a>
        <a href="{{ route('purchases.create') }}" class="btn btn-outline-dark d-inline-flex align-items-center gap-1">
            <i class="bi bi-box-arrow-in-down"></i> Beli Stok / Restock
        </a>
    </div>
</div>

<!-- KPI Metric Cards -->
<div class="row g-3 mb-4">
    <!-- Total Produk -->
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 p-3 border-0 bg-white shadow-sm border-start border-primary border-4">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-secondary small fw-medium">Total Unit Produk</span>
                    <h3 class="fw-bold my-1 text-dark">{{ $totalProducts }}</h3>
                    <div class="small">
                        @if($lowStockCount > 0)
                            <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $lowStockCount }} stok menipis</span>
                        @else
                            <span class="badge bg-success-subtle text-success">Stok aman</span>
                        @endif
                    </div>
                </div>
                <div class="p-2 rounded bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-camera fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Pelanggan Terdaftar -->
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 p-3 border-0 bg-white shadow-sm border-start border-info border-4">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-secondary small fw-medium">Pelanggan Rental</span>
                    <h3 class="fw-bold my-1 text-dark">{{ $totalCustomers }}</h3>
                    <div class="text-muted small">
                        <i class="bi bi-people me-1"></i>Penyewa kamera terverifikasi
                    </div>
                </div>
                <div class="p-2 rounded bg-info bg-opacity-10 text-info">
                    <i class="bi bi-person-check fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Penjualan Hari Ini -->
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 p-3 border-0 bg-white shadow-sm border-start border-success border-4">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-secondary small fw-medium">Rental/Sales Hari Ini</span>
                    <h4 class="fw-bold my-1 text-dark">Rp {{ number_format($todaySalesAmount, 0, ',', '.') }}</h4>
                    <div class="text-success small fw-medium">
                        <i class="bi bi-check2-circle me-1"></i>{{ $todaySalesCount }} transaksi berhasil
                    </div>
                </div>
                <div class="p-2 rounded bg-success bg-opacity-10 text-success">
                    <i class="bi bi-cash-stack fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Penjualan Bulan Ini -->
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 p-3 border-0 bg-white shadow-sm border-start border-warning border-4">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-secondary small fw-medium">Omset Bulan Ini</span>
                    <h4 class="fw-bold my-1 text-dark">Rp {{ number_format($monthSalesAmount, 0, ',', '.') }}</h4>
                    <div class="text-muted small">
                        <i class="bi bi-receipt me-1"></i>{{ $monthSalesCount }} transaksi bulan ini
                    </div>
                </div>
                <div class="p-2 rounded bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-graph-up-arrow fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Low Stock Alert Section -->
@if($lowStockProducts->count() > 0)
<div class="card mb-4 border-warning border-1 shadow-sm">
    <div class="card-header bg-warning bg-opacity-10 border-warning border-bottom d-flex justify-content-between align-items-center py-2">
        <div class="d-flex align-items-center text-dark fw-bold">
            <i class="bi bi-exclamation-triangle-fill text-warning me-2 fs-5"></i>
            <span>Peringatan Stok Rendah (Segera Lakukan Pengadaan / Stock-In)</span>
        </div>
        <a href="{{ route('purchases.create') }}" class="btn btn-sm btn-dark">
            <i class="bi bi-plus-circle me-1"></i> Restock Sekarang
        </a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>SKU</th>
                    <th>Nama Kamera / Gear</th>
                    <th>Kategori</th>
                    <th class="text-center">Sisa Stok</th>
                    <th class="text-center">Batas Minimum</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lowStockProducts as $low)
                <tr>
                    <td class="font-monospace fw-semibold text-secondary">{{ $low->sku }}</td>
                    <td class="fw-medium">{{ $low->name }}</td>
                    <td><span class="badge bg-light text-dark border">{{ $low->category->name ?? '-' }}</span></td>
                    <td class="text-center">
                        <span class="badge bg-danger fs-6">{{ $low->stock }} {{ $low->unit }}</span>
                    </td>
                    <td class="text-center text-muted">{{ $low->minimum_stock }} {{ $low->unit }}</td>
                    <td>
                        <span class="text-danger small fw-semibold"><i class="bi bi-arrow-down-circle me-1"></i>Menipis</span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('purchases.create') }}" class="btn btn-sm btn-outline-primary py-1">
                            <i class="bi bi-cart-plus me-1"></i> Restock
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Tables Section: Recent Sales & Recent Purchases -->
<div class="row g-4">
    <!-- Riwayat Rental & Penjualan Terbaru -->
    <div class="col-lg-6">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-receipt-cutoff text-success me-2"></i>Transaksi Rental / Penjualan Terbaru
                </h6>
                <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-secondary">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No. Transaksi</th>
                            <th>Pelanggan</th>
                            <th>Tipe</th>
                            <th>Total</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSales as $sale)
                        <tr>
                            <td>
                                <a href="{{ route('sales.show', $sale->id) }}" class="fw-bold text-decoration-none font-monospace">
                                    {{ $sale->sale_number }}
                                </a>
                                <div class="text-muted small">{{ $sale->sold_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td>
                                <div class="fw-medium">{{ $sale->customer->name ?? 'Pelanggan Umum' }}</div>
                                <div class="text-muted small">{{ $sale->customer->phone ?? '-' }}</div>
                            </td>
                            <td>
                                @if($sale->type === 'rental')
                                    <span class="badge bg-primary">Rental</span>
                                @else
                                    <span class="badge bg-secondary">Penjualan</span>
                                @endif
                            </td>
                            <td class="fw-bold text-dark">
                                Rp {{ number_format($sale->total, 0, ',', '.') }}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-sm btn-outline-primary py-1" title="Lihat Faktur">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Belum ada transaksi rental atau penjualan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Riwayat Pembelian / Stock-In Terbaru -->
    <div class="col-lg-6">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-box-arrow-in-down text-info me-2"></i>Pembelian Stok (Stock-In) Terbaru
                </h6>
                <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-outline-secondary">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No. PO</th>
                            <th>Pemasok</th>
                            <th>Tanggal</th>
                            <th>Total Beli</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPurchases as $purchase)
                        <tr>
                            <td>
                                <a href="{{ route('purchases.show', $purchase->id) }}" class="fw-bold text-decoration-none font-monospace">
                                    {{ $purchase->purchase_number }}
                                </a>
                                <div class="text-muted small">Oleh: {{ $purchase->user->name ?? 'Staff' }}</div>
                            </td>
                            <td>
                                <div class="fw-medium">{{ $purchase->supplier->name ?? '-' }}</div>
                                <div class="text-muted small">{{ $purchase->items->count() }} jenis item</div>
                            </td>
                            <td class="text-muted small">
                                {{ $purchase->purchased_at->format('d/m/Y') }}
                            </td>
                            <td class="fw-bold text-dark">
                                Rp {{ number_format($purchase->total, 0, ',', '.') }}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-sm btn-outline-primary py-1" title="Lihat PO">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Belum ada transaksi pembelian stok.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
