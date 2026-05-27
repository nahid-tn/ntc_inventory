<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sale Replacement Invoice</title>
    <style>
        @page { margin: 50px 50px; }
        @font-face {
            font-family: 'SolaimanLipi';
            src: url("{{ public_path('fonts/SolaimanLipi.ttf') }}") format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        body {
            font-family: 'SolaimanLipi', sans-serif;
            font-size: 14px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        h2, h3 {
            text-align: center;
            margin: 10px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #999;
            padding: 8px;
            text-align: center;
        }

        /* th {
            background-color: #f2f2f2;
        } */

        .no-border td, .no-border th {
            border: none;
            padding: 0;
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        /* Signature section */
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 60px;
        }

        .signatures div {
            width: 45%;
            text-align: center;
        }

        .signatures div p {
            margin-top: 60px;
            border-top: 1px solid #333;
        }
    </style>
</head>
<body>

<h2>Sale Replacement Info</h2>

<table class="no-border">
    <tr>
        <th>Invoice No:</th>
        <td>{{ $replacement->sale?->invoice_no ?? '-' }}</td>
    </tr>
    <tr>
        <th>Sale Date:</th>
        <td>
            {{ $replacement->sale?->sale_date ? \Carbon\Carbon::parse($replacement->sale->sale_date)->format('d-M-Y') : '-' }} |
            Replacement Date: {{ $replacement->replaceMent_date ? \Carbon\Carbon::parse($replacement->replaceMent_date)->format('d-M-Y') : '-' }}
        </td>
    </tr>
    <tr>
        <th>Customer:</th>
        <td>
            {{ $replacement->sale?->customer?->customer_name ?? '-' }} |
            {{ $replacement->sale?->customer?->mobile ?? '-' }} |
            {{ $replacement->sale?->customer?->address ?? '-' }}
        </td>
    </tr>
</table>

    {{-- Old Product --}}
    <h3>Old Product</h3>
    <table>
        <tr>
            <th>Product Name</th>
            <td>{{ $oldSaleDetail?->product?->product_name ?? '-' }}</td>
        </tr>
        <tr>
            <th>Quantity</th>
            <td>{{ $oldSaleDetail?->qty ?? 0 }} | {{ $oldSaleDetail?->unit ?? '-' }}</td>
        </tr>
        <tr>
            <th>Price</th>
            <td>{{ number_format($oldSaleDetail?->price ?? 0, 2) }}</td>
        </tr>
        <tr>
            <th>Total</th>
            <td>{{ number_format($oldSaleDetail?->total ?? 0, 2) }}</td>
        </tr>
    </table>

<h3>New Product</h3>
<table>
    <tr>
        <th>Product Name</th>
        <td>{{ $replacement->newProduct?->product_name ?? '-' }}</td>
    </tr>
    <tr>
        <th>Quantity</th>
        <td>{{ $replacement->qty ?? 0 }} | {{ $replacement->unit ?? '-' }}</td>
    </tr>
    <tr>
        <th>Price</th>
        <td class="text-right">{{ number_format($replacement->price ?? 0, 2) }}</td>
    </tr>
    <tr>
        <th>Total</th>
        <td class="text-right">{{ number_format($replacement->total ?? 0, 2) }}</td>
    </tr>
    <tr>
        <th>Replacement Fee</th>
        <td class="text-right">{{ number_format($replacement->replaceMent_fee ?? 0, 2) }}</td>
    </tr>
</table>

<div class="signatures">
    <div>
        <p>Prepared By</p>
    </div>
    <div>
        <p>Approved By</p>
    </div>
</div>

</body>
</html>