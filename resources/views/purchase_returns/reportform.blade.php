<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Purchase Return Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                        <a href="{{ route('purchase-return.create') }}" class="btn btn-primary mb-3">Return Create</a>
                    {{-- Filter Form --}}
                    <form method="GET" action="{{ route('purchase-return.reportform') }}" class="mb-4">
                        <div class="row g-3">

                            <div class="col-md-3">
                                <label>Supplier</label>
                                <select name="supplier_id" class="form-control">
                                    <option value="">All Suppliers</option>
                                    @foreach($suppliers as $s)
                                        <option value="{{ $s->id }}" @selected(request('supplier_id') == $s->id)>
                                            {{ $s->supplier_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label>Product</label>
                                <select name="product_id" class="form-control">
                                    <option value="">All Products</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}" @selected(request('product_id') == $p->id)>
                                            {{ $p->product_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label>From Date</label>
                                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" required>
                            </div>
                            <div class="col-md-2">
                                <label>To Date</label>
                                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Search</button>
                            </div>
                        </div>
                    </form>

                    {{-- Generate PDF --}}
                    @if($returns && $returns->count() > 0)
                        <form method="POST" action="{{ route('purchase-return.reportgenerate') }}" class="mb-4">
                            @csrf
                            <input type="hidden" name="supplier_id" value="{{ request('supplier_id') }}">
                            <input type="hidden" name="product_id" value="{{ request('product_id') }}">
                            <input type="hidden" name="from_date" value="{{ request('from_date') }}">
                            <input type="hidden" name="to_date" value="{{ request('to_date') }}">
                            <button type="submit" class="btn btn-success mb-4">
                                <i class="fa fa-file-pdf"></i> Return Generate PDF
                            </button>
                        </form>
                    @endif

                    {{-- Report Table --}}
                    @if($returns && $returns->count() > 0)
                        @php
                            $totalQty = 0;
                            $totalAmount = 0;
                        @endphp
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Invoice No</th>
                                        <th>Supplier</th>
                                        <th>Product</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-center">Unit</th>
                                        <th class="text-center">Total Amount</th>
                                        <th class="text-center">Return Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($returns as $r)
                                        
                                        @foreach($r->items as $item)
                                            @php
                                                $totalQty += $item->qty; // detail table
                                                $totalAmount += $item->total;
                                            @endphp
                                            <tr>
                                                <td>{{ $r->invoice_no ?? 'N/A' }}</td>
                                                <td>{{ optional($r->supplier)->supplier_name ?? 'N/A' }}</td>
                                                <td>{{ optional($item->product)->product_name ?? 'N/A' }}</td>
                                                <td class="text-end">{{ $item->qty ?? 0 }}</td>
                                                <td class="text-center">{{ $item->unit ?? '' }}</td>
                                                <td class="text-center">{{ $item->total ?? 0 }}</td>
                                                <td class="text-center">{{ $r->return_date ? \Carbon\Carbon::parse($r->return_date)->format('d-m-Y') : 'N/A' }}</td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>

                                {{-- TOTAL FOOTER --}}
                                <tfoot class="fw-bold bg-light">
                                    <tr>
                                        <td colspan="3" class="text-end"> Total Quantity </td>
                                        <td class="text-end"> {{ $totalQty }} </td>
                                        <td class="text-center">Total Amount</td>
                                        <td class="text-center">{{ $totalAmount }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @elseif(request()->filled(['from_date','to_date']))
                        <p class="text-muted">No records found for selected filter.</p>
                    @endif

                    <button type="button" class="btn btn-secondary mt-3" onclick="history.back()">← Back</button>

                </div>
            </div>
        </div>

        <br>
        <span>@include('layouts.footer')</span>
    </div>
</x-app-layout>