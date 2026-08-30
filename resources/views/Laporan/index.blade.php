@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <!-- HEADER & FILTER TANGGAL -->
        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center mb-4 gap-3">
            <div>
                <h2 class="fw-bold mb-1 fs-3">Laporan Keuangan</h2>
                <p class="text-muted mb-0 small">Pantau arus kas, pemasukan, pengeluaran, dan bagi hasil lahan.</p>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <form action="{{ url('/laporan-keuangan') }}" method="GET" class="mb-0">
                        <div class="d-flex flex-column flex-sm-row gap-2 align-items-center mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <label class="form-label mb-0 small fw-bold text-muted text-nowrap">Dari:</label>
                                <input type="date" name="start_date" class="form-control form-control-sm"
                                    value="{{ request('start_date') }}">
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <label class="form-label mb-0 small fw-bold text-muted text-nowrap">Sampai:</label>
                                <input type="date" name="end_date" class="form-control form-control-sm"
                                    value="{{ request('end_date') }}">
                            </div>
                            <div class="d-flex gap-2 w-100 w-sm-auto">
                                <button type="submit" class="btn btn-sm btn-primary px-3 w-100"><i
                                        class="bi bi-funnel-fill"></i> Filter Tabel</button>
                                @if (request('start_date') || request('end_date'))
                                    <a href="{{ url('/laporan-keuangan') }}" class="btn btn-sm btn-outline-secondary px-3"
                                        title="Reset Filter"><i class="bi bi-arrow-counterclockwise"></i></a>
                                @endif
                            </div>
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

        @if (request('start_date') || request('end_date'))
            <div class="alert alert-info border-0 shadow-sm rounded-3 mb-4 py-2">
                <i class="bi bi-info-circle-fill me-2"></i> Menampilkan <strong>tabel riwayat histori</strong> (Pemasukan,
                Pengeluaran & Mutasi) dari:
                <strong>{{ request('start_date') ? date('d M Y', strtotime(request('start_date'))) : 'Awal' }}</strong>
                s/d
                <strong>{{ request('end_date') ? date('d M Y', strtotime(request('end_date'))) : 'Sekarang' }}</strong>.
                <span class="small">(Saldo & Rangkuman di atas tetap menampilkan total keseluruhan).</span>
            </div>
        @endif

        @if (session('sukses'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('sukses') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- INFO SALDO (UANG FISIK & BANK) --}}
        <div class="row mb-4 align-items-stretch">
            <div class="col-12 col-md-5 mb-3 mb-md-0">
                <div class="card shadow-sm border-0 border-start border-success border-5 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center p-3 p-md-4">
                        <div>
                            <h6 class="text-muted fw-bold mb-1" style="font-size: 0.85rem;"><i
                                    class="bi bi-wallet2 me-1"></i> ARUS KAS CASH (TUNAI)</h6>
                            <h3 class="fw-bold {{ $saldoCash < 0 ? 'text-danger' : 'text-success' }} mb-0 text-break">Rp
                                {{ number_format($saldoCash, 0, ',', '.') }}</h3>
                        </div>
                        <i class="bi bi-cash-stack text-success opacity-50 d-none d-sm-block" style="font-size: 3rem;"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-5 mb-3 mb-md-0">
                <div class="card shadow-sm border-0 border-start border-primary border-5 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center p-3 p-md-4">
                        <div>
                            <h6 class="text-muted fw-bold mb-1" style="font-size: 0.85rem;"><i class="bi bi-bank me-1"></i>
                                ARUS KAS BANK (TRANSFER)</h6>
                            <h3 class="fw-bold {{ $saldoTransfer < 0 ? 'text-danger' : 'text-primary' }} mb-0 text-break">
                                Rp {{ number_format($saldoTransfer, 0, ',', '.') }}</h3>
                        </div>
                        <i class="bi bi-credit-card text-primary opacity-50 d-none d-sm-block" style="font-size: 3rem;"></i>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-2">
                <button
                    class="btn btn-dark w-100 h-100 py-3 py-md-0 shadow-sm fw-bold d-flex flex-row flex-md-column align-items-center justify-content-center gap-2"
                    data-bs-toggle="modal" data-bs-target="#mutasiModal">
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

        {{-- Pembagian Dana Cards --}}
        <h4 class="fw-bold mb-3 mt-5" style="font-size: 1.1rem;">Pembagian Dana Berdasarkan Persentase</h4>
        <div class="row">
            @foreach ($pembagian as $nama => $data)
                <div class="col-12 col-md-6 col-lg-4 mb-3">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body d-flex flex-column">
                            <h6 class="fw-bold text-dark">{{ $nama }}</h6>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted small">Pemasukan:</span>
                                <span class="text-primary fw-bold small">Rp
                                    {{ number_format($data['pemasukan'], 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Pengeluaran:</span>
                                <span class="text-danger fw-bold small">Rp
                                    {{ number_format($data['pengeluaran'], 0, ',', '.') }}</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between fw-bold mb-3">
                                <span class="small">Sisa Saldo Jatah:</span>
                                <span class="text-success small">Rp {{ number_format($data['sisa'], 0, ',', '.') }}</span>
                            </div>
                            <button class="btn btn-sm btn-outline-dark w-100 mt-auto" data-bs-toggle="modal"
                                data-bs-target="#expenseModal{{ str_replace([' ', '(', ')'], '', $nama) }}">
                                <i class="bi bi-plus-circle me-1"></i> Pakai Dana
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row mt-4">
            {{-- BAGIAN ATAS/KIRI: HISTORI PEMASUKAN --}}
            <div class="col-lg-12 mb-4">
                <h5 class="fw-bold mb-3 text-success" style="font-size: 1.1rem;"><i
                        class="bi bi-box-arrow-in-down-left me-2"></i>Histori Pemasukan</h5>
                <div class="card border-0 shadow-sm rounded border-start border-success border-4">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle text-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Tanggal Bayar</th>
                                        <th>Nama Penyewa</th>
                                        <th>Metode Bayar</th>
                                        <th>Nominal Masuk</th>
                                        <th class="pe-3">Dicatat Oleh</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($historiPemasukan as $in)
                                        <tr>
                                            <td class="ps-3">{{ date('d/m/Y', strtotime($in->payment_date)) }}</td>
                                            <td class="fw-bold">{{ $in->tenant_name }}</td>
                                            <td>
                                                @if (strtoupper($in->payment_method) == 'CASH' || $in->payment_method == null)
                                                    <span
                                                        class="badge bg-success bg-opacity-10 text-success border border-success"><i
                                                            class="bi bi-cash-coin me-1"></i>Cash</span>
                                                @else
                                                    <span
                                                        class="badge bg-primary bg-opacity-10 text-primary border border-primary"><i
                                                            class="bi bi-bank me-1"></i>Transfer</span>
                                                @endif
                                            </td>
                                            <td class="text-success fw-bold">+ Rp
                                                {{ number_format($in->amount_paid, 0, ',', '.') }}</td>
                                            <td class="pe-3">
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-person-fill"></i> {{ $in->created_by ?? 'Admin' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">Belum ada data
                                                pemasukan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if ($historiPemasukan->hasPages())
                        <div class="card-footer bg-white border-top py-2 d-flex justify-content-center">
                            {{ $historiPemasukan->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- BAGIAN TENGAH: HISTORI PENGELUARAN --}}
            <div class="col-lg-12 mb-4">
                <h5 class="fw-bold mb-3 text-danger" style="font-size: 1.1rem;"><i
                        class="bi bi-box-arrow-up-right me-2"></i>Histori Pengeluaran</h5>
                <div class="card border-0 shadow-sm rounded border-start border-danger border-4">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle text-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Tanggal</th>
                                        <th>Kategori</th>
                                        <th>Keterangan</th>
                                        <th>Sumber Dana</th>
                                        <th>Nominal Keluar</th>
                                        <th>Dicatat Oleh</th>
                                        <th width="120" class="text-center pe-3">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($historiPengeluaran as $exp)
                                        <tr>
                                            <td class="ps-3">{{ date('d/m/Y', strtotime($exp->created_at)) }}</td>
                                            <td>{{ $exp->category_name }}</td>
                                            <td>{{ $exp->description }}</td>
                                            <td>
                                                @if (strtoupper($exp->payment_method) == 'CASH' || $exp->payment_method == null)
                                                    <span
                                                        class="badge bg-success bg-opacity-10 text-success border border-success">Cash</span>
                                                @else
                                                    <span
                                                        class="badge bg-primary bg-opacity-10 text-primary border border-primary">Transfer</span>
                                                @endif
                                            </td>
                                            <td class="text-danger fw-bold">- Rp
                                                {{ number_format($exp->amount, 0, ',', '.') }}</td>
                                            <td><span class="badge bg-secondary"><i class="bi bi-person-fill"></i>
                                                    {{ $exp->created_by ?? 'Admin' }}</span></td>
                                            <td class="text-center pe-3">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                                        data-bs-target="#editModal{{ $exp->id }}"><i
                                                            class="bi bi-pencil-square"></i></button>
                                                    <form action="{{ url('/laporan/hapus-pengeluaran/' . $exp->id) }}"
                                                        method="POST" style="display:inline;">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Hapus pengeluaran ini?')"><i
                                                                class="bi bi-trash-fill"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">Belum ada data
                                                pengeluaran.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if ($historiPengeluaran->hasPages())
                        <div class="card-footer bg-white border-top py-2 d-flex justify-content-center">
                            {{ $historiPengeluaran->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- BAGIAN BAWAH: HISTORI MUTASI DANA --}}
            <div class="col-lg-12">
                <h5 class="fw-bold mb-3 text-info" style="font-size: 1.1rem;"><i
                        class="bi bi-arrow-left-right me-2"></i>Histori Mutasi Dana (Tarik / Setor)</h5>
                <div class="card border-0 shadow-sm rounded border-start border-info border-4">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle text-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Waktu Transaksi</th>
                                        <th>Jenis Mutasi</th>
                                        <th>Nominal Dipindahkan</th>
                                        <th>Keterangan</th>
                                        <th>Dicatat Oleh</th>
                                        <th width="100" class="text-center pe-3">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($historiMutasi as $mutasi)
                                        <tr>
                                            <td class="ps-3">{{ date('d M Y H:i', strtotime($mutasi->created_at)) }}
                                            </td>
                                            <td>
                                                @if ($mutasi->type == 'Tarik Tunai')
                                                    <span class="badge bg-info text-dark"><i
                                                            class="bi bi-cash-stack me-1"></i>Tarik Tunai (Bank ➡
                                                        Cash)</span>
                                                @else
                                                    <span class="badge bg-secondary"><i class="bi bi-bank me-1"></i>Setor
                                                        Tunai (Cash ➡ Bank)</span>
                                                @endif
                                            </td>
                                            <td class="fw-bold">Rp {{ number_format($mutasi->amount, 0, ',', '.') }}</td>
                                            <td>{{ $mutasi->description }}</td>
                                            <td><span class="badge bg-secondary"><i class="bi bi-person-fill"></i>
                                                    {{ $mutasi->created_by ?? 'Admin' }}</span></td>
                                            <td class="text-center pe-3">
                                                <form action="{{ url('/laporan/hapus-mutasi/' . $mutasi->id) }}"
                                                    method="POST" style="display:inline;">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Hapus histori mutasi ini?')"><i
                                                            class="bi bi-trash-fill"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">Belum ada riwayat
                                                pindah dana.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if ($historiMutasi->hasPages())
                        <div class="card-footer bg-white border-top py-2 d-flex justify-content-center">
                            {{ $historiMutasi->appends(request()->query())->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
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
                    <div class="alert alert-info small pb-0 mb-4">
                        <ul class="mb-2 ps-3">
                            <li class="mb-1"><strong>Tarik Tunai:</strong> Memindahkan uang dari Bank menjadi Cash.</li>
                            <li><strong>Setor Tunai:</strong> Memasukkan Cash ke dalam Bank.</li>
                        </ul>
                    </div>
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
                <form action="{{ url('/laporan/tambah-pengeluaran') }}" method="POST"
                    class="modal-content border-0 shadow">
                    @csrf
                    <div class="modal-header bg-dark text-white">
                        <h5 class="modal-title fs-6">Catat Pengeluaran - {{ $nama }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    {{-- Tampilan --}}
                    <div class="modal-body p-3 p-md-4">
                        <input type="hidden" name="category_name" value="{{ $nama }}">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Ambil Uang Dari</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="Cash">Uang Cash Fisik</option>
                                <option value="Transfer">Transfer Bank</option>
                            </select>
                        </div>
                        <!-- TAMBAHAN: INPUT TANGGAL PENGELUARAN -->
                        <div class="mb-3">
                            <label class="form-label fw-bold" style="font-size: 13px;">Tanggal Pengeluaran</label>
                            <input type="date" name="tanggal_pengeluaran" class="form-control"
                                value="{{ date('Y-m-d') }}" required>
                        </div>
                        <!-- ===================================== -->
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
    {{-- Modals Edit Pengeluaran --}}
    @foreach (DB::table('expenses')->get() as $exp)
        <div class="modal fade" id="editModal{{ $exp->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ url('/laporan/update-pengeluaran/' . $exp->id) }}" method="POST"
                    class="modal-content border-0 shadow">
                    @csrf
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title fs-6">Edit Pengeluaran</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-3 p-md-4">

                        <!-- TAMBAHAN: EDIT TANGGAL PENGELUARAN -->
                        <div class="mb-3">
                            <label class="form-label fw-bold" style="font-size: 13px;">Tanggal Pengeluaran</label>
                            <input type="date" name="tanggal_pengeluaran" class="form-control border-primary"
                                value="{{ date('Y-m-d', strtotime($exp->created_at)) }}" required>
                        </div>
                        <!-- ===================================== -->

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Sumber Uang</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="Cash" {{ $exp->payment_method == 'Cash' ? 'selected' : '' }}>Uang Cash
                                    Fisik</option>
                                <option value="Transfer" {{ $exp->payment_method == 'Transfer' ? 'selected' : '' }}>
                                    Transfer Bank</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Nominal (Rp)</label>
                            <input type="number" name="amount" class="form-control" value="{{ $exp->amount }}"
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Keterangan</label>
                            <input type="text" name="description" class="form-control"
                                value="{{ $exp->description }}" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="submit" class="btn btn-primary w-100">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endsection
