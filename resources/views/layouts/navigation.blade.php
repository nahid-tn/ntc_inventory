
<nav
    x-data="{
        open: false,
        activeMenu: null,
        userOpen: false,
        mobileMenus: {},

        toggleMenu(name) {
            this.activeMenu = this.activeMenu === name ? null : name;
            this.userOpen = false;
        },

        closeMenus() {
            this.open = false;
            this.activeMenu = null;
            this.userOpen = false;
            this.mobileMenus = {};
        }
    }"
    @keydown.escape.window="closeMenus()"
    @resize.window="if (window.innerWidth >= 768) open = false"
    class="ntc-navbar"
>
    {{-- =====================================
         Main Navigation Container
         Logo | Desktop Menu | User | Hamburger
    ====================================== --}}
    <div class="ntc-nav-container">

        {{-- Logo: Left --}}
        <a href="{{ route('dashboard') }}" class="ntc-logo">
            <img
                src="{{ asset('images/logo.png') }}"
                alt="NTC Logo"
            >
        </a>

        {{-- =====================================
             Desktop Navigation: Center
        ====================================== --}}
        <div class="ntc-desktop-nav">

            {{-- Inventory --}}
            <div class="ntc-nav-item">
                <button
                    type="button"
                    class="ntc-nav-button"
                    :class="{ 'active': activeMenu === 'inventory' }"
                    @click="toggleMenu('inventory')"
                    :aria-expanded="activeMenu === 'inventory'"
                >
                    📝 Inventory
                    <span
                        class="ntc-chevron"
                        :class="{ 'rotated': activeMenu === 'inventory' }"
                    >⌄</span>
                </button>

                <div
                    class="ntc-dropdown"
                    x-cloak
                    x-show="activeMenu === 'inventory'"
                    x-transition.origin.top.left
                    @click.outside="activeMenu = null"
                >
                    <a class="ntc-dropdown-link" href="{{ route('product.index') }}">🏷️ Products</a>
                    <a class="ntc-dropdown-link" href="{{ route('admin.index') }}">📚 Category</a>
                    <a class="ntc-dropdown-link" href="{{ route('stock.report') }}">📊 Warehouse</a>
                </div>
            </div>

            {{-- Purchase --}}
            <div class="ntc-nav-item">
                <button
                    type="button"
                    class="ntc-nav-button"
                    :class="{ 'active': activeMenu === 'purchase' }"
                    @click="toggleMenu('purchase')"
                    :aria-expanded="activeMenu === 'purchase'"
                >
                    🚚 Purchase
                    <span
                        class="ntc-chevron"
                        :class="{ 'rotated': activeMenu === 'purchase' }"
                    >⌄</span>
                </button>

                <div
                    class="ntc-dropdown"
                    x-cloak
                    x-show="activeMenu === 'purchase'"
                    x-transition.origin.top.left
                    @click.outside="activeMenu = null"
                >
                    <a class="ntc-dropdown-link" href="{{ route('supplier.index') }}">🏭 Supplier</a>
                    <a class="ntc-dropdown-link" href="{{ route('purchase.index') }}">☰ Purchase List</a>
                    <a class="ntc-dropdown-link" href="{{ route('supplier.dues') }}">♻️ Purchase Dues</a>
                    <a class="ntc-dropdown-link" href="{{ route('purchase-return.index') }}">♻️ Purchase Return</a>
                    <a class="ntc-dropdown-link" href="{{ route('reports.supplier_payment') }}">📊 Supplier Payment Report</a>
                    <a class="ntc-dropdown-link" href="{{ route('supplier.ledger', 1) }}">📒 Supplier Ledger</a>
                    <a class="ntc-dropdown-link" href="{{ route('purchase.dashboard') }}">🧮 Purchase Dashboard</a>
                    <a class="ntc-dropdown-link" href="{{ route('purchase-orders.index') }}">📦 Purchase Orders</a>
                </div>
            </div>

            {{-- Sales --}}
            <div class="ntc-nav-item">
                <button
                    type="button"
                    class="ntc-nav-button"
                    :class="{ 'active': activeMenu === 'sales' }"
                    @click="toggleMenu('sales')"
                    :aria-expanded="activeMenu === 'sales'"
                >
                    🚴 Sales
                    <span
                        class="ntc-chevron"
                        :class="{ 'rotated': activeMenu === 'sales' }"
                    >⌄</span>
                </button>

                <div
                    class="ntc-dropdown"
                    x-cloak
                    x-show="activeMenu === 'sales'"
                    x-transition.origin.top.left
                    @click.outside="activeMenu = null"
                >
                    <a class="ntc-dropdown-link" href="{{ route('customer.index') }}">🙋 Customer</a>
                    <a class="ntc-dropdown-link" href="{{ route('sale.index') }}">🌿 Sale Details</a>
                    <a class="ntc-dropdown-link" href="{{ route('replacement.index') }}">♻️ Sale Replacement</a>
                    <a class="ntc-dropdown-link" href="{{ route('oldsale.old_index') }}">🛍️ Old Product Index</a>
                    <a class="ntc-dropdown-link" href="{{ route('oldsale.index') }}">🛍️ Old Product Sale</a>
                    <a class="ntc-dropdown-link" href="{{ route('old_product_exchange.index') }}">🔄 Old Product Exchange</a>
                    <a class="ntc-dropdown-link" href="{{ route('oldsale.profitlos_report') }}">📈 Profit / Loss Report</a>
                    <a class="ntc-dropdown-link" href="{{ route('ips.form') }}">⚡ IPS Calculator</a>
                </div>
            </div>

            {{-- Accounts --}}
            <div class="ntc-nav-item">
                <button
                    type="button"
                    class="ntc-nav-button"
                    :class="{ 'active': activeMenu === 'accounts' }"
                    @click="toggleMenu('accounts')"
                    :aria-expanded="activeMenu === 'accounts'"
                >
                    🌿 Accounts
                    <span
                        class="ntc-chevron"
                        :class="{ 'rotated': activeMenu === 'accounts' }"
                    >⌄</span>
                </button>

                <div
                    class="ntc-dropdown"
                    x-cloak
                    x-show="activeMenu === 'accounts'"
                    x-transition.origin.top.left
                    @click.outside="activeMenu = null"
                >
                    <a class="ntc-dropdown-link" href="{{ route('reports.due_reports') }}">📊 Customer Due Report</a>
                    <a class="ntc-dropdown-link" href="{{ route('reports.supplier_payment') }}">📊 Supplier Due Report</a>
                    <a class="ntc-dropdown-link" href="{{ route('supplier.payment.index') }}">☰ Supplier Payments List</a>
                    <a class="ntc-dropdown-link" href="{{ route('expense.index') }}">ℹ️ Expense Details</a>
                    <a class="ntc-dropdown-link" href="{{ route('expense.reportform') }}">🔎 Search Expense Report</a>
                </div>
            </div>

            {{-- Reports --}}
            <div class="ntc-nav-item">
                <button
                    type="button"
                    class="ntc-nav-button"
                    :class="{ 'active': activeMenu === 'reports' }"
                    @click="toggleMenu('reports')"
                    :aria-expanded="activeMenu === 'reports'"
                >
                    📊 Reports
                    <span
                        class="ntc-chevron"
                        :class="{ 'rotated': activeMenu === 'reports' }"
                    >⌄</span>
                </button>

                <div
                    class="ntc-dropdown"
                    x-cloak
                    x-show="activeMenu === 'reports'"
                    x-transition.origin.top.left
                    @click.outside="activeMenu = null"
                >
                    <a class="ntc-dropdown-link" href="{{ route('report.dashboard') }}">📊 Dashboard</a>
                    <a class="ntc-dropdown-link" href="{{ route('sales.report') }}">📈 Sales Report</a>
                    <a class="ntc-dropdown-link" href="{{ route('purchase.stock') }}">📦 Purchase Stock</a>
                    <a class="ntc-dropdown-link" href="{{ route('purchase-return.reportform') }}">📊 Purchase Return Report</a>
                    <a class="ntc-dropdown-link" href="{{ route('reports.index') }}">📈 Profit or Loss Report</a>
                    <a class="ntc-dropdown-link" href="{{ route('customerReport.customer_all') }}">👥 Customer Report</a>
                    <a class="ntc-dropdown-link" href="{{ route('reports.profit_loss') }}">📊 Daily Profit Report</a>
                </div>
            </div>

        </div>

        {{-- =====================================
             Desktop User Menu: Right
        ====================================== --}}
        <div class="ntc-user-desktop">
            <div class="ntc-user-menu">

                <button
                    type="button"
                    class="ntc-user-button"
                    @click="userOpen = !userOpen; activeMenu = null"
                    :aria-expanded="userOpen"
                    aria-label="User menu"
                >
                    <span class="ntc-avatar">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                    </span>

                    <span class="ntc-user-name">
                        {{ Auth::user()->name ?? 'User' }}
                    </span>

                    <span
                        class="ntc-chevron"
                        :class="{ 'rotated': userOpen }"
                    >⌄</span>
                </button>

                <div
                    class="ntc-dropdown ntc-user-dropdown"
                    x-cloak
                    x-show="userOpen"
                    x-transition.origin.top.right
                    @click.outside="userOpen = false"
                >
                    <div class="ntc-user-info">
                        <strong>{{ Auth::user()->name ?? 'User' }}</strong>
                        <small>{{ Auth::user()->email ?? '' }}</small>
                    </div>

                    <a
                        class="ntc-dropdown-link"
                        href="{{ route('profile.edit') }}"
                    >
                        👤 Profile
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="ntc-dropdown-link ntc-logout-button"
                        >
                            🚪 Log Out
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- =====================================
             Mobile Hamburger: Right
        ====================================== --}}
        <button
            type="button"
            class="ntc-hamburger"
            @click="open = !open; activeMenu = null; userOpen = false"
            :aria-expanded="open"
            aria-label="Toggle navigation menu"
        >
            <svg
                x-show="!open"
                x-cloak
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                aria-hidden="true"
            >
                <path
                    d="M4 6h16M4 12h16M4 18h16"
                    stroke-width="2"
                    stroke-linecap="round"
                />
            </svg>

            <svg
                x-show="open"
                x-cloak
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                aria-hidden="true"
            >
                <path
                    d="M6 6l12 12M18 6L6 18"
                    stroke-width="2"
                    stroke-linecap="round"
                />
            </svg>
        </button>

    </div>

    {{-- =====================================
         Mobile Navigation
    ====================================== --}}
    <div
        class="ntc-mobile-nav"
        x-cloak
        x-show="open"
        x-transition
    >
        <a
            class="ntc-mobile-link"
            href="{{ route('dashboard') }}"
        >
            🏠 Dashboard
        </a>

        {{-- Mobile Inventory --}}
        <div class="ntc-mobile-group">
            <button
                type="button"
                class="ntc-mobile-heading"
                @click="mobileMenus.inventory = !mobileMenus.inventory"
                :aria-expanded="!!mobileMenus.inventory"
            >
                <span>📝 Inventory</span>
                <span
                    class="ntc-chevron"
                    :class="{ 'rotated': mobileMenus.inventory }"
                >⌄</span>
            </button>

            <div
                class="ntc-mobile-submenu"
                x-cloak
                x-show="mobileMenus.inventory"
                x-transition
            >
                <a href="{{ route('product.index') }}">🏷️ Products</a>
                <a href="{{ route('admin.index') }}">📚 Category</a>
                <a href="{{ route('stock.report') }}">📊 Warehouse</a>
            </div>
        </div>

        {{-- Mobile Purchase --}}
        <div class="ntc-mobile-group">
            <button
                type="button"
                class="ntc-mobile-heading"
                @click="mobileMenus.purchase = !mobileMenus.purchase"
                :aria-expanded="!!mobileMenus.purchase"
            >
                <span>🚚 Purchase</span>
                <span
                    class="ntc-chevron"
                    :class="{ 'rotated': mobileMenus.purchase }"
                >⌄</span>
            </button>

            <div
                class="ntc-mobile-submenu"
                x-cloak
                x-show="mobileMenus.purchase"
                x-transition
            >
                <a href="{{ route('supplier.index') }}">🏭 Supplier</a>
                <a href="{{ route('purchase.index') }}">☰ Purchase List</a>
                <a href="{{ route('supplier.dues') }}">♻️ Purchase Dues</a>
                <a href="{{ route('purchase-return.index') }}">♻️ Purchase Return</a>
                <a href="{{ route('reports.supplier_payment') }}">📊 Supplier Payment Report</a>
                <a href="{{ route('supplier.ledger', 1) }}">📒 Supplier Ledger</a>
                <a href="{{ route('purchase.dashboard') }}">🧮 Purchase Dashboard</a>
                <a href="{{ route('purchase-orders.index') }}">📦 Purchase Orders</a>
            </div>
        </div>

        {{-- Mobile Sales --}}
        <div class="ntc-mobile-group">
            <button
                type="button"
                class="ntc-mobile-heading"
                @click="mobileMenus.sales = !mobileMenus.sales"
                :aria-expanded="!!mobileMenus.sales"
            >
                <span>🚴 Sales</span>
                <span
                    class="ntc-chevron"
                    :class="{ 'rotated': mobileMenus.sales }"
                >⌄</span>
            </button>

            <div
                class="ntc-mobile-submenu"
                x-cloak
                x-show="mobileMenus.sales"
                x-transition
            >
                <a href="{{ route('customer.index') }}">🙋 Customer</a>
                <a href="{{ route('sale.index') }}">🌿 Sale Details</a>
                <a href="{{ route('replacement.index') }}">♻️ Sale Replacement</a>
                <a href="{{ route('oldsale.old_index') }}">🛍️ Old Product Index</a>
                <a href="{{ route('oldsale.index') }}">🛍️ Old Product Sale</a>
                <a href="{{ route('old_product_exchange.index') }}">🔄 Old Product Exchange</a>
                <a href="{{ route('oldsale.profitlos_report') }}">📈 Profit / Loss Report</a>
                <a href="{{ route('ips.form') }}">⚡ IPS Calculator</a>
            </div>
        </div>

        {{-- Mobile Accounts --}}
        <div class="ntc-mobile-group">
            <button
                type="button"
                class="ntc-mobile-heading"
                @click="mobileMenus.accounts = !mobileMenus.accounts"
                :aria-expanded="!!mobileMenus.accounts"
            >
                <span>🌿 Accounts</span>
                <span
                    class="ntc-chevron"
                    :class="{ 'rotated': mobileMenus.accounts }"
                >⌄</span>
            </button>

            <div
                class="ntc-mobile-submenu"
                x-cloak
                x-show="mobileMenus.accounts"
                x-transition
            >
                <a href="{{ route('reports.due_reports') }}">📊 Customer Due Report</a>
                <a href="{{ route('reports.supplier_payment') }}">📊 Supplier Due Report</a>
                <a href="{{ route('supplier.payment.index') }}">☰ Supplier Payments List</a>
                <a href="{{ route('expense.index') }}">ℹ️ Expense Details</a>
                <a href="{{ route('expense.reportform') }}">🔎 Search Expense Report</a>
            </div>
        </div>

        {{-- Mobile Reports --}}
        <div class="ntc-mobile-group">
            <button
                type="button"
                class="ntc-mobile-heading"
                @click="mobileMenus.reports = !mobileMenus.reports"
                :aria-expanded="!!mobileMenus.reports"
            >
                <span>📊 Reports</span>
                <span
                    class="ntc-chevron"
                    :class="{ 'rotated': mobileMenus.reports }"
                >⌄</span>
            </button>

            <div
                class="ntc-mobile-submenu"
                x-cloak
                x-show="mobileMenus.reports"
                x-transition
            >
                <a href="{{ route('report.dashboard') }}">📊 Dashboard</a>
                <a href="{{ route('sales.report') }}">📈 Sales Report</a>
                <a href="{{ route('purchase.stock') }}">📦 Purchase Stock</a>
                <a href="{{ route('purchase-return.reportform') }}">📊 Purchase Return Report</a>
                <a href="{{ route('reports.index') }}">📈 Profit or Loss Report</a>
                <a href="{{ route('customerReport.customer_all') }}">👥 Customer Report</a>
                <a href="{{ route('reports.profit_loss') }}">📊 Daily Profit Report</a>
            </div>
        </div>

        {{-- =====================================
             Mobile User: Name, Email, Profile, Logout
        ====================================== --}}
        <div class="ntc-mobile-user">

            <div class="ntc-mobile-user-info">
                <strong>
                    {{ Auth::user()?->name ?? 'নাম পাওয়া যায়নি' }}
                </strong>

                <small>
                    {{ Auth::user()?->email ?? '' }}
                </small>
            </div>

            <a
                class="ntc-mobile-link"
                href="{{ route('profile.edit') }}"
            >
                👤 Profile
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button
                    type="submit"
                    class="ntc-mobile-link ntc-mobile-logout"
                >
                    🚪 Log Out
                </button>
            </form>

        </div>

    </div>
</nav>
