<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Old Product List') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <a href="{{ route('oldsale.createform') }}" class="btn btn-primary mb-4">Create Old Product Sale</a>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Invoice No</th>
                            <th>Customer</th>
                            <th>Sale Date</th>
                            <th>Subtotal</th>
                            <th>Total Amount</th>
                            <th>Paid</th>
                            <th>Due</th>
                            <th>Details</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sales as $key => $sale)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $sale->invoice_no }}</td>
                                <td>{{ $sale->customer->customer_name ?? 'N/A' }}</td>
                                <td>{{ $sale->sale_date->format('d-m-Y') }}</td>
                                <td>{{ number_format($sale->subtotal, 2) }}</td>
                                <td>{{ number_format($sale->total_amount, 2) }}</td>
                                <td>{{ number_format($sale->paid_amount, 2) }}</td>
                                <td>{{ number_format($sale->due_amount, 2) }}</td>
                                <td>
                                    <button class="btn btn-sm btn-info" type="button" data-bs-toggle="collapse" data-bs-target="#details-{{ $sale->id }}">
                                        View
                                    </button>
                                    <div class="collapse mt-2" id="details-{{ $sale->id }}">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Qty</th>
                                                    <th>Unit</th>
                                                    <th>Price</th>
                                                    <th>Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($sale->details as $detail)
                                                    <tr>
                                                        <td>{{ $detail->oldProduct->product_name ?? 'N/A' }}</td>
                                                        <td>{{ $detail->qty }}</td>
                                                        <td>{{ $detail->unit }}</td>
                                                        <td>{{ number_format($detail->price,2) }}</td>
                                                        <td>{{ number_format($detail->subtotal,2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('oldsale.show', $sale->id) }}" class="btn btn-sm btn-success">View</a>
                                </td>
                            </tr>
                        @endforeach
                        @if($sales->isEmpty())
                            <tr>
                                <td colspan="9" class="text-center">No old product sales found.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
       <br>
        <span> @include('layouts.footer')</span>
    </div>
    </div>
</x-app-layout>
