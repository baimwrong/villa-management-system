@extends('layouts.app')

@section('title', 'Guest Dossier: ' . $guest->full_name)

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge {{ $guest->getVipBadgeClass() }} fs-6">
                <i class="fa-solid fa-crown me-1"></i>{{ strtoupper($guest->vip_level) }}
            </span>
            <span class="badge bg-light text-secondary border fs-6">
                {{ $guest->bookings->count() }} Total Stays
            </span>
        </div>
        <h3 class="fw-bold mb-0 text-dark">{{ $guest->full_name }}</h3>
        <p class="text-muted small mb-0">{{ $guest->email }} &bull; {{ $guest->phone ?: 'No phone provided' }} &bull; {{ $guest->nationality ?: 'International' }}</p>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('bookings.create') }}" class="btn btn-gold btn-sm d-flex align-items-center gap-2">
            <i class="fa-solid fa-calendar-plus"></i>
            <span>Book New Stay</span>
        </a>
        <a href="{{ route('guests.edit', $guest) }}" class="btn btn-outline-dark btn-sm d-flex align-items-center gap-2">
            <i class="fa-solid fa-pen-to-square"></i>
            <span>Edit Profile</span>
        </a>
        <a href="{{ route('guests.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Dossier Details -->
    <div class="col-lg-4">
        <div class="card card-luxury mb-4">
            <div class="card-header bg-white">
                <strong><i class="fa-solid fa-id-card text-warning me-2"></i>Identification & Metrics</strong>
            </div>
            <div class="card-body p-3">
                <div class="mb-3">
                    <span class="text-muted small d-block">Passport / National ID</span>
                    <span class="fw-bold text-dark">{{ $guest->id_or_passport ?: 'Unrecorded' }}</span>
                </div>
                <div class="mb-3">
                    <span class="text-muted small d-block">Citizenship / Country</span>
                    <span class="fw-bold text-dark">{{ $guest->nationality ?: 'Not specified' }}</span>
                </div>
                <div class="mb-3">
                    <span class="text-muted small d-block">Total Lifetime Spend</span>
                    <span class="fs-4 fw-bold text-success">${{ number_format($guest->bookings->sum('paid_amount'), 2) }}</span>
                </div>

                @if($guest->special_requests)
                    <div class="p-3 bg-light rounded-3 mt-3">
                        <span class="small fw-bold text-dark d-block mb-1">
                            <i class="fa-solid fa-bell-concierge text-warning me-1"></i> Special Requests & Preferences:
                        </span>
                        <p class="small text-secondary mb-0">{{ $guest->special_requests }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column: Stays & Bookings History -->
    <div class="col-lg-8">
        <div class="card card-luxury">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong><i class="fa-solid fa-clock-rotate-left text-warning me-2"></i>Reservation History</strong>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Booking Code</th>
                            <th>Villa</th>
                            <th>Stay Window</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($guest->bookings as $b)
                            <tr>
                                <td>
                                    <a href="{{ route('bookings.show', $b) }}" class="fw-bold text-decoration-none text-dark">
                                        {{ $b->booking_code }}
                                    </a>
                                </td>
                                <td>{{ $b->villa->name }}</td>
                                <td class="small">{{ \Carbon\Carbon::parse($b->check_in)->format('M d') }} - {{ \Carbon\Carbon::parse($b->check_out)->format('M d, Y') }}</td>
                                <td class="fw-bold">${{ number_format($b->total_amount, 2) }}</td>
                                <td><span class="badge {{ $b->getPaymentBadgeClass() }}">{{ ucfirst($b->payment_status) }}</span></td>
                                <td><span class="badge {{ $b->getStatusBadgeClass() }}">{{ ucfirst($b->status) }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('bookings.show', $b) }}" class="btn btn-sm btn-light" title="View Folio">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted small">No stay history recorded for this guest.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
