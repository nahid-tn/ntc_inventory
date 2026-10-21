<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Old Product List') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">

               <a href="{{ route('oldsale.createform') }}" class="btn btn-info mb-4">Create Old Product Sale</a>
                <table class="table table-bordered table-striped">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product Name</th>
                            {{-- <th>Category</th> --}}
                            <th>Stock Qty</th>
                            <th>Buy Price</th>
                            <th>Sale Price</th>
                            <th>Total Stock Value</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $key => $product)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $product->product_name ?? $product->name }}</td>
                            {{-- <td>{{ $product->category->name ?? 'N/A' }}</td> --}}
                            <td>{{ $product->qty }} {{ $product->unit }}</td>
                            <td>{{ number_format($product->buy_price,2) }}</td>
                            <td>{{ number_format($product->price,2) }}</td>
                            <td>{{ number_format($product->qty * $product->buy_price,2) }}</td>
                            <td></td>
                        </tr>
                        @endforeach
                        @if($products->isEmpty())
                            <tr><td colspan="8" class="text-center">No products found.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        <br>
        <span>@include('layouts.footer')</span>
    </div>

</x-app-layout>
