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

    public function test_same_day_turnover_bookings_do_not_conflict(): void
    {
        $user = User::factory()->create();
        $villa = Villa::factory()->create(['price_per_night' => 500.00]);
        $guest1 = Guest::factory()->create();
        $guest2 = Guest::factory()->create();

        // Booking 1: Nov 1 to Nov 6
        Booking::factory()->create([
            'villa_id' => $villa->id,
            'guest_id' => $guest1->id,
            'check_in' => '2026-11-01',
            'check_out' => '2026-11-06',
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Booking 2: Nov 6 to Nov 10 (Same-day checkout of Booking 1 and checkin of Booking 2)
        $response = $this->actingAs($user)->post(route('bookings.store'), [
            'villa_id' => $villa->id,
            'guest_id' => $guest2->id,
            'check_in' => '2026-11-06',
            'check_out' => '2026-11-10',
            'adults' => 2,
            'children' => 0,
            'nightly_rate' => 500.00,
            'cleaning_fee' => 50.00,
            'service_fee' => 50.00,
            'tax_amount' => 50.00,
            'total_amount' => 2150.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $response->assertSessionHasNoErrors();
        $booking2 = Booking::where('guest_id', $guest2->id)->where('villa_id', $villa->id)->first();
        $this->assertNotNull($booking2);
        $this->assertEquals('2026-11-06', Carbon::parse($booking2->check_in)->toDateString());
        $this->assertEquals('2026-11-10', Carbon::parse($booking2->check_out)->toDateString());
    }

    public function test_overlapping_booking_dates_are_rejected(): void
    {
        $user = User::factory()->create();
        $villa = Villa::factory()->create();
        $guest1 = Guest::factory()->create();
        $guest2 = Guest::factory()->create();

        Booking::factory()->create([
            'villa_id' => $villa->id,
            'guest_id' => $guest1->id,
            'check_in' => '2026-11-01',
            'check_out' => '2026-11-06',
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Attempt overlap: Nov 4 to Nov 8
        $response = $this->actingAs($user)->post(route('bookings.store'), [
            'villa_id' => $villa->id,
            'guest_id' => $guest2->id,
            'check_in' => '2026-11-04',
            'check_out' => '2026-11-08',
            'adults' => 2,
            'nightly_rate' => 500.00,
            'total_amount' => 2000.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $response->assertSessionHasErrors('check_in');
    }

    public function test_booking_total_amount_is_recalculated_server_side_ignoring_forged_payload(): void
    {
        $user = User::factory()->create();
        $villa = Villa::factory()->create();
        $guest = Guest::factory()->create();

        // 3 nights @ 1000 = 3000, cleaning 100, service 50, tax 150 => true total 3300.00
        // Client maliciously supplies forged total_amount = 1.00
        $response = $this->actingAs($user)->post(route('bookings.store'), [
            'villa_id' => $villa->id,
            'guest_id' => $guest->id,
            'check_in' => '2026-12-01',
            'check_out' => '2026-12-04',
            'adults' => 2,
            'children' => 0,
            'nightly_rate' => 1000.00,
            'cleaning_fee' => 100.00,
            'service_fee' => 50.00,
            'tax_amount' => 150.00,
            'total_amount' => 1.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('bookings', [
            'villa_id' => $villa->id,
            'total_nights' => 3,
            'total_amount' => 3300.00,
        ]);
        $this->assertDatabaseMissing('bookings', [
            'villa_id' => $villa->id,
            'total_amount' => 1.00,
        ]);
    }

    public function test_cannot_check_in_unconfirmed_or_when_villa_occupied(): void
    {
        $user = User::factory()->create();
        $villa = Villa::factory()->create(['status' => Villa::STATUS_OCCUPIED]);
        $booking = Booking::factory()->create([
            'villa_id' => $villa->id,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        // Attempt check-in while villa is already occupied
        $response = $this->actingAs($user)->post(route('bookings.check-in', $booking));
        $response->assertSessionHas('error');
        $this->assertEquals(Booking::STATUS_CONFIRMED, $booking->fresh()->status);

        // Attempt check-in on a cancelled booking
        $villa->update(['status' => Villa::STATUS_AVAILABLE]);
        $booking->update(['status' => Booking::STATUS_CANCELLED]);

        $response = $this->actingAs($user)->post(route('bookings.check-in', $booking));
        $response->assertSessionHas('error');
        $this->assertEquals(Booking::STATUS_CANCELLED, $booking->fresh()->status);
    }

    public function test_cannot_cancel_active_stay_with_guest_in_house(): void
    {
        $user = User::factory()->create();
        $villa = Villa::factory()->create(['status' => Villa::STATUS_OCCUPIED]);
        $booking = Booking::factory()->create([
            'villa_id' => $villa->id,
            'status' => Booking::STATUS_CHECKED_IN,
        ]);

        $response = $this->actingAs($user)->post(route('bookings.cancel', $booking), [
            'cancellation_reason' => 'Guest wants to leave early',
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(Booking::STATUS_CHECKED_IN, $booking->fresh()->status);
        $this->assertEquals(Villa::STATUS_OCCUPIED, $villa->fresh()->status);
    }

    public function test_booking_applies_weekend_rate_pricing_for_weekend_nights(): void
    {
        $user = User::factory()->create();
        $villa = Villa::factory()->create([
            'price_per_night' => 1000.00,
            'weekend_price_per_night' => 1500.00,
        ]);
        $guest = Guest::factory()->create();

        // Friday 2026-11-06 to Sunday 2026-11-08 (2 weekend nights: Fri & Sat @ 1500 = 3000)
        $response = $this->actingAs($user)->post(route('bookings.store'), [
            'villa_id' => $villa->id,
            'guest_id' => $guest->id,
            'check_in' => '2026-11-06',
            'check_out' => '2026-11-08',
            'adults' => 2,
            'nightly_rate' => 1000.00,
            'cleaning_fee' => 100.00,
            'service_fee' => 50.00,
            'tax_amount' => 50.00,
            'total_amount' => 3200.00,
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('bookings', [
            'villa_id' => $villa->id,
            'total_nights' => 2,
            'total_amount' => 3200.00,
        ]);
    }

    public function test_user_can_post_and_void_incidental_charge_to_folio(): void
    {
        $user = User::factory()->create();
        $booking = Booking::factory()->create([
            'total_amount' => 2000.00,
            'paid_amount' => 2000.00,
            'balance_due' => 0.00,
            'payment_status' => Booking::PAYMENT_PAID,
        ]);
        Payment::factory()->create([
            'booking_id' => $booking->id,
            'amount' => 2000.00,
            'status' => Payment::STATUS_COMPLETED,
        ]);

        // Post In-Villa Dining charge of $350
        $response = $this->actingAs($user)->post(route('bookings.charges.store', $booking), [
            'title' => 'Vintage Wine & Private Dining',
            'category' => 'dining',
            'amount' => 350.00,
            'notes' => 'Room service delivered at 20:30',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('booking_charges', [
            'booking_id' => $booking->id,
            'title' => 'Vintage Wine & Private Dining',
            'amount' => 350.00,
        ]);

        $booking->refresh();
        $this->assertEquals(2350.00, (float) $booking->total_amount);
        $this->assertEquals(350.00, (float) $booking->balance_due);
        $this->assertEquals(Booking::PAYMENT_PARTIAL, $booking->payment_status);

        // Void the charge
        $charge = $booking->extraCharges()->first();
        $voidResponse = $this->actingAs($user)->delete(route('bookings.charges.destroy', [$booking, $charge]));

        $voidResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('booking_charges', ['id' => $charge->id]);

        $booking->refresh();
        $this->assertEquals(2000.00, (float) $booking->total_amount);
        $this->assertEquals(0.00, (float) $booking->balance_due);
        $this->assertEquals(Booking::PAYMENT_PAID, $booking->payment_status);
    }

    public function test_user_can_view_tape_chart_calendar(): void
    {
        $user = User::factory()->create();
        $villa = Villa::factory()->create(['name' => 'Sunset Ocean Villa']);
        $booking = Booking::factory()->create([
            'villa_id' => $villa->id,
            'check_in' => '2026-11-05',
            'check_out' => '2026-11-10',
            'status' => Booking::STATUS_CONFIRMED,
        ]);

        $response = $this->actingAs($user)->get(route('bookings.calendar', ['month' => 11, 'year' => 2026]));

        $response->assertStatus(200);
        $response->assertSee('Tape Chart');
        $response->assertSee('Sunset Ocean Villa');
        $response->assertSee($booking->booking_code);
    }
}
