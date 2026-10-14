@extends('layouts.app')

@section('title', 'Register Guest Profile')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold mb-1 text-dark">Register VIP Guest Profile</h3>
                <p class="text-muted small mb-0">Record personal details, preferences, and identification.</p>
            </div>
            <a href="{{ route('guests.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Guests
            </a>
        </div>

        <div class="card card-luxury">
            <div class="card-body p-4">
                <form action="{{ route('guests.store') }}" method="POST">
                    @csrf

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" required placeholder="e.g. Charlotte">
                            @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Last Name</label>
                            <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" placeholder="e.g. Laurent">
                            @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required placeholder="guest@luxurytravel.com">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Contact Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="+1 (555) 012-3456">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Nationality</label>
                            <input type="text" name="nationality" class="form-control" value="{{ old('nationality') }}" placeholder="e.g. France">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Passport / National ID</label>
                            <input type="text" name="id_or_passport" class="form-control" value="{{ old('id_or_passport') }}" placeholder="e.g. FR8819203">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">VIP Classification <span class="text-danger">*</span></label>
                            <select name="vip_level" class="form-select" required>
                                <option value="standard" {{ old('vip_level') == 'standard' ? 'selected' : '' }}>Standard Guest</option>
                                <option value="vip" {{ old('vip_level') == 'vip' ? 'selected' : '' }}>VIP Preferred Guest</option>
                                <option value="vvip" {{ old('vip_level') == 'vvip' ? 'selected' : '' }}>VVIP Royal / Dignitary</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-secondary">Special Preferences, Dietaries & Concierge Notes</label>
                        <textarea name="special_requests" rows="3" class="form-control" placeholder="Allergies, pillow preferences, preferred champagne, limousine greeting, etc...">{{ old('special_requests') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('guests.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-gold px-4">
                            <i class="fa-solid fa-user-check me-2"></i> Save Guest Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
