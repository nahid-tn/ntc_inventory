<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Old Product Sale') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <form action="{{ route('oldsale.store') }}" method="post">
                    @csrf
                    <div class="form-row mb-3">
                        <div class="form-group col-md-2">
                            <label>Invoice No<span class="text-danger">*</span></label>
                            <input type="text" name="invoice_no" class="form-control" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Customer Name</label>
                            <input type="text" id="customer_search" class="form-control" placeholder="Search customer..." autocomplete="off" required>
                            <input type="hidden" name="customer_id" id="customer_id">
                            <small class="text-muted" id="customer_info"></small>
                            <small class="text-muted d-block" id="customer_address"></small>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Sale Date</label>
                            <input type="date" name="sale_date" class="form-control" required>
                        </div>
                    </div>

                    <hr>
                    <h4 class="text-center">Old Products</h4>
                    <table class="table table-bordered" id="old_product_table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Unit</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select name="old_products[0][product_id]" class="form-control old_product_select">
                                        <option value="">-- Select Old Product --</option>
                                        @foreach($oldProducts as $p)
                                            <option value="{{ $p->id }}">{{ $p->product_name ?? $p->name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted old_stock_info"></small>
                                </td>
                                <td><input type="number" name="old_products[0][qty]" class="form-control qty" step="0.001"></td>
                                <td>
                                    <select name="old_products[0][unit]" class="form-control unit" required>
                                        <option value="">-- Select Unit --</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->name }}">
                                                {{ $unit->name }} ({{ $unit->ShortName }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" name="old_products[0][price]" class="form-control price" step="0.001"></td>
                                <td><input type="text" class="form-control row_total" readonly></td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button>
                                </td>
                            </tr>
                        </tbody>
                        
                    </table>
                    <button type="button" class="btn btn-success btn-sm" onclick="addRow()">Add</button>
                    <div class="form-row mb-3">
                        <div class="form-group col-md-4">
                            <label>Paid Amount</label>
                            <input type="text" class="form-control" id="paid_amount" name="paid_amount">
                        </div>
                        <div class="form-group col-md-4">
                            <label>Total Amount</label>
                            <input type="text" class="form-control" id="total_amount" name="total_amount" readonly>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Due Amount</label>
                            <input type="text" class="form-control" id="due_amount" name="due_amount" readonly>
                        </div>
                    </div>

                    <div class="form-row mb-3">
                        
                        
                        <div class="form-group col-md-4">
                            <label>Payment Method</label>
                            <select name="payment_method" class="form-control" required>
                                <option value="">-- Select Method --</option>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank</option>
                                <option value="mobile">Mobile Banking</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                        <!-- <div class="form-group col-md-4">
                            <label>Reference No</label>
                            <input type="text" class="form-control" id="ref_no" name="ref_no" placeholder="If any reference number">
                        </div> -->
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Sale</button> | <button type="button" class="btn btn-secondary" onclick="history.back()">
    ← Back
</button>
                </form>
            </div>
        </div>
        <br>
        <span> @include('layouts.footer')</span>
    </div>
    </div>
</x-app-layout>


<script>
let rowIndex = 1;

function addRow() {
    let tbody = document.querySelector('#old_product_table tbody');
    let row = tbody.insertRow();

    row.innerHTML = `
        <td>
            <select name="old_products[${rowIndex}][product_id]" class="form-control old_product_select">
                <option value="">-- Select Old Product --</option>
                @foreach($oldProducts as $p)
                    <option value="{{ $p->id }}">{{ $p->product_name ?? $p->name }}</option>
                @endforeach
            </select>
            <small class="text-muted old_stock_info"></small>
        </td>
        <td>
            <input type="number" name="old_products[${rowIndex}][qty]" class="form-control qty" step="0.001">
        </td>
        <td>
            <select name="old_products[${rowIndex}][unit]" class="form-control unit" required>
                <option value="">-- Select Unit --</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->name }}">{{ $unit->name }} ({{ $unit->ShortName }})</option>
                @endforeach
            </select>
        </td>
        <td><input type="number" name="old_products[${rowIndex}][price]" class="form-control price" step="0.001"></td>
        <td><input type="text" class="form-control row_total" readonly></td>
        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button>
        </td>
    `;

    rowIndex++;
    calculateGrandTotal();
}

function removeRow(btn) {
    btn.closest('tr').remove();
    calculateGrandTotal();
}

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('product_select')) {
        let row = e.target.closest('tr');
        let price = parseFloat(e.target.selectedOptions[0].dataset.price) || 0;
        row.querySelector('.price').value = price;
        row.querySelector('.qty').value = 1;
        row.querySelector('.row_total').value = price.toFixed(2);
        calculateGrandTotal();
    }
});

document.addEventListener('input', function(e) {
    if (e.target.classList.contains('qty') || e.target.classList.contains('price')) {
        let row = e.target.closest('tr');
        let qty = parseFloat(row.querySelector('.qty').value) || 0;
        let unit = row.querySelector('.unit').value;
        let price = parseFloat(row.querySelector('.price').value) || 0;
        row.querySelector('.row_total').value = (qty * price).toFixed(2);
        calculateGrandTotal();
    }
});

function calculateGrandTotal() {
    let subtotal = 0;
    document.querySelectorAll('.row_total').forEach(el => subtotal += parseFloat(el.value) || 0);

    // Subtract old products
    let total = subtotal;
    document.getElementById('total_amount').value = total.toFixed(2);

    calculateDue();
}

function calculateDue() {
    let total = parseFloat(document.getElementById('total_amount').value) || 0;
    let paid  = parseFloat(document.getElementById('paid_amount').value) || 0;
    if (paid > total) paid = total;
    document.getElementById('due_amount').value = (total - paid).toFixed(2);
}

document.getElementById('paid_amount').addEventListener('input', calculateDue);

$(document).ready(function () {

    $('#customer_search').autocomplete({
        source: function (request, response) {
            $.ajax({
                url: "/customer-autocomplete",
                dataType: "json",
                data: {
                    term: request.term
                },
                success: function (data) {
                    response(data);
                }
            });
        },
        minLength: 1,
        select: function (event, ui) {
            $('#customer_search').val(ui.item.value);
            $('#customer_id').val(ui.item.id);
            $('#customer_info').text(
                'Mobile: ' + (ui.item.mobile ?? '') +
                ' | Address: ' + (ui.item.address ?? '')
            );

            return false;
        }
    });

});

    $('#customer_search').on('blur', function () {
        if (!$('#customer_id').val()) {
            $(this).val('');
        }
    });


$(document).on('focus', '.product_search', function () {
    $(this).autocomplete({
        source: '/products-old-autocomplete',
        minLength: 1,
        select: function (event, ui) {

            let row = $(this).closest('tr');

            row.find('.product_id').val(ui.item.value); // name save
            row.find('.stock_info').text('Total Stock: ' + ui.item.stock);
            row.find('.price').val(ui.item.price);

            $(this).val(ui.item.value);
            return false;
        }
    });
});
    $(document).on('change', '.old_product_select', function() {
        var productId = $(this).val();
        var stockInfo = $(this).closest('tr').find('.old_stock_info');

        if(!productId) {
            stockInfo.text('');
            return;
        }

        $.get('/old-product-stock/' + productId, function(data) {
            stockInfo.text('Available Stock: ' + data.stock);
        });
    });
</script>