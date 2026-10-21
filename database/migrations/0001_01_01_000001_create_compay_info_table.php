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
        Schema::create('company_info', function (Blueprint $table) {
            $table->id();
            $table->string('company_name_english')->nullable();
            $table->string('company_name_bangla')->nullable();
            $table->string('company_nick_name_english')->nullable();
            $table->string('company_nick_name_bangla')->nullable();
            $table->string('logo')->nullable();
            $table->string('email')->unique();
            $table->string('company_address_english')->nullable();
            $table->string('company_address_bangla')->nullable();
            $table->string('company_phone_english')->nullable();
            $table->string('company_phone_bangla')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_info');
    }
};
