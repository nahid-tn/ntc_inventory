<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800">
        Product Return Details
    </h2>
</x-slot>

<div class="py-10">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded p-4">

            @if($details->count())
            <table class="min-w-full border-collapse border border-gray-400 mt-4">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="border p-2 text-center">#</th>
                        <th class="border p-2 text-center">Return Invoice</th>
                        <th class="border p-2 text-center">Return Date</th>
                        <th class="border p-2 text-center">Product Name</th>
                        <th class="border p-2 text-center">Qty</th>
                        <th class="border p-2 text-right">Unit Price</th>
                        <th class="border p-2 text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($details as $item)
                    <tr>
                        <td class="border p-2 text-center">{{ $loop->iteration }}</td>
                        <td class="border p-2 text-center">{{ $item->purchaseReturn->return_no ?? '-' }}</td>
                        <td class="border p-2 text-center">{{ \Carbon\Carbon::parse($item->purchaseReturn->return_date)->format('d-m-Y') }}</td>
                        <td class="border p-2 text-center">{{ $item->product->product_name ?? '-' }}</td>
                        <td class="border p-2 text-center">{{ number_format($item->qty,2) }} | {{ $item->unit ?? '-' }}</td>
                        <td class="border p-2 text-right">{{ number_format($item->price,2) }}</td>
                        <td class="border p-2 text-right">{{ number_format($item->total,2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
                <p class="text-center text-red-600 mt-4">No details found for this product in selected period.</p>
            @endif

        </div>
        <br>
        <span> @include('layouts.footer')</span>
    </div>
</div>
</x-app-layout>
