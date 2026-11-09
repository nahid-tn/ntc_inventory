
<x-app-layout>

    <div class="container-fluid py-4 transaction-page">

        {{-- Header --}}
<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">

    <h4 class="mb-0">
        <i class="fas fa-file-invoice-dollar"></i>
        কাস্টমার লেনদেন হিসাব
    </h4>

@if($customer)
    <div class="d-flex justify-content-end mb-3" style="gap: 8px;">

        <a href="{{ route('sale.transactionHistoryPdf', ['customerId' => $customer->id]) }}"
           class="btn btn-danger"
           target="_blank"
           rel="noopener">
            <i class="fas fa-file-pdf"></i>
            PDF Download
        </a>

        <button type="button"
                onclick="window.print()"
                class="btn btn-success">
            <i class="fas fa-print"></i>
            প্রিন্ট
        </button>

    </div>
@endif

</div>


        {{-- Search --}}
        <div class="card mb-4 no-print">

            <div class="card-body">

                <form method="GET"
                      action="{{ route('sale.transactionHistory') }}">

                    <div class="row">

                        <div class="col-md-8">

                            <label class="form-label">
                                কাস্টমারের নাম / ID / মোবাইল নম্বর
                            </label>

                            <input type="text"
                                   name="search"
                                   value="{{ $search }}"
                                   class="form-control"
                                   placeholder="নাম, কাস্টমার ID অথবা মোবাইল নম্বর লিখুন">

                        </div>

                        <div class="col-md-4 d-flex align-items-end gap-2">

                            @if(isset($customer) && $customer)
    <a href="{{ route('sale.transactionHistoryPdf', ['customerId' => $customer->id]) }}"
       class="btn btn-danger btn-sm"
       target="_blank"
       rel="noopener">
        <i class="fas fa-file-pdf"></i> PDF Download
    </a>

    <button type="button"
            onclick="window.print()"
            class="btn btn-secondary btn-sm">
        <i class="fas fa-print"></i> প্রিন্ট
    </button>
@else
    <button type="button"
            onclick="window.print()"
            class="btn btn-secondary btn-sm">
        <i class="fas fa-print"></i> প্রিন্ট
    </button>
@endif

                        </div>

                    </div>

                </form>

            </div>

        </div>


        {{-- Customer Selection --}}
        @if(!$customer && $customers->count() > 0)

            <div class="card mb-4 no-print">

                <div class="card-header">
                    <i class="fas fa-users"></i>
                    সার্চ ফলাফল — কাস্টমার নির্বাচন করুন
                </div>

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover">

                            <thead class="table-light">
                                <tr>
                                    <th>কাস্টমার ID</th>
                                    <th>কাস্টমারের নাম</th>
                                    <th>মোবাইল</th>
                                    <th>নির্বাচন</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach($customers as $item)

                                    <tr>

                                        <td>{{ $item->id }}</td>

                                        <td>
                                            {{ $item->customer_name }}
                                        </td>

                                        <td>
                                            {{ $item->mobile }}
                                        </td>

                                        <td>

                                            <a href="{{ route('sale.transactionHistory', [
                                                'customer_id' => $item->id
                                            ]) }}"
                                               class="btn btn-sm btn-primary">

                                                <i class="fas fa-eye"></i>
                                                হিসাব দেখুন

                                            </a>

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        @elseif(!$customer && $search !== '')

            <div class="alert alert-warning no-print">
                <i class="fas fa-exclamation-triangle"></i>
                কোনো কাস্টমার পাওয়া যায়নি।
            </div>

        @endif


        {{-- Customer Ledger --}}
        @if($customer)

            {{-- Customer Information --}}
            <div class="card mb-4">

                <div class="card-header">
                    <i class="fas fa-user"></i>
                    কাস্টমার তথ্য
                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-4 mb-2">
                            <strong>কাস্টমার ID:</strong>
                            {{ $customer->id }}
                        </div>

                        <div class="col-md-4 mb-2">
                            <strong>নাম:</strong>
                            {{ $customer->customer_name }}
                        </div>

                        <div class="col-md-4 mb-2">
                            <strong>মোবাইল:</strong>
                            {{ $customer->mobile }}
                        </div>

                    </div>

                </div>

            </div>


            {{-- Summary --}}
            <div class="row mb-4">

                {{-- Total Sales --}}
                <div class="col-md-4 mb-3">

                    <div class="card border-primary h-100">

                        <div class="card-body">

                            <h6>
                                <i class="fas fa-shopping-cart"></i>
                                মোট বিক্রয়
                            </h6>

                            <h4 class="text-primary mb-0">
                                ৳ {{ number_format($totalSales, 2) }}
                            </h4>

                        </div>

                    </div>

                </div>


                {{-- Total Received --}}
                <div class="col-md-4 mb-3">

                    <div class="card border-success h-100">

                        <div class="card-body">

                            <h6>
                                <i class="fas fa-money-bill-wave"></i>
                                মোট জমা
                            </h6>

                            <h4 class="text-success mb-0">
                                ৳ {{ number_format($totalReceived, 2) }}
                            </h4>

                        </div>

                    </div>

                </div>


                {{-- Current Due --}}
                <div class="col-md-4 mb-3">

                    <div class="card border-danger h-100">

                        <div class="card-body">

                            <h6>
                                <i class="fas fa-balance-scale"></i>
                                {{ $dueAmount >= 0 ? 'বর্তমান বকেয়া' : 'অতিরিক্ত জমা' }}
                            </h6>

                            <h4 class="{{ $dueAmount >= 0 ? 'text-danger' : 'text-success' }} mb-0">

                                ৳ {{ number_format(abs($dueAmount), 2) }}

                            </h4>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Transaction Table --}}
            <div class="card">

                <div class="card-header">
                    <i class="fas fa-list-alt"></i>
                    বিস্তারিত লেনদেন
                </div>

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-bordered table-striped transaction-table">

                            <thead class="table-dark">

                                <tr>
                                    <th>ক্রমিক</th>
                                    <th>তারিখ</th>
                                    <th>Invoice</th>
                                    <th>বিবরণ</th>
                                    <th>পেমেন্ট মাধ্যম</th>
                                    <th>Tracking No.</th>
                                    <th class="text-end">বিক্রয় / জমা</th>
                                    <th class="text-end">বকেয়া</th>
                                </tr>

                            </thead>

                            <tbody>

                                @forelse($transactions as $index => $row)

                                    <tr>

                                        <td>
                                            {{ $index + 1 }}
                                        </td>

                                        <td>
                                            {{ $row['date']
                                                ? \Carbon\Carbon::parse($row['date'])->format('d-m-Y')
                                                : '-' }}
                                        </td>

                                        <td>
                                            {{ $row['invoice_no'] }}
                                        </td>

                                        <td>
                                            {{ $row['description'] }}
                                        </td>

                                        <td>
                                            {{ $row['payment_method'] ?: '-' }}
                                        </td>

                                        <td>
                                            {{ $row['tracking_no'] ?: '-' }}
                                        </td>

                                        <td class="text-end">

                                            @if($row['debit'] > 0)

                                                <span class="text-danger">
                                                    {{ number_format($row['debit'], 2) }}
                                                </span>

                                            @elseif($row['credit'] > 0)

                                                <span class="text-success">
                                                    {{ number_format($row['credit'], 2) }}
                                                </span>

                                            @else
                                                -
                                            @endif

                                        </td>

                                        <td class="text-end">

                                            <strong class="{{ $row['balance'] > 0 ? 'text-danger' : 'text-success' }}">

                                                {{ number_format(abs($row['balance']), 2) }}

                                                {{ $row['balance'] > 0
                                                    ? '(বকেয়া)'
                                                    : ($row['balance'] < 0 ? '(অগ্রিম)' : '') }}

                                            </strong>

                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="8"
                                            class="text-center">

                                            এই কাস্টমারের কোনো লেনদেন পাওয়া যায়নি।

                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>


                            {{-- Table Footer --}}
                            @if($transactions->count() > 0)

                                <tfoot class="table-light">

                                    <tr>

                                        <th colspan="6"
                                            class="text-end">

                                            সর্বমোট

                                        </th>

                                        <th class="text-end">

                                            <div class="text-danger">
                                                বিক্রয়:
                                                {{ number_format($totalSales, 2) }}
                                            </div>

                                            <div class="text-success">
                                                জমা:
                                                {{ number_format($totalReceived, 2) }}
                                            </div>

                                        </th>

                                        <th class="text-end">

                                            {{ number_format(abs($dueAmount), 2) }}

                                        </th>

                                    </tr>

                                </tfoot>

                            @endif

                        </table>

                    </div>

                </div>

            </div>

        @endif

    </div>


    {{-- Print CSS --}}
    <style>

        @media print {

            @page {
                size: A4 landscape;
                margin: 10mm;
            }

            body {
                background: #fff !important;
                font-size: 11px !important;
                color: #000 !important;
            }

            nav,
            header,
            footer,
            .no-print,
            .btn,
            form,
            .card-header {
                display: none !important;
            }

            .transaction-page {
                width: 100% !important;
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .card {
                border: none !important;
                box-shadow: none !important;
            }

            .card-body {
                padding: 5px !important;
            }

            .row {
                display: flex !important;
                flex-wrap: nowrap !important;
            }

            .col-md-4 {
                width: 33.333% !important;
            }

            .table-responsive {
                overflow: visible !important;
            }

            table {
                width: 100% !important;
                border-collapse: collapse !important;
            }

            th,
            td {
                border: 1px solid #000 !important;
                padding: 5px !important;
                color: #000 !important;
            }

            .table-dark th {
                background: #eee !important;
                color: #000 !important;
            }

            .text-danger,
            .text-success,
            .text-primary {
                color: #000 !important;
            }

            tr {
                page-break-inside: avoid !important;
            }

        }

    </style>

</x-app-layout>
