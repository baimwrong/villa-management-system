@extends('layouts.app')

@section('title', 'Edit Task: ' . $task->title)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold mb-1 text-dark">Edit Maintenance Task</h3>
                <p class="text-muted small mb-0">{{ $task->villa->name }} ({{ $task->villa->code }})</p>
            </div>
            <a href="{{ route('maintenance.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Tasks
            </a>
        </div>

        <div class="card card-luxury">
            <div class="card-body p-4">
                <form action="{{ route('maintenance.update', $task) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Target Property <span class="text-danger">*</span></label>
                            <select name="villa_id" class="form-select" required>
                                @foreach($villas as $villa)
                                    <option value="{{ $villa->id }}" {{ old('villa_id', $task->villa_id) == $villa->id ? 'selected' : '' }}>
                                        [{{ $villa->code }}] {{ $villa->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Assign Staff Specialist</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">-- Unassigned (Front Desk Pool) --</option>
                                @foreach($staffMembers as $staff)
                                    <option value="{{ $staff->id }}" {{ old('assigned_to', $task->assigned_to) == $staff->id ? 'selected' : '' }}>
                                        {{ $staff->name }} ({{ ucfirst($staff->role) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Task Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" value="{{ old('title', $task->title) }}" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Task Classification <span class="text-danger">*</span></label>
                            <select name="task_type" class="form-select" required>
                                @foreach(['cleaning', 'pool_service', 'plumbing', 'electrical', 'hvac', 'landscaping', 'inspection'] as $type)
                                    <option value="{{ $type }}" {{ old('task_type', $task->task_type) == $type ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $type)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Urgency / Priority <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select" required>
                                @foreach(['low', 'medium', 'high', 'urgent'] as $p)
                                    <option value="{{ $p }}" {{ old('priority', $task->priority) == $p ? 'selected' : '' }}>
                                        {{ ucfirst($p) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                @foreach(['pending', 'in_progress', 'completed', 'cancelled'] as $st)
                                    <option value="{{ $st }}" {{ old('status', $task->status) == $st ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $st)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Scheduled Date & Time</label>
                            <input type="datetime-local" name="scheduled_at" class="form-control" value="{{ old('scheduled_at', $task->scheduled_at ? $task->scheduled_at->format('Y-m-d\TH:i') : '') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Incurred / Estimated Cost ($)</label>
                            <input type="number" step="0.01" name="cost" class="form-control" value="{{ old('cost', $task->cost) }}">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-secondary">Work Description</label>
                        <textarea name="description" rows="3" class="form-control">{{ old('description', $task->description) }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="if(confirm('Delete this task?')) document.getElementById('delete-task-form').submit();">
                            Delete Task
                        </button>

                        <div class="d-flex gap-2">
                            <a href="{{ route('maintenance.index') }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-gold px-4">
                                Update Task
                            </button>
                        </div>
                    </div>
                </form>

                <form id="delete-task-form" action="{{ route('maintenance.destroy', $task) }}" method="POST" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
