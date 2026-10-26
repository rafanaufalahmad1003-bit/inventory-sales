@extends('layouts.app')

@section('title', 'Tambah Pelanggan Rental')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-person-plus text-primary me-2"></i>Pendaftaran Pelanggan Rental Kamera Baru
                </h5>
                <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('customers.store') }}" method="POST">
                    @csrf

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label small fw-semibold text-secondary">
                                Nama Lengkap Pelanggan <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Contoh: Dimas Anggara" required autofocus>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="nik" class="form-label small fw-semibold text-secondary">
                                NIK / No. KTP (Identitas Penyewa)
                            </label>
                            <input type="text" name="nik" id="nik" class="form-control font-monospace @error('nik') is-invalid @enderror" value="{{ old('nik') }}" placeholder="16 digit NIK KTP">
                            @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="phone" class="form-label small fw-semibold text-secondary">
                                No. WhatsApp / HP Aktif <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="Contoh: 081234567890" required>
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text small">Wajib untuk konfirmasi dan pengingat pengembalian gear.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label small fw-semibold text-secondary">Alamat Email</label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="email@contoh.com">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="address" class="form-label small fw-semibold text-secondary">Alamat Lengkap Domisili</label>
                        <textarea name="address" id="address" rows="3" class="form-control @error('address') is-invalid @enderror" placeholder="Alamat tinggal / studio sesuai identitas penyewa...">{{ old('address') }}</textarea>
                        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Daftarkan Pelanggan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
