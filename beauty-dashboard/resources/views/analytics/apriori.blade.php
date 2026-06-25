@extends('layouts.app')

@section('title', 'Apriori Association Rules')
@section('page-title', 'Apriori Association Rules')
@section('page-subtitle', 'Market basket analysis & overstock bundling strategy')

@section('content')

{{-- Loading Overlay --}}
<div id="loading-overlay" class="d-none position-fixed top-0 start-0 w-100 h-100 d-flex flex-column justify-content-center align-items-center" style="background:rgba(15,10,30,0.92);z-index:9999;backdrop-filter:blur(4px)">
    <div class="spinner-border mb-3" style="width:3rem;height:3rem;color:var(--brand-primary)" role="status"></div>
    <h5 class="fw-bold mb-1" style="color:var(--text-primary)">Memproses Data</h5>
    <p style="color:var(--text-muted);font-size:.85rem">Menjalankan algoritma Apriori...</p>
</div>

{{-- Run Analysis Button --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <form id="analysis-form" action="{{ route('analytics.apriori.run') }}" method="POST">
        @csrf
        <button type="submit" class="btn fw-bold px-4 py-2" style="background:linear-gradient(135deg,var(--brand-primary),var(--brand-secondary));color:#FFFFFF;border:none;border-radius:10px;">
            <i class="bi bi-arrow-clockwise me-2"></i>Run Analysis
        </button>
    </form>
</div>

{{-- KPI Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="kpi-card purple">
            <div class="kpi-icon purple"><i class="bi bi-diagram-3"></i></div>
            <div class="kpi-label">Total Rules</div>
            <div class="kpi-value">{{ $total_rules }}</div>
            <div class="kpi-sub">Valid item associations ditemukan</div>
        </div>
    </div>
    <div class="kpi-tooltip col-md-4" title="Seberapa sering kombinasi item muncul bersama. Min 0.5% = muncul di ≥4 transaksi.">
        <div class="kpi-card blue h-100">
            <div class="kpi-icon blue"><i class="bi bi-info-circle"></i></div>
            <div class="kpi-label">Min Support <i class="bi bi-question-circle" style="font-size:.7rem;cursor:help"></i></div>
            <div class="kpi-value">0.5<small style="font-size:1rem;font-weight:600">%</small></div>
            <div class="kpi-sub">≥ 4 basket occurrences</div>
        </div>
    </div>
    <div class="kpi-tooltip col-md-4" title="Probabilitas B dibeli jika A sudah dibeli. Min 10% = relevansi dasar.">
        <div class="kpi-card green h-100">
            <div class="kpi-icon green"><i class="bi bi-percent"></i></div>
            <div class="kpi-label">Min Confidence <i class="bi bi-question-circle" style="font-size:.7rem;cursor:help"></i></div>
            <div class="kpi-value">10<small style="font-size:1rem;font-weight:600">%</small></div>
            <div class="kpi-sub">Base relation probability</div>
        </div>
    </div>
</div>

@if(!$has_data)
    {{-- Empty State --}}
    <div class="section-card text-center py-5">
        <div class="mb-4" style="font-size:3.5rem">📊</div>
        <h4 class="fw-bold mb-2" style="color:var(--text-primary)">Belum Ada Data</h4>
        <p style="color:var(--text-muted);font-size:.9rem;max-width:400px;margin:0 auto 28px">
            Jalankan analisis Apriori untuk menghasilkan association rules dan rekomendasi overstock.
        </p>
        <form action="{{ route('analytics.apriori.run') }}" method="POST">
            @csrf
            <button type="submit" class="btn fw-bold px-5 py-2" style="background:linear-gradient(135deg,var(--brand-primary),var(--brand-secondary));color:#FFFFFF;border:none;border-radius:10px;">
                <i class="bi bi-play-fill me-2"></i>Run First Analysis
            </button>
        </form>
    </div>

@else

    {{-- Metric Info Panel --}}
    <div class="section-card mb-4" style="border-color:rgba(37,99,235,0.3)">
        <div class="section-title mb-0">💡 Panduan Membaca Metrik</div>
        <div class="row g-3 mt-1">
            <div class="col-md-4">
                <div style="background:rgba(37,99,235,0.08);border-radius:10px;padding:14px;border:1px solid rgba(37,99,235,0.15)">
                    <div style="font-size:.7rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#A78BFA;margin-bottom:4px">Support</div>
                    <div style="font-size:.82rem;color:var(--text-secondary);line-height:1.6">Seberapa sering kombinasi item muncul di transaksi. <strong style="color:var(--text-primary)">Semakin tinggi = semakin populer</strong> pasangannya.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div style="background:rgba(16,185,129,0.08);border-radius:10px;padding:14px;border:1px solid rgba(16,185,129,0.15)">
                    <div style="font-size:.7rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#34D399;margin-bottom:4px">Confidence</div>
                    <div style="font-size:.82rem;color:var(--text-secondary);line-height:1.6">Probabilitas item B dibeli jika A sudah dibeli. <strong style="color:var(--text-primary)">Semakin tinggi = semakin kuat hubungannya</strong>.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div style="background:rgba(245,158,11,0.08);border-radius:10px;padding:14px;border:1px solid rgba(245,158,11,0.15)">
                    <div style="font-size:.7rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#FCD34D;margin-bottom:4px">Lift</div>
                    <div style="font-size:.82rem;color:var(--text-secondary);line-height:1.6">Seberapa besar A meningkatkan probabilitas B. <strong style="color:var(--text-primary)">Lift &gt; 1 = positif</strong>, artinya ada korelasi nyata.</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="section-card mb-4">
        <div class="section-title">Visual Analysis</div>
        <div class="section-subtitle">Visualisasi kekuatan dan distribusi association rules</div>
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="chart-box chart-box-lg">
                    <canvas id="topRulesChart"></canvas>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="chart-box chart-box-lg">
                    <canvas id="scatterChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Overstock Bundles --}}
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-end mb-3">
            <div>
                <div class="section-title">Overstock Bundling Strategy</div>
                <div class="section-subtitle">Bundle produk lambat dengan produk high-affinity untuk kosongkan gudang</div>
            </div>
            <span class="bi-badge bi-badge-amber">Actionable Insights</span>
        </div>

        @if(count($overstockBundles) === 0)
            <div class="section-card text-center py-4">
                <p class="fw-semibold mb-0" style="color:var(--success)">✅ Gudang teroptimasi. Tidak ada overstock kritis teridentifikasi.</p>
            </div>
        @else
            <div class="row g-3">
                @foreach($overstockBundles as $bundle)
                <div class="col-md-6 col-lg-4">
                    <div class="kpi-card purple h-100" style="padding:20px">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="kpi-icon purple"><i class="bi bi-graph-up-arrow"></i></div>
                            <span class="bi-badge bi-badge-amber">LIFT {{ number_format($bundle['lift'], 2) }}</span>
                        </div>
                        <div class="fw-bold mb-3" style="color:var(--text-primary);font-size:.95rem">{{ $bundle['overstock_item'] }}</div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div style="background:rgba(0,0,0,0.05);border-radius:8px;padding:8px;text-align:center;border:1px solid var(--border)">
                                    <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted)">Stok</div>
                                    <div class="fw-bold" style="color:var(--text-primary)">{{ number_format($bundle['stock']) }}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div style="background:rgba(239,68,68,0.1);border-radius:8px;padding:8px;text-align:center;border:1px solid rgba(239,68,68,0.2)">
                                    <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#F87171">Stagnant</div>
                                    <div class="fw-bold" style="color:#F87171">{{ $bundle['days_left'] }}d</div>
                                </div>
                            </div>
                        </div>
                        <div style="font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);margin-bottom:6px">Bundle With</div>
                        <div style="background:linear-gradient(135deg,rgba(37,99,235,0.2),rgba(96,165,250,0.1));border:1px solid rgba(37,99,235,0.25);border-radius:8px;padding:10px 12px;font-weight:600;font-size:.85rem;color:var(--text-primary)">
                            {{ $bundle['bundle_with'] }}
                        </div>
                        <div class="d-flex justify-content-between mt-3 pt-3" style="border-top:1px solid var(--border)">
                            <div>
                                <div style="font-size:.65rem;color:var(--text-muted);font-weight:700;text-transform:uppercase">Support</div>
                                <strong style="color:var(--text-primary)">{{ number_format($bundle['support'] * 100, 2) }}%</strong>
                            </div>
                            <div class="text-end">
                                <div style="font-size:.65rem;color:var(--text-muted);font-weight:700;text-transform:uppercase">Confidence</div>
                                <strong style="color:var(--text-primary)">{{ number_format($bundle['confidence'] * 100, 2) }}%</strong>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Association Rules Table --}}
    <div class="section-card">
        {{-- Table Header --}}
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <div class="section-title">Association Rules Data</div>
                <div class="section-subtitle">{{ $total_rules }} rules ditemukan — complete dataset</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <input type="text" id="rule-search" class="form-control form-control-sm" placeholder="Cari produk..."
                    style="background:rgba(0,0,0,0.06);border:1px solid var(--border);color:var(--text-primary);min-width:180px;border-radius:8px">
                <select id="rule-size-filter" class="form-select form-select-sm"
                    style="background:rgba(0,0,0,0.06);border:1px solid var(--border);color:var(--text-primary);min-width:130px;border-radius:8px">
                    <option value="all">Semua Ukuran</option>
                    <option value="2">2 Items</option>
                    <option value="3">3 Items</option>
                    <option value="4">4 Items</option>
                </select>
                <button id="btn-export" class="btn btn-sm fw-bold px-3" style="background:rgba(245,158,11,0.15);border:1px solid rgba(245,158,11,0.3);color:#FCD34D;border-radius:8px">
                    <i class="bi bi-download me-1"></i>CSV
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table id="rules-table" class="bi-table">
                <thead>
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Antecedents (If)</th>
                        <th>Consequents (Then)</th>
                        <th class="sortable text-end" data-sort="support" style="cursor:pointer">
                            Support <i class="bi bi-arrow-down-up" style="font-size:.7rem"></i>
                        </th>
                        <th class="sortable text-end" data-sort="confidence" style="cursor:pointer">
                            Confidence <i class="bi bi-arrow-down-up" style="font-size:.7rem"></i>
                        </th>
                        <th class="sortable text-end" data-sort="lift" style="cursor:pointer">
                            Lift <i class="bi bi-arrow-down-up" style="font-size:.7rem"></i>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rules as $index => $rule)
                    @php
                        $ant_count   = substr_count($rule['antecedent'], ',') + 1;
                        $con_count   = substr_count($rule['consequent'], ',') + 1;
                        $total_items = $ant_count + $con_count;
                        $liftClass   = $rule['lift'] >= 2 ? 'bi-badge-green' : ($rule['lift'] >= 1 ? 'bi-badge-amber' : 'bi-badge-red');
                    @endphp
                    <tr class="rule-row"
                        data-size="{{ $total_items }}"
                        data-support="{{ $rule['support'] }}"
                        data-confidence="{{ $rule['confidence'] }}"
                        data-lift="{{ $rule['lift'] }}">
                        <td class="col-no" style="color:var(--text-muted);font-weight:700;font-size:.8rem">{{ $index + 1 }}</td>
                        <td class="col-antecedent" style="font-size:.83rem">{{ $rule['antecedent'] }}</td>
                        <td class="col-consequent fw-semibold" style="color:var(--brand-primary);font-size:.83rem">
                            <i class="bi bi-arrow-right" style="font-size:.7rem;opacity:.6"></i> {{ $rule['consequent'] }}
                        </td>
                        <td class="text-end">{{ number_format($rule['support'] * 100, 2) }}%</td>
                        <td class="text-end">{{ number_format($rule['confidence'] * 100, 2) }}%</td>
                        <td class="text-end">
                            <span class="bi-badge {{ $liftClass }}">{{ number_format($rule['lift'], 3) }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination info --}}
        <div class="d-flex justify-content-between align-items-center mt-3 pt-3" style="border-top:1px solid var(--border)">
            <span id="visible-count" style="font-size:.78rem;color:var(--text-muted)">Menampilkan {{ $total_rules }} dari {{ $total_rules }} rules</span>
        </div>
    </div>

@endif

@endsection

@push('scripts')
<script>
    // Loading overlay
    const analysisForm = document.getElementById('analysis-form');
    if (analysisForm) {
        analysisForm.addEventListener('submit', () => {
            const overlay = document.getElementById('loading-overlay');
            overlay.classList.remove('d-none');
            overlay.classList.add('d-flex');
        });
    }

    // Search & filter
    const searchInput = document.getElementById('rule-search');
    const sizeFilter  = document.getElementById('rule-size-filter');
    const tableBody   = document.querySelector('#rules-table tbody');
    const visibleCount= document.getElementById('visible-count');

    function filterAndSearch() {
        if (!tableBody) return;
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const size  = sizeFilter  ? sizeFilter.value : 'all';
        const rows  = document.querySelectorAll('.rule-row');

        let shown = 0;
        let visIdx = 1;
        rows.forEach(row => {
            const ant  = row.querySelector('.col-antecedent')?.textContent.toLowerCase() || '';
            const con  = row.querySelector('.col-consequent')?.textContent.toLowerCase() || '';
            const rSz  = row.getAttribute('data-size');
            const ok   = (ant.includes(query) || con.includes(query)) && (size === 'all' || rSz === size);
            row.style.display = ok ? '' : 'none';
            if (ok) { row.querySelector('.col-no').textContent = visIdx++; shown++; }
        });
        if (visibleCount) visibleCount.textContent = `Menampilkan ${shown} dari ${rows.length} rules`;
    }

    if (searchInput) searchInput.addEventListener('keyup', filterAndSearch);
    if (sizeFilter)  sizeFilter.addEventListener('change', filterAndSearch);

    // Sort
    let currentSort = '', currentAsc = false;
    document.querySelectorAll('.sortable').forEach(header => {
        header.addEventListener('click', () => {
            if (!tableBody) return;
            const sort = header.getAttribute('data-sort');
            if (currentSort === sort) currentAsc = !currentAsc;
            else { currentSort = sort; currentAsc = false; }

            document.querySelectorAll('.sortable i').forEach(ic => ic.className = 'bi bi-arrow-down-up');
            header.querySelector('i').className = currentAsc ? 'bi bi-sort-up' : 'bi bi-sort-down';

            const rows = Array.from(tableBody.querySelectorAll('.rule-row'));
            rows.sort((a, b) => {
                const va = parseFloat(a.getAttribute('data-' + sort));
                const vb = parseFloat(b.getAttribute('data-' + sort));
                return currentAsc ? va - vb : vb - va;
            });
            rows.forEach(r => tableBody.appendChild(r));
            filterAndSearch();
        });
    });

    // CSV Export
    const exportBtn = document.getElementById('btn-export');
    if (exportBtn) {
        exportBtn.addEventListener('click', () => {
            let csv = [];
            document.querySelectorAll('#rules-table tr').forEach(row => {
                if (row.style.display === 'none') return;
                let cols = row.querySelectorAll('td,th');
                csv.push([...cols].map(c => '"' + c.innerText.replace(/\n/g,' ').trim().replace(/"/g,'""') + '"').join(','));
            });
            const blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
            const a    = document.createElement('a');
            a.href     = URL.createObjectURL(blob);
            a.download = 'AuraBeauty_AssociationRules.csv';
            a.style.display = 'none';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        });
    }

    @if($has_data && count($rules) > 0)
    const rawRules = @json(array_slice($rules, 0, 10));
    const allRules = @json($rules);

    // Bar chart — Top 10 Lift
    new Chart(document.getElementById('topRulesChart'), {
        type: 'bar',
        data: {
            labels: rawRules.map(r => {
                const n = r.antecedent + ' → ' + r.consequent;
                return n.length > 28 ? n.substr(0,28)+'…' : n;
            }),
            datasets: [{
                label: 'Lift Score',
                data: rawRules.map(r => r.lift),
                backgroundColor: rawRules.map((_, i) => {
                    if (i === 0) return 'rgba(245,158,11,0.85)';
                    if (i === 1) return 'rgba(156,163,175,0.75)';
                    if (i === 2) return 'rgba(180,83,9,0.75)';
                    return 'rgba(37,99,235,0.6)';
                }),
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: { display: false },
                title: { display: true, text: 'TOP 10 BUNDLES BY LIFT', align: 'start', font: { size: 13, weight: '700' }, padding: { bottom: 16 }, color: 'rgba(17,24,39,0.8)' }
            },
            scales: { x: { beginAtZero: true } }
        }
    });

    // Scatter — Support vs Confidence
    new Chart(document.getElementById('scatterChart'), {
        type: 'scatter',
        data: {
            datasets: [{
                label: 'Rules',
                data: allRules.map(r => ({ x: r.support * 100, y: r.confidence * 100, raw: r })),
                backgroundColor: allRules.map(r => r.lift > 2 ? 'rgba(16,185,129,0.7)' : (r.lift > 1 ? 'rgba(245,158,11,0.7)' : 'rgba(239,68,68,0.5)')),
                pointRadius: 5,
                pointHoverRadius: 9,
                pointBorderColor: '#FFFFFF',
                pointBorderWidth: 1,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                title: { display: true, text: 'SUPPORT vs CONFIDENCE', align: 'start', font: { size: 13, weight: '700' }, padding: { bottom: 16 }, color: 'rgba(17,24,39,0.8)' },
                tooltip: {
                    callbacks: {
                        label: ctx => {
                            const r = ctx.raw.raw;
                            return [
                                r.antecedent + ' → ' + r.consequent,
                                'Lift: ' + r.lift.toFixed(3)
                            ];
                        }
                    }
                }
            },
            scales: {
                x: { title: { display: true, text: 'SUPPORT (%)', font: { weight: '700', size: 11 } }, beginAtZero: true },
                y: { title: { display: true, text: 'CONFIDENCE (%)', font: { weight: '700', size: 11 } }, beginAtZero: true }
            }
        }
    });
    @endif
</script>
@endpush
