@extends('layouts.app')

@section('title', 'Edit Guest: ' . $guest->full_name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold mb-1 text-dark">Edit Guest Profile: {{ $guest->full_name }}</h3>
                <p class="text-muted small mb-0">Update contact credentials and VIP preferences.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('guests.show', $guest) }}" class="btn btn-outline-dark btn-sm">
                    <i class="fa-solid fa-eye me-1"></i> View Profile
                </a>
                <a href="{{ route('guests.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>

        <div class="card card-luxury">
            <div class="card-body p-4">
                <form action="{{ route('guests.update', $guest) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name', $guest->first_name) }}" required>
                            @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Last Name</label>
                            <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name', $guest->last_name) }}">
                            @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $guest->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Contact Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $guest->phone) }}">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Nationality</label>
                            <input type="text" name="nationality" class="form-control" value="{{ old('nationality', $guest->nationality) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Passport / National ID</label>
                            <input type="text" name="id_or_passport" class="form-control" value="{{ old('id_or_passport', $guest->id_or_passport) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">VIP Classification <span class="text-danger">*</span></label>
                            <select name="vip_level" class="form-select" required>
                                <option value="standard" {{ old('vip_level', $guest->vip_level) == 'standard' ? 'selected' : '' }}>Standard Guest</option>
                                <option value="vip" {{ old('vip_level', $guest->vip_level) == 'vip' ? 'selected' : '' }}>VIP Preferred Guest</option>
                                <option value="vvip" {{ old('vip_level', $guest->vip_level) == 'vvip' ? 'selected' : '' }}>VVIP Royal / Dignitary</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-secondary">Special Preferences, Dietaries & Concierge Notes</label>
                        <textarea name="special_requests" rows="3" class="form-control">{{ old('special_requests', $guest->special_requests) }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('guests.show', $guest) }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-gold px-4">
                            <i class="fa-solid fa-save me-2"></i> Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
