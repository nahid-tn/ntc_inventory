<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Old Product Sale') }}
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

                <form action="{{ route('oldsale.update', $sale->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Sale Info --}}
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Invoice No <span class="text-danger">*</span></label>
                            <input type="text" name="invoice_no" class="form-control" value="{{ $sale->invoice_no }}" required>
                        </div>
                        <div class="col-md-5">
                            <label>Customer</label>
                            <select name="customer_id" class="form-control" required>
                                <option value="">-- Select Customer --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" {{ $sale->customer_id == $c->id ? 'selected' : '' }}>
                                        {{ $c->customer_name ?? $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Sale Date</label>
                            <input type="date" name="sale_date" class="form-control" value="{{ $sale->sale_date->format('Y-m-d') }}" required>
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
                                <th>Subtotal</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->details as $index => $detail)
                            <tr>
                                <td>
                                    <select name="old_products[{{ $index }}][product_id]" class="form-control old_product_select">
                                        <option value="">-- Select Old Product --</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}" {{ $p->id == $detail->old_product_id ? 'selected' : '' }}>
                                                {{ $p->product_name ?? $p->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted old_stock_info"></small>
                                </td>
                                <td><input type="number" name="old_products[{{ $index }}][qty]" class="form-control qty" value="{{ $detail->qty }}" step="0.001"></td>
                                <td>
                                    <select name="old_products[{{ $index }}][unit]" class="form-control unit" required>
                                        <option value="">-- Select Unit --</option>
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->name }}" {{ $detail->unit == $unit->name ? 'selected' : '' }}>
                                                {{ $unit->name }} ({{ $unit->ShortName }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="number" name="old_products[{{ $index }}][price]" class="form-control price" value="{{ $detail->price }}"></td>
                                <td><input type="text" class="form-control row_total" readonly value="{{ $detail->qty * $detail->price }}"></td>
                                <td>
                                    <button type="button" class="btn btn-success btn-sm" onclick="addRow()">Add</button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Paid Amount</label>
                            <input type="number" name="paid_amount" id="paid_amount" class="form-control" value="{{ $sale->paid_amount }}">
                        </div>
                        <div class="col-md-4">
                            <label>Total Amount</label>
                            <input type="text" id="total_amount" class="form-control" readonly value="{{ $sale->total_amount }}">
                        </div>
                        <div class="col-md-4">
                            <label>Due Amount</label>
                            <input type="text" id="due_amount" class="form-control" readonly value="{{ $sale->due_amount }}">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Payment Method</label>
                            <select name="payment_method" class="form-control">
                                <option value="">-- Select Method --</option>
                                <option value="cash" {{ $sale->payment_method == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="bank" {{ $sale->payment_method == 'bank' ? 'selected' : '' }}>Bank</option>
                                <option value="mobile" {{ $sale->payment_method == 'mobile' ? 'selected' : '' }}>Mobile Banking</option>
                                <option value="cheque" {{ $sale->payment_method == 'cheque' ? 'selected' : '' }}>Cheque</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Sale</button>
                    <button type="button" class="btn btn-secondary" onclick="history.back()">← Back</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
let rowIndex = {{ $sale->details->count() }};

function addRow() {
    let tbody = document.querySelector('#old_product_table tbody');
    let row = tbody.insertRow();
    row.innerHTML = `
        <td>
            <select name="old_products[${rowIndex}][product_id]" class="form-control old_product_select">
                <option value="">-- Select Old Product --</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}">{{ $p->product_name ?? $p->name }}</option>
                @endforeach
            </select>
            <small class="text-muted old_stock_info"></small>
        </td>
        <td><input type="number" name="old_products[${rowIndex}][qty]" class="form-control qty" step="0.001"></td>
        <td>
        <select name="old_products[${rowIndex}][unit]" class="form-control unit" required>
            <option value="">-- Select Unit --</option>
            @foreach($units as $unit)
                <option value="{{ $unit->name }}">{{ $unit->name }} ({{ $unit->ShortName }})</option>
            @endforeach
        </select>
    </td>
        <td><input type="number" name="old_products[${rowIndex}][price]" class="form-control price"></td>
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

document.addEventListener('input', function(e) {
    if (e.target.classList.contains('qty') || e.target.classList.contains('price')) {
        let row = e.target.closest('tr');
        let qty = parseFloat(row.querySelector('.qty').value) || 0;
        let price = parseFloat(row.querySelector('.price').value) || 0;
        row.querySelector('.row_total').value = (qty * price).toFixed(2);
        calculateGrandTotal();
    }
});

function calculateGrandTotal() {
    let total = 0;
    document.querySelectorAll('.row_total').forEach(el => total += parseFloat(el.value) || 0);
    document.getElementById('total_amount').value = total.toFixed(2);
    calculateDue();
}

function calculateDue() {
    let total = parseFloat(document.getElementById('total_amount').value) || 0;
    let paid = parseFloat(document.getElementById('paid_amount').value) || 0;
    document.getElementById('due_amount').value = (total - paid).toFixed(2);
}

document.getElementById('paid_amount').addEventListener('input', calculateDue);
</script>