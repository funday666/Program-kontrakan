@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Header Halaman -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h2 class="fw-bold mb-0 fs-3"><i class="bi bi-map text-primary me-2"></i> Data Sewa Lahan</h2>
                <p class="text-muted mb-0 small">Kelola data penyewa lahan, durasi kontrak, pembayaran, dan laporan.</p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <form action="{{ url('/sewa-lahan') }}" method="GET"
                    class="d-flex gap-2 align-items-center bg-white p-2 rounded shadow-sm border border-2">
                    <select name="status" class="form-select form-select-sm border-0 bg-light" style="width: auto;">
                        <option value="">Semua Status</option>
                        <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                        <option value="mencicil" {{ request('status') == 'mencicil' ? 'selected' : '' }}>Mencicil</option>
                        <option value="belum_bayar" {{ request('status') == 'belum_bayar' ? 'selected' : '' }}>Belum Bayar
                        </option>
                    </select>

                    <select name="waktu" class="form-select form-select-sm border-0 bg-light" style="width: auto;">
                        <option value="">Semua Waktu</option>
                        <option value="hampir_habis" {{ request('waktu') == 'hampir_habis' ? 'selected' : '' }}>Hampir Habis
                            (30 Hari)</option>
                        <option value="habis" {{ request('waktu') == 'habis' ? 'selected' : '' }}>Sudah Habis</option>
                    </select>

                    <div class="input-group input-group-sm" style="width: auto; min-width: 180px;">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0"
                            placeholder="Cari penyewa..." value="{{ request('search') }}">
                    </div>

                    <button type="submit" class="btn btn-sm btn-primary px-3 fw-bold">Filter</button>

                    @if (request('search') || request('status') || request('waktu'))
                        <a href="{{ url('/sewa-lahan') }}" class="btn btn-sm btn-outline-danger"><i
                                class="bi bi-x-lg"></i></a>
                    @endif
                </form>

                <a href="{{ url('/sewa-lahan/export-excel') }}"
                    class="btn btn-success shadow-sm d-flex align-items-center fw-bold">
                    <i class="bi bi-file-earmark-excel me-2"></i> Export
                </a>
            </div>
        </div>

        <!-- Alert Notifikasi -->
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

        <!-- Card Tabel Data -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-secondary">Daftar Kontrak Lahan</h6>
                @if (!strpos(strtolower(Auth::user()->email), 'viewer'))
                    <button class="btn btn-sm btn-primary fw-bold shadow-sm" data-bs-toggle="modal"
                        data-bs-target="#modalTambah">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Sewa Baru
                    </button>
                @endif
            </div>

            <div class="card-body p-0">
                <div class="table-responsive-xl" style="padding-bottom: 120px; min-height: 350px;">
                    <!-- TABEL 8 KOLOM -->
                    <table class="table table-hover align-middle text-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">ID Kontrak</th>
                                <th>Nama Penyewa</th>
                                <th>No. Handphone</th>
                                <th>Ukuran Lahan</th>
                                <th>Masa Sewa</th>
                                <th>Total Tagihan</th>
                                <th>Status Pembayaran</th>
                                <th class="text-center pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dataRentals as $rental)
                                @php
                                    $tglMulai = \Carbon\Carbon::parse($rental->created_at);
                                    $tglBerakhir = $tglMulai->copy()->addMonths($rental->contract_duration_months);
                                    $sisaHari = now()->diffInDays($tglBerakhir, false);

                                    $bgStatus = 'bg-success text-success fw-bold';
                                    $iconStatus = 'bi-check-circle-fill';
                                    if ($rental->status_tampilan == 'mencicil') {
                                        $bgStatus = 'bg-warning text-dark fw-bold';
                                        $iconStatus = 'bi-hourglass-split';
                                    }
                                    if ($rental->status_tampilan == 'belum_bayar') {
                                        $bgStatus = 'bg-danger text-danger fw-bold';
                                        $iconStatus = 'bi-x-circle-fill';
                                    }
                                @endphp
                                <tr>
                                    <!-- 1. ID Kontrak -->
                                    <td class="ps-4">
                                        <div class="badge bg-secondary">PM-{{ sprintf('%05d', $rental->id) }}</div>
                                    </td>

                                    <!-- 2. Nama Penyewa -->
                                    <td class="fw-bold text-dark">
                                        {{ $rental->tenant_name }}
                                    </td>

                                    <!-- 3. No. Handphone -->
                                    <td>
                                        @if ($rental->tenant_phone)
                                            <a href="#" class="text-success text-decoration-none btn-wa"
                                                data-phone="{{ $rental->tenant_phone }}"
                                                data-nama="{{ $rental->tenant_name }}"
                                                data-spek="{{ rtrim(rtrim(number_format($rental->rented_length, 2, '.', ''), '0'), '.') }}m x {{ rtrim(rtrim(number_format($rental->rented_width, 2, '.', ''), '0'), '.') }}m"
                                                data-durasi="{{ $rental->contract_duration_months }}"
                                                data-total="Rp {{ number_format($rental->total_price, 0, ',', '.') }}"
                                                data-bayar="Rp {{ number_format($rental->total_dibayar, 0, ',', '.') }}"
                                                data-sisa="Rp {{ number_format($rental->sisa_pembayaran, 0, ',', '.') }}"
                                                data-nota="{{ url('/sewa-lahan/cetak-nota/' . $rental->id) }}">
                                                <i class="bi bi-whatsapp"></i> {{ $rental->tenant_phone }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>

                                    <!-- 4. Ukuran Lahan -->
                                    <td>
                                        <div class="text-primary fw-bold text-xs">
                                            {{ rtrim(rtrim(number_format($rental->rented_length, 2, '.', ''), '0'), '.') }}m
                                            ×
                                            {{ rtrim(rtrim(number_format($rental->rented_width, 2, '.', ''), '0'), '.') }}m
                                        </div>
                                        <div class="small text-muted">Luas:
                                            {{ rtrim(rtrim(number_format($rental->rented_length * $rental->rented_width, 2, '.', ''), '0'), '.') }}
                                            m²</div>
                                    </td>

                                    <!-- 5. Masa Sewa -->
                                    <td>
                                        <div class="fw-bold text-dark small">{{ $rental->contract_duration_months }} Bulan
                                        </div>
                                        <div class="small">
                                            @if ($sisaHari < 0)
                                                <span class="badge bg-danger">Habis:
                                                    {{ $tglBerakhir->format('d/m/Y') }}</span>
                                            @elseif($sisaHari <= 30)
                                                <span class="badge bg-warning text-dark">Sisa {{ ceil($sisaHari) }}
                                                    Hari</span>
                                            @else
                                                <span class="text-muted"><i class="bi bi-calendar-event"></i> S/d
                                                    {{ $tglBerakhir->format('d M Y') }}</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- 6. Total Tagihan -->
                                    <td>
                                        <div class="fw-bold text-dark">Rp
                                            {{ number_format($rental->total_price, 0, ',', '.') }}</div>
                                        <div class="text-muted" style="font-size: 0.7rem;">Rp
                                            {{ number_format($rental->price_per_meter_year, 0, ',', '.') }}/m²/Thn</div>
                                    </td>

                                    <!-- 7. Status Pembayaran -->
                                    <td>
                                        <span class="badge {{ $bgStatus }} bg-opacity-10 border mb-1">
                                            <i class="bi {{ $iconStatus }} me-1"></i>
                                            {{ $rental->status_tampilan == 'lunas' ? 'Lunas' : ($rental->status_tampilan == 'mencicil' ? 'Mencicil' : 'Belum Bayar') }}
                                        </span>
                                        @if ($rental->sisa_pembayaran > 0)
                                            <div class="small fw-bold text-danger">Sisa: Rp
                                                {{ number_format($rental->sisa_pembayaran, 0, ',', '.') }}</div>
                                        @else
                                            <div class="small fw-bold text-success"><i class="bi bi-check-all"></i> Terbayar
                                                Lunas</div>
                                        @endif
                                    </td>

                                    <!-- 8. Aksi -->
                                    <td class="text-center pe-4">
                                        @if (!strpos(strtolower(Auth::user()->email), 'viewer'))
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-secondary dropdown-toggle shadow-sm w-100"
                                                    type="button" data-bs-toggle="dropdown">
                                                    <i class="bi bi-gear-fill me-1"></i> Opsi
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0"
                                                    style="min-width: 180px;">
                                                    <li>
                                                        <a class="dropdown-item fw-bold text-primary py-2"
                                                            href="{{ url('/sewa-lahan/cetak-nota/' . $rental->id) }}"
                                                            target="_blank">
                                                            <i class="bi bi-printer-fill me-2"></i> Cetak Nota PDF
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <button class="dropdown-item fw-bold text-success py-2"
                                                            type="button" data-bs-toggle="modal"
                                                            data-bs-target="#modalCicilan{{ $rental->id }}">
                                                            <i class="bi bi-cash-coin me-2"></i> Histori Setoran
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button class="dropdown-item fw-bold text-dark py-2"
                                                            type="button" data-bs-toggle="modal"
                                                            data-bs-target="#modalEdit{{ $rental->id }}">
                                                            <i class="bi bi-pencil-square me-2"></i> Edit Kontrak
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    <li>
                                                        <form action="{{ url('/sewa-lahan/hapus/' . $rental->id) }}"
                                                            method="GET" class="m-0 p-0">
                                                            <button type="submit"
                                                                class="dropdown-item fw-bold text-danger py-2"
                                                                onclick="return confirm('Hapus seluruh data sewa ini beserta histori pembayarannya?')">
                                                                <i class="bi bi-trash-fill me-2"></i> Hapus Permanen
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
                                    <td colspan="8" class="text-center text-muted py-5">
                                        <i class="bi bi-folder2-open fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                        Belum ada data kontrak lahan yang dicatat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($dataRentals->hasPages())
                <div class="card-footer bg-white border-top py-2 d-flex justify-content-center">
                    {{ $dataRentals->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>


    <!-- ======================================================= -->
    <!-- KUMPULAN SEMUA MODAL EDIT & CICILAN                     -->
    <!-- Modal berada di luar struktur tabel agar UI tidak pecah -->
    <!-- ======================================================= -->
    @foreach ($dataRentals as $rental)
        @php
            $tglMulai = \Carbon\Carbon::parse($rental->created_at);
            $tglBerakhir = $tglMulai->copy()->addMonths($rental->contract_duration_months);
        @endphp

        <!-- MODAL EDIT KONTRAK -->
        <div class="modal fade form-modal" id="modalEdit{{ $rental->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <form action="{{ url('/sewa-lahan/update/' . $rental->id) }}" method="POST"
                    class="modal-content border-0 shadow">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title fs-6"><i class="bi bi-pencil-square me-2"></i> Edit Kontrak Sewa</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 bg-white">
                        <div class="row g-3">
                            <!-- Row 1 -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size: 13px;">ID Kontrak</label>
                                <input type="text" class="form-control bg-light"
                                    value="PM-{{ sprintf('%05d', $rental->id) }}" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size: 13px;">Tanggal Mulai Kontrak</label>
                                <input type="date" name="start_date" class="form-control calc-date"
                                    value="{{ date('Y-m-d', strtotime($rental->created_at)) }}" required>
                            </div>

                            <!-- Row 2 -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size: 13px;">Nama Lengkap Penyewa</label>
                                <input type="text" name="tenant_name" class="form-control"
                                    value="{{ $rental->tenant_name }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size: 13px;">Nomor Handphone (WA)</label>
                                <input type="text" name="tenant_phone" class="form-control"
                                    value="{{ $rental->tenant_phone }}">
                            </div>

                            <!-- Row 3 -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size: 13px;">Panjang Lahan (m)</label>
                                <input type="number" step="0.1" name="rented_length"
                                    class="form-control calc-panjang" value="{{ $rental->rented_length }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size: 13px;">Lebar Lahan (m)</label>
                                <input type="number" step="0.1" name="rented_width" class="form-control calc-lebar"
                                    value="{{ $rental->rented_width }}" required>
                            </div>

                            <!-- Row 4 -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size: 13px;">Durasi Sewa (Bulan)</label>
                                <input type="number" name="contract_duration_months"
                                    class="form-control calc-durasi calc-date"
                                    value="{{ $rental->contract_duration_months }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size: 13px;">Harga per m² / Tahun</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">Rp</span>
                                    <input type="number" name="price_per_meter_year"
                                        class="form-control border-start-0 calc-harga"
                                        value="{{ $rental->price_per_meter_year }}" required>
                                </div>
                            </div>

                            <!-- Row 5 -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size: 13px;">Kontrak Berakhir</label>
                                <input type="text" class="form-control fw-bold text-danger bg-light result-date"
                                    value="{{ $tglBerakhir->format('m/d/Y') }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size: 13px;">Total Yang Harus
                                    Dibayarkan</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">Rp</span>
                                    <input type="text"
                                        class="form-control border-start-0 fw-bold text-success bg-light result-total"
                                        value="{{ number_format($rental->total_price, 0, ',', '.') }}" readonly>
                                </div>
                            </div>

                            <div class="col-12">
                                <hr class="my-2">
                            </div>

                            <!-- Row 6 -->
                            <div class="col-12">
                                <label class="form-label fw-bold text-primary" style="font-size: 13px;">Status Pembayaran
                                    Terdata Saat Ini</label>
                                <select name="payment_status" class="form-select border-primary text-primary" required>
                                    <option value="belum_bayar"
                                        {{ $rental->payment_status == 'belum_bayar' ? 'selected' : '' }}>Belum Ada
                                        Pembayaran Sama Sekali</option>
                                    <option value="mencicil"
                                        {{ $rental->payment_status == 'mencicil' ? 'selected' : '' }}>Mencicil (Sebagian
                                        Terbayar)</option>
                                    <option value="lunas" {{ $rental->payment_status == 'lunas' ? 'selected' : '' }}>
                                        Lunas (Bayar Penuh)</option>
                                </select>
                                <div class="form-text small">*Mengubah status di sini tidak mempengaruhi nilai histori yang
                                    telah tercatat sebelumnya.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Tombol -->
                    <div class="modal-footer d-flex flex-column gap-2 border-0 px-4 pb-4 pt-1">
                        <button type="button" class="btn btn-secondary w-100 m-0 text-white fw-bold"
                            style="background-color: #6c757d;" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary w-100 m-0 fw-bold">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL SETORAN / HISTORI CICILAN -->
        <div class="modal fade" id="modalCicilan{{ $rental->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title fs-6"><i class="bi bi-cash-coin me-2"></i> Kelola Setoran Sewa</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 bg-light">
                        <div class="bg-white p-3 rounded border mb-4 shadow-sm text-center">
                            <div class="row">
                                <div class="col-4 border-end">
                                    <span class="text-muted d-block small">Total Tagihan</span>
                                    <span class="fw-bold">Rp {{ number_format($rental->total_price, 0, ',', '.') }}</span>
                                </div>
                                <div class="col-4 border-end">
                                    <span class="text-muted d-block small">Sudah Dibayar</span>
                                    <span class="fw-bold text-success">Rp
                                        {{ number_format($rental->total_dibayar, 0, ',', '.') }}</span>
                                </div>
                                <div class="col-4">
                                    <span class="text-muted d-block small">Sisa Kekurangan</span>
                                    <span class="fw-black text-danger fs-5">Rp
                                        {{ number_format($rental->sisa_pembayaran, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        @if ($rental->sisa_pembayaran > 0)
                            <form action="{{ url('/sewa-lahan/simpan-cicilan/' . $rental->id) }}" method="POST"
                                class="mb-4 p-3 bg-success bg-opacity-10 border border-success rounded">
                                @csrf
                                <div class="row g-2 mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold text-success small">Tanggal Bayar</label>
                                        <input type="date" name="payment_date" class="form-control"
                                            value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label fw-bold text-success small">Nominal Setoran (Rp)</label>
                                        <input type="number" name="amount_paid" class="form-control fw-bold" required
                                            max="{{ $rental->sisa_pembayaran }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold text-success small">Metode</label>
                                        <select name="payment_method" class="form-select">
                                            <option value="Cash">Cash / Tunai</option>
                                            <option value="Transfer">Transfer Bank</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success fw-bold w-100">Simpan Setoran</button>
                            </form>
                        @else
                            <div class="alert alert-success text-center fw-bold shadow-sm mb-4">
                                <i class="bi bi-check-circle-fill me-2 fs-5"></i> Kontrak Lahan Ini Sudah Lunas
                            </div>
                        @endif

                        <h6 class="fw-bold text-secondary mb-3"><i class="bi bi-clock-history me-1"></i> Riwayat
                            Pembayaran Tercatat</h6>
                        <div class="table-responsive bg-white rounded border">
                            <table class="table table-sm table-hover text-center align-middle mb-0"
                                style="font-size: 0.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Waktu Transaksi</th>
                                        <th>Metode</th>
                                        <th>Nominal Masuk</th>
                                        <th>Pencatat</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($paymentDetails->where('rental_id', $rental->id) as $pd)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($pd->payment_date)->format('d M Y') }}</td>
                                            <td>{{ $pd->payment_method ?? 'Cash' }}</td>
                                            <td class="text-success fw-bold">Rp
                                                {{ number_format($pd->amount_paid, 0, ',', '.') }}</td>
                                            <td><span class="badge bg-secondary"><i class="bi bi-person-fill"></i>
                                                    {{ $pd->created_by }}</span></td>
                                            <td>
                                                <form action="{{ url('/sewa-lahan/hapus-cicilan/' . $pd->id) }}"
                                                    method="POST">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2"
                                                        onclick="return confirm('Hapus histori setoran ini? (Sisa tagihan akan otomatis kembali naik)')"><i
                                                            class="bi bi-x-lg"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                    @if ($paymentDetails->where('rental_id', $rental->id)->count() == 0)
                                        <tr>
                                            <td colspan="5" class="text-muted py-3">Belum ada jejak pembayaran
                                                tercatat.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Tutup
                            Panel</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach


    <!-- ======================================================= -->
    <!-- MODAL TAMBAH SEWA BARU                                  -->
    <!-- ======================================================= -->
    <div class="modal fade form-modal" id="modalTambah" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form action="{{ url('/sewa-lahan/simpan') }}" method="POST" class="modal-content border-0 shadow">
                @csrf
                <!-- Modal Header Biru -->
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fs-6"><i class="bi bi-plus-circle me-2"></i> Tambah Kontrak Sewa Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4 bg-white">
                    <div class="row g-3">
                        <!-- Row 1 -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size: 13px;">ID Kontrak</label>
                            <input type="text" class="form-control" value="AUTO (Dibuat Otomatis)" readonly disabled
                                style="background-color: #f8f9fa;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size: 13px;">Tanggal Mulai Kontrak</label>
                            <input type="date" name="start_date" class="form-control calc-date" required>
                        </div>

                        <!-- Row 2 -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size: 13px;">Nama Lengkap Penyewa</label>
                            <input type="text" name="tenant_name" class="form-control"
                                placeholder="Contoh: Bpk Ahmad" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size: 13px;">Nomor Handphone (WA)</label>
                            <input type="text" name="tenant_phone" class="form-control"
                                placeholder="Contoh: 081234...">
                        </div>

                        <!-- Row 3 -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size: 13px;">Panjang Lahan (m)</label>
                            <input type="number" step="0.1" name="rented_length" class="form-control calc-panjang"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size: 13px;">Lebar Lahan (m)</label>
                            <input type="number" step="0.1" name="rented_width" class="form-control calc-lebar"
                                required>
                        </div>

                        <!-- Row 4 -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size: 13px;">Durasi Sewa (Bulan)</label>
                            <input type="number" name="contract_duration_months"
                                class="form-control calc-durasi calc-date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size: 13px;">Harga per m² / Tahun</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">Rp</span>
                                <input type="number" name="price_per_meter_year"
                                    class="form-control border-start-0 calc-harga" required>
                            </div>
                        </div>

                        <!-- Row 5 (Berakhir & Total) -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size: 13px;">Kontrak Berakhir</label>
                            <input type="text" class="form-control fw-bold text-danger bg-white result-date"
                                placeholder="mm/dd/yyyy" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold" style="font-size: 13px;">Total Yang Harus Dibayarkan</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">Rp</span>
                                <input type="text"
                                    class="form-control border-start-0 fw-bold text-success bg-white result-total"
                                    value="0" readonly>
                            </div>
                        </div>

                        <!-- Divider Pembatas -->
                        <div class="col-12">
                            <hr class="my-2 text-muted">
                        </div>

                        <!-- Row 6 (Status Pembayaran dropdown biru) -->
                        <div class="col-12 mb-1">
                            <label class="form-label fw-bold text-primary" style="font-size: 13px;">Status Awal
                                Pembayaran</label>
                            <select name="payment_status" class="form-select border-primary text-primary status-select"
                                required>
                                <option value="belum_bayar">Belum Ada Pembayaran Sama Sekali</option>
                                <option value="mencicil">Bayar Sebagian (DP / Cicilan)</option>
                                <option value="lunas">Lunas (Bayar Penuh)</option>
                            </select>
                        </div>

                        <!-- DP & Metode Input (Tersembunyi Awalnya) -->
                        <div class="col-md-6 d-none dp-nominal-container">
                            <label class="form-label fw-bold" style="font-size: 13px;">Nominal Pembayaran Awal</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">Rp</span>
                                <input type="number" name="initial_payment"
                                    class="form-control border-start-0 dp-input">
                            </div>
                        </div>
                        <div class="col-md-6 d-none dp-metode-container">
                            <label class="form-label fw-bold" style="font-size: 13px;">Metode Pembayaran</label>
                            <select name="initial_payment_method" class="form-select">
                                <option value="Cash">Cash (Tunai)</option>
                                <option value="Transfer">Transfer Bank</option>
                            </select>
                        </div>

                    </div>
                </div>

                <!-- Footer Tombol -->
                <div class="modal-footer d-flex flex-column gap-2 border-0 px-4 pb-4 pt-1">
                    <button type="button" class="btn btn-secondary w-100 m-0 fw-bold text-white"
                        style="background-color: #6c757d;" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary w-100 m-0 fw-bold">Simpan Kontrak</button>
                </div>
            </form>
        </div>
    </div>

    <!-- LOGIKA JAVASCRIPT AUTO-CALCULATE UNTUK MODAL TAMBAH & EDIT -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Loop ke semua form modal agar logika jalan di Tambah maupun Edit
            document.querySelectorAll('.form-modal').forEach(modal => {

                // Elemen pemicu kalkulasi Kontrak Berakhir
                const dateInputs = modal.querySelectorAll('.calc-date');
                const endDateResult = modal.querySelector('.result-date');

                // Elemen pemicu kalkulasi Total Harga
                const panjangInput = modal.querySelector('.calc-panjang');
                const lebarInput = modal.querySelector('.calc-lebar');
                const durasiInput = modal.querySelector('.calc-durasi');
                const hargaInput = modal.querySelector('.calc-harga');
                const totalResult = modal.querySelector('.result-total');

                // Elemen pemicu status pembayaran
                const statusSelect = modal.querySelector('.status-select');
                const dpNominal = modal.querySelector('.dp-nominal-container');
                const dpMetode = modal.querySelector('.dp-metode-container');

                // Fungsi hitung tanggal
                function calculateEndDate() {
                    const startVal = modal.querySelector('input[name="start_date"]').value;
                    const durationVal = durasiInput.value;

                    if (startVal && durationVal) {
                        let date = new Date(startVal);
                        date.setMonth(date.getMonth() + parseInt(durationVal));

                        let y = date.getFullYear();
                        let m = String(date.getMonth() + 1).padStart(2, '0');
                        let d = String(date.getDate()).padStart(2, '0');

                        endDateResult.value = `${m}/${d}/${y}`;
                    } else {
                        endDateResult.value = '';
                    }
                }

                // Fungsi hitung total harga
                function calculateTotalPrice() {
                    let p = parseFloat(panjangInput.value) || 0;
                    let l = parseFloat(lebarInput.value) || 0;
                    let d = parseFloat(durasiInput.value) || 0;
                    let h = parseFloat(hargaInput.value) || 0;

                    let total = p * l * (d / 12) * h;

                    totalResult.value = new Intl.NumberFormat('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(total);
                }

                // Pasang Event Listener
                dateInputs.forEach(input => input.addEventListener('input', calculateEndDate));

                [panjangInput, lebarInput, durasiInput, hargaInput].forEach(input => {
                    if (input) input.addEventListener('input', calculateTotalPrice);
                });

                // Tampil/Sembunyi metode pembayaran berdasar dropdown
                if (statusSelect) {
                    statusSelect.addEventListener('change', function() {
                        if (this.value === 'mencicil') {
                            dpNominal.classList.remove('d-none');
                            dpMetode.classList.remove('d-none');
                        } else if (this.value === 'lunas') {
                            dpNominal.classList.add('d-none');
                            dpMetode.classList.remove('d-none');
                        } else {
                            dpNominal.classList.add('d-none');
                            dpMetode.classList.add('d-none');
                        }
                    });
                }
            });

            // TOMBOL WHATSAPP
            document.querySelectorAll('.btn-wa').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const phone = this.getAttribute('data-phone');
                    let hp = phone.replace(/\D/g, '');
                    if (hp.startsWith('0')) hp = '62' + hp.substring(1);
                    else if (!hp.startsWith('62')) hp = '62' + hp;

                    let pesan = "Halo Bapak/Ibu *" + this.getAttribute('data-nama') + "*,\n\n";
                    pesan +=
                        "Berikut adalah informasi tagihan kontrak Lahan Anda di *Pande Mesari*:\n\n";
                    pesan += "- *Luas Lahan:* " + this.getAttribute('data-spek') + "\n";
                    pesan += "- *Durasi Sewa:* " + this.getAttribute('data-durasi') + " Bulan\n";
                    pesan += "- *Total Kontrak:* " + this.getAttribute('data-total') + "\n";
                    pesan += "- *Telah Dibayar:* " + this.getAttribute('data-bayar') + "\n";
                    pesan += "- *Sisa Tagihan:* *" + this.getAttribute('data-sisa') + "*\n\n";
                    pesan +=
                        "\u{1F4C4} *Kwitansi / Nota Digital* Anda dapat dilihat melalui tautan berikut:\n";
                    pesan += this.getAttribute('data-nota') + "\n\nTerima kasih. \u{1F64F}";
                    window.open("https://wa.me/" + hp + "?text=" + encodeURIComponent(pesan),
                        "_blank");
                });
            });
        });
    </script>
@endsection
