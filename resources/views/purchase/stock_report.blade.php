<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Purchase & Stock Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <h3>Purchase Stock Report</h3>
                <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Total Purchased Qty</th>
                        <th>Current Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stocks as $row)
                    <tr>
                        <td>{{ $row->product_name }}</td>
                        <td>{{ $row->total_qty }}</td>
                        <!-- <td>{{ $row->stock }}</td> -->
                        <!-- <td>{{ number_format($row->stock, 0) }}</td> -->
                        <td>{{ intval($row->stock) }}</td>
                        <!-- <td class="{{ $row->stock < 0 ? 'text-danger fw-bold' : '' }}"> -->
                <!-- {{ intval($row->stock) }} -->
            </td>
                    </tr>
                    @endforeach
                </tbody>
</table>
            </div>
        </div>
        <br>
        <span> @include('layouts.footer')</span>
    </div>
    </div>
</x-app-layout>
