@extends('layouts.app')

@section('title', $villa->name)

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-dark fs-6">{{ $villa->code }}</span>
            <span class="badge {{ $villa->getStatusBadgeClass() }} fs-6 text-capitalize">{{ $villa->status }}</span>
            @if($villa->is_featured)
                <span class="badge bg-warning text-dark"><i class="fa-solid fa-crown me-1"></i>Featured Villa</span>
            @endif
        </div>
        <h3 class="fw-bold mb-0 text-dark">{{ $villa->name }}</h3>
        <p class="text-muted small mb-0"><i class="fa-solid fa-location-dot me-1 text-danger"></i>{{ $villa->location }} &bull; {{ $villa->address ?: 'Resort Grounds' }}</p>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('bookings.create', ['villa_id' => $villa->id]) }}" class="btn btn-gold btn-sm d-flex align-items-center gap-2">
            <i class="fa-solid fa-calendar-plus"></i>
            <span>Create Reservation</span>
        </a>
        <a href="{{ route('villas.edit', $villa) }}" class="btn btn-outline-dark btn-sm d-flex align-items-center gap-2">
            <i class="fa-solid fa-pen-to-square"></i>
            <span>Edit Villa</span>
        </a>
        <a href="{{ route('villas.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
    </div>
</div>

<!-- Active Guest Alert (if occupied) -->
@if($activeBooking)
    <div class="alert alert-primary d-flex align-items-center justify-content-between shadow-sm mb-4" role="alert">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-white text-primary p-2">
                <i class="fa-solid fa-user-check fs-4"></i>
            </div>
            <div>
                <span class="fw-bold d-block">Currently Occupied by {{ $activeBooking->guest->full_name }}</span>
                <span class="small text-muted">
                    Reservation: <strong>{{ $activeBooking->booking_code }}</strong> &bull;
                    Check-in: {{ \Carbon\Carbon::parse($activeBooking->check_in)->format('M d, Y') }} &bull;
                    Check-out: {{ \Carbon\Carbon::parse($activeBooking->check_out)->format('M d, Y') }}
                </span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('bookings.show', $activeBooking) }}" class="btn btn-sm btn-light">Manage Booking</a>
            @if($activeBooking->status === 'checked_in')
                <form action="{{ route('bookings.check-out', $activeBooking) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-warning">Process Checkout</button>
                </form>
            @endif
        </div>
    </div>
@endif

<div class="row g-4">
    <!-- Left Column: Specs, Rates, Amenities -->
    <div class="col-lg-8">
        <!-- Specs & Rates Card -->
        <div class="card card-luxury mb-4">
            <div class="card-header bg-white">
                <strong><i class="fa-solid fa-circle-info text-warning me-2"></i>Property Specifications & Tariffs</strong>
            </div>
            <div class="card-body">
                <div class="row g-3 text-center mb-4">
                    <div class="col-3 p-3 bg-light rounded-3">
                        <span class="text-muted small d-block">Bedrooms</span>
                        <span class="fs-4 fw-bold text-dark"><i class="fa-solid fa-bed text-primary me-1"></i>{{ $villa->bedrooms }}</span>
                    </div>
                    <div class="col-3 p-3 bg-light rounded-3">
                        <span class="text-muted small d-block">Bathrooms</span>
                        <span class="fs-4 fw-bold text-dark"><i class="fa-solid fa-bath text-info me-1"></i>{{ $villa->bathrooms }}</span>
                    </div>
                    <div class="col-3 p-3 bg-light rounded-3">
                        <span class="text-muted small d-block">Capacity</span>
                        <span class="fs-4 fw-bold text-dark"><i class="fa-solid fa-users text-success me-1"></i>{{ $villa->max_guests }}</span>
                    </div>
                    <div class="col-3 p-3 bg-light rounded-3">
                        <span class="text-muted small d-block">Living Area</span>
                        <span class="fs-4 fw-bold text-dark"><i class="fa-solid fa-vector-square text-warning me-1"></i>{{ $villa->area_sqm ?: '—' }} m²</span>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-3 col-6">
                        <div class="border p-2 rounded text-center">
                            <span class="small text-muted d-block">Standard Night</span>
                            <span class="fw-bold text-dark fs-5">${{ number_format($villa->price_per_night, 2) }}</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="border p-2 rounded text-center">
                            <span class="small text-muted d-block">Weekend Night</span>
                            <span class="fw-bold text-dark fs-5">${{ number_format($villa->weekend_price_per_night ?: $villa->price_per_night, 2) }}</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="border p-2 rounded text-center">
                            <span class="small text-muted d-block">Turnover Cleaning</span>
                            <span class="fw-bold text-dark fs-5">${{ number_format($villa->cleaning_fee, 2) }}</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="border p-2 rounded text-center">
                            <span class="small text-muted d-block">Security Deposit</span>
                            <span class="fw-bold text-dark fs-5">${{ number_format($villa->security_deposit, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <h6 class="fw-bold text-dark mb-2">Description</h6>
                    <p class="text-secondary leading-relaxed mb-0">
                        {{ $villa->description ?: 'No detailed description specified.' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Amenities Card -->
        <div class="card card-luxury mb-4">
            <div class="card-header bg-white">
                <strong><i class="fa-solid fa-wand-magic-sparkles text-warning me-2"></i>Luxury Amenities & Features</strong>
            </div>
            <div class="card-body">
                @if(!empty($villa->amenities))
                    <div class="row g-2">
                        @foreach($villa->amenities as $amenity)
                            <div class="col-md-4 col-sm-6">
                                <div class="p-2 border rounded bg-light-subtle d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-circle-check text-warning"></i>
                                    <span class="small fw-semibold text-dark">{{ $amenity }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted small mb-0">No specific amenities configured.</p>
                @endif
            </div>
        </div>

        <!-- Recent Reservations for this Villa -->
        <div class="card card-luxury">
            <div class="card-header d-flex justify-content-between align-items-center bg-white">
                <strong><i class="fa-solid fa-calendar-check text-warning me-2"></i>Reservation History</strong>
                <a href="{{ route('bookings.create', ['villa_id' => $villa->id]) }}" class="btn btn-sm btn-gold">
                    + New Booking
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Code</th>
                            <th>Guest</th>
                            <th>Stay Dates</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($villa->bookings as $b)
                            <tr>
                                <td>
                                    <a href="{{ route('bookings.show', $b) }}" class="fw-bold text-decoration-none text-dark">
                                        {{ $b->booking_code }}
                                    </a>
                                </td>
                                <td>{{ $b->guest->full_name }}</td>
                                <td class="small">{{ \Carbon\Carbon::parse($b->check_in)->format('M d') }} - {{ \Carbon\Carbon::parse($b->check_out)->format('M d, Y') }}</td>
                                <td class="fw-bold">${{ number_format($b->total_amount, 2) }}</td>
                                <td><span class="badge {{ $b->getStatusBadgeClass() }}">{{ ucfirst($b->status) }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-3 text-muted small">No past or upcoming reservations for this villa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Quick Status Changer & Maintenance Tasks -->
    <div class="col-lg-4">
        <!-- Quick State Controller -->
        <div class="card card-luxury mb-4">
            <div class="card-header bg-white">
                <strong><i class="fa-solid fa-toggle-on text-warning me-2"></i>Live State Controller</strong>
            </div>
            <div class="card-body">
                <form action="{{ route('villas.status', $villa) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3">
                        <label class="form-label small text-muted">Change Property State:</label>
                        <select name="status" class="form-select">
                            <option value="available" {{ $villa->status == 'available' ? 'selected' : '' }}>Available for Guests</option>
                            <option value="occupied" {{ $villa->status == 'occupied' ? 'selected' : '' }}>Occupied by Guest</option>
                            <option value="cleaning" {{ $villa->status == 'cleaning' ? 'selected' : '' }}>Housekeeping Cleaning</option>
                            <option value="maintenance" {{ $villa->status == 'maintenance' ? 'selected' : '' }}>Under Repair / Maintenance</option>
                            <option value="reserved" {{ $villa->status == 'reserved' ? 'selected' : '' }}>Management Reserved</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-dark btn-sm w-100">
                        Update State Immediately
                    </button>
                </form>
            </div>
        </div>

        <!-- Maintenance & Housekeeping Tasks -->
        <div class="card card-luxury">
            <div class="card-header d-flex justify-content-between align-items-center bg-white">
                <strong><i class="fa-solid fa-screwdriver-wrench text-warning me-2"></i>Tasks & Logs</strong>
                <a href="{{ route('maintenance.create') }}" class="btn btn-sm btn-outline-dark py-0 px-2" style="font-size: 0.75rem;">
                    + Log
                </a>
            </div>
            <div class="card-body p-3">
                @forelse($villa->maintenanceTasks as $task)
                    <div class="p-2 mb-2 border rounded bg-light-subtle">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <span class="fw-bold text-dark small">{{ $task->title }}</span>
                            <span class="badge {{ $task->getStatusBadgeClass() }}">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                        </div>
                        <div class="text-muted small" style="font-size: 0.75rem;">
                            Priority: <span class="badge {{ $task->getPriorityBadgeClass() }}">{{ $task->priority }}</span> &bull;
                            Staff: {{ $task->assignedStaff?->name ?? 'Unassigned' }}
                        </div>
                    </div>
                @empty
                    <p class="text-muted small text-center my-2">No maintenance tasks recorded.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
