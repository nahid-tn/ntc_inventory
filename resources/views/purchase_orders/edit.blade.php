{{-- resources/views/purchase_orders/edit.blade.php --}}

<x-app-layout>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Edit Purchase Order
    </h2>
</x-slot>

<div class="py-6">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

<form method="POST" action="{{ route('purchase-orders.update', $purchaseOrder->id) }}">
@csrf
@method('PUT')

{{-- ================= SUPPLIER & INFO ================= --}}
<div class="row mb-3">

    <div class="col-md-4">
        <label>Supplier</label>

        <input 
            type="text"
            id="supplier_search"
            class="form-control"
            value="{{ $purchaseOrder->supplier->supplier_name ?? '' }}"
            placeholder="Search supplier"
        >

        <input 
            type="hidden"
            name="supplier_id"
            id="supplier_id"
            value="{{ $purchaseOrder->supplier_id }}"
        >

        <small id="supplier_info" class="text-muted">
            Mobile: {{ $purchaseOrder->supplier->mobile ?? 'N/A' }}
        </small>
    </div>

    <div class="col-md-4">
        <label>Order Date</label>
        <input 
            type="date"
            name="order_date"
            class="form-control"
            value="{{ $purchaseOrder->order_date }}"
            required
        >
    </div>

    <div class="col-md-4">
        <label>Expected Date</label>
        <input 
            type="date"
            name="expected_date"
            class="form-control"
            value="{{ $purchaseOrder->expected_date }}"
        >
    </div>

</div>

<hr>

{{-- ================= PRODUCT TABLE ================= --}}
<h4 class="text-center">Products</h4>

<table class="table table-bordered" id="po_table">

<thead>
<tr>
    <th>Product</th>
    <th>Qty</th>
    <th>Unit</th>
    <th>Price</th>
    <th>Total</th>
    <th>Remarks</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

@foreach($purchaseOrder->details as $index => $item)
<tr>

    <td>
        <input 
            type="text"
            name="products[{{ $index }}][product_name]"
            class="form-control product_search"
            value="{{ $item->product->product_name ?? '' }}"
            placeholder="Search product"
        >

        <input 
            type="hidden"
            name="products[{{ $index }}][product_id]"
            class="product_id"
            value="{{ $item->product_id }}"
        >

        <small class="text-muted stock_info">
            Stock: {{ $item->product->stock ?? 0 }}
        </small>
    </td>

    <td>
        <input type="number" name="products[{{ $index }}][qty]" class="form-control qty" value="{{ $item->qty }}">
    </td>

    <td>
        <select name="products[{{ $index }}][unit]" class="form-control">
            @foreach($units as $unit)
                <option 
                    value="{{ $unit->ShortName }}"
                    {{ $item->unit == $unit->ShortName ? 'selected' : '' }}
                >
                    {{ $unit->ShortName }}
                </option>
            @endforeach
        </select>
    </td>

    <td>
        <input type="number" name="products[{{ $index }}][price]" class="form-control price" value="{{ $item->price }}">
    </td>
    <td>
        <input type="text" class="form-control row_total" value="{{ number_format($item->qty * $item->price, 2) }}" readonly>
    </td>
    <td>
        <input type="text" name="products[{{ $index }}][remarks]" class="form-control" value="{{ $item->remarks }}">
    </td>
    <td>
        <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)"> X </button>
    </td>

</tr>
@endforeach

</tbody>
</table>
    <button type="button" class="btn btn-success btn-sm" onclick="addRow()"> Add More</button>
<hr>

{{-- ================= SUMMARY ================= --}}
<div class="row">

    <div class="col-md-3">
        <label>Tax</label>
        <input type="number" name="tax" class="form-control" value="{{ $purchaseOrder->tax }}">
    </div>
    <div class="col-md-3">
        <label>Shipping</label>
        <input type="number" name="shipping" class="form-control" value="{{ $purchaseOrder->shipping }}">
    </div>
    <div class="col-md-3">
        <label>Discount</label>
        <input type="number" name="discount" class="form-control" value="{{ $purchaseOrder->discount }}">
    </div>
    <div class="col-md-3">
        <label>Total</label>
        <input name="total_amount" id="total_amount" class="form-control" value="{{ $purchaseOrder->total_amount }}" readonly>
    </div>
</div>

<hr>
    <button class="btn btn-primary"> Update Purchase Order </button>
    <button type="button" class="btn btn-secondary" onclick="history.back()">← Back</button>
</form>

</div>
</div>

<script>

let rowIndex = {{ $purchaseOrder->details->count() }};

function initAutocomplete() {

    $('.product_search').autocomplete({
        source: '/product-search',
        minLength: 1,
        select: function(event, ui) {
        let row = $(this).closest('tr');
        // product name show
        $(this).val(ui.item.label);
        // hidden product id
        row.find('.product_id').val(ui.item.id);
        // auto price
        row.find('.price').val(ui.item.price);
        // stock info
        row.find('.stock_info').text('Stock: ' + ui.item.stock);
        // calculate
        calculateRow(row);
        return false;
    }
    });
}

function addRow() {

    let tbody = document.querySelector("#po_table tbody");

    let row = `
    <tr>

        <td>
            <input type="text" name="products[${rowIndex}][product_name]" class="form-control product_search" placeholder="Search product">
            <input type="hidden" name="products[${rowIndex}][product_id]" class="product_id" value="">
            <small class="text-muted stock_info"></small>
        </td>

        <td>
            <input type="number" name="products[${rowIndex}][qty]" class="form-control qty" value="1" >
        </td>

        <td>
            <select 
                name="products[${rowIndex}][unit]" class="form-control">
                @foreach($units as $unit)
                    <option value="{{ $unit->ShortName }}">
                        {{ $unit->ShortName }}
                    </option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="number" name="products[${rowIndex}][price]" class="form-control price" value="0">
        </td>
        <td>
            <input type="text" class="form-control row_total" readonly >
        </td>
        <td>
            <input 
                type="text" name="products[${rowIndex}][remarks]" class="form-control" value="">
        </td>
        <td>
            <button  type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)"> X </button>
        </td>
    </tr>
    `;
    $('#po_table tbody').append(row);
    rowIndex++;
    initAutocomplete();
}

function removeRow(btn) {
    $(btn).closest('tr').remove();
    calculateTotal();
}

function calculateRow(row) {
    let qty = parseFloat(row.find('.qty').val()) || 0;
    let price = parseFloat(row.find('.price').val()) || 0;
    row.find('.row_total').val((qty * price).toFixed(2));
    calculateTotal();
}

$(document).on('input', '.qty, .price', function () {
    let row = $(this).closest('tr');
    calculateRow(row);
});

function calculateTotal() {
    let total = 0;
    $('.row_total').each(function () {
        total += parseFloat($(this).val()) || 0;
    });
    $('#total_amount').val(total.toFixed(2));
}

$(document).ready(function () {
    initAutocomplete();
    calculateTotal();
});

</script>

</x-app-layout>