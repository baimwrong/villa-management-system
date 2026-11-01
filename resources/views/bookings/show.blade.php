@extends('layouts.app')

@section('title', 'Reservation ' . $booking->booking_code)

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-dark fs-6">{{ $booking->booking_code }}</span>
            <span class="badge {{ $booking->getStatusBadgeClass() }} fs-6 text-capitalize">
                {{ str_replace('_', ' ', $booking->status) }}
            </span>
            <span class="badge {{ $booking->getPaymentBadgeClass() }} fs-6 text-capitalize">
                Payment: {{ $booking->payment_status }}
            </span>
        </div>
        <h3 class="fw-bold mb-0 text-dark">{{ $booking->villa->name }} ({{ $booking->villa->code }})</h3>
        <p class="text-muted small mb-0">Booked by {{ $booking->guest->full_name }} &bull; Reserved on {{ $booking->created_at->format('M d, Y') }}</p>
    </div>

    <div class="d-flex flex-wrap gap-2">
        @if($booking->status === 'confirmed')
            <form action="{{ route('bookings.check-in', $booking) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success btn-sm d-flex align-items-center gap-1">
                    <i class="fa-solid fa-key"></i> Check In Guest
                </button>
            </form>
        @elseif($booking->status === 'checked_in')
            <form action="{{ route('bookings.check-out', $booking) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-warning btn-sm d-flex align-items-center gap-1 text-dark">
                    <i class="fa-solid fa-door-open"></i> Complete Check-Out
                </button>
            </form>
        @endif

        <button type="button" class="btn btn-gold btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#paymentModal">
            <i class="fa-solid fa-credit-card"></i> Add Payment
        </button>

        <a href="{{ route('bookings.invoice', $booking) }}" class="btn btn-outline-dark btn-sm d-flex align-items-center gap-1" target="_blank">
            <i class="fa-solid fa-receipt"></i> Guest Folio
        </a>

        @if(!in_array($booking->status, ['cancelled', 'checked_out']))
            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cancelModal">
                Cancel
            </button>
        @endif

        <button class="btn btn-gold btn-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#chargeModal">
            <i class="fa-solid fa-plus-circle"></i>
            <span>Post Incidental Charge</span>
        </button>
        <button class="btn btn-outline-dark btn-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#paymentModal">
            <i class="fa-solid fa-credit-card"></i>
            <span>Add Payment</span>
        </button>

        <a href="{{ route('bookings.edit', $booking) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-pen-to-square"></i>
        </a>
        <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Stay Breakdown & Guest Profile -->
    <div class="col-lg-8">
        <!-- Stay Window & Specs -->
        <div class="card card-luxury mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong><i class="fa-solid fa-calendar-days text-warning me-2"></i>Stay Schedule & Itinerary</strong>
                <span class="badge bg-light text-dark border">{{ $booking->total_nights }} Total Nights</span>
            </div>
            <div class="card-body">
                <div class="row g-3 text-center mb-4">
                    <div class="col-sm-6 p-3 bg-light rounded-3">
                        <span class="text-muted small d-block text-uppercase fw-semibold">Check-In Arrival</span>
                        <span class="fs-4 fw-bold text-dark">{{ \Carbon\Carbon::parse($booking->check_in)->format('l, M j, Y') }}</span>
                        <div class="text-secondary small">Standard Check-In from 14:00</div>
                    </div>
                    <div class="col-sm-6 p-3 bg-light rounded-3">
                        <span class="text-muted small d-block text-uppercase fw-semibold">Check-Out Departure</span>
                        <span class="fs-4 fw-bold text-dark">{{ \Carbon\Carbon::parse($booking->check_out)->format('l, M j, Y') }}</span>
                        <div class="text-secondary small">Standard Check-Out by 11:00</div>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between p-3 border rounded-3 bg-light-subtle">
                    <div>
                        <span class="text-muted small d-block">PARTY CAPACITY</span>
                        <span class="fw-bold text-dark">
                            <i class="fa-solid fa-user-group me-1 text-primary"></i> {{ $booking->adults }} Adults, {{ $booking->children }} Children
                        </span>
                    </div>
                    <div>
                        <span class="text-muted small d-block">RESERVED PROPERTY</span>
                        <a href="{{ route('villas.show', $booking->villa) }}" class="fw-bold text-decoration-none text-dark">
                            {{ $booking->villa->name }} ({{ $booking->villa->category }})
                        </a>
                    </div>
                    <div>
                        <span class="text-muted small d-block">PROCESSED BY</span>
                        <span class="text-secondary small">{{ $booking->user?->name ?? 'Front Desk Automated' }}</span>
                    </div>
                </div>

                @if($booking->special_notes)
                    <div class="mt-4 p-3 border rounded bg-warning-subtle text-dark">
                        <strong class="d-block mb-1"><i class="fa-solid fa-concierge-bell me-1"></i>Guest Special Requests:</strong>
                        <p class="mb-0 small">{{ $booking->special_notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Incidental & Concierge Charges Table -->
        <div class="card card-luxury mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong><i class="fa-solid fa-bell-concierge text-warning me-2"></i>In-Stay Incidentals & Extra Charges</strong>
                <button class="btn btn-sm btn-outline-dark py-0 px-2" data-bs-toggle="modal" data-bs-target="#chargeModal">
                    + Post Charge
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Charge Description</th>
                            <th>Category</th>
                            <th>Date / Time</th>
                            <th>Amount</th>
                            <th>Logged By</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($booking->extraCharges as $charge)
                            <tr>
                                <td>
                                    <span class="fw-bold text-dark d-block small">{{ $charge->title }}</span>
                                    @if($charge->notes)
                                        <span class="text-muted small" style="font-size: 0.75rem;">{{ $charge->notes }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $charge->getCategoryBadgeClass() }} text-capitalize">
                                        {{ $charge->category }}
                                    </span>
                                </td>
                                <td class="small">{{ $charge->charged_at ? $charge->charged_at->format('M d, Y H:i') : $charge->created_at->format('M d, Y') }}</td>
                                <td class="fw-bold text-dark">${{ number_format($charge->amount, 2) }}</td>
                                <td class="text-muted small">{{ $charge->creator?->name ?? 'Staff' }}</td>
                                <td class="text-end">
                                    <form action="{{ route('bookings.charges.destroy', [$booking, $charge]) }}" method="POST" onsubmit="return confirm('Void this charge from folio?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Void charge">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-3 text-muted small">No incidental or concierge charges posted to this stay.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment History Table -->
        <div class="card card-luxury">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong><i class="fa-solid fa-receipt text-warning me-2"></i>Recorded Transaction Ledger</strong>
                <button class="btn btn-sm btn-gold py-0 px-2" data-bs-toggle="modal" data-bs-target="#paymentModal">
                    + Add Payment
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Transaction Ref</th>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($booking->payments as $payment)
                            <tr>
                                <td class="fw-bold text-dark small">{{ $payment->transaction_reference }}</td>
                                <td class="small">{{ $payment->payment_date ? $payment->payment_date->format('M d, Y H:i') : $payment->created_at->format('M d, Y') }}</td>
                                <td class="small text-capitalize">{{ str_replace('_', ' ', $payment->payment_method) }}</td>
                                <td class="fw-bold text-success">${{ number_format($payment->amount, 2) }}</td>
                                <td>
                                    <span class="badge {{ $payment->getStatusBadgeClass() }}">{{ ucfirst($payment->status) }}</span>
                                </td>
                                <td class="text-muted small">{{ $payment->notes ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-3 text-muted small">No payment transactions recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Financial Summary & Guest Contact -->
    <div class="col-lg-4">
        <!-- Financial Card -->
        <div class="card card-luxury mb-4">
            <div class="card-header bg-white">
                <strong><i class="fa-solid fa-vault text-warning me-2"></i>Tariff & Billing Folio</strong>
            </div>
            <div class="card-body p-3">
                <div class="d-flex justify-content-between small mb-2">
                    <span class="text-muted">{{ $booking->total_nights }} nights &times; ${{ number_format($booking->nightly_rate, 2) }}</span>
                    <span class="fw-bold text-dark">${{ number_format($booking->total_nights * $booking->nightly_rate, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between small mb-2">
                    <span class="text-muted">Turnover Cleaning Fee</span>
                    <span class="fw-bold text-dark">${{ number_format($booking->cleaning_fee, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between small mb-2">
                    <span class="text-muted">Resort Service Fee</span>
                    <span class="fw-bold text-dark">${{ number_format($booking->service_fee, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between small mb-2">
                    <span class="text-muted">Hospitality Tax</span>
                    <span class="fw-bold text-dark">${{ number_format($booking->tax_amount, 2) }}</span>
                </div>

                @if($booking->extraCharges->count() > 0)
                    <div class="d-flex justify-content-between small mb-2 text-warning-emphasis">
                        <span><i class="fa-solid fa-plus-circle me-1"></i>In-Stay Incidentals ({{ $booking->extraCharges->count() }})</span>
                        <span class="fw-bold">${{ number_format($booking->extraCharges->sum('amount'), 2) }}</span>
                    </div>
                @endif

                <hr>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold text-dark">Total Stay Invoice:</span>
                    <span class="fw-bold text-dark fs-5">${{ number_format($booking->total_amount, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2 text-success small">
                    <span>Total Paid to Date:</span>
                    <span class="fw-bold">${{ number_format($booking->paid_amount, 2) }}</span>
                </div>

                <div class="p-3 mt-3 rounded-3 {{ $booking->balance_due > 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }} d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Balance Outstanding:</span>
                    <span class="fs-5 fw-bold">${{ number_format($booking->balance_due, 2) }}</span>
                </div>

                @if($booking->balance_due > 0)
                    <button class="btn btn-gold btn-sm w-100 mt-3" data-bs-toggle="modal" data-bs-target="#paymentModal">
                        Settle Balance Now
                    </button>
                @endif
            </div>
        </div>

        <!-- Guest Profile Card -->
        <div class="card card-luxury">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong><i class="fa-solid fa-address-card text-warning me-2"></i>Guest Dossier</strong>
                <span class="badge {{ $booking->guest->getVipBadgeClass() }}">{{ strtoupper($booking->guest->vip_level) }}</span>
            </div>
            <div class="card-body p-3">
                <h5 class="fw-bold text-dark mb-1">{{ $booking->guest->full_name }}</h5>
                <div class="text-muted small mb-3">
                    <i class="fa-solid fa-envelope me-1"></i> {{ $booking->guest->email }}<br>
                    <i class="fa-solid fa-phone me-1"></i> {{ $booking->guest->phone ?: 'Not provided' }}<br>
                    <i class="fa-solid fa-globe me-1"></i> {{ $booking->guest->nationality ?: 'International' }}
                </div>

                <div class="d-flex justify-content-between small text-secondary p-2 bg-light rounded">
                    <span>Past Stays: <strong>{{ $booking->guest->total_stays }}</strong></span>
                    <span>Lifetime Spend: <strong>${{ number_format($booking->guest->total_spent, 2) }}</strong></span>
                </div>

                <a href="{{ route('guests.show', $booking->guest) }}" class="btn btn-outline-dark btn-sm w-100 mt-3">
                    View Full Guest Profile
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add Payment -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('bookings.payments.store', $booking) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Record Payment: {{ $booking->booking_code }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Outstanding Balance:</label>
                        <div class="fs-4 fw-bold text-danger">${{ number_format($booking->balance_due, 2) }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Payment Amount ($) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control" value="{{ max(0.01, $booking->balance_due) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select" required>
                            <option value="credit_card">Credit Card (Amex/Visa/MasterCard)</option>
                            <option value="bank_transfer">Wire / Bank Transfer</option>
                            <option value="cash">Cash Settlement</option>
                            <option value="stripe">Online Gateway / Stripe</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Transaction Memo / Reference</label>
                        <input type="text" name="notes" class="form-control" placeholder="e.g. Front desk card terminal receipt #4492">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gold">Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Post Incidental Charge -->
<div class="modal fade" id="chargeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('bookings.charges.store', $booking) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-bell-concierge text-warning me-2"></i>Post Folio Charge</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Charge Title / Service <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. In-Villa Dining & Vintage Wine" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-secondary">Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                <option value="dining">In-Villa Dining</option>
                                <option value="spa">Spa & Wellness</option>
                                <option value="transportation">Airport / Yacht Transfer</option>
                                <option value="experience">Excursion / Experience</option>
                                <option value="damage">Damage / Replacement</option>
                                <option value="other" selected>General Incidental</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-secondary">Amount ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount" min="0.01" max="50000" class="form-control" placeholder="0.00" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Notes / Order Reference</label>
                        <textarea name="notes" rows="2" class="form-control" placeholder="Ticket number, butler notes, guest signature ref..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gold">Post to Folio</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Cancel Booking -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('bookings.cancel', $booking) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-danger">Cancel Reservation: {{ $booking->booking_code }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">Cancelling this reservation will release the dates for <strong>{{ $booking->villa->name }}</strong> and flag the booking record.</p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Reason for Cancellation <span class="text-danger">*</span></label>
                        <textarea name="cancellation_reason" rows="3" class="form-control" required placeholder="Guest flight cancelled, personal emergency, etc..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Abort</button>
                    <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
