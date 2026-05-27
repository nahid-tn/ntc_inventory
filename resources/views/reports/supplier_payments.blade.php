<x-app-layout>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Supplier Due Report
    </h2>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg p-6">

        {{-- Success Message --}}
        @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif

        <form method="GET">
            <div class="row mb-3">
                <div class="col-md-3">
                    <label>From</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-3">
                    <label>To</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-3">
                    <label>Supplier</label>
                    <select name="supplier_id" class="form-control">
                        <option value="">All</option>
                        @foreach(\App\Models\Supplier::all() as $s)
                            <option value="{{ $s->id }}" {{ request('supplier_id')==$s->id?'selected':'' }}>
                                {{ $s->supplier_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mt-4">
                    <button class="btn btn-primary">Search</button>
                    <button name="pdf" value="1" class="btn btn-danger">PDF</button>
                </div>
            </div>
        </form>

<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>Supplier</th>
            <th>Total Purchase</th>
            <th>Purchase Payment</th>
            <th>Partial payment</th>
            <th>Total Payment With Return</th>
            <th>Total Return</th>
            <th>Due</th>
        </tr>
    </thead>
    <tbody>
    @foreach($reports as $row)
    <tr>
        <td>{{ $row['supplier']->supplier_name }}</td>
        <td>{{ number_format($row['totalPurchase'],2) }}</td>
        <td>{{ number_format($row['purchasePayment'],2) }}</td>
        <td>{{ number_format($row['supplierPayment'],2) }}</td>
        <td class="fw-bold text-success">{{ number_format($row['totalPayment'],2) }}</td>
        <td class="text-danger">{{ number_format($row['totalReturn'],2) }}</td>
        <td class="text-danger fw-bold">{{ number_format($row['due'],2) }}</td>
    </tr>
    @endforeach
    </tbody>
</table> 
        </div>
        <br>
            <span class="flex justify-center"> @include('layouts.footer')</span>
    </div>
</div>

</x-app-layout>
