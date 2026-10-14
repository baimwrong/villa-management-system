<?php

namespace Database\Seeders;

use App\Models\MaintenanceTask;
use App\Models\User;
use App\Models\Villa;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MaintenanceTaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $staff = User::where('role', User::ROLE_STAFF)->first() ?? User::first();
        $villa5 = Villa::where('code', 'VIL-005')->first();
        $villa7 = Villa::where('code', 'VIL-007')->first();
        $villa2 = Villa::where('code', 'VIL-002')->first();

        if (! $villa5 || ! $villa7) {
            return;
        }

        $now = Carbon::now();

        $tasks = [
            [
                'villa_id' => $villa5->id,
                'assigned_to' => $staff?->id,
                'title' => 'Deep Turn-Over Cleaning & Linen Sanitization',
                'task_type' => MaintenanceTask::TYPE_CLEANING,
                'priority' => MaintenanceTask::PRIORITY_HIGH,
                'status' => MaintenanceTask::STATUS_IN_PROGRESS,
                'cost' => 120.00,
                'scheduled_at' => $now->copy()->startOfDay()->addHours(9),
                'completed_at' => null,
                'description' => 'Complete turnover cleaning, steam disinfection of mattresses and upholstery, restock organic spa amenities.',
                'notes' => 'Housekeeping team on-site.',
            ],
            [
                'villa_id' => $villa7->id,
                'assigned_to' => $staff?->id,
                'title' => 'Rainforest Plunge Pool Pump Replacement',
                'task_type' => MaintenanceTask::TYPE_POOL_SERVICE,
                'priority' => MaintenanceTask::PRIORITY_URGENT,
                'status' => MaintenanceTask::STATUS_PENDING,
                'cost' => 450.00,
                'scheduled_at' => $now->copy()->addDays(1)->startOfDay()->addHours(10),
                'completed_at' => null,
                'description' => 'Replace secondary circulation pump and test filtration rate before next guest arrival.',
                'notes' => 'Parts arrived from supplier warehouse.',
            ],
            [
                'villa_id' => $villa2->id,
                'assigned_to' => $staff?->id,
                'title' => 'Quarterly Fire & Safety Systems Inspection',
                'task_type' => MaintenanceTask::TYPE_INSPECTION,
                'priority' => MaintenanceTask::PRIORITY_MEDIUM,
                'status' => MaintenanceTask::STATUS_COMPLETED,
                'cost' => 80.00,
                'scheduled_at' => $now->copy()->subDays(3),
                'completed_at' => $now->copy()->subDays(3)->addHours(4),
                'description' => 'Inspected smoke alarms, fire suppression in kitchen, emergency generator batteries, and smart locks.',
                'notes' => 'All systems fully certified.',
            ],
        ];

        foreach ($tasks as $taskData) {
            MaintenanceTask::create($taskData);
        }
    }
}
