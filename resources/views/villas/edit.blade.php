@extends('layouts.app')

@section('title', 'Edit ' . $villa->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold mb-1 text-dark">Edit Villa: {{ $villa->name }}</h3>
                <p class="text-muted small mb-0">Update property details, rates, and amenities.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('villas.show', $villa) }}" class="btn btn-outline-dark btn-sm">
                    <i class="fa-solid fa-eye me-1"></i> View Details
                </a>
                <a href="{{ route('villas.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Fleet
                </a>
            </div>
        </div>

        <div class="card card-luxury">
            <div class="card-body p-4">
                <form action="{{ route('villas.update', $villa) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">1. Identification & Category</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Villa Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $villa->code) }}" required>
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-5">
                            <label class="form-label small fw-semibold text-secondary">Villa Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $villa->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-secondary">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                @foreach(['Beachfront', 'Cliffside', 'Garden Estate', 'Overwater', 'Mountain View', 'Luxury Compound', 'Eco Luxury'] as $cat)
                                    <option value="{{ $cat }}" {{ old('category', $villa->category) == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">2. Dimensions & Capacities</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Bedrooms <span class="text-danger">*</span></label>
                            <input type="number" name="bedrooms" min="1" max="20" class="form-control" value="{{ old('bedrooms', $villa->bedrooms) }}" required>
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Bathrooms <span class="text-danger">*</span></label>
                            <input type="number" name="bathrooms" min="1" max="20" class="form-control" value="{{ old('bathrooms', $villa->bathrooms) }}" required>
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Max Guests <span class="text-danger">*</span></label>
                            <input type="number" name="max_guests" min="1" max="50" class="form-control" value="{{ old('max_guests', $villa->max_guests) }}" required>
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Floor Area (m²)</label>
                            <input type="number" name="area_sqm" min="10" class="form-control" value="{{ old('area_sqm', $villa->area_sqm) }}">
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">3. Pricing & Operational Rates</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Nightly Rate ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="price_per_night" class="form-control" value="{{ old('price_per_night', $villa->price_per_night) }}" required>
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Weekend Rate ($)</label>
                            <input type="number" step="0.01" name="weekend_price_per_night" class="form-control" value="{{ old('weekend_price_per_night', $villa->weekend_price_per_night) }}">
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Cleaning Turn Fee ($)</label>
                            <input type="number" step="0.01" name="cleaning_fee" class="form-control" value="{{ old('cleaning_fee', $villa->cleaning_fee) }}">
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Security Deposit ($)</label>
                            <input type="number" step="0.01" name="security_deposit" class="form-control" value="{{ old('security_deposit', $villa->security_deposit) }}">
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">4. Location & Operational Status</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Location Zone <span class="text-danger">*</span></label>
                            <input type="text" name="location" class="form-control" value="{{ old('location', $villa->location) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="available" {{ old('status', $villa->status) == 'available' ? 'selected' : '' }}>Available for Booking</option>
                                <option value="occupied" {{ old('status', $villa->status) == 'occupied' ? 'selected' : '' }}>Occupied by Guest</option>
                                <option value="cleaning" {{ old('status', $villa->status) == 'cleaning' ? 'selected' : '' }}>Housekeeping / Cleaning</option>
                                <option value="maintenance" {{ old('status', $villa->status) == 'maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                                <option value="reserved" {{ old('status', $villa->status) == 'reserved' ? 'selected' : '' }}>Management Reserved</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-center">
                            <div class="form-check mt-3">
                                <input type="checkbox" name="is_featured" id="is_featured" class="form-check-input" value="1" {{ old('is_featured', $villa->is_featured) ? 'checked' : '' }}>
                                <label for="is_featured" class="form-check-label small fw-semibold">Mark as Featured VIP Villa</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Physical Address / Plot Reference</label>
                            <input type="text" name="address" class="form-control" value="{{ old('address', $villa->address) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Description & Highlights</label>
                            <textarea name="description" rows="3" class="form-control">{{ old('description', $villa->description) }}</textarea>
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">5. Luxury Amenities & Equipment</h5>
                    <div class="row g-2 mb-4">
                        @php $currentAmenities = $villa->amenities ?? []; @endphp
                        @foreach($standardAmenities as $amenity)
                            <div class="col-md-3 col-sm-4 col-6">
                                <div class="form-check">
                                    <input type="checkbox" name="amenities[]" value="{{ $amenity }}" id="amenity_{{ $loop->index }}" class="form-check-input" {{ in_array($amenity, old('amenities', $currentAmenities)) ? 'checked' : '' }}>
                                    <label for="amenity_{{ $loop->index }}" class="form-check-label small text-secondary">
                                        {{ $amenity }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="if(confirm('Are you sure you want to delete this villa?')) document.getElementById('delete-villa-form').submit();">
                            <i class="fa-solid fa-trash me-1"></i> Delete Villa
                        </button>

                        <div class="d-flex gap-2">
                            <a href="{{ route('villas.show', $villa) }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-gold px-4">
                                <i class="fa-solid fa-save me-2"></i> Update Villa
                            </button>
                        </div>
                    </div>
                </form>

                <form id="delete-villa-form" action="{{ route('villas.destroy', $villa) }}" method="POST" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
