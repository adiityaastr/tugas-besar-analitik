<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apriori Association Rules - AuraBeauty BI</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        flat: {
                            bg: '#FFFFFF',
                            fg: '#111827',
                            primary: '#3B82F6',
                            secondary: '#10B981',
                            accent: '#F59E0B',
                            muted: '#F3F4F6',
                            border: '#E5E7EB',
                        }
                    },
                    fontFamily: {
                        sans: ['"Outfit"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            background-color: #F3F4F6; /* Gray background for contrast against white cards */
            color: #111827;
            font-family: 'Outfit', sans-serif;
            scroll-behavior: smooth;
        }
        
        /* Loading Overlay */
        .overlay {
            position: fixed;
            inset: 0;
            background: #FFFFFF;
            z-index: 9999;
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .spinner {
            width: 48px;
            height: 48px;
            border: 4px solid #F3F4F6;
            border-top-color: #3B82F6;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { 100% { transform: rotate(360deg); } }
    </style>
</head>
<body class="antialiased min-h-screen selection:bg-flat-primary selection:text-white">

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="overlay">
        <div class="spinner mb-6"></div>
        <h2 class="text-3xl font-bold text-flat-fg tracking-tight">Processing Data</h2>
        <p class="text-flat-fg/60 mt-2 font-medium">Re-calculating Apriori rules...</p>
    </div>

    <!-- Header Section (Solid Blue Block) -->
    <header class="bg-flat-primary pt-20 pb-24 px-6 relative overflow-hidden">
        <!-- Abstract geometric decoration -->
        <div class="absolute -top-32 -right-32 w-96 h-96 bg-white/10 rounded-full"></div>
        <div class="absolute bottom-0 left-10 w-64 h-64 bg-white/5 rotate-45 transform origin-bottom-left"></div>
        
        <div class="max-w-7xl mx-auto relative z-10 flex flex-col md:flex-row justify-between items-start md:items-end gap-8">
            <div>
                <span class="inline-block px-4 py-1.5 bg-white/20 text-white font-bold tracking-widest uppercase text-xs rounded-md mb-6">Market Basket Analysis</span>
                <h1 class="text-6xl md:text-7xl font-extrabold tracking-tighter text-white mb-4 leading-none">
                    AuraBeauty <span class="text-flat-accent">BI</span>
                </h1>
                <p class="text-xl text-white/80 font-medium tracking-wide">Association Rules & Overstock Management</p>
            </div>
            
            <form id="analysis-form" action="{{ route('analytics.apriori.run') }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center gap-3 px-8 py-4 bg-flat-accent hover:bg-yellow-400 text-flat-fg rounded-md font-bold tracking-widest uppercase text-sm transition-all duration-200 hover:scale-105 shadow-none border-2 border-transparent">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                    Run Analysis
                </button>
            </form>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-12 -mt-12 relative z-20">
        
        <!-- Alerts -->
        @if (session('success'))
            <div class="bg-flat-secondary text-white border-2 border-green-600 px-6 py-5 rounded-md mb-12 flex items-center gap-4">
                <div class="bg-white/20 p-2 rounded-md">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <span class="font-bold text-lg tracking-wide">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-500 text-white border-2 border-red-700 px-6 py-5 rounded-md mb-12 flex items-center gap-4">
                <div class="bg-white/20 p-2 rounded-md">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
                <span class="font-bold text-lg tracking-wide">{{ session('error') }}</span>
            </div>
        @endif

        <!-- KPI Grid -->
        <section class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-24">
            <div class="bg-white rounded-lg p-8 border-2 border-flat-border flex flex-col justify-between group cursor-pointer transition-all duration-200 hover:scale-[1.02] hover:border-flat-primary">
                <h3 class="text-xs font-bold tracking-widest uppercase text-flat-fg/50 mb-6">Total Rules Found</h3>
                <div>
                    <p class="text-6xl font-extrabold text-flat-fg tracking-tight">{{ $total_rules }}</p>
                    <p class="mt-2 text-sm font-medium text-flat-primary">Valid item associations</p>
                </div>
            </div>
            
            <div class="bg-flat-primary text-white rounded-lg p-8 border-2 border-blue-600 flex flex-col justify-between group cursor-pointer transition-all duration-200 hover:scale-[1.02] hover:bg-blue-600">
                <h3 class="text-xs font-bold tracking-widest uppercase text-white/70 mb-6">Min Support</h3>
                <div>
                    <p class="text-6xl font-extrabold tracking-tight">0.5<span class="text-3xl text-white/50">%</span></p>
                    <p class="mt-2 text-sm font-medium text-white/90">&ge; 4 basket occurrences</p>
                </div>
            </div>
            
            <div class="bg-white rounded-lg p-8 border-2 border-flat-border flex flex-col justify-between group cursor-pointer transition-all duration-200 hover:scale-[1.02] hover:border-flat-primary">
                <h3 class="text-xs font-bold tracking-widest uppercase text-flat-fg/50 mb-6">Min Confidence</h3>
                <div>
                    <p class="text-6xl font-extrabold text-flat-fg tracking-tight">10<span class="text-3xl text-flat-fg/30">%</span></p>
                    <p class="mt-2 text-sm font-medium text-flat-primary">Base relation probability</p>
                </div>
            </div>
        </section>

        @if(!$has_data)
            <div class="text-center py-32 bg-white rounded-lg border-2 border-flat-border shadow-none">
                <div class="mx-auto w-20 h-20 bg-flat-muted rounded-full flex items-center justify-center mb-6">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-flat-fg/40"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                </div>
                <h3 class="text-4xl font-extrabold mb-4 text-flat-fg tracking-tight">No Data Available</h3>
                <p class="text-lg text-flat-fg/60 mb-10 font-medium">Run the Apriori analysis to generate rules and insights.</p>
                <form action="{{ route('analytics.apriori.run') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-8 py-4 bg-flat-primary hover:bg-blue-600 text-white rounded-md font-bold tracking-widest uppercase transition-all duration-200 hover:scale-105">
                        Run First Analysis
                    </button>
                </form>
            </div>
        @else
            <!-- Charts Section (Color Block Background) -->
            <section class="mb-24 bg-white p-8 md:p-12 rounded-lg border-2 border-flat-border">
                <div class="mb-12">
                    <h2 class="text-4xl font-extrabold text-flat-fg mb-2 tracking-tight">Visual Analysis</h2>
                    <p class="text-lg font-medium text-flat-fg/60">Data mapping for intuition.</p>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="bg-flat-muted p-6 rounded-md border-2 border-flat-border h-[400px]">
                        <canvas id="topRulesChart"></canvas>
                    </div>
                    <div class="bg-flat-muted p-6 rounded-md border-2 border-flat-border h-[400px]">
                        <canvas id="scatterChart"></canvas>
                    </div>
                </div>
            </section>

            <!-- Overstock Recommendations -->
            <section class="mb-24">
                <div class="mb-12 flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div>
                        <h2 class="text-4xl font-extrabold text-flat-fg mb-2 tracking-tight">Overstock Strategy</h2>
                        <p class="text-lg font-medium text-flat-fg/60">Bundle slow-moving items with high-affinity products.</p>
                    </div>
                    <span class="px-4 py-2 bg-flat-accent text-flat-fg font-bold uppercase tracking-widest text-xs rounded-md">Actionable Insights</span>
                </div>

                @if(count($overstockBundles) == 0)
                    <div class="bg-flat-secondary/10 p-12 text-center rounded-lg border-2 border-flat-secondary/30">
                        <p class="text-xl text-flat-secondary font-bold tracking-tight">Warehouse is optimized. No urgent overstock identified.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($overstockBundles as $index => $bundle)
                            <!-- Strict flat cards -->
                            <div class="bg-white rounded-lg p-8 border-2 border-flat-border flex flex-col group cursor-pointer transition-all duration-200 hover:scale-[1.02] hover:border-flat-primary">
                                
                                <div class="flex justify-between items-start mb-6">
                                    <div class="w-12 h-12 bg-flat-muted rounded-full flex items-center justify-center text-flat-fg group-hover:scale-110 transition-transform duration-200 group-hover:bg-flat-primary group-hover:text-white">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                                    </div>
                                    <span class="text-sm font-extrabold text-flat-primary bg-blue-50 px-3 py-1 rounded-md">LIFT: {{ number_format($bundle['lift'], 2) }}</span>
                                </div>
                                
                                <h3 class="text-2xl font-bold text-flat-fg mb-4 leading-tight tracking-tight">{{ $bundle['overstock_item'] }}</h3>
                                
                                <div class="flex gap-4 mb-6 pb-6 border-b-2 border-flat-border">
                                    <div class="bg-flat-muted px-4 py-2 rounded-md flex-1">
                                        <span class="block text-xs font-bold text-flat-fg/50 uppercase tracking-widest mb-1">Stock</span>
                                        <span class="text-lg font-extrabold text-flat-fg">{{ $bundle['stock'] }}</span>
                                    </div>
                                    <div class="bg-red-50 border-2 border-red-100 px-4 py-2 rounded-md flex-1">
                                        <span class="block text-xs font-bold text-red-400 uppercase tracking-widest mb-1">Stagnant</span>
                                        <span class="text-lg font-extrabold text-red-600">{{ $bundle['days_left'] }}d</span>
                                    </div>
                                </div>
                                
                                <div class="mb-2">
                                    <span class="text-xs font-bold uppercase tracking-widest text-flat-fg/50 block mb-2">Bundle With</span>
                                    <div class="bg-flat-primary text-white p-4 rounded-md font-bold text-lg flex items-center justify-between">
                                        {{ $bundle['bundle_with'] }}
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                    </div>
                                </div>
                                
                                <div class="mt-auto flex justify-between pt-6 text-sm">
                                    <div><span class="text-xs uppercase tracking-widest text-flat-fg/40 block font-bold">Sup</span><strong class="text-flat-fg">{{ number_format($bundle['support'] * 100, 2) }}%</strong></div>
                                    <div class="text-right"><span class="text-xs uppercase tracking-widest text-flat-fg/40 block font-bold">Conf</span><strong class="text-flat-fg">{{ number_format($bundle['confidence'] * 100, 2) }}%</strong></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <!-- Table Section -->
            <section class="mb-24">
                <div class="bg-flat-fg p-8 md:p-12 rounded-lg text-white mb-6 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3"></div>
                    <div class="relative z-10 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8">
                        <div>
                            <h2 class="text-4xl font-extrabold mb-2 tracking-tight">Association Rules Data</h2>
                            <p class="text-lg font-medium text-white/60">Complete dataset of all discovered product relationships.</p>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row gap-4 w-full lg:w-auto">
                            <input type="text" id="rule-search" class="px-6 py-4 bg-white/10 border-2 border-white/20 text-white rounded-md text-sm font-bold outline-none focus:border-flat-primary focus:bg-white focus:text-flat-fg placeholder:text-white/40 w-full sm:w-64 transition-colors" placeholder="SEARCH RULES...">
                            
                            <select id="rule-size-filter" class="px-6 py-4 bg-white/10 border-2 border-white/20 text-white rounded-md text-sm font-bold outline-none focus:border-flat-primary focus:bg-white focus:text-flat-fg cursor-pointer appearance-none">
                                <option class="text-flat-fg" value="all">ALL SIZES</option>
                                <option class="text-flat-fg" value="2">2 ITEMS</option>
                                <option class="text-flat-fg" value="3">3 ITEMS</option>
                                <option class="text-flat-fg" value="4">4 ITEMS</option>
                            </select>
                            
                            <button id="btn-export" class="px-8 py-4 bg-flat-accent hover:bg-yellow-400 text-flat-fg rounded-md font-bold tracking-widest uppercase text-sm transition-all duration-200 hover:scale-105 shadow-none flex justify-center items-center gap-2">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                CSV
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg border-2 border-flat-border overflow-hidden">
                    <div class="overflow-x-auto">
                        <table id="rules-table" class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-flat-muted border-b-2 border-flat-border">
                                    <th class="py-5 px-6 font-bold text-xs tracking-widest uppercase text-flat-fg/60">ID</th>
                                    <th class="py-5 px-6 font-bold text-xs tracking-widest uppercase text-flat-fg/60">Antecedents (If)</th>
                                    <th class="py-5 px-6 font-bold text-xs tracking-widest uppercase text-flat-fg/60">Consequents (Then)</th>
                                    <th class="sortable py-5 px-6 font-bold text-xs tracking-widest uppercase text-flat-fg/60 cursor-pointer hover:text-flat-primary transition-colors" data-sort="support">Support ⇅</th>
                                    <th class="sortable py-5 px-6 font-bold text-xs tracking-widest uppercase text-flat-fg/60 cursor-pointer hover:text-flat-primary transition-colors" data-sort="confidence">Confidence ⇅</th>
                                    <th class="sortable py-5 px-6 font-bold text-xs tracking-widest uppercase text-flat-fg/60 cursor-pointer hover:text-flat-primary transition-colors" data-sort="lift">Lift ⇅</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y-2 divide-flat-muted text-sm font-medium">
                                @foreach($rules as $index => $rule)
                                    @php
                                        $ant_count = substr_count($rule['antecedent'], ',') + 1;
                                        $con_count = substr_count($rule['consequent'], ',') + 1;
                                        $total_items = $ant_count + $con_count;
                                    @endphp
                                    <tr class="rule-row hover:bg-flat-muted/50 transition-colors duration-150" data-size="{{ $total_items }}" data-support="{{ $rule['support'] }}" data-confidence="{{ $rule['confidence'] }}" data-lift="{{ $rule['lift'] }}">
                                        <td class="col-no py-5 px-6 text-flat-fg/40 font-bold">{{ $index + 1 }}</td>
                                        <td class="col-antecedent py-5 px-6 text-flat-fg">{{ $rule['antecedent'] }}</td>
                                        <td class="col-consequent py-5 px-6 font-bold text-flat-primary">{{ $rule['consequent'] }}</td>
                                        <td class="py-5 px-6 text-flat-fg">{{ number_format($rule['support'] * 100, 2) }}%</td>
                                        <td class="py-5 px-6 text-flat-fg">{{ number_format($rule['confidence'] * 100, 2) }}%</td>
                                        <td class="py-5 px-6">
                                            <span class="inline-flex items-center px-3 py-1 rounded-md text-xs font-bold bg-flat-secondary/10 text-flat-secondary border-2 border-flat-secondary/20">
                                                {{ number_format($rule['lift'], 3) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        @endif

        <footer class="mt-24 pt-8 border-t-4 border-flat-fg flex flex-col md:flex-row justify-between items-center gap-4">
            <h1 class="text-xl font-extrabold tracking-tighter text-flat-fg">
                AuraBeauty <span class="text-flat-accent">BI</span>
            </h1>
            <p class="font-bold text-sm text-flat-fg/40 uppercase tracking-widest">&copy; 2026. Data Driven.</p>
        </footer>
    </main>

    <!-- Scripts -->
    <script>
        // Loading Overlay
        const analysisForm = document.getElementById('analysis-form');
        if (analysisForm) {
            analysisForm.addEventListener('submit', () => {
                const overlay = document.getElementById('loading-overlay');
                overlay.style.display = 'flex';
            });
        }

        // Filter & Search Logic
        const searchInput = document.getElementById('rule-search');
        const sizeFilter = document.getElementById('rule-size-filter');
        const tableBody = document.querySelector('#rules-table tbody');
        
        function filterAndSearch() {
            if(!tableBody) return;
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
                if(!tableBody) return;
                const sortType = header.getAttribute('data-sort');
                
                if (currentSortColumn === sortType) {
                    currentSortAsc = !currentSortAsc;
                } else {
                    currentSortColumn = sortType;
                    currentSortAsc = false;
                }
                
                headers.forEach(h => {
                    if(h !== header) {
                        h.innerHTML = h.innerHTML.replace(' ↑', ' ⇅').replace(' ↓', ' ⇅');
                    }
                });
                
                if(header.innerHTML.includes('⇅')) {
                    header.innerHTML = header.innerHTML.replace(' ⇅', currentSortAsc ? ' ↑' : ' ↓');
                } else if(header.innerHTML.includes('↑')) {
                    header.innerHTML = header.innerHTML.replace(' ↑', ' ↓');
                } else {
                    header.innerHTML = header.innerHTML.replace(' ↓', ' ↑');
                }
                
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
            exportBtn.addEventListener('click', function() {
                let csv = [];
                const rows = document.querySelectorAll('#rules-table tr');
                
                for (let i = 0; i < rows.length; i++) {
                    let row = [], cols = rows[i].querySelectorAll('td, th');
                    if (rows[i].style.display === 'none') continue;
                    for (let j = 0; j < cols.length; j++) {
                        let text = cols[j].innerText.replace(/[\n\r]+/g, ' ').replace(/[↑↓⇅]/g, '').trim();
                        row.push('"' + text.replace(/"/g, '""') + '"');
                    }
                    csv.push(row.join(','));
                }
                let csvFile = new Blob([csv.join('\n')], {type: 'text/csv;charset=utf-8;'});
                let downloadLink = document.createElement('a');
                downloadLink.download = 'AuraBeauty_Flat_Rules.csv';
                downloadLink.href = window.URL.createObjectURL(csvFile);
                downloadLink.style.display = 'none';
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);
            });
        }

        // Chart.js Configuration for Flat Design
        @if($has_data && count($rules) > 0)
        Chart.defaults.color = '#111827';
        Chart.defaults.font.family = '"Outfit", sans-serif';
        Chart.defaults.font.weight = '600';
        Chart.defaults.scale.grid.color = '#E5E7EB';
        Chart.defaults.scale.grid.lineWidth = 2; // Thicker grid lines

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
                    backgroundColor: '#3B82F6', // Solid Primary Blue
                    borderRadius: 0, // Sharp edges
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
                        font: { family: '"Outfit", sans-serif', size: 18, weight: 800 },
                        padding: { bottom: 20 },
                        align: 'start'
                    },
                    tooltip: {
                        backgroundColor: '#111827',
                        titleFont: { family: '"Outfit", sans-serif', size: 14, weight: 800 },
                        bodyFont: { family: '"Outfit", sans-serif', size: 14, weight: 600 },
                        padding: 16,
                        cornerRadius: 6,
                        callbacks: {
                            title: function(ctx) { return rawRules[ctx[0].dataIndex].antecedent + ' → ' + rawRules[ctx[0].dataIndex].consequent; }
                        }
                    }
                },
                scales: { 
                    x: { beginAtZero: true, border: { display: false } },
                    y: { border: { display: false } }
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
                    backgroundColor: '#10B981', // Solid Emerald
                    pointRadius: 6,
                    pointHoverRadius: 10,
                    pointBorderColor: '#FFFFFF',
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
                        font: { family: '"Outfit", sans-serif', size: 18, weight: 800 },
                        padding: { bottom: 20 },
                        align: 'start'
                    },
                    tooltip: {
                        backgroundColor: '#111827',
                        padding: 16,
                        cornerRadius: 6,
                        callbacks: {
                            label: function(ctx) {
                                const r = ctx.raw.raw;
                                return r.antecedent + ' → ' + r.consequent + ' (Lift: ' + r.lift.toFixed(2) + ')';
                            }
                        }
                    }
                },
                scales: {
                    x: { title: { display: true, text: 'SUPPORT (%)', font: { weight: 800, size: 12 } }, beginAtZero: true },
                    y: { title: { display: true, text: 'CONFIDENCE (%)', font: { weight: 800, size: 12 } }, beginAtZero: true }
                }
            }
        });
        @endif
    </script>
</body>
</html>
