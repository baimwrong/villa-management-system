<?php

namespace App\Http\Controllers;

use App\Http\Requests\MaintenanceTaskRequest;
use App\Models\MaintenanceTask;
use App\Models\User;
use App\Models\Villa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    public function index(Request $request): View
    {
        $query = MaintenanceTask::with(['villa', 'assignedStaff']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->string('priority'));
        }

        if ($request->filled('task_type')) {
            $query->where('task_type', $request->string('task_type'));
        }

        if ($request->filled('villa_id')) {
            $query->where('villa_id', $request->input('villa_id'));
        }

        $tasks = $query->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END")
            ->latest('scheduled_at')
            ->paginate(12)
            ->withQueryString();

        $villas = Villa::orderBy('code')->get();
        $staffMembers = User::whereIn('role', [User::ROLE_STAFF, User::ROLE_MANAGER])->get();

        $pendingCount = MaintenanceTask::where('status', MaintenanceTask::STATUS_PENDING)->count();
        $inProgressCount = MaintenanceTask::where('status', MaintenanceTask::STATUS_IN_PROGRESS)->count();
        $completedCount = MaintenanceTask::where('status', MaintenanceTask::STATUS_COMPLETED)->count();

        return view('maintenance.index', [
            'tasks' => $tasks,
            'villas' => $villas,
            'staffMembers' => $staffMembers,
            'pendingCount' => $pendingCount,
            'inProgressCount' => $inProgressCount,
            'completedCount' => $completedCount,
            'filters' => $request->only(['status', 'priority', 'task_type', 'villa_id']),
        ]);
    }

    public function create(): View
    {
        $villas = Villa::orderBy('code')->get();
        $staffMembers = User::whereIn('role', [User::ROLE_STAFF, User::ROLE_MANAGER])->get();

        return view('maintenance.create', [
            'villas' => $villas,
            'staffMembers' => $staffMembers,
        ]);
    }

    public function store(MaintenanceTaskRequest $request): RedirectResponse
    {
        $task = MaintenanceTask::create($request->validated());

        // If high/urgent maintenance, option to mark villa as under maintenance
        if ($task->priority === MaintenanceTask::PRIORITY_URGENT && $task->villa->status === Villa::STATUS_AVAILABLE) {
            $task->villa->update(['status' => Villa::STATUS_MAINTENANCE]);
        }

        return redirect()->route('maintenance.index')
            ->with('success', "Maintenance task '{$task->title}' scheduled.");
    }

    public function edit(MaintenanceTask $maintenance): View
    {
        $villas = Villa::orderBy('code')->get();
        $staffMembers = User::whereIn('role', [User::ROLE_STAFF, User::ROLE_MANAGER])->get();

        return view('maintenance.edit', [
            'task' => $maintenance,
            'villas' => $villas,
            'staffMembers' => $staffMembers,
        ]);
    }

    public function update(MaintenanceTaskRequest $request, MaintenanceTask $maintenance): RedirectResponse
    {
        $maintenance->update($request->validated());

        return redirect()->route('maintenance.index')
            ->with('success', "Task '{$maintenance->title}' updated.");
    }

    public function updateStatus(Request $request, MaintenanceTask $maintenance): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:pending,in_progress,completed,cancelled'],
        ]);

        $newStatus = $request->string('status')->toString();
        $updateData = ['status' => $newStatus];

        if ($newStatus === MaintenanceTask::STATUS_COMPLETED) {
            $updateData['completed_at'] = now();

            // If the villa was in cleaning or maintenance, restore it to available if no other urgent tasks exist
            if (in_array($maintenance->villa->status, [Villa::STATUS_CLEANING, Villa::STATUS_MAINTENANCE], true)) {
                $hasOtherPending = MaintenanceTask::where('villa_id', $maintenance->villa_id)
                    ->where('id', '!=', $maintenance->id)
                    ->whereIn('status', [MaintenanceTask::STATUS_PENDING, MaintenanceTask::STATUS_IN_PROGRESS])
                    ->exists();

                if (! $hasOtherPending) {
                    $maintenance->villa->update(['status' => Villa::STATUS_AVAILABLE]);
                }
            }
        }

        $maintenance->update($updateData);

        return back()->with('success', 'Task status updated to '.ucfirst(str_replace('_', ' ', $newStatus)).'.');
    }

    public function destroy(MaintenanceTask $maintenance): RedirectResponse
    {
        $maintenance->delete();

        return redirect()->route('maintenance.index')
            ->with('success', 'Maintenance task removed.');
    }
}
