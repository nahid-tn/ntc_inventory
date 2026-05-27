<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
            ✏️ Update Product
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow rounded p-4">

                {{-- Success Message --}}
                @if(session('success'))
                    <p class="text-green-600">{{ session('success') }}</p>
                @endif

                <form action="{{ route('product.update', $products->id) }}" method="POST">
                    @csrf

                    <div class="row">

                        {{-- Product Name --}}
                        <div class="col-md-6 mb-3">
                            <label>Product Name</label>
                            <input type="text" class="form-control" 
                                   name="product_name"
                                   value="{{ $products->product_name }}">
                        </div>

                        {{-- Product Code --}}
                        <div class="col-md-6 mb-3">
                            <label>Product Code</label>
                            <input type="text" class="form-control" 
                                   name="product_code"
                                   value="{{ $products->product_code }}">
                        </div>

                    </div>

                    <div class="row">

                        {{-- Model Name --}}
                        <div class="col-md-6 mb-3">
                            <label>Model Name</label>
                            <input type="text" class="form-control" 
                                   name="model_name"
                                   value="{{ $products->model_name }}">
                        </div>

                        {{-- Category --}}
                        <div class="col-md-6 mb-3">
                            <label>Category</label>
                            <input type="text" id="category_name"
                                   class="form-control"
                                   value="{{ $categories->where('id',$products->category_id)->first()?->category_name }}">
                            <input type="hidden" name="category_id"
                                   id="category_id"
                                   value="{{ $products->category_id }}">
                        </div>

                    </div>

                    <div class="row">

                        {{-- Purchase Price --}}
                        <div class="col-md-6 mb-3">
                            <label>Purchase Price</label>
                            <input type="number" class="form-control"
                                   name="purchase_price" step="0.01"
                                   value="{{ $products->purchase_price }}">
                        </div>

                        {{-- Selling Price --}}
                        <div class="col-md-6 mb-3">
                            <label>Selling Price</label>
                            <input type="number" class="form-control"
                                   name="salling_price"
                                   value="{{ $products->salling_price }}">
                        </div>

                    </div>
                     <div>
                            <label>Product Description</label>
                            <textarea class="form-control" name="description" rows="4">{{ old('description', $products->description) }}</textarea>
                        </div>
                    <div class="row">

                        {{-- Stock --}}
                        <div class="col-md-6 mb-3">
                            <label>Stock</label>
                            <input type="number" class="form-control"
                                   name="stock"
                                   id="stock"
                                   value="{{ $products->stock }}">
                        </div>

                        {{-- Status --}}
                        <div class="col-md-6 mb-3">
                            <label>Status</label>
                            <input type="text" class="form-control"
                                   id="status"
                                   value="{{ $products->stock <= 5 ? 'Low Stock' : 'In Stock' }}"
                                   readonly>
                        </div>

                    </div>

                    {{-- Buttons --}}
                    <div class="d-flex justify-content-end mt-3">

                        <button type="submit" class="btn btn-primary me-2">
                            💾 Update
                        </button>

                        <a href="{{ route('product.show', $products->id) }}" 
                           class="btn btn-info me-2">
                            👁 View
                        </a>

                        <button type="button" class="btn btn-secondary" onclick="history.back()">
                            ← Back
                        </button>

                    </div>

                </form>

            </div>
        </div>

        <br>
        <span>@include('layouts.footer')</span>
    </div>

    {{-- 🔥 Auto Status --}}
    <script>
        document.getElementById('stock').addEventListener('input', function () {
            let stock = parseInt(this.value) || 0;
            let status = document.getElementById('status');

            if (stock <= 5) {
                status.value = 'Low Stock';
            } else {
                status.value = 'In Stock';
            }
        });
    </script>

</x-app-layout>