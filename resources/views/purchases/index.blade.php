@extends('layouts.app')

@section('title', 'Daftar Pembelian Stok (Stock-In)')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-box-arrow-in-down text-info me-2"></i>Pengadaan & Pembelian Stok (Stock-In)
        </h3>
        <p class="text-secondary small mb-0">Catatan transaksi pengadaan unit kamera, lensa, dan gear dari distributor resmi.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <a href="{{ route('purchases.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-plus-circle"></i> Input Pembelian Stok Baru
        </a>
    </div>
</div>

<!-- Filter Bar -->
<div class="card p-3 mb-4 bg-white border-0 shadow-sm">
    <form action="{{ route('purchases.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-3">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Cari No. PO atau Pemasok..." value="{{ request('search') }}">
            </div>
        </div>

        <div class="col-md-3">
            <select name="supplier_id" class="form-select">
                <option value="">-- Semua Pemasok --</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                        {{ $supplier->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <select name="status" class="form-select">
                <option value="">-- Status --</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
        </div>

        <div class="col-md-2">
            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" title="Tanggal Mulai">
        </div>

        <div class="col-md-2 d-flex gap-1">
            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" title="Tanggal Akhir">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-funnel"></i>
            </button>
            @if(request()->hasAny(['search', 'supplier_id', 'status', 'start_date', 'end_date']))
                <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary" title="Reset">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
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
                    <th>No. PO</th>
                    <th>Pemasok / Distributor</th>
                    <th>Staff Pencatat</th>
                    <th>Tanggal Masuk</th>
                    <th class="text-center">Jumlah Item</th>
                    <th class="text-end">Total Pembelian</th>
                    <th class="text-center">Status</th>
                    <th class="text-end" style="width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                <tr>
                    <td>
                        <a href="{{ route('purchases.show', $purchase->id) }}" class="fw-bold font-monospace text-decoration-none">
                            {{ $purchase->purchase_number }}
                        </a>
                    </td>
                    <td>
                        <div class="fw-semibold text-dark">{{ $purchase->supplier->name ?? '-' }}</div>
                        <div class="text-muted small">{{ $purchase->supplier->phone ?? '' }}</div>
                    </td>
                    <td class="text-secondary small">{{ $purchase->user->name ?? '-' }}</td>
                    <td class="text-secondary small">{{ $purchase->purchased_at->format('d/m/Y') }}</td>
                    <td class="text-center">
                        <span class="badge bg-light text-dark border">{{ $purchase->items->count() }} jenis gear</span>
                    </td>
                    <td class="text-end fw-bold text-dark">
                        Rp {{ number_format($purchase->total, 0, ',', '.') }}
                    </td>
                    <td class="text-center">
                        @if($purchase->status === 'completed')
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Selesai (Stok Masuk)</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Dibatalkan</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-sm btn-outline-primary py-1">
                            <i class="bi bi-eye me-1"></i> Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">Belum ada transaksi pembelian stok.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($purchases->hasPages())
    <div class="card-footer bg-white py-3 border-top d-flex justify-content-end">
        {{ $purchases->links() }}
    </div>
    @endif
</div>
@endsection
