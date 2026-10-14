@extends('layouts.app')

@section('title', 'Housekeeping & Maintenance')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold mb-1 text-dark">Housekeeping & Maintenance Log</h3>
        <p class="text-muted small mb-0">Track turnover cleaning, pool services, mechanical repairs, and routine inspections.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('maintenance.create') }}" class="btn btn-gold btn-sm d-flex align-items-center gap-2">
            <i class="fa-solid fa-plus"></i>
            <span>Schedule New Task</span>
        </a>
    </div>
</div>

<!-- Task Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card card-luxury p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Pending Tasks</span>
                    <h3 class="fw-bold text-dark mb-0 mt-1">{{ $pendingCount }}</h3>
                </div>
                <div class="p-3 bg-warning-subtle text-warning rounded-3">
                    <i class="fa-solid fa-clock-rotate-left fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-luxury p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">In Progress</span>
                    <h3 class="fw-bold text-dark mb-0 mt-1">{{ $inProgressCount }}</h3>
                </div>
                <div class="p-3 bg-primary-subtle text-primary rounded-3">
                    <i class="fa-solid fa-spinner fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-luxury p-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Completed</span>
                    <h3 class="fw-bold text-dark mb-0 mt-1">{{ $completedCount }}</h3>
                </div>
                <div class="p-3 bg-success-subtle text-success rounded-3">
                    <i class="fa-solid fa-circle-check fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Bar -->
<div class="card card-luxury mb-4 p-3">
    <form action="{{ route('maintenance.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-3 col-sm-6">
            <select name="villa_id" class="form-select form-select-sm">
                <option value="">All Villas</option>
                @foreach($villas as $v)
                    <option value="{{ $v->id }}" {{ ($filters['villa_id'] ?? '') == $v->id ? 'selected' : '' }}>{{ $v->code }} - {{ $v->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 col-sm-6">
            <select name="task_type" class="form-select form-select-sm">
                <option value="">All Task Types</option>
                <option value="cleaning" {{ ($filters['task_type'] ?? '') === 'cleaning' ? 'selected' : '' }}>Turnover Cleaning</option>
                <option value="pool_service" {{ ($filters['task_type'] ?? '') === 'pool_service' ? 'selected' : '' }}>Pool & Spa Balancing</option>
                <option value="plumbing" {{ ($filters['task_type'] ?? '') === 'plumbing' ? 'selected' : '' }}>Plumbing</option>
                <option value="electrical" {{ ($filters['task_type'] ?? '') === 'electrical' ? 'selected' : '' }}>Electrical / Smart Home</option>
                <option value="hvac" {{ ($filters['task_type'] ?? '') === 'hvac' ? 'selected' : '' }}>HVAC / Air Conditioning</option>
                <option value="landscaping" {{ ($filters['task_type'] ?? '') === 'landscaping' ? 'selected' : '' }}>Grounds & Landscaping</option>
                <option value="inspection" {{ ($filters['task_type'] ?? '') === 'inspection' ? 'selected' : '' }}>Safety & Inspection</option>
            </select>
        </div>
        <div class="col-md-2 col-sm-4">
            <select name="priority" class="form-select form-select-sm">
                <option value="">All Priorities</option>
                <option value="urgent" {{ ($filters['priority'] ?? '') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                <option value="high" {{ ($filters['priority'] ?? '') === 'high' ? 'selected' : '' }}>High</option>
                <option value="medium" {{ ($filters['priority'] ?? '') === 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="low" {{ ($filters['priority'] ?? '') === 'low' ? 'selected' : '' }}>Low</option>
            </select>
        </div>
        <div class="col-md-2 col-sm-4">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="in_progress" {{ ($filters['status'] ?? '') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ ($filters['status'] ?? '') === 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
        </div>
        <div class="col-md-2 col-sm-4 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-dark w-100" title="Filter">
                <i class="fa-solid fa-filter"></i>
            </button>
            <a href="{{ route('maintenance.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset">
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        </div>
    </form>
</div>

<!-- Tasks Table -->
<div class="card card-luxury">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light small">
                <tr>
                    <th>Villa</th>
                    <th>Task Description</th>
                    <th>Type</th>
                    <th>Priority</th>
                    <th>Assigned Staff</th>
                    <th>Scheduled Date</th>
                    <th>Status</th>
                    <th class="text-end">Quick Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $task)
                    <tr>
                        <td>
                            <a href="{{ route('villas.show', $task->villa) }}" class="fw-bold text-decoration-none text-dark">
                                {{ $task->villa->code }}
                            </a>
                            <div class="text-muted small text-truncate" style="max-width: 130px;">{{ $task->villa->name }}</div>
                        </td>
                        <td>
                            <span class="fw-bold text-dark d-block">{{ $task->title }}</span>
                            @if($task->description)
                                <span class="text-muted small text-truncate d-block" style="max-width: 280px;">{{ $task->description }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border text-capitalize">
                                {{ str_replace('_', ' ', $task->task_type) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $task->getPriorityBadgeClass() }} text-uppercase" style="font-size: 0.68rem;">
                                {{ $task->priority }}
                            </span>
                        </td>
                        <td class="small">
                            {{ $task->assignedStaff?->name ?? 'Unassigned' }}
                        </td>
                        <td class="small">
                            {{ $task->scheduled_at ? $task->scheduled_at->format('M d, Y H:i') : 'Immediate' }}
                        </td>
                        <td>
                            <span class="badge {{ $task->getStatusBadgeClass() }}">
                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                @if($task->status !== 'completed')
                                    <form action="{{ route('maintenance.status', $task) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="btn btn-outline-success" title="Mark Done">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('maintenance.edit', $task) }}" class="btn btn-light" title="Edit">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-clipboard-check fs-2 text-muted mb-2 d-block"></i>
                            No maintenance or cleaning tasks found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $tasks->links('pagination::bootstrap-5') }}
</div>
@endsection
