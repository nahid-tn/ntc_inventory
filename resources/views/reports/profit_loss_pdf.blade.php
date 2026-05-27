<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Profit Loss Report</title>

    <style>
        body {
            font-family: SolaimanLipi;
            font-size: 12px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        table, th, td {
            border: 1px solid #999;
        }

        th, td {
            padding: 6px;
            text-align: center;
        }

        .total-row {
            font-weight: bold;
            background: #eef2ff;
        }

        .summary-box {
            margin-bottom: 10px;
        }

        h4 {
            margin-top: 15px;
        }
    </style>
</head>

<body>

<h3>Profit & Loss Report</h3>

<p>
{{ $startDate->format('d M, Y') }} - {{ $endDate->format('d M, Y') }}
</p>

@php
    // ================= NEW TOTAL =================
    $newTotalCost = $newProducts->sum('total_cost');
    $newTotalSale = $newProducts->sum('total_sale');
    $newTotalQty  = $newProducts->sum('total_qty');

    // ================= OLD TOTAL =================
    $oldTotalCost = $oldProducts->sum('total_cost');
    $oldTotalSale = $oldProducts->sum('total_sale');
    $oldTotalQty  = $oldProducts->sum('total_qty');

    // ================= GRAND TOTAL =================
    $grandTotalCost = $newTotalCost + $oldTotalCost;
    $grandTotalSale = $newTotalSale + $oldTotalSale;

    // ================= PROFIT =================
    $grossProfit = $grandTotalSale - $grandTotalCost;

    // ================= EXPENSE =================
    $totalExpense = $expenses + $transport;

    // ================= NET PROFIT =================
    $netProfitFinal = $grossProfit - $totalExpense;
@endphp

{{-- ================= SUMMARY ================= --}}
<div class="summary-box">
    Total Sale: {{ number_format($grandTotalSale,2) }} |
    Total Cost: {{ number_format($grandTotalCost,2) }} |
    Total Expense: {{ number_format($totalExpense,2) }} |
    Gross Profit: {{ number_format($grossProfit,2) }} |
    Net Profit: 
    <b style="color: {{ $netProfitFinal >= 0 ? 'green' : 'red' }}">
        {{ number_format($netProfitFinal,2) }}
    </b>
</div>

{{-- ================= NEW PRODUCTS ================= --}}
<h4>New Products</h4>
<table>
    <tr>
        <th>Name</th>
        <th>Qty</th>
        <th>Cost</th>
        <th>Sale</th>
        <th>Profit</th>
    </tr>

    @forelse($newProducts as $p)
    <tr>
        <td>{{ $p->product_name }}</td>
        <td>{{ $p->total_qty }}</td>
        <td>{{ number_format($p->total_cost,2) }}</td>
        <td>{{ number_format($p->total_sale,2) }}</td>
        <td style="color: {{ ($p->total_sale - $p->total_cost) >= 0 ? 'green' : 'red' }}">
            {{ number_format($p->total_sale - $p->total_cost,2) }}
        </td>
    </tr>
    @empty
    <tr><td colspan="5">No Data</td></tr>
    @endforelse

    {{-- TOTAL --}}
    <tr class="total-row">
        <td>Total</td>
        <td>{{ $newTotalQty }}</td>
        <td>{{ number_format($newTotalCost,2) }}</td>
        <td>{{ number_format($newTotalSale,2) }}</td>
        <td>{{ number_format($newTotalSale - $newTotalCost,2) }}</td>
    </tr>
</table>

{{-- ================= OLD PRODUCTS ================= --}}
<h4>Old Products</h4>
<table>
    <tr>
        <th>Name</th>
        <th>Qty</th>
        <th>Cost</th>
        <th>Sale</th>
        <th>Profit</th>
    </tr>

    @forelse($oldProducts as $p)
    <tr>
        <td>{{ $p->product_name }}</td>
        <td>{{ $p->total_qty }}</td>
        <td>{{ number_format($p->total_cost,2) }}</td>
        <td>{{ number_format($p->total_sale,2) }}</td>
        <td style="color: {{ ($p->total_sale - $p->total_cost) >= 0 ? 'green' : 'red' }}">
            {{ number_format($p->total_sale - $p->total_cost,2) }}
        </td>
    </tr>
    @empty
    <tr><td colspan="5">No Data</td></tr>
    @endforelse

    {{-- TOTAL --}}
    <tr class="total-row">
        <td>Total</td>
        <td>{{ $oldTotalQty }}</td>
        <td>{{ number_format($oldTotalCost,2) }}</td>
        <td>{{ number_format($oldTotalSale,2) }}</td>
        <td>{{ number_format($oldTotalSale - $oldTotalCost,2) }}</td>
    </tr>
</table>

{{-- ================= EXPENSE ================= --}}
<h4>Expense</h4>
<table>
    <tr>
        <td>Expense</td>
        <td>{{ number_format($expenses,2) }}</td>
    </tr>
    <tr>
        <td>Transport</td>
        <td>{{ number_format($transport,2) }}</td>
    </tr>
    <tr class="total-row">
        <td>Total Expense</td>
        <td>{{ number_format($totalExpense,2) }}</td>
    </tr>
</table>

{{-- ================= FINAL RESULT ================= --}}
<h3>
Net Profit / Loss:
<span style="color: {{ $netProfitFinal >= 0 ? 'green' : 'red' }}">
    {{ number_format($netProfitFinal,2) }}
</span>
</h3>

</body>
</html>