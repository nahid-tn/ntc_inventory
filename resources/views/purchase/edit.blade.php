<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('Edit Purchase') }}</h2>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">

                <form action="{{ route('purchase.update', $purchase->id) }}" method="post">
                @csrf
                @method('PUT')

                {{-- Invoice & Date --}}
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Invoice No<span class="text-danger">*</span></label>
                        <input type="text" name="Invoice_no" class="form-control" value="{{ $purchase->Invoice_no }}" required>
                    </div>

                    <div class="form-group col-md-4">
                        <label>Date</label>
                        <input type="date" name="date" class="form-control" value="{{ $purchase->date->format('Y-m-d') }}" required>
                    </div>
                </div>

                {{-- Supplier --}}
                <div class="form-group">
                    <label>Supplier</label>
                    <input type="text" id="supplier_search" class="form-control" placeholder="Search supplier..." value="{{ $purchase->supplier->supplier_name ?? '' }}">
                    <input type="hidden" name="supplier_id" id="supplier_id" value="{{ $purchase->supplier_id }}">
                    <small id="supplier_info" class="text-muted">
                        Mobile: {{ $purchase->supplier->mobile ?? '' }}
                    </small>
                </div>
                <hr>
                <h4 class="text-center">Products Details</h4>

                <table class="table table-bordered" id="product_table">
                <thead>
                <tr class="text-center">
                    <th width="260">Product</th>
                    <th width="100">Qty</th>
                    <th width="100">Unit</th>
                    <th width="120">Price</th>
                    <th width="120">Total</th>
                    <th width="120">Action</th>
                </tr>
                </thead>
                    <tbody>

                    @foreach($purchase->details as $index => $item)
                    <tr>
                    <td>
                        <input type="text" name="products[{{ $index }}][name]" class="form-control product_search" value="{{ $item->product->product_name ?? '' }}" required>
                        <input type="hidden" name="products[{{ $index }}][product_id]" class="product_id" value="{{ $item->product_id }}">
                        <small class="text-muted stock_info"> @if($item->product) Stock: {{ $item->product->stock }} @endif </small>
                    </td>
                    <td><input type="number" name="products[{{ $index }}][qty]" class="form-control qty" value="{{ $item->qty }}" min="1" step="0.01" required></td>
                    <td>
                        <select name="products[{{ $index }}][unit]"
                                class="form-control unit" required>
                            <option value="">-- Select Unit --</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->ShortName }}"
                                    {{ $item->unit == $unit->ShortName ? 'selected' : '' }}>
                                    {{ $unit->ShortName }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" name="products[{{ $index }}][price]" class="form-control price" value="{{ $item->price }}" step="0.001" required></td>
                    <td><input type="text" class="form-control row_total" value="{{ $item->total }}" readonly></td>
                    <td>
                        @if($index == 0)  
                        @else
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button>
                        @endif
                    </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                <button type="button" class="btn btn-success btn-sm" onclick="addRow()">Add</button>
                {{-- Calculation Section --}}
                <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Tax %</label>
                    <input type="number" class="form-control" name="tax_percentage" value="{{ $purchase->tax_percentage }}">
                </div>
                <div class="form-group col-md-4">
                    <label>Tax Amount</label>
                    <input type="text" class="form-control" id="tax_amount" name="tax_amount" value="{{ $purchase->tax_amount }}" readonly>
                </div>
                <div class="form-group col-md-4">
                    <label>Shipping</label>
                    <input type="number" class="form-control" id="shipping_amount" name="shipping_amount" value="{{ $purchase->shipping_amount }}">
                </div>
                </div>
                <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Total</label>
                    <input type="text" class="form-control" id="total_amount" name="total_amount" value="{{ $purchase->total_amount }}" readonly>
                </div>
                <div class="form-group col-md-4">
                    <label>Paid</label>
                    <input type="number" class="form-control" id="paid_amount" name="paid_amount" value="{{ $paidAmount }}" step="0.01">
                </div>
                <div class="form-group col-md-4">
                    <label>Due</label>
                    <input type="text" class="form-control" id="due_amount" value="{{ number_format($totalDue,2) }}" readonly>
                </div>
                </div>
                <div class="form-group col-md-4">
                <label>Payment Method</label>
                <select name="payment_method" class="form-control" required>
                    <option value="">-- Select --</option>
                    <option value="cash" {{ $purchase->payment_method == 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="bank" {{ $purchase->payment_method == 'bank' ? 'selected' : '' }}>Bank</option>
                    <option value="mobile" {{ $purchase->payment_method == 'mobile' ? 'selected' : '' }}>Mobile</option>
                    <option value="cheque" {{ $purchase->payment_method == 'cheque' ? 'selected' : '' }}>Cheque</option>
                </select>
                </div>
                <hr>
                <button type="submit" class="btn btn-primary">Update Purchase</button>
                <button type="button" class="btn btn-secondary" onclick="history.back()">← Back</button>
                </form>
            
                
            </div>
        </div>
    </div>
</div>

<script>

// Supplier autocomplete
$(document).ready(function(){

    $("#supplier_search").autocomplete({
        source: "/supplier-autocomplete",
        minLength: 1,
        select: function(event, ui){
            $('#supplier_search').val(ui.item.value); // দেখাবে
            $('#supplier_id').val(ui.item.id);       // hidden input set
            $('#supplier_info').text('Mobile: ' + (ui.item.mobile ?? 'N/A'));
            return false;
        }
    });

    $('#supplier_search').on('blur', function(){
        if(!$('#supplier_id').val()){
            $(this).val('');
            $('#supplier_info').text('');
        }
    });

});

let rowIndex = {{ $purchase->details->count() }};
function addRow() {

let tbody = document.querySelector('#product_table tbody');
let row = tbody.insertRow();

row.innerHTML = `
<td>
    <input type="text" name="products[${rowIndex}][name]" class="form-control product_search">
    <input type="hidden" name="products[${rowIndex}][product_id]" class="product_id">
    <small class="text-muted stock_info"></small>
    </td>
    <td><input type="number" name="products[${rowIndex}][qty]" class="form-control qty" value="1" step="0.01"></td>
    <td>
    <select name="products[${rowIndex}][unit]" class="form-control unit">
    <option value="">-- Select Unit --</option>
    @foreach($units as $unit)
    <option value="{{ $unit->ShortName }}">{{ $unit->ShortName }}</option>
    @endforeach
    </select>
</td>

    <td><input type="number" name="products[${rowIndex}][price]" class="form-control price" value="0" step="0.001"></td>
    <td><input type="text" class="form-control row_total" readonly></td>
    <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button></td>
`;

rowIndex++;
    }
        function removeRow(btn){
        btn.closest('tr').remove();
        calculateGrandTotal();
        }
        function calculateGrandTotal(){
        let subtotal = 0;
        document.querySelectorAll('.row_total').forEach(el=>{
        subtotal += parseFloat(el.value) || 0;
    });

    let taxPercent = parseFloat(document.querySelector('[name="tax_percentage"]').value) || 0;
    let taxAmount = subtotal * taxPercent / 100;
    let shipping = parseFloat(document.getElementById('shipping_amount').value) || 0;
    // let total = subtotal + taxAmount + shipping;
    let total = subtotal + taxAmount;
    document.getElementById('tax_amount').value = taxAmount.toFixed(2);
    document.getElementById('total_amount').value = total.toFixed(2);
    let paid = parseFloat(document.getElementById('paid_amount').value) || 0;
    if(paid > total) paid = total;
    document.getElementById('paid_amount').value = paid;
    document.getElementById('due_amount').value = (total - paid).toFixed(2);
    }

    document.addEventListener('input', function(e){
    if(e.target.classList.contains('qty') || e.target.classList.contains('price')){
    let row = e.target.closest('tr');
    let qty = parseFloat(row.querySelector('.qty').value) || 0;
    let price = parseFloat(row.querySelector('.price').value) || 0;
    row.querySelector('.row_total').value = (qty * price).toFixed(2);
    calculateGrandTotal();
    }
    });

    document.querySelector('[name="tax_percentage"]').addEventListener('input', calculateGrandTotal);
    document.getElementById('shipping_amount').addEventListener('input', calculateGrandTotal);
    document.getElementById('paid_amount').addEventListener('input', calculateGrandTotal);
    document.addEventListener("DOMContentLoaded", calculateGrandTotal);

$(document).on('focus','.product_search',function(){
$(this).autocomplete({
    source:'/product-autocomplete',
    minLength:1,
    select:function(event,ui){
    let row = $(this).closest('tr');
    row.find('.product_id').val(ui.item.id);
    $(this).val(ui.item.value);
    row.find('.stock_info').text('Stock: '+(ui.item.stock ?? 0));
    return false;
    }
    });
});


</script>
</x-app-layout>