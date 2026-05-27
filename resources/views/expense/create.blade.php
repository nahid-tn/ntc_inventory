<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Expense') }}
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
                <form action="{{ route('expense.store') }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Date:</label>
                            <input type="date" name="expense_date" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label>Payment Method:</label>
                            <select name="payment_method" class="form-control">
                                <option value="Cash">Cash</option>
                                <option value="Bank">Bank</option>
                                <option value="Mobile Banking">Mobile Banking</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Total Amount:</label>
                            <input type="text" id="total" name="total" class="form-control" readonly>
                        </div>
                    </div>

                    <hr>
                    <h2 class="text-center">Expense Items</h2>
                    <br>
                    <hr>

                    <div id="items">
                        <div class="row align-items-center item-row my-2">
                            <div class="col-md-3">
                                <label>Item Name</label>
                                <input type="text" name="categories[]" class="form-control" placeholder="Item Name" required>
                            </div>
                            <div class="col-md-2">
                                <label>Quentity</label>
                                <input type="number" name="qtys[]" class="form-control qty" value="1" min="1" step="0.01" required>
                            </div>
                            <div class="col-md-2">
                                <label>Price</label>
                                <input type="number" name="prices[]" class="form-control price" value="0" step="0.01" required>
                            </div>
                            <div class="col-md-2">
                                <label>Sub Total</label>
                                <input type="text" class="form-control subtotal" value="0" readonly>
                            </div>
                            <div class="col-md-2">
                                <label>Description</label>
                                <input type="text" name="descriptions[]" class="form-control" placeholder="Description">
                            </div>
                            <div class="col-md-1 text-end">
                                <label>Action</label>
                                <button type="button" onclick="removeRow(this)" class="btn btn-danger btn-sm">Remove</button>
                            </div>
                        </div>
                    </div>

                    <button type="button" onclick="addRow()" class="btn btn-success mt-2">➕ Add More</button>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Save Expense</button>
                        <button type="button" class="btn btn-secondary" onclick="history.back()">← Back</button>
                    </div>
                </form>
            </div>
        </div>
        <br>
        <span>@include('layouts.footer')</span>
    </div>

    <script>
        function addRow() {
            let row = `
            <div class="row align-items-center item-row my-2">
                <div class="col-md-3">
                    <input type="text" name="categories[]" class="form-control" placeholder="Item Name" required>
                </div>
                <div class="col-md-2">
                    <input type="number" name="qtys[]" class="form-control qty" value="1" min="1" required>
                </div>
                <div class="col-md-2">
                    <input type="number" name="prices[]" class="form-control price" value="0" step="0.01" required>
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
                const qty   = parseFloat(row.querySelector(".qty").value) || 0;
                const price = parseFloat(row.querySelector(".price").value) || 0;
                const subtotal = qty * price;
                row.querySelector(".subtotal").value = subtotal.toFixed(2);
                total += subtotal;
            });
            document.getElementById("total").value = total.toFixed(2);
        }

        window.addEventListener("DOMContentLoaded", calcTotal);
        document.addEventListener("input", calcTotal);
    </script>
</x-app-layout>