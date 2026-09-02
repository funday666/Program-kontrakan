@extends('layouts.app')

@section('content')
<style>
    .hover-elevate { transition: all 0.3s ease; }
    .hover-elevate:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
</style>
<div class="container-fluid">
    <!-- HEADER & FILTER CETAK LAPORAN -->
    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1 fs-3">Dashboard Keuangan</h2>
            <p class="text-muted mb-0 small">Rangkuman keseluruhan arus kas, pemasukan, dan pembagian dana.</p>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-3">
                <form action="{{ url('/laporan-keuangan') }}" method="GET" class="mb-0">
                    <div class="d-flex flex-column flex-sm-row gap-2 align-items-center mb-2">
                        <span class="badge bg-secondary w-100 text-start py-2"><i class="bi bi-printer-fill me-1"></i> Filter Tanggal Cetak Laporan</span>
                    </div>
                    <div class="d-flex flex-column flex-sm-row gap-2 align-items-center mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <label class="form-label mb-0 small fw-bold text-muted text-nowrap">Dari:</label>
                            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label class="form-label mb-0 small fw-bold text-muted text-nowrap">Sampai:</label>
                            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                        </div>
                        @if (request('start_date') || request('end_date'))
                            <a href="{{ url('/laporan-keuangan') }}" class="btn btn-sm btn-outline-danger px-2" title="Reset Filter"><i class="bi bi-x-circle"></i></a>
                        @endif
                    </div>

                    <!-- TOMBOL EXPORT -->
                    <div class="border-top pt-3 mt-2 d-flex gap-2">
                        <a href="{{ url('/laporan-keuangan/export/excel') }}?start_date={{ request('start_date') }}&end_date={{ request('end_date') }}"
                            class="btn btn-sm btn-success w-100 fw-semibold d-flex justify-content-center align-items-center">
                            <i class="bi bi-file-earmark-excel-fill me-1"></i> Unduh Excel
                        </a>
                        <a href="{{ url('/laporan-keuangan/export/pdf') }}?start_date={{ request('start_date') }}&end_date={{ request('end_date') }}"
                            target="_blank"
                            class="btn btn-sm btn-danger w-100 fw-semibold d-flex justify-content-center align-items-center">
                            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Cetak PDF
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if (session('sukses'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('sukses') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $errors->first() }}
        </div>
    @endif

    {{-- INFO SALDO (UANG FISIK & BANK) --}}
    <div class="row mb-4 align-items-stretch">
        <div class="col-12 col-md-5 mb-3 mb-md-0">
            <div class="card shadow-sm border-0 border-start border-success border-5 h-100">
                <div class="card-body d-flex justify-content-between align-items-center p-3 p-md-4">
                    <div>
                        <h6 class="text-muted fw-bold mb-1" style="font-size: 0.85rem;"><i class="bi bi-wallet2 me-1"></i> ARUS KAS CASH (TUNAI)</h6>
                        <h3 class="fw-bold {{ $saldoCash < 0 ? 'text-danger' : 'text-success' }} mb-0 text-break">Rp {{ number_format($saldoCash, 0, ',', '.') }}</h3>
                    </div>
                    <i class="bi bi-cash-stack text-success opacity-50 d-none d-sm-block" style="font-size: 3rem;"></i>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-5 mb-3 mb-md-0">
            <div class="card shadow-sm border-0 border-start border-primary border-5 h-100">
                <div class="card-body d-flex justify-content-between align-items-center p-3 p-md-4">
                    <div>
                        <h6 class="text-muted fw-bold mb-1" style="font-size: 0.85rem;"><i class="bi bi-bank me-1"></i> ARUS KAS BANK (TRANSFER)</h6>
                        <h3 class="fw-bold {{ $saldoTransfer < 0 ? 'text-danger' : 'text-primary' }} mb-0 text-break">Rp {{ number_format($saldoTransfer, 0, ',', '.') }}</h3>
                    </div>
                    <i class="bi bi-credit-card text-primary opacity-50 d-none d-sm-block" style="font-size: 3rem;"></i>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-2">
            <button class="btn btn-dark w-100 h-100 py-3 py-md-0 shadow-sm fw-bold d-flex flex-row flex-md-column align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#mutasiModal">
                <i class="bi bi-arrow-left-right fs-4 fs-md-3 mb-0 mb-md-1"></i>
                <span>Mutasi Dana</span>
            </button>
        </div>
    </div>

    {{-- Card Header Info Total Lahan --}}
    <h5 class="fw-bold mb-3 text-secondary" style="font-size: 1.1rem;">Rangkuman Nilai Kontrak Sewa Lahan</h5>
    <div class="row mb-4">
        <div class="col-12 col-md-4 mb-3 mb-md-0">
            <div class="card bg-secondary text-white shadow-sm border-0 h-100">
                <div class="card-body p-3">
                    <small class="d-block mb-1 opacity-75">Total Keseluruhan Nilai Kontrak</small>
                    <h4 class="fw-bold mb-0 text-break">Rp {{ number_format($totalHargaSeluruh, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4 mb-3 mb-md-0">
            <div class="card bg-secondary text-white shadow-sm border-0 h-100 opacity-75">
                <div class="card-body p-3">
                    <small class="d-block mb-1">Total Sudah Masuk (Termasuk Cicilan)</small>
                    <h4 class="fw-bold mb-0 text-break">Rp {{ number_format($totalTerbayarSeluruh, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card bg-danger text-white shadow-sm border-0 h-100 opacity-75">
                <div class="card-body p-3">
                    <small class="d-block mb-1">Total Piutang Belum Dibayar</small>
                    <h4 class="fw-bold mb-0 text-break">Rp {{ number_format($totalPiutangSeluruh, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Pembagian Dana Cards dengan Dropdown Histori --}}
    <h4 class="fw-bold mb-3 mt-5" style="font-size: 1.1rem;">Pembagian Dana Berdasarkan Persentase</h4>
    <div class="row">
        @foreach ($pembagian as $nama => $data)
            @php $idUnik = str_replace([' ', '(', ')'], '', $nama); @endphp
            <div class="col-12 col-md-6 col-lg-4 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex flex-column">
                        <h6 class="fw-bold text-dark">{{ $nama }}</h6>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Pemasukan:</span>
                            <span class="text-primary fw-bold small">Rp {{ number_format($data['pemasukan'], 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Pengeluaran:</span>
                            <span class="text-danger fw-bold small">Rp {{ number_format($data['pengeluaran'], 0, ',', '.') }}</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between fw-bold mb-3">
                            <span class="small">Sisa Saldo Jatah:</span>
                            <span class="text-success small">Rp {{ number_format($data['sisa'], 0, ',', '.') }}</span>
                        </div>
                        
                        <!-- Dua Tombol Aksi -->
                        <div class="d-flex gap-2 mt-auto">
                            <button class="btn btn-sm btn-dark w-100" data-bs-toggle="modal" data-bs-target="#expenseModal{{ $idUnik }}">
                                <i class="bi bi-plus-circle me-1"></i> Pakai Dana
                            </button>
                            <button class="btn btn-sm btn-outline-secondary w-100" data-bs-toggle="collapse" data-bs-target="#collapseHistori{{ $idUnik }}">
                                <i class="bi bi-clock-history me-1"></i> Riwayat
                            </button>
                        </div>

                        <!-- Panel Dropdown Histori (Muncul Saat Tombol Riwayat Diklik) -->
                        <div class="collapse mt-2" id="collapseHistori{{ $idUnik }}">
                            <div class="card card-body bg-light p-2 border-0 shadow-sm" style="font-size: 0.8rem;">
                                <div class="text-muted fw-bold border-bottom pb-1 mb-2">
                                    5 Pengeluaran Terakhir
                                </div>
                                
                                @forelse($data['histori'] as $hist)
                                    <div class="d-flex justify-content-between align-items-center border-bottom border-light pb-1 mb-1">
                                        <div class="text-truncate pe-2" style="max-width: 65%;" title="{{ $hist->description }}">
                                            <span class="text-secondary fw-bold">{{ date('d/m', strtotime($hist->created_at)) }}</span> 
                                            - {{ $hist->description }}
                                        </div>
                                        <span class="text-danger fw-bold">-Rp {{ number_format($hist->amount, 0, ',', '.') }}</span>
                                    </div>
                                @empty
                                    <div class="text-center text-muted fst-italic py-2">Belum ada pengeluaran.</div>
                                @endforelse
                                
                                <!-- Tombol Lihat Semua jika data lebih dari 0 -->
                                @if(count($data['histori']) > 0)
                                    <a href="{{ url('/histori/pengeluaran?search=' . urlencode($nama)) }}" class="text-center text-decoration-none small text-primary mt-1 d-block fw-bold">
                                        Lihat Semua <i class="bi bi-arrow-right"></i>
                                    </a>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- TOMBOL MENU HISTORI UTAMA --}}
    <h4 class="fw-bold mb-3 mt-5 text-secondary" style="font-size: 1.1rem;">Navigasi Rincian Histori Tabel</h4>
    <div class="row mb-5">
        <div class="col-12 col-md-4 mb-3 mb-md-0">
            <a href="{{ url('/histori/pemasukan') }}" class="card text-decoration-none shadow-sm border-0 border-start border-success border-4 h-100 hover-elevate">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 p-3 rounded me-3">
                        <i class="bi bi-box-arrow-in-down-left text-success fs-3"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">Histori Pemasukan</h6>
                        <small class="text-muted">Tabel Riwayat Uang Masuk & Nota</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-4 mb-3 mb-md-0">
            <a href="{{ url('/histori/pengeluaran') }}" class="card text-decoration-none shadow-sm border-0 border-start border-danger border-4 h-100 hover-elevate">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-danger bg-opacity-10 p-3 rounded me-3">
                        <i class="bi bi-box-arrow-up-right text-danger fs-3"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">Histori Pengeluaran</h6>
                        <small class="text-muted">Rincian Pakai Dana Seluruh Kategori</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-4">
            <a href="{{ url('/histori/mutasi') }}" class="card text-decoration-none shadow-sm border-0 border-start border-info border-4 h-100 hover-elevate">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 p-3 rounded me-3">
                        <i class="bi bi-arrow-left-right text-info fs-3"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">Histori Mutasi Dana</h6>
                        <small class="text-muted">Catatan Tarik & Setor Tunai Bank</small>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

{{-- Modal Mutasi Dana --}}
<div class="modal fade" id="mutasiModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ url('/laporan/mutasi') }}" method="POST" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="bi bi-arrow-left-right me-2"></i>Mutasi Pindah Dana</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <div class="mb-3">
                    <label class="form-label fw-bold small">Jenis Mutasi</label>
                    <select name="type" class="form-select" required>
                        <option value="Tarik Tunai">Tarik Tunai (Bank ➡ Cash)</option>
                        <option value="Setor Tunai">Setor Tunai (Cash ➡ Bank)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Nominal (Rp)</label>
                    <input type="number" name="amount" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Keterangan</label>
                    <input type="text" name="description" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="submit" class="btn btn-primary px-4 w-100">Proses</button>
            </div>
        </form>
    </div>
</div>

{{-- Modals Tambah Pengeluaran --}}
@foreach ($pembagian as $nama => $data)
    <div class="modal fade" id="expenseModal{{ str_replace([' ', '(', ')'], '', $nama) }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ url('/laporan/tambah-pengeluaran') }}" method="POST" class="modal-content border-0 shadow">
                @csrf
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fs-6">Catat Pengeluaran - {{ $nama }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 p-md-4">
                    <input type="hidden" name="category_name" value="{{ $nama }}">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Ambil Uang Dari</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="Cash">Uang Cash Fisik</option>
                            <option value="Transfer">Transfer Bank</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Tanggal Pengeluaran</label>
                        <input type="date" name="tanggal_pengeluaran" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nominal Keluar (Rp)</label>
                        <input type="number" name="amount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Keterangan / Untuk Apa</label>
                        <input type="text" name="description" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="submit" class="btn btn-success w-100">Simpan Pengeluaran</button>
                </div>
            </form>
        </div>
    </div>
@endforeach
@endsection