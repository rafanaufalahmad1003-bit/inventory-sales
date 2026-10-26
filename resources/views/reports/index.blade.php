@extends('layouts.app')

@section('title', 'Laporan Bisnis & Keuangan Terintegrasi')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-bar-chart-line text-primary me-2"></i>Laporan Bisnis & Inventaris Kamera
        </h3>
        <p class="text-secondary small mb-0">Analisis performa pendapatan sewa, pengadaan unit baru, dan valuasi aset gear studio.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0 no-print">
        <button type="button" class="btn btn-outline-dark d-inline-flex align-items-center gap-1 shadow-sm" onclick="window.print()">
            <i class="bi bi-printer"></i> Cetak Laporan
        </button>
    </div>
</div>

<!-- Date Filter Form -->
<div class="card p-3 mb-4 bg-white border-0 shadow-sm no-print">
    <form action="{{ route('reports.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-secondary mb-1">Dari Tanggal:</label>
            <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-secondary mb-1">Sampai Tanggal:</label>
            <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-secondary mb-1">Status Transaksi:</label>
            <select name="status" class="form-select">
                <option value="">-- Semua Status --</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Hanya Selesai (Completed)</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan (Cancelled)</option>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end gap-1">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-filter me-1"></i> Terapkan Filter
            </button>
            @if(request()->hasAny(['start_date', 'end_date', 'status']))
                <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
            @endif
        </div>
    </form>
</div>

<!-- Summary Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 p-3 border-0 bg-white shadow-sm border-start border-success border-4">
            <span class="text-secondary small fw-medium">Pendapatan Rental & Sales</span>
            <h4 class="fw-bold my-1 text-dark">Rp {{ number_format($totalSalesAmount, 0, ',', '.') }}</h4>
            <div class="text-success small fw-medium">
                <i class="bi bi-check-circle me-1"></i>{{ $totalSalesCount }} transaksi berhasil
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 p-3 border-0 bg-white shadow-sm border-start border-info border-4">
            <span class="text-secondary small fw-medium">Biaya Pengadaan Stok (PO)</span>
            <h4 class="fw-bold my-1 text-dark">Rp {{ number_format($totalPurchasesAmount, 0, ',', '.') }}</h4>
            <div class="text-muted small">
                <i class="bi bi-truck me-1"></i>{{ $totalPurchasesCount }} pengadaan stok
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 p-3 border-0 bg-white shadow-sm border-start border-primary border-4">
            <span class="text-secondary small fw-medium">Valuasi Aset Inventaris Kamera</span>
            <h4 class="fw-bold my-1 text-dark">Rp {{ number_format($totalInventoryValue, 0, ',', '.') }}</h4>
            <div class="text-muted small">
                <i class="bi bi-camera me-1"></i>Total nilai fisik gear saat ini
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 p-3 border-0 bg-white shadow-sm border-start border-warning border-4">
            <span class="text-secondary small fw-medium">Net Arus Kas Periode Ini</span>
            @php $netCash = $totalSalesAmount - $totalPurchasesAmount; @endphp
            <h4 class="fw-bold my-1 {{ $netCash >= 0 ? 'text-success' : 'text-danger' }}">
                Rp {{ number_format($netCash, 0, ',', '.') }}
            </h4>
            <div class="small text-muted">
                {{ $netCash >= 0 ? 'Surplus operasional' : 'Investasi aset aktif' }}
            </div>
        </div>
    </div>
</div>

<!-- Laporan Transaksi Rental & Penjualan -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="bi bi-receipt-cutoff text-success me-2"></i>Rincian Transaksi Rental & Penjualan Kamera ({{ date('d/m/Y', strtotime($startDate)) }} s/d {{ date('d/m/Y', strtotime($endDate)) }})
        </h6>
        <span class="badge bg-success-subtle text-success">{{ $sales->count() }} Data</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>No. Transaksi</th>
                    <th>Pelanggan</th>
                    <th>Tipe</th>
                    <th>Item Gear Disewa</th>
                    <th>Waktu</th>
                    <th class="text-end">Total Biaya</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                <tr>
                    <td class="font-monospace fw-bold">{{ $sale->sale_number }}</td>
                    <td>
                        <div class="fw-semibold">{{ $sale->customer->name ?? 'Pelanggan Umum' }}</div>
                        <div class="text-muted small">{{ $sale->customer->phone ?? '-' }}</div>
                    </td>
                    <td>
                        @if($sale->type === 'rental')
                            <span class="badge bg-primary">Rental</span>
                        @else
                            <span class="badge bg-secondary">Penjualan</span>
                        @endif
                    </td>
                    <td>
                        @foreach($sale->items as $item)
                            <div class="small">&bull; {{ $item->product->name }} ({{ $item->quantity }}x)</div>
                        @endforeach
                    </td>
                    <td class="small text-muted">{{ $sale->sold_at->format('d/m/Y H:i') }}</td>
                    <td class="text-end fw-bold">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
                    <td class="text-center">
                        @if($sale->status === 'completed')
                            <span class="badge bg-success-subtle text-success">Selesai</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger">Dibatalkan</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">Tidak ada transaksi rental/penjualan pada periode ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Laporan Transaksi Pengadaan / Pembelian -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="bi bi-box-arrow-in-down text-info me-2"></i>Rincian Pengadaan Stok Masuk (Stock-In)
        </h6>
        <span class="badge bg-info-subtle text-info">{{ $purchases->count() }} Data</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>No. PO</th>
                    <th>Pemasok / Distributor</th>
                    <th>Item Dipesan</th>
                    <th>Tanggal Beli</th>
                    <th class="text-end">Total Biaya</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $p)
                <tr>
                    <td class="font-monospace fw-bold">{{ $p->purchase_number }}</td>
                    <td class="fw-semibold">{{ $p->supplier->name ?? '-' }}</td>
                    <td>
                        @foreach($p->items as $item)
                            <div class="small">&bull; {{ $item->product->name }} ({{ $item->quantity }}x)</div>
                        @endforeach
                    </td>
                    <td class="small text-muted">{{ $p->purchased_at->format('d/m/Y') }}</td>
                    <td class="text-end fw-bold">Rp {{ number_format($p->total, 0, ',', '.') }}</td>
                    <td class="text-center">
                        @if($p->status === 'completed')
                            <span class="badge bg-success-subtle text-success">Selesai</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger">Dibatalkan</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">Tidak ada transaksi pembelian stok pada periode ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
