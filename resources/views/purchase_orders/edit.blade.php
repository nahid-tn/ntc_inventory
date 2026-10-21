{{-- resources/views/purchase_orders/edit.blade.php --}}

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Purchase Order
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

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

                        <div class="table-responsive">
                            <table class="table table-bordered" id="po_table">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width: 30%;">Product</th>
                                        <th style="width: 10%;">Qty</th>
                                        <th style="width: 10%;">Unit</th>
                                        <th style="width: 15%;">Price</th>
                                        <th style="width: 15%;">Total</th>
                                        <th style="width: 15%;">Remarks</th>
                                        <th style="width: 5%;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchaseOrder->details as $index => $item)
                                    <tr class="product-row">
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
                                                Stock: {{ $item->product->stock ?? 0 }} | Price: {{ number_format($item->product->price ?? 0, 2) }}
                                            </small>
                                        </td>
                                        <td>
                                            <input 
                                                type="number" 
                                                name="products[{{ $index }}][qty]" 
                                                class="form-control qty" 
                                                value="{{ $item->qty }}" 
                                                step="any" 
                                                min="0.01"
                                            >
                                        </td>
                                        <td>
                                            <select 
                                                name="products[{{ $index }}][unit]" 
                                                class="form-control unit-select"
                                            >
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
                                            <input 
                                                type="number" 
                                                name="products[{{ $index }}][price]" 
                                                class="form-control price" 
                                                value="{{ $item->price }}" 
                                                step="any" 
                                                min="0"
                                            >
                                        </td>
                                        <td>
                                            <input 
                                                type="text" 
                                                class="form-control row_total" 
                                                value="{{ number_format($item->qty * $item->price, 2) }}" 
                                                readonly
                                            >
                                        </td>
                                        <td>
                                            <input 
                                                type="text" 
                                                name="products[{{ $index }}][remarks]" 
                                                class="form-control" 
                                                value="{{ $item->remarks }}"
                                            >
                                        </td>
                                        <td>
                                            <button 
                                                type="button" 
                                                class="btn btn-danger btn-sm remove-row"
                                            >
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <button type="button" class="btn btn-success btn-sm" id="addRow">
                            <i class="fas fa-plus"></i> Add More
                        </button>

                        <hr>

                        {{-- ================= SUMMARY ================= --}}
                        <div class="row">
                            <div class="col-md-3">
                                <label>Tax (%)</label>
                                <input 
                                    type="number" 
                                    name="tax" 
                                    id="tax" 
                                    class="form-control" 
                                    value="{{ $purchaseOrder->tax }}" 
                                    step="any" 
                                    min="0"
                                >
                            </div>
                            <div class="col-md-3">
                                <label>Shipping</label>
                                <input 
                                    type="number" 
                                    name="shipping" 
                                    id="shipping" 
                                    class="form-control" 
                                    value="{{ $purchaseOrder->shipping }}" 
                                    step="any" 
                                    min="0"
                                >
                            </div>
                            <div class="col-md-3">
                                <label>Discount</label>
                                <input 
                                    type="number" 
                                    name="discount" 
                                    id="discount" 
                                    class="form-control" 
                                    value="{{ $purchaseOrder->discount }}" 
                                    step="any" 
                                    min="0"
                                >
                            </div>
                            <div class="col-md-3">
                                <label>Grand Total</label>
                                <input 
                                    type="text" 
                                    name="total_amount" 
                                    id="total_amount" 
                                    class="form-control bg-light fw-bold" 
                                    value="{{ number_format($purchaseOrder->total_amount, 2) }}" 
                                    readonly
                                >
                            </div>
                        </div>

                        <hr>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Purchase Order
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="history.back()">
                            <i class="fas fa-arrow-left"></i> Back
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>

{{-- ================= JAVASCRIPT ================= --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

<script>
$(document).ready(function() {
    // Initialize row index for new rows
    let rowIndex = {{ $purchaseOrder->details->count() }};

    // ============================================
    // SUPPLIER AUTOCOMPLETE
    // ============================================
    $("#supplier_search").autocomplete({
        source: "/supplier-autocomplete",
        minLength: 1,
        select: function(event, ui) {
            $("#supplier_search").val(ui.item.label);
            $("#supplier_id").val(ui.item.id);
            $("#supplier_info").text('Mobile: ' + ui.item.mobile + ' | Address: ' + ui.item.address);
            return false;
        }
    });

    // ============================================
    // PRODUCT AUTOCOMPLETE
    // ============================================
    function initProductAutocomplete() {
        $('.product_search').autocomplete({
            source: "/product-autocomplete",
            minLength: 1,
            select: function(event, ui) {
                let row = $(this).closest('tr');
                $(this).val(ui.item.label);
                row.find('.product_id').val(ui.item.id);
                row.find('.price').val(ui.item.price);
                row.find('.stock_info').text('Stock: ' + ui.item.stock);
                calculateRowTotal(row);
                calculateGrandTotal();
                return false;
            }
        });
    }

    // ============================================
    // ADD NEW ROW
    // ============================================
    $('#addRow').on('click', function() {
        let newRow = `
        <tr class="product-row">
            <td>
                <input type="text" name="products[${rowIndex}][product_name]" class="form-control product_search" placeholder="Search product">
                <input type="hidden" name="products[${rowIndex}][product_id]" class="product_id" value="">
                <small class="text-muted stock_info"></small>
            </td>
            <td>
                <input type="number" name="products[${rowIndex}][qty]" class="form-control qty" value="1" step="any" min="0.01">
            </td>
            <td>
                <select name="products[${rowIndex}][unit]" class="form-control unit-select">
                    @foreach($units as $unit)
                        <option value="{{ $unit->ShortName }}">{{ $unit->ShortName }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" name="products[${rowIndex}][price]" class="form-control price" value="1" step="any" min="0">
            </td>
            <td>
                <input type="text" class="form-control row_total" value="0.00" readonly>
            </td>
            <td>
                <input type="text" name="products[${rowIndex}][remarks]" class="form-control" placeholder="Remarks">
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-sm remove-row">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        </tr>
        `;

        $('#po_table tbody').append(newRow);
        rowIndex++;
        initProductAutocomplete();
        calculateGrandTotal();
    });

    // ============================================
    // REMOVE ROW
    // ============================================
    $(document).on('click', '.remove-row', function() {
        if ($('.product-row').length > 1) {
            $(this).closest('tr').remove();
            calculateGrandTotal();
        } else {
            alert('You must have at least one product!');
        }
    });

    // ============================================
    // CALCULATE ROW TOTAL
    // ============================================
    function calculateRowTotal(row) {
        let qty = parseFloat(row.find('.qty').val()) || 0;
        let price = parseFloat(row.find('.price').val()) || 0;
        let total = qty * price;
        row.find('.row_total').val(total.toFixed(2));
        return total;
    }

    // ============================================
    // CALCULATE GRAND TOTAL
    // ============================================
    function calculateGrandTotal() {
        let subtotal = 0;
        
        $('.row_total').each(function() {
            let val = parseFloat($(this).val()) || 0;
            subtotal += val;
        });

        let tax = parseFloat($('#tax').val()) || 0;
        let shipping = parseFloat($('#shipping').val()) || 0;
        let discount = parseFloat($('#discount').val()) || 0;

        // Tax calculation (percentage based)
        let taxAmount = (subtotal * tax) / 100;
        
        let grandTotal = subtotal + taxAmount + shipping - discount;
        
        $('#total_amount').val(grandTotal.toFixed(2));
    }

    // ============================================
    // EVENT LISTENERS FOR CALCULATIONS
    // ============================================
    $(document).on('input', '.qty, .price', function() {
        let row = $(this).closest('tr');
        calculateRowTotal(row);
        calculateGrandTotal();
    });

    $(document).on('input', '#tax, #shipping, #discount', function() {
        calculateGrandTotal();
    });

    // ============================================
    // FORM SUBMIT VALIDATION
    // ============================================
    $('form').on('submit', function(e) {
        let hasProducts = $('.product-row').length > 0;
        let supplierId = $('#supplier_id').val();
        
        if (!supplierId) {
            e.preventDefault();
            alert('Please select a supplier!');
            return false;
        }
        
        if (!hasProducts) {
            e.preventDefault();
            alert('Please add at least one product!');
            return false;
        }

        // Check if all products have product_id
        let allValid = true;
        $('.product_id').each(function() {
            if (!$(this).val()) {
                allValid = false;
                return false;
            }
        });

        if (!allValid) {
            e.preventDefault();
            alert('Please select valid products for all rows!');
            return false;
        }

        return true;
    });

    // ============================================
    // INITIALIZE
    // ============================================
    initProductAutocomplete();
    calculateGrandTotal();

});
</script>

{{-- ================= CSS STYLES ================= --}}
<style>
    /* Dark mode compatibility */
    .bg-white {
        background-color: #ffffff;
    }
    
    .dark .bg-white {
        background-color: #1f2937;
    }
    
    .text-gray-900 {
        color: #111827;
    }
    
    .dark .text-gray-900 {
        color: #f3f4f6;
    }
    
    .table {
        width: 100%;
        margin-bottom: 1rem;
        color: #212529;
    }
    
    .dark .table {
        color: #f3f4f6;
    }
    
    .table-bordered {
        border: 1px solid #dee2e6;
    }
    
    .dark .table-bordered {
        border-color: #374151;
    }
    
    .table-bordered td,
    .table-bordered th {
        border: 1px solid #dee2e6;
        padding: 0.5rem;
        vertical-align: middle;
    }
    
    .dark .table-bordered td,
    .dark .table-bordered th {
        border-color: #374151;
    }
    
    .table-dark {
        background-color: #343a40;
        color: #fff;
    }
    
    .dark .table-dark {
        background-color: #1f2937;
    }
    
    .form-control {
        width: 100%;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
    }
    
    .dark .form-control {
        background-color: #1f2937;
        color: #f3f4f6;
        border-color: #374151;
    }
    
    .bg-light {
        background-color: #f8f9fa;
    }
    
    .dark .bg-light {
        background-color: #374151;
    }
    
    .fw-bold {
        font-weight: 700;
    }
    
    .btn {
        display: inline-block;
        padding: 0.375rem 0.75rem;
        font-size: 1rem;
        line-height: 1.5;
        border-radius: 0.25rem;
        border: 1px solid transparent;
        cursor: pointer;
    }
    
    .btn-primary {
        color: #fff;
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    
    .btn-success {
        color: #fff;
        background-color: #198754;
        border-color: #198754;
    }
    
    .btn-danger {
        color: #fff;
        background-color: #dc3545;
        border-color: #dc3545;
    }
    
    .btn-secondary {
        color: #fff;
        background-color: #6c757d;
        border-color: #6c757d;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        border-radius: 0.2rem;
    }
    
    .alert {
        padding: 0.75rem 1.25rem;
        margin-bottom: 1rem;
        border: 1px solid transparent;
        border-radius: 0.25rem;
    }
    
    .alert-danger {
        color: #721c24;
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }
    
    .text-muted {
        color: #6c757d;
    }
    
    .dark .text-muted {
        color: #9ca3af;
    }
    
    .table-responsive {
        overflow-x: auto;
    }
    
    .row {
        display: flex;
        flex-wrap: wrap;
        margin-right: -0.75rem;
        margin-left: -0.75rem;
    }
    
    .col-md-3 {
        flex: 0 0 25%;
        max-width: 25%;
        padding-right: 0.75rem;
        padding-left: 0.75rem;
    }
    
    .col-md-4 {
        flex: 0 0 33.333333%;
        max-width: 33.333333%;
        padding-right: 0.75rem;
        padding-left: 0.75rem;
    }
    
    .mb-3 {
        margin-bottom: 1rem;
    }
    
    .mt-3 {
        margin-top: 1rem;
    }
    
    .text-center {
        text-align: center;
    }
    
    hr {
        margin-top: 1rem;
        margin-bottom: 1rem;
        border: 0;
        border-top: 1px solid rgba(0, 0, 0, 0.1);
    }
    
    .dark hr {
        border-top-color: rgba(255, 255, 255, 0.1);
    }
</style>

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">