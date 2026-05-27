<x-app-layout>
    <h4>Payment List for {{ $supplier->supplier_name }}</h4>

    <table border="1" cellpadding="5" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Date</th>
                <th>Purchase Invoice</th>
                <th>Purchase Amount</th>
                <th>Payment Type</th>
                <th>Payment Amount</th>
                <th>Narration</th>
            </tr>
        </thead>
        <tbody>
            @php $totalPaid = 0; @endphp
            @foreach($payments as $payment)
                @php $totalPaid += $payment->amount; @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') }}</td>
                    <td>
                        @if($payment->purchase)
                            <a href="{{ route('purchase.invoice', $payment->purchase->id) }}" target="_blank">
                                {{ $payment->purchase->Invoice_no }}
                            </a>
                        @else
                            N/A
                        @endif
                    </td>
                    <td>
                        @if($payment->purchase)
                            {{ number_format($payment->purchase->subtotal, 2) }}
                        @else
                            0.00
                        @endif
                    </td>
                    <td>{{ ucfirst($payment->payment_method) }}</td>
                    <td>{{ number_format($payment->amount, 2) }}</td>
                    <td>{{ $payment->note ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align:right;font-weight:bold;">Total Paid:</td>
                <td colspan="2" style="font-weight:bold;">{{ number_format($totalPaid,2) }}</td>
            </tr>
        </tfoot>
    </table>
</x-app-layout>
