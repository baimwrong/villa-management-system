<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Villa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VillaFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_villas_fleet(): void
    {
        $user = User::factory()->create();
        $villa = Villa::factory()->create(['name' => 'Villa Azure Horizon']);

        $response = $this->actingAs($user)->get(route('villas.index'));

        $response->assertStatus(200);
        $response->assertSee('Villa Azure Horizon');
    }

    public function test_user_can_create_a_luxury_villa(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('villas.store'), [
            'code' => 'VIL-990',
            'name' => 'Royal Mirage Palace',
            'category' => 'Beachfront',
            'bedrooms' => 5,
            'bathrooms' => 6,
            'max_guests' => 10,
            'area_sqm' => 650,
            'price_per_night' => 2500.00,
            'weekend_price_per_night' => 2800.00,
            'cleaning_fee' => 150.00,
            'security_deposit' => 1000.00,
            'status' => 'available',
            'location' => 'Golden Palm Cove',
            'description' => 'Exclusive private estate.',
            'amenities' => ['Private Pool', 'Ocean View', 'Butler Service'],
            'is_featured' => 1,
        ]);

        $this->assertDatabaseHas('villas', [
            'code' => 'VIL-990',
            'name' => 'Royal Mirage Palace',
            'category' => 'Beachfront',
        ]);

        $villa = Villa::where('code', 'VIL-990')->first();
        $response->assertRedirect(route('villas.show', $villa));
    }

    public function test_user_can_update_villa_status(): void
    {
        $user = User::factory()->create();
        $villa = Villa::factory()->create(['status' => 'available']);

        $response = $this->actingAs($user)->patch(route('villas.status', $villa), [
            'status' => 'cleaning',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('cleaning', $villa->fresh()->status);
    }
}
