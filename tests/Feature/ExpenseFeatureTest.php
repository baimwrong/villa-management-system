<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\User;
use App\Models\Villa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_cannot_access_expenses(): void
    {
        $staff = User::factory()->create(['role' => User::ROLE_STAFF]);

        $response = $this->actingAs($staff)->get(route('expenses.index'));

        $response->assertStatus(403);
    }

    public function test_manager_can_view_expenses_list_and_filter(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $villa = Villa::factory()->create(['code' => 'VIL-201']);

        $expense1 = Expense::factory()->create([
            'title' => 'Pool Chemical Refill',
            'category' => Expense::CATEGORY_MAINTENANCE,
            'amount' => 350.00,
            'villa_id' => $villa->id,
            'expense_date' => '2026-10-01',
        ]);

        $expense2 = Expense::factory()->create([
            'title' => 'Organic Breakfast Basket',
            'category' => Expense::CATEGORY_FOOD_BEVERAGE,
            'amount' => 120.00,
            'expense_date' => '2026-10-02',
        ]);

        $response = $this->actingAs($manager)->get(route('expenses.index'));
        $response->assertStatus(200);
        $response->assertSee('Pool Chemical Refill');
        $response->assertSee('Organic Breakfast Basket');

        // Filter by category
        $response = $this->actingAs($manager)->get(route('expenses.index', [
            'category' => Expense::CATEGORY_MAINTENANCE,
        ]));
        $response->assertStatus(200);
        $response->assertSee('Pool Chemical Refill');
        $response->assertDontSee('Organic Breakfast Basket');
    }

    public function test_manager_can_create_expense(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $villa = Villa::factory()->create();

        $response = $this->actingAs($manager)->post(route('expenses.store'), [
            'title' => 'High-Speed Starlink Router',
            'category' => Expense::CATEGORY_UTILITIES,
            'villa_id' => $villa->id,
            'amount' => 450.00,
            'expense_date' => '2026-10-15',
            'vendor' => 'SpaceX Starlink',
            'payment_method' => 'credit_card',
            'invoice_number' => 'INV-9901',
            'notes' => 'Backup satellite link for villa',
        ]);

        $response->assertRedirect(route('expenses.index'));
        $this->assertDatabaseHas('expenses', [
            'title' => 'High-Speed Starlink Router',
            'amount' => 450.00,
            'category' => Expense::CATEGORY_UTILITIES,
            'villa_id' => $villa->id,
        ]);
    }

    public function test_manager_cannot_delete_expense(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_MANAGER]);
        $expense = Expense::factory()->create();

        $response = $this->actingAs($manager)->delete(route('expenses.destroy', $expense));

        $response->assertStatus(403);
        $this->assertDatabaseHas('expenses', ['id' => $expense->id]);
    }

    public function test_admin_can_delete_expense(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $expense = Expense::factory()->create();

        $response = $this->actingAs($admin)->delete(route('expenses.destroy', $expense));

        $response->assertRedirect(route('expenses.index'));
        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }
}
