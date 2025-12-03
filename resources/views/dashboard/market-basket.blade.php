@extends('layouts.dashboard')

@section('title', 'Market Basket Analysis - AdventureWorks DW')

@push('styles')
<style>
    .filter-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .filter-card:hover { transform: translateY(-2px); box-shadow: 0 12px 24px -8px rgba(0,0,0,0.15); }
    .loading-spinner { border: 3px solid #f3f4f6; border-top: 3px solid #3b82f6; border-radius: 50%; width: 36px; height: 36px; animation: spin 1s linear infinite; }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    .fade-in { animation: fadeIn 0.5s ease-in; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>
@endpush

@section('content')
    <div class="mb-8 fade-in">
        <div class="flex items-center mb-4">
            <div class="bg-gradient-to-r from-amber-400 to-orange-500 text-white w-12 h-12 rounded-full flex items-center justify-center mr-4 text-xl font-bold shadow-lg">
                1
            </div>
            <div>
                <h2 class="text-4xl font-bold text-gray-800">Market Basket Analysis</h2>
                <p class="text-gray-600 font-medium mt-1">Produk yang sering dibeli bersamaan - landasan bundling & cross-selling</p>
            </div>
        </div>
    </div>

    <!-- Interactive Filters -->
    <div class="bg-white rounded-2xl shadow-lg p-6 mb-8 filter-card fade-in">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
            </svg>
            Interactive Filters
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Year Filter -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">📅 Tahun Order</label>
                <select id="dateRangeFilter" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
                    <option value="all">Semua Tahun</option>
                    @foreach($availableYears as $year)
                        <option value="{{ $year->Year }}">{{ $year->Year }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Top N Products Filter -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">📊 Show Top</label>
                <select id="topNFilter" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
                    <option value="10">Top 10 Products</option>
                    <option value="15" selected>Top 15 Products</option>
                    <option value="20">Top 20 Products</option>
                    <option value="30">Top 30 Products</option>
                </select>
            </div>
        </div>
        <div class="mt-4 flex flex-wrap items-center space-x-3">
            <button onclick="applyFilters()" class="bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white px-6 py-2.5 rounded-lg font-semibold transition shadow-lg hover:shadow-xl transform hover:scale-105">
                Apply Filters
            </button>
            <button onclick="resetFilters()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2.5 rounded-lg font-semibold transition">
                Reset
            </button>
            <div id="loadingIndicator" class="hidden items-center ml-4">
                <div class="loading-spinner"></div>
                <span class="ml-3 text-gray-600 font-medium">Loading data...</span>
            </div>
        </div>
    </div>

    <!-- Market Basket Analysis Content -->
    <div class="bg-white rounded-2xl shadow-xl p-8 filter-card fade-in hover:shadow-2xl">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Bundling Opportunities</h3>
                <p class="text-gray-600 leading-relaxed">Analisis produk dengan frekuensi co-purchase tertinggi untuk strategi bundling</p>
            </div>
            <div class="bg-amber-50 px-4 py-2 rounded-lg">
                <span class="text-amber-700 font-bold text-lg" id="productCount">{{ min(count($bundlingProducts), 15) }}</span>
                <span class="text-amber-600 text-sm ml-1">products</span>
            </div>
        </div>
        
        <div class="overflow-x-auto bg-gray-50 rounded-xl p-2">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gradient-to-r from-amber-500 to-orange-600 text-white">
                        <th class="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider rounded-tl-lg">Product ID</th>
                        <th class="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">Product Name</th>
                        <th class="px-6 py-4 text-right text-sm font-bold uppercase tracking-wider rounded-tr-lg">Co-Purchase Orders</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="bundlingTableBody">
                    @foreach(array_slice($bundlingProducts, 0, 15) as $product)
                        <tr class="hover:bg-amber-50 transition duration-200 cursor-pointer">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                    {{ $product->ProductID }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-semibold text-gray-900">{{ $product->Name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-lg font-bold text-orange-600">{{ number_format($product->OrdersWithOtherProducts) }}</span>
                                <span class="text-xs text-gray-500 ml-1">orders</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        

        
    </div>

    <!-- Product Pair Co-occurrence (still Q1) -->
    <div class="bg-white rounded-2xl shadow-xl p-8 filter-card fade-in hover:shadow-2xl mt-8">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Top Product Pairs</h3>
                <p class="text-gray-600 leading-relaxed">Pasangan produk yang paling sering muncul dalam satu order (basis paket bundling)</p>
            </div>
            <div class="bg-amber-50 px-4 py-2 rounded-lg">
                <span class="text-amber-700 font-bold text-lg" id="pairCount">0</span>
                <span class="text-amber-600 text-sm ml-1">pairs</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Kategori A</label>
                <select id="pairCategoryFilter" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
                    <option value="all">Semua Kategori</option>
                    @foreach(collect($productPairs)->pluck('ProductA_CategoryID', 'ProductA_CategoryName')->unique() as $catName => $catId)
                        <option value="{{ $catId }}">{{ $catName ?? 'Uncategorized' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Min Co-purchase Orders</label>
                <select id="pairMinOrders" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
                    <option value="5">5+</option>
                    <option value="10">10+</option>
                    <option value="15">15+</option>
                    <option value="20">20+</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Top N</label>
                <select id="pairTopN" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
                    <option value="10">Top 10</option>
                    <option value="20" selected>Top 20</option>
                    <option value="30">Top 30</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto bg-gray-50 rounded-xl p-2">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gradient-to-r from-amber-500 to-orange-600 text-white">
                        <th class="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider rounded-tl-lg">Product A</th>
                        <th class="px-6 py-4 text-left text-sm font-bold uppercase tracking-wider">Product B</th>
                        <th class="px-6 py-4 text-right text-sm font-bold uppercase tracking-wider rounded-tr-lg">Co-Purchase Orders</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="pairTableBody">
                </tbody>
            </table>
        </div>

        <div class="mt-8 bg-gray-50 rounded-xl p-6">
            <canvas id="bundlingChart" height="70"></canvas>
        </div>

        <!-- Insights Section -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 p-6 rounded-xl border-2 border-amber-200">
                <div class="text-3xl mb-2">🛒</div>
                <div class="text-2xl font-bold text-amber-700 mb-1">{{ number_format(collect($bundlingProducts)->first()->OrdersWithOtherProducts ?? 0) }}</div>
                <div class="text-sm text-gray-600 font-medium">Highest Co-Purchase Orders</div>
            </div>
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 p-6 rounded-xl border-2 border-amber-200">
                <div class="text-3xl mb-2">📦</div>
                <div class="text-2xl font-bold text-amber-700 mb-1">{{ count($bundlingProducts) }}</div>
                <div class="text-sm text-gray-600 font-medium">Total Products Analyzed</div>
            </div>
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 p-6 rounded-xl border-2 border-amber-200">
                <div class="text-3xl mb-2">💡</div>
                <div class="text-2xl font-bold text-amber-700 mb-1">{{ number_format(collect($bundlingProducts)->avg('OrdersWithOtherProducts') ?? 0, 0) }}</div>
                <div class="text-sm text-gray-600 font-medium">Average Co-Purchases</div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const bundlingDataAll = @json($bundlingProducts);
    const bundlingDataYearly = @json($bundlingProductsYearly);
    const pairDataAll = @json($productPairs);

    const bundlingChartCtx = document.getElementById('bundlingChart');

    const bundlingChart = new Chart(bundlingChartCtx, {
        type: 'bar',
        data: {
            labels: bundlingDataAll.slice(0, 15).map(p => p.Name.substring(0, 35)),
            datasets: [{
                label: 'Co-Purchase Orders',
                data: bundlingDataAll.slice(0, 15).map(p => p.OrdersWithOtherProducts),
                backgroundColor: 'rgba(251, 191, 36, 0.8)',
                borderColor: 'rgba(251, 191, 36, 1)',
                borderWidth: 2,
                borderRadius: 8,
                hoverBackgroundColor: 'rgba(245, 158, 11, 0.9)'
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
                x: { grid: { display: false }, ticks: { font: { size: 11 } } }
            }
        }
    });

    function applyFilters() {
        const year = document.getElementById('dateRangeFilter').value;
        const topN = parseInt(document.getElementById('topNFilter').value);
        
        document.getElementById('loadingIndicator').classList.remove('hidden');
        document.getElementById('loadingIndicator').classList.add('flex');

        const sourceData = year === 'all' ? bundlingDataAll : (bundlingDataYearly[year] || []);
        const filteredData = sourceData.slice(0, topN);

        updateTable(filteredData);
        updateChart(filteredData);

        setTimeout(() => {
            document.getElementById('loadingIndicator').classList.add('hidden');
            document.getElementById('loadingIndicator').classList.remove('flex');
        }, 500);
    }

    function updateTable(data) {
        const tbody = document.getElementById('bundlingTableBody');
        tbody.innerHTML = data.map(product => `
            <tr class="hover:bg-amber-50 transition duration-200 cursor-pointer">
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                        ${product.ProductID}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm font-semibold text-gray-900">${product.Name}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                    <span class="text-lg font-bold text-orange-600">${Number(product.OrdersWithOtherProducts).toLocaleString()}</span>
                    <span class="text-xs text-gray-500 ml-1">orders</span>
                </td>
            </tr>
        `).join('');
        
        document.getElementById('productCount').textContent = data.length;
    }

    function updateChart(data) {
        bundlingChart.data.labels = data.map(p => p.Name.substring(0, 35));
        bundlingChart.data.datasets[0].data = data.map(p => p.OrdersWithOtherProducts);
        bundlingChart.update();
    }

    function resetFilters() {
        document.getElementById('dateRangeFilter').value = 'all';
        document.getElementById('topNFilter').value = '15';
        applyFilters();
    }

    // --- Product Pair rendering ---
    function renderPairs() {
        const category = document.getElementById('pairCategoryFilter').value;
        const minOrders = parseInt(document.getElementById('pairMinOrders').value, 10);
        const topN = parseInt(document.getElementById('pairTopN').value, 10);

        let pairs = [...pairDataAll].filter(p => p.CooccurrenceOrders >= minOrders);
        if (category !== 'all') {
            pairs = pairs.filter(p => String(p.ProductA_CategoryID) === String(category));
        }

        pairs = pairs
            .sort((a, b) => b.CooccurrenceOrders - a.CooccurrenceOrders)
            .slice(0, topN);

        const tbody = document.getElementById('pairTableBody');
        tbody.innerHTML = pairs.map(pair => `
            <tr class="hover:bg-amber-50 transition duration-200">
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-bold bg-amber-100 text-amber-800 mr-2">${pair.ProductA_ID}</span>
                        <div>
                            <div class="text-sm font-semibold text-gray-900">${pair.ProductA_Name}</div>
                            <div class="text-xs text-gray-500">${pair.ProductA_CategoryName || 'Uncategorized'}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-bold bg-orange-100 text-orange-800 mr-2">${pair.ProductB_ID}</span>
                        <span class="text-sm font-semibold text-gray-900">${pair.ProductB_Name}</span>
                    </div>
                </td>
                <td class="px-6 py-4 text-right">
                    <span class="text-lg font-bold text-orange-600">${pair.CooccurrenceOrders.toLocaleString()}</span>
                    <span class="text-xs text-gray-500 ml-1">orders</span>
                </td>
            </tr>
        `).join('') || `<tr><td colspan="3" class="px-6 py-4 text-sm text-gray-600">No pairs match the filters.</td></tr>`;

        document.getElementById('pairCount').textContent = pairs.length;
    }

    document.addEventListener('DOMContentLoaded', () => {
        renderPairs();
        document.getElementById('pairCategoryFilter').addEventListener('change', renderPairs);
        document.getElementById('pairMinOrders').addEventListener('change', renderPairs);
        document.getElementById('pairTopN').addEventListener('change', renderPairs);
    });
</script>
@endpush
