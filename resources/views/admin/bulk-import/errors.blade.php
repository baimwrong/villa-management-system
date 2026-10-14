@extends('layouts.app')

@section('title', 'Batch Import Diagnostics')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold mb-1 text-dark">Batch Import Failure Diagnostics</h3>
                <p class="text-muted small mb-0">File: <strong>{{ $log->file_name }}</strong> &bull; Entity: <span class="badge bg-secondary">{{ strtoupper($log->import_type) }}</span> &bull; {{ $log->created_at->format('M d, Y H:i:s') }}</p>
            </div>
            <a href="{{ route('admin.bulk-import.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Import Hub
            </a>
        </div>

        <div class="card card-luxury">
            <div class="card-header bg-danger-subtle text-danger fw-bold d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-bug me-2"></i>Row-Level Error Telemetry ({{ count($log->errors ?? []) }} Issues Recorded)</span>
                <span class="badge bg-danger">{{ $log->failed_rows }} Skipped Rows</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th style="width: 70px;">#</th>
                                <th>Error Diagnostics</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($log->errors ?? [] as $index => $err)
                                <tr>
                                    <td class="text-muted small fw-bold">{{ $index + 1 }}</td>
                                    <td class="text-danger font-monospace small">
                                        <i class="fa-solid fa-circle-exclamation me-2"></i>{{ $err }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center py-4 text-muted small">No specific errors recorded for this log.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
