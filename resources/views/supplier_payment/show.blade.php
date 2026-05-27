<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Payment Details
    </h2>
</x-slot>

<div class="py-12">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
<div class="bg-white shadow-sm sm:rounded-lg p-6">

<h5>Payment Info</h5>
<table class="table table-bordered">
    <tr>
        <th>Supplier</th>
        <td>{{ $payment->supplier->supplier_name }}</td>
    </tr>
    <tr>
        <th>Invoice</th>
        <td>{{ $payment->purchase ? $payment->purchase->Invoice_no : 'Without Invoice' }}</td>
    </tr>
    <tr>
        <th>Amount</th>
        <td>{{ number_format($payment->amount,2) }}</td>
    </tr>
    <tr>
        <th>Payment Method</th>
        <td>{{ $payment->payment_method }}</td>
    </tr>
    @if($payment->cheque_number)
    <tr>
        <th>Cheque Number</th>
        <td>{{ $payment->cheque_number }}</td>
    </tr>
    @endif
    @if($payment->transaction_number)
    <tr>
        <th>Transaction Number</th>
        <td>{{ $payment->transaction_number }}</td>
    </tr>
    @endif
    <tr>
        <th>Payment Date</th>
        <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') }}</td>
    </tr>
</table>
    <div class="col-md-12 d-flex gap-2 mt-3">
    <a href="{{ route('supplier.payment.edit', $payment->id) }}" class="btn btn-primary">Edit</a>
    <form action="{{ route('supplier.payment.destroy', $payment->id) }}" method="POST" onsubmit="return confirm('Are you sure to delete?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>
    <button type="button" class="btn btn-secondary" onclick="history.back()">← Back</button>
</div>
</div>
</div>
</div>

<script>
$.ajax({
    url: '/supplier-payment/' + payment_id + '/delete',
    type: 'DELETE',
    data: {
        _token: '{{ csrf_token() }}'
    },
    success: function(res){
        alert('Deleted Successfully');
        location.reload();
    }
});
</script>
</x-app-layout>
