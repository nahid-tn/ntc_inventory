<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Sales Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header img { height: 60px; }
        .company-info { line-height: 1.2; }
        h2, h3 { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; font-size: 12px; }
        th { background-color: #eee; }
        .section-title { margin-top: 20px; margin-bottom: 5px; font-weight: bold; font-size: 14px; }
        .customer-info p { margin: 2px 0; font-size: 12px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <div class="logo">
            <img src="{{ public_path('images/logo.png') }}" alt="Company Logo">
        </div>
        <div class="company-info">
            <h2>NTC Company Ltd.</h2>
            <p>Kalibari Road, Monirampur, Sirajgonj, Bangladesh</p>
            <p>Phone: +88 0171-2407042 | Email: info@ntc.com</p>
        </div>
        <div class="report-title" style="text-align:right;">
            <h3>Customer Sales Report</h3>
            <p>{{ now()->format('d-m-Y') }}</p>
        </div>
    </div>

    <!-- Customer Info -->
    <div class="customer-info">
        <h3 class="section-title">Customer Information</h3>
        <p><strong>Name:</strong> {{ $customer->customer_name }}</p>
        <p><strong>Mobile:</strong> {{ $customer->mobile }}</p>
        @if($customer->address)
        <p><strong>Address:</strong> {{ $customer->address }}</p>
        @endif
    </div>

    <!-- New Products Sales -->
    <div class="new-sales">
        <h3 class="section-title">Products Sales List</h3>
        <table>
             <thead>
                <tr>
                    <th>Invoice No</th>
                    <th>Sale Date</th>
                    <th>Subtotal</th>
                    <th>Discount</th>
                    <th>Total</th>
                    <th>Paid</th>
                    <th>Due</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $subtotalSum = 0;
                    $taxSum = 0;
                    $discountSum = 0;
                    $totalSum = 0;
                    $paidSum = 0;
                    $dueSum = 0;
                @endphp

                @forelse($customer->sales as $sale)
                @php
                    $subtotalSum += $sale->subtotal;
                    $taxSum += $sale->tax_amount;
                    $discountSum += $sale->discount_amount;
                    $totalSum += $sale->total_amount;
                    $paidSum += $sale->paid_amount;
                    $dueSum += $sale->due_amount;
                @endphp
                <tr>
                    <td>{{ $sale->invoice_no }}</td>
                    <td>{{ $sale->sale_date->format('d-m-Y') }}</td>
                    <td>{{ number_format($sale->subtotal,2) }}</td>
                    <!-- <td>{{ number_format($sale->tax_amount,2) }}</td> -->
                    <td>{{ number_format($sale->discount_amount,2) }}</td>
                    <td>{{ number_format($sale->total_amount,2) }}</td>
                    <td>{{ number_format($sale->paid_amount,2) }}</td>
                    <td>{{ number_format($sale->due_amount,2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">No new product sales available.</td>
                </tr>
                @endforelse
            </tbody>

            @if($customer->sales->count() > 0)
            <tfoot>
                <tr style="font-weight:bold; background:#f5f5f5;">
                    <td colspan="2">Total</td>
                    <td>{{ number_format($subtotalSum,2) }}</td>
                    <!-- <td>{{ number_format($taxSum,2) }}</td> -->
                    <td>{{ number_format($discountSum,2) }}</td>
                    <td>{{ number_format($totalSum,2) }}</td>
                    <td>{{ number_format($paidSum,2) }}</td>
                    <td>{{ number_format($dueSum,2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    <!-- Old Products Sales -->
    @if($customer->oldProductSales->count() > 0)
    <div class="old-sales">
        <h3 class="section-title">Old Products Sales</h3>
        <table>
            <thead>
                <tr>
                    <th>Invoice No</th>
                    <th>Sale Date</th>
                    <th>Total</th>
                    <th>Paid</th>
                    <th>Due</th>
                </tr>
            </thead>
            <tbody>
                @foreach($customer->oldProductSales as $sale)
                <tr>
                    <td>{{ $sale->invoice_no }}</td>
                    <td>{{ $sale->sale_date->format('d-m-Y') }}</td>
                    <td>{{ number_format($sale->total_amount,2) }}</td>
                    <td>{{ number_format($sale->paid_amount,2) }}</td>
                    <td>{{ number_format($sale->due_amount,2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <p style="text-align:center; font-size:12px; margin-top:50px;">Generated by NTC Company Ltd. - {{ now()->format('d-m-Y') }}</p>

</body>
</html>