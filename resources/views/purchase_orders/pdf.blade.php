<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Order PDF</title>

    <style>
        .header{
            display:flex;
            align-items:center;
            border-bottom:1px solid #333;
            padding-bottom:5px;
            margin-bottom:5px;
        }
        .logo{width:60px;margin-right:12px;}
        .company-info{font-size:12px;width:500px;}

        body {
            font-family: DejaVu Sans;
            font-size: 12px;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 6px;
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        /* PRODUCT TABLE */
        .product-table th,
        .product-table td {
            border: 1px solid #ddd;
        }

        .product-table th {
            background: #f3f4f6;
        }

        /* SUMMARY */
        .summary td {
            border: none;
            text-align: right;
            padding: 5px;
        }

        .summary tr td:first-child {
            text-align: left;
        }

        .total-row {
            font-weight: bold;
            background: #f1f5f9;
        }
        .footer{
            position:fixed;
            bottom:0mm;
            left:0;
            right:0;
            text-align:center;
            font-size:9px;
            color:#666;
        }
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

<div style="width: 100%; display: flex; justify-content: space-between; margin-bottom: 10px;">
    <h4 style="text-align:center; margin:3px 0;">Purchase Order</h4>
    <hr>
    <!-- Left Side -->
    <div style="width: 70%; float: left;">
        
        <p style="text-align:left; font-size:12px;">
            <b>Supplier:</b> {{ $purchaseOrder->supplier->supplier_name ?? '' }}<br>
            <b>Mobile:</b> {{ $purchaseOrder->supplier->mobile ?? '' }}<br>
            <b>Address:</b> {{ $purchaseOrder->supplier->address ?? '' }}<br>
        </p>
    </div>

    <!-- Right Side -->
    <div style="width: 30%; float: right; text-align: left; font-size: 12px; margin-top: 10px;">
        <b>PO Number:</b> {{ $purchaseOrder->po_number }}<br>
        <b>Order Date:</b> {{ $purchaseOrder->order_date }}<br>
        <b>Expected Date:</b> {{ $purchaseOrder->expected_date ?? '-' }}
    </div>
</div>

<div style="clear: both;"></div>
<hr>

<!-- ================= PRODUCT TABLE ================= -->
<table class="product-table">
    <thead>
        <tr>
            <th>#</th>
            <th class="text-left">Product</th>
            <th>Qty</th>
            <th>Unit</th>
            <th>Price</th>
            <th>Total</th>
        </tr>
    </thead>

    <tbody>
        @php $i = 1; @endphp

        @foreach($purchaseOrder->details as $item)
        <tr>
            <td>{{ $i++ }}</td>
            <td class="text-left">
                {{ $item->product->product_name ?? '' }}
            </td>
            <td>{{ $item->qty }}</td>
            <td>{{ $item->unit }}</td>
            <!-- <td>{{ number_format($item->price, 2) }}</td>
            <td>{{ number_format($item->total, 2) }}</td> -->
            <td></td>
            <td></td>
        </tr>
        @endforeach
    </tbody>
</table>

<!-- ================= SUMMARY ================= -->
<table class="summary">

    @if(($purchaseOrder->subtotal ?? 0) != 0)
    <tr>
        <td>Subtotal:</td>
        <td>{{ number_format($purchaseOrder->subtotal, 2) }}</td>
    </tr>
    @endif

    @if(($purchaseOrder->discount ?? 0) != 0)
    <tr>
        <td>Discount:</td>
        <td>{{ number_format($purchaseOrder->discount, 2) }}</td>
    </tr>
    @endif

    @if(($purchaseOrder->shipping ?? 0) != 0)
    <tr>
        <td>Shipping:</td>
        <td>{{ number_format($purchaseOrder->shipping, 2) }}</td>
    </tr>
    @endif

    @if(($purchaseOrder->tax ?? 0) != 0)
    <tr>
        <td>Tax:</td>
        <td>{{ number_format($purchaseOrder->tax, 2) }}</td>
    </tr>
    @endif

    @if(($purchaseOrder->total_amount ?? 0) != 0)
    <tr class="total-row">
        <td><b>Total</b></td>
        <!-- <td><b>{{ number_format($purchaseOrder->total_amount, 2) }}</b></td> -->
         <td><b></b></td>
    </tr>
    @endif

</table>

<!-- Footer -->
<div class="footer">
    Page <span class="page-number"></span>
    | Generated : {{ now()->format('d-m-Y H:i') }}
</div>

</body>
</html>