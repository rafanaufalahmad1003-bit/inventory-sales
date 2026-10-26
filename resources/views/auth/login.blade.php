<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - LensRent Camera Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .login-card {
            border-radius: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: #ffffff;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            max-width: 440px;
            width: 100%;
            overflow: hidden;
        }
        .demo-btn {
            font-size: 0.78rem;
            padding: 0.35rem 0.6rem;
            border-radius: 0.375rem;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="bg-dark text-white p-4 text-center">
        <div class="d-inline-flex p-3 rounded-circle bg-primary bg-opacity-25 text-primary mb-3">
            <i class="bi bi-camera-fill fs-2"></i>
        </div>
        <h4 class="fw-bold mb-1">LensRent</h4>
        <p class="text-secondary small mb-0">Inventory & Camera Rental Management System</p>
    </div>

    <div class="p-4">
        @if(session('success'))
            <div class="alert alert-success small py-2 d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger small py-2">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label small fw-semibold text-secondary">Email Pengguna</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', 'owner@lensrent.com') }}" placeholder="contoh: owner@lensrent.com" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label small fw-semibold text-secondary">Kata Sandi</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" value="password" placeholder="Password" required>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label small text-secondary" for="remember">
                        Ingat Saya
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mb-3">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Sistem
            </button>
        </form>

        <div class="border-top pt-3 mt-2">
            <div class="text-muted small fw-medium mb-2 text-center">Akun Demo Praktis (Klik Cepat):</div>
            <div class="d-flex flex-wrap gap-1 justify-content-center">
                <button type="button" class="btn btn-outline-danger demo-btn" onclick="fillDemo('owner@lensrent.com')">
                    <i class="bi bi-shield-lock me-1"></i>Owner
                </button>
                <button type="button" class="btn btn-outline-primary demo-btn" onclick="fillDemo('admin@lensrent.com')">
                    <i class="bi bi-person-gear me-1"></i>Admin
                </button>
                <button type="button" class="btn btn-outline-info text-dark demo-btn" onclick="fillDemo('staff@lensrent.com')">
                    <i class="bi bi-person-badge me-1"></i>Staff
                </button>
                <button type="button" class="btn btn-outline-success demo-btn" onclick="fillDemo('pelanggan@lensrent.com')">
                    <i class="bi bi-person me-1"></i>Pelanggan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function fillDemo(email) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = 'password';
    }
</script>
</body>
</html>
