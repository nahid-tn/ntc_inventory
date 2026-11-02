<x-app-layout>

    {{-- Dashboard Header --}}
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                    <i class="fa-solid fa-gauge-high mr-2 text-blue-600"></i>
                    Dashboard
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Business overview and sales summary
                </p>
            </div>

            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                <i class="fa-regular fa-calendar"></i>
                <span>{{ now()->format('d M, Y') }}</span>
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gray-50 py-8 dark:bg-gray-900">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Page Introduction --}}
            <div class="mb-6">
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">
                    Business Overview
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    আপনার ব্যবসার দৈনিক ও মাসিক বিক্রয় এবং পাওনার হিসাব।
                </p>
            </div>

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-5">

                {{-- Daily Sales --}}
                <div class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500 to-blue-700 p-5 text-white shadow-md transition duration-300 hover:-translate-y-1 hover:shadow-xl">
                    <div class="relative z-10 flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-blue-100">
                                Daily Sales
                            </p>
                            <h3 class="mt-3 whitespace-nowrap text-lg font-bold tracking-tight sm:text-xl 2xl:text-2xl">
                                ৳{{ number_format($dailySales, 2) }}
                            </h3>
                        </div>

                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/20">
                            <i class="fa-solid fa-cart-shopping text-xl"></i>
                        </div>
                    </div>

                    <div class="relative z-10 mt-5 border-t border-white/20 pt-3 text-sm text-blue-100">
                        <i class="fa-regular fa-calendar mr-1"></i>
                        Today Sales
                    </div>

                    <i class="fa-solid fa-chart-line pointer-events-none absolute -bottom-4 -right-2 text-7xl text-white/10"></i>
                </div>

                {{-- Daily Due --}}
                <div class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-orange-400 to-orange-600 p-5 text-white shadow-md transition duration-300 hover:-translate-y-1 hover:shadow-xl">
                    <div class="relative z-10 flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-orange-100">
                                Daily Due
                            </p>
                            <h3 class="mt-3 whitespace-nowrap text-lg font-bold tracking-tight sm:text-xl 2xl:text-2xl">
                                ৳{{ number_format($dailyDue, 2) }}
                            </h3>
                        </div>

                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/20">
                            <i class="fa-solid fa-clock text-xl"></i>
                        </div>
                    </div>

                    <div class="relative z-10 mt-5 border-t border-white/20 pt-3 text-sm text-orange-100">
                        <i class="fa-solid fa-hourglass-half mr-1"></i>
                        Today Due
                    </div>

                    <i class="fa-solid fa-wallet pointer-events-none absolute -bottom-4 -right-2 text-7xl text-white/10"></i>
                </div>

                {{-- Monthly Sales --}}
                <div class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 p-5 text-white shadow-md transition duration-300 hover:-translate-y-1 hover:shadow-xl">
                    <div class="relative z-10 flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-emerald-100">
                                Monthly Sales
                            </p>
                            <h3 class="mt-3 whitespace-nowrap text-lg font-bold tracking-tight sm:text-xl 2xl:text-2xl">
                                ৳{{ number_format($monthlySalesTotal, 2) }}
                            </h3>
                        </div>

                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/20">
                            <i class="fa-solid fa-chart-column text-xl"></i>
                        </div>
                    </div>

                    <div class="relative z-10 mt-5 border-t border-white/20 pt-3 text-sm text-emerald-100">
                        <i class="fa-solid fa-calendar-check mr-1"></i>
                        This Month
                    </div>

                    <i class="fa-solid fa-chart-area pointer-events-none absolute -bottom-4 -right-2 text-7xl text-white/10"></i>
                </div>

                {{-- Monthly Due --}}
                <div class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-rose-500 to-rose-700 p-5 text-white shadow-md transition duration-300 hover:-translate-y-1 hover:shadow-xl">
                    <div class="relative z-10 flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-rose-100">
                                Monthly Due
                            </p>
                            <h3 class="mt-3 whitespace-nowrap text-lg font-bold tracking-tight sm:text-xl 2xl:text-2xl">
                                ৳{{ number_format($monthlyDue, 2) }}
                            </h3>
                        </div>

                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/20">
                            <i class="fa-solid fa-file-invoice-dollar text-xl"></i>
                        </div>
                    </div>

                    <div class="relative z-10 mt-5 border-t border-white/20 pt-3 text-sm text-rose-100">
                        <i class="fa-solid fa-calendar-minus mr-1"></i>
                        This Month's Due
                    </div>

                    <i class="fa-solid fa-money-bill-wave pointer-events-none absolute -bottom-4 -right-2 text-7xl text-white/10"></i>
                </div>

                {{-- Monthly Received --}}
                <div class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-violet-500 to-violet-700 p-5 text-white shadow-md transition duration-300 hover:-translate-y-1 hover:shadow-xl">
                    <div class="relative z-10 flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-violet-100">
                                Monthly Received
                            </p>
                            <h3 class="mt-3 whitespace-nowrap text-lg font-bold tracking-tight sm:text-xl 2xl:text-2xl">
                                ৳{{ number_format($monthlyReceived, 2) }}
                            </h3>
                        </div>

                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/20">
                            <i class="fa-solid fa-hand-holding-dollar text-xl"></i>
                        </div>
                    </div>

                    <div class="relative z-10 mt-5 border-t border-white/20 pt-3 text-sm text-violet-100">
                        <i class="fa-solid fa-circle-check mr-1"></i>
                        Payments Received
                    </div>

                    <i class="fa-solid fa-coins pointer-events-none absolute -bottom-4 -right-2 text-7xl text-white/10"></i>
                </div>

            </div>

            {{-- Monthly Sales Chart --}}
            <div class="mt-8 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">

                <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-5 dark:border-gray-700 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white">
                            <i class="fa-solid fa-chart-column mr-2 text-blue-500"></i>
                            Monthly Sales Chart
                        </h3>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            মাস অনুযায়ী বিক্রয়ের পরিমাণ
                        </p>
                    </div>

                    <div class="inline-flex items-center gap-2 self-start rounded-lg bg-blue-50 px-3 py-2 text-sm font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                        <span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span>
                        Monthly Sales
                    </div>
                </div>

                <div class="p-4 sm:p-6">
                    <div class="relative h-72 w-full sm:h-80">
                        <canvas
                            id="salesChart"
                            role="img"
                            aria-label="Monthly sales chart"
                        ></canvas>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="mt-8">
                @include('layouts.footer')
            </div>

        </div>
    </div>

    {{-- Chart.js: Local Vite bundle, no CDN --}}
    <script>
        (function () {
            function initializeSalesChart() {
                const canvas = document.getElementById('salesChart');

                if (!canvas) {
                    return;
                }

                if (typeof window.Chart === 'undefined') {
                    console.error(
                        'Chart.js পাওয়া যায়নি। resources/js/app.js-এ Chart.js import করা আছে কি না দেখুন।'
                    );
                    return;
                }

                // একই চার্ট যেন একাধিকবার তৈরি না হয়
                if (canvas.chartInstance) {
                    canvas.chartInstance.destroy();
                }

                const labels = {{ Illuminate\Support\Js::from($monthlySalesChart->keys()->values()) }};
                const salesData = {{ Illuminate\Support\Js::from($monthlySalesChart->values()->values()) }};

                const isDarkMode = document.documentElement.classList.contains('dark');
                const textColor = isDarkMode ? '#d1d5db' : '#6b7280';
                const gridColor = isDarkMode
                    ? 'rgba(255,255,255,0.08)'
                    : 'rgba(107,114,128,0.12)';

                canvas.chartInstance = new window.Chart(canvas, {
                    type: 'bar',

                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Monthly Sales',
                            data: salesData,
                            backgroundColor: 'rgba(59, 130, 246, 0.80)',
                            hoverBackgroundColor: 'rgba(37, 99, 235, 0.95)',
                            borderColor: '#3b82f6',
                            borderWidth: 1,
                            borderRadius: 7,
                            borderSkipped: false,
                            maxBarThickness: 48
                        }]
                    },

                    options: {
                        responsive: true,
                        maintainAspectRatio: false,

                        plugins: {
                            legend: {
                                display: false
                            },

                            tooltip: {
                                backgroundColor: isDarkMode ? '#111827' : '#1f2937',
                                titleColor: '#ffffff',
                                bodyColor: '#ffffff',
                                padding: 12,
                                cornerRadius: 8,

                                callbacks: {
                                    label: function (context) {
                                        const value = Number(context.parsed.y || 0);

                                        return ' Sales: ৳' + value.toLocaleString('en-BD', {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        });
                                    }
                                }
                            }
                        },

                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },

                                ticks: {
                                    color: textColor,
                                    maxRotation: 45,
                                    minRotation: 0
                                },

                                border: {
                                    display: false
                                }
                            },

                            y: {
                                beginAtZero: true,

                                grid: {
                                    color: gridColor
                                },

                                ticks: {
                                    color: textColor,

                                    callback: function (value) {
                                        return '৳' + Number(value).toLocaleString('en-BD');
                                    }
                                },

                                border: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }

            // DOM প্রস্তুত থাকলে সঙ্গে সঙ্গে, নইলে DOMContentLoaded-এর পরে চার্ট তৈরি হবে
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializeSalesChart);
            } else {
                initializeSalesChart();
            }
        })();
    </script>

</x-app-layout>