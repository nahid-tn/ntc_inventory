<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Chronological Purchase Report</title>
<style>
/* Page Setup */
@page {
    size: A4 portrait;
    margin: 20mm 15mm 25mm 15mm;
}
body{font-family: Arial,sans-serif;font-size:12px;}

/* Header & Company Info */
.header{
    display:flex;
    align-items:center;
    border-bottom:1px solid #333;
    padding-bottom:5px;
    margin-bottom:5px;
}
.logo{width:60px;margin-right:12px;}
.company-info{font-size:9px;width:500px;}


/* Table Styling */
table{width:100%;border-collapse:collapse;margin-top:5px;}
table,th,td{border:.5px solid #000;}
th,td{padding:4px;text-align:center;}
/* Nested Table */
.nested-table{
    width:100%;
    border-collapse:collapse;
    margin-top:1px 5px;
    table-layout:fixed;
    border:none;
    font-style:italic;
}
.nested-table th, .nested-table td{
    border:none;
    padding:2px;
    font-size:9px;
    border-bottom:1px solid #858080;
    text-align:center;
    font-style:italic;
}

/* Text Alignment & Color */
.text-right { text-align:right !important; }
.text-left { text-align:left !important; }
.positive { color: green; font-weight: bold; }
.negative { color: red; font-weight: bold; }

/* Force header on every page */
thead { display: table-header-group; }
tfoot { display: table-footer-group; }

/* Space between transactions */
.transaction-space{height:8px;}
</style>
</head>
<body>

<!-- Header -->
<div class="header">
    <img src="data:image/png;base64,{{ $logo }}" width="60">
    <div class="company-info">
        <strong>Nafis Trade Corporation</strong><br>
        Kalibari Road, (Nearby Shaheen School)<br>
        Monirampur, Shahzadpur, Sirajgonj<br>
        Phone: 01712407042
    </div>
</div>


<h2 style="text-align:center;margin:2px 0;">Supplier Base Purchase Report</h2>
@if($supplier)
<p><b>Supplier:</b> {{ $supplier->supplier_name }} | Period: {{ $from_date }} to {{ $to_date }}</p>
@else
<p style="text-align:center"><b>Period:</b> {{ $from_date }} to {{ $to_date }}</p>
@endif
<p><b>Opening Balance:</b> {{ number_format($openingBalance,2) }}</p>

<table>
<thead>
<tr>
    <th style="width:10%">Date</th>
    <th style="width:15%">Particulars</th>
    <th style="width:15%">Type</th>
    <th style="width:15%">Reference</th>
    <th style="width:12%">Debit</th>
    <th style="width:12%">Credit</th>
    <th style="width:12%">Running Balance</th>
</tr>
</thead>
<tbody>
@foreach($transactions as $tx)
<tr>
    <td>{{ \Carbon\Carbon::parse($tx['date'])->format('d-m-Y') }}</td>
    <td>{{ $tx['Particulars'] ?? '' }}</td>
    <td>{{ $tx['type'] }}</td>
    <td>{{ $tx['reference'] ?? '-' }}</td>
    <td class="text-right">{{ $tx['debit'] ? number_format($tx['debit'],2) : '-' }}</td>
    <td class="text-right">{{ $tx['credit'] ? number_format($tx['credit'],2) : '-' }}</td>
    <td class="text-right {{ $tx['running_balance'] >= 0 ? 'positive' : 'negative' }}">
        {{ number_format($tx['running_balance'],2) }}
    </td>
</tr>

@if(in_array($tx['type'], ['Purchase','Purchase Invoice','Return', 'Return Invoice']) && !empty($tx['products']))
<tr>
    <td></td>
    <td colspan="3" style="padding:0;">
        <table class="nested-table">
            
            <tbody>
            @foreach($tx['products'] as $p)
                <tr>
                    <td class="text-left" style="width:50%">{{ $p->product->product_name ?? '-' }}</td>
                    <td style="width:15%">{{ number_format($p->qty,2) }} {{ $p->unit ?? '' }}</td>
                    <td class="text-right" style="width:15%">{{ number_format($p->price,2) }}</td>
                    <td class="text-right" style="width:20%">{{ number_format($p->total,2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="transaction-space"></div>
    </td>
    <td></td>
    <td></td>
    <td></td>
</tr>
@endif

@endforeach
</tbody>
<tfoot>
<tr>
    <td colspan="7"><b>Closing Balance:</b> {{ number_format($closingBalance,2) }}</td>
</tr>

</tfoot>
</table>

</body>
<!-- <script type="text/php">
if (isset($pdf)) {

    $font = $fontMetrics->getFont("Arial", "normal");

    $pdf->page_text(
        270,   // X position (center approx)
        820,   // Y position (bottom)
        "Page {PAGE_NUM} / {PAGE_COUNT}",
        $font,
        7,
        array(0,0,0)
    );

}
</script> -->
</html>