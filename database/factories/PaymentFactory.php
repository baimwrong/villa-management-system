<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'transaction_reference' => 'TXN-'.strtoupper(fake()->unique()->bothify('????########')),
            'amount' => fake()->randomFloat(2, 500, 5000),
            'payment_method' => fake()->randomElement([Payment::METHOD_CREDIT_CARD, Payment::METHOD_BANK_TRANSFER, Payment::METHOD_CASH]),
            'payment_date' => now(),
            'status' => Payment::STATUS_COMPLETED,
            'notes' => fake()->sentence(),
        ];
    }
}
