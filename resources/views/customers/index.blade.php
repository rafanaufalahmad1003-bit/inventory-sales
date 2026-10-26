@extends('layouts.app')

@section('title', 'Pelanggan Rental Kamera')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-person-check text-primary me-2"></i>Data Pelanggan (Customer Rental Kamera)
        </h3>
        <p class="text-secondary small mb-0">Kelola identitas penyewa kamera, verifikasi NIK/KTP, kontak WhatsApp, dan riwayat rental.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <a href="{{ route('customers.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-person-plus"></i> Tambah Pelanggan Baru
        </a>
    </div>
</div>

<div class="card p-3 mb-4 bg-white border-0 shadow-sm">
    <form action="{{ route('customers.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama, NIK KTP, atau No. WhatsApp..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-primary w-100">Cari</button>
            @if(request('search'))
                <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
            @endif
        </div>
    </form>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 60px;">No</th>
                    <th>Nama Pelanggan</th>
                    <th>NIK / No. KTP</th>
                    <th>No. WhatsApp / HP</th>
                    <th>Email</th>
                    <th>Alamat Domisili</th>
                    <th class="text-center">Total Rental</th>
                    <th class="text-end" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                <tr>
                    <td class="text-muted small">{{ $loop->iteration + ($customers->currentPage() - 1) * $customers->perPage() }}</td>
                    <td>
                        <div class="fw-semibold text-dark">{{ $customer->name }}</div>
                        @if($customer->user_id)
                            <span class="badge bg-success-subtle text-success border border-success-subtle small">Terdaftar sebagai Akun Web</span>
                        @endif
                    </td>
                    <td class="font-monospace text-secondary small">{{ $customer->nik ?? '-' }}</td>
                    <td>
                        <span class="fw-medium text-dark"><i class="bi bi-whatsapp text-success me-1"></i>{{ $customer->phone ?? '-' }}</span>
                    </td>
                    <td class="text-secondary small">{{ $customer->email ?? '-' }}</td>
                    <td class="text-secondary small text-truncate" style="max-width: 200px;">{{ $customer->address ?? '-' }}</td>
                    <td class="text-center">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6">
                            {{ $customer->sales_count }} Transaksi
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-outline-secondary" title="Detail & Riwayat Rental">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-outline-primary" title="Ubah">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @can('delete-master')
                            <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelanggan {{ $customer->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-4 text-muted">Belum ada data pelanggan rental kamera.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($customers->hasPages())
    <div class="card-footer bg-white py-3 border-top d-flex justify-content-end">
        {{ $customers->links() }}
    </div>
    @endif
</div>
@endsection
