@extends('layouts.app')

@section('title', 'Reservations & Bookings')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Reservations & Guest Stays</h3>
        <p class="text-muted small mb-0">Manage guest bookings, check-ins, payments, and turnover schedules.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div class="btn-group btn-group-sm">
            <a href="{{ route('bookings.index') }}" class="btn btn-dark active">
                <i class="fa-solid fa-list me-1"></i> List
            </a>
            <a href="{{ route('bookings.calendar') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-calendar-days me-1"></i> Tape Chart
            </a>
        </div>
        <a href="{{ route('bookings.create') }}" class="btn btn-gold btn-sm d-flex align-items-center gap-2">
            <i class="fa-solid fa-calendar-plus"></i>
            <span>New Reservation</span>
        </a>
    </div>
</div>

<!-- Filters Bar -->
<div class="card card-luxury mb-4 p-3">
    <form action="{{ route('bookings.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-4 col-sm-6">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Search by code, guest name, email, or villa..." value="{{ $filters['search'] ?? '' }}">
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <select name="villa_id" class="form-select form-select-sm">
                <option value="">All Villas</option>
                @foreach($villas as $v)
                    <option value="{{ $v->id }}" {{ ($filters['villa_id'] ?? '') == $v->id ? 'selected' : '' }}>{{ $v->code }} - {{ $v->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 col-sm-4">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Booking Statuses</option>
                <option value="confirmed" {{ ($filters['status'] ?? '') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                <option value="checked_in" {{ ($filters['status'] ?? '') === 'checked_in' ? 'selected' : '' }}>Checked In</option>
                <option value="checked_out" {{ ($filters['status'] ?? '') === 'checked_out' ? 'selected' : '' }}>Checked Out</option>
                <option value="cancelled" {{ ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        <div class="col-md-2 col-sm-4">
            <select name="payment_status" class="form-select form-select-sm">
                <option value="">All Payment States</option>
                <option value="paid" {{ ($filters['payment_status'] ?? '') === 'paid' ? 'selected' : '' }}>Fully Paid</option>
                <option value="partial" {{ ($filters['payment_status'] ?? '') === 'partial' ? 'selected' : '' }}>Partial Balance</option>
                <option value="pending" {{ ($filters['payment_status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="refunded" {{ ($filters['payment_status'] ?? '') === 'refunded' ? 'selected' : '' }}>Refunded</option>
            </select>
        </div>
        <div class="col-md-1 col-sm-4 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-dark w-100" title="Filter">
                <i class="fa-solid fa-filter"></i>
            </button>
            <a href="{{ route('bookings.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        </div>
    </form>
</div>

<!-- Bookings Table -->
<div class="card card-luxury">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Reference</th>
                    <th>Villa</th>
                    <th>Guest</th>
                    <th>Stay Window</th>
                    <th>Total ($)</th>
                    <th>Balance Due</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                    <tr>
                        <td>
                            <a href="{{ route('bookings.show', $booking) }}" class="fw-bold text-decoration-none text-dark">
                                {{ $booking->booking_code }}
                            </a>
                        </td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $booking->villa->name }}</span>
                            <span class="badge bg-light text-secondary border ms-1">{{ $booking->villa->code }}</span>
                        </td>
                        <td>
                            {{ $booking->guest->full_name }}
                            <span class="badge {{ $booking->guest->getVipBadgeClass() }} ms-1" style="font-size: 0.65rem;">
                                {{ strtoupper($booking->guest->vip_level) }}
                            </span>
                        </td>
                        <td class="small">
                            {{ \Carbon\Carbon::parse($booking->check_in)->format('M d') }} - {{ \Carbon\Carbon::parse($booking->check_out)->format('M d, Y') }}
                            <div class="text-muted" style="font-size: 0.72rem;">{{ $booking->total_nights }} nights &bull; {{ $booking->adults }} adults</div>
                        </td>
                        <td class="fw-bold">${{ number_format($booking->total_amount, 2) }}</td>
                        <td>
                            @if($booking->balance_due > 0)
                                <span class="text-danger fw-semibold small">${{ number_format($booking->balance_due, 2) }}</span>
                            @else
                                <span class="text-success small"><i class="fa-solid fa-circle-check me-1"></i>Settled</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $booking->getPaymentBadgeClass() }}">
                                {{ ucfirst($booking->payment_status) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $booking->getStatusBadgeClass() }}">
                                {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                @if($booking->status === 'confirmed')
                                    <form action="{{ route('bookings.check-in', $booking) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Check In Guest">
                                            <i class="fa-solid fa-key"></i>
                                        </button>
                                    </form>
                                @elseif($booking->status === 'checked_in')
                                    <form action="{{ route('bookings.check-out', $booking) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-warning" title="Check Out Guest">
                                            <i class="fa-solid fa-door-open"></i>
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('bookings.show', $booking) }}" class="btn btn-light" title="View Details">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('bookings.invoice', $booking) }}" class="btn btn-light" title="Guest Folio / Invoice" target="_blank">
                                    <i class="fa-solid fa-receipt"></i>
                                </a>
                                <a href="{{ route('bookings.edit', $booking) }}" class="btn btn-light" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-calendar-xmark fs-2 text-muted mb-2 d-block"></i>
                            No bookings matching your criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $bookings->links('pagination::bootstrap-5') }}
</div>
@endsection
