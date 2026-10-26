@extends('layouts.app')

@section('title', 'Detail Pelanggan - ' . $customer->name)

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm p-4 text-center">
            <div class="p-3 rounded-circle bg-primary bg-opacity-10 d-inline-flex justify-content-center align-items-center mb-3 text-primary mx-auto" style="width: 80px; height: 80px;">
                <i class="bi bi-person-fill fs-1"></i>
            </div>
            <h4 class="fw-bold text-dark mb-1">{{ $customer->name }}</h4>
            <div class="badge bg-success-subtle text-success border border-success-subtle mb-3">Pelanggan Rental Terverifikasi</div>

            <div class="border-top pt-3 text-start">
                <div class="mb-3">
                    <span class="text-secondary small d-block">NIK / No. KTP:</span>
                    <span class="font-monospace fw-semibold">{{ $customer->nik ?: 'Belum diisi' }}</span>
                </div>
                <div class="mb-3">
                    <span class="text-secondary small d-block">No. WhatsApp / HP:</span>
                    <span class="fw-medium text-dark"><i class="bi bi-whatsapp text-success me-1"></i>{{ $customer->phone ?: '-' }}</span>
                </div>
                <div class="mb-3">
                    <span class="text-secondary small d-block">Email:</span>
                    <span class="text-secondary">{{ $customer->email ?: '-' }}</span>
                </div>
                <div class="mb-3">
                    <span class="text-secondary small d-block">Alamat Domisili:</span>
                    <span class="text-secondary small">{{ $customer->address ?: '-' }}</span>
                </div>
            </div>

            <div class="d-grid gap-2 mt-3">
                <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i> Ubah Data Pelanggan
                </a>
                <a href="{{ route('sales.create') }}?customer_id={{ $customer->id }}" class="btn btn-outline-success">
                    <i class="bi bi-cart-plus me-1"></i> Buat Rental Baru untuk Pelanggan Ini
                </a>
                <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-receipt-cutoff text-primary me-2"></i>Riwayat Transaksi Rental & Belanja Pelanggan Ini
                </h6>
                <span class="badge bg-primary">{{ $customer->sales->count() }} Transaksi</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>No. Transaksi</th>
                            <th>Tipe</th>
                            <th>Gear / Kamera Disewa</th>
                            <th>Periode Sewa</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customer->sales as $sale)
                        <tr>
                            <td>
                                <a href="{{ route('sales.show', $sale->id) }}" class="fw-bold font-monospace text-decoration-none">
                                    {{ $sale->sale_number }}
                                </a>
                                <div class="text-muted small">{{ $sale->sold_at->format('d/m/Y H:i') }}</div>
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
                                    <div class="small fw-medium">&bull; {{ $item->product->name }} ({{ $item->quantity }}x)</div>
                                @endforeach
                            </td>
                            <td>
                                @if($sale->type === 'rental' && $sale->rental_start_date)
                                    <div class="small text-dark">
                                        {{ $sale->rental_start_date->format('d/m/Y') }} - {{ $sale->rental_end_date ? $sale->rental_end_date->format('d/m/Y') : '' }}
                                    </div>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="fw-bold text-dark">
                                Rp {{ number_format($sale->total, 0, ',', '.') }}
                            </td>
                            <td>
                                @if($sale->status === 'completed')
                                    <span class="badge bg-success-subtle text-success">Selesai</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">Dibatalkan</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-sm btn-outline-primary py-1">
                                    <i class="bi bi-eye"></i> Faktur
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                Pelanggan ini belum pernah melakukan transaksi rental atau pembelian.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
