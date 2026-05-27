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
        Schema::create('product_replacements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade'); // কোন সেলের সাথে
            $table->foreignId('old_product_id')->constrained('products')->onDelete('cascade'); // বিক্রি করা পণ্য
            $table->foreignId('new_product_id')->constrained('products')->onDelete('cascade'); // রিপ্লেস পণ্য
            $table->integer('qty')->default(1);
            $table->string('unit')->nullable();
            $table->boolean('is_active')->default(1);
            $table->decimal('price',10,2)->default(0);
            $table->decimal('total',12,2)->default(0);
            $table->date('replaceMent_date');
            $table->date('body_date');
            $table->decimal('replaceMent_fee',10,2)->default(0);
            $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_replacements');
    }
};