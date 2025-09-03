<!DOCTYPE html>
<html lang="en" x-data="AppShell" x-init="init()" :class="themeClass">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KelzNet POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class', theme: { extend: { colors: { brand: '#5b3a29' } } } }
    </script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @media print {
            body * {
                visibility: hidden
            }

            #receipt,
            #receipt * {
                visibility: visible
            }

            #receipt {
                position: fixed;
                inset: 0;
                width: 148mm;
                height: 210mm;
                margin: 0 auto;
                padding: 12mm;
                background: white
            }

            .no-print {
                display: none !important
            }
        }

        ::-webkit-scrollbar {
            height: 8px;
            width: 8px
        }

        ::-webkit-scrollbar-thumb {
            background-color: #a3a3a3;
            border-radius: 8px
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 min-h-screen"
    x-on:keydown.window="handleKeys($event)">
    <div
        class="sticky top-0 z-40 bg-white/80 dark:bg-gray-900/80 backdrop-blur border-b border-gray-200 dark:border-gray-800">
        <nav class="max-w-7xl mx-auto px-3 md:px-6">
            <div class="flex items-center justify-between h-14">
                <div class="flex items-center gap-3">
                    <div class="h-8 w-8 rounded bg-brand/10 flex items-center justify-center text-brand font-bold">K
                    </div>
                    <div class="hidden sm:flex items-center gap-1 text-sm">
                        <a href="#/dashboard" :class="navClass('#/dashboard')"
                            class="px-3 py-2 rounded-md flex items-center gap-2"><i data-lucide="gauge"
                                class="w-4 h-4"></i><span>Dashboard</span></a>
                        <a href="#/pos" :class="navClass('#/pos')"
                            class="px-3 py-2 rounded-md flex items-center gap-2"><i data-lucide="shopping-cart"
                                class="w-4 h-4"></i><span>POS</span></a>
                        <div x-data="Dropdown">
                            <button @click="toggle" :class="navClass('#/inventory')"
                                class="px-3 py-2 rounded-md flex items-center gap-2"><i data-lucide="package"
                                    class="w-4 h-4"></i><span>Inventory</span><i data-lucide="chevron-down"
                                    class="w-4 h-4"></i></button>
                            <div x-show="open" x-transition @click.outside="open=false"
                                class="absolute mt-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded shadow-lg w-56">
                                <a @click="open=false" href="#/inventory/products"
                                    class="block px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">Products</a>
                                <a @click="open=false" href="#/inventory/vouchers"
                                    class="block px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">Hotspot Vouchers</a>
                                <a @click="open=false" href="#/inventory/printing"
                                    class="block px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">Printing
                                    Services</a>
                                <a @click="open=false" href="#/inventory/adjustments"
                                    class="block px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">Stock
                                    Adjustments</a>
                            </div>
                        </div>
                        <div x-data="Dropdown">
                            <button @click="toggle" :class="navClass('#/sales')"
                                class="px-3 py-2 rounded-md flex items-center gap-2"><i data-lucide="receipt"
                                    class="w-4 h-4"></i><span>Sales</span><i data-lucide="chevron-down"
                                    class="w-4 h-4"></i></button>
                            <div x-show="open" x-transition @click.outside="open=false"
                                class="absolute mt-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded shadow-lg w-56">
                                <a @click="open=false" href="#/sales/today"
                                    class="block px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">Today</a>
                                <a @click="open=false" href="#/sales/history"
                                    class="block px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">History</a>
                                <a @click="open=false" href="#/sales/returns"
                                    class="block px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">Returns</a>
                            </div>
                        </div>
                        <div x-data="Dropdown">
                            <button @click="toggle" :class="navClass('#/customers')"
                                class="px-3 py-2 rounded-md flex items-center gap-2"><i data-lucide="users"
                                    class="w-4 h-4"></i><span>Customers</span><i data-lucide="chevron-down"
                                    class="w-4 h-4"></i></button>
                            <div x-show="open" x-transition @click.outside="open=false"
                                class="absolute mt-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded shadow-lg w-56">
                                <a @click="open=false" href="#/customers"
                                    class="block px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">List</a>
                                <a @click="open=false" href="#/customers/new"
                                    class="block px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">New</a>
                            </div>
                        </div>
                        <div x-data="Dropdown">
                            <button @click="toggle" :class="navClass('#/reports')"
                                class="px-3 py-2 rounded-md flex items-center gap-2"><i data-lucide="printer"
                                    class="w-4 h-4"></i><span>Reports</span><i data-lucide="chevron-down"
                                    class="w-4 h-4"></i></button>
                            <div x-show="open" x-transition @click.outside="open=false"
                                class="absolute mt-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded shadow-lg w-56">
                                <a @click="open=false" href="#/reports/overview"
                                    class="block px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">Overview</a>
                                <a @click="open=false" href="#/reports/top-items"
                                    class="block px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">Top Items</a>
                                <a @click="open=false" href="#/reports/low-stock"
                                    class="block px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">Low Stock</a>
                                <a @click="open=false" href="#/reports/revenue"
                                    class="block px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">Revenue by
                                    Day/Period</a>
                            </div>
                        </div>
                        <div x-data="Dropdown">
                            <button @click="toggle" :class="navClass('#/settings')"
                                class="px-3 py-2 rounded-md flex items-center gap-2"><i data-lucide="settings"
                                    class="w-4 h-4"></i><span>Settings</span><i data-lucide="chevron-down"
                                    class="w-4 h-4"></i></button>
                            <div x-show="open" x-transition @click.outside="open=false"
                                class="absolute mt-2 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded shadow-lg w-64">
                                <a @click="open=false" href="#/settings/profile"
                                    class="block px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">Business Profile</a>
                                <a @click="open=false" href="#/settings/tax"
                                    class="block px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">Taxes &
                                    Discounts</a>
                                <a @click="open=false" href="#/settings/hotspot"
                                    class="block px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">Hotspot Settings</a>
                                <a @click="open=false" href="#/settings/backup"
                                    class="block px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">Backup/Restore</a>
                                <a @click="open=false" href="#/settings/theme"
                                    class="block px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">Theme</a>
                                <a @click="open=false" href="#/settings/factory"
                                    class="block px-3 py-2 hover:bg-gray-50 dark:hover:bg-gray-800">Factory Reset</a>
                            </div>
                        </div>
                        <a href="#/help" :class="navClass('#/help')"
                            class="px-3 py-2 rounded-md flex items-center gap-2"><i data-lucide="help-circle"
                                class="w-4 h-4"></i><span>Help</span></a>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="toggleTheme()" class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800"
                        :title="themeTitle"><i :data-lucide="themeIcon" class="w-5 h-5"></i></button>
                    <button @click="$router.go('#/pos')"
                        class="hidden sm:inline-flex items-center gap-2 px-3 py-2 rounded bg-brand text-white hover:opacity-90"><i
                            data-lucide="shopping-cart" class="w-4 h-4"></i><span>New Sale</span></button>
                    <div class="sm:hidden" x-data="Dropdown">
                        <button @click="toggle" class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800"><i
                                data-lucide="menu" class="w-5 h-5"></i></button>
                        <div x-show="open" x-transition @click.outside="open=false"
                            class="absolute right-2 mt-2 w-64 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded shadow-lg">
                            <a @click="open=false" href="#/dashboard" class="block px-4 py-2">Dashboard</a>
                            <a @click="open=false" href="#/pos" class="block px-4 py-2">POS</a>
                            <a @click="open=false" href="#/inventory/products" class="block px-4 py-2">Products</a>
                            <a @click="open=false" href="#/inventory/vouchers" class="block px-4 py-2">Hotspot
                                Vouchers</a>
                            <a @click="open=false" href="#/customers" class="block px-4 py-2">Customers</a>
                            <a @click="open=false" href="#/sales/today" class="block px-4 py-2">Sales Today</a>
                            <a @click="open=false" href="#/settings/profile" class="block px-4 py-2">Settings</a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </div>

    <main class="max-w-7xl mx-auto p-3 md:p-6 space-y-6">
        <section x-show="$router.is('#/dashboard')" x-transition>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div class="rounded border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Today’s Sales</span><i data-lucide="gauge"
                            class="w-4 h-4 text-brand"></i>
                    </div>
                    <div class="text-2xl font-semibold mt-2" x-text="fmtCurrency($db.kpis.todayTotal)"></div>
                </div>
                <div class="rounded border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Receipts Today</span><i data-lucide="receipt"
                            class="w-4 h-4 text-brand"></i>
                    </div>
                    <div class="text-2xl font-semibold mt-2" x-text="$db.kpis.receiptsToday"></div>
                </div>
                <div class="rounded border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Items Sold</span><i data-lucide="shopping-bag"
                            class="w-4 h-4 text-brand"></i>
                    </div>
                    <div class="text-2xl font-semibold mt-2" x-text="$db.kpis.itemsSoldToday"></div>
                </div>
                <div class="rounded border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Vouchers Sold</span><i data-lucide="wifi"
                            class="w-4 h-4 text-brand"></i>
                    </div>
                    <div class="text-2xl font-semibold mt-2" x-text="$db.kpis.vouchersSoldToday"></div>
                </div>
                <div class="rounded border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-gray-900">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Low Stock</span><i data-lucide="alert-triangle"
                            class="w-4 h-4 text-brand"></i>
                    </div>
                    <div class="text-2xl font-semibold mt-2" x-text="$db.lowStock().length"></div>
                </div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div
                    class="rounded border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-gray-900 lg:col-span-2">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold">Trends</h3>
                        <div class="text-xs text-gray-500">Last 7 days</div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="text-gray-500">
                                <tr>
                                    <th class="text-left py-2">Date</th>
                                    <th class="text-right">Revenue</th>
                                    <th class="text-right">Vouchers</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="row in $db.last7Days()">
                                    <tr class="border-t border-gray-100 dark:border-gray-800">
                                        <td class="py-2" x-text="row.date"></td>
                                        <td class="text-right" x-text="fmtCurrency(row.revenue)"></td>
                                        <td class="text-right" x-text="row.vouchers"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="rounded border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-gray-900">
                    <h3 class="font-semibold mb-3">Quick Actions</h3>
                    <div class="grid grid-cols-2 gap-2">
                        <button @click="$router.go('#/pos')"
                            class="px-3 py-2 rounded bg-brand/10 text-brand flex items-center justify-center gap-2"><i
                                data-lucide="shopping-cart" class="w-4 h-4"></i><span>New Sale</span></button>
                        <button @click="$router.go('#/inventory/adjustments')"
                            class="px-3 py-2 rounded bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 flex items-center justify-center gap-2"><i
                                data-lucide="download" class="w-4 h-4"></i><span>Receive Stock</span></button>
                        <button @click="$router.go('#/inventory/vouchers')"
                            class="px-3 py-2 rounded bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-300 flex items-center justify-center gap-2"><i
                                data-lucide="wifi" class="w-4 h-4"></i><span>Sell Hotspot</span></button>
                        <button @click="$router.go('#/customers/new')"
                            class="px-3 py-2 rounded bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-300 flex items-center justify-center gap-2"><i
                                data-lucide="user-plus" class="w-4 h-4"></i><span>Add Customer</span></button>
                    </div>
                </div>
            </div>
            <div class="rounded border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-gray-900">
                <h3 class="font-semibold mb-3">Recent Activity</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-gray-500">
                            <tr>
                                <th class="text-left py-2">Time</th>
                                <th class="text-left">Receipt</th>
                                <th class="text-right">Total</th>
                                <th class="text-left">Items</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="s in $db.recentSales(10)">
                                <tr class="border-t border-gray-100 dark:border-gray-800">
                                    <td class="py-2" x-text="fmtDateTime(s.timestamp)"></td>
                                    <td><a href="#/sales/history" class="text-brand" x-text="s.id"></a></td>
                                    <td class="text-right" x-text="fmtCurrency(s.total)"></td>
                                    <td x-text="s.items.length + (s.hotspot?.length||0)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section x-show="$router.is('#/pos')" x-transition x-data="POS" x-init="mount()">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="rounded border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900">
                    <div class="p-3 flex items-center gap-2">
                        <i data-lucide="search" class="w-4 h-4 text-gray-500"></i>
                        <input x-ref="search" x-model="q" placeholder="Search by name or SKU (F2 or / to focus)"
                            class="w-full bg-transparent outline-none">
                        <select x-model="filter"
                            class="text-sm bg-transparent border border-gray-200 dark:border-gray-800 rounded px-2 py-1">
                            <option>All</option>
                            <option>Stationery</option>
                            <option>Printing</option>
                            <option>Hotspot</option>
                        </select>
                        <button @click="view='grid'" :class="view==='grid'?'text-brand':''"
                            class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800"><i data-lucide="grid"
                                class="w-4 h-4"></i></button>
                        <button @click="view='list'" :class="view==='list'?'text-brand':''"
                            class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800"><i data-lucide="list"
                                class="w-4 h-4"></i></button>
                    </div>
                    <div class="border-t border-gray-100 dark:border-gray-800"></div>
                    <div class="p-3">
                        <div x-show="view==='grid'" class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            <template x-for="item in catalog()">
                                <button @click="onItemClick(item)"
                                    class="rounded border border-gray-200 dark:border-gray-800 p-3 hover:border-brand text-left">
                                    <div class="text-sm font-medium" x-text="item.name"></div>
                                    <div class="text-xs text-gray-500"
                                        x-text="item.sku||item.unit||item.durationHours+'h'"></div>
                                    <div class="mt-2 font-semibold" x-text="fmtCurrency(item.price)"></div>
                                </button>
                            </template>
                        </div>
                        <div x-show="view==='list'" class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="text-gray-500">
                                    <tr>
                                        <th class="text-left py-2">Name</th>
                                        <th class="text-left">Info</th>
                                        <th class="text-right">Price</th>
                                        <th class="text-right">Stock</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="item in catalog()">
                                        <tr class="border-t border-gray-100 dark:border-gray-800">
                                            <td class="py-2" x-text="item.name"></td>
                                            <td class="text-gray-500"
                                                x-text="item.sku||item.unit||item.durationHours+'h'"></td>
                                            <td class="text-right" x-text="fmtCurrency(item.price)"></td>
                                            <td class="text-right" x-text="item.stock??'-'"></td>
                                            <td class="text-right"><button @click="onItemClick(item)"
                                                    class="px-2 py-1 rounded bg-brand/10 text-brand">Add</button></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div
                    class="rounded border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 flex flex-col">
                    <div class="p-3 flex items-center justify-between">
                        <div class="font-semibold">Cart</div>
                        <div class="flex items-center gap-2">
                            <select x-model="customerId"
                                class="text-sm bg-transparent border border-gray-200 dark:border-gray-800 rounded px-2 py-1">
                                <template x-for="c in $db.state.customers">
                                    <option :value="c.id" x-text="c.name"></option>
                                </template>
                            </select>
                            <button @click="$router.go('#/customers/new')"
                                class="p-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800" title="Add Customer"><i
                                    data-lucide="user-plus" class="w-4 h-4"></i></button>
                        </div>
                    </div>
                    <div class="border-t border-gray-100 dark:border-gray-800"></div>
                    <div class="p-3 flex-1 overflow-auto">
                        <template x-if="items.length===0">
                            <div class="text-sm text-gray-500">No items. Add from catalog.</div>
                        </template>
                        <div class="space-y-2">
                            <template x-for="(it,i) in items" :key="i">
                                <div
                                    class="flex items-center gap-2 rounded border border-gray-200 dark:border-gray-800 p-2">
                                    <div class="flex-1">
                                        <div class="font-medium" x-text="it.name"></div>
                                        <div class="text-xs text-gray-500"
                                            x-text="it.type==='hotspot' ? it.code : it.type==='product' ? $db.getProduct(it.refId)?.sku : $db.getService(it.refId)?.unit">
                                        </div>
                                    </div>
                                    <div class="w-24 text-right" x-text="fmtCurrency(it.unitPrice)"></div>
                                    <div class="flex items-center gap-1">
                                        <button @click="decQty(i)"
                                            class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-800"><i
                                                data-lucide="minus" class="w-4 h-4"></i></button>
                                        <input type="number" min="1" x-model.number="it.qty"
                                            class="w-14 text-center bg-transparent border border-gray-200 dark:border-gray-800 rounded px-2 py-1">
                                        <button @click="incQty(i)"
                                            class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-800"><i
                                                data-lucide="plus" class="w-4 h-4"></i></button>
                                    </div>
                                    <div class="w-24 text-right font-semibold"
                                        x-text="fmtCurrency(it.qty*it.unitPrice)"></div>
                                    <button @click="remove(i)"
                                        class="p-2 rounded hover:bg-red-50 dark:hover:bg-red-900/30 text-red-600"><i
                                            data-lucide="x" class="w-4 h-4"></i></button>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="border-t border-gray-100 dark:border-gray-800"></div>
                    <div class="p-3 space-y-2">
                        <div class="flex items-center justify-between text-sm"><span>Subtotal</span><span
                                x-text="fmtCurrency(subtotal)"></span></div>
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <span>Discount</span>
                                <button @click="openDiscount()"
                                    class="px-2 py-1 rounded bg-gray-100 dark:bg-gray-800 text-xs">F4</button>
                            </div>
                            <span x-text="discountLabel()"></span>
                        </div>
                        <div class="flex items-center justify-between text-sm"><span>Tax</span><span
                                x-text="fmtCurrency(tax)"></span></div>
                        <div class="flex items-center justify-between font-semibold text-lg"><span>Total</span><span
                                x-text="fmtCurrency(total)"></span></div>
                        <div class="flex items-center gap-2">
                            <button @click="paymentMethod='Cash'" :class="payBtn('Cash')"
                                class="px-3 py-2 rounded border">Cash</button>
                            <button @click="paymentMethod='Mobile Money'" :class="payBtn('Mobile Money')"
                                class="px-3 py-2 rounded border">Mobile Money</button>
                            <button @click="paymentMethod='Bank'" :class="payBtn('Bank')"
                                class="px-3 py-2 rounded border">Bank</button>
                        </div>
                        <div class="flex items-center gap-2">
                            <button @click="confirmCheckout()"
                                class="flex-1 px-4 py-2 rounded bg-brand text-white">Complete Sale (F10)</button>
                            <button @click="clearCart()" class="px-3 py-2 rounded border">Clear</button>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="showQty" class="fixed inset-0 bg-black/40 flex items-center justify-center"
                @keydown.escape.window="showQty=false">
                <div class="bg-white dark:bg-gray-900 rounded shadow-lg w-[90%] max-w-sm p-4">
                    <div class="font-semibold mb-2" x-text="qtyTitle"></div>
                    <input type="number" min="1" x-model.number="qtyValue"
                        class="w-full border border-gray-200 dark:border-gray-800 rounded px-3 py-2 bg-transparent">
                    <div class="mt-3 flex justify-end gap-2">
                        <button @click="showQty=false" class="px-3 py-2 rounded border">Cancel</button>
                        <button @click="confirmQty()" class="px-3 py-2 rounded bg-brand text-white">Add</button>
                    </div>
                </div>
            </div>

            <div x-show="showDiscount" class="fixed inset-0 bg-black/40 flex items-center justify-center"
                @keydown.escape.window="showDiscount=false">
                <div class="bg-white dark:bg-gray-900 rounded shadow-lg w-[90%] max-w-sm p-4">
                    <div class="font-semibold mb-2">Discount</div>
                    <div class="flex items-center gap-2">
                        <label class="text-sm">Amount</label>
                        <input type="number" min="0" x-model.number="discountAmount"
                            class="flex-1 border border-gray-200 dark:border-gray-800 rounded px-2 py-1 bg-transparent">
                        <label class="text-sm">%</label>
                        <input type="number" min="0" max="100" x-model.number="discountPercent"
                            class="w-20 border border-gray-200 dark:border-gray-800 rounded px-2 py-1 bg-transparent">
                    </div>
                    <div class="mt-3 flex justify-end gap-2">
                        <button @click="showDiscount=false" class="px-3 py-2 rounded border">Close</button>
                    </div>
                </div>
            </div>

            <div x-show="showConfirm" class="fixed inset-0 bg-black/40 flex items-center justify-center"
                @keydown.escape.window="showConfirm=false">
                <div class="bg-white dark:bg-gray-900 rounded shadow-lg w-[90%] max-w-md p-4">
                    <div class="flex items-center gap-2 font-semibold"><i data-lucide="check"
                            class="w-5 h-5 text-emerald-600"></i><span>Confirm Checkout</span></div>
                    <div class="mt-2 text-sm">Payment Method: <span class="font-medium" x-text="paymentMethod"></span>
                    </div>
                    <div class="mt-1 text-sm">Total: <span class="font-semibold" x-text="fmtCurrency(total)"></span>
                    </div>
                    <div class="mt-3 flex justify-end gap-2">
                        <button @click="showConfirm=false" class="px-3 py-2 rounded border">Cancel</button>
                        <button @click="checkout()" class="px-3 py-2 rounded bg-brand text-white">Confirm</button>
                    </div>
                </div>
            </div>
        </section>

        <section x-show="$router.is('#/inventory/products')" x-transition x-data="InventoryProducts">
            <div class="flex items-center justify-between">
                <div class="text-xl font-semibold">Products</div>
                <div class="flex items-center gap-2">
                    <input x-model="q" placeholder="Search"
                        class="border border-gray-200 dark:border-gray-800 rounded px-3 py-2 bg-transparent">
                    <button @click="openNew()" class="px-3 py-2 rounded bg-brand text-white flex items-center gap-2"><i
                            data-lucide="plus" class="w-4 h-4"></i><span>New Product</span></button>
                </div>
            </div>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-gray-500">
                        <tr>
                            <th class="text-left py-2">SKU</th>
                            <th class="text-left">Name</th>
                            <th class="text-left">Category</th>
                            <th class="text-right">Price</th>
                            <th class="text-right">Stock</th>
                            <th class="text-right">Reorder</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="p in filtered()">
                            <tr class="border-t border-gray-100 dark:border-gray-800"
                                :class="p.stock<=p.reorderLevel?'bg-amber-50 dark:bg-amber-900/20':''">
                                <td class="py-2" x-text="p.sku"></td>
                                <td x-text="p.name"></td>
                                <td x-text="p.category"></td>
                                <td class="text-right" x-text="fmtCurrency(p.price)"></td>
                                <td class="text-right" x-text="p.stock"></td>
                                <td class="text-right" x-text="p.reorderLevel"></td>
                                <td class="text-right space-x-1">
                                    <button @click="edit(p)" class="px-2 py-1 rounded border">Edit</button>
                                    <button @click="adjust(p)" class="px-2 py-1 rounded border">Adjust</button>
                                    <button @click="remove(p.id)"
                                        class="px-2 py-1 rounded border text-red-600">Delete</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div x-show="showForm" class="fixed inset-0 bg-black/40 flex items-center justify-center"
                @keydown.escape.window="showForm=false">
                <div class="bg-white dark:bg-gray-900 rounded shadow-lg w-[90%] max-w-md p-4">
                    <div class="font-semibold mb-2" x-text="form.id?'Edit Product':'New Product'"></div>
                    <div class="grid grid-cols-2 gap-2">
                        <input x-model="form.sku" placeholder="SKU"
                            class="border border-gray-200 dark:border-gray-800 rounded px-2 py-1 bg-transparent col-span-2">
                        <input x-model="form.name" placeholder="Name"
                            class="border border-gray-200 dark:border-gray-800 rounded px-2 py-1 bg-transparent col-span-2">
                        <select x-model="form.category"
                            class="border border-gray-200 dark:border-gray-800 rounded px-2 py-1 bg-transparent col-span-2">
                            <template x-for="c in $db.state.categories">
                                <option x-text="c"></option>
                            </template>
                        </select>
                        <input type="number" x-model.number="form.price" placeholder="Price"
                            class="border border-gray-200 dark:border-gray-800 rounded px-2 py-1 bg-transparent">
                        <input type="number" x-model.number="form.stock" placeholder="Stock"
                            class="border border-gray-200 dark:border-gray-800 rounded px-2 py-1 bg-transparent">
                        <input type="number" x-model.number="form.reorderLevel" placeholder="Reorder Level"
                            class="border border-gray-200 dark:border-gray-800 rounded px-2 py-1 bg-transparent">
                    </div>
                    <div class="mt-3 flex justify-end gap-2">
                        <button @click="showForm=false" class="px-3 py-2 rounded border">Cancel</button>
                        <button @click="save()" class="px-3 py-2 rounded bg-brand text-white">Save</button>
                    </div>
                </div>
            </div>

            <div x-show="showAdjust" class="fixed inset-0 bg-black/40 flex items-center justify-center"
                @keydown.escape.window="showAdjust=false">
                <div class="bg-white dark:bg-gray-900 rounded shadow-lg w-[90%] max-w-md p-4">
                    <div class="font-semibold mb-2">Adjust Stock</div>
                    <div class="space-y-2">
                        <div class="text-sm" x-text="target?.name"></div>
                        <input type="number" x-model.number="delta" placeholder="+/- Qty"
                            class="w-full border border-gray-200 dark:border-gray-800 rounded px-2 py-1 bg-transparent">
                        <input x-model="reason" placeholder="Reason"
                            class="w-full border border-gray-200 dark:border-gray-800 rounded px-2 py-1 bg-transparent">
                    </div>
                    <div class="mt-3 flex justify-end gap-2">
                        <button @click="showAdjust=false" class="px-3 py-2 rounded border">Cancel</button>
                        <button @click="applyAdjust()" class="px-3 py-2 rounded bg-brand text-white">Apply</button>
                    </div>
                </div>
            </div>
        </section>

        <section x-show="$router.is('#/inventory/vouchers')" x-transition x-data="InventoryVouchers">
            <div class="flex items-center justify-between">
                <div class="text-xl font-semibold">Hotspot Vouchers</div>
                <div class="flex items-center gap-2">
                    <button @click="openBulk()" class="px-3 py-2 rounded bg-brand text-white flex items-center gap-2"><i
                            data-lucide="upload" class="w-4 h-4"></i><span>Bulk Add</span></button>
                    <button @click="exportAvailable()" class="px-3 py-2 rounded border flex items-center gap-2"><i
                            data-lucide="download" class="w-4 h-4"></i><span>Export Available</span></button>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mt-3">
                <template x-for="pkg in $db.state.hotspotPackages">
                    <div class="rounded border border-gray-200 dark:border-gray-800 p-3 bg-white dark:bg-gray-900">
                        <div class="font-semibold" x-text="pkg.name"></div>
                        <div class="text-sm text-gray-500" x-text="pkg.durationHours+' hours'"></div>
                        <div class="mt-2 text-sm">Available: <span class="font-semibold"
                                x-text="$db.voucherCounts(pkg.id).available"></span></div>
                        <div class="text-sm">Sold: <span class="font-semibold"
                                x-text="$db.voucherCounts(pkg.id).sold"></span></div>
                        <div class="text-sm">Void: <span class="font-semibold"
                                x-text="$db.voucherCounts(pkg.id).void"></span></div>
                    </div>
                </template>
            </div>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-gray-500">
                        <tr>
                            <th class="text-left py-2">Code</th>
                            <th class="text-left">Package</th>
                            <th class="text-left">Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="v in $db.state.vouchers">
                            <tr class="border-top border-gray-100 dark:border-gray-800">
                                <td class="py-2" x-text="v.code"></td>
                                <td x-text="$db.getPackage(v.packageId)?.name"></td>
                                <td><span class="px-2 py-1 rounded text-xs" :class="badge(v.status)"
                                        x-text="v.status"></span></td>
                                <td class="text-right space-x-1">
                                    <button @click="voidVoucher(v)" class="px-2 py-1 rounded border">Void</button>
                                    <button @click="replaceVoucher(v)" class="px-2 py-1 rounded border">Replace</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div x-show="showBulk" class="fixed inset-0 bg-black/40 flex items-center justify-center"
                @keydown.escape.window="showBulk=false">
                <div class="bg-white dark:bg-gray-900 rounded shadow-lg w-[90%] max-w-lg p-4">
                    <div class="font-semibold mb-2">Bulk Add Vouchers</div>
                    <select x-model="bulkPackage"
                        class="w-full border border-gray-200 dark:border-gray-800 rounded px-2 py-1 bg-transparent">
                        <option value="">Select Package</option>
                        <template x-for="p in $db.state.hotspotPackages">
                            <option :value="p.id" x-text="p.name"></option>
                        </template>
                    </select>
                    <textarea x-model="bulkCodes" placeholder="One code per line"
                        class="w-full h-40 mt-2 border border-gray-200 dark:border-gray-800 rounded px-2 py-1 bg-transparent"></textarea>
                    <div class="mt-3 flex justify-end gap-2">
                        <button @click="showBulk=false" class="px-3 py-2 rounded border">Cancel</button>
                        <button @click="applyBulk()" class="px-3 py-2 rounded bg-brand text-white">Add</button>
                    </div>
                </div>
            </div>
        </section>

        <section x-show="$router.is('#/inventory/printing')" x-transition x-data="InventoryPrinting">
            <div class="flex items-center justify-between">
                <div class="text-xl font-semibold">Printing Services</div>
                <button @click="openForm()" class="px-3 py-2 rounded bg-brand text-white flex items-center gap-2"><i
                        data-lucide="plus" class="w-4 h-4"></i><span>New Service</span></button>
            </div>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-gray-500">
                        <tr>
                            <th class="text-left py-2">Name</th>
                            <th class="text-left">Unit</th>
                            <th class="text-right">Price</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="s in $db.state.services">
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <td class="py-2" x-text="s.name"></td>
                                <td x-text="s.unit"></td>
                                <td class="text-right" x-text="fmtCurrency(s.price)"></td>
                                <td class="text-right space-x-1">
                                    <button @click="edit(s)" class="px-2 py-1 rounded border">Edit</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div x-show="showForm" class="fixed inset-0 bg-black/40 flex items-center justify-center"
                @keydown.escape.window="showForm=false">
                <div class="bg-white dark:bg-gray-900 rounded shadow-lg w-[90%] max-w-md p-4">
                    <div class="font-semibold mb-2" x-text="form.id?'Edit Service':'New Service'"></div>
                    <input x-model="form.name" placeholder="Name"
                        class="w-full border border-gray-200 dark:border-gray-800 rounded px-2 py-1 bg-transparent">
                    <div class="grid grid-cols-2 gap-2 mt-2">
                        <input x-model="form.unit" placeholder="Unit"
                            class="border border-gray-200 dark:border-gray-800 rounded px-2 py-1 bg-transparent">
                        <input type="number" x-model.number="form.price" placeholder="Price"
                            class="border border-gray-200 dark:border-gray-800 rounded px-2 py-1 bg-transparent">
                    </div>
                    <div class="mt-3 flex justify-end gap-2">
                        <button @click="showForm=false" class="px-3 py-2 rounded border">Cancel</button>
                        <button @click="save()" class="px-3 py-2 rounded bg-brand text-white">Save</button>
                    </div>
                </div>
            </div>
        </section>

        <section x-show="$router.is('#/inventory/adjustments')" x-transition x-data="InventoryAdjustments">
            <div class="flex items-center justify-between">
                <div class="text-xl font-semibold">Stock Adjustments</div>
                <button @click="exportCSV()" class="px-3 py-2 rounded border flex items-center gap-2"><i
                        data-lucide="download" class="w-4 h-4"></i><span>Export CSV</span></button>
            </div>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-gray-500">
                        <tr>
                            <th class="text-left py-2">Time</th>
                            <th class="text-left">Product</th>
                            <th class="text-right">Delta</th>
                            <th class="text-left">Reason</th>
                            <th class="text-left">User</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="a in $db.state.adjustments">
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <td class="py-2" x-text="fmtDateTime(a.timestamp)"></td>
                                <td x-text="$db.getProduct(a.productId)?.name"></td>
                                <td class="text-right" x-text="a.delta"></td>
                                <td x-text="a.reason"></td>
                                <td x-text="a.user"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </section>

        <section x-show="$router.is('#/sales/today')" x-transition x-data="SalesToday">
            <div class="text-xl font-semibold mb-3">Sales Today</div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-gray-500">
                        <tr>
                            <th class="text-left py-2">Time</th>
                            <th class="text-left">Receipt</th>
                            <th class="text-left">Payment</th>
                            <th class="text-right">Items</th>
                            <th class="text-right">Total</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="s in list">
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <td class="py-2" x-text="fmtDateTime(s.timestamp)"></td>
                                <td x-text="s.id"></td>
                                <td x-text="s.paymentMethod"></td>
                                <td class="text-right" x-text="s.items.length + (s.hotspot?.length||0)"></td>
                                <td class="text-right" x-text="fmtCurrency(s.total)"></td>
                                <td class="text-right"><button @click="print(s)"
                                        class="px-2 py-1 rounded border">Print</button></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </section>

        <section x-show="$router.is('#/sales/history')" x-transition x-data="SalesHistory">
            <div class="flex items-center justify-between">
                <div class="text-xl font-semibold">Sales History</div>
                <div class="flex items-center gap-2">
                    <input type="date" x-model="from"
                        class="border border-gray-200 dark:border-gray-800 rounded px-2 py-1 bg-transparent">
                    <input type="date" x-model="to"
                        class="border border-gray-200 dark:border-gray-800 rounded px-2 py-1 bg-transparent">
                    <input x-model="q" placeholder="Search receipt, customer, item"
                        class="border border-gray-200 dark:border-gray-800 rounded px-2 py-1 bg-transparent w-64">
                    <button @click="exportCSV()" class="px-3 py-2 rounded border flex items-center gap-2"><i
                            data-lucide="download" class="w-4 h-4"></i><span>Export CSV</span></button>
                </div>
            </div>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-gray-500">
                        <tr>
                            <th class="text-left py-2">Date</th>
                            <th class="text-left">Receipt</th>
                            <th class="text-left">Customer</th>
                            <th class="text-left">Payment</th>
                            <th class="text-right">Total</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="s in filtered()">
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <td class="py-2" x-text="fmtDate(s.timestamp)"></td>
                                <td x-text="s.id"></td>
                                <td x-text="$db.customerName(s.customerId)"></td>
                                <td x-text="s.paymentMethod"></td>
                                <td class="text-right" x-text="fmtCurrency(s.total)"></td>
                                <td class="text-right"><button @click="print(s)"
                                        class="px-2 py-1 rounded border">Print</button></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </section>

        <section x-show="$router.is('#/sales/returns')" x-transition x-data="SalesReturns">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="rounded border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-gray-900">
                    <div class="font-semibold mb-2">Create Return</div>
                    <select x-model="saleId"
                        class="w-full border border-gray-200 dark:border-gray-800 rounded px-2 py-1 bg-transparent">
                        <option value="">Select Sale</option>
                        <template x-for="s in $db.state.sales">
                            <option :value="s.id" x-text="s.id+' - '+fmtCurrency(s.total)"></option>
                        </template>
                    </select>
                    <div class="mt-3 space-y-2" x-show="sale">
                        <template x-for="(it,i) in sale.items">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 text-sm" x-text="it.name+' @ '+fmtCurrency(it.unitPrice)"></div>
                                <input type="number" min="0" :max="it.qty" x-model.number="retQty[i]"
                                    class="w-20 border border-gray-200 dark:border-gray-800 rounded px-2 py-1 bg-transparent">
                            </div>
                        </template>
                        <input x-model="reason" placeholder="Reason"
                            class="w-full border border-gray-200 dark:border-gray-800 rounded px-2 py-1 bg-transparent">
                        <div class="flex justify-end">
                            <button @click="submit()" class="px-3 py-2 rounded bg-brand text-white">Record
                                Return</button>
                        </div>
                    </div>
                </div>
                <div class="rounded border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-gray-900">
                    <div class="font-semibold mb-2">Returns Log</div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="text-gray-500">
                                <tr>
                                    <th class="text-left py-2">Time</th>
                                    <th class="text-left">Return ID</th>
                                    <th class="text-left">Sale</th>
                                    <th class="text-left">Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="r in $db.state.returns">
                                    <tr class="border-t border-gray-100 dark:border-gray-800">
                                        <td class="py-2" x-text="fmtDateTime(r.timestamp)"></td>
                                        <td x-text="r.id"></td>
                                        <td x-text="r.saleId"></td>
                                        <td x-text="r.reason"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <section x-show="$router.is('#/customers')" x-transition x-data="CustomersList">
            <div class="flex items-center justify-between">
                <div class="text-xl font-semibold">Customers</div>
                <div class="flex items-center gap-2">
                    <input x-model="q" placeholder="Search name or phone"
                        class="border border-gray-200 dark:border-gray-800 rounded px-3 py-2 bg-transparent">
                    <a href="#/customers/new" class="px-3 py-2 rounded bg-brand text-white flex items-center gap-2"><i
                            data-lucide="plus" class="w-4 h-4"></i><span>New</span></a>
                </div>
            </div>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-gray-500">
                        <tr>
                            <th class="text-left py-2">Name</th>
                            <th class="text-left">Phone</th>
                            <th class="text-left">Email</th>
                            <th class="text-right">Total Spent</th>
                            <th class="text-left">Last Purchase</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="c in filtered()">
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <td class="py-2" x-text="c.name"></td>
                                <td x-text="c.phone"></td>
                                <td x-text="c.email"></td>
                                <td class="text-right" x-text="fmtCurrency($db.totalSpent(c.id))"></td>
                                <td x-text="$db.lastPurchase(c.id)||'-'"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </section>

        <section x-show="$router.is('#/customers/new')" x-transition x-data="CustomerNew">
            <div class="text-xl font-semibold mb-3">New Customer</div>
            <div
                class="max-w-lg rounded border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-gray-900 space-y-2">
                <input x-model="form.name" placeholder="Name"
                    class="w-full border border-gray-200 dark:border-gray-800 rounded px-3 py-2 bg-transparent">
                <input x-model="form.phone" placeholder="Phone"
                    class="w-full border border-gray-200 dark:border-gray-800 rounded px-3 py-2 bg-transparent">
                <input x-model="form.email" placeholder="Email"
                    class="w-full border border-gray-200 dark:border-gray-800 rounded px-3 py-2 bg-transparent">
                <div class="flex justify-end gap-2">
                    <a href="#/customers" class="px-3 py-2 rounded border">Cancel</a>
                    <button @click="save()" class="px-3 py-2 rounded bg-brand text-white">Save</button>
                </div>
            </div>
        </section>

        <section x-show="$router.is('#/reports/overview')" x-transition x-data="ReportsOverview">
            <div class="text-xl font-semibold mb-3">Reports Overview</div>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
                <div class="rounded border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-gray-900">
                    <div class="font-semibold mb-2">Totals by Day (7)</div>
                    <div class="space-y-2">
                        <template x-for="r in $db.last7Days()">
                            <div>
                                <div class="flex items-center justify-between text-sm"><span
                                        x-text="r.date"></span><span x-text="fmtCurrency(r.revenue)"></span></div>
                                <div class="h-2 bg-gray-100 dark:bg-gray-800 rounded">
                                    <div class="h-2 bg-brand rounded" :style="'width:'+(r.revenue/maxRev()*100)+'%'">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="rounded border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-gray-900">
                    <div class="font-semibold mb-2">Top Categories</div>
                    <table class="w-full text-sm">
                        <thead class="text-gray-500">
                            <tr>
                                <th class="text-left py-2">Category</th>
                                <th class="text-right">Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <td class="py-2">Stationery</td>
                                <td class="text-right" x-text="$db.topCounts().stationery"></td>
                            </tr>
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <td class="py-2">Printing</td>
                                <td class="text-right" x-text="$db.topCounts().printing"></td>
                            </tr>
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <td class="py-2">Hotspot</td>
                                <td class="text-right" x-text="$db.topCounts().hotspot"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="rounded border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-gray-900">
                    <div class="font-semibold mb-2">Low Stock</div>
                    <ul class="text-sm space-y-1">
                        <template x-for="p in $db.lowStock()">
                            <li class="flex items-center justify-between">
                                <span x-text="p.name"></span>
                                <span
                                    class="px-2 py-0.5 text-xs rounded bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300"
                                    x-text="p.stock"></span>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </section>

        <section x-show="$router.is('#/reports/top-items')" x-transition x-data="ReportsTopItems">
            <div class="text-xl font-semibold mb-3">Top Items</div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-gray-500">
                        <tr>
                            <th class="text-left py-2">Item</th>
                            <th class="text-left">Category</th>
                            <th class="text-right">Qty</th>
                            <th class="text-right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="row in rows">
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <td class="py-2" x-text="row.name"></td>
                                <td x-text="row.category"></td>
                                <td class="text-right" x-text="row.qty"></td>
                                <td class="text-right" x-text="fmtCurrency(row.revenue)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </section>

        <section x-show="$router.is('#/reports/low-stock')" x-transition>
            <div class="text-xl font-semibold mb-3">Low Stock</div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                <template x-for="p in $db.lowStock()">
                    <div
                        class="rounded border border-amber-300 dark:border-amber-800 p-3 bg-amber-50 dark:bg-amber-900/20">
                        <div class="font-semibold" x-text="p.name"></div>
                        <div class="text-sm text-gray-600 dark:text-gray-300">Stock: <span class="font-semibold"
                                x-text="p.stock"></span> | Reorder: <span x-text="p.reorderLevel"></span></div>
                        <a href="#/inventory/products" class="mt-2 inline-flex items-center gap-2 text-brand text-sm"><i
                                data-lucide="download" class="w-4 h-4"></i><span>Receive Stock</span></a>
                    </div>
                </template>
            </div>
        </section>

        <section x-show="$router.is('#/reports/revenue')" x-transition x-data="ReportsRevenue">
            <div class="text-xl font-semibold mb-3">Revenue by Day/Period</div>
            <div class="flex items-center gap-2 mb-3">
                <input type="date" x-model="from"
                    class="border border-gray-200 dark:border-gray-800 rounded px-2 py-1 bg-transparent">
                <input type="date" x-model="to"
                    class="border border-gray-200 dark:border-gray-800 rounded px-2 py-1 bg-transparent">
                <button @click="calc()" class="px-3 py-2 rounded border">Apply</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-gray-500">
                        <tr>
                            <th class="text-left py-2">Date</th>
                            <th class="text-right">Receipts</th>
                            <th class="text-right">Revenue</th>
                            <th class="text-right">Avg Ticket</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="r in rows">
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <td class="py-2" x-text="r.date"></td>
                                <td class="text-right" x-text="r.count"></td>
                                <td class="text-right" x-text="fmtCurrency(r.revenue)"></td>
                                <td class="text-right" x-text="fmtCurrency(r.avg)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </section>

        <section x-show="$router.is('#/settings/profile')" x-transition x-data="SettingsProfile">
            <div class="text-xl font-semibold mb-3">Business Profile</div>
            <div
                class="max-w-2xl rounded border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-gray-900 space-y-2">
                <input x-model="$db.state.business.name" placeholder="Business Name"
                    class="w-full border border-gray-200 dark:border-gray-800 rounded px-3 py-2 bg-transparent">
                <input x-model="$db.state.business.currency" placeholder="Currency (e.g., UGX)"
                    class="w-full border border-gray-200 dark:border-gray-800 rounded px-3 py-2 bg-transparent">
                <input x-model="$db.state.business.receiptHeader" placeholder="Receipt Header"
                    class="w-full border border-gray-200 dark:border-gray-800 rounded px-3 py-2 bg-transparent">
                <input x-model="$db.state.business.receiptFooter" placeholder="Receipt Footer"
                    class="w-full border border-gray-200 dark:border-gray-800 rounded px-3 py-2 bg-transparent">
                <div class="flex justify-end"><button @click="$db.save();$ui.toast('Saved')"
                        class="px-3 py-2 rounded bg-brand text-white">Save</button></div>
            </div>
        </section>

        <section x-show="$router.is('#/settings/tax')" x-transition x-data="SettingsTax">
            <div class="text-xl font-semibold mb-3">Taxes & Discounts</div>
            <div
                class="max-w-xl rounded border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-gray-900 space-y-2">
                <div class="flex items-center gap-2">
                    <label class="w-48">Default Tax Rate (%)</label>
                    <input type="number" min="0" x-model.number="$db.state.business.taxRate"
                        class="flex-1 border border-gray-200 dark:border-gray-800 rounded px-3 py-2 bg-transparent">
                </div>
                <div class="flex items-center gap-2">
                    <label class="w-48">Allow Negative Discounts</label>
                    <input type="checkbox" x-model="$db.state.settings.allowNegativeDiscounts">
                </div>
                <div class="flex justify-end"><button @click="$db.save();$ui.toast('Saved')"
                        class="px-3 py-2 rounded bg-brand text-white">Save</button></div>
            </div>
        </section>

        <section x-show="$router.is('#/settings/hotspot')" x-transition x-data="SettingsHotspot">
            <div class="text-xl font-semibold mb-3">Hotspot Settings</div>
            <div
                class="max-w-xl rounded border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-gray-900 space-y-2">
                <div class="flex items-center gap-2">
                    <label class="w-48">Show Codes on Receipt</label>
                    <input type="checkbox" x-model="$db.state.settings.showHotspotCodesOnReceipt">
                </div>
                <input x-model="$db.state.settings.hotspotReminder" placeholder="Reminder text"
                    class="w-full border border-gray-200 dark:border-gray-800 rounded px-3 py-2 bg-transparent">
                <div class="flex justify-end"><button @click="$db.save();$ui.toast('Saved')"
                        class="px-3 py-2 rounded bg-brand text-white">Save</button></div>
            </div>
        </section>

        <section x-show="$router.is('#/settings/backup')" x-transition x-data="SettingsBackup">
            <div class="text-xl font-semibold mb-3">Backup & Restore</div>
            <div
                class="rounded border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-gray-900 space-y-3 max-w-2xl">
                <div class="flex items-center gap-2">
                    <button @click="$db.exportJSON()" class="px-3 py-2 rounded border flex items-center gap-2"><i
                            data-lucide="download" class="w-4 h-4"></i><span>Export JSON</span></button>
                    <label class="px-3 py-2 rounded border cursor-pointer flex items-center gap-2">
                        <i data-lucide="upload" class="w-4 h-4"></i><span>Import JSON</span>
                        <input type="file" class="hidden" @change="$db.importJSON($event)">
                    </label>
                </div>
                <div class="text-xs text-gray-500">Keep backups safe. Importing replaces current data.</div>
            </div>
        </section>

        <section x-show="$router.is('#/settings/theme')" x-transition>
            <div class="text-xl font-semibold mb-3">Theme</div>
            <div class="rounded border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-gray-900 max-w-md">
                <div class="flex items-center gap-2">
                    <button @click="setTheme('system')" :class="theme==='system'?'bg-brand text-white':'border'"
                        class="px-3 py-2 rounded">System</button>
                    <button @click="setTheme('light')" :class="theme==='light'?'bg-brand text-white':'border'"
                        class="px-3 py-2 rounded">Light</button>
                    <button @click="setTheme('dark')" :class="theme==='dark'?'bg-brand text-white':'border'"
                        class="px-3 py-2 rounded">Dark</button>
                </div>
            </div>
        </section>

        <section x-show="$router.is('#/settings/factory')" x-transition x-data="SettingsFactory">
            <div class="text-xl font-semibold mb-3">Factory Reset</div>
            <div
                class="rounded border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-gray-900 max-w-md space-y-3">
                <div class="text-sm">This clears all local data and reloads seeded defaults.</div>
                <div class="flex items-center gap-2">
                    <input type="text" x-model="confirm" placeholder="Type RESET to confirm"
                        class="flex-1 border border-gray-200 dark:border-gray-800 rounded px-3 py-2 bg-transparent">
                    <button :disabled="confirm!=='RESET'" @click="$db.factoryReset()"
                        class="px-3 py-2 rounded bg-red-600 text-white disabled:opacity-50">Reset</button>
                </div>
            </div>
        </section>

        <section x-show="$router.is('#/help')" x-transition>
            <div class="text-xl font-semibold mb-3">Help</div>
            <div
                class="rounded border border-gray-200 dark:border-gray-800 p-4 bg-white dark:bg-gray-900 space-y-2 max-w-2xl text-sm">
                <div>Shortcuts: F2 or / focus search, F4 discount dialog, F10 complete sale, Del remove line, Esc close
                    modals.</div>
                <div>Data is stored in your browser. Use Backup/Restore to move between machines.</div>
            </div>
        </section>
    </main>

    <section id="receipt" class="hidden p-6">
        <div class="text-center">
            <div class="text-lg font-semibold" x-text="$db.state.business.receiptHeader"></div>
            <div class="text-sm" x-text="$db.state.business.name"></div>
        </div>
        <div class="mt-2 text-sm flex justify-between">
            <div><span class="font-medium">Receipt:</span> <span x-text="$ui.printData.id"></span></div>
            <div><span class="font-medium">Time:</span> <span x-text="fmtDateTime($ui.printData.timestamp)"></span>
            </div>
        </div>
        <div class="mt-2 text-sm"><span class="font-medium">Cashier:</span> <span x-text="$ui.printData.cashier"></span>
            | <span class="font-medium">Customer:</span> <span
                x-text="$db.customerName($ui.printData.customerId)"></span></div>
        <div class="mt-3">
            <table class="w-full text-sm">
                <thead>
                    <tr>
                        <th class="text-left">Item</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Price</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="it in $ui.printData.items">
                        <tr>
                            <td x-text="it.name"></td>
                            <td class="text-right" x-text="it.qty"></td>
                            <td class="text-right" x-text="fmtCurrency(it.unitPrice)"></td>
                            <td class="text-right" x-text="fmtCurrency(it.lineTotal||it.qty*it.unitPrice)"></td>
                        </tr>
                    </template>
                    <template x-for="h in $ui.printData.hotspot">
                        <tr>
                            <td colspan="2" x-text="$db.getPackage(h.packageId)?.name"></td>
                            <td class="text-right" x-text="fmtCurrency(h.price)"></td>
                            <td class="text-right" x-text="fmtCurrency(h.price)"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <div class="mt-3 text-sm space-y-1">
                <div class="flex justify-between"><span>Subtotal</span><span
                        x-text="fmtCurrency($ui.printData.subTotal)"></span></div>
                <div class="flex justify-between"><span>Discount</span><span
                        x-text="fmtCurrency($ui.printData.discount)"></span></div>
                <div class="flex justify-between"><span>Tax</span><span x-text="fmtCurrency($ui.printData.tax)"></span>
                </div>
                <div class="flex justify-between font-semibold text-base"><span>Total</span><span
                        x-text="fmtCurrency($ui.printData.total)"></span></div>
                <div class="flex justify-between"><span>Paid</span><span
                        x-text="fmtCurrency($ui.printData.paid)"></span></div>
                <div class="flex justify-between"><span>Change</span><span
                        x-text="fmtCurrency($ui.printData.change)"></span></div>
            </div>
            <div class="mt-3 text-sm" x-show="$db.state.settings.showHotspotCodesOnReceipt">
                <div class="font-semibold">Hotspot Codes</div>
                <ul class="list-disc ml-6">
                    <template x-for="h in $ui.printData.hotspot">
                        <li x-text="h.code"></li>
                    </template>
                </ul>
                <div class="mt-1" x-text="$db.state.settings.hotspotReminder"></div>
            </div>
            <div class="mt-4 text-center text-sm" x-text="$db.state.business.receiptFooter"></div>
        </div>
    </section>

    <div class="fixed bottom-4 right-4 space-y-2" x-data x-init="$watch('$ui.toasts',()=>lucide.createIcons())">
        <template x-for="t in $ui.toasts">
            <div class="px-3 py-2 rounded shadow-md text-sm text-white"
                :class="t.type==='error'?'bg-red-600':t.type==='warn'?'bg-amber-600':'bg-emerald-600'">
                <div class="flex items-center gap-2"><i :data-lucide="t.icon" class="w-4 h-4"></i><span
                        x-text="t.msg"></span></div>
            </div>
        </template>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('router', {
                current: localStorage.getItem('kelz_route') || '#/dashboard',
                go(h) { location.hash = h },
                is(prefix) { return this.current.startsWith(prefix) }
            })
            window.addEventListener('hashchange', () => {
                Alpine.store('router').current = location.hash || '#/dashboard'
                localStorage.setItem('kelz_route', Alpine.store('router').current)
                setTimeout(() => lucide.createIcons(), 0)
            })
            if (!location.hash) location.hash = Alpine.store('router').current
            Alpine.data('Dropdown', () => ({ open: false, toggle() { this.open = !this.open } }))
            Alpine.store('ui', {
                toasts: [],
                printData: {},
                toast(msg, type = 'success', icon = 'check') { const t = { msg, type, icon }; this.toasts.push(t); setTimeout(() => this.toasts.splice(this.toasts.indexOf(t), 1), 2500) }
            })
            Alpine.store('db', {
                key: 'kelznet_pos_db_v1',
                state: {},
                seed() {
                    this.state = {
                        business: { name: 'KelzNet Hub', currency: 'UGX', taxRate: 0, receiptHeader: 'KelzNet Hub — Public Hotspot & Stationery', receiptFooter: 'Thank you for your business!' },
                        categories: ['Hotspot', 'Stationery', 'Printing'],
                        products: [
                            { id: 'P001', sku: 'BK-A5-80', name: 'A5 Exercise Book (80pg)', category: 'Stationery', price: 1500, stock: 120, reorderLevel: 30 },
                            { id: 'P002', sku: 'PEN-BIC', name: 'BIC Pen Blue', category: 'Stationery', price: 1000, stock: 200, reorderLevel: 50 },
                            { id: 'P003', sku: 'PPR-A4-RM', name: 'A4 Paper Ream (500)', category: 'Stationery', price: 25000, stock: 4, reorderLevel: 5 }
                        ],
                        services: [
                            { id: 'S001', name: 'B/W Printing (per page)', category: 'Printing', unit: 'page', price: 300 },
                            { id: 'S002', name: 'Color Printing (per page)', category: 'Printing', unit: 'page', price: 1000 },
                            { id: 'S003', name: 'Photocopy (per page)', category: 'Printing', unit: 'page', price: 200 },
                            { id: 'S004', name: 'Lamination (per sheet)', category: 'Printing', unit: 'sheet', price: 2000 }
                        ],
                        hotspotPackages: [
                            { id: 'H12', name: 'Hotspot 12hrs', durationHours: 12, price: 2000 },
                            { id: 'H24', name: 'Hotspot 24hrs', durationHours: 24, price: 3000 },
                            { id: 'HWK', name: 'Hotspot Weekly (7 days)', durationHours: 168, price: 12000 },
                            { id: 'HMO', name: 'Hotspot Monthly (30 days)', durationHours: 720, price: 30000 }
                        ],
                        vouchers: [
                            { id: 'V0001', packageId: 'H12', code: '12H-9F4K-TZ7P', status: 'available' },
                            { id: 'V0002', packageId: 'H12', code: '12H-7A2L-MQ3C', status: 'available' },
                            { id: 'V0010', packageId: 'H24', code: '24H-APX1-4KLM', status: 'available' },
                            { id: 'V0011', packageId: 'HWK', code: 'WK-8Z1Q-PLMN', status: 'sold' },
                            { id: 'V0012', packageId: 'HMO', code: 'MO-2LA9-RTYU', status: 'available' }
                        ],
                        customers: [
                            { id: 'C001', name: 'Walk-in', phone: '', email: '' },
                            { id: 'C002', name: 'John Doe', phone: '+256700000001', email: 'john@example.com' }
                        ],
                        sales: [],
                        returns: [],
                        adjustments: [],
                        settings: { allowNegativeDiscounts: false, showHotspotCodesOnReceipt: true, hotspotReminder: 'Use responsibly. Contact attendant for support.' }
                    }
                    const today = new Date()
                    const pad = (n) => String(n).padStart(2, '0')
                    const iso = (d, h = 10) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(h)}:00:00+03:00`
                    const mkSale = (id, dt, items, hot = [], total) => ({ id, timestamp: dt, items, hotspot: hot, customerId: 'C001', subTotal: total, discount: 0, tax: 0, total, paid: total, change: 0, paymentMethod: 'Cash', cashier: 'Admin', note: '' })
                    const s1 = mkSale('S-00001', iso(today, 9), [{ type: 'product', refId: 'P001', name: 'A5 Exercise Book (80pg)', qty: 2, unitPrice: 1500, lineTotal: 3000 }, { type: 'print', refId: 'S001', name: 'B/W Printing', qty: 10, unitPrice: 300, lineTotal: 3000 }], [{ voucherId: 'V0011', packageId: 'HWK', code: 'WK-8Z1Q-PLMN', price: 12000 }], 18000)
                    const d1 = new Date(today); d1.setDate(today.getDate() - 1)
                    const s2 = mkSale('S-00002', iso(d1, 11), [{ type: 'product', refId: 'P002', name: 'BIC Pen Blue', qty: 5, unitPrice: 1000, lineTotal: 5000 }], [], 5000)
                    const d2 = new Date(today); d2.setDate(today.getDate() - 3)
                    const s3 = mkSale('S-00003', iso(d2, 14), [{ type: 'print', refId: 'S002', name: 'Color Printing (per page)', qty: 6, unitPrice: 1000, lineTotal: 6000 }], [], 6000)
                    this.state.sales.push(s1, s2, s3)
                    this.save()
                },
                load() {
                    const raw = localStorage.getItem(this.key)
                    if (raw) { this.state = JSON.parse(raw) } else { this.seed() }
                },
                save() { localStorage.setItem(this.key, JSON.stringify(this.state)) },
                factoryReset() { localStorage.removeItem(this.key); location.reload() },
                exportJSON() {
                    const blob = new Blob([JSON.stringify(this.state, null, 2)], { type: 'application/json' })
                    const url = URL.createObjectURL(blob)
                    const a = document.createElement('a'); a.href = url; a.download = 'kelznet_pos_backup.json'; a.click(); URL.revokeObjectURL(url)
                },
                importJSON(e) {
                    const f = e.target.files[0]; if (!f) return
                    const r = new FileReader()
                    r.onload = () => { this.state = JSON.parse(r.result); this.save(); location.reload() }
                    r.readAsText(f)
                },
                genId(p) { const n = Math.floor(Math.random() * 1e6).toString().padStart(6, '0'); return `${p}-${n}` },
                getProduct(id) { return this.state.products.find(x => x.id === id) },
                getService(id) { return this.state.services.find(x => x.id === id) },
                getPackage(id) { return this.state.hotspotPackages.find(x => x.id === id) },
                voucherCounts(pid) {
                    const v = this.state.vouchers.filter(x => x.packageId === pid)
                    return { available: v.filter(x => x.status === 'available').length, sold: v.filter(x => x.status === 'sold').length, void: v.filter(x => x.status === 'void').length }
                },
                availableVoucher(pid) { return this.state.vouchers.find(x => x.packageId === pid && x.status === 'available') },
                lowStock() { return this.state.products.filter(p => p.stock <= p.reorderLevel) },
                last7Days() {
                    const out = [], now = new Date()
                    for (let i = 6; i >= 0; i--) {
                        const d = new Date(now); d.setDate(now.getDate() - i)
                        const key = d.toISOString().slice(0, 10)
                        const daySales = this.state.sales.filter(s => s.timestamp.slice(0, 10) === key)
                        const rev = daySales.reduce((a, b) => a + b.total, 0)
                        const vouchers = daySales.reduce((a, s) => a + (s.hotspot?.length || 0), 0)
                        out.push({ date: key, revenue: rev, vouchers })
                    }
                    return out
                },
                recentSales(n) { return [...this.state.sales].sort((a, b) => b.timestamp.localeCompare(a.timestamp)).slice(0, n) },
                kpis: {
                    get todayTotal() { const key = new Date().toISOString().slice(0, 10); return Alpine.store('db').state.sales.filter(s => s.timestamp.slice(0, 10) === key).reduce((a, b) => a + b.total, 0) },
                    get receiptsToday() { const key = new Date().toISOString().slice(0, 10); return Alpine.store('db').state.sales.filter(s => s.timestamp.slice(0, 10) === key).length },
                    get itemsSoldToday() { const key = new Date().toISOString().slice(0, 10); return Alpine.store('db').state.sales.filter(s => s.timestamp.slice(0, 10) === key).reduce((a, s) => a + s.items.reduce((x, y) => x + y.qty, 0), 0) },
                    get vouchersSoldToday() { const key = new Date().toISOString().slice(0, 10); return Alpine.store('db').state.sales.filter(s => s.timestamp.slice(0, 10) === key).reduce((a, s) => a + (s.hotspot?.length || 0), 0) }
                },
                addSale(payload) {
                    this.state.sales.push(payload)
                    payload.items.forEach(it => {
                        if (it.type === 'product') { const p = this.getProduct(it.refId); if (p) p.stock = Math.max(0, (p.stock || 0) - it.qty) }
                    })
                    payload.hotspot?.forEach(h => {
                        const v = this.state.vouchers.find(x => x.id === h.voucherId)
                        if (v) v.status = 'sold'
                    })
                    this.save()
                },
                totalSpent(cid) { return this.state.sales.filter(s => s.customerId === cid).reduce((a, b) => a + b.total, 0) },
                lastPurchase(cid) { const s = [...this.state.sales].filter(s => s.customerId === cid).sort((a, b) => b.timestamp.localeCompare(a.timestamp))[0]; return s ? s.timestamp.slice(0, 16).replace('T', ' ') : null },
                addAdjustment(productId, delta, reason, user) { this.state.adjustments.push({ id: this.genId('ADJ'), timestamp: new Date().toISOString(), productId, delta, reason, user }); const p = this.getProduct(productId); if (p) p.stock = (p.stock || 0) + delta; this.save() },
                addVouchers(packageId, codes) { codes.forEach((c) => { const code = c.trim(); if (code) this.state.vouchers.push({ id: this.genId('V'), packageId, code, status: 'available' }) }); this.save() },
                replaceVoucher(v) { v.code = 'REP-' + Math.random().toString(36).slice(2, 8).toUpperCase(); v.status = 'available'; this.save() },
                voidVoucher(v) { v.status = 'void'; this.save() },
                customerName(id) { return (this.state.customers.find(c => c.id === id) || {}).name || '-' },
                topCounts() {
                    let stationery = 0, printing = 0, hotspot = 0
                    this.state.sales.forEach(s => {
                        s.items.forEach(it => {
                            if (it.type === 'product') stationery += it.qty
                            if (it.type === 'print') printing += it.qty
                        })
                        hotspot += s.hotspot?.length || 0
                    })
                    return { stationery, printing, hotspot }
                }
            })
            Alpine.magic('router', () => Alpine.store('router'))
            Alpine.magic('db', () => Alpine.store('db'))
            Alpine.magic('ui', () => Alpine.store('ui'))

            Alpine.data('AppShell', () => ({
                theme: localStorage.getItem('kelz_theme') || 'system',
                get themeClass() { if (this.theme === 'dark') return 'dark'; if (this.theme === 'light') return ''; return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : '' },
                get themeIcon() { return this.themeClass === 'dark' ? 'moon' : 'sun' },
                get themeTitle() { return this.themeClass === 'dark' ? 'Dark' : 'Light' },
                init() { this.$db.load(); this.$nextTick(() => lucide.createIcons()) },
                setTheme(v) { this.theme = v; localStorage.setItem('kelz_theme', v) },
                toggleTheme() { this.setTheme(this.themeClass === 'dark' ? 'light' : 'dark') },
                handleKeys(e) {
                    if (this.$router.is('#/pos')) {
                        if ((e.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) || e.key === 'F2') { e.preventDefault(); const s = document.querySelector('[x-ref=search]'); if (s) { s.focus(); s.select() } }
                        if (e.key === 'F4') { e.preventDefault(); this.$dispatch('open-discount') }
                        if (e.key === 'F10') { e.preventDefault(); this.$dispatch('confirm-checkout') }
                        if (e.key === 'Escape') { this.$dispatch('close-modals') }
                        if (e.key === 'Delete') { this.$dispatch('delete-line') }
                    }
                },
                navClass(prefix) { return this.$router.is(prefix) ? 'bg-gray-100 dark:bg-gray-800 text-brand' : 'hover:bg-gray-100 dark:hover:bg-gray-800' }
            }))

            Alpine.data('POS', () => ({
                q: '', filter: 'All', view: 'grid',
                items: [], customerId: 'C001', paymentMethod: 'Cash',
                showQty: false, qtyTitle: '', qtyValue: 1, pending: null,
                showDiscount: false, discountAmount: 0, discountPercent: 0,
                showConfirm: false,
                get subtotal() { return this.items.reduce((a, it) => a + (it.qty * it.unitPrice), 0) },
                get disc() { const a = Number(this.discountAmount) || 0; const p = Number(this.discountPercent) || 0; const d = a + this.subtotal * (p / 100); return this.$db.state.settings.allowNegativeDiscounts ? d : Math.max(0, d) },
                get tax() { return ((this.subtotal - this.disc) * (Number(this.$db.state.business.taxRate) || 0)) / 100 },
                get total() { return Math.max(0, this.subtotal - this.disc + this.tax) },
                mount() {
                    this.$watch('items', () => lucide.createIcons())
                    window.addEventListener('open-discount', () => this.openDiscount())
                    window.addEventListener('confirm-checkout', () => this.confirmCheckout())
                    window.addEventListener('close-modals', () => { this.showQty = false; this.showDiscount = false; this.showConfirm = false })
                    window.addEventListener('delete-line', () => { if (this.items.length) this.items.splice(this.items.length - 1, 1) })
                },
                catalog() {
                    let list = []
                    if (this.filter === 'All' || this.filter === 'Stationery') {
                        list = list.concat(this.$db.state.products.map(p => ({ kind: 'product', id: p.id, sku: p.sku, name: p.name, category: 'Stationery', price: p.price, stock: p.stock })))
                    }
                    if (this.filter === 'All' || this.filter === 'Printing') {
                        list = list.concat(this.$db.state.services.map(s => ({ kind: 'print', id: s.id, unit: s.unit, name: s.name, category: 'Printing', price: s.price })))
                    }
                    if (this.filter === 'All' || this.filter === 'Hotspot') {
                        list = list.concat(this.$db.state.hotspotPackages.map(h => ({ kind: 'hotspot', id: h.id, durationHours: h.durationHours, name: h.name, category: 'Hotspot', price: h.price })))
                    }
                    const q = this.q.toLowerCase()
                    if (!q) return list
                    return list.filter(it => (it.name || '').toLowerCase().includes(q) || (it.sku || '').toLowerCase().includes(q))
                },
                onItemClick(item) {
                    if (item.kind === 'product') {
                        const inCart = this.items.filter(x => x.type === 'product' && x.refId === item.id).reduce((a, b) => a + b.qty, 0)
                        const p = this.$db.getProduct(item.id)
                        if (p && (p.stock - inCart) <= 0) { this.$ui.toast('Insufficient stock', 'warn', 'alert-triangle'); return }
                        this.items.push({ type: 'product', refId: item.id, name: item.name, qty: 1, unitPrice: item.price })
                    } else if (item.kind === 'print') {
                        this.pending = { type: 'print', refId: item.id, name: item.name, unitPrice: item.price }
                        this.qtyTitle = item.name
                        this.qtyValue = 1
                        this.showQty = true
                    } else if (item.kind === 'hotspot') {
                        const v = this.$db.availableVoucher(item.id)
                        if (!v) { this.$ui.toast('Out of voucher stock', 'warn', 'wifi-off'); return }
                        this.items.push({ type: 'hotspot', refId: item.id, name: item.name, qty: 1, unitPrice: item.price, code: v.code, voucherId: v.id })
                    }
                },
                confirmQty() {
                    if (this.pending && this.qtyValue > 0) {
                        this.items.push({ ...this.pending, qty: Number(this.qtyValue) })
                        this.pending = null
                        this.showQty = false
                    }
                },
                openDiscount() { this.showDiscount = true },
                confirmCheckout() {
                    if (!this.items.length) { this.$ui.toast('Cart empty', 'warn', 'shopping-cart'); return }
                    this.showConfirm = true
                },
                remove(i) { this.items.splice(i, 1) },
                incQty(i) { this.items[i].qty = Number(this.items[i].qty || 1) + 1 },
                decQty(i) { const q = Number(this.items[i].qty || 1) - 1; this.items[i].qty = Math.max(1, q) },
                payBtn(t) { return this.paymentMethod === t ? 'border-brand text-brand' : 'border' },
                discountLabel() {
                    const parts = []
                    if (Number(this.discountAmount)) parts.push(fmtCurrency(this.discountAmount))
                    if (Number(this.discountPercent)) parts.push(this.discountPercent + '%')
                    return parts.length ? parts.join(' + ') : fmtCurrency(0)
                },
                clearCart() { this.items = []; this.discountAmount = 0; this.discountPercent = 0 },
                checkout() {
                    const productLines = this.items.filter(it => it.type === 'product')
                    for (const it of productLines) {
                        const p = this.$db.getProduct(it.refId)
                        const cartQty = productLines.filter(x => x.refId === it.refId).reduce((a, b) => a + b.qty, 0)
                        if (!p || p.stock < cartQty) { this.$ui.toast('Insufficient stock for ' + (p?.name || ''), 'error', 'alert-triangle'); this.showConfirm = false; return }
                    }
                    const hotLines = this.items.filter(it => it.type === 'hotspot')
                    for (const h of hotLines) {
                        const v = this.$db.state.vouchers.find(x => x.id === h.voucherId)
                        if (!v || v.status !== 'available') { this.$ui.toast('Voucher no longer available', 'error', 'wifi-off'); this.showConfirm = false; return }
                    }
                    const id = this.$db.genId('S')
                    const items = this.items.filter(it => it.type !== 'hotspot').map(it => ({ type: it.type, refId: it.refId, name: it.name, qty: it.qty, unitPrice: it.unitPrice, lineTotal: it.qty * it.unitPrice }))
                    const hotspot = hotLines.map(h => ({ voucherId: h.voucherId, packageId: h.refId, code: h.code, price: h.unitPrice }))
                    const payload = {
                        id,
                        timestamp: new Date().toISOString(),
                        items,
                        hotspot,
                        customerId: this.customerId,
                        subTotal: this.subtotal,
                        discount: this.disc,
                        tax: this.tax,
                        total: this.total,
                        paid: this.total,
                        change: 0,
                        paymentMethod: this.paymentMethod,
                        cashier: 'Admin',
                        note: ''
                    }
                    this.$db.addSale(payload)
                    this.$ui.toast('Sale recorded', 'success', 'check')
                    this.showConfirm = false
                    this.clearCart()
                    printReceipt(payload)
                }
            }))

            Alpine.data('InventoryProducts', () => ({
                q: '',
                showForm: false,
                form: { id: '', sku: '', name: '', category: 'Stationery', price: 0, stock: 0, reorderLevel: 0 },
                showAdjust: false, target: null, delta: 0, reason: '',
                filtered() {
                    const q = this.q.toLowerCase()
                    return this.$db.state.products.filter(p => !q || p.name.toLowerCase().includes(q) || p.sku.toLowerCase().includes(q))
                },
                openNew() { this.form = { id: '', sku: '', name: '', category: 'Stationery', price: 0, stock: 0, reorderLevel: 0 }; this.showForm = true },
                edit(p) { this.form = JSON.parse(JSON.stringify(p)); this.showForm = true },
                save() {
                    if (this.form.id) {
                        const i = this.$db.state.products.findIndex(x => x.id === this.form.id)
                        if (i >= 0) this.$db.state.products.splice(i, 1, JSON.parse(JSON.stringify(this.form)))
                    } else {
                        this.form.id = 'P' + Math.random().toString(36).slice(2, 7).toUpperCase()
                        this.$db.state.products.push(JSON.parse(JSON.stringify(this.form)))
                    }
                    this.$db.save(); this.showForm = false; this.$ui.toast('Saved')
                },
                remove(id) { const i = this.$db.state.products.findIndex(p => p.id === id); if (i >= 0) { this.$db.state.products.splice(i, 1); this.$db.save(); this.$ui.toast('Deleted', 'warn', 'trash') } },
                adjust(p) { this.target = p; this.delta = 0; this.reason = ''; this.showAdjust = true },
                applyAdjust() { if (!this.target) return; this.$db.addAdjustment(this.target.id, Number(this.delta || 0), this.reason || 'Adjustment', 'Admin'); this.showAdjust = false; this.$ui.toast('Stock adjusted') }
            }))

            Alpine.data('InventoryVouchers', () => ({
                showBulk: false, bulkPackage: '', bulkCodes: '',
                badge(s) { return s === 'available' ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300' : s === 'sold' ? 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300' : 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300' },
                openBulk() { this.bulkPackage = ''; this.bulkCodes = ''; this.showBulk = true },
                applyBulk() {
                    if (!this.bulkPackage) { this.$ui.toast('Select package', 'warn', 'wifi'); return }
                    const codes = this.bulkCodes.split('\n').map(x => x.trim()).filter(Boolean)
                    if (!codes.length) { this.$ui.toast('No codes', 'warn', 'wifi'); return }
                    this.$db.addVouchers(this.bulkPackage, codes)
                    this.showBulk = false; this.$ui.toast('Vouchers added')
                },
                exportAvailable() {
                    const codes = this.$db.state.vouchers.filter(v => v.status === 'available').map(v => v.code).join('\n')
                    navigator.clipboard.writeText(codes); this.$ui.toast('Copied to clipboard')
                },
                voidVoucher(v) { this.$db.voidVoucher(v); this.$ui.toast('Voucher voided', 'warn', 'x') },
                replaceVoucher(v) { this.$db.replaceVoucher(v); this.$ui.toast('Voucher replaced') }
            }))

            Alpine.data('InventoryPrinting', () => ({
                showForm: false, form: { id: '', name: '', unit: 'page', price: 0 },
                openForm() { this.form = { id: '', name: '', unit: 'page', price: 0 }; this.showForm = true },
                edit(s) { this.form = JSON.parse(JSON.stringify(s)); this.showForm = true },
                save() {
                    if (this.form.id) {
                        const i = this.$db.state.services.findIndex(x => x.id === this.form.id)
                        if (i >= 0) this.$db.state.services.splice(i, 1, JSON.parse(JSON.stringify(this.form)))
                    } else {
                        this.form.id = 'S' + Math.random().toString(36).slice(2, 7).toUpperCase()
                        this.$db.state.services.push(JSON.parse(JSON.stringify(this.form)))
                    }
                    this.$db.save(); this.showForm = false; this.$ui.toast('Saved')
                }
            }))

            Alpine.data('InventoryAdjustments', () => ({
                exportCSV() {
                    const rows = [['Time', 'Product', 'Delta', 'Reason', 'User']]
                    this.$db.state.adjustments.forEach(a => rows.push([a.timestamp, (this.$db.getProduct(a.productId) || {}).name || '', a.delta, a.reason, a.user]))
                    const csv = rows.map(r => r.map(x => `"${String(x).replace(/"/g, '""')}"`).join(',')).join('\n')
                    const blob = new Blob([csv], { type: 'text/csv' })
                    const url = URL.createObjectURL(blob); const a = document.createElement('a'); a.href = url; a.download = 'adjustments.csv'; a.click(); URL.revokeObjectURL(url)
                }
            }))

            Alpine.data('SalesToday', () => ({
                get list() {
                    const key = new Date().toISOString().slice(0, 10)
                    return this.$db.state.sales.filter(s => s.timestamp.slice(0, 10) === key).sort((a, b) => b.timestamp.localeCompare(a.timestamp))
                },
                print(s) { printReceipt(s) }
            }))

            Alpine.data('SalesHistory', () => ({
                from: '', to: '', q: '',
                init() {
                    const now = new Date(); const past = new Date(); past.setDate(now.getDate() - 7)
                    this.from = past.toISOString().slice(0, 10); this.to = now.toISOString().slice(0, 10)
                },
                filtered() {
                    const q = this.q.toLowerCase()
                    return this.$db.state.sales.filter(s => {
                        const d = s.timestamp.slice(0, 10)
                        if (this.from && d < this.from) return false
                        if (this.to && d > this.to) return false
                        if (!q) return true
                        const cust = this.$db.customerName(s.customerId).toLowerCase()
                        const items = s.items.map(i => i.name.toLowerCase()).join(' ')
                        return s.id.toLowerCase().includes(q) || cust.includes(q) || items.includes(q)
                    }).sort((a, b) => b.timestamp.localeCompare(a.timestamp))
                },
                exportCSV() {
                    const rows = [['Date', 'Receipt', 'Customer', 'Payment', 'Total']]
                    this.filtered().forEach(s => rows.push([s.timestamp, s.id, this.$db.customerName(s.customerId), s.paymentMethod, s.total]))
                    const csv = rows.map(r => r.map(x => `"${String(x).replace(/"/g, '""')}"`).join(',')).join('\n')
                    const blob = new Blob([csv], { type: 'text/csv' })
                    const url = URL.createObjectURL(blob); const a = document.createElement('a'); a.href = url; a.download = 'sales_history.csv'; a.click(); URL.revokeObjectURL(url)
                },
                print(s) { printReceipt(s) }
            }))

            Alpine.data('SalesReturns', () => ({
                saleId: '', retQty: {}, reason: '',
                get sale() { return this.$db.state.sales.find(s => s.id === this.saleId) || null },
                submit() {
                    if (!this.sale) return
                    const items = this.sale.items.map((it, i) => ({ ...it, qty: Math.min(Number(this.retQty[i] || 0), it.qty) })).filter(it => it.qty > 0)
                    if (!items.length) { this.$ui.toast('No quantities selected', 'warn', 'alert-triangle'); return }
                    const id = this.$db.genId('R')
                    this.$db.state.returns.push({ id, saleId: this.sale.id, timestamp: new Date().toISOString(), items, reason: this.reason || '' })
                    items.forEach(it => { if (it.type === 'product') this.$db.addAdjustment(it.refId, it.qty, 'Return', 'Admin') })
                    this.$db.save()
                    this.saleId = ''; this.retQty = {}; this.reason = ''
                    this.$ui.toast('Return recorded')
                }
            }))

            Alpine.data('CustomersList', () => ({
                q: '',
                filtered() {
                    const q = this.q.toLowerCase()
                    return this.$db.state.customers.filter(c => !q || c.name.toLowerCase().includes(q) || (c.phone || '').toLowerCase().includes(q))
                }
            }))

            Alpine.data('CustomerNew', () => ({
                form: { name: '', phone: '', email: '' },
                save() {
                    if (!this.form.name.trim()) { this.$ui.toast('Name required', 'warn', 'user'); return }
                    const id = 'C' + Math.random().toString(36).slice(2, 6).toUpperCase()
                    this.$db.state.customers.push({ id, ...this.form })
                    this.$db.save()
                    this.$ui.toast('Customer added')
                    this.form = { name: '', phone: '', email: '' }
                    this.$router.go('#/customers')
                }
            }))

            Alpine.data('ReportsOverview', () => ({
                maxRev() { return Math.max(1, ...this.$db.last7Days().map(r => r.revenue)) }
            }))

            Alpine.data('ReportsTopItems', () => ({
                get rows() {
                    const map = new Map()
                    this.$db.state.sales.forEach(s => {
                        s.items.forEach(it => {
                            const key = it.name + '|' + (it.type === 'product' ? 'Stationery' : 'Printing')
                            const cur = map.get(key) || { name: it.name, category: it.type === 'product' ? 'Stationery' : 'Printing', qty: 0, revenue: 0 }
                            cur.qty += it.qty
                            cur.revenue += it.qty * it.unitPrice
                            map.set(key, cur)
                        })
                        s.hotspot?.forEach(h => {
                            const name = (this.$db.getPackage(h.packageId) || {}).name || 'Hotspot'
                            const key = name + '|Hotspot'
                            const cur = map.get(key) || { name, category: 'Hotspot', qty: 0, revenue: 0 }
                            cur.qty += 1
                            cur.revenue += h.price
                            map.set(key, cur)
                        })
                    })
                    return [...map.values()].sort((a, b) => b.revenue - a.revenue)
                }
            }))

            Alpine.data('ReportsRevenue', () => ({
                from: '', to: '', rows: [],
                init() {
                    const now = new Date(); const past = new Date(); past.setDate(now.getDate() - 7)
                    this.from = past.toISOString().slice(0, 10); this.to = now.toISOString().slice(0, 10)
                    this.calc()
                },
                calc() {
                    const map = new Map()
                    this.$db.state.sales.forEach(s => {
                        const d = s.timestamp.slice(0, 10)
                        if (this.from && d < this.from) return
                        if (this.to && d > this.to) return
                        const cur = map.get(d) || { date: d, count: 0, revenue: 0, avg: 0 }
                        cur.count += 1; cur.revenue += s.total; map.set(d, cur)
                    })
                    const arr = [...map.values()].sort((a, b) => a.date.localeCompare(b.date))
                    arr.forEach(r => r.avg = r.count ? r.revenue / r.count : 0)
                    this.rows = arr
                }
            }))

            Alpine.data('SettingsProfile', () => ({}))
            Alpine.data('SettingsTax', () => ({}))
            Alpine.data('SettingsHotspot', () => ({}))
            Alpine.data('SettingsBackup', () => ({}))
            Alpine.data('SettingsFactory', () => ({ confirm: '' }))

            window.fmtCurrency = (n) => {
                const c = Alpine.store('db')?.state?.business?.currency || ''
                const val = Number(n || 0)
                return c ? `${c} ${val.toLocaleString()}` : val.toLocaleString()
            }
            window.fmtDateTime = (iso) => {
                try { const d = new Date(iso); return d.toLocaleString() } catch { return iso }
            }
            window.fmtDate = (iso) => {
                try { const d = new Date(iso); return d.toLocaleDateString() } catch { return iso }
            }
            window.printReceipt = (sale) => {
                Alpine.store('ui').printData = sale
                const el = document.getElementById('receipt')
                if (el) el.classList.remove('hidden')
                setTimeout(() => {
                    window.print()
                    setTimeout(() => { if (el) el.classList.add('hidden') }, 300)
                }, 50)
            }
        })
        window.addEventListener('load', () => lucide.createIcons())
    </script>
</body>

</html>