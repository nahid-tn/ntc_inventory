{{-- resources/views/purchase_returns/edit.blade.php --}}

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Purchase Return') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Validation Errors --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('purchase-return.update', $purchaseReturn->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Invoice + Date --}}
                        <div class="form-row mb-3">
                            <div class="form-group col-md-3">
                                <label>Invoice No</label>
                                <input type="text" name="invoice_no" class="form-control" value="{{ $purchaseReturn->invoice_no }}" placeholder="Leave blank for auto">
                            </div>

                            <div class="form-group col-md-4">
                                <label>Purchase Return Date <span class="text-danger">*</span></label>
                                <input type="date" name="return_date" class="form-control" value="{{ \Carbon\Carbon::parse($purchaseReturn->return_date)->format('Y-m-d') }}" required>
                            </div>
                        </div>

                        {{-- Supplier --}}
                        <div class="form-group mb-3">
                            <label>Supplier <span class="text-danger">*</span></label>
                            <input type="text" id="supplier_search" class="form-control" value="{{ $purchaseReturn->supplier->supplier_name ?? '' }}" placeholder="Search Supplier...">
                            <input type="hidden" name="supplier_id" id="supplier_id" value="{{ $purchaseReturn->supplier_id }}">
                            <small id="supplier_info">{{ $purchaseReturn->supplier->mobile ?? '' }}</small>
                        </div>
                        <hr>

                        <h3 class="text-center mb-3">Product Details</h3>
                        {{-- Product Table --}}
                        <table class="table table-bordered" id="product_table">
                            <thead>
                                <tr class="text-center">
                                    <th width="38%">Product</th>
                                    <th width="10%">Qty</th>
                                    <th width="10%">Unit</th>
                                    <th width="12%">Unit Price</th>
                                    <th width="12%">Total</th>
                                    <th width="20%">Reason</th>
                                    <th width="10%">Action</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach($purchaseReturn->items as $index => $item)
                                    <tr>
                                        <td>
                                            <input type="text" name="items[{{ $index }}][name]" class="form-control product_search" value="{{ $item->product->product_name ?? '' }}" placeholder="Search product">                                           >
                                            <input type="hidden" name="items[{{ $index }}][product_id]" class="product_id" value="{{ $item->product_id }}" >
                                            <small class="text-muted stock_info"> Stock: {{ $item->product->stock ?? 0 }} </small>
                                        </td>
                                        <td><input type="number" name="items[{{ $index }}][qty]" class="form-control qty" value="{{ $item->qty }}" min="1" step="0.01"></td>
                                        <td><input type="text" name="items[{{ $index }}][unit]" class="form-control unit" value="{{ $item->unit }}" ></td>
                                        <td><input type="number" name="items[{{ $index }}][price]" class="form-control price" value="{{ $item->price }}" step="0.01" ></td>
                                        <td><input type="text" class="form-control row_total" value="{{ number_format($item->total, 2, '.', '') }}" readonly ></td>
                                        <td><input type="text" name="items[{{ $index }}][reason]" class="form-control" value="{{ $item->reason }}" placeholder="Reason"></td>
                                        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)" > Remove </button></td>
                                    </tr>
                                @endforeach

                            </tbody>
                        </table>

                        <button type="button" class="btn btn-success" onclick="addRow()"> Add More </button>

                        <div class="form-row mt-4">
                            <div class="form-group col-md-3">
                                <label>Transport Cost</label>
                                <input type="text" name="transport_cost" id="transport_cost" class="form-control" value="{{ number_format($purchaseReturn->transport_cost, 2, '.', '') }}" >
                            </div>
                            <div class="form-group col-md-3">
                                <label>Total Amount</label>
                                <input type="text" name="total_amount" id="total_amount" class="form-control" value="{{ number_format($purchaseReturn->total_amount, 2, '.', '') }}" readonly >
                            </div>
                            <div class="form-group col-md-3">
                                <label>Reason</label>
                                <input type="text" name="note" class="form-control" value="{{ $purchaseReturn->note }}" placeholder="Any reason for return">
                            </div>

                            <div class="form-group col-md-3">
                                <label>Transport Receive</label>
                                <input type="file" class="form-control" name="transport_receive" id="transport_receive" value="{{ $purchaseReturn->transport_receive }}" accept=".jpg,.jpeg,.png,.webp,.pdf">
                                <!-- Preview Area -->
                                <div id="filePreview" style="margin-top:10px;"></div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary"> Update Return </button>
                        <button type="button" class="btn btn-secondary" onclick="history.back()" > ← Back </button>
                    </form>
                </div>
            </div>
        </div>

        <br>
        <span>@include('layouts.footer')</span>
    </div>
</x-app-layout>


<script>
let rowIndex = {{ $purchaseReturn->items->count() }};

function addRow() {
    let tbody = document.querySelector('#product_table tbody');
    let row = tbody.insertRow();
    row.innerHTML = `
        <td>
            <input type="text" name="items[${rowIndex}][name]" class="form-control product_search" placeholder="Search product">
            <input type="hidden" name="items[${rowIndex}][product_id]" class="product_id">
            <small class="text-muted stock_info"></small>
        </td>
        <td><input type="number" name="items[${rowIndex}][qty]" class="form-control qty" value="1" min="1" step="0.01"></td>
        <td><input type="text" name="items[${rowIndex}][unit]" class="form-control unit"></td>
        <td><input type="number" name="items[${rowIndex}][price]" class="form-control price" value="0" step="0.01"></td>
        <td><input type="text" class="form-control row_total" value="0" readonly></td>
        <td><input type="text" name="items[${rowIndex}][reason]" class="form-control" placeholder="Reason"></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
    `;

    rowIndex++;
}

function removeRow(btn) {
    btn.closest('tr').remove();
    calculateGrandTotal();
}

function calculateGrandTotal() {
    let grandTotal = 0;
    document.querySelectorAll('.row_total').forEach(function(el) {
        grandTotal += parseFloat(el.value) || 0;
    });
    document.getElementById('total_amount').value = grandTotal.toFixed(2);
}

document.addEventListener('input', function(e) {
    if (
        e.target.classList.contains('qty') ||
        e.target.classList.contains('price')
    ) {
        let row = e.target.closest('tr');
        let qty = parseFloat(row.querySelector('.qty').value) || 0;
        let price = parseFloat(row.querySelector('.price').value) || 0;
        row.querySelector('.row_total').value = (qty * price).toFixed(2);
        calculateGrandTotal();
    }
});

$(document).ready(function () {
    $('#supplier_search').autocomplete({
        source: '/supplier-autocomplete',
        minLength: 1,
        select: function(event, ui) {
            $('#supplier_search').val(ui.item.value);
            $('#supplier_id').val(ui.item.id);
            $('#supplier_info').text(
                'Mobile: ' + (ui.item.mobile ?? 'N/A')
            );
            return false;
        }
    });

    $('#supplier_search').on('blur', function () {
        if (!$('#supplier_id').val()) {
            $(this).val('');
            $('#supplier_info').text('');
        }
    });

    $(document).on('focus', '.product_search', function () {
        $(this).autocomplete({
            source: '/product-autocomplete',
            minLength: 1,
            select: function(event, ui) {
                let row = $(this).closest('tr');
                $(this).val(ui.item.value);
                row.find('.product_id').val(ui.item.id);
                row.find('.stock_info').text(
                    'Stock: ' + (ui.item.stock ?? 0)
                );
                row.find('.price').val(
                    ui.item.selling_price ?? 0
                );
                row.find('.unit').val(
                    ui.item.unit ?? ''
                );
                let qty = parseFloat(
                    row.find('.qty').val()
                ) || 0;
                let price = parseFloat(
                    row.find('.price').val()
                ) || 0;
                row.find('.row_total').val(
                    (qty * price).toFixed(2)
                );
                calculateGrandTotal();
                return false;
            }
        });

    });

});

document.querySelector('form').addEventListener('submit', function(e) {
    let valid = true;
    document.querySelectorAll('.product_id').forEach(function(input) {
        if (!input.value) {
            alert('Please select product from autocomplete list');
            input.focus();
            valid = false;
            e.preventDefault();
            return false;
        }
    });
});
</script>