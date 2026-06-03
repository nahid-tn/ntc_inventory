<!DOCTYPE html>
<html lang="bn">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Profit Loss Report</title>

    <style>
        @font-face {
            font-family: 'SolaimanLipi';
            src: url('{{ public_path('fonts/SolaimanLipi.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        * {
            font-family: 'SolaimanLipi', DejaVu Sans, sans-serif !important;
        }

        body {
            font-family: 'SolaimanLipi', DejaVu Sans, sans-serif;
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

<h3>লাভ-ক্ষতি রিপোর্ট</h3>

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
    মোট বিক্রয়: {{ number_format($grandTotalSale,2) }} |
    মোট খরচ: {{ number_format($grandTotalCost,2) }} |
    মোট ব্যয়: {{ number_format($totalExpense,2) }} |
    মোট লাভ: {{ number_format($grossProfit,2) }} |
    নিট লাভ: 
    <b style="color: {{ $netProfitFinal >= 0 ? 'green' : 'red' }}">
        {{ number_format($netProfitFinal,2) }}
    </b>
</div>

{{-- ================= NEW PRODUCTS ================= --}}
<h4>নতুন পণ্য</h4>
<table>
    <thead>
        <tr>
            <th>নাম</th>
            <th>পরিমাণ</th>
            <th>ক্রয়মূল্য</th>
            <th>বিক্রয়মূল্য</th>
            <th>লাভ</th>
        </tr>
    </thead>
    <tbody>
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
        <tr><td colspan="5">কোনো তথ্য নেই</td></tr>
        @endforelse

        <tr class="total-row">
            <td>মোট</td>
            <td>{{ $newTotalQty }}</td>
            <td>{{ number_format($newTotalCost,2) }}</td>
            <td>{{ number_format($newTotalSale,2) }}</td>
            <td>{{ number_format($newTotalSale - $newTotalCost,2) }}</td>
        </tr>
    </tbody>
</table>

{{-- ================= OLD PRODUCTS ================= --}}
<h4>পুরাতন পণ্য</h4>
<table>
    <thead>
        <tr>
            <th>নাম</th>
            <th>পরিমাণ</th>
            <th>ক্রয়মূল্য</th>
            <th>বিক্রয়মূল্য</th>
            <th>লাভ</th>
        </tr>
    </thead>
    <tbody>
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
        <tr><td colspan="5">কোনো তথ্য নেই</td></tr>
        @endforelse

        <tr class="total-row">
            <td>মোট</td>
            <td>{{ $oldTotalQty }}</td>
            <td>{{ number_format($oldTotalCost,2) }}</td>
            <td>{{ number_format($oldTotalSale,2) }}</td>
            <td>{{ number_format($oldTotalSale - $oldTotalCost,2) }}</td>
        </tr>
    </tbody>
</table>

{{-- ================= EXPENSE ================= --}}
<h4>ব্যয়</h4>
<table>
    <tr>
        <td>অন্যান্য খরচ</td>
        <td>{{ number_format($expenses,2) }}</td>
    </tr>
    <tr>
        <td>পরিবহন</td>
        <td>{{ number_format($transport,2) }}</td>
    </tr>
    <tr class="total-row">
        <td>মোট ব্যয়</td>
        <td>{{ number_format($totalExpense,2) }}</td>
    </tr>
</table>

{{-- ================= FINAL RESULT ================= --}}
<h3>
নিট লাভ/ক্ষতি:
<span style="color: {{ $netProfitFinal >= 0 ? 'green' : 'red' }}">
    {{ number_format($netProfitFinal,2) }} টাকা
</span>
</h3>

</body>
</html>