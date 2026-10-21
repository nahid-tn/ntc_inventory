<x-app-layout>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Purchase Order Create
    </h2>
</x-slot>

<div class="py-6">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

<form method="POST" action="{{ route('purchase-orders.store') }}">
@csrf

{{-- ================= SUPPLIER & INFO ================= --}}
<div class="row mb-3">

    <div class="col-md-4">
        <label>Supplier</label>
        <input type="text" id="supplier_search" class="form-control" placeholder="Search supplier">
        <input type="hidden" name="supplier_id" id="supplier_id">
        <small id="supplier_info" class="text-muted"></small>
    </div>

    <div class="col-md-4">
        <label>Order Date</label>
        <input type="date" name="order_date" class="form-control" required>
    </div>

    <div class="col-md-4">
        <label>Expected Date</label>
        <input type="date" name="expected_date" class="form-control">
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
<tr>
    <td>
        <input type="text" name="products[0][name]" class="form-control product_search" placeholder="Search product">
        <input type="hidden" name="products[0][product_id]" class="product_id">
        <small class="text-muted stock_info"></small>
    </td>

    <td>
        <input type="number" name="products[0][qty]" class="form-control qty" value="1">
    </td>

    <td>
        <select name="products[0][unit]" class="form-control">
            <option value="">Select Unit</option>
            @foreach($units as $unit)
                <option value="{{ $unit->ShortName }}">
                    {{ $unit->ShortName }}
                </option>
            @endforeach
        </select>
    </td>

    <td>
        <input type="number" name="products[0][price]" class="form-control price" value="0">
    </td>

    <td>
        <input type="text" class="form-control row_total" readonly>
    </td>

    <td>
        <input type="text" name="products[0][remarks]" class="form-control">
    </td>

    <td>
        <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">X</button>
    </td>
</tr>
</tbody>

</table>

<button type="button" class="btn btn-success btn-sm" onclick="addRow()">
Add More
</button>

<hr>

{{-- ================= SUMMARY ================= --}}
<div class="row">

    <div class="col-md-3">
        <label>Tax</label>
        <input type="number" name="tax" class="form-control" value="0">
    </div>

    <div class="col-md-3">
        <label>Shipping</label>
        <input type="number" name="shipping" class="form-control" value="0">
    </div>

    <div class="col-md-3">
        <label>Discount</label>
        <input type="number" name="discount" class="form-control" value="0">
    </div>

    <div class="col-md-3">
        <label>Total</label>
        <input type="text" name="total_amount" id="total_amount" class="form-control" readonly>
    </div>

</div>

<hr>

<button class="btn btn-primary"> Save Purchase Order </button>
<button type="button" class="btn btn-secondary" onclick="history.back()">← Back</button>

</form>

</div>
</div>

<script>
let rowIndex = 1;

/* ================= ADD ROW ================= */
function addRow() {
    let tbody = document.querySelector("#po_table tbody");

    let row = tbody.insertRow();

    row.innerHTML = `
    <td>
        <input type="text" name="products[${rowIndex}][name]" class="form-control product_search" placeholder="Search product">
        <input type="hidden" name="products[${rowIndex}][product_id]" class="product_id">
        <small class="text-muted stock_info"></small>
    </td>
    <td><input type="number" name="products[${rowIndex}][qty]" class="form-control qty" value="1"></td>
    <td>
        <select name="products[${rowIndex}][unit]" class="form-control">
            @foreach($units as $unit)
                <option value="{{ $unit->ShortName }}">{{ $unit->ShortName }}</option>
            @endforeach
        </select>
    </td>
    <td><input type="number" name="products[${rowIndex}][price]" class="form-control price" value="0"></td>
    <td><input type="text" class="form-control row_total" readonly></td>
    <td><input type="text" name="products[${rowIndex}][remarks]" class="form-control"></td>
    <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">X</button></td>
    `;

    rowIndex++;
}

/* ================= REMOVE ROW ================= */
function removeRow(btn) {
    btn.closest("tr").remove();
    calculateTotal();
}

/* ================= CALCULATION ================= */
document.addEventListener("input", function(e) {

    let row = e.target.closest("tr");

    if (row && (e.target.classList.contains("qty") || e.target.classList.contains("price"))) {

        let qty = parseFloat(row.querySelector(".qty").value) || 0;
        let price = parseFloat(row.querySelector(".price").value) || 0;

        row.querySelector(".row_total").value = (qty * price).toFixed(2);

        calculateTotal();
    }
});

function calculateTotal() {
    let total = 0;

    document.querySelectorAll(".row_total").forEach(el => {
        total += parseFloat(el.value) || 0;
    });

    document.getElementById("total_amount").value = total.toFixed(2);
}

/* ================= SUPPLIER AUTOCOMPLETE ================= */
$(document).ready(function() {

    $('#supplier_search').autocomplete({
        source: '/supplier-autocomplete',
        minLength: 1,
        select: function(event, ui) {
            $('#supplier_search').val(ui.item.value);
            $('#supplier_id').val(ui.item.id);
            $('#supplier_info').text('Mobile: ' + (ui.item.mobile ?? 'N/A'));
            return false;
        }
    });

    /* ================= PRODUCT AUTOCOMPLETE ================= */
    $(document).on('focus', '.product_search', function () {

        $(this).autocomplete({
            source: '/product-autocomplete',
            minLength: 1,

            select: function(event, ui) {

                let row = $(this).closest('tr');

                row.find('.product_id').val(ui.item.id);
                $(this).val(ui.item.value);

                row.find('.stock_info').text('Stock: ' + (ui.item.stock ?? 0));

                /* ================= LAST PURCHASE PRICE ================= */
                fetch(`/product-last-price/${ui.item.id}`)
                    .then(res => res.json())
                    .then(data => {
                        row.find('.price').val(data.price ?? 0);
                        calculateTotal();
                    });

                return false;
            }
        });

    });

});
</script>

</x-app-layout>