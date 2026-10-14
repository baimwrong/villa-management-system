@extends('layouts.app')

@section('title', 'Admin Bulk Data Import Hub')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h3 class="fw-bold mb-0 text-dark">Executive Bulk Data Import Hub</h3>
            <span class="badge bg-warning text-dark"><i class="fa-solid fa-lock me-1"></i>ADMIN ONLY</span>
        </div>
        <p class="text-muted small mb-0">High-throughput CSV ingestion engine for fleet properties, guest directories, reservations, and financial ledgers.</p>
    </div>
</div>

<!-- Import Statistics Header -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card card-luxury p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Total Batch Runs</span>
                    <h3 class="fw-bold text-dark mb-0 mt-1">{{ $totalImports }}</h3>
                </div>
                <div class="p-3 bg-primary-subtle text-primary rounded-3">
                    <i class="fa-solid fa-file-import fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-luxury p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Clean Executions</span>
                    <h3 class="fw-bold text-success mb-0 mt-1">{{ $successfulImports }}</h3>
                </div>
                <div class="p-3 bg-success-subtle text-success rounded-3">
                    <i class="fa-solid fa-circle-check fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-luxury p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Ingested Records</span>
                    <h3 class="fw-bold text-dark mb-0 mt-1">{{ number_format($totalRecordsImported) }}</h3>
                </div>
                <div class="p-3 bg-warning-subtle text-warning rounded-3">
                    <i class="fa-solid fa-database fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Error Banner if last import had issues -->
@if(session('import_errors'))
    <div class="card border-warning mb-4 shadow-sm">
        <div class="card-header bg-warning-subtle text-dark fw-bold d-flex align-items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation text-warning fs-5"></i>
            <span>Import Completed with Warnings: Line Item Failures Detected</span>
        </div>
        <div class="card-body p-3">
            <p class="small text-muted mb-2">The following rows failed validation and were skipped while valid rows were imported:</p>
            <ul class="list-group list-group-flush small" style="max-height: 200px; overflow-y: auto;">
                @foreach(session('import_errors') as $err)
                    <li class="list-group-item text-danger py-1 px-2"><i class="fa-solid fa-circle-xmark me-1"></i>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<!-- Tabbed Import Workstations -->
<div class="card card-luxury mb-4">
    <div class="card-header bg-white border-bottom p-0">
        <ul class="nav nav-tabs nav-fill card-header-tabs m-0 border-0" id="importTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active py-3 fw-bold d-flex align-items-center justify-content-center gap-2" data-bs-toggle="tab" data-bs-target="#villas-tab">
                    <i class="fa-solid fa-house-chimney-window text-warning"></i>
                    <span>1. Luxury Villas</span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link py-3 fw-bold d-flex align-items-center justify-content-center gap-2" data-bs-toggle="tab" data-bs-target="#guests-tab">
                    <i class="fa-solid fa-users text-primary"></i>
                    <span>2. VIP Guests</span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link py-3 fw-bold d-flex align-items-center justify-content-center gap-2" data-bs-toggle="tab" data-bs-target="#bookings-tab">
                    <i class="fa-solid fa-calendar-check text-success"></i>
                    <span>3. Reservations</span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link py-3 fw-bold d-flex align-items-center justify-content-center gap-2" data-bs-toggle="tab" data-bs-target="#expenses-tab">
                    <i class="fa-solid fa-file-invoice-dollar text-danger"></i>
                    <span>4. Accounts & Outlays</span>
                </button>
            </li>
        </ul>
    </div>

    <div class="card-body p-4">
        <div class="tab-content" id="importTabContent">
            <!-- 1. Villas Import Pane -->
            <div class="tab-pane fade show active" id="villas-tab">
                <div class="row align-items-center mb-4">
                    <div class="col-md-8">
                        <h5 class="fw-bold text-dark mb-1">Bulk Ingest Luxury Villas</h5>
                        <p class="text-muted small mb-0">
                            Upload a CSV containing property codes, names, categories, capacities, night rates, and amenities.
                            Existing villa codes will be updated, new codes will be created.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <a href="{{ route('admin.bulk-import.template', 'villas') }}" class="btn btn-outline-dark btn-sm d-inline-flex align-items-center gap-2">
                            <i class="fa-solid fa-download text-warning"></i>
                            <span>Download Villas Sample CSV</span>
                        </a>
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 mb-4">
                    <span class="small fw-bold text-dark d-block mb-1">Supported CSV Columns:</span>
                    <code class="small text-secondary">code, name, category, bedrooms, bathrooms, max_guests, area_sqm, price_per_night, weekend_price_per_night, cleaning_fee, security_deposit, status, location, address, description, amenities, is_featured</code>
                </div>

                <form action="{{ route('admin.bulk-import.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type" value="villas">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Select Villas CSV File (.csv)</label>
                        <input type="file" name="file" class="form-control" accept=".csv,text/csv" required>
                    </div>

                    <button type="submit" class="btn btn-gold">
                        <i class="fa-solid fa-cloud-arrow-up me-2"></i> Ingest Villas Data
                    </button>
                </form>
            </div>

            <!-- 2. Guests Import Pane -->
            <div class="tab-pane fade" id="guests-tab">
                <div class="row align-items-center mb-4">
                    <div class="col-md-8">
                        <h5 class="fw-bold text-dark mb-1">Bulk Ingest VIP Guests Directory</h5>
                        <p class="text-muted small mb-0">
                            Upload guest contact records, passport numbers, nationalities, and VIP classifications.
                            Matched by email address automatically.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <a href="{{ route('admin.bulk-import.template', 'guests') }}" class="btn btn-outline-dark btn-sm d-inline-flex align-items-center gap-2">
                            <i class="fa-solid fa-download text-primary"></i>
                            <span>Download Guests Sample CSV</span>
                        </a>
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 mb-4">
                    <span class="small fw-bold text-dark d-block mb-1">Supported CSV Columns:</span>
                    <code class="small text-secondary">first_name, last_name, email, phone, nationality, id_or_passport, vip_level, special_requests</code>
                </div>

                <form action="{{ route('admin.bulk-import.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type" value="guests">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Select Guests CSV File (.csv)</label>
                        <input type="file" name="file" class="form-control" accept=".csv,text/csv" required>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-cloud-arrow-up me-2"></i> Ingest Guests Directory
                    </button>
                </form>
            </div>

            <!-- 3. Bookings Import Pane -->
            <div class="tab-pane fade" id="bookings-tab">
                <div class="row align-items-center mb-4">
                    <div class="col-md-8">
                        <h5 class="fw-bold text-dark mb-1">Bulk Ingest Historic & Upcoming Reservations</h5>
                        <p class="text-muted small mb-0">
                            Upload reservation records. Automatically maps to villas via <code>villa_code</code> and guests via <code>guest_email</code>.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <a href="{{ route('admin.bulk-import.template', 'bookings') }}" class="btn btn-outline-dark btn-sm d-inline-flex align-items-center gap-2">
                            <i class="fa-solid fa-download text-success"></i>
                            <span>Download Bookings Sample CSV</span>
                        </a>
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 mb-4">
                    <span class="small fw-bold text-dark d-block mb-1">Supported CSV Columns:</span>
                    <code class="small text-secondary">booking_code, villa_code, guest_email, guest_name, check_in, check_out, adults, children, nightly_rate, cleaning_fee, service_fee, tax_amount, total_amount, paid_amount, status, payment_method, special_notes</code>
                </div>

                <form action="{{ route('admin.bulk-import.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type" value="bookings">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Select Bookings CSV File (.csv)</label>
                        <input type="file" name="file" class="form-control" accept=".csv,text/csv" required>
                    </div>

                    <button type="submit" class="btn btn-success">
                        <i class="fa-solid fa-cloud-arrow-up me-2"></i> Ingest Reservations
                    </button>
                </form>
            </div>

            <!-- 4. Expenses Import Pane -->
            <div class="tab-pane fade" id="expenses-tab">
                <div class="row align-items-center mb-4">
                    <div class="col-md-8">
                        <h5 class="fw-bold text-dark mb-1">Bulk Ingest Financial Expenditures</h5>
                        <p class="text-muted small mb-0">
                            Import historical utilities, vendor invoices, repair receipts, and general estate overhead costs.
                        </p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <a href="{{ route('admin.bulk-import.template', 'expenses') }}" class="btn btn-outline-dark btn-sm d-inline-flex align-items-center gap-2">
                            <i class="fa-solid fa-download text-danger"></i>
                            <span>Download Expenses Sample CSV</span>
                        </a>
                    </div>
                </div>

                <div class="p-3 bg-light rounded-3 mb-4">
                    <span class="small fw-bold text-dark d-block mb-1">Supported CSV Columns:</span>
                    <code class="small text-secondary">title, category, villa_code, amount, expense_date, vendor, payment_method, invoice_number, notes</code>
                </div>

                <form action="{{ route('admin.bulk-import.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type" value="expenses">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Select Expenses CSV File (.csv)</label>
                        <input type="file" name="file" class="form-control" accept=".csv,text/csv" required>
                    </div>

                    <button type="submit" class="btn btn-danger">
                        <i class="fa-solid fa-cloud-arrow-up me-2"></i> Ingest Expenses Ledger
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Audit Trail of Previous Imports -->
<div class="card card-luxury">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-history text-secondary"></i>
            <strong>Batch Import Audit Trail & Telemetry</strong>
        </span>
        <span class="text-muted small">PostgreSQL 18 Import Logs</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Timestamp</th>
                    <th>Administrator</th>
                    <th>Entity Type</th>
                    <th>Filename</th>
                    <th>Processed</th>
                    <th>Ingested</th>
                    <th>Failed</th>
                    <th>Status</th>
                    <th class="text-end">Diagnostics</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="small">{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                        <td class="small fw-semibold">{{ $log->user?->name ?? 'System Admin' }}</td>
                        <td>
                            <span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.72rem;">
                                {{ $log->import_type }}
                            </span>
                        </td>
                        <td class="small text-truncate" style="max-width: 180px;">{{ $log->file_name }}</td>
                        <td class="small fw-bold">{{ $log->total_rows }}</td>
                        <td class="small text-success fw-bold">{{ $log->imported_rows }}</td>
                        <td class="small {{ $log->failed_rows > 0 ? 'text-danger fw-bold' : 'text-muted' }}">{{ $log->failed_rows }}</td>
                        <td>
                            <span class="badge {{ $log->getStatusBadgeClass() }} text-capitalize">
                                {{ $log->status }}
                            </span>
                        </td>
                        <td class="text-end">
                            @if(!empty($log->errors))
                                <a href="{{ route('admin.bulk-import.errors', $log) }}" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size: 0.75rem;">
                                    View Errors ({{ count($log->errors) }})
                                </a>
                            @else
                                <span class="text-success small"><i class="fa-solid fa-check me-1"></i>Clean</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted small">No bulk import jobs executed yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $logs->links('pagination::bootstrap-5') }}
</div>
@endsection
