<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('old_product_purchase_details', function (Blueprint $table) {
    $table->id();
    $table->foreignId('old_product_purchase_id')
          ->constrained('old_product_purchases')
          ->cascadeOnDelete();
    $table->foreignId('old_product_id')
          ->constrained('old_products')
          ->cascadeOnDelete();
    $table->decimal('qty',12,2);
    $table->string('unit');
    $table->decimal('price',12,2);
    $table->decimal('total_amount',12,2);
    $table->text('narration')->nullable();
    $table->timestamps();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('old_products_purchase_details');
    }
};