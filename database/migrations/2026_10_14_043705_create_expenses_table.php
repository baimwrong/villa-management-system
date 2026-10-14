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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('villa_id')->nullable()->constrained('villas')->nullOnDelete();
            $table->string('title');
            $table->string('category')->index(); // utilities, supplies, maintenance, staff_payroll, marketing, food_beverage, other
            $table->decimal('amount', 12, 2);
            $table->date('expense_date')->index();
            $table->string('vendor')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('invoice_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
