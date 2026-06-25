@extends('layouts.app')

@section('title', 'Product Performance')
@section('page-title', 'Product Performance')
@section('page-subtitle', 'Best sellers, low performers & analisis kategori produk')

@section('content')

{{-- Summary KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="kpi-card purple">
            <div class="kpi-icon purple"><i class="bi bi-boxes"></i></div>
            <div class="kpi-label">Total Produk</div>
            <div class="kpi-value">{{ count($products) }}</div>
            <div class="kpi-sub">SKU aktif dalam dataset</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card green">
            <div class="kpi-icon green"><i class="bi bi-trophy"></i></div>
            <div class="kpi-label">Top Product</div>
            <div class="kpi-value" style="font-size:1.1rem">{{ $topProducts[0]['name'] ?? '-' }}</div>
            <div class="kpi-sub">Rp {{ number_format(($topProducts[0]['net_sales'] ?? 0) / 1e6, 0) }} Jt net sales</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card amber">
            <div class="kpi-icon amber"><i class="bi bi-grid"></i></div>
            <div class="kpi-label">Top Kategori</div>
            <div class="kpi-value" style="font-size:1.1rem">{{ array_key_first($catLabels !== [] ? array_combine($catLabels, $catNet) : ['-' => 0]) }}</div>
            <div class="kpi-sub">Rp {{ number_format(($catNet[0] ?? 0) / 1e6, 0) }} Jt net sales</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card red">
            <div class="kpi-icon red"><i class="bi bi-arrow-down-circle"></i></div>
            <div class="kpi-label">Low Performer</div>
            <div class="kpi-value" style="font-size:1.1rem">{{ $bottomProducts[0]['name'] ?? '-' }}</div>
            <div class="kpi-sub">Rp {{ number_format(($bottomProducts[0]['net_sales'] ?? 0) / 1e6, 0) }} Jt net sales</div>
        </div>
    </div>
</div>

{{-- Charts --}}
<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="section-card h-100">
            <div class="section-title">Top 10 Produk — Net Sales</div>
            <div class="section-subtitle">Produk dengan penjualan bersih tertinggi</div>
            <div class="chart-box chart-box-lg">
                <canvas id="topProductsChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="section-card h-100">
            <div class="section-title">Penjualan per Kategori</div>
            <div class="section-subtitle">Net sales berdasarkan kategori produk</div>
            <div class="chart-box chart-box-lg">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Top 10 Table --}}
<div class="section-card mb-4">
    <div class="section-title">🏆 Top 10 Best Sellers</div>
    <div class="section-subtitle">Detail produk dengan performa terbaik</div>
    <div class="table-responsive">
        <table class="bi-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Produk</th>
                    <th>Kategori</th>
                    <th class="text-end">Net Sales</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Harga Jual</th>
                    <th class="text-end">Margin</th>
                    <th class="text-end">Rating</th>
                    <th class="text-end">Stok</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topProducts as $i => $p)
                <tr>
                    <td>
                        <span class="rank-no {{ $i === 0 ? 'gold' : ($i === 1 ? 'silver' : ($i === 2 ? 'bronze' : '')) }}">
                            {{ $i + 1 }}
                        </span>
                    </td>
                    <td style="color:var(--text-primary);font-weight:600">{{ $p['name'] }}</td>
                    <td><span class="bi-badge bi-badge-purple">{{ $p['category'] }}</span></td>
                    <td class="text-end fw-semibold" style="color:var(--text-primary)">
                        Rp {{ number_format($p['net_sales'] / 1e6, 0) }} Jt
                    </td>
                    <td class="text-end">{{ number_format($p['qty']) }}</td>
                    <td class="text-end">Rp {{ number_format($p['selling_price'] / 1e3, 0) }}K</td>
                    <td class="text-end">
                        <span class="bi-badge {{ $p['margin_pct'] > 30 ? 'bi-badge-green' : 'bi-badge-amber' }}">
                            {{ number_format($p['margin_pct'], 0) }}%
                        </span>
                    </td>
                    <td class="text-end">
                        <span style="color:#FCD34D">
                            @for($s = 0; $s < floor($p['avg_rating']); $s++) ★ @endfor
                        </span>
                        {{ number_format($p['avg_rating'], 1) }}
                    </td>
                    <td class="text-end">
                        <span class="bi-badge {{ $p['stock'] < 200 ? 'bi-badge-red' : 'bi-badge-green' }}">
                            {{ number_format($p['stock']) }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Bottom 10 --}}
<div class="section-card">
    <div class="section-title">⚠️ Low Performing Products</div>
    <div class="section-subtitle">Produk dengan penjualan terendah — perlu perhatian khusus</div>
    <div class="table-responsive">
        <table class="bi-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Produk</th>
                    <th>Kategori</th>
                    <th class="text-end">Net Sales</th>
                    <th class="text-end">Qty</th>
                    <th class="text-end">Stok</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bottomProducts as $i => $p)
                <tr>
                    <td><span class="rank-no">{{ $i + 1 }}</span></td>
                    <td style="color:var(--text-primary);font-weight:600">{{ $p['name'] }}</td>
                    <td><span class="bi-badge bi-badge-purple">{{ $p['category'] }}</span></td>
                    <td class="text-end">Rp {{ number_format($p['net_sales'] / 1e6, 0) }} Jt</td>
                    <td class="text-end">{{ number_format($p['qty']) }}</td>
                    <td class="text-end">{{ number_format($p['stock']) }}</td>
                    <td>
                        <span class="bi-badge bi-badge-red">Perlu Evaluasi</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
const topProds  = @json($topProducts);
const catLabels = @json($catLabels);
const catNet    = @json($catNet);

// Top products chart
new Chart(document.getElementById('topProductsChart'), {
    type: 'bar',
    data: {
        labels: topProds.map(p => p.name.length > 20 ? p.name.substr(0,20)+'…' : p.name),
        datasets: [{
            label: 'Net Sales',
            data: topProds.map(p => p.net_sales),
            backgroundColor: function(ctx) {
                const i = ctx.dataIndex;
                const colors = ['rgba(245,158,11,0.8)','rgba(156,163,175,0.7)','rgba(180,83,9,0.7)'];
                return i < 3 ? colors[i] : 'rgba(37,99,235,0.6)';
            },
            borderRadius: 6,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: { legend: { display: false } },
        scales: {
            x: { ticks: { callback: v => 'Rp ' + (v / 1e6).toFixed(0) + 'Jt' } }
        }
    }
});

// Category doughnut
const catColors = ['#2563EB','#60A5FA','#10B981','#F59E0B','#3B82F6','#EF4444','#8B5CF6','#F97316'];
new Chart(document.getElementById('categoryChart'), {
    type: 'doughnut',
    data: {
        labels: catLabels,
        datasets: [{
            data: catNet,
            backgroundColor: catColors,
            borderColor: '#FFFFFF',
            borderWidth: 2,
            hoverOffset: 6,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '60%',
        plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
            tooltip: { callbacks: { label: ctx => ' Rp ' + (ctx.raw / 1e6).toFixed(0) + ' Jt' } }
        }
    }
});
</script>
@endpush
