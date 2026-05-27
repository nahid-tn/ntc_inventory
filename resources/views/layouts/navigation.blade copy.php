<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <div class="flex">

                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <img src="{{ asset('images/logo.png') }}" alt="NTC Logo" class="h-10 w-auto">
                    </a>
                </div>

                <!-- Menus -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">

                    <!-- Inventory -->
                    <div class="dropdown mt-3">
                        <button class="btn btn-warning dropdown-toggle" data-bs-toggle="dropdown">
                            📝 Inventory
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('product.index') }}">🏷️ Products</a></li>
                            <li><a class="dropdown-item" href="{{ route('admin.index') }}">📚 Category</a></li>
                            <li><a class="dropdown-item" href="{{ route('stock.report') }}">📊 Warehouse</a></li>
                        </ul>
                    </div>

                    <!-- Purchase -->
                    <div class="dropdown mt-3">
                        <button class="btn btn-warning dropdown-toggle" data-bs-toggle="dropdown">
                            🚚 Purchase
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('supplier.index') }}">🏭 Supplier</a></li>
                            <li><a class="dropdown-item" href="{{ route('purchase.index') }}">☰ Purchase List</a></li>
                            <li><a class="dropdown-item" href="{{ route('supplier.dues') }}">♻️ Purchase Dues</a></li>
                            <li><a class="dropdown-item" href="{{ route('purchase-return.index') }}">♻️ Purchase Return</a></li>
                        </ul>
                    </div>

                    <!-- Sales -->
                    <div class="dropdown mt-3">
                        <button class="btn btn-warning dropdown-toggle" data-bs-toggle="dropdown">
                            🚴 Sales
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('customer.index') }}">🙋🏻‍♂️ Customer</a></li>
                            <li><a class="dropdown-item" href="{{ route('sale.index') }}">🌿 Sale Details</a></li>
                        </ul>
                    </div>

                    <!-- Accounts -->
                    <div class="dropdown mt-3">
                        <button class="btn btn-warning dropdown-toggle" data-bs-toggle="dropdown">
                            🌿 Accounts
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('expense.index') }}">ℹ Expense</a></li>
                            <li><a class="dropdown-item" href="{{ route('reports.due_reports') }}">📊 Due Report</a></li>
                        </ul>
                    </div>

                    <!-- Reports -->
                    <div class="dropdown mt-3">
                        <button class="btn btn-warning dropdown-toggle" data-bs-toggle="dropdown">
                            📊 Reports
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('report.dashboard') }}">Dashboard</a></li>
                            <li><a class="dropdown-item" href="{{ route('sales.report') }}">Sales Report</a></li>

                            {{-- 🔥 IPS Calculator Added --}}
                            <li>
                                <a class="dropdown-item" href="{{ route('ips.form') }}">
                                    ⚡ IPS Calculator
                                </a>
                            </li>

                        </ul>
                    </div>

                    <!-- 🔋 Power Tools (NEW MENU) -->
                    <div class="dropdown mt-3">
                        <button class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                            🔋 Power Tools
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('ips.form') }}">
                                    ⚡ IPS Calculator
                                </a>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>

            <!-- User Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 text-sm rounded-md text-gray-500 dark:text-gray-400">
                            {{ Auth::user()->name }}
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            Profile
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                Log Out
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

        </div>
    </div>

</nav>