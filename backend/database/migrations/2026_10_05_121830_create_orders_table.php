<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('farmer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('bid_id')->nullable()->constrained('bids')->nullOnDelete();
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->default('pending');
            $table->string('payment_status')->default('unpaid');
            $table->string('source'); // fixed_price_purchase | accepted_bid
            $table->timestamps();

            $table->index(['farmer_id', 'status']);
            $table->index('buyer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
