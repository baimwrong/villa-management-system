@extends('layouts.app')

@section('title', 'Luxury Villas Fleet')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Luxury Villa Portfolio</h3>
        <p class="text-muted small mb-0">Manage property configurations, availability, rates, and luxury amenities.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('villas.create') }}" class="btn btn-gold btn-sm d-flex align-items-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>Add New Villa</span>
        </a>
    </div>
</div>

<!-- Filters Bar -->
<div class="card card-luxury mb-4 p-3">
    <form action="{{ route('villas.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-4 col-sm-6">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Search by name, code, location..." value="{{ $filters['search'] ?? '' }}">
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <select name="category" class="form-select form-select-sm">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ ($filters['category'] ?? '') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 col-sm-4">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <option value="available" {{ ($filters['status'] ?? '') === 'available' ? 'selected' : '' }}>Available</option>
                <option value="occupied" {{ ($filters['status'] ?? '') === 'occupied' ? 'selected' : '' }}>Occupied</option>
                <option value="cleaning" {{ ($filters['status'] ?? '') === 'cleaning' ? 'selected' : '' }}>Cleaning</option>
                <option value="maintenance" {{ ($filters['status'] ?? '') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                <option value="reserved" {{ ($filters['status'] ?? '') === 'reserved' ? 'selected' : '' }}>Reserved</option>
            </select>
        </div>
        <div class="col-md-2 col-sm-4">
            <select name="bedrooms" class="form-select form-select-sm">
                <option value="">Any Bedrooms</option>
                <option value="2" {{ ($filters['bedrooms'] ?? '') === '2' ? 'selected' : '' }}>2+ Bedrooms</option>
                <option value="3" {{ ($filters['bedrooms'] ?? '') === '3' ? 'selected' : '' }}>3+ Bedrooms</option>
                <option value="4" {{ ($filters['bedrooms'] ?? '') === '4' ? 'selected' : '' }}>4+ Bedrooms</option>
                <option value="5" {{ ($filters['bedrooms'] ?? '') === '5' ? 'selected' : '' }}>5+ Bedrooms</option>
            </select>
        </div>
        <div class="col-md-1 col-sm-4 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-dark w-100" title="Apply Filter">
                <i class="fa-solid fa-filter"></i>
            </button>
            <a href="{{ route('villas.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        </div>
    </form>
</div>

<!-- Villa Grid -->
<div class="row g-4 mb-4">
    @forelse($villas as $villa)
        <div class="col-xl-4 col-md-6">
            <div class="card card-luxury h-100 d-flex flex-column justify-content-between overflow-hidden">
                <div>
                    <!-- Villa Header Accent -->
                    <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-dark fw-bold px-2 py-1">{{ $villa->code }}</span>
                            <span class="badge bg-secondary-subtle text-secondary border">{{ $villa->category }}</span>
                        </div>
                        <span class="badge {{ $villa->getStatusBadgeClass() }} text-capitalize px-2 py-1">
                            {{ $villa->status }}
                        </span>
                    </div>

                    @if($villa->featured_image)
                        <div class="position-relative overflow-hidden" style="height: 180px;">
                            <img src="{{ asset('storage/' . $villa->featured_image) }}" alt="{{ $villa->name }}" class="w-100 h-100" style="object-fit: cover;">
                        </div>
                    @endif

                    <div class="p-4">
                        <h5 class="fw-bold mb-1">
                            <a href="{{ route('villas.show', $villa) }}" class="text-decoration-none text-dark">
                                {{ $villa->name }}
                            </a>
                        </h5>
                        <div class="text-muted small mb-3">
                            <i class="fa-solid fa-map-pin me-1 text-danger"></i> {{ $villa->location }}
                        </div>

                        <p class="text-secondary small mb-3 text-truncate-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; min-height: 40px;">
                            {{ $villa->description ?: 'Exclusive luxury villa with bespoke concierge service and panoramic views.' }}
                        </p>

                        <!-- Key Specs -->
                        <div class="d-flex align-items-center justify-content-between bg-light p-2 rounded-3 text-secondary small mb-3">
                            <span title="Bedrooms"><i class="fa-solid fa-bed me-1 text-dark"></i> {{ $villa->bedrooms }} Beds</span>
                            <span title="Bathrooms"><i class="fa-solid fa-bath me-1 text-dark"></i> {{ $villa->bathrooms }} Baths</span>
                            <span title="Max Guests"><i class="fa-solid fa-user-group me-1 text-dark"></i> Up to {{ $villa->max_guests }}</span>
                            @if($villa->area_sqm)
                                <span title="Area"><i class="fa-solid fa-vector-square me-1 text-dark"></i> {{ $villa->area_sqm }} m²</span>
                            @endif
                        </div>

                        <!-- Amenities Pills -->
                        @if(!empty($villa->amenities))
                            <div class="d-flex flex-wrap gap-1 mb-2">
                                @foreach(array_slice($villa->amenities, 0, 4) as $amenity)
                                    <span class="badge bg-light text-dark border" style="font-size: 0.7rem; font-weight: 500;">
                                        <i class="fa-solid fa-check text-warning me-1"></i>{{ $amenity }}
                                    </span>
                                @endforeach
                                @if(count($villa->amenities) > 4)
                                    <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">+{{ count($villa->amenities) - 4 }} more</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Footer with Rate & Actions -->
                <div class="p-3 bg-white border-top d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small d-block" style="font-size: 0.72rem;">NIGHTLY RATE</span>
                        <span class="fw-bold text-dark fs-5">${{ number_format($villa->price_per_night, 2) }}</span>
                    </div>

                    <div class="d-flex gap-1">
                        <a href="{{ route('bookings.create', ['villa_id' => $villa->id]) }}" class="btn btn-sm btn-gold" title="Reserve Villa">
                            <i class="fa-solid fa-calendar-plus me-1"></i> Book
                        </a>
                        <a href="{{ route('villas.show', $villa) }}" class="btn btn-sm btn-outline-dark" title="View Details">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        <a href="{{ route('villas.edit', $villa) }}" class="btn btn-sm btn-outline-secondary" title="Edit Villa">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <div class="p-4 bg-white rounded-3 shadow-sm d-inline-block">
                <i class="fa-solid fa-house-crack fs-1 text-muted mb-3"></i>
                <h5>No Villas Found</h5>
                <p class="text-muted small mb-3">No villas matched your search criteria or none have been imported yet.</p>
                <a href="{{ route('villas.create') }}" class="btn btn-gold btn-sm">Create New Villa</a>
            </div>
        </div>
    @endforelse
</div>

<div class="d-flex justify-content-center">
    {{ $villas->links('pagination::bootstrap-5') }}
</div>
@endsection
