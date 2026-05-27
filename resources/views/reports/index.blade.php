<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Sales List') }}
        </h2>
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

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #999;
        }
        th, td {
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }

        /* Totals */
        .totals {
            width: 40%;
            float: right;
            margin-top: 10px;
        }
        .totals table {
            border: none;
        }
        .totals th, .totals td {
            border: none;
            text-align: right;
            padding: 5px 10px;
        }

        /* Signature */
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
    </style>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                
            <form method="GET" class="row mb-3">
                <div class="col-md-3">
                    <input type="date" name="start_date" class="form-control" value="{{ $start }}">
                </div>
                <div class="col-md-3">
                    <input type="date" name="end_date" class="form-control" value="{{ $end }}">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary">Filter</button>
                </div>
            </form>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Purchase Qty</th>
                            <th>Purchase Amount</th>
                            <th>Sale Qty</th>
                            <th>Sale Amount</th>
                            <th>Gross Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report as $key=>$item)
                        <tr>
                            <td>{{ $key+1 }}</td>
                            <td>{{ $item['product_name'] }}</td>
                            <td>{{ $item['purchase_qty'] }}</td>
                            <td>{{ number_format($item['purchase_amount'],2) }}</td>
                            <td>{{ $item['sale_qty'] }}</td>
                            <td>{{ number_format($item['sale_amount'],2) }}</td>
                            <td class="{{ $item['gross_profit'] >= 0 ? 'text-success':'text-danger' }}">
                                {{ number_format($item['gross_profit'],2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <hr>

                <h5>Summary ({{ $start }} to {{ $end }})</h5>

                <ul>
                    <li>Total Purchase: {{ number_format($totalPurchase,2) }}</li>
                    <li>Total Sale: {{ number_format($totalSale,2) }}</li>
                    <li>Total Gross Profit: {{ number_format($totalGrossProfit,2) }}</li>
                    <li>Total Expense: {{ number_format($totalExpense,2) }}</li>
                    <li>
                        Net Profit:
                        <span class="{{ $netProfit >= 0 ? 'text-success':'text-danger' }}">
                            {{ number_format($netProfit,2) }}
                        </span>
                    </li>
                </ul>

            </div>
        </div>
        <br>
        <span> @include('layouts.footer')</span>
    </div>
</x-app-layout>
