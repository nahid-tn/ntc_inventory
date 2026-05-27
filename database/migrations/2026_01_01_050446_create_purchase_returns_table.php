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
        // যদি টেবিল না থাকে, তখনই তৈরি হবে
        if (!Schema::hasTable('purchase_returns')) {
            Schema::create('purchase_returns', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_no');
                // $table->string('return_no');
                $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->cascadeOnDelete();
                $table->date('return_date');
                $table->text('note')->nullable();
                $table->decimal('total_amount', 12, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // টেবিল থাকলে ড্রপ করা হবে
        Schema::dropIfExists('purchase_returns');
    }
};
