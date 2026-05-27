<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Sale Partial Payment Edit
        </h2>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="bg-white p-4 shadow-sm rounded-lg">
            {{-- Sale Info --}}
            <h4>Sale Info</h4>
            <p><strong>Invoice No:</strong> {{ $sale->invoice_no }}</p>
            <p><strong>Customer:</strong> {{ $sale->customer->customer_name ?? '' }}</p>
            <p><strong>Total Amount:</strong> {{ number_format($sale->total_amount,2) }}</p>
            @php
                $totalReceived = $sale->receivedPayments->sum('amount');
                $dueAmount = $sale->total_amount - $totalReceived;
            @endphp
            <p><strong>Paid Amount:</strong> {{ number_format($totalReceived,2) }}</p>
            <p><strong>Due Amount:</strong> {{ number_format($dueAmount,2) }}</p>

            <hr class="my-3">

            {{-- Add New Partial Payment --}}
            <h4>Add Partial Payment</h4>
            <form action="{{ route('sale.savePartialPayment', $sale->id) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <input type="number" name="amount" placeholder="Payment Amount" step="any" class="form-control" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <input type="date" name="received_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <select name="received_method" id="new_received_method" class="form-control" onchange="toggleNewOtherField()">
                            <option value="">--Select Method--</option>
                            <option value="cash">Cash</option>
                            <option value="bank">Bank</option>
                            <option value="other">Other</option>
                        </select>
                        <input type="text" name="received_method_other" id="new_received_method_other" placeholder="Write method" class="form-control mt-1" style="display:none;">
                    </div>
                    <div class="col-md-3 mb-2">
                        <input type="text" name="tracking_no" placeholder="Tracking No." class="form-control">
                    </div>
                </div>
                <button type="submit" class="btn btn-success mt-2">Add Payment</button>
            </form>

            <hr class="my-3">

            {{-- Payment History --}}
            <h4>Payment History</h4>
            @if($sale->receivedPayments->count())
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Tracking No</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->receivedPayments as $index => $payment)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <input type="date" form="updateForm_{{ $payment->id }}" name="received_date" value="{{ \Carbon\Carbon::parse($payment->received_date)->format('Y-m-d') }}" class="form-control">
                        </td>
                        <td>
                            <input type="number" form="updateForm_{{ $payment->id }}" name="amount" value="{{ $payment->amount }}" step="any" class="form-control">
                        </td>
                        <td style="min-width:150px;">
                            <select form="updateForm_{{ $payment->id }}" name="received_method" id="received_method_{{ $payment->id }}" class="form-control" onchange="toggleOtherField({{ $payment->id }})">
                                <option value="cash" {{ $payment->received_method=='cash'?'selected':'' }}>Cash</option>
                                <option value="bank" {{ $payment->received_method=='bank'?'selected':'' }}>Bank</option>
                                <option value="other" {{ $payment->received_method=='other'?'selected':'' }}>Other</option>
                            </select>
                            <input type="text" form="updateForm_{{ $payment->id }}" name="received_method_other" id="received_method_other_{{ $payment->id }}" value="{{ $payment->received_method_other ?? '' }}" class="form-control mt-1" placeholder="Write method" style="display:{{ $payment->received_method=='other'?'block':'none' }};">
                        </td>
                        <td>
                            <input type="text" form="updateForm_{{ $payment->id }}" name="tracking_no" value="{{ $payment->tracking_no }}" class="form-control">
                        </td>
                        <td>
                            <form id="updateForm_{{ $payment->id }}" action="{{ route('sale.updatePartialPayment', $payment->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-sm btn-primary mb-1">Update</button>
                            </form>
                            <form action="{{ route('sale.deletePartialPayment', $payment->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <button type="button" class="btn btn-secondary" onclick="history.back()">← Back</button>
            @else
            <p>No payments yet.</p>
            @endif
        </div>
    </div>

    <script>
        // Toggle for existing payment rows
        function toggleOtherField(id) {
            let select = document.getElementById('received_method_' + id);
            let otherField = document.getElementById('received_method_other_' + id);
            if(select.value === 'other') {
                otherField.style.display = 'block';
                otherField.required = true;
            } else {
                otherField.style.display = 'none';
                otherField.required = false;
            }
        }

        // Toggle for new payment form
        function toggleNewOtherField() {
            let select = document.getElementById('new_received_method');
            let otherField = document.getElementById('new_received_method_other');
            if(select.value === 'other') {
                otherField.style.display = 'block';
                otherField.required = true;
            } else {
                otherField.style.display = 'none';
                otherField.required = false;
            }
        }
    </script>
</x-app-layout>