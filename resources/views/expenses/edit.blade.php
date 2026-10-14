@extends('layouts.app')

@section('title', 'Edit Expense')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold mb-1 text-dark">Edit Expense Record</h3>
                <p class="text-muted small mb-0">{{ $expense->title }}</p>
            </div>
            <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Expenses
            </a>
        </div>

        <div class="card card-luxury">
            <div class="card-body p-4">
                <form action="{{ route('expenses.update', $expense) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Expense Title / Item <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $expense->title) }}" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Accounting Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                @foreach(['maintenance' => 'Repairs & Maintenance', 'utilities' => 'Utilities & Telecom', 'supplies' => 'Linens, Amenities & Supplies', 'staff_payroll' => 'Staff & Contractor Payroll', 'food_beverage' => 'Food, Wine & Beverage', 'marketing' => 'Marketing', 'other' => 'Overhead'] as $key => $label)
                                    <option value="{{ $key }}" {{ old('category', $expense->category) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Specific Villa Allocation</label>
                            <select name="villa_id" class="form-select">
                                <option value="">-- General Resort Overhead --</option>
                                @foreach($villas as $villa)
                                    <option value="{{ $villa->id }}" {{ old('villa_id', $expense->villa_id) == $villa->id ? 'selected' : '' }}>
                                        [{{ $villa->code }}] {{ $villa->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Amount ($) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount', $expense->amount) }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="expense_date" class="form-control" value="{{ old('expense_date', $expense->expense_date->toDateString()) }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Disbursement Method</label>
                            <input type="text" name="payment_method" class="form-control" value="{{ old('payment_method', $expense->payment_method) }}">
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Vendor / Supplier</label>
                            <input type="text" name="vendor" class="form-control" value="{{ old('vendor', $expense->vendor) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Invoice Reference</label>
                            <input type="text" name="invoice_number" class="form-control" value="{{ old('invoice_number', $expense->invoice_number) }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">Accounting Notes</label>
                            <textarea name="notes" rows="2" class="form-control">{{ old('notes', $expense->notes) }}</textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="if(confirm('Delete this expense record?')) document.getElementById('delete-expense-form').submit();">
                            Delete Record
                        </button>

                        <div class="d-flex gap-2">
                            <a href="{{ route('expenses.index') }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-gold px-4">
                                Update Expense
                            </button>
                        </div>
                    </div>
                </form>

                <form id="delete-expense-form" action="{{ route('expenses.destroy', $expense) }}" method="POST" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
