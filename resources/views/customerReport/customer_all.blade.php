<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Customer List') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h2>All Customers Sales Report</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Customer</th>
                <th>New Products Total</th>
                <th>New Paid</th>
                <th>New Due</th>
                <th>Old Products Total</th>
                <th>Old Paid</th>
                <th>Old Due</th>
                <th>Total Sales</th>
                <th>Total Paid</th>
                <th>Total Due</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report as $r)
            <tr>
                <td>{{ $r['customer_name'] }} ({{ $r['mobile'] }})</td>
                <td>{{ number_format($r['total_new_sales'],2) }}</td>
                <td>{{ number_format($r['total_new_paid'],2) }}</td>
                <td>{{ number_format($r['total_new_due'],2) }}</td>
                <td>{{ number_format($r['total_old_sales'],2) }}</td>
                <td>{{ number_format($r['total_old_paid'],2) }}</td>
                <td>{{ number_format($r['total_old_due'],2) }}</td>
                <td>{{ number_format($r['total_sales'],2) }}</td>
                <td>{{ number_format($r['total_paid'],2) }}</td>
                <td>{{ number_format($r['total_due'],2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

                    
            </div>
        </div>
        <br>
        <span> @include('layouts.footer')</span>
    </div>
    </div>
</x-app-layout>
