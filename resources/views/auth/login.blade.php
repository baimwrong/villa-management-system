@extends('layouts.auth')

@section('title', 'Sign In')

@section('content')
    <h4 class="fw-bold mb-3 text-dark">Staff & Executive Sign In</h4>
    <p class="text-muted small mb-4">Enter your credentials to access the resort management portal.</p>

    <!-- Quick Demo Credential Fillers -->
    <div class="card bg-light border-0 mb-4 p-3 rounded-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="small fw-bold text-secondary"><i class="fa-solid fa-key me-1 text-warning"></i> Quick Demo Fill:</span>
            <span class="badge bg-secondary text-white" style="font-size: 0.68rem;">password123</span>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-dark role-btn flex-fill" onclick="fillCredentials('admin@villamanagement.local', 'password123')">
                <i class="fa-solid fa-user-shield me-1"></i> Admin
            </button>
            <button type="button" class="btn btn-outline-dark role-btn flex-fill" onclick="fillCredentials('manager@villamanagement.local', 'password123')">
                <i class="fa-solid fa-user-tie me-1"></i> Manager
            </button>
            <button type="button" class="btn btn-outline-dark role-btn flex-fill" onclick="fillCredentials('concierge@villamanagement.local', 'password123')">
                <i class="fa-solid fa-bell-concierge me-1"></i> Staff
            </button>
        </div>
    </div>

    <form action="{{ route('login.post') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label small fw-semibold text-secondary">Email Address</label>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-regular fa-envelope"></i></span>
                <input type="email" name="email" id="email" class="form-control border-start-0 @error('email') is-invalid @enderror" value="{{ old('email', 'admin@villamanagement.local') }}" required autofocus placeholder="name@villamanagement.local">
            </div>
            @error('email')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between">
                <label for="password" class="form-label small fw-semibold text-secondary">Password</label>
            </div>
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                <input type="password" name="password" id="password" class="form-control border-start-0 @error('password') is-invalid @enderror" value="password123" required placeholder="••••••••">
            </div>
            @error('password')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4 form-check">
            <input type="checkbox" name="remember" id="remember" class="form-check-input">
            <label for="remember" class="form-check-label small text-secondary">Keep me signed in on this station</label>
        </div>

        <button type="submit" class="btn btn-gold w-100 mb-3">
            <i class="fa-solid fa-arrow-right-to-bracket me-2"></i> Access Management Console
        </button>

        <div class="text-center">
            <a href="{{ route('register') }}" class="small text-decoration-none text-muted">
                Need a new staff account? <span class="fw-semibold text-dark">Register here</span>
            </a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    function fillCredentials(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
    }
</script>
@endpush
