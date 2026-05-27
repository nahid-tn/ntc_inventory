<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Sales List') }}
        </h2>
        <style>
        @page { margin: 50px 50px; }
        @font-face {
            font-family: 'SolaimanLipi';
            src: url("{{ public_path('fonts/SolaimanLipi.ttf') }}") format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        body {
            font-family: 'SolaimanLipi', sans-serif;
            font-size: 14px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid #999; }
        th, td { padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }

        .totals { width: 40%; float: right; margin-top: 10px; }
        .totals table { border: none; }
        .totals th, .totals td { border: none; text-align: right; padding: 5px 10px; }

        .signatures { display: flex; justify-content: space-between; margin-top: 60px; }
        .signatures div { width: 45%; text-align: center; }
        .signatures div p { margin-top: 60px; border-top: 1px solid #333; }

        .table-danger { background-color: #f8d7da !important; }
        </style>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <div class="text-center mt-4">
                        <h2 class="fw-bold">নাফিস ট্রেড কর্পোরেশন</h2>
                        <p>ঠিকানা- কালীবাড়ি রোড, মনিরামপুর, শাহজাদপুর, সিরাজগঞ্জ।<br>
                        মোবাইল- ০১৭১২-৪০৭০৪২, ০১৫৫১-৮০৬১০২।</p>
                        <h3><u>বিল/চালান</u></h3>
                    </div>

                    {{-- Partial Payment Form --}}
                    <h4>Add Partial Payment</h4>
                    <form action="{{ route('sales.receivePayment', $sale->id) }}" method="POST">
                        @csrf
                        <input type="number" name="amount" placeholder="Payment Amount" step="any" required>
                        <input type="date" name="received_date" value="{{ date('Y-m-d') }}">
                        <select name="received_method" id="received_method" onchange="toggleOtherField()" required>
                            <option value="">--Select---</option>
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                            <option value="other">Other</option>
                        </select>

                        <input type="text" name="received_method_other" id="received_method_other" placeholder="Write method" style="display:none; margin-top:5px;">
                        <input type="text" name="tracking_no" placeholder="Tracking No.">
                        <button type="submit" class="btn btn-success">Add Payment</button>
                    </form>
                    <hr class="my-4">

                    {{-- Sale Info --}}
                    <p><strong>Invoice No:</strong> {{ $sale->invoice_no }}</p>
                    <p><strong>Date:</strong> {{ $sale->sale_date->format('d-m-Y') }}</p>
                    <p><strong>Customer:</strong> {{ $sale->customer->customer_name ?? '' }}, <strong>Address:</strong> {{ $sale->customer->address ?? '' }}</p>
                    <p><strong>Mobile:</strong> {{ $sale->customer->mobile ?? '' }}</p>

                    {{-- Payment History --}}
                    @if(($sale->receivedPayments ?? collect())->count())
                    <hr class="my-4">
                    <h4>Payment History</h4>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Payment Date</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Tracking No</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->receivedPayments as $index => $payment)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($payment->received_date)->format('d-m-Y') }}</td>
                                <td>{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ ucfirst($payment->received_method) }}</td>
                                <td>{{ $payment->tracking_no }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif

                    {{-- Products Table --}}
                    <h4>Products:</h4>
                    <table class="table table-bordered mt-2">
                        <thead>
                            <tr>
                                <th>SLNo</th>
                                <th>Product Name</th>
                                <th>Qty</th>
                                <th>Unit</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php 
                                $sl = 1; 
                                $oldTotal = 0; 
                            @endphp

                            {{-- New Products --}}
                            @foreach($sale->details as $item)
                            <tr>
                                <td>{{ $sl++ }}</td>
                                <td>{{ $item->product->product_name ?? '' }}</td>
                                <td>{{ $item->qty }}</td>
                                <td>{{ $item->unit }}</td>
                                <td style="text-align: right;">{{ number_format($item->price,2) }}</td>
                                <td style="text-align: right;">{{ number_format($item->total,2) }}</td>
                            </tr>
                            @endforeach
                            
                            {{-- (Old Products) (Exchange) --}}

                            @foreach($sale->oldProductPurchases as $purchase)
                                @foreach($purchase->details as $i)
                                    @php $oldTotal += $i->qty * $i->price; @endphp
                                    <tr>
                                        <td>{{ $sl++ }}</td>
                                        <td>{{ optional($i->oldProduct)->product_name }}</td>
                                        <td>{{ $i->qty }}</td>
                                        <td>{{ $i->unit }}</td>
                                        <td style="text-align:right;">{{ number_format($i->price,2) }}</td>
                                        <td style="text-align:right; color:red; font-weight:bold;">
                                            ( - ) {{ number_format($i->qty * $i->price,2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            @endforeach
                            <tr>
                                {{-- Totals --}}
                                @php
                                    $receivedTotal = $sale->receivedPayments->sum('amount'); // Partial payments
                                    $totalPaid = $sale->paid_amount + $receivedTotal;
                                    $dueAmount = $sale->total_amount - $totalPaid;
                                @endphp

                                @if($sale->discount_amount > 0)
                                <tr>
                                    <th colspan="5" style="text-align: right;">Discount:</th>
                                    <td style="text-align: right;">( - ) {{ number_format($sale->discount_amount,2) }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th colspan="5" style="text-align: right;">Total Payable Amount:</th>
                                    <td style="text-align: right;">{{ number_format($sale->total_amount,2) }}</td>
                                </tr>
                                <tr>
                                    <th colspan="5" style="text-align: right;">Paid Amount:</th>
                                    <td style="text-align: right; color: green; font-weight: bold;">{{ number_format($totalPaid,2) }}</td>
                                </tr>
                                @if($dueAmount >= 50)
                                <tr>
                                    <th colspan="5" style="text-align: right;">Due Amount:</th>
                                    <td style="text-align: right;">{{ number_format($dueAmount,2) }}</td>
                                </tr>
                                @endif
                            </tr>
                        </tbody>
                    </table>
                    <hr class="my-4">
                    <button type="button" class="btn btn-secondary" onclick="history.back()">← Back</button>
                    <a href="{{ route('sale.pdf', $sale->id) }}" target="_blank" class="btn btn-success">Download PDF</a>
                    <a href="{{ route('sale.editPartial', $sale->id) }}" class="btn btn-primary">Edit Sale / Partial Payment</a>
            </div>
        </div>
        <br>
        <span>@include('layouts.footer')</span>
    </div>

    <script>
        function printDiv(divId) {
            let printContents = document.getElementById(divId).innerHTML;
            let originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            location.reload();
        }

        function toggleOtherField() {
            let method = document.getElementById('received_method').value;
            let otherField = document.getElementById('received_method_other');

            if(method === 'other'){
                otherField.style.display = 'block';
                otherField.required = true;
            } else {
                otherField.style.display = 'none';
                otherField.required = false;
            }
        }
    </script>
</x-app-layout>