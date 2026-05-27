<x-app-layout>
<x-slot name="header">
<h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
Dashboard
</h2>
</x-slot>

<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

    <!-- Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
            
            <h2>Sales Report</h2>

<form method="GET">

From
<input type="date" name="from_date">

To
<input type="date" name="to_date">

<button class="btn btn-primary">Search</button>

</form>

<hr>

<h3>New Product Sales</h3>

<table class="table table-bordered">

<tr>
<th>Invoice</th>
<th>Date</th>
<th>Customer</th>
<th>Total</th>
<th>Due</th>
</tr>

@foreach($newSales as $sale)

<tr>
<td>{{ $sale->invoice_no }}</td>
<td>{{ $sale->sale_date }}</td>
<td>{{ $sale->customer->customer_name ?? '' }}</td>
<td>{{ $sale->total_amount }}</td>
<td>{{ $sale->due_amount }}</td>
</tr>

@endforeach

</table>

<h4>Total New Sales : {{ $totalNew }}</h4>

<hr>

<h3>Old Product Sales</h3>

<table class="table table-bordered">

<tr>
<th>Invoice</th>
<th>Date</th>
<th>Customer</th>
<th>Total</th>
<th>Due</th>
</tr>

@foreach($oldSales as $sale)

<tr>
<td>{{ $sale->invoice_no }}</td>
<td>{{ $sale->sale_date }}</td>
<td>{{ $sale->customer->customer_name ?? '' }}</td>
<td>{{ $sale->total_amount }}</td>
<td>{{ $sale->due_amount }}</td>
</tr>

@endforeach

</table>

<h4>Total Old Sales : {{ $totalOld }}</h4>

<hr>

<h3>Grand Total Sales : {{ $grandTotal }}</h3>

        </div>
        <!-- Chart Section -->
    </div>
</div>


</x-app-layout>