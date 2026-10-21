<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Old Product Exchange
        </h2>
    </x-slot>


    <div class="py-10">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">


            {{-- ========================================================= --}}
            {{-- PAGE HEADER --}}
            {{-- ========================================================= --}}

            <div class="flex justify-between items-center mb-4">

                <h4 class="text-xl font-semibold">
                    🔄 Old Product Exchange
                </h4>

                <a href="{{ route('old_product_exchange.index') }}"
                   class="btn btn-secondary">
                    Exchange List
                </a>

            </div>


            {{-- ========================================================= --}}
            {{-- VALIDATION ERROR --}}
            {{-- ========================================================= --}}

            @if($errors->any())

                <div class="alert alert-danger">

                    <strong>
                        সমস্যা হয়েছে:
                    </strong>

                    <ul class="mb-0">

                        @foreach($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            @endif


            {{-- ========================================================= --}}
            {{-- SESSION ERROR --}}
            {{-- ========================================================= --}}

            @if(session('error'))

                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>

            @endif


            {{-- ========================================================= --}}
            {{-- SESSION SUCCESS --}}
            {{-- ========================================================= --}}

            @if(session('success'))

                <div class="alert alert-success">
                    {{ session('success') }}
                </div>

            @endif


            {{-- ========================================================= --}}
            {{-- MAIN FORM --}}
            {{-- ========================================================= --}}

            <form method="POST"
                  action="{{ route('old_product_exchange.store') }}">

                @csrf


                {{-- ===================================================== --}}
                {{-- CUSTOMER INFORMATION --}}
                {{-- ===================================================== --}}

                <div class="card mb-4">

                    <div class="card-header bg-primary text-white">

                        <strong>
                            👤 Customer Information
                        </strong>

                    </div>


                    <div class="card-body">

                        <div class="row g-2">


                            {{-- CUSTOMER --}}
                            <div class="col-md-6">

                                <label class="form-label">
                                    Customer Name *
                                </label>


                                <div class="position-relative">

                                    <input type="text"
                                           id="customer_search"
                                           class="form-control"
                                           placeholder="Customer name or mobile ..."
                                           autocomplete="off"
                                           value="{{ old('customer_name') }}"
                                           required>


                                    <button type="button"
                                            class="btn btn-success btn-sm position-absolute"
                                            style="
                                                top:50%;
                                                right:5px;
                                                transform:translateY(-50%);
                                                width:36px;
                                                height:36px;
                                                padding:0;
                                            "
                                            title="Add Customer"
                                            onclick="openCustomerWindow()">

                                        <i class="fa fa-plus"></i>

                                    </button>

                                </div>


                                <input type="hidden"
                                       name="customer_id"
                                       id="customer_id"
                                       value="{{ old('customer_id') }}">


                                <div id="customer_info"
                                     class="small text-muted mt-1">
                                </div>


                                @error('customer_id')

                                    <div class="text-danger">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>


                            {{-- INVOICE --}}
                            <div class="col-md-6">

                                <label class="form-label">
                                    Invoice No
                                </label>


                                <input type="text"
                                       name="invoice_no"
                                       id="invoice_no"
                                       class="form-control"
                                       value="{{ old('invoice_no', 'INV-' . date('YmdHis')) }}"
                                       readonly>


                                <small class="text-muted">
                                    Invoice No. automatically generated
                                </small>

                            </div>

                        </div>

                    </div>

                </div>



                {{-- ========================================================= --}}
                {{-- CUSTOMER GIVES / CUSTOMER TAKES --}}
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


                                {{-- OLD PRODUCT --}}
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

                                            <option
                                                value="{{ $product->id }}"
                                                data-price="{{ $product->price }}"
                                                data-unit="{{ $product->unit }}"
                                                {{ old('old_product_id') == $product->id ? 'selected' : '' }}>

                                                ID: {{ $product->id }}
                                                |
                                                {{ $product->product_name }}
                                                |
                                                Stock:
                                                {{ number_format($product->stock, 2) }}
                                                {{ $product->unit }}

                                            </option>

                                        @endforeach

                                    </select>

                                </div>


                                {{-- QUANTITY + WEIGHT --}}
                                <div class="row g-3">


                                    {{-- QUANTITY --}}
                                    <div class="col-md-6"
                                         id="old_qty_group">

                                        <label class="form-label">
                                            Quantity
                                        </label>


                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               name="old_qty"
                                               id="old_qty"
                                               class="form-control"
                                               value="{{ old('old_qty', 0) }}">

                                    </div>


                                    {{-- WEIGHT --}}
                                    <div class="col-md-6"
                                         id="old_weight_group">

                                        <label class="form-label">
                                            Weight (KG)
                                        </label>


                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               name="old_weight_kg"
                                               id="old_weight_kg"
                                               class="form-control"
                                               value="{{ old('old_weight_kg', 0) }}">

                                    </div>

                                </div>


                                {{-- PRICE --}}
                                <div class="mt-3">

                                    <label class="form-label">
                                        Price
                                    </label>


                                    <input type="number"
                                           step="0.01"
                                           id="old_price"
                                           class="form-control"
                                           value="0"
                                           readonly>

                                </div>


                                {{-- CUSTOMER GIVES VALUE --}}
                                <div class="mt-3">

                                    <label class="form-label">
                                        Customer Gives Value
                                    </label>


                                    <input type="text"
                                           id="old_total_display"
                                           class="form-control bg-light"
                                           value="0.00"
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


                                {{-- EXCHANGE PRODUCT --}}
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

                                            <option
                                                value="{{ $product->id }}"
                                                data-price="{{ $product->price }}"
                                                data-stock="{{ $product->stock }}"
                                                data-unit="{{ $product->unit }}"
                                                {{ old('exchange_product_id') == $product->id ? 'selected' : '' }}>

                                                ID: {{ $product->id }}
                                                |
                                                {{ $product->product_name }}
                                                |
                                                Stock:
                                                {{ number_format($product->stock, 2) }}
                                                {{ $product->unit }}

                                            </option>

                                        @endforeach

                                    </select>

                                </div>


                                {{-- QUANTITY + WEIGHT --}}
                                <div class="row g-3">


                                    {{-- QUANTITY --}}
                                    <div class="col-md-6"
                                         id="exchange_qty_group">

                                        <label class="form-label">
                                            Quantity
                                        </label>


                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               name="exchange_qty"
                                               id="exchange_qty"
                                               class="form-control"
                                               value="{{ old('exchange_qty', 0) }}">

                                    </div>


                                    {{-- WEIGHT --}}
                                    <div class="col-md-6"
                                         id="exchange_weight_group">

                                        <label class="form-label">
                                            Weight (KG)
                                        </label>


                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               name="exchange_weight_kg"
                                               id="exchange_weight_kg"
                                               class="form-control"
                                               value="{{ old('exchange_weight_kg', 0) }}">

                                    </div>

                                </div>


                                {{-- PRICE --}}
                                <div class="mt-3">

                                    <label class="form-label">
                                        Price
                                    </label>


                                    <input type="number"
                                           step="0.01"
                                           id="exchange_price"
                                           class="form-control"
                                           value="0"
                                           readonly>

                                </div>


                                {{-- CUSTOMER TAKES VALUE --}}
                                <div class="mt-3">

                                    <label class="form-label">
                                        Customer Takes Value
                                    </label>


                                    <input type="text"
                                           id="exchange_total_display"
                                           class="form-control bg-light"
                                           value="0.00"
                                           readonly>

                                </div>


                            </div>

                        </div>

                    </div>

                </div>



                {{-- ========================================================= --}}
                {{-- WEIGHT / FEE --}}
                {{-- ========================================================= --}}

                <div class="card mb-4">

                    <div class="card-header bg-warning">

                        <strong>
                            ⚖️ Weight & Exchange Fee
                        </strong>

                    </div>


                    <div class="card-body">

                        <div class="row g-3">


                            {{-- WEIGHT RATE --}}
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
                                       value="{{ old('weight_rate', 0) }}">

                            </div>


                            {{-- EXCHANGE FEE --}}
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
                                       value="{{ old('exchange_fee', 0) }}">

                            </div>


                            {{-- DATE --}}
                            <div class="col-md-4">

                                <label class="form-label">
                                    Exchange Date
                                </label>


                                <input type="date"
                                       name="exchange_date"
                                       class="form-control"
                                       value="{{ old('exchange_date', date('Y-m-d')) }}"
                                       required>

                            </div>

                        </div>


                        <hr>


                        {{-- CALCULATION SUMMARY --}}
                        <div class="row">


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


                        {{-- SETTLEMENT MESSAGE --}}
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
                                  rows="3">{{ old('notes') }}</textarea>

                    </div>

                </div>



                {{-- ========================================================= --}}
                {{-- SUBMIT --}}
                {{-- ========================================================= --}}

                <div class="text-end">

                    <a href="{{ route('old_product_exchange.index') }}"
                       class="btn btn-secondary">

                        Cancel

                    </a>


                    <button type="submit"
                            class="btn btn-primary">

                        <i class="fas fa-exchange-alt"></i>

                        Complete Exchange

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
           NUMBER
        ============================================================ */

        function number(value)
        {
            const result = parseFloat(value);

            return Number.isFinite(result)
                ? result
                : 0;
        }



        /* ============================================================
           PRODUCT CALCULATION TYPE
        ============================================================ */

        function getProductCalculationType(productId)
        {
            productId = parseInt(productId) || 0;

            if (
                productId === 1 ||
                productId === 2
            ) {
                return 'weight';
            }

            return 'qty';
        }



        /* ============================================================
           GET PRODUCT PRICE
        ============================================================ */

        function getSelectedProductPrice(selectId)
        {
            const select =
                document.getElementById(selectId);

            if (!select) {
                return 0;
            }

            const option =
                select.options[select.selectedIndex];

            if (!option) {
                return 0;
            }

            return number(option.dataset.price);
        }



        /* ============================================================
           OLD PRODUCT INPUT MODE
        ============================================================ */

        function updateOldProductInputMode()
        {
            const productId =
                parseInt(
                    document.getElementById(
                        'old_product_id'
                    ).value
                ) || 0;


            const qtyInput =
                document.getElementById('old_qty');

            const weightInput =
                document.getElementById('old_weight_kg');

            const qtyGroup =
                document.getElementById('old_qty_group');

            const weightGroup =
                document.getElementById('old_weight_group');


            /*
            |--------------------------------------------------------------------------
            | ID 1 / 2 = WEIGHT BASED
            |--------------------------------------------------------------------------
            */

            if (
                productId === 1 ||
                productId === 2
            ) {

                qtyInput.value = '0';

                qtyInput.disabled = true;

                qtyInput.required = false;


                weightInput.disabled = false;

                weightInput.required = true;


                qtyGroup.style.opacity = '0.5';

                weightGroup.style.opacity = '1';

            }


            /*
            |--------------------------------------------------------------------------
            | ID 3 = QUANTITY BASED
            |--------------------------------------------------------------------------
            */

            else if (
                productId === 3
            ) {

                weightInput.value = '0';

                weightInput.disabled = true;

                weightInput.required = false;


                qtyInput.disabled = false;

                qtyInput.required = true;


                qtyGroup.style.opacity = '1';

                weightGroup.style.opacity = '0.5';

            }


            /*
            |--------------------------------------------------------------------------
            | OTHER = QUANTITY BASED
            |--------------------------------------------------------------------------
            */

            else {

                qtyInput.disabled = false;

                weightInput.disabled = false;


                qtyInput.required = false;

                weightInput.required = false;


                qtyGroup.style.opacity = '1';

                weightGroup.style.opacity = '1';

            }


            calculateExchange();
        }



        /* ============================================================
           EXCHANGE PRODUCT INPUT MODE
        ============================================================ */

        function updateExchangeProductInputMode()
        {
            const productId =
                parseInt(
                    document.getElementById(
                        'exchange_product_id'
                    ).value
                ) || 0;


            const qtyInput =
                document.getElementById('exchange_qty');

            const weightInput =
                document.getElementById('exchange_weight_kg');

            const qtyGroup =
                document.getElementById('exchange_qty_group');

            const weightGroup =
                document.getElementById('exchange_weight_group');


            /*
            |--------------------------------------------------------------------------
            | ID 1 / 2 = WEIGHT BASED
            |--------------------------------------------------------------------------
            */

            if (
                productId === 1 ||
                productId === 2
            ) {

                qtyInput.value = '0';

                qtyInput.disabled = true;

                qtyInput.required = false;


                weightInput.disabled = false;

                weightInput.required = true;


                qtyGroup.style.opacity = '0.5';

                weightGroup.style.opacity = '1';

            }


            /*
            |--------------------------------------------------------------------------
            | ID 3 = QUANTITY BASED
            |--------------------------------------------------------------------------
            */

            else if (
                productId === 3
            ) {

                weightInput.value = '0';

                weightInput.disabled = true;

                weightInput.required = false;


                qtyInput.disabled = false;

                qtyInput.required = true;


                qtyGroup.style.opacity = '1';

                weightGroup.style.opacity = '0.5';

            }


            /*
            |--------------------------------------------------------------------------
            | OTHER = QUANTITY BASED
            |--------------------------------------------------------------------------
            */

            else {

                qtyInput.disabled = false;

                weightInput.disabled = false;


                qtyInput.required = false;

                weightInput.required = false;


                qtyGroup.style.opacity = '1';

                weightGroup.style.opacity = '1';

            }


            calculateExchange();
        }



        /* ============================================================
           CALCULATE EXCHANGE
        ============================================================ */

        function calculateExchange()
        {

            const oldQty =
                number(
                    document.getElementById('old_qty').value
                );


            const exchangeQty =
                number(
                    document.getElementById('exchange_qty').value
                );


            const oldWeight =
                number(
                    document.getElementById('old_weight_kg').value
                );


            const exchangeWeight =
                number(
                    document.getElementById('exchange_weight_kg').value
                );


            const weightRate =
                number(
                    document.getElementById('weight_rate').value
                );


            const fee =
                number(
                    document.getElementById('exchange_fee').value
                );


            const oldProductId =
                parseInt(
                    document.getElementById(
                        'old_product_id'
                    ).value
                ) || 0;


            const exchangeProductId =
                parseInt(
                    document.getElementById(
                        'exchange_product_id'
                    ).value
                ) || 0;


            const oldPrice =
                getSelectedProductPrice(
                    'old_product_id'
                );


            const exchangePrice =
                getSelectedProductPrice(
                    'exchange_product_id'
                );


            /*
            |--------------------------------------------------------------------------
            | PRICE DISPLAY
            |--------------------------------------------------------------------------
            */

            document.getElementById(
                'old_price'
            ).value =
                oldPrice.toFixed(2);


            document.getElementById(
                'exchange_price'
            ).value =
                exchangePrice.toFixed(2);



            /*
            |--------------------------------------------------------------------------
            | CUSTOMER GIVES
            |--------------------------------------------------------------------------
            */

            const oldType =
                getProductCalculationType(
                    oldProductId
                );


            let oldTotal = 0;


            if (oldType === 'weight') {

                oldTotal =
                    oldWeight *
                    oldPrice;

            } else {

                oldTotal =
                    oldQty *
                    oldPrice;

            }



            /*
            |--------------------------------------------------------------------------
            | CUSTOMER TAKES
            |--------------------------------------------------------------------------
            */

            const exchangeType =
                getProductCalculationType(
                    exchangeProductId
                );


            let exchangeTotal = 0;


            if (exchangeType === 'weight') {

                exchangeTotal =
                    exchangeWeight *
                    exchangePrice;

            } else {

                exchangeTotal =
                    exchangeQty *
                    exchangePrice;

            }



            /*
            |--------------------------------------------------------------------------
            | VALUE DIFFERENCE
            |--------------------------------------------------------------------------
            */

            const valueDifference =
                exchangeTotal -
                oldTotal;



            /*
            |--------------------------------------------------------------------------
            | WEIGHT DIFFERENCE
            |--------------------------------------------------------------------------
            */

            const weightDifference =
                exchangeWeight -
                oldWeight;



            /*
            |--------------------------------------------------------------------------
            | WEIGHT ADJUSTMENT
            |--------------------------------------------------------------------------
            */

            const weightAdjustment =
                weightDifference *
                weightRate;



            /*
            |--------------------------------------------------------------------------
            | FINAL SETTLEMENT
            |--------------------------------------------------------------------------
            */

            const settlement =
                valueDifference +
                weightAdjustment +
                fee;



            /*
            |--------------------------------------------------------------------------
            | DISPLAY
            |--------------------------------------------------------------------------
            */

            document.getElementById(
                'old_total_display'
            ).value =
                oldTotal.toFixed(2);


            document.getElementById(
                'exchange_total_display'
            ).value =
                exchangeTotal.toFixed(2);


            document.getElementById(
                'weight_difference'
            ).innerText =
                weightDifference.toFixed(2)
                + ' KG';


            document.getElementById(
                'value_difference'
            ).innerText =
                '৳ ' +
                valueDifference.toFixed(2);


            document.getElementById(
                'weight_adjustment'
            ).innerText =
                '৳ ' +
                weightAdjustment.toFixed(2);


            document.getElementById(
                'settlement'
            ).innerText =
                '৳ ' +
                Math.abs(
                    settlement
                ).toFixed(2);



            /*
            |--------------------------------------------------------------------------
            | SETTLEMENT MESSAGE
            |--------------------------------------------------------------------------
            */

            const message =
                document.getElementById(
                    'settlement_message'
                );


            if (settlement > 0) {

                message.className =
                    'alert alert-danger';

                message.innerHTML =
                    '<strong>Customer Pay:</strong> ৳ ' +
                    Math.abs(
                        settlement
                    ).toFixed(2);

            }

            else if (settlement < 0) {

                message.className =
                    'alert alert-success';

                message.innerHTML =
                    '<strong>Customer Receive:</strong> ৳ ' +
                    Math.abs(
                        settlement
                    ).toFixed(2);

            }

            else {

                message.className =
                    'alert alert-secondary';

                message.innerHTML =
                    '<strong>No Adjustment</strong>';

            }

        }



        /* ============================================================
           DOCUMENT READY
        ============================================================ */

        $(document).ready(function () {


            /* ========================================================
               CUSTOMER AUTOCOMPLETE
            ======================================================== */

            $("#customer_search").autocomplete({

                source: function (request, response) {

                    $.ajax({

                        url:
                            "{{ route('customer.autocomplete') }}",

                        type: "GET",

                        dataType: "json",

                        data: {
                            term: request.term
                        },

                        success: function (data) {

                            response(data);

                        },

                        error: function (xhr) {

                            console.log(
                                "Customer autocomplete error:",
                                xhr.responseText
                            );

                            response([]);

                        }

                    });

                },

                minLength: 1,

                select: function (event, ui) {

                    $("#customer_id")
                        .val(ui.item.id);


                    $("#customer_search")
                        .val(ui.item.value);


                    $("#customer_info").html(

                        "Mobile: " +
                        (ui.item.mobile ?? '') +

                        " | Address: " +
                        (ui.item.address ?? '')

                    );


                    return false;

                },

                change: function (event, ui) {

                    if (!ui.item) {

                        $("#customer_id")
                            .val("");

                        $("#customer_info")
                            .html("");

                    }

                }

            });



            /* ========================================================
               OLD PRODUCT CHANGE
            ======================================================== */

            $('#old_product_id').on(
                'change',
                function () {

                    const option =
                        this.options[
                            this.selectedIndex
                        ];


                    $('#old_price').val(
                        option.dataset.price || 0
                    );


                    updateOldProductInputMode();

                }
            );



            /* ========================================================
               EXCHANGE PRODUCT CHANGE
            ======================================================== */

            $('#exchange_product_id').on(
                'change',
                function () {

                    const option =
                        this.options[
                            this.selectedIndex
                        ];


                    $('#exchange_price').val(
                        option.dataset.price || 0
                    );


                    updateExchangeProductInputMode();

                }
            );



            /* ========================================================
               INPUT EVENTS
            ======================================================== */

            [

                'old_qty',

                'exchange_qty',

                'old_weight_kg',

                'exchange_weight_kg',

                'weight_rate',

                'exchange_fee'

            ].forEach(function (id) {

                const element =
                    document.getElementById(id);


                if (!element) {
                    return;
                }


                element.addEventListener(
                    'input',
                    calculateExchange
                );

            });


            /* ========================================================
               INITIALIZE
            ======================================================== */

            updateOldProductInputMode();

            updateExchangeProductInputMode();

            calculateExchange();

        });



        /* ============================================================
           CUSTOMER POPUP
        ============================================================ */

        function openCustomerWindow()
        {

            const width = 900;

            const height = 700;


            const left =
                window.screenX +
                (
                    window.outerWidth -
                    width
                ) / 2;


            const top =
                window.screenY +
                (
                    window.outerHeight -
                    height
                ) / 2;


            window.open(

                "{{ route('customer.newcustomerform') }}",

                "CustomerWindow",

                `width=${width},
                 height=${height},
                 left=${left},
                 top=${top},
                 scrollbars=yes,
                 resizable=yes`

            );

        }



        /* ============================================================
           CUSTOMER RETURN FROM POPUP
        ============================================================ */

        function selectCustomer(
            id,
            name,
            mobile,
            address
        )
        {

            $('#customer_search')
                .val(name);


            $('#customer_id')
                .val(id);


            $('#customer_info')
                .text(

                    'Mobile: ' +
                    (mobile ?? '') +

                    ' | Address: ' +
                    (address ?? '')

                );

        }

    </script>


</x-app-layout>