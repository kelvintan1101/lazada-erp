<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Add the new status field (simplified to 2 statuses)
            $table->enum('status', [
                'active',             // Product exists on Lazada (returned by API)
                'deleted_from_lazada' // Product removed from Lazada (not returned by API)
            ])->default('active')->after('is_active');
            
            // Add index for performance
            $table->index('status');
        });

        // Migrate existing data: convert is_active boolean to status enum
        DB::statement("UPDATE products SET status = CASE WHEN is_active = 1 THEN 'active' ELSE 'deleted_from_lazada' END");

        Schema::table('products', function (Blueprint $table) {
            // Remove the old boolean field and its index
            $table->dropIndex(['is_active']);
            $table->dropColumn('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Add back the boolean field
            $table->boolean('is_active')->default(true)->after('synced_at');
            $table->index('is_active');
        });

        // Convert status back to boolean
        DB::statement("UPDATE products SET is_active = CASE WHEN status = 'active' THEN 1 ELSE 0 END");

        Schema::table('products', function (Blueprint $table) {
            // Remove the status field
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });
    }
};
