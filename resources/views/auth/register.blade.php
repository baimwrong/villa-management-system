@extends('layouts.auth')

@section('title', 'Register Staff')

@section('content')
    <h4 class="fw-bold mb-3 text-dark">Create Management Account</h4>
    <p class="text-muted small mb-4">Register a new resort staff or operational administrator profile.</p>

    <form action="{{ route('register.post') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label small fw-semibold text-secondary">Full Name</label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="e.g. Jean-Luc Bernard">
            @error('name')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label small fw-semibold text-secondary">Email Address</label>
            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required placeholder="staff@villamanagement.local">
            @error('email')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <label for="role" class="form-label small fw-semibold text-secondary">Role Assignment</label>
                <select name="role" id="role" class="form-select @error('role') is-invalid @enderror">
                    <option value="staff" selected>Staff / Concierge</option>
                    <option value="manager">Resort Manager</option>
                    <option value="admin">System Administrator</option>
                </select>
            </div>
            <div class="col-6">
                <label for="phone" class="form-label small fw-semibold text-secondary">Phone Number</label>
                <input type="text" name="phone" id="phone" class="form-control" placeholder="+1 555-0100">
            </div>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label small fw-semibold text-secondary">Password</label>
            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required placeholder="Minimum 8 characters">
            @error('password')
                <div class="text-danger small mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label small fw-semibold text-secondary">Confirm Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required placeholder="Repeat password">
        </div>

        <button type="submit" class="btn btn-gold w-100 mb-3">
            <i class="fa-solid fa-user-plus me-2"></i> Register Profile
        </button>

        <div class="text-center">
            <a href="{{ route('login') }}" class="small text-decoration-none text-muted">
                Already registered? <span class="fw-semibold text-dark">Sign in here</span>
            </a>
        </div>
    </form>
@endsection
