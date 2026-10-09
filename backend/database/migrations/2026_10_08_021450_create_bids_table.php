<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('bid_amount', 10, 2);   // price per unit, comparable to listings.starting_price
            $table->unsignedInteger('bid_qty');
            $table->string('bid_status')->default('pending');
            $table->timestamps();                    // created_at = bid time

            $table->index(['listing_id', 'bid_amount']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bids');
    }
};
