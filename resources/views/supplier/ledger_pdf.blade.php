<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Supplier Ledger</title>

<style>
body{
    font-family: solaimanlipi;
    font-size:12px;
}
h1{
    font-family: solaimanlipi;
    font-size:18px;
    margin:0;
    white-space: nowrap;
    word-break: keep-all;
    overflow: visible;
}
table {width:100%;border-collapse:collapse;}

th,td {border:.25px solid #000;padding:4px;}

th {background:#f2f2f2;}

.right {text-align:right;}
.center {text-align:center;}

.items-box{
    font-size:8px;
    font-style: italic;
}

.item-row{
    display:block;
    padding:2px 0;
    /* border-bottom:1px dotted #999; */
}

.item-name{
    display:inline-block;
    width:auto;
}

.item-qty{
    display:inline-block;
    width:20%;
    text-align:right;
}

.item-rate{
    display:inline-block;
    width:25%;
    text-align:right;
}
.badge-advance {
    display: inline-block;
    padding: 1px 6px;
    font-size: 9px;
    font-weight: bold;
    color: #856404;
    background-color: #fff3cd;
    border-radius: 3px;
    margin-left: 4px;
}

.text-muted {
    color: #6c757d;
}
</style>
</head>

<body>
<!-- HEADER -->
<table style="width:100%; border:none; margin-bottom:10px;">
    <tr>

        <td style="width:10%; border:none; text-align:left; vertical-align:top;">
            @php $logo = public_path('images/logo.png'); @endphp

            @if(file_exists($logo))
                <img src="file://{{ $logo }}" style="width:80px; height:50px;">
            @endif
        </td>

        <td style="width:80%; border:none; text-align:center; vertical-align:middle;">

            <p style=" margin:0; font-family:solaimanlipi; font-size:20px; width:100%; white-space:nowrap; word-break:keep-all;">
                {{ $company->company_name_bangla ?? 'Company Name' }}
        </p>

            <p style="margin:2px 0; font-size:14px;">
                {{ $company->company_address_bangla ?? '' }}
            </p>

            <p style="margin:2px 0; font-size:14px;">
                মোবাইল: {{ $company->company_phone_bangla ?? '' }}
            </p>

           

        </td>

        <td style="width:10%; border:none;"></td>
    </tr>
</table>

<hr>


<p style="text-align:center;"><strong>
Supplier: {{ $supplier->supplier_name }} <br>
Start Date: {{ $from ? \Carbon\Carbon::parse($from)->format('Y-m-d') : '' }} <br>
Ending Date: {{ $to ? \Carbon\Carbon::parse($to)->format('Y-m-d') : '' }}</strong>
</p>
<hr>
<h3 style="text-align:center;">Ledger</h3>
<table>

<tr>
<td colspan="7"><b>Opening:</b> {{ number_format($openingBalance,2) }}</td>
</tr>

<tr>
<th style="width:13%;">Date</th>
<th style="width:10%;">Type</th>
<th style="width:13%;">Voucher</th>
<th style="width:30%;">Items</th>
<th class="right" style="width:11%;">Debit</th>
<th class="right" style="width:11%;">Credit</th>
<th class="right" style="width:12%;">Balance</th>
</tr>

@foreach($ledger as $row)

<tr>
<td class="center">
    {{ \Carbon\Carbon::parse($row['date'])->format('d-m-Y') }}
</td>
<td>{{ $row['type'] }}</td>
<td>{{ $row['voucher'] }}</td>

<td>
    <div class="items-box">
        @php
            // ফিল্টার করুন শুধুমাত্র Advance Payment 1 0.00 বাদ দিতে
            $filteredItems = collect($row['items'])->filter(function($item) {
                return !(isset($item['name']) && 
                        strpos($item['name'], 'Advance Payment') !== false && 
                        ($item['qty'] ?? 1) == 1 && 
                        ($item['rate'] ?? 0) == 0);
            });
        @endphp

        @if($filteredItems->isNotEmpty())
            @foreach($filteredItems as $item)
                <div class="item-row">
                    <span class="item-name">
                        {{ $item['name'] }}
                        @if(isset($item['is_advance']) && $item['is_advance'])
                            <span class="badge-advance">(Advance)</span>
                        @endif
                    </span>
                    <span class="item-qty">{{ rtrim(rtrim(number_format($item['qty'] ?? 1, 2, '.', ''), '0'), '.') }}</span>
                    <span class="item-rate">{{ number_format($item['rate'] ?? 0, 2) }}</span>
                </div>
            @endforeach
        @else
            <div class="item-row">
                <span class="item-name text-muted"> — </span>
            </div>
        @endif
    </div>
</td>

<td class="right">@if(($row['debit'] ?? 0) != 0){{ number_format($row['debit'],2) }}@endif</td>
<td class="right">@if(($row['credit'] ?? 0) != 0){{ number_format($row['credit'],2) }}@endif</td>
<td class="right">{{ number_format($row['running_balance'],2) }}</td>
</tr>

@endforeach

<tr>
<td colspan="7" class="right">
<b>Total Debit:</b> {{ number_format($totalDebit,2) }} |
<b>Total Credit:</b> {{ number_format($totalCredit,2) }}
</td>
</tr>
@php
$closing = ($openingBalance + $totalCredit) - $totalDebit;
@endphp

<tr>
    <td colspan="7" class="right" style="font-weight:bold;">
        Closing Balance : {{ number_format($closing,2) }}
    </td>
</tr>
</table>
<br>
<p style="text-align:center; font-size:8px;">Generated on {{ \Carbon\Carbon::now()->format('d-m-Y') }}</p>
<div style="position:fixed; width:100%; text-align:center; font-size:10px;">
    Powered by NTC Nafis Trade Corporation | www.ntcnafistrade.com | 01712-407042
</div>
</body>
</html>