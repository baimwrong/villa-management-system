@extends('layouts.app')

@section('title', 'Tape Chart & Reservations Calendar')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Tape Chart & Occupancy Calendar</h3>
        <p class="text-muted small mb-0">Visual property management dispatch matrix showing estate occupancy, turnover gaps, and stays.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <!-- View Toggle -->
        <div class="btn-group btn-group-sm">
            <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-list me-1"></i> List
            </a>
            <a href="{{ route('bookings.calendar') }}" class="btn btn-dark active">
                <i class="fa-solid fa-calendar-days me-1"></i> Tape Chart
            </a>
        </div>
        <a href="{{ route('bookings.create') }}" class="btn btn-gold btn-sm d-flex align-items-center gap-2">
            <i class="fa-solid fa-calendar-plus"></i>
            <span>New Reservation</span>
        </a>
    </div>
</div>

<!-- Month Selector Bar -->
<div class="card card-luxury mb-4 p-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('bookings.calendar', ['month' => $prevMonth, 'year' => $prevYear]) }}" class="btn btn-sm btn-outline-dark" title="Previous Month">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
            <h4 class="fw-bold mb-0 text-dark px-2">{{ $monthName }}</h4>
            <a href="{{ route('bookings.calendar', ['month' => $nextMonth, 'year' => $nextYear]) }}" class="btn btn-sm btn-outline-dark" title="Next Month">
                <i class="fa-solid fa-chevron-right"></i>
            </a>
            <a href="{{ route('bookings.calendar') }}" class="btn btn-sm btn-light border ms-2">
                Today
            </a>
        </div>

        <!-- Legend -->
        <div class="d-flex flex-wrap align-items-center gap-3 small">
            <span class="d-flex align-items-center gap-1">
                <span class="badge bg-info text-dark">&nbsp;</span>
                <span class="text-secondary">Confirmed Stay</span>
            </span>
            <span class="d-flex align-items-center gap-1">
                <span class="badge bg-success">&nbsp;</span>
                <span class="text-secondary">Checked In (In-House)</span>
            </span>
            <span class="d-flex align-items-center gap-1">
                <span class="badge bg-secondary">&nbsp;</span>
                <span class="text-secondary">Checked Out</span>
            </span>
            <span class="d-flex align-items-center gap-1">
                <span class="badge bg-warning text-dark">&nbsp;</span>
                <span class="text-secondary">Turnover Day</span>
            </span>
        </div>
    </div>
</div>

<!-- Calendar Matrix -->
<div class="card card-luxury">
    <div class="table-responsive" style="max-height: 700px; overflow-x: auto;">
        <table class="table table-bordered align-middle mb-0 text-center" style="font-size: 0.8rem; min-width: 1200px;">
            <thead class="table-light sticky-top" style="z-index: 10;">
                <tr>
                    <th style="min-width: 220px; width: 220px; text-align: left;" class="bg-white sticky-left">
                        Luxury Villa
                    </th>
                    @foreach($daysInMonth as $day)
                        <th class="{{ $day['isToday'] ? 'bg-warning-subtle text-dark border-warning' : ($day['isWeekend'] ? 'bg-light text-secondary' : 'bg-white') }}" style="min-width: 36px; padding: 6px 2px;">
                            <div class="fw-bold fs-6">{{ $day['day'] }}</div>
                            <div style="font-size: 0.65rem;" class="text-uppercase">{{ substr($day['dayName'], 0, 2) }}</div>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($villas as $villa)
                    <tr>
                        <td style="text-align: left; background-color: #ffffff;" class="sticky-left fw-semibold">
                            <a href="{{ route('villas.show', $villa) }}" class="text-decoration-none text-dark d-block text-truncate" style="max-width: 200px;">
                                <span class="badge bg-dark me-1" style="font-size: 0.7rem;">{{ $villa->code }}</span>
                                {{ $villa->name }}
                            </a>
                            <span class="badge {{ $villa->getStatusBadgeClass() }} mt-1" style="font-size: 0.65rem;">
                                {{ ucfirst($villa->status) }}
                            </span>
                            <span class="text-muted ms-1" style="font-size: 0.7rem;">${{ number_format($villa->price_per_night, 0) }}/nt</span>
                        </td>

                        @php
                            $bookingsForVilla = $villaBookings[$villa->id] ?? collect();
                        @endphp

                        @foreach($daysInMonth as $day)
                            @php
                                $currentDateStr = $day['date'];
                                $activeBooking = $bookingsForVilla->first(function ($b) use ($currentDateStr) {
                                    $in = \Carbon\Carbon::parse($b->check_in)->toDateString();
                                    $out = \Carbon\Carbon::parse($b->check_out)->toDateString();
                                    return $currentDateStr >= $in && $currentDateStr < $out;
                                });

                                $isTurnoverDeparture = $bookingsForVilla->first(function ($b) use ($currentDateStr) {
                                    return \Carbon\Carbon::parse($b->check_out)->toDateString() === $currentDateStr;
                                });
                            @endphp

                            @if($activeBooking)
                                @php
                                    $isCheckInDay = \Carbon\Carbon::parse($activeBooking->check_in)->toDateString() === $currentDateStr;
                                    $badgeClass = match($activeBooking->status) {
                                        'checked_in' => 'bg-success text-white',
                                        'confirmed' => 'bg-info text-dark',
                                        default => 'bg-secondary text-white'
                                    };
                                @endphp
                                <td class="p-1 {{ $day['isToday'] ? 'border-warning' : '' }}" style="background-color: rgba(194, 155, 56, 0.08);">
                                    <a href="{{ route('bookings.show', $activeBooking) }}"
                                       class="d-block text-decoration-none rounded py-1 px-1 {{ $badgeClass }} text-truncate"
                                       title="[{{ $activeBooking->booking_code }}] {{ $activeBooking->guest->full_name }} ({{ \Carbon\Carbon::parse($activeBooking->check_in)->format('M d') }} - {{ \Carbon\Carbon::parse($activeBooking->check_out)->format('M d') }})"
                                       style="font-size: 0.7rem; font-weight: 600;">
                                        @if($isCheckInDay)
                                            <i class="fa-solid fa-arrow-right-to-bracket me-1"></i>{{ Str::limit($activeBooking->guest->first_name, 6) }}
                                        @else
                                            &bull;
                                        @endif
                                    </a>
                                </td>
                            @elseif($isTurnoverDeparture)
                                <td class="p-1 {{ $day['isToday'] ? 'bg-warning-subtle' : '' }}" style="background-color: #fef9c3;">
                                    <a href="{{ route('bookings.create', ['villa_id' => $villa->id, 'check_in' => $currentDateStr]) }}"
                                       class="d-block text-decoration-none text-muted py-1"
                                       title="Checkout day of [{{ $isTurnoverDeparture->booking_code }}]. Available for next check-in!">
                                        <i class="fa-solid fa-arrow-right-from-bracket text-warning" style="font-size: 0.75rem;"></i>
                                    </a>
                                </td>
                            @else
                                <td class="p-1 {{ $day['isToday'] ? 'bg-warning-subtle' : ($day['isWeekend'] ? 'bg-light-subtle' : '') }}">
                                    <a href="{{ route('bookings.create', ['villa_id' => $villa->id, 'check_in' => $currentDateStr]) }}"
                                       class="d-block text-muted text-decoration-none py-1 hover-slot"
                                       title="Available — click to book {{ $villa->name }} for {{ $day['date'] }}">
                                        <span class="text-transparent">&middot;</span>
                                    </a>
                                </td>
                            @endif
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($daysInMonth) + 1 }}" class="py-4 text-muted text-center">
                            No villas found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
    .sticky-left {
        position: sticky;
        left: 0;
        z-index: 5;
        box-shadow: 2px 0 5px rgba(0,0,0,0.05);
    }
    .hover-slot:hover {
        background-color: rgba(194, 155, 56, 0.2);
        border-radius: 4px;
    }
    .text-transparent {
        color: transparent;
    }
    .hover-slot:hover .text-transparent {
        color: #c29b38;
    }
</style>
@endsection
