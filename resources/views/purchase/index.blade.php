<x-app-layout>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800">
        Purchase Report
    </h2>
    <style>
        @page { margin: 50px 50px; }
        @font-face {
            font-family: 'SolaimanLipi';
            src: url("{{ public_path('fonts/SolaimanLipi.ttf') }}") format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        body {
            font-family: 'SolaimanLipi', sans-serif;

            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        /* Table */
        table {

            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #999;
        }
        th, td {
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }

        /* Totals */
        .totals {
            width: 40%;
            float: right;
            margin-top: 10px;
        }
        .totals table {
            border: none;
        }
        .totals th, .totals td {
            border: none;
            text-align: right;
            padding: 5px 10px;
        }
    </style>
</x-slot>
<div class="py-10">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded p-4">
        {{-- ================= SEARCH FORM ================= --}}
       <form method="GET" action="{{ route('purchase.index') }}" class="mb-4">
            <div class="row g-2">
                <div class="col-md-2">
                    <input type="text" name="invoice" value="{{ request('invoice') }}" class="form-control" placeholder="Invoice No">
                </div>
                <div class="col-md-2">
                    <input type="text" id="supplier_search" name="supplier" value="{{ request('supplier') }}" class="form-control" placeholder="Supplier Name">
                    <input type="hidden" id="supplier_id" name="supplier_id">
                </div>
                <div class="col-md-2">
                    <input type="text" id="product_search" name="product" value="{{ request('product') }}" class="form-control" placeholder="Product Name">
                    <input type="hidden" id="product_id" name="product_id">
                </div>

                <div class="col-md-2">
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                </div>

                <div class="col-md-2">
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary">🔍 Search</button>
                    <button type="submit" formaction="{{ route('purchase.report.pdf') }}" formtarget="_blank" class="btn btn-danger"> 📄 PDF </button>
                </div>
            </div>

        </form>

        {{-- ================= SUMMARY REPORT ================= --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h6>Total Purchase</h6>
                        <h4>{{ number_format($totalPurchase,2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h6>Total Paid</h6>
                        <h4>{{ number_format($totalPaymentReturn, 2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h6>Total Due</h6>
                        <h4>{{ number_format($totalDue_D,2) }}</h4>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-warning">
                    <div class="card-body text-center">
                        <h6>Partial Payment</h6>
                        <h4>{{ $partialCount }}</h4>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= PURCHASE TABLE ================= --}}

        <a href="{{ route('purchase.form') }}" class="btn btn-primary mb-3"> ➕ Add Purchase</a>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>SL</th>
                    <th>Invoice</th>
                    <th>Date</th>
                    <th>Supplier</th>
                    <th>Shipping</th>
                    <th>Subtotal</th>
                    <th>Paid</th>
                    <th>Due</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($purchases as $key => $purchase)
                <tr>
                    <td>{{ $purchases->firstItem() + $key }}</td>
                    <td class="text-center" data-bs-toggle="collapse" data-bs-target="#details-{{ $purchase->id }}"><a href="{{ $purchase->Invoice_no }}"> {{ $purchase->Invoice_no }}</a>
                        <div class="collapse mt-2" id="details-{{ $purchase->id }}">
                            <table class="table table-bordered mt-2">
                                <tbody>
                                    @php 
                                        $sl = 1;  // Serial number
                                        $oldTotal = 0; // Old products total
                                    @endphp  

                                    {{-- New Products --}}
                                    @foreach($purchase->details as $item)
                                        <tr>
                                            <td class="text-left">{{ $item->product->product_name ?? '' }}</td>
                                            <td class="text-right">{{ $item->qty }}</td>
                                            <td class="text-right">{{ $item->unit }}</td>
                                            <td style="text-align: right;">{{ number_format($item->price,2) }}</td>
                                            <td style="text-align: right;">{{ number_format($item->total,2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>    
                        </div>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($purchase->date)->format('d-m-Y') }}</td>
                    <td>{{ $purchase->supplier->supplier_name }}</td>
                    <td class="text-right">{{ number_format($purchase->shipping_amount,2) }}</td>
                    <td class="text-right">{{ number_format($purchase->subtotal,2) }}</td>
                    @php
                        $paid = $purchase->total_paid ?? 0;
                        $due  = $purchase->subtotal - $paid;
                    @endphp

                    <td class="text-right">
                        {{ number_format($paid, 2) }}
                    </td>

                    <td class="text-right">
                        {{ number_format($due, 2) }}
                    </td>
                    <td>
                        <a href="{{ route('purchase.view',$purchase->id) }}" class="btn btn-sm btn-success">View</a>
                        <a href="{{ route('purchase.edit',$purchase->id) }}" class="btn btn-sm btn-primary">Edit</a>
                        <a href="{{ route('purchase.pdf',$purchase->id) }}" class="btn btn-sm btn-warning">Print</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-danger"> No Data Found </td>
                </tr>
            @endforelse
            </tbody>
        </table>
        {{-- ================= PAGINATION ================= --}}
            <div class="mt-3">{{ $purchases->links() }}</div>
        </div>
        <br>
            <span class="flex justify-center"> @include('layouts.footer')</span>
    </div>
</div>
    <script>

$(document).ready(function(){

// ================= SUPPLIER AUTOCOMPLETE =================

$("#supplier_search").autocomplete({

source: function(request,response){

$.ajax({

url: "{{ url('/supplier-autocomplete') }}",

dataType:"json",

data:{
term: request.term
},

success:function(data){

response($.map(data,function(item){

return{

label: item.value + " | " + item.mobile,
value: item.value,
id: item.id

};

}));

}

});

},

minLength:1,

select:function(event,ui){

$("#supplier_search").val(ui.item.value);
$("#supplier_id").val(ui.item.id);

return false;

}

});


// ================= PRODUCT AUTOCOMPLETE =================

$("#product_search").autocomplete({

source:function(request,response){

$.ajax({

url:"{{ url('/product-autocomplete') }}",

dataType:"json",

data:{
term: request.term
},

success:function(data){

response($.map(data,function(item){

return{

label: item.value + " | Stock: " + item.stock,
value: item.value,
id: item.id

};

}));

}

});

},

minLength:1,

select:function(event,ui){

$("#product_search").val(ui.item.value);
$("#product_id").val(ui.item.id);

return false;

}

});

});

</script>
</x-app-layout>
