@extends('layouts.app')

@section('title', 'Faktur Pembelian - ' . $purchase->purchase_number)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Pembelian
            </a>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-dark" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Cetak Bukti PO
                </button>

                @can('cancel-transaction')
                    @if($purchase->status === 'completed')
                    <form action="{{ route('purchases.cancel', $purchase->id) }}" method="POST" onsubmit="return confirm('Peringatan: Membatalkan transaksi pembelian ini akan mengurangi kembali stok fisik produk yang telah masuk. Lanjutkan?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="bi bi-x-circle me-1"></i> Batalkan Transaksi Ini
                        </button>
                    </form>
                    @endif
                @endcan
            </div>
        </div>

        <div class="card border-0 shadow-sm p-4 p-md-5">
            <!-- Header Faktur -->
            <div class="row pb-4 border-bottom mb-4 align-items-center">
                <div class="col-sm-6">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="p-2 rounded bg-primary text-white d-inline-flex align-items-center justify-content-center">
                            <i class="bi bi-camera-fill fs-5"></i>
                        </span>
                        <h4 class="fw-bold mb-0 text-dark">LensRent</h4>
                    </div>
                    <p class="text-secondary small mb-0">
                        Camera & Production Gear Management<br>
                        Jl. Radio Dalam Raya No. 42, Gandaria, Jakarta Selatan<br>
                        Telp: (021) 7288-9900 | info@lensrent.com
                    </p>
                </div>
                <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                    <h5 class="fw-bold text-uppercase text-secondary mb-1">Bukti Penerimaan Stok (PO)</h5>
                    <div class="font-monospace fw-bold text-primary fs-5">{{ $purchase->purchase_number }}</div>
                    <div class="text-muted small">Tanggal: {{ $purchase->purchased_at->format('d F Y') }}</div>
                    <div class="mt-2">
                        @if($purchase->status === 'completed')
                            <span class="badge bg-success fs-6">Selesai (Stok Masuk)</span>
                        @else
                            <span class="badge bg-danger fs-6">Transaksi Dibatalkan</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Info Vendor & Penerima -->
            <div class="row mb-4">
                <div class="col-sm-6">
                    <h6 class="text-secondary small text-uppercase fw-bold mb-2">Pemasok / Distributor:</h6>
                    <h5 class="fw-bold text-dark mb-1">{{ $purchase->supplier->name }}</h5>
                    <p class="text-secondary small mb-0">
                        Telepon: {{ $purchase->supplier->phone ?: '-' }}<br>
                        Email: {{ $purchase->supplier->email ?: '-' }}<br>
                        Alamat: {{ $purchase->supplier->address ?: '-' }}
                    </p>
                </div>
                <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                    <h6 class="text-secondary small text-uppercase fw-bold mb-2">Penerima Inventaris:</h6>
                    <div class="fw-bold text-dark">{{ $purchase->user->name }} (Staff/Admin)</div>
                    <div class="text-secondary small">Email: {{ $purchase->user->email }}</div>
                    @if($purchase->notes)
                        <div class="mt-2 text-secondary small bg-light p-2 rounded text-start text-sm-end">
                            <strong>Catatan:</strong> {{ $purchase->notes }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tabel Item -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>SKU & Nama Produk Kamera</th>
                            <th class="text-center" style="width: 120px;">Kuantitas</th>
                            <th class="text-end" style="width: 180px;">Harga Satuan Beli</th>
                            <th class="text-end" style="width: 200px;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->items as $item)
                        <tr>
                            <td class="text-center text-muted">{{ $loop->iteration }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $item->product->name ?? 'Produk Dihapus' }}</div>
                                <div class="font-monospace text-muted small">{{ $item->product->sku ?? '-' }}</div>
                            </td>
                            <td class="text-center fw-semibold">
                                {{ $item->quantity }} {{ $item->product->unit ?? 'unit' }}
                            </td>
                            <td class="text-end text-muted">
                                Rp {{ number_format($item->unit_cost, 0, ',', '.') }}
                            </td>
                            <td class="text-end fw-bold text-dark">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end fw-bold fs-6">Grand Total Pembelian:</td>
                            <td class="text-end fw-bold fs-5 text-primary">
                                Rp {{ number_format($purchase->total, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Tanda Tangan Dokumen -->
            <div class="row pt-4 text-center mt-4">
                <div class="col-6">
                    <div class="text-secondary small mb-5">Pengirim / Distributor</div>
                    <div class="fw-bold text-dark border-top pt-2 d-inline-block px-4">
                        {{ $purchase->supplier->name }}
                    </div>
                </div>
                <div class="col-6">
                    <div class="text-secondary small mb-5">Penerima Gudang</div>
                    <div class="fw-bold text-dark border-top pt-2 d-inline-block px-4">
                        {{ $purchase->user->name }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
