<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('maintenance_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('villa_id')->constrained('villas')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('task_type')->index(); // cleaning, pool_service, plumbing, electrical, hvac, landscaping, inspection
            $table->string('priority')->default('medium')->index(); // low, medium, high, urgent
            $table->string('status')->default('pending')->index(); // pending, in_progress, completed, cancelled
            $table->decimal('cost', 10, 2)->default(0);
            $table->dateTime('scheduled_at')->nullable()->index();
            $table->dateTime('completed_at')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_tasks');
    }
};
