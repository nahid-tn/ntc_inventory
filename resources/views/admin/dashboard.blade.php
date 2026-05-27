<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            📊 {{ __('Admin Dashboard') }} 
        </h2>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h2 class="mb-4">📊 Dashboard</h2>

                    <!-- Top Cards -->
                    <div class="row mb-4">
                        <!-- Total Purchase -->
                        <div class="col-md-3">
                            <div class="card text-white bg-info mb-3">
                                <div class="card-body text-center">
                                    <h5>Total Purchase</h5>
                                    <h2>{{ number_format($totalPurchase, 2) }}</h2>
                                </div>
                            </div>
                        </div>

                        <!-- Total Profit / Loss -->
                        <div class="col-md-3">
                            <div class="card mb-3 {{ $totalProfit < 0 ? 'bg-danger text-white' : 'bg-success text-white' }}">
                                <div class="card-body text-center">
                                    <h5>Total Stock</h5>
                                    <h2>{{ number_format(abs($totalProfit), 2) }}</h2>
                                </div>
                            </div>
                        </div>

                        <!-- Total Sale -->
                        <div class="col-md-3">
                            <div class="card text-white bg-primary mb-3">
                                <div class="card-body text-center">
                                    <h5>Total Sale</h5>
                                    <h2>{{ number_format($totalSale, 2) }}</h2>
                                </div>
                            </div>
                        </div>

                        <!-- Low Stock -->
                        <div class="col-md-3">
                            <div class="card text-dark bg-warning mb-3">
                                <div class="card-body text-center">
                                    <h5>Low Stock (&lt;2)</h5>
                                    <h2>{{ $lowStock ?? 0 }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Expense Cards -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card mb-3 {{ $expenseToday < 0 ? 'bg-danger text-white' : 'bg-secondary text-white' }}">
                                <div class="card-body text-center">
                                    <h5>Expense Today</h5>
                                    <h2>{{ $expenseToday < 0 ? '-' : '' }}{{ number_format(abs($expenseToday), 2) }}</h2>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card mb-3 {{ $expenseMonth < 0 ? 'bg-danger text-white' : 'bg-warning text-dark' }}">
                                <div class="card-body text-center">
                                    <h5>Expense Running Month</h5>
                                    <h2>{{ $expenseMonth < 0 ? '-' : '' }}{{ number_format(abs($expenseMonth), 2) }}</h2>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card mb-3 {{ $expenseTotal < 0 ? 'bg-danger text-white' : 'bg-light text-dark' }}">
                                <div class="card-body text-center">
                                    <h5>Total Expense</h5>
                                    <h2>{{ $expenseTotal < 0 ? '-' : '' }}{{ number_format(abs($expenseTotal), 2) }}</h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header">Monthly Total Purchase</div>
                                <div class="card-body">
                                    <canvas id="purchaseChart"></canvas>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header">Monthly Total Sale</div>
                                <div class="card-body">
                                    <canvas id="saleChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header">Monthly Total Expense</div>
                                <div class="card-body">
                                    <canvas id="expenseChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <br>
        <span>@include('layouts.footer')</span>
    </div>

    <script>
        // Sale Chart
        new Chart(document.getElementById('saleChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode($saleLabels) !!},
                datasets: [{
                    label: 'Total Sale',
                    data: {!! json_encode($saleData) !!},
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });

        // Expense Chart
        new Chart(document.getElementById('expenseChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode($expenseLabels) !!},
                datasets: [{
                    label: 'Total Expense',
                    data: {!! json_encode($expenseData) !!},
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });

        // Purchase Chart
        new Chart(document.getElementById('purchaseChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode($purchaseLabels) !!},
                datasets: [{
                    label: 'Total Purchase',
                    data: {!! json_encode($purchaseData) !!},
                    borderColor: 'rgba(255, 206, 86, 1)',
                    backgroundColor: 'rgba(255, 206, 86, 0.2)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { responsive: true, scales: { y: { beginAtZero: true } } }
        });
    </script>
</x-app-layout>