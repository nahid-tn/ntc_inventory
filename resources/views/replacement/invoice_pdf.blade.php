<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sale Replacement Invoice</title>
    <style>
    @page {
        margin: 50px 50px;
    }

    body {
        font-family: solaimanlipi;
        font-size: 14px;
        line-height: 1.4;
        color: #333;
        margin: 0;
        padding: 0;
    }

    h1, h2, h3 {
        font-family: solaimanlipi;
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
        padding: 2px;
        text-align: center;
        font-family: solaimanlipi;
    }

    .no-border td,
    .no-border th {
        border: none;
        padding: 0;
        text-align: left;
        font-family: solaimanlipi;
    }

    .text-right {
        text-align: right;
    }
    .table-tow,
    .table-tow tr,
    .table-tow th,
    .table-tow td {
        border: none !important;
        border-collapse: collapse;
        padding: 3px;
    }
    .signatures {
        margin-top: 60px;
        width: 100%;
    }

    .signature-left {
        float: left;
        width: 45%;
        text-align: center;
    }

    .signature-right {
        float: right;
        width: 45%;
        text-align: center;
    }

    .signature-line {
        margin-top: 60px;
        border-top: 1px solid #333;
        padding-top: 5px;
    }

    .clearfix {
        clear: both;
    }
</style>
</head>
<body>
<!-- HEADER -->
<table style="width:100%; border:none; margin-bottom:10px;">
    <tr>
        <td style="width:10%; border:none; text-align:left; vertical-align:top;">
            @php $logo = public_path('images/logo.png'); @endphp

            @if(file_exists($logo))
                <img src="file://{{ $logo }}" style="width:80px; height:50px;">
            @endif
        </td>

        <td style="width:80%; border:none; text-align:center; vertical-align:middle;">

            <h1 style="margin:0; font-family:solaimanlipi;">
                {{ $companyInfo->company_name_english ?? 'নাফিস ট্রেড কর্পোরেশন' }}
            </h1>

            <p style="margin:2px 0;">
                {{ $companyInfo->company_address_english ?? '' }}
            </p>

            <p style="margin:2px 0;">
                Phone: {{ $companyInfo->company_phone_english ?? '' }}
            </p>

            <h2 style="margin:8px 0 0 0; font-family: solaimanlipi;">
                Invoice for Sale Replacement
            </h2>

        </td>

        <td style="width:10%; border:none;"></td>
    </tr>
</table>

<hr>

<table style="width:100%; margin-bottom:1px;" class="table-tow">
    <tr>
        <th style="text-align:right;">Invoice No:</th>
        <td style="text-align:left;" colspan="2">
            {{ $replacement->sale?->invoice_no ?? '-' }}
        </td>

        <th style="text-align:right;">Sale Date:</th>
        <td style="text-align:right;">
            {{ $replacement->sale?->sale_date ? \Carbon\Carbon::parse($replacement->sale->sale_date)->format('d-M-Y') : '-' }}
        </td>
    </tr>

    <tr>
        <th style="text-align:right;">Name:</th>
        <td style="text-align:left;" colspan="2">
            {{ $replacement->sale?->customer?->customer_name ?? '-' }}
        </td>

        <th style="text-align:right;">Replacement Date:</th>
        <td style="text-align:right;">
            {{ $replacement->replaceMent_date ? \Carbon\Carbon::parse($replacement->replaceMent_date)->format('d-M-Y') : '-' }}
        </td>
    </tr>

    <tr>
        <th style="text-align:right;">Address:</th>
        <td style="text-align:left;" colspan="2">
            {{ $replacement->sale?->customer?->address ?? '-' }}
        </td>

        <th style="text-align:right;">Customer Mobile:</th>
        <td style="text-align:right;">
            {{ $replacement->sale?->customer?->mobile ?? '-' }}
        </td>
    </tr>
</table>
<hr>
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

    <div class="signature-left">
        <div class="signature-line">
            Prepared By
        </div>
    </div>

    <div class="signature-right">
        <div class="signature-line">
            Approved By
        </div>
    </div>

    <div class="clearfix"></div>

</div>

</body>
</html>