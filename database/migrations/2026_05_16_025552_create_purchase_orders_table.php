<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();

            // PO Number
            $table->string('po_number')->unique();

            // Supplier
            $table->foreignId('supplier_id')
                ->constrained()
                ->cascadeOnDelete();

            // Dates
            $table->date('order_date');

            $table->date('expected_date')
                ->nullable();

            // Financial Summary
            $table->decimal('subtotal', 12, 2)
                ->default(0);

            $table->decimal('discount', 12, 2)
                ->default(0);

            $table->decimal('shipping', 12, 2)
                ->default(0);

            $table->decimal('tax', 12, 2)
                ->default(0);

            $table->decimal('total_amount', 12, 2)
                ->default(0);

            // Status
            $table->enum('status', [
                'pending',
                'approved',
                'partial_received',
                'completed',
                'cancelled'
            ])->default('pending');

            // Payment Method (Optional)
            $table->string('payment_method')
                ->nullable();

            // Remarks
            $table->text('note')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};