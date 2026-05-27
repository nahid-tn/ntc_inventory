<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Sales Report') }}
        </h2>
        <style>

        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .report-table th {
            background: #f4f4f4;
        }

        </style>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                
<h2>Sales Report</h2>

<form method="GET" action="{{ route('sales.report.filter') }}">

    <select name="type">
        <option value="daily">Daily</option>
        <option value="monthly">Monthly</option>
        <option value="yearly">Yearly</option>
    </select>

    <input type="date" name="date">
    <input type="month" name="month">
    <input type="number" name="year" placeholder="Year">
<button type="submit">Search</button>

</form>


<hr>

<h3>New Product Sales</h3>
<table class="report-table">
<tr>

<th>Invoice</th>
<th>Date</th>
<th>Customer</th>
<th>Total</th>
<th>Paid</th>
<th>Due</th>

</tr>

@php
$totalNew=0;
$paidNew=0;
$dueNew=0;
@endphp

@foreach($newSales as $sale)

@php
$totalNew += $sale->total_amount;
$paidNew += $sale->total_paid;
$dueNew += $sale->due_amount;
@endphp

<tr>

<td>{{ $sale->invoice_no }}</td>
<td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d-m-Y') }}</td>
<td>{{ $sale->customer->customer_name ?? '' }}</td>
<td>{{ $sale->total_amount }}</td>
<td>{{ $sale->total_paid }}</td>
<td>{{ number_format($sale->due_amount, 2) }}</td>

</tr>

@endforeach

<tr>

<th colspan="3">Total</th>
<th>{{ $totalNew }}</th>
<th>{{ $paidNew }}</th>
<th>{{ $dueNew }}</th>

</tr>

</table>


<br><br>


@php
$totalOld = 0;
@endphp

@if($oldSales->isNotEmpty())

<h3>Old Product Sales</h3>

<table class="report-table">

<tr>
<th>Invoice</th>
<th>Date</th>
<th>Customer</th>
<th>Total</th>
<th>Paid</th>
<th>Due</th>
</tr>

@php
$paidOld=0;
$dueOld=0;
@endphp

@foreach($oldSales as $sale)

@php
$totalOld += $sale->total_amount;
$paidOld += $sale->paid_amount;
$dueOld += $sale->due_amount;
@endphp

<tr>
<td>{{ $sale->invoice_no }}</td>
<td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d-m-Y') }}</td>
<td>{{ $sale->customer->customer_name ?? '' }}</td>
<td>{{ $sale->total_amount }}</td>
<td>{{ $sale->paid_amount }}</td>
<td>{{ number_format($sale->due_amount, 2) }}</td>
</tr>

@endforeach

<tr>
<th colspan="3">Total</th>
<th>{{ $totalOld }}</th>
<th>{{ $paidOld }}</th>
<th>{{ $dueOld }}</th>
</tr>

</table>

@endif


<br>
                
            <h3>
                Grand Total Sales :
                {{ $totalNew + $totalOld }}
            </h3>
            </div>
            
        </div>
    </div>

    
</x-app-layout>
