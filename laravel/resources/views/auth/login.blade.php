<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Weighbridge Application</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-login {
            width: 100%;
            max-width: 420px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body>
<div class="card card-login border-0 p-4 bg-white">
    <div class="text-center mb-4">
        <h3 class="fw-bold text-primary">⚖️ Weighbridge POS</h3>
        <p class="text-muted fs-7">Industrial Weighment Management System</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger py-2 fs-7">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-semibold">Email Address</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', 'admin@weighbridge.com') }}" required autofocus>
        </div>

        <div class="mb-3">
            <label class="form-label fw-semibold">Password</label>
            <input type="password" name="password" class="form-control" value="admin123" required>
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" name="remember" class="form-check-input" id="remember">
            <label class="form-check-label" for="remember">Remember Me</label>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">Sign In</button>
    </form>

    <div class="mt-4 pt-3 border-top text-muted text-center fs-7">
        <small>Demo Logins:</small><br>
        <small class="text-dark">admin@weighbridge.com | operator@weighbridge.com | supervisor@weighbridge.com</small><br>
        <small class="text-muted">(Password: admin123 / operator123 / supervisor123)</small>
    </div>
</div>
</body>
</html>
