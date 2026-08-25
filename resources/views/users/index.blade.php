@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- HEADER SECTION (Responsif) -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h2 class="fw-bold mb-1 fs-3">Manajemen User (Admin)</h2>
                <p class="text-muted mb-0 small">Kelola akun yang memiliki akses ke dalam sistem aplikasi ini.</p>
            </div>
            <div>
                <button
                    class="btn btn-primary w-100 w-md-auto d-flex align-items-center justify-content-center px-4 py-2 shadow-sm"
                    data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-person-plus-fill me-2"></i> Tambah Admin Baru
                </button>
            </div>
        </div>

        <!-- NOTIFIKASI BERHASIL / GAGAL -->
        @if (session('sukses'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('sukses') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- TABEL DATA USER -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-nowrap mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th width="60" class="ps-4 py-3">No</th>
                                <th class="py-3">Info Akun (Nama & Email)</th>
                                <th class="py-3">Tanggal Didaftarkan</th>
                                <th width="150" class="text-center pe-4 py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dataUsers as $index => $user)
                                <tr>
                                    <td class="ps-4 text-muted">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center me-3"
                                                style="width: 45px; height: 45px;">
                                                <i class="bi bi-person-fill fs-4"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark fs-6">{{ $user->name }}
                                                    @if (Auth::id() == $user->id)
                                                        <span class="badge bg-success ms-1 small"
                                                            style="font-size: 10px;">Anda (Sedang Login)</span>
                                                    @endif
                                                </div>
                                                <div class="text-muted small"><i
                                                        class="bi bi-envelope-at me-1"></i>{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ date('d M Y', strtotime($user->created_at)) }}</div>
                                        <small class="text-muted">Pukul {{ date('H:i', strtotime($user->created_at)) }}
                                            WITA</small>
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center gap-2">
                                            <!-- Tombol Edit -->
                                            <button type="button"
                                                class="btn btn-sm btn-warning d-flex align-items-center shadow-sm"
                                                data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}"
                                                title="Edit User">
                                                <i class="bi bi-pencil-square me-1 d-none d-sm-inline"></i> Edit
                                            </button>

                                            <!-- Tombol Hapus -->
                                            <form action="{{ url('/manajemen-user/hapus/' . $user->id) }}" method="POST"
                                                style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="btn btn-sm btn-danger d-flex align-items-center shadow-sm"
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus admin {{ $user->name }}?')"
                                                    title="Hapus User">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-5">
                                        <i class="bi bi-people fs-1 d-block mb-2"></i> Belum ada data admin terdaftar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH ADMIN BARU -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-3">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fs-6"><i class="bi bi-person-plus-fill me-2"></i>Registrasi Admin Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form action="{{ url('/manajemen-user/simpan') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4 text-start">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Nama Lengkap</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-person text-secondary"></i></span>
                                <input type="text" name="name" class="form-control" placeholder="Masukkan nama..."
                                    required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Alamat Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-envelope text-secondary"></i></span>
                                <input type="email" name="email" class="form-control" placeholder="admin@email.com"
                                    required>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-bold text-muted">Kata Sandi (Password)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-key text-secondary"></i></span>
                                <input type="password" name="password" class="form-control"
                                    placeholder="Minimal 6 karakter..." required minlength="6">
                            </div>
                            <small class="text-muted" style="font-size: 11px;">Password harus dijaga
                                kerahasiaannya.</small>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-secondary w-100 w-md-auto mb-2 mb-md-0"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary w-100 w-md-auto px-4">Simpan Admin</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT ADMIN (Dilakukan Loop untuk Setiap Baris Data) -->
    @foreach ($dataUsers as $user)
        <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow rounded-3">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title fs-6 text-dark"><i class="bi bi-pencil-square me-2"></i>Edit Data Admin
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ url('/manajemen-user/update/' . $user->id) }}" method="POST">
                        @csrf
                        <div class="modal-body p-4 text-start">

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Nama Lengkap</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i
                                            class="bi bi-person text-secondary"></i></span>
                                    <input type="text" name="name" class="form-control"
                                        value="{{ $user->name }}" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Alamat Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i
                                            class="bi bi-envelope text-secondary"></i></span>
                                    <input type="email" name="email" class="form-control"
                                        value="{{ $user->email }}" required>
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label small fw-bold text-muted">Ganti Kata Sandi (Opsional)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i
                                            class="bi bi-key text-secondary"></i></span>
                                    <input type="password" name="password" class="form-control"
                                        placeholder="Kosongkan jika tidak ingin ganti">
                                </div>
                                <small class="text-danger" style="font-size: 11px;">Biarkan kolom ini kosong jika tidak
                                    ingin merubah password.</small>
                            </div>

                        </div>
                        <div class="modal-footer bg-light border-0">
                            <button type="button" class="btn btn-secondary w-100 w-md-auto mb-2 mb-md-0"
                                data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary w-100 w-md-auto px-4">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

@endsection
