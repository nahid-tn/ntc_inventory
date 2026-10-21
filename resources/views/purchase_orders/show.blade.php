<x-app-layout>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Purchase Order Details
    </h2>
</x-slot>

<div class="py-6">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

<div class="bg-white shadow-sm sm:rounded-lg p-4">

{{-- ================= HEADER ACTION ================= --}}
<div class="d-flex justify-content-between mb-3">

    <h4>
        📦 PO No: {{ $purchaseOrder->po_number }}
    </h4>

    <div>

        {{-- PDF --}}
        <a href="{{ route('purchase-orders.pdf', $purchaseOrder->id) }}"
           class="btn btn-danger btn-sm">
            📄 PDF
        </a>

        <!-- {{-- PRINT --}}
        <button onclick="window.print()" class="btn btn-primary btn-sm">
            🖨 Print
        </button> -->

    </div>

</div>

{{-- ================= SUPPLIER INFO ================= --}}
<table class="table table-bordered">

    <tr>
        <th width="25%">Supplier</th>
        <td>{{ $purchaseOrder->supplier->supplier_name ?? '' }}</td>
    </tr>

    <tr>
        <th>Order Date</th>
        <td>{{ $purchaseOrder->order_date }}</td>
    </tr>

    <tr>
        <th>Expected Date</th>
        <td>{{ $purchaseOrder->expected_date ?? '-' }}</td>
    </tr>

    <tr>
        <th>Status</th>
        <td>
            @if($purchaseOrder->status == 'pending')
                <span class="badge bg-warning">Pending</span>
            @elseif($purchaseOrder->status == 'approved')
                <span class="badge bg-success">Approved</span>
            @else
                <span class="badge bg-secondary">
                    {{ ucfirst($purchaseOrder->status) }}
                </span>
            @endif
        </td>
    </tr>

</table>

<hr>

{{-- ================= PRODUCT LIST ================= --}}
<h5>🧾 Product Details</h5>

<table class="table table-bordered table-striped">

<thead>
<tr>
    <th>#</th>
    <th>Product</th>
    <th>Qty</th>
    <th>Unit</th>
    <th>Unit Price</th>
    <th>Total</th>
</tr>
</thead>

<tbody>

@php $i = 1; @endphp

@foreach($purchaseOrder->details as $item)

<tr>
    <td>{{ $i++ }}</td>

    <td>
        {{ $item->product->product_name ?? 'N/A' }}
    </td>

    <td style="text-align: right">{{ number_format($item->qty, 2) }}</td>

    <td style="text-align: center">{{ $item->unit }}</td>

    <td style="text-align: right">{{ number_format($item->price, 2) }}</td>

    <td style="text-align: right">
        {{ number_format($item->qty * $item->price, 2) }}
    </td>
</tr>

@endforeach

</tbody>

</table>

<hr>

{{-- ================= SUMMARY ================= --}}
<table class="table table-bordered">

    <tr>
        <th>Subtotal</th>
        <td>{{ number_format($purchaseOrder->subtotal ?? 0, 2) }}</td>
    </tr>

    <tr>
        <th>Discount</th>
        <td>{{ number_format($purchaseOrder->discount ?? 0, 2) }}</td>
    </tr>

    <tr>
        <th>Shipping</th>
        <td>{{ number_format($purchaseOrder->shipping ?? 0, 2) }}</td>
    </tr>

    <tr>
        <th>Tax</th>
        <td>{{ number_format($purchaseOrder->tax ?? 0, 2) }}</td>
    </tr>

    <tr>
        <th>Total Amount</th>
        <td>
            <strong class="text-primary">
                {{ number_format($purchaseOrder->total_amount ?? 0, 2) }}
            </strong>
        </td>
    </tr>

</table>
<button type="button" class="btn btn-secondary" onclick="history.back()">← Back</button>
</div>

</div>

</div>

</x-app-layout>