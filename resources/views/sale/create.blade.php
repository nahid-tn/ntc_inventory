<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('নতুন বিক্রয়') }}
        </h2>
    </x-slot>

    @php
        // ================= ইউনিট ম্যাপ (ইংরেজি → বাংলা) =================
        $unitMap = [
            'PC'   => 'টি', 'PCS'  => 'টি', 'SET'  => 'সেট', 'KG'   => 'কেজি',
            'KGS'  => 'কেজি', 'BOX'  => 'বক্স', 'DGN'  => 'ডজন', 'JOR'  => 'জোরা',
            'GUR'  => 'গুরুস', 'DRM'  => 'ড্রাম', 'DRAM' => 'ড্রাম', 'PAC'  => 'প্যাকেট',
        ];

        // ⭐ ইউনিট বাংলায় কনভার্ট করার ফাংশন
        $bnUnit = function($unit) use ($unitMap) {
            if (empty($unit)) return '';
            $key = strtoupper(trim($unit));
            return $unitMap[$key] ?? $unit;
        };
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <form action="{{ route('sale.store') }}" method="post" id="saleForm">
                    @csrf

                    <!-- Customer & Invoice Info -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">চালান নম্বর (Invoice No)</label>
                            <input type="text" name="invoice_no" class="form-control" placeholder="খালি রাখলে অটো তৈরি হবে" lang="en" inputmode="numeric">
                            <small class="text-muted">খালি রাখলে স্বয়ংক্রিয়ভাবে তৈরি হবে</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">বিক্রয়ের তারিখ (Sale Date)</label>
                            <input type="date" name="sale_date"  class="form-control" required value="{{ date('Y-m-d') }}" lang="en">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">ক্রেতার নাম (Customer Name)</label>
                            <div class="position-relative">
                                <input type="text" id="customer_search" class="form-control pe-5" placeholder="ক্রেতা খুঁজুন..." autocomplete="off" required lang="bn">
                                <button type="button" class="btn btn-success btn-sm position-absolute" style="top:50%; right:5px; transform:translateY(-50%); width:36px; height:36px; padding:0;" title="নতুন ক্রেতা যোগ করুন" onclick="openCustomerWindow()">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                            <input type="hidden" name="customer_id" id="customer_id">
                            <small class="text-muted" id="customer_info"></small>
                        </div>
                    </div>

                    <hr>

                    <!-- New Products -->
                    <h4 class="text-center">নতুন পণ্যসমূহ</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="new_product_table">
                            <thead>
                                <tr>
                                    <th style="min-width: 300px;">পণ্যের নাম</th>
                                    <th style="min-width: 50px;">পরিমাণ</th>
                                    <th style="min-width: 80px;">একক</th>
                                    <th style="min-width: 80px;">একক দর</th>
                                    <th style="min-width: 100px;">মোট</th>
                                    <th style="min-width: 120px;">মন্তব্য</th>
                                    <th style="min-width: 80px;">অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <input type="text" name="products[0][product_name]" class="form-control product_search" placeholder="পণ্য খুঁজুন" lang="bn">
                                        <input type="hidden" name="products[0][product_id]" class="product_id">
                                        <input type="hidden" name="products[0][is_others]" class="is_others" value="0">
                                        <small class="text-muted stock_info"></small>
                                    </td>
                                    <td><input type="text" name="products[0][qty]" class="form-control qty" value="১" lang="bn" inputmode="decimal" placeholder="পরিমাণ"></td>
                                    <td>
                                        <select name="products[0][unit]" class="form-control unit" required lang="bn">
                                            <option value="">-- একক নির্বাচন --</option>
                                            @foreach($units as $unit)
                                                <option value="{{ $unit->ShortName }}">
                                                    {{ $bnUnit($unit->ShortName) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" name="products[0][price]" class="form-control price" value="০" lang="bn" inputmode="decimal" placeholder="দর"></td>
                                    <td><input type="text" class="form-control row_total" readonly value="০.০০" lang="bn"></td>
                                    <td><input type="text" class="form-control" name="products[0][narration]" placeholder="মন্তব্য" lang="bn"> </td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-success btn-sm mb-3" onclick="addRow()">
                        <i class="fa fa-plus"></i> আরও যোগ করুন
                    </button>

                    <hr>

                    <!-- Old Products -->
                    <h4 class="text-center">পুরাতন পণ্যসমূহ</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="old_product_table">
                            <thead>
                                <tr>
                                    <th style="min-width: 200px;">পণ্যের নাম</th>
                                    <th style="min-width: 80px;">পরিমাণ</th>
                                    <th style="min-width: 100px;">একক</th>
                                    <th style="min-width: 120px;">দর</th>
                                    <th style="min-width: 120px;">মোট</th>
                                    <th style="min-width: 150px;">মন্তব্য</th>
                                    <th style="min-width: 80px;">অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <button type="button" class="btn btn-success btn-sm mb-3" onclick="addOldRow()">
                        <i class="fa fa-plus"></i> আরও যোগ করুন
                    </button>

                    <hr>

                    <!-- Summary & Payment -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">কর % (Tax %)</label>
                            <input type="text" class="form-control" name="tax_percentage" id="tax_percentage" value="০" lang="bn" inputmode="decimal">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">শিপিং (Shipping)</label>
                            <input type="text" class="form-control" id="shipping_amount" name="shipping_amount" value="০" lang="bn" inputmode="decimal">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">ছাড় (Discount)</label>
                            <input type="text" class="form-control" id="discount_amount" name="discount_amount" value="০" lang="bn" inputmode="decimal">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">সর্বমোট (Total Amount)</label>
                            <input type="text" class="form-control" id="total_amount" name="total_amount" readonly value="০.০০" style="background-color: #e9ecef; font-weight: bold;" lang="bn">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">পরিশোধিত (Paid Amount)</label>
                            <input type="text" class="form-control" id="paid_amount" name="paid_amount" value="০" lang="bn" inputmode="decimal">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">বাকি (Due Amount)</label>
                            <input type="text" class="form-control" id="due_amount" name="due_amount" readonly value="০.০০" style="background-color: #e9ecef; font-weight: bold;" lang="bn">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">পেমেন্ট পদ্ধতি</label>
                            <select name="payment_method" class="form-control" lang="bn">
                                <option value="">-- পদ্ধতি নির্বাচন --</option>
                                <option value="cash">নগদ</option>
                                <option value="bank">ব্যাংক</option>
                                <option value="mobile">মোবাইল ব্যাংকিং</option>
                                <option value="cheque">চেক</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">গ্যারান্টির সময় (warranty_period)</label>
                            <input type="text" class="form-control" id="warranty_period" name="warranty_period"placeholder="যদি থাকে" lang="bn">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">বডি তারিখ (Body Date)</label>
                            <input type="date" class="form-control" id="body_date" name="body_date" lang="en">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">স্ট্যাটাস</label>
                            <select name="status" class="form-control" lang="bn">
                                <option value="pending">অপেক্ষমাণ</option>
                                <option value="completed">সম্পন্ন</option>
                                <option value="cancelled">বাতিল</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">নোট / মন্তব্য</label>
                        <textarea name="note" class="form-control" rows="3" placeholder="যদি থাকে" lang="bn"></textarea>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> বিক্রয় সংরক্ষণ করুন
                        </button>
                        <button type="reset" class="btn btn-warning">
                            <i class="fa fa-undo"></i> রিসেট
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="history.back()">
                            <i class="fa fa-arrow-left"></i> ফিরে যান
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

<!-- CSS -->
<style>
    .table-responsive { overflow-x: auto; }
    .form-label { font-weight: 600; margin-bottom: 5px; }
    .alert { padding: 12px 20px; border-radius: 5px; margin-bottom: 20px; }
    .alert-danger { background-color: #f8d7da; border-color: #f5c6cb; color: #721c24; }
    .alert-success { background-color: #d4edda; border-color: #c3e6cb; color: #155724; }
    .btn-sm i { font-size: 12px; }
    .ui-autocomplete { max-height: 250px; overflow-y: auto; z-index: 9999; }

    .ui-autocomplete .others-item {
        background-color: #fff3cd !important;
        font-weight: 600;
        border-top: 1px solid #ffc107;
    }
    .ui-autocomplete .others-item.ui-state-active {
        background-color: #ffc107 !important;
        color: #000 !important;
    }

    input[lang="bn"], textarea[lang="bn"], select[lang="bn"] { ime-mode: active; }
    input[lang="en"], textarea[lang="en"], select[lang="en"] { ime-mode: inactive; }

    @media (max-width: 768px) {
        .col-md-2, .col-md-3, .col-md-4, .col-md-6 { margin-bottom: 10px; }
    }
</style>

<!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script>
// ============================================
// ⭐ বাংলা ↔ ইংরেজি সংখ্যা কনভার্টার (সবার উপরে)
// ============================================
const bnToEn = {
    '০': '0', '১': '1', '২': '2', '৩': '3', '৪': '4',
    '৫': '5', '৬': '6', '৭': '7', '৮': '8', '৯': '9'
};

function banglaToEnglish(str) {
    if (str === null || str === undefined || str === '') return '';
    return String(str).replace(/[০-৯]/g, function(match) {
        return bnToEn[match];
    });
}

function englishToBangla(str) {
    if (str === null || str === undefined || str === '') return '';
    return String(str).replace(/[0-9]/g, function(match) {
        return Object.keys(bnToEn).find(key => bnToEn[key] === match) || match;
    });
}

// ============================================
// ⭐ ক্যালকুলেশন
// ============================================
function calculateGrandTotal() {
    let subtotal = 0;
    document.querySelectorAll('.row_total').forEach(el => {
        subtotal += parseFloat(banglaToEnglish(el.value)) || 0;
    });

    let old_total = 0;
    document.querySelectorAll('.row_total_old').forEach(el => {
        old_total += parseFloat(banglaToEnglish(el.value)) || 0;
    });

    let taxPercent = parseFloat(banglaToEnglish(document.querySelector('[name="tax_percentage"]').value)) || 0;
    let taxAmount = (subtotal * taxPercent) / 100;

    let shipping = parseFloat(banglaToEnglish(document.getElementById('shipping_amount').value)) || 0;
    let discount = parseFloat(banglaToEnglish(document.getElementById('discount_amount').value)) || 0;

    let total = subtotal + taxAmount + shipping - old_total - discount;
    
    document.getElementById('total_amount').value = englishToBangla(total.toFixed(2));

    calculateDue();
}

function calculateDue() {
    let total = parseFloat(banglaToEnglish(document.getElementById('total_amount').value)) || 0;
    let paid = parseFloat(banglaToEnglish(document.getElementById('paid_amount').value)) || 0;
    let due = total - paid;
    document.getElementById('due_amount').value = englishToBangla(due.toFixed(2));
}

// ============================================
// ⭐ রো অ্যাড
// ============================================
let rowIndex = 1;
let oldRowIndex = 1;

function addRow() {
    let tbody = document.querySelector('#new_product_table tbody');
    let row = tbody.insertRow();
    row.innerHTML = `
        <td>
            <input type="text" 
                   name="products[${rowIndex}][product_name]" 
                   class="form-control product_search" 
                   placeholder="পণ্য খুঁজুন"
                   lang="bn">
            <input type="hidden" name="products[${rowIndex}][product_id]" class="product_id">
            <input type="hidden" name="products[${rowIndex}][is_others]" class="is_others" value="0">
            <small class="text-muted stock_info"></small>
        </td>
        <td>
            <input type="text" 
                   name="products[${rowIndex}][qty]" 
                   class="form-control qty" 
                   value="১" 
                   lang="bn"
                   inputmode="decimal">
        </td>
        <td>
            <select name="products[${rowIndex}][unit]" class="form-control unit" required lang="bn">
                <option value="">-- একক নির্বাচন --</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->ShortName }}">
                        {{ $bnUnit($unit->ShortName) }}
                    </option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="text" 
                   name="products[${rowIndex}][price]" 
                   class="form-control price" 
                   value="০"
                   lang="bn"
                   inputmode="decimal">
        </td>
        <td>
            <input type="text" 
                   class="form-control row_total" 
                   readonly
                   value="০.০০"
                   lang="bn">
        </td>
        <td>
            <input type="text" 
                   class="form-control narration" 
                   placeholder="মন্তব্য"
                   lang="bn">
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">
                <i class="fa fa-trash"></i>
            </button>
        </td>
    `;
    rowIndex++;
    calculateGrandTotal();
}

function addOldRow() {
    let tbody = document.querySelector('#old_product_table tbody');
    let row = tbody.insertRow();
    row.innerHTML = `
        <td>
            <select name="old_products[${oldRowIndex}][product_name]" class="form-control old_product_select" required lang="bn">
                <option value="">-- পণ্যের নাম নির্বাচন --</option>
                @foreach($old_products as $old_product)
                    <option value="{{ $old_product->product_name }}">
                        {{ $old_product->product_name }}
                    </option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="text" 
                   name="old_products[${oldRowIndex}][qty]" 
                   class="form-control old_qty" 
                   value="০" 
                   lang="bn"
                   inputmode="decimal">
        </td>
        <td>
            <select name="old_products[${oldRowIndex}][unit]" class="form-control" required lang="bn">
                <option value="">-- একক নির্বাচন --</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->ShortName }}">
                        {{ $bnUnit($unit->ShortName) }}
                    </option>
                @endforeach
            </select>
        </td>
        <td>
            <input type="text" 
                   name="old_products[${oldRowIndex}][price]" 
                   class="form-control old_price" 
                   value="০"
                   lang="bn"
                   inputmode="decimal">
        </td>
        <td>
            <input type="text" 
                   class="form-control row_total_old" 
                   readonly
                   value="০.০০"
                   lang="bn">
        </td>
        <td>
            <input type="text" 
                   class="form-control narration" 
                   placeholder="মন্তব্য"
                   lang="bn">
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">
                <i class="fa fa-trash"></i>
            </button>
        </td>
    `;
    oldRowIndex++;
    calculateGrandTotal();
}

function removeRow(btn) {
    let row = btn.closest('tr');
    let tbody = row.parentNode;
    if (tbody.rows.length > 1) {
        row.remove();
        calculateGrandTotal();
    } else {
        alert('অন্তত একটি সারি থাকতে হবে!');
    }
}

// ============================================
// ⭐ ইনপুট লিসেনার
// ============================================
document.addEventListener('input', function(e) {
    let row = e.target.closest('tr');

    // ⭐ Qty/Price ইনপুটে বাংলা → ইংরেজি কনভার্ট (গণনার জন্য)
    if (e.target.classList.contains('qty') || 
        e.target.classList.contains('price') || 
        e.target.classList.contains('old_qty') || 
        e.target.classList.contains('old_price')) {
        
        let englishValue = banglaToEnglish(e.target.value);
        if (e.target.value !== englishValue) {
            let cursorPos = e.target.selectionStart;
            e.target.value = englishValue;
            e.target.setSelectionRange(cursorPos, cursorPos);
        }
    }

    // New product calculation
    if (row && (e.target.classList.contains('qty') || e.target.classList.contains('price'))) {
        let qty = parseFloat(banglaToEnglish(row.querySelector('.qty').value)) || 0;
        let price = parseFloat(banglaToEnglish(row.querySelector('.price').value)) || 0;
        let totalEl = row.querySelector('.row_total');
        if (totalEl) {
            totalEl.value = englishToBangla((qty * price).toFixed(2));
        }
        calculateGrandTotal();
    }

    // Old product calculation
    if (row && (e.target.classList.contains('old_qty') || e.target.classList.contains('old_price'))) {
        let o_qty = parseFloat(banglaToEnglish(row.querySelector('.old_qty').value)) || 0;
        let o_price = parseFloat(banglaToEnglish(row.querySelector('.old_price').value)) || 0;
        let totalEl = row.querySelector('.row_total_old');
        if (totalEl) {
            totalEl.value = englishToBangla((o_qty * o_price).toFixed(2));
        }
        calculateGrandTotal();
    }

    if (['paid_amount', 'shipping_amount', 'discount_amount', 'tax_percentage'].includes(e.target.id)) {
        calculateGrandTotal();
    }
});

// ============================================
// jQuery - অটোকমপ্লিট
// ============================================
$(document).ready(function () {
    
    // Customer autocomplete
    $('#customer_search').autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "{{ route('customer.autocomplete') }}",
                dataType: "json",
                data: { term: request.term },
                success: function(data) {
                    response($.map(data, function(item){
                        return {
                            label: item.value + ' | মোবাইল: ' + (item.mobile ?? ''),
                            value: item.value,
                            id: item.id,
                            mobile: item.mobile,
                            address: item.address
                        };
                    }));
                }
            });
        },
        minLength: 1,
        select: function(event, ui) {
            $('#customer_search').val(ui.item.value);
            $('#customer_id').val(ui.item.id);
            $('#customer_info').text('মোবাইল: ' + (ui.item.mobile ?? '') + ' | ঠিকানা: ' + (ui.item.address ?? ''));
            return false;
        }
    });

    $('#customer_search').on('blur', function() {
        if (!$('#customer_id').val()) {
            $(this).val('');
            $('#customer_info').text('');
        }
    });

    // Product autocomplete
    $(document).on('focus', '.product_search', function () {
        let $input = $(this);

        if ($input.data('ui-autocomplete')) {
            $input.autocomplete('destroy');
        }

        $input.autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "{{ route('product.autocomplete') }}",
                    dataType: "json",
                    data: { term: request.term },
                    success: function(data) {
                        let results = $.map(data, function(item) {
                            return {
                                label: item.label + 
                                    ' (স্টক:' + item.stock + 
                                    ' ক্রয়:' + (item.purchase_price || '0') +
                                    ' পাইকারি:' + (item.wholesale_price || '0') + 
                                    ' বিক্রয়:' + (item.selling_price || '0') + ')',
                                value: item.value,
                                id: item.id,
                                stock: item.stock || 0,
                                purchase_price: item.purchase_price || 0,
                                wholesale_price: item.wholesale_price || 0,
                                selling_price: item.selling_price || 0,
                                is_others: false
                            };
                        });

                        if (results.length === 0 && request.term.trim() !== '') {
                            results.push({
                                label: '➕ অন্যান্য (নতুন পণ্য হিসেবে যোগ করুন): "' + request.term + '"',
                                value: request.term,
                                id: 'others',
                                stock: 0,
                                purchase_price: 0,
                                wholesale_price: 0,
                                selling_price: 0,
                                is_others: true
                            });
                        }

                        response(results);
                    }
                });
            },
            minLength: 1,
            open: function(event, ui) {
                $('.ui-autocomplete .ui-menu-item').each(function() {
                    if ($(this).text().indexOf('➕ অন্যান্য') !== -1) {
                        $(this).addClass('others-item');
                    }
                });
            },
            select: function(event, ui) {
                let row = $(this).closest('tr');

                if (ui.item.is_others) {
                    row.find('.product_id').val('');
                    row.find('.is_others').val('1');
                    row.find('.stock_info').html(
                        '<span class="text-warning fw-bold">' +
                        '<i class="fa fa-exclamation-triangle"></i> নতুন পণ্য (ডাটাবেজে নেই)' +
                        '</span>'
                    );
                    row.find('.price').val('০').focus().select();
                    $(this).val(ui.item.value);
                } else {
                    row.find('.product_id').val(ui.item.id);
                    row.find('.is_others').val('0');
                    
                    row.find('.stock_info').html(
                        'স্টক:' + ui.item.stock + 
                        ' ক্রয়:' + parseFloat(ui.item.purchase_price).toFixed(2) +
                        ' পাইকারি:' + parseFloat(ui.item.wholesale_price).toFixed(2) +
                        ' বিক্রয়:' + parseFloat(ui.item.selling_price).toFixed(2)
                    );
                    
                    row.find('.price').val(englishToBangla(ui.item.wholesale_price));
                    $(this).val(ui.item.value);
                }

                calculateGrandTotal();
                return false;
            }
        });
    });

    // Ctrl+M shortcut
    $(document).on('keydown', '.product_search', function(e) {
        if (e.ctrlKey && e.key.toLowerCase() === 'm') {
            e.preventDefault();
            let row = $(this).closest('tr');
            row.find('.is_others').val('1');
            row.find('.product_id').val('');
            row.find('.stock_info').html(
                '<span class="text-warning fw-bold">' +
                '<i class="fa fa-exclamation-triangle"></i> নতুন পণ্য (ডাটাবেজে নেই)' +
                '</span>'
            );
            row.find('.price').focus().select();
        }
    });

    // ফর্ম সাবমিট ভ্যালিডেশন
    $('#saleForm').on('submit', function(e) {
        let customerId = $('#customer_id').val();
        if (!customerId) {
            e.preventDefault();
            alert('অনুগ্রহ করে একজন ক্রেতা নির্বাচন করুন!');
            $('#customer_search').focus();
            return false;
        }

        let hasProduct = false;
        let invalidRow = false;

        document.querySelectorAll('#new_product_table tbody tr').forEach(function(row) {
            let name = row.querySelector('.product_search')?.value?.trim();
            let isOthers = row.querySelector('.is_others')?.value === '1';
            let productId = row.querySelector('.product_id')?.value;
            let unit = row.querySelector('.unit')?.value;

            if (name) {
                hasProduct = true;
                if (isOthers && !unit) {
                    invalidRow = true;
                    row.querySelector('.unit')?.focus();
                }
                if (!isOthers && !productId) {
                    invalidRow = true;
                }
            }
        });

        if (!hasProduct) {
            e.preventDefault();
            alert('অনুগ্রহ করে অন্তত একটি পণ্য যোগ করুন!');
            return false;
        }

        if (invalidRow) {
            e.preventDefault();
            alert('অনুগ্রহ করে সব প্রয়োজনীয় ঘর পূরণ করুন (একক, ইত্যাদি)!');
            return false;
        }

        // ⭐ সব সংখ্যা ইংরেজিতে কনভার্ট
        document.querySelectorAll(
            '.qty, .price, .old_qty, .old_price, ' +
            '.row_total, .row_total_old, ' +
            '#paid_amount, #shipping_amount, #discount_amount, ' +
            '#tax_percentage, #total_amount, #due_amount'
        ).forEach(function(input) {
            if (input.value) {
                input.value = banglaToEnglish(input.value);
            }
        });

        return true;
    });
});

// ============================================
// কাস্টমার উইন্ডো
// ============================================
function openCustomerWindow() {
    const width = 900;
    const height = 700;
    const left = window.screenX + (window.outerWidth - width) / 2;
    const top = window.screenY + (window.outerHeight - height) / 2;

    window.open(
        "{{ route('customer.newcustomerform') }}",
        "CustomerWindow",
        `width=${width},height=${height},left=${left},top=${top},scrollbars=yes,resizable=yes`
    );
}

function selectCustomer(id, name, mobile, address) {
    $('#customer_search').val(name);
    $('#customer_id').val(id);
    $('#customer_info').text('মোবাইল: ' + (mobile ?? '') + ' | ঠিকানা: ' + (address ?? ''));
}

// ============================================
// পেজ লোড হলে
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const invoiceInput = document.querySelector('[name="invoice_no"]');
    if (invoiceInput) {
        invoiceInput.addEventListener('blur', function() {
            if (!this.value.trim()) {
                this.placeholder = 'অটো: INV-' + Date.now().toString().slice(-6);
            }
        });
    }
    
    calculateGrandTotal();
});
</script>