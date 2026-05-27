<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Purchases Return Details') }}
        </h2>
        <style>
            .header{
                    display:flex;
                    align-items:center;
                    border-bottom:1px solid #333;
                    padding-bottom:5px;
                    margin-bottom:5px;
                }
                .logo{width:60px;margin-right:12px;}
                
            </style>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="header">
                        <img src="{{ asset('images/logo.png') }}" class="logo">
                        <div class="company-info">
                            <strong>Nafis Trade Corporation</strong><br>
                            Kalibari Road, (Nearby Shaheen School)<br>
                            Monirampur, Shahzadpur, Sirajgonj<br>
                            Phone: 01712407042
                        </div>
                    </div>
                    <h2 class="text-center"><strong>Purchase Return Details</strong></h2>
                    <br>
                    <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-4">
                    <strong>Invoice No:</strong> {{ $purchaseReturn->invoice_no }}
                </div>
                <div class="col-md-8 text-right">
                    <strong>Return Date:</strong> {{ \Carbon\Carbon::parse($purchaseReturn->return_date)->format('d-m-Y') }}
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-4">
                    <p><strong>Supplier:</strong>{{ $purchaseReturn->supplier->supplier_name ?? 'N/A' }}</p>
                    {{ $purchaseReturn->supplier->address ?? 'N/A' }} | {{ $purchaseReturn->supplier->mobile ?? 'N/A' }}
                    
                </div>
            </div>
        </div>
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="5%">#</th>
                        <th>Product</th>
                        <th width="10%">Qty</th>
                        <!-- <th width="10%">Unit</th> -->
                        <th width="15%">Price</th>
                        <th width="15%">Total</th>
                        <th>Body Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchaseReturn->items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                {{ $item->product->product_name ?? 'Product #' . $item->product_id }}
                            </td>
                            <td>{{ $item->qty }} {{ $item->unit }}</td>
                            <td>{{ number_format($item->price, 2) }}</td>
                            <td>{{ number_format($item->total, 2) }}</td>
                            <td>{{ $item->reason ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                No items found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">Grand Total</th>
                        <th>{{ number_format($purchaseReturn->total_amount, 2) }}</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
            <div class="col-md-8">
                <strong>Note:</strong>{{ $purchaseReturn->note ?? '-' }}
                </div>
                <div class="mt-3">
                    <a href="{{ route('purchase-return.pdf', $purchaseReturn->id) }}" class="btn btn-primary">Download PDF</a>
                    <a href="{{ route('purchase-return.index') }}" class="btn btn-secondary">Back</a>
                </div>
                </div>
                
            </div>
        </div>
        <br>
        <span> @include('layouts.footer')</span>
    </div>
    </div>
</x-app-layout>
