<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Purchase Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <h4>Add Partial Payment</h4>
                <form action="{{ route('purchase.addPayment', $purchase->id) }}" method="POST">
                    @csrf
                    <input type="number" name="amount" placeholder="Payment Amount" required>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}">
                    <select name="payment_method" required>
                        <option value="cash">Cash</option>
                        <option value="bank">Bank</option>
                    </select>
                    <button type="submit">Add Payment</button>
                </form>

                <h4>Payment History</h4>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Payment Date</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->payments as $index => $payment)
                        <tr>
                            <td>{{ $index+1 }}</td>
                            <td>{{ $payment->payment_date->format('d-m-Y') }}</td>
                            <td>{{ number_format($payment->amount,2) }}</td>
                            <td>{{ $payment->payment_method }}</td>
                            <td>{{ $payment->note }}</td>
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
