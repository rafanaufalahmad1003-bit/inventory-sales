@extends('layouts.app')

@section('title', 'Daftar Transaksi Rental & Penjualan')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-receipt-cutoff text-success me-2"></i>Transaksi Rental & Penjualan Kamera
        </h3>
        <p class="text-secondary small mb-0">Kelola transaksi penyewaan kamera, durasi sewa, status pengembalian, dan faktur pembayaran.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <a href="{{ route('sales.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-cart-plus"></i> Transaksi Rental / Penjualan Baru
        </a>
    </div>
</div>

<!-- Filter Bar -->
<div class="card p-3 mb-4 bg-white border-0 shadow-sm">
    <form action="{{ route('sales.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-3">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Cari No. Transaksi / Pelanggan..." value="{{ request('search') }}">
            </div>
        </div>

        <div class="col-md-2">
            <select name="type" class="form-select">
                <option value="">-- Semua Tipe --</option>
                <option value="rental" {{ request('type') == 'rental' ? 'selected' : '' }}>Rental Kamera</option>
                <option value="sale" {{ request('type') == 'sale' ? 'selected' : '' }}>Penjualan Gear</option>
            </select>
        </div>

        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">-- Semua Status --</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
        </div>

        <div class="col-md-2">
            <select name="customer_id" class="form-select">
                <option value="">-- Pelanggan --</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3 d-flex gap-1">
            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" title="Mulai">
            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" title="Sampai">
            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i></button>
            @if(request()->hasAny(['search', 'type', 'status', 'customer_id', 'start_date', 'end_date']))
                <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
            @endif
        </div>
    </form>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>No. Transaksi</th>
                    <th>Pelanggan</th>
                    <th>Tipe</th>
                    <th>Periode Sewa</th>
                    <th>Metode Bayar</th>
                    <th>Kasir/Staff</th>
                    <th class="text-end">Total Biaya</th>
                    <th class="text-center">Status</th>
                    <th class="text-end" style="width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                <tr>
                    <td>
                        <a href="{{ route('sales.show', $sale->id) }}" class="fw-bold font-monospace text-decoration-none">
                            {{ $sale->sale_number }}
                        </a>
                        <div class="text-muted small">{{ $sale->sold_at->format('d/m/Y H:i') }}</div>
                    </td>
                    <td>
                        <div class="fw-semibold text-dark">{{ $sale->customer->name ?? 'Pelanggan Umum' }}</div>
                        <div class="text-muted small">{{ $sale->customer->phone ?? '-' }}</div>
                    </td>
                    <td>
                        @if($sale->type === 'rental')
                            <span class="badge bg-primary">Rental</span>
                        @else
                            <span class="badge bg-secondary">Penjualan</span>
                        @endif
                    </td>
                    <td class="small">
                        @if($sale->type === 'rental' && $sale->rental_start_date)
                            <div class="fw-medium text-dark">{{ $sale->rental_start_date->format('d/m/Y') }}</div>
                            <div class="text-muted">s/d {{ $sale->rental_end_date ? $sale->rental_end_date->format('d/m/Y') : '' }}</div>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border text-uppercase">{{ $sale->payment_method }}</span>
                    </td>
                    <td class="text-secondary small">{{ $sale->user->name ?? '-' }}</td>
                    <td class="text-end fw-bold text-dark">
                        Rp {{ number_format($sale->total, 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        @if($sale->status === 'completed')
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Selesai</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Dibatalkan</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-sm btn-outline-primary py-1">
                            <i class="bi bi-eye me-1"></i> Nota
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">Belum ada transaksi rental atau penjualan kamera.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($sales->hasPages())
    <div class="card-footer bg-white py-3 border-top d-flex justify-content-end">
        {{ $sales->links() }}
    </div>
    @endif
</div>
@endsection
