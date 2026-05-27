{{-- resources/views/purchase_returns/report_pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Purchase Return Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h2 { text-align: center; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 5px; text-align: center; }
        /* th { background: #f2f2f2; } */
        .supplier-info td { border: none; text-align: left; padding: 3px 0; }
        .nested-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .nested-table th, .nested-table td { border: 1px solid #000; padding: 3px; font-size: 10px; text-align: center; }
        .transaction-space { height: 8px; }
        .grand-total { font-weight: bold; }
        .text-left { text-align: left !important; }
        .text-right { text-align: right !important; }
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
<h2>Purchase Return Report</h2>

{{-- Supplier Info --}}
@if($supplier)
    <table class="supplier-info">
        <tr>
            <td><b>Supplier:</b> {{ $supplier->supplier_name }}</td>
            <td><b>Mobile:</b> {{ $supplier->mobile ?? '-' }}</td>
            <td><b>Address:</b> {{ $supplier->address ?? '-' }}</td>
        </tr>
        <tr>
            <td colspan="3"><b>Period:</b> {{ $from_date }} to {{ $to_date }}</td>
        </tr>
    </table>
@else
    <p style="text-align:center">
        <b>Period:</b> {{ $from_date }} to {{ $to_date }}
    </p>
@endif

{{-- Each Return --}}
@foreach($returns as $r)
    <table>
        <thead>
            <tr>
                <th colspan="7" style="text-align:left">
                    Return Invoice: {{ $r->invoice_no }} | Date: {{ \Carbon\Carbon::parse($r->return_date)->format('d-m-Y') }}
                </th>
            </tr>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Unit</th>
                <th>Price</th>
                <th>Total</th>
                <th>Reason</th>
            </tr>
        </thead>
        <tbody>
        @foreach($r->items as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td class="text-left">{{ $item->product->product_name ?? '-' }}</td>
                <td>{{ number_format($item->qty, 2) }}</td>
                <td>{{ $item->unit ?? '-' }}</td>
                <td class="text-right">{{ number_format($item->price, 2) }}</td>
                <td class="text-right">{{ number_format($item->total, 2) }}</td>
                <td class="text-left">{{ $item->reason ?? '-' }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr class="grand-total">
                <td colspan="5" style="text-align:right">Sub Total</td>
                <td class="text-right">{{ number_format($r->items->sum('total'), 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    <div class="transaction-space"></div>
@endforeach

{{-- Overall Grand Total --}}
<table>
    <tr class="grand-total">
        <td style="text-align:right;width:80%">
            Overall Grand Total
        </td>
        <td style="width:20%;text-align:center">
            {{ number_format($overallTotal,2) }}
        </td>
    </tr>
</table>

</body>
</html>
