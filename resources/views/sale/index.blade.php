<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Sales List') }}
        </h2>

        <style>
            @page {
                margin: 50px 50px;
            }

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

            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }

            table,
            th,
            td {
                border: 1px solid #999;
            }

            th,
            td {
                padding: 8px;
                text-align: center;
            }

            th {
                background-color: #f2f2f2;
            }

            .totals {
                width: 40%;
                float: right;
                margin-top: 10px;
            }

            .totals table {
                border: none;
            }

            .totals th,
            .totals td {
                border: none;
                text-align: right;
                padding: 5px 10px;
            }

            .signatures {
                display: flex;
                justify-content: space-between;
                margin-top: 60px;
            }

            .signatures div {
                width: 45%;
                text-align: center;
            }

            .signatures div p {
                margin-top: 60px;
                border-top: 1px solid #333;
            }

            .table-danger {
                background-color: #f8d7da !important;
            }

            .ui-autocomplete {
                z-index: 9999 !important;
                background: #fff;
                border: 1px solid #ddd;
                max-height: 250px;
                overflow-y: auto;
                overflow-x: hidden;
            }
        </style>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">

                <a href="{{ route('sale.createform') }}"
                   class="btn btn-primary d-inline-block mb-3">
                    Create Sale
                </a>

                {{-- SEARCH FORM --}}
                <form method="GET"
                      action="{{ route('sale.index') }}"
                      class="mb-3">

                    <div class="row g-2">

                        {{-- Invoice --}}
                        <div class="col-md-2">
                            <input
                                type="text"
                                name="invoice"
                                class="form-control"
                                placeholder="Invoice No"
                                value="{{ request('invoice') }}"
                            >
                        </div>

                        {{-- Customer Autocomplete --}}
                        <div class="col-md-2">
                            <input
                                type="text"
                                name="customer"
                                id="customer-autocomplete"
                                class="form-control"
                                placeholder="Customer Name Or Mobile"
                                value="{{ request('customer') }}"
                                autocomplete="off"
                            >

                            <input
                                type="hidden"
                                name="customer_id"
                                id="customer-id"
                                value="{{ request('customer_id') }}"
                            >
                        </div>

                        {{-- Product Autocomplete --}}
                        <div class="col-md-2">
                            <input
                                type="text"
                                name="product"
                                id="product-autocomplete"
                                class="form-control"
                                placeholder="Product Name"
                                value="{{ request('product') }}"
                                autocomplete="off"
                            >

                            <input
                                type="hidden"
                                name="product_id"
                                id="product-id"
                                value="{{ request('product_id') }}"
                            >
                        </div>

                        {{-- From Date --}}
                        <div class="col-md-2">
                            <input
                                type="date"
                                name="from_date"
                                class="form-control"
                                value="{{ request('from_date') }}"
                            >
                        </div>

                        {{-- To Date --}}
                        <div class="col-md-2">
                            <input
                                type="date"
                                name="to_date"
                                class="form-control"
                                value="{{ request('to_date') }}"
                            >
                        </div>

                        {{-- Search --}}
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100">
                                Search
                            </button>
                        </div>

                    </div>
                </form>

                {{-- SALES TABLE --}}
                <table class="table table-bordered table-striped">
                    <thead class="text-center">
                        <tr>
                            <th>#</th>
                            <th>Invoice No</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Total Amount</th>
                            <th>Paid</th>
                            <th>Due</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($sales as $key => $sale)

                            @php
                                $paidFromSale = $sale->paid_amount ?? 0;
                                $paidFromReceived = $sale->receivedPayments?->sum('amount') ?? 0;
                                $totalPaid = $paidFromSale + $paidFromReceived;
                                $due = $sale->total_amount - $totalPaid;

                                $rowClass = $due > 150 ? 'table-danger' : '';
                            @endphp

                            <tr class="{{ $rowClass }}">

                                <td>{{ $key + 1 }}</td>

                                <td class="text-center"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#details-{{ $sale->id }}">

                                    <a href="#">
                                        {{ $sale->invoice_no }}
                                    </a>

                                    <div class="collapse mt-2"
                                         id="details-{{ $sale->id }}">

                                        <table class="table table-bordered mt-2">
                                            <tbody>

                                                {{-- New Products --}}
                                                @foreach($sale->details as $item)
                                                    <tr>
                                                        <td>{{ $item->product->product_name ?? '' }}</td>
                                                        <td>{{ $item->qty }}</td>
                                                        <td>{{ $item->unit }}</td>
                                                        <td style="text-align: right;">
                                                            {{ number_format($item->price, 2) }}
                                                        </td>
                                                        <td style="text-align: right;">
                                                            {{ number_format($item->total, 2) }}
                                                        </td>
                                                    </tr>
                                                @endforeach

                                                {{-- Old Products --}}
                                                @foreach($sale->oldProductPurchases as $purchase)
                                                    @foreach($purchase->details as $i)
                                                        <tr>
                                                            <td>{{ $i->oldProduct->product_name ?? '' }}</td>
                                                            <td>{{ $i->qty }}</td>
                                                            <td>{{ $i->unit }}</td>
                                                            <td style="text-align: right;">
                                                                {{ number_format($i->price, 2) }}
                                                            </td>
                                                            <td style="text-align: right;">
                                                                {{ number_format($i->qty * $i->price, 2) }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endforeach

                                            </tbody>
                                        </table>

                                    </div>
                                </td>

                                <td class="text-center">
                                    {{ $sale->sale_date->format('Y-m-d') }}
                                </td>

                                <td class="text-center">
                                    {{ $sale->customer->customer_name ?? '' }}
                                </td>

                                <td class="text-right">
                                    {{ number_format($sale->total_amount, 2) }}
                                </td>

                                <td class="text-right">
                                    {{ number_format($totalPaid, 2) }}
                                </td>

                                <td class="text-right">
                                    {{ number_format($due, 2) }}
                                </td>

                                <td>
                                    <a href="{{ route('sale.view', $sale->id) }}"
                                       class="btn btn-info btn-sm">
                                        View
                                    </a>

                                    <a href="{{ route('sale.edit', $sale->id) }}"
                                       class="btn btn-warning btn-sm">
                                        Edit
                                    </a>

                                    <a href="{{ route('sale.pdf', $sale->id) }}"
                                       class="btn btn-success btn-sm"
                                       target="_blank">
                                        PDF
                                    </a>

                                    <a href="{{ route('sale.delete', $sale->id) }}"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Are you sure to delete?')">
                                        Delete
                                    </a>
                                </td>

                            </tr>

                        @endforeach

                    </tbody>
                </table>

                <button
                    type="button"
                    class="btn btn-secondary"
                    onclick="history.back()">
                    ← Back
                </button>

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

    {{-- jQuery --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    {{-- jQuery UI --}}
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <link rel="stylesheet"
          href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

    <script>
        $(document).ready(function () {

            /*
            =====================================
            PRODUCT AUTOCOMPLETE
            =====================================
            */
            $('#product-autocomplete').autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: "{{ route('product.autocomplete') }}",
                        dataType: 'json',
                        data: {
                            term: request.term
                        },
                        success: function (data) {
                            response($.map(data, function (item) {
                                return {
                                    label: item.label +
                                        " (Stock: " + item.stock +
                                        ", Price: " + item.purchase_price + ")",

                                    value: item.label,
                                    id: item.id
                                };
                            }));
                        }
                    });
                },

                minLength: 1,

                select: function (event, ui) {
                    $('#product-id').val(ui.item.id);
                }
            });


            /*
            =====================================
            CUSTOMER AUTOCOMPLETE
            =====================================
            */
            $('#customer-autocomplete').autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: "{{ route('customer.autocomplete') }}",
                        dataType: 'json',
                        data: {
                            term: request.term
                        },
                        success: function (data) {
                            response($.map(data, function (item) {
                                return {
                                    label: item.label +
                                        (item.mobile ? " (" + item.mobile + ")" : ""),

                                    value: item.value,
                                    id: item.id
                                };
                            }));
                        }
                    });
                },

                minLength: 1,

                select: function (event, ui) {
                    $('#customer-id').val(ui.item.id);
                    $('#customer-autocomplete').val(ui.item.value);
                    return false;
                }
            });
        });
    </script>

</x-app-layout>