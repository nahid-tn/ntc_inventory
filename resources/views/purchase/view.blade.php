<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Purchase Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                {{-- =========================
                    PARTIAL PAYMENT FORM
                ========================== --}}

                <h4 class="text-lg font-semibold mb-3">Add Partial Payment</h4>

                @php
                    $payments = $purchase->payments ?? collect();

                    $paidAmount = (float) $payments->sum('amount');
                    $totalAmount = (float) ($purchase->total_amount ?? 0);

                    // Shipping is already included in total amount.
                    // Do not add shipping to paid amount.
                    $dueAmount = max(0, $totalAmount - $paidAmount);
                @endphp

                @if($dueAmount > 0)

                    <form action="{{ route('purchase.addPayment', $purchase->id) }}"
                          method="POST"
                          class="mb-4">

                        @csrf

                        <div class="flex flex-wrap gap-3 items-center">

                            <input
                                type="number"
                                name="amount"
                                placeholder="Payment Amount"
                                min="0.01"
                                max="{{ $dueAmount }}"
                                step="0.01"
                                required
                                class="border border-gray-300 rounded px-3 py-2"
                            >

                            <input
                                type="date"
                                name="payment_date"
                                value="{{ date('Y-m-d') }}"
                                required
                                class="border border-gray-300 rounded px-3 py-2"
                            >

                            <select
                                name="payment_method"
                                required
                                class="border border-gray-300 rounded px-3 py-2"
                            >
                                <option value="cash">Cash</option>
                                <option value="bank">Bank</option>
                            </select>

                            <input
                                type="text"
                                name="note"
                                placeholder="Note (optional)"
                                class="border border-gray-300 rounded px-3 py-2"
                            >

                            <button
                                type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded"
                            >
                                Add Payment
                            </button>

                        </div>

                        @error('amount')
                            <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                        @enderror

                    </form>

                @else

                    <p class="text-green-600 font-semibold mb-4">
                        Purchase payment is fully paid.
                    </p>

                @endif


                <hr class="my-4">


                {{-- =========================
                    PURCHASE INFORMATION
                ========================== --}}

                <h4 class="text-lg font-semibold mb-3">Purchase Information</h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

                    <p>
                        <strong>Invoice:</strong>
                        {{ $purchase->Invoice_no }}
                    </p>

                    <p>
                        <strong>Supplier Name:</strong>
                        {{ $purchase->supplier->supplier_name ?? 'N/A' }}
                    </p>

                    <p>
                        <strong>Contact:</strong>
                        {{ $purchase->supplier->mobile ?? 'N/A' }}
                    </p>

                    <p>
                        <strong>Address:</strong>
                        {{ $purchase->supplier->address ?? 'N/A' }}
                    </p>

                    <p>
                        <strong>Date:</strong>
                        {{ \Carbon\Carbon::parse($purchase->date)->format('d-M-Y') }}
                    </p>

                </div>


                {{-- =========================
                    CHALLAN ATTACHMENT
                ========================== --}}

                <hr class="my-4">

                <h4 class="text-lg font-semibold mb-3">
                    Purchase Challan / চালানের কপি
                </h4>

                @if(!empty($purchase->challan_file))

                    @php
                        $challanUrl = asset('storage/' . ltrim($purchase->challan_file, '/'));
                        $challanExtension = strtolower(
                            pathinfo($purchase->challan_file, PATHINFO_EXTENSION)
                        );
                    @endphp

                    <div class="border rounded-lg p-4 bg-gray-50">

                        <p class="text-green-700 font-semibold mb-3">
                            চালানের কপি সংযুক্ত রয়েছে।
                        </p>

                        <div class="flex flex-wrap gap-3 mb-3">

                            <a
                                href="{{ $challanUrl }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded"
                            >
                                View Challan
                            </a>

                            <a
                                href="{{ $challanUrl }}"
                                download
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded"
                            >
                                Download Challan
                            </a>

                        </div>

                        {{-- Image Preview --}}

                        @if(in_array($challanExtension, ['jpg', 'jpeg', 'png']))

                            <div class="mt-3">
                                <p class="font-semibold mb-2">Challan Preview:</p>

                                <a href="{{ $challanUrl }}" target="_blank" rel="noopener noreferrer">
                                    <img
                                        src="{{ $challanUrl }}"
                                        alt="Purchase Challan"
                                        class="max-w-full rounded border"
                                        style="max-height: 400px;"
                                    >
                                </a>
                            </div>

                        @elseif($challanExtension === 'pdf')

                            <p class="text-gray-600 text-sm mt-2">
                                PDF চালানটি দেখতে View Challan বাটনে ক্লিক করুন।
                            </p>

                        @endif

                    </div>

                @else

                    <p class="text-red-600">
                        কোনো চালানের কপি সংযুক্ত নেই।
                    </p>

                @endif


                {{-- =========================
                    PAYMENT HISTORY
                ========================== --}}

                @if($payments->count())

                    <hr class="my-4">

                    <h4 class="text-lg font-semibold mb-3">
                        Payment History
                    </h4>

                    <div class="overflow-x-auto">

                        <table class="table table-bordered w-full border-collapse">

                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border p-2 text-center">#</th>
                                    <th class="border p-2 text-center">Payment Date</th>
                                    <th class="border p-2 text-right">Amount</th>
                                    <th class="border p-2 text-center">Method</th>
                                    <th class="border p-2 text-left">Note</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach($payments as $index => $payment)

                                    <tr>
                                        <td class="border p-2 text-center">
                                            {{ $index + 1 }}
                                        </td>

                                        <td class="border p-2 text-center">
                                            {{ \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') }}
                                        </td>

                                        <td class="border p-2 text-right">
                                            {{ number_format($payment->amount, 2) }}
                                        </td>

                                        <td class="border p-2 text-center">
                                            {{ ucfirst($payment->payment_method) }}
                                        </td>

                                        <td class="border p-2">
                                            {{ $payment->note ?? '' }}
                                        </td>
                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                @endif


                {{-- =========================
                    PURCHASE PRODUCTS
                ========================== --}}

                <hr class="my-4">

                <h4 class="text-lg font-semibold mb-3">
                    Products
                </h4>

                <div class="overflow-x-auto">

                    <table class="table table-bordered w-full border-collapse">

                        <thead>
                            <tr class="bg-gray-100">

                                <th class="border p-2 text-center" style="width: 3%;">
                                    #
                                </th>

                                <th class="border p-2 text-center" style="width: 30%;">
                                    Product Name
                                </th>

                                @if($purchase->details->firstWhere('barcode_image', '!=', null))

                                    <th class="border p-2 text-center" style="width: 20%;">
                                        Barcode
                                    </th>

                                @endif

                                <th class="border p-2 text-center" style="width: 10%;">
                                    Qty
                                </th>

                                <th class="border p-2 text-center" style="width: 10%;">
                                    Unit
                                </th>

                                <th class="border p-2 text-center" style="width: 15%;">
                                    Unit Price
                                </th>

                                <th class="border p-2 text-center" style="width: 15%;">
                                    Total
                                </th>

                            </tr>
                        </thead>

                        <tbody>

                            @foreach($purchase->details as $item)

                                <tr>

                                    <td class="border p-2 text-center">
                                        {{ $loop->iteration }}
                                    </td>

                                    <td class="border p-2">
                                        {{ $item->product->product_name ?? 'N/A' }}
                                    </td>

                                    @if($purchase->details->firstWhere('barcode_image', '!=', null))

                                        <td class="border p-2 text-center">

                                            @if($item->barcode_image)

                                                <img
                                                    src="data:image/png;base64,{{ $item->barcode_image }}"
                                                    alt="Barcode"
                                                    style="height:40px; max-width:100%;"
                                                >

                                            @endif

                                        </td>

                                    @endif

                                    <td class="border p-2 text-right">
                                        {{ $item->qty }}
                                    </td>

                                    <td class="border p-2 text-center">
                                        {{ $item->unit }}
                                    </td>

                                    <td class="border p-2 text-right">
                                        {{ number_format($item->price, 2) }}
                                    </td>

                                    <td class="border p-2 text-right">
                                        {{ number_format($item->total, 2) }}
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>


                {{-- =========================
                    PURCHASE SUMMARY
                ========================== --}}

                <hr class="my-4">

                <div class="text-right space-y-2">

                    <h5>
                        Subtotal:
                        {{ number_format($purchase->subtotal, 2) }}
                    </h5>

                    <h5>
                        Tax ({{ $purchase->tax_percentage ?? 0 }}%):
                        {{ number_format($purchase->tax_amount ?? 0, 2) }}
                    </h5>

                    <h5>
                        Shipping:
                        {{ number_format($purchase->shipping_amount ?? 0, 2) }}
                    </h5>

                    <h5 class="font-semibold">
                        Total Amount:
                        {{ number_format($totalAmount, 2) }}
                    </h5>

                    <h5 class="text-green-700 font-semibold">
                        Paid Amount:
                        {{ number_format($paidAmount, 2) }}
                    </h5>

                    <h5 class="text-red-600 font-semibold">
                        Due Amount:
                        {{ number_format($dueAmount, 2) }}
                    </h5>

                </div>


                <hr class="my-4">

                {{-- BACK BUTTON --}}

                <button
                    type="button"
                    class="btn btn-secondary bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded"
                    onclick="history.back()"
                >
                    ← Back
                </button>

            </div>

        </div>

        <br>

        <span>
            @include('layouts.footer')
        </span>

    </div>

</x-app-layout>