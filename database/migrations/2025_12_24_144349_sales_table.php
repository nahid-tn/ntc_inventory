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
        Schema::create('sales', function (Blueprint $table) {
        $table->id();
        $table->string('invoice_no')->unique();;
        $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
        $table->date('sale_date');
        $table->decimal('subtotal', 12,2);
        $table->decimal('tax_percentage', 5,2)->nullable();
        $table->decimal('tax_amount', 12,2)->default(0);
        $table->decimal('shipping_amount', 12,2)->default(0);
        $table->decimal('total_amount', 12,2);
        $table->decimal('paid_amount', 12,2)->default(0);
        $table->decimal('discount_amount', 10,2)->default(0);
        $table->decimal('due_amount', 12,2)->nullable();
        $table->string('payment_method')->nullable();
        $table->string('received_type')->nullable();
        $table->string('note')->nullable();
        $table->date('body_date')->nullable();
        $table->string('warranty_period')->nullable();
        $table->string('status')->default('pending');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};