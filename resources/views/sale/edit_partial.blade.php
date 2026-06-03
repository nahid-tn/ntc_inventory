<x-app-layout>
    <x-slot name="header">
        <h2>Partial Payment Edit</h2>
    </x-slot>

    <div class="container">
        <div class="py-6"> 
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8"> 
                @if(session('success')) 
                <div class="alert alert-success"> {{ session('success') }} </div>
         @endif 
         <div class="bg-white shadow-sm rounded-lg p-4"> 
            <div class="text-center mb-4"> <h2>নাফিস ট্রেড কর্পোরেশন</h2> 
                <h4>Partial Payment Edit</h4> </div> 
                <div class="row mb-3"> 
                    <div class="col-md-6"> <strong>Invoice No:</strong> {{ $sale->invoice_no }} </div> 
                    <div class="col-md-6"> <strong>Date:</strong> {{ \Carbon\Carbon::parse($sale->sale_date)->format('d-m-Y') }} </div> 
                </div> 
                <div class="row mb-3"> 
                    <div class="col-md-6"> <strong>Customer:</strong> {{ $sale->customer->customer_name ?? '' }} </div> 
                    <div class="col-md-6"> <strong>Mobile:</strong> {{ $sale->customer->mobile ?? '' }} </div> 
                </div> 
                <hr> 
                <h4>Add New Payment</h4> 
                <form action="{{ route('sales.receivePayment', $sale->id) }}" method="POST"> 
                    @csrf 
                    <div class="row"> 
                        <div class="col-md-3"> <label>Amount</label> <input type="number" step="0.01" name="amount" class="form-control" required> </div> 
                        <div class="col-md-3"> <label>Date</label> <input type="date" name="received_date" value="{{ date('Y-m-d') }}" class="form-control"> </div> 
                        <div class="col-md-3"> <label>Method</label> 
                            <select name="received_method" class="form-control" required> 
                                <option value="cash">Cash</option> 
                                <option value="bank">Bank</option> 
                                <option value="bkash">Bkash</option> 
                                <option value="nagad">Nagad</option> 
                        </select> 
                    </div> 
                    <div class="col-md-3"> <label>Tracking No</label> 
                        <input type="text" name="tracking_no" class="form-control"> 
                    </div> 
                </div> 
                <br> 
                <button type="submit" class="btn btn-success"> Save Payment </button> 
            </form> 
            <hr> 
            <h4>Payment History</h4> 
            <table class="table table-bordered table-striped"> 
                <thead> 
                    <tr> 
                        <th>SL</th> 
                        <th>Date</th>
                        <th>Amount</th> 
                        <th>Method</th> 
                        <th>Tracking No</th> 
                        <th width="180">Action</th> 
                    </tr> 
                </thead> 
                        <tbody> 
                            @forelse($sale->receivedPayments as $payment) 
                            <tr> 
                                <form action="{{ route('sale.updatePartialPayment',$payment->id) }}" method="POST"> 
                                    @csrf @method('PUT') 
                                    <td>{{ $loop->iteration }}</td> 
                                    <td> <input type="date" name="received_date" value="{{ $payment->received_date }}" class="form-control"> </td> 
                                    <td> <input type="number" step="0.01" name="amount" value="{{ $payment->amount }}" class="form-control"> </td> 
                                    <td> <input type="text" name="received_method" value="{{ $payment->received_method }}" class="form-control"> </td> 
                                    <td> <input type="text" name="tracking_no" value="{{ $payment->tracking_no }}" class="form-control"> </td> 
                                    <td> 
                                        <button class="btn btn-primary btn-sm"> Update </button> 
                                </form> 
                                    <form action="{{ route('sale.deletePartialPayment',$payment->id) }}" method="POST" style="display:inline"> 
                                        @csrf 
                                        @method('DELETE')
                                    <button class="btn btn-danger btn-sm" onclick="return confirm('Delete Payment?')"> Delete </button> 
                                </form> 
                                </td> 
                            </tr> 

                        @empty 
                            <tr> 
                                <td colspan="6"> No Payment Found </td> 
                            </tr> 
                        @endforelse 

                        </tbody> 
                        </table> 
                        @php 
                            $receivedTotal = $sale->receivedPayments->sum('amount'); 
                            $totalPaid = $sale->paid_amount + $receivedTotal; 
                            $dueAmount = $sale->total_amount - $totalPaid;
                        @endphp 
                        <div class="card p-3 mt-4"> <h5>Total Amount : {{ number_format($sale->total_amount,2) }} </h5> 
                            <h5 style="color:green;"> Paid Amount : {{ number_format($totalPaid,2) }} </h5> 
                            <h5 style="color:red;"> Due Amount : {{ number_format($dueAmount,2) }} </h5> 
                        </div> 
                        <br> 
                        <button type="button" class="btn btn-secondary" onclick="history.back()">← Back</button> 
                        <a href="{{ route('sale.partialPaymentPdf',$sale->id) }}" target="_blank" class="btn btn-danger"> Download PDF </a>
                    </div> 
                    </div> 
                    </div>
    </div>
</x-app-layout>