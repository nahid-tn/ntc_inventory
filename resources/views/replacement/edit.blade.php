<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Product Replacement') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if($errors->any())
                <div class="alert alert-danger mb-4">
                    <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form action="{{ route('replacement.update', $replacement->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Sale select / autocomplete --}}
                    <div class="form-group mb-3">
                        <label>Sale Invoice <span class="text-danger">*</span></label>
                        <input type="text" id="sale_search" class="form-control"
                               value="Invoice: {{ $replacement->sale->invoice_no ?? '' }} | Customer: {{ $replacement->sale->customer->customer_name ?? '' }}">
                        <input type="hidden" name="sale_id" id="sale_id" value="{{ $replacement->sale_id }}">
                    </div>

                    {{-- Old Product --}}
                    <div class="form-group mb-3">
                        <label>Old Product <span class="text-danger">*</span></label>
                        <select name="old_product_id" id="old_product_select" class="form-control" required>
                            <option value="{{ $replacement->old_product_id }}">
                                {{ $replacement->oldProduct->product_name ?? $replacement->oldProduct->name ?? '' }}
                            </option>
                        </select>
                        <small id="old_stock_info" class="text-muted">
                            <span class="badge bg-secondary">Stock: {{ $replacement->oldProduct->stock ?? 0 }}</span>
                            <span class="badge bg-success">Price: {{ $replacement->oldProduct->price ?? 0 }}</span>
                        </small>
                    </div>

                    {{-- New Product --}}
                    <div class="row">
                        <div class="form-group mb-3 col-md-4">
                            <label>New Product <span class="text-danger">*</span></label>
                            <input type="text" name="new_product_search" id="new_product_search" class="form-control"
                                   value="{{ $replacement->newProduct->product_name ?? $replacement->newProduct->name ?? '' }}" required>
                            <input type="hidden" name="new_product_id" id="new_product_id" value="{{ $replacement->new_product_id }}">
                            <small id="new_stock_info" class="text-muted">
                                <span class="badge bg-info">Stock: {{ $replacement->newProduct->stock ?? 0 }}</span>
                                <span class="badge bg-warning text-dark">Purchase: {{ $replacement->newProduct->purchase_price ?? 0 }}</span>
                                <span class="badge bg-success">Sale: {{ $replacement->newProduct->price ?? 0 }}</span>
                            </small>
                        </div>

                        {{-- Qty & Price --}}
                        <div class="form-group mb-3 col-md-2">
                            <label>Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="qty" id="qty" class="form-control" min="1" value="{{ $replacement->qty }}" required>
                        </div>
                        <div class="form-group mb-3 col-md-2">
                            <label>Unit <span class="text-danger">*</span></label>
                            <select name="unit" id="unit" class="form-control" required>
                                <option value="">-- Select Unit --</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->name }}" 
                                        {{ old('unit', $replacement->unit ?? '') == $unit->name ? 'selected' : '' }}>
                                        {{ $unit->name }} ({{ $unit->ShortName }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-3 col-md-2">
                            <label>Price <span class="text-danger">*</span></label>
                            <input type="number" name="price" id="price" class="form-control" min="0" value="{{ $replacement->price }}" required>
                        </div>
                        <div class="form-group mb-3 col-md-2">
                            <label>Total</label>
                            <input type="text" id="total" class="form-control" value="{{ $replacement->total }}" readonly>
                        </div>
                    </div>

                    {{-- Dates & Replacement Fee --}}
                    <div class="row">
                        <div class="form-group mb-3 col-md-4">
                            <label>Replacement Date</label>
                            <input type="date" name="replaceMent_date" id="replaceMent_date" class="form-control" value="{{ $replacement->replaceMent_date }}">
                        </div>
                        <div class="form-group mb-3 col-md-4">
                            <label>Body Date</label>
                            <input type="date" name="body_date" id="body_date" class="form-control" value="{{ $replacement->body_date }}">
                        </div>
                        <div class="form-group mb-3 col-md-4">
                            <label>Replacement Fee</label>
                            <input type="number" name="replaceMent_fee" id="replaceMent_fee" class="form-control" min="0" value="{{ $replacement->replaceMent_fee }}" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Replacement</button>
                    <a href="{{ route('replacement.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="button" class="btn btn-secondary" onclick="history.back()">← Back</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
$(document).ready(function () {

    // Load old products based on sale selection
    function loadOldProducts(sale_id, selected_old_id = null) {
        if (!sale_id) return;
        $.get('/replacement/old-products/' + sale_id, function(data){
            let html = '<option value="">-- Select Old Product --</option>';
            data.forEach(p => {
                let selected = (selected_old_id == p.id) ? 'selected' : '';
                html += `<option value="${p.id}" data-stock="${p.stock}" data-price="${p.price}" ${selected}>
                            ${p.name} (Qty: ${p.qty} | Stock: ${p.stock} | Price: ${p.price})
                         </option>`;
            });
            $('#old_product_select').html(html);
            if(selected_old_id){
                let selected_option = $('#old_product_select option:selected');
                $('#old_stock_info').html(`
                    <span class="badge bg-secondary">Stock: ${selected_option.data('stock')}</span>
                    <span class="badge bg-success">Price: ${selected_option.data('price')}</span>
                `);
                $('#price').val(selected_option.data('price'));
            }
        });
    }

    // Trigger old product load on page load
    loadOldProducts($('#sale_id').val(), '{{ $replacement->old_product_id }}');

    // Sale autocomplete
    $('#sale_search').autocomplete({
        source: '/sale-autocomplete',
        minLength: 1,
        select: function(event, ui){
            $('#sale_search').val(ui.item.label);
            $('#sale_id').val(ui.item.id);
            loadOldProducts(ui.item.id);
            return false;
        }
    });

    // Old product select → update stock & price
    $('#old_product_select').on('change', function(){
        let stock = $(this).find(':selected').data('stock') || 0;
        let price = $(this).find(':selected').data('price') || 0;
        $('#old_stock_info').html(`
            <span class="badge bg-secondary">Stock: ${stock}</span>
            <span class="badge bg-success">Price: ${price}</span>
        `);
        $('#price').val(price);
        calculateTotal();
    });

    // New product autocomplete
    $('#new_product_search').autocomplete({
        source: '/product-autocomplete',
        minLength: 1,
        select: function(event, ui){
            $('#new_product_search').val(ui.item.value);
            $('#new_product_id').val(ui.item.id);
            $('#new_stock_info').html(`
                <span class="badge bg-info">Stock: ${ui.item.stock}</span>
                <span class="badge bg-warning text-dark">Purchase: ${ui.item.purchase_price}</span>
                <span class="badge bg-success">Sale: ${ui.item.price}</span>
            `);
            $('#price').val(ui.item.price);
            calculateTotal();
            return false;
        }
    });

    // Qty / Price change → recalc total
    $('#qty, #price').on('input', calculateTotal);
    function calculateTotal(){
        let qty = parseFloat($('#qty').val()) || 0;
        let price = parseFloat($('#price').val()) || 0;
        $('#total').val((qty * price).toFixed(2));
    }

});
</script>