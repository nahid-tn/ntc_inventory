<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Expense Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        
                    <p>
    <strong>Type:</strong> {{ ucfirst($type) }} <br>
    <strong>Date:</strong> {{ $date }}
        </p>

        <table border="1" width="100%" cellpadding="8">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Payment</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp
                @forelse($expenses as $exp)
                    @php $total += $exp->amount; @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $exp->expense_date }}</td>
                        <td>{{ $exp->description }}</td>
                        <td>{{ $exp->payment_method }}</td>
                        <td>{{ number_format($exp->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" align="center">No data found</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" align="right">Total</th>
                    <th>{{ number_format($total, 2) }}</th>
                </tr>
            </tfoot>
        </table>

                </div>
            </div>
        </div>
        <br>
        <span> @include('layouts.footer')</span>
    </div>
    </div>
    <style>
        body { font-family: DejaVu Sans; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 6px; }
        th { background: #f0f0f0; }
    </style>
</x-app-layout>
