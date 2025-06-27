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
        Schema::create('bulk_update_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('product_title_update'); // Task type
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->string('file_path')->nullable(); // Uploaded Excel file path
            $table->json('file_data')->nullable(); // Parsed file data
            $table->integer('total_items')->default(0); // Total items count
            $table->integer('processed_items')->default(0); // Processed items count
            $table->integer('successful_items')->default(0); // Successful items count
            $table->integer('failed_items')->default(0); // Failed items count
            $table->json('results')->nullable(); // Detailed results
            $table->json('errors')->nullable(); // Error messages
            $table->timestamp('started_at')->nullable(); // Start time
            $table->timestamp('completed_at')->nullable(); // Completion time
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_update_tasks');
    }
};
