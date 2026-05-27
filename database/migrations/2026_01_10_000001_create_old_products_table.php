<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('old_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id')->nullable(); // nullable হলে create problem হবে না
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
            $table->string('product_name');
            $table->decimal('qty', 10, 2);
            $table->string('unit');
            $table->decimal('price', 10, 2);
            $table->decimal('total',12,2);
            $table->decimal('stock', 10, 2)->default(0);
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('old_products');
    }
};
