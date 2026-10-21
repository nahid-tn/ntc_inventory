<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Old Product Exchange') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- ================= TOP BAR ================= --}}
            <div class="flex justify-between items-center mb-3">
                <h4 class="text-lg font-semibold">Old Product Exchange</h4>
                <a href="{{ route('old_product_exchange.create') }}"
   class="btn btn-primary">
    <i class="fas fa-exchange-alt"></i>
    New Exchange
</a>
            </div>

            {{-- ================= ALERTS ================= --}}
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ================= SEARCH FORM ================= --}}
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('old_product_exchange.index') }}">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <input type="text" name="search"
                                       value="{{ request('search') }}"
                                       class="form-control"
                                       placeholder="Product name">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">From Date</label>
                                <input type="date" name="from_date"
                                       value="{{ request('from_date') }}"
                                       class="form-control">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">To Date</label>
                                <input type="date" name="to_date"
                                       value="{{ request('to_date') }}"
                                       class="form-control">
                            </div>

                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-secondary w-100">Search</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ================= TABLE ================= --}}
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Customer Gives</th>
                                    <th>Weight (kg)</th>
                                    <th>Value</th>
                                    <th>Customer Takes</th>
                                    <th>Weight (kg)</th>
                                    <th>Value</th>
                                    <th>Fee</th>
                                    <th>Settlement</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($exchanges as $exchange)
                                    <tr>
                                        <td>{{ $exchanges->firstItem() + $loop->index }}</td>
                                        <td>{{ $exchange->exchange_date?->format('d-m-Y') }}</td>

                                        <td>{{ $exchange->old_product_name }}</td>
                                        <td>
                                            {{ number_format($exchange->old_weight_kg, 2) }}
                                            {{ $exchange->old_unit }}
                                        </td>
                                        <td>{{ number_format($exchange->old_total, 2) }}</td>

                                        <td>{{ $exchange->exchange_product_name }}</td>
                                        <td>
                                            {{ number_format($exchange->exchange_weight_kg, 2) }}
                                            {{ $exchange->exchange_unit }}
                                        </td>
                                        <td>{{ number_format($exchange->exchange_total, 2) }}</td>

                                        <td>{{ number_format($exchange->exchange_fee, 2) }}</td>

                                        <td>
                                            @if($exchange->settlement_type === 'customer_pay')
                                                <span class="badge bg-danger">Customer Pay</span><br>
                                                <strong>{{ number_format(abs($exchange->settlement_amount), 2) }}</strong>
                                            @elseif($exchange->settlement_type === 'customer_receive')
                                                <span class="badge bg-success">Customer Receive</span><br>
                                                <strong>{{ number_format(abs($exchange->settlement_amount), 2) }}</strong>
                                            @else
                                                <span class="badge bg-secondary">No Adjustment</span><br>
                                                <strong>0.00</strong>
                                            @endif
                                        </td>

                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('old_product_exchange.show', $exchange->id) }}"
                                                   class="btn btn-sm btn-info">View</a>

                                                <a href="{{ route('old_product_exchange.edit', $exchange->id) }}"
                                                   class="btn btn-sm btn-warning">Edit</a>

                                                <a href="{{ route('old_product_exchange.invoice', $exchange->id) }}"
                                                   target="_blank"
                                                   class="btn btn-sm btn-dark">Invoice</a>

                                                <form method="POST"
                                                      action="{{ route('old_product_exchange.destroy', $exchange->id) }}"
                                                      onsubmit="return confirm('আপনি কি এই Exchange delete করতে চান?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center">
                                            কোনো Exchange পাওয়া যায়নি।
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $exchanges->links() }}
                    </div>
                </div>
            </div>

        </div>
        <br>
        <span class="flex justify-center">@include('layouts.footer')</span>
    </div>

</x-app-layout>