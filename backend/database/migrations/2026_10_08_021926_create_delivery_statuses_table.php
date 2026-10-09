<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_statuses', function (Blueprint $table) {
            $table->foreignId('delivery_id')->constrained()->cascadeOnDelete();
            $table->timestamp('status_timestamp', 6);
            $table->string('status');

            $table->primary(['delivery_id', 'status_timestamp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_statuses');
    }
};
