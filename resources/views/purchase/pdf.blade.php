<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>Purchase Invoice - {{ $purchase->Invoice_no }}</title>

    <style>
        @font-face {
            font-family: 'SolaimanLipi';
            src: url("{{ public_path('fonts/SolaimanLipi.ttf') }}") format('truetype');
        }

        body {
            font-family: SolaimanLipi, sans-serif;
            font-size: 12px;
            line-height: 1.6;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: .5px solid #73a1af;
            padding: 6px 8px;
            vertical-align: top;
        }

        th {
            background: #f2f2f2;
        }

        td, th, p, span {
            white-space: pre-wrap;
        }
    </style>
</head>

<body>

@php
    $company = $companyInfo ?? null;

    // 🔥 SAFE CHECK (ALL ROWS CHECK)
    $hasBarcode = $purchase->details->contains(function($item){
        return !empty($item->barcode_image);
    });

    // 🔥 AUTO COLSPAN FIX
    $colspan = $hasBarcode ? 6 : 5;
@endphp

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

            <h1 style="margin:0; font-family:SolaimanLipi;">
                {{ $company->company_name_bangla ?? 'Company Name' }}
            </h1>

            <p style="margin:2px 0;">
                {{ $company->company_address_bangla ?? '' }}
            </p>

            <p style="margin:2px 0;">
                Phone: {{ $company->company_phone_bangla ?? '' }}
            </p>

            <h2 style="margin:8px 0 0 0; font-family: SolaimanLipi, sans-serif;">
                বিল / চালান
            </h2>

        </td>

        <td style="width:10%; border:none;"></td>
    </tr>
</table>

<hr>

<p>
<strong>Invoice No:</strong> {{ $purchase->Invoice_no }}<br>
<strong>Supplier:</strong> {{ $purchase->supplier->supplier_name }} <br>
{{ $purchase->supplier->address }} |
<strong>Mobile No:</strong> {{ $purchase->supplier->mobile }}<br>
<strong>Date:</strong> {{ \Carbon\Carbon::parse($purchase->date)->format('d-M-Y') }}
</p>

<!-- PRODUCTS -->
<table>
    <thead>
        <tr>
            <th style="text-align:center; width:3%;">SlNo</th>
            <th style="text-align:center; width:40%;">Product Name</th>

            @if($hasBarcode)
                <th style="text-align:center; width:20%;">Barcode</th>
            @endif

            <th style="text-align:center; width:10%;">Qty</th>
            <th style="text-align:center; width:15%;">Unit Price</th>
            <th style="text-align:center; width:15%;">Total</th>
        </tr>
    </thead>

    <tbody>
        @foreach($purchase->details as $index => $item)
        <tr>
            <td style="text-align:center; width:3%;">{{ $index + 1 }}</td>

            <td style="text-align:center;">
                {{ $item->product->product_name }}
            </td>

            @if($hasBarcode)
            <td style="text-align:center;">
                @if($item->barcode_image)
                    <img src="data:image/png;base64,{{ $item->barcode_image }}" style="height:40px; width:20%;">
                @endif
            </td>
            @endif

            <td style="text-align:center;">
                {{ $item->qty }} {{ $item->unit }}
            </td>

            <td style="text-align:right;">
                {{ number_format($item->price, 2) }}
            </td>

            <td style="text-align:right;">
                {{ number_format($item->total, 2) }}
            </td>
        </tr>
        @endforeach

        <tr>
            <td colspan="{{ $colspan }}" style="text-align:right;font-weight:bold;">
                Total Amount: {{ number_format($purchase->total_amount, 2) }}
            </td>
        </tr>

        @php
            $paid = $purchase->payments->sum('amount') ?? 0;
            $due  = ($purchase->total_amount ?? 0) - $paid;
        @endphp

        <tr>
            <td colspan="{{ $colspan }}" style="text-align:right;font-weight:bold;">
                Paid Amount: {{ number_format($paid, 2) }}
            </td>
        </tr>

        @if(isset($purchase->discount) && $purchase->discount > 0)
        <tr>
            <td colspan="{{ $colspan }}" style="text-align:right;font-weight:bold;">
                Discount: {{ number_format($purchase->discount, 2) }}
            </td>
        </tr>
        @endif

        @php
            $dueColor = $due < 0 ? 'green' : 'red';
        @endphp

        <tr>
            <td colspan="{{ $colspan }}" style="text-align:right;font-weight:bold; color:{{ $dueColor }};">
                Due Amount: {{ number_format($due, 2) }}
            </td>
        </tr>

    </tbody>
</table>

<br><br>

<div style="text-align:center;">
    @if(!empty($barcode))
        <img src="data:image/png;base64,{{ $barcode }}" style="height:40px; width:40%;">
    @endif
</div>

<br>

<div style="text-align:center; font-size:14px; color:#2c09ac;">
    This is System Generated Invoice not to be signed
</div>

</body>
</html>