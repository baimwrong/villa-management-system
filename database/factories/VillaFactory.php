<?php

namespace Database\Factories;

use App\Models\Villa;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Villa>
 */
class VillaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = 'VIL-'.fake()->unique()->numberBetween(100, 999);
        $name = fake()->company().' Villa';

        return [
            'code' => $code,
            'name' => $name,
            'slug' => Str::slug($name).'-'.strtolower($code),
            'category' => fake()->randomElement(['Beachfront', 'Cliffside', 'Garden Estate', 'Overwater', 'Mountain View']),
            'bedrooms' => fake()->numberBetween(1, 6),
            'bathrooms' => fake()->numberBetween(1, 6),
            'max_guests' => fake()->numberBetween(2, 12),
            'area_sqm' => fake()->numberBetween(150, 700),
            'price_per_night' => fake()->randomFloat(2, 500, 3000),
            'weekend_price_per_night' => fake()->randomFloat(2, 600, 3500),
            'cleaning_fee' => fake()->randomFloat(2, 50, 200),
            'security_deposit' => fake()->randomFloat(2, 200, 1000),
            'status' => Villa::STATUS_AVAILABLE,
            'location' => fake()->city(),
            'address' => fake()->address(),
            'description' => fake()->paragraph(),
            'amenities' => ['Private Pool', 'Ocean View', 'Wi-Fi 6', 'Butler Service'],
            'is_featured' => fake()->boolean(30),
        ];
    }
}
