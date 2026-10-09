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
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained('users')->cascadeOnDelete();
            $table->string('crop_type');
            $table->unsignedInteger('qty');
            $table->decimal('price_per_unit', 10, 2)->nullable();
            $table->date('harvest_date');
            $table->string('location');
            $table->string('photo_url')->nullable();
            $table->string('selling_method');
            $table->decimal('starting_price', 10, 2)->nullable();
            $table->timestamp('closing_time')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
