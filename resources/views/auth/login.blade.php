<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pande Mesari</title>

    <!-- Memanggil CSS Bootstrap bawaan Dashboard -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        body {
            /* Warna latar belakang abu-abu sangat muda agar form login terlihat menonjol */
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-wrapper {
            max-width: 420px;
            width: 100%;
            padding: 15px;
        }

        .brand-logo i {
            font-size: 3.5rem;
            color: #198754;
            /* Warna hijau khas Pande Mesari */
        }

        .brand-text {
            color: #0f5132;
            font-weight: 900;
            letter-spacing: 1.5px;
        }

        /* Mempercantik inputan agar mirip aplikasi modern */
        .input-group-text {
            background-color: transparent;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #0d6efd;
        }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center min-vh-100">

    <div class="login-wrapper">

        <!-- LOGO & BRANDING PANDE MESARI -->
        <div class="text-center mb-4 brand-logo">
            <i class="bi bi-house-door-fill"></i>
            <h2 class="brand-text mt-2 mb-0">PANDE MESARI</h2>
            <p class="text-muted small mt-1">Sistem Manajemen Transaksi Penyewaan</p>
        </div>

        <!-- KOTAK FORM LOGIN -->
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
            <div class="text-center mb-4">
                <h5 class="fw-bold text-dark mb-1">Selamat Datang Admin</h5>
                <p class="text-muted" style="font-size: 13px;">Silakan login untuk melanjutkan ke Dashboard.</p>
            </div>

            <!-- Pesan Error Jika Password/Email Salah -->
            @if ($errors->any())
                <div class="alert alert-danger py-2 px-3 small rounded-3 border-0 d-flex align-items-center mb-4">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <!-- FORM -->
            <form action="{{ url('/login') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-bold text-secondary" style="font-size: 12px;">ALAMAT EMAIL</label>
                    <div class="input-group">
                        <span class="input-group-text border-end-0 text-muted">
                            <i class="bi bi-envelope-fill"></i>
                        </span>
                        <input type="email" name="email" class="form-control border-start-0 ps-0"
                            placeholder="contoh: admin@pandemesari.com" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold text-secondary" style="font-size: 12px;">KATA SANDI</label>
                    <div class="input-group">
                        <span class="input-group-text border-end-0 text-muted">
                            <i class="bi bi-lock-fill"></i>
                        </span>
                        <input type="password" name="password" class="form-control border-start-0 ps-0"
                            placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fs-6 fw-bold shadow-sm rounded-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i> Masuk Dashboard
                </button>
            </form>
        </div>

        <!-- FOOTER KECIL -->
        <div class="text-center mt-4 text-muted" style="font-size: 12px;">
            &copy; {{ date('Y') }} <b>Pande Mesari</b>. Dikelola oleh Admin Utama.
        </div>

    </div>

    <!-- Script Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
