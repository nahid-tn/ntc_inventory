<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_details', function (Blueprint $table) {
            $table->id();

            // Purchase Order Master
            $table->foreignId('purchase_order_id')
                ->constrained()
                ->cascadeOnDelete();

            // Product
            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            // Qty
            $table->decimal('qty', 12, 3)
                ->default(0);

            // Unit
            $table->string('unit')
                ->nullable();

            // Expected Price
            $table->decimal('price', 12, 2)
                ->default(0);

            // Total
            $table->decimal('total', 12, 2)
                ->default(0);

            // Remarks
            $table->text('remarks')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_details');
    }
};