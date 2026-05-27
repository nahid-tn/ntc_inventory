<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ __('Expenses List') }}
    </h2>

    <style>
        @page { margin: 50px 50px; }
        @font-face {
            font-family: 'SolaimanLipi';
            src: url("{{ public_path('fonts/SolaimanLipi.ttf') }}") format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        body { font-family: 'SolaimanLipi', sans-serif; line-height:1.4; color:#333; margin:0; padding:0; }

        table { width:100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border:1px solid #999; }
        th, td { padding:8px; text-align:center; }
        th { background-color:#f2f2f2; }
        .totals { width:40%; float:right; margin-top:10px; }
        .totals table { border:none; }
        .totals th, .totals td { border:none; text-align:right; padding:5px 10px; }
    </style>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        @if(session('success'))
            <div class="alert alert-success mb-4">{{ session('success') }}</div>
        @endif

        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
            <a href="{{ route('expense.create') }}" class="btn btn-primary mb-4">➕ Add Expense</a>

            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>SL</th>
                        <th>Date</th>
                        <th>Payment Method</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $key => $exp)
                        <tr>
                            <td>{{ $expenses->firstItem() + $key }}</td>
                            <td>{{ \Carbon\Carbon::parse($exp->expense_date)->format('d-m-Y') }}</td>
                            <td>{{ $exp->payment_method }}</td>
                            <td class="text-left">
                                <a href="#" data-bs-toggle="collapse" data-bs-target="#details-{{ $exp->id }}">
                                    View Items ({{ $exp->items->count() }})
                                </a>
                                <div class="collapse mt-2" id="details-{{ $exp->id }}">
                                    <table class="table table-bordered mt-2">
                                        <thead>
                                            <tr>
                                                <th>SL</th>
                                                <th>Item Name</th>
                                                <th>Qty</th>
                                                <th>Price</th>
                                                <th>Subtotal</th>
                                                <th>Description</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($exp->items as $i => $item)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                
                                                <td class="text-left">{{ $item->item_name }}</td>
                                                <td>{{ $item->qty }}</td>
                                                <td>{{ number_format($item->price,2) }} ৳</td>
                                                <td>{{ number_format($item->subtotal,2) }} ৳</td>
                                                <td class="text-left">{{ $item->description ?? '-' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                            <td class="text-right">{{ number_format($exp->total,2) }} ৳</td>
                            <td>
                                <a href="{{ route('expense.view',$exp->id) }}" class="btn btn-sm btn-success mb-1">View</a>
                                <a href="{{ route('expense.edit',$exp->id) }}" class="btn btn-sm btn-warning mb-1">Edit</a>
                                <form action="{{ route('expense.destroy',$exp->id) }}" method="POST" style="display:inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Are you sure?')" class="btn btn-sm btn-danger mb-1">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-danger">No expenses found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            <div class="mt-3">{{ $expenses->links() }}</div>
        </div>

        <br>
        <span class="flex justify-center">@include('layouts.footer')</span>
    </div>
</div>

</x-app-layout>