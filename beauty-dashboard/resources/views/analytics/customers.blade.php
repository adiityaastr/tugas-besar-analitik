@extends('layouts.app')

@section('title', 'Customer Insight')
@section('page-title', 'Customer Insight')
@section('page-subtitle', 'Demografi, membership tier & pola perilaku customer')

@section('content')

@php
    $totalTx = array_sum($metrics['gender_count']);
@endphp

{{-- KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="kpi-card purple">
            <div class="kpi-icon purple"><i class="bi bi-people-fill"></i></div>
            <div class="kpi-label">Unique Customers</div>
            <div class="kpi-value">{{ number_format($metrics['total_unique_customers']) }}</div>
            <div class="kpi-sub">Customer unik dalam dataset</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card pink">
            <div class="kpi-icon pink"><i class="bi bi-gender-ambiguous"></i></div>
            <div class="kpi-label">Dominan Gender</div>
            @php $topGender = array_key_first(array_reverse(arsort($metrics['gender_count']) ? $metrics['gender_count'] : [], ARRAY_FILTER_USE_BOTH) ?: $metrics['gender_count']); @endphp
            <div class="kpi-value" style="font-size:1.3rem">
                {{ array_search(max($metrics['gender_count']), $metrics['gender_count']) }}
            </div>
            <div class="kpi-sub">{{ number_format(max($metrics['gender_count'])) }} transaksi</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card amber">
            <div class="kpi-icon amber"><i class="bi bi-award"></i></div>
            <div class="kpi-label">Top Membership</div>
            <div class="kpi-value" style="font-size:1.3rem">
                {{ array_search(max($metrics['membership_count']), $metrics['membership_count']) }}
            </div>
            <div class="kpi-sub">{{ number_format(max($metrics['membership_count'])) }} member aktif</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card green">
            <div class="kpi-icon green"><i class="bi bi-geo-alt"></i></div>
            <div class="kpi-label">Top Kota</div>
            <div class="kpi-value" style="font-size:1.1rem">{{ array_key_first($metrics['top_cities']) }}</div>
            <div class="kpi-sub">Rp {{ number_format(array_values($metrics['top_cities'])[0] / 1e6, 0) }} Jt net sales</div>
        </div>
    </div>
</div>

{{-- Charts Row 1 --}}
<div class="row g-3 mb-4">
    {{-- Age Distribution --}}
    <div class="col-lg-4">
        <div class="section-card h-100">
            <div class="section-title">Distribusi Usia</div>
            <div class="section-subtitle">Segmentasi customer berdasarkan usia</div>
            <div class="chart-box" style="height:260px">
                <canvas id="ageChart"></canvas>
            </div>
        </div>
    </div>
    {{-- Membership --}}
    <div class="col-lg-4">
        <div class="section-card h-100">
            <div class="section-title">Membership Tier</div>
            <div class="section-subtitle">Distribusi tier & net sales per tier</div>
            <div class="chart-box" style="height:260px">
                <canvas id="membershipChart"></canvas>
            </div>
        </div>
    </div>
    {{-- Rating Distribution --}}
    <div class="col-lg-4">
        <div class="section-card h-100">
            <div class="section-title">Distribusi Rating</div>
            <div class="section-subtitle">Customer rating 1–5 bintang</div>
            <div class="chart-box" style="height:260px">
                <canvas id="ratingChart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Charts Row 2 --}}
<div class="row g-3 mb-4">
    {{-- Gender --}}
    <div class="col-lg-4">
        <div class="section-card h-100">
            <div class="section-title">Gender</div>
            <div class="section-subtitle">Distribusi transaksi berdasarkan gender</div>
            <div class="chart-box" style="height:220px">
                <canvas id="genderChart"></canvas>
            </div>
        </div>
    </div>
    {{-- Payment Methods --}}
    <div class="col-lg-4">
        <div class="section-card h-100">
            <div class="section-title">Metode Pembayaran</div>
            <div class="section-subtitle">Preferensi pembayaran customer</div>
            <div class="chart-box" style="height:220px">
                <canvas id="paymentChart"></canvas>
            </div>
        </div>
    </div>
    {{-- Sales Channel --}}
    <div class="col-lg-4">
        <div class="section-card h-100">
            <div class="section-title">Channel Penjualan</div>
            <div class="section-subtitle">Online vs Offline transaksi</div>
            <div class="chart-box" style="height:220px">
                <canvas id="channelChart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Top cities + Membership table --}}
<div class="row g-3">
    {{-- Top Cities --}}
    <div class="col-lg-6">
        <div class="section-card">
            <div class="section-title">Top 10 Kota by Net Sales</div>
            <div class="section-subtitle">Kota dengan kontribusi revenue tertinggi</div>
            @php $maxCity = max(array_values($metrics['top_cities'])); @endphp
            @foreach($metrics['top_cities'] as $city => $net)
            <div class="mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <span style="font-size:.83rem;font-weight:600;color:var(--text-primary)">{{ $city }}</span>
                    <span style="font-size:.78rem;color:var(--text-muted)">Rp {{ number_format($net / 1e6, 0) }} Jt</span>
                </div>
                <div class="bi-progress">
                    <div class="bi-progress-bar" style="width:{{ ($net / $maxCity) * 100 }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    {{-- Membership breakdown --}}
    <div class="col-lg-6">
        <div class="section-card">
            <div class="section-title">Membership Revenue Breakdown</div>
            <div class="section-subtitle">Net sales & jumlah transaksi per tier membership</div>
            <div class="table-responsive">
                <table class="bi-table">
                    <thead>
                        <tr>
                            <th>Tier</th>
                            <th class="text-end">Transaksi</th>
                            <th class="text-end">Net Sales</th>
                            <th class="text-end">AOV</th>
                            <th class="text-end">Share</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalMemberNet = array_sum($metrics['membership_net']); @endphp
                        @foreach($metrics['membership_net'] as $tier => $net)
                        <tr>
                            <td>
                                @php
                                    $tierColor = match(strtolower($tier)) {
                                        'gold','platinum' => 'bi-badge-amber',
                                        'silver' => 'bi-badge-blue',
                                        default => 'bi-badge-purple'
                                    };
                                @endphp
                                <span class="bi-badge {{ $tierColor }}">{{ $tier }}</span>
                            </td>
                            <td class="text-end">{{ number_format($metrics['membership_count'][$tier] ?? 0) }}</td>
                            <td class="text-end fw-semibold" style="color:var(--text-primary)">
                                Rp {{ number_format($net / 1e6, 0) }} Jt
                            </td>
                            <td class="text-end">
                                @php $cnt = $metrics['membership_count'][$tier] ?? 1; @endphp
                                Rp {{ number_format($net / $cnt / 1e3, 0) }}K
                            </td>
                            <td class="text-end">
                                {{ number_format($totalMemberNet > 0 ? ($net / $totalMemberNet) * 100 : 0, 1) }}%
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const PALETTE = ['#2563EB','#60A5FA','#10B981','#F59E0B','#3B82F6','#EF4444','#8B5CF6'];

// Age
new Chart(document.getElementById('ageChart'), {
    type: 'bar',
    data: {
        labels: @json(array_keys($metrics['age_groups'])),
        datasets: [{
            label: 'Transaksi',
            data: @json(array_values($metrics['age_groups'])),
            backgroundColor: PALETTE,
            borderRadius: 6,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { ticks: { callback: v => v.toLocaleString() } } }
    }
});

// Membership doughnut
new Chart(document.getElementById('membershipChart'), {
    type: 'doughnut',
    data: {
        labels: @json(array_keys($metrics['membership_net'])),
        datasets: [{
            data: @json(array_values($metrics['membership_net'])),
            backgroundColor: PALETTE,
            borderColor: '#FFFFFF',
            borderWidth: 2,
            hoverOffset: 5,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        cutout: '62%',
        plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
            tooltip: { callbacks: { label: ctx => ' Rp ' + (ctx.raw / 1e6).toFixed(0) + ' Jt' } }
        }
    }
});

// Rating
const ratingDist = @json($metrics['rating_dist']);
const ratingLabels = Object.keys(ratingDist).map(r => r + ' ⭐');
const ratingValues = Object.values(ratingDist);
const ratingColors = ratingValues.map((_, i) => {
    const pct = (i + 1) / 5;
    return `rgba(${Math.round(239 + (16-239)*pct)},${Math.round(68 + (185-68)*pct)},${Math.round(68 + (129-68)*pct)},0.8)`;
});
new Chart(document.getElementById('ratingChart'), {
    type: 'bar',
    data: {
        labels: ratingLabels,
        datasets: [{ label: 'Transaksi', data: ratingValues, backgroundColor: ratingColors, borderRadius: 6, borderSkipped: false }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
    }
});

// Gender
new Chart(document.getElementById('genderChart'), {
    type: 'doughnut',
    data: {
        labels: @json(array_keys($metrics['gender_count'])),
        datasets: [{
            data: @json(array_values($metrics['gender_count'])),
            backgroundColor: ['rgba(37,99,235,0.8)', 'rgba(96,165,250,0.8)', 'rgba(59,130,246,0.8)'],
            borderColor: '#FFFFFF', borderWidth: 2,
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, cutout: '58%', plugins: { legend: { position: 'bottom' } } }
});

// Payment
new Chart(document.getElementById('paymentChart'), {
    type: 'doughnut',
    data: {
        labels: @json(array_keys($metrics['payment_count'])),
        datasets: [{
            data: @json(array_values($metrics['payment_count'])),
            backgroundColor: PALETTE,
            borderColor: '#FFFFFF', borderWidth: 2,
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, cutout: '58%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } } }
});

// Channel
new Chart(document.getElementById('channelChart'), {
    type: 'doughnut',
    data: {
        labels: @json(array_keys($metrics['channel_count'])),
        datasets: [{
            data: @json(array_values($metrics['channel_count'])),
            backgroundColor: ['rgba(37,99,235,0.8)','rgba(16,185,129,0.8)'],
            borderColor: '#FFFFFF', borderWidth: 2,
        }]
    },
    options: { responsive: true, maintainAspectRatio: false, cutout: '58%', plugins: { legend: { position: 'bottom' } } }
});
</script>
@endpush
