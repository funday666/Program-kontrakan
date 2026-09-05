@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Header Halaman -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h2 class="fw-bold mb-0 fs-3"><i class="bi bi-building text-primary me-2"></i> Data Property</h2>
                <p class="text-muted mb-0 small">Kelola penyewa bangunan, ukuran properti, dan tagihan tahunan.</p>
            </div>

            <form action="{{ url('/data-property') }}" method="GET"
                class="d-flex flex-wrap gap-2 align-items-center bg-white p-2 rounded shadow-sm border border-2">
                <div class="input-group input-group-sm" style="width: auto; min-width: 200px;">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0"
                        placeholder="Cari nama penyewa..." value="{{ request('search') }}">
                </div>
                <button type="submit" class="btn btn-sm btn-primary px-3 fw-bold">Cari</button>
                @if (request('search'))
                    <a href="{{ url('/data-property') }}" class="btn btn-sm btn-outline-danger"><i
                            class="bi bi-x-lg"></i></a>
                @endif
            </form>
        </div>

        @if (session('sukses'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('sukses') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-secondary">Daftar Kontrak Property</h6>
                @if (!strpos(strtolower(Auth::user()->email), 'viewer'))
                    <button class="btn btn-sm btn-primary fw-bold shadow-sm" data-bs-toggle="modal"
                        data-bs-target="#modalTambah">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Property Baru
                    </button>
                @endif
            </div>

            <div class="card-body p-0">
                <div class="table-responsive-xl" style="padding-bottom: 120px; min-height: 350px;">
                    <!-- TABEL DENGAN 6 KOLOM (Status & Tagihan Digabung) -->
                    <table class="table table-hover align-middle text-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">ID / Penyewa</th>
                                <th>Kontak WhatsApp</th>
                                <th>Ukuran Properti</th>
                                <th>Durasi & Harga</th>
                                <th>Status & Rincian Tagihan</th>
                                <th class="text-center pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dataProperty as $row)
                                @php
                                    $sisaTagihan = max(0, $row->total_pembayaran - $row->nominal_dp);

                                    $bgStatus = 'bg-success text-success fw-bold';
                                    if ($row->jenis_pembayaran == 'Cicilan') {
                                        $bgStatus = 'bg-warning text-dark fw-bold';
                                    }
                                    if ($row->jenis_pembayaran == 'Belum Bayar') {
                                        $bgStatus = 'bg-danger text-danger fw-bold';
                                    }
                                @endphp
                                <tr>
                                    <!-- 1. ID / Penyewa -->
                                    <td class="ps-4">
                                        <div class="badge bg-secondary mb-1">PROP-{{ sprintf('%03d', $row->id) }}</div>
                                        <div class="fw-bold text-dark">{{ $row->nama_penyewa }}</div>
                                    </td>

                                    <!-- 2. Kontak WhatsApp -->
                                    <td>
                                        @if ($row->no_whatsapp)
                                            <a href="#" class="text-success text-decoration-none btn-wa"
                                                data-phone="{{ $row->no_whatsapp }}" data-nama="{{ $row->nama_penyewa }}"
                                                data-spek="{{ $row->panjang }}m x {{ $row->lebar }}m"
                                                data-durasi="{{ $row->durasi_bulan }}"
                                                data-total="Rp {{ number_format($row->total_pembayaran, 0, ',', '.') }}"
                                                data-bayar="Rp {{ number_format($row->nominal_dp, 0, ',', '.') }}"
                                                data-sisa="Rp {{ number_format($sisaTagihan, 0, ',', '.') }}"
                                                data-nota="{{ url('/data-property/cetak/' . $row->id) }}">
                                                <i class="bi bi-whatsapp"></i> {{ $row->no_whatsapp }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    <!-- 3. Ukuran Properti -->
                                    <td>
                                        <div class="text-primary fw-bold text-xs">
                                            <i class="bi bi-rulers"></i> {{ $row->panjang }}m × {{ $row->lebar }}m
                                        </div>
                                        <div class="small text-muted">Luas: {{ $row->panjang * $row->lebar }} m²</div>
                                    </td>

                                    <!-- 4. Durasi & Harga -->
                                    <td>
                                        <div class="fw-bold text-dark small">{{ $row->durasi_bulan }} Bulan</div>
                                        <div class="small text-muted">Rp
                                            {{ number_format($row->harga_per_tahun, 0, ',', '.') }}/Thn</div>
                                    </td>

                                    <!-- 5. Status & Rincian Tagihan (Digabung agar rapat dan mudah dilihat) -->
                                    <td>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="badge {{ $bgStatus }} bg-opacity-10 border">
                                                {{ $row->jenis_pembayaran == 'Cash' ? 'Lunas (Cash)' : $row->jenis_pembayaran }}
                                            </span>
                                            @if ($sisaTagihan > 0)
                                                <span class="small fw-bold text-danger">Sisa: Rp
                                                    {{ number_format($sisaTagihan, 0, ',', '.') }}</span>
                                            @else
                                                <span class="small fw-bold text-success"><i class="bi bi-check-all"></i>
                                                    Lunas</span>
                                            @endif
                                        </div>
                                        <div class="small text-muted">
                                            Terbayar: Rp {{ number_format($row->nominal_dp, 0, ',', '.') }} <span
                                                class="text-dark">|</span> Total: Rp
                                            {{ number_format($row->total_pembayaran, 0, ',', '.') }}
                                        </div>
                                    </td>

                                    <!-- 6. Aksi -->
                                    <td class="text-center pe-4">
                                        @if (!strpos(strtolower(Auth::user()->email), 'viewer'))
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-secondary dropdown-toggle shadow-sm w-100"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-gear-fill me-1"></i> Opsi
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0"
                                                    style="min-width: 180px;">
                                                    <li>
                                                        <a class="dropdown-item fw-bold text-primary py-2"
                                                            href="{{ url('/data-property/cetak/' . $row->id) }}"
                                                            target="_blank">
                                                            <i class="bi bi-printer-fill me-2"></i> Cetak Nota
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <button class="dropdown-item fw-bold text-success py-2"
                                                            type="button" data-bs-toggle="modal"
                                                            data-bs-target="#modalBayar{{ $row->id }}">
                                                            <i class="bi bi-receipt-cutoff me-2"></i> Detail Cicilan
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button class="dropdown-item fw-bold text-dark py-2" type="button"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#modalEdit{{ $row->id }}">
                                                            <i class="bi bi-pencil-square me-2"></i> Edit Kontrak
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <form action="{{ url('/data-property/hapus/' . $row->id) }}"
                                                            method="POST" class="m-0 p-0">
                                                            @csrf @method('DELETE')
                                                            <button type="submit"
                                                                class="dropdown-item fw-bold text-danger py-2"
                                                                onclick="return confirm('Hapus data property ini beserta histori pembayarannya secara permanen?')">
                                                                <i class="bi bi-trash-fill me-2"></i> Hapus Kontrak
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="bi bi-folder2-open fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                        Belum ada data penyewa property.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($dataProperty->hasPages())
                <div class="card-footer bg-white border-top py-2 d-flex justify-content-center">
                    {{ $dataProperty->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- KUMPULAN SEMUA MODAL                                    -->
    <!-- ======================================================= -->

    @foreach ($dataProperty as $row)
        @php
            $sisaTagihan = max(0, $row->total_pembayaran - $row->nominal_dp);
            $histori = DB::table('property_payments')
                ->where('property_id', $row->id)
                ->orderBy('created_at', 'desc')
                ->get();
        @endphp

        <!-- MODAL PEMBAYARAN & HISTORI -->
        @if (!strpos(strtolower(Auth::user()->email), 'viewer'))
            <div class="modal fade" id="modalBayar{{ $row->id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 shadow text-start">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title fs-6"><i class="bi bi-cash-coin me-2"></i>Histori & Pembayaran Cicilan
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4 bg-light">
                            <!-- Ringkasan -->
                            <div class="bg-white p-3 rounded border mb-4 shadow-sm">
                                <div class="row text-center">
                                    <div class="col-4 border-end">
                                        <span class="text-muted d-block small">Total Tagihan</span>
                                        <span class="fw-bold">Rp
                                            {{ number_format($row->total_pembayaran, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="col-4 border-end">
                                        <span class="text-muted d-block small">Sudah Dibayar</span>
                                        <span class="fw-bold text-success">Rp
                                            {{ number_format($row->nominal_dp, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="col-4">
                                        <span class="text-muted d-block small">Sisa Kekurangan</span>
                                        <span class="fw-black text-danger fs-5">Rp
                                            {{ number_format($sisaTagihan, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>

                            @if ($sisaTagihan > 0)
                                <form action="{{ url('/data-property/bayar-cicilan/' . $row->id) }}" method="POST"
                                    class="mb-4 p-3 bg-success bg-opacity-10 border border-success rounded">
                                    @csrf
                                    <div class="row g-2 mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold text-success small">Tanggal Bayar</label>
                                            <input type="date" name="tanggal_bayar" class="form-control"
                                                value="{{ date('Y-m-d') }}" required>
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label fw-bold text-success small">Nominal Cicilan Baru
                                                (Rp)
                                            </label>
                                            <input type="number" name="nominal_bayar" class="form-control fw-bold"
                                                placeholder="Contoh: 5000000" required max="{{ $sisaTagihan }}">
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-success fw-bold w-100">Simpan Bayar</button>
                                </form>
                            @else
                                <div class="alert alert-success text-center fw-bold shadow-sm mb-4">
                                    <i class="bi bi-check-circle-fill me-2 fs-5"></i> Kontrak Properti Ini Sudah Lunas
                                </div>
                            @endif

                            <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-clock-history me-1"></i> Jejak
                                Perekaman Pembayaran</h6>
                            @if ($histori->count() > 0)
                                <div class="table-responsive bg-white rounded border">
                                    <table class="table table-sm table-hover text-center align-middle mb-0"
                                        style="font-size: 0.85rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Waktu Transaksi</th>
                                                <th>Nominal Masuk</th>
                                                <th>Admin Pencatat</th>
                                                <th>Batal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($histori as $h)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($h->created_at)->format('d M Y') }}
                                                    </td>
                                                    <td class="text-success fw-bold">Rp
                                                        {{ number_format($h->nominal_bayar, 0, ',', '.') }}</td>
                                                    <td><span class="badge bg-secondary"><i class="bi bi-person-fill"></i>
                                                            {{ $h->admin_name }}</span></td>
                                                    <td>
                                                        <form action="{{ url('/data-property/hapus-cicilan/' . $h->id) }}"
                                                            method="POST" style="display:inline;">
                                                            @csrf @method('DELETE')
                                                            <button type="submit"
                                                                class="btn btn-sm btn-outline-danger py-0 px-2"
                                                                onclick="return confirm('Hapus histori ini? (Total tagihan akan disesuaikan kembali)')"
                                                                title="Batalkan Transaksi">
                                                                <i class="bi bi-x-lg"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center text-muted p-3 bg-white border rounded small">Belum ada jejak
                                    cicilan tercatat.</div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup
                                Keluar</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- MODAL EDIT PROPERTY -->
        @if (!strpos(strtolower(Auth::user()->email), 'viewer'))
            <div class="modal fade" id="modalEdit{{ $row->id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <form action="{{ url('/data-property/update/' . $row->id) }}" method="POST"
                        class="modal-content border-0 shadow text-start">
                        @csrf
                        <div class="modal-header bg-warning">
                            <h5 class="modal-title fs-6"><i class="bi bi-pencil-square me-2"></i>Edit Data Property</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4 bg-light">

                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small">Nama Penyewa</label>
                                    <input type="text" name="nama_penyewa" class="form-control"
                                        value="{{ $row->nama_penyewa }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-success"><i class="bi bi-whatsapp"></i>
                                        No. WhatsApp</label>
                                    <input type="text" name="no_whatsapp" class="form-control"
                                        value="{{ $row->no_whatsapp }}" placeholder="Contoh: 08123456789">
                                </div>
                            </div>

                            <div class="row g-4 mb-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small">Ukuran (P x L)</label>
                                    <div class="input-group">
                                        <input type="number" step="0.1" name="panjang"
                                            class="form-control calc-trigger-edit-{{ $row->id }}" placeholder="P"
                                            value="{{ $row->panjang }}" required>
                                        <span
                                            class="input-group-text bg-white border-start-0 border-end-0 text-muted">x</span>
                                        <input type="number" step="0.1" name="lebar"
                                            class="form-control calc-trigger-edit-{{ $row->id }} border-start-0"
                                            placeholder="L" value="{{ $row->lebar }}" required>
                                        <span class="input-group-text bg-light">m</span>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-bold small">Harga / Tahun (Rp)</label>
                                    <input type="number" name="harga_per_tahun"
                                        class="form-control calc-trigger-edit-{{ $row->id }}"
                                        value="{{ $row->harga_per_tahun }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold small">Durasi Sewa</label>
                                    <div class="input-group">
                                        <input type="number" name="durasi_bulan"
                                            class="form-control calc-trigger-edit-{{ $row->id }}"
                                            value="{{ $row->durasi_bulan }}" required>
                                        <span class="input-group-text bg-light">Bln</span>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white border rounded p-3 mb-4">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Jenis Pembayaran</label>
                                        <select name="jenis_pembayaran"
                                            class="form-select border-primary jenis-bayar-edit-{{ $row->id }}"
                                            required>
                                            <option value="Cash"
                                                {{ $row->jenis_pembayaran == 'Cash' ? 'selected' : '' }}>Cash</option>
                                            <option value="Cicilan"
                                                {{ $row->jenis_pembayaran == 'Cicilan' ? 'selected' : '' }}>Cicilan
                                            </option>
                                            <option value="Belum Bayar"
                                                {{ $row->jenis_pembayaran == 'Belum Bayar' ? 'selected' : '' }}>Belum Bayar
                                            </option>
                                        </select>
                                    </div>
                                    <div
                                        class="col-md-4 dp-container-edit-{{ $row->id }} {{ $row->jenis_pembayaran == 'Belum Bayar' ? 'd-none' : '' }}">
                                        <label class="form-label fw-bold small text-info">Total Telah Dibayar (Rp)</label>
                                        <input type="number" name="nominal_dp"
                                            class="form-control dp-trigger-edit-{{ $row->id }}"
                                            value="{{ $row->nominal_dp }}">
                                    </div>
                                    <div
                                        class="col-md-4 dp-container-edit-{{ $row->id }} {{ $row->jenis_pembayaran == 'Belum Bayar' ? 'd-none' : '' }}">
                                        <label class="form-label fw-bold small text-info">Metode Bayar Akhir</label>
                                        <select name="metode_dp" class="form-select dp-method-edit-{{ $row->id }}">
                                            <option value="Cash" {{ $row->metode_dp == 'Cash' ? 'selected' : '' }}>Cash
                                            </option>
                                            <option value="Transfer"
                                                {{ $row->metode_dp == 'Transfer' ? 'selected' : '' }}>Transfer Bank
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">Catatan</label>
                                <textarea name="catatan" class="form-control" rows="2">{{ $row->catatan }}</textarea>
                            </div>

                            <div class="bg-white p-3 rounded border border-danger text-end">
                                <span class="text-muted small">Total Tagihan Terkalkulasi:</span>
                                <input type="hidden" name="total_pembayaran"
                                    class="total-input-edit-{{ $row->id }}" value="{{ $row->total_pembayaran }}">
                                <h4 class="fw-black text-danger mb-0 total-display-edit-{{ $row->id }}">Rp
                                    {{ number_format($row->total_pembayaran, 0, ',', '.') }}</h4>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary px-4 fw-bold">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endforeach

    <!-- MODAL TAMBAH PROPERTY -->
    <div class="modal fade" id="modalTambah" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form action="{{ url('/data-property/simpan') }}" method="POST" class="modal-content border-0 shadow">
                @csrf
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fs-6"><i class="bi bi-plus-circle me-2"></i>Tambah Property Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-light">

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Nama Penyewa</label>
                            <input type="text" name="nama_penyewa" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-success"><i class="bi bi-whatsapp"></i> No.
                                WhatsApp</label>
                            <input type="text" name="no_whatsapp" class="form-control"
                                placeholder="Contoh: 08123456789">
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small">Ukuran (P x L)</label>
                            <div class="input-group">
                                <input type="number" step="0.1" name="panjang" class="form-control"
                                    placeholder="P" value="0" required>
                                <span class="input-group-text bg-white border-start-0 border-end-0 text-muted">x</span>
                                <input type="number" step="0.1" name="lebar" class="form-control border-start-0"
                                    placeholder="L" value="0" required>
                                <span class="input-group-text bg-light">m</span>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold small">Harga / Tahun (Rp)</label>
                            <input type="number" name="harga_per_tahun" id="addHarga"
                                class="form-control calc-trigger-add" value="0" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Durasi Sewa</label>
                            <div class="input-group">
                                <input type="number" name="durasi_bulan" id="addBulan"
                                    class="form-control calc-trigger-add" value="12" required>
                                <span class="input-group-text bg-light">Bln</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border rounded p-3 mb-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Jenis Pembayaran</label>
                                <select name="jenis_pembayaran" id="addJenisBayar" class="form-select border-primary"
                                    required>
                                    <option value="Cash">Cash (Sekaligus)</option>
                                    <option value="Cicilan">Dicicil</option>
                                    <option value="Belum Bayar">Belum Bayar</option>
                                </select>
                            </div>
                            <div class="col-md-4 add-dp-container">
                                <label class="form-label fw-bold small text-info">Nominal DP (Jika Ada)</label>
                                <input type="number" name="nominal_dp" id="addDP" class="form-control"
                                    value="0">
                            </div>
                            <div class="col-md-4 add-dp-container">
                                <label class="form-label fw-bold small text-info">Metode Bayar DP</label>
                                <select name="metode_dp" id="addMetodeDP" class="form-select text-muted" disabled>
                                    <option value="Cash">Cash</option>
                                    <option value="Transfer">Transfer Bank</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small">Catatan Penting</label>
                        <textarea name="catatan" class="form-control" rows="2" placeholder="Ketentuan khusus atau kesepakatan..."></textarea>
                    </div>

                </div>
                <div class="modal-footer bg-white d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small d-block">Total Tagihan Otomatis:</span>
                        <input type="hidden" name="total_pembayaran" id="addTotalInput" value="0">
                        <h3 class="fw-black text-danger mb-0" id="addTotalDisplay">Rp 0</h3>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">Simpan Data</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Kalkulasi Modal Tambah
            const addTriggers = document.querySelectorAll('.calc-trigger-add');
            addTriggers.forEach(input => input.addEventListener('input', hitungTotalAdd));

            function hitungTotalAdd() {
                let hargaThn = parseFloat(document.getElementById('addHarga').value) || 0;
                let bulan = parseFloat(document.getElementById('addBulan').value) || 0;

                let total = hargaThn * (bulan / 12);

                document.getElementById('addTotalInput').value = total;
                document.getElementById('addTotalDisplay').innerText = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    maximumFractionDigits: 0
                }).format(total);
            }

            // Toggle Disable/Enable Metode DP (Tambah)
            let addDPInput = document.getElementById('addDP');
            addDPInput.addEventListener('input', function() {
                let metode = document.getElementById('addMetodeDP');
                if (this.value > 0) {
                    metode.removeAttribute('disabled');
                    metode.classList.remove('text-muted');
                } else {
                    metode.setAttribute('disabled', 'true');
                    metode.classList.add('text-muted');
                }
            });

            // Sembunyikan DP jika Belum Bayar (Tambah)
            document.getElementById('addJenisBayar').addEventListener('change', function() {
                let dpContainers = document.querySelectorAll('.add-dp-container');
                if (this.value === 'Belum Bayar') {
                    dpContainers.forEach(el => el.classList.add('d-none'));
                    addDPInput.value = 0;
                    addDPInput.dispatchEvent(new Event('input'));
                } else {
                    dpContainers.forEach(el => el.classList.remove('d-none'));
                }
            });

            // Kalkulasi Dinamis untuk Semua Modal Edit
            @foreach ($dataProperty as $row)
                const editTriggers{{ $row->id }} = document.querySelectorAll(
                    '.calc-trigger-edit-{{ $row->id }}');
                editTriggers{{ $row->id }}.forEach(input => {
                    input.addEventListener('input', function() {
                        let inputs = document.querySelectorAll(
                            '.calc-trigger-edit-{{ $row->id }}');
                        let hargaThn = parseFloat(inputs[2].value) || 0;
                        let bulan = parseFloat(inputs[3].value) || 0;

                        let total = hargaThn * (bulan / 12);

                        document.querySelector('.total-input-edit-{{ $row->id }}').value =
                            total;
                        document.querySelector('.total-display-edit-{{ $row->id }}')
                            .innerText = new Intl.NumberFormat('id-ID', {
                                style: 'currency',
                                currency: 'IDR',
                                maximumFractionDigits: 0
                            }).format(total);
                    });
                });

                let dpInputEdit{{ $row->id }} = document.querySelector(
                    '.dp-trigger-edit-{{ $row->id }}');
                dpInputEdit{{ $row->id }}.addEventListener('input', function() {
                    let metode = document.querySelector('.dp-method-edit-{{ $row->id }}');
                    if (this.value > 0) {
                        metode.removeAttribute('disabled');
                        metode.classList.remove('text-muted');
                    } else {
                        metode.setAttribute('disabled', 'true');
                        metode.classList.add('text-muted');
                    }
                });

                if (dpInputEdit{{ $row->id }}.value <= 0) {
                    document.querySelector('.dp-method-edit-{{ $row->id }}').setAttribute('disabled', 'true');
                }

                document.querySelector('.jenis-bayar-edit-{{ $row->id }}').addEventListener('change',
                    function() {
                        let dpContainers = document.querySelectorAll('.dp-container-edit-{{ $row->id }}');
                        if (this.value === 'Belum Bayar') {
                            dpContainers.forEach(el => el.classList.add('d-none'));
                            dpInputEdit{{ $row->id }}.value = 0;
                            dpInputEdit{{ $row->id }}.dispatchEvent(new Event('input'));
                        } else {
                            dpContainers.forEach(el => el.classList.remove('d-none'));
                        }
                    });
            @endforeach

            // LOGIKA PESAN WHATSAPP OTOMATIS
            document.querySelectorAll('.btn-wa').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();

                    const phone = this.getAttribute('data-phone');
                    const nama = this.getAttribute('data-nama');
                    const spek = this.getAttribute('data-spek');
                    const durasi = this.getAttribute('data-durasi');
                    const total = this.getAttribute('data-total');
                    const bayar = this.getAttribute('data-bayar');
                    const sisa = this.getAttribute('data-sisa');
                    const nota = this.getAttribute('data-nota');

                    let hp = phone.replace(/\D/g, '');
                    if (hp.startsWith('0')) {
                        hp = '62' + hp.substring(1);
                    } else if (!hp.startsWith('62')) {
                        hp = '62' + hp;
                    }

                    let pesan = "Halo Bapak/Ibu *" + nama + "*,\n\n";
                    pesan +=
                        "Berikut adalah informasi tagihan kontrak properti Anda di *Pande Mesari*:\n\n";
                    pesan += "- *Spesifikasi:* " + spek + "\n";
                    pesan += "- *Durasi Sewa:* " + durasi + " Bulan\n";
                    pesan += "- *Total Kontrak:* " + total + "\n";
                    pesan += "- *Telah Dibayar:* " + bayar + "\n";
                    pesan += "- *Sisa Tagihan:* *" + sisa + "*\n\n";
                    pesan +=
                        "\u{1F4C4} *Kwitansi / Nota Digital* Anda dapat dilihat dan dicetak melalui tautan berikut:\n";
                    pesan += nota + "\n\n";
                    pesan += "Terima kasih. \u{1F64F}";

                    const url = "https://wa.me/" + hp + "?text=" + encodeURIComponent(pesan);
                    window.open(url, "_blank");
                });
            });
        });
    </script>
@endsection
