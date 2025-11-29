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
            <div class="shrink-0 px-6 py-6 flex items-center space-x-3 border-b border-white/10">
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

            <!-- Navigation - Scrollable -->
            <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-2">
                <a href="{{ route('dashboard.sales-overview') }}"
                   class="flex items-center px-4 py-3 rounded-xl font-semibold text-sm transition {{ request()->routeIs('dashboard.sales-overview') ? 'bg-white/20 shadow-lg' : 'hover:bg-white/10' }}">
                    <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 7h16M4 12h10M4 17h6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Sales Overview
                </a>
                <a href="{{ route('dashboard.product-analysis') }}"
                   class="flex items-center px-4 py-3 rounded-xl font-semibold text-sm transition {{ request()->routeIs('dashboard.product-analysis') ? 'bg-white/20 shadow-lg' : 'hover:bg-white/10' }}">
                    <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M4 7.5L12 3l8 4.5v9L12 21l-8-4.5v-9z" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 12l8-4.5M12 12v9M12 12L4 7.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Product Analysis
                </a>
                <a href="{{ route('dashboard.customer-geo') }}"
                   class="flex items-center px-4 py-3 rounded-xl font-semibold text-sm transition {{ request()->routeIs('dashboard.customer-geo') ? 'bg-white/20 shadow-lg' : 'hover:bg-white/10' }}">
                    <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M12 21a9 9 0 100-18 9 9 0 000 18z" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M3 12h18M12 3a15 15 0 010 18" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Customer & Geo
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

    <script>
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
