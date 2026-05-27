<aside class="main-sidebar sidebar-dark-warning elevation-4">

    <!-- Logo -->
    <a href="{{ route('dashboard') }}" class="brand-link text-center">
        <img src="{{ asset('images/logo.png') }}"
             class="brand-image img-circle elevation-3">
        <span class="brand-text font-weight-bold">NTC</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">

        <!-- User Panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ asset('images/user.png') }}"
                     class="img-circle elevation-2">
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ Auth::user()->name }}</a>
            </div>
        </div>

        <!-- Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column"
                data-widget="treeview"
                role="menu">

                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link">
                        <i class="nav-icon fas fa-home"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- Product -->
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-box"></i>
                        <p>
                            Product
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.index') }}" class="nav-link">
                                <p>Category</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('brand.index') }}" class="nav-link">
                                <p>Brand</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('product.index') }}" class="nav-link">
                                <p>Products</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('stock.report') }}" class="nav-link">
                                <p>Stock Report</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Partner -->
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fa-users"></i>
                        <p>
                            Partner
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('customer.index') }}" class="nav-link">
                                <p>Customer</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('supplier.index') }}" class="nav-link">
                                <p>Supplier</p>
                            </a>
                        </li>
                    </ul>
                </li>


                <!-- Sale -->
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fas fa-cart-plus"></i>
                        <p>
                            PURCHASE
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('purchase.dashboard') }}" class="nav-link">
                                <p>PURCHASES DASHBOARD</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('purchase.index') }}" class="nav-link">
                                <p>PURCHASES LIST</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('supplier.dues') }}" class="nav-link">
                                <p>PURCHASES DUES</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Sale -->
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fas fa-balance-scale"></i>
                        <p>
                            SALES
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('sale.index') }}" class="nav-link">
                                <p>SALE DETAILS</p>
                            </a>
                        </li>
                        
                    </ul>
                </li>

                <!-- OLD PRODUCTS -->
                <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fas fa-balance-scale"></i>
                        <p>
                            OLD PRODUCT
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('oldsale.index') }}" class="nav-link">
                                <p>OLD PRODUCT LIST</p>
                            </a>
                        </li>
                        
                    </ul>
                </li>

                <!-- Expense -->
                 <li class="nav-item has-treeview">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas fas fa-money-bill"></i>
                        <p>
                            EXPENNSE
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('expense.index') }}" class="nav-link">
                                <p>EXPENNSE LIST</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('expense.reportform') }}" class="nav-link">
                                <p>EXPENNSE REPORTS</p>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="{{ route('expense.index') }}" class="nav-link">
                        <i class="nav-icon fas fa-money-bill"></i>
                        <p>Expense</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('expense.reportform') }}" class="nav-link">
                        <i class="nav-icon fas fa-money-bill"></i>
                        <p>EXPENNSE REPORTS</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
