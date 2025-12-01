@extends('layouts.dashboard')

@section('title', 'Inventory Turnover - AdventureWorks DW')

@push('styles')
<style>
    .filter-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .filter-card:hover { transform: translateY(-2px); box-shadow: 0 12px 24px -8px rgba(0,0,0,0.15); }
    .fade-in { animation: fadeIn 0.5s ease-in; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endpush

@section('content')
    <div class="mb-8 fade-in">
        <div class="flex items-center mb-4">
            <div class="bg-gradient-to-r from-pink-500 to-rose-600 text-white w-12 h-12 rounded-full flex items-center justify-center mr-4 text-xl font-bold shadow-lg">
                5
            </div>
            <div>
                <h2 class="text-4xl font-bold text-gray-800">Inventory Turnover Analysis</h2>
                <p class="text-gray-600 font-medium mt-1">Rasio perputaran stok per kategori - identifikasi fast & slow movers</p>
            </div>
        </div>
    </div>

    <!-- Interactive Filters -->
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-8 filter-card fade-in">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
            </svg>
            Interactive Filters
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">📦 Category</label>
                <select id="categoryFilter" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent transition">
                    <option value="all">All Categories</option>
                    @foreach($inventoryTurnover as $category)
                        <option value="{{ $category->ProductCategoryID }}">{{ $category->CategoryName ?? 'Uncategorized' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">📊 Sort By</label>
                <select id="sortBy" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent transition">
                    <option value="units_desc">Highest Units Sold</option>
                    <option value="units_asc">Lowest Units Sold</option>
                    <option value="name">Category Name (A-Z)</option>
                </select>
            </div>
        </div>
        <div class="mt-4 flex flex-wrap items-center space-x-3">
            <button onclick="applyFilters()" class="bg-gradient-to-r from-pink-500 to-rose-600 hover:from-pink-600 hover:to-rose-700 text-white px-6 py-2.5 rounded-lg font-semibold transition shadow-lg hover:shadow-xl transform hover:scale-105">
                Apply Filters
            </button>
            <button onclick="resetFilters()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2.5 rounded-lg font-semibold transition">
                Reset
            </button>
        </div>
    </div>

    <!-- Inventory Turnover Content -->
    <div class="bg-white rounded-2xl shadow-xl p-8 filter-card fade-in hover:shadow-2xl">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Inventory Turnover by Category</h3>
                <p class="text-gray-600 leading-relaxed">
                    Total units terjual per kategori - semakin tinggi semakin cepat rotasi stok
                </p>
            </div>
            <div class="bg-pink-50 px-4 py-2 rounded-lg">
                <span class="text-pink-700 font-bold text-lg" id="categoryCount">{{ count($inventoryTurnover) }}</span>
                <span class="text-pink-600 text-sm ml-1">categories</span>
            </div>
        </div>
        
        <div class="overflow-x-auto bg-gray-50 rounded-xl p-2">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gradient-to-r from-pink-600 to-rose-600 text-white">
                        <th class="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider rounded-tl-lg">Rank</th>
                        <th class="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">Category ID</th>
                        <th class="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">Category Name</th>
                        <th class="px-6 py-4 text-right text-sm font-bold uppercase tracking-wider">Total Units Sold</th>
                        <th class="px-6 py-4 text-center text-sm font-bold uppercase tracking-wider rounded-tr-lg">Performance</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="inventoryTableBody">
                    @foreach($inventoryTurnover as $index => $category)
                        @php
                            $totalUnits = $category->TotalUnitsSold;
                            $avgUnits = collect($inventoryTurnover)->avg('TotalUnitsSold');
                            $performance = $totalUnits > $avgUnits ? 'Fast Mover' : 'Slow Mover';
                            $badgeColor = $totalUnits > $avgUnits ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800';
                        @endphp
                        <tr class="hover:bg-pink-50 transition duration-200">
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center">
                                    <div class="bg-gradient-to-br from-pink-500 to-rose-500 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">
                                        {{ $index + 1 }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-pink-100 text-pink-800">
                                    {{ $category->ProductCategoryID }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-semibold text-gray-900">{{ $category->CategoryName ?? 'Uncategorized' }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end">
                                    <span class="text-lg font-bold text-rose-600">{{ number_format($category->TotalUnitsSold) }}</span>
                                    <span class="text-xs text-gray-500 ml-1">units</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $badgeColor }}">
                                    {{ $performance }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-8 bg-gray-50 rounded-xl p-6">
            <h4 class="text-lg font-bold text-gray-800 mb-4">Category Performance Chart</h4>
            <canvas id="categoryChart" height="70"></canvas>
        </div>

        <!-- Insights Section -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gradient-to-br from-pink-50 to-rose-50 p-6 rounded-xl border-2 border-pink-200">
                <div class="text-3xl mb-2">🚀</div>
                <div class="text-2xl font-bold text-pink-700 mb-1">{{ number_format(collect($inventoryTurnover)->max('TotalUnitsSold') ?? 0) }}</div>
                <div class="text-sm text-gray-600 font-medium">Highest Category Sales</div>
            </div>
            <div class="bg-gradient-to-br from-pink-50 to-rose-50 p-6 rounded-xl border-2 border-pink-200">
                <div class="text-3xl mb-2">📊</div>
                <div class="text-2xl font-bold text-pink-700 mb-1">{{ number_format(collect($inventoryTurnover)->avg('TotalUnitsSold') ?? 0, 0) }}</div>
                <div class="text-sm text-gray-600 font-medium">Average Units Per Category</div>
            </div>
            <div class="bg-gradient-to-br from-pink-50 to-rose-50 p-6 rounded-xl border-2 border-pink-200">
                <div class="text-3xl mb-2">📦</div>
                <div class="text-2xl font-bold text-pink-700 mb-1">{{ number_format(collect($inventoryTurnover)->sum('TotalUnitsSold') ?? 0) }}</div>
                <div class="text-sm text-gray-600 font-medium">Total Units Sold</div>
            </div>
        </div>

        <!-- Fast vs Slow Movers -->
        @php
            $avgUnits = collect($inventoryTurnover)->avg('TotalUnitsSold');
            $fastMovers = collect($inventoryTurnover)->filter(fn($c) => $c->TotalUnitsSold > $avgUnits);
            $slowMovers = collect($inventoryTurnover)->filter(fn($c) => $c->TotalUnitsSold <= $avgUnits);
        @endphp
        
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Fast Movers -->
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 p-6 rounded-xl border-2 border-green-300">
                <h4 class="text-lg font-bold text-gray-800 mb-3 flex items-center">
                    <span class="text-2xl mr-2">🚀</span>
                    Fast Movers ({{ $fastMovers->count() }})
                </h4>
                <div class="space-y-2">
                    @foreach($fastMovers->take(5) as $category)
                        <div class="flex justify-between items-center bg-white p-3 rounded-lg shadow-sm">
                            <span class="text-sm font-semibold text-gray-800">{{ $category->CategoryName ?? 'Uncategorized' }}</span>
                            <span class="text-sm font-bold text-green-600">{{ number_format($category->TotalUnitsSold) }} units</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Slow Movers -->
            <div class="bg-gradient-to-br from-orange-50 to-amber-50 p-6 rounded-xl border-2 border-orange-300">
                <h4 class="text-lg font-bold text-gray-800 mb-3 flex items-center">
                    <span class="text-2xl mr-2">🐌</span>
                    Slow Movers ({{ $slowMovers->count() }})
                </h4>
                <div class="space-y-2">
                    @foreach($slowMovers->take(5) as $category)
                        <div class="flex justify-between items-center bg-white p-3 rounded-lg shadow-sm">
                            <span class="text-sm font-semibold text-gray-800">{{ $category->CategoryName ?? 'Uncategorized' }}</span>
                            <span class="text-sm font-bold text-orange-600">{{ number_format($category->TotalUnitsSold) }} units</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Recommendations -->
        <div class="mt-8 bg-gradient-to-r from-pink-50 to-rose-50 border-l-4 border-pink-500 p-6 rounded-lg">
            <h4 class="text-lg font-bold text-gray-800 mb-3 flex items-center">
                <span class="text-2xl mr-2">💡</span>
                Inventory Management Insights
            </h4>
            <ul class="space-y-2 text-gray-700">
                <li class="flex items-start">
                    <span class="text-pink-500 mr-2">•</span>
                    <span><strong>Fast Movers:</strong> Pastikan stok selalu tersedia, pertimbangkan bulk purchasing untuk kategori dengan volume tinggi</span>
                </li>
                <li class="flex items-start">
                    <span class="text-pink-500 mr-2">•</span>
                    <span><strong>Slow Movers:</strong> Evaluasi pricing strategy atau promosi khusus untuk meningkatkan turnover</span>
                </li>
                <li class="flex items-start">
                    <span class="text-pink-500 mr-2">•</span>
                    <span><strong>Stock Optimization:</strong> Alokasikan warehouse space berdasarkan velocity - prioritaskan fast movers</span>
                </li>
            </ul>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const categoryDataAll = @json($inventoryTurnover);
    let categoryData = [...categoryDataAll];

    const categoryChart = new Chart(document.getElementById('categoryChart'), {
        type: 'bar',
        data: {
            labels: categoryData.map(c => c.CategoryName || 'Uncategorized'),
            datasets: [{
                label: 'Total Units Sold',
                data: categoryData.map(c => c.TotalUnitsSold),
                backgroundColor: [
                    'rgba(236, 72, 153, 0.8)',
                    'rgba(219, 39, 119, 0.8)',
                    'rgba(190, 24, 93, 0.8)',
                    'rgba(244, 114, 182, 0.8)',
                    'rgba(251, 207, 232, 0.8)'
                ],
                borderColor: 'rgba(219, 39, 119, 1)',
                borderWidth: 2,
                borderRadius: 8,
                hoverBackgroundColor: 'rgba(190, 24, 93, 0.9)'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { 
                    display: true, 
                    position: 'top',
                    labels: { font: { size: 14, weight: 'bold' }, padding: 15 }
                },
                tooltip: { 
                    backgroundColor: 'rgba(0, 0, 0, 0.8)', 
                    padding: 12, 
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y.toLocaleString() + ' units';
                        }
                    }
                }
            },
            scales: {
                y: { 
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.05)' },
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    }
                },
                x: { 
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                }
            }
        }
    });

    function applyFilters() {
        const categoryId = document.getElementById('categoryFilter').value;
        const sortBy = document.getElementById('sortBy').value;

        let filteredData = [...categoryDataAll];

        // Filter by category
        if (categoryId !== 'all') {
            filteredData = filteredData.filter(c => c.ProductCategoryID == categoryId);
        }

        // Sort
        if (sortBy === 'units_desc') {
            filteredData.sort((a, b) => b.TotalUnitsSold - a.TotalUnitsSold);
        } else if (sortBy === 'units_asc') {
            filteredData.sort((a, b) => a.TotalUnitsSold - b.TotalUnitsSold);
        } else if (sortBy === 'name') {
            filteredData.sort((a, b) => (a.CategoryName || 'Uncategorized').localeCompare(b.CategoryName || 'Uncategorized'));
        }

        categoryData = filteredData;
        updateTable();
        updateChart();
    }

    function updateTable() {
        const tbody = document.getElementById('inventoryTableBody');
        const avgUnits = categoryData.reduce((sum, c) => sum + c.TotalUnitsSold, 0) / categoryData.length;

        tbody.innerHTML = categoryData.map((category, index) => {
            const performance = category.TotalUnitsSold > avgUnits ? 'Fast Mover' : 'Slow Mover';
            const badgeColor = category.TotalUnitsSold > avgUnits ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800';

            return `
                <tr class="hover:bg-pink-50 transition duration-200">
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center">
                            <div class="bg-gradient-to-br from-pink-500 to-rose-500 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm">
                                ${index + 1}
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-pink-100 text-pink-800">
                            ${category.ProductCategoryID}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-sm font-semibold text-gray-900">${category.CategoryName || 'Uncategorized'}</span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end">
                            <span class="text-lg font-bold text-rose-600">${Number(category.TotalUnitsSold).toLocaleString()}</span>
                            <span class="text-xs text-gray-500 ml-1">units</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold ${badgeColor}">
                            ${performance}
                        </span>
                    </td>
                </tr>
            `;
        }).join('');

        document.getElementById('categoryCount').textContent = categoryData.length;
    }

    function updateChart() {
        categoryChart.data.labels = categoryData.map(c => c.CategoryName || 'Uncategorized');
        categoryChart.data.datasets[0].data = categoryData.map(c => c.TotalUnitsSold);
        categoryChart.update();
    }

    function resetFilters() {
        document.getElementById('categoryFilter').value = 'all';
        document.getElementById('sortBy').value = 'units_desc';
        categoryData = [...categoryDataAll].sort((a, b) => b.TotalUnitsSold - a.TotalUnitsSold);
        updateTable();
        updateChart();
    }
</script>
@endpush
