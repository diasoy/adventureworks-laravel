@extends('layouts.dashboard')

@section('title', 'Product Analysis - AdventureWorks DW')

@push('styles')
<style>
    .filter-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .filter-card:hover { transform: translateY(-2px); box-shadow: 0 12px 24px -8px rgba(0,0,0,0.15); }
</style>
@endpush

@section('content')
    <div class="mb-8 fade-in">
        <h2 class="text-4xl font-bold text-gray-800 mb-2">Product Analysis Dashboard</h2>
        <p class="text-gray-600 font-medium">Deep dive ke performa produk & relasi pembelian</p>
    </div>

    <!-- Interactive Filters -->
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-8 filter-card fade-in">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 text-white mr-3">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M4 7h16M8 12h8M10 17h4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            Interactive Filters
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                <select id="categoryFilter" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                    <option value="all">All Categories</option>
                    @foreach($inventoryTurnover as $category)
                        <option value="{{ $category->ProductCategoryID }}">{{ $category->CategoryName ?? 'Uncategorized' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Min Co-purchases</label>
                <select id="minCooccurrence" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                    <option value="5">5+ Orders</option>
                    <option value="10">10+ Orders</option>
                    <option value="15">15+ Orders</option>
                    <option value="20">20+ Orders</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Show Top</label>
                <select id="topNPairs" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                    <option value="20" selected>Top 20 Pairs</option>
                    <option value="10">Top 10 Pairs</option>
                    <option value="30">Top 30 Pairs</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Sort By</label>
                <select id="sortBy" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                    <option value="cooccurrence">Co-occurrence</option>
                    <option value="alphabetical">Alphabetical</option>
                </select>
            </div>
        </div>
        <div class="mt-4 flex space-x-3">
            <button onclick="applyFilters()" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2.5 rounded-lg font-semibold transition shadow-lg hover:shadow-xl transform hover:scale-105">Apply Filters</button>
            <button onclick="resetFilters()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2.5 rounded-lg font-semibold transition">Reset</button>
        </div>
    </div>

    <!-- Product Pairs -->
    <div class="bg-white rounded-2xl shadow-xl p-8 mb-8 filter-card fade-in hover:shadow-2xl">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2 flex items-center">
                    <span class="inline-flex items-center justify-center w-11 h-11 rounded-2xl bg-gradient-to-r from-purple-500 to-pink-500 text-white shadow mr-3">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 6l7 4-7 4-7-4 7-4z" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M5 10v4l7 4 7-4v-4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    Product Pair Analysis
                </h3>
                <p class="text-gray-600 leading-relaxed">Pasangan produk yang sering dibeli bersama (basis rekomendasi bundling)</p>
            </div>
            <div class="bg-purple-50 px-4 py-2 rounded-lg">
                <span class="text-purple-700 font-bold text-lg" id="pairCount">{{ min(count($productPairs), 20) }}</span>
                <span class="text-purple-600 text-sm ml-1">pairs</span>
            </div>
        </div>
        <div class="overflow-x-auto bg-gray-50 rounded-xl p-2">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gradient-to-r from-purple-600 to-pink-600 text-white">
                        <th class="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider rounded-tl-lg">Product A</th>
                        <th class="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">Product B</th>
                        <th class="px-6 py-4 text-right text-sm font-bold uppercase tracking-wider rounded-tr-lg">Co-purchases</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="pairsTableBody">
                    @foreach(array_slice($productPairs, 0, 20) as $pair)
                        <tr class="hover:bg-purple-50 transition duration-200">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-bold bg-purple-100 text-purple-800 mr-2">{{ $pair->ProductA_ID }}</span>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">{{ $pair->ProductA_Name }}</div>
                                        <div class="text-xs text-gray-500">{{ $pair->ProductA_CategoryName ?? 'Uncategorized' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-bold bg-pink-100 text-pink-800 mr-2">{{ $pair->ProductB_ID }}</span>
                                    <span class="text-sm font-semibold text-gray-900">{{ $pair->ProductB_Name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-lg font-bold text-purple-600">{{ number_format($pair->CooccurrenceOrders) }}</span>
                                <span class="text-xs text-gray-500 ml-1">orders</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Inventory Turnover -->
    <div class="bg-white rounded-2xl shadow-xl p-8 filter-card fade-in hover:shadow-2xl">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2 flex items-center">
                    <span class="inline-flex items-center justify-center w-11 h-11 rounded-2xl bg-gradient-to-r from-orange-500 to-red-500 text-white shadow mr-3">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M4 7h16M6 12h12M8 17h8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    Inventory Turnover by Category
                </h3>
                <p class="text-gray-600 leading-relaxed">Total units terjual per kategori - semakin tinggi semakin cepat rotasi stok</p>
            </div>
            <div class="bg-orange-50 px-4 py-2 rounded-lg">
                <span class="text-orange-700 font-bold text-lg">{{ count($inventoryTurnover) }}</span>
                <span class="text-orange-600 text-sm ml-1">categories</span>
            </div>
        </div>
        <div class="overflow-x-auto bg-gray-50 rounded-xl p-2 mb-8">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gradient-to-r from-orange-600 to-red-600 text-white">
                        <th class="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider rounded-tl-lg">Category ID</th>
                        <th class="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">Category Name</th>
                        <th class="px-6 py-4 text-right text-sm font-bold uppercase tracking-wider rounded-tr-lg">Total Units Sold</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($inventoryTurnover as $category)
                        <tr class="hover:bg-orange-50 transition duration-200">
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-orange-100 text-orange-800">{{ $category->ProductCategoryID }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-semibold text-gray-900">{{ $category->CategoryName ?? 'Uncategorized' }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-lg font-bold text-orange-600">{{ number_format($category->TotalUnitsSold) }}</span>
                                <span class="text-xs text-gray-500 ml-1">units</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="bg-gray-50 rounded-xl p-6">
            <canvas id="categoryChart" height="70"></canvas>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const categoryData = @json($inventoryTurnover);
    const productPairsData = @json($productPairs);

    new Chart(document.getElementById('categoryChart'), {
        type: 'bar',
        data: {
            labels: categoryData.map(c => c.CategoryName || 'Uncategorized'),
            datasets: [{
                label: 'Total Units Sold',
                data: categoryData.map(c => c.TotalUnitsSold),
                backgroundColor: ['rgba(249, 115, 22, 0.8)', 'rgba(234, 88, 12, 0.8)', 'rgba(194, 65, 12, 0.8)', 'rgba(251, 146, 60, 0.8)', 'rgba(253, 186, 116, 0.8)'],
                borderColor: 'rgba(234, 88, 12, 1)',
                borderWidth: 2,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true, position: 'top', labels: { font: { size: 14, weight: 'bold' }, padding: 15 } },
                tooltip: { backgroundColor: 'rgba(0, 0, 0, 0.8)', padding: 12, cornerRadius: 8 }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0, 0, 0, 0.05)' } },
                x: { grid: { display: false } }
            }
        }
    });

    function applyFilters() {
        const category = document.getElementById('categoryFilter').value;
        const minCooccurrence = parseInt(document.getElementById('minCooccurrence').value, 10);
        const topN = parseInt(document.getElementById('topNPairs').value, 10);
        const sortBy = document.getElementById('sortBy').value;

        let filtered = [...productPairsData].filter(p => p.CooccurrenceOrders >= minCooccurrence);
        if (category !== 'all') {
            filtered = filtered.filter(p => String(p.ProductA_CategoryID) === String(category));
        }

        if (sortBy === 'alphabetical') {
            filtered.sort((a, b) => a.ProductA_Name.localeCompare(b.ProductA_Name));
        } else {
            filtered.sort((a, b) => b.CooccurrenceOrders - a.CooccurrenceOrders);
        }

        filtered = filtered.slice(0, topN);
        renderPairs(filtered);
    }

    function renderPairs(pairs) {
        const rows = pairs.map(pair => `
            <tr class="hover:bg-purple-50 transition duration-200">
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-bold bg-purple-100 text-purple-800 mr-2">${pair.ProductA_ID}</span>
                        <div>
                            <div class="text-sm font-semibold text-gray-900">${pair.ProductA_Name}</div>
                            <div class="text-xs text-gray-500">${pair.ProductA_CategoryName || 'Uncategorized'}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-bold bg-pink-100 text-pink-800 mr-2">${pair.ProductB_ID}</span>
                        <span class="text-sm font-semibold text-gray-900">${pair.ProductB_Name}</span>
                    </div>
                </td>
                <td class="px-6 py-4 text-right">
                    <span class="text-lg font-bold text-purple-600">${pair.CooccurrenceOrders.toLocaleString()}</span>
                    <span class="text-xs text-gray-500 ml-1">orders</span>
                </td>
            </tr>
        `).join('');

        document.getElementById('pairsTableBody').innerHTML = rows || `<tr><td class="px-6 py-4 text-sm text-gray-600" colspan="3">No pairs match the filters.</td></tr>`;
        document.getElementById('pairCount').textContent = pairs.length;
    }

    function resetFilters() {
        document.getElementById('categoryFilter').value = 'all';
        document.getElementById('minCooccurrence').value = '5';
        document.getElementById('topNPairs').value = '20';
        document.getElementById('sortBy').value = 'cooccurrence';
        applyFilters();
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.fade-in').forEach((el, index) => el.style.animationDelay = `${index * 0.05}s`);
        applyFilters();
    });
</script>
@endpush
