<?php

namespace Database\Factories;

use App\Models\ImportLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImportLog>
 */
class ImportLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'import_type' => fake()->randomElement([
                ImportLog::TYPE_VILLAS,
                ImportLog::TYPE_GUESTS,
                ImportLog::TYPE_BOOKINGS,
                ImportLog::TYPE_EXPENSES,
            ]),
            'file_name' => fake()->word().'.csv',
            'total_rows' => 10,
            'imported_rows' => 10,
            'failed_rows' => 0,
            'status' => ImportLog::STATUS_COMPLETED,
            'errors' => null,
        ];
    }
}
