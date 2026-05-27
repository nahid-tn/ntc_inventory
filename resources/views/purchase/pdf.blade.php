<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>Purchase Invoice - {{ $purchase->Invoice_no }}</title>

    <style>
        /* Font-face properly declared with absolute path */
        @font-face {
            font-family: 'SolaimanLipi';
            src: url('{{ public_path('fonts/SolaimanLipi.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        body {
            font-family: 'SolaimanLipi', sans-serif;
            font-size: 12px;
            line-height: 1.6; /* improves ligature rendering */
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header h2, .header h3, .header h4 {
            margin: 0;
            padding: 0;
        }

        .header p {
            margin: 2px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            page-break-inside: auto;
        }

        th, td {
            border: .5px solid #000;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word; /* prevent text overflow */
        }

        th {
            background: #f2f2f2;
        }

        .text-right {
            text-align: right;
        }

        .total {
            font-weight: bold;
        }

        /* Optional: prevent breaking ligatures */
        td, th, p, span {
            white-space: pre-wrap;
        }
    </style>
</head>
<body>

<div class="header" lang="bn">
    <h2>NAFIS TRADE CORPORATION</h2>
    <p>KALIBARI ROAD, MONIRAMPUR, SHAH ব্রেক হাড্ডি জাদপুর</p>
    <p>MOBILE- 01712407042 / 01551806102</p>
    <h3>BILL/CHALLAN</h3>
</div>

<p><strong>Invoice No:</strong> {{ $purchase->Invoice_no }} | <strong>Reference No:</strong> {{ $purchase->reference_no }}</p>
<p><strong>Supplier:</strong> {{ $purchase->supplier->supplier_name }} | {{ $purchase->supplier->address }} | <strong>Mobile No:</strong> {{ $purchase->supplier->mobile }}</p>
<p><strong>Date:</strong> {{ \Carbon\Carbon::parse($purchase->date)->format('d-M-Y') }}</p>

<h4>Products:</h4>
<table>
    <thead>
        <tr>
            <th>SlNo</th>
            <th>Product Name</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($purchase->details as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item->product->product_name }}</td>
            <td>{{ $item->qty }} {{ $item->unit }}</td>
            <td class="text-right">{{ number_format($item->price, 2) }}</td>
            <td class="text-right">{{ number_format($item->total, 2) }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="5" class="text-right total">Subtotal: {{ number_format($purchase->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td colspan="5" class="text-right total">Total Amount: {{ number_format($purchase->total_amount, 2) }}</td>
        </tr>
    </tbody>
</table>

</body>
</html>