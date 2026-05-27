<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Sale') }}
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
                <form action="{{ route('sale.store') }}" method="post">
                    @csrf

                    <!-- Customer & Invoice Info -->
                    <div class="form-row mb-3">
                        <div class="form-group col-md-2">
                            <label>Invoice No</label>
                            <input type="text" name="invoice_no" class="form-control" placeholder="Leave blank for auto">
                            <small class="text-muted">If empty, invoice will be auto-generated</small>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Sale Date</label>
                            <input type="date" name="sale_date" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Customer Name</label>
                            <input type="text" id="customer_search" class="form-control" placeholder="Search customer..." autocomplete="off" required>
                            <input type="hidden" name="customer_id" id="customer_id">
                            <small class="text-muted" id="customer_info"></small>
                        </div>
                    </div>

                    <hr>
                    <!-- New Products -->
                    <h4 class="text-center">New Products</h4>
                    <table class="table table-bordered" id="new_product_table">
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
                            <tr>
                                <td>
                                    <input type="text" name="products[0][product_name]" class="form-control product_search" placeholder="Search product">
                                    <input type="hidden" name="products[0][product_id]" class="product_id">
                                    <small class="text-muted stock_info"></small>
                                </td>
                                <td><input type="number" name="products[0][qty]" class="form-control qty" value="1" step="any"></td>
                                <!-- <td>
                                    <select name="products[0][unit]" class="form-control unit" required>
                                        <option value="">-- Select Unit --</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->ShortName }}">
                                                {{ $unit->ShortName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td> -->
                                <td>
                                    <select name="products[0][unit]" class="form-control" required>
                                        <option value="">-- Select Unit --</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->ShortName }}">
                                                {{ $unit->ShortName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" step="any" name="products[0][price]" class="form-control price" value="0"></td>
                                <td><input type="text" class="form-control row_total" readonly></td>
                                <td><input type="text" class="form-control narration" placeholder="Product Narration"></td>
                                <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-success btn-sm" onclick="addRow()">Add More</button>

                    <hr>
                    <!-- Old Products -->
                    <h4 class="text-center">Old Products</h4>
                    <table class="table table-bordered" id="old_product_table">
                        <tbody>
                            <tr>
                                <td>
                                    <select name="old_products[0][product_name]" class="form-control" required>
                                        <option value="">-- Select Product Name --</option>
                                        @foreach($old_products as $old_product)
                                            <option value="{{ $old_product->product_name }}">
                                                {{ $old_product->product_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" name="old_products[0][qty]" class="form-control old_qty" step="any" value="0"></td>
                                <td>
                                    <select name="old_products[0][unit]" class="form-control" required>
                                        <option value="">-- Select Unit --</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->ShortName }}">
                                                {{ $unit->ShortName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" step="any" name="old_products[0][price]" class="form-control old_price" value="0"></td>
                                <td><input type="text" class="form-control row_total_old" readonly></td>
                                <td><input type="text" class="form-control narration"></td>
                                <td> <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-success btn-sm" onclick="addOldRow()">Add More</button>

                    <!-- Summary & Payment -->
                    <div class="form-row mb-3">
                        <div class="form-group col-md-4">
                            <label>Tax %</label>
                            <input type="number" class="form-control" name="tax_percentage" value="0">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Shipping</label>
                            <input type="number" class="form-control" id="shipping_amount" name="shipping_amount" value="0">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Total Amount</label>
                            <input type="text" class="form-control" id="total_amount" name="total_amount" readonly>
                        </div>
                    </div>

                    <div class="form-row mb-3">
                        <div class="form-group col-md-4">
                            <label>Paid Amount</label>
                            <input type="number" class="form-control" id="paid_amount" name="paid_amount" value="0">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Discount</label>
                            <input type="number" class="form-control" id="discount_amount" name="discount_amount" value="0">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Due Amount</label>
                            <input type="text" class="form-control" id="due_amount" name="due_amount" readonly>
                        </div>
                    </div>    
                        <div class="form-row mb-3">
                            <div class="form-group col-md-4">
                                <label>Payment Method</label>
                                <select name="payment_method" class="form-control">
                                    <option value="">-- Select Method --</option>
                                    <option value="cash">Cash</option>
                                    <option value="bank">Bank</option>
                                    <option value="mobile">Mobile Banking</option>
                                    <option value="cheque">Cheque</option>
                                </select>
                            </div>

                            <div class="form-group col-md-4">
                                <label>Guaranty Date</label>
                                <input type="text" class="form-control" id="ref_no" name="ref_no" placeholder="If any Guaranty Date">
                            </div>

                            <div class="form-group col-md-4">
                                <label>Body Date</label>
                                <input type="date" class="form-control" id="body_date" name="body_date" placeholder="If any Guaranty Date">
                            </div>
                        </div>

                
                    <button type="submit" class="btn btn-primary">Submit Sale</button>
                    <button type="button" class="btn btn-secondary" onclick="history.back()">← Back</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
let rowIndex = 1;

function addRow() {
    let tbody = document.querySelector('#new_product_table tbody');
    let row = tbody.insertRow();
    row.innerHTML = `
        <td>
            <input type="text" name="products[${rowIndex}][product_name]" class="form-control product_search" placeholder="Search product">
            <input type="hidden" name="products[${rowIndex}][product_id]" class="product_id">
            <small class="text-muted stock_info"></small>
        </td>
        <td><input type="number" name="products[${rowIndex}][qty]" class="form-control qty" value="1" min="0" step="any"></td>

        <td>
            <select name="products[${rowIndex}][unit]" class="form-control unit" required>
                <option value="">-- Select Unit --</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->ShortName }}">
                        {{ $unit->ShortName }}
                    </option>
                @endforeach
            </select>
        </td>
        <td><input type="number" step="any" name="products[${rowIndex}][price]" class="form-control price" value="0"></td>
        <td><input type="text" class="form-control row_total" readonly></td>
        <td><input type="text" class="form-control narration" placeholder="Product Narration"></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
    `;
    rowIndex++;
    calculateGrandTotal();
}

let oldRowIndex = 1;

function addOldRow() {
    let tbody = document.querySelector('#old_product_table tbody');

    let row = tbody.insertRow();

    row.innerHTML = `
        <td>
            <select name="old_products[${oldRowIndex}][product_name]" class="form-control" required>
                <option value="">-- Select Product Name --</option>
                @foreach($old_products as $old_product)
                    <option value="{{ $old_product->product_name }}">
                        {{ $old_product->product_name }}
                    </option>
                @endforeach
            </select>
        </td>

        <td><input type="number" name="old_products[${oldRowIndex}][qty]" class="form-control old_qty" step="any"></td>

        <td>
            <select name="old_products[${oldRowIndex}][unit]" class="form-control" required>
                <option value="">-- Select Unit --</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->ShortName }}">
                        {{ $unit->ShortName }}
                    </option>
                @endforeach
            </select>
        </td>

        <td><input type="number" step="any" name="old_products[${oldRowIndex}][price]" class="form-control old_price" value="0"></td>

        <td><input type="text" class="form-control row_total_old" readonly></td>

        <td><input type="text" class="form-control narration"></td>

        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button>
        </td>
    `;

    oldRowIndex++;

    calculateGrandTotal();
}

function removeRow(btn) {
    btn.closest('tr').remove();
    calculateGrandTotal();
}

// Calculate totals
function calculateGrandTotal() {
    let subtotal = 0;
    document.querySelectorAll('.row_total').forEach(el => subtotal += parseFloat(el.value) || 0);

    let old_total = 0;
    document.querySelectorAll('.row_total_old').forEach(el => old_total += parseFloat(el.value) || 0);

    let taxPercent = parseFloat(document.querySelector('[name="tax_percentage"]').value) || 0;
    let taxAmount = (subtotal * taxPercent) / 100;

    let shipping = parseFloat(document.getElementById('shipping_amount').value) || 0;
    // let discount = parseFloat(document.getElementById('discount_amount').value) || 0;

    let total = subtotal + taxAmount + shipping - old_total;
    document.getElementById('total_amount').value = total.toFixed(2);

    calculateDue();
}

function calculateDue() {
    let total = parseFloat(document.getElementById('total_amount').value) || 0;
    let paid  = parseFloat(document.getElementById('paid_amount').value) || 0;
    let discount = parseFloat(document.getElementById('discount_amount').value) || 0;
    let due = total - paid - discount;
    document.getElementById('due_amount').value = due.toFixed(2);
}

// Input listeners
document.addEventListener('input', function(e) {
    let row = e.target.closest('tr');

    if (e.target.classList.contains('qty') || e.target.classList.contains('price')) {
        let qty = parseFloat(row.querySelector('.qty').value) || 0;
        let price = parseFloat(row.querySelector('.price').value) || 0;
        row.querySelector('.row_total').value = (qty * price).toFixed(2);
        calculateGrandTotal();
    }

    if (e.target.classList.contains('old_qty') || e.target.classList.contains('old_price')) {
        let o_qty = parseFloat(row.querySelector('.old_qty').value) || 0;
        let o_price = parseFloat(row.querySelector('.old_price').value) || 0;
        row.querySelector('.row_total_old').value = (o_qty * o_price).toFixed(2);
        calculateGrandTotal();
    }

    if (['paid_amount', 'shipping_amount', 'discount_amount'].includes(e.target.id)) {
        calculateGrandTotal();
    }
});

// Customer autocomplete
$(document).ready(function () {
    $('#customer_search').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "/customer-autocomplete",
                dataType: "json",
                data: { term: request.term },
                success: function(data) {
                    response($.map(data, function(item){
                        return {
                            label: item.value + ' | Mobile: ' + (item.mobile ?? ''),
                            value: item.value,
                            id: item.id,
                            mobile: item.mobile,
                            address: item.address
                        };
                    }));
                }
            });
        },
        minLength: 1,
        select: function(event, ui) {
            $('#customer_search').val(ui.item.value);
            $('#customer_id').val(ui.item.id);
            $('#customer_info').text('Mobile: ' + (ui.item.mobile ?? '') + ' | Address: ' + (ui.item.address ?? ''));
            return false;
        }
    });

    $('#customer_search').on('blur', function() {
        if (!$('#customer_id').val()) {
            $(this).val('');
            $('#customer_info').text('');
        }
    });

        // Product autocomplete
        $(document).on('focus', '.product_search', function () {
            $(this).autocomplete({
                source: '/product-autocomplete',
                minLength: 1,
                select: function (event, ui) {
                    let row = $(this).closest('tr');
                    row.find('.product_id').val(ui.item.id);
                    row.find('.stock_info').html('Stock: ' + ui.item.stock + ' | Purchase: ' + ui.item.purchase_price);
                    row.find('.price').val(ui.item.purchase_price);
                    $(this).val(ui.item.value);
                    calculateGrandTotal();
                    return false;
                }
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const invoiceInput = document.querySelector('[name="invoice_no"]');

        invoiceInput.addEventListener('blur', function () {
            if (!this.value.trim()) {
                this.placeholder = 'Auto: INV-XXXXXX';
            }
        });
    });

</script>