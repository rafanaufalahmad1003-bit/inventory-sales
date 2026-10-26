@extends('layouts.app')

@section('title', 'Kategori Gear Kamera')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-tags text-primary me-2"></i>Kategori Produk & Perlengkapan Kamera
        </h3>
        <p class="text-secondary small mb-0">Kelola kelompok kategori kamera, lensa, lighting, audio, dan stabilizer.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <a href="{{ route('categories.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-plus-circle"></i> Tambah Kategori Baru
        </a>
    </div>
</div>

<div class="card p-3 mb-4 bg-white border-0 shadow-sm">
    <form action="{{ route('categories.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama atau deskripsi kategori..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-primary w-100">Cari</button>
            @if(request('search'))
                <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
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
                    <th>Nama Kategori</th>
                    <th>Slug</th>
                    <th>Deskripsi</th>
                    <th class="text-center">Jumlah Produk</th>
                    <th class="text-end" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                <tr>
                    <td class="text-muted small">{{ $loop->iteration + ($categories->currentPage() - 1) * $categories->perPage() }}</td>
                    <td class="fw-semibold text-dark">{{ $category->name }}</td>
                    <td class="font-monospace text-secondary small">{{ $category->slug }}</td>
                    <td class="text-secondary small">{{ $category->description ?? '-' }}</td>
                    <td class="text-center">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-6">
                            {{ $category->products_count }} item
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('categories.edit', $category->id) }}" class="btn btn-outline-primary" title="Ubah">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @can('delete-master')
                            <form action="{{ route('categories.destroy', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori {{ $category->name }}?');">
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
                    <td colspan="6" class="text-center py-4 text-muted">Belum ada data kategori.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($categories->hasPages())
    <div class="card-footer bg-white py-3 border-top d-flex justify-content-end">
        {{ $categories->links() }}
    </div>
    @endif
</div>
@endsection
