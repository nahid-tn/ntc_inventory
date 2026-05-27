<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Supplier Payment</h2>
</x-slot>

<div class="py-12">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
<div class="bg-white shadow-sm sm:rounded-lg p-6">

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
    @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
    @endforeach
    </ul>
</div>
@endif

<form id="payment_form" action="{{ route('supplier.payment.store') }}" method="POST" enctype="multipart/form-data">
@csrf

<div class="row mb-3">
    <div class="col-md-6">
        <label>Supplier <span class="text-danger">*</span></label>
        <input type="text" id="supplier_name" name="supplier_name" class="form-control" placeholder="Type supplier name..." required value="{{ old('supplier_name') }}">
        <input type="hidden" id="supplier_id" name="supplier_id" value="{{ old('supplier_id') }}">
    </div>
    <div class="col-md-6">
        <label>Invoice (Optional)</label>
        <select name="purchase_id" id="invoice_id" class="form-control">
            <option value="">-- Without Invoice --</option>
        </select>
    </div>
</div>

<div id="invoice_result" style="display:none; border:1px solid #ccc; padding:10px; margin-bottom:15px;">
    <h5>Invoice Info:</h5>
    <p>Invoice No: <span id="res_invoice_no"></span></p>
    <p>Date: <span id="res_invoice_date"></span></p>
    <p>Total: <span id="res_total"></span></p>
    <p>Paid: <span id="res_paid"></span></p>
    <p>Due: <span id="res_due"></span></p>
    <h6>Previous Payments:</h6>
    <ul id="res_payments"></ul>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <label>Amount <span class="text-danger">*</span></label>
        <input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount') }}" required>
    </div>
    <div class="col-md-8">
        <label>Payment Method <span class="text-danger">*</span></label>
        <select name="payment_method" id="payment_method" class="form-control" required>
            <option value="">-- Select Method --</option>
            <option value="Cash" {{ old('payment_method')=='Cash'?'selected':'' }}>Cash</option>
            <option value="Cheque" {{ old('payment_method')=='Cheque'?'selected':'' }}>Cheque</option>
            <option value="Bank" {{ old('payment_method')=='Bank'?'selected':'' }}>Bank</option>
            <option value="Mobile Banking" {{ old('payment_method')=='Mobile Banking'?'selected':'' }}>Mobile Banking</option>
        </select>
    </div>
</div>

<div id="cheque_fields" class="mb-3" style="display:none;">
    <label>Cheque Number</label>
    <input type="text" name="cheque_number" class="form-control" value="{{ old('cheque_number') }}">
    <label>Cheque Image</label>
    <input type="file" name="cheque_image" class="form-control">
</div>

<div id="bank_basic_fields" class="row mb-3" style="display:none;">
    <div class="col-md-6">
        <label>Bank Name</label>
        <input type="text" name="BankName" id="BankName" class="form-control">
    </div>
    <div class="col-md-6">
        <label>A/C No.</label>
        <input type="text" name="account_number" id="account_number" class="form-control">
    </div>
</div>

<div id="bank_fields" class="mb-3" style="display:none;">
    <label>Deposit Slip / Transaction Image</label>
    <input type="file" name="deposit_slip" class="form-control">
    <label>Transaction / Tracking Number</label>
    <input type="text" name="transaction_number" class="form-control" value="{{ old('transaction_number') }}">
</div>

<div class="mb-3">
    <label>Payment Date <span class="text-danger">*</span></label>
    <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date', date('Y-m-d')) }}" required>
</div>

<button type="submit" class="btn btn-primary">Save Payment</button> | <button type="button" class="btn btn-secondary" onclick="history.back()">← Back</button>
</form>
</div>
</div>
</div>

@include('layouts.footer')

<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<script>
$(document).ready(function(){
    // Supplier Autocomplete
    $("#supplier_name").autocomplete({
        source: "{{ url('/supplier-autocomplete') }}",
        minLength: 1,
        select: function(event, ui){
            $("#supplier_id").val(ui.item.id);
            $("#supplier_name").val(ui.item.value);
            loadInvoices(ui.item.id);
            return false;
        }
    });

    function loadInvoices(supplier_id){
        if(!supplier_id) return $('#invoice_id').html('<option value="">-- Without Invoice --</option>');
        $.getJSON('/supplier/'+supplier_id+'/invoices', function(data){
            let options = '<option value="">-- Without Invoice --</option>';
            $.each(data, function(i, inv){
                let paid = inv.payments_sum || 0;
                let due = inv.total_amount - paid;
                options += `<option value="${inv.id}">${inv.Invoice_no} | Due: ${due}</option>`;
            });
            $('#invoice_id').html(options);
        });
    }

    $('#invoice_id').change(function(){
        let invoice_id = $(this).val();
        if(!invoice_id){ $('#invoice_result').hide(); return; }
        $.getJSON('/purchase/'+invoice_id+'/info', function(data){
            let paid = data.payments_sum || 0;
            let due = data.total_amount - paid;
            $('#res_invoice_no').text(data.Invoice_no);
            $('#res_invoice_date').text(data.date);
            $('#res_total').text(data.total_amount);
            $('#res_paid').text(paid);
            $('#res_due').text(due);

            let html = '';
            if(data.payments && data.payments.length){
                $.each(data.payments, function(i,p){
                    html += `<li>${p.payment_date} | ${p.amount} | ${p.payment_method}</li>`;
                });
            } else html = '<li>No payments yet.</li>';
            $('#res_payments').html(html);
            $('#invoice_result').show();
        });
    });

    function togglePaymentFields(){
        let method = $('#payment_method').val();
        $('#cheque_fields, #bank_basic_fields, #bank_fields').hide();
        $('#BankName, #account_number, #cheque_number, #cheque_image, #deposit_slip, #transaction_number').prop('required', false);

        if(method === 'Cheque'){
            $('#cheque_fields').show();
            $('#cheque_number, #cheque_image').prop('required', true);
        } else if(method === 'Bank'){
            $('#bank_basic_fields').show();
            $('#BankName, #account_number').prop('required', true);
        } else if(method === 'Mobile Banking'){
            $('#bank_fields').show();
            $('#deposit_slip, #transaction_number').prop('required', true);
        }
    }

    togglePaymentFields();
    $('#payment_method').change(togglePaymentFields);
});
</script>
</x-app-layout>