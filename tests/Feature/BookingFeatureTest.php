<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Payment;
use App\Models\User;
use App\Models\Villa;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_bookings_list(): void
    {
        $user = User::factory()->create();
        $booking = Booking::factory()->create();

        $response = $this->actingAs($user)->get(route('bookings.index'));

        $response->assertStatus(200);
        $response->assertSee($booking->booking_code);
    }

    public function test_user_can_create_a_booking_and_record_payment(): void
    {
        $user = User::factory()->create();
        $villa = Villa::factory()->create([
            'price_per_night' => 1000.00,
            'cleaning_fee' => 100.00,
        ]);
        $guest = Guest::factory()->create();

        $checkIn = Carbon::now()->addDays(5)->toDateString();
        $checkOut = Carbon::now()->addDays(8)->toDateString();

        $response = $this->actingAs($user)->post(route('bookings.store'), [
            'villa_id' => $villa->id,
            'guest_id' => $guest->id,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'adults' => 2,
            'children' => 0,
            'nightly_rate' => 1000.00,
            'cleaning_fee' => 100.00,
            'service_fee' => 100.00,
            'tax_amount' => 150.00,
            'total_amount' => 3350.00,
            'paid_amount' => 1500.00,
            'payment_method' => 'credit_card',
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $this->assertDatabaseHas('bookings', [
            'villa_id' => $villa->id,
            'guest_id' => $guest->id,
            'total_nights' => 3,
            'total_amount' => 3350.00,
            'paid_amount' => 1500.00,
            'payment_status' => Booking::PAYMENT_PARTIAL,
        ]);

        $booking = Booking::where('villa_id', $villa->id)->first();
        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'amount' => 1500.00,
            'status' => Payment::STATUS_COMPLETED,
        ]);

        $response->assertRedirect(route('bookings.show', $booking));
    }

    public function test_user_can_check_in_and_check_out_guest(): void
    {
        $user = User::factory()->create();
        $villa = Villa::factory()->create(['status' => Villa::STATUS_AVAILABLE]);
        $booking = Booking::factory()->create([
            'villa_id' => $villa->id,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Check in
        $response = $this->actingAs($user)->post(route('bookings.check-in', $booking));
        $response->assertSessionHas('success');
        $this->assertEquals(Booking::STATUS_CHECKED_IN, $booking->fresh()->status);
        $this->assertEquals(Villa::STATUS_OCCUPIED, $villa->fresh()->status);

        // Check out
        $response = $this->actingAs($user)->post(route('bookings.check-out', $booking));
        $response->assertSessionHas('success');
        $this->assertEquals(Booking::STATUS_CHECKED_OUT, $booking->fresh()->status);
        $this->assertEquals(Villa::STATUS_CLEANING, $villa->fresh()->status);

        // Verify automated cleaning task was scheduled
        $this->assertDatabaseHas('maintenance_tasks', [
            'villa_id' => $villa->id,
            'task_type' => 'cleaning',
            'status' => 'pending',
        ]);
    }

    public function test_user_can_view_printable_folio_invoice(): void
    {
        $user = User::factory()->create();
        $booking = Booking::factory()->create();

        $response = $this->actingAs($user)->get(route('bookings.invoice', $booking));

        $response->assertStatus(200);
        $response->assertSee('GUEST FOLIO');
        $response->assertSee($booking->booking_code);
    }
}
