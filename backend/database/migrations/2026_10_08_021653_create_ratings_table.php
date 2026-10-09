<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('score'); // 1-5, enforce in the FormRequest
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['buyer_id', 'farmer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
