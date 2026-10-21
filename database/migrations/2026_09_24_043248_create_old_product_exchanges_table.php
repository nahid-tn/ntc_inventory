<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('old_product_exchanges', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Exchange Identification
            |--------------------------------------------------------------------------
            */

            $table->string('exchange_no')
                ->unique();

            $table->string('invoice_no')
                ->nullable()
                ->index();

            /*
            |--------------------------------------------------------------------------
            | Customer
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('customer_id')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | CUSTOMER GIVES
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('old_product_id');

            $table->string('old_product_name');

            $table->decimal('old_qty', 10, 2)
                ->default(1);

            $table->string('old_unit')
                ->default('pcs');

            /*
            | Total weight of customer's old product
            */
            $table->decimal('old_weight_kg', 10, 2)
                ->default(0);

            /*
            | Value / unit
            */
            $table->decimal('old_price', 10, 2)
                ->default(0);

            /*
            | Total product value
            */
            $table->decimal('old_total', 12, 2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | CUSTOMER TAKES
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('exchange_product_id');

            $table->string('exchange_product_name');

            $table->decimal('exchange_qty', 10, 2)
                ->default(1);

            $table->string('exchange_unit')
                ->default('pcs');

            /*
            | Total weight of exchanged product
            */
            $table->decimal('exchange_weight_kg', 10, 2)
                ->default(0);

            /*
            | Value / unit
            */
            $table->decimal('exchange_price', 10, 2)
                ->default(0);

            /*
            | Total product value
            */
            $table->decimal('exchange_total', 12, 2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | WEIGHT DIFFERENCE
            |--------------------------------------------------------------------------
            */

            /*
            | exchange_weight - old_weight
            */
            $table->decimal('weight_difference_kg', 10, 2)
                ->default(0);

            /*
            | টাকা প্রতি KG
            */
            $table->decimal('weight_rate', 10, 2)
                ->default(0);

            /*
            | Weight difference monetary value
            */
            $table->decimal('weight_adjustment', 12, 2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | EXCHANGE FEE
            |--------------------------------------------------------------------------
            */

            $table->decimal('exchange_fee', 12, 2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | VALUE DIFFERENCE
            |--------------------------------------------------------------------------
            */

            /*
            | exchange_total - old_total
            */
            $table->decimal('value_difference', 12, 2)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | FINAL SETTLEMENT
            |--------------------------------------------------------------------------
            */

            $table->decimal('settlement_amount', 12, 2)
                ->default(0);

            /*
            | customer_pay
            | customer_receive
            | no_adjustment
            */
            $table->string('settlement_type')
                ->default('no_adjustment')
                ->index();

            /*
            |--------------------------------------------------------------------------
            | Date / Status
            |--------------------------------------------------------------------------
            */

            $table->date('exchange_date')
                ->index();

            $table->string('status')
                ->default('completed')
                ->index();

            /*
            |--------------------------------------------------------------------------
            | Notes
            |--------------------------------------------------------------------------
            */

            $table->text('notes')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Cancellation
            |--------------------------------------------------------------------------
            */

            $table->timestamp('cancelled_at')
                ->nullable();

            $table->unsignedBigInteger('cancelled_by')
                ->nullable();

            $table->text('cancel_reason')
                ->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Foreign Keys
            |--------------------------------------------------------------------------
            */

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->nullOnDelete();

            $table->foreign('old_product_id')
                ->references('id')
                ->on('old_products')
                ->restrictOnDelete();

            $table->foreign('exchange_product_id')
                ->references('id')
                ->on('old_products')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('old_product_exchanges');
    }
};