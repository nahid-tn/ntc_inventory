<x-app-layout>
<x-slot name="header">
<h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
Dashboard
</h2>
</x-slot>

<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

    <!-- Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-primary dark:bg-gray-800 shadow  p-4 rounded">
                    <h5>Daily Sales</h5>
                    <h3>{{ number_format($dailySales,2) }}</h3>
                </div>
                <div class="bg-secondary dark:bg-gray-800 shadow  p-4 rounded">
                    <h5>Daily Due</h5>
                    <h3>{{ number_format($dailyDue,2) }}</h3>
                </div>
                <div class="bg-success dark:bg-gray-800 shadow  p-4 rounded">
                    <h5>Monthly Sales</h5>
                    <h3>{{ number_format($monthlySalesTotal,2) }}</h3>
                </div>
                <div class="bg-danger dark:bg-gray-800 shadow  p-4 rounded">
                    <h5>Monthly Due</h5>
                    <h3>{{ number_format($monthlyDue,2) }}</h3>
                </div>
                <div class="bg-info dark:bg-gray-800 shadow  p-4 rounded">
                    <h5>Monthly Received</h5>
                    <h3>{{ number_format($monthlyReceived,2) }}</h3>
                </div>
            </div>
        </div>
        <!-- Chart Section -->
        <div class="bg-white dark:bg-gray-800 shadow rounded p-6 mt-8">
            <h3 class="text-lg font-semibold mb-4 text-gray-700 dark:text-gray-200"> Monthly Sales Chart </h3>
            <canvas id="salesChart"></canvas>
        </div>
        
        <span> @include('layouts.footer')</span>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
var ctx = document.getElementById('salesChart');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($monthlySalesChart->keys()) !!},
        datasets: [{
            label: 'Monthly Sales',
            data: {!! json_encode($monthlySalesChart->values()) !!},
            backgroundColor: '#3b82f6'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            }
        }
    }
});
</script>

</x-app-layout>