<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Sales Replacement') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">

                <form action="{{ route('replacement.store') }}" method="POST">
                    @csrf

                    {{-- Sale Invoice Autocomplete --}}
                    <div class="form-group mb-3">
                        <label>Sale Invoice <span class="text-danger">*</span></label>
                        <input type="text" id="sale_search" name="sale_search" class="form-control">
                        <input type="hidden" name="sale_id" id="sale_id" required >
                        <small id="sale_info" class="text-muted"></small>
                    </div>

                    {{-- Old Product --}}
                    <div class="form-group mb-3">
                        <label>Sales Product <span class="text-danger">*</span></label>
                        <select name="old_product_id" id="old_product_select" class="form-control">
                            <option value="">-- Select Old Product --</option>
                        </select>
                        <small id="old_stock_info" class="text-muted"></small>
                    </div>

                    {{-- New Product --}}
                    <div class="row">

                        <div class="form-group mb-3 col-md-4">
                            <label>New Product <span class="text-danger">*</span></label>

                            <input
                                type="text"
                                name="new_product_search"
                                id="new_product_search"
                                class="form-control"
                                placeholder="Search new product..."
                                required
                            >

                            <input
                                type="hidden"
                                name="new_product_id"
                                id="new_product_id"
                            >

                            <small id="new_stock_info" class="text-muted"></small>
                        </div>

                        {{-- Qty --}}
                        <div class="form-group mb-3 col-md-2">
                            <label>Quantity <span class="text-danger">*</span></label>
                            <input
                                type="number"
                                name="qty"
                                id="qty"
                                class="form-control"
                                min="1"
                                value="1"
                                required
                            >
                        </div>

                        {{-- Unit --}}
                        <div class="form-group mb-3 col-md-2">
                            <label>Unit <span class="text-danger">*</span></label>

                            <select
                                name="unit"
                                id="unit"
                                class="form-control"
                                required
                            >
                                <option value="">-- Select Unit --</option>

                                @foreach($units as $unit)
                                    <option
                                        value="{{ $unit->name }}"
                                        {{ old('unit', $replacement->unit ?? '') == $unit->name ? 'selected' : '' }}
                                    >
                                        {{ $unit->name }} ({{ $unit->short_name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Price --}}
                        <div class="form-group mb-3 col-md-2">
                            <label>Price <span class="text-danger">*</span></label>
                            <input
                                type="number"
                                name="price"
                                id="price"
                                class="form-control"
                                min="0"
                                value="0"
                                required
                            >
                        </div>

                        {{-- Total --}}
                        <div class="form-group mb-3 col-md-2">
                            <label>Total</label>
                            <input
                                type="text"
                                id="total"
                                class="form-control"
                                readonly
                            >
                        </div>

                    </div>

                    {{-- Dates + Fee --}}
                    <div class="row">

                        <div class="form-group mb-3 col-md-4">
                            <label>Replacement Date</label>
                            <input
                                type="date"
                                name="replaceMent_date"
                                id="replaceMent_date"
                                class="form-control"
                            >
                        </div>

                        <div class="form-group mb-3 col-md-4">
                            <label>Body Date</label>
                            <input
                                type="date"
                                name="body_date"
                                id="body_date"
                                class="form-control"
                            >
                        </div>

                        <div class="form-group mb-3 col-md-4">
                            <label>Replacement Fee</label>
                            <input
                                type="number"
                                name="replaceMent_fee"
                                id="replaceMent_fee"
                                class="form-control"
                                min="0"
                                value="0"
                                required
                            >
                        </div>

                    </div>

                    {{-- Due Section --}}
                    <div class="row">

                        <div class="form-group mb-3 col-md-4">
                            <label>Received Replacement Fee</label>
                            <input
                                type="number"
                                name="received_replacement_fee"
                                id="received_replacement_fee"
                                class="form-control"
                                min="0"
                                value="0"
                                required
                            >
                        </div>

                        <div class="form-group mb-3 col-md-4">
                            <label>Due Date</label>
                            <input
                                type="date"
                                name="due_date"
                                id="due_date"
                                class="form-control"
                            >
                        </div>

                        <div class="form-group mb-3 col-md-4">
                            <label>Total Due</label>
                            <input
                                type="number"
                                name="total_due"
                                id="total_due"
                                class="form-control"
                                min="0"
                                value="0"
                                readonly
                            >
                        </div>

                    </div>

                    <button type="submit" class="btn btn-primary">
                        Create Replacement
                    </button>

                    |

                    <button
                        type="button"
                        class="btn btn-secondary"
                        onclick="history.back()"
                    >
                        ← Back
                    </button>

                </form>

            </div>
        </div>

        <br>

        <span>
            @include('layouts.footer')
        </span>
    </div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

<script>
$(document).ready(function () {

    /*
    =====================================
    Sale Invoice Autocomplete
    =====================================
    */
    $('#sale_search').autocomplete({

    source: function (request, response) {

        $.ajax({

            url: "{{ url('/sale-autocomplete') }}",

            dataType: "json",

            data: {
                term: request.term
            },

            success: function (data) {

                response($.map(data, function (item) {

                    return {

                        label:
                            "Invoice: " + item.invoice_no +
                            " | Date: " + item.sale_date +
                            " | Customer: " + item.customer_name,

                        value:
                            item.invoice_no +
                            " - " +
                            item.customer_name,

                        id: item.id,

                        invoice_no: item.invoice_no,

                        sale_date: item.sale_date,

                        customer_name: item.customer_name
                    };
                }));
            }
        });
    },

    minLength: 1,

    delay: 0,

    select: function (event, ui) {

        $('#sale_id').val(ui.item.id);

        $('#sale_search').val(
            "Invoice: " +
            ui.item.invoice_no +
            " | Customer: " +
            ui.item.customer_name
        );

        $('#sale_info').html(
            "Selected → Invoice: " +
            ui.item.invoice_no +
            " | Date: " +
            ui.item.sale_date +
            " | Customer: " +
            ui.item.customer_name
        );

        loadOldProducts(ui.item.id);

        return false;
    }
});


    /*
    =====================================
    Load Old Products
    =====================================
    */
    function loadOldProducts(sale_id) {

        if (!sale_id) return;

        $.get('/replacement/old-products/' + sale_id, function (data) {

            let html = '<option value="">-- Select Old Product --</option>';

            data.forEach(function (p) {
                html += `
                    <option
                        value="${p.id}"
                        data-stock="${p.stock}"
                        data-price="${p.price}">
                        Product: ${p.name}
                        | Qty: ${p.qty}
                        | Price: ${p.price}
                    </option>
                `;
            });

            $('#old_product_select').html(html);
            $('#old_stock_info').text('');
        });
    }


    /*
    =====================================
    Old Product Select
    =====================================
    */
    $('#old_product_select').on('change', function () {

        let stock = $(this).find(':selected').data('stock') || 0;
        let price = $(this).find(':selected').data('price') || 0;

        $('#old_stock_info').text(
            'Stock: ' + stock + ' | Price: ' + price
        );

        $('#price').val(price);

        calculateAll();
    });


    /*
    =====================================
    New Product Autocomplete
    =====================================
    */
    $('#new_product_search').autocomplete({
        source: '/product-autocomplete',
        minLength: 1,

        select: function (event, ui) {

            $('#new_product_id').val(ui.item.id);

            $('#new_stock_info').html(
                'Stock: ' + ui.item.stock +
                ' | Purchase Price: ' + ui.item.purchase_price +
                ' | Sale Price: ' + ui.item.price
            );

            $('#price').val(ui.item.price);

            $(this).val(ui.item.value);

            calculateAll();

            return false;
        }
    });


    /*
    =====================================
    Main Calculation
    =====================================
    */
    function calculateAll() {

        let qty = parseFloat($('#qty').val()) || 0;
        let price = parseFloat($('#price').val()) || 0;

        let replacementFee =
            parseFloat($('#replaceMent_fee').val()) || 0;

        let receivedFee =
            parseFloat($('#received_replacement_fee').val()) || 0;

        /*
        Product Total
        */
        let total = qty * price;

        $('#total').val(total.toFixed(2));

        /*
        Total Due
        */
        let totalDue = replacementFee - receivedFee;

        if (totalDue < 0) {
            totalDue = 0;
        }

        $('#total_due').val(totalDue.toFixed(2));
    }


    /*
    =====================================
    Events
    =====================================
    */
    $('#qty, #price').on('input', function () {
        calculateAll();
    });

    $('#replaceMent_fee, #received_replacement_fee').on('input', function () {
        calculateAll();
    });


    /*
    =====================================
    Page Load
    =====================================
    */
    calculateAll();

});
</script>
</x-app-layout>