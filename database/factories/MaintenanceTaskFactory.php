<?php

namespace Database\Factories;

use App\Models\MaintenanceTask;
use App\Models\Villa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaintenanceTask>
 */
class MaintenanceTaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'villa_id' => Villa::factory(),
            'assigned_to' => null,
            'title' => fake()->sentence(4),
            'task_type' => fake()->randomElement([
                MaintenanceTask::TYPE_CLEANING,
                MaintenanceTask::TYPE_POOL_SERVICE,
                MaintenanceTask::TYPE_PLUMBING,
                MaintenanceTask::TYPE_HVAC,
            ]),
            'priority' => fake()->randomElement([
                MaintenanceTask::PRIORITY_LOW,
                MaintenanceTask::PRIORITY_MEDIUM,
                MaintenanceTask::PRIORITY_HIGH,
            ]),
            'status' => MaintenanceTask::STATUS_PENDING,
            'cost' => fake()->randomFloat(2, 50, 500),
            'scheduled_at' => now()->addDays(2),
            'description' => fake()->paragraph(),
        ];
    }
}
