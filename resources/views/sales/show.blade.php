@extends('layouts.app')

@section('title', 'Nota Rental & Invoice - ' . $sale->sale_number)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Transaksi
            </a>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-dark" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Cetak Faktur / Nota
                </button>

                @can('cancel-transaction')
                    @if($sale->status === 'completed')
                    <form action="{{ route('sales.cancel', $sale->id) }}" method="POST" onsubmit="return confirm('Peringatan: Membatalkan transaksi ini akan mengembalikan stok unit kamera ke dalam inventaris. Lanjutkan pembatalan?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="bi bi-x-circle me-1"></i> Batalkan Transaksi (Pulihkan Stok)
                        </button>
                    </form>
                    @endif
                @endcan
            </div>
        </div>

        <div class="card border-0 shadow-sm p-4 p-md-5">
            <!-- Header Nota -->
            <div class="row pb-4 border-bottom mb-4 align-items-center">
                <div class="col-sm-6">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="p-2 rounded bg-primary text-white d-inline-flex align-items-center justify-content-center">
                            <i class="bi bi-camera-fill fs-5"></i>
                        </span>
                        <h4 class="fw-bold mb-0 text-dark">LensRent</h4>
                    </div>
                    <p class="text-secondary small mb-0">
                        Camera & Gear Rental Studio Hub<br>
                        Jl. Radio Dalam Raya No. 42, Gandaria, Jakarta Selatan<br>
                        WhatsApp CS: 0811-9988-7766 | Email: rental@lensrent.com
                    </p>
                </div>
                <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                    <h5 class="fw-bold text-uppercase text-secondary mb-1">
                        {{ $sale->type === 'rental' ? 'Faktur Sewa Kamera' : 'Nota Penjualan Gear' }}
                    </h5>
                    <div class="font-monospace fw-bold text-success fs-5">{{ $sale->sale_number }}</div>
                    <div class="text-muted small">Waktu Transaksi: {{ $sale->sold_at->format('d F Y H:i') }}</div>
                    <div class="mt-2">
                        @if($sale->status === 'completed')
                            <span class="badge bg-success fs-6">Transaksi Selesai & Sah</span>
                        @else
                            <span class="badge bg-danger fs-6">Transaksi Dibatalkan</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Info Penyewa & Periode -->
            <div class="row mb-4 p-3 bg-light rounded border mx-0">
                <div class="col-sm-6">
                    <h6 class="text-secondary small text-uppercase fw-bold mb-1">Penyewa / Pelanggan:</h6>
                    <h5 class="fw-bold text-dark mb-1">{{ $sale->customer->name ?? 'Pelanggan Umum' }}</h5>
                    <div class="text-secondary small">
                        <strong>NIK KTP:</strong> {{ $sale->customer->nik ?? '-' }}<br>
                        <strong>No. HP / WA:</strong> {{ $sale->customer->phone ?? '-' }}<br>
                        <strong>Alamat:</strong> {{ $sale->customer->address ?? '-' }}
                    </div>
                </div>
                <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                    <h6 class="text-secondary small text-uppercase fw-bold mb-1">Detail Periode & Pembayaran:</h6>
                    @if($sale->type === 'rental' && $sale->rental_start_date)
                        @php
                            $start = \Carbon\Carbon::parse($sale->rental_start_date);
                            $end = \Carbon\Carbon::parse($sale->rental_end_date);
                            $days = max(1, $start->diffInDays($end) + 1);
                        @endphp
                        <div class="fw-bold text-dark mb-1">
                            {{ $start->format('d M Y') }} s/d {{ $end->format('d M Y') }}
                        </div>
                        <span class="badge bg-primary mb-2">Durasi: {{ $days }} Hari Sewa</span>
                    @else
                        <div class="fw-bold text-dark mb-2">Penjualan Langsung</div>
                    @endif
                    <div class="text-secondary small">
                        <strong>Metode Bayar:</strong> <span class="text-uppercase">{{ $sale->payment_method }}</span><br>
                        <strong>Status:</strong> <span class="badge bg-success-subtle text-success">LUNAS</span><br>
                        <strong>Kasir / Petugas:</strong> {{ $sale->user->name }}
                    </div>
                </div>
            </div>

            @if($sale->notes)
            <div class="alert alert-secondary py-2 small mb-4">
                <strong>Catatan / Jaminan Identitas:</strong> {{ $sale->notes }}
            </div>
            @endif

            <!-- Tabel Gear Disewa -->
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>SKU & Nama Kamera / Perlengkapan</th>
                            <th class="text-center" style="width: 120px;">Kuantitas</th>
                            <th class="text-end" style="width: 180px;">Tarif Satuan / Hari</th>
                            <th class="text-end" style="width: 200px;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $item)
                        <tr>
                            <td class="text-center text-muted">{{ $loop->iteration }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $item->product->name ?? 'Produk Dihapus' }}</div>
                                <div class="font-monospace text-muted small">{{ $item->product->sku ?? '-' }} &bull; Kategori: {{ $item->product->category->name ?? '-' }}</div>
                            </td>
                            <td class="text-center fw-semibold">
                                {{ $item->quantity }} {{ $item->product->unit ?? 'unit' }}
                            </td>
                            <td class="text-end text-muted">
                                Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                            </td>
                            <td class="text-end fw-bold text-dark">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end fw-bold fs-6">Grand Total Pembayaran:</td>
                            <td class="text-end fw-bold fs-4 text-success">
                                Rp {{ number_format($sale->total, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Ketentuan Rental Kamera -->
            <div class="border rounded p-3 mb-4 bg-white small text-secondary">
                <strong class="text-dark d-block mb-1">Ketentuan Rental Kamera LensRent:</strong>
                <ol class="mb-0 ps-3">
                    <li>Penyewa bertanggung jawab penuh atas keutuhan dan fungsi seluruh kamera, lensa, dan aksesoris yang disewa.</li>
                    <li>Keterlambatan pengembalian unit melebihi jadwal yang tertera akan dikenakan denda sewa harian.</li>
                    <li>Kerusakan atau kehilangan unit wajib diganti sesuai nilai pertanggungan unit.</li>
                </ol>
            </div>

            <!-- Tanda Tangan Dokumen -->
            <div class="row pt-4 text-center">
                <div class="col-6">
                    <div class="text-secondary small mb-5">Penyewa Kamera</div>
                    <div class="fw-bold text-dark border-top pt-2 d-inline-block px-4">
                        {{ $sale->customer->name ?? 'Pelanggan' }}
                    </div>
                </div>
                <div class="col-6">
                    <div class="text-secondary small mb-5">Petugas / Kasir Rental</div>
                    <div class="fw-bold text-dark border-top pt-2 d-inline-block px-4">
                        {{ $sale->user->name }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
