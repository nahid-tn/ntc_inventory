<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Expense Report</title>
    <style>
        @font-face {
            font-family: 'SolaimanLipi';
            src: url("{{ public_path('fonts/SolaimanLipi.ttf') }}") format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        body { font-family: 'SolaimanLipi', sans-serif; font-size: 12px; color:#333; line-height:1.4; }
        h2 { text-align: center; margin-bottom: 10px; }
        p { margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: center; }
        th { background-color: #f2f2f2; }
        td.text-left { text-align: left; }
        td.text-right { text-align: right; }
        tfoot th, tfoot td { font-weight: bold; }
        .logo{width:60px;margin-right:12px;}
        .header{
            display:flex;
            align-items:center;
            border-bottom:1px solid #333;
            padding-bottom:5px;
            margin-bottom:5px;
        }
    </style>
</head>
<body>
<div class="header">
    <img src="{{ base_path('public/images/logo.png') }}" class="logo">
    <div class="company-info">
        <strong>Nafis Trade Corporation</strong><br>
        Kalibari Road, (Nearby Shaheen School)<br>
        Monirampur, Shahzadpur, Sirajgonj<br>
        Phone: 01712407042
    </div>
</div>
<h2>Expense Report</h2>

<p>
    <!-- <strong>Report Type:</strong> {{ ucfirst($request->type) }} <br> -->
    <strong>Expense Date:</strong> {{ $request->date }}
</p>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Date</th>
            <th>Items</th>
            <th>Payment Method</th>
            <th>Amount (tk)</th>
        </tr>
    </thead>
    <tbody>
        @php $grandTotal = 0; @endphp
        @foreach($expenses as $i => $expense)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($expense->expense_date)->format('d-m-Y') }}</td>
                <td class="text-left">
                    @foreach($expense->items as $item)
                        {{ $item->item_name }} ({{ $item->qty }} × {{ number_format($item->price,2) }}) = {{ number_format($item->subtotal,2) }} tk<br>
                    @endforeach
                </td>
                <td>{{ $expense->payment_method }}</td>
                <td class="text-right">{{ number_format($expense->total,2) }}</td>
            </tr>
            @php $grandTotal += $expense->total; @endphp
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th colspan="4" class="text-right">Total</th>
            <th class="text-right">{{ number_format($grandTotal,2) }} tk</th>
        </tr>
    </tfoot>
</table>

</body>
</html>