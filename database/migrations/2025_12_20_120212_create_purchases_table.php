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
        Schema::create('purchases', function (Blueprint $table) {
        $table->id();
        $table->string('Invoice_no', 20)->default('INV-00001');
        $table->string('supplier_invoice_no', 20)->nullable();
        $table->string('purchase_order_no', 20)->nullable();
        $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
        $table->date('date');        
        $table->decimal('subtotal', 12, 2);
        $table->decimal('tax_percentage', 5, 2)->nullable();
        $table->decimal('tax_amount', 12, 2)->default(0);
        $table->decimal('shipping_amount', 12, 2)->default(0);
        $table->decimal('total_amount', 12, 2);
        $table->string('payment_method');
        $table->timestamps();

    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};