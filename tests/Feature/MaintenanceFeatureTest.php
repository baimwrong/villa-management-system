<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\MaintenanceTask;
use App\Models\User;
use App\Models\Villa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_maintenance_list(): void
    {
        $user = User::factory()->create();
        $villa = Villa::factory()->create();
        $task = MaintenanceTask::factory()->create([
            'villa_id' => $villa->id,
            'title' => 'AC Compressor Service',
            'status' => MaintenanceTask::STATUS_PENDING,
        ]);

        $response = $this->actingAs($user)->get(route('maintenance.index'));

        $response->assertStatus(200);
        $response->assertSee('AC Compressor Service');
    }

    public function test_urgent_task_marks_available_villa_as_maintenance(): void
    {
        $user = User::factory()->create();
        $villa = Villa::factory()->create(['status' => Villa::STATUS_AVAILABLE]);

        $response = $this->actingAs($user)->post(route('maintenance.store'), [
            'villa_id' => $villa->id,
            'title' => 'Burst Pipe in Master Bathroom',
            'task_type' => 'plumbing',
            'priority' => MaintenanceTask::PRIORITY_URGENT,
            'status' => MaintenanceTask::STATUS_PENDING,
            'cost' => 500.00,
        ]);

        $response->assertRedirect(route('maintenance.index'));
        $this->assertEquals(Villa::STATUS_MAINTENANCE, $villa->fresh()->status);
    }

    public function test_completing_repair_task_does_not_mark_cleaning_villa_available(): void
    {
        $user = User::factory()->create();
        $villa = Villa::factory()->create(['status' => Villa::STATUS_CLEANING]);

        // Non-cleaning repair task
        $repairTask = MaintenanceTask::factory()->create([
            'villa_id' => $villa->id,
            'task_type' => 'electrical',
            'status' => MaintenanceTask::STATUS_IN_PROGRESS,
        ]);

        $response = $this->actingAs($user)->patch(route('maintenance.status', $repairTask), [
            'status' => MaintenanceTask::STATUS_COMPLETED,
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(MaintenanceTask::STATUS_COMPLETED, $repairTask->fresh()->status);
        // Villa must remain in CLEANING status
        $this->assertEquals(Villa::STATUS_CLEANING, $villa->fresh()->status);
    }

    public function test_completing_cleaning_task_restores_villa_to_available_when_no_other_tasks_pending(): void
    {
        $user = User::factory()->create();
        $villa = Villa::factory()->create(['status' => Villa::STATUS_CLEANING]);

        $cleaningTask = MaintenanceTask::factory()->create([
            'villa_id' => $villa->id,
            'task_type' => MaintenanceTask::TYPE_CLEANING,
            'status' => MaintenanceTask::STATUS_PENDING,
        ]);

        $response = $this->actingAs($user)->patch(route('maintenance.status', $cleaningTask), [
            'status' => MaintenanceTask::STATUS_COMPLETED,
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(MaintenanceTask::STATUS_COMPLETED, $cleaningTask->fresh()->status);
        // Villa is now cleaned and ready
        $this->assertEquals(Villa::STATUS_AVAILABLE, $villa->fresh()->status);
    }

    public function test_completing_task_on_occupied_villa_retains_occupied_status(): void
    {
        $user = User::factory()->create();
        $villa = Villa::factory()->create(['status' => Villa::STATUS_OCCUPIED]);
        Booking::factory()->create([
            'villa_id' => $villa->id,
            'status' => Booking::STATUS_CHECKED_IN,
        ]);

        $task = MaintenanceTask::factory()->create([
            'villa_id' => $villa->id,
            'task_type' => 'plumbing',
            'status' => MaintenanceTask::STATUS_PENDING,
        ]);

        $response = $this->actingAs($user)->patch(route('maintenance.status', $task), [
            'status' => MaintenanceTask::STATUS_COMPLETED,
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(Villa::STATUS_OCCUPIED, $villa->fresh()->status);
    }
}
