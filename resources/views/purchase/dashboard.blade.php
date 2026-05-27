<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Purchase Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <h2>Purchase Dashboard</h2>
                <div class="row">
                    <div class="col">Total Purchase: {{ number_format($totalPurchase,2) }}</div>
                    <div class="col text-danger">Total Due: {{ number_format($totalDue,2) }}</div>
                </div>

                <div class="row mt-2">
                    <div class="col">Today Purchase: {{ $todayPurchase }}</div>
                    <div class="col text-danger">Today Due: {{ $todayDue }}</div>
                </div>

                <div class="row mt-2">
                    <div class="col">Monthly Purchase: {{ $monthlyPurchase }}</div>
                    <div class="col text-danger">Monthly Due: {{ $monthlyDue }}</div>
                </div>

                <hr>
                <h4>Supplier Wise Due</h4>
                <table class="table table-bordered">
                    <tr>
                        <th>Supplier</th>
                        <th>Due</th>
                    </tr>
                    @foreach($supplierDue as $row)
                    <tr>
                        <td>{{ $row->supplier->supplier_name ?? 'N/A' }}</td>
                        <td class="text-danger">{{ $row->total_due }}</td>
                    </tr>
                    @endforeach
                </table>
            </div>
        </div>
        <br>
        <span> @include('layouts.footer')</span>
    </div>
    </div>
</x-app-layout>
