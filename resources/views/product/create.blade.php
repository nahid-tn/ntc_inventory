<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Product') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if(session('success'))
                        <p style="color:green">{{ session('success') }}</p>
                    @endif
                    <form action="{{route('product.create')}}" method="post">
                        @csrf
                        <div class="row">
                            <!-- Product Name -->
                            <div class="col-md-6 mb-3">
                                <label>Product Name</label>
                                <input type="text" id="product_name" name="product_name" class="form-control uppercase">
                                    <div id="suggestions"
                                        style="border:1px solid #ccc; display:none; background:#fff; position:absolute; z-index:1000; width:100%;">
                                    </div>
                            </div>

                            <!-- Product Code -->
                            <div class="col-md-6 mb-3">
                                <label>Product Code</label>
                                <input type="text" name="product_code" class="form-control" value="{{ $productCode }}" readonly>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Model Name -->
                            <div class="col-md-6 mb-3">
                                <label for="modelName">Model Name</label>
                                <input type="text" class="form-control" name="model_name" id="model_name" placeholder="Auto-generated Model Name" readonly required>
                            </div>

                             <!-- Category Name -->
                            <div class="col-md-6 mb-3">
                                <label for="category_id">Category Name</label>
                                <input type="text" id="category_name" class="form-control" placeholder="Type category..." autocomplete="off">
                                <input type="hidden" name="category_id" id="category_id">
                                <small id="category-warning" class="text-danger" style="display:none"></small>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Purchase Price -->
                            <div class="col-md-6 mb-3">
                                <label for="purchase_price">Purchase Price</label>
                                <input type="text" id="purchase_price" name="purchase_price" class="form-control" placeholder="Enter purchase price..." autocomplete="off">
                                <small id="purchase-price-warning" class="text-danger" style="display:none"></small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="selling_price">Selling Price</label>
                                <input type="text" id="selling_price" name="selling_price" class="form-control" placeholder="Enter selling price..." autocomplete="off">
                                <small id="selling-price-warning" class="text-danger" style="display:none"></small>
                            </div>
                        </div>
                        <!-- Product Description -->
                        <div class="mb-3">
                                <label for="description">Product Description</label>
                                <textarea id="description" name="description" class="form-control" rows="4" placeholder="Enter product description..." style="resize: vertical;"></textarea>
                                <small id="description-warning" class="text-danger" style="display:none"></small>
                            </div>
                        <div class="d-flex justify-content-end">
    <button type="submit" class="btn btn-primary me-2">Submit</button>
    <button type="button" class="btn btn-secondary" onclick="history.back()">
        ← Back
    </button>
</div>
                    </form>
                </div>
            </div>
        </div>
        <br>
        <span> @include('layouts.footer')</span>
    </div>
    </div>

    <script>
        document.addEventListener('input', function(e){
            if(e.target.classList.contains('uppercase')){
                e.target.value = e.target.value.toUpperCase();
            }
        });

        $(document).ready(function() {
        $('#product_name').autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: '/product-search', // route for AJAX search
                    dataType: 'json',
                    data: { term: request.term },
                    success: function(data) {
                        response(data);
                    }
                });
            },
            minLength: 1,
            select: function(event, ui) {
                $('#product_id').val(ui.item.id); // hidden input
                $('#product_name').val(ui.item.value);
                $('#model_name').val(ui.item.model); // autofill model
                return false;
            }
        });
    });

    document.getElementById('product_name').addEventListener('keyup', function () {
        let name = this.value;

        if (name.length < 2) {
            document.getElementById('suggestions').style.display = 'none';
            return;
        }

        fetch(`/product/check-name?name=${name}`)
            .then(response => response.json())
            .then(data => {
                let box = document.getElementById('suggestions');
                box.innerHTML = '';

                if (data.length > 0) {
                    box.style.display = 'block';
                    data.forEach(item => {
                        box.innerHTML += `
                            <div style="padding:5px; color:red;">
                                ⚠️ ${item}
                            </div>
                        `;
                    });
                } else {
                    box.style.display = 'none';
                }
            });
    });

    

        $(function () {
        $("#category_name").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "{{ route('admin.autocomplete') }}",
                    dataType: 'json',
                    data: { term: request.term },
                    success: function(data) {
                        response(data);
                    }
                });
            },
            minLength: 1,
            select: function(event, ui) {
                $("#category_name").val(ui.item.value);
                $("#category_id").val(ui.item.id);
                return false;
            },
            focus: function(event, ui) {
                $("#category_name").val(ui.item.value);
                return false;
            }
        });
    });


    document.getElementById('product_name').addEventListener('input', function () {
    let name = this.value.trim();

    if (name.length < 3) {
        document.getElementById('model_name').value = '';
        return;
    }

    let words = name.split(/\s+/);

    // প্রথম শব্দের ৩ অক্ষর
    let code = words[0].substring(0, 3).toUpperCase();

    // মাঝের শব্দগুলোর প্রথম অক্ষর (শেষ শব্দ বাদ)
    if (words.length > 2) {
        for (let i = 1; i < words.length - 1; i++) {
            code += words[i][0].toUpperCase();
        }
    }

    // শেষ শব্দ
    let lastPart = words.length > 1 ? words[words.length - 1].toUpperCase() : '';

    document.getElementById('model_name').value = code + ' - ' + lastPart;
});

</script>

</x-app-layout>