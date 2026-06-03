<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
            📦 Product Details
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow rounded p-4">

                {{-- Success Message --}}
                @if(session('success'))
                    <p class="text-green-600">{{ session('success') }}</p>
                @endif

                <div class="row">

                    {{-- Product Name --}}
                    <div class="col-md-6 mb-3">
                        <label>Product Name</label>
                        <input type="text" class="form-control" 
                               value="{{ $product->product_name }}" readonly>
                    </div>

                    {{-- Product Code --}}
                    <div class="col-md-6 mb-3">
                        <label>Product Code</label>
                        <input type="text" class="form-control" 
                               value="{{ $product->product_code }}" readonly>
                    </div>

                </div>

                <div class="row">

                    {{-- Model Name --}}
                    <div class="col-md-6 mb-3">
                        <label>Model Name</label>
                        <input type="text" class="form-control" 
                               value="{{ $product->model_name }}" readonly>
                    </div>

                    {{-- Category --}}
                    <div class="col-md-6 mb-3">
                        <label>Category</label>
                        <input type="text" class="form-control" 
                               value="{{ $product->category->category_name ?? '' }}" readonly>
                    </div>

                </div>

                <div class="row">

                    {{-- Purchase Price --}}
                    <div class="col-md-6 mb-3">
                        <label>Purchase Price</label>
                        <input type="text" class="form-control" 
                               value="{{ number_format($product->purchase_price,2) }}" readonly>
                    </div>

                    {{-- Selling Price --}}
                    <div class="col-md-6 mb-3">
                        <label>Selling Price</label>
                        <input type="text" class="form-control" 
                               value="{{ number_format($product->selling_price,2) }}" readonly>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label>Product Description</label>
                        <textarea class="form-control" rows="4" readonly>{{ $product->description }}</textarea>
                    </div>


                </div>

                <div class="row">

                    {{-- Stock --}}
                    <div class="col-md-6 mb-3">
                        <label>Stock</label>
                        <input type="text" class="form-control" 
                               value="{{ $product->stock }}" readonly>
                    </div>

                    {{-- Status --}}
                    <div class="col-md-6 mb-3">
                        <label>Status</label>
                        <input type="text" class="form-control"
                               value="{{ $product->stock <= 5 ? 'Low Stock' : 'In Stock' }}" readonly>
                    </div>

                </div>

                {{-- Buttons --}}
                <div class="d-flex justify-content-end mt-3">

                    <a href="{{ route('product.edit', $product->id) }}" 
                       class="btn btn-warning me-2">
                        ✏️ Edit
                    </a>

                    <button type="button" class="btn btn-secondary" onclick="history.back()">
                        ← Back
                    </button>

                </div>

            </div>
        </div>

        <br>
        <span>@include('layouts.footer')</span>
    </div>
</x-app-layout>