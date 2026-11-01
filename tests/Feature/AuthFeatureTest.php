<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('Villa Management');
        $response->assertSee('Sign In');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@villamanagement.local',
            'password' => Hash::make('password123'),
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'admin@villamanagement.local',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@villamanagement.local',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'admin@villamanagement.local',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_new_staff_can_register(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => 'Jean-Luc Bernard',
            'email' => 'jeanluc@villamanagement.local',
            'role' => 'staff',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('users', [
            'email' => 'jeanluc@villamanagement.local',
            'role' => 'staff',
        ]);
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }

    public function test_registration_ignores_supplied_role_and_defaults_to_staff(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => 'Attacker User',
            'email' => 'attacker@villamanagement.local',
            'role' => User::ROLE_ADMIN,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('users', [
            'email' => 'attacker@villamanagement.local',
            'role' => User::ROLE_STAFF,
        ]);
        $this->assertDatabaseMissing('users', [
            'email' => 'attacker@villamanagement.local',
            'role' => User::ROLE_ADMIN,
        ]);
    }

    public function test_inactive_users_cannot_authenticate(): void
    {
        User::factory()->create([
            'email' => 'inactive@villamanagement.local',
            'password' => Hash::make('password123'),
            'status' => 'inactive',
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'inactive@villamanagement.local',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors([
            'email' => 'Your account has been deactivated. Please contact an administrator.',
        ]);
    }

    public function test_registration_is_blocked_when_disabled(): void
    {
        config(['auth.registration_enabled' => false]);

        $response = $this->get(route('register'));
        $response->assertStatus(403);

        $postResponse = $this->post(route('register.post'), [
            'name' => 'Blocked Staff',
            'email' => 'blocked@villamanagement.local',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $postResponse->assertStatus(403);
        $this->assertDatabaseMissing('users', ['email' => 'blocked@villamanagement.local']);
    }
}
