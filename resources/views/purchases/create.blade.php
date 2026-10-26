@extends('layouts.app')

@section('title', 'Input Pembelian Stok (Stock-In)')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-11">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-box-arrow-in-down text-info me-2"></i>Pengadaan Stok Kamera & Gear (Stock-In)
                </h5>
                <a href="{{ route('purchases.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm">
                    @csrf

                    <!-- Header Transaksi -->
                    <div class="row g-3 mb-4 p-3 bg-light rounded border">
                        <div class="col-md-5">
                            <label for="supplier_id" class="form-label small fw-semibold text-secondary">
                                Pemasok / Distributor <span class="text-danger">*</span>
                            </label>
                            <select name="supplier_id" id="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Distributor / Vendor --</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="purchased_at" class="form-label small fw-semibold text-secondary">
                                Tanggal Penerimaan / Pembelian <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="purchased_at" id="purchased_at" class="form-control @error('purchased_at') is-invalid @enderror" value="{{ old('purchased_at', date('Y-m-d')) }}" required>
                            @error('purchased_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="notes" class="form-label small fw-semibold text-secondary">Catatan / No. Surat Jalan</label>
                            <input type="text" name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" value="{{ old('notes') }}" placeholder="Contoh: No. Faktur Distributor INV-8829">
                            @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Detail Item Transaksi -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold text-dark mb-0">
                            <i class="bi bi-list-check me-1 text-primary"></i>Daftar Gear / Kamera yang Masuk
                        </h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addRowBtn">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Baris Produk
                        </button>
                    </div>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle" id="itemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 320px;">Produk Kamera / Perlengkapan <span class="text-danger">*</span></th>
                                    <th style="width: 130px;" class="text-center">Kuantitas Masuk <span class="text-danger">*</span></th>
                                    <th style="width: 200px;" class="text-end">Harga Beli Satuan (Rp) <span class="text-danger">*</span></th>
                                    <th style="width: 200px;" class="text-end">Subtotal (Rp)</th>
                                    <th style="width: 60px;" class="text-center">Hapus</th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <!-- Baris Awal -->
                                <tr class="item-row">
                                    <td>
                                        <select name="items[0][product_id]" class="form-select product-select" required onchange="onProductChange(this)">
                                            <option value="">-- Pilih Kamera / Gear --</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}" data-price="{{ (float) $product->purchase_price }}" data-stock="{{ $product->stock }}">
                                                    {{ $product->sku }} - {{ $product->name }} (Stok saat ini: {{ $product->stock }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][quantity]" class="form-control text-center qty-input" value="1" min="1" required oninput="calculateRow(this)">
                                    </td>
                                    <td>
                                        <input type="number" step="1000" name="items[0][unit_cost]" class="form-control text-end cost-input" value="0" min="0" required oninput="calculateRow(this)">
                                    </td>
                                    <td class="text-end fw-semibold subtotal-text">
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
                                    <td colspan="3" class="text-end fw-bold fs-6">Total Nilai Pembelian:</td>
                                    <td class="text-end fw-bold fs-5 text-primary" id="grandTotalText">Rp 0</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-circle me-1"></i> Simpan Transaksi Pembelian & Tambah Stok
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
            <option value="{{ $product->id }}" data-price="{{ (float) $product->purchase_price }}" data-stock="{{ $product->stock }}">
                {{ $product->sku }} - {{ $product->name }} (Stok saat ini: {{ $product->stock }})
            </option>
        @endforeach
    `;

    document.getElementById('addRowBtn').addEventListener('click', function () {
        const tbody = document.getElementById('itemsBody');
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML = `
            <td>
                <select name="items[${rowIndex}][product_id]" class="form-select product-select" required onchange="onProductChange(this)">
                    ${productOptionsHtml}
                </select>
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][quantity]" class="form-control text-center qty-input" value="1" min="1" required oninput="calculateRow(this)">
            </td>
            <td>
                <input type="number" step="1000" name="items[${rowIndex}][unit_cost]" class="form-control text-end cost-input" value="0" min="0" required oninput="calculateRow(this)">
            </td>
            <td class="text-end fw-semibold subtotal-text">
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
        const selectedOption = selectElem.options[selectElem.selectedIndex];
        const defaultPrice = selectedOption.getAttribute('data-price') || 0;
        const row = selectElem.closest('tr');
        const costInput = row.querySelector('.cost-input');
        costInput.value = defaultPrice;
        calculateRow(selectElem);
    }

    function calculateRow(elem) {
        const row = elem.closest('tr');
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
        const subtotal = qty * cost;
        row.querySelector('.subtotal-text').innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
        calculateGrandTotal();
    }

    function removeRow(btn) {
        const row = btn.closest('tr');
        row.remove();
        calculateGrandTotal();
        updateDeleteButtons();
    }

    function updateDeleteButtons() {
        const rows = document.querySelectorAll('#itemsBody .item-row');
        rows.forEach(r => {
            const btn = r.querySelector('button');
            btn.disabled = (rows.length <= 1);
        });
    }

    function calculateGrandTotal() {
        let total = 0;
        const rows = document.querySelectorAll('#itemsBody .item-row');
        rows.forEach(r => {
            const qty = parseFloat(r.querySelector('.qty-input').value) || 0;
            const cost = parseFloat(r.querySelector('.cost-input').value) || 0;
            total += (qty * cost);
        });
        document.getElementById('grandTotalText').innerText = 'Rp ' + total.toLocaleString('id-ID');
    }
</script>
@endsection
