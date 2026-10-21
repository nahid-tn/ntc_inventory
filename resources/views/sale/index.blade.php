<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('বিক্রয় তালিকা') }}</h2>
        <style>
            @page { margin: 50px 50px; }

            @font-face {
                font-family: 'SolaimanLipi';
                src: url("{{ public_path('fonts/SolaimanLipi.ttf') }}") format('truetype');
                font-weight: normal;
                font-style: normal;
            }

            body {
                font-family: 'SolaimanLipi', sans-serif;
                font-size: 14px;
                line-height: 1.4;
                color: #333;
                margin: 0;
                padding: 0;
            }

            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            table, th, td { border: 1px solid #999; }
            th, td { padding: 8px; text-align: center; }
            th { background-color: #f2f2f2; }
            .totals { width: 40%; float: right; margin-top: 10px; }
            .totals table { border: none; }
            .totals th, .totals td { border: none; text-align: right; padding: 5px 10px; }
            .signatures { display: flex; justify-content: space-between; margin-top: 60px; }
            .signatures div { width: 45%; text-align: center; }
            .signatures div p { margin-top: 60px; border-top: 1px solid #333; }
            .table-danger { background-color: #f8d7da !important; }
            .ui-autocomplete {
                z-index: 9999 !important;
                background: #fff;
                border: 1px solid #ddd;
                max-height: 250px;
                overflow-y: auto;
                overflow-x: hidden;
            }
            .stats-container {
                display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;
                padding: 15px 20px;
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                border-radius: 10px; border: 1px solid #dee2e6;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }
            .stat-item {
                display: flex; align-items: center; gap: 8px;
                background: white; padding: 5px 12px 5px 8px;
                border-radius: 25px; box-shadow: 0 1px 3px rgba(0,0,0,0.08);
                transition: all 0.3s ease;
            }
            .stat-item:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.15); }
            .stat-item .label { font-weight: 600; color: #495057; font-size: 13px; white-space: nowrap; }
            .stat-item .badge { font-size: 13px; padding: 6px 14px; border-radius: 20px; font-weight: 500; white-space: nowrap; }
            .badge-primary { background-color: #0d6efd; color: white; }
            .badge-success { background-color: #198754; color: white; }
            .badge-info    { background-color: #0dcaf0; color: #000; }
            .badge-warning { background-color: #ffc107; color: #000; }
            .badge-danger  { background-color: #dc3545; color: white; }
            .badge-secondary { background-color: #6c757d; color: white; }
            .badge-dark    { background-color: #212529; color: white; }
            .table-responsive { overflow-x: auto; }
            .table th { background-color: #f8f9fa; font-weight: 600; vertical-align: middle; }
            .table td { vertical-align: middle; }
            .text-right { text-align: right; }
            .text-center { text-align: center; }
            .btn-group-sm .btn { padding: 4px 8px; font-size: 12px; }
            .btn-group-sm .btn i { font-size: 12px; }
            .collapse-table { margin-top: 10px; background: #f8f9fa; border-radius: 5px; }
            .collapse-table table { margin-bottom: 0; }
            .alert-success {
                background-color: #d4edda; border-color: #c3e6cb; color: #155724;
                padding: 12px 20px; border-radius: 5px; margin-bottom: 20px;
                border-left: 4px solid #28a745;
            }

            @media (max-width: 768px) {
                .stats-container { flex-direction: column; gap: 8px; padding: 12px 15px; }
                .stat-item { width: 100%; justify-content: space-between; }
                .stat-item .badge { font-size: 12px; padding: 4px 10px; }
                .btn-group-sm .btn { padding: 2px 6px; font-size: 10px; }
            }

            @media print {
                .stats-container, .search-form, .btn, .pagination { display: none !important; }
            }
        </style>

        <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </x-slot>

    @php
        // ================= বাংলা কনভার্টার হেল্পার =================
        $bn = function($num, $decimals = 2) {
            $num = is_numeric($num) ? (float) $num : 0;
            $formatted = number_format($num, $decimals);
            $en = ['0','1','2','3','4','5','6','7','8','9','.',','];
            $bnChars = ['০','১','২','৩','৪','৫','৬','৭','৮','৯','.','،'];
            return str_replace($en, $bnChars, $formatted);
        };

        $bnPhone = function($num) {
            if (empty($num)) return '';
            $en = ['0','1','2','3','4','5','6','7','8','9'];
            $bnChars = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
            return str_replace($en, $bnChars, (string) $num);
        };

        $bnDate = function($date) {
            if (empty($date)) return '';
            $formatted = \Carbon\Carbon::parse($date)->format('d-m-Y');
            $en = ['0','1','2','3','4','5','6','7','8','9'];
            $bnChars = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
            return str_replace($en, $bnChars, $formatted);
        };

        $bnInv = function($invoice) {
            if (empty($invoice)) return '';
            $invoice = str_replace(['INV-', 'inv-', 'Inv-', 'INV', 'inv', 'Inv'], '', $invoice);
            $invoice = trim($invoice);
            if (substr($invoice, 0, 1) === '0') {
                $invoice = '1' . $invoice;
            }
            $en = ['0','1','2','3','4','5','6','7','8','9'];
            $bnChars = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
            return str_replace($en, $bnChars, (string) $invoice);
        };
    @endphp

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <div class="stats-container">
                    <div class="stat-item">
                        <a href="{{ route('sale.createform') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> বিক্রয়
                        </a>
                    </div>
                    
                    <div class="stat-item">
                        <span class="label">
                            <i class="fas fa-users" style="color: #0dcaf0;"></i> মোট ক্রেতা:</span>
                        <span class="badge badge-info">
                            {{ $bn($totalCustomers ?? 0, 0) }}
                        </span>
                    </div>
                    
                    <div class="stat-item">
                        <span class="label">
                            <i class="fas fa-shopping-cart" style="color: #198754;"></i> 
                            মোট বিক্রয়:
                        </span>
                        <span class="badge badge-success">
                            {{ $bn($totalSalesCount ?? 0, 0) }}
                        </span>
                    </div>
                    
                    <div class="stat-item">
                        <span class="label">
                            <i class="fas fa-calendar-day" style="color: #0d6efd;"></i> 
                            আজ:
                        </span>
                        <span class="badge badge-primary">
                            {{ $bn($todaySalesCount ?? 0, 0) }} 
                            ({{ $bn($todaySalesAmount ?? 0, 2) }})
                        </span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px; margin-top: 3px; padding-left: 5px;">
                        <span style="font-size: 12px; color: #6c757d;">
                            <i class="fas fa-boxes"></i> আইটেম:
                        </span>
                        <span class="badge" style="background-color: #0d6efd; color: white; font-size: 12px;">
                            {{ $bn($todaySaleItems ?? 0, 0) }}
                        </span>
                    </div>
                    
                    {{-- <div class="stat-item">
                        <span class="label">
                            <i class="fas fa-calendar-alt" style="color: #ffc107;"></i> মাস:</span>
                        <span class="badge badge-warning">
                            {{ $bn($monthlySalesCount ?? 0, 0) }} 
                            ({{ $bn($monthlySalesAmount ?? 0, 2) }})
                        </span>
                    </div> --}}
                    
                    <div class="stat-item">
                        <span class="label">
                            <i class="fas fa-money-bill-wave" style="color: #dc3545;"></i> 
                            বাকি:
                        </span>
                        <span class="badge badge-danger">
                            {{ $bn($totalDue ?? 0, 2) }}
                        </span>
                    </div>
                </div>
                <form method="GET" action="{{ route('sale.index') }}" class="mb-3 search-form">
                    <div class="row g-2">
                        <div class="col-md-2 col-sm-6">
                            <input type="text" name="invoice" class="form-control form-control-sm" 
                                   placeholder="চালান নম্বর" value="{{ request('invoice') }}">
                        </div>
                    
                        <div class="col-md-2 col-sm-6">
                            <input type="text" name="customer" id="customer-autocomplete" class="form-control form-control-sm" placeholder="ক্রেতার নাম বা মোবাইল" value="{{ request('customer') }}" autocomplete="off">
                            <input type="hidden" name="customer_id" id="customer-id" value="{{ request('customer_id') }}">
                        </div>
                        
                        <div class="col-md-2 col-sm-6">
                            <input type="text" name="product" id="product-autocomplete" 
                                   class="form-control form-control-sm" 
                                   placeholder="পণ্যের নাম" value="{{ request('product') }}" 
                                   autocomplete="off">
                            <input type="hidden" name="product_id" id="product-id" 
                                   value="{{ request('product_id') }}">
                        </div>
                        
                        <div class="col-md-2 col-sm-6">
                            <input type="date" name="from_date" class="form-control form-control-sm" 
                                   value="{{ request('from_date') }}" placeholder="শুরুর তারিখ">
                        </div>
                        
                        <div class="col-md-2 col-sm-6">
                            <input type="date" name="to_date" class="form-control form-control-sm" 
                                   value="{{ request('to_date') }}" placeholder="শেষ তারিখ">
                        </div>
                        
                        <div class="col-md-2 col-sm-6">
                            <div class="d-flex gap-1">
                                <button class="btn btn-primary btn-sm w-100" type="submit">
                                    <i class="fas fa-search"></i> অনুসন্ধান
                                </button>
                                <a href="{{ route('sale.index') }}" class="btn btn-secondary btn-sm" title="রিসেট">
                                    <i class="fas fa-undo"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>

                {{-- সেলস টেবিল --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover">
                        <thead class="text-center">
                            <tr>
                                <th style="width: 5%;">ক্রমিক</th>
                                <th style="width: 12%;">চালান নম্বর</th>
                                <th style="width: 10%;">তারিখ</th>
                                <th style="width: 15%;">ক্রেতা</th>
                                <th style="width: 12%;">মোট টাকা</th>
                                <th style="width: 12%;">পরিশোধিত</th>
                                <th style="width: 15%;">বাকি</th>
                                <th style="width: 19%;">অ্যাকশন</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($sales as $key => $sale)
                                @php
                                    $paidFromSale = $sale->paid_amount ?? 0;
                                    $paidFromReceived = $sale->receivedPayments?->sum('amount') ?? 0;
                                    $totalPaid = $paidFromSale + $paidFromReceived;
                                    $due = max(0, $sale->total_amount - $totalPaid);
                                    $rowClass = $due > 150 ? 'table-danger' : '';
                                @endphp

                                <tr class="{{ $rowClass }}">
                                    <td>{{ $bn($sales->firstItem() + $key, 0) }}</td>

                                    <td class="text-center" style="cursor: pointer;">
                                        <a href="#" data-bs-toggle="collapse" 
                                           data-bs-target="#details-{{ $sale->id }}"
                                           title="বিস্তারিত দেখতে ক্লিক করুন">
                                            <strong>{{ $bnInv($sale->invoice_no) }}</strong>
                                            <i class="fas fa-chevron-down" style="font-size: 10px;"></i>
                                        </a>
                                        
                                        <div class="collapse mt-2 collapse-table" 
                                             id="details-{{ $sale->id }}">
                                            <table class="table table-bordered table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>পণ্য</th>
                                                        <th>পরিমাণ</th>
                                                        <th>একক</th>
                                                        <th>দর</th>
                                                        <th>মোট</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($sale->details as $item)
                                                        <tr>
                                                            <td>{{ $item->product->product_name ?? '' }}</td>
                                                            <td>{{ $bn($item->qty, 2) }}</td>
                                                            <td>{{ $item->unit }}</td>
                                                            <td style="text-align: right;">
                                                                {{ $bn($item->price, 2) }}
                                                            </td>
                                                            <td style="text-align: right;">
                                                                {{ $bn($item->total, 2) }}
                                                            </td>
                                                        </tr>
                                                    @endforeach

                                                    @foreach($sale->oldProductPurchases as $purchase)
                                                        @foreach($purchase->details as $i)
                                                            <tr>
                                                                <td>{{ $i->oldProduct->product_name ?? '' }}</td>
                                                                <td>{{ $bn($i->qty, 2) }}</td>
                                                                <td>{{ $i->unit }}</td>
                                                                <td style="text-align: right;">
                                                                    {{ $bn($i->price, 2) }}
                                                                </td>
                                                                <td style="text-align: right;">
                                                                    {{ $bn($i->qty * $i->price, 2) }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    @endforeach

                                                    @if($sale->details->isEmpty() && $sale->oldProductPurchases->isEmpty())
                                                        <tr>
                                                            <td colspan="5" class="text-center text-muted">
                                                                কোনো পণ্য পাওয়া যায়নি
                                                            </td>
                                                        </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>

                                    <td class="text-center">
                                        {{ $bnDate($sale->sale_date) }}
                                    </td>

                                    <td class="text-center">
                                        <strong>{{ $sale->customer->customer_name ?? 'N/A' }}</strong>
                                        @if($sale->customer && $sale->customer->mobile)
                                            <br><small class="text-muted">{{ $bnPhone($sale->customer->mobile) }}</small>
                                        @endif
                                    </td>

                                    <td class="text-right">
                                        <strong>{{ $bn($sale->total_amount, 2) }}</strong>
                                    </td>

                                    <td class="text-right">
                                        {{ $bn($totalPaid, 2) }}
                                    </td>

                                    <td class="text-right">
                                        <strong style="color: {{ $due > 0 ? '#dc3545' : '#28a745' }}">
                                            {{ $bn($due, 2) }}
                                        </strong>
                                        
                                        @if(isset($customerDueTotals[$sale->customer_id]))
                                            <div style="font-size: 11px; border-top: 1px dashed #ccc; margin-top: 5px; padding-top: 3px;">
                                                <strong>মোট বাকি:</strong> 
                                                <span style="color: {{ $customerDueTotals[$sale->customer_id] > 0 ? '#dc3545' : '#28a745' }}">
                                                    {{ $bn($customerDueTotals[$sale->customer_id], 2) }}
                                                </span>
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('sale.view', $sale->id) }}" class="btn btn-info btn-sm" title="দেখুন">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            <a href="{{ route('sale.edit', $sale->id) }}" class="btn btn-warning btn-sm" title="সম্পাদনা">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <a href="{{ route('sale.downloadPdf', $sale->id) }}" 
                                               class="btn btn-primary btn-sm" title="পিডিএফ ডাউনলোড" target="_blank">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>

                                            <a href="{{ route('sale.delete', $sale->id) }}" class="btn btn-danger btn-sm" title="মুছুন" onclick="return confirm('আপনি কি নিশ্চিত এই বিক্রয়টি মুছে ফেলতে চান?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>

                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-info-circle fa-2x mb-2 d-block"></i> কোনো বিক্রয় পাওয়া যায়নি। 
                                        <a href="{{ route('sale.createform') }}">নতুন বিক্রয় তৈরি করুন</a>
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>

                        @if($sales->count() > 0)
                            <tfoot>
                                <tr style="background-color: #f8d7da !important; font-weight: bold;">
                                    <td colspan="6" class="text-end">
                                        <strong>মোট বাকি:</strong>
                                    </td>
                                    <td class="text-right" style="color: #dc3545;">
                                        <strong>{{ $bn($totalDue, 2) }}</strong>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        @endif

                    </table>
                </div>

                {{-- বাটন এবং পেজিনেশন --}}
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="history.back()">
                            <i class="fas fa-arrow-left"></i> ফিরে যান
                        </button>
                        
                        <a href="{{ route('sale.createform') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> নতুন বিক্রয়
                        </a>
                    </div>
                    
                    <div class="d-flex gap-2 align-items-center">
                        <span class="badge bg-secondary">
                            <i class="fas fa-list"></i> 
                            দেখানো হচ্ছে {{ $bn($sales->firstItem() ?? 0, 0) }} থেকে {{ $bn($sales->lastItem() ?? 0, 0) }} 
                            মোট {{ $bn($sales->total(), 0) }} টির মধ্যে
                        </span>
                    </div>
                </div>

                <div class="mt-4">
                    {{ $sales->links() }}
                </div>

            </div>
        </div>

        <br>
        <span>
            @include('layouts.footer')
        </span>
    </div>
    
    @stack('scripts')

    <script>
        $(document).ready(function () {

            /* PRODUCT AUTOCOMPLETE */
            $('#product-autocomplete').autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: "{{ route('product.autocomplete') }}",
                        dataType: 'json',
                        data: { term: request.term },
                        success: function (data) {
                            response($.map(data, function (item) {
                                return {
                                    label: item.label + 
                                        " (স্টক: " + item.stock + 
                                        ", দর: " + item.purchase_price + ")",
                                    value: item.label,
                                    id: item.id
                                };
                            }));
                        },
                        error: function() { response([]); }
                    });
                },
                minLength: 1,
                select: function (event, ui) {
                    $('#product-id').val(ui.item.id);
                    $('#product-autocomplete').val(ui.item.label);
                    return false;
                }
            });

            /* CUSTOMER AUTOCOMPLETE */
            $('#customer-autocomplete').autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: "{{ route('customer.autocomplete') }}",
                        dataType: 'json',
                        data: { term: request.term },
                        success: function (data) {
                            response($.map(data, function (item) {
                                return {
                                    label: item.label + 
                                        (item.mobile ? " (" + item.mobile + ")" : ""),
                                    value: item.value,
                                    id: item.id
                                };
                            }));
                        },
                        error: function() { response([]); }
                    });
                },
                minLength: 1,
                select: function (event, ui) {
                    $('#customer-id').val(ui.item.id);
                    $('#customer-autocomplete').val(ui.item.value);
                    return false;
                }
            });

            /* Enter কী প্রেস করলে সার্চ */
            $('.form-control').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $(this).closest('form').submit();
                }
            });
            

            /* ডেট পিকার ক্লিয়ার */
            $('input[type="date"]').on('click', function() {
                if (this.value) {
                    this.showPicker();
                }
            });

            console.log('বিক্রয় পেজ সফলভাবে লোড হয়েছে!');
        });
    </script>

</x-app-layout>