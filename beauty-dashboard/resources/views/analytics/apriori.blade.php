<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apriori Association Rules - AuraBeauty BI Console</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Source+Sans+3:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        botanical: {
                            bg: '#F9F8F4',
                            fg: '#2D3A31',
                            primary: '#8C9A84',
                            secondary: '#DCCFC2',
                            border: '#E6E2DA',
                            interactive: '#C27B66',
                        }
                    },
                    fontFamily: {
                        serif: ['"Playfair Display"', 'serif'],
                        sans: ['"Source Sans 3"', 'sans-serif'],
                    },
                    boxShadow: {
                        'soft': '0 4px 6px -1px rgba(45, 58, 49, 0.05), 0 10px 15px -3px rgba(45, 58, 49, 0.05)',
                        'hover': '0 20px 40px -10px rgba(45, 58, 49, 0.08)',
                    }
                }
            }
        }
    </script>
    
    <style>
        body {
            background-color: #F9F8F4;
            color: #2D3A31;
            /* Smooth scrolling */
            scroll-behavior: smooth;
        }
        /* Paper grain overlay (CRITICAL for Botanical style) */
        .paper-grain {
            pointer-events: none;
            position: fixed;
            inset: 0;
            z-index: 50;
            opacity: 0.015;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 400 400' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
            background-repeat: repeat;
        }
        
        .overlay {
            position: fixed;
            inset: 0;
            background: rgba(249, 248, 244, 0.9);
            backdrop-filter: blur(4px);
            z-index: 9999;
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 3px solid #DCCFC2;
            border-top-color: #8C9A84;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { 100% { transform: rotate(360deg); } }
    </style>
</head>
<body class="font-sans antialiased relative min-h-screen selection:bg-botanical-primary/20">
    <!-- Paper Grain Overlay -->
    <div class="paper-grain"></div>

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="overlay transition-opacity duration-500">
        <div class="spinner mb-6"></div>
        <h2 class="font-serif text-3xl font-semibold text-botanical-fg">Memproses Analisis...</h2>
        <p class="text-botanical-fg/70 mt-2">Harmonisasi data sedang berlangsung secara natural.</p>
    </div>

    <div class="max-w-7xl mx-auto px-6 py-16 md:py-24 relative z-10">
        
        <!-- Header Section -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-end mb-16 md:mb-24 gap-8 border-b border-botanical-border pb-12">
            <div>
                <h1 class="font-serif text-6xl md:text-7xl font-semibold tracking-tight text-botanical-fg mb-4 leading-tight">
                    Aura<span class="italic text-botanical-primary">Beauty</span>
                </h1>
                <p class="text-xl text-botanical-fg/70 font-medium tracking-wide">Pola Asosiasi & Ekosistem Produk</p>
            </div>
            
            <form id="analysis-form" action="{{ route('analytics.apriori.run') }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center gap-3 px-8 py-4 bg-botanical-primary hover:bg-botanical-interactive text-white rounded-full font-semibold tracking-widest uppercase text-sm transition-all duration-500 shadow-soft hover:shadow-hover hover:-translate-y-1">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                    Segarkan Data
                </button>
            </form>
        </header>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="bg-white border border-botanical-border text-botanical-fg px-8 py-6 rounded-3xl mb-16 flex items-center gap-4 shadow-soft">
                <div class="p-2 bg-botanical-secondary/30 rounded-full text-botanical-primary">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <span class="font-medium text-lg">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-botanical-bg border border-botanical-interactive/30 text-botanical-interactive px-8 py-6 rounded-3xl mb-16 flex items-center gap-4 shadow-soft">
                <div class="p-2 bg-botanical-interactive/10 rounded-full">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                </div>
                <span class="font-medium text-lg">{{ session('error') }}</span>
            </div>
        @endif

        <!-- KPIs -->
        <section class="grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-12 mb-24 md:mb-32">
            <div class="bg-white rounded-3xl p-10 shadow-soft border border-botanical-border hover:-translate-y-2 transition-all duration-500 flex flex-col justify-between">
                <h3 class="text-sm font-semibold tracking-widest uppercase text-botanical-fg/50 mb-6">Total Pola Ditemukan</h3>
                <div>
                    <p class="font-serif text-6xl text-botanical-fg">{{ $total_rules }}</p>
                    <p class="mt-4 text-sm text-botanical-fg/70">Kombinasi bundling organik dari transaksi</p>
                </div>
            </div>
            
            <div class="bg-botanical-secondary/20 rounded-3xl p-10 shadow-soft border border-botanical-border hover:-translate-y-2 transition-all duration-500 flex flex-col justify-between md:translate-y-6">
                <h3 class="text-sm font-semibold tracking-widest uppercase text-botanical-fg/50 mb-6">Batas Natural (Support)</h3>
                <div>
                    <p class="font-serif text-6xl text-botanical-fg">0.5<span class="text-3xl text-botanical-fg/50">%</span></p>
                    <p class="mt-4 text-sm text-botanical-fg/70">Mewakili setidaknya 4 keranjang pelanggan</p>
                </div>
            </div>
            
            <div class="bg-white rounded-3xl p-10 shadow-soft border border-botanical-border hover:-translate-y-2 transition-all duration-500 flex flex-col justify-between">
                <h3 class="text-sm font-semibold tracking-widest uppercase text-botanical-fg/50 mb-6">Keyakinan (Confidence)</h3>
                <div>
                    <p class="font-serif text-6xl text-botanical-fg">10<span class="text-3xl text-botanical-fg/50">%</span></p>
                    <p class="mt-4 text-sm text-botanical-fg/70">Peluang minimal relasi dianggap bertumbuh</p>
                </div>
            </div>
        </section>

        @if(!$has_data)
            <div class="text-center py-32 bg-white rounded-3xl border border-botanical-border shadow-soft">
                <h3 class="font-serif text-4xl mb-4 text-botanical-fg">Ekosistem Belum Tumbuh</h3>
                <p class="text-lg text-botanical-fg/70 mb-10 max-w-lg mx-auto">Sistem belum menganalisis data riwayat transaksi Anda. Silakan mulai proses untuk menemukan pola organik antar produk.</p>
                <form action="{{ route('analytics.apriori.run') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-10 py-4 bg-botanical-primary hover:bg-botanical-interactive text-white rounded-full font-semibold tracking-wider transition-colors duration-500 shadow-soft hover:shadow-hover">
                        Tumbuhkan Analisis
                    </button>
                </form>
            </div>
        @else
            <!-- Charts Section -->
            <section class="mb-32">
                <div class="mb-16">
                    <h2 class="font-serif text-4xl md:text-5xl text-botanical-fg mb-4">Harmoni <span class="italic text-botanical-primary">Visual</span></h2>
                    <p class="text-xl text-botanical-fg/70">Pemetaan grafis untuk memudahkan intuisi bisnis Anda.</p>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                    <div class="bg-white p-10 rounded-3xl shadow-soft border border-botanical-border h-[450px]">
                        <canvas id="topRulesChart"></canvas>
                    </div>
                    <div class="bg-white p-10 rounded-3xl shadow-soft border border-botanical-border h-[450px]">
                        <canvas id="scatterChart"></canvas>
                    </div>
                </div>
            </section>

            <!-- Overstock Recommendations -->
            <section class="mb-32">
                <div class="mb-16">
                    <h2 class="font-serif text-4xl md:text-5xl text-botanical-fg mb-4">Kurasi <span class="italic">Overstock</span></h2>
                    <p class="text-xl text-botanical-fg/70">Saran penggabungan untuk menyegarkan produk yang terdiam di gudang.</p>
                </div>

                @if(count($overstockBundles) == 0)
                    <div class="bg-white p-16 text-center rounded-3xl border border-botanical-border shadow-soft">
                        <p class="text-xl text-botanical-fg/60 font-serif italic">Gudang Anda dalam keseimbangan sempurna. Tidak ada overstock mendesak.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                        @foreach($overstockBundles as $index => $bundle)
                            <!-- Staggered grid effect on lg screens -->
                            <div class="bg-white rounded-[40px] p-8 shadow-soft border border-botanical-border hover:-translate-y-2 transition-all duration-700 relative overflow-hidden group {{ $index % 2 != 0 ? 'lg:translate-y-12' : '' }}">
                                <!-- Decorative background element -->
                                <div class="absolute -top-10 -right-10 w-40 h-40 bg-botanical-bg rounded-full opacity-50 pointer-events-none group-hover:scale-110 transition-transform duration-1000"></div>
                                
                                <div class="flex justify-between items-center mb-8 relative z-10">
                                    <span class="px-4 py-1.5 bg-botanical-secondary/30 text-botanical-fg text-xs font-bold uppercase tracking-widest rounded-full">Overstock</span>
                                    <span class="text-sm font-semibold text-botanical-primary">Lift: {{ number_format($bundle['lift'], 2) }}</span>
                                </div>
                                
                                <h3 class="font-serif text-3xl text-botanical-fg mb-3 relative z-10">{{ $bundle['overstock_item'] }}</h3>
                                
                                <div class="flex gap-6 text-sm text-botanical-fg/60 mb-8 relative z-10 border-b border-botanical-border pb-6">
                                    <span>Stok:<br><strong class="text-lg text-botanical-fg font-serif">{{ $bundle['stock'] }}</strong></span>
                                    <span>Menetap:<br><strong class="text-lg text-botanical-interactive font-serif">{{ $bundle['days_left'] }} Hari</strong></span>
                                </div>
                                
                                <div class="text-center text-xs tracking-widest uppercase text-botanical-fg/40 mb-3 relative z-10">Pasangkan dengan</div>
                                
                                <div class="bg-botanical-bg p-6 rounded-[24px] text-center mb-8 border border-botanical-border relative z-10 hover:border-botanical-primary/30 transition-colors duration-500">
                                    <span class="font-medium text-lg text-botanical-fg">{{ $bundle['bundle_with'] }}</span>
                                </div>
                                
                                <div class="flex justify-between text-sm text-botanical-fg/70 relative z-10">
                                    <div><span class="text-xs uppercase tracking-widest text-botanical-fg/40 block mb-1">Support</span><strong class="text-botanical-fg">{{ number_format($bundle['support'] * 100, 2) }}%</strong></div>
                                    <div class="text-right"><span class="text-xs uppercase tracking-widest text-botanical-fg/40 block mb-1">Confidence</span><strong class="text-botanical-fg">{{ number_format($bundle['confidence'] * 100, 2) }}%</strong></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <!-- Table Section -->
            <section class="mt-24 md:mt-48">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end mb-16 gap-10">
                    <div>
                        <h2 class="font-serif text-4xl md:text-5xl text-botanical-fg mb-4">Lanskap <span class="italic">Korelasi</span></h2>
                        <p class="text-xl text-botanical-fg/70">Daftar lengkap relasi produk yang tumbuh secara organik.</p>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-4 w-full lg:w-auto">
                        <button id="btn-export" class="px-6 py-4 bg-white border border-botanical-border text-botanical-fg rounded-full text-sm font-semibold tracking-wider uppercase hover:bg-botanical-bg hover:text-botanical-interactive transition-colors shadow-soft flex justify-center items-center gap-3 w-full sm:w-auto duration-500">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            CSV
                        </button>
                        
                        <select id="rule-size-filter" class="px-6 py-4 bg-white border border-botanical-border text-botanical-fg rounded-full text-sm font-medium outline-none focus:ring-2 focus:ring-botanical-primary shadow-soft cursor-pointer w-full sm:w-auto appearance-none transition-shadow duration-300">
                            <option value="all">Semua Ukuran Bundle</option>
                            <option value="2">Pasangan (2 Produk)</option>
                            <option value="3">Trio (3 Produk)</option>
                            <option value="4">Kuartet (4 Produk)</option>
                        </select>
                        
                        <input type="text" id="rule-search" class="px-6 py-4 bg-white border border-botanical-border text-botanical-fg rounded-full text-sm font-medium outline-none focus:ring-2 focus:ring-botanical-primary shadow-soft w-full sm:w-80 transition-shadow duration-300 placeholder:text-botanical-fg/40" placeholder="Cari nama koleksi...">
                    </div>
                </div>

                <div class="bg-white rounded-[40px] shadow-soft border border-botanical-border overflow-hidden">
                    <div class="overflow-x-auto">
                        <table id="rules-table" class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-botanical-bg/40 border-b border-botanical-border">
                                    <th class="py-6 px-8 font-semibold text-xs tracking-widest uppercase text-botanical-fg/50">#</th>
                                    <th class="py-6 px-8 font-semibold text-xs tracking-widest uppercase text-botanical-fg/50">Akar (Antecedents)</th>
                                    <th class="py-6 px-8 font-semibold text-xs tracking-widest uppercase text-botanical-fg/50">Cabang (Consequents)</th>
                                    <th class="sortable py-6 px-8 font-semibold text-xs tracking-widest uppercase text-botanical-fg/50 cursor-pointer hover:text-botanical-primary transition-colors" data-sort="support">Support ⇅</th>
                                    <th class="sortable py-6 px-8 font-semibold text-xs tracking-widest uppercase text-botanical-fg/50 cursor-pointer hover:text-botanical-primary transition-colors" data-sort="confidence">Confidence ⇅</th>
                                    <th class="sortable py-6 px-8 font-semibold text-xs tracking-widest uppercase text-botanical-fg/50 cursor-pointer hover:text-botanical-primary transition-colors" data-sort="lift">Lift ⇅</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-botanical-border text-base">
                                @foreach($rules as $index => $rule)
                                    @php
                                        $ant_count = substr_count($rule['antecedent'], ',') + 1;
                                        $con_count = substr_count($rule['consequent'], ',') + 1;
                                        $total_items = $ant_count + $con_count;
                                    @endphp
                                    <tr class="rule-row hover:bg-botanical-bg/60 transition-colors duration-500" data-size="{{ $total_items }}" data-support="{{ $rule['support'] }}" data-confidence="{{ $rule['confidence'] }}" data-lift="{{ $rule['lift'] }}">
                                        <td class="col-no py-6 px-8 text-botanical-fg/40 font-serif italic">{{ $index + 1 }}</td>
                                        <td class="col-antecedent py-6 px-8 font-medium text-botanical-fg">{{ $rule['antecedent'] }}</td>
                                        <td class="col-consequent py-6 px-8 font-medium text-botanical-primary">{{ $rule['consequent'] }}</td>
                                        <td class="py-6 px-8 text-botanical-fg/80">{{ number_format($rule['support'] * 100, 2) }}%</td>
                                        <td class="py-6 px-8 text-botanical-fg/80">{{ number_format($rule['confidence'] * 100, 2) }}%</td>
                                        <td class="py-6 px-8">
                                            <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold bg-botanical-secondary/20 text-botanical-fg">
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

        <footer class="mt-40 pt-12 border-t border-botanical-border text-center">
            <p class="font-serif italic text-xl text-botanical-fg/60">&copy; 2026 AuraBeauty Botanical Insights. Crafted with natural intention.</p>
        </footer>
    </div>

    <!-- Scripts -->
    <script>
        // Loading Overlay
        const analysisForm = document.getElementById('analysis-form');
        if (analysisForm) {
            analysisForm.addEventListener('submit', () => {
                const overlay = document.getElementById('loading-overlay');
                overlay.style.display = 'flex';
                setTimeout(() => { overlay.style.opacity = '1'; }, 10);
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
                filterAndSearch(); // re-apply number indices
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
                downloadLink.download = 'AuraBeauty_Botanical_Rules.csv';
                downloadLink.href = window.URL.createObjectURL(csvFile);
                downloadLink.style.display = 'none';
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);
            });
        }

        // Chart.js Configuration
        @if($has_data && count($rules) > 0)
        Chart.defaults.color = '#2D3A31';
        Chart.defaults.font.family = '"Source Sans 3", sans-serif';
        Chart.defaults.scale.grid.color = '#E6E2DA';

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
                    backgroundColor: '#8C9A84',
                    borderRadius: 12,
                    barThickness: 16
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
                        text: 'Top 10 Kurasi (Lift Score)', 
                        font: { family: '"Playfair Display", serif', size: 22, weight: 600 },
                        padding: { bottom: 20 }
                    },
                    tooltip: {
                        backgroundColor: '#2D3A31',
                        titleFont: { family: '"Source Sans 3", sans-serif', size: 14 },
                        bodyFont: { family: '"Source Sans 3", sans-serif', size: 13 },
                        padding: 14,
                        cornerRadius: 16,
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
                    label: 'Relasi Produk',
                    data: scatterData,
                    backgroundColor: '#C27B66',
                    pointRadius: 5,
                    pointHoverRadius: 9,
                    pointBorderColor: '#F9F8F4',
                    pointBorderWidth: 1.5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    title: { 
                        display: true, 
                        text: 'Sebaran Natural (Support vs Confidence)', 
                        font: { family: '"Playfair Display", serif', size: 22, weight: 600 },
                        padding: { bottom: 20 }
                    },
                    tooltip: {
                        backgroundColor: '#2D3A31',
                        padding: 14,
                        cornerRadius: 16,
                        callbacks: {
                            label: function(ctx) {
                                const r = ctx.raw.raw;
                                return r.antecedent + ' → ' + r.consequent + ' (Lift: ' + r.lift.toFixed(2) + ')';
                            }
                        }
                    }
                },
                scales: {
                    x: { title: { display: true, text: 'Support (%)', font: { weight: 600 } }, beginAtZero: true },
                    y: { title: { display: true, text: 'Confidence (%)', font: { weight: 600 } }, beginAtZero: true }
                }
            }
        });
        @endif
    </script>
</body>
</html>
