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
        Schema::create('old_product_sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no');
            $table->foreignId('customer_id')->constrained('customers');
            $table->date('sale_date');
            $table->decimal('total_amount', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('paid_amount', 12, 2);
            $table->string('payment_method', 50);
            $table->decimal('due_amount', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('old_product_sales');
    }
};