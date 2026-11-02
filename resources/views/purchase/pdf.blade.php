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
        font-size: 16px;
        line-height: 1.8;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        border: .5px solid #73a1af;
        padding: 9px 10px;
        vertical-align: middle;
        font-size: 15px;
    }

    th {
        background: #f2f2f2;
        font-size: 16px;
        font-weight: bold;
    }

    td, th, p, span {
        white-space: pre-wrap;
    }

    h1 {
        font-size: 28px;
        line-height: 1.5;
    }

    h2 {
        font-size: 23px;
        line-height: 1.5;
    }

    strong {
        font-weight: bold;
    }
</style>
</head>

<body>

@php
    $company = $companyInfo ?? null;

    // Barcode আছে কি না যাচাই
    $hasBarcode = $purchase->details->contains(function ($item) {
        return !empty($item->barcode_image);
    });

    // Barcode থাকলে 6টি, না থাকলে 5টি কলাম
    
    $colspan = 5;

    /*
     * Shipping Cost বাদ দিয়ে Total Amount
     * ধরে নেওয়া হয়েছে shipping_amount আগে থেকেই total_amount-এর মধ্যে যুক্ত।
     */
    $shippingCost = (float) ($purchase->shipping_amount ?? 0);
    $totalAmount = (float) ($purchase->total_amount ?? 0) - $shippingCost;

    // মোট পরিশোধ
    $paid = (float) ($purchase->payments->sum('amount') ?? 0);

    // Shipping বাদ দেওয়া মোট হিসাব থেকে Due
    $due = $totalAmount - $paid;

    $dueColor = $due < 0 ? 'green' : 'red';
@endphp

<!-- HEADER -->
<table style="width:100%; border:none; margin-bottom:10px;">
    <tr>
        <td style="width:10%; border:none; text-align:left; vertical-align:top;">
            @php
                $logo = public_path('images/logo.png');
            @endphp

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

            <h2 style="margin:8px 0 0 0; font-family:SolaimanLipi, sans-serif;">
                বিল / চালান
            </h2>
        </td>

        <td style="width:10%; border:none;"></td>
    </tr>
</table>

<hr>

<!-- SUPPLIER INFORMATION -->
<p>
    <strong>Invoice No:</strong> {{ $purchase->Invoice_no }}<br>
    <strong>Supplier:</strong> {{ $purchase->supplier->supplier_name ?? '' }}<br>
    {{ $purchase->supplier->address ?? '' }} |
    <strong>Mobile No:</strong> {{ $purchase->supplier->mobile ?? '' }}<br>
    <strong>Date:</strong> {{ \Carbon\Carbon::parse($purchase->date)->format('d-M-Y') }}
</p>

<!-- PRODUCTS -->
<table>
    <thead>
        <tr>
            <th style="text-align:center; width:10%;">SlNo</th>
            <th style="text-align:center; width:45%;">Product Name</th>

            {{-- @if($hasBarcode)
                <th style="text-align:center; width:20%;">Barcode</th>
            @endif --}}

            <th style="text-align:center; width:10%;">Qty</th>
            <th style="text-align:center; width:10%;">U.Price</th>
            <th style="text-align:center; width:20%;">Total Price</th>
        </tr>
    </thead>

    <tbody>
        @foreach($purchase->details as $index => $item)
            <tr>
                <td style="text-align:center;">
                    {{ $index + 1 }}
                </td>

                <td style="text-align:left;">
                    {{ $item->product->product_name ?? '' }}
                </td>

                {{-- @if($hasBarcode)
                    <td style="text-align:center;">
                        @if(!empty($item->barcode_image))
                            <img
                                src="data:image/png;base64,{{ $item->barcode_image }}"
                                style="height:40px; width:100%;"
                            >
                        @endif
                    </td>
                @endif --}}

                <td style="text-align:center;">
                    {{ $item->qty }} | {{ $item->unit }}
                </td>

                <td style="text-align:right;">
                    {{ number_format((float) $item->price, 2) }}
                </td>

                <td style="text-align:right;">
                    {{ number_format((float) $item->total, 2) }} | Tk
                </td>
            </tr>
        @endforeach

        <!-- TOTAL AMOUNT: SHIPPING বাদ -->
        <tr>
            <td colspan="{{ $colspan }}" style="text-align:right; font-weight:bold;">
                Total Amount: {{ number_format($totalAmount, 2) }} | Tk
            </td>
        </tr>

        <!-- PAID AMOUNT -->
        <tr>
            <td colspan="{{ $colspan }}" style="text-align:right; font-weight:bold; -moz-text-decoration-color: #2c09ac;">
                Paid Amount: {{ number_format($paid, 2) }} | Tk
            </td>
        </tr>

        <!-- DISCOUNT -->
        @if(isset($purchase->discount) && (float) $purchase->discount > 0)
            <tr>
                <td colspan="{{ $colspan }}" style="text-align:right; font-weight:bold;">
                    Discount: {{ number_format((float) $purchase->discount, 2) }} | Tk
                </td>
            </tr>
        @endif

        <!-- DUE AMOUNT -->
        <tr>
            <!-- DUE AMOUNT: বাকি থাকলেই দেখাবে -->
        @if($due > 0)
            <tr>
                <td colspan="{{ $colspan }}"
                    style="text-align:right; font-weight:bold; color:red;">
                    Due Amount: {{ number_format($due, 2) }} | Tk
                </td>
            </tr>
        @endif
        </tr>
    </tbody>
</table>

<br><br>

<!-- INVOICE BARCODE -->
<div style="text-align:center;">
    @if(!empty($barcode))
        <img
            src="data:image/png;base64,{{ $barcode }}"
            style="height:40px; width:40%;"
        >
    @endif
</div>

<br>

<div style="text-align:center; font-size:14px; color:#2c09ac;">
    This is System Generated Invoice not to be signed
</div>

</body>
</html>