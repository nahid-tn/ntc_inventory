<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Purchase') }}
        </h2>
    </x-slot>

    <style>
        .purchase-edit-wrap {
            padding: 24px 12px;
        }

        .purchase-card {
            max-width: 1240px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 6px 22px rgba(0, 0, 0, .06);
        }

        .purchase-card h3 {
            font-size: 1.15rem;
            font-weight: 700;
            margin: 18px 0 12px;
        }

        .purchase-card label {
            font-weight: 600;
            margin-bottom: 6px;
        }

        .table-wrap {
            overflow-x: auto;
        }

        #product_table {
            min-width: 1050px;
        }

        #product_table th,
        #product_table td {
            vertical-align: middle;
        }

        .barcode-preview {
            display: block;
            max-width: 160px;
            height: 48px;
            object-fit: contain;
            margin: 0 auto 4px;
        }

        .stock-info {
            display: block;
            font-size: 12px;
            color: #64748b;
            margin-top: 4px;
        }

        .form-group {
            margin-bottom: 14px;
        }

        .summary-box {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px;
            background: #f8fafc;
        }

        .action-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .barcode-missing {
            color: #dc2626;
            font-size: 12px;
        }

        @media (max-width: 767px) {
            .purchase-card {
                padding: 14px;
            }
        }
    </style>

    @php
        $paidValue = (float) old('paid_amount', $paidAmount ?? 0);
        $purchaseTotal = (float) ($purchase->total_amount ?? 0);
        $purchaseDue = $purchaseTotal - $paidValue;

        $paymentMethod = old('payment_method', $purchase->payment_method);

        $dateValue = old(
            'date',
            $purchase->date
                ? \Illuminate\Support\Carbon::parse($purchase->date)->format('Y-m-d')
                : ''
        );
    @endphp

    <div class="purchase-edit-wrap">
        <div class="purchase-card">

            <h1 class="text-2xl font-bold mb-2">Purchase Edit</h1>

            <p class="text-sm text-gray-500 mb-5">
                ইনভয়েস, সরবরাহকারী, পণ্য, বারকোড ও পেমেন্টের তথ্য আপডেট করুন।
            </p>

            @if (session('success'))
                <div class="alert alert-success mb-3">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger mb-3">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger mb-3">
                    <strong>নিচের সমস্যাগুলো ঠিক করুন:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                action="{{ route('purchase.update', $purchase->id) }}"
                method="POST"
                enctype="multipart/form-data"
                id="purchaseEditForm"
            >
                @csrf
                @method('PUT')

                {{-- Invoice information --}}
                <div class="row">
                    <div class="form-group col-md-4">
                        <label for="Invoice_no">
                            Invoice No <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            id="Invoice_no"
                            name="Invoice_no"
                            class="form-control"
                            value="{{ old('Invoice_no', $purchase->Invoice_no) }}"
                            maxlength="20"
                            required
                        >
                    </div>

                    <div class="form-group col-md-4">
                        <label for="date">
                            Purchase Date <span class="text-danger">*</span>
                        </label>

                        <input
                            type="date"
                            id="date"
                            name="date"
                            class="form-control"
                            value="{{ $dateValue }}"
                            required
                        >
                    </div>

                    <div class="form-group col-md-4">
                        <label for="purchase_order_no">Purchase Order No</label>

                        <input
                            type="text"
                            id="purchase_order_no"
                            name="purchase_order_no"
                            class="form-control"
                            value="{{ old('purchase_order_no', $purchase->purchase_order_no) }}"
                            maxlength="20"
                        >
                    </div>
                </div>

                {{-- Supplier and payment method --}}
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="supplier_search">
                            Supplier <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            id="supplier_search"
                            class="form-control"
                            placeholder="Search supplier..."
                            value="{{ old('supplier_name', $purchase->supplier->supplier_name ?? '') }}"
                            autocomplete="off"
                            required
                        >

                        <input
                            type="hidden"
                            name="supplier_id"
                            id="supplier_id"
                            value="{{ old('supplier_id', $purchase->supplier_id) }}"
                        >

                        <small id="supplier_info" class="text-muted">
                            Mobile: {{ $purchase->supplier->mobile ?? 'N/A' }}
                        </small>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="payment_method">
                            Payment Method <span class="text-danger">*</span>
                        </label>

                        <select
                            name="payment_method"
                            id="payment_method"
                            class="form-control"
                            required
                        >
                            <option value="">-- Select Method --</option>

                            <option value="cash"
                                {{ $paymentMethod === 'cash' ? 'selected' : '' }}>
                                Cash
                            </option>

                            <option value="bank"
                                {{ $paymentMethod === 'bank' ? 'selected' : '' }}>
                                Bank
                            </option>

                            <option value="mobile"
                                {{ $paymentMethod === 'mobile' ? 'selected' : '' }}>
                                Mobile Banking
                            </option>

                            <option value="cheque"
                                {{ $paymentMethod === 'cheque' ? 'selected' : '' }}>
                                Cheque
                            </option>

                            @if (
                                $paymentMethod &&
                                !in_array($paymentMethod, ['cash', 'bank', 'mobile', 'cheque'], true)
                            )
                                <option value="{{ $paymentMethod }}" selected>
                                    {{ $paymentMethod }}
                                </option>
                            @endif
                        </select>
                    </div>
                </div>

                {{-- Purchase Challan Attachment --}}
                <div class="row mt-3">
                    <div class="form-group col-md-12">
                        <label for="challan_file">Purchase Challan / চালানের কপি</label>
                        <input
                            type="file"
                            id="challan_file"
                            name="challan_file"
                            class="form-control"
                            accept=".pdf,.jpg,.jpeg,.png"
                        >
                        <small class="form-text text-muted">
                            PDF, JPG, JPEG অথবা PNG ফাইল (সর্বোচ্চ 5 MB)। নতুন ফাইল দিলে আগের চালানের কপি পরিবর্তিত হবে।
                        </small>

                        @if ($purchase->challan_file)
                            <div class="mt-3">
                                <span class="text-success font-weight-bold">বর্তমান চালানের কপি:</span>
                                <a
                                    href="{{ asset('storage/' . $purchase->challan_file) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="btn btn-sm btn-outline-primary ml-2"
                                >
                                    View / Download Challan
                                </a>
                            </div>
                        @else
                            <small class="d-block text-muted mt-2">এখনো কোনো চালানের কপি সংযুক্ত করা হয়নি।</small>
                        @endif
                    </div>
                </div>

                <hr>

                <h3>Product Details & Barcode</h3>

                <div class="table-wrap">
                    <table class="table table-bordered" id="product_table">
                        <thead>
                            <tr class="text-center">
                                <th style="min-width:260px">Product</th>
                                <th style="min-width:100px">Qty</th>
                                <th style="min-width:110px">Unit</th>
                                <th style="min-width:130px">Unit Price</th>
                                <th style="min-width:130px">Total</th>
                                <th style="min-width:180px">Barcode</th>
                                <th style="min-width:90px">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($purchase->details as $index => $item)
                                <tr>
                                    <td>
                                        <input
                                            type="text" name="products[{{ $index }}][name]" class="form-control product_search" value="{{ old("products.$index.name", $item->product->product_name ?? '') }}" placeholder="Search product..." autocomplete="off" required>
                                        <input type="hidden" name="products[{{ $index }}][product_id]" class="product_id" value="{{ old("products.$index.product_id", $item->product_id) }}">
                                        <small class="stock-info">
                                            @if ($item->product)
                                                Stock: {{ $item->product->stock }}
                                            @endif
                                        </small>
                                    </td>

                                    <td>
                                        <input
                                            type="number"
                                            name="products[{{ $index }}][qty]"
                                            class="form-control qty"
                                            value="{{ old("products.$index.qty", $item->qty) }}"
                                            min="0.001"
                                            step="0.001"
                                            required
                                        >
                                    </td>

                                    <td>
                                        <select
                                            name="products[{{ $index }}][unit]"
                                            class="form-control unit"
                                            required
                                        >
                                            <option value="">-- Select Unit --</option>

                                            @foreach ($units as $unit)
                                                <option
                                                    value="{{ $unit->ShortName }}"
                                                    {{ old("products.$index.unit", $item->unit) == $unit->ShortName ? 'selected' : '' }}
                                                >
                                                    {{ $unit->ShortName }}
                                                </option>
                                            @endforeach

                                            @if (
                                                $item->unit &&
                                                !$units->contains(fn ($unit) => $unit->ShortName == $item->unit)
                                            )
                                                <option value="{{ $item->unit }}" selected>
                                                    {{ $item->unit }}
                                                </option>
                                            @endif
                                        </select>
                                    </td>

                                    <td>
                                        <input
                                            type="number"
                                            name="products[{{ $index }}][price]"
                                            class="form-control price"
                                            value="{{ old("products.$index.price", $item->price) }}"
                                            min="0"
                                            step="0.00001"
                                            required
                                        >
                                    </td>

                                    <td>
                                        <input
                                            type="text"
                                            class="form-control row_total"
                                            value="{{ number_format((float) $item->qty * (float) $item->price, 2, '.', '') }}"
                                            readonly
                                        >
                                    </td>

                                    <td class="text-center">
                                        @if (!empty($item->barcode))
                                            <img
                                                class="barcode-preview"
                                                src="data:image/png;base64,{{ $item->barcode }}"
                                                alt="Product barcode"
                                            >
                                            <small class="text-muted">Saved barcode</small>
                                        @else
                                            <span class="barcode-missing">
                                                Barcode not available
                                            </span>
                                            <small class="d-block text-muted">
                                                Update to generate
                                            </small>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <button
                                            type="button"
                                            class="btn btn-danger btn-sm remove-row"
                                        >
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td>
                                        <input
                                            type="text"
                                            name="products[0][name]"
                                            class="form-control product_search"
                                            placeholder="Search product..."
                                            autocomplete="off"
                                            required
                                        >
                                        <input
                                            type="hidden"
                                            name="products[0][product_id]"
                                            class="product_id"
                                        >
                                        <small class="stock-info"></small>
                                    </td>

                                    <td>
                                        <input
                                            type="number"
                                            name="products[0][qty]"
                                            class="form-control qty"
                                            value="1"
                                            min="0.001"
                                            step="0.001"
                                            required
                                        >
                                    </td>

                                    <td>
                                        <select
                                            name="products[0][unit]"
                                            class="form-control unit"
                                            required
                                        >
                                            <option value="">-- Select Unit --</option>
                                            @foreach ($units as $unit)
                                                <option value="{{ $unit->ShortName }}">
                                                    {{ $unit->ShortName }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>

                                    <td>
                                        <input
                                            type="number"
                                            name="products[0][price]"
                                            class="form-control price"
                                            value="0"
                                            min="0"
                                            step="0.00001"
                                            required
                                        >
                                    </td>

                                    <td>
                                        <input type="text" class="form-control row_total" value="0.00" readonly>
                                    </td>

                                    <td class="text-center">
                                        <small class="text-muted">Generated on update</small>
                                    </td>

                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-sm remove-row">
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <button type="button" class="btn btn-success btn-sm mt-2" id="addRowBtn">
                    + Add Product
                </button>

                {{-- Totals --}}
                <h3>Calculation & Payment</h3>

                <div class="row">
                    <div class="form-group col-md-4">
                        <label for="subtotal_display">Subtotal</label>
                        <input
                            type="number"
                            class="form-control"
                            id="subtotal_display"
                            value="{{ number_format((float) ($purchase->subtotal ?? 0), 2, '.', '') }}"
                            readonly
                        >
                    </div>

                    <div class="form-group col-md-4">
                        <label for="tax_percentage">Tax (%)</label>
                        <input
                            type="number"
                            class="form-control"
                            id="tax_percentage"
                            name="tax_percentage"
                            value="{{ old('tax_percentage', $purchase->tax_percentage ?? 0) }}"
                            min="0"
                            step="0.01"
                        >
                    </div>

                    <div class="form-group col-md-4">
                        <label for="tax_amount">Tax Amount</label>
                        <input
                            type="number"
                            class="form-control"
                            id="tax_amount"
                            name="tax_amount"
                            value="{{ number_format((float) ($purchase->tax_amount ?? 0), 2, '.', '') }}"
                            readonly
                        >
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-4">
                        <label for="shipping_amount">Shipping Amount</label>
                        <input
                            type="number"
                            class="form-control"
                            id="shipping_amount"
                            name="shipping_amount"
                            value="{{ old('shipping_amount', $purchase->shipping_amount ?? 0) }}"
                            min="0"
                            step="0.01"
                        >
                    </div>

                    <div class="form-group col-md-4">
                        <label for="total_amount">Total Amount</label>
                        <input
                            type="number"
                            class="form-control"
                            id="total_amount"
                            name="total_amount"
                            value="{{ number_format($purchaseTotal, 2, '.', '') }}"
                            readonly
                        >
                    </div>

                    <div class="form-group col-md-4">
                        <label for="paid_amount">Paid Amount</label>
                        <input
                            type="number"
                            class="form-control"
                            id="paid_amount"
                            name="paid_amount"
                            value="{{ number_format($paidValue, 2, '.', '') }}"
                            min="0"
                            step="0.01"
                        >
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-4">
                        <label for="due_amount">Due Amount</label>
                        <input
                            type="number"
                            class="form-control"
                            id="due_amount"
                            value="{{ number_format($purchaseDue, 2, '.', '') }}"
                            readonly
                        >
                    </div>

                    <div class="form-group col-md-4">
                        <label for="BankName">Bank Name</label>
                        <input
                            type="text"
                            class="form-control"
                            id="BankName"
                            name="BankName"
                            value="{{ old('BankName') }}"
                        >
                    </div>

                    <div class="form-group col-md-4">
                        <label for="account_number">Account Number</label>
                        <input
                            type="text"
                            class="form-control"
                            id="account_number"
                            name="account_number"
                            value="{{ old('account_number') }}"
                        >
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="cheque_number">Cheque Number</label>
                        <input
                            type="text"
                            class="form-control"
                            id="cheque_number"
                            name="cheque_number"
                            value="{{ old('cheque_number') }}"
                        >
                    </div>

                    <div class="form-group col-md-6">
                        <label for="transaction_number">Transaction Number</label>
                        <input
                            type="text"
                            class="form-control"
                            id="transaction_number"
                            name="transaction_number"
                            value="{{ old('transaction_number') }}"
                        >
                    </div>
                </div>

                <div class="action-row">
                    <button type="button" class="btn btn-secondary" onclick="history.back()">
                        ← Back
                    </button>

                    <button type="submit" class="btn btn-primary">
                        Update Purchase
                    </button>
                </div>
            </form>
        </div>
    </div>

    @includeIf('layouts.footer')

    <script>
        (function () {
            'use strict';

            const form = document.getElementById('purchaseEditForm');
            const tableBody = document.querySelector('#product_table tbody');

            const units = @json(
                $units->map(fn ($unit) => [
                    'value' => (string) $unit->ShortName,
                    'label' => (string) $unit->ShortName,
                ])->values()
            );

            // নতুন সারির index যেন বিদ্যমান input name-এর সঙ্গে না মিলে।
            let rowIndex = {{ (int) $purchase->details->count() }};

            function numberValue(value) {
                const parsed = parseFloat(String(value ?? '').replace(/,/g, ''));
                return Number.isFinite(parsed) ? parsed : 0;
            }

            function escapeHtml(value) {
                return String(value ?? '').replace(/[&<>"']/g, function (char) {
                    return {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#039;'
                    }[char];
                });
            }

            function updateRowTotal(row) {
                const qty = numberValue(row.querySelector('.qty')?.value);
                const price = numberValue(row.querySelector('.price')?.value);
                const totalInput = row.querySelector('.row_total');

                if (totalInput) {
                    totalInput.value = (qty * price).toFixed(2);
                }
            }

            function calculateDue() {
                const total = numberValue(document.getElementById('total_amount').value);
                const paid = numberValue(document.getElementById('paid_amount').value);

                document.getElementById('due_amount').value =
                    (total - paid).toFixed(2);
            }

            function calculateGrandTotal() {
                let subtotal = 0;

                tableBody.querySelectorAll('tr').forEach(function (row) {
                    updateRowTotal(row);
                    subtotal += numberValue(row.querySelector('.row_total')?.value);
                });

                const taxPercent = Math.max(
                    0,
                    numberValue(document.getElementById('tax_percentage').value)
                );

                const taxAmount = subtotal * taxPercent / 100;

                const shipping = Math.max(
                    0,
                    numberValue(document.getElementById('shipping_amount').value)
                );

                const total = subtotal + taxAmount + shipping;

                document.getElementById('subtotal_display').value = subtotal.toFixed(2);
                document.getElementById('tax_amount').value = taxAmount.toFixed(2);
                document.getElementById('total_amount').value = total.toFixed(2);

                calculateDue();
            }

            function initProductAutocomplete(input) {
                if (
                    !input ||
                    !window.jQuery ||
                    !jQuery.ui ||
                    !jQuery.ui.autocomplete
                ) {
                    return;
                }

                if (jQuery(input).data('ui-autocomplete')) {
                    jQuery(input).autocomplete('destroy');
                }

                jQuery(input).autocomplete({
                    source: '/product-autocomplete',
                    minLength: 1,

                    select: function (event, ui) {
                        const row = input.closest('tr');

                        row.querySelector('.product_id').value = ui.item.id ?? '';
                        input.value = ui.item.value ?? ui.item.label ?? '';

                        const stockInfo = row.querySelector('.stock-info');

                        if (stockInfo) {
                            stockInfo.textContent = 'Stock: ' + (ui.item.stock ?? 0);
                        }

                        // নতুন পণ্যের জন্য autocomplete থেকে মূল্য পাওয়া গেলে বসাবে।
                        const priceInput = row.querySelector('.price');

                        if (
                            priceInput &&
                            (priceInput.value === '' || numberValue(priceInput.value) === 0) &&
                            ui.item.purchase_price !== undefined
                        ) {
                            priceInput.value = ui.item.purchase_price;
                        }

                        calculateGrandTotal();

                        return false;
                    }
                });
            }

            function initSupplierAutocomplete() {
                const input = document.getElementById('supplier_search');

                if (
                    !input ||
                    !window.jQuery ||
                    !jQuery.ui ||
                    !jQuery.ui.autocomplete
                ) {
                    return;
                }

                jQuery(input).autocomplete({
                    source: '/supplier-autocomplete',
                    minLength: 1,

                    select: function (event, ui) {
                        input.value = ui.item.value ?? ui.item.label ?? '';

                        document.getElementById('supplier_id').value =
                            ui.item.id ?? '';

                        document.getElementById('supplier_info').textContent =
                            'Mobile: ' + (ui.item.mobile ?? 'N/A');

                        return false;
                    }
                });
            }

            function addRow() {
                const index = rowIndex++;

                const unitOptions = [
                    '<option value="">-- Select Unit --</option>',
                    ...units.map(function (unit) {
                        return `
                            <option value="${escapeHtml(unit.value)}">
                                ${escapeHtml(unit.label)}
                            </option>
                        `;
                    })
                ].join('');

                const row = document.createElement('tr');

                row.innerHTML = `
                    <td>
                        <input
                            type="text"
                            name="products[${index}][name]"
                            class="form-control product_search"
                            placeholder="Search product..."
                            autocomplete="off"
                            required
                        >
                        <input
                            type="hidden"
                            name="products[${index}][product_id]"
                            class="product_id"
                        >
                        <small class="stock-info"></small>
                    </td>

                    <td>
                        <input
                            type="number"
                            name="products[${index}][qty]"
                            class="form-control qty"
                            value="1"
                            min="0.001"
                            step="0.001"
                            required
                        >
                    </td>

                    <td>
                        <select
                            name="products[${index}][unit]"
                            class="form-control unit"
                            required
                        >
                            ${unitOptions}
                        </select>
                    </td>

                    <td>
                        <input
                            type="number"
                            name="products[${index}][price]"
                            class="form-control price"
                            value="0"
                            min="0"
                            step="0.00001"
                            required
                        >
                    </td>

                    <td>
                        <input
                            type="text"
                            class="form-control row_total"
                            value="0.00"
                            readonly
                        >
                    </td>

                    <td class="text-center">
                        <small class="text-muted">
                            Update-এর পর বারকোড দেখাবে
                        </small>
                    </td>

                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm remove-row">
                            Remove
                        </button>
                    </td>
                `;

                tableBody.appendChild(row);

                initProductAutocomplete(row.querySelector('.product_search'));
                calculateGrandTotal();
            }

            function removeRow(button) {
                const rows = tableBody.querySelectorAll('tr');

                if (rows.length <= 1) {
                    alert('কমপক্ষে একটি পণ্য রাখতে হবে।');
                    return;
                }

                button.closest('tr').remove();
                calculateGrandTotal();
            }

            document.getElementById('addRowBtn').addEventListener('click', addRow);

            tableBody.addEventListener('click', function (event) {
                const button = event.target.closest('.remove-row');

                if (button) {
                    removeRow(button);
                }
            });

            form.addEventListener('input', function (event) {
                if (
                    event.target.matches(
                        '.qty, .price, #tax_percentage, #shipping_amount'
                    )
                ) {
                    calculateGrandTotal();
                }

                if (event.target.matches('#paid_amount')) {
                    calculateDue();
                }

                if (event.target.matches('.product_search')) {
                    const row = event.target.closest('tr');

                    if (row) {
                        row.querySelector('.product_id').value = '';
                    }
                }

                if (event.target.matches('#supplier_search')) {
                    document.getElementById('supplier_id').value = '';
                }
            });

            form.addEventListener('change', function (event) {
                if (
                    event.target.matches(
                        '.qty, .price, #tax_percentage, #shipping_amount'
                    )
                ) {
                    calculateGrandTotal();
                }

                if (event.target.matches('#paid_amount')) {
                    calculateDue();
                }
            });

            form.addEventListener('submit', function (event) {
                const supplierId = document.getElementById('supplier_id').value;

                if (!supplierId) {
                    event.preventDefault();

                    alert('Autocomplete তালিকা থেকে Supplier নির্বাচন করুন।');

                    document.getElementById('supplier_search').focus();

                    return;
                }

                let valid = true;

                tableBody.querySelectorAll('tr').forEach(function (row) {
                    const productId = row.querySelector('.product_id')?.value;
                    const qty = numberValue(row.querySelector('.qty')?.value);
                    const price = numberValue(row.querySelector('.price')?.value);
                    const unit = row.querySelector('.unit')?.value;

                    if (
                        !productId ||
                        !unit ||
                        qty <= 0 ||
                        price < 0
                    ) {
                        valid = false;
                    }
                });

                if (!valid) {
                    event.preventDefault();

                    alert(
                        'প্রতিটি সারিতে autocomplete থেকে পণ্য নির্বাচন করুন এবং Qty, Unit ও Price সঠিকভাবে দিন।'
                    );

                    return;
                }

                const paid = numberValue(
                    document.getElementById('paid_amount').value
                );

                if (paid < 0) {
                    event.preventDefault();
                    alert('Paid Amount ঋণাত্মক হতে পারবে না।');
                }
            });

            // পুরোনো পণ্যগুলোর autocomplete চালু করা।
            tableBody.querySelectorAll('.product_search').forEach(
                initProductAutocomplete
            );

            initSupplierAutocomplete();

            // প্রথমবার পেজ খোলার সময় হিসাব আপডেট।
            calculateGrandTotal();
        })();
    </script>
</x-app-layout>