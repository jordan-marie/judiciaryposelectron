<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Weighbridge Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary d-flex align-items-center justify-content-center min-vh-100">

<div class="container" style="max-width: 440px;">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-body p-4 p-sm-5">
            <div class="text-center mb-4">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex p-3 mb-3">
                    <i class="bi bi-truck-front-fill fs-1"></i>
                </div>
                <h4 class="fw-bold">Weighbridge Terminal</h4>
                <p class="text-muted small">Sign in to access scale operations & management</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger py-2 small mb-3">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
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

                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" value="password" required>
                    </div>
                </div>

                <div class="mb-4 form-check d-flex justify-content-between align-items-center">
                    <div>
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label small" for="remember">Remember me</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Sign In</button>
            </form>

            <div class="mt-4 pt-3 border-top text-center text-muted small">
                <p class="mb-1"><strong>Demo Credentials:</strong></p>
                <p class="mb-0">Super Admin: <code>admin@weighbridge.com</code> / <code>password</code></p>
                <p class="mb-0">Operator: <code>operator@weighbridge.com</code> / <code>password</code></p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
