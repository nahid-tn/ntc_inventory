<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Old Product Profit/Loss Report ') }}
        </h2>
    </x-slot>

    <div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <table class="table table-bordered">
                    <tr>
                        <th>Total Buy</th>
                        <td>{{ number_format($totalBuy,2) }} | Total Stock: {{ $currentStock ?? 0 }} KG</td>
                    </tr>
                    <tr>
                        <th>Total Sale</th>
                        <td>{{ number_format($totalSale,2) }}</td>
                    </tr>
                    <tr>
                        <th>Net Profit / Loss</th>
                        <td>
                            @if($totalProfit >= 0)
                                <span class="text-success">
                                    +{{ number_format($totalProfit,2) }}
                                </span>
                            @else
                                <span class="text-danger">
                                    {{ number_format($totalProfit,2) }}
                                </span>
                            @endif
                        </td>
                    </tr>
                </table>
                | <button type="button" class="btn btn-secondary" onclick="history.back()"> ← Back </button> |
            </div>
            
        </div>
       <br>
        <span> @include('layouts.footer')</span>
    </div>
    </div>
</x-app-layout>
