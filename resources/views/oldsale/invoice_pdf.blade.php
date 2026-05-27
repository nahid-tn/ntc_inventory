<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Old Product Sale PDF</title>
<style>
    @page {
        size: A4 portrait;
        margin: 12.7mm; /* 0.5 inch */
    }

    body {
        font-family: "Courier New", Courier, monospace;
        font-size: 12px;
        margin: 0;
        padding: 0;
    }

    .report {
        width: 95%;
        height: 112.8mm; /* fixed height */
        padding: 5mm;
        border: .5px solid #000;
        box-sizing: border-box;
        margin-bottom: 12mm; /* gap between reports */
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }

    h2, h3 {
        text-align: center;
        margin: 2mm 0;
    }

    h3 {
        text-align: center;
        margin: 2mm 0;
        padding-bottom: 1mm;
        border-bottom: .5px solid #000;
    }
    .company_info {
        text-align: center;
        margin: 0mm;
        padding: 0mm;
        line-height: 0; 
        border-bottom: .5px solid #000;
    }

    .info{
        margin: 0mm;
        padding: 0mm;
        line-height: .5; 
        border-bottom: .5px solid #000;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 4mm;
        font-size: 11px;
    }

    th, td {
        border: 0.5px solid #000;
        padding: 5px 4px;
    }

    th {
        background: #f2f2f2;
    }

    .text-right { text-align: right; }

    .no-bg th, .no-bg td {
        background: transparent !important;
    }

    .signature {
        width: 40%;
        text-align: center;
        margin-top: 60px;
        border-top: 1px solid #000;
    }
</style>
</head>
<body>

<!-- Top half: Challan -->
<div class="report">
    <div class="company_info">
        <h2>Nafis Trade Corporation</h2>
        <h4>Kalibar Road, Monirampur, Shahzadpur, Sirajgonj</h4>
        <h4>Mobile: 01712-407042</h4>
</div>
<div class="info">
    <h3>Delivery Challan</h3>
    <p><strong>Invoice No:</strong> {{ $sale->invoice_no }}</p>
    <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($sale->sale_date)->format('d-M-Y') }}</p>
    <p><strong>Customer:</strong> {{ $sale->customer->customer_name ?? '-' }} | {{ $sale->customer->mobile ?? '-' }}</p>
    <p><strong>Customer Address:</strong> {{ $sale->customer->address ?? '-' }}</p>
</div>
    <h3>Products Details</h3>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Unit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->details as $d)
            <tr>
                <td>{{ $d->product->product_name ?? '' }}</td>
                <td>{{ $d->qty }}</td>
                <td>{{ $d->unit }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature">Authorized Signature</div>
</div>

<!-- Bottom half: Invoice Copy -->
<div class="report">
    <div class="company_info">
        <h2>Nafis Trade Corporation</h2>
        <h4>Kalibar Road, Monirampur, Shahzadpur, Sirajgonj</h4>
        <h4>Mobile: 01712-407042</h4>
</div>
<div class="info">
    <h3>Invoice</h3>
    <p><strong>Invoice No:</strong> {{ $sale->invoice_no }}</p>
    <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($sale->sale_date)->format('d-M-Y') }}</p>
    <p><strong>Customer:</strong> {{ $sale->customer->customer_name ?? '-' }} | {{ $sale->customer->mobile ?? '-' }}</p>
    <p><strong>Customer Address:</strong> {{ $sale->customer->address ?? '-' }}</p>
</div>
    <h3>Products Details</h3>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Unit</th>
                <th>Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->details as $d)
            <tr>
                <td>{{ $d->product->product_name ?? '' }}</td>
                <td>{{ $d->qty }}</td>
                <td>{{ $d->unit }}</td>
                <td class="text-right">{{ number_format($d->price,2) }}</td>
                <td class="text-right">{{ number_format($d->subtotal,2) }}</td>
            </tr>
            @endforeach

            <tr class="no-bg"><td colspan="5"></td></tr>
            <tr class="no-bg">
                <th>Grand Total</th>
                <td class="text-right" colspan="4">{{ number_format($sale->subtotal,2) }}</td>
            </tr>
            <tr class="no-bg">
                <th>Paid</th>
                <td class="text-right" colspan="4">{{ number_format($sale->paid_amount,2) }}</td>
            </tr>
            <tr class="no-bg">
                <th>Due</th>
                <td class="text-right" colspan="4">{{ number_format($sale->due_amount,2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="signature">Authorized Signature</div>
</div>

</body>
</html>