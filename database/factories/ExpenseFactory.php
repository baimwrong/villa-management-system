<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\Villa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
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
            'title' => fake()->sentence(3),
            'category' => fake()->randomElement([
                Expense::CATEGORY_MAINTENANCE,
                Expense::CATEGORY_UTILITIES,
                Expense::CATEGORY_SUPPLIES,
                Expense::CATEGORY_FOOD_BEVERAGE,
            ]),
            'amount' => fake()->randomFloat(2, 50, 1500),
            'expense_date' => now()->toDateString(),
            'vendor' => fake()->company(),
            'payment_method' => 'Bank Transfer',
            'notes' => fake()->sentence(),
        ];
    }
}
