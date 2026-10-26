@extends('layouts.app')

@section('title', 'Tambah Gear / Kamera Baru')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-plus-circle text-primary me-2"></i>Tambah Produk Kamera & Gear Baru
                </h5>
                <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('products.store') }}" method="POST">
                    @csrf

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="sku" class="form-label small fw-semibold text-secondary">
                                SKU / Kode Unik <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="sku" id="sku" class="form-control font-monospace @error('sku') is-invalid @enderror" value="{{ old('sku') }}" placeholder="Contoh: CAM-SNY-A7M4" required>
                            @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text small">SKU harus unik di seluruh sistem inventaris.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="category_id" class="form-label small fw-semibold text-secondary">
                                Kategori <span class="text-danger">*</span>
                            </label>
                            <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label small fw-semibold text-secondary">
                            Nama Produk Kamera / Perlengkapan <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Contoh: Sony Alpha A7 IV Body Only" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label small fw-semibold text-secondary">Deskripsi & Spesifikasi Singkat</label>
                        <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror" placeholder="Resolusi sensor, kelengkapan unit, kondisi, dsb.">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="purchase_price" class="form-label small fw-semibold text-secondary">
                                Harga Beli / Modal Pengadaan (Rp) <span class="text-danger">*</span>
                            </label>
                            <input type="number" step="1000" name="purchase_price" id="purchase_price" class="form-control @error('purchase_price') is-invalid @enderror" value="{{ old('purchase_price', 0) }}" required min="0">
                            @error('purchase_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="selling_price" class="form-label small fw-semibold text-secondary">
                                Tarif Sewa / Jual (Rp) <span class="text-danger">*</span>
                            </label>
                            <input type="number" step="1000" name="selling_price" id="selling_price" class="form-control @error('selling_price') is-invalid @enderror" value="{{ old('selling_price', 0) }}" required min="0">
                            @error('selling_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="stock" class="form-label small fw-semibold text-secondary">
                                Stok Awal Fisik <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="stock" id="stock" class="form-control @error('stock') is-invalid @enderror" value="{{ old('stock', 0) }}" required min="0">
                            @error('stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="minimum_stock" class="form-label small fw-semibold text-secondary">
                                Batas Minimum Stok <span class="text-danger">*</span>
                            </label>
                            <input type="number" name="minimum_stock" id="minimum_stock" class="form-control @error('minimum_stock') is-invalid @enderror" value="{{ old('minimum_stock', 2) }}" required min="0">
                            @error('minimum_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text small">Memicu alert stok menipis di dashboard.</div>
                        </div>

                        <div class="col-md-4">
                            <label for="unit" class="form-label small fw-semibold text-secondary">
                                Satuan Unit <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="unit" id="unit" class="form-control @error('unit') is-invalid @enderror" value="{{ old('unit', 'unit') }}" placeholder="unit, set, pcs" required>
                            @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Simpan Gear Kamera
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
