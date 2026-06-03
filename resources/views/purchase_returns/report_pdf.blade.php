{{-- resources/views/purchase_returns/report_pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" lang="bn">
    <title>Purchase Return Report</title>

    <style>
        body {
            font-family: solaimanlipi, sans-serif;
            font-size: 12px;
        }

        h2 { text-align: center; margin: 5px 0; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table, th, td {
            border: .5px solid #000;
        }

        th, td {
            padding: 5px;
            text-align: center;
        }

        .supplier-info td {
            border: none;
            text-align: left;
            padding: 3px 0;
        }

        .transaction-space {
            height: 8px;
        }

        .grand-total {
            font-weight: bold;
        }

        .text-left { text-align: left !important; }
        .text-right { text-align: right !important; }

        .logo {
            width: 60px;
            margin-right: 12px;
        }

        .header {
            display: flex;
            align-items: center;
            border-bottom: 1px solid #333;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }
    </style>
</head>

<body>

{{-- SAFE LOGO PATH FOR MPDF --}}
@php
    $logoPath = public_path('images/logo.png');
@endphp

<table style="width:100%; border:none; margin-bottom:10px;">
    <tr>

        <!-- LOGO -->
        <td style="width:10%; border:none; text-align:left; vertical-align:top;">
            @php $logo = public_path('images/logo.png'); @endphp

            @if(file_exists($logo))
                <img src="file://{{ $logo }}" style="width:80px; height:50px;">
            @endif
        </td>

        <!-- COMPANY -->
        <td style="width:80%; border:none; text-align:center; vertical-align:middle;">

            <h1 style="margin:0; font-family:SolaimanLipi;font-size:18px;">
                {{ $companyInfo->company_name_bangla ?? 'Company Name' }}
            </h1>

            <p style="margin:2px 0; font-size:16px;">
                 {{ $companyInfo->company_address_bangla ?? '' }}
            </p>

            <p style="margin:2px 0; font-size:16px;">
                মোবাইল: {{ $companyInfo->company_phone_bangla ?? '' }}
            </p>

            <h2 style="
    margin:8px 0 0 0;
    font-family: SolaimanLipi, sans-serif;
">
    বিল / চালান
</h2>
        </td>

        <td style="width:10%; border:none;"></td>
    </tr>
</table>
<h2>Purchase Return Report</h2>
<hr>

{{-- Supplier Info --}}
@if(!empty($supplier))
    <table class="supplier-info" style="width:100%; border:none; margin-bottom:10px;">
        <tr>
            <td><b>Supplier:</b> {{ $supplier->supplier_name ?? '-' }}</td>
            <td><b>Mobile:</b> {{ $supplier->mobile ?? '-' }}</td>
            <td><b>Address:</b> {{ $supplier->address ?? '-' }}</td>
        </tr>
        <tr>
            <td colspan="3">
                <b>Period:</b> {{ $from_date }} to {{ $to_date }}
            </td>
        </tr>
    </table>
@else
    <p style="text-align:center">
        <b>Period:</b> {{ $from_date }} to {{ $to_date }}
    </p>
@endif

{{-- RETURNS --}}
@foreach($returns as $r)

@php
    $itemsTotal = $r->items->sum('total') ?? 0;
@endphp

<table>

    <thead>
        <tr>
            <th colspan="7" style="text-align:left">
                Return Invoice: {{ $r->invoice_no ?? '-' }}
                | Date: {{ \Carbon\Carbon::parse($r->return_date)->format('d-m-Y') }}
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
            <td class="text-left">{{ $item->product->product_name ?? '-' }}</td>
            <td>{{ number_format($item->qty, 2) }}</td>
            <td>{{ $item->unit ?? '-' }}</td>
            <td class="text-right">{{ number_format($item->price, 2) }}</td>
            <td class="text-right">{{ number_format($item->total, 2) }}</td>
            <td class="text-left">{{ $item->reason ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>

    <tfoot>
        <tr class="grand-total">
            <td colspan="5" style="text-align:right">Sub Total</td>
            <td class="text-right">{{ number_format($itemsTotal, 2) }}</td>
            <td></td>
        </tr>
        <tr class="grand-total">
            <td colspan="5" style="text-align:right"> Overall Grand Total</td>
            <td class="text-right">{{ number_format($itemsTotal, 2) }}</td>
            <td></td>
        </tr>
    </tfoot>

</table>

<div class="transaction-space"></div>

@endforeach
<div style="text-align:center; margin-top:10px;">
    {{-- <strong>Invoice No:</strong> {{ $purchase->Invoice_no }}<br> --}}

    @if(!empty($barcode))
        <img src="data:image/png;base64,{{ $barcode }}" style="height:40px; width:40%; margin-top:5px;">
    @endif
</div>
<div style="text-align:center; font-size:14px; color:#2c09ac;">
    This is System Generated Report not to be signed
</body>
</html>