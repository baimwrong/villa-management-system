<?php

namespace Database\Factories;

use App\Models\Guest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Guest>
 */
class GuestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'nationality' => fake()->country(),
            'id_or_passport' => strtoupper(fake()->bothify('??######')),
            'vip_level' => fake()->randomElement([Guest::VIP_STANDARD, Guest::VIP_VIP, Guest::VIP_VVIP]),
            'special_requests' => fake()->sentence(),
            'total_stays' => fake()->numberBetween(0, 5),
            'total_spent' => fake()->randomFloat(2, 0, 20000),
        ];
    }
}
