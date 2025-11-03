<aside id="sidebar"
    class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform -translate-x-full
           bg-gradient-to-b from-primary-600 to-primary-500 border-r border-gray-200 sm:translate-x-0"
    aria-label="Sidebar">
    <div class="h-full px-3 pb-4 overflow-y-auto">
        <ul class="space-y-2 font-medium">
            <x-sidebar-link :href="route('dashboard')" icon="fa-solid fa-gauge-high" :active="request()->routeIs('dashboard')">
                Dashboard
            </x-sidebar-link>

            <x-sidebar-link :href="route('akun-kas.index')" icon="fa-solid fa-wallet" :active="request()->routeIs('akun-kas.*')">
                Akun Kas
            </x-sidebar-link>

            <x-sidebar-link :href="route('kategori-kas.index')" icon="fa-solid fa-layer-group" :active="request()->routeIs('kategori-kas.*')">
                Kategori Kas
            </x-sidebar-link>

            <x-sidebar-link :href="route('customer.index')" icon="fa-solid fa-users" :active="request()->routeIs('customer.*')">
                Customer
            </x-sidebar-link>

            <x-sidebar-link :href="route('kas.index')" icon="fa-solid fa-money-bill-transfer" :active="request()->routeIs('kas.*')">
                Arus Kas
            </x-sidebar-link>

            <x-sidebar-link :href="route('stok.index')" icon="fa-solid fa-boxes-stacked" :active="request()->routeIs('stok.*')">
                Stok
            </x-sidebar-link>

            <x-sidebar-link :href="route('supplier.index')" icon="fa-solid fa-id-card" :active="request()->routeIs('supplier.*')">
                Supplier
            </x-sidebar-link>

            <x-sidebar-link :href="route('penjualan.index')" icon="fa-solid fa-cart-shopping" :active="request()->routeIs('penjualan.*')">
                Penjualan
            </x-sidebar-link>

            <x-sidebar-link :href="route('pembelian.index')" icon="fa-solid fa-dolly" :active="request()->routeIs('pembelian.*')">
                Pembelian
            </x-sidebar-link>

            <x-sidebar-link :href="route('laporan.index', ['type' => 'jual'])" icon="fa-solid fa-file-arrow-up" :active="request()->fullUrlIs(route('laporan.index', ['type' => 'jual']))">
                Laporan Penjualan
            </x-sidebar-link>

            <x-sidebar-link :href="route('laporan.index', ['type' => 'beli'])" icon="fa-solid fa-file-arrow-down" :active="request()->fullUrlIs(route('laporan.index', ['type' => 'beli']))">
                Laporan Pembelian
            </x-sidebar-link>
        </ul>
    </div>
</aside>
