<?php

namespace App\Http\Requests;

use App\Models\MaintenanceTask;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MaintenanceTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'villa_id' => ['required', 'exists:villas,id'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'task_type' => ['required', 'string', 'max:100'],
            'priority' => ['required', Rule::in([
                MaintenanceTask::PRIORITY_LOW,
                MaintenanceTask::PRIORITY_MEDIUM,
                MaintenanceTask::PRIORITY_HIGH,
                MaintenanceTask::PRIORITY_URGENT,
            ])],
            'status' => ['required', Rule::in([
                MaintenanceTask::STATUS_PENDING,
                MaintenanceTask::STATUS_IN_PROGRESS,
                MaintenanceTask::STATUS_COMPLETED,
                MaintenanceTask::STATUS_CANCELLED,
            ])],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'scheduled_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
