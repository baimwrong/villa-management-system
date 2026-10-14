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
        Schema::create('villas', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->index();
            $table->unsignedTinyInteger('bedrooms')->default(1);
            $table->unsignedTinyInteger('bathrooms')->default(1);
            $table->unsignedTinyInteger('max_guests')->default(2);
            $table->unsignedSmallInteger('area_sqm')->nullable();
            $table->decimal('price_per_night', 12, 2);
            $table->decimal('weekend_price_per_night', 12, 2)->nullable();
            $table->decimal('cleaning_fee', 10, 2)->default(0);
            $table->decimal('security_deposit', 10, 2)->default(0);
            $table->string('status')->default('available')->index(); // available, occupied, cleaning, maintenance, reserved
            $table->string('location')->index();
            $table->text('address')->nullable();
            $table->text('description')->nullable();
            $table->json('amenities')->nullable();
            $table->string('featured_image')->nullable();
            $table->json('images')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('villas');
    }
};
