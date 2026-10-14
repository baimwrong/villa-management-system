<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Payment;
use App\Models\User;
use App\Models\Villa;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::first();
        $villa1 = Villa::where('code', 'VIL-001')->first();
        $villa2 = Villa::where('code', 'VIL-002')->first();
        $villa4 = Villa::where('code', 'VIL-004')->first();
        $villa6 = Villa::where('code', 'VIL-006')->first();
        $villa8 = Villa::where('code', 'VIL-008')->first();

        $guest1 = Guest::where('email', 'sophia.montgomery@montecarloconsulting.com')->first();
        $guest2 = Guest::where('email', 'hiroshi.tanaka@tokyo-ventures.jp')->first();
        $guest3 = Guest::where('email', 'claire.dubois@haute-joaillerie.fr')->first();
        $guest4 = Guest::where('email', 'lucas.silva@rio-capital.br')->first();
        $guest5 = Guest::where('email', 'amira.almaktoum@dubai-estates.ae')->first();

        if (! $villa1 || ! $guest1) {
            return;
        }

        $now = Carbon::now();

        $bookings = [
            [
                'booking_code' => 'BK-2026-001',
                'villa_id' => $villa1->id,
                'guest_id' => $guest1->id,
                'user_id' => $admin?->id,
                'check_in' => $now->copy()->subDays(2)->toDateString(),
                'check_out' => $now->copy()->addDays(4)->toDateString(),
                'adults' => 4,
                'children' => 2,
                'total_nights' => 6,
                'nightly_rate' => 2400.00,
                'cleaning_fee' => 180.00,
                'service_fee' => 500.00,
                'tax_amount' => 1200.00,
                'total_amount' => 16280.00,
                'paid_amount' => 16280.00,
                'balance_due' => 0.00,
                'payment_status' => Booking::PAYMENT_PAID,
                'status' => Booking::STATUS_CHECKED_IN,
                'special_notes' => 'Airport helicopter arrival coordinated. In-villa spa session booked for afternoon.',
            ],
            [
                'booking_code' => 'BK-2026-002',
                'villa_id' => $villa6->id,
                'guest_id' => $guest5->id,
                'user_id' => $admin?->id,
                'check_in' => $now->copy()->subDays(3)->toDateString(),
                'check_out' => $now->copy()->addDays(5)->toDateString(),
                'adults' => 6,
                'children' => 4,
                'total_nights' => 8,
                'nightly_rate' => 3200.00,
                'cleaning_fee' => 250.00,
                'service_fee' => 800.00,
                'tax_amount' => 2100.00,
                'total_amount' => 28750.00,
                'paid_amount' => 28750.00,
                'balance_due' => 0.00,
                'payment_status' => Booking::PAYMENT_PAID,
                'status' => Booking::STATUS_CHECKED_IN,
                'special_notes' => 'Private chef full-board service active. 2 security team accommodations reserved.',
            ],
            [
                'booking_code' => 'BK-2026-003',
                'villa_id' => $villa4->id,
                'guest_id' => $guest3->id,
                'user_id' => $admin?->id,
                'check_in' => $now->copy()->addDays(3)->toDateString(),
                'check_out' => $now->copy()->addDays(8)->toDateString(),
                'adults' => 2,
                'children' => 0,
                'total_nights' => 5,
                'nightly_rate' => 1400.00,
                'cleaning_fee' => 110.00,
                'service_fee' => 300.00,
                'tax_amount' => 600.00,
                'total_amount' => 8010.00,
                'paid_amount' => 4005.00,
                'balance_due' => 4005.00,
                'payment_status' => Booking::PAYMENT_PARTIAL,
                'status' => Booking::STATUS_CONFIRMED,
                'special_notes' => '50% deposit paid via American Express. Balance due on check-in.',
            ],
            [
                'booking_code' => 'BK-2026-004',
                'villa_id' => $villa2->id,
                'guest_id' => $guest2->id,
                'user_id' => $admin?->id,
                'check_in' => $now->copy()->addDays(10)->toDateString(),
                'check_out' => $now->copy()->addDays(15)->toDateString(),
                'adults' => 4,
                'children' => 0,
                'total_nights' => 5,
                'nightly_rate' => 1850.00,
                'cleaning_fee' => 140.00,
                'service_fee' => 400.00,
                'tax_amount' => 800.00,
                'total_amount' => 10590.00,
                'paid_amount' => 10590.00,
                'balance_due' => 0.00,
                'payment_status' => Booking::PAYMENT_PAID,
                'status' => Booking::STATUS_CONFIRMED,
                'special_notes' => 'Executive business retreat setup with presentation screens.',
            ],
            [
                'booking_code' => 'BK-2026-005',
                'villa_id' => $villa8->id,
                'guest_id' => $guest4->id,
                'user_id' => $admin?->id,
                'check_in' => $now->copy()->subDays(10)->toDateString(),
                'check_out' => $now->copy()->subDays(5)->toDateString(),
                'adults' => 2,
                'children' => 1,
                'total_nights' => 5,
                'nightly_rate' => 1150.00,
                'cleaning_fee' => 110.00,
                'service_fee' => 250.00,
                'tax_amount' => 500.00,
                'total_amount' => 6610.00,
                'paid_amount' => 6610.00,
                'balance_due' => 0.00,
                'payment_status' => Booking::PAYMENT_PAID,
                'status' => Booking::STATUS_CHECKED_OUT,
                'special_notes' => 'Guest left 5-star review thanking concierge for private boat trip.',
            ],
        ];

        foreach ($bookings as $data) {
            $booking = Booking::updateOrCreate(['booking_code' => $data['booking_code']], $data);

            if ($booking->paid_amount > 0) {
                Payment::updateOrCreate(
                    ['transaction_reference' => 'TXN-'.str_replace('BK-', '', $booking->booking_code).'-01'],
                    [
                        'booking_id' => $booking->id,
                        'amount' => $booking->paid_amount,
                        'payment_method' => Payment::METHOD_CREDIT_CARD,
                        'payment_date' => $booking->check_in,
                        'status' => Payment::STATUS_COMPLETED,
                        'notes' => 'Advance booking payment processed.',
                    ]
                );
            }
        }
    }
}
