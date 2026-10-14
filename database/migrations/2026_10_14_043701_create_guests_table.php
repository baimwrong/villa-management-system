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
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('nationality')->nullable()->index();
            $table->string('id_or_passport')->nullable()->index();
            $table->string('vip_level')->default('standard')->index(); // standard, vip, vvip
            $table->text('special_requests')->nullable();
            $table->unsignedInteger('total_stays')->default(0);
            $table->decimal('total_spent', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
