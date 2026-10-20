<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->unique()->constrained('order_items')->cascadeOnDelete();
            $table->foreignId('listing_id')->nullable()->constrained()->nullOnDelete();
            $table->string('crop_type');
            $table->string('region');
            $table->decimal('unit_price', 10, 2);
            $table->unsignedInteger('qty');
            $table->decimal('total_amount', 12, 2);
            $table->string('source');
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['crop_type', 'recorded_at']);
            $table->index(['region', 'recorded_at']);
        });

        DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('listings', 'listings.id', '=', 'order_items.listing_id')
            ->where('orders.payment_status', 'paid')
            ->select([
                'order_items.id as order_item_id', 'order_items.order_id', 'order_items.listing_id',
                'order_items.qty', 'order_items.unit_price', 'order_items.sub_total',
                'orders.source', 'orders.updated_at as paid_at',
                'listings.crop_type', 'listings.location',
            ])
            ->orderBy('order_items.id')
            ->each(function ($row) {
                DB::table('price_records')->insert([
                    'order_id' => $row->order_id,
                    'order_item_id' => $row->order_item_id,
                    'listing_id' => $row->listing_id,
                    'crop_type' => Str::title(trim($row->crop_type)),
                    'region' => Str::title(trim($row->location)),
                    'unit_price' => $row->unit_price,
                    'qty' => $row->qty,
                    'total_amount' => $row->sub_total,
                    'source' => $row->source,
                    'recorded_at' => $row->paid_at,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_records');
    }
};
