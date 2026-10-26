@extends('layouts.app')

@section('title', 'Portal Pelanggan - LensRent')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-8">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-camera text-primary me-2"></i>Halo, {{ $user->name }}!
        </h3>
        <p class="text-secondary small mb-0">Selamat datang di portal rental kamera & gear studio LensRent. Pantau gear yang sedang Anda sewa dan riwayat transaksi Anda.</p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a href="{{ route('products.index') }}" class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-grid"></i> Lihat Katalog Rental Kamera
        </a>
    </div>
</div>

<!-- Profil Singkat Pelanggan -->
@if($customer)
<div class="card p-3 mb-4 bg-white border-0 shadow-sm border-start border-primary border-4">
    <div class="row align-items-center">
        <div class="col-md-3">
            <span class="text-muted small">Nomor Identitas (NIK/KTP):</span>
            <div class="fw-bold">{{ $customer->nik ?? 'Belum diverifikasi' }}</div>
        </div>
        <div class="col-md-3">
            <span class="text-muted small">No. WhatsApp / HP:</span>
            <div class="fw-bold">{{ $customer->phone ?? '-' }}</div>
        </div>
        <div class="col-md-4">
            <span class="text-muted small">Alamat Domisili:</span>
            <div class="fw-bold small">{{ $customer->address ?? '-' }}</div>
        </div>
        <div class="col-md-2 text-md-end">
            <span class="badge bg-success-subtle text-success border border-success-subtle">
                <i class="bi bi-check-circle me-1"></i>Penyewa Aktif
            </span>
        </div>
    </div>
</div>
@endif

<!-- Active Rentals -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="bi bi-clock-history text-warning me-2"></i>Sewa Kamera Sedang Berjalan (Active Rentals)
        </h6>
        <span class="badge bg-warning text-dark">{{ $activeRentals->count() }} transaksi aktif</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>No. Transaksi</th>
                    <th>Gear / Kamera Disewa</th>
                    <th>Periode Sewa</th>
                    <th>Total Biaya</th>
                    <th>Status</th>
                    <th class="text-end">Faktur</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activeRentals as $rental)
                <tr>
                    <td class="font-monospace fw-bold text-primary">{{ $rental->sale_number }}</td>
                    <td>
                        @foreach($rental->items as $item)
                            <div class="fw-medium">&bull; {{ $item->product->name }} ({{ $item->quantity }}x)</div>
                        @endforeach
                    </td>
                    <td>
                        <div class="small fw-semibold text-dark">
                            {{ $rental->rental_start_date ? $rental->rental_start_date->format('d/m/Y') : '-' }} s/d {{ $rental->rental_end_date ? $rental->rental_end_date->format('d/m/Y') : '-' }}
                        </div>
                    </td>
                    <td class="fw-bold text-dark">Rp {{ number_format($rental->total, 0, ',', '.') }}</td>
                    <td><span class="badge bg-success">Berjalan</span></td>
                    <td class="text-end">
                        <a href="{{ route('sales.show', $rental->id) }}" class="btn btn-sm btn-outline-primary py-1">
                            <i class="bi bi-file-earmark-text me-1"></i>Lihat Nota
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        Saat ini Anda tidak memiliki kamera yang sedang disewa.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Available Gear Catalog Preview -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="bi bi-camera-reels text-primary me-2"></i>Katalog Gear Populer Siap Sewa
        </h6>
        <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-primary">Lihat Selengkapnya</a>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @foreach($availableProducts as $prod)
            <div class="col-md-3 col-sm-6">
                <div class="card h-100 p-3 border shadow-sm">
                    <span class="badge bg-light text-secondary border mb-2 align-self-start">{{ $prod->category->name }}</span>
                    <h6 class="fw-bold text-dark mb-1">{{ $prod->name }}</h6>
                    <div class="text-muted small mb-2 font-monospace">{{ $prod->sku }}</div>
                    <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small d-block">Tarif Sewa:</span>
                            <span class="fw-bold text-primary">Rp {{ number_format($prod->selling_price, 0, ',', '.') }}/hari</span>
                        </div>
                        <span class="badge bg-success-subtle text-success">Stok: {{ $prod->stock }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
