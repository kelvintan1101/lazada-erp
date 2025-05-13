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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lazada_order_id')->unique()->index();
            $table->string('lazada_order_number')->index();
            $table->string('customer_name');
            $table->timestamp('order_date');
            $table->string('status');
            $table->decimal('total_amount', 10, 2);
            $table->json('shipping_address');
            $table->string('payment_method');
            $table->json('raw_data_from_lazada')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
