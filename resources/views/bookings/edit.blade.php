@extends('layouts.app')

@section('title', 'Edit Reservation: ' . $booking->booking_code)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold mb-1 text-dark">Modify Reservation: {{ $booking->booking_code }}</h3>
                <p class="text-muted small mb-0">Update dates, tariffs, status, or guest notes.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('bookings.show', $booking) }}" class="btn btn-outline-dark btn-sm">
                    <i class="fa-solid fa-eye me-1"></i> View Booking
                </a>
                <a href="{{ route('bookings.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>

        <div class="card card-luxury">
            <div class="card-body p-4">
                <form action="{{ route('bookings.update', $booking) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">1. Property & Guest</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Assigned Villa <span class="text-danger">*</span></label>
                            <select name="villa_id" class="form-select" required>
                                @foreach($villas as $villa)
                                    <option value="{{ $villa->id }}" {{ old('villa_id', $booking->villa_id) == $villa->id ? 'selected' : '' }}>
                                        [{{ $villa->code }}] {{ $villa->name }} — ${{ number_format($villa->price_per_night, 0) }}/nt
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Guest Profile <span class="text-danger">*</span></label>
                            <select name="guest_id" class="form-select" required>
                                @foreach($guests as $guest)
                                    <option value="{{ $guest->id }}" {{ old('guest_id', $booking->guest_id) == $guest->id ? 'selected' : '' }}>
                                        {{ $guest->full_name }} ({{ $guest->email }}) [{{ strtoupper($guest->vip_level) }}]
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">2. Stay Dates & Party</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Check-In Date <span class="text-danger">*</span></label>
                            <input type="date" name="check_in" class="form-control" value="{{ old('check_in', $booking->check_in->toDateString()) }}" required>
                        </div>

                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Check-Out Date <span class="text-danger">*</span></label>
                            <input type="date" name="check_out" class="form-control" value="{{ old('check_out', $booking->check_out->toDateString()) }}" required>
                        </div>

                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Adult Guests</label>
                            <input type="number" name="adults" min="1" class="form-control" value="{{ old('adults', $booking->adults) }}" required>
                        </div>

                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Children</label>
                            <input type="number" name="children" min="0" class="form-control" value="{{ old('children', $booking->children) }}">
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">3. Tariffs & Status</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Nightly Rate ($)</label>
                            <input type="number" step="0.01" name="nightly_rate" class="form-control" value="{{ old('nightly_rate', $booking->nightly_rate) }}" required>
                        </div>

                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Cleaning Turn Fee ($)</label>
                            <input type="number" step="0.01" name="cleaning_fee" class="form-control" value="{{ old('cleaning_fee', $booking->cleaning_fee) }}">
                        </div>

                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Service Fee ($)</label>
                            <input type="number" step="0.01" name="service_fee" class="form-control" value="{{ old('service_fee', $booking->service_fee) }}">
                        </div>

                        <div class="col-md-3 col-6">
                            <label class="form-label small fw-semibold text-secondary">Tax Amount ($)</label>
                            <input type="number" step="0.01" name="tax_amount" class="form-control" value="{{ old('tax_amount', $booking->tax_amount) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Total Amount ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="total_amount" class="form-control" value="{{ old('total_amount', $booking->total_amount) }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Booking Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="confirmed" {{ old('status', $booking->status) == 'confirmed' ? 'selected' : '' }}>Confirmed (Upcoming)</option>
                                <option value="checked_in" {{ old('status', $booking->status) == 'checked_in' ? 'selected' : '' }}>Checked In (On-site)</option>
                                <option value="checked_out" {{ old('status', $booking->status) == 'checked_out' ? 'selected' : '' }}>Checked Out (Completed)</option>
                                <option value="cancelled" {{ old('status', $booking->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                <option value="no_show" {{ old('status', $booking->status) == 'no_show' ? 'selected' : '' }}>No Show</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Special Notes</label>
                            <textarea name="special_notes" rows="3" class="form-control">{{ old('special_notes', $booking->special_notes) }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('bookings.show', $booking) }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-gold px-4">
                            <i class="fa-solid fa-save me-2"></i> Update Reservation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
