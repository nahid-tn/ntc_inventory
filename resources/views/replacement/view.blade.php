<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
             {{ __('Sales Replacement Details') }}
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

        /* Table */
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
        }
        th {
            background-color: #f2f2f2;
        }

        /* Totals */
        .totals {
            width: 40%;
            float: right;
            margin-top: 10px;
        }
        .totals table {
            border: none;
        }
        .totals th, .totals td {
            border: none;
            text-align: right;
            padding: 5px 10px;
        }

        /* Signature */
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 60px;
        }
        .signatures div {
            width: 45%;
            text-align: center;
        }
        .signatures div p {
            margin-top: 60px;
            border-top: 1px solid #333;
        }
    </style>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                
            {{-- Sale Info --}}
                <h3 class="text-lg font-semibold mb-4">Sale Replacement Info</h3>
                <table class="table table-bordered">
                    <tr>
                        <th>Invoice No:</th>
                        <td>{{ $replacement->sale?->invoice_no ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Sale Date:</th>
                        <td>
                            Sale Date: 
                            {{ $replacement->sale?->sale_date ? \Carbon\Carbon::parse($replacement->sale->sale_date)->format('d-M-Y') : '-' }} 
                            | 
                            <strong>Replacement Date: {{ $replacement->replaceMent_date ? \Carbon\Carbon::parse($replacement->replaceMent_date)->format('d-M-Y') : '-' }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <th>Customer:</th>
                        <td>{{ $replacement->sale?->customer?->customer_name ?? '-' }} | {{ $replacement->sale?->customer?->mobile ?? '-' }} | {{ $replacement->sale?->customer?->address ?? '-' }}</td>
                    </tr>

                </table>

                {{-- Old Product --}}
                <h3>Old Product</h3>
                    <table>
                        <tr>
                            <th>Product Name</th>
                            <td>{{ $replacement->oldSaleDetail?->product?->product_name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Quantity</th>
                            <td>
                                {{ $replacement->oldSaleDetail?->qty ?? '-' }}
                                |
                                {{ $replacement->oldSaleDetail?->unit ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Price</th>
                            <td>{{ number_format($replacement->oldSaleDetail?->price ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Total</th>
                            <td>{{ number_format($replacement->oldSaleDetail?->total ?? 0, 2) }}</td>
                        </tr>
                    </table>

                {{-- New Product --}}
                <h3 class="text-lg font-semibold mb-4 mt-6">New Product</h3>
                <table class="table table-bordered">
                    <tr>
                        <th>Product Name:</th>
                        <td>{{ $replacement->newProduct?->product_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Quantity:</th>
                        <td>{{ $replacement->qty }} | {{ $replacement->unit?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Price:</th>
                        <td>{{ number_format($replacement->price, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Total:</th>
                        <td>{{ number_format($replacement->total, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Replacement Fee:</th>
                        <td>{{ $replacement->replaceMent_fee }}</td>
                    </tr>
                </table>

                {{-- Buttons --}}
                <div class="mt-6 flex gap-3">
                    <a href="{{ route('replacement.invoice.pdf', $replacement->id) }}"
                       target="_blank"
                       class="btn btn-primary">
                        🧾 Download Invoice (PDF)
                    </a>

                    <a href="{{ route('replacement.index') }}" class="btn btn-secondary">
                        Back to List
                    </a>
                </div>

            </div>
        </div>
        <br>
        <span> @include('layouts.footer')</span>
    </div>
</x-app-layout>