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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->string('transaction_reference')->unique();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->default('credit_card')->index(); // credit_card, bank_transfer, cash, stripe
            $table->dateTime('payment_date')->index();
            $table->string('status')->default('completed')->index(); // completed, pending, failed, refunded
            $table->text('notes')->nullable();
            $table->string('receipt_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
