<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Old Product Sale Details') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                
                <h3 class="font-semibold mb-4">Sale Info</h3>
            <table class="table table-bordered">
                <tr>
                    <th>Invoice No</th>
                    <td>{{ $sale->invoice_no }}</td>
                </tr>
                <tr>
                    <th>Sale Date</th>
                    <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d-M-Y') }}</td>
                </tr>
                <tr>
                    <th>Customer</th>
                    <td>{{ $sale->customer->customer_name ?? '-' }} | {{ $sale->customer->mobile ?? '-' }}</td>
                </tr>
                
            </table>

            <h3 class="mt-6 mb-2">Products</h3>
            <table class="table table-bordered">
                <thead>
                    <tr class="text-center">
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->details as $d)
                        <tr>
                            <td>{{ $d->product->product_name }}</td>
                            <td class="text-center">{{ $d->qty }}</td>
                            <td class="text-center">{{ $d->unit }}</td>
                            <td class="text-right">{{ number_format($d->price,2) }}</td>
                            <td class="text-right">{{ number_format($d->subtotal,2) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                    <th colspan="4" class="text-right">Subtotal</th>
                    <td class="text-right">{{ number_format($sale->subtotal,2) }}</td>
                </tr>
                <tr>
                    <th colspan="4" class="text-right">Total Paid</th>
                    <td class="text-right">{{ number_format($sale->paid_amount,2) }}</td>
                </tr>
                <tr>
                    <th colspan="4" class="text-right">Due</th>
                    <td class="text-right">{{ number_format($sale->due_amount,2) }}</td>
                </tr>
                </tbody>
            </table>
            <div style="display: flex; gap: 10px; margin-top: 10px;">
    <a href="{{ route('oldsale.invoice.pdf', $sale->id) }}" class="btn btn-primary" target="_blank">🧾 Download PDF</a>
    <a href="{{ route('oldsale.edit', $sale->id) }}" class="btn btn-sm btn-success">Edit</a>
    <button type="button" class="btn btn-secondary" onclick="history.back()">
        ← Back
    </button>
</div>
        </div>
       <br>
        <span> @include('layouts.footer')</span>
    </div>
    </div>
</x-app-layout>
