<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign out — Smart Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .panel {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .panel h1 {
            font-size: 1.25rem;
            color: #333;
            margin-bottom: 0.5rem;
        }
        .panel p {
            color: #666;
            margin-bottom: 1.5rem;
        }
        .btn-signout {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);
            border: none;
            color: #fff;
            font-weight: 500;
            border-radius: 5px;
        }
        .btn-signout:hover {
            color: #fff;
            opacity: 0.95;
        }
    </style>
</head>
<body>
    <div class="panel">
        <h1><i class="fas fa-sign-out-alt me-2"></i>Sign out</h1>
        <p>Keluar dari aplikasi? Tekan tombol di bawah untuk mengakhiri sesi.</p>
        <form action="{{ route('logout') }}" method="POST" class="d-grid gap-2">
            @csrf
            <button type="submit" class="btn btn-signout">Sign out</button>
        </form>
        <a href="{{ route('home') }}" class="btn btn-link mt-3">Batal, kembali ke dashboard</a>
    </div>
</body>
</html>
