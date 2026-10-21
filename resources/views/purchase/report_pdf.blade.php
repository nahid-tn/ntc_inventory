<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Chronological Purchase Report</title>

<style>
body{font-family: Arial,sans-serif;font-size:12px;}
h2{text-align:center;margin:5px 0;}
table{width:100%;border-collapse:collapse;margin-top:10px;}
table,th,td{border:1px solid #000;}
th,td{padding:4px;text-align:center;}
th{background:#f2f2f2;}
.nested-table{width:100%;border-collapse:collapse;margin-top:2px;}
.nested-table th, .nested-table td{border:1px solid #000;padding:3px;font-size:10px;text-align:center;}
.transaction-space{height:8px;}
.grand-total{font-weight:bold;background:#eee;}
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
    <th>SL</th>
    <th>Date</th>
    <th>Type</th>
    <th>Reference</th>
    <th>Purchase</th>
    <th>Return</th>
    <th>Net</th>
    <th>Paid</th>
    <th>Running Balance</th>
</tr>
</thead>

<tbody>

@forelse($transactions as $tx)

<tr>

    <td>{{ $loop->iteration }}</td>

    <td>
        {{ isset($tx['date']) ? \Carbon\Carbon::parse($tx['date'])->format('d-m-Y') : '-' }}
    </td>

    <td>{{ $tx['type'] ?? '-' }}</td>

    <td>{{ $tx['reference'] ?? '-' }}</td>

    <td>{{ number_format($tx['purchase'] ?? 0,2) }}</td>
    <td>{{ number_format($tx['return'] ?? 0,2) }}</td>
    <td>{{ number_format($tx['net'] ?? 0,2) }}</td>
    <td>{{ number_format($tx['paid'] ?? 0,2) }}</td>

    <td>{{ number_format($tx['running_balance'] ?? 0,2) }}</td>

</tr>

{{-- ================= NESTED TABLE (UNCHANGED DESIGN) ================= --}}
@if(
    ($tx['type'] == 'Purchase' || $tx['type'] == 'Return' || $tx['type'] == 'Purchase Invoice' || $tx['type'] == 'Return Invoice')
    && !empty($tx['products'])
    && is_countable($tx['products'])
    && count($tx['products']) > 0
)

<tr>
    <td colspan="9" style="padding:0;">

        <table class="nested-table">

            <thead>
                <tr>
                    <th>SL</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>Price</th>
                    <th>Total</th>
                </tr>
            </thead>

            <tbody>

            @foreach($tx['products'] as $p)

                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $p->product->product_name ?? '-' }}</td>
                    <td>{{ $p->qty ?? 0 }}</td>
                    <td>{{ $p->unit ?? '-' }}</td>
                    <td>{{ number_format($p->price ?? 0,2) }}</td>
                    <td>{{ number_format($p->total ?? 0,2) }}</td>
                </tr>

            @endforeach

            </tbody>

        </table>

        <div class="transaction-space"></div>

    </td>
</tr>

@endif

@empty

<tr>
    <td colspan="9">No Data Found</td>
</tr>

@endforelse

</tbody>

</table>

<p><b>Closing Balance:</b> {{ number_format($closingBalance,2) }}</p>

</body>
</html>