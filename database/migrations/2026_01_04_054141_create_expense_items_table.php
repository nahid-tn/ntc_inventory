<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_items', function (Blueprint $table) {
        $table->id();

        $table->foreignId('expense_id')->constrained()->onDelete('cascade');
        $table->string('item_name');
        $table->integer('qty');
        $table->decimal('price', 10, 2);
        $table->decimal('subtotal', 12, 2);
        $table->string('description')->nullable();
        $table->timestamps();
    });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_items');
    }
};