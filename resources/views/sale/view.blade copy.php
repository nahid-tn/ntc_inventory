<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('বিক্রয় বিবরণ') }}
            </h2>
        </div>
    </x-slot>

    @php
        // বাংলা সংখ্যা ও প্রদর্শনের সহায়ক ফাংশন
        if (!function_exists('toBnNumber')) {
            function toBnNumber($number, $decimals = 2) {
                if ($number === null || $number === '') return '';
                $formatted = number_format((float) $number, $decimals, '.', ',');
                return str_replace(['0','1','2','3','4','5','6','7','8','9'], ['০','১','২','৩','৪','৫','৬','৭','৮','৯'], $formatted);
            }
        }
        if (!function_exists('toBnTextDigits')) {
            function toBnTextDigits($value) {
                if ($value === null || $value === '') return '';
                return str_replace(['0','1','2','3','4','5','6','7','8','9'], ['০','১','২','৩','৪','৫','৬','৭','৮','৯'], (string) $value);
            }
        }
        if (!function_exists('toBnMobile')) {
            function toBnMobile($number) {
                if ($number === null || $number === '') return '';
                $number = (string) $number;
                if (strlen($number) === 10 && $number[0] !== '0') $number = '0' . $number;
                return toBnTextDigits($number);
            }
        }
        if (!function_exists('toBnDate')) {
            function toBnDate($date) {
                if (!$date) return '';
                try { return toBnTextDigits(\Carbon\Carbon::parse($date)->format('d-m-Y')); }
                catch (\Throwable $e) { return (string) $date; }
            }
        }
        if (!function_exists('toBnUnit')) {
            function toBnUnit($unit) {
                $map = [
                    'pc' => 'টি', 'pcs' => 'পিস', 'set' => 'সেট', 'kg' => 'কেজি', 'kgs' => 'কেজি',
                    'box' => 'বক্স', 'dgn' => 'ডজন', 'jor' => 'জোড়া', 'gur' => 'গুরুস',
                    'drm' => 'ড্রাম', 'dram' => 'ড্রাম', 'pac' => 'প্যাকেট',
                ];
                $key = strtolower(trim((string) $unit));
                return $map[$key] ?? $unit;
            }
        }
        if (!function_exists('toBnMethod')) {
            function toBnMethod($method) {
                $map = ['cash' => 'নগদ', 'bank' => 'ব্যাংক', 'mobile' => 'মোবাইল ব্যাংকিং', 'cheque' => 'চেক', 'other' => 'অন্যান্য'];
                $key = strtolower(trim((string) $method));
                return $map[$key] ?? ($method ?: '—');
            }
        }
        if (!function_exists('toBnStatus')) {
            function toBnStatus($status) {
                $map = ['pending' => 'অপেক্ষমাণ', 'completed' => 'সম্পন্ন', 'cancelled' => 'বাতিল'];
                $key = strtolower(trim((string) $status));
                return $map[$key] ?? ($status ?: '—');
            }
        }

        $payments = $sale->receivedPayments ?? collect();
        $receivedTotal = (float) $payments->sum('amount');
        // paid_amount যদি প্রাথমিক পেমেন্ট সংরক্ষণ করে, সেটি এবং পরবর্তী পেমেন্টের যোগফল
        $totalPaid = (float) ($sale->paid_amount ?? 0) + $receivedTotal;
        $dueAmount = (float) ($sale->total_amount ?? 0) - $totalPaid;
        $oldTotal = 0;
    @endphp

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        @font-face {
            font-family: 'SolaimanLipi';
            src: url("{{ asset('fonts/SolaimanLipi.ttf') }}") format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        .sale-detail-page { font-family: 'SolaimanLipi', sans-serif; font-size: 13px; line-height: 1.45; }
        .sale-detail-page .page-card { padding: 14px !important; border-radius: 10px; }
        .sale-detail-page .company-head { text-align: center; padding: 6px 0 12px; border-bottom: 1px solid #e5e7eb; margin-bottom: 14px; }
        .sale-detail-page .company-head img { max-height: 58px; max-width: 150px; object-fit: contain; margin: 0 auto 6px; }
        .sale-detail-page .company-head h2 { font-size: 21px; font-weight: 700; margin: 3px 0; }
        .sale-detail-page .company-head p { margin: 2px 0; }
        .sale-detail-page .section-title { font-size: 16px; font-weight: 700; margin: 12px 0 8px; }
        .sale-detail-page .compact-form { display: flex; flex-wrap: wrap; align-items: end; gap: 8px; }
        .sale-detail-page .field { flex: 1 1 135px; min-width: 0; }
        .sale-detail-page label { display: block; font-weight: 600; margin-bottom: 3px; }
        .sale-detail-page .form-control, .sale-detail-page select, .sale-detail-page input { width: 100%; min-height: 34px; padding: 5px 8px; border: 1px solid #cbd5e1; border-radius: 5px; font-size: 13px; }
        .sale-detail-page .btn { display: inline-flex; align-items: center; justify-content: center; gap: 5px; padding: 7px 10px; border-radius: 5px; font-size: 12px; text-decoration: none; border: 0; cursor: pointer; white-space: normal; }
        .sale-detail-page .btn-success { color: #fff; background: #198754; }
        .sale-detail-page .btn-primary { color: #fff; background: #0d6efd; }
        .sale-detail-page .btn-secondary { color: #fff; background: #6c757d; }
        .sale-detail-page .btn-outline { color: #374151; background: #f3f4f6; }
        .sale-detail-page .detail-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 7px 16px; margin: 12px 0; }
        .sale-detail-page .detail-item { overflow-wrap: anywhere; }
        .sale-detail-page .table-wrap { width: 100%; overflow-x: auto; margin: 8px 0 14px; }
        .sale-detail-page table { width: 100%; border-collapse: collapse; margin: 0; }
        .sale-detail-page th, .sale-detail-page td { border: 1px solid #d1d5db; padding: 6px 7px; text-align: center; vertical-align: middle; }
        .sale-detail-page th { background: #f3f4f6; font-weight: 700; }
        .sale-detail-page .text-right { text-align: right; }
        .sale-detail-page .text-left { text-align: left; }
        .sale-detail-page .negative { color: #dc2626; font-weight: 700; }
        .sale-detail-page .paid { color: #15803d; font-weight: 700; }
        .sale-detail-page .summary-label { text-align: right; font-weight: 700; }
        .sale-detail-page .actions { display: flex; flex-wrap: wrap; gap: 7px; padding-top: 8px; border-top: 1px solid #e5e7eb; }
        .sale-detail-page .divider { border: 0; border-top: 1px solid #e5e7eb; margin: 14px 0; }
        .sale-detail-page .alert { padding: 9px 12px; border-radius: 6px; margin-bottom: 12px; }
        .sale-detail-page .alert-success { background: #d1e7dd; color: #0f5132; }
        .sale-detail-page .alert-danger { background: #f8d7da; color: #842029; }
        @media (max-width: 640px) {
            .sale-detail-page .page-card { padding: 9px !important; }
            .sale-detail-page .detail-grid { grid-template-columns: 1fr; }
            .sale-detail-page .company-head h2 { font-size: 18px; }
            .sale-detail-page th, .sale-detail-page td { padding: 5px; white-space: nowrap; }
            .sale-detail-page .btn { flex: 1 1 auto; }
        }
        @media print {
            @page { margin: 12mm; }
            body { background: #fff !important; }
            .no-print, x-slot, header, nav { display: none !important; }
            .sale-detail-page .page-card { box-shadow: none !important; border: 0 !important; padding: 0 !important; }
            .sale-detail-page .table-wrap { overflow: visible; }
        }
    </style>

    <div class="py-5 sale-detail-page">
        <div class="max-w-7xl mx-auto px-3 sm:px-5 lg:px-6">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul style="margin:0; padding-left:20px;">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm page-card">
                {{-- প্রতিষ্ঠানের তথ্য --}}
                <div class="company-head">
                    @if(!empty($company->logo))
                        <img src="{{ asset('images/logo.png') }}" alt="{{ $company->company_name_bangla ?? 'প্রতিষ্ঠানের লোগো' }}">
                    @endif
                    <h2>{{ $company->company_name_bangla ?? '' }}</h2>
                    @if(!empty($company->company_address_bangla))
                        <p>{{ $company->company_address_bangla }}</p>
                    @endif
                    @if(!empty($company->company_phone_bangla))
                        <p>মোবাইল: {{ $company->company_phone_bangla }}</p>
                    @endif
                    @if(!empty($company->email))<p>ইমেইল: {{ $company->email }}</p>@endif
                    <h3 style="font-size:17px; font-weight:700; margin-top:7px;"><u>বিল / চালান</u></h3>
                </div>

                {{-- আংশিক পেমেন্ট ফর্ম --}}
                <div class="no-print">
                    <h3 class="section-title">আংশিক পেমেন্ট যোগ করুন</h3>
                    <form action="{{ route('sales.receivePayment', $sale->id) }}" method="POST" class="compact-form" id="receivePaymentForm">
                        @csrf
                        <div class="field">
                            <label for="payment_amount">পেমেন্টের পরিমাণ</label>
                            <input type="number" id="payment_amount" name="amount" min="0.01" step="0.01" placeholder="পরিমাণ লিখুন" required>
                        </div>
                        <div class="field">
                            <label for="received_date_bn">পেমেন্টের তারিখ</label>
                            <input type="text" id="received_date_bn" class="form-control" placeholder="তারিখ নির্বাচন করুন" value="{{ toBnDate(now()->format('Y-m-d')) }}" readonly required>
                            <input type="hidden" name="received_date" id="received_date" value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div class="field">
                            <label for="received_method">পেমেন্ট পদ্ধতি</label>
                            <select name="received_method" id="received_method" onchange="toggleOtherField()" required>
                                <option value="">-- নির্বাচন করুন --</option>
                                <option value="cash">নগদ</option>
                                <option value="bank">ব্যাংক</option>
                                <option value="mobile">মোবাইল ব্যাংকিং</option>
                                <option value="cheque">চেক</option>
                                <option value="other">অন্যান্য</option>
                            </select>
                        </div>
                        <div class="field" id="other_method_wrap" style="display:none;">
                            <label for="received_method_other">অন্যান্য পদ্ধতি</label>
                            <input type="text" name="received_method_other" id="received_method_other" placeholder="পদ্ধতি লিখুন">
                        </div>
                        <div class="field">
                            <label for="tracking_no">ট্র্যাকিং নম্বর</label>
                            <input type="text" name="tracking_no" id="tracking_no" placeholder="ঐচ্ছিক">
                        </div>
                        <div><button type="submit" class="btn btn-success"><i class="fa fa-plus"></i> পেমেন্ট যোগ করুন</button></div>
                    </form>
                </div>

                <hr class="divider">

                {{-- বিক্রয় ও ক্রেতার তথ্য --}}
                <h3 class="section-title">বিক্রয়ের তথ্য</h3>
                <div class="detail-grid">
                    <div class="detail-item"><strong>ইনভয়েস নং:</strong> {{ toBnTextDigits($sale->invoice_no) }}</div>
                    <div class="detail-item"><strong>বিক্রয়ের তারিখ:</strong> {{ toBnDate($sale->sale_date) }}</div>
                    <div class="detail-item"><strong>ক্রেতা:</strong> {{ $sale->customer->customer_name ?? '' }}</div>
                    <div class="detail-item"><strong>মোবাইল:</strong> {{ toBnMobile($sale->customer->mobile ?? '') }}</div>
                    <div class="detail-item" style="grid-column:1/-1;"><strong>ঠিকানা:</strong> {{ $sale->customer->address ?? '' }}</div>
                </div>

                {{-- পেমেন্ট ইতিহাস --}}
                @if($payments->count())
                    <h3 class="section-title">পেমেন্ট ইতিহাস</h3>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>ক্রমিক</th><th>পেমেন্টের তারিখ</th><th>পরিমাণ</th><th>পদ্ধতি</th><th>ট্র্যাকিং নং</th></tr></thead>
                            <tbody>
                                @foreach($payments as $index => $payment)
                                    <tr>
                                        <td>{{ toBnNumber($index + 1, 0) }}</td>
                                        <td>{{ toBnDate($payment->received_date) }}</td>
                                        <td class="text-right">{{ toBnNumber($payment->amount, 2) }}</td>
                                        <td>{{ toBnMethod($payment->received_method) }}</td>
                                        <td>{{ $payment->tracking_no ? toBnTextDigits($payment->tracking_no) : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- পণ্য ও হিসাব --}}
                <h3 class="section-title">পণ্যসমূহ</h3>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr><th>ক্রমিক</th><th>পণ্যের নাম</th><th>পরিমাণ</th><th>একক</th><th>একক মূল্য</th><th>মোট</th></tr>
                        </thead>
                        <tbody>
                            @php $sl = 1; @endphp
                            @forelse($sale->details as $item)
                                <tr>
                                    <td>{{ toBnNumber($sl++, 0) }}</td>
                                    <td class="text-left">{{ $item->product->product_name ?? '—' }}</td>
                                    <td>{{ toBnNumber($item->qty, 2) }}</td>
                                    <td>{{ toBnUnit($item->unit) }}</td>
                                    <td class="text-right">{{ toBnNumber($item->price, 2) }}</td>
                                    <td class="text-right">{{ toBnNumber($item->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6">কোনো নতুন পণ্য পাওয়া যায়নি।</td></tr>
                            @endforelse

                            @foreach($sale->oldProductPurchases as $purchase)
                                @foreach($purchase->details as $oldItem)
                                    @php $oldLineTotal = (float) $oldItem->qty * (float) $oldItem->price; $oldTotal += $oldLineTotal; @endphp
                                    <tr>
                                        <td>{{ toBnNumber($sl++, 0) }}</td>
                                        <td class="text-left">{{ optional($oldItem->oldProduct)->product_name ?? 'পুরাতন পণ্য' }}</td>
                                        <td>{{ toBnNumber($oldItem->qty, 2) }}</td>
                                        <td>{{ toBnUnit($oldItem->unit) }}</td>
                                        <td class="text-right">{{ toBnNumber($oldItem->price, 2) }}</td>
                                        <td class="text-right negative">(−) {{ toBnNumber($oldLineTotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            @endforeach

                            @if((float) ($sale->tax_amount ?? 0) > 0)
                                <tr><th colspan="5" class="summary-label">কর:</th><td class="text-right">{{ toBnNumber($sale->tax_amount, 2) }}</td></tr>
                            @elseif((float) ($sale->tax_percentage ?? 0) > 0)
                                <tr><th colspan="5" class="summary-label">কর ({{ toBnNumber($sale->tax_percentage, 2) }}%):</th><td class="text-right">{{ toBnNumber(((float) $sale->subtotal * (float) $sale->tax_percentage) / 100, 2) }}</td></tr>
                            @endif
                            @if((float) ($sale->shipping_amount ?? 0) > 0)
                                <tr><th colspan="5" class="summary-label">শিপিং:</th><td class="text-right">{{ toBnNumber($sale->shipping_amount, 2) }}</td></tr>
                            @endif
                            @if((float) ($sale->discount_amount ?? 0) > 0)
                                <tr><th colspan="5" class="summary-label">ছাড়:</th><td class="text-right negative">(−) {{ toBnNumber($sale->discount_amount, 2) }}</td></tr>
                            @endif
                            <tr><th colspan="5" class="summary-label">মোট পরিশোধযোগ্য:</th><td class="text-right"><strong>{{ toBnNumber($sale->total_amount, 2) }}</strong></td></tr>
                            <tr><th colspan="5" class="summary-label">পরিশোধিত পরিমাণ:</th><td class="text-right paid">{{ toBnNumber($totalPaid, 2) }}</td></tr>
                            @if($dueAmount > 0.009)
                                <tr><th colspan="5" class="summary-label">বাকি পরিমাণ:</th><td class="text-right negative">{{ toBnNumber($dueAmount, 2) }}</td></tr>
                            @elseif($dueAmount < -0.009)
                                <tr><th colspan="5" class="summary-label">অতিরিক্ত পরিশোধ:</th><td class="text-right negative">{{ toBnNumber(abs($dueAmount), 2) }}</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                {{-- অতিরিক্ত তথ্য --}}
                <h3 class="section-title">অতিরিক্ত তথ্য</h3>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>গ্যারান্টির সময়</th><th>বডি তারিখ</th><th>স্ট্যাটাস</th><th>নোট</th></tr></thead>
                        <tbody>
                            <tr>
                                <td>{{ $sale->warranty_period ?? '—' }}</td>
                                <td>{{ toBnDate($sale->body_date) ?: '—' }}</td>
                                <td>{{ toBnStatus($sale->status) }}</td>
                                <td class="text-left" style="white-space:normal; min-width:130px;">{{ $sale->note ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- অ্যাকশন বাটন --}}
                <div class="actions no-print">
                    <button type="button" class="btn btn-outline" onclick="history.back()"><i class="fa fa-arrow-left"></i> পিছনে</button>
                    <a href="{{ route('sale.partialPaymentPdf', $sale->id) }}" target="_blank" class="btn btn-success"><i class="fa fa-file-pdf"></i> পিডিএফ ডাউনলোড</a>
                    <a href="{{ route('sale.editPartial', $sale->id) }}" class="btn btn-primary"><i class="fa fa-pen"></i> বিক্রয় / পেমেন্ট সম্পাদনা</a>
                    <a href="{{ route('sale.downloadPdf', ['id' => $sale->id, 'print_both' => 'true']) }}" target="_blank" class="btn btn-success"><i class="fa fa-print"></i> বিল ও চালান</a>
                    <button type="button" class="btn btn-secondary" onclick="window.print()"><i class="fa fa-print"></i> প্রিন্ট</button>
                </div>
            </div>
        </div>
        <div class="mt-4">@include('layouts.footer')</div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/bn.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof flatpickr !== 'undefined') {
                flatpickr('#received_date_bn', {
                    locale: 'bn',
                    dateFormat: 'd-m-Y',
                    defaultDate: new Date(),
                    onReady: function (selectedDates) {
                        if (selectedDates.length) updateReceivedDate(selectedDates[0]);
                    },
                    onChange: function (selectedDates) {
                        if (selectedDates.length) updateReceivedDate(selectedDates[0]);
                    }
                });
            }

            function updateReceivedDate(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                document.getElementById('received_date').value = `${year}-${month}-${day}`;
            }

            toggleOtherField();
        });

        function toggleOtherField() {
            const method = document.getElementById('received_method');
            const wrap = document.getElementById('other_method_wrap');
            const otherField = document.getElementById('received_method_other');
            if (!method || !wrap || !otherField) return;
            const isOther = method.value === 'other';
            wrap.style.display = isOther ? 'block' : 'none';
            otherField.required = isOther;
            if (!isOther) otherField.value = '';
        }
    </script>
</x-app-layout>
