<x-app-layout>

<h3>Supplier Payment</h3>

<form method="POST"
      action="{{ route('supplier.payment.store') }}">

@csrf

<div class="mb-3">

<label>Supplier</label>

<select name="supplier_id"
        class="form-control" required>

<option value="">-- Select Supplier --</option>

@foreach($suppliers as $s)
<option value="{{ $s->id }}">
    {{ $s->supplier_name }}
</option>
@endforeach

</select>

</div>


<div class="mb-3">

<label>Invoice (Optional)</label>

<select name="purchase_id"
        class="form-control">

<option value="">-- Without Invoice --</option>

@foreach($purchases as $p)
<option value="{{ $p->id }}">
    {{ $p->Invoice_no }}
</option>
@endforeach

</select>

</div>


<div class="mb-3">

<label>Amount</label>

<input type="number"
       step="0.01"
       name="amount"
       class="form-control" required>
</div>


<div class="mb-3">

<label>Payment Method</label>

<select name="payment_method"
        class="form-control">

<option>Cash</option>
<option>Bank</option>
<option>Bkash</option>
<option>Nagad</option>

</select>
</div>


<div class="mb-3">

<label>Date</label>

<input type="date"
       name="payment_date"
       value="{{ date('Y-m-d') }}"
       class="form-control">
</div>


<div class="mb-3">

<label>Note</label>

<textarea name="note"
          class="form-control"></textarea>
</div>


<button class="btn btn-success">
    Save Payment
</button>

</form>

</x-app-layout>
