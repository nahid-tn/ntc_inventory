<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Old Product Exchange') }}
        </h2>
    </x-slot>

    <div class="py-10">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- ========================================================= --}}
            {{-- TOP BAR --}}
            {{-- ========================================================= --}}
            <div class="flex justify-between items-center mb-4">

                <h4 class="text-xl font-semibold">
                    🔄 Edit Old Product Exchange
                </h4>

                <a href="{{ route('old_product_exchange.show', $exchange->id) }}"
                   class="btn btn-secondary">
                    Back
                </a>

            </div>
            {{-- ========================================================= --}}
            {{-- ALERTS --}}
            {{-- ========================================================= --}}
            {{-- VALIDATION ERROR --}}
            {{-- ========================================================= --}}
            @if($errors->any())
                <div class="alert alert-danger">
                    <strong> সমস্যা হয়েছে:</strong>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            {{-- ========================================================= --}}
            {{-- FORM --}}
            {{-- ========================================================= --}}
            <form method="POST"
                  action="{{ route('old_product_exchange.update', $exchange->id) }}">

                @csrf
                @method('PUT')
                {{-- ========================================================= --}}
                {{-- CUSTOMER INFORMATION --}}
                {{-- ========================================================= --}}
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <strong>👤 Customer Information</strong>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            {{-- Customer Name --}}
                            <div class="col-md-4">
                                <label class="form-label"> Customer Name * </label>
                                <input type="text" id="customer_search" class="form-control" placeholder="Customer name or mobile..." autocomplete="off" value="{{ old('customer_name', optional($exchange->customer)->customer_name) }}" required>
                                {{-- Actual Customer ID --}}
                                <input type="hidden" name="customer_id" id="customer_id" value="{{ old('customer_id', $exchange->customer_id) }}">
                                <div id="customer_info" class="small text-muted mt-1">
                                    @if($exchange->customer)
                                        Mobile:
                                        {{ $exchange->customer->mobile }}
                                        |
                                        Address:
                                        {{ $exchange->customer->address }}
                                    @endif
                                </div>
                                @error('customer_id')
                                    <div class="text-danger">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>


                            {{-- Mobile --}}
                            <div class="col-md-4">
                                <label class="form-label"> Mobile </label>
                                <input type="text" name="customer_mobile" id="customer_mobile" class="form-control" value="{{ old('customer_mobile', optional($exchange->customer)->mobile) }}" readonly>
                            </div>

                            {{-- Invoice --}}
                            <div class="col-md-4">
                                <label class="form-label"> Invoice No </label>
                                <input type="text" name="invoice_no" class="form-control" value="{{ old('invoice_no', $exchange->invoice_no) }}">
                            </div>
                        </div>
                    </div>
                </div>


                {{-- ========================================================= --}}
                {{-- PRODUCTS --}}
                {{-- ========================================================= --}}
                <div class="row">


                    {{-- ===================================================== --}}
                    {{-- CUSTOMER GIVES --}}
                    {{-- ===================================================== --}}
                    <div class="col-md-6">

                        <div class="card border-danger mb-4">

                            <div class="card-header bg-danger text-white">

                                <strong>
                                    🔴 Customer Gives
                                </strong>

                            </div>

                            <div class="card-body">


                                {{-- Old Product --}}
                                <div class="mb-3">

                                    <label class="form-label">
                                        Old Product *
                                    </label>

                                    <select name="old_product_id"
                                            id="old_product_id"
                                            class="form-control"
                                            required>

                                        <option value="">
                                            -- Select Product --
                                        </option>

                                        @foreach($oldProducts as $product)

                                            <option value="{{ $product->id }}"
                                                    data-stock="{{ $product->stock }}"
                                                    data-unit="{{ $product->unit }}"
                                                    data-price="{{ $product->price }}"
                                                    {{ $exchange->old_product_id == $product->id ? 'selected' : '' }}>

                                                {{ $product->product_name }}
                                                |
                                                Stock:
                                                {{ number_format($product->stock, 2) }}
                                                {{ $product->unit }}

                                            </option>

                                        @endforeach

                                    </select>

                                </div>


                                {{-- Stock / Qty / Price --}}
                                <div class="row g-3">

                                    <div class="col-md-4">

                                        <label class="form-label">
                                            Stock
                                        </label>

                                        <input type="text"
                                               id="old_stock"
                                               class="form-control"
                                               readonly>

                                    </div>


                                    <div class="col-md-4">

                                        <label class="form-label">
                                            Quantity
                                        </label>

                                        <input type="number"
                                               step="0.01"
                                               min="0.01"
                                               name="old_qty"
                                               id="old_qty"
                                               class="form-control"
                                               value="{{ old('old_qty', $exchange->old_qty) }}"
                                               required>

                                    </div>


                                    <div class="col-md-4">

                                        <label class="form-label">
                                            Price
                                        </label>

                                        <input type="text"
                                               id="old_price"
                                               class="form-control"
                                               readonly>

                                    </div>

                                </div>


                                {{-- Weight --}}
                                <div class="mt-3">

                                    <label class="form-label">
                                        Weight (KG) *
                                    </label>

                                    <input type="number"
                                           step="0.01"
                                           min="0"
                                           name="old_weight_kg"
                                           id="old_weight_kg"
                                           class="form-control"
                                           value="{{ old('old_weight_kg', $exchange->old_weight_kg) }}"
                                           required>

                                </div>


                                {{-- Total --}}
                                <div class="mt-3">

                                    <label class="form-label">
                                        Customer Gives Value
                                    </label>

                                    <input type="text"
                                           id="old_total"
                                           class="form-control bg-light"
                                           readonly>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- ===================================================== --}}
                    {{-- CUSTOMER TAKES --}}
                    {{-- ===================================================== --}}
                    <div class="col-md-6">

                        <div class="card border-success mb-4">

                            <div class="card-header bg-success text-white">

                                <strong>
                                    🟢 Customer Takes
                                </strong>

                            </div>

                            <div class="card-body">


                                {{-- Exchange Product --}}
                                <div class="mb-3">

                                    <label class="form-label">
                                        Exchange Product *
                                    </label>

                                    <select name="exchange_product_id"
                                            id="exchange_product_id"
                                            class="form-control"
                                            required>

                                        <option value="">
                                            -- Select Product --
                                        </option>

                                        @foreach($oldProducts as $product)

                                            <option value="{{ $product->id }}"
                                                    data-stock="{{ $product->stock }}"
                                                    data-unit="{{ $product->unit }}"
                                                    data-price="{{ $product->price }}"
                                                    {{ $exchange->exchange_product_id == $product->id ? 'selected' : '' }}>

                                                {{ $product->product_name }}
                                                |
                                                Stock:
                                                {{ number_format($product->stock, 2) }}
                                                {{ $product->unit }}

                                            </option>

                                        @endforeach

                                    </select>

                                </div>


                                {{-- Stock / Qty / Price --}}
                                <div class="row g-3">

                                    <div class="col-md-4">

                                        <label class="form-label">
                                            Stock
                                        </label>

                                        <input type="text"
                                               id="exchange_stock"
                                               class="form-control"
                                               readonly>

                                    </div>


                                    <div class="col-md-4">

                                        <label class="form-label">
                                            Quantity
                                        </label>

                                        <input type="number"
                                               step="0.01"
                                               min="0.01"
                                               name="exchange_qty"
                                               id="exchange_qty"
                                               class="form-control"
                                               value="{{ old('exchange_qty', $exchange->exchange_qty) }}"
                                               required>

                                    </div>


                                    <div class="col-md-4">

                                        <label class="form-label">
                                            Price
                                        </label>

                                        <input type="text"
                                               id="exchange_price"
                                               class="form-control"
                                               readonly>

                                    </div>

                                </div>


                                {{-- Weight --}}
                                <div class="mt-3">

                                    <label class="form-label">
                                        Weight (KG) *
                                    </label>

                                    <input type="number"
                                           step="0.01"
                                           min="0"
                                           name="exchange_weight_kg"
                                           id="exchange_weight_kg"
                                           class="form-control"
                                           value="{{ old('exchange_weight_kg', $exchange->exchange_weight_kg) }}"
                                           required>

                                </div>


                                {{-- Total --}}
                                <div class="mt-3">

                                    <label class="form-label">
                                        Customer Takes Value
                                    </label>

                                    <input type="text"
                                           id="exchange_total"
                                           class="form-control bg-light"
                                           readonly>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- ========================================================= --}}
                {{-- WEIGHT / FEE / SETTLEMENT --}}
                {{-- ========================================================= --}}
                <div class="card mb-4">

                    <div class="card-header bg-warning">

                        <strong>
                            ⚖️ Weight & Exchange Fee
                        </strong>

                    </div>

                    <div class="card-body">

                        <div class="row g-3">


                            {{-- Weight Rate --}}
                            <div class="col-md-4">

                                <label class="form-label">
                                    Weight Rate / KG
                                </label>

                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       name="weight_rate"
                                       id="weight_rate"
                                       class="form-control"
                                       value="{{ old('weight_rate', $exchange->weight_rate ?? 0) }}">

                            </div>


                            {{-- Exchange Fee --}}
                            <div class="col-md-4">

                                <label class="form-label">
                                    Exchange Fee
                                </label>

                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       name="exchange_fee"
                                       id="exchange_fee"
                                       class="form-control"
                                       value="{{ old('exchange_fee', $exchange->exchange_fee ?? 0) }}"
                                       required>

                            </div>


                            {{-- Exchange Date --}}
                            <div class="col-md-4">

                                <label class="form-label">
                                    Exchange Date
                                </label>

                                <input type="date"
                                       name="exchange_date"
                                       class="form-control"
                                       value="{{ old('exchange_date', optional($exchange->exchange_date)->format('Y-m-d')) }}"
                                       required>

                            </div>

                        </div>


                        <hr>


                        {{-- Calculation Result --}}
                        <div class="row">


                            {{-- Weight Difference --}}
                            <div class="col-md-3">

                                <div class="alert alert-secondary">

                                    <small>
                                        Weight Difference
                                    </small>

                                    <h5 id="weight_difference">
                                        0.00 KG
                                    </h5>

                                </div>

                            </div>


                            {{-- Product Difference --}}
                            <div class="col-md-3">

                                <div class="alert alert-secondary">

                                    <small>
                                        Product Difference
                                    </small>

                                    <h5 id="value_difference">
                                        ৳ 0.00
                                    </h5>

                                </div>

                            </div>


                            {{-- Weight Adjustment --}}
                            <div class="col-md-3">

                                <div class="alert alert-secondary">

                                    <small>
                                        Weight Adjustment
                                    </small>

                                    <h5 id="weight_adjustment">
                                        ৳ 0.00
                                    </h5>

                                </div>

                            </div>


                            {{-- Final Settlement --}}
                            <div class="col-md-3">

                                <div class="alert alert-primary">

                                    <small>
                                        Final Settlement
                                    </small>

                                    <h4 id="settlement">
                                        ৳ 0.00
                                    </h4>

                                </div>

                            </div>

                        </div>


                        {{-- Settlement Message --}}
                        <div id="settlement_message"
                             class="alert alert-secondary">

                            <strong>
                                No Adjustment
                            </strong>

                        </div>

                    </div>

                </div>


                {{-- ========================================================= --}}
                {{-- NOTES --}}
                {{-- ========================================================= --}}
                <div class="card mb-4">

                    <div class="card-body">

                        <label class="form-label">
                            Notes
                        </label>

                        <textarea name="notes"
                                  class="form-control"
                                  rows="3">{{ old('notes', $exchange->notes) }}</textarea>

                    </div>

                </div>


                {{-- ========================================================= --}}
                {{-- SUBMIT --}}
                {{-- ========================================================= --}}
                <div class="text-end">

                    <a href="{{ route('old_product_exchange.show', $exchange->id) }}"
                       class="btn btn-secondary">

                        Cancel

                    </a>

                    <button type="submit"
                            class="btn btn-primary">

                        <i class="fas fa-save"></i>
                        Update Exchange

                    </button>

                </div>


            </form>

        </div>


        <br>

        <span class="flex justify-center">
            @include('layouts.footer')
        </span>

    </div>


    {{-- ============================================================= --}}
    {{-- JAVASCRIPT --}}
    {{-- ============================================================= --}}
    <script>

        /* ============================================================
           NUMBER HELPER
        ============================================================ */

        function number(value)
        {
            return parseFloat(value) || 0;
        }


        /* ============================================================
           CUSTOMER AUTOCOMPLETE
        ============================================================ */

        $(document).ready(function () {

            $("#customer_search").autocomplete({

                source: function (request, response) {

                    $.ajax({

                        url: "{{ route('customer.autocomplete') }}",

                        type: "GET",

                        dataType: "json",

                        data: {
                            term: request.term
                        },

                        success: function (data) {

                            response(data);

                        },

                        error: function (xhr) {

                            console.log(xhr.responseText);

                            response([]);

                        }

                    });

                },

                minLength: 1,


                select: function (event, ui) {

                    /* Customer ID */
                    $("#customer_id").val(ui.item.id);


                    /* Customer Name */
                    $("#customer_search").val(ui.item.value);


                    /* Customer Mobile */
                    $("#customer_mobile").val(ui.item.mobile);


                    /* Customer Information */
                    $("#customer_info").html(
                        "Mobile: " +
                        ui.item.mobile +
                        " | Address: " +
                        ui.item.address
                    );


                    return false;

                },


                change: function (event, ui) {

                    if (!ui.item) {

                        $("#customer_id").val("");

                        $("#customer_mobile").val("");

                        $("#customer_info").html("");

                    }

                }

            });

        });


        /* ============================================================
           CALCULATE EXCHANGE
        ============================================================ */

        function calculateExchange()
        {

            let oldQty = number(
                document.getElementById('old_qty').value
            );


            let exchangeQty = number(
                document.getElementById('exchange_qty').value
            );


            let oldPrice = number(
                document.getElementById('old_price').value
            );


            let exchangePrice = number(
                document.getElementById('exchange_price').value
            );


            let oldWeight = number(
                document.getElementById('old_weight_kg').value
            );


            let exchangeWeight = number(
                document.getElementById('exchange_weight_kg').value
            );


            let weightRate = number(
                document.getElementById('weight_rate').value
            );


            let fee = number(
                document.getElementById('exchange_fee').value
            );


            /* ========================================================
               PRODUCT TOTAL
            ======================================================== */

            let oldTotal =
                oldQty * oldPrice;


            let exchangeTotal =
                exchangeQty * exchangePrice;


            /* ========================================================
               PRODUCT VALUE DIFFERENCE
            ======================================================== */

            let valueDifference =
                exchangeTotal - oldTotal;


            /* ========================================================
               WEIGHT DIFFERENCE
            ======================================================== */

            let weightDifference =
                exchangeWeight - oldWeight;


            /* ========================================================
               WEIGHT ADJUSTMENT
            ======================================================== */

            let weightAdjustment =
                weightDifference * weightRate;


            /* ========================================================
               FINAL SETTLEMENT
            ======================================================== */

            let settlement =
                valueDifference +
                weightAdjustment +
                fee;


            /* ========================================================
               DISPLAY TOTALS
            ======================================================== */

            document.getElementById(
                'old_total'
            ).value =
                oldTotal.toFixed(2);


            document.getElementById(
                'exchange_total'
            ).value =
                exchangeTotal.toFixed(2);


            /* ========================================================
               WEIGHT DIFFERENCE
            ======================================================== */

            document.getElementById(
                'weight_difference'
            ).innerText =
                weightDifference.toFixed(2) +
                ' KG';


            /* ========================================================
               VALUE DIFFERENCE
            ======================================================== */

            document.getElementById(
                'value_difference'
            ).innerText =
                '৳ ' +
                valueDifference.toFixed(2);

            /* ========================================================
               WEIGHT ADJUSTMENT
            ======================================================== */

            document.getElementById(
                'weight_adjustment'
            ).innerText =
                '৳ ' +
                weightAdjustment.toFixed(2);

            /* ========================================================
               FINAL SETTLEMENT
            ======================================================== */

            document.getElementById(
                'settlement'
            ).innerText =
                '৳ ' +
                Math.abs(settlement).toFixed(2);
            /* ========================================================
               SETTLEMENT MESSAGE
            ======================================================== */

            let message =
                document.getElementById(
                    'settlement_message'
                );
            if (settlement > 0) {

                message.className =
                    'alert alert-danger';
                message.innerHTML =
                    '<strong>Customer Pay:</strong> ৳ ' +
                    Math.abs(settlement).toFixed(2);

            }


            else if (settlement < 0) {
                message.className =
                    'alert alert-success';
                message.innerHTML =
                    '<strong>Customer Receive:</strong> ৳ ' +
                    Math.abs(settlement).toFixed(2);

            }

            else {

                message.className =
                    'alert alert-secondary';

                message.innerHTML =
                    '<strong>No Adjustment</strong>';

            }

        }


        /* ============================================================
           LOAD OLD PRODUCT
        ============================================================ */

        function loadOldProduct()
        {

            const select =
                document.getElementById(
                    'old_product_id'
                );

            const option =
                select.options[
                    select.selectedIndex
                ];

            if (!option || !option.value) {
                document.getElementById(
                    'old_stock'
                ).value = '';
                document.getElementById(
                    'old_price'
                ).value = '';
                calculateExchange();

                return;

            }

            document.getElementById(
                'old_stock'
            ).value =
                option.dataset.stock || 0;

            document.getElementById(
                'old_price'
            ).value =
                option.dataset.price || 0;

            calculateExchange();

        }


        /* ============================================================
           LOAD EXCHANGE PRODUCT
        ============================================================ */

        function loadExchangeProduct()
        {

            const select =
                document.getElementById(
                    'exchange_product_id'
                );

            const option =
                select.options[
                    select.selectedIndex
                ];


            if (!option || !option.value) {
                document.getElementById(
                    'exchange_stock'
                ).value = '';
                document.getElementById(
                    'exchange_price'
                ).value = '';
                calculateExchange();
                return;

            }

            document.getElementById(
                'exchange_stock'
            ).value =
                option.dataset.stock || 0;
            document.getElementById(
                'exchange_price'
            ).value =
                option.dataset.price || 0;
            calculateExchange();

        }


        /* ============================================================
           PRODUCT CHANGE
        ============================================================ */

        document.getElementById(
            'old_product_id'
        ).addEventListener(
            'change',
            loadOldProduct
        );


        document.getElementById(
            'exchange_product_id'
        ).addEventListener(
            'change',
            loadExchangeProduct
        );


        /* ============================================================
           INPUT CALCULATION
        ============================================================ */

        [
            'old_qty',
            'exchange_qty',
            'old_weight_kg',
            'exchange_weight_kg',
            'weight_rate',
            'exchange_fee'
        ].forEach(function (id) {

            document.getElementById(id)
                .addEventListener(
                    'input',
                    calculateExchange
                );

        });


        /* ============================================================
           INITIAL LOAD
        ============================================================ */

        document.addEventListener(
            'DOMContentLoaded',
            function () {
                loadOldProduct();
                loadExchangeProduct();
                calculateExchange();

            }
        );

    </script>


</x-app-layout>