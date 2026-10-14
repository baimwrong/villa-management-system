@extends('layouts.app')

@section('title', 'Schedule Maintenance Task')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h3 class="fw-bold mb-1 text-dark">Schedule Housekeeping or Repair Task</h3>
                <p class="text-muted small mb-0">Assign cleaning turn, routine pool balancing, or urgent mechanical repairs.</p>
            </div>
            <a href="{{ route('maintenance.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Tasks
            </a>
        </div>

        <div class="card card-luxury">
            <div class="card-body p-4">
                <form action="{{ route('maintenance.store') }}" method="POST">
                    @csrf

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Target Property <span class="text-danger">*</span></label>
                            <select name="villa_id" class="form-select @error('villa_id') is-invalid @enderror" required>
                                <option value="">-- Choose Villa --</option>
                                @foreach($villas as $villa)
                                    <option value="{{ $villa->id }}" {{ old('villa_id') == $villa->id ? 'selected' : '' }}>
                                        [{{ $villa->code }}] {{ $villa->name }} ({{ ucfirst($villa->status) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('villa_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Assign Staff Specialist</label>
                            <select name="assigned_to" class="form-select">
                                <option value="">-- Unassigned (Front Desk Pool) --</option>
                                @foreach($staffMembers as $staff)
                                    <option value="{{ $staff->id }}" {{ old('assigned_to') == $staff->id ? 'selected' : '' }}>
                                        {{ $staff->name }} ({{ ucfirst($staff->role) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Task Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required placeholder="e.g. Master Bedroom Jacuzzi Filter Inspection & Chemical Balance">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Task Classification <span class="text-danger">*</span></label>
                            <select name="task_type" class="form-select" required>
                                <option value="cleaning" {{ old('task_type') == 'cleaning' ? 'selected' : '' }}>Turnover Cleaning</option>
                                <option value="pool_service" {{ old('task_type') == 'pool_service' ? 'selected' : '' }}>Pool & Spa Balancing</option>
                                <option value="plumbing" {{ old('task_type') == 'plumbing' ? 'selected' : '' }}>Plumbing & Sanitation</option>
                                <option value="electrical" {{ old('task_type') == 'electrical' ? 'selected' : '' }}>Electrical & Smart Home</option>
                                <option value="hvac" {{ old('task_type') == 'hvac' ? 'selected' : '' }}>HVAC / Air Conditioning</option>
                                <option value="landscaping" {{ old('task_type') == 'landscaping' ? 'selected' : '' }}>Garden & Grounds</option>
                                <option value="inspection" {{ old('task_type') == 'inspection' ? 'selected' : '' }}>Safety & Equipment Inspection</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Urgency / Priority <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select" required>
                                <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low (Routine)</option>
                                <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High (Pre-Arrival)</option>
                                <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent (Requires immediate action)</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-secondary">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="pending" selected>Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Scheduled Execution Date & Time</label>
                            <input type="datetime-local" name="scheduled_at" class="form-control" value="{{ old('scheduled_at', now()->format('Y-m-d\TH:i')) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Estimated Expense Cost ($)</label>
                            <input type="number" step="0.01" name="cost" class="form-control" value="{{ old('cost', 0.00) }}">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-secondary">Detailed Work Description & Instructions</label>
                        <textarea name="description" rows="3" class="form-control" placeholder="Check water pressure, restock Bulgari bath amenities, replace air filters in east wing...">{{ old('description') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('maintenance.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-gold px-4">
                            <i class="fa-solid fa-calendar-check me-2"></i> Schedule Task
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
