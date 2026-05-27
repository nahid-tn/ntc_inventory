<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Expense Details') }}
        </h2>
        <style>
            body { font-family: sans-serif; font-size: 14px; color: #333; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { border: .5px solid #999; padding: 8px; text-align: center; }
            th { background-color: #f2f2f2; }
            .totals { width: 40%; float: right; margin-top: 10px; }
            .totals table { border: none; }
            .totals th, .totals td { border: none; text-align: right; padding: 5px 10px; }
        </style>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">

                {{-- Expense Info --}}
                <h3 class="text-lg font-semibold mb-4">Expense Info</h3>
                <table>
                    <tr>
                        <th>Date</th>
                        <td>{{ $expense->expense_date->format('d-M-Y') }}</td>
                    </tr>
                    <!-- <tr>
                        <th>Reference</th>
                        <td>{{ $expense->reference ?? '-' }}</td>
                    </tr> -->
                    <tr>
                        <th>Payment Method</th>
                        <td>{{ $expense->payment_method ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Total</th>
                        <td>{{ number_format($expense->total, 2) }} ৳</td>
                    </tr>
                    <!-- <tr>
                        <th>For Expences</th>
                        <td>{{ $expense->forName ?? 'N/A' }}</td>
                    </tr> -->
                </table>

                {{-- Expense Items --}}
                <h3 class="text-lg font-semibold mb-4 mt-6">Expense Items</h3>
                
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item Name</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expense->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->item_name }}</td>
                                <td>{{ $item->qty }}</td>
                                <td>{{ number_format($item->price, 2) }} ৳</td>
                                <td>{{ number_format($item->subtotal, 2) }} ৳</td>
                                <td>{{ $item->description ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Back Button --}}
                <div class="mt-4">
                    <a href="{{ route('expense.index') }}" class="btn btn-secondary">← Back to Expenses</a>
                </div>

            </div>
        </div>
        <br>
        <span>@include('layouts.footer')</span>
    </div>
</x-app-layout>