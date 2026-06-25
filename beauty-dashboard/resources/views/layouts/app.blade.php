<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="AuraBeauty BI — Business Intelligence Dashboard untuk analitik penjualan beauty marketplace.">
    <title>@yield('title', 'Dashboard') — AuraBeauty BI</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --sidebar-width: 260px;
            --topbar-height: 64px;
            --brand-primary: #2563EB; /* Blue */
            --brand-secondary: #3B82F6; /* Lighter Blue */
            --brand-accent: #60A5FA;
            --surface: #F3F4F6; /* Light gray background */
            --surface-2: #FFFFFF; /* White for sidebar/topbar */
            --surface-3: #F9FAFB;
            --surface-card: #FFFFFF; /* White for cards */
            --border: #E5E7EB; /* Light border */
            --text-primary: #111827; /* Dark text */
            --text-secondary: #4B5563; /* Medium dark text */
            --text-muted: #9CA3AF; /* Light gray text */
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
            --info: #3B82F6;
        }

        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--surface);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ─── Sidebar ─── */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--surface-2);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            padding: 24px 20px 20px;
            border-bottom: 1px solid var(--border);
        }
        .sidebar-brand h2 {
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin: 0;
            background: linear-gradient(135deg, var(--brand-primary), var(--brand-secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .sidebar-brand small {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--text-muted);
            font-weight: 600;
        }

        .sidebar-nav { flex: 1; padding: 16px 12px; overflow-y: auto; }
        .nav-section-label {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--text-muted);
            padding: 12px 8px 6px;
            margin-top: 8px;
        }

        .nav-item-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 10px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.18s ease;
            margin-bottom: 2px;
        }
        .nav-item-link i { font-size: 1.05rem; width: 20px; text-align: center; }
        .nav-item-link:hover {
            background: rgba(37,99,235,0.15);
            color: var(--text-primary);
        }
        .nav-item-link.active {
            background: linear-gradient(135deg, rgba(37,99,235,0.3), rgba(96,165,250,0.15));
            color: #ffffff;
            font-weight: 600;
            border: 1px solid rgba(37,99,235,0.3);
        }
        .nav-item-link.active i { color: var(--brand-primary); }

        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid var(--border);
        }
        .sidebar-footer small {
            font-size: 0.7rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* ─── Main layout ─── */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            height: var(--topbar-height);
            background: var(--surface-2);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .topbar-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
        }
        .topbar-subtitle {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin: 0;
        }
        .topbar-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--success);
            background: rgba(16,185,129,0.12);
            border: 1px solid rgba(16,185,129,0.25);
            border-radius: 20px;
            padding: 4px 10px;
        }
        .topbar-badge::before {
            content: '';
            width: 6px; height: 6px;
            background: var(--success);
            border-radius: 50%;
            animation: pulse-dot 1.5s ease infinite;
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: 0.5; transform: scale(0.8); }
        }

        .page-content { padding: 28px; flex: 1; }

        /* ─── KPI Cards ─── */
        .kpi-card {
            background: var(--surface-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 22px 24px;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            border-radius: 16px 16px 0 0;
        }
        .kpi-card.purple::before { background: linear-gradient(90deg, var(--brand-primary), var(--brand-secondary)); }
        .kpi-card.green::before  { background: linear-gradient(90deg, var(--success), #34D399); }
        .kpi-card.amber::before  { background: linear-gradient(90deg, var(--warning), #FCD34D); }
        .kpi-card.red::before    { background: linear-gradient(90deg, var(--danger), #F87171); }
        .kpi-card.blue::before   { background: linear-gradient(90deg, var(--info), #60A5FA); }
        .kpi-card.pink::before   { background: linear-gradient(90deg, #60A5FA, #F472B6); }

        .kpi-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }
        .kpi-label {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 6px;
        }
        .kpi-value {
            font-size: 1.85rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1.1;
            letter-spacing: -1px;
        }
        .kpi-sub {
            font-size: 0.75rem;
            color: var(--text-secondary);
            margin-top: 4px;
            font-weight: 500;
        }
        .kpi-icon {
            position: absolute;
            right: 20px;
            top: 20px;
            width: 40px; height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
        }
        .kpi-icon.purple { background: rgba(37,99,235,0.2); color: #A78BFA; }
        .kpi-icon.green  { background: rgba(16,185,129,0.2);  color: #34D399; }
        .kpi-icon.amber  { background: rgba(245,158,11,0.2);  color: #FCD34D; }
        .kpi-icon.red    { background: rgba(239,68,68,0.2);   color: #F87171; }
        .kpi-icon.blue   { background: rgba(59,130,246,0.2);  color: #60A5FA; }
        .kpi-icon.pink   { background: rgba(96,165,250,0.2);  color: #F472B6; }

        /* ─── Section cards ─── */
        .section-card {
            background: var(--surface-card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
        }
        .section-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 4px;
        }
        .section-subtitle {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-bottom: 20px;
        }

        /* ─── Chart containers ─── */
        .chart-box {
            position: relative;
            height: 280px;
            width: 100%;
        }
        .chart-box-lg { height: 340px; }
        .chart-box-sm { height: 220px; }

        /* ─── Tables ─── */
        .bi-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.82rem;
        }
        .bi-table th {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--text-muted);
            padding: 10px 14px;
            border-bottom: 1px solid var(--border);
            text-align: left;
        }
        .bi-table td {
            padding: 11px 14px;
            border-bottom: 1px solid rgba(0,0,0,0.04);
            color: var(--text-secondary);
        }
        .bi-table tbody tr:hover td {
            background: rgba(0,0,0,0.03);
            color: var(--text-primary);
        }
        .bi-table tbody tr:last-child td { border-bottom: none; }

        /* ─── Badges ─── */
        .bi-badge {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.3px;
        }
        .bi-badge-purple { background: rgba(37,99,235,0.2); color: #A78BFA; border: 1px solid rgba(37,99,235,0.3); }
        .bi-badge-green  { background: rgba(16,185,129,0.2);  color: #34D399; border: 1px solid rgba(16,185,129,0.3); }
        .bi-badge-amber  { background: rgba(245,158,11,0.2);  color: #FCD34D; border: 1px solid rgba(245,158,11,0.3); }
        .bi-badge-red    { background: rgba(239,68,68,0.2);   color: #F87171; border: 1px solid rgba(239,68,68,0.3); }
        .bi-badge-blue   { background: rgba(59,130,246,0.2);  color: #60A5FA; border: 1px solid rgba(59,130,246,0.3); }

        /* ─── Progress bar ─── */
        .bi-progress {
            height: 6px;
            background: rgba(0,0,0,0.07);
            border-radius: 99px;
            overflow: hidden;
        }
        .bi-progress-bar {
            height: 100%;
            border-radius: 99px;
            background: linear-gradient(90deg, var(--brand-primary), var(--brand-secondary));
            transition: width 0.6s ease;
        }

        /* ─── Rank number ─── */
        .rank-no {
            width: 26px; height: 26px;
            border-radius: 8px;
            background: rgba(0,0,0,0.06);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--text-muted);
            flex-shrink: 0;
        }
        .rank-no.gold   { background: rgba(245,158,11,0.2); color: #FCD34D; }
        .rank-no.silver { background: rgba(156,163,175,0.2); color: #D1D5DB; }
        .rank-no.bronze { background: rgba(180,83,9,0.2); color: #FCA572; }

        /* ─── Scrollbar ─── */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.2); }

        /* ─── Mobile sidebar toggle ─── */
        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--text-primary);
            font-size: 1.3rem;
            cursor: pointer;
            padding: 4px;
        }

        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-wrapper { margin-left: 0; }
            .sidebar-toggle { display: block; }
            .page-content { padding: 16px; }
            .kpi-value { font-size: 1.5rem; }
        }
    </style>
    @stack('head')
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <h2>AuraBeauty BI</h2>
        <small>Business Intelligence</small>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section-label">Analytics</div>

        <a href="{{ route('analytics.sales') }}"
           class="nav-item-link {{ request()->routeIs('analytics.sales') ? 'active' : '' }}">
            <i class="bi bi-graph-up-arrow"></i>
            <span>Sales Performance</span>
        </a>

        <a href="{{ route('analytics.marketplace') }}"
           class="nav-item-link {{ request()->routeIs('analytics.marketplace') ? 'active' : '' }}">
            <i class="bi bi-shop"></i>
            <span>Marketplace</span>
        </a>

        <a href="{{ route('analytics.products') }}"
           class="nav-item-link {{ request()->routeIs('analytics.products') ? 'active' : '' }}">
            <i class="bi bi-box-seam"></i>
            <span>Product Performance</span>
        </a>

        <a href="{{ route('analytics.customers') }}"
           class="nav-item-link {{ request()->routeIs('analytics.customers') ? 'active' : '' }}">
            <i class="bi bi-people"></i>
            <span>Customer Insight</span>
        </a>

        <a href="{{ route('analytics.operational') }}"
           class="nav-item-link {{ request()->routeIs('analytics.operational') ? 'active' : '' }}">
            <i class="bi bi-activity"></i>
            <span>Operational</span>
        </a>

        <div class="nav-section-label">Intelligence</div>

        <a href="{{ route('analytics.apriori') }}"
           class="nav-item-link {{ request()->routeIs('analytics.apriori') ? 'active' : '' }}">
            <i class="bi bi-diagram-3"></i>
            <span>Apriori Rules</span>
        </a>
    </nav>
    <div class="sidebar-footer">
        <small>© 2026 AuraBeauty BI · Data Driven</small>
    </div>
</aside>

<!-- Main Wrapper -->
<div class="main-wrapper">
    <!-- Topbar -->
    <header class="topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <div>
                <p class="topbar-title">@yield('page-title', 'Dashboard')</p>
                <p class="topbar-subtitle">@yield('page-subtitle', 'AuraBeauty Business Intelligence')</p>
            </div>
        </div>
        <div class="topbar-badge">Live Data</div>
    </header>

    <!-- Alerts -->
    <div class="px-4 pt-3">
        @if (session('success'))
            <div class="alert d-flex align-items-center gap-2 py-2 px-3 mb-0"
                 style="background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.3);border-radius:10px;color:#34D399;font-size:.83rem"
                 role="alert">
                <i class="bi bi-check-circle-fill"></i>
                <span class="fw-semibold">{{ session('success') }}</span>
            </div>
        @endif
        @if (session('error'))
            <div class="alert d-flex align-items-center gap-2 py-2 px-3 mb-0"
                 style="background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.3);border-radius:10px;color:#F87171;font-size:.83rem"
                 role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span class="fw-semibold">{{ session('error') }}</span>
            </div>
        @endif
    </div>

    <!-- Page Content -->
    <main class="page-content">
        @yield('content')
    </main>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Sidebar toggle for mobile
    const toggle  = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    if (toggle && sidebar) {
        toggle.addEventListener('click', () => sidebar.classList.toggle('open'));
        document.addEventListener('click', (e) => {
            if (!sidebar.contains(e.target) && !toggle.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });
    }

    // Default Chart.js theme
    Chart.defaults.color = '#4B5563';
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.font.size   = 12;
    Chart.defaults.plugins.legend.labels.boxRadius = 4;
    Chart.defaults.plugins.tooltip.backgroundColor = '#FFFFFF';
    Chart.defaults.plugins.tooltip.borderColor     = '#E5E7EB';
    Chart.defaults.plugins.tooltip.titleColor      = '#111827';
    Chart.defaults.plugins.tooltip.bodyColor       = '#4B5563';
    Chart.defaults.plugins.tooltip.borderWidth     = 1;
    Chart.defaults.plugins.tooltip.padding         = 10;
    Chart.defaults.plugins.tooltip.cornerRadius    = 8;
    Chart.defaults.plugins.tooltip.titleFont       = { weight: '700', size: 12 };
    Chart.defaults.scale.grid.color = '#F3F4F6';
    Chart.defaults.scale.ticks.color = '#6B7280';
</script>
@stack('scripts')
</body>
</html>
