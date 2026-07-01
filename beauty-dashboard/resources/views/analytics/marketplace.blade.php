@extends('layouts.app')

@section('title', 'Marketplace Performance')
@section('page-title', 'Marketplace Performance')
@section('page-subtitle', 'Perbandingan performa Shopee · TikTok · Website · Offline')

@section('content')

{{-- Platform cards --}}
<div class="row g-3 mb-4">
    @foreach($platforms as $p)
    @php
        $n = strtolower($p['name']);
        if (str_contains($n, 'tiktok')) { $color = 'green'; $icon = 'bi-tiktok'; }
        elseif (str_contains($n, 'shopee')) { $color = 'amber'; $icon = 'bi-bag'; }
        elseif (str_contains($n, 'offline')) { $color = 'purple'; $icon = 'bi-building'; }
        elseif (str_contains($n, 'website')) { $color = 'pink'; $icon = 'bi-globe'; }
        else { $color = 'blue'; $icon = 'bi-shop'; }
    @endphp
    <div class="col-md-6 col-lg-3">
        <div class="kpi-card {{ $color }}">
            <div class="kpi-icon {{ $color }}">
                <i class="bi {{ $icon }}"></i>
            </div>
            <div class="kpi-label">{{ $p['name'] }}</div>
            <div class="kpi-value">Rp {{ number_format($p['net'] / 1e6, 0) }}<small style="font-size:.9rem;font-weight:600">Jt</small></div>
            <div class="kpi-sub">{{ number_format($p['count']) }} transaksi · AOV Rp {{ number_format($p['aov'] / 1e3, 0) }}K</div>
        </div>
    </div>
    @endforeach
</div>

{{-- Charts Row --}}
<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="section-card h-100">
            <div class="section-title">Net Sales per Platform</div>
            <div class="section-subtitle">Perbandingan revenue bersih</div>
            <div class="chart-box chart-box-lg">
                <canvas id="platNetChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="section-card h-100">
            <div class="section-title">Jumlah Transaksi per Platform</div>
            <div class="section-subtitle">Volume order per channel penjualan</div>
            <div class="chart-box chart-box-lg">
                <canvas id="platCountChart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Detail Comparison Table --}}
<div class="section-card mb-4">
    <div class="section-title">Perbandingan Detail Platform</div>
    <div class="section-subtitle">Gross, net, fee, diskon & AOV tiap platform</div>
    <div class="table-responsive">
        <table class="bi-table">
            <thead>
                <tr>
                    <th>Platform</th>
                    <th class="text-end">Gross Sales</th>
                    <th class="text-end">Net Sales</th>
                    <th class="text-end">Platform Fee</th>
                    <th class="text-end">Fee Rate</th>
                    <th class="text-end">Disc Rate</th>
                    <th class="text-end">Transaksi</th>
                    <th class="text-end">AOV</th>
                </tr>
            </thead>
            <tbody>
                @foreach($platforms as $idx => $p)
                <tr>
                    <td>
                        <span class="fw-semibold" style="color:var(--text-primary)">{{ $p['name'] }}</span>
                    </td>
                    <td class="text-end">Rp {{ number_format($p['gross'] / 1e6, 0) }} Jt</td>
                    <td class="text-end fw-semibold" style="color:var(--text-primary)">
                        Rp {{ number_format($p['net'] / 1e6, 0) }} Jt
                    </td>
                    <td class="text-end">Rp {{ number_format($p['fee'] / 1e6, 0) }} Jt</td>
                    <td class="text-end">
                        <span class="bi-badge {{ $p['fee_rate'] > 5 ? 'bi-badge-red' : 'bi-badge-green' }}">
                            {{ number_format($p['fee_rate'], 1) }}%
                        </span>
                    </td>
                    <td class="text-end">
                        <span class="bi-badge bi-badge-amber">{{ number_format($p['disc_rate'], 1) }}%</span>
                    </td>
                    <td class="text-end">{{ number_format($p['count']) }}</td>
                    <td class="text-end">Rp {{ number_format($p['aov'] / 1e3, 0) }}K</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Fee vs Net Bars --}}
<div class="section-card">
    <div class="section-title">Kontribusi Biaya Platform</div>
    <div class="section-subtitle">Perbandingan net sales vs biaya platform (fee)</div>
    <div class="chart-box chart-box-lg">
        <canvas id="feeNetChart"></canvas>
    </div>
</div>

@endsection

@push('scripts')
<script>
const platLabels = @json(array_column($platforms, 'name'));
const platNet    = @json(array_column($platforms, 'net'));
const platGross  = @json(array_column($platforms, 'gross'));
const platFee    = @json(array_column($platforms, 'fee'));
const platCount  = @json(array_column($platforms, 'count'));

const getPlatformColor = (name) => {
    const n = name.toLowerCase();
    if (n.includes('tiktok')) return '#10B981'; // Green
    if (n.includes('shopee')) return '#F97316'; // Orange
    if (n.includes('offline')) return '#8B5CF6'; // Purple
    if (n.includes('website')) return '#EC4899'; // Pink
    return '#3B82F6'; // Default Blue
};
const COLORS = platLabels.map(getPlatformColor);
const COLORS_ALPHA = COLORS.map(c => c + 'BB');

// Net Sales Bar
new Chart(document.getElementById('platNetChart'), {
    type: 'bar',
    data: {
        labels: platLabels,
        datasets: [{
            label: 'Net Sales',
            data: platNet,
            backgroundColor: COLORS_ALPHA,
            borderColor: COLORS,
            borderWidth: 1.5,
            borderRadius: 8,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { ticks: { callback: v => 'Rp ' + (v / 1e9).toFixed(1) + 'M' } }
        }
    }
});

// Count Chart
new Chart(document.getElementById('platCountChart'), {
    type: 'bar',
    data: {
        labels: platLabels,
        datasets: [{
            label: 'Transaksi',
            data: platCount,
            backgroundColor: COLORS_ALPHA,
            borderColor: COLORS,
            borderWidth: 1.5,
            borderRadius: 8,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { ticks: { callback: v => v.toLocaleString() } } }
    }
});

// Fee vs Net stacked
new Chart(document.getElementById('feeNetChart'), {
    type: 'bar',
    data: {
        labels: platLabels,
        datasets: [
            {
                label: 'Net Sales',
                data: platNet,
                backgroundColor: 'rgba(37,99,235,0.7)',
                borderRadius: { topLeft: 0, topRight: 0, bottomLeft: 6, bottomRight: 6 },
                borderSkipped: false,
                stack: 'a',
            },
            {
                label: 'Platform Fee',
                data: platFee,
                backgroundColor: 'rgba(239,68,68,0.7)',
                borderRadius: { topLeft: 6, topRight: 6, bottomLeft: 0, bottomRight: 0 },
                borderSkipped: false,
                stack: 'a',
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'top', align: 'end' } },
        scales: {
            x: { stacked: true },
            y: { stacked: true, ticks: { callback: v => 'Rp ' + (v / 1e9).toFixed(1) + 'M' } }
        }
    }
});
</script>
@endpush
