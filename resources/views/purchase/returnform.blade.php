<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Purchase Return') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('purchase.return.store', $purchase->id) }}" method="POST">
                    @csrf
                    <table class="table">
                        <tr>
                            <th>Product</th>
                            <th>Purchased Qty</th>
                            <th>Return Qty</th>
                            <th>Reason</th>
                        </tr>
                        @foreach($purchase->details as $detail)
                        <tr>
                            <td>{{ $detail->product->name }}</td>
                            <td>{{ $detail->qty }}</td>
                            <td>
                                <input type="number" name="qty[]" max="{{ $detail->qty }}" value="0" class="form-control">
                                <input type="hidden" name="product_id[]" value="{{ $detail->product_id }}">
                            </td>
                            <td><input type="text" name="reason[]" class="form-control" required></td>
                            <td><input type="text" name="note[]" class="form-control"></td>
                        </tr>
                        @endforeach
                    </table>
                    <button type="submit" class="btn btn-danger">Return Products</button> | <button type="button" class="btn btn-secondary" onclick="history.back()">
    ← Back
</button>
                </form>
                </div>
            </div>
        </div>
        <br>
        <span> @include('layouts.footer')</span>
    </div>
    </div>
</x-app-layout>

