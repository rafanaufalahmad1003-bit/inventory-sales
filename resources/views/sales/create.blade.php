@extends('layouts.app')

@section('title', 'Buat Transaksi Rental & Penjualan Kamera')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-11">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-cart-plus text-primary me-2"></i>Transaksi Rental / Penjualan Kamera Baru
                </h5>
                <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('sales.store') }}" method="POST" id="salesForm">
                    @csrf

                    <!-- Header Transaksi -->
                    <div class="row g-3 mb-4 p-3 bg-light rounded border">
                        <div class="col-md-4">
                            <label for="customer_id" class="form-label small fw-semibold text-secondary">
                                Pelanggan (Penyewa) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <select name="customer_id" id="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Pelanggan Rental --</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}" {{ old('customer_id', request('customer_id')) == $c->id ? 'selected' : '' }}>
                                            {{ $c->name }} (NIK: {{ $c->nik ?: '-' }} | {{ $c->phone ?: '-' }})
                                        </option>
                                    @endforeach
                                </select>
                                <a href="{{ route('customers.create') }}" class="btn btn-outline-primary" target="_blank" title="Daftar Pelanggan Baru">
                                    <i class="bi bi-person-plus"></i>
                                </a>
                            </div>
                            @error('customer_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-2">
                            <label for="type" class="form-label small fw-semibold text-secondary">
                                Tipe Transaksi <span class="text-danger">*</span>
                            </label>
                            <select name="type" id="type" class="form-select" required onchange="toggleRentalDates()">
                                <option value="rental" {{ old('type') == 'rental' ? 'selected' : '' }}>Rental Kamera</option>
                                <option value="sale" {{ old('type') == 'sale' ? 'selected' : '' }}>Penjualan Gear</option>
                            </select>
                        </div>

                        <div class="col-md-3 rental-date-col">
                            <label for="rental_start_date" class="form-label small fw-semibold text-secondary">
                                Tanggal Mulai Sewa <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="rental_start_date" id="rental_start_date" class="form-control" value="{{ old('rental_start_date', date('Y-m-d')) }}" onchange="updateRentalDays()">
                        </div>

                        <div class="col-md-3 rental-date-col">
                            <label for="rental_end_date" class="form-label small fw-semibold text-secondary">
                                Tanggal Selesai Sewa <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="rental_end_date" id="rental_end_date" class="form-control" value="{{ old('rental_end_date', date('Y-m-d', strtotime('+1 day'))) }}" onchange="updateRentalDays()">
                        </div>

                        <div class="col-md-3">
                            <label for="payment_method" class="form-label small fw-semibold text-secondary">
                                Metode Pembayaran <span class="text-danger">*</span>
                            </label>
                            <select name="payment_method" id="payment_method" class="form-select" required>
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Tunai / Cash</option>
                                <option value="transfer" {{ old('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                                <option value="qris" {{ old('payment_method') == 'qris' ? 'selected' : '' }}>QRIS / E-Wallet</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="sold_at" class="form-label small fw-semibold text-secondary">
                                Waktu Transaksi <span class="text-danger">*</span>
                            </label>
                            <input type="datetime-local" name="sold_at" id="sold_at" class="form-control" value="{{ old('sold_at', date('Y-m-d\TH:i')) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label for="notes" class="form-label small fw-semibold text-secondary">Catatan Khusus / Jaminan</label>
                            <input type="text" name="notes" id="notes" class="form-control" value="{{ old('notes') }}" placeholder="Contoh: Jaminan KTP Asli + SIM A, Pengambilan Jam 09:00">
                        </div>
                    </div>

                    <!-- Durasi Sewa Info Banner -->
                    <div id="rentalDaysBanner" class="alert alert-info py-2 d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <i class="bi bi-info-circle-fill me-2"></i>
                            <span>Durasi Sewa Terhitung: <strong id="rentalDaysText">2 Hari</strong></span>
                        </div>
                        <span class="badge bg-primary">Tarif otomatis dikalikan durasi hari</span>
                    </div>

                    <!-- Detail Item Transaksi -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold text-dark mb-0">
                            <i class="bi bi-camera me-1 text-primary"></i>Pilihan Kamera & Aksesoris Disewa / Dibeli
                        </h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addRowBtn">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Gear Lainnya
                        </button>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle" id="itemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 340px;">Gear / Produk Kamera <span class="text-danger">*</span></th>
                                    <th style="width: 130px;" class="text-center">Kuantitas <span class="text-danger">*</span></th>
                                    <th style="width: 180px;" class="text-end">Tarif Satuan (Rp)</th>
                                    <th style="width: 200px;" class="text-end">Subtotal (Rp)</th>
                                    <th style="width: 60px;" class="text-center">Hapus</th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <tr class="item-row">
                                    <td>
                                        <select name="items[0][product_id]" class="form-select product-select" required onchange="onProductChange(this)">
                                            <option value="">-- Pilih Kamera / Gear --</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" data-price="{{ (float) $product->selling_price }}" data-stock="{{ $product->stock }}">
                                                    {{ $product->sku }} - {{ $product->name }} (Tersedia: {{ $product->stock }} {{ $product->unit }}) - Rp {{ number_format($product->selling_price, 0, ',', '.') }}/hari
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="stock-warning text-danger small mt-1" style="display: none;"></div>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][quantity]" class="form-control text-center qty-input" value="1" min="1" required oninput="calculateRow(this)">
                                    </td>
                                    <td class="text-end fw-medium unit-price-text">
                                        Rp 0
                                    </td>
                                    <td class="text-end fw-bold subtotal-text text-dark">
                                        Rp 0
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)" disabled>
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="text-end fw-bold fs-6">Total Biaya Rental / Transaksi:</td>
                                    <td class="text-end fw-bold fs-4 text-success" id="grandTotalText">Rp 0</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-success px-4" id="submitBtn">
                            <i class="bi bi-check2-circle me-1"></i> Simpan Transaksi & Kurangi Stok Kamera
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    let rowIndex = 1;

    const productOptionsHtml = `
        <option value="">-- Pilih Kamera / Gear --</option>
        @foreach($products as $product)
            <option value="{{ $product->id }}" data-price="{{ (float) $product->selling_price }}" data-stock="{{ $product->stock }}">
                {{ $product->sku }} - {{ $product->name }} (Tersedia: {{ $product->stock }} {{ $product->unit }}) - Rp {{ number_format($product->selling_price, 0, ',', '.') }}/hari
            </option>
        @endforeach
    `;

    function getRentalDays() {
        const type = document.getElementById('type').value;
        if (type !== 'rental') return 1;

        const start = new Date(document.getElementById('rental_start_date').value);
        const end = new Date(document.getElementById('rental_end_date').value);
        if (isNaN(start.getTime()) || isNaN(end.getTime())) return 1;

        const diffTime = end - start;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        return diffDays > 0 ? diffDays : 1;
    }

    function updateRentalDays() {
        const days = getRentalDays();
        document.getElementById('rentalDaysText').innerText = days + ' Hari';
        calculateAllRows();
    }

    function toggleRentalDates() {
        const type = document.getElementById('type').value;
        const dateCols = document.querySelectorAll('.rental-date-col');
        const banner = document.getElementById('rentalDaysBanner');

        if (type === 'rental') {
            dateCols.forEach(col => col.style.display = 'block');
            banner.style.display = 'flex';
        } else {
            dateCols.forEach(col => col.style.display = 'none');
            banner.style.display = 'none';
        }
        calculateAllRows();
    }

    document.getElementById('addRowBtn').addEventListener('click', function () {
        const tbody = document.getElementById('itemsBody');
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML = `
            <td>
                <select name="items[${rowIndex}][product_id]" class="form-select product-select" required onchange="onProductChange(this)">
                    ${productOptionsHtml}
                </select>
                <div class="stock-warning text-danger small mt-1" style="display: none;"></div>
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][quantity]" class="form-control text-center qty-input" value="1" min="1" required oninput="calculateRow(this)">
            </td>
            <td class="text-end fw-medium unit-price-text">
                Rp 0
            </td>
            <td class="text-end fw-bold subtotal-text text-dark">
                Rp 0
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        rowIndex++;
        updateDeleteButtons();
    });

    function onProductChange(selectElem) {
        const row = selectElem.closest('tr');
        const selectedOption = selectElem.options[selectElem.selectedIndex];
        const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
        const stock = parseInt(selectedOption.getAttribute('data-stock')) || 0;

        row.querySelector('.unit-price-text').innerText = 'Rp ' + price.toLocaleString('id-ID');
        row.querySelector('.qty-input').max = stock;

        calculateRow(selectElem);
    }

    function calculateRow(elem) {
        const row = elem.closest('tr');
        const select = row.querySelector('.product-select');
        const selectedOption = select.options[select.selectedIndex];
        const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
        const stock = parseInt(selectedOption.getAttribute('data-stock')) || 0;
        const qty = parseInt(row.querySelector('.qty-input').value) || 0;
        const warningDiv = row.querySelector('.stock-warning');
        const days = getRentalDays();

        // Validasi stok di client-side
        if (selectedOption.value && qty > stock) {
            warningDiv.style.display = 'block';
            warningDiv.innerText = `Peringatan: Stok hanya tersisa ${stock} unit!`;
            row.querySelector('.qty-input').classList.add('is-invalid');
        } else {
            warningDiv.style.display = 'none';
            row.querySelector('.qty-input').classList.remove('is-invalid');
        }

        const subtotal = price * qty * days;
        row.querySelector('.subtotal-text').innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
        calculateGrandTotal();
    }

    function calculateAllRows() {
        const rows = document.querySelectorAll('#itemsBody .item-row');
        rows.forEach(r => {
            const select = r.querySelector('.product-select');
            if (select.value) calculateRow(select);
        });
    }

    function removeRow(btn) {
        btn.closest('tr').remove();
        calculateGrandTotal();
        updateDeleteButtons();
    }

    function updateDeleteButtons() {
        const rows = document.querySelectorAll('#itemsBody .item-row');
        rows.forEach(r => {
            r.querySelector('button').disabled = (rows.length <= 1);
        });
    }

    function calculateGrandTotal() {
        let grand = 0;
        const rows = document.querySelectorAll('#itemsBody .item-row');
        const days = getRentalDays();

        rows.forEach(r => {
            const select = r.querySelector('.product-select');
            const selectedOption = select.options[select.selectedIndex];
            const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
            const qty = parseInt(r.querySelector('.qty-input').value) || 0;
            grand += (price * qty * days);
        });

        document.getElementById('grandTotalText').innerText = 'Rp ' + grand.toLocaleString('id-ID');
    }

    document.addEventListener('DOMContentLoaded', function () {
        updateRentalDays();
    });
</script>
@endsection
