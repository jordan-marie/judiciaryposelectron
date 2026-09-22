<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Weighbridge Management System</title>
    <!-- Native Theme Handler Script -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('wb_theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
        })();
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--bs-tertiary-bg);
        }
        .login-card {
            max-width: 420px;
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="card login-card p-4">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle mb-2" style="width: 56px; height: 56px;">
                <i class="bi bi-truck fs-2"></i>
            </div>
            <h4 class="fw-bold mb-1">WeighSys Login</h4>
            <p class="text-muted fs-7 mb-0">Weighbridge Management System Operator Portal</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email', 'admin@weighbridge.com') }}" required autofocus>
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                    <input type="password" class="form-control" id="password" name="password" value="password" required>
                </div>
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">Remember Me</label>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
            </button>
        </form>

        <div class="mt-4 pt-3 border-top text-center text-muted fs-7">
            <p class="mb-1 fw-bold">Demo Quick Logins:</p>
            <div class="d-flex flex-wrap justify-content-center gap-1">
                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 quick-login" data-email="admin@weighbridge.com">Admin</button>
                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 quick-login" data-email="operator@weighbridge.com">Operator</button>
                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 quick-login" data-email="manager@weighbridge.com">Manager</button>
                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 quick-login" data-email="auditor@weighbridge.com">Auditor</button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.quick-login').on('click', function() {
                $('#email').val($(this).data('email'));
                $('#password').val('password');
            });
        });
    </script>
</body>
</html>
