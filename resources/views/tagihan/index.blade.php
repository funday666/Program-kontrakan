@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h2 class="fw-bold mb-0 fs-3"><i class="bi bi-lightning-charge-fill text-warning me-2"></i> Tagihan Air &
                    Listrik</h2>
                <p class="text-muted mb-0 small">Catat pemakaian meteran penyewa dan pantau tunggakan utilitas bulanan.</p>
            </div>

            <form action="{{ url('/tagihan-utilitas') }}" method="GET"
                class="d-flex flex-wrap gap-2 align-items-center bg-white p-2 rounded shadow-sm border border-2">
                <div class="input-group input-group-sm" style="width: auto; min-width: 200px;">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0"
                        placeholder="Cari penyewa..." value="{{ request('search') }}">
                </div>

                <select name="status" class="form-select form-select-sm" style="width: auto;">
                    <option value="">Semua Status</option>
                    <option value="Belum Lunas" {{ request('status') == 'Belum Lunas' ? 'selected' : '' }}>Belum Lunas
                    </option>
                    <option value="Lunas" {{ request('status') == 'Lunas' ? 'selected' : '' }}>Lunas</option>
                </select>

                <button type="submit" class="btn btn-sm btn-primary px-3 fw-bold">Cari</button>
                @if (request('search') || request('status'))
                    <a href="{{ url('/tagihan-utilitas') }}" class="btn btn-sm btn-outline-danger"><i
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
                <h6 class="mb-0 fw-bold text-secondary">Daftar Tagihan Bulanan</h6>
                <button class="btn btn-sm btn-warning fw-bold shadow-sm text-dark" data-bs-toggle="modal"
                    data-bs-target="#modalTambahTagihan">
                    <i class="bi bi-plus-circle me-1"></i> Catat Tagihan Baru
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Periode</th>
                                <th>Penyewa</th>
                                <th>Rincian Air (m³)</th>
                                <th>Rincian Listrik (kWh)</th>
                                <th>Lain-lain</th>
                                <th class="text-end">Total Tagihan</th>
                                <th class="text-center">Status</th>
                                <th class="text-center pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dataTagihan as $row)
                                <tr>
                                    <td class="ps-4 font-monospace fw-bold text-secondary">{{ $row->periode_bulan }}</td>
                                    <td class="fw-bold">{{ $row->tenant_name }}</td>
                                    <td>
                                        <div class="text-primary fw-bold text-xs">
                                            {{ (float) $row->meter_air_akhir - (float) $row->meter_air_awal }} m³</div>
                                        <div class="small text-muted">Rp {{ number_format($row->total_air, 0, ',', '.') }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-warning fw-bold text-xs">
                                            {{ (float) $row->meter_listrik_akhir - (float) $row->meter_listrik_awal }} kWh
                                        </div>
                                        <div class="small text-muted">Rp
                                            {{ number_format($row->total_listrik, 0, ',', '.') }}</div>
                                    </td>
                                    <td>
                                        @if ($row->biaya_lain > 0)
                                            <div class="text-dark fw-bold text-xs">Rp
                                                {{ number_format($row->biaya_lain, 0, ',', '.') }}</div>
                                            <div class="small text-muted">{{ $row->keterangan_biaya_lain }}</div>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-black text-danger">Rp
                                        {{ number_format($row->total_tagihan, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        @if ($row->status_bayar == 'Lunas')
                                            <span
                                                class="badge bg-success bg-opacity-10 text-success border border-success d-block mb-1">Lunas</span>
                                            <small class="text-muted fw-bold"><i
                                                    class="bi bi-{{ strtolower($row->metode_pembayaran) == 'cash' ? 'cash-coin' : 'bank' }}"></i>
                                                {{ $row->metode_pembayaran ?? 'Cash' }}</small>
                                        @else
                                            <span
                                                class="badge bg-danger bg-opacity-10 text-danger border border-danger">Belum
                                                Lunas</span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-4">
                                        @if ($row->status_bayar != 'Lunas' && !strpos(strtolower(Auth::user()->email), 'viewer'))
                                            <form action="{{ url('/tagihan-utilitas/bayar/' . $row->id) }}" method="POST"
                                                class="mb-2">
                                                @csrf
                                                <div class="input-group input-group-sm shadow-sm mb-1">
                                                    <select name="metode_pembayaran" class="form-select bg-light"
                                                        style="max-width: 110px;" required>
                                                        <option value="Cash">Cash</option>
                                                        <option value="Transfer">Transfer</option>
                                                    </select>
                                                    <button type="submit" class="btn btn-success" title="Tandai Lunas"
                                                        onclick="return confirm('Tandai tagihan ini lunas?')">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                </div>
                                            </form>
                                        @endif
                                        @if (!strpos(strtolower(Auth::user()->email), 'viewer'))
                                            <form action="{{ url('/tagihan-utilitas/hapus/' . $row->id) }}" method="POST"
                                                class="d-block">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger w-100"
                                                    title="Hapus Tagihan"
                                                    onclick="return confirm('Hapus permanen tagihan ini?')">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">Belum ada data tagihan air &
                                        listrik.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($dataTagihan->hasPages())
                <div class="card-footer bg-white border-top py-2 d-flex justify-content-center">
                    {{ $dataTagihan->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>

    {{-- MODAL TAMBAH TAGIHAN (DENGAN JS KALKULASI OTOMATIS) --}}
    <div class="modal fade" id="modalTambahTagihan" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form action="{{ url('/tagihan-utilitas/simpan') }}" method="POST" class="modal-content border-0 shadow">
                @csrf
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fs-6"><i class="bi bi-calculator me-2"></i>Kalkulator Tagihan Air & Listrik
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-light">

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Pilih Penyewa (Sesuai Kontrak)</label>
                            <select name="rental_id" id="pilihPenyewa" class="form-select border-primary" required>
                                <option value="">-- Pilih Penyewa --</option>
                                @foreach ($dataRentals as $r)
                                    <option value="{{ $r->id }}">{{ $r->tenant_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Bulan Tagihan</label>
                            <input type="month" name="periode_bulan" class="form-control border-primary"
                                value="{{ date('Y-m') }}" required>
                        </div>
                    </div>

                    <!-- KALKULATOR AIR -->
                    <div class="card border-info mb-3">
                        <div class="card-header bg-info bg-opacity-10 text-info fw-bold py-2"><i
                                class="bi bi-droplet-fill me-1"></i> Meteran Air (m³)</div>
                        <div class="card-body row g-2">
                            <div class="col-md-4">
                                <label class="small text-muted">Meter Awal</label>
                                <input type="number" step="0.1" name="meter_air_awal" id="wAwal"
                                    class="form-control calc-trigger" value="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="small fw-bold">Meter Akhir</label>
                                <input type="number" step="0.1" name="meter_air_akhir" id="wAkhir"
                                    class="form-control border-info fw-bold calc-trigger" value="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="small text-muted">Tarif (Rp / m³)</label>
                                <input type="number" name="tarif_air" id="wTarif" class="form-control calc-trigger"
                                    value="5000" required>
                            </div>
                        </div>
                    </div>

                    <!-- KALKULATOR LISTRIK -->
                    <div class="card border-warning mb-3">
                        <div class="card-header bg-warning bg-opacity-10 text-dark fw-bold py-2"><i
                                class="bi bi-lightning-fill me-1 text-warning"></i> Meteran Listrik (kWh)</div>
                        <div class="card-body row g-2">
                            <div class="col-md-4">
                                <label class="small text-muted">Meter Awal</label>
                                <input type="number" step="0.1" name="meter_listrik_awal" id="eAwal"
                                    class="form-control calc-trigger" value="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="small fw-bold">Meter Akhir</label>
                                <input type="number" step="0.1" name="meter_listrik_akhir" id="eAkhir"
                                    class="form-control border-warning fw-bold calc-trigger" value="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="small text-muted">Tarif (Rp / kWh)</label>
                                <input type="number" name="tarif_listrik" id="eTarif"
                                    class="form-control calc-trigger" value="1500" required>
                            </div>
                        </div>
                    </div>

                    <!-- BIAYA LAIN & STATUS -->
                    <div class="row g-2 mb-2">
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Biaya Lain (Rp)</label>
                            <input type="number" name="biaya_lain" id="bLain" class="form-control calc-trigger"
                                value="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Keterangan Biaya</label>
                            <input type="text" name="keterangan_biaya_lain" class="form-control"
                                placeholder="Opsional">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Status Awal</label>
                            <select name="status_bayar" class="form-select border-primary">
                                <option value="Belum Lunas">Belum Lunas</option>
                                <option value="Lunas">Langsung Lunas</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold small">Metode Bayar</label>
                            <select name="metode_pembayaran" class="form-select border-primary">
                                <option value="Cash">Cash (Tunai)</option>
                                <option value="Transfer">Transfer Bank</option>
                            </select>
                        </div>
                    </div>

                    <!-- TOTAL DISPLAY -->
                    <div class="modal-footer bg-white d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small d-block">Estimasi Total Tagihan:</span>
                            <h3 class="fw-black text-danger mb-0" id="grandTotalDisplay">Rp 0</h3>
                        </div>
                        <div>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary px-4 fw-bold">Simpan Tagihan</button>
                        </div>
                    </div>
            </form>
        </div>
    </div>

    <script>
        // JS Untuk Kalkulasi Real-time
        document.addEventListener('DOMContentLoaded', function() {
            const triggers = document.querySelectorAll('.calc-trigger');
            triggers.forEach(input => {
                input.addEventListener('input', hitungTotal);
            });

            function hitungTotal() {
                let wA = parseFloat(document.getElementById('wAwal').value) || 0;
                let wK = parseFloat(document.getElementById('wAkhir').value) || 0;
                let wT = parseFloat(document.getElementById('wTarif').value) || 0;

                let eA = parseFloat(document.getElementById('eAwal').value) || 0;
                let eK = parseFloat(document.getElementById('eAkhir').value) || 0;
                let eT = parseFloat(document.getElementById('eTarif').value) || 0;

                let bL = parseFloat(document.getElementById('bLain').value) || 0;

                let totalW = Math.max(0, (wK - wA)) * wT;
                let totalE = Math.max(0, (eK - eA)) * eT;
                let grandTotal = totalW + totalE + bL;

                document.getElementById('grandTotalDisplay').innerText =
                    new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        maximumFractionDigits: 0
                    }).format(grandTotal);
            }

            // =====================================================
            // SCRIPT AUTO-FILL METERAN AWAL DARI BULAN SEBELUMNYA
            // =====================================================
            document.getElementById('pilihPenyewa').addEventListener('change', function() {
                let rentalId = this.value;
                let wAwalInput = document.getElementById('wAwal');
                let eAwalInput = document.getElementById('eAwal');

                if (rentalId) {
                    // Tarik data dari database tanpa loading halaman
                    fetch('{{ url('/tagihan-utilitas/get-meter-terakhir') }}/' + rentalId)
                        .then(response => response.json())
                        .then(data => {
                            // Isi kolom meter awal secara otomatis
                            wAwalInput.value = data.air_awal;
                            eAwalInput.value = data.listrik_awal;

                            // Beri efek warna hijau sebentar agar admin tahu data ditarik otomatis
                            wAwalInput.style.backgroundColor = '#d1e7dd';
                            eAwalInput.style.backgroundColor = '#fff3cd';
                            setTimeout(() => {
                                wAwalInput.style.backgroundColor = '';
                                eAwalInput.style.backgroundColor = '';
                            }, 1500);

                            // Jadikan read-only agar tidak salah ketik (opsional)
                            if (data.air_awal > 0) wAwalInput.setAttribute('readonly', true);
                            else wAwalInput.removeAttribute('readonly');

                            if (data.listrik_awal > 0) eAwalInput.setAttribute('readonly', true);
                            else eAwalInput.removeAttribute('readonly');

                            // Jalankan ulang kalkulasi ke 0 (karena meter akhir belum diisi)
                            hitungTotal();
                        })
                        .catch(error => console.error('Error fetching meter data:', error));
                } else {
                    wAwalInput.value = 0;
                    eAwalInput.value = 0;
                    wAwalInput.removeAttribute('readonly');
                    eAwalInput.removeAttribute('readonly');
                    hitungTotal();
                }
            });
        });
    </script>
@endsection
