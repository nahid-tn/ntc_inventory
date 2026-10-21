<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

    <title>Profit Loss Report</title>

    <style>
        @font-face {
            font-family: 'SolaimanLipi';
            src: url("{{ public_path('fonts/SolaimanLipi.ttf') }}") format('truetype');
        }

        /* 🔥 ONLY FONT FIX - NO DESIGN CHANGE */
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
            font-family: 'SolaimanLipi', DejaVu Sans, sans-serif;
        }

        th, td {
            padding: 6px;
            text-align: center;
            font-family: 'SolaimanLipi', DejaVu Sans, sans-serif;
        }

        .total-row {
            font-weight: bold;
            background: #eef2ff;
        }

        .summary-box {
            margin-bottom: 10px;
            text-align: center;
            font-size: 13px;
        }

        h4 {
            margin-top: 15px;
        }
        
        h1, h2, h3, h4, p, span, div {
            font-family: 'SolaimanLipi', DejaVu Sans, sans-serif !important;
            text-align: center;
        }
    </style>
</head>

<body>

<!-- HEADER (UNCHANGED) -->
<table style="width:100%; border:none; margin-bottom:10px;">
    <tr>

        <td style="width:10%; border:none; text-align:left; vertical-align:top;">
            @php $logo = public_path('images/logo.png'); @endphp

            @if(file_exists($logo))
                <img src="file://{{ $logo }}" style="width:80px; height:50px;">
            @endif
        </td>

        <td style="width:80%; border:none; text-align:center; vertical-align:middle;">

            <h1 style="margin:0; font-size:18px;">
                {{ $company->company_name_bangla ?? 'Company Name' }}
            </h1>

            <p style="margin:2px 0; font-size:12px;">
                {{ $company->company_address_bangla ?? '' }}
            </p>

            <p style="margin:2px 0; font-size:12px;">
                মোবাইল: {{ $company->company_phone_bangla ?? '' }}
            </p>

        </td>

        <td style="width:10%; border:none;"></td>
    </tr>
</table>

<hr>

<h3 style="text-align:center; font-size:16px; margin:0px;">লাভ-ক্ষতি রিপোর্ট</h3>

<p style="text-align:center; font-size:12px; margin:1px;">
{{ $startDate->format('d M, Y') }} - {{ $endDate->format('d M, Y') }}
</p>
<hr>
@php
    $newTotalCost = $newProducts->sum('total_cost');
    $newTotalSale = $newProducts->sum('total_sale');
    $newTotalQty  = $newProducts->sum('total_qty');

    $oldTotalCost = $oldProducts->sum('total_cost');
    $oldTotalSale = $oldProducts->sum('total_sale');
    $oldTotalQty  = $oldProducts->sum('total_qty');

    $grandTotalCost = $newTotalCost + $oldTotalCost;
    $grandTotalSale = $newTotalSale + $oldTotalSale;

    $grossProfit = $grandTotalSale - $grandTotalCost;
    $totalExpense = $expenses + $transport;
    $netProfitFinal = $grossProfit - $totalExpense;
@endphp

<!-- SUMMARY (UNCHANGED) -->
<div class="summary-box">
    <strong>মোট বিক্রয় (ডিসকাউন্ট সহ):</strong> {{ number_format($totalSaleWithDiscount,2) }} |
    <strong>মোট ডিসকাউন্ট:</strong> {{ number_format($totalDiscount,2) }} |
    <strong>মোট বিক্রয় (ডিসকাউন্ট ছাড়া):</strong> {{ number_format($totalSaleWithoutDiscount,2) }} <br>
    <strong>মোট খরচ:</strong> {{ number_format($totalCost,2) }} |
    <strong>মোট ব্যয়:</strong> {{ number_format($expenses + $transport,2) }} <br>
    <strong>মোট লাভ (ডিসকাউন্ট ছাড়া):</strong> {{ number_format($grossProfitWithoutDiscount,2) }} |
    <strong>নিট লাভ:</strong>
    <b style="color: {{ $netProfitFinal >= 0 ? 'green' : 'red' }}">
        {{ number_format($netProfitFinal,2) }} /= টাকা
    </b>
</div>

<!-- NEW PRODUCTS (UNCHANGED) -->
<h4>নতুন পণ্য</h4>
<table>
    <thead>
        <tr>
            <th>নাম</th>
            <th>পরিমাণ</th>
            <th>ক্রয়মূল্য</th>
            <th>বিক্রয়মূল্য (ডিস্কাউন্ট ছাড়া)</th>
            <th>ডিসকাউন্ট</th>
            <th>নেট বিক্রয়</th>
            <th>লাভ (ডিস্কাউন্ট ছাড়া)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($newProducts as $p)
        <tr>
            <td>{{ $p->product_name }}</td>
            <td>{{ $p->total_qty }}</td>
            <td>{{ number_format($p->total_cost,2) }}</td>
            <td>{{ number_format($p->total_sale_without_discount,2) }}</td>
            <td>{{ number_format($p->product_discount + $p->sale_discount,2) }}</td>
            <td>{{ number_format($p->total_sale,2) }}</td>
            <td style="color: {{ $p->profit_without_discount >= 0 ? 'green' : 'red' }}">
                {{ number_format($p->profit_without_discount,2) }}
            </td>
        </tr>
        @empty
        <tr><td colspan="7">কোনো তথ্য নেই</td></tr>
        @endforelse

        <tr class="total-row">
            <td>মোট</td>
            <td>{{ $newProducts->sum('total_qty') }}</td>
            <td>{{ number_format($newProducts->sum('total_cost'),2) }}</td>
            <td>{{ number_format($newProducts->sum('total_sale_without_discount'),2) }}</td>
            <td>{{ number_format($newProducts->sum('product_discount') + $newProducts->sum('sale_discount'),2) }}</td>
            <td>{{ number_format($newProducts->sum('total_sale'),2) }}</td>
            <td>{{ number_format($newProducts->sum('profit_without_discount'),2) }}</td>
        </tr>
    </tbody>
</table>

<!-- OLD PRODUCTS (UNCHANGED) -->
<h4>পুরাতন পণ্য</h4>
<table>
    <thead>
        <tr>
            <th>নাম</th>
            <th>পরিমাণ</th>
            <th>ক্রয়মূল্য</th>
            <th>বিক্রয়মূল্য (ডিস্কাউন্ট ছাড়া)</th>
            <th>ডিসকাউন্ট</th>
            <th>নেট বিক্রয়</th>
            <th>লাভ (ডিস্কাউন্ট ছাড়া)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($oldProducts as $p)
        <tr>
            <td>{{ $p->product_name }}</td>
            <td>{{ $p->total_qty }}</td>
            <td>{{ number_format($p->total_cost,2) }}</td>
            <td>{{ number_format($p->total_sale_without_discount,2) }}</td>
            <td>{{ number_format($p->product_discount + $p->sale_discount,2) }}</td>
            <td>{{ number_format($p->total_sale,2) }}</td>
            <td style="color: {{ $p->profit_without_discount >= 0 ? 'green' : 'red' }}">
                {{ number_format($p->profit_without_discount,2) }}
            </td>
        </tr>
        @empty
        <tr><td colspan="7">কোনো তথ্য নেই</td></tr>
        @endforelse

        <tr class="total-row">
            <td>মোট</td>
            <td>{{ $oldProducts->sum('total_qty') }}</td>
            <td>{{ number_format($oldProducts->sum('total_cost'),2) }}</td>
            <td>{{ number_format($oldProducts->sum('total_sale_without_discount'),2) }}</td>
            <td>{{ number_format($oldProducts->sum('product_discount') + $oldProducts->sum('sale_discount'),2) }}</td>
            <td>{{ number_format($oldProducts->sum('total_sale'),2) }}</td>
            <td>{{ number_format($oldProducts->sum('profit_without_discount'),2) }}</td>
        </tr>
    </tbody>
</table>

<!-- EXPENSE (UNCHANGED) -->
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
        <td>{{ number_format($expenses + $transport,2) }}</td>
    </tr>
</table>

<!-- FINAL -->
<h3>
নিট লাভ/ক্ষতি (ডিসকাউন্ট বাদ দিয়ে):
<span style="color: {{ $netProfitFinal >= 0 ? 'green' : 'red' }}">
    {{ number_format($netProfitFinal,2) }} টাকা
</span>
</h3>

</body>
</html>