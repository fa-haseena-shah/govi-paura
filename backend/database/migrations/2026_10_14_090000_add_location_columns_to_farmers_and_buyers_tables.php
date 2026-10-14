<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // farmers already have address + region; they only need map coordinates
        Schema::table('farmers', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('region');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });

        // buyers had no location at all: this is their default delivery (drop-off) location
        Schema::table('buyers', function (Blueprint $table) {
            $table->string('address', 500)->nullable()->after('business_type');
            $table->string('region')->nullable()->after('address');
            $table->decimal('latitude', 10, 7)->nullable()->after('region');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('farmers', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });

        Schema::table('buyers', function (Blueprint $table) {
            $table->dropColumn(['address', 'region', 'latitude', 'longitude']);
        });
    }
};
