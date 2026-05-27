<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Edit Supplier Payment
    </h2>
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

            <form action="{{ route('supplier.payment.update',$payment->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Supplier -->
            <div class="mb-3">
                <label>Supplier <span class="text-danger">*</span></label>
                <input type="text" id="supplier_name" name="supplier_name" class="form-control" value="{{ old('supplier_name',$payment->supplier->supplier_name) }}" required>
                <input type="hidden" id="supplier_id" name="supplier_id" value="{{ old('supplier_id',$payment->supplier_id) }}">
            </div>

            <!-- Invoice -->
            <div class="mb-3">
                <label>Invoice (Optional)</label>
                <select name="purchase_id" id="invoice_id" class="form-control">
                    <option value="">-- Without Invoice --</option>
                    @if($payment->purchase)
                        <option value="{{ $payment->purchase_id }}" selected>
                            {{ $payment->purchase->Invoice_no }} |
                            Due: {{ $payment->purchase->due_amount }}
                        </option>
                    @endif
                </select>
            </div>


            <!-- Invoice Info -->
            <div id="invoice_result"
                style="display:none;margin-top:20px;border:1px solid #ccc;padding:10px;">
                <h5>Invoice Info:</h5>
                <p>Invoice No: <span id="res_invoice_no"></span></p>
                <p>Date: <span id="res_invoice_date"></span></p>
                <p>Total: <span id="res_total"></span></p>
                <p>Paid: <span id="res_paid"></span></p>
                <p>Due: <span id="res_due"></span></p>
                <h6>Previous Payments:</h6>
                <ul id="res_payments"></ul>
            </div>

            <!-- Amount -->
            <div class="mb-3">
                <label>Amount <span class="text-danger">*</span></label>
                <input type="number" step="0.01" name="amount" class="form-control" value="{{ old('amount',$payment->amount) }}" required>
            </div>

            <!-- Payment Method -->
            <div class="mb-3">
            <label>Payment Method <span class="text-danger">*</span></label>
                <select name="payment_method" id="payment_method" class="form-control" required>
                    <option value="">-- Select Method --</option>
                    <option value="Cash" {{ $payment->payment_method=='Cash'?'selected':'' }}>Cash</option>
                    <option value="Cheque" {{ $payment->payment_method=='Cheque'?'selected':'' }}>Cheque</option>
                    <option value="Bank" {{ $payment->payment_method=='Bank'?'selected':'' }}>Bank</option>
                    <option value="Mobile Banking" {{ $payment->payment_method=='Mobile Banking'?'selected':'' }}>Mobile Banking</option>
                </select>
            </div>

            <!-- Cheque -->
            <div id="cheque_fields" style="display:none;">
            <div class="mb-3">
                <label>Cheque Number</label>
                <input type="text" name="cheque_number" class="form-control" value="{{ old('cheque_number',$payment->cheque_number) }}">
            </div>
            <div class="mb-3">
                <label>Cheque Image</label>
                <input type="file" name="cheque_image" class="form-control">
                @if($payment->cheque_image)
                    <a target="_blank"
                    href="{{ asset('storage/'.$payment->cheque_image) }}">
                    View Old Image
                    </a>
                @endif
            </div>
            </div>


            <!-- Bank / Mobile -->
            <div id="bank_fields" style="display:none;">
                <div class="mb-3">
                    <label>Deposit Slip</label>
                    <input type="file" name="deposit_slip" class="form-control">
                </div>
                <div class="mb-3">
                    <label>Transaction Number</label>
                    <input type="text" name="transaction_number" class="form-control" value="{{ old('transaction_number',$payment->transaction_number) }}">
                </div>
            </div>
            <!-- Date -->
                <div class="mb-3">
                    <label>Payment Date <span class="text-danger">*</span></label>
                    <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date', $payment->payment_date?->format('Y-m-d')) }}" required>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Update Payment</button>
                    <button type="button" class="btn btn-secondary" onclick="history.back()"> ← Back </button>
                </div>
            </form>
        </div>
        <br>
            <span class="flex justify-center"> @include('layouts.footer')</span>
    </div>
</div>

<!-- Scripts -->
<link rel="stylesheet"
href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
$(document).ready(function(){
/* Supplier Autocomplete */
$("#supplier_name").autocomplete({
source:function(request,response){
$.ajax({
url:"{{ url('/supplier-autocomplete') }}",
data:{ term:request.term },
success:function(data){
response($.map(data,function(item){
return{
label:item.value+' | '+item.mobile,
value:item.value,
id:item.id
};
}));
}
});
},
select:function(event,ui){
$("#supplier_id").val(ui.item.id);
loadInvoices(ui.item.id);
},
minLength:1
});

/* Load Invoice */

function loadInvoices(id){
if(!id){
$('#invoice_id').html('<option value="">-- Without Invoice --</option>');
return;
}
$.get('/supplier/'+id+'/invoices',function(data){
let html='<option value="">-- Without Invoice --</option>';
$.each(data,function(i,inv){
html+=`<option value="${inv.id}">
${inv.Invoice_no} | Due: ${inv.due_amount}
</option>`;
});
$('#invoice_id').html(html);
});
}

/* Invoice Info */
$('#invoice_id').change(function(){
let id=$(this).val();
if(!id){
$('#invoice_result').hide();
return;
}
$.get('/purchase/'+id+'/info',function(data){
$('#res_invoice_no').text(data.Invoice_no);
$('#res_invoice_date').text(data.date);
$('#res_total').text(data.total_amount);
$('#res_paid').text(data.paid_amount);
$('#res_due').text(data.due_amount);
let html='';
if(data.payments.length){
$.each(data.payments,function(i,p){
html+=`<li>${p.payment_date} | ${p.amount}</li>`;
});
}else{
html='<li>No payments</li>';
}
$('#res_payments').html(html);
$('#invoice_result').show();
});
});



/* Payment Method */

function toggle(method){
    if(method=='Cheque'){
    $('#cheque_fields').show();
    $('#bank_fields').hide();
    }
    else if(method=='Bank' || method=='Mobile Banking'){
    $('#bank_fields').show();
    $('#cheque_fields').hide();
    }
    else{
    $('#cheque_fields').hide();
    $('#bank_fields').hide();
    }
    }
    toggle($('#payment_method').val());
    $('#payment_method').change(function(){
    toggle($(this).val());
    });
});

</script>

</x-app-layout>
