<x-app-layout>
<x-slot name="header">
<h2 class="font-semibold text-xl text-gray-800 leading-tight">
{{ __('Purchase Details') }}
</h2>
</x-slot>

<div class="py-12">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

<h4>Add Partial Payment</h4>
<form action="{{ route('purchase.addPayment', $purchase->id) }}" method="POST">
@csrf

<input type="number" name="amount" placeholder="Payment Amount" required>

<input type="date" name="payment_date" value="{{ date('Y-m-d') }}">

<select name="payment_method" required>
<option value="cash">Cash</option>
<option value="bank">Bank</option>
</select>

<input type="text" name="note" placeholder="Note (optional)">

<button type="submit">Add Payment</button>

</form>

<hr class="my-4">

<h4>Invoice: {{ $purchase->Invoice_no }}</h4>
<h4>Supplier Name: {{ $purchase->supplier->supplier_name }}</h4>
<h4>Contact: {{ $purchase->supplier->mobile }}</h4>
<h4>Address: {{ $purchase->supplier->address }}</h4>
<h4>Date: {{ \Carbon\Carbon::parse($purchase->date)->format('d-M-Y') }}</h4>


@php
$paidAmount = $purchase->payments->sum('amount');
$shippingAmount = $purchase->shipping_amount ?? 0;
$fullAmount = $paidAmount + $shippingAmount; 
$dueAmount = $purchase->total_amount - $fullAmount;
@endphp


@if(($purchase->payments ?? collect())->count())
<hr class="my-4">

<h4>Payment History</h4>

<table class="table table-bordered">
<thead>
<tr>
<th>#</th>
<th>Payment Date</th>
<th>Amount</th>
<th>Method</th>
<th>Note</th>
</tr>
</thead>

<tbody>

@foreach($purchase->payments as $index => $payment)

<tr>

<td>{{ $index + 1 }}</td>

<td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') }}</td>

<td>{{ number_format($payment->amount,2) }}</td>

<td>{{ ucfirst($payment->payment_method) }}</td>

<td>{{ $payment->note }}</td>

</tr>

@endforeach

</tbody>
</table>

@endif

<hr class="my-4">

<h4>Products:</h4>

<table class="table table-bordered">

<thead>
<tr>
<th>Product Name</th>
<th>Qty</th>
<th>Unit</th>
<th>Unit Price</th>
<th>Total</th>
</tr>
</thead>

<tbody>

@foreach($purchase->details as $item)

<tr>

<td>{{ $item->product->product_name }}</td>

<td>{{ $item->qty }}</td>

<td>{{ $item->unit }}</td>

<td>{{ number_format($item->price,2) }}</td>

<td>{{ number_format($item->total,2) }}</td>

</tr>

@endforeach

</tbody>
</table>


<hr class="my-4">

<h5>Subtotal: {{ number_format($purchase->subtotal,2) }}</h5>

<h5>Tax ({{ $purchase->tax_percentage ?? 0 }}%): 
{{ number_format($purchase->tax_amount,2) }}
</h5>

<h5>Shipping: {{ number_format($purchase->shipping_amount,2) }}</h5>

<h5>Total Amount: {{ number_format($purchase->total_amount,2) }}</h5>

<h5>Paid Amount: {{ number_format($fullAmount,2) }}</h5>

<h5>Due Amount: {{ number_format($dueAmount,2) }}</h5>

<hr class="my-4">

<button type="button" class="btn btn-secondary" onclick="history.back()">
← Back
</button>

</div>
</div>

<br>

<span>@include('layouts.footer')</span>

</div>
</x-app-layout>
```
