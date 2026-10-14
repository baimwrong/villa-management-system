@extends('layouts.app')

@section('title', 'Expenses & Operational Accounts')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Operational Expenses & Accounts</h3>
        <p class="text-muted small mb-0">Record utilities, luxury restocks, vendor outlays, and estate upkeep costs.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('expenses.create') }}" class="btn btn-gold btn-sm d-flex align-items-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>Record New Expense</span>
        </a>
    </div>
</div>

<!-- Total Spent Header Card -->
<div class="card card-luxury mb-4 p-3 bg-white">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <span class="text-muted small text-uppercase fw-semibold">Filtered Expenditure Total</span>
            <h3 class="fw-bold text-danger mb-0 mt-1">${{ number_format($totalExpenses, 2) }}</h3>
        </div>
        <div class="d-flex gap-2 text-muted small">
            <span>Tracking property allocations & general overhead.</span>
        </div>
    </div>
</div>

<!-- Filters Bar -->
<div class="card card-luxury mb-4 p-3">
    <form action="{{ route('expenses.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-3 col-sm-6">
            <select name="category" class="form-select form-select-sm">
                <option value="">All Categories</option>
                @foreach($categories as $key => $label)
                    <option value="{{ $key }}" {{ ($filters['category'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 col-sm-6">
            <select name="villa_id" class="form-select form-select-sm">
                <option value="">All Properties (Inc. Overhead)</option>
                @foreach($villas as $v)
                    <option value="{{ $v->id }}" {{ ($filters['villa_id'] ?? '') == $v->id ? 'selected' : '' }}>{{ $v->code }} - {{ $v->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 col-sm-4">
            <select name="month" class="form-select form-select-sm">
                <option value="">All Months</option>
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ ($filters['month'] ?? '') == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                @endfor
            </select>
        </div>
        <div class="col-md-2 col-sm-4">
            <select name="year" class="form-select form-select-sm">
                <option value="">All Years</option>
                <option value="2026" {{ ($filters['year'] ?? '') == '2026' ? 'selected' : '' }}>2026</option>
                <option value="2025" {{ ($filters['year'] ?? '') == '2025' ? 'selected' : '' }}>2025</option>
            </select>
        </div>
        <div class="col-md-2 col-sm-4 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-dark w-100" title="Filter">
                <i class="fa-solid fa-filter"></i>
            </button>
            <a href="{{ route('expenses.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        </div>
    </form>
</div>

<!-- Expenses Table -->
<div class="card card-luxury">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Date</th>
                    <th>Expense Title</th>
                    <th>Category</th>
                    <th>Allocated Asset</th>
                    <th>Vendor / Payee</th>
                    <th>Amount ($)</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                    <tr>
                        <td class="small text-muted">{{ \Carbon\Carbon::parse($expense->expense_date)->format('M d, Y') }}</td>
                        <td>
                            <span class="fw-bold text-dark d-block">{{ $expense->title }}</span>
                            @if($expense->invoice_number)
                                <span class="text-muted small" style="font-size: 0.72rem;">Inv #{{ $expense->invoice_number }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $expense->getCategoryBadgeClass() }} text-capitalize">
                                {{ str_replace('_', ' ', $expense->category) }}
                            </span>
                        </td>
                        <td class="small">
                            @if($expense->villa)
                                <a href="{{ route('villas.show', $expense->villa) }}" class="text-decoration-none fw-semibold text-dark">
                                    {{ $expense->villa->code }} &bull; {{ $expense->villa->name }}
                                </a>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border">General Resort Overhead</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $expense->vendor ?: 'Direct' }}</td>
                        <td class="fw-bold text-danger">${{ number_format($expense->amount, 2) }}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-light" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-receipt fs-2 text-muted mb-2 d-block"></i>
                            No expense records matching filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $expenses->links('pagination::bootstrap-5') }}
</div>
@endsection
