@extends('layouts.app')

@section('title', 'Daftar Pemasok / Supplier')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-truck text-primary me-2"></i>Pemasok (Suppliers) Kamera & Perlengkapan
        </h3>
        <p class="text-secondary small mb-0">Kelola data distributor resmi dan vendor suplai kamera, lensa, dan aksesoris studio.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-plus-circle"></i> Tambah Pemasok Baru
        </a>
    </div>
</div>

<div class="card p-3 mb-4 bg-white border-0 shadow-sm">
    <form action="{{ route('suppliers.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama, kontak, atau email pemasok..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-primary w-100">Cari</button>
            @if(request('search'))
                <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
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
                    <th>Nama Distributor / Pemasok</th>
                    <th>No. Telepon / HP</th>
                    <th>Email</th>
                    <th>Alamat Kantor / Gudang</th>
                    <th class="text-center">Riwayat PO</th>
                    <th class="text-end" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $supplier)
                <tr>
                    <td class="text-muted small">{{ $loop->iteration + ($suppliers->currentPage() - 1) * $suppliers->perPage() }}</td>
                    <td class="fw-semibold text-dark">{{ $supplier->name }}</td>
                    <td>{{ $supplier->phone ?? '-' }}</td>
                    <td class="text-secondary small">{{ $supplier->email ?? '-' }}</td>
                    <td class="text-secondary small">{{ $supplier->address ?? '-' }}</td>
                    <td class="text-center">
                        <span class="badge bg-info-subtle text-info border border-info-subtle fs-6">
                            {{ $supplier->purchases_count }} PO
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-outline-primary" title="Ubah">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @can('delete-master')
                            <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pemasok {{ $supplier->name }}?');">
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
                    <td colspan="7" class="text-center py-4 text-muted">Belum ada data pemasok.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($suppliers->hasPages())
    <div class="card-footer bg-white py-3 border-top d-flex justify-content-end">
        {{ $suppliers->links() }}
    </div>
    @endif
</div>
@endsection
