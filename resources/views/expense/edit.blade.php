<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Expense') }}
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

                <form action="{{ route('expense.update', $expense->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row col-md-12 mb-3">

                        <div class="form-group col-md-3">
                            <label>Date:</label>
                            <input type="date" name="expense_date" class="form-control"
                                   value="{{ old('expense_date', $expense->expense_date->format('Y-m-d')) }}" required>
                        </div>

                        <div class="form-group col-md-3">
                            <label>Payment Method:</label>
                            <select name="payment_method" class="form-control">
                                <option value="Cash" {{ $expense->payment_method=='Cash' ? 'selected':'' }}>Cash</option>
                                <option value="Bank" {{ $expense->payment_method=='Bank' ? 'selected':'' }}>Bank</option>
                                <option value="Mobile Banking" {{ $expense->payment_method=='Mobile Banking' ? 'selected':'' }}>Mobile Banking</option>
                            </select>
                        </div>

                        {{-- ✅ TOTAL FIX --}}
                        <div class="form-group col-md-2">
                            <label>Total Amount:</label>
                            <div style="display:flex; align-items:center; gap:5px;">
                                <input type="text" id="total_display" class="form-control" readonly>
                                <input type="hidden" name="total" id="total">
                                TK
                            </div>
                        </div>

                    </div>

                    <hr class="my-3">

                    <h3 class="font-semibold mb-2">Expense For</h3>

                    <div id="items">
                        @foreach($expense->items as $item)
                        <div class="row align-items-center mb-2 item-row">
                            <div class="col-md-3">
                                <input type="text" name="categories[]" class="form-control"
                                       value="{{ $item->item_name }}" required>
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="qtys[]" class="form-control qty"
                                       value="{{ $item->qty }}" min="1" required>
                            </div>
                            <div class="col-md-2">
                                <input type="number" name="prices[]" class="form-control price"
                                       value="{{ $item->price }}" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control subtotal"
                                       value="{{ $item->subtotal }}" readonly>
                            </div>
                            <div class="col-md-2">
                                <input type="text" name="descriptions[]" class="form-control"
                                       value="{{ $item->description }}">
                            </div>
                            <div class="col-md-1 text-end">
                                <button type="button" onclick="removeRow(this)" class="btn btn-danger btn-sm">Remove</button>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <button type="button" onclick="addRow()" class="btn btn-success mt-2">➕ Add More</button>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Update Expense</button>
                        <a href="{{ route('expense.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>

                </form>

            </div>
        </div>

        <br><span>@include('layouts.footer')</span>
    </div>

<script>
function addRow() {
    let row = `
    <div class="row align-items-center mb-2 item-row">
        <div class="col-md-3">
            <input type="text" name="categories[]" class="form-control" placeholder="Item Name" required>
        </div>
        <div class="col-md-2">
            <input type="number" name="qtys[]" class="form-control qty" value="1" min="1" required>
        </div>
        <div class="col-md-2">
            <input type="number" name="prices[]" class="form-control price" value="0" step="0.01" min="0" required>
        </div>
        <div class="col-md-2">
            <input type="text" class="form-control subtotal" value="0" readonly>
        </div>
        <div class="col-md-2">
            <input type="text" name="descriptions[]" class="form-control" placeholder="Description">
        </div>
        <div class="col-md-1 text-end">
            <button type="button" onclick="removeRow(this)" class="btn btn-danger btn-sm">Remove</button>
        </div>
    </div>`;

    document.getElementById("items").insertAdjacentHTML("beforeend", row);
    calcTotal();
}

function removeRow(btn) {
    btn.closest(".item-row").remove();
    calcTotal();
}

function calcTotal() {
    let total = 0;

    document.querySelectorAll(".item-row").forEach(row => {
        let qty = parseFloat(row.querySelector(".qty").value) || 0;
        let price = parseFloat(row.querySelector(".price").value) || 0;

        let subtotal = qty * price;
        row.querySelector(".subtotal").value = subtotal.toFixed(2);

        total += subtotal;
    });

    document.getElementById("total_display").value = total.toFixed(2);
    document.getElementById("total").value = total.toFixed(2);
}

document.addEventListener("input", function(e){
    if(e.target.classList.contains('qty') || e.target.classList.contains('price')){
        calcTotal();
    }
});

window.onload = calcTotal;
</script>

</x-app-layout>