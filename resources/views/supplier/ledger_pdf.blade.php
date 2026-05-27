<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Supplier Ledger</title>

<style>
@page {size: A4 portrait; margin: 15mm 15mm 25mm 15mm;}
body {font-family: DejaVu Sans, Microsoft Yi Baiti, sans-serif;font-size: 10px;}

.header{
    display:flex;
    align-items:center;
    border-bottom:1px solid #333;
    padding-bottom:5px;
    margin-bottom:5px;
}
.logo{width:60px;margin-right:12px;}
.company-info{font-size:9px;width:500px;}

table{width:100%;border-collapse:collapse;}
thead{display:table-header-group;}
th,td{border:1px solid #333;padding:3px;}
th{background:#f2f2f2;}
tr{page-break-inside:avoid;}

.text-right{text-align:right;}
.balance-cell{font-weight:bold;text-align:right;}

.items-holder{padding:2px 4px;}
.nested-table{width:100%;font-size:7.5px;}
.nested-table th,.nested-table td{border:none;padding:1px;}

.summary{margin-top:6px;font-size:10px;}

.footer{
    position:fixed;
    bottom:0mm;
    left:0;
    right:0;
    text-align:center;
    font-size:9px;
    color:#666;
}
.page-number:after{content:counter(page);}
</style>
</head>

<body>

<!-- Header -->
<div class="header">
    <img src="{{ public_path('images/logo.png') }}" class="logo">

    <div class="company-info">
        <strong>Nafis Trade Corporation</strong><br>
        Kalibari Road, (Nearby Shaheen School)<br>
        Monirampur, Shahzadpur, Sirajgonj<br>
        Phone: 01712407042
    </div>
</div>

<h4 style="text-align:center;margin:3px 0;">Supplier Ledger</h4>
<p style="text-align:center;font-size:10px;">
    {{ $supplier->supplier_name ?? '' }} <br>
    Period :
    {{ $from ? \Carbon\Carbon::parse($from)->format('d-m-Y') : 'Start' }}
    to
    {{ $to ? \Carbon\Carbon::parse($to)->format('d-m-Y') : 'End' }}
</p>

@php
$totalDebit = 0;
$totalCredit = 0;
@endphp

<table>

    <!-- Opening -->
    <tr>
        <td colspan="7" style="font-weight:bold;">
            Opening Balance :
            {{ $openingBalance >= 0
                ? number_format($openingBalance,2)
                : '-'.number_format(abs($openingBalance),2)
            }}
        </td>
    </tr>

    <thead>
    <tr>
        <th width="10%">Date</th>
        <th width="12%">Type</th>
        <th width="12%">Voucher</th>
        <th width="35%">Items</th>
        <th width="10%">Debit</th>
        <th width="10%">Credit</th>
        <th width="11%">Balance</th>
    </tr>
    </thead>

    <tbody>

    @foreach($ledger as $entry)

    @php
    $totalDebit += $entry['debit'] ?? 0;
    $totalCredit += $entry['credit'] ?? 0;
    $running = $entry['running_balance'] ?? 0;
    @endphp

    <tr>
        <td>{{ \Carbon\Carbon::parse($entry['date'])->format('d-m-Y') }}</td>
        <td>{{ $entry['type'] }}</td>
        <!-- <td>{{ $entry['voucher_no'] ?? '' }}</td> -->

        <!-- Items -->
        <td colspan="2" class="items-holder">
            @if(!empty($entry['items']) && $entry['items']->count())
            <table class="nested-table">
                <!-- <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Rate</th>
                </tr> -->
                @foreach($entry['items'] as $item)
                <tr>
                    <td>{{ $item['name'] }}</td>
                    <td class="text-right">{{ $item['qty'] }}</td>
                    <td class="text-right">{{ number_format($item['rate'],2) }}</td>
                </tr>
                @endforeach
            </table>
            @endif
        </td>

        <!-- Debit -->
        <td class="text-right">{{ number_format($entry['debit'] ?? 0,2) }}</td>

        <!-- Credit -->
        <td class="text-right">{{ number_format($entry['credit'] ?? 0,2) }}</td>

        <!-- Balance -->
        <td class="balance-cell">
            {{ $running >= 0 ? number_format($running,2) : '-'.number_format(abs($running),2) }}
        </td>
    </tr>

    <!-- Narration -->
    @if(!empty($entry['narration']))
    <tr>
        <td colspan="7" style="font-style:italic;font-size:9px;">
            Narration : {{ $entry['narration'] }}
        </td>
    </tr>
    @endif

    @endforeach

    <!-- Closing -->
    @php
    $closing = $totalDebit - $totalCredit;
    @endphp

    <tr>
        <td colspan="7" style="font-weight:bold;text-align:right;">
            Closing Balance :
            {{ $closing >= 0 ? number_format($closing,2) : '-'.number_format(abs($closing),2) }}
        </td>
    </tr>

    </tbody>
</table>

<!-- Summary -->
<div class="summary">
    <b>Total Debit :</b> {{ number_format($totalDebit,2) }} &nbsp;&nbsp;
    <b>Total Credit :</b> {{ number_format($totalCredit,2) }} &nbsp;&nbsp;
    <b>Final Balance :</b> {{ $closing >= 0 ? number_format($closing,2) : '-'.number_format(abs($closing),2) }}
</div>

<!-- Footer -->
<div class="footer">
    Page <span class="page-number"></span>
    | Generated : {{ now()->format('d-m-Y H:i') }}
</div>

</body>
</html>