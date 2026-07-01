@extends('layouts.app')

@section('title', 'Sales Performance')
@section('page-title', 'Sales Performance')
@section('page-subtitle', 'Overview penjualan lintas platform & waktu')

@section('content')

{{-- KPI Row --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-2">
        <div class="kpi-card purple">
            <div class="kpi-icon purple"><i class="bi bi-currency-dollar"></i></div>
            <div class="kpi-label">Net Sales</div>
            <div class="kpi-value">{{ 'Rp ' . number_format($metrics['total_net_sales'] / 1e9, 1) . 'M' }}</div>
            <div class="kpi-sub">Total pendapatan bersih</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="kpi-card blue">
            <div class="kpi-icon blue"><i class="bi bi-receipt"></i></div>
            <div class="kpi-label">Transaksi</div>
            <div class="kpi-value">{{ number_format($metrics['total_transactions']) }}</div>
            <div class="kpi-sub">Total order masuk</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="kpi-card green">
            <div class="kpi-icon green"><i class="bi bi-box-seam"></i></div>
            <div class="kpi-label">Qty Terjual</div>
            <div class="kpi-value">{{ number_format($metrics['total_qty']) }}</div>
            <div class="kpi-sub">Unit produk terjual</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="kpi-card amber">
            <div class="kpi-icon amber"><i class="bi bi-basket2"></i></div>
            <div class="kpi-label">Avg Order</div>
            <div class="kpi-value">{{ 'Rp ' . number_format($metrics['avg_order_value'] / 1e3, 0) . 'K' }}</div>
            <div class="kpi-sub">Nilai rata-rata per order</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="kpi-card pink">
            <div class="kpi-icon pink"><i class="bi bi-tag"></i></div>
            <div class="kpi-label">Total Diskon</div>
            <div class="kpi-value">{{ 'Rp ' . number_format($metrics['total_discount'] / 1e9, 1) . 'M' }}</div>
            <div class="kpi-sub">{{ number_format(($metrics['total_discount'] / max($metrics['total_gross_sales'], 1)) * 100, 1) }}% dari gross</div>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="kpi-card green">
            <div class="kpi-icon green"><i class="bi bi-star-fill"></i></div>
            <div class="kpi-label">Avg Rating</div>
            <div class="kpi-value">{{ number_format($metrics['avg_rating'], 1) }}</div>
            <div class="kpi-sub">Customer satisfaction</div>
        </div>
    </div>
</div>

{{-- Charts Row --}}
<div class="row g-3 mb-4">
    {{-- Sales Trend --}}
    <div class="col-lg-8">
        <div class="section-card h-100">
            <div class="section-title">Tren Penjualan Bulanan</div>
            <div class="section-subtitle">Net sales vs Gross sales per bulan</div>
            <div class="chart-box chart-box-lg">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </div>
    {{-- Platform Distribution --}}
    <div class="col-lg-4">
        <div class="section-card h-100">
            <div class="section-title">Distribusi Platform</div>
            <div class="section-subtitle">Kontribusi net sales per platform</div>
            <div class="chart-box" style="height:240px">
                <canvas id="platformDonut"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Platform Detail & Campaign --}}
<div class="row g-3 mb-4">
    {{-- Platform detail table --}}
    <div class="col-lg-6">
        <div class="section-card h-100">
            <div class="section-title">Platform Breakdown</div>
            <div class="section-subtitle">Revenue, transaksi & fee per platform</div>
            <div class="table-responsive">
                <table class="bi-table">
                    <thead>
                        <tr>
                            <th>Platform</th>
                            <th>Net Sales</th>
                            <th>Transaksi</th>
                            <th>Avg Order</th>
                            <th>Fee</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($platLabels as $i => $plat)
                        <tr>
                            <td>
                                <span class="fw-600" style="color:var(--text-primary)">{{ $plat }}</span>
                            </td>
                            <td class="fw-semibold" style="color:var(--text-primary)">
                                Rp {{ number_format($platNet[$i] / 1e6, 0) }}Jt
                            </td>
                            <td>{{ number_format($platCount[$i]) }}</td>
                            <td>Rp {{ number_format($platCount[$i] > 0 ? $platNet[$i] / $platCount[$i] / 1e3 : 0, 0) }}K</td>
                            <td>
                                <span class="bi-badge bi-badge-amber">
                                    Rp {{ number_format($platFee[$i] / 1e6, 0) }}Jt
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Campaign performance --}}
    <div class="col-lg-6">
        <div class="section-card h-100">
            <div class="section-title">Campaign Performance</div>
            <div class="section-subtitle">Net sales per campaign name (Top 8)</div>
            <div class="chart-box chart-box-lg">
                <canvas id="campaignChart"></canvas>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const platLabels = @json($platLabels);
const getPlatformColor = (name) => {
    const n = name.toLowerCase();
    if (n.includes('tiktok')) return '#10B981'; // Green
    if (n.includes('shopee')) return '#F97316'; // Orange
    if (n.includes('offline')) return '#8B5CF6'; // Purple
    if (n.includes('website')) return '#EC4899'; // Pink
    return '#3B82F6'; // Default Blue
};
const platColors = platLabels.map(getPlatformColor);

// Trend Chart
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: @json($trendLabels),
        datasets: [
            {
                label: 'Net Sales',
                data: @json($trendNet),
                borderColor: '#2563EB',
                backgroundColor: 'rgba(37,99,235,0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 2.5,
                pointBackgroundColor: '#2563EB',
                pointRadius: 4,
                pointHoverRadius: 7,
            },
            {
                label: 'Gross Sales',
                data: @json($trendGross),
                borderColor: '#60A5FA',
                backgroundColor: 'rgba(96,165,250,0.05)',
                fill: true,
                tension: 0.4,
                borderWidth: 2,
                borderDash: [5,3],
                pointBackgroundColor: '#60A5FA',
                pointRadius: 3,
                pointHoverRadius: 6,
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { position: 'top', align: 'end' } },
        scales: {
            y: {
                ticks: {
                    callback: v => 'Rp ' + (v / 1e9).toFixed(1) + 'M'
                }
            }
        }
    }
});

// Platform Donut
new Chart(document.getElementById('platformDonut'), {
    type: 'doughnut',
    data: {
        labels: @json($platLabels),
        datasets: [{
            data: @json($platNet),
            backgroundColor: platColors,
            borderWidth: 2,
            borderColor: '#FFFFFF',
            hoverOffset: 6,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '68%',
        plugins: {
            legend: { position: 'bottom' },
            tooltip: {
                callbacks: {
                    label: ctx => ' Rp ' + (ctx.raw / 1e6).toFixed(0) + ' Jt'
                }
            }
        }
    }
});

// Campaign Chart
const campaignData = @json($campaignData);
new Chart(document.getElementById('campaignChart'), {
    type: 'bar',
    data: {
        labels: campaignData.map(c => c.name.length > 18 ? c.name.substr(0,18)+'…' : c.name),
        datasets: [{
            label: 'Net Sales',
            data: campaignData.map(c => c.net),
            backgroundColor: 'rgba(37,99,235,0.7)',
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
            x: {
                ticks: { callback: v => 'Rp ' + (v / 1e6).toFixed(0) + 'Jt' }
            }
        }
    }
});
</script>
@endpush
