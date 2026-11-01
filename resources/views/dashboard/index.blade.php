@extends('layouts.app')

@section('title', 'Executive Operations Dashboard')

@section('content')
<div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Executive Operations Command</h3>
        <p class="text-muted small mb-0">
            Real-time resort occupancy, villa states, financial telemetry, and housekeeping schedule.
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('bookings.create') }}" class="btn btn-gold btn-sm d-flex align-items-center gap-2">
            <i class="fa-solid fa-calendar-plus"></i>
            <span>New Reservation</span>
        </a>
        @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.bulk-import.index') }}" class="btn btn-outline-warning text-dark btn-sm d-flex align-items-center gap-2">
                <i class="fa-solid fa-file-import text-warning"></i>
                <span>Bulk Import</span>
            </a>
        @endif
    </div>
</div>

@if($overdueCheckOuts->isNotEmpty())
    <div class="alert alert-danger d-flex align-items-center justify-content-between shadow-sm mb-4" role="alert">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-white text-danger p-2 fs-5">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <span class="fw-bold d-block fs-6">Overstay Alert: {{ $overdueCheckOuts->count() }} In-House Guest(s) Past Scheduled Check-Out</span>
                <span class="small text-danger-emphasis">
                    Departure dates have passed but checkout has not been processed. Stays must be settled before turnovers can begin.
                </span>
            </div>
        </div>
        <div class="d-flex gap-2">
            @foreach($overdueCheckOuts->take(3) as $overdue)
                <a href="{{ route('bookings.show', $overdue) }}" class="btn btn-sm btn-dark text-nowrap">
                    [{{ $overdue->villa->code }}] {{ $overdue->guest->last_name }}
                </a>
            @endforeach
        </div>
    </div>
@endif

<!-- Key Performance Metrics -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="card card-luxury h-100 p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold" style="letter-spacing: 0.05em;">Villa Fleet</span>
                    <h2 class="fw-bold text-dark mb-1 mt-1">{{ $totalVillas }}</h2>
                    <div class="d-flex gap-2 mt-2" style="font-size: 0.76rem;">
                        <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="fa-solid fa-circle-check me-1"></i>{{ $availableVillas }} Free</span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="fa-solid fa-key me-1"></i>{{ $occupiedVillas }} Busy</span>
                    </div>
                </div>
                <div class="p-3 rounded-3 bg-primary-subtle text-primary">
                    <i class="fa-solid fa-house-chimney-window fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card card-luxury h-100 p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold" style="letter-spacing: 0.05em;">Live Occupancy</span>
                    <h2 class="fw-bold text-dark mb-1 mt-1">{{ $occupancyRate }}%</h2>
                    <div class="progress mt-2" style="height: 6px; width: 140px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $occupancyRate }}%"></div>
                    </div>
                    <div class="text-muted mt-2" style="font-size: 0.76rem;">
                        {{ $cleaningVillas }} cleaning, {{ $maintenanceVillas }} in repair
                    </div>
                </div>
                <div class="p-3 rounded-3 bg-warning-subtle text-warning">
                    <i class="fa-solid fa-chart-pie fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card card-luxury h-100 p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold" style="letter-spacing: 0.05em;">Month Revenue</span>
                    <h2 class="fw-bold text-dark mb-1 mt-1">${{ number_format($currentMonthRevenue, 2) }}</h2>
                    <div class="text-success small fw-semibold mt-2" style="font-size: 0.76rem;">
                        <i class="fa-solid fa-arrow-trend-up me-1"></i> Settled guest payments
                    </div>
                </div>
                <div class="p-3 rounded-3 bg-success-subtle text-success">
                    <i class="fa-solid fa-vault fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card card-luxury h-100 p-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold" style="letter-spacing: 0.05em;">Guest Roster</span>
                    <h2 class="fw-bold text-dark mb-1 mt-1">{{ $totalGuests }}</h2>
                    <div class="d-flex gap-2 mt-2" style="font-size: 0.76rem;">
                        <span class="badge bg-warning text-dark"><i class="fa-solid fa-crown me-1"></i>{{ $vipGuestsCount }} VIP/VVIP</span>
                    </div>
                </div>
                <div class="p-3 rounded-3 bg-info-subtle text-info">
                    <i class="fa-solid fa-users-viewfinder fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Live Villa Fleet Status Matrix -->
<div class="card card-luxury mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-cubes-stacked text-warning"></i>
            <strong>Live Villa Status Grid</strong>
        </span>
        <div class="d-flex gap-2 small">
            <span class="badge bg-success">Available</span>
            <span class="badge bg-primary">Occupied</span>
            <span class="badge bg-warning text-dark">Cleaning</span>
            <span class="badge bg-danger">Maintenance</span>
            <span class="badge bg-info text-dark">Reserved</span>
        </div>
    </div>
    <div class="card-body p-3">
        <div class="row g-3">
            @forelse($allVillas as $villa)
                <div class="col-xl-3 col-lg-4 col-sm-6">
                    <div class="p-3 border rounded-3 bg-light-subtle h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-dark fw-bold">{{ $villa->code }}</span>
                                <span class="badge {{ $villa->getStatusBadgeClass() }} text-capitalize">
                                    {{ $villa->status }}
                                </span>
                            </div>
                            <h6 class="fw-bold text-truncate mb-1" title="{{ $villa->name }}">
                                <a href="{{ route('villas.show', $villa) }}" class="text-decoration-none text-dark">
                                    {{ $villa->name }}
                                </a>
                            </h6>
                            <div class="text-muted small mb-2">
                                <i class="fa-solid fa-location-dot me-1 text-secondary"></i> {{ $villa->category }} &bull; {{ $villa->bedrooms }} BR
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top mt-2">
                            <span class="fw-bold text-dark small">${{ number_format($villa->price_per_night, 0) }}<span class="text-muted fw-normal">/nt</span></span>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li><h6 class="dropdown-header">Quick Change Status</h6></li>
                                    <li>
                                        <form action="{{ route('villas.status', $villa) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="available">
                                            <button type="submit" class="dropdown-item small text-success"><i class="fa-solid fa-circle-check me-2"></i>Set Available</button>
                                        </form>
                                    </li>
                                    <li>
                                        <form action="{{ route('villas.status', $villa) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="cleaning">
                                            <button type="submit" class="dropdown-item small text-warning"><i class="fa-solid fa-broom me-2"></i>Set Cleaning</button>
                                        </form>
                                    </li>
                                    <li>
                                        <form action="{{ route('villas.status', $villa) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="maintenance">
                                            <button type="submit" class="dropdown-item small text-danger"><i class="fa-solid fa-screwdriver-wrench me-2"></i>Set Maintenance</button>
                                        </form>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item small" href="{{ route('villas.show', $villa) }}">Full Details</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-4 text-muted">
                    No villas added yet. <a href="{{ route('villas.create') }}">Create one</a> or use <a href="{{ route('admin.bulk-import.index') }}">Bulk Import</a>.
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Charts Row (100% Offline with Local Chart.js) -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card card-luxury h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-chart-line text-warning"></i>
                    <strong>Settled Revenue History (Last 6 Months)</strong>
                </span>
                <span class="badge bg-light text-dark border">Offline Telemetry</span>
            </div>
            <div class="card-body">
                <div style="height: 280px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-luxury h-100">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="fa-solid fa-chart-pie text-warning"></i>
                <strong>Portfolio by Category</strong>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div style="height: 250px; width: 100%;">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Operations Section: Check-ins, Check-outs, Recent Bookings & Maintenance -->
<div class="row g-3">
    <!-- Today's Turnover Schedule -->
    <div class="col-lg-6">
        <div class="card card-luxury h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-bell-concierge text-primary"></i>
                    <strong>Today's Guest Schedule</strong>
                </span>
                <span class="text-muted small">{{ now()->format('l, F j, Y') }}</span>
            </div>
            <div class="card-body p-0">
                <ul class="nav nav-tabs nav-fill border-bottom px-3 pt-2" id="turnoverTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active small py-2 fw-semibold" id="checkin-tab" data-bs-toggle="tab" data-bs-target="#checkin-pane">
                            Arrivals / Check-ins ({{ $todayCheckIns->count() }})
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link small py-2 fw-semibold" id="checkout-tab" data-bs-toggle="tab" data-bs-target="#checkout-pane">
                            Departures / Check-outs ({{ $todayCheckOuts->count() }})
                        </button>
                    </li>
                </ul>

                <div class="tab-content p-3">
                    <!-- Check-in Pane -->
                    <div class="tab-pane fade show active" id="checkin-pane">
                        @forelse($todayCheckIns as $b)
                            <div class="d-flex align-items-center justify-content-between p-2 mb-2 border rounded bg-white">
                                <div>
                                    <span class="fw-bold text-dark">{{ $b->guest->full_name }}</span>
                                    <span class="badge {{ $b->guest->getVipBadgeClass() }} ms-1">{{ strtoupper($b->guest->vip_level) }}</span>
                                    <div class="text-muted small">
                                        <i class="fa-solid fa-house me-1"></i>{{ $b->villa->name }} ({{ $b->villa->code }})
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge {{ $b->getStatusBadgeClass() }} mb-1 d-block">{{ ucfirst($b->status) }}</span>
                                    @if($b->status !== 'checked_in')
                                        <form action="{{ route('bookings.check-in', $b) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success py-0 px-2" style="font-size: 0.75rem;">
                                                Check In
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small text-center my-3">No arrivals scheduled for today.</p>
                        @endforelse
                    </div>

                    <!-- Check-out Pane -->
                    <div class="tab-pane fade" id="checkout-pane">
                        @forelse($todayCheckOuts as $b)
                            <div class="d-flex align-items-center justify-content-between p-2 mb-2 border rounded bg-white">
                                <div>
                                    <span class="fw-bold text-dark">{{ $b->guest->full_name }}</span>
                                    <div class="text-muted small">
                                        <i class="fa-solid fa-house me-1"></i>{{ $b->villa->name }} ({{ $b->villa->code }})
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge {{ $b->getStatusBadgeClass() }} mb-1 d-block">{{ ucfirst($b->status) }}</span>
                                    @if($b->status === 'checked_in')
                                        <form action="{{ route('bookings.check-out', $b) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning py-0 px-2" style="font-size: 0.75rem;">
                                                Check Out
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-muted small text-center my-3">No departures scheduled for today.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Priority Maintenance Tasks -->
    <div class="col-lg-6">
        <div class="card card-luxury h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-broom-ball text-warning"></i>
                    <strong>Priority Maintenance & Housekeeping</strong>
                </span>
                <a href="{{ route('maintenance.create') }}" class="btn btn-sm btn-outline-dark py-0 px-2" style="font-size: 0.78rem;">
                    + Schedule
                </a>
            </div>
            <div class="card-body p-3">
                @forelse($urgentTasks as $task)
                    <div class="d-flex align-items-start justify-content-between p-2 mb-2 border rounded bg-light-subtle">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge {{ $task->getPriorityBadgeClass() }} text-uppercase" style="font-size: 0.68rem;">
                                    {{ $task->priority }}
                                </span>
                                <span class="fw-bold text-dark small">{{ $task->title }}</span>
                            </div>
                            <div class="text-muted small" style="font-size: 0.78rem;">
                                <i class="fa-solid fa-house me-1"></i>{{ $task->villa->code }} &bull; Assigned: {{ $task->assignedStaff?->name ?? 'Unassigned' }}
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge {{ $task->getStatusBadgeClass() }} mb-1 d-block">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                            @if($task->status !== 'completed')
                                <form action="{{ route('maintenance.status', $task) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="completed">
                                    <button type="submit" class="btn btn-sm btn-outline-success py-0 px-2" style="font-size: 0.72rem;">Done</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-muted small text-center my-3">All maintenance and cleaning tasks are clear.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Reservations -->
    <div class="col-12">
        <div class="card card-luxury">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-calendar-check text-warning"></i>
                    <strong>Recent Reservations</strong>
                </span>
                <a href="{{ route('bookings.index') }}" class="btn btn-sm btn-link text-decoration-none text-muted">
                    View All Bookings <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Booking Code</th>
                            <th>Villa</th>
                            <th>Guest</th>
                            <th>Dates</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBookings as $booking)
                            <tr>
                                <td>
                                    <a href="{{ route('bookings.show', $booking) }}" class="fw-bold text-decoration-none text-dark">
                                        {{ $booking->booking_code }}
                                    </a>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $booking->villa->name }}</span>
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
                                    <span class="text-muted">({{ $booking->total_nights }} nts)</span>
                                </td>
                                <td class="fw-bold">${{ number_format($booking->total_amount, 2) }}</td>
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
                                    <a href="{{ route('bookings.show', $booking) }}" class="btn btn-sm btn-light" title="View Details">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('bookings.invoice', $booking) }}" class="btn btn-sm btn-light" title="Invoice" target="_blank">
                                        <i class="fa-solid fa-receipt"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No reservations recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // 1. Revenue History Chart
        const ctxRev = document.getElementById('revenueChart');
        if (ctxRev) {
            new Chart(ctxRev, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($chartLabels) !!},
                    datasets: [{
                        label: 'Settled Revenue ($)',
                        data: {!! json_encode($chartRevenue) !!},
                        backgroundColor: 'rgba(194, 155, 56, 0.85)',
                        borderColor: '#996515',
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }

        // 2. Category Distribution Doughnut Chart
        const ctxCat = document.getElementById('categoryChart');
        if (ctxCat) {
            const catData = {!! json_encode($categoryBreakdown) !!};
            new Chart(ctxCat, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(catData),
                    datasets: [{
                        data: Object.values(catData),
                        backgroundColor: [
                            '#0f172a',
                            '#d4af37',
                            '#0284c7',
                            '#10b981',
                            '#8b5cf6',
                            '#f59e0b'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }
    });
</script>
@endpush
