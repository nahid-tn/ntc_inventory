<!DOCTYPE html>

<html>
<head>
<meta charset="utf-8">

<style>

@page{
    margin: 30px 60px;
}

body{
    font-family: solaimanlipi;
    font-size:12px;
}

.header{
    text-align:center;
    margin-bottom:15px;
}

table{
    width:100%;
    border-collapse:collapse;
}

table,th,td{
    border:.25px solid #000;
}

th{
    background:#f2f2f2;
}

th,td{
    padding:6px;
}

.right{
    text-align:right;
}

.summary{
    margin-top:15px;
    width:40%;
    float:right;
}

.footer{
    margin-top:80px;
    text-align:center;
}

</style>

</head>

<body>

<div class="header">
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
            <h1 style="margin:0; font-family:SolaimanLipi;font-size:25px;">
                {{ $companyInfo->company_name_bangla ?? 'Company Name' }}
            </h1>
            <p style="margin:2px 0; font-size:16px;">
                 {{ $companyInfo->company_address_bangla ?? '' }}
            </p>
            <p style="margin:2px 0; font-size:16px;">
                মোবাইল: {{ $companyInfo->company_phone_bangla ?? '' }}
            </p>
            <br>
            <h2 style="margin:8px 0 0 0; font-family: SolaimanLipi, sans-serif; font-size:20px;"> বিল পরিশোধের বিবৃতি</h2>
            
        </td><hr>
        <td style="width:10%; border:none;"></td>
    </tr>
</table>
</div>
    <table>
        <tr>
            <td><strong>Invoice No: </strong>{{ $sale->invoice_no }}</td>
            <td><strong> Date: </strong>{{ date('d-m-Y',strtotime($sale->sale_date)) }}</td>
        </tr>
        <tr>
            <td><strong>Customer: </strong>{{ $sale->customer->customer_name ?? '' }}</td>
            <td><strong>Mobile: </strong>{{ $sale->customer->mobile ?? '' }}</td>
        </tr>
    </table>
<br>
    <h4>Products</h4>
<table>
    <thead>
        <tr>
            <th>SL</th>
            <th>Product</th>
            <th>Qty</th>
            <th>Rate</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>

        @foreach($sale->details as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->product->product_name ?? '' }}</td>
                <td>{{ $item->qty }}</td>
                <td class="right">{{ number_format($item->price,2) }}</td>
                <td class="right">{{ number_format($item->total,2) }}</td>
            </tr>
        @endforeach
            
        <tr>
            <td colspan="4" class="right"><strong>Subtotal</strong></td>
            <td class="right">{{ number_format($sale->total_amount,2) }}</td>
        </tr>

        <tr>
            <td colspan="4" class="right"><strong>Discount</strong></td>
            <td class="right">- {{ number_format($discount,2) }}</td>
        </tr>
        <tr>
            <td colspan="4" class="right"><strong>Net Total</strong></td>
            <td class="right"><strong>{{ number_format($netTotal,2) }}</strong></td>
        </tr>
    </tbody>
</table>
<br>
<h4>Payment History</h4>
@php
    $receivedTotal = $sale->receivedPayments->sum('amount');

    $discount = $sale->discount ?? 0;

    // মূল টোটাল থেকে ডিসকাউন্ট বাদ
    $netTotal = $sale->total_amount - $discount;

    // মোট paid (old + received)
    $totalPaid = $sale->paid_amount + $receivedTotal;

    // due হিসাব
    $dueAmount = $netTotal - $totalPaid;
@endphp
<table>

<thead>
    <tr>
        <th>SL</th>
        <th>Date</th>
        <th>Amount</th>
        <th>Method</th>
        <th>Tracking No/ Reference</th>
    </tr>
</thead>
    <tbody>
    @foreach($sale->receivedPayments as $payment)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ date('d-m-Y',strtotime($payment->received_date)) }}</td>
            <td class="right">{{ number_format($payment->amount,2) }}</td>
            <td>{{ ucfirst($payment->received_method) }}</td>
            <td>{{ $payment->tracking_no }}</td>
        </tr>
        
    @endforeach
    
    <tr>
            <td colspan="2" class="right"><strong>Total Paid</strong></td>
            <td class="right"><strong>{{ number_format($totalPaid,2) }}</strong></td>
        </tr>

        @if($dueAmount > 0)
        <tr>
            <td colspan="2" class="right"><strong>Due Amount</strong></td>
            <td class="right"><strong>{{ number_format($dueAmount,2) }}</strong></td>
        </tr>
        @endif
    </tbody>
</table>


<div style="clear:both"></div>
<div class="footer">
<br>
<br>



<br>

<div style="position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 10px; border-top: .5px solid #f79516; padding-top: 5px;">

    <div>
        This is a computer-generated report and does not require any physical signature.
    </div>

    <div style="margin-top: 2px;">
        © {{ date('Y') }} {{ $companyInfo->company_name_english ?? 'Company Name' }} | All Rights Reserved
    </div>

</div>

</div>

</body>
</html>
