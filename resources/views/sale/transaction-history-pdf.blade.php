<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">

    <style>
        @page {
            margin: 12mm 10mm 15mm 10mm;
        }

        body {
            font-family: solaimanlipi;
            font-size: 11pt;
            color: #222;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .logo {
            height: 65px;
            margin-bottom: 5px;
        }

        .company-name {
            font-size: 19pt;
            font-weight: bold;
            margin: 0;
        }

        .company-info {
            font-size: 10pt;
            line-height: 1.5;
        }

        .report-title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            margin: 15px 0;
        }

        .customer-box {
            border: 1px solid #777;
            padding: 8px 12px;
            margin-bottom: 15px;
            background-color: #f7f7f7;
        }

        .customer-box table {
            width: 100%;
        }

        .customer-box td {
            padding: 3px;
        }

        table.transaction-table {
            width: 100%;
            border-collapse: collapse;
        }

        .transaction-table th,
        .transaction-table td {
            border: 1px solid #777;
            padding: 6px 5px;
            font-size: 10pt;
        }

        .transaction-table th {
            background-color: #e8e8e8;
            font-weight: bold;
            text-align: center;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .summary {
            width: 55%;
            margin-left: auto;
            margin-top: 15px;
            border-collapse: collapse;
        }

        .summary td {
            border: 1px solid #777;
            padding: 7px;
        }

        .summary .label {
            font-weight: bold;
            background-color: #f0f0f0;
        }

        .due {
            font-weight: bold;
            font-size: 12pt;
        }

        .footer {
            margin-top: 35px;
            width: 100%;
        }

        .footer td {
            text-align: center;
            width: 33%;
            padding-top: 25px;
        }

        .signature-line {
            border-top: 1px solid #333;
            padding-top: 5px;
        }

        .print-date {
            text-align: right;
            font-size: 9pt;
            margin-top: 15px;
        }
    </style>
</head>

<body>

@php
    $logoPath = public_path('images/logo.png');

    $logoBase64 = null;

    if (file_exists($logoPath)) {
        $logoBase64 = 'data:image/png;base64,' .
            base64_encode(file_get_contents($logoPath));
    }

    $companyName = $company->company_name_bn
        ?? $company->company_name
        ?? $company->name
        ?? 'কোম্পানির নাম';

    $companyAddress = $company->address ?? '';
    $companyPhone = $company->phone ?? '';
    $companyEmail = $company->email ?? '';

    $customerName = $customer->customer_name ?? '';
    $customerMobile = $customer->mobile ?? '';

    $paymentMethodName = function ($method) {
        $methods = [
            'cash' => 'নগদ',
            'bank' => 'ব্যাংক',
            'mobile' => 'মোবাইল ব্যাংকিং',
            'cheque' => 'চেক',
            'other' => 'অন্যান্য',
        ];

        return $methods[strtolower($method)] ?? $method;
    };
@endphp

{{-- Company Header --}}
<div class="header">

    @if($logoBase64)
        <img src="{{ $logoBase64 }}" class="logo">
    @endif

    <div class="company-name">
        {{ $companyName }}
    </div>

    <div class="company-info">
        @if($companyAddress)
            ঠিকানা: {{ $companyAddress }}<br>
        @endif

        @if($companyPhone)
            মোবাইল: {{ $companyPhone }}
        @endif

        @if($companyEmail)
            | ইমেইল: {{ $companyEmail }}
        @endif
    </div>

</div>

{{-- Report Title --}}
<div class="report-title">
    কাস্টমার লেনদেন বিবরণী
</div>

{{-- Customer Information --}}
<div class="customer-box">

    <table>
        <tr>
            <td width="20%">
                <strong>কাস্টমারের নাম:</strong>
            </td>

            <td width="45%">
                {{ $customerName }}
            </td>

            <td width="15%">
                <strong>কাস্টমার ID:</strong>
            </td>

            <td width="20%">
                {{ $customer->id }}
            </td>
        </tr>

        <tr>
            <td>
                <strong>মোবাইল নম্বর:</strong>
            </td>

            <td>
                {{ $customerMobile }}
            </td>

            <td>
                <strong>রিপোর্ট তারিখ:</strong>
            </td>

            <td>
                {{ date('d-m-Y') }}
            </td>
        </tr>
    </table>

</div>

{{-- Transaction Table --}}
<table class="transaction-table">

    <thead>
        <tr>
            <th width="5%">ক্রমিক</th>
            <th width="12%">তারিখ</th>
            <th width="17%">ইনভয়েস</th>
            <th width="19%">বিবরণ</th>
            <th width="12%">পেমেন্ট মাধ্যম</th>
            <th width="12%">জমা (Credit)</th>
            <th width="12%">বিক্রয় (Debit)</th>
            <th width="11%">ব্যালেন্স</th>
        </tr>
    </thead>

    <tbody>

        @forelse($transactions as $index => $row)

            <tr>
                <td class="center">
                    {{ $index + 1 }}
                </td>

                <td class="center">
                    {{ $row['date'] ? date('d-m-Y', strtotime($row['date'])) : '-' }}
                </td>

                <td class="center">
                    {{ $row['invoice_no'] }}
                </td>

                <td>
                    {{ $row['description'] }}

                    @if(!empty($row['tracking_no']))
                        <br>
                        <small>ট্র্যাকিং: {{ $row['tracking_no'] }}</small>
                    @endif
                </td>

                <td class="center">
                    {{ $paymentMethodName($row['payment_method']) }}
                </td>

                <td class="right">
                    @if($row['credit'] > 0)
                        {{ number_format($row['credit'], 2) }}
                    @else
                        -
                    @endif
                </td>

                <td class="right">
                    @if($row['debit'] > 0)
                        {{ number_format($row['debit'], 2) }}
                    @else
                        -
                    @endif
                </td>

                <td class="right">
                    {{ number_format($row['balance'], 2) }}
                </td>
            </tr>

        @empty

            <tr>
                <td colspan="8" class="center">
                    কোনো লেনদেন পাওয়া যায়নি।
                </td>
            </tr>

        @endforelse

    </tbody>

</table>

{{-- Summary --}}
<table class="summary">

    <tr>
        <td class="label">
            মোট বিক্রয়:
        </td>

        <td class="right">
            {{ number_format($totalSales, 2) }} টাকা
        </td>
    </tr>

    <tr>
        <td class="label">
            মোট জমা:
        </td>

        <td class="right">
            {{ number_format($totalReceived, 2) }} টাকা
        </td>
    </tr>

    <tr>
        <td class="label due">
            সর্বশেষ বকেয়া:
        </td>

        <td class="right due">
            {{ number_format($dueAmount, 2) }} টাকা
        </td>
    </tr>

</table>

{{-- Signature --}}
<table class="footer">
    <tr>
        <td>
            <div class="signature-line">
                প্রস্তুতকারীর স্বাক্ষর
            </div>
        </td>

        <td>
            <div class="signature-line">
                যাচাইকারীর স্বাক্ষর
            </div>
        </td>

        <td>
            <div class="signature-line">
                অনুমোদনকারীর স্বাক্ষর
            </div>
        </td>
    </tr>
</table>

<div class="print-date">
    রিপোর্ট তৈরি: {{ date('d-m-Y h:i A') }}
</div>

</body>
</html>