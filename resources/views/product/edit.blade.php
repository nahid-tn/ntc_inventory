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

<form action="{{ route('product.update', $product->id) }}" 
      method="POST"
      enctype="multipart/form-data">
    @csrf
    @method('PUT')

                    <div class="row">

                        {{-- Product Name --}}
                        <div class="col-md-6 mb-3">
                            <label>Product Name</label>
                            <input type="text" class="form-control" 
                                   name="product_name"
                                   value="{{ $product->product_name }}">
                        </div>

                        {{-- Product Code --}}
                        <div class="col-md-6 mb-3">
                            <label>Product Code</label>
                            <input type="text" class="form-control" 
                                   name="product_code"
                                   value="{{ $product->product_code }}">
                        </div>

                    </div>

                    <div class="row">

                        {{-- Model Name --}}
                        <div class="col-md-6 mb-3">
                            <label>Model Name</label>
                            <input type="text" class="form-control" 
                                   name="model_name"
                                   value="{{ $product->model_name }}">
                        </div>

                        {{-- Category --}}
                        <div class="col-md-6 mb-3">
                            <label>Category</label>
                            <input type="text" id="category_name"
                                   class="form-control"
                                   value="{{ $categories->where('id',$product->category_id)->first()?->category_name }}">
                            <input type="hidden" name="category_id"
                                   id="category_id"
                                   value="{{ $product->category_id }}">
                        </div>

                    </div>

                    <div class="row">

                        {{-- Purchase Price --}}
                        <div class="col-md-4 mb-3">
                            <label>Purchase Price</label>
                            <input type="number" class="form-control"
                                   name="purchase_price" step="0.01"
                                   value="{{ $product->purchase_price }}">
                        </div>

                        {{-- Wholesale Price --}}
                        <div class="col-md-4 mb-3">
                            <label>Wholesale Price</label>
                            <input type="number" class="form-control"
                                   name="wholesale_price" step="0.01"
                                   value="{{ $product->wholesale_price }}">
                        </div>

                        {{-- Selling Price --}}
                        <div class="col-md-4 mb-3">
                            <label>Selling Price</label>
                            <input type="number" class="form-control"
                                   name="selling_price" step="0.01"
                                   value="{{ $product->selling_price }}">
                        </div>

                    </div>
                     <div>
                            <label>Product Description</label>
                            <textarea class="form-control" name="description" rows="4">{{ old('description', $product->description) }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
    <label>Product Image</label>

    <input type="file"
           name="image"
           id="image"
           class="form-control">

    <br>

    <img id="preview-image"
         src="{{ $product->image
                ? asset('uploads/products/'.$product->image)
                : asset('images/no-image.png') }}"
         width="200"
         height="200"
         style="object-fit:cover;border:1px solid #ddd;">
</div>
                    <div class="row">

                        {{-- Stock --}}
                        <div class="col-md-6 mb-3">
                            <label>Stock</label>
                            <input type="number" class="form-control"
                                   name="stock"
                                   id="stock"
                                   value="{{ $product->stock }}">
                        </div>

                        {{-- Status --}}
                        <div class="col-md-6 mb-3">
                            <label>Status</label>
                            <input type="text" class="form-control"
                                   id="status"
                                   value="{{ $product->stock <= 5 ? 'Low Stock' : 'In Stock' }}"
                                   readonly>
                        </div>

                    </div>

                    {{-- Buttons --}}
                    <div class="d-flex justify-content-end mt-3">

                        <button type="submit" class="btn btn-primary me-2">
                            💾 Update
                        </button>

                        <a href="{{ route('product.show', $product->id) }}" 
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

        document.getElementById('image').addEventListener('change', function(e){

            const file = e.target.files[0];

            if(file){

                const reader = new FileReader();

                reader.onload = function(event){

                    document.getElementById('preview-image')
                            .src = event.target.result;

                };

                reader.readAsDataURL(file);
            }
        });
    </script>

</x-app-layout>