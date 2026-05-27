<x-app-layout>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Purchase Orders List
    </h2>
</x-slot>

<div class="py-6">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

<div class="bg-white shadow-sm sm:rounded-lg p-4">

{{-- ================= HEADER ================= --}}
<div class="d-flex justify-content-between mb-3">

    <h4>📦 Purchase Orders</h4>

    <a href="{{ route('purchase-orders.create') }}"
       class="btn btn-primary btn-sm">
        ➕ Create PO
    </a>

</div>

{{-- ================= TABLE ================= --}}
<table class="table table-bordered table-striped">

<thead>
<tr>
    <th>#</th>
    <th>PO No</th>
    <th>Supplier</th>
    <th>Date</th>
    <th>Status</th>
    <th>Total</th>
    <th>Action</th>
</tr>
</thead>

<tbody>

@forelse($purchaseOrders as $key => $po)

<tr>

    <td>{{ $key + 1 }}</td>

    <td>{{ $po->po_number }}</td>

    <td>{{ $po->supplier->supplier_name ?? '' }}</td>

    <td>{{ $po->order_date }}</td>

    <td>
        @if($po->status == 'pending')
            <span class="badge bg-warning">Pending</span>
        @elseif($po->status == 'approved')
            <span class="badge bg-success">Approved</span>
        @else
            <span class="badge bg-secondary">
                {{ ucfirst($po->status) }}
            </span>
        @endif
    </td>

    <td>
        <strong>
            {{ number_format($po->total_amount, 2) }}
        </strong>
    </td>

    <td>

        <a href="{{ route('purchase-orders.show', $po->id) }}"
           class="btn btn-info btn-sm">
            View
        </a>

        <a href="{{ route('purchase-orders.edit', $po->id) }}"
           class="btn btn-primary btn-sm">
            Edit
        </a>
        {{-- PDF --}}
        <a href="{{ route('purchase-orders.pdf', $po->id) }}"
            class="btn btn-danger btn-sm">
        📄 PDF
</a>
    </td>

</tr>

@empty

<tr>
    <td colspan="7" class="text-center text-danger">
        No Purchase Orders Found
    </td>
</tr>

@endforelse

</tbody>

</table>

{{-- Pagination --}}
<div class="mt-3">
    {{ $purchaseOrders->links() }}
</div>

</div>
</div>
</div>

</x-app-layout>