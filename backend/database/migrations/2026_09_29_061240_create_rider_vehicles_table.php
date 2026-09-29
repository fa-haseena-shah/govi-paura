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
        Schema::create('rider_vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rider_id')->constrained('riders', 'user_id')->cascadeOnDelete();
            $table->string('vehicle_type');
            $table->string('vehicle_no')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rider_vehicles');
    }
};
