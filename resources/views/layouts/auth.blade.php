<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sign In') | Villa Management System</title>

    <!-- Local Offline Assets - 100% Internal Network Compatible (No CDN) -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.min.css') }}">

    <style>
        :root {
            --vs-primary: #0f172a;
            --vs-accent: #c29b38;
            --vs-accent-hover: #a17f2c;
        }

        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #090d16 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            padding: 2rem 1rem;
        }

        .auth-card {
            width: 100%;
            max-width: 480px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .auth-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            padding: 2.25rem 2rem 1.75rem;
            text-align: center;
            color: #ffffff;
            border-bottom: 2px solid var(--vs-accent);
        }

        .brand-icon-large {
            width: 58px;
            height: 58px;
            background: linear-gradient(135deg, #d4af37, #996515);
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #0f172a;
            font-size: 1.75rem;
            margin-bottom: 0.75rem;
            box-shadow: 0 6px 18px rgba(212, 175, 55, 0.4);
        }

        .btn-gold {
            background: linear-gradient(135deg, #d4af37, #b48a28);
            color: #0f172a;
            font-weight: 700;
            border: none;
            padding: 0.75rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
            transition: all 0.2s ease;
        }

        .btn-gold:hover {
            background: linear-gradient(135deg, #c29b38, #996515);
            color: #ffffff;
            transform: translateY(-1px);
        }

        .role-btn {
            font-size: 0.78rem;
            font-weight: 600;
            padding: 0.4rem 0.6rem;
            border-radius: 6px;
        }
    </style>
</head>
<body>

    <div class="auth-card">
        <div class="auth-header">
            <div class="brand-icon-large">
                <i class="fa-solid fa-hotel"></i>
            </div>
            <h3 class="fw-bold mb-1">Villa Management</h3>
            <p class="text-warning-subtle small text-uppercase tracking-wider mb-0" style="letter-spacing: 0.12em;">Luxury Resort & Villa Operations</p>
        </div>

        <div class="p-4 p-md-5">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
