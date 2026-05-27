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
        Schema::create('supplier_payments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
        $table->foreignId('purchase_id')->nullable()->constrained()->nullOnDelete();
        $table->date('payment_date');
        $table->string('payment_method')->nullable();
        $table->decimal('amount', 12, 2);
        $table->string('BankName')->nullable();
        $table->string('account_number')->nullable();
        $table->string('note')->nullable();
        $table->string('cheque_number')->nullable();
        $table->string('transaction_number')->nullable();
        $table->string('deposit_slip')->nullable();
        $table->string('cheque_image')->nullable();
        $table->timestamps();
    });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');
    }
};

