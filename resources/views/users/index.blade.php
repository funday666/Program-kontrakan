@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h2 class="fw-bold mb-1 fs-3">Manajemen Akun</h2>
                <p class="text-muted mb-0 small">Kelola akses Admin Utama dan Viewer (Pemantau) aplikasi.</p>
            </div>

            @if (Auth::user()->role == 'admin')
                <button class="btn btn-primary shadow-sm px-4" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-person-plus-fill me-1"></i> Tambah Akun Baru
                </button>
            @endif
        </div>

        @if ($errors->any())
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $errors->first() }}
            </div>
        @endif

        @if (session('sukses'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('sukses') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-3" width="50">No</th>
                                <th>Nama Pengguna</th>
                                <th>Email Login</th>
                                <th>Hak Akses (Role)</th>
                                <th>Dibuat Pada</th>
                                @if (Auth::user()->role == 'admin')
                                    <th class="text-center pe-3" width="150">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dataUsers as $index => $user)
                                <tr>
                                    <td class="ps-3 text-muted">{{ $index + 1 }}</td>
                                    <td class="fw-bold">{{ $user->name }}
                                        @if (Auth::id() == $user->id)
                                            <span class="badge bg-success ms-1">Anda</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if ($user->role == 'admin')
                                            <span class="badge bg-primary px-3 py-2"><i
                                                    class="bi bi-shield-lock-fill me-1"></i> Admin Utama</span>
                                        @else
                                            <span class="badge bg-secondary px-3 py-2"><i class="bi bi-eye-fill me-1"></i>
                                                Viewer (Pemantau)</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">{{ date('d M Y, H:i', strtotime($user->created_at)) }}</td>

                                    @if (Auth::user()->role == 'admin')
                                        <td class="text-center pe-3">
                                            <div class="d-flex justify-content-center gap-2">
                                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                                    data-bs-target="#editUserModal{{ $user->id }}" title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                @if (Auth::id() != $user->id)
                                                    <form action="{{ url('/manajemen-user/hapus/' . $user->id) }}"
                                                        method="POST" class="m-0">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Hapus akun ini?')" title="Hapus">
                                                            <i class="bi bi-trash-fill"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    @endif
                                </tr>

                                <!-- MODAL EDIT USER -->
                                <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <form action="{{ url('/manajemen-user/update/' . $user->id) }}" method="POST"
                                            class="modal-content border-0 shadow">
                                            @csrf
                                            <div class="modal-header bg-warning">
                                                <h5 class="modal-title fs-6"><i class="bi bi-pencil-square me-2"></i>Edit
                                                    Akun</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small">Nama Lengkap</label>
                                                    <input type="text" name="name" class="form-control"
                                                        value="{{ $user->name }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small">Alamat Email</label>
                                                    <input type="email" name="email" class="form-control"
                                                        value="{{ $user->email }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-bold small">Hak Akses (Role)</label>
                                                    <select name="role" class="form-select border-primary" required>
                                                        <option value="admin"
                                                            {{ $user->role == 'admin' ? 'selected' : '' }}>Admin Utama
                                                            (Akses Penuh)</option>
                                                        <option value="viewer"
                                                            {{ $user->role == 'viewer' ? 'selected' : '' }}>Viewer (Hanya
                                                            Lihat)</option>
                                                    </select>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label fw-bold small">Password Baru <span
                                                            class="text-muted fw-normal">(Opsional)</span></label>
                                                    <input type="password" name="password" class="form-control"
                                                        placeholder="Kosongkan jika tidak ingin mengubah password">
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light border-0">
                                                <button type="submit" class="btn btn-primary px-4 w-100">Simpan
                                                    Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- MODAL TAMBAH USER -->
        <div class="modal fade" id="addUserModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ url('/manajemen-user/simpan') }}" method="POST" class="modal-content border-0 shadow">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title fs-6"><i class="bi bi-person-plus-fill me-2"></i>Tambah Akun Baru</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control" placeholder="Masukkan nama..."
                                required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Alamat Email</label>
                            <input type="email" name="email" class="form-control"
                                placeholder="contoh: viewer@pandemesari.com" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-primary">Hak Akses (Role)</label>
                            <select name="role" class="form-select border-primary shadow-sm" required>
                                <option value="viewer">Viewer (Hanya bisa melihat data)</option>
                                <option value="admin">Admin Utama (Akses Penuh)</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-bold small">Kata Sandi (Password)</label>
                            <input type="password" name="password" class="form-control"
                                placeholder="Minimal 6 karakter..." required minlength="6">
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0">
                        <button type="submit" class="btn btn-primary px-4 w-100">Simpan Akun</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
