<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Purchase') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Success message --}}
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    {{-- Validation errors --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('purchase.create') }}" method="post">
                        @csrf
                        {{-- Invoice & Date --}}
                        <div class="row">
                        
                            <div class="form-group col-md-3">
                                <label>Invoice No</label>
                                <input type="text" name="invoice_no" class="form-control" placeholder="Leave blank for auto">
                                <small class="text-muted">If empty, invoice will be auto-generated</small>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Purchase Date<span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="date" required>
                            </div>
                        </div>

                        {{-- Supplier --}}
                        <div class="form-group mb-3">
                            <label>Supplier<span class="text-danger">*</span></label>
                            <input type="text" id="supplier_search" class="form-control" placeholder="Search Supplier...">
                            <input type="hidden" name="supplier_id" id="supplier_id">
                            <small id="supplier_info"></small>
                        </div>
                        <hr>
                        <h3 class="text-center mb-3">Product Details</h3>
                        {{-- Product table --}}
                        <table class="table table-bordered" id="product_table">
                            <thead>
                                <tr class="text-center">
                                    <th width="40%">Product</th>
                                    <th width="15%">Qty</th>
                                    <th width="10%">Unit</th>
                                    <th width="15%">Unit Price</th>
                                    <th width="15%">Total</th>
                                    <th width="15%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <input type="text" name="products[0][name]" class="form-control product_search" placeholder="Search product">
                                        <input type="hidden" name="products[0][product_id]" class="product_id">
                                        <small class="text-muted stock_info"></small>
                                    </td>
                                    <td><input type="number" name="products[0][qty]" class="form-control qty" value="1" step="0.001"></td>
                                    <td>
                                        <select name="products[0][unit]" class="form-control unit" required>
                                            <option value="">-- Select Unit --</option>
                                            @foreach($units as $unit)
                                                <option value="{{ $unit->ShortName }}">
                                                    {{ $unit->ShortName }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="number" name="products[0][price]" class="form-control price" value="0" step="0.01"></td>
                                    <td><input type="text" class="form-control row_total" value="0" readonly></td>
                                    <td>   
                                        <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                            <button type="button" class="btn btn-success" onclick="addRow()"> Add More</button>

                        {{-- Tax, Shipping, Total --}}
                        <div class="form-row mb-3">
                            <div class="form-group col-md-4">
                                <label>Tax %</label>
                                <input type="text" class="form-control" name="tax_percentage" value="0">
                            </div>
                            <div class="form-group col-md-4">
                                <label>Tax Amount</label>
                                <input type="text" class="form-control" name="tax_amount" id="tax_amount" value="0" readonly>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Shipping</label>
                                <input type="text" class="form-control" name="shipping_amount" id="shipping_amount" value="0">
                            </div>
                        </div>
                        <div class="form-row mb-3">
                            <div class="form-group col-md-4">
                                <label>Total Amount</label>
                                <input type="text" class="form-control" name="total_amount" id="total_amount" value="0" readonly>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Paid Amount<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="paid_amount" id="paid_amount" value="0" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Due Amount</label>
                                <input type="text" class="form-control" name="due_amount" id="due_amount" value="0" readonly>
                            </div>
                        </div>
                        {{-- Payment Method --}}
                        <div class="form-group mb-3">
                            <label>Payment Method<span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-control" required>
                                <option value="">-- Select Method --</option>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank</option>
                                <option value="mobile">Mobile Banking</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Purchase</button> | <button type="button" class="btn btn-secondary" onclick="history.back()"> ← Back </button>
                    </form>
                </div>
            </div>
        </div>
        <br>
        <span> @include('layouts.footer')</span>
    </div>
</x-app-layout>

{{-- JS --}}
<script>
let rowIndex = 1;

// Add new row
function addRow() {
    let tbody = document.querySelector('#product_table tbody');
    let row = tbody.insertRow();
    row.innerHTML = `
<td>
    <input type="text" name="products[${rowIndex}][name]" class="form-control product_search" placeholder="Search product">
    <input type="hidden" name="products[${rowIndex}][product_id]" class="product_id">
    <small class="text-muted stock_info"></small>
</td>
<td><input type="number" name="products[${rowIndex}][qty]" class="form-control qty" value="1" step="0.01"></td>
<td>
    <select name="products[${rowIndex}][unit]" class="form-control unit" required>
        <option value="">-- Select Unit --</option>
        @foreach($units as $unit)
            <option value="{{ $unit->ShortName }}">{{ $unit->ShortName }}</option>
        @endforeach
    </select>
</td>
<td><input type="number" name="products[${rowIndex}][price]" class="form-control price" value="0" step="0.01"></td>
<td><input type="text" class="form-control row_total" readonly></td>
<td>
    <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button>
</td>`;
    rowIndex++;
}

// Remove row
function removeRow(btn) {
    btn.closest('tr').remove();
    calculateGrandTotal();
}

// Calculate totals
function calculateGrandTotal() {
    let subtotal = 0;
    document.querySelectorAll('.row_total').forEach(el => {
        subtotal += parseFloat(el.value) || 0;
    });

    let taxPercent = parseFloat(document.querySelector('[name="tax_percentage"]').value) || 0;
    let taxAmount = (subtotal * taxPercent) / 100;
    document.getElementById('tax_amount').value = taxAmount.toFixed(2);

    let shipping = parseFloat(document.getElementById('shipping_amount').value) || 0;
    // let total = subtotal + taxAmount + shipping;
    let total = subtotal + taxAmount;
    document.getElementById('total_amount').value = total.toFixed(2);

    calculateDue();
}

// Calculate due
function calculateDue() {
    let total = parseFloat(document.getElementById('total_amount').value) || 0;
    let paid  = parseFloat(document.getElementById('paid_amount').value) || 0;
    if (paid > total) paid = total;
    document.getElementById('paid_amount').value = paid;
    document.getElementById('due_amount').value = (total - paid).toFixed(2);
}

// Events
document.addEventListener('input', function(e){
    if(e.target.classList.contains('qty') || e.target.classList.contains('price')) {
        let row = e.target.closest('tr');
        let qty = parseFloat(row.querySelector('.qty').value) || 0;
        let price = parseFloat(row.querySelector('.price').value) || 0;
        row.querySelector('.row_total').value = (qty * price).toFixed(2);
        calculateGrandTotal();
    }
});

document.getElementById('paid_amount').addEventListener('input', calculateDue);
document.getElementById('shipping_amount').addEventListener('input', calculateGrandTotal);
document.querySelector('[name="tax_percentage"]').addEventListener('input', calculateGrandTotal);

// Supplier autocomplete
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

    $('#supplier_search').on('blur', function() {
        if(!$('#supplier_id').val()) {
            $(this).val('');
            $('#supplier_info').text('');
        }
    });

    // Product autocomplete
    $(document).on('focus', '.product_search', function () {
        $(this).autocomplete({
            source: '/product-autocomplete',
            minLength: 1,
            select: function(event, ui) {
                let row = $(this).closest('tr');
                row.find('.product_id').val(ui.item.id); // ✅ hidden input set
                $(this).val(ui.item.value);             // product name দেখাবে
                row.find('.stock_info').text('Stock: ' + (ui.item.stock ?? 0));
                calculateGrandTotal();
                return false;
            }
        });
    });
});

// Final submit validation
            document.querySelector('form').addEventListener('submit', function(e){
                let valid = true;
                document.querySelectorAll('.product_id').forEach(function(input){
                    if(!input.value){
                        alert('Please select product from list');
                        input.focus();
                        valid = false;
                        e.preventDefault();
                        return false;
                    }
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
