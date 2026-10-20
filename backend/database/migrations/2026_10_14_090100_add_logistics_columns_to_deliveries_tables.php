<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            // what kind of produce, and where it is, decides which riders can see it
            $table->string('category', 50)->nullable()->after('status');
            $table->string('region', 100)->nullable()->after('category');

            // map pins, snapshotted from the farmer / buyer profiles when the order is placed
            $table->decimal('pickup_lat', 10, 7)->nullable()->after('dropoff');
            $table->decimal('pickup_lng', 10, 7)->nullable()->after('pickup_lat');
            $table->decimal('dropoff_lat', 10, 7)->nullable()->after('pickup_lng');
            $table->decimal('dropoff_lng', 10, 7)->nullable()->after('dropoff_lat');
            $table->decimal('distance_km', 8, 2)->nullable()->after('dropoff_lng');
            $table->unsignedSmallInteger('estimated_minutes')->nullable()->after('distance_km');

            // the 12-hour promise
            $table->timestamp('deadline_at')->nullable()->after('estimated_minutes');
            $table->timestamp('pickup_due_at')->nullable()->after('assigned_at');
            $table->timestamp('picked_up_at')->nullable()->after('pickup_due_at');
            $table->timestamp('in_transit_at')->nullable()->after('picked_up_at');
            $table->timestamp('at_risk_at')->nullable()->after('delivered_at');
            $table->timestamp('sla_breached_at')->nullable()->after('at_risk_at');

            $table->unique('order_id');
            $table->index(['status', 'region', 'deadline_at']);
            $table->index(['rider_id', 'status']);
        });

        Schema::table('delivery_statuses', function (Blueprint $table) {
            $table->text('note')->nullable()->after('status');
            $table->unsignedBigInteger('changed_by')->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_statuses', function (Blueprint $table) {
            $table->dropColumn(['note', 'changed_by']);
        });

        Schema::table('deliveries', function (Blueprint $table) {
            $table->dropIndex(['rider_id', 'status']);
            $table->dropIndex(['status', 'region', 'deadline_at']);
            $table->dropUnique(['order_id']);
            $table->dropColumn([
                'category', 'region', 'pickup_lat', 'pickup_lng', 'dropoff_lat', 'dropoff_lng',
                'distance_km', 'estimated_minutes', 'deadline_at', 'pickup_due_at', 'picked_up_at',
                'in_transit_at', 'at_risk_at', 'sla_breached_at',
            ]);
        });
    }
};
