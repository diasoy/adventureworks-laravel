@extends('layouts.dashboard')

@section('title', 'Customer & Geography - AdventureWorks DW')

@push('styles')
<style>
    .filter-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .filter-card:hover { transform: translateY(-2px); box-shadow: 0 12px 24px -8px rgba(0,0,0,0.15); }
</style>
@endpush

@section('content')
    <div class="mb-8 fade-in">
        <h2 class="text-4xl font-bold text-gray-800 mb-2">Customer & Geo Analysis</h2>
        <p class="text-gray-600 font-medium">Segmentasi pelanggan dan performa retensi salesperson</p>
    </div>

    <!-- Question 3: Customer Segments -->
    <div class="bg-white rounded-2xl shadow-md p-6 mb-8 filter-card">
        <h3 class="text-xl font-semibold mb-4">📊 Segmen Pelanggan High-Frequency, Low-Ticket</h3>
        <p class="text-gray-600 mb-4">Pelanggan dengan frekuensi pembelian tinggi namun nilai transaksi per order relatif kecil.</p>
        
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border-collapse">
                <thead class="bg-indigo-500 text-white">
                    <tr>
                        <th class="px-4 py-2 text-left">Customer ID</th>
                        <th class="px-4 py-2 text-right">Avg Orders/Year</th>
                        <th class="px-4 py-2 text-right">Avg Order Value</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customerSegments as $customer)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2">{{ $customer->CustomerID }}</td>
                            <td class="px-4 py-2 text-right font-semibold">{{ number_format($customer->AvgOrdersPerYear, 2) }}</td>
                            <td class="px-4 py-2 text-right">${{ number_format($customer->AvgOrderValueAcrossYears, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if(count($customerSegments) == 0)
            <div class="mt-4 p-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700">
                <p class="font-bold">Info:</p>
                <p>Tidak ada data pelanggan yang memenuhi kriteria high-frequency & low-ticket.</p>
            </div>
        @endif

        <div class="mt-6">
            <canvas id="customerChart" height="80"></canvas>
        </div>
    </div>

    <!-- Question 4: Salesperson Retention -->
    <div class="bg-white rounded-2xl shadow-md p-6 filter-card">
        <h3 class="text-xl font-semibold mb-4">📊 Retention Rate Salesperson</h3>
        <p class="text-gray-600 mb-4">Salesperson dengan tingkat retensi pelanggan terbaik (repeat orders).</p>
        
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border-collapse">
                <thead class="bg-teal-500 text-white">
                    <tr>
                        <th class="px-4 py-2 text-left">Salesperson</th>
                        <th class="px-4 py-2 text-right">Customers</th>
                        <th class="px-4 py-2 text-right">Repeat Orders</th>
                        <th class="px-4 py-2 text-right">Retention Rate</th>
                        <th class="px-4 py-2 text-right">Total Sales</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salespersonRetention as $sp)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2">
                                {{ $sp->SalespersonName }}<br>
                                <span class="text-xs text-gray-500">ID: {{ $sp->SalesPersonID }}</span>
                            </td>
                            <td class="px-4 py-2 text-right">{{ number_format($sp->CustomersHandled) }}</td>
                            <td class="px-4 py-2 text-right">{{ number_format($sp->CustomersWithRepeatOrders) }}</td>
                            <td class="px-4 py-2 text-right font-semibold">{{ number_format($sp->RetentionRate * 100, 1) }}%</td>
                            <td class="px-4 py-2 text-right">${{ number_format($sp->TotalSales, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            <canvas id="retentionChart" height="80"></canvas>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const customerData = @json($customerSegments);
    if(customerData.length > 0) {
        new Chart(document.getElementById('customerChart'), {
            type: 'scatter',
            data: {
                datasets: [{
                    label: 'Customer Segments',
                    data: customerData.map(c => ({ x: c.AvgOrdersPerYear, y: c.AvgOrderValueAcrossYears })),
                    backgroundColor: 'rgba(99, 102, 241, 0.6)',
                    borderColor: 'rgba(99, 102, 241, 1)',
                    pointRadius: 6
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: { title: { display: true, text: 'Avg Orders Per Year' } },
                    y: { title: { display: true, text: 'Avg Order Value ($)' } }
                }
            }
        });
    }

    const spData = @json($salespersonRetention);
    new Chart(document.getElementById('retentionChart'), {
        type: 'bar',
        data: {
            labels: spData.map(sp => sp.SalespersonName.substring(0, 20)),
            datasets: [
                {
                    label: 'Retention Rate (%)',
                    data: spData.map(sp => sp.RetentionRate * 100),
                    backgroundColor: 'rgba(20, 184, 166, 0.6)',
                    borderColor: 'rgba(20, 184, 166, 1)',
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    label: 'Total Sales ($)',
                    data: spData.map(sp => sp.TotalSales / 1000),
                    backgroundColor: 'rgba(251, 146, 60, 0.6)',
                    borderColor: 'rgba(251, 146, 60, 1)',
                    borderWidth: 1,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: { display: true, text: 'Retention Rate (%)' }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: { display: true, text: 'Total Sales (K$)' },
                    grid: { drawOnChartArea: false }
                }
            }
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.fade-in').forEach((el, index) => el.style.animationDelay = `${index * 0.05}s`);
    });
</script>
@endpush
