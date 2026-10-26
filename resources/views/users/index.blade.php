@extends('layouts.app')

@section('title', 'Manajemen Pengguna & Role')

@section('content')
<div class="row align-items-center mb-4">
    <div class="col-md-7">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-people text-primary me-2"></i>Manajemen Pengguna Sistem
        </h3>
        <p class="text-secondary small mb-0">Kelola akun, hak akses role (Owner, Admin, Staff, Pelanggan), dan keamanan sistem.</p>
    </div>
    <div class="col-md-5 text-md-end mt-3 mt-md-0">
        <a href="{{ route('users.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-person-plus"></i> Tambah Pengguna Baru
        </a>
    </div>
</div>

<!-- Filter Bar -->
<div class="card p-3 mb-4 bg-white border-0 shadow-sm">
    <form action="{{ route('users.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0" placeholder="Cari nama atau email pengguna..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-3">
            <select name="role" class="form-select">
                <option value="">-- Semua Role --</option>
                <option value="owner" {{ request('role') == 'owner' ? 'selected' : '' }}>Owner</option>
                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="staff" {{ request('role') == 'staff' ? 'selected' : '' }}>Staff</option>
                <option value="pelanggan" {{ request('role') == 'pelanggan' ? 'selected' : '' }}>Pelanggan</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-primary w-100">Cari</button>
            @if(request()->hasAny(['search', 'role']))
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
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
                    <th style="width: 60px;">No</th>
                    <th>Nama Pengguna</th>
                    <th>Email</th>
                    <th>No. HP</th>
                    <th class="text-center">Role / Hak Akses</th>
                    <th>Terdaftar Sejak</th>
                    <th class="text-end" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td class="text-muted small">{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                    <td>
                        <div class="fw-semibold text-dark">{{ $user->name }}</div>
                        @if($user->id === auth()->id())
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle small">Akun Anda Sendiri</span>
                        @endif
                    </td>
                    <td class="text-secondary small">{{ $user->email }}</td>
                    <td class="text-secondary small">{{ $user->phone ?? '-' }}</td>
                    <td class="text-center">
                        @if($user->role === 'owner')
                            <span class="badge bg-danger">Owner</span>
                        @elseif($user->role === 'admin')
                            <span class="badge bg-primary">Admin</span>
                        @elseif($user->role === 'staff')
                            <span class="badge bg-info text-dark">Staff</span>
                        @else
                            <span class="badge bg-success">Pelanggan</span>
                        @endif
                    </td>
                    <td class="text-muted small">{{ $user->created_at->format('d/m/Y') }}</td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-outline-primary" title="Ubah Pengguna">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if($user->id !== auth()->id())
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun {{ $user->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">Belum ada data pengguna.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="card-footer bg-white py-3 border-top d-flex justify-content-end">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection
