<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Villa;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn = Carbon::now()->addDays(fake()->numberBetween(1, 30));
        $nights = fake()->numberBetween(2, 7);
        $checkOut = $checkIn->copy()->addDays($nights);
        $nightlyRate = fake()->randomFloat(2, 600, 2500);
        $cleaningFee = 120.00;
        $totalAmount = ($nights * $nightlyRate) + $cleaningFee;

        return [
            'booking_code' => 'BK-'.strtoupper(fake()->unique()->bothify('????####')),
            'villa_id' => Villa::factory(),
            'guest_id' => Guest::factory(),
            'user_id' => null,
            'check_in' => $checkIn->toDateString(),
            'check_out' => $checkOut->toDateString(),
            'adults' => fake()->numberBetween(1, 4),
            'children' => fake()->numberBetween(0, 2),
            'total_nights' => $nights,
            'nightly_rate' => $nightlyRate,
            'cleaning_fee' => $cleaningFee,
            'service_fee' => 150.00,
            'tax_amount' => 200.00,
            'total_amount' => $totalAmount,
            'paid_amount' => $totalAmount,
            'balance_due' => 0.00,
            'payment_status' => Booking::PAYMENT_PAID,
            'status' => Booking::STATUS_CONFIRMED,
            'special_notes' => fake()->sentence(),
        ];
    }
}
