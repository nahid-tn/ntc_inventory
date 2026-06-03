<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Invoice PDF</title>

<style>
    body {
        font-family: dejavusans, sans-serif;
        font-size: 12px;
    }

    .container {
        width: 100%;
    }

    .box {
        padding: 10px;
        margin-bottom: 15px;
    }

    h2, h3 {
        text-align: center;
        margin: 5px 0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    table, th, td {
        border: 1px solid #000;
    }

    th, td {
        padding: 5px;
    }

    th {
        background: #f2f2f2;
    }

    .text-right {
        text-align: right;
    }

    .text-center {
        text-align: center;
    }

    .signature {
        margin-top: 30px;
        text-align: center;
        width: 40%;
        border-top: 1px solid #000;
        float: right;
    }

    /* IMPORTANT for mPDF */
    .page-break {
        page-break-before: always;
    }

</style>
</head>

<body>

<div class="container">

    <!-- ================= CHALLAN (NO MONEY) ================= -->
    <div class="box">

        <h2>DELIVERY CHALLAN</h2>

        <strong>Invoice No:</strong> {{ $sale->invoice_no }}<br>
        <strong>Customer:</strong> {{ $sale->customer->customer_name ?? '-' }}<br>
        <strong>Address:</strong> {{ $sale->customer->address ?? '-' }}<br>
        <strong>Mobile:</strong> {{ $sale->customer->mobile ?? '-' }}<br>
        <strong>Date:</strong> {{ \Carbon\Carbon::parse($sale->sale_date)->format('d-M-Y') }}<br>

        <h3>Product List</h3>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th class="text-center">Qty</th>
                    <th class="text-center">Unit</th>
                </tr>
            </thead>

            <tbody>
                @foreach($sale->details as $d)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $d->product->product_name }}</td>
                    <td class="text-center">{{ $d->qty }}</td>
                    <td class="text-center">{{ $d->unit }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="signature">
            Authorized Signature
        </div>

    </div>

    <!-- ================= INVOICE (WITH MONEY) ================= -->
    <div class="box page-break">

        <h2>INVOICE</h2>

        <strong>Invoice No:</strong> {{ $sale->invoice_no }}<br>
        <strong>Customer:</strong> {{ $sale->customer->customer_name ?? '-' }}<br>
        <strong>Address:</strong> {{ $sale->customer->address ?? '-' }}<br>
        <strong>Mobile:</strong> {{ $sale->customer->mobile ?? '-' }}<br>
        <strong>Date:</strong> {{ \Carbon\Carbon::parse($sale->sale_date)->format('d-M-Y') }}<br>

        <h3>Product Details</h3>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th class="text-center">Qty</th>
                    <th class="text-center">Unit</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>

            <tbody>
                @foreach($sale->details as $d)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $d->product->product_name }}</td>
                    <td class="text-center">{{ $d->qty }}</td>
                    <td class="text-center">{{ $d->unit }}</td>
                    <td class="text-right">{{ number_format($d->price,2) }}</td>
                    <td class="text-right">{{ number_format($d->subtotal,2) }}</td>
                </tr>
                @endforeach

                <tr>
                    <th colspan="5" class="text-right">Subtotal</th>
                    <th class="text-right">{{ number_format($sale->subtotal,2) }}</th>
                </tr>

                <tr>
                    <th colspan="5" class="text-right">Paid</th>
                    <th class="text-right">{{ number_format($sale->paid_amount,2) }}</th>
                </tr>

                <tr>
                    <th colspan="5" class="text-right">Due</th>
                    <th class="text-right">{{ number_format($sale->due_amount,2) }}</th>
                </tr>

            </tbody>
        </table>

        <div class="signature">
            Authorized Signature
        </div>

    </div>

</div>

</body>
</html>