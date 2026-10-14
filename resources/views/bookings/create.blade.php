@extends('layouts.app')

@section('title', 'Create Reservation')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold mb-1 text-dark">Create Guest Reservation</h3>
                <p class="text-muted small mb-0">Book a villa stay, calculate charges, and record advance payments.</p>
            </div>
            <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Reservations
            </a>
        </div>

        <div class="card card-luxury">
            <div class="card-body p-4">
                <form action="{{ route('bookings.store') }}" method="POST" id="bookingForm">
                    @csrf

                    <!-- 1. Villa & Guest Selection -->
                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">1. Select Property & Guest</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Assigned Villa <span class="text-danger">*</span></label>
                            <select name="villa_id" id="villa_id" class="form-select @error('villa_id') is-invalid @enderror" required onchange="updateRates()">
                                <option value="">-- Choose Luxury Villa --</option>
                                @foreach($villas as $villa)
                                    <option value="{{ $villa->id }}"
                                        data-price="{{ $villa->price_per_night }}"
                                        data-cleaning="{{ $villa->cleaning_fee }}"
                                        {{ old('villa_id', $selectedVillaId) == $villa->id ? 'selected' : '' }}>
                                        [{{ $villa->code }}] {{ $villa->name }} — ${{ number_format($villa->price_per_night, 0) }}/nt ({{ $villa->category }})
                                    </option>
                                @endforeach
                            </select>
                            @error('villa_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Guest Profile <span class="text-danger">*</span></label>
                            <div class="d-flex gap-2">
                                <select name="guest_id" id="guest_id" class="form-select @error('guest_id') is-invalid @enderror" required>
                                    <option value="">-- Choose Existing Guest --</option>
                                    @foreach($guests as $guest)
                                        <option value="{{ $guest->id }}" {{ old('guest_id') == $guest->id ? 'selected' : '' }}>
                                            {{ $guest->full_name }} ({{ $guest->email }}) [{{ strtoupper($guest->vip_level) }}]
                                        </option>
                                    @endforeach
                                </select>
                                <a href="{{ route('guests.create') }}" class="btn btn-outline-dark text-nowrap" target="_blank" title="Create New Guest Profile">
                                    <i class="fa-solid fa-user-plus"></i>
                                </a>
                            </div>
                            @error('guest_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- 2. Stay Dates & Party -->
                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">2. Stay Dates & Occupancy</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Check-In Date <span class="text-danger">*</span></label>
                            <input type="date" name="check_in" id="check_in" class="form-control @error('check_in') is-invalid @enderror" value="{{ old('check_in', now()->toDateString()) }}" required onchange="calculateCharges()">
                            @error('check_in') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Check-Out Date <span class="text-danger">*</span></label>
                            <input type="date" name="check_out" id="check_out" class="form-control @error('check_out') is-invalid @enderror" value="{{ old('check_out', now()->addDays(3)->toDateString()) }}" required onchange="calculateCharges()">
                            @error('check_out') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Adult Guests <span class="text-danger">*</span></label>
                            <input type="number" name="adults" min="1" max="30" class="form-control" value="{{ old('adults', 2) }}" required>
                        </div>

                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Children (Under 12)</label>
                            <input type="number" name="children" min="0" max="20" class="form-control" value="{{ old('children', 0) }}">
                        </div>
                    </div>

                    <!-- 3. Financial Breakdown -->
                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">3. Tariffs & Charges Breakdown</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Nightly Base Rate ($)</label>
                            <input type="number" step="0.01" name="nightly_rate" id="nightly_rate" class="form-control" value="{{ old('nightly_rate', 1200.00) }}" required oninput="calculateCharges()">
                        </div>

                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Cleaning Turn Fee ($)</label>
                            <input type="number" step="0.01" name="cleaning_fee" id="cleaning_fee" class="form-control" value="{{ old('cleaning_fee', 120.00) }}" oninput="calculateCharges()">
                        </div>

                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Resort Concierge Fee ($)</label>
                            <input type="number" step="0.01" name="service_fee" id="service_fee" class="form-control" value="{{ old('service_fee', 150.00) }}" oninput="calculateCharges()">
                        </div>

                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Hospitality Taxes ($)</label>
                            <input type="number" step="0.01" name="tax_amount" id="tax_amount" class="form-control" value="{{ old('tax_amount', 200.00) }}" oninput="calculateCharges()">
                        </div>

                        <!-- Computed Summary Banner -->
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 d-flex flex-wrap justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted small d-block">CALCULATED TOTAL STAY</span>
                                    <span class="fs-4 fw-bold text-dark" id="displayTotalStay">3 Nights &bull; $4,070.00</span>
                                </div>
                                <div>
                                    <input type="hidden" name="total_amount" id="total_amount" value="{{ old('total_amount', 4070.00) }}">
                                    <span class="badge bg-warning text-dark px-3 py-2 fs-6">
                                        Total Payable: $<span id="displayPayable">4,070.00</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Initial Payment & Booking Status -->
                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">4. Advance Payment & Booking Status</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Initial Payment Received ($)</label>
                            <input type="number" step="0.01" name="paid_amount" id="paid_amount" class="form-control" value="{{ old('paid_amount', 0.00) }}" placeholder="0.00">
                            <div class="form-text small">Enter deposit paid today (if any).</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Payment Method</label>
                            <select name="payment_method" class="form-select">
                                <option value="credit_card">Credit Card (Amex/Visa/MC)</option>
                                <option value="bank_transfer">Wire / Bank Transfer</option>
                                <option value="cash">Cash on Arrival</option>
                                <option value="stripe">Online Gateway / Stripe</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Initial Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="confirmed" selected>Confirmed (Upcoming)</option>
                                <option value="checked_in">Checked In (Guest On-Site)</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Special Guest Requests / Concierge Notes</label>
                            <textarea name="special_notes" rows="3" class="form-control" placeholder="Airport transfer details, champagne requests, dietary requirements...">{{ old('special_notes') }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('bookings.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-gold px-4">
                            <i class="fa-solid fa-calendar-check me-2"></i> Confirm Reservation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function updateRates() {
        const villaSelect = document.getElementById('villa_id');
        const selected = villaSelect.options[villaSelect.selectedIndex];
        if (selected && selected.dataset.price) {
            document.getElementById('nightly_rate').value = parseFloat(selected.dataset.price).toFixed(2);
            document.getElementById('cleaning_fee').value = parseFloat(selected.dataset.cleaning).toFixed(2);
            calculateCharges();
        }
    }

    function calculateCharges() {
        const inDate = new Date(document.getElementById('check_in').value);
        const outDate = new Date(document.getElementById('check_out').value);

        let nights = 1;
        if (outDate > inDate) {
            const diffTime = Math.abs(outDate - inDate);
            nights = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        }

        const rate = parseFloat(document.getElementById('nightly_rate').value) || 0;
        const cleaning = parseFloat(document.getElementById('cleaning_fee').value) || 0;
        const service = parseFloat(document.getElementById('service_fee').value) || 0;
        const tax = parseFloat(document.getElementById('tax_amount').value) || 0;

        const total = (nights * rate) + cleaning + service + tax;

        document.getElementById('total_amount').value = total.toFixed(2);
        document.getElementById('displayTotalStay').innerText = `${nights} Nights • $${total.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
        document.getElementById('displayPayable').innerText = total.toLocaleString('en-US', {minimumFractionDigits: 2});
    }

    document.addEventListener('DOMContentLoaded', function() {
        calculateCharges();
    });
</script>
@endpush
