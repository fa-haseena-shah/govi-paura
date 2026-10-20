<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            // Base fee calculated at delivery creation (rate_per_km × distance_km).
            $table->decimal('fee_lkr', 10, 2)->nullable()->after('estimated_minutes');

            // Boosted fee applied once the delivery becomes at-risk.
            // Stored separately so the original fee is never lost.
            $table->decimal('at_risk_fee_lkr', 10, 2)->nullable()->after('fee_lkr');
        });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropColumn(['fee_lkr', 'at_risk_fee_lkr']);
        });
    }
};
