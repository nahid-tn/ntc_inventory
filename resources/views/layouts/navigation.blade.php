<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                   <a href="{{ route('dashboard') }}"><img src="{{ asset('images/logo.png') }}" alt="NTC Logo" class="h-10 w-auto"></a>
                </div>
                <!-- Navigation Links -->
                
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    
                    <div class="dropdown mt-3 btn-group-toggle">
                        <button class="btn btn-warning dropdown-toggle"
                                type="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false">
                                📝 Inventory
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="{{ route('product.index') }}">
                                🏷️ Products
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('admin.index') }}">
                                📚 Category
                                </a>
                            </li>
                            <!-- <li>
                                <a class="dropdown-item" href="{{ route('brand.index') }}">
                                   🎯 Brand
                                </a>
                            </li> -->
                            <li>
                                <a class="dropdown-item" href="{{ route('stock.report') }}">
                                📊 Warehouse</a>
                            </li>
                            
                        </ul>
                    </div>
                </div>

                
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    
                <div class="dropdown mt-3">
                        <button class="btn btn-warning dropdown-toggle"
                                type="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false">
                           🚚 Purchase
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('supplier.index') }}"> 🏭 Supplier</a></li>
                            <li><a class="dropdown-item" href="{{ route('purchase.index') }}"> ☰ Purchase List</a></li>
                            <li><a class="dropdown-item" href="{{ route('supplier.dues') }}"> ♻️ Purchase Dues </a></li>
                            <li><a class="dropdown-item" href="{{ route('purchase-return.index') }}"> ♻️ Purchase Return </a></li>
                            <li><a class="dropdown-item" href="{{ route('reports.supplier_payment') }}"> ♻️ Supplier Payment Report </a></li>
                            <li><a class="dropdown-item" href="{{ route('supplier.ledger',1) }}">📒 Supplier Ledger</a></li>
                            <li><a class="dropdown-item" href="{{ route('purchase.dashboard') }}"> 🧮 Dashboard</a></li>
                            <!-- Purchase Order -->
                            <li class="nav-item"> <a class="nav-link" href="{{ route('purchase-orders.index') }}">📦 Purchase Orders</a></li>
                        </ul>
                    </div>

                    <div class="dropdown mt-3">
                        <button class="btn btn-warning dropdown-toggle"
                                type="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false">
                           🚴 Sales
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('customer.index') }}">🙋🏻‍♂️ Customer </a></li>
                            <li><a class="dropdown-item" href="{{ route('sale.index') }}">🌿 Sale Details </a></li>
                             <li><a class="dropdown-item" href="{{ route('replacement.index') }}">♻️ Sale Replacement</a></li>
                            <li><a class="dropdown-item" href="{{ route('oldsale.old_index') }}">🛍️ Old Product Index</a></li>
                            <li><a class="dropdown-item" href="{{ route('oldsale.index') }}">🛍️ Old Product Sale</a></li>
                            <li><a class="btn btn-info" href="{{ route('oldsale.profitlos_report') }}" >🚴 Profit / Loss Report</a></li>
                            <li><a class="dropdown-item" href="{{ route('ips.form') }}">⚡IPS Calculator</a></li>
                        </ul>
                    </div>
                        <div class="dropdown mt-3">
                            <button class="btn btn-warning dropdown-toggle"
                                    type="button"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                🌿 Accounts
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('product.index') }}">🏷️ Products</a></li>
                                <li> <a class="dropdown-item" href="{{ route('reports.due_reports') }}"> 📊 Customer Due Report</a> </li>
                                <li><a class="dropdown-item" href="{{ route('reports.supplier_payment') }}">📊 Supplier Due Report</a></li>
                                <li><a class="dropdown-item" href="{{ route('supplier.payment.index') }}">☰ Supplier Payments List</a></li>
                                <li><a class="dropdown-item" href="{{ route('expense.index') }}"> ℹ Expense Details</a></li>
                                <li><a class="dropdown-item" href="{{ route('expense.reportform') }}">ℹ Search Report Expense</a></li>
                            </ul>
                        </div>

                    <!-- <div class="dropdown mt-3">
                        <button class="btn btn-warning dropdown-toggle"
                                type="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false">
                           🚴 Expense
                        </button>
                        <ul class="dropdown-menu">
                            
                             <li>
                                <a class="dropdown-item" href="{{ route('expense.reportform') }}">
                                   ℹ Search Report Expense
                                </a>
                            </li>
                        </ul>
                    </div> -->
                    <div class="dropdown mt-3">
                        <button class="btn btn-warning dropdown-toggle"
                                type="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false">
                           🚴 Reports
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('report.dashboard') }}"> Dashboard</a></li>
                            <li><a class="dropdown-item" href="{{ route('sales.report') }}"> Sales Report</a></li>
                            <li><a class="dropdown-item" href="{{ route('purchase.stock') }}"> Purchase Stock</a></li>
                            <!-- <li><a class="dropdown-item" href="{{ route('ProfitLos.report') }}"> 📊 Profit Loss Report</a></li> -->
                            <li><a class="dropdown-item" href="{{ route('purchase-return.reportform') }}">📊 Purchase Return Report</a></li>
                            <li><a class="dropdown-item" href="{{ route('reports.index') }}"> 📊 Profit or Loss Report</a></li>
                            <li><a class="dropdown-item" href="{{ route('customerReport.customer_all') }}"> 📊 Customer Report</a></li>
                           <li><a class="dropdown-item" href="{{ route('reports.profit_loss') }}">📊 Daily Profit Report</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <!-- <div class="ms-1">
                                <img src="{{ asset('images/logo.png') }}" alt="NIC" class="h-10 w-auto">
                            </div> -->
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
