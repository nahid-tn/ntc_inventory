<x-app-layout>

<x-slot name="header">
<h2 class="font-semibold text-xl text-gray-800">
    Supplier Ledger
</h2>
</x-slot>
<div class="py-12">
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
<div class="bg-white shadow rounded">
<div class="p-6">
    {{-- Header --}}
<h3 class="mb-3 text-primary">
    {{ $supplier->supplier_name }} Ledger
</h3>

{{-- Search --}}
<form action="{{ route('supplier.ledger',$supplier->id) }}" method="GET" class="mb-4">
    <div class="row g-2">
        <div class="col-md-3"><input type="date" name="from" class="form-control" value="{{ $from }}"></div>
        <div class="col-md-3"><input type="date" name="to" class="form-control" value="{{ $to }}"></div>
        <div class="col-md-4">
            <input type="text" id="supplier_name" class="form-control" value="{{ $supplier->supplier_name }}">
            <input type="hidden" name="supplier_id" id="supplier_id" value="{{ $supplier->id }}">
        </div>
        <div class="col-md-2"><button class="btn btn-primary w-100">Search</button></div>
        <div><a href="{{ route('supplier.ledger.pdf',$supplier->id) }}?from={{ $from }}&to={{ $to }}" class="btn btn-danger mb-3"> 📄 Download PDF </a></div>
        {{-- <div><a href="{{ route('supplier.bank_statement.pdf',$supplier->id) }}?from={{ $from }}&to={{ $to }}" class="btn btn-danger mb-3"> 📄 Bank Statement PDF </a></div> --}}
    </div>
</form>

{{-- Summary --}}
<div class="row mb-4 text-center">
    <div class="col-md-4">
        <div class="p-2 border rounded bg-success"><b>Total Purchase</b><br><a href="{{ route('purchase.index', $supplier->id) }}" class="text-white" target="_blank">
        {{ number_format($totalPurchase,2) }}
    </a></div>
        
    </div>
    <div class="col-md-4">
        <div class="p-2 border rounded bg-success"><b>Total Paid</b><br><a href="{{ route('supplier.payment.list', $supplier->id) }}" class="text-white" target="_blank">
        {{ number_format($totalPaidWithReturn,2) }}
    </a></div>
    </div>
    <div class="col-md-4">
        <div class="p-2 border rounded bg-warning"><b>Balance</b><br>{{ number_format($balance,2) }}</div>
    </div>
</div>

{{-- Ledger Table --}}
<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="table-dark text-center">
            <tr>
                <th>Date</th>
                <th>Description</th>
                <th width="120">Debit</th>
                <th width="120">Credit</th>
                <th width="140">Balance</th>
            </tr>
        </thead>
        <tbody>
            @php $running = 0; @endphp
            @forelse($ledger as $row)
            @if(($row['type'] ?? '') != 'shipping')
                @php
                    $running += $row['debit'];
                    $running -= $row['credit'];
                @endphp
            @endif
            <tr>
                <td class="text-center">{{ \Carbon\Carbon::parse($row['date'])->format('d-m-Y') }}</td>
                <td>
                    {{ $row['note'] }}
                    {{-- Shipping Badge --}}
                    @if(($row['type'] ?? '') == 'shipping')
                        <span class="badge bg-info ms-2">
                            {{ number_format($row['shipping'],2) }}
                        </span>
                    @endif
                </td>
                <td class="text-end text-danger">{{ $row['debit'] ? number_format($row['debit'],2) : '-' }}</td>
                <td class="text-end text-success">{{ $row['credit'] ? number_format($row['credit'],2) : '-' }}</td>
                <td class="text-end fw-bold">{{ number_format($running,2) }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-danger">No Data Found</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
</div>
</div>
<br>
            <span class="flex justify-center"> @include('layouts.footer')</span>
</div>
</div>

{{-- Autocomplete --}}
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script>
        $(function(){
            $("#supplier_name").autocomplete({
                source:function(request,response){
                    $.ajax({
                        url:"{{ url('/supplier-autocomplete') }}",
                        data:{ term:request.term },
                        success:function(data){
                            response($.map(data,function(item){
                                return{
                                    label:item.value + ' | ' + item.mobile,
                                    value:item.value,
                                    id:item.id
                                };
                            }));
                        }
                    });
                },
                select:function(e,ui){
                    $("#supplier_id").val(ui.item.id);
                    $("#supplier_name").val(ui.item.value);
                    return false;
                },
                minLength:1
            });
        });
    </script>
</x-app-layout>
