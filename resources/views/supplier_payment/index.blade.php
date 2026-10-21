<x-app-layout>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Supplier Payments
    </h2>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">

        {{-- Success Message --}}
        @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif

        {{-- New Payment Button --}}
        <a href="{{ route('supplier.payment.create') }}" class="btn btn-primary mb-3"> + New Payment </a>

        {{-- Search --}}
        <form method="GET" class="row mb-3">
            <div class="col-md-4">
                <input type="text" id="supplier_search" class="form-control" placeholder="Search Supplier...">
                <input type="hidden" name="supplier_id" id="supplier_id">
                <small id="supplier_info"></small>
            </div>

            <div class="col-md-3">
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-3">
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100"> Search </button>
            </div>
        </form>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark text-center">
            <tr>
                <th>#</th>
                <th>Payment Date</th>
                <th>Invoice</th>
                <th>Supplier</th>
                <th>Paid Amount</th>
                <th>Due After Pay</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>

@forelse($payments as $index => $payment)

@php
    $invoiceTotal = $payment->purchase->total_amount ?? 0;
    $shipping = $payment->purchase->shipping_amount ?? 0;

    if($payment->purchase){

        $paidSoFar = $payment->purchase->payments
            ->where('payment_date','<=',$payment->payment_date)
            ->sum('amount');

        // ✅ Shipping বাদ
        $dueAfter = ($invoiceTotal - $shipping) - $paidSoFar;

    } else {
        $dueAfter = 0;
    }
@endphp

<tr>
    <td class="text-center"> {{ $payments->firstItem() + $index }} </td>
    <td class="text-center"> {{ \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') }} </td>
    <td class="text-center"> {{ $payment->purchase->Invoice_no ?? 'Without Invoice' }} </td>
    <td> {{ $payment->supplier->supplier_name ?? '-' }} </td>
    <td class="text-end text-success fw-bold"> {{ number_format($payment->amount,2) }} </td>
    <td class="text-end fw-bold"> {{ number_format($dueAfter, 2) }} </td>

    <td class="text-center">
        <a href="{{ route('supplier.payment.show', $payment->id) }}" class="btn btn-sm btn-info"> View </a>
        <a href="{{ route('supplier.payment.edit', $payment->id) }}" class="btn btn-sm btn-primary"> Edit </a>
        <form action="{{ route('supplier.payment.destroy', $payment->id) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Are you sure?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger"> Delete </button>
        </form>
    </td>
</tr>

@empty
<tr>
    <td colspan="7" class="text-center text-danger">
        No Payment Found
    </td>
</tr>
@endforelse

</tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-3">
            {{ $payments->links() }}
        </div>

        </div>

        <br>
        <span class="flex justify-center">
            @include('layouts.footer')
        </span>

    </div>
</div>

{{-- Script --}}
<script>
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
});
</script>

</x-app-layout>