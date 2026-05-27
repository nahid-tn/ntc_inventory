<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Customer Due Report
    </h2>
</x-slot>

<div class="py-12">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
<div class="bg-white shadow-sm sm:rounded-lg p-6">

<form action="{{ route('reports.due_reports') }}" method="GET" class="row g-3 mb-4">
    <div class="row">
        <div class="col-md-3">
            <input type="text" name="customer" class="form-control" placeholder="Customer / Mobile" value="{{ $customerFilter }}">
        </div>
        <div class="col-md-3">
        <input type="text" name="invoice" class="form-control" placeholder="Invoice" value="{{ $invoiceFilter }}">
        </div>
        <div class="col-md-3">
        <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
        </div>
        <div class="col-md-3">
        <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
        </div>
    </div>
    <div class="row mt-2">
    <div class="col-md-3">
    <input type="number" step="0.01" name="due_min" class="form-control" placeholder="Min Due" value="{{ $dueMin }}">
    </div>
    <div class="col-md-3">
    <input type="number" step="0.01" name="due_max" class="form-control" placeholder="Max Due" value="{{ $dueMax }}">
    </div>
    <div class="col-md-2">
        <select name="report_type" class="form-control">
            <option value="">All Reports</option>
            <option value="customer" {{ $reportType=='customer'?'selected':'' }}>Customer Wise</option>
            <option value="invoice" {{ $reportType=='invoice'?'selected':'' }}>Invoice Wise</option>
            <option value="payment" {{ $reportType=='payment'?'selected':'' }}>Payment History</option>
        </select>
    </div>
    <div class="col-md-1">
        <button class="btn btn-primary w-100">Filter</button>
    </div>
    <div class="col-md-1">
        <a href="{{ route('reports.due_reports.pdf', request()->query()) }}" class="btn btn-danger mb-2"> Download PDF </a>
    </div>
</form>

<!-- ================= Customer Wise Due ================= -->
@if($reportType=='customer' || $reportType==null)

<h5>Customer Wise Due</h5>

@php
$totalSales = 0;
$totalPaid = 0;
$totalDue = 0;
@endphp

<table class="table table-bordered table-striped">

<thead>
<tr>
<th>Customer</th>
<th>Mobile</th>
<th>Total Sales</th>
<th>Total Paid</th>
<th class="text-danger">Due</th>
</tr>
</thead>

<tbody>

@foreach($customers as $row)

@php
$totalSales += $row->total_sales;
$totalPaid += $row->total_paid;
$totalDue += $row->total_due;
@endphp

<tr>
<td>{{ $row->customer_name }}</td>
<td>{{ $row->mobile }}</td>
<td>{{ number_format($row->total_sales,2) }}</td>
<td>{{ number_format($row->total_paid,2) }}</td>
<td class="text-danger">{{ number_format($row->total_due,2) }}</td>
</tr>

@endforeach

</tbody>

<tfoot>
<tr style="font-weight:bold;background:#f3f3f3">
<td colspan="2" class="text-end">Total</td>
<td>{{ number_format($totalSales,2) }}</td>
<td>{{ number_format($totalPaid,2) }}</td>
<td class="text-danger">{{ number_format($totalDue,2) }}</td>
</tr>
</tfoot>

</table>

@endif


<!-- ================= Invoice Wise Due ================= -->

@if($reportType=='invoice' || $reportType==null)

<h5>Invoice Wise Due</h5>

@php
$totalInvoice = 0;
$totalInvoiceDue = 0;
@endphp

<table class="table table-bordered table-striped">

<thead>
<tr>
<th>Invoice</th>
<th>Customer</th>
<th>Sale Date</th>
<th>Total</th>
<th class="text-danger">Due</th>
</tr>
</thead>

<tbody>

@foreach($invoices as $row)

@php
$totalInvoice += $row->total_amount;
$totalInvoiceDue += $row->due;
@endphp

<tr>
<td>{{ $row->invoice_no }}</td>
<td>{{ $row->customer_name }}</td>
<td>{{ $row->sale_date }}</td>
<td>{{ number_format($row->total_amount,2) }}</td>
<td class="text-danger">{{ number_format($row->due,2) }}</td>
</tr>

@endforeach

</tbody>

<tfoot>
<tr style="font-weight:bold;background:#f3f3f3">
<td colspan="3" class="text-end">Total</td>
<td>{{ number_format($totalInvoice,2) }}</td>
<td class="text-danger">{{ number_format($totalInvoiceDue,2) }}</td>
</tr>
</tfoot>

</table>

@endif


<!-- ================= Payment History ================= -->

@if($reportType=='payment' || $reportType==null)

<h5>Invoice + Payment History</h5>

@php
$totalAmount = 0;
$totalReceived = 0;
$totalDueAmount = 0;
@endphp

<table class="table table-bordered table-striped">

<thead>
<tr>
<th>Invoice</th>
<th>Customer</th>
<th>Sale Date</th>
<th>Total Amount</th>
<th>Total Received</th>
<th class="text-danger">Due</th>
</tr>
</thead>

<tbody>

@foreach($salesPayments as $row)

@php
$totalAmount += $row->total_amount;
$totalReceived += $row->total_received;
$totalDueAmount += $row->due;
@endphp

<tr>
<td>{{ $row->invoice_no }}</td>
<td>{{ $row->customer_name }}</td>
<td>{{ $row->sale_date }}</td>
<td>{{ number_format($row->total_amount,2) }}</td>
<td>{{ number_format($row->total_received,2) }}</td>
<td class="text-danger">{{ number_format($row->due,2) }}</td>
</tr>

@endforeach

</tbody>

<tfoot>
<tr style="font-weight:bold;background:#f3f3f3">
<td colspan="3" class="text-end">Total</td>
<td>{{ number_format($totalAmount,2) }}</td>
<td>{{ number_format($totalReceived,2) }}</td>
<td class="text-danger">{{ number_format($totalDueAmount,2) }}</td>
</tr>
</tfoot>

</table>

@endif


</div>
</div>
</div>
</x-app-layout>