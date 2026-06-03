<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Product List') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                <div class="row">
                    <div class="col-md-8">    
                        <a href="{{ route('product.productform') }}" class="btn btn-primary d-inline-block mb-3">Add New Product</a>
                    </div>
                    <div class="col-md-4">
                        <form method="GET" action="{{ route('product.index') }}" class="mb-3" id="searchForm">
                            <div style="display:flex; gap:10px;">
                                <input type="text" id="search" name="search" class="form-control" placeholder="Search product / code" value="{{ request('search') }}">
                                <button class="btn btn-primary">Search</button>
                            </div>
                        </form>
                    </div>
                </div>
                    <table class="table table-bordered table-striped">
                        <thead class="thead-light">
                            <tr class="text-center">
                                <th scope="col">SL NO.</th>
                                <th scope="col">PRODUCT NAME</th>
                                 <th scope="col">PURCHASES PRICE</th>
                                 <th scope="col">SALLING PRICE</th>
                                  <th scope="col">STOCK</th>
                                <th scope="col">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                            <tr>
                                <th scope="row">{{ $products->firstItem() + $loop->index }}</th> <!-- পেজিনেশন SL ফিক্স -->
                                <td>{{$product->product_name}}</td>
                                <td class="text-right">{{ number_format($product->purchase_price ?? 0, 2) }}</td>
                                <td class="text-right">{{ number_format($product->selling_price ?? 0, 2) }}</td> <!-- টাইপো ফিক্স -->
                                <td class="text-center">{{ number_format($product->stock ?? 0, 0) }}</td>
                                <td class="text-center">   
                                    <a href="{{route('product.edit',$product)}}" class="btn btn-primary">Edit</a>
                                    <form action="{{route('product.destroy',$product)}}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4">
                        {{ $products->withQueryString()->links() }}
                    </div>
                </div>
                
            </div>
        </div>
        <br>
        <span> @include('layouts.footer')</span>
    </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        if (new URLSearchParams(window.location.search).has('search')) {
            document.getElementById('search').value = '';
        }
    });
</script>
</x-app-layout>
