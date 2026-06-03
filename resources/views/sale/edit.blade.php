<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Sale') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <form action="{{ route('sale.update', $sale->id) }}" method="post">
                    @csrf
                    @method('PUT')

                    <!-- Customer & Invoice -->
                    <div class="form-row mb-3">
                        <div class="form-group col-md-2">
                            <label>Invoice No<span class="text-danger">*</span></label>
                            <input type="text" name="invoice_no" class="form-control" value="{{ $sale->invoice_no }}" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Customer Name</label>
                            <input type="text" id="customer_search" class="form-control" placeholder="Search customer..." autocomplete="off" required
                                value="{{ $sale->customer->customer_name ?? '' }}">
                            <input type="hidden" name="customer_id" id="customer_id" value="{{ $sale->customer_id }}">
                            <small class="text-muted" id="customer_info">
                                @if($sale->customer)
                                    Mobile: {{ $sale->customer->mobile ?? '' }} | Address: {{ $sale->customer->address ?? '' }}
                                @endif
                            </small>
                        </div>
                        <div class="form-group col-md-2">
                            <label>Sale Date</label> 
                            <input type="date" name="sale_date" class="form-control" value="{{ \Carbon\Carbon::parse($sale->sale_date)->format('Y-m-d') }}" required>
                        </div>
                    </div>

                    <hr>
                    <!-- New Products -->
                    <h4 class="text-center">New Products</h4>
                    <table class="table table-bordered" id="product_table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Unit</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                                <th>Narration</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->details as $index => $item)
                            <tr>
                                <td>
                                    <input type="text" name="products[{{ $index }}][product_name]" class="form-control product_search" placeholder="Search product" value="{{ $item->product->product_name ?? '' }}">
                                    <input type="hidden" name="products[{{ $index }}][product_id]" class="product_id" value="{{ $item->product_id }}">
                                    <small class="text-muted stock_info">Stock: {{ $item->product->stock ?? 0 }}</small>
                                </td>
                                <td><input type="number" name="products[{{ $index }}][qty]" class="form-control qty" value="{{ $item->qty }}" step="any"></td>
                                <td><input type="text" name="products[{{ $index }}][unit]" class="form-control unit" value="{{ $item->unit }}" required></td>
                                <td><input type="number" step="any" min="0" name="products[{{ $index }}][price]" class="form-control price" value="{{ $item->price }}"></td>
                                <td><input type="text" class="form-control row_total" value="{{ $item->total }}" readonly></td>
                                <td><input type="text" name="products[{{ $index }}][narration]" class="form-control narration" value="{{ $item->narration }}"></td>
                                <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-success btn-sm" onclick="addRow()">Add New Product</button>

                    <hr>
                    <!-- Old Products -->
                    <h4 class="text-center">Old Products</h4>
                    <table class="table table-bordered" id="old_product_table">
                        <tbody>
                            @php $oldRowIndex = 0; @endphp
                            @foreach($sale->oldProductPurchases as $purchase)
                                @foreach($purchase->details as $i)
                                <tr>
                                    <td>
                                        <select name="old_products[{{ $oldRowIndex }}][product_name]" class="form-control old_product_name" required>
                                            <option value="">-- Select Product Name --</option>
                                            @foreach($old_products as $old_product)
                                                <option value="{{ $old_product->product_name }}"
                                                    {{ (isset($i->oldProduct) && $i->oldProduct->product_name == $old_product->product_name) ? 'selected' : '' }}>
                                                    {{ $old_product->product_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" name="old_products[{{ $oldRowIndex }}][qty]" class="form-control old_qty" value="{{ $i->qty }}" step="0.001"></td>
                                    <td>
                                        <select name="old_products[{{ $oldRowIndex }}][unit]" class="form-control old_unit">
                                            <option value="set" {{ $i->unit=='set'?'selected':'' }}>Set</option>
                                            <option value="kg" {{ $i->unit=='kg'?'selected':'' }}>KG</option>
                                            <option value="pcs" {{ $i->unit=='pcs'?'selected':'' }}>PC</option>
                                            <option value="box" {{ $i->unit=='box'?'selected':'' }}>BOX</option>
                                        </select>
                                    </td>
                                    <td><input type="number" step="any" min="0" name="old_products[{{ $oldRowIndex }}][price]" class="form-control old_price" value="{{ $i->price }}"></td>
                                    <td><input type="text" class="form-control row_total_old" readonly value="{{ number_format($i->qty * $i->price,2) }}"></td>
                                    <td><input type="text" name="old_products[{{ $oldRowIndex }}][narration]" class="form-control narration" value="{{ $i->narration }}"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
                                </tr>
                                @php $oldRowIndex++; @endphp
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-success btn-sm" onclick="addOldRow()">Add Old Product</button>

                    <hr>
                    <!-- Summary -->
                    <div class="form-row mb-3">
                        <div class="form-group col-md-4">
                            <label>Tax %</label>
                            <input type="text" class="form-control" name="tax_percentage" value="{{ $sale->tax_percentage }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Shipping</label>
                            <input type="text" class="form-control" id="shipping_amount" name="shipping_amount" value="{{ $sale->shipping_amount }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Total Amount</label>
                            <input type="text" class="form-control" id="total_amount" name="total_amount" value="{{ $sale->total_amount }}" readonly>
                        </div>
                    </div>

                    <div class="form-row mb-3">
                        <div class="form-group col-md-4">
                            <label>Discount Amount</label>
                            <input type="number" name="discount_amount" id="discount_amount" class="form-control" value="{{ $sale->discount_amount }}">
                        </div>
                        @php
                            $paidAmount = $sale->receivedPayments->sum('amount');
                            $dueAmount  = $sale->total_amount - $paidAmount;
                            $lastPayment = $sale->receivedPayments->last();
                        @endphp

                        <div class="form-group col-md-4">
                            <label>Paid Amount</label>
                            <input type="text" class="form-control" id="paid_amount" name="paid_amount" value="{{ $paidAmount }}">
                        </div>

                        <div class="form-group col-md-4">
                            <label>Due Amount</label>
                            <input type="text" class="form-control" id="due_amount" name="due_amount" readonly value="{{ $dueAmount }}">
                        </div>
                    </div>

                    <div class="form-row mb-3">
                        <div class="form-group col-md-4">
                            <label>Payment Method</label>
                            <select name="payment_method" class="form-control">
                                <option value="">-- Select Method --</option>
                                <option value="cash" {{ $lastPayment && $lastPayment->received_method=='cash'?'selected':'' }}> Cash </option>
                                <option value="bank" {{ $lastPayment && $lastPayment->received_method=='bank'?'selected':'' }}> Bank </option>
                                <option value="mobile" {{ $lastPayment && $lastPayment->received_method=='mobile'?'selected':'' }}> Mobile Banking </option>
                                <option value="cheque" {{ $lastPayment && $lastPayment->received_method=='cheque'?'selected':'' }}> Cheque </option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Guaranty Period / Ref No</label>
                            <input type="text" class="form-control" id="tracking_no" name="ref_no" value="{{ $sale->ref_no }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Battery Body Date</label>
                            <input type="date" class="form-control" id="body_date" name="body_date"
                                value="{{ $sale->body_date ? \Carbon\Carbon::parse($sale->body_date)->format('Y-m-d') : '' }}">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Sale</button> | 
                    <button type="button" class="btn btn-secondary" onclick="history.back()">← Back</button>
                </form>
            </div>
        </div>
        <br>
        <span>@include('layouts.footer')</span>
    </div>

<script>
let rowIndex = {{ $sale->details->count() }};
let oldRowIndex = {{ $oldRowIndex }};

// Old Products list for JS
let oldProductsList = @json($old_products->pluck('product_name'));

// ------------------------- Add / Remove New Product -------------------------
function addRow() {
    let tbody = document.querySelector('#product_table tbody');
    let row = tbody.insertRow();
    row.innerHTML = `
        <td>
            <input type="text" name="products[${rowIndex}][product_name]" class="form-control product_search" placeholder="Search product">
            <input type="hidden" name="products[${rowIndex}][product_id]" class="product_id">
            <small class="text-muted stock_info"></small>
        </td>
        <td><input type="number" name="products[${rowIndex}][qty]" class="form-control qty" value="1" min="1" step="any"></td>
        <td>
            <select name="products[${rowIndex}][unit]" class="form-control unit">
                <option value="set">Set</option>
                <option value="kg">kg</option>
                <option value="pcs">pcs</option>
                <option value="box">box</option>
                <option value="dram">Dram</option>
            </select>
        </td>
        <td><input type="number" step="any" name="products[${rowIndex}][price]" class="form-control price" value="0"></td>
        <td><input type="text" class="form-control row_total" readonly value="0.00"></td>
        <td><input type="text" name="products[${rowIndex}][narration]" class="form-control narration"></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
    `;
    rowIndex++;
    updateRowTotal(row);
}

// ------------------------- Add / Remove Old Product -------------------------
function addOldRow() {
    let tbody = document.querySelector('#old_product_table tbody');
    let row = tbody.insertRow();

    let options = '<option value="">-- Select Product Name --</option>';
    oldProductsList.forEach(p => {
        options += `<option value="${p}">${p}</option>`;
    });
    row.innerHTML = `
        <td><select name="old_products[${oldRowIndex}][product_name]" class="form-control old_product_name" required>${options}</select></td>
        <td><input type="number" name="old_products[${oldRowIndex}][qty]" class="form-control old_qty" value="0" step="any"></td>
        <td>
            <select name="old_products[${oldRowIndex}][unit]" class="form-control old_unit">
                <option value="set">Set</option>
                <option value="kg">KG</option>
                <option value="pcs">PC</option>
                <option value="box">BOX</option>
                <option value="dram">Dram</option>
            </select>
        </td>
        <td><input type="number" step="any" min="0" name="old_products[${oldRowIndex}][price]" class="form-control old_price" value="0"></td>
        <td><input type="text" class="form-control row_total_old" readonly value="0.00"></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
    `;
    oldRowIndex++;
    updateOldRowTotal(row);
}

// ------------------------- Remove Row -------------------------
function removeRow(btn) {
    btn.closest('tr').remove();
    calculateGrandTotal();
}

// ------------------------- Update individual row totals -------------------------
function updateRowTotal(row) {
    let qty = parseFloat(row.querySelector('.qty')?.value) || 0;
    let price = parseFloat(row.querySelector('.price')?.value) || 0;
    if(row.querySelector('.row_total')) row.querySelector('.row_total').value = (qty*price).toFixed(2);
    let oq = parseFloat(row.querySelector('.old_qty')?.value) || 0;
    let op = parseFloat(row.querySelector('.old_price')?.value) || 0;
    if(row.querySelector('.row_total_old')) row.querySelector('.row_total_old').value = (oq*op).toFixed(2);
}

// ------------------------- Grand Total Calculation -------------------------
function calculateGrandTotal() {
    let newTotal = 0;
    document.querySelectorAll('.row_total').forEach(el => newTotal += parseFloat(el.value) || 0);
    let oldTotal = 0;
    document.querySelectorAll('.row_total_old').forEach(el => oldTotal += parseFloat(el.value) || 0);
    let shipping = parseFloat(document.getElementById('shipping_amount').value) || 0;
    let discount = parseFloat(document.getElementById('discount_amount').value) || 0;
    // Grand Total = New Products - Old Products + Shipping - Discount
    let total = newTotal - oldTotal + shipping - discount;
    document.getElementById('total_amount').value = total.toFixed(2);
    calculateDue();
}

// ------------------------- Due Amount Calculation -------------------------
function calculateDue() {
    let total = parseFloat(document.getElementById('total_amount').value) || 0;
    let paid  = parseFloat(document.getElementById('paid_amount').value) || 0;
    document.getElementById('due_amount').value = (total - paid).toFixed(2);
}

// ------------------------- Event Listeners -------------------------
document.addEventListener('input', function(e){
    let row = e.target.closest('tr');
    if(!row) return;
    if(e.target.classList.contains('qty') || e.target.classList.contains('price')) {
        updateRowTotal(row);
        calculateGrandTotal();
    }
    if(e.target.classList.contains('old_qty') || e.target.classList.contains('old_price')) {
        updateRowTotal(row);
        calculateGrandTotal();
    }
    if(['shipping_amount','discount_amount'].includes(e.target.id)) calculateGrandTotal();
});

document.getElementById('paid_amount').addEventListener('input', calculateDue);
// ------------------------- Initialize on page load -------------------------
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('#old_product_table tbody tr').forEach(updateRowTotal);
    document.querySelectorAll('#product_table tbody tr').forEach(updateRowTotal);
    calculateGrandTotal();
});

// ------------------------- jQuery Autocomplete for Products -------------------------
$(document).on('focus', '.product_search', function () {
    $(this).autocomplete({
        source: '/product-autocomplete',
        minLength: 1,
        select: function (event, ui) {
            let row = $(this).closest('tr');
            row.find('.product_id').val(ui.item.id);
            row.find('.stock_info').text('Stock: ' + ui.item.stock);
            row.find('.price').val(ui.item.purchase_price ?? 0);
            $(this).val(ui.item.value);
            row.find('.qty').trigger('input');
            return false;
        }
    });
});

// ------------------------- jQuery Autocomplete for Customer -------------------------
$('#customer_search').autocomplete({
    source: "/customer-autocomplete",
    minLength: 1,
    select: function(event, ui){
        $('#customer_search').val(ui.item.value);
        $('#customer_id').val(ui.item.id);
        $('#customer_info').text('Mobile: ' + (ui.item.mobile??'') + ' | Address: ' + (ui.item.address??''));
        return false;
    }
});

</script>
</x-app-layout>