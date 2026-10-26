@extends('layouts.app')

@section('title', 'Pemesanan Rental Kamera')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-camera text-primary me-2"></i>Form Pemesanan / Sewa Kamera Online
                </h5>
                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
                </a>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('booking.store') }}" method="POST" id="bookingForm">
                    @csrf

                    <!-- Identitas Pelanggan (Otomatis Terisi) -->
                    <div class="p-3 bg-light rounded border mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="bi bi-person-check-fill text-success me-1"></i>Data Penyewa (Akun Anda)
                            </h6>
                            <span class="badge bg-success-subtle text-success">Terverifikasi</span>
                        </div>
                        <div class="row g-2 small text-secondary">
                            <div class="col-md-4">
                                <strong>Nama:</strong> <span class="text-dark">{{ $customer->name }}</span>
                            </div>
                            <div class="col-md-4">
                                <strong>No. WhatsApp / HP:</strong> <span class="text-dark">{{ $customer->phone ?: 'Belum diisi' }}</span>
                            </div>
                            <div class="col-md-4">
                                <strong>NIK / KTP:</strong> <span class="text-dark">{{ $customer->nik ?: 'Belum diisi' }}</span>
                            </div>
                            <div class="col-12 mt-2">
                                <strong>Alamat Domisili:</strong> <span class="text-dark">{{ $customer->address ?: 'Sesuai data akun' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Pilihan Tanggal & Pembayaran -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="rental_start_date" class="form-label small fw-semibold text-secondary">
                                Tanggal Mulai Sewa <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="rental_start_date" id="rental_start_date" class="form-control @error('rental_start_date') is-invalid @enderror" value="{{ old('rental_start_date', date('Y-m-d')) }}" required onchange="updateRentalDays()">
                            @error('rental_start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="rental_end_date" class="form-label small fw-semibold text-secondary">
                                Tanggal Selesai Sewa <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="rental_end_date" id="rental_end_date" class="form-control @error('rental_end_date') is-invalid @enderror" value="{{ old('rental_end_date', date('Y-m-d', strtotime('+1 day'))) }}" required onchange="updateRentalDays()">
                            @error('rental_end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="payment_method" class="form-label small fw-semibold text-secondary">
                                Metode Pembayaran <span class="text-danger">*</span>
                            </label>
                            <select name="payment_method" id="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                                <option value="transfer" {{ old('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer Bank (BCA / Mandiri)</option>
                                <option value="qris" {{ old('payment_method') == 'qris' ? 'selected' : '' }}>QRIS / GoPay / OVO</option>
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Bayar Tunai di Gerai Studio</option>
                            </select>
                            @error('payment_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label for="notes" class="form-label small fw-semibold text-secondary">Catatan Khusus / Keperluan Sewa</label>
                            <input type="text" name="notes" id="notes" class="form-control" value="{{ old('notes') }}" placeholder="Contoh: Keperluan dokumentasi wedding / photoshoot outdoor">
                        </div>
                    </div>

                    <!-- Banner Perhitungan Hari -->
                    <div class="alert alert-info py-2 d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <i class="bi bi-clock-history me-2"></i>
                            <span>Durasi Sewa: <strong id="rentalDaysText">2 Hari</strong></span>
                        </div>
                        <span class="badge bg-primary">Tarif otomatis dihitung per hari</span>
                    </div>

                    <!-- Pilihan Item Kamera -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold text-dark mb-0">
                            <i class="bi bi-camera me-1 text-primary"></i>Pilih Kamera & Perlengkapan yang Ingin Disewa
                        </h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addRowBtn">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Gear Lainnya
                        </button>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle" id="itemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Kamera / Gear <span class="text-danger">*</span></th>
                                    <th style="width: 120px;" class="text-center">Kuantitas</th>
                                    <th style="width: 180px;" class="text-end">Tarif Sewa/Hari</th>
                                    <th style="width: 190px;" class="text-end">Subtotal</th>
                                    <th style="width: 60px;" class="text-center">Hapus</th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <tr class="item-row">
                                    <td>
                                        <select name="items[0][product_id]" class="form-select product-select" required onchange="onProductChange(this)">
                                            <option value="">-- Pilih Kamera / Lensa / Gear --</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" 
                                                    data-price="{{ (float) $product->selling_price }}" 
                                                    data-stock="{{ $product->stock }}"
                                                    {{ (old('items.0.product_id', $selectedProductId) == $product->id) ? 'selected' : '' }}>
                                                    {{ $product->name }} (Tersedia: {{ $product->stock }} {{ $product->unit }}) - Rp {{ number_format($product->selling_price, 0, ',', '.') }}/hari
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
                                    <td colspan="3" class="text-end fw-bold fs-6">Perkiraan Total Biaya Sewa:</td>
                                    <td class="text-end fw-bold fs-4 text-success" id="grandTotalText">Rp 0</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="bi bi-check-circle me-1"></i> Konfirmasi & Ajukan Pemesanan Rental
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
        <option value="">-- Pilih Kamera / Lensa / Gear --</option>
        @foreach($products as $product)
            <option value="{{ $product->id }}" data-price="{{ (float) $product->selling_price }}" data-stock="{{ $product->stock }}">
                {{ $product->name }} (Tersedia: {{ $product->stock }} {{ $product->unit }}) - Rp {{ number_format($product->selling_price, 0, ',', '.') }}/hari
            </option>
        @endforeach
    `;

    function getRentalDays() {
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
        const firstSelect = document.querySelector('.product-select');
        if (firstSelect && firstSelect.value) {
            onProductChange(firstSelect);
        }
    });
</script>
@endsection
