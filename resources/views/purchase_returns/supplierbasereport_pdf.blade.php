<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
    <title>Purchase Return Report</title>
        <style>
            body{font-family: 'SolaimanLipi', DejaVu Sans, sans-serif;font-size:12px;}
            h2,h4{text-align:center;margin:0;}
            table{width:100%;border-collapse:collapse;margin-top:10px;}
            table,th,td{border:1px solid #000;}
            th,td{padding:5px;text-align:center;}
            th{background:#f2f2f2;}
            .supplier-info td{border:none;text-align:left;}
            hr{border:1px dashed #000; margin:15px 0;}
        </style>
</head>
    <body>
    <!-- Header -->
    <div style="text-align:center;">
        <h2 style="margin:0;">Nafis Trade Corporation</h2>
        <p style="margin:2px 0; font-size:12px;">
            Kalibari Road (Nearby Shaheen School), Moniramp, Shahzadpur, Sirajganj<br>
            Mobile: 01712-407042, 01551-806102
        </p>
        <hr>
    </div>
    @if($supplier)
        <table class="supplier-info">
            <tr>
                <td><b>Supplier:</b> {{ $supplier->supplier_name }}</td>
                <td><b>Mobile:</b> {{ $supplier->mobile }}</td>
                <td><b>Address:</b> {{ $supplier->address }}</td>
            </tr>
            <tr>
                <td colspan="3"><b>From:</b> {{ $from_date }} | <b>To:</b> {{ $to_date }}</td>
            </tr>
        </table>
    @endif
    @php $grandTotal = 0; @endphp
    @foreach($returns as $r)
    <table>
        <thead>
            <tr>
                <th colspan="7" style="text-align:left">
                Return No: {{ $r->return_no }} | Date: {{ \Carbon\Carbon::parse($r->return_date)->format('d-m-Y') }}
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
        <td>{{ $item->product->product_name ?? '' }}</td>
        <td>{{ $item->qty }}</td>
        <td>{{ $item->unit ?? '-' }}</td>
        <td>{{ number_format($item->price,2) }}</td>
        <td>{{ number_format($item->total,2) }}</td>
        <td>{{ $item->reason ?? '-' }}</td>
    </tr>
    @php $grandTotal += $item->total; @endphp
    @endforeach
    </tbody>
        <tfoot>
            <tr>
            <th colspan="5" align="right">Return Total</th>
            <th>{{ number_format($r->total_amount,2) }}</th>
            <th></th>
            </tr>
        </tfoot>
    </table>
    <hr>
    @endforeach
    <!-- Grand Total of all returns -->
    <table>
        <tr>
            <th style="text-align:right">Grand Total of All Returns:</th>
            <th>{{ number_format($grandTotal,2) }}</th>
        </tr>
    </table>
    </body>
</html>
