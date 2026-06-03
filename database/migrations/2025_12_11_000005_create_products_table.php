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
       Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('product_name');
        $table->string('product_code');
        $table->string('model_name')->nullable();
        $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
        $table->decimal('purchase_price', 10, 4);
        $table->decimal('selling_price', 10, 4);
        $table->longText('description')->nullable();
        $table->string('status')->default('active');
        $table->decimal('stock', 10, 2)->default(0);
        $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
