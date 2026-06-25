@extends('layouts.app')

@section('title', 'Operational Insight')
@section('page-title', 'Operational Insight')
@section('page-subtitle', 'Return rate, delivery performance & cancelled transactions')

@section('content')

@php
    $totalTx      = $metrics['total_transactions'];
    $returnedTotal = array_sum($metrics['return_by_platform']);
    $cancelledTotal = array_sum($metrics['cancel_by_platform']);
    $deliveredTotal = $metrics['delivery_status_count']['Delivered'] ?? ($metrics['delivery_status_count']['delivered'] ?? 0);
@endphp

{{-- KPI Row --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="kpi-card red">
            <div class="kpi-icon red"><i class="bi bi-arrow-return-left"></i></div>
            <div class="kpi-label">Return Rate</div>
            <div class="kpi-value">{{ number_format($returnRate, 1) }}<small style="font-size:1rem;font-weight:600">%</small></div>
            <div class="kpi-sub">{{ number_format($returnedTotal) }} dari {{ number_format($totalTx) }} transaksi</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card amber">
            <div class="kpi-icon amber"><i class="bi bi-x-circle"></i></div>
            <div class="kpi-label">Cancel Rate</div>
            <div class="kpi-value">{{ number_format($cancelRate, 1) }}<small style="font-size:1rem;font-weight:600">%</small></div>
            <div class="kpi-sub">{{ number_format($cancelledTotal) }} order dibatalkan</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card green">
            <div class="kpi-icon green"><i class="bi bi-truck"></i></div>
            <div class="kpi-label">Avg Delivery</div>
            <div class="kpi-value">{{ number_format($avgDelivery, 1) }}<small style="font-size:1rem;font-weight:600">d</small></div>
            <div class="kpi-sub">Rata-rata hari pengiriman</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="kpi-card blue">
            <div class="kpi-icon blue"><i class="bi bi-check-circle"></i></div>
            <div class="kpi-label">Delivery Rate</div>
            <div class="kpi-value">{{ number_format($totalTx > 0 ? ($deliveredTotal / $totalTx) * 100 : 0, 1) }}<small style="font-size:1rem;font-weight:600">%</small></div>
            <div class="kpi-sub">{{ number_format($deliveredTotal) }} terkirim berhasil</div>
        </div>
    </div>
</div>

{{-- Charts Row 1 --}}
<div class="row g-3 mb-4">
    {{-- Delivery Status --}}
    <div class="col-lg-5">
        <div class="section-card h-100">
            <div class="section-title">Status Pengiriman</div>
            <div class="section-subtitle">Distribusi delivery_status seluruh transaksi</div>
            <div class="chart-box" style="height:260px">
                <canvas id="deliveryStatusChart"></canvas>
            </div>
        </div>
    </div>
    {{-- Delivery Days Distribution --}}
    <div class="col-lg-7">
        <div class="section-card h-100">
            <div class="section-title">Distribusi Waktu Pengiriman</div>
            <div class="section-subtitle">Berapa hari yang dibutuhkan untuk pengiriman sampai</div>
            <div class="chart-box" style="height:260px">
                <canvas id="deliveryDaysChart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Charts Row 2 --}}
<div class="row g-3 mb-4">
    {{-- Return Reasons --}}
    <div class="col-lg-6">
        <div class="section-card h-100">
            <div class="section-title">Alasan Retur</div>
            <div class="section-subtitle">Penyebab utama customer melakukan retur</div>
            <div class="chart-box chart-box-lg">
                <canvas id="returnReasonChart"></canvas>
            </div>
        </div>
    </div>
    {{-- Return & Cancel by Platform --}}
    <div class="col-lg-6">
        <div class="section-card h-100">
            <div class="section-title">Retur & Cancel per Platform</div>
            <div class="section-subtitle">Jumlah retur dan pembatalan order per marketplace</div>
            <div class="chart-box chart-box-lg">
                <canvas id="returnPlatformChart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Return by Product Table --}}
<div class="section-card">
    <div class="section-title">Top 10 Produk — Tertinggi Retur</div>
    <div class="section-subtitle">Produk yang paling sering dikembalikan customer</div>
    <div class="table-responsive">
        <table class="bi-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Produk</th>
                    <th class="text-end">Jumlah Retur</th>
                    <th>Bar</th>
                </tr>
            </thead>
            <tbody>
                @php $maxReturn = max(array_values($metrics['return_by_product']) ?: [1]); @endphp
                @foreach($metrics['return_by_product'] as $prod => $count)
                @php $loopIdx = $loop->index; @endphp
                <tr>
                    <td><span class="rank-no">{{ $loopIdx + 1 }}</span></td>
                    <td style="color:var(--text-primary);font-weight:600">{{ $prod }}</td>
                    <td class="text-end">
                        <span class="bi-badge bi-badge-red">{{ number_format($count) }}</span>
                    </td>
                    <td style="width:40%">
                        <div class="bi-progress">
                            <div class="bi-progress-bar" style="width:{{ ($count / $maxReturn) * 100 }}%;background:linear-gradient(90deg,#EF4444,#F87171)"></div>
                        </div>
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
const PALETTE = ['#2563EB','#60A5FA','#10B981','#F59E0B','#3B82F6','#EF4444','#8B5CF6'];

// Delivery Status Doughnut
const deliveryStatusData = @json($metrics['delivery_status_count']);
new Chart(document.getElementById('deliveryStatusChart'), {
    type: 'doughnut',
    data: {
        labels: Object.keys(deliveryStatusData),
        datasets: [{
            data: Object.values(deliveryStatusData),
            backgroundColor: PALETTE,
            borderColor: '#FFFFFF',
            borderWidth: 2,
            hoverOffset: 5,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        cutout: '62%',
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } } }
    }
});

// Delivery Days Distribution
const daysData = @json($metrics['delivery_days_dist']);
new Chart(document.getElementById('deliveryDaysChart'), {
    type: 'bar',
    data: {
        labels: Object.keys(daysData),
        datasets: [{
            label: 'Transaksi',
            data: Object.values(daysData),
            backgroundColor: ['rgba(16,185,129,0.8)','rgba(245,158,11,0.8)','rgba(96,165,250,0.8)','rgba(239,68,68,0.8)'],
            borderRadius: 8,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { ticks: { callback: v => v.toLocaleString() } } }
    }
});

// Return Reasons
const returnReasons = @json($metrics['return_reason_count']);
new Chart(document.getElementById('returnReasonChart'), {
    type: 'bar',
    data: {
        labels: Object.keys(returnReasons).map(r => r.length > 22 ? r.substr(0,22)+'…' : r),
        datasets: [{
            label: 'Retur',
            data: Object.values(returnReasons),
            backgroundColor: 'rgba(239,68,68,0.7)',
            borderRadius: 6,
            borderSkipped: false,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: { legend: { display: false } },
    }
});

// Return & Cancel per Platform
const returnPlat  = @json($metrics['return_by_platform']);
const cancelPlat  = @json($metrics['cancel_by_platform']);
const allPlatforms = [...new Set([...Object.keys(returnPlat), ...Object.keys(cancelPlat)])];
new Chart(document.getElementById('returnPlatformChart'), {
    type: 'bar',
    data: {
        labels: allPlatforms,
        datasets: [
            {
                label: 'Retur',
                data: allPlatforms.map(p => returnPlat[p] || 0),
                backgroundColor: 'rgba(239,68,68,0.7)',
                borderRadius: 5,
                borderSkipped: false,
            },
            {
                label: 'Cancel',
                data: allPlatforms.map(p => cancelPlat[p] || 0),
                backgroundColor: 'rgba(245,158,11,0.7)',
                borderRadius: 5,
                borderSkipped: false,
            }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'top', align: 'end' } },
        scales: { y: { ticks: { callback: v => v.toLocaleString() } } }
    }
});
</script>
@endpush
