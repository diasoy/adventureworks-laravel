<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AdventureWorks Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .fade-in { animation: fadeIn 0.4s ease-in; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    </style>
    @stack('styles')
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-72 bg-gradient-to-b from-indigo-700 via-purple-700 to-pink-600 text-white transform -translate-x-full transition-transform duration-200 ease-out md:translate-x-0 flex flex-col shadow-2xl">
            <!-- Header - Fixed -->
            <div class="shrink-0 px-6 py-6 border-b border-white/10">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3">
                        <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                            <path d="M4 13.5V19a1 1 0 001 1h3.5a1 1 0 001-1v-5.5a1 1 0 00-1-1H5a1 1 0 00-1 1z" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10.5 8.5V19a1 1 0 001 1H15a1 1 0 001-1V8.5a1 1 0 00-1-1h-3.5a1 1 0 00-1 1z" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M17 5v14a1 1 0 001 1h3a1 1 0 001-1V5a1 1 0 00-1-1h-3a1 1 0 00-1 1z" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-white/70">AdventureWorks Analytics</p>
                        <h1 class="text-xl font-bold tracking-tight leading-tight">Data Warehouse</h1>
                    </div>
                </div>
                <!-- OLAP Mondrian Badge -->
                <button onclick="openOlapModal()" class="w-full bg-gradient-to-r from-amber-400 to-orange-500 hover:from-amber-500 hover:to-orange-600 text-white px-3 py-2 rounded-lg text-xs font-bold transition-all duration-200 shadow-lg flex items-center justify-between group">
                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a8 8 0 100 16 8 8 0 000-16zM9 9a1 1 0 112 0v4a1 1 0 11-2 0V9zm1-4a1 1 0 100 2 1 1 0 000-2z"/>
                        </svg>
                        <span>OLAP Mondrian Engine</span>
                    </div>
                    <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

            <!-- Navigation - Scrollable -->
            <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-3">
                <!-- Header Section -->
                <div class="px-3 mb-2">
                    <p class="text-xs font-bold text-white/50 uppercase tracking-wider">Business Questions</p>
                </div>

                <!-- Q1: Market Basket Analysis -->
                <a href="{{ route('dashboard.market-basket') }}"
                   class="group flex items-start px-4 py-3 rounded-xl text-sm transition {{ request()->routeIs('dashboard.market-basket') ? 'bg-white/20 shadow-lg' : 'hover:bg-white/10' }}">
                    <div class="flex-shrink-0 mt-0.5">
                        <div class="bg-amber-400 text-amber-900 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">
                            1
                        </div>
                    </div>
                    <div class="ml-3 flex-1">
                        <div class="font-bold text-white mb-1">Market Basket Analysis</div>
                        <div class="text-xs text-white/70 leading-relaxed">
                            Produk yang sering dibeli bersamaan untuk bundling & cross-selling
                        </div>
                    </div>
                </a>

                <!-- Q2: Territory Discount vs Profit -->
                <a href="{{ route('dashboard.territory-discount') }}"
                   class="group flex items-start px-4 py-3 rounded-xl text-sm transition {{ request()->routeIs('dashboard.territory-discount') ? 'bg-white/20 shadow-lg' : 'hover:bg-white/10' }}">
                    <div class="flex-shrink-0 mt-0.5">
                        <div class="bg-green-400 text-green-900 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">
                            2
                        </div>
                    </div>
                    <div class="ml-3 flex-1">
                        <div class="font-bold text-white mb-1">Wilayah: Diskon vs Profit</div>
                        <div class="text-xs text-white/70 leading-relaxed">
                            Analisis wilayah dengan diskon tinggi & profit margin rendah
                        </div>
                    </div>
                </a>

                <!-- Q3: Customer Frequency Analysis -->
                <a href="{{ route('dashboard.customer-segmentation') }}"
                   class="group flex items-start px-4 py-3 rounded-xl text-sm transition {{ request()->routeIs('dashboard.customer-segmentation') ? 'bg-white/20 shadow-lg' : 'hover:bg-white/10' }}">
                    <div class="flex-shrink-0 mt-0.5">
                        <div class="bg-blue-400 text-blue-900 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">
                            3
                        </div>
                    </div>
                    <div class="ml-3 flex-1">
                        <div class="font-bold text-white mb-1">Segmentasi Pelanggan</div>
                        <div class="text-xs text-white/70 leading-relaxed">
                            Frekuensi pembelian & segmen high frequency - low ticket size
                        </div>
                    </div>
                </a>

                <!-- Q4: Salesperson Retention -->
                <a href="{{ route('dashboard.salesperson-retention') }}"
                   class="group flex items-start px-4 py-3 rounded-xl text-sm transition {{ request()->routeIs('dashboard.salesperson-retention') ? 'bg-white/20 shadow-lg' : 'hover:bg-white/10' }}">
                    <div class="flex-shrink-0 mt-0.5">
                        <div class="bg-purple-400 text-purple-900 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">
                            4
                        </div>
                    </div>
                    <div class="ml-3 flex-1">
                        <div class="font-bold text-white mb-1">Retensi Salesperson</div>
                        <div class="text-xs text-white/70 leading-relaxed">
                            Salesperson dengan customer retention terbaik & total penjualan
                        </div>
                    </div>
                </a>

                <!-- Q5: Inventory Turnover -->
                <a href="{{ route('dashboard.inventory-turnover') }}"
                   class="group flex items-start px-4 py-3 rounded-xl text-sm transition {{ request()->routeIs('dashboard.inventory-turnover') ? 'bg-white/20 shadow-lg' : 'hover:bg-white/10' }}">
                    <div class="flex-shrink-0 mt-0.5">
                        <div class="bg-pink-400 text-pink-900 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold">
                            5
                        </div>
                    </div>
                    <div class="ml-3 flex-1">
                        <div class="font-bold text-white mb-1">Inventory Turnover</div>
                        <div class="text-xs text-white/70 leading-relaxed">
                            Rasio perputaran stok per kategori: tercepat & terlambat
                        </div>
                    </div>
                </a>

                <!-- Divider -->
                <div class="border-t border-white/10 my-4"></div>

                <!-- Additional Menu -->
                <div class="px-3 mb-2">
                    <p class="text-xs font-bold text-white/50 uppercase tracking-wider">Quick Access</p>
                </div>

                <a href="{{ route('dashboard.market-basket') }}"
                   class="flex items-center px-4 py-2.5 rounded-xl text-sm transition hover:bg-white/10">
                    <svg class="w-4 h-4 mr-3 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span class="text-white/90 font-medium">Dashboard Overview</span>
                </a>
            </nav>

            <!-- Footer - Fixed at bottom -->
            <div class="shrink-0 px-4 py-6 border-t border-white/10">
                <div class="flex items-center space-x-3 bg-white/10 rounded-lg px-4 py-3">
                    <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center font-bold">
                        {{ strtoupper(substr(session('user', 'U'), 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold">{{ session('user', 'User') }}</p>
                        <p class="text-xs text-white/70">Analytics Access</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-4">
                    @csrf
                    <button type="submit" class="w-full bg-red-500/90 hover:bg-red-600 px-4 py-2 rounded-lg text-sm font-semibold transition shadow-lg">
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Mobile overlay -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-black/40 z-30 hidden md:hidden"></div>

        <!-- Mobile top bar -->
        <div class="md:hidden fixed top-0 inset-x-0 z-30 bg-white shadow">
            <div class="flex items-center justify-between px-4 py-3">
                <div class="flex items-center space-x-3">
                    <button id="sidebarOpen" class="p-2 rounded-lg border border-gray-200 text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <div>
                        <p class="text-xs text-gray-500">AdventureWorks</p>
                        <p class="text-sm font-semibold">Dashboard</p>
                    </div>
                </div>
                <span class="text-sm text-gray-600">👤 {{ session('user', 'User') }}</span>
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1 flex flex-col md:ml-72">
            <main class="py-6 px-4 md:px-10 md:py-10 w-full mt-14 md:mt-0">
                <div class="max-w-7xl mx-auto">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- OLAP Mondrian Info Modal -->
    <div id="olapModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto transform transition-all">
            <div class="sticky top-0 bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-5 rounded-t-2xl">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-2xl font-bold mb-1">OLAP Mondrian Engine</h2>
                        <p class="text-indigo-100 text-sm">Multidimensional Analysis System</p>
                    </div>
                    <button onclick="closeOlapModal()" class="text-white hover:bg-white/20 rounded-lg p-2 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="p-6 space-y-6">
                <!-- System Info -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-5 border border-indigo-200">
                    <div class="flex items-start space-x-3">
                        <div class="bg-indigo-500 rounded-lg p-2">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2a8 8 0 100 16 8 8 0 000-16zM9 9a1 1 0 112 0v4a1 1 0 11-2 0V9zm1-4a1 1 0 100 2 1 1 0 000-2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 mb-1">Tentang Sistem OLAP Ini</h3>
                            <p class="text-gray-700 text-sm leading-relaxed">
                                Sistem ini menggunakan konsep <strong>OLAP (Online Analytical Processing)</strong> berbasis Mondrian 
                                untuk analisis multidimensional data warehouse AdventureWorks. Mendukung operasi Roll-up, Drill-down, 
                                Slice, dan Dice untuk eksplorasi data yang mendalam.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- OLAP Operations -->
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                        </svg>
                        OLAP Operations
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Drill-down -->
                        <div class="bg-white border-2 border-green-200 rounded-lg p-4 hover:shadow-lg transition">
                            <div class="flex items-center space-x-2 mb-2">
                                <div class="bg-green-500 text-white rounded p-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                                <h4 class="font-bold text-gray-900">Drill-Down</h4>
                            </div>
                            <p class="text-sm text-gray-600">Navigasi dari ringkasan ke detail. Contoh: Territory → Salesperson → Monthly Sales</p>
                        </div>

                        <!-- Roll-up -->
                        <div class="bg-white border-2 border-blue-200 rounded-lg p-4 hover:shadow-lg transition">
                            <div class="flex items-center space-x-2 mb-2">
                                <div class="bg-blue-500 text-white rounded p-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                    </svg>
                                </div>
                                <h4 class="font-bold text-gray-900">Roll-Up</h4>
                            </div>
                            <p class="text-sm text-gray-600">Agregasi dari detail ke ringkasan. Contoh: Daily → Monthly → Yearly</p>
                        </div>

                        <!-- Slice -->
                        <div class="bg-white border-2 border-purple-200 rounded-lg p-4 hover:shadow-lg transition">
                            <div class="flex items-center space-x-2 mb-2">
                                <div class="bg-purple-500 text-white rounded p-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                    </svg>
                                </div>
                                <h4 class="font-bold text-gray-900">Slice</h4>
                            </div>
                            <p class="text-sm text-gray-600">Filter berdasarkan dimensi tertentu. Contoh: Data tahun 2024 saja</p>
                        </div>

                        <!-- Dice -->
                        <div class="bg-white border-2 border-orange-200 rounded-lg p-4 hover:shadow-lg transition">
                            <div class="flex items-center space-x-2 mb-2">
                                <div class="bg-orange-500 text-white rounded p-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5z"/>
                                    </svg>
                                </div>
                                <h4 class="font-bold text-gray-900">Dice</h4>
                            </div>
                            <p class="text-sm text-gray-600">Filter multi-dimensi. Contoh: Territory = 'Canada' AND Year = 2024</p>
                        </div>
                    </div>
                </div>

                <!-- Dimensions -->
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm3 5a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1z" clip-rule="evenodd"/>
                        </svg>
                        Data Warehouse Dimensions
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <div class="bg-gradient-to-br from-indigo-50 to-blue-50 rounded-lg p-3 border border-indigo-200">
                            <div class="text-indigo-600 font-bold text-sm mb-1">📅 DimDate</div>
                            <div class="text-xs text-gray-600">Time dimension</div>
                        </div>
                        <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg p-3 border border-purple-200">
                            <div class="text-purple-600 font-bold text-sm mb-1">📦 DimProduct</div>
                            <div class="text-xs text-gray-600">Product hierarchy</div>
                        </div>
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-3 border border-green-200">
                            <div class="text-green-600 font-bold text-sm mb-1">👥 DimCustomer</div>
                            <div class="text-xs text-gray-600">Customer data</div>
                        </div>
                        <div class="bg-gradient-to-br from-orange-50 to-amber-50 rounded-lg p-3 border border-orange-200">
                            <div class="text-orange-600 font-bold text-sm mb-1">🌍 DimGeography</div>
                            <div class="text-xs text-gray-600">Territory/Region</div>
                        </div>
                        <div class="bg-gradient-to-br from-blue-50 to-cyan-50 rounded-lg p-3 border border-blue-200">
                            <div class="text-blue-600 font-bold text-sm mb-1">💼 DimSalesperson</div>
                            <div class="text-xs text-gray-600">Sales team</div>
                        </div>
                        <div class="bg-gradient-to-br from-red-50 to-rose-50 rounded-lg p-3 border border-red-200">
                            <div class="text-red-600 font-bold text-sm mb-1">💰 FactSales</div>
                            <div class="text-xs text-gray-600">Fact table</div>
                        </div>
                    </div>
                </div>

                <!-- Features -->
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Key Features
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm text-gray-700">Interactive drill-down navigation</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm text-gray-700">Star schema data warehouse</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm text-gray-700">Optimized ETL pipeline</span>
                        </div>
                        <div class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm text-gray-700">Real-time analytics queries</span>
                        </div>
                    </div>
                </div>

                <!-- Close button -->
                <div class="pt-4 border-t">
                    <button onclick="closeOlapModal()" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition shadow-lg">
                        Got it! Let's analyze data
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // OLAP Modal functions
        function openOlapModal() {
            document.getElementById('olapModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closeOlapModal() {
            document.getElementById('olapModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        // Close modal on ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeOlapModal();
        });
        
        // Close modal when clicking outside
        document.getElementById('olapModal')?.addEventListener('click', (e) => {
            if (e.target.id === 'olapModal') closeOlapModal();
        });

        // Sidebar toggle functions
        const sidebar = document.getElementById('sidebar');
        const openBtn = document.getElementById('sidebarOpen');
        const overlay = document.getElementById('sidebarOverlay');

        const closeSidebar = () => {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        };

        openBtn?.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        });

        overlay?.addEventListener('click', closeSidebar);

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.add('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
