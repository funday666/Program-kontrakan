@extends('layouts.app')

@section('content')
    <style>
        @media (min-width: 768px) {
            .table-responsive {
                overflow: visible !important;
            }
        }
    </style>

    <div class="container-fluid">
        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center mb-4 gap-3">
            <div>
                <h2 class="fw-bold mb-1 fs-3">Manajemen Transaksi Penyewaan</h2>
                <p class="text-muted mb-0 small">Kelola kontrak sewa lahan, pembayaran cicilan, dan laporan data penyewa.</p>
            </div>

            <div class="d-flex flex-column flex-md-row gap-2 align-items-stretch align-items-md-center">
                <form action="{{ url('/sewa-lahan') }}" method="GET" class="d-flex flex-column flex-sm-row gap-2 mb-0">
                    <input type="text" name="search" class="form-control" placeholder="Cari nama penyewa..."
                        value="{{ request('search') }}">

                    <select name="status" class="form-select" style="min-width: 130px;">
                        <option value="">Status Bayar</option>
                        <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                        <option value="mencicil" {{ request('status') == 'mencicil' ? 'selected' : '' }}>Mencicil</option>
                        <option value="belum_bayar" {{ request('status') == 'belum_bayar' ? 'selected' : '' }}>Belum Bayar
                        </option>
                    </select>

                    <select name="waktu" class="form-select" style="min-width: 170px;">
                        <option value="">Semua Waktu</option>
                        <option value="hampir_habis" {{ request('waktu') == 'hampir_habis' ? 'selected' : '' }}>⏳ Hampir
                            Habis (≤ 30 Hari)</option>
                        <option value="habis" {{ request('waktu') == 'habis' ? 'selected' : '' }}>❌ Sudah Habis</option>
                    </select>

                    <button type="submit" class="btn btn-outline-primary px-3">Filter</button>

                    @if (request('search') || request('status') || request('waktu'))
                        <a href="{{ url('/sewa-lahan') }}" class="btn btn-outline-secondary" title="Reset Filter">Reset</a>
                    @endif
                </form>

                <div class="d-flex gap-2 mt-2 mt-md-0">
                    <a href="{{ url('/sewa-lahan/export-excel') }}"
                        class="btn btn-success flex-grow-1 d-flex align-items-center justify-content-center px-3 shadow-sm"
                        title="Unduh Excel">
                        <i class="bi bi-file-earmark-excel-fill me-1"></i> <span class="d-none d-sm-inline">Excel</span>
                    </a>
                    <button
                        class="btn btn-primary flex-grow-1 d-flex align-items-center justify-content-center px-3 shadow-sm"
                        data-bs-toggle="modal" data-bs-target="#addRentalModal">
                        <i class="bi bi-plus-circle-fill me-1"></i> Kontrak Baru
                    </button>
                </div>
            </div>
        </div>

        @if (session('sukses'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('sukses') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-nowrap mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th width="60" class="ps-3">No</th>
                                <th>Nama Penyewa</th>
                                <th>No. Telepon</th>
                                <th>Luas Lahan</th>
                                <th>Total & Sisa Tagihan</th>
                                <th>Sisa Waktu Sewa</th>
                                <th>Status Bayar</th>
                                <th class="text-center pe-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dataRentals as $index => $row)
                                @php
                                    // KALKULASI SISA WAKTU KONTRAK
                                    $tanggalMulai = \Carbon\Carbon::parse($row->created_at)->startOfDay();
                                    $tanggalHabis = $tanggalMulai
                                        ->copy()
                                        ->addMonths($row->contract_duration_months)
                                        ->startOfDay();
                                    $hariIni = \Carbon\Carbon::now()->startOfDay();

                                    if ($hariIni->greaterThanOrEqualTo($tanggalHabis)) {
                                        $sisaBulan = 0;
                                        $sisaHari = 0;
                                        $teksSisaWaktu = 'Sudah Habis Kontrak';
                                    } else {
                                        $sisaBulan = (int) $hariIni->diffInMonths($tanggalHabis);
                                        $sisaHari = (int) $hariIni
                                            ->copy()
                                            ->addMonths($sisaBulan)
                                            ->diffInDays($tanggalHabis);

                                        if ($sisaBulan == 0) {
                                            $teksSisaWaktu = $sisaHari . ' Hari Lagi';
                                        } else {
                                            $teksSisaWaktu = $sisaBulan . ' Bulan, ' . $sisaHari . ' Hari';
                                        }
                                    }
                                @endphp

                                <tr>
                                    <td class="ps-3 text-muted">{{ $dataRentals->firstItem() + $index }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $row->tenant_name }}</div>
                                        <small class="text-muted" style="font-size: 11px;">Mulai:
                                            {{ date('d-m-Y', strtotime($row->created_at)) }}</small>
                                    </td>
                                    <td>
                                        <a href="#"
                                            class="text-decoration-none text-dark d-flex align-items-center btn-wa"
                                            data-phone="{{ $row->tenant_phone }}" data-nama="{{ $row->tenant_name }}"
                                            data-durasi="{{ (int) $row->contract_duration_months }}"
                                            data-sisa="{{ $teksSisaWaktu }}"
                                            data-nota="{{ url('/sewa-lahan/cetak-nota/' . $row->id) }}?t={{ time() }}"
                                            title="Kirim Pesan WA">
                                            <i class="bi bi-whatsapp text-success me-2"></i>{{ $row->tenant_phone }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ $row->rented_length }} x {{ $row->rented_width }}
                                            m</span>
                                        <div class="text-muted small" style="font-size: 11px;">Luas:
                                            {{ $row->rented_length * $row->rented_width }} m²</div>
                                    </td>

                                    <td>
                                        <div class="fw-bold text-dark mb-1" style="font-size: 13px;">
                                            <span class="text-muted fw-normal">Total:</span> Rp
                                            {{ number_format($row->total_price, 0, ',', '.') }}
                                        </div>

                                        @if ($row->sisa_pembayaran > 0)
                                            <div class="fw-bold text-danger mb-1" style="font-size: 13px;">
                                                <span class="text-muted fw-normal">Sisa:</span> Rp
                                                {{ number_format($row->sisa_pembayaran, 0, ',', '.') }}
                                            </div>
                                        @else
                                            <div class="fw-bold text-success mb-1" style="font-size: 13px;">
                                                <span class="text-muted fw-normal">Sisa:</span> Rp 0 (Lunas)
                                            </div>
                                        @endif

                                        <div class="text-muted fw-normal" style="font-size: 11px;">
                                            <i class="bi bi-calendar-event me-1"></i>Durasi:
                                            {{ $row->contract_duration_months }} Bulan
                                        </div>
                                    </td>

                                    <td>
                                        @if ($hariIni->greaterThanOrEqualTo($tanggalHabis))
                                            <span class="badge bg-danger rounded-pill px-2 py-1 small">
                                                <i class="bi bi-exclamation-octagon-fill me-1"></i>Habis Kontrak
                                            </span>
                                        @elseif($sisaBulan == 0)
                                            <span class="badge bg-warning text-dark rounded-pill px-2 py-1 small">
                                                <i class="bi bi-clock-history me-1"></i>{{ (int) $sisaHari }} Hari Lagi
                                            </span>
                                        @else
                                            <span class="badge bg-info text-dark rounded-pill px-2 py-1 small">
                                                <i class="bi bi-calendar3 me-1"></i>{{ (int) $sisaBulan }} Bln,
                                                {{ (int) $sisaHari }} Hari
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        @if ($row->status_tampilan == 'lunas')
                                            <span class="badge bg-success rounded-pill px-3 py-2">Lunas</span>
                                        @elseif($row->status_tampilan == 'mencicil')
                                            <span class="badge bg-warning text-dark rounded-pill px-3 py-2">Mencicil</span>
                                        @else
                                            <span class="badge bg-danger rounded-pill px-3 py-2">Belum Bayar</span>
                                        @endif
                                    </td>

                                    <td class="text-center pe-3">
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle p-2 shadow-sm"
                                                type="button" id="actionMenu{{ $row->id }}" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                <i class="bi bi-gear-fill me-1"></i> Opsi
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow"
                                                aria-labelledby="actionMenu{{ $row->id }}">
                                                <li>
                                                    <a class="dropdown-item py-2 text-primary fw-bold"
                                                        href="{{ url('/sewa-lahan/cetak-nota/' . $row->id) }}?t={{ time() }}"
                                                        target="_blank">
                                                        <i class="bi bi-printer-fill me-2"></i> Cetak Nota
                                                    </a>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item py-2 text-success fw-bold" type="button"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#paymentModal{{ $row->id }}">
                                                        <i class="bi bi-receipt me-2"></i> Detail Cicilan
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item py-2 text-warning text-dark fw-bold"
                                                        type="button" data-bs-toggle="modal"
                                                        data-bs-target="#editRentalModal{{ $row->id }}">
                                                        <i class="bi bi-pencil-square me-2"></i> Edit Kontrak
                                                    </button>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li>
                                                    <a class="dropdown-item py-2 text-danger fw-bold"
                                                        href="{{ url('/sewa-lahan/hapus/' . $row->id) }}"
                                                        onclick="return confirm('Hapus transaksi atas nama {{ $row->tenant_name }}?')">
                                                        <i class="bi bi-trash3-fill me-2"></i> Hapus Kontrak
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">
                                        <i class="bi bi-folder-x fs-1 d-block mb-2"></i> Belum ada data transaksi sewa
                                        lahan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-white border-top py-3">
                <div class="d-flex justify-content-center">
                    {{ $dataRentals->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>

    @foreach ($dataRentals as $row)
        @php
            $totalBayar = $paymentDetails->where('rental_id', $row->id)->sum('amount_paid');
            $sisaTagihan = $row->total_price - $totalBayar;
        @endphp

        <div class="modal fade" id="paymentModal{{ $row->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-3">
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title fs-6"><i class="bi bi-cash-coin me-2"></i>Manajemen Cicilan -
                            {{ $row->tenant_name }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-3 p-md-4">
                        <div class="row g-2 mb-4">
                            <div class="col-12 col-md-4">
                                <div class="bg-light p-3 rounded-3 border-start border-primary border-4">
                                    <div class="small text-muted mb-1" style="font-size: 11px;">TOTAL TAGIHAN</div>
                                    <div class="fs-5 fw-bold text-dark">Rp
                                        {{ number_format($row->total_price, 0, ',', '.') }}</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="bg-light p-3 rounded-3 border-start border-success border-4">
                                    <div class="small text-muted mb-1" style="font-size: 11px;">SUDAH DIBAYAR</div>
                                    <div class="fs-5 fw-bold text-success">Rp
                                        {{ number_format($totalBayar, 0, ',', '.') }}</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="bg-light p-3 rounded-3 border-start border-danger border-4">
                                    <div class="small text-muted mb-1" style="font-size: 11px;">SISA PEMBAYARAN</div>
                                    <div class="fs-5 fw-bold text-danger">Rp
                                        {{ number_format($sisaTagihan, 0, ',', '.') }}</div>
                                </div>
                            </div>
                        </div>

                        <form action="{{ url('/sewa-lahan/simpan-cicilan/' . $row->id) }}" method="POST"
                            class="mb-4 bg-light p-3 rounded-3 border">
                            @csrf
                            <h6 class="fw-bold mb-3 small"><i class="bi bi-plus-circle me-1"></i>Input Setoran Baru</h6>
                            <div class="row g-2">
                                <div class="col-12 col-sm-6 col-md-3">
                                    <input type="date" name="payment_date" class="form-control"
                                        value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-12 col-sm-6 col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" name="amount_paid" class="form-control"
                                            max="{{ $sisaTagihan }}" placeholder="Nominal" required>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3">
                                    <select name="payment_method" class="form-select" required>
                                        <option value="Cash">Tunai / Cash</option>
                                        <option value="Transfer">Transfer Bank</option>
                                    </select>
                                </div>
                                <div class="col-12 col-sm-6 col-md-2">
                                    <button type="submit" class="btn btn-success w-100">Simpan</button>
                                </div>
                            </div>
                        </form>

                        <h6 class="fw-bold mb-2 small"><i class="bi bi-clock-history me-1"></i>Riwayat Histori Setoran
                        </h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle text-nowrap mb-0 text-center small">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">No</th>
                                        <th>Tanggal Bayar</th>
                                        <th>Metode</th>
                                        <th>Nominal Setoran</th>
                                        <th>Dicatat Oleh</th>
                                        <th width="80">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $noCicilan = 1;
                                        $histori = $paymentDetails->where('rental_id', $row->id);
                                    @endphp
                                    @forelse($histori as $pay)
                                        <tr>
                                            <td>{{ $noCicilan++ }}</td>
                                            <td>{{ date('d-m-Y', strtotime($pay->payment_date)) }}</td>
                                            <td>
                                                <span
                                                    class="badge {{ strtolower($pay->payment_method) == 'transfer' ? 'bg-primary' : 'bg-success' }}">
                                                    {{ $pay->payment_method ?? 'Cash' }}
                                                </span>
                                            </td>
                                            <td class="text-success fw-bold text-end pe-3">Rp
                                                {{ number_format($pay->amount_paid, 0, ',', '.') }}</td>
                                            <td>{{ $pay->created_by ?? 'Admin' }}</td>
                                            <td>
                                                <form id="delete-cicilan-{{ $pay->id }}"
                                                    action="{{ url('/sewa-lahan/hapus-cicilan/' . $pay->id) }}"
                                                    method="POST" class="d-none">
                                                    @csrf @method('DELETE')
                                                </form>
                                                <button type="button"
                                                    class="btn btn-sm text-danger text-decoration-none p-0 border-0 bg-transparent"
                                                    onclick="if(confirm('Hapus histori setoran cicilan ini?')) { event.preventDefault(); document.getElementById('delete-cicilan-{{ $pay->id }}').submit(); }">
                                                    <i class="bi bi-trash-fill"></i> Hapus
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-muted py-3">Belum ada cicilan yang masuk.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="editRentalModal{{ $row->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-3">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title fs-6 text-dark"><i class="bi bi-pencil-square me-2"></i>Edit Data Kontrak
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ url('/sewa-lahan/update/' . $row->id) }}" method="POST">
                        @csrf
                        <div class="modal-body p-3 p-md-4 text-start">

                            <div class="row g-3 mb-3 pb-3 border-bottom">
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-bold text-primary"><i
                                            class="bi bi-calendar-event me-1"></i>Tanggal Mulai Kontrak</label>
                                    <input type="date" name="start_date" class="form-control border-primary"
                                        value="{{ \Carbon\Carbon::parse($row->created_at)->format('Y-m-d') }}" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-bold">ID Kontrak</label>
                                    <input type="text" class="form-control bg-light"
                                        value="PM-{{ sprintf('%05d', $row->id) }}" readonly>
                                </div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-bold">Nama Lengkap Penyewa</label>
                                    <input type="text" name="tenant_name" class="form-control"
                                        value="{{ $row->tenant_name }}" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-bold">No. WA</label>
                                    <input type="text" name="tenant_phone" class="form-control"
                                        value="{{ $row->tenant_phone }}" required>
                                </div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-bold">Panjang Lahan (m)</label>
                                    <input type="number" step="0.1" name="rented_length" class="form-control"
                                        value="{{ $row->rented_length }}" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-bold">Lebar Lahan (m)</label>
                                    <input type="number" step="0.1" name="rented_width" class="form-control"
                                        value="{{ $row->rented_width }}" required>
                                </div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-bold">Durasi Sewa (Bulan)</label>
                                    <input type="number" name="contract_duration_months" class="form-control"
                                        value="{{ $row->contract_duration_months }}" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-bold">Harga per m² / Tahun</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" name="price_per_meter_year" class="form-control"
                                            value="{{ $row->price_per_meter_year }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label small fw-bold">Status Pembayaran Saat Ini</label>
                                    <select name="payment_status" class="form-select bg-light" disabled>
                                        <option value="belum_bayar"
                                            {{ $row->payment_status == 'belum_bayar' ? 'selected' : '' }}>Belum Bayar
                                        </option>
                                        <option value="mencicil"
                                            {{ $row->payment_status == 'mencicil' ? 'selected' : '' }}>Mencicil</option>
                                        <option value="lunas" {{ $row->payment_status == 'lunas' ? 'selected' : '' }}>
                                            Lunas</option>
                                    </select>
                                    <input type="hidden" name="payment_status" value="{{ $row->payment_status }}">
                                    <small class="text-muted" style="font-size: 11px;">Status otomatis membaca histori
                                        cicilan, ubah transaksi di tabel cicilan jika salah.</small>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-light border-0">
                            <button type="button" class="btn btn-secondary w-100 w-md-auto mb-2 mb-md-0"
                                data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary w-100 w-md-auto px-4">Update Kontrak</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <div class="modal fade" id="addRentalModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-3">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fs-6"><i class="bi bi-plus-circle me-2"></i>Tambah Kontrak Sewa Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form action="{{ url('/sewa-lahan/simpan') }}" method="POST">
                    @csrf
                    <div class="modal-body p-3 p-md-4 text-start">
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">ID Kontrak</label>
                                <input type="text" class="form-control bg-light" value="AUTO (Dibuat Otomatis)"
                                    readonly>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">Tanggal Mulai Kontrak</label>
                                <input type="date" class="form-control" name="start_date" id="start_date" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">Nama Lengkap Penyewa</label>
                                <input type="text" name="tenant_name" class="form-control"
                                    placeholder="Contoh: Bpk Ahmad" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">Nomor Handphone (WA)</label>
                                <input type="text" name="tenant_phone" class="form-control"
                                    placeholder="Contoh: 081234..." required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">Panjang Lahan (m)</label>
                                <input type="number" step="any" name="rented_length" id="panjang"
                                    class="form-control hitung-otomatis" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">Lebar Lahan (m)</label>
                                <input type="number" step="any" name="rented_width" id="lebar"
                                    class="form-control hitung-otomatis" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">Durasi Sewa (Bulan)</label>
                                <input type="number" name="contract_duration_months" id="durasi"
                                    class="form-control hitung-otomatis hitung-tanggal" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">Harga per m² / Tahun</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="price_per_meter_year" id="harga"
                                        class="form-control hitung-otomatis" required>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-4 pb-3 border-bottom">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">Kontrak Berakhir</label>
                                <input type="date" class="form-control bg-light text-danger fw-bold" name="end_date"
                                    id="end_date" readonly>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold">Total Yang Harus Dibayarkan</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">Rp</span>
                                    <input type="text" class="form-control bg-light fw-bold text-success"
                                        id="total_bayar" value="0" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-primary">Status Awal Pembayaran</label>
                                <select name="payment_status" id="status_awal"
                                    class="form-select border-primary shadow-sm">
                                    <option value="belum_bayar">Belum Ada Pembayaran Sama Sekali</option>
                                    <option value="mencicil">Mencicil / Bayar Uang Muka (DP) Sekarang</option>
                                    <option value="lunas">Langsung Dibayar Lunas Penuh</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mt-1 bg-primary bg-opacity-10 p-3 rounded-3 mt-3" id="dynamic_payment_section"
                            style="display: none;">
                            <div class="col-12 col-md-6" id="dp_input_col">
                                <label class="form-label small fw-bold text-primary">Nominal Uang Muka (DP)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="initial_payment" id="initial_payment"
                                        class="form-control" placeholder="Contoh: 1000000">
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-bold text-primary">Uang Masuk Ke (Metode)</label>
                                <select name="initial_payment_method" class="form-select border-primary">
                                    <option value="Cash">Tunai / Cash (Uang Laci)</option>
                                    <option value="Transfer">Transfer Bank</option>
                                </select>
                            </div>
                            <div class="col-12" id="lunas_text_info" style="display: none;">
                                <small class="text-success fw-bold"><i class="bi bi-info-circle-fill me-1"></i>Sistem akan
                                    otomatis mencatat uang masuk lunas secara penuh.</small>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-secondary w-100 w-md-auto mb-2 mb-md-0"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary w-100 w-md-auto px-4">Simpan Kontrak</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Kalkulasi Total dan Tanggal
            const inputPanjang = document.getElementById('panjang');
            const inputLebar = document.getElementById('lebar');
            const inputDurasi = document.getElementById('durasi');
            const inputHarga = document.getElementById('harga');
            const displayTotal = document.getElementById('total_bayar');

            function hitungTotal() {
                const panjang = parseFloat(inputPanjang.value) || 0;
                const lebar = parseFloat(inputLebar.value) || 0;
                const durasiBulan = parseFloat(inputDurasi.value) || 0;
                const hargaPerMeter = parseFloat(inputHarga.value) || 0;
                const luas = panjang * lebar;
                const total = luas * (durasiBulan / 12) * hargaPerMeter;
                displayTotal.value = new Intl.NumberFormat('id-ID').format(total);
            }

            const inputsHitung = document.querySelectorAll('.hitung-otomatis');
            inputsHitung.forEach(input => {
                input.addEventListener('input', hitungTotal);
            });

            const inputMulai = document.getElementById('start_date');
            const inputAkhir = document.getElementById('end_date');

            function hitungTanggalAkhir() {
                const tglMulai = inputMulai.value;
                const durasiBulan = parseInt(inputDurasi.value) || 0;
                if (tglMulai && durasiBulan > 0) {
                    const date = new Date(tglMulai);
                    date.setMonth(date.getMonth() + durasiBulan);
                    const yyyy = date.getFullYear();
                    const mm = String(date.getMonth() + 1).padStart(2, '0');
                    const dd = String(date.getDate()).padStart(2, '0');
                    inputAkhir.value = `${yyyy}-${mm}-${dd}`;
                } else {
                    inputAkhir.value = '';
                }
            }

            inputMulai.addEventListener('change', hitungTanggalAkhir);
            inputDurasi.addEventListener('input', hitungTanggalAkhir);

            // LOGIKA SMART FORM PEMBAYARAN
            const selectStatus = document.getElementById('status_awal');
            const paymentSection = document.getElementById('dynamic_payment_section');
            const dpCol = document.getElementById('dp_input_col');
            const inputDp = document.getElementById('initial_payment');
            const lunasInfo = document.getElementById('lunas_text_info');

            selectStatus.addEventListener('change', function() {
                if (this.value === 'lunas') {
                    paymentSection.style.display = 'flex';
                    dpCol.style.display = 'none';
                    lunasInfo.style.display = 'block';
                    inputDp.required = false;
                } else if (this.value === 'mencicil') {
                    paymentSection.style.display = 'flex';
                    dpCol.style.display = 'block';
                    lunasInfo.style.display = 'none';
                    inputDp.required = true;
                } else {
                    paymentSection.style.display = 'none';
                    inputDp.required = false;
                }
            });

            // LOGIKA PESAN WHATSAPP OTOMATIS (BROWSER RENDERING ANTI-ERROR)
            document.querySelectorAll('.btn-wa').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();

                    const phone = this.getAttribute('data-phone');
                    const nama = this.getAttribute('data-nama');
                    const durasi = this.getAttribute('data-durasi');
                    const sisa = this.getAttribute('data-sisa');
                    const nota = this.getAttribute('data-nota');

                    let hp = phone.replace(/\D/g, '');
                    if (hp.startsWith('0')) {
                        hp = '62' + hp.substring(1);
                    } else if (!hp.startsWith('62')) {
                        hp = '62' + hp;
                    }

                    // Rakit pesan dengan Unicode ES6 murni untuk emotikon
                    let pesan = "Halo Bapak/Ibu *" + nama + "*,\n\n";
                    pesan += "Berikut adalah informasi terkait kontrak sewa lahan Anda:\n";
                    pesan += "- *Lama Kontrak:* " + durasi + " Bulan\n";
                    pesan += "- *Sisa Waktu:* " + sisa + "\n\n";
                    pesan +=
                        "\u{1F4C4} *Nota / Bukti Transaksi* dapat Anda lihat secara online pada link berikut:\n";
                    pesan += nota + "\n\n";
                    pesan += "Terima kasih. \u{1F64F}";

                    const url = "https://wa.me/" + hp + "?text=" + encodeURIComponent(pesan);
                    window.open(url, "_blank");
                });
            });
        });
    </script>
@endsection
