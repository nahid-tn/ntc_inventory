<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Payment Details
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- ===== Supplier Due Summary ===== --}}
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <small class="text-muted">Total Purchase</small>
                                <h5 class="mb-0 text-primary">
                                    {{ number_format($totalPurchase, 2) }}
                                </h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <small class="text-muted">Total Paid</small>
                                <h5 class="mb-0 text-success">
                                    {{ number_format($totalPaid, 2) }}
                                </h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-warning">
                            <div class="card-body text-center">
                                <small class="text-muted">Total Return</small>
                                <h5 class="mb-0 text-warning">
                                    {{ number_format($totalReturn, 2) }}
                                </h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card {{ $balance > 0 ? 'border-danger' : 'border-secondary' }}">
                            <div class="card-body text-center">
                                <small class="text-muted">Current Total Due</small>
                                <h5 class="mb-0 {{ $balance > 0 ? 'text-danger' : 'text-secondary' }}">
                                    {{ number_format($balance, 2) }}
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ছোট করে সারসংক্ষেপ লাইন --}}
                <p class="text-muted small mb-3">
                    <strong>Due Formula:</strong>
                    Total Purchase ({{ number_format($totalPurchase,2) }})
                    − Total Paid ({{ number_format($totalPaid,2) }})
                    − Total Return ({{ number_format($totalReturn,2) }})
                    = <strong class="{{ $balance > 0 ? 'text-danger' : 'text-success' }}">
                        {{ number_format($balance,2) }}
                    </strong>
                </p>

                <h5 class="mb-3">Payment Info</h5>

                <table class="table table-bordered">
                    <tr>
                        <th>Supplier</th>
                        <td>{{ $payment->supplier->supplier_name }}</td>
                    </tr>
                    <tr>
                        <th>Invoice</th>
                        <td>{{ $payment->purchase ? $payment->purchase->Invoice_no : 'Without Invoice' }}</td>
                    </tr>

                    @if($invoiceDue !== null)
                        <tr>
                            <th>This Invoice Due</th>
                            <td class="{{ $invoiceDue > 0 ? 'text-danger fw-bold' : 'text-success fw-bold' }}">
                                {{ number_format($invoiceDue, 2) }}
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <th>Amount (This Payment)</th>
                        <td>{{ number_format($payment->amount, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Payment Method</th>
                        <td>{{ $payment->payment_method }}</td>
                    </tr>

                    @if($payment->payment_method === 'Cheque')
                        @if($payment->cheque_number)
                            <tr>
                                <th>Cheque Number</th>
                                <td>{{ $payment->cheque_number }}</td>
                            </tr>
                        @endif
                        @if($payment->cheque_image)
                            <tr>
                                <th>Cheque Image</th>
                                <td>
                                    <a href="{{ asset('storage/' . $payment->cheque_image) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $payment->cheque_image) }}"
                                             alt="Cheque Image"
                                             style="max-height:150px; border:1px solid #ccc; padding:3px;">
                                    </a>
                                </td>
                            </tr>
                        @endif
                    @endif

                    @if($payment->payment_method === 'Bank')
                        @if($payment->BankName)
                            <tr>
                                <th>Bank Name</th>
                                <td>{{ $payment->BankName }}</td>
                            </tr>
                        @endif
                        @if($payment->account_number)
                            <tr>
                                <th>A/C No.</th>
                                <td>{{ $payment->account_number }}</td>
                            </tr>
                        @endif
                    @endif

                    @if(in_array($payment->payment_method, ['Bank', 'Mobile Banking']))
                        @if($payment->transaction_number)
                            <tr>
                                <th>Transaction / Tracking Number</th>
                                <td>{{ $payment->transaction_number }}</td>
                            </tr>
                        @endif
                        @if($payment->deposit_slip)
                            <tr>
                                <th>Deposit Slip / Transaction Image</th>
                                <td>
                                    <a href="{{ asset('storage/' . $payment->deposit_slip) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $payment->deposit_slip) }}"
                                             alt="Deposit Slip"
                                             style="max-height:150px; border:1px solid #ccc; padding:3px;">
                                    </a>
                                </td>
                            </tr>
                        @endif
                    @endif

                    <tr>
                        <th>Payment Date</th>
                        <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') }}</td>
                    </tr>
                </table>

                <div class="d-flex gap-2 mt-3">
                    <a href="{{ route('supplier.payment.edit', $payment->id) }}" class="btn btn-primary">Edit</a>

                    <form action="{{ route('supplier.payment.destroy', $payment->id) }}" method="POST"
                          onsubmit="return confirm('Are you sure to delete?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>

                    <a href="{{ route('supplier.ledger', $payment->supplier_id) }}"
                       class="btn btn-info text-white">
                        View Ledger
                    </a>

                    <button type="button" class="btn btn-secondary" onclick="history.back()">← Back</button>
                </div>

            </div>
        </div>
    </div>

    @include('layouts.footer')
</x-app-layout>