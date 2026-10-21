<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profit & Loss Report') }}
        </h2>

        <style>
            @page { margin: 40px; }

            body {
                font-family: 'SolaimanLipi', sans-serif;
                font-size: 14px;
                color: #333;
            }

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

            .filter-form {
                margin-bottom: 20px;
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
                align-items: center;
            }

            .filter-form input, .filter-form button {
                padding: 6px 10px;
                border: 1px solid #ccc;
                border-radius: 4px;
            }

            .filter-form button {
                background: #3b82f6;
                color: white;
                cursor: pointer;
            }

            .summary-box {
                background: #f9fafb;
                border: 1px solid #ddd;
                padding: 10px;
                margin-bottom: 20px;
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

            .total-row {
                font-weight: bold;
                background: #eef2ff;
            }
        </style>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- FILTER --}}
            <form action="{{ route('reports.profit_loss') }}" method="GET" class="filter-form">
                <label>Start Date:</label>
                <input type="date" name="start_date"
                    value="{{ $startDate->format('Y-m-d') }}">

                <label>End Date:</label>
                <input type="date" name="end_date"
                    value="{{ $endDate->format('Y-m-d') }}">

                <button type="submit">Generate</button>
                <a href="{{ route('reports.profit_loss_pdf', request()->all()) }}" 
   class="btn btn-danger">
   Download PDF
</a>
            </form>

            {{-- REPORT --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4">

                <h2 class="text-lg font-bold mb-3">
                    লাভ/ক্ষতির রিপোর্ট :
                    {{ $startDate->format('d M, Y') }}
                    @if($startDate != $endDate)
                        → {{ $endDate->format('d M, Y') }}
                    @endif
                </h2>

                {{-- SUMMARY --}}
                <div class="summary-box">
                    <strong>Total Sale:</strong> {{ number_format($totalSale, 2) }} |
                    <strong>Total Cost:</strong> {{ number_format($totalCost, 2) }} |
                    <strong>Expenses:</strong> {{ number_format($expenses, 2) }} |
                    <strong>Transport:</strong> {{ number_format($transport, 2) }} |
                    <strong>Net Profit:</strong>
                    <span style="color: {{ $netProfit >= 0 ? 'green' : 'red' }}">
                        {{ number_format($netProfit, 2) }}
                    </span>
                </div>

                {{-- NEW PRODUCTS --}}
                <h3 class="font-bold mb-2">নতুন পণ্য</h3>

                <table>
                    <thead>
                        <tr>
                            <th>পণ্য</th>
                            <th>পরিমাণ</th>
                            <th>ক্রয় মূল্য</th>
                            <th>মোট ক্রয়</th>
                            <th>বিক্রয় মূল্য</th>
                            <th>মোট বিক্রয়</th>
                            <th>লাভ/ক্ষতি</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php
                            $newTotalQty = 0;
                            $newTotalCost = 0;
                            $newTotalSale = 0;
                        @endphp

                        @forelse($newProducts as $product)

                            @php
                                $profit = ($product->total_sale ?? 0) - ($product->total_cost ?? 0);

                                $newTotalQty += $product->total_qty ?? 0;
                                $newTotalCost += $product->total_cost ?? 0;
                                $newTotalSale += $product->total_sale ?? 0;
                            @endphp

                            <tr>
                                <td>{{ $product->product_name }}</td>
                                <td>{{ $product->total_qty }}</td>
                                <td>{{ number_format($product->purchase_price ?? 0, 2) }}</td>
                                <td>{{ number_format($product->total_cost ?? 0, 2) }}</td>
                                <td>{{ number_format($product->sale_price ?? 0, 2) }}</td>
                                <td>{{ number_format($product->total_sale ?? 0, 2) }}</td>
                                <td style="color: {{ $profit >= 0 ? 'green' : 'red' }}">
                                    {{ number_format($profit, 2) }}
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="7">কোনো ডাটা নেই</td>
                            </tr>
                        @endforelse

                        {{-- TOTAL ROW --}}
                        <tr class="total-row">
                            <td>Total</td>
                            <td>{{ $newTotalQty }}</td>
                            <td>-</td>
                            <td>{{ number_format($newTotalCost, 2) }}</td>
                            <td>-</td>
                            <td>{{ number_format($newTotalSale, 2) }}</td>
                            <td style="color: {{ ($newTotalSale - $newTotalCost) >= 0 ? 'green' : 'red' }}">
                                {{ number_format($newTotalSale - $newTotalCost, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>

                {{-- OLD PRODUCTS --}}
                <h3 class="font-bold mb-2">পুরাতন পণ্য</h3>

                <table>
    <thead>
        <tr>
            <th>পণ্য</th>
            <th>পরিমাণ</th>
            <th>ক্রয় মূল্য</th>
            <th>মোট ক্রয়</th>
            <th>বিক্রয় মূল্য</th>
            <th>মোট বিক্রয়</th>
            <th>লাভ/ক্ষতি</th>
        </tr>
    </thead>

    <tbody>

        @php
            $oldTotalQty = 0;
            $oldTotalCost = 0;
            $oldTotalSale = 0;
        @endphp

        @forelse($oldProducts as $product)

            @php
                $profit = ($product->total_sale ?? 0) - ($product->total_cost ?? 0);

                $oldTotalQty += $product->total_qty ?? 0;
                $oldTotalCost += $product->total_cost ?? 0;
                $oldTotalSale += $product->total_sale ?? 0;
            @endphp

            <tr>
                <td>{{ $product->product_name }}</td>
                <td>{{ $product->total_qty }}</td>
                <td>{{ number_format($product->purchase_price ?? 0, 2) }}</td>
                <td>{{ number_format($product->total_cost ?? 0, 2) }}</td>
                <td>{{ number_format($product->sale_price ?? 0, 2) }}</td>
                <td>{{ number_format($product->total_sale ?? 0, 2) }}</td>
                <td style="color: {{ $profit >= 0 ? 'green' : 'red' }}">
                    {{ number_format($profit, 2) }}
                </td>
            </tr>

        @empty
            <tr>
                <td colspan="7">কোনো ডাটা নেই</td>
            </tr>
        @endforelse

        {{-- TOTAL ROW --}}
        <tr class="total-row">
            <td>Total</td>
            <td>{{ $oldTotalQty }}</td>
            <td>-</td>
            <td>{{ number_format($oldTotalCost, 2) }}</td>
            <td>-</td>
            <td>{{ number_format($oldTotalSale, 2) }}</td>
            <td style="color: {{ ($oldTotalSale - $oldTotalCost) >= 0 ? 'green' : 'red' }}">
                {{ number_format($oldTotalSale - $oldTotalCost, 2) }}
            </td>
        </tr>

    </tbody>
</table>

 {{-- EXPENSE TABLE --}}

@php
    $totalExpense = $expenses + $transport;

    // মোট লাভ/ক্ষতি (Expense বাদে)
    $grossProfit = $totalSale - $totalCost;

    // Net Profit/Loss (Gross - Expense)
    $netProfitFinal = $grossProfit - $totalExpense;
@endphp

<table>

    <tr>
        <td>দৈনন্দিন খরচ</td>
        <td>-{{ number_format($expenses, 2) }}</td>
    </tr>

    <tr>
        <td>ট্রান্সপোর্ট খরচ</td>
        <td>-{{ number_format($transport, 2) }}</td>
    </tr>

    {{-- TOTAL EXPENSE --}}
    <tr class="total-row">
        <th>মোট খরচ</th>
        <th style="color: red;">
            -{{ number_format($totalExpense, 2) }}
        </th>
    </tr>

    {{-- GROSS PROFIT --}}
    <tr>
        <td>মোট লাভ/ক্ষতি</td>
        <td style="color: {{ $grossProfit >= 0 ? 'green' : 'red' }}">
            {{ number_format($grossProfit, 2) }}
        </td>
    </tr>

    {{-- FINAL NET PROFIT --}}
    <tr class="total-row">
        <th>Net Profit / Loss</th>
        <th style="color: {{ $netProfitFinal >= 0 ? 'green' : 'red' }}">
            {{ number_format($netProfitFinal, 2) }}
        </th>
    </tr>

</table>

                {{-- SIGNATURE --}}
                <div class="signatures">
                    <div><p>Prepared By</p></div>
                    <div><p>Authorized By</p></div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>