<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Customer Sales Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Report Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <!-- Company Header -->
                    <div class="flex items-center justify-between border-b pb-2 mb-4">
                        <div class="flex items-center space-x-4">
                            <img src="{{ asset('images/logo.png') }}" alt="Company Logo" class="h-16 w-auto">
                            <div>
                                <h1 class="text-2xl font-bold">NTC Company Ltd.</h1>
                                <p class="text-sm text-gray-600">123 Main Street, Dhaka, Bangladesh</p>
                                <p class="text-sm text-gray-600">Phone: +880123456789 | Email: info@ntc.com</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <h2 class="font-semibold text-lg">Customer Sales Report</h2>
                            <span class="text-sm text-gray-500">{{ now()->format('d-m-Y') }}</span>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Customer Information</h3>
                        <p><strong>Name:</strong> {{ $customer->customer_name }}</p>
                        <p><strong>Mobile:</strong> {{ $customer->mobile }}</p>
                        @if($customer->address)
                        <p><strong>Address:</strong> {{ $customer->address }}</p>
                        @endif
                    </div>

                    <!-- New Products Sales -->
                    <div class="mb-6">
                        <h3 class="font-semibold mb-2">New Products Sales</h3>
                        <div class="overflow-x-auto">
                            <table class="table-auto border-collapse border border-gray-300 w-full text-sm">
                                <thead class="bg-gray-200">
                                    <tr>
                                        <th class="border border-gray-300 px-2 py-1">Invoice No</th>
                                        <th class="border border-gray-300 px-2 py-1">Sale Date</th>
                                        <th class="border border-gray-300 px-2 py-1">Subtotal</th>
                                        <th class="border border-gray-300 px-2 py-1">Tax</th>
                                        <th class="border border-gray-300 px-2 py-1">Discount</th>
                                        <th class="border border-gray-300 px-2 py-1">Total</th>
                                        <th class="border border-gray-300 px-2 py-1">Paid</th>
                                        <th class="border border-gray-300 px-2 py-1">Due</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($customer->sales as $sale)
                                    <tr class="text-center">
                                        <td class="border border-gray-300 px-2 py-1">{{ $sale->invoice_no }}</td>
                                        <td class="border border-gray-300 px-2 py-1">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d-m-Y') }}</td>
                                        <td class="border border-gray-300 px-2 py-1">{{ number_format($sale->subtotal,2) }}</td>
                                        <td class="border border-gray-300 px-2 py-1">{{ number_format($sale->tax_amount,2) }}</td>
                                        <td class="border border-gray-300 px-2 py-1">{{ number_format($sale->discount_amount,2) }}</td>
                                        <td class="border border-gray-300 px-2 py-1">{{ number_format($sale->total_amount,2) }}</td>
                                        <td class="border border-gray-300 px-2 py-1">{{ number_format($sale->paid_amount ?? 0, 2) }}</td>
                                        <td class="border border-gray-300 px-2 py-1">{{ number_format($sale->due_amount,2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-gray-500 py-2">No new product sales available.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Old Products Sales -->
                    @if($customer->oldProductSales->count() > 0)
                    <div class="mb-6">
                        <h3 class="font-semibold mb-2">Old Products Sales</h3>
                        <div class="overflow-x-auto">
                            <table class="table-auto border-collapse border border-gray-300 w-full text-sm">
                                <thead class="bg-gray-200">
                                    <tr>
                                        <th class="border border-gray-300 px-2 py-1">Invoice No</th>
                                        <th class="border border-gray-300 px-2 py-1">Sale Date</th>
                                        <th class="border border-gray-300 px-2 py-1">Total</th>
                                        <th class="border border-gray-300 px-2 py-1">Paid</th>
                                        <th class="border border-gray-300 px-2 py-1">Due</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customer->oldProductSales as $sale)
                                    <tr class="text-center">
                                        <td class="border border-gray-300 px-2 py-1">{{ $sale->invoice_no }}</td>
                                        <td class="border border-gray-300 px-2 py-1">{{ $sale->sale_date->format('d-m-Y') }}</td>
                                        <td class="border border-gray-300 px-2 py-1">{{ number_format($sale->total_amount,2) }}</td>
                                        <td class="border border-gray-300 px-2 py-1">{{ number_format($sale->paid_amount,2) }}</td>
                                        <td class="border border-gray-300 px-2 py-1">{{ number_format($sale->due_amount,2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    <!-- PDF & Excel Buttons -->
                    <div class="mt-4 flex space-x-2">
                        <a href="{{ route('customerReport.report.pdf', $customer->id) }}"
                           class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                           Download PDF
                        </a>
                    </div>

                </div>
            </div>

            <!-- Footer -->
            <br>
            <span> @include('layouts.footer') </span>
        </div>
    </div>
</x-app-layout>