@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <a href="{{ url('/laporan-keuangan') }}" class="btn btn-sm btn-outline-secondary mb-2"><i class="bi bi-arrow-left"></i> Kembali ke Dashboard</a>
            <h2 class="fw-bold mb-0 fs-3"><i class="bi {{ $icon }} me-2"></i> {{ $judul }}</h2>
        </div>
        
        <form action="{{ url('/histori/'.$jenis) }}" method="GET" class="d-flex flex-wrap gap-2 align-items-center bg-white p-2 rounded shadow-sm border border-2">
            <div class="input-group input-group-sm" style="width: auto; min-width: 220px;">
                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari data..." value="{{ request('search') }}">
            </div>
            
            <div class="d-none d-md-block border-end h-100 mx-1" style="width: 1px; min-height: 24px; background-color: #dee2e6;"></div>

            <div class="d-flex align-items-center gap-2">
                <span class="small fw-bold text-muted d-none d-lg-block">Filter:</span>
                <input type="date" name="start_date" class="form-control form-control-sm" style="width: auto;" value="{{ request('start_date') }}">
                <span class="text-muted">-</span>
                <input type="date" name="end_date" class="form-control form-control-sm" style="width: auto;" value="{{ request('end_date') }}">
            </div>
            
            <div class="d-flex gap-1 ms-auto ms-md-0 mt-2 mt-md-0">
                <button type="submit" class="btn btn-sm btn-primary px-3 fw-bold">Cari</button>
                @if(request('start_date') || request('end_date') || request('search'))
                    <a href="{{ url('/histori/'.$jenis) }}" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i></a>
                @endif
            </div>
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

    <div class="card border-0 shadow-sm rounded-3 border-start {{ str_replace('text-', 'border-', explode(' ', $icon)[1]) }} border-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-nowrap mb-0">
                    <thead class="table-light">
                        @if($jenis == 'pemasukan')
                            <tr>
                                <th class="ps-3">Tanggal Bayar</th>
                                <th class="text-center">No. Nota</th>
                                <th>Nama Penyewa</th>
                                <th>Metode Bayar</th>
                                <th>Nominal Masuk</th>
                                <th>Dicatat Oleh</th>
                                @if(!$isViewer) <th class="text-center pe-3">Aksi</th> @endif
                            </tr>
                        @elseif($jenis == 'pengeluaran')
                            <tr>
                                <th class="ps-3">Tanggal</th>
                                <th>Kategori</th>
                                <th>Keterangan</th>
                                <th>Sumber Dana</th>
                                <th>Nominal Keluar</th>
                                <th>Dicatat Oleh</th>
                                @if(!$isViewer) <th class="text-center pe-3">Aksi</th> @endif
                            </tr>
                        @elseif($jenis == 'mutasi')
                            <tr>
                                <th class="ps-3">Waktu Transaksi</th>
                                <th>Jenis Mutasi</th>
                                <th>Nominal Dipindahkan</th>
                                <th>Keterangan</th>
                                <th>Dicatat Oleh</th>
                                @if(!$isViewer) <th class="text-center pe-3">Aksi</th> @endif
                            </tr>
                        @endif
                    </thead>
                    <tbody>
                        @forelse($data as $row)
                            <tr>
                                <!-- BARIS PEMASUKAN -->
                                @if($jenis == 'pemasukan')
                                    <td class="ps-3">{{ date('d/m/Y', strtotime($row->payment_date)) }}</td>
                                    <td class="text-center">
                                        <a href="{{ url('/sewa-lahan/cetak-nota/' . $row->rental_id) }}" target="_blank" class="text-decoration-none">
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2 py-1"><i class="bi bi-printer-fill me-1"></i> PM-{{ sprintf('%05d', $row->rental_id) }}</span>
                                        </a>
                                    </td>
                                    <td class="fw-bold">{{ $row->tenant_name }}</td>
                                    <td>
                                        @if (strtoupper($row->payment_method) == 'CASH' || $row->payment_method == null)
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success"><i class="bi bi-cash-coin me-1"></i>Cash</span>
                                        @else
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary"><i class="bi bi-bank me-1"></i>Transfer</span>
                                        @endif
                                    </td>
                                    <td class="text-success fw-bold">+ Rp {{ number_format($row->amount_paid, 0, ',', '.') }}</td>
                                    <td><span class="badge bg-secondary"><i class="bi bi-person-fill"></i> {{ $row->created_by ?? 'Admin' }}</span></td>
                                    @if(!$isViewer)
                                    <td class="text-center pe-3">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $row->id }}"><i class="bi bi-pencil-square"></i></button>
                                            <form action="{{ url('/histori/hapus-pemasukan/' . $row->id) }}" method="POST" style="display:inline;">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus data pemasukan ini?')"><i class="bi bi-trash-fill"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                    @endif

                                <!-- BARIS PENGELUARAN -->
                                @elseif($jenis == 'pengeluaran')
                                    <td class="ps-3">{{ date('d M Y', strtotime($row->created_at)) }}</td>
                                    <td>{{ $row->category_name }}</td>
                                    <td>{{ $row->description }}</td>
                                    <td>
                                        @if (strtoupper($row->payment_method) == 'CASH' || $row->payment_method == null)
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success">Cash</span>
                                        @else
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">Transfer</span>
                                        @endif
                                    </td>
                                    <td class="text-danger fw-bold">- Rp {{ number_format($row->amount, 0, ',', '.') }}</td>
                                    <td><span class="badge bg-secondary"><i class="bi bi-person-fill"></i> {{ $row->created_by ?? 'Admin' }}</span></td>
                                    @if(!$isViewer)
                                    <td class="text-center pe-3">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $row->id }}"><i class="bi bi-pencil-square"></i></button>
                                            <form action="{{ url('/laporan/hapus-pengeluaran/' . $row->id) }}" method="POST" style="display:inline;">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus pengeluaran ini?')"><i class="bi bi-trash-fill"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                    @endif

                                <!-- BARIS MUTASI -->
                                @elseif($jenis == 'mutasi')
                                    <td class="ps-3">{{ date('d M Y H:i', strtotime($row->created_at)) }}</td>
                                    <td>
                                        @if ($row->type == 'Tarik Tunai')
                                            <span class="badge bg-info text-dark"><i class="bi bi-cash-stack me-1"></i>Tarik Tunai (Bank ➡ Cash)</span>
                                        @else
                                            <span class="badge bg-secondary"><i class="bi bi-bank me-1"></i>Setor Tunai (Cash ➡ Bank)</span>
                                        @endif
                                    </td>
                                    <td class="fw-bold">Rp {{ number_format($row->amount, 0, ',', '.') }}</td>
                                    <td>{{ $row->description }}</td>
                                    <td><span class="badge bg-secondary"><i class="bi bi-person-fill"></i> {{ $row->created_by ?? 'Admin' }}</span></td>
                                    @if(!$isViewer)
                                    <td class="text-center pe-3">
                                        <div class="d-flex justify-content-center gap-1">
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $row->id }}"><i class="bi bi-pencil-square"></i></button>
                                            <form action="{{ url('/laporan/hapus-mutasi/' . $row->id) }}" method="POST" style="display:inline;">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus histori mutasi ini?')"><i class="bi bi-trash-fill"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                    @endif
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="bi bi-search fs-1 d-block mb-3 opacity-25"></i>
                                    Data tidak ditemukan atau belum ada data.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($data->hasPages())
        <div class="card-footer bg-white border-top py-2 d-flex justify-content-center">
            {{ $data->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>

<!-- ============================================== -->
<!-- FORMULIR MODAL EDIT CERDAS UNTUK SEMUA HISTORI -->
<!-- ============================================== -->
@if(!$isViewer)
    @foreach ($data as $row)
    <div class="modal fade" id="editModal{{ $row->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            
            {{-- Form Edit Pemasukan --}}
            @if($jenis == 'pemasukan')
            <form action="{{ url('/histori/update-pemasukan/' . $row->id) }}" method="POST" class="modal-content border-0 shadow">
                @csrf
                <div class="modal-header bg-warning">
                    <h5 class="modal-title fs-6">Edit Pemasukan ({{ $row->tenant_name }})</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Tanggal Bayar</label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d', strtotime($row->payment_date)) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Metode Bayar</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="Cash" {{ ($row->payment_method ?? 'Cash') == 'Cash' ? 'selected' : '' }}>Uang Cash Fisik</option>
                            <option value="Transfer" {{ $row->payment_method == 'Transfer' ? 'selected' : '' }}>Transfer Bank</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nominal Masuk (Rp)</label>
                        <input type="number" name="amount_paid" class="form-control" value="{{ $row->amount_paid }}" required>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="submit" class="btn btn-primary w-100">Simpan Perubahan</button>
                </div>
            </form>

            {{-- Form Edit Pengeluaran --}}
            @elseif($jenis == 'pengeluaran')
            <form action="{{ url('/laporan/update-pengeluaran/' . $row->id) }}" method="POST" class="modal-content border-0 shadow">
                @csrf
                <div class="modal-header bg-warning">
                    <h5 class="modal-title fs-6">Edit Pengeluaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Tanggal Pengeluaran</label>
                        <input type="date" name="tanggal_pengeluaran" class="form-control" value="{{ date('Y-m-d', strtotime($row->created_at)) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Sumber Uang</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="Cash" {{ ($row->payment_method ?? 'Cash') == 'Cash' ? 'selected' : '' }}>Uang Cash Fisik</option>
                            <option value="Transfer" {{ $row->payment_method == 'Transfer' ? 'selected' : '' }}>Transfer Bank</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nominal (Rp)</label>
                        <input type="number" name="amount" class="form-control" value="{{ $row->amount }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Keterangan</label>
                        <input type="text" name="description" class="form-control" value="{{ $row->description }}" required>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="submit" class="btn btn-primary w-100">Simpan Perubahan</button>
                </div>
            </form>

            {{-- Form Edit Mutasi --}}
            @elseif($jenis == 'mutasi')
            <form action="{{ url('/histori/update-mutasi/' . $row->id) }}" method="POST" class="modal-content border-0 shadow">
                @csrf
                <div class="modal-header bg-warning">
                    <h5 class="modal-title fs-6">Edit Mutasi Dana</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Tanggal Mutasi</label>
                        <input type="date" name="tanggal_mutasi" class="form-control" value="{{ date('Y-m-d', strtotime($row->created_at)) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Jenis Mutasi</label>
                        <select name="type" class="form-select" required>
                            <option value="Tarik Tunai" {{ $row->type == 'Tarik Tunai' ? 'selected' : '' }}>Tarik Tunai (Bank ➡ Cash)</option>
                            <option value="Setor Tunai" {{ $row->type == 'Setor Tunai' ? 'selected' : '' }}>Setor Tunai (Cash ➡ Bank)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nominal (Rp)</label>
                        <input type="number" name="amount" class="form-control" value="{{ $row->amount }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Keterangan</label>
                        <input type="text" name="description" class="form-control" value="{{ $row->description }}" required>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="submit" class="btn btn-primary w-100">Simpan Perubahan</button>
                </div>
            </form>
            @endif

        </div>
    </div>
    @endforeach
@endif
@endsection