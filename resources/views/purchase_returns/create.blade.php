<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Return') }}
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

                    <form action="{{ route('purchase-return.store') }}" method="post" enctype="multipart/form-data">
                        @csrf

                        {{-- Invoice & Date --}}
                        <div class="form-row mb-3">
                            <!-- <div class="form-group col-md-4">
                                <label>Return Invoice No.<span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="invoice_no" placeholder="Invoice No." required>
                            </div> -->
                            <div class="form-group col-md-3">
                                <label>Invoice No</label>
                                <input type="text" name="invoice_no" class="form-control" placeholder="Leave blank for auto">
                                <small class="text-muted">If empty, invoice will be auto-generated</small>
                            </div>

                            <div class="form-group col-md-4">
                                <label>Purchase Returne Date<span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="return_date" required>
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
                                    <th width="10%">Unit Price</th>
                                    <th width="10%">Total</th>
                                    <th width="20%">Body Date</th>
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
                                    <td><input type="number" name="products[0][qty]" class="form-control qty" value="1" min="1" step="0.01"></td>
                                    <td><input type="text" name="products[0][unit]" class="form-control unit"></td>
                                    <td><input type="number" name="products[0][price]" class="form-control price" value="0" step="0.01"></td>
                                    <td><input type="text" class="form-control row_total" value="0" readonly></td>
                                    <td><input type="text" name="products[0][reason]" class="form-control" placeholder="Reason"></td>
                                    <td>   
                                        <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                                    <button type="button" class="btn btn-success" onclick="addRow()"> Add More</button>
                        <div class="form-row mb-4 mt-3">
                            <div class="form-group col-md-3">
                                <label>Transport Cost</label>
                                <input type="number" class="form-control" name="transport_cost" id="transport_cost" value="0" step="0.01">
                            </div>
                            <div class="form-group col-md-3">
                                <label>Total Amount</label>
                                <input type="text" class="form-control" name="total_amount" id="total_amount" value="0" readonly>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Note</label>
                                <input type="text" class="form-control" name="note" placeholder="Any Reason for Return">
                            </div>
                            {{-- <div class="form-group col-md-3">
                                <label>Transport Receive Image</label>
                                <input type="file" class="form-control border" name="transport_receive" accept="application/pdf">
                            </div> --}}
                            <div class="form-group col-md-3">
                                <label>Transport Receive</label>

                                <input type="file"
                                    class="form-control"
                                    name="transport_receive"
                                    id="transport_receive"
                                    accept=".jpg,.jpeg,.png,.webp,.pdf">

                                <!-- Preview Area -->
                                <div id="filePreview" style="margin-top:10px;"></div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Returne</button> | <button type="button" class="btn btn-secondary" onclick="history.back()"> ← Back </button>
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
        <td><input type="number" name="products[${rowIndex}][qty]" class="form-control qty" value="1" min="1"></td>
        <td><input type="text" name="products[${rowIndex}][unit]" class="form-control unit"></td>
        <td><input type="number" name="products[${rowIndex}][price]" class="form-control price" value="0"></td>
        <td><input type="text" class="form-control row_total" readonly></td>
        <td><input type="text" name="products[${rowIndex}][reason]" class="form-control" placeholder="Reason"></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button></td>`;
    rowIndex++;
    }

// Remove row
function removeRow(btn) {
    btn.closest('tr').remove();
    calculateGrandTotal();
}

// Calculate totals
function calculateGrandTotal() {
    let grandTotal = 0;

    document.querySelectorAll('.row_total').forEach(el => {
        grandTotal += parseFloat(el.value) || 0;
    });

    document.getElementById('total_amount').value = grandTotal.toFixed(2);
}

// Row calculation
document.addEventListener('input', function(e){
    if (e.target.classList.contains('qty') || e.target.classList.contains('price')) {

        let row = e.target.closest('tr');
        let qty   = parseFloat(row.querySelector('.qty').value) || 0;
        let price = parseFloat(row.querySelector('.price').value) || 0;

        row.querySelector('.row_total').value = (qty * price).toFixed(2);

        calculateGrandTotal();
    }
});

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

    document.querySelector('form').addEventListener('submit', function(e){
        setTimeout(() => {
            this.reset();
            document.querySelectorAll('#product_table tbody tr').forEach((row, index) => {
                if(index > 0) row.remove(); // keep only first row
                row.querySelector('.row_total').value = '0';
            });
            document.getElementById('total_amount').value = '0';
        }, 100);
    });

    document.addEventListener('DOMContentLoaded', function () {
        const invoiceInput = document.querySelector('[name="invoice_no"]');

        invoiceInput.addEventListener('blur', function () {
            if (!this.value.trim()) {
                this.placeholder = 'Auto: INV-XXXXXX';
            }
        });
    });


    document.getElementById('transport_receive').addEventListener('change', function (e) {

    const file = e.target.files[0];
    const preview = document.getElementById('filePreview');

    preview.innerHTML = "";

    if (!file) return;

    const fileType = file.type;

    // ================= IMAGE PREVIEW =================
    if (fileType.startsWith('image/')) {

        const reader = new FileReader();

        reader.onload = function (e) {
            preview.innerHTML = `
                <img src="${e.target.result}"
                     style="width:120px;height:120px;object-fit:cover;border:1px solid #ccc;padding:3px;border-radius:5px;">
                <br>
                <small>${file.name}</small>
            `;
        };

        reader.readAsDataURL(file);
    }

    // ================= PDF PREVIEW =================
    else if (fileType === "application/pdf") {

        preview.innerHTML = `
            <div style="border:1px solid #ccc;padding:10px;border-radius:5px;width:200px;">
                📄 <b>PDF File</b><br>
                <small>${file.name}</small>
            </div>
        `;
    }

    // ================= OTHER FILE =================
    else {
        preview.innerHTML = `
            <div style="color:red;">
                Unsupported file type
            </div>
        `;
    }
});
</script>
