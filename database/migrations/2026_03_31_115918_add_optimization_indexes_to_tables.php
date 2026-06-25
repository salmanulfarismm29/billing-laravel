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
        Schema::table('users', function (Blueprint $table) {
            $table->index(['shop_id', 'is_active']);
            $table->index('role');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index(['shop_id', 'is_active', 'price']);
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->index(['shop_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['shop_id', 'is_active']);
            $table->dropIndex(['role']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['shop_id', 'is_active', 'price']);
        });

        Schema::table('bills', function (Blueprint $table) {
            $table->dropIndex(['shop_id', 'created_at']);
        });
    }
};
