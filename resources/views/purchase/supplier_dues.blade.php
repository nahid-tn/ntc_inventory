<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-center">Supplier Due List</h2> <!-- centered header -->
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Card --}}
            <div class="bg-white shadow rounded p-4">

                {{-- 🔍 Search Form --}}
                <div class="py-6 flex justify-center">
                    <form method="GET" action="{{ route('supplier.dues') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4 w-full max-w-4xl">

                        <div class="row g-3">
                            <!-- Supplier Name -->
                            <div class="col-md-6">
                                <label for="supplier" class="form-label">Supplier Name</label>
                                <input type="text" id="supplier" name="supplier" value="{{ request('supplier') }}" placeholder="Supplier Name" class="form-control">
                            </div>

                            <!-- Invoice No -->
                            <div class="col-md-6">
                                <label for="invoice" class="form-label">Invoice No</label>
                                <input type="text" id="invoice" name="invoice" value="{{ request('invoice') }}" placeholder="Invoice No" class="form-control">
                            </div>
                        </div>

                        <div class="row g-3 align-items-end">
                            <!-- From Date -->
                            <div class="col-md-4">
                                <label for="from" class="form-label">From Date</label>
                                <input type="date" name="from" id="from" value="{{ request('from') }}" class="form-control">
                            </div>

                            <!-- To Date -->
                            <div class="col-md-4">
                                <label for="to" class="form-label">To Date</label>
                                <input type="date" name="to" id="to" value="{{ request('to') }}" class="form-control">
                            </div>

                            <!-- Button -->
                            <div class="col-md-4 d-flex justify-content-start justify-content-md-end">
                                <button class="btn btn-primary w-100 w-md-auto">Search</button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- 📋 Result Table --}}
                <div class="bg-white shadow rounded overflow-x-auto mt-6">
                    <table class="table table-bordered w-full text-center">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Supplier</th>
                                <th>Invoice No</th>
                                <th>Purchase Amount</th>
                                <th>Paid Amount</th>
                                <th>Due Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dues as $index => $p)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="text-left">{{ $p->supplier->supplier_name }}</td>
                                <td>
                                    <a href="{{ route('purchase.view', $p->id) }}" class="text-blue-600 underline">
                                        {{ $p->Invoice_no }}
                                    </a>
                                </td>
                                <td class="text-right">{{ number_format($p->total_amount, 2) }}</td>
                                <td class="text-right">{{ number_format($p->paid_amount, 2) }}</td>
                                <td class="text-right">{{ number_format($p->due_amount, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-red-600">No dues found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
            <br>
            <span class="flex justify-center"> @include('layouts.footer')</span>
        </div>
    </div>
</x-app-layout>

<script>
$(function () {
    // Supplier Autocomplete
    $("#supplier").autocomplete({
        source: "{{ route('autocomplete.supplier') }}",
        minLength: 1
    });

    // Invoice Autocomplete
    $("#invoice").autocomplete({
        source: "{{ route('autocomplete.invoice') }}",
        minLength: 1
    });
});
</script>
