<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apriori Association Rules - AuraBeauty BI</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            background-color: #f8f9fa;
        }
        .hero {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
        }
        .kpi-card {
            transition: transform 0.15s;
        }
        .kpi-card:hover {
            transform: scale(1.03);
        }
        .table-actions .form-control,
        .table-actions .form-select {
            min-width: 180px;
        }
    </style>
</head>
<body>

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="d-none position-fixed top-0 start-0 w-100 h-100 bg-white d-flex flex-column justify-content-center align-items-center" style="z-index:9999">
        <div class="spinner-border text-primary mb-3" style="width:3rem;height:3rem" role="status"></div>
        <h4 class="fw-bold">Processing Data</h4>
        <p class="text-muted">Re-calculating Apriori rules...</p>
    </div>

    <!-- Header -->
    <header class="hero text-white py-5 mb-4">
        <div class="container">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
                <div>
                    <span class="badge bg-light text-primary fw-semibold text-uppercase mb-3">Market Basket Analysis</span>
                    <h1 class="display-4 fw-bold mb-1">AuraBeauty <span class="text-warning">BI</span></h1>
                    <p class="lead mb-0 opacity-75">Association Rules & Overstock Management</p>
                </div>
                <form id="analysis-form" action="{{ route('analytics.apriori.run') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-lg fw-bold">
                        <i class="bi bi-arrow-clockwise me-2"></i>Run Analysis
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="container pb-5">

        <!-- Alerts -->
        @if (session('success'))
            <div class="alert alert-success d-flex align-items-center gap-3" role="alert">
                <i class="bi bi-check-circle-fill fs-4"></i>
                <span class="fw-semibold">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger d-flex align-items-center gap-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                <span class="fw-semibold">{{ session('error') }}</span>
            </div>
        @endif

        <!-- KPI Cards -->
        <section class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card kpi-card h-100 border-primary border-2">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase fw-bold small">Total Rules Found</h6>
                        <h2 class="display-4 fw-bold mb-1">{{ $total_rules }}</h2>
                        <small class="text-primary fw-semibold">Valid item associations</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card kpi-card h-100 bg-primary text-white border-0">
                    <div class="card-body">
                        <h6 class="text-white-50 text-uppercase fw-bold small">Min Support</h6>
                        <h2 class="display-4 fw-bold mb-1">0.5<small class="fs-5 opacity-50">%</small></h2>
                        <small class="opacity-75 fw-semibold">&ge; 4 basket occurrences</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card kpi-card h-100 border-2">
                    <div class="card-body">
                        <h6 class="text-muted text-uppercase fw-bold small">Min Confidence</h6>
                        <h2 class="display-4 fw-bold mb-1">10<small class="fs-5 text-muted">%</small></h2>
                        <small class="text-primary fw-semibold">Base relation probability</small>
                    </div>
                </div>
            </div>
        </section>

        @if(!$has_data)
            <!-- No Data State -->
            <div class="card text-center py-5">
                <div class="card-body">
                    <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:80px;height:80px">
                        <i class="bi bi-bar-chart fs-1 text-muted"></i>
                    </div>
                    <h3 class="fw-bold">No Data Available</h3>
                    <p class="text-muted mb-4">Run the Apriori analysis to generate rules and insights.</p>
                    <form action="{{ route('analytics.apriori.run') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-lg fw-bold">
                            <i class="bi bi-play-fill me-2"></i>Run First Analysis
                        </button>
                    </form>
                </div>
            </div>
        @else

            <!-- Charts Section -->
            <section class="card mb-5">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-1">Visual Analysis</h4>
                    <p class="text-muted mb-4">Data mapping for intuition.</p>
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="bg-light rounded p-3" style="height:400px">
                                <canvas id="topRulesChart"></canvas>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="bg-light rounded p-3" style="height:400px">
                                <canvas id="scatterChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Overstock Recommendations -->
            <section class="mb-5">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-3">
                    <div>
                        <h4 class="fw-bold mb-1">Overstock Strategy</h4>
                        <p class="text-muted mb-0">Bundle slow-moving items with high-affinity products.</p>
                    </div>
                    <span class="badge bg-warning text-dark fw-bold text-uppercase">Actionable Insights</span>
                </div>

                @if(count($overstockBundles) == 0)
                    <div class="alert alert-success text-center py-4">
                        <p class="fw-bold mb-0">Warehouse is optimized. No urgent overstock identified.</p>
                    </div>
                @else
                    <div class="row g-4">
                        @foreach($overstockBundles as $index => $bundle)
                            <div class="col-md-6 col-lg-4">
                                <div class="card kpi-card h-100">
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width:48px;height:48px">
                                                <i class="bi bi-graph-up-arrow text-primary"></i>
                                            </div>
                                            <span class="badge bg-primary fs-6">LIFT: {{ number_format($bundle['lift'], 2) }}</span>
                                        </div>

                                        <h5 class="fw-bold mb-3">{{ $bundle['overstock_item'] }}</h5>

                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <div class="bg-light rounded p-2 text-center">
                                                    <small class="text-muted fw-bold text-uppercase d-block">Stock</small>
                                                    <strong>{{ $bundle['stock'] }}</strong>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded p-2 text-center">
                                                    <small class="text-danger fw-bold text-uppercase d-block">Stagnant</small>
                                                    <strong class="text-danger">{{ $bundle['days_left'] }}d</strong>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <small class="text-muted fw-bold text-uppercase d-block mb-1">Bundle With</small>
                                            <div class="bg-primary text-white rounded p-3 d-flex justify-content-between align-items-center fw-bold">
                                                {{ $bundle['bundle_with'] }}
                                                <i class="bi bi-arrow-right"></i>
                                            </div>
                                        </div>

                                        <div class="mt-auto d-flex justify-content-between pt-3 border-top">
                                            <div>
                                                <small class="text-muted fw-bold text-uppercase d-block">Sup</small>
                                                <strong>{{ number_format($bundle['support'] * 100, 2) }}%</strong>
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted fw-bold text-uppercase d-block">Conf</small>
                                                <strong>{{ number_format($bundle['confidence'] * 100, 2) }}%</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <!-- Table Section -->
            <section class="mb-5">
                <div class="bg-dark text-white rounded p-4 mb-3">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                        <div>
                            <h4 class="fw-bold mb-1">Association Rules Data</h4>
                            <p class="mb-0 opacity-50">Complete dataset of all discovered product relationships.</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2 table-actions">
                            <input type="text" id="rule-search" class="form-control" placeholder="Search rules...">
                            <select id="rule-size-filter" class="form-select">
                                <option value="all">All Sizes</option>
                                <option value="2">2 Items</option>
                                <option value="3">3 Items</option>
                                <option value="4">4 Items</option>
                            </select>
                            <button id="btn-export" class="btn btn-warning fw-bold">
                                <i class="bi bi-download me-1"></i>CSV
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="table-responsive">
                        <table id="rules-table" class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Antecedents (If)</th>
                                    <th>Consequents (Then)</th>
                                    <th class="sortable" data-sort="support" style="cursor:pointer">Support <i class="bi bi-arrow-down-up"></i></th>
                                    <th class="sortable" data-sort="confidence" style="cursor:pointer">Confidence <i class="bi bi-arrow-down-up"></i></th>
                                    <th class="sortable" data-sort="lift" style="cursor:pointer">Lift <i class="bi bi-arrow-down-up"></i></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rules as $index => $rule)
                                    @php
                                        $ant_count = substr_count($rule['antecedent'], ',') + 1;
                                        $con_count = substr_count($rule['consequent'], ',') + 1;
                                        $total_items = $ant_count + $con_count;
                                    @endphp
                                    <tr class="rule-row" data-size="{{ $total_items }}" data-support="{{ $rule['support'] }}" data-confidence="{{ $rule['confidence'] }}" data-lift="{{ $rule['lift'] }}">
                                        <td class="col-no text-muted fw-bold">{{ $index + 1 }}</td>
                                        <td class="col-antecedent">{{ $rule['antecedent'] }}</td>
                                        <td class="col-consequent fw-bold text-primary">{{ $rule['consequent'] }}</td>
                                        <td>{{ number_format($rule['support'] * 100, 2) }}%</td>
                                        <td>{{ number_format($rule['confidence'] * 100, 2) }}%</td>
                                        <td><span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">{{ number_format($rule['lift'], 3) }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        @endif

        <!-- Footer -->
        <footer class="pt-4 mt-5 border-top d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <h5 class="fw-bold mb-0">AuraBeauty <span class="text-warning">BI</span></h5>
            <small class="text-muted text-uppercase fw-bold">&copy; 2026. Data Driven.</small>
        </footer>
    </main>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Loading Overlay
        const analysisForm = document.getElementById('analysis-form');
        if (analysisForm) {
            analysisForm.addEventListener('submit', () => {
                document.getElementById('loading-overlay').classList.remove('d-none');
                document.getElementById('loading-overlay').classList.add('d-flex');
            });
        }

        // Filter & Search Logic
        const searchInput = document.getElementById('rule-search');
        const sizeFilter = document.getElementById('rule-size-filter');
        const tableBody = document.querySelector('#rules-table tbody');

        function filterAndSearch() {
            if (!tableBody) return;
            const query = (searchInput ? searchInput.value.toLowerCase().trim() : '');
            const size = (sizeFilter ? sizeFilter.value : 'all');
            const rows = document.querySelectorAll('.rule-row');

            let visibleIndex = 1;
            rows.forEach(row => {
                const antecedent = row.querySelector('.col-antecedent').textContent.toLowerCase();
                const consequent = row.querySelector('.col-consequent').textContent.toLowerCase();
                const rowSize = row.getAttribute('data-size');

                const matchesSearch = antecedent.includes(query) || consequent.includes(query);
                const matchesSize = (size === 'all' || rowSize === size);

                if (matchesSearch && matchesSize) {
                    row.style.display = '';
                    row.querySelector('.col-no').textContent = visibleIndex++;
                } else {
                    row.style.display = 'none';
                }
            });
        }

        if (searchInput) searchInput.addEventListener('keyup', filterAndSearch);
        if (sizeFilter) sizeFilter.addEventListener('change', filterAndSearch);

        // Sorting Logic
        const headers = document.querySelectorAll('.sortable');
        let currentSortColumn = '';
        let currentSortAsc = false;

        headers.forEach(header => {
            header.addEventListener('click', () => {
                if (!tableBody) return;
                const sortType = header.getAttribute('data-sort');

                if (currentSortColumn === sortType) {
                    currentSortAsc = !currentSortAsc;
                } else {
                    currentSortColumn = sortType;
                    currentSortAsc = false;
                }

                headers.forEach(h => {
                    const icon = h.querySelector('i');
                    if (h !== header) {
                        icon.className = 'bi bi-arrow-down-up';
                    }
                });

                const icon = header.querySelector('i');
                icon.className = currentSortAsc ? 'bi bi-sort-up' : 'bi bi-sort-down';

                const rowsArray = Array.from(tableBody.querySelectorAll('.rule-row'));
                rowsArray.sort((a, b) => {
                    const valA = parseFloat(a.getAttribute('data-' + sortType));
                    const valB = parseFloat(b.getAttribute('data-' + sortType));
                    if (valA < valB) return currentSortAsc ? -1 : 1;
                    if (valA > valB) return currentSortAsc ? 1 : -1;
                    return 0;
                });

                rowsArray.forEach(row => tableBody.appendChild(row));
                filterAndSearch();
            });
        });

        // CSV Export
        const exportBtn = document.getElementById('btn-export');
        if (exportBtn) {
            exportBtn.addEventListener('click', function () {
                let csv = [];
                const rows = document.querySelectorAll('#rules-table tr');

                for (let i = 0; i < rows.length; i++) {
                    let row = [], cols = rows[i].querySelectorAll('td, th');
                    if (rows[i].style.display === 'none') continue;
                    for (let j = 0; j < cols.length; j++) {
                        let text = cols[j].innerText.replace(/[\n\r]+/g, ' ').trim();
                        row.push('"' + text.replace(/"/g, '""') + '"');
                    }
                    csv.push(row.join(','));
                }
                let csvFile = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
                let downloadLink = document.createElement('a');
                downloadLink.download = 'AuraBeauty_Rules.csv';
                downloadLink.href = window.URL.createObjectURL(csvFile);
                downloadLink.style.display = 'none';
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);
            });
        }

        // Chart.js
        @if($has_data && count($rules) > 0)
        Chart.defaults.font.family = 'system-ui, -apple-system, sans-serif';
        Chart.defaults.font.weight = '600';

        const rawRules = @json(array_slice($rules, 0, 10));

        // Horizontal Bar Chart
        const topRulesCtx = document.getElementById('topRulesChart').getContext('2d');
        const labels = rawRules.map(r => {
            const name = r.antecedent + ' → ' + r.consequent;
            return name.length > 25 ? name.substring(0, 25) + '...' : name;
        });
        const liftData = rawRules.map(r => r.lift);

        new Chart(topRulesCtx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Lift Score',
                    data: liftData,
                    backgroundColor: '#0d6efd',
                    borderRadius: 4,
                    barThickness: 24
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    title: {
                        display: true,
                        text: 'TOP 10 BUNDLES (LIFT)',
                        font: { size: 16, weight: 'bold' },
                        padding: { bottom: 20 },
                        align: 'start'
                    }
                },
                scales: {
                    x: { beginAtZero: true },
                    y: { beginAtZero: true }
                }
            }
        });

        // Scatter Plot
        const allRules = @json($rules);
        const scatterCtx = document.getElementById('scatterChart').getContext('2d');
        const scatterData = allRules.map(r => ({
            x: r.support * 100,
            y: r.confidence * 100,
            raw: r
        }));

        new Chart(scatterCtx, {
            type: 'scatter',
            data: {
                datasets: [{
                    label: 'Rules',
                    data: scatterData,
                    backgroundColor: '#198754',
                    pointRadius: 6,
                    pointHoverRadius: 10,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    title: {
                        display: true,
                        text: 'SUPPORT VS CONFIDENCE',
                        font: { size: 16, weight: 'bold' },
                        padding: { bottom: 20 },
                        align: 'start'
                    },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                const r = ctx.raw.raw;
                                return r.antecedent + ' → ' + r.consequent + ' (Lift: ' + r.lift.toFixed(2) + ')';
                            }
                        }
                    }
                },
                scales: {
                    x: { title: { display: true, text: 'SUPPORT (%)', font: { weight: 'bold' } }, beginAtZero: true },
                    y: { title: { display: true, text: 'CONFIDENCE (%)', font: { weight: 'bold' } }, beginAtZero: true }
                }
            }
        });
        @endif
    </script>
</body>
</html>
