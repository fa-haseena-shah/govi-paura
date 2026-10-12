<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rider_id')->constrained('riders', 'user_id')->cascadeOnDelete();
            $table->string('type');
            $table->string('status')->default('pending');
            $table->decimal('fee', 10, 2);
            $table->unsignedTinyInteger('period_months')->default(1);
            $table->string('payment_status')->default('unpaid');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['rider_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
