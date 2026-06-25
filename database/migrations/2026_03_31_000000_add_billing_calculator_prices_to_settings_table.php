<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Stores an ordered JSON array of selected price shortcuts,
            // e.g. [10.00, 15.00, 25.00] — up to 10 elements.
            $table->json('billing_calculator_prices')->nullable()->after('ask_payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('billing_calculator_prices');
        });
    }
};
