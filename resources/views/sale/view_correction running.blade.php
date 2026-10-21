<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('বিক্রয় তালিকা') }}
        </h2>

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/bn.js"></script>

        <style>
            @page { margin: 50px 50px; }

            @font-face {
                font-family: 'SolaimanLipi';
                src: url("{{ asset('fonts/SolaimanLipi.ttf') }}") format('truetype');
                font-weight: normal;
                font-style: normal;
            }

            body {
                font-family: 'SolaimanLipi', sans-serif;
                font-size: 14px;
                line-height: 1.5;
                color: #333;
                margin: 0;
                padding: 0;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }

            table, th, td {
                border: 1px solid #999;
            }

            th, td {
                padding: 8px;
                text-align: center;
                vertical-align: middle;
            }

            th { background-color: #f2f2f2; }

            .company-header {
                text-align: center;
                margin-bottom: 20px;
            }

            .company-header img {
                max-height: 80px;
                max-width: 160px;
                object-fit: contain;
                margin-bottom: 10px;
            }

            .payment-form {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                gap: 8px;
            }

            .payment-form input,
            .payment-form select {
                padding: 7px;
                border: 1px solid #ced4da;
                border-radius: 4px;
                max-width: 100%;
            }

            .summary-table th,
            .summary-table td {
                padding: 9px 12px;
            }

            .summary-label {
                text-align: right !important;
                font-weight: bold;
            }

            .summary-value {
                text-align: right !important;
                white-space: nowrap;
            }

            .summary-total {
                background-color: #f2f2f2;
                font-weight: bold;
            }

            .summary-paid {
                color: #198754;
                font-weight: bold;
            }

            .summary-due {
                color: #dc3545;
                font-weight: bold;
            }

            .summary-previous {
                color: #b45309;
                font-weight: bold;
            }

            .summary-advance {
                color: #0d6efd;
                font-weight: bold;
            }

            .table-danger { background-color: #f8d7da !important; }

            @media print {
                .no-print { display: none !important; }

                body {
                    font-size: 12px;
                    color: #000;
                }

                table { page-break-inside: auto; }
                tr { page-break-inside: avoid; }
            }
        </style>
    </x-slot>

    @php
        /*
        |--------------------------------------------------------------------------
        | বাংলা সংখ্যা, তারিখ, একক ও পেমেন্ট মেথড
        |--------------------------------------------------------------------------
        */

        if (!function_exists('toBnNumber')) {
            function toBnNumber($number, $decimals = 2) {
                if ($number === null || $number === '') {
                    return '';
                }

                $formatted = number_format(
                    (float) $number,
                    $decimals,
                    '.',
                    ','
                );

                $bnDigits = [
                    '০','১','২','৩','৪','৫','৬','৭','৮','৯'
                ];

                return str_replace(
                    ['0','1','2','3','4','5','6','7','8','9'],
                    $bnDigits,
                    $formatted
                );
            }
        }

        if (!function_exists('toBnMobile')) {
            function toBnMobile($number) {
                if ($number === null || $number === '') {
                    return '';
                }

                $number = (string) $number;

                if (strlen($number) == 10 && $number[0] != '0') {
                    $number = '0' . $number;
                }

                $bnDigits = [
                    '০','১','২','৩','৪','৫','৬','৭','৮','৯'
                ];

                return str_replace(
                    ['0','1','2','3','4','5','6','7','8','9'],
                    $bnDigits,
                    $number
                );
            }
        }

        if (!function_exists('toBnDate')) {
            function toBnDate($date) {
                if (!$date) {
                    return '';
                }

                $enDate = \Carbon\Carbon::parse($date)->format('d-m-Y');

                $bnDigits = [
                    '০','১','২','৩','৪','৫','৬','৭','৮','৯'
                ];

                return str_replace(
                    ['0','1','2','3','4','5','6','7','8','9'],
                    $bnDigits,
                    $enDate
                );
            }
        }

        if (!function_exists('toBnUnit')) {
            function toBnUnit($unit) {
                $map = [
                    'set'  => 'সেট',
                    'kg'   => 'কেজি',
                    'pcs'  => 'পিস',
                    'box'  => 'বক্স',
                    'dram' => 'ড্রাম',
                ];

                $key = strtolower(trim((string) $unit));

                return $map[$key] ?? $unit;
            }
        }

        if (!function_exists('toBnMethod')) {
            function toBnMethod($method) {
                $map = [
                    'cash'   => 'নগদ',
                    'bank'   => 'ব্যাংক',
                    'mobile' => 'মোবাইল ব্যাংকিং',
                    'cheque' => 'চেক',
                    'other'  => 'অন্যান্য',
                ];

                $key = strtolower(trim((string) $method));

                return $map[$key] ?? ucfirst((string) $method);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | বর্তমান ইনভয়েসের হিসাব
        |--------------------------------------------------------------------------
        |
        | এই সংস্করণে ধরে নেওয়া হয়েছে:
        | paid_amount = বিক্রয়ের সময় নেওয়া প্রাথমিক পেমেন্ট
        | receivedPayments = পরবর্তী পেমেন্ট
        |
        | যদি paid_amount-এর মধ্যে receivedPayments আগে থেকেই অন্তর্ভুক্ত থাকে,
        | তাহলে এখানে দুইবার যোগ করা যাবে না।
        */

        $payments = $sale->receivedPayments ?? collect();

        $invoiceTotal = (float) ($sale->total_amount ?? 0);

        $initialPaid = (float) ($sale->paid_amount ?? 0);

        $additionalPaid = (float) $payments->sum('amount');

        $totalPaid = $initialPaid + $additionalPaid;

        // বকেয়া ঋণাত্মক হতে পারবে না
        $dueAmount = max(0, $invoiceTotal - $totalPaid);

        // মোট বিলের চেয়ে বেশি পরিশোধ করলে অতিরিক্ত অংশ
        $advanceAmount = max(0, $totalPaid - $invoiceTotal);

        /*
        |--------------------------------------------------------------------------
        | পূর্বের ইনভয়েসগুলোর বকেয়া
        |--------------------------------------------------------------------------
        |
        | বর্তমান ইনভয়েসের আগের তারিখের বিক্রয় নেওয়া হচ্ছে।
        | প্রতিটি ইনভয়েসের বকেয়া আলাদাভাবে শূন্যের নিচে নামতে পারবে না।
        */

        $previousDue = 0;

        if (
            !empty($sale->customer_id) &&
            !empty($sale->sale_date)
        ) {
            $previousSales = \App\Models\Sales::with('receivedPayments')
                ->where('customer_id', $sale->customer_id)
                ->where('sale_date', '<', $sale->sale_date)
                ->where('id', '!=', $sale->id)
                ->get();

            foreach ($previousSales as $previousSale) {
                $previousInvoiceTotal = (float) (
                    $previousSale->total_amount ?? 0
                );

                $previousInitialPaid = (float) (
                    $previousSale->paid_amount ?? 0
                );

                $previousAdditionalPaid = (float) (
                    ($previousSale->receivedPayments ?? collect())
                        ->sum('amount')
                );

                $previousTotalPaid =
                    $previousInitialPaid + $previousAdditionalPaid;

                // কোনো পুরোনো ইনভয়েসের ঋণাত্মক বকেয়া যোগ হবে না
                $previousInvoiceDue = max(
                    0,
                    $previousInvoiceTotal - $previousTotalPaid
                );

                $previousDue += $previousInvoiceDue;
            }
        }

        // সর্বমোট বকেয়া কখনো ঋণাত্মক হবে না
        $grandDue = max(0, $previousDue + $dueAmount);

        $sl = 1;
        $oldTotal = 0;
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="alert alert-success no-print">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger no-print">
                    <strong>অনুগ্রহ করে নিচের ভুলগুলো সংশোধন করুন:</strong>

                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">

                {{-- প্রতিষ্ঠানের তথ্য --}}
                <div class="company-header mt-4">

                    @if(!empty($company->logo))
                        <img
                            src="{{ asset('images/logo.png') }}"
                            alt="{{ $company->company_name_bangla ?? 'প্রতিষ্ঠানের লোগো' }}"
                        >
                    @endif

                    <h2 class="fw-bold">
                        {{ $company->company_name_bangla ?? '' }}
                    </h2>

                    <p>
                        {{ $company->company_address_bangla ?? '' }}<br>
                        মোবাইল- {{ $company->company_phone_bangla ?? '' }}
                    </p>

                    @if(!empty($company->email))
                        <p>ইমেইল: {{ $company->email }}</p>
                    @endif

                    <h3><u>বিল/চালান</u></h3>
                </div>

                {{-- আংশিক পেমেন্ট ফর্ম --}}
                <div class="no-print">
                    <h4>আংশিক পেমেন্ট যোগ করুন</h4>

                    <form
                        action="{{ route('sales.receivePayment', $sale->id) }}"
                        method="POST"
                        class="payment-form"
                    >
                        @csrf

                        <input
                            type="number"
                            name="amount"
                            placeholder="পেমেন্টের পরিমাণ"
                            step="0.01"
                            min="0.01"
                            value="{{ old('amount') }}"
                            required
                        >

                        <input
                            type="text"
                            id="received_date_bn"
                            class="form-control"
                            style="width: 180px;"
                            placeholder="তারিখ নির্বাচন করুন"
                            value="{{ toBnDate(old('received_date', date('Y-m-d'))) }}"
                            required
                            readonly
                        >

                        <input
                            type="hidden"
                            name="received_date"
                            id="received_date"
                            value="{{ old('received_date', date('Y-m-d')) }}"
                        >

                        <select
                            name="received_method"
                            id="received_method"
                            onchange="toggleOtherField()"
                            required
                        >
                            <option value="">-- নির্বাচন করুন --</option>

                            <option
                                value="cash"
                                {{ old('received_method') === 'cash' ? 'selected' : '' }}
                            >
                                নগদ
                            </option>

                            <option
                                value="bank"
                                {{ old('received_method') === 'bank' ? 'selected' : '' }}
                            >
                                ব্যাংক
                            </option>

                            <option
                                value="other"
                                {{ old('received_method') === 'other' ? 'selected' : '' }}
                            >
                                অন্যান্য
                            </option>
                        </select>

                        <input
                            type="text"
                            name="received_method_other"
                            id="received_method_other"
                            placeholder="পদ্ধতি লিখুন"
                            value="{{ old('received_method_other') }}"
                            style="{{ old('received_method') === 'other' ? '' : 'display:none;' }}"
                        >

                        <input
                            type="text"
                            name="tracking_no"
                            placeholder="ট্র্যাকিং নং"
                            value="{{ old('tracking_no') }}"
                        >

                        <button type="submit" class="btn btn-success">
                            পেমেন্ট যোগ করুন
                        </button>
                    </form>
                </div>

                <hr class="my-4">

                {{-- বিক্রয় তথ্য --}}
                <p>
                    <strong>ইনভয়েস নং:</strong>
                    {{ toBnNumber($sale->invoice_no, 0) }}
                </p>

                <p>
                    <strong>তারিখ:</strong>
                    {{ toBnDate($sale->sale_date) }}
                </p>

                <p>
                    <strong>গ্রাহক:</strong>
                    {{ $sale->customer->customer_name ?? '' }},
                    <strong>ঠিকানা:</strong>
                    {{ $sale->customer->address ?? '' }}
                </p>

                <p>
                    <strong>মোবাইল:</strong>
                    {{ toBnMobile($sale->customer->mobile ?? '') }}
                </p>

                {{-- পেমেন্ট ইতিহাস --}}
                @if($payments->isNotEmpty())
                    <hr class="my-4">

                    <h4>পেমেন্ট ইতিহাস</h4>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ক্রমিক</th>
                                <th>পেমেন্টের তারিখ</th>
                                <th>পরিমাণ</th>
                                <th>পদ্ধতি</th>
                                <th>ট্র্যাকিং নং</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($payments as $index => $payment)
                                <tr>
                                    <td>{{ toBnNumber($index + 1, 0) }}</td>

                                    <td>
                                        {{ toBnDate($payment->received_date ?? null) }}
                                    </td>

                                    <td style="text-align: right;">
                                        {{ toBnNumber($payment->amount ?? 0, 2) }}
                                    </td>

                                    <td>
                                        {{ toBnMethod($payment->received_method ?? '') }}
                                    </td>

                                    <td>
                                        {{ $payment->tracking_no
                                            ? toBnNumber($payment->tracking_no, 0)
                                            : '' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                {{-- পণ্যের তালিকা --}}
                <h4>পণ্যসমূহ:</h4>

                <table class="table table-bordered mt-2">
                    <thead>
                        <tr>
                            <th>ক্রমিক</th>
                            <th>পণ্যের নাম</th>
                            <th>পরিমাণ</th>
                            <th>একক</th>
                            <th>একক মূল্য</th>
                            <th>মোট</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach(($sale->details ?? collect()) as $item)
                            <tr>
                                <td>{{ toBnNumber($sl++, 0) }}</td>

                                <td>
                                    {{ $item->product->product_name ?? '' }}
                                </td>

                                <td>
                                    {{ toBnNumber($item->qty ?? 0, 2) }}
                                </td>

                                <td>
                                    {{ toBnUnit($item->unit ?? '') }}
                                </td>

                                <td style="text-align: right;">
                                    {{ toBnNumber($item->price ?? 0, 2) }}
                                </td>

                                <td style="text-align: right;">
                                    {{ toBnNumber($item->total ?? 0, 2) }}
                                </td>
                            </tr>
                        @endforeach

                        {{-- পুরাতন পণ্যের সমন্বয় --}}
                        @foreach(($sale->oldProductPurchases ?? collect()) as $purchase)
                            @foreach(($purchase->details ?? collect()) as $oldItem)
                                @php
                                    $oldLineTotal =
                                        (float) ($oldItem->qty ?? 0)
                                        * (float) ($oldItem->price ?? 0);

                                    $oldTotal += $oldLineTotal;
                                @endphp

                                <tr>
                                    <td>{{ toBnNumber($sl++, 0) }}</td>

                                    <td>
                                        {{ optional($oldItem->oldProduct)->product_name ?? '' }}
                                    </td>

                                    <td>
                                        {{ toBnNumber($oldItem->qty ?? 0, 2) }}
                                    </td>

                                    <td>
                                        {{ toBnUnit($oldItem->unit ?? '') }}
                                    </td>

                                    <td style="text-align: right;">
                                        {{ toBnNumber($oldItem->price ?? 0, 2) }}
                                    </td>

                                    <td style="text-align: right; color:red; font-weight:bold;">
                                        ( - ) {{ toBnNumber($oldLineTotal, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>

                    {{-- আর্থিক সারাংশ --}}
                    <tfoot class="summary-table">

                        @if((float) ($sale->discount_amount ?? 0) > 0)
                            <tr>
                                <th colspan="5" class="summary-label">
                                    ছাড়:
                                </th>

                                <td class="summary-value">
                                    ( - ) {{ toBnNumber($sale->discount_amount, 2) }}
                                </td>
                            </tr>
                        @endif

                        <tr class="summary-total">
                            <th colspan="5" class="summary-label">
                                মোট পরিশোধযোগ্য:
                            </th>

                            <td class="summary-value">
                                {{ toBnNumber($invoiceTotal, 2) }} টাকা
                            </td>
                        </tr>

                        <tr>
                            <th colspan="5" class="summary-label">
                                পরিশোধিত পরিমাণ:
                            </th>

                            <td class="summary-value summary-paid">
                                {{ toBnNumber($totalPaid, 2) }} টাকা
                            </td>
                        </tr>

                        <tr>
                            <th colspan="5" class="summary-label">
                                বাকি পরিমাণ:
                            </th>

                            <td class="summary-value summary-due">
                                {{ toBnNumber($dueAmount, 2) }} টাকা
                            </td>
                        </tr>

                        @if($advanceAmount > 0)
                            <tr>
                                <th colspan="5" class="summary-label">
                                    অতিরিক্ত পরিশোধিত:
                                </th>

                                <td class="summary-value summary-advance">
                                    {{ toBnNumber($advanceAmount, 2) }} টাকা
                                </td>
                            </tr>
                        @endif

                        <tr>
                            <th colspan="5" class="summary-label">
                                পূর্বের মোট বাকি:
                            </th>

                            <td class="summary-value summary-previous">
                                {{ toBnNumber($previousDue, 2) }} টাকা
                            </td>
                        </tr>

                        <tr class="summary-total">
                            <th colspan="5" class="summary-label">
                                সর্বমোট বাকি:
                            </th>

                            <td class="summary-value summary-due">
                                {{ toBnNumber($grandDue, 2) }} টাকা
                            </td>
                        </tr>

                    </tfoot>
                </table>

                {{-- অ্যাকশন বাটন --}}
                <div class="no-print">
                    <hr class="my-4">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        onclick="history.back()"
                    >
                        ← পিছনে
                    </button>

                    <a
                        href="{{ route('sale.partialPaymentPdf', $sale->id) }}"
                        target="_blank"
                        class="btn btn-success"
                    >
                        পিডিএফ ডাউনলোড
                    </a>

                    <a
                        href="{{ route('sale.editPartial', $sale->id) }}"
                        class="btn btn-primary"
                    >
                        বিক্রয় / আংশিক পেমেন্ট সম্পাদনা
                    </a>

                    <a
                        href="{{ route('sale.downloadPdf', [
                            'id' => $sale->id,
                            'print_both' => 'true'
                        ]) }}"
                        target="_blank"
                        class="btn btn-success"
                    >
                        বিল ও চালান
                    </a>
                </div>

            </div>
        </div>

        <br>

        <span>@include('layouts.footer')</span>
    </div>

    <script>
        // বাংলা সংখ্যা থেকে ইংরেজি সংখ্যা
        function toEn(str) {
            if (str === null || str === undefined) {
                return '';
            }

            str = str.toString();

            const bnToEn = {
                '০':'0', '১':'1', '২':'2', '৩':'3', '৪':'4',
                '৫':'5', '৬':'6', '৭':'7', '৮':'8', '৯':'9'
            };

            return str.replace(/[০-৯]/g, digit => bnToEn[digit]);
        }

        // বাংলা তারিখ নির্বাচন
        document.addEventListener('DOMContentLoaded', function () {
            const dateInput = document.getElementById('received_date_bn');
            const hiddenDate = document.getElementById('received_date');

            if (
                dateInput &&
                hiddenDate &&
                typeof flatpickr !== 'undefined'
            ) {
                flatpickr(dateInput, {
                    locale: 'bn',
                    dateFormat: 'd-m-Y',
                    defaultDate: hiddenDate.value || new Date(),

                    onChange: function (selectedDates, dateStr) {
                        const enDate = toEn(dateStr);
                        const parts = enDate.split('-');

                        if (parts.length === 3) {
                            hiddenDate.value =
                                parts[2] + '-' +
                                parts[1] + '-' +
                                parts[0];
                        }
                    }
                });
            }

            toggleOtherField();
        });

        // অন্যান্য পেমেন্ট পদ্ধতির ইনপুট
        function toggleOtherField() {
            const methodField =
                document.getElementById('received_method');

            const otherField =
                document.getElementById('received_method_other');

            if (!methodField || !otherField) {
                return;
            }

            if (methodField.value === 'other') {
                otherField.style.display = 'inline-block';
                otherField.required = true;
            } else {
                otherField.style.display = 'none';
                otherField.required = false;
                otherField.value = '';
            }
        }
    </script>
</x-app-layout>