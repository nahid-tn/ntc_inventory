<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profit/Loss Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- SUMMARY --}}
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="form-row mb-3">
                    <div class="bg-white p-4 rounded shadow col-md-4">
                        <h4>Total Purchase</h4>
                        <strong>{{ number_format($total_purchase,2) }}</strong>
                    </div>

                    <div class="bg-white p-4 rounded shadow col-md-4">
                        <h4>Total Sale</h4>
                        <strong>{{ number_format($total_sale,2) }}</strong>
                    </div>
                    <div class="bg-white p-4 rounded shadow col-md-4">
                        <h4>Net Profit / Loss</h4>
                        <strong class="{{ $net_profit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($net_profit,2) }}
                        </strong>
                    </div>
                </div>
            </div>

            {{-- TABLE --}}
            <div class="bg-white overflow-hidden shadow sm:rounded-lg">
                <table class="table table-bordered w-full">
                    <thead class="bg-gray-200">
                        <tr>
                            <th>Product</th>
                            <th>Purchase Qty</th>
                            <th>Purchase Amount</th>
                            <th>Sale Qty</th>
                            <th>Sale Amount</th>
                            <th>Profit / Loss</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports as $row)
                        <tr>
                            <td>{{ $row->product_name }}</td>

                            <td class="text-center">{{ $row->purchase_qty }}</td>
                            <td>{{ number_format($row->purchase_amount,2) }}</td>

                            <td class="text-center">{{ $row->sale_qty }}</td>
                            <td>{{ number_format($row->sale_amount,2) }}</td>

                            <td class="{{ $row->profit_loss >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($row->profit_loss,2) }}
                            </td>
                        </tr>
                        @endforeach

                        @if($reports->count() == 0)
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                No data found
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>

        </div>
    </div>
                </div>
            </div>
        </div>
        <br>
        <span> @include('layouts.footer')</span>
    </div>
    
</x-app-layout>
