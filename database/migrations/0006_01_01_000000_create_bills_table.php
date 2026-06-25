<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cashier_id')->constrained('users')->cascadeOnDelete();
            
            $table->unsignedInteger('bill_number');
            $table->decimal('total', 10, 2);
            $table->unsignedTinyInteger('payment_method'); // Enum
            
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('qr_code')->nullable(); // filename only
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Sequential bill number per shop
            $table->unique(['shop_id', 'bill_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
