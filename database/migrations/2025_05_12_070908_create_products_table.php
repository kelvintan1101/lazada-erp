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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lazada_product_id')->unique()->index();
            $table->string('name');
            $table->string('sku')->index();
            $table->decimal('price', 10, 2);
            $table->integer('stock_quantity');
            $table->text('description')->nullable();
            $table->json('images')->nullable();
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
        Schema::dropIfExists('products');
    }
};
