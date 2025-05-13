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
        Schema::create('lazada_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('seller_id_on_lazada')->index();
            $table->string('access_token');
            $table->string('refresh_token');
            $table->timestamp('expires_at');
            $table->json('country_user_info')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lazada_tokens');
    }
};
