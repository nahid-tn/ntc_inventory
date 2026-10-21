<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Invoice PDF</title>

<style>
    body {
        font-family: solaimanlipi, sans-serif;
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
        font-family: solaimanlipi, sans-serif;
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


    .text-right {
        text-align: right;
    }

    .text-center {
        text-align: center;
    }

    .signature {
        margin-top: 70px;
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
    @php
    $logo = public_path('images/logo.png');
@endphp

<table style="border:none; margin-bottom:10px; width:100%;">
<tr>

    {{-- LOGO --}}
    <td style="width:10%; border:none; vertical-align:middle; text-align:left;">
        @if(file_exists($logo))
            <img src="file://{{ $logo }}"
                 style="width:80px; height:50px; display:block;">
        @endif
    </td>

    {{-- COMPANY --}}
    <td style="width:80%; border:none; text-align:center; vertical-align:middle;">
        <p style="font-size: 24"px>
            {{ $companyInfo->company_name_bangla ?? 'Company Name' }}
        </p>

        <p>
            {{ $companyInfo->company_address_bangla ?? '' }} <br>
            মোবাইল: {{ $companyInfo->company_phone_bangla ?? '' }}
        </p>

        <hr>
    </td>

    <td style="width:10%; border:none;"></td>

</tr>
</table>
    <div class="box">

        <div style="font-size: 24px; text-decoration: underline; text-align: center;"> {{ $invoice->challan_bn }}</div>

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

    <hr>
@php
    $logo = public_path('images/logo.png');
@endphp

<table style="border:none; margin-bottom:10px; width:100%;">
<tr>

    {{-- LOGO --}}
    <td style="width:10%; border:none; vertical-align:middle; text-align:left;">
        @if(file_exists($logo))
            <img src="file://{{ $logo }}"
                 style="width:80px; height:50px; display:block;">
        @endif
    </td>

    {{-- COMPANY --}}
    <td style="width:80%; border:none; text-align:center; vertical-align:middle;">
        <p style="font-size: 24"px>
            {{ $companyInfo->company_name_bangla ?? 'Company Name' }}
        </p>

        <p>
            {{ $companyInfo->company_address_bangla ?? '' }} <br>
            মোবাইল: {{ $companyInfo->company_phone_bangla ?? '' }}
        </p>

        <hr>
    </td>

    <td style="width:10%; border:none;"></td>

</tr>
</table>

    <!-- ================= INVOICE (WITH MONEY) ================= -->
    <div class="box">
        
        <div style="font-size: 24px; text-decoration: underline; text-align: center;">{{ $invoice->bill_bn }}</div>
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