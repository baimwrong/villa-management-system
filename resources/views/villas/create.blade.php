@extends('layouts.app')

@section('title', 'Add New Luxury Villa')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold mb-1 text-dark">Register New Luxury Villa</h3>
                <p class="text-muted small mb-0">Add a new property to the resort management portfolio.</p>
            </div>
            <a href="{{ route('villas.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Fleet
            </a>
        </div>

        <div class="card card-luxury">
            <div class="card-body p-4">
                <form action="{{ route('villas.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">1. Identification & Category</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Villa Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', 'VIL-' . rand(100, 999)) }}" required placeholder="e.g. VIL-105">
                            <div class="form-text small">Unique reference identifier.</div>
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-5">
                            <label class="form-label small fw-semibold text-secondary">Villa Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="e.g. The Sapphire Horizon Pavilion">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                <option value="Beachfront" {{ old('category') == 'Beachfront' ? 'selected' : '' }}>Beachfront</option>
                                <option value="Cliffside" {{ old('category') == 'Cliffside' ? 'selected' : '' }}>Cliffside</option>
                                <option value="Garden Estate" {{ old('category') == 'Garden Estate' ? 'selected' : '' }}>Garden Estate</option>
                                <option value="Overwater" {{ old('category') == 'Overwater' ? 'selected' : '' }}>Overwater</option>
                                <option value="Mountain View" {{ old('category') == 'Mountain View' ? 'selected' : '' }}>Mountain View</option>
                                <option value="Luxury Compound" {{ old('category') == 'Luxury Compound' ? 'selected' : '' }}>Luxury Compound</option>
                                <option value="Eco Luxury" {{ old('category') == 'Eco Luxury' ? 'selected' : '' }}>Eco Luxury</option>
                            </select>
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">2. Dimensions & Capacities</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Bedrooms <span class="text-danger">*</span></label>
                            <input type="number" name="bedrooms" min="1" max="20" class="form-control" value="{{ old('bedrooms', 3) }}" required>
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Bathrooms <span class="text-danger">*</span></label>
                            <input type="number" name="bathrooms" min="1" max="20" class="form-control" value="{{ old('bathrooms', 3) }}" required>
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Max Guests <span class="text-danger">*</span></label>
                            <input type="number" name="max_guests" min="1" max="50" class="form-control" value="{{ old('max_guests', 6) }}" required>
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Floor Area (m²)</label>
                            <input type="number" name="area_sqm" min="10" class="form-control" value="{{ old('area_sqm', 350) }}" placeholder="e.g. 420">
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">3. Pricing & Operational Rates</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Nightly Rate ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="price_per_night" class="form-control @error('price_per_night') is-invalid @enderror" value="{{ old('price_per_night', 1200.00) }}" required>
                            @error('price_per_night') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Weekend Rate ($)</label>
                            <input type="number" step="0.01" name="weekend_price_per_night" class="form-control" value="{{ old('weekend_price_per_night', 1400.00) }}">
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Cleaning Turn Fee ($)</label>
                            <input type="number" step="0.01" name="cleaning_fee" class="form-control" value="{{ old('cleaning_fee', 120.00) }}">
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Security Deposit ($)</label>
                            <input type="number" step="0.01" name="security_deposit" class="form-control" value="{{ old('security_deposit', 500.00) }}">
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">4. Location & Operational Status</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Location Zone <span class="text-danger">*</span></label>
                            <input type="text" name="location" class="form-control" value="{{ old('location', 'North Coast Bay') }}" required placeholder="e.g. Sunset Headlands">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Initial Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="available" selected>Available for Booking</option>
                                <option value="cleaning">Housekeeping / Cleaning</option>
                                <option value="maintenance">Under Maintenance</option>
                                <option value="reserved">Management Reserved</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-center">
                            <div class="form-check mt-3">
                                <input type="checkbox" name="is_featured" id="is_featured" class="form-check-input" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                <label for="is_featured" class="form-check-label small fw-semibold">Mark as Featured VIP Villa</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Physical Address / Plot Reference</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address') }}" placeholder="Plot 14, Royal Peninsula Reserve">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Description & Highlights</label>
                            <textarea name="description" rows="3" class="form-control" placeholder="Describe the architectural design, views, and unique luxuries of this villa...">{{ old('description') }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Featured Villa Photograph / Cover Image</label>
                            <input type="file" name="featured_image" accept="image/jpeg,image/png,image/webp" class="form-control @error('featured_image') is-invalid @enderror">
                            <div class="form-text small">Upload luxury high-resolution image (JPEG, PNG, or WEBP, max 5MB).</div>
                            @error('featured_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">5. Luxury Amenities & Equipment</h5>
                    <div class="row g-2 mb-4">
                        @foreach($standardAmenities as $amenity)
                            <div class="col-md-3 col-sm-4 col-6">
                                <div class="form-check">
                                    <input type="checkbox" name="amenities[]" value="{{ $amenity }}" id="amenity_{{ $loop->index }}" class="form-check-input" {{ in_array($amenity, old('amenities', ['Private Pool', 'Ocean View', 'Wi-Fi 6', 'Butler Service'])) ? 'checked' : '' }}>
                                    <label for="amenity_{{ $loop->index }}" class="form-check-label small text-secondary">
                                        {{ $amenity }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('villas.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-gold px-4">
                            <i class="fa-solid fa-save me-2"></i> Register Villa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
