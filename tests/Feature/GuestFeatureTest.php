<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_index_can_be_viewed_by_authenticated_user(): void
    {
        $user = User::factory()->create();
        $guest = Guest::factory()->create(['first_name' => 'Sophia', 'last_name' => 'Laurent']);

        $response = $this->actingAs($user)->get(route('guests.index'));

        $response->assertStatus(200);
        $response->assertSee('Sophia Laurent');
    }

    public function test_guest_search_and_vip_filtering(): void
    {
        $user = User::factory()->create();
        $vvipGuest = Guest::factory()->create([
            'first_name' => 'Alexander',
            'last_name' => 'Sterling',
            'nationality' => 'British',
            'vip_level' => 'vvip',
        ]);
        $standardGuest = Guest::factory()->create([
            'first_name' => 'Elena',
            'last_name' => 'Rostova',
            'nationality' => 'Russian',
            'vip_level' => 'standard',
        ]);

        // Filter by VIP level
        $response = $this->actingAs($user)->get(route('guests.index', ['vip_level' => 'vvip']));
        $response->assertStatus(200);
        $response->assertSee('Alexander Sterling');
        $response->assertDontSee('Elena Rostova');

        // Filter by search keyword
        $response = $this->actingAs($user)->get(route('guests.index', ['search' => 'Elena']));
        $response->assertStatus(200);
        $response->assertSee('Elena Rostova');
        $response->assertDontSee('Alexander Sterling');
    }

    public function test_guest_can_be_created(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('guests.store'), [
            'first_name' => 'Lucas',
            'last_name' => 'Vanderbilt',
            'email' => 'lucas.v@luxurytravel.com',
            'phone' => '+1 555-0199',
            'nationality' => 'United States',
            'id_or_passport' => 'US778899',
            'vip_level' => 'vip',
            'special_requests' => 'Prefers oceanfront top-floor and extra pillows',
        ]);

        $this->assertDatabaseHas('guests', [
            'email' => 'lucas.v@luxurytravel.com',
            'first_name' => 'Lucas',
            'last_name' => 'Vanderbilt',
            'vip_level' => 'vip',
        ]);

        $guest = Guest::where('email', 'lucas.v@luxurytravel.com')->first();
        $response->assertRedirect(route('guests.show', $guest));
    }

    public function test_guest_can_be_updated(): void
    {
        $user = User::factory()->create();
        $guest = Guest::factory()->create([
            'first_name' => 'Marcus',
            'last_name' => 'Aurelius',
            'vip_level' => 'standard',
        ]);

        $response = $this->actingAs($user)->put(route('guests.update', $guest), [
            'first_name' => 'Marcus',
            'last_name' => 'Aurelius',
            'email' => $guest->email,
            'vip_level' => 'vvip',
        ]);

        $response->assertRedirect(route('guests.show', $guest));
        $this->assertEquals('vvip', $guest->fresh()->vip_level);
    }

    public function test_manager_or_admin_can_delete_guest(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $guest = Guest::factory()->create();

        $response = $this->actingAs($manager)->delete(route('guests.destroy', $guest));

        $response->assertRedirect(route('guests.index'));
        $this->assertDatabaseMissing('guests', ['id' => $guest->id]);
    }

    public function test_staff_cannot_delete_guest(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);
        $guest = Guest::factory()->create();

        $response = $this->actingAs($staff)->delete(route('guests.destroy', $guest));

        $response->assertStatus(403);
        $this->assertDatabaseHas('guests', ['id' => $guest->id]);
    }

    public function test_guest_with_bookings_cannot_be_deleted(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $guest = Guest::factory()->create();
        Booking::factory()->create(['guest_id' => $guest->id]);

        $response = $this->actingAs($manager)->delete(route('guests.destroy', $guest));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('guests', ['id' => $guest->id]);
    }
}
