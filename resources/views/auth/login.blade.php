<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SewaLahan.id</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 400px;
        }
        .brand-logo {
            font-size: 24px;
            font-weight: 700;
            color: #212529;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="card login-card p-4 bg-white">
    <div class="brand-logo">
        <i class="bi bi-building-fill-check text-primary me-2"></i>SewaLahan<span class="text-primary">.id</span>
    </div>
    
    <h5 class="text-center text-muted mb-4">Silakan masuk ke akun Anda</h5>

    @if(session('sukses'))
        <div class="alert alert-success py-2 text-center small mb-3">
            {{ session('sukses') }}
        </div>
    @endif

    <form action="{{ url('/login') }}" method="POST">
        @csrf
        
        <div class="mb-3">
            <label for="email" class="form-label small fw-bold">Alamat Email</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                <input type="email" class="form-control border-start-0 bg-light @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required autofocus>
            </div>
            @error('email')
                <div class="text-danger small mt-1" style="font-size: 12px;">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password" class="form-label small fw-bold">Kata Sandi</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                <input type="password" class="form-control border-start-0 bg-light" id="password" name="password" placeholder="••••••••" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-3 shadow-sm mb-2">
            Masuk <i class="bi bi-box-arrow-in-right ms-1"></i>
        </button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>