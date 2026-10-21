<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Chronological Purchase Report</title>

<style>
body{font-family: Arial,sans-serif;font-size:12px;}
h2{text-align:center;margin:5px 0;}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
    table-layout:fixed;
}

table,th,td{
    border:1px solid #000;
}

th,td{
    padding:4px;
    text-align:center;
    word-wrap:break-word;
}

th{
    background:#f2f2f2;
    }


/* SAFE nested table for mPDF */
.nested-table{
    width:100%;
    border-collapse:collapse;
    margin-top:2px;
}

.nested-table td{
    border:1px solid #999;
    padding:3px;
    font-size:10px;
    text-align:center;
}

.transaction-space{height:6px;}

.text-right{text-align:right;}
.text-left{text-align:left;}

.positive{color:green;font-weight:bold;}
.negative{color:red;font-weight:bold;}

</style>
</head>

<body>

<h2>Chronological Purchase Report</h2>

@if($supplier)
<p><b>Supplier:</b> {{ $supplier->supplier_name }} | Period: {{ $from_date }} to {{ $to_date }}</p>
@else
<p style="text-align:center"><b>Period:</b> {{ $from_date }} to {{ $to_date }}</p>
@endif

<p><b>Opening Balance:</b> {{ number_format($openingBalance,2) }}</p>

<table>

<thead>
<tr>
    <th>Date</th>
    <th>Particulars</th>
    <th>Type</th>
    <th>Reference</th>
    <th>Debit</th>
    <th>Credit</th>
    <th>Running Balance</th>
</tr>
</thead>

<tbody>

@forelse($transactions as $tx)

<tr>

    <td>{{ \Carbon\Carbon::parse($tx['date'])->format('d-m-Y') }}</td>
    <td>{{ $tx['Particulars'] ?? '-' }}</td>
    <td>{{ $tx['type'] ?? '-' }}</td>
    <td>{{ $tx['reference'] ?? '-' }}</td>

    <td class="text-right">
        {{ !empty($tx['debit']) ? number_format($tx['debit'],2) : '-' }}
    </td>

    <td class="text-right">
        {{ !empty($tx['credit']) ? number_format($tx['credit'],2) : '-' }}
    </td>

    <td class="text-right {{ ($tx['running_balance'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
        {{ number_format($tx['running_balance'] ?? 0,2) }}
    </td>

</tr>

{{-- ================= SAFE NESTED TABLE ================= --}}
@if(!empty($tx['products']) && is_countable($tx['products']) && count($tx['products']) > 0)

<tr>
    <td colspan="7" style="padding:0;">

        <table class="nested-table">

            @foreach($tx['products'] as $p)
            <tr>
                <td class="text-left">{{ $p->product->product_name ?? '-' }}</td>
                <td>{{ $p->qty ?? 0 }} {{ $p->unit ?? '' }}</td>
                <td>{{ number_format($p->price ?? 0,2) }}</td>
                <td>{{ number_format($p->total ?? 0,2) }}</td>
            </tr>
            @endforeach

        </table>

        <div class="transaction-space"></div>

    </td>
</tr>

@endif

@empty

<tr>
    <td colspan="7">No Data Found</td>
</tr>

@endforelse

</tbody>

<tfoot>
<tr>
    <td colspan="7">
        <b>Closing Balance:</b> {{ number_format($closingBalance,2) }}
    </td>
</tr>
</tfoot>

</table>

</body>
</html>