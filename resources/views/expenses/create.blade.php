@extends('layouts.app')

@section('title', 'Record Expense')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold mb-1 text-dark">Record Resort Expenditure</h3>
                <p class="text-muted small mb-0">Post operational maintenance bills, utility invoices, and luxury restocks.</p>
            </div>
            <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Expenses
            </a>
        </div>

        <div class="card card-luxury">
            <div class="card-body p-4">
                <form action="{{ route('expenses.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Expense Title / Item <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required placeholder="e.g. Monthly Oceanfront Saltwater Pool Filter Replacements">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Accounting Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                                <option value="maintenance">Repairs & Maintenance</option>
                                <option value="utilities">Utilities & Telecom</option>
                                <option value="supplies">Linens, Amenities & Supplies</option>
                                <option value="staff_payroll">Staff & Contractor Payroll</option>
                                <option value="food_beverage">Food, Wine & Beverage Restock</option>
                                <option value="marketing">Marketing & Photography</option>
                                <option value="other">General Resort Overhead</option>
                            </select>
                            @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Specific Villa Allocation (Optional)</label>
                            <select name="villa_id" class="form-select">
                                <option value="">-- General Resort Overhead --</option>
                                @foreach($villas as $villa)
                                    <option value="{{ $villa->id }}" {{ old('villa_id') == $villa->id ? 'selected' : '' }}>
                                        [{{ $villa->code }}] {{ $villa->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text small">Leave blank if general resort expenditure.</div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Amount ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}" required placeholder="0.00">
                            @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="expense_date" class="form-control @error('expense_date') is-invalid @enderror" value="{{ old('expense_date', now()->toDateString()) }}" required>
                            @error('expense_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Disbursement Method</label>
                            <select name="payment_method" class="form-select">
                                <option value="Bank Transfer">Bank Transfer / Wire</option>
                                <option value="Corporate Card">Corporate Card</option>
                                <option value="Direct Debit">Direct Debit</option>
                                <option value="Cash / Petty Cash">Cash / Petty Cash</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Vendor / Supplier Name</label>
                            <input type="text" name="vendor" class="form-control" value="{{ old('vendor') }}" placeholder="e.g. PureBlue Aquatic Services">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Invoice / Voucher Reference</label>
                            <input type="text" name="invoice_number" class="form-control" value="{{ old('invoice_number') }}" placeholder="e.g. INV-99021">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Accounting Notes</label>
                            <textarea name="notes" rows="2" class="form-control" placeholder="Additional audit details or expense justification...">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('expenses.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-gold px-4">
                            <i class="fa-solid fa-file-invoice-dollar me-2"></i> Save Expense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
