<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('বিক্রয় সম্পাদনা') }}
        </h2>
    </x-slot>

    <style>
        .sale-edit-page { padding: 28px 12px; background: #f4f7fb; }
        .sale-edit-container { max-width: 1240px; margin: 0 auto; }
        .sale-edit-card { background: #fff; border: 1px solid #e5eaf1; border-radius: 16px; box-shadow: 0 8px 28px rgba(15, 23, 42, .07); padding: 24px; }
        .sale-page-title { color: #172554; font-size: 1.45rem; font-weight: 800; margin: 0; }
        .sale-subtitle { color: #64748b; font-size: .92rem; margin-top: 5px; }
        .section-heading { display: flex; align-items: center; gap: 10px; color: #1e293b; font-size: 1.05rem; font-weight: 800; margin: 8px 0 14px; }
        .section-heading:before { content: ''; display: block; width: 5px; height: 24px; border-radius: 8px; background: #2563eb; }
        .section-card { background: #fbfdff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; margin-bottom: 20px; }
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; color: #334155; font-weight: 700; font-size: .9rem; margin-bottom: 6px; }
        .form-control { min-height: 40px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px 11px; box-shadow: none; }
        .form-control:focus { border-color: #60a5fa; box-shadow: 0 0 0 3px rgba(59,130,246,.13); }
        .table-wrap { overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 10px; }
        .table { margin-bottom: 0; min-width: 940px; }
        .table th, .table td { vertical-align: middle; padding: 8px !important; font-size: 14px !important; }
        .table thead th { background: #eff6ff; color: #1e3a8a; font-weight: 800; border-bottom: 1px solid #dbeafe; white-space: nowrap; }
        .table .form-control, .table .form-select { min-width: 72px; min-height: 38px; height: auto; padding: 6px 8px !important; font-size: 14px !important; border-radius: 7px; }
        .serial-no { font-weight: 800; color: #475569; text-align: center; background: #f8fafc; width: 48px; }
        .qty, .old_qty { text-align: center !important; }
        .price, .old_price, .row_total, .row_total_old, #shipping_amount, #discount_amount, #paid_amount, #total_amount_display, #due_amount_display, input[name="tax_percentage"] { text-align: right !important; }
        .row_total, .row_total_old { background: #f8fafc; font-weight: 700; }
        .stock_info { display: block; margin-top: 4px; color: #64748b; font-size: 12px; }
        .narration { min-width: 100px; }
        .btn { border-radius: 8px; font-weight: 700; padding: 9px 14px; }
        .btn-sm { padding: 6px 10px !important; font-size: 13px !important; }
        .btn-add-row { margin-top: 12px; }
        .summary-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; height: 100%; }
        .summary-box.total-box { background: #eff6ff; border-color: #bfdbfe; }
        .summary-box.total-box input { font-weight: 800; color: #1d4ed8; }
        .form-actions { display: flex; flex-wrap: wrap; gap: 10px; justify-content: flex-end; padding-top: 18px; border-top: 1px solid #e2e8f0; }
        .alert-danger { border-radius: 10px; }
        @media (max-width: 767px) { .sale-edit-page { padding: 14px 8px; } .sale-edit-card { padding: 14px; } .section-card { padding: 12px; } .sale-page-title { font-size: 1.2rem; } }
    </style>

    <div class="sale-edit-page">
        <div class="sale-edit-container">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="sale-edit-card">
                <div class="mb-4">
                    <h1 class="sale-page-title">বিক্রয় সম্পাদনা</h1>
                    <p class="sale-subtitle">ইনভয়েস, পণ্য, মূল্য এবং পেমেন্টের তথ্য যাচাই করে আপডেট করুন।</p>
                </div>
                <form action="{{ route('sale.update', $sale->id) }}" method="post" id="saleForm">
                    @csrf
                    @method('PUT')

                    <!-- গ্রাহক ও ইনভয়েস -->
                    <div class="section-card">
                    <h3 class="section-heading">গ্রাহক ও ইনভয়েসের তথ্য</h3>
                    <div class="form-row mb-0">
                        <div class="form-group col-md-2">
                            <label>ইনভয়েস নং<span class="text-danger">*</span></label>
                            <input type="text" name="invoice_no" class="form-control" value="{{ $sale->invoice_no }}" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label>গ্রাহকের নাম</label>
                            <input type="text" id="customer_search" class="form-control" placeholder="গ্রাহক খুঁজুন..." autocomplete="off" required
                                value="{{ $sale->customer->customer_name ?? '' }}">
                            <input type="hidden" name="customer_id" id="customer_id" value="{{ $sale->customer_id }}">
                            <small class="text-muted" id="customer_info">
                                @if($sale->customer)
                                    মোবাইল: {{ $sale->customer->mobile ?? '' }} | ঠিকানা: {{ $sale->customer->address ?? '' }}
                                @endif
                            </small>
                        </div>
                        <div class="form-group col-md-2">
                            <label>বিক্রয়ের তারিখ</label> 
                            <input type="date" name="sale_date" class="form-control" value="{{ \Carbon\Carbon::parse($sale->sale_date)->format('Y-m-d') }}" required>
                        </div>
                    </div>
                    </div>

                    <!-- নতুন পণ্য -->
                    <div class="section-card">
                    <h3 class="section-heading">নতুন পণ্য</h3>
                    <div class="table-wrap"><table class="table table-bordered" id="product_table">
                        <thead>
                            <tr>
                                <th style="width: 5%;">ক্রমিক</th>
                                <th style="width: 36%;">পণ্য</th>
                                <th style="width: 7%;" class="qty-col">পরিমাণ</th>
                                <th style="width: 10%;">একক</th>
                                <th style="width: 8%;" class="num-col">একক মূল্য</th>
                                <th style="width: 10%;" class="num-col">মোট</th>
                                <th style="width: 10%;">বিবরণ</th>
                                <th style="width: 10%;">কার্যক্রম</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->details as $index => $item)
                            <tr>
                                <td class="serial-no"></td>
                                <td>
                                    <input type="text" name="products[{{ $index }}][product_name]" class="form-control product_search" placeholder="পণ্য খুঁজুন" value="{{ $item->product->product_name ?? '' }}">
                                    <input type="hidden" name="products[{{ $index }}][product_id]" class="product_id" value="{{ $item->product_id }}">
                                    {{-- <small class="text-muted stock_info">স্টক: {{ $item->product->stock ?? 0 }}</small> --}}
                                </td>
                                <td><input type="text" name="products[{{ $index }}][qty]" class="form-control qty bn-number" value="{{ $item->qty }}" required></td>
                                <td>
                                    <select name="products[{{ $index }}][unit]" class="form-control unit" required>
                                        <option value="set" {{ $item->unit=='set'?'selected':'' }}>সেট</option>
                                        <option value="kg" {{ $item->unit=='kg'?'selected':'' }}>কেজি</option>
                                        <option value="pcs" {{ $item->unit=='pcs'?'selected':'' }}>পিস</option>
                                        <option value="box" {{ $item->unit=='box'?'selected':'' }}>বক্স</option>
                                        <option value="dram" {{ $item->unit=='dram'?'selected':'' }}>ড্রাম</option>
                                    </select>
                                </td>
                                <td><input type="text" name="products[{{ $index }}][price]" class="form-control price bn-number" value="{{ $item->price }}" required></td>
                                <td><input type="text" class="form-control row_total" value="{{ $item->total }}" readonly></td>
                                <td><input type="text" name="products[{{ $index }}][narration]" class="form-control narration" value="{{ $item->narration }}"></td>
                                <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">মুছুন</button></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table></div>
                    <button type="button" class="btn btn-success btn-sm btn-add-row" onclick="addRow()">＋ নতুন পণ্য যোগ করুন</button>
                    </div>

                    <!-- পুরাতন পণ্য -->
                    <div class="section-card">
                    <h3 class="section-heading">পুরাতন পণ্য / এক্সচেঞ্জ</h3>
                    <div class="table-wrap"><table class="table table-bordered" id="old_product_table">
                        <thead>
                            <tr>
                                <th style="width: 6%;">ক্রমিক</th>
                                <th style="width: 25%;">পণ্য</th>
                                <th style="width: 10%;" class="qty-col">পরিমাণ</th>
                                <th style="width: 10%;">একক</th>
                                <th style="width: 12%;" class="num-col">একক মূল্য</th>
                                <th style="width: 12%;" class="num-col">মোট</th>
                                <th style="width: 15%;">বিবরণ</th>
                                <th style="width: 10%;">কার্যক্রম</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $oldRowIndex = 0; @endphp
                            @foreach($sale->oldProductPurchases as $purchase)
                                @foreach($purchase->details as $i)
                                <tr>
                                    <td class="serial-no"></td>
                                    <td>
                                        <select name="old_products[{{ $oldRowIndex }}][product_name]" class="form-control old_product_name" required>
                                            <option value="">-- পণ্যের নাম নির্বাচন করুন --</option>
                                            @foreach($old_products as $old_product)
                                                <option value="{{ $old_product->product_name }}"
                                                    {{ (isset($i->oldProduct) && $i->oldProduct->product_name == $old_product->product_name) ? 'selected' : '' }}>
                                                    {{ $old_product->product_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" name="old_products[{{ $oldRowIndex }}][qty]" class="form-control old_qty bn-number" value="{{ $i->qty }}"></td>
                                    <td>
                                        <select name="old_products[{{ $oldRowIndex }}][unit]" class="form-control old_unit">
                                            <option value="set" {{ $i->unit=='set'?'selected':'' }}>সেট</option>
                                            <option value="kg" {{ $i->unit=='kg'?'selected':'' }}>কেজি</option>
                                            <option value="pcs" {{ $i->unit=='pcs'?'selected':'' }}>পিস</option>
                                            <option value="box" {{ $i->unit=='box'?'selected':'' }}>বক্স</option>
                                            <option value="dram" {{ $i->unit=='dram'?'selected':'' }}>ড্রাম</option>
                                        </select>
                                    </td>
                                    <td><input type="text" name="old_products[{{ $oldRowIndex }}][price]" class="form-control old_price bn-number" value="{{ $i->price }}"></td>
                                    <td><input type="text" class="form-control row_total_old" readonly></td>
                                    <td><input type="text" name="old_products[{{ $oldRowIndex }}][narration]" class="form-control narration" value="{{ $i->narration }}"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">মুছুন</button></td>
                                </tr>
                                @php $oldRowIndex++; @endphp
                                @endforeach
                            @endforeach
                        </tbody>
                    </table></div>
                    <button type="button" class="btn btn-success btn-sm btn-add-row" onclick="addOldRow()">＋ পুরাতন পণ্য যোগ করুন</button>
                    </div>

                    <!-- সারসংক্ষেপ -->
                    <div class="section-card">
                    <h3 class="section-heading">বিলের হিসাব ও পেমেন্ট</h3>
                    <div class="form-row mb-3">
                        <div class="form-group col-md-4">
                            <label class="label-right">কর %</label>
                            <input type="text" class="form-control bn-number" name="tax_percentage" value="{{ $sale->tax_percentage }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="label-right">শিপিং</label>
                            <input type="text" class="form-control bn-number" id="shipping_amount" name="shipping_amount" value="{{ $sale->shipping_amount }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="label-right">মোট পরিমাণ</label>
                            <input type="text" class="form-control" id="total_amount_display" readonly>
                            <input type="hidden" name="total_amount" id="total_amount">
                        </div>
                    </div>

                    <div class="form-row mb-3">
                        <div class="form-group col-md-4">
                            <label>ছাড়ের পরিমাণ</label>
                            <input type="text" name="discount_amount" id="discount_amount" class="form-control bn-number" value="{{ $sale->discount_amount }}">
                        </div>
                        @php
                            $paidAmount = $sale->receivedPayments->sum('amount');
                            $dueAmount = $sale->total_amount - $paidAmount;
                        @endphp
                        <div class="form-group col-md-4">
                            <label>পরিশোধিত পরিমাণ</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="paid_amount" id="paid_amount" value="{{ number_format($paidAmount, 2, '.', '') }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label>বাকি পরিমাণ</label>
                            <input type="text" class="form-control" id="due_amount_display" value="{{ number_format($dueAmount, 2, '.', '') }}" readonly>
                            <input type="hidden" name="due_amount" id="due_amount" value="{{ number_format($dueAmount, 2, '.', '') }}">
                        </div>
                    </div>

                    <div class="form-row mb-3">
                        
                        <div class="form-group col-md-4">
                            <label>পেমেন্ট পদ্ধতি</label>

                            @php
                                $paymentMethods = [
                                    'cash'   => 'নগদ',
                                    'bank'   => 'ব্যাংক',
                                    'mobile' => 'মোবাইল ব্যাংকিং',
                                    'cheque' => 'চেক',
                                ];

                                $currentMethod = $sale->payment_method;
                            @endphp

                            <select name="payment_method" class="form-control" required>
                                <option value="">-- পদ্ধতি নির্বাচন করুন --</option>

                                {{-- ডাটাবেইজে থাকা মান তালিকায় না থাকলেও দেখাবে --}}
                                @if ($currentMethod && !array_key_exists($currentMethod, $paymentMethods))
                                    <option value="{{ $currentMethod }}" selected>
                                        {{ $currentMethod }}
                                    </option>
                                @endif

                                @foreach ($paymentMethods as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ $currentMethod === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label>গ্যারান্টি সময়কাল</label>
                            <input type="text" class="form-control" id="warranty_period" name="warranty_period" value="{{ $sale->warranty_period }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label>ব্যাটারি বডি তারিখ</label>
                            <input type="date" class="form-control" id="body_date" name="body_date"
                                value="{{ $sale->body_date ? \Carbon\Carbon::parse($sale->body_date)->format('Y-m-d') : '' }}">
                        </div>
                    </div>
                    <div>
                        <input type="text" class="form-control" id="note" name="note" value="{{ $sale->note }}">
                    </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline-secondary" onclick="history.back()">← পিছনে</button>
                        <button type="submit" class="btn btn-primary">✓ বিক্রয় আপডেট করুন</button>
                    </div>
                </form>
            </div>
        </div>
        <br>
        <span>@include('layouts.footer')</span>
    </div>

<script>
// ==================== বাংলা ↔ ইংরেজি সংখ্যা রূপান্তর ====================
const bnDigits = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];

function toBn(num) {
    if (num === null || num === undefined || num === '') return '';
    return num.toString().replace(/\d/g, d => bnDigits[d]);
}

function toEn(str) {
    if (str === null || str === undefined) return '';
    str = str.toString();
    const bnToEn = {'০':'0','১':'1','২':'2','৩':'3','৪':'4','৫':'5','৬':'6','৭':'7','৮':'8','৯':'9'};
    return str.replace(/[০-৯]/g, d => bnToEn[d]);
}

function formatBn(value) {
    let num = parseFloat(toEn(value)) || 0;
    return toBn(num.toFixed(2));
}

// ==================== ক্রমিক নম্বর অটো আপডেট ====================
function updateSerialNumbers() {
    document.querySelectorAll('#product_table tbody tr').forEach(function(row, index) {
        let serialCell = row.querySelector('.serial-no');
        if (serialCell) serialCell.textContent = toBn(index + 1);
    });

    document.querySelectorAll('#old_product_table tbody tr').forEach(function(row, index) {
        let serialCell = row.querySelector('.serial-no');
        if (serialCell) serialCell.textContent = toBn(index + 1);
    });
}

// ==================== পেজ লোডে সব সংখ্যা বাংলায় দেখানো ====================
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.bn-number').forEach(function(input) {
        if (input.value !== '' && !isNaN(toEn(input.value))) {
            let num = parseFloat(toEn(input.value));
            input.value = toBn(num);
        }
    });

    document.querySelectorAll('#product_table tbody tr').forEach(updateRowTotal);
    document.querySelectorAll('#old_product_table tbody tr').forEach(updateRowTotal);
    calculateGrandTotal();
    updateSerialNumbers();
});

let rowIndex = {{ $sale->details->count() }};
let oldRowIndex = {{ $oldRowIndex }};

let oldProductsList = @json($old_products->pluck('product_name'));

// ------------------------- নতুন পণ্য যোগ -------------------------
function addRow() {
    let tbody = document.querySelector('#product_table tbody');
    let row = tbody.insertRow();
    row.innerHTML = `
        <td class="serial-no"></td>
        <td>
            <input type="text" name="products[${rowIndex}][product_name]" class="form-control product_search" placeholder="পণ্য খুঁজুন">
            <input type="hidden" name="products[${rowIndex}][product_id]" class="product_id">
            <small class="text-muted stock_info"></small>
        </td>
        <td><input type="text" name="products[${rowIndex}][qty]" class="form-control qty bn-number" value="১"></td>
        <td>
            <select name="products[${rowIndex}][unit]" class="form-control unit">
                <option value="set">সেট</option>
                <option value="kg">কেজি</option>
                <option value="pcs">পিস</option>
                <option value="box">বক্স</option>
                <option value="dram">ড্রাম</option>
            </select>
        </td>
        <td><input type="text" name="products[${rowIndex}][price]" class="form-control price bn-number" value="০"></td>
        <td><input type="text" class="form-control row_total" readonly value="০.০০"></td>
        <td><input type="text" name="products[${rowIndex}][narration]" class="form-control narration"></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">মুছুন</button></td>
    `;
    rowIndex++;
    updateRowTotal(row);
    updateSerialNumbers();
}

// ------------------------- পুরাতন পণ্য যোগ -------------------------
function addOldRow() {
    let tbody = document.querySelector('#old_product_table tbody');
    let row = tbody.insertRow();

    let options = '<option value="">-- পণ্যের নাম নির্বাচন করুন --</option>';
    oldProductsList.forEach(p => {
        options += `<option value="${p}">${p}</option>`;
    });
    row.innerHTML = `
        <td class="serial-no"></td>
        <td><select name="old_products[${oldRowIndex}][product_name]" class="form-control old_product_name" required>${options}</select></td>
        <td><input type="text" name="old_products[${oldRowIndex}][qty]" class="form-control old_qty bn-number" value="০"></td>
        <td>
            <select name="old_products[${oldRowIndex}][unit]" class="form-control old_unit">
                <option value="set">সেট</option>
                <option value="kg">কেজি</option>
                <option value="pcs">পিস</option>
                <option value="box">বক্স</option>
                <option value="dram">ড্রাম</option>
            </select>
        </td>
        <td><input type="text" name="old_products[${oldRowIndex}][price]" class="form-control old_price bn-number" value="০"></td>
        <td><input type="text" class="form-control row_total_old" readonly value="০.০০"></td>
        <td><input type="text" name="old_products[${oldRowIndex}][narration]" class="form-control narration"></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">মুছুন</button></td>
    `;
    oldRowIndex++;
    updateRowTotal(row);
    updateSerialNumbers();
}

// ------------------------- সারি মুছুন -------------------------
function removeRow(btn) {
    btn.closest('tr').remove();
    updateSerialNumbers();
    calculateGrandTotal();
}

// ------------------------- প্রতিটি সারির মোট আপডেট -------------------------
function updateRowTotal(row) {
    let qtyEl = row.querySelector('.qty');
    let priceEl = row.querySelector('.price');
    if (qtyEl && priceEl) {
        let qty = parseFloat(toEn(qtyEl.value)) || 0;
        let price = parseFloat(toEn(priceEl.value)) || 0;
        if (row.querySelector('.row_total')) {
            row.querySelector('.row_total').value = formatBn(qty * price);
        }
    }
    let oqEl = row.querySelector('.old_qty');
    let opEl = row.querySelector('.old_price');
    if (oqEl && opEl) {
        let oq = parseFloat(toEn(oqEl.value)) || 0;
        let op = parseFloat(toEn(opEl.value)) || 0;
        if (row.querySelector('.row_total_old')) {
            row.querySelector('.row_total_old').value = formatBn(oq * op);
        }
    }
}

// ------------------------- সর্বমোট হিসাব -------------------------
function calculateGrandTotal() {
    let newTotal = 0;
    document.querySelectorAll('.row_total').forEach(el => newTotal += parseFloat(toEn(el.value)) || 0);
    let oldTotal = 0;
    document.querySelectorAll('.row_total_old').forEach(el => oldTotal += parseFloat(toEn(el.value)) || 0);
    let taxPercent = parseFloat(toEn(document.querySelector('[name="tax_percentage"]').value)) || 0;
    let taxAmount = newTotal * taxPercent / 100;
    let shipping = parseFloat(toEn(document.getElementById('shipping_amount').value)) || 0;
    let discount = parseFloat(toEn(document.getElementById('discount_amount').value)) || 0;
    let total = Math.max(0, newTotal + taxAmount - oldTotal + shipping - discount);

    document.getElementById('total_amount_display').value = formatBn(total);
    document.getElementById('total_amount').value = total.toFixed(2);

    calculateDue();
}

// ------------------------- বাকি পরিমাণ হিসাব -------------------------
function calculateDue() {
    let total = parseFloat(document.getElementById('total_amount').value) || 0;
    let paid  = parseFloat(toEn(document.getElementById('paid_amount').value)) || 0;
    let due = total - paid;

    document.getElementById('due_amount_display').value = formatBn(due);
    document.getElementById('due_amount').value = due.toFixed(2);
}

// ------------------------- ইভেন্ট লিসেনার -------------------------
document.addEventListener('input', function(e){
    let row = e.target.closest('tr');
    if(row) {
        if(e.target.classList.contains('qty') || e.target.classList.contains('price')) {
            updateRowTotal(row);
            calculateGrandTotal();
        }
        if(e.target.classList.contains('old_qty') || e.target.classList.contains('old_price')) {
            updateRowTotal(row);
            calculateGrandTotal();
        }
    }
    if(['shipping_amount','discount_amount'].includes(e.target.id) || e.target.name === 'tax_percentage') calculateGrandTotal();
    if(e.target.id === 'paid_amount') calculateDue();
});

// ==================== ফর্ম সাবমিটের আগে সব বাংলা সংখ্যা ইংরেজিতে রূপান্তর ====================
document.getElementById('saleForm').addEventListener('submit', function(e) {
    document.querySelectorAll('.bn-number').forEach(function(input) {
        let enValue = toEn(input.value);
        input.value = enValue;
    });
});

// ==================== পণ্যের জন্য jQuery অটোকমপ্লিট ====================
$(document).on('focus', '.product_search', function () {
    $(this).autocomplete({
        source: '/product-autocomplete',
        minLength: 1,
        select: function (event, ui) {
            let row = $(this).closest('tr');
            row.find('.product_id').val(ui.item.id);
            row.find('.stock_info').text('স্টক: ' + toBn(ui.item.stock));
            row.find('.price').val(toBn(ui.item.purchase_price ?? 0));
            $(this).val(ui.item.value);
            row.find('.qty').trigger('input');
            return false;
        }
    });
});

// ==================== গ্রাহকের জন্য jQuery অটোকমপ্লিট ====================
$('#customer_search').autocomplete({
    source: "/customer-autocomplete",
    minLength: 1,
    select: function(event, ui){
        $('#customer_search').val(ui.item.value);
        $('#customer_id').val(ui.item.id);
        $('#customer_info').text('মোবাইল: ' + toBn(ui.item.mobile??'') + ' | ঠিকানা: ' + (ui.item.address??''));
        return false;
    }
});
</script>
</x-app-layout>