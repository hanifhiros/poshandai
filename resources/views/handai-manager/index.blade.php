@extends('layouts.master')

@section('title', 'Dashboard â€” Handai Manager')

@section('vendor-style')
@endsection

@section('page-style')
<style>
    /* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
       ENTERPRISE DASHBOARD v2 â€” MODERN ERP STYLE
       â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */

    :root {
        --dash-bg: #f1f5f9;
        --card-bg: #ffffff;
        --card-border: #e2e8f0;
        --card-shadow: 0 1px 3px 0 rgba(0,0,0,.04), 0 1px 2px -1px rgba(0,0,0,.04);
        --card-shadow-hover: 0 10px 25px -5px rgba(0,0,0,.06), 0 4px 10px -6px rgba(0,0,0,.04);
        --card-radius: 16px;
        --text-primary: #0f172a;
        --text-secondary: #475569;
        --text-muted: #94a3b8;
        --brand: #0C9044;
        --brand-light: #ecfdf5;
        --brand-soft: #d1fae5;
        --danger: #ef4444;
        --danger-bg: #fef2f2;
        --warning: #f59e0b;
        --warning-bg: #fffbeb;
        --info: #3b82f6;
        --info-bg: #eff6ff;
        --success: #10b981;
        --success-bg: #ecfdf5;
    }

    [data-theme="dark"] {
        --dash-bg: #0f172a;
        --card-bg: #1e293b;
        --card-border: #334155;
        --card-shadow: 0 1px 3px 0 rgba(0,0,0,.3);
        --card-shadow-hover: 0 10px 25px -5px rgba(0,0,0,.4);
        --text-primary: #f1f5f9;
        --text-secondary: #94a3b8;
        --text-muted: #64748b;
        --brand-light: #064e3b;
        --brand-soft: #065f46;
        --danger-bg: #450a0a;
        --warning-bg: #451a03;
        --info-bg: #172554;
        --success-bg: #064e3b;
    }

    .erp-dashboard {
        background: var(--dash-bg);
        min-height: 100vh;
        font-family: 'Poppins', 'Public Sans', sans-serif;
    }

    .erp-card {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: var(--card-radius);
        box-shadow: var(--card-shadow);
        transition: all .2s ease;
    }

    .erp-card:hover {
        box-shadow: var(--card-shadow-hover);
        transform: translateY(-1px);
    }

    .erp-card-flat {
        background: var(--card-bg);
        border: 1px solid var(--card-border);
        border-radius: var(--card-radius);
    }

    .kpi-value {
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1.2;
        color: var(--text-primary);
        letter-spacing: -0.02em;
    }

    .kpi-label {
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
    }

    .growth-badge {
        display: inline-flex;
        align-items: center;
        gap: 2px;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .growth-up { background: var(--success-bg); color: var(--success); }
    .growth-down { background: var(--danger-bg); color: var(--danger); }
    .growth-neutral { background: #f1f5f9; color: var(--text-muted); }

    .section-title {
        font-size: 1.05rem;
        font-weight: 600;
        color: var(--text-primary);
        letter-spacing: -0.01em;
    }

    .section-subtitle {
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }

    .alert-card {
        border-radius: 12px;
        padding: 14px 18px;
        display: flex;
        align-items: flex-start;
        gap: 14px;
        font-size: 0.85rem;
        transition: all .15s ease;
    }

    .alert-card:hover { filter: brightness(0.97); }
    .alert-danger { background: var(--danger-bg); border-left: 4px solid var(--danger); }
    .alert-warning { background: var(--warning-bg); border-left: 4px solid var(--warning); }
    .alert-info { background: var(--info-bg); border-left: 4px solid var(--info); }
    .alert-success { background: var(--success-bg); border-left: 4px solid var(--success); }

    .sparkline-container { width: 80px; height: 32px; }

    .product-rank-row {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 0;
        border-bottom: 1px solid var(--card-border);
        transition: background .15s;
    }

    .product-rank-row:last-child { border-bottom: none; }
    .product-rank-row:hover {
        background: var(--brand-light);
        border-radius: 8px;
        margin: 0 -8px;
        padding: 10px 8px;
    }

    .rank-number {
        width: 28px; height: 28px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.75rem; font-weight: 700;
    }

    .rank-1 { background: #fef3c7; color: #d97706; }
    .rank-2 { background: #e2e8f0; color: #475569; }
    .rank-3 { background: #fed7aa; color: #ea580c; }
    .rank-default { background: #f1f5f9; color: #94a3b8; }

    .progress-bar-bg { height: 6px; background: #f1f5f9; border-radius: 999px; overflow: hidden; }
    .progress-bar-fill { height: 100%; border-radius: 999px; transition: width .6s ease; }

    .chart-wrapper { position: relative; width: 100%; }
    .chart-wrapper canvas { width: 100% !important; }

    .custom-scroll::-webkit-scrollbar { width: 4px; }
    .custom-scroll::-webkit-scrollbar-track { background: transparent; }
    .custom-scroll::-webkit-scrollbar-thumb { background: var(--card-border); border-radius: 10px; }

    .dash-grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
    .dash-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }

    @media (max-width: 1280px) { .dash-grid-3 { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 768px) {
        .dash-grid-2 { grid-template-columns: 1fr; }
        .dash-grid-3 { grid-template-columns: 1fr; }
        .kpi-value { font-size: 1.35rem; }
    }

    .donut-center {
        position: absolute; top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        text-align: center; pointer-events: none;
    }

    .btn-erp {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 18px; border-radius: 10px;
        font-size: 0.8rem; font-weight: 600;
        transition: all .15s ease; cursor: pointer; border: none;
    }

    .btn-erp-primary { background: var(--brand); color: white; }
    .btn-erp-primary:hover { background: #0a7a3a; transform: translateY(-1px); }
    .btn-erp-outline { background: transparent; color: var(--brand); border: 1.5px solid var(--brand); }
    .btn-erp-outline:hover { background: var(--brand-light); }

    /* Target Progress Bars */
    .target-progress-bg {
        height: 10px; background: #f1f5f9;
        border-radius: 999px; overflow: hidden;
    }

    .target-progress-fill {
        height: 100%; border-radius: 999px;
        transition: width 1.5s cubic-bezier(0.22, 1, 0.36, 1);
        background: linear-gradient(90deg, var(--brand), #10b981);
    }

    .target-progress-fill.over-target {
        background: linear-gradient(90deg, #10b981, #059669);
    }

    /* Skeleton Loading */
    .skeleton {
        position: relative; overflow: hidden;
        background: var(--card-border); border-radius: 8px;
    }

    .skeleton::after {
        content: ''; position: absolute; inset: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,.4), transparent);
        animation: skeleton-shimmer 1.5s infinite;
    }

    @keyframes skeleton-shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
    }

    /* Quick Actions */
    .quick-action-btn {
        display: flex; flex-direction: column; align-items: center; gap: 8px;
        padding: 16px 12px; border-radius: 14px;
        border: 1px solid var(--card-border); background: var(--card-bg);
        cursor: pointer; transition: all .2s ease;
        text-decoration: none; min-width: 0;
    }

    .quick-action-btn:hover {
        border-color: var(--brand); background: var(--brand-light);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(12, 144, 68, 0.1);
    }

    .quick-action-btn .qa-icon {
        width: 44px; height: 44px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.25rem;
    }

    .quick-action-btn .qa-label {
        font-size: 0.72rem; font-weight: 600;
        color: var(--text-secondary); text-align: center; line-height: 1.3;
    }

    /* Auto-refresh indicator */
    .refresh-indicator {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 0.72rem; color: var(--text-muted);
    }

    .refresh-dot {
        width: 6px; height: 6px; border-radius: 50%;
        background: var(--success);
        animation: pulse-dot 2s ease infinite;
    }

    @keyframes pulse-dot {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(0.8); }
    }

    /* Fade-in animation */
    .fade-in-up {
        opacity: 0; transform: translateY(16px);
        animation: fadeInUp .5s ease forwards;
    }

    @keyframes fadeInUp {
        to { opacity: 1; transform: translateY(0); }
    }

    .fade-delay-1 { animation-delay: .05s; }
    .fade-delay-2 { animation-delay: .1s; }
    .fade-delay-3 { animation-delay: .15s; }
    .fade-delay-4 { animation-delay: .2s; }
    .fade-delay-5 { animation-delay: .25s; }
    .fade-delay-6 { animation-delay: .3s; }

    /* Tab Switcher */
    .tab-btn {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 10px 20px; border-radius: 11px;
        font-size: 0.82rem; font-weight: 600;
        cursor: pointer; border: none;
        transition: all .2s ease;
        white-space: nowrap;
    }

    .tab-active {
        background: var(--brand); color: white;
        box-shadow: 0 2px 8px rgba(12, 144, 68, 0.25);
    }

    .tab-inactive {
        background: transparent; color: var(--text-secondary);
    }

    .tab-inactive:hover {
        background: var(--brand-light); color: var(--brand);
    }

    [data-theme="dark"] .tab-active {
        box-shadow: 0 2px 8px rgba(12, 144, 68, 0.4);
    }
</style>
@endsection

@section('content')

<div class="erp-dashboard px-4 md:px-6 lg:px-8 py-6 space-y-6" id="dashboardContainer">

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
         HEADER + GREETING
         â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 fade-in-up">
        <div>
            <h1 class="text-2xl font-bold" style="color: var(--text-primary); letter-spacing: -0.02em;">
                {{ $greeting }}, {{ $userName }}! ðŸ‘‹
            </h1>
            <p class="text-sm mt-1" style="color: var(--text-muted);">
                {{ now()->translatedFormat('l, d F Y') }} &middot; {{ $selected_store->store_name ?? 'Semua Outlet' }}
            </p>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            <div class="refresh-indicator">
                <span class="refresh-dot"></span>
                <span>Auto-refresh</span>
                <span id="refresh-countdown" class="font-semibold tabular-nums">60s</span>
            </div>
            <div class="flex items-center gap-2 px-4 py-2 erp-card-flat text-sm" style="color: var(--text-secondary);">
                <i class="ti ti-clock text-base" style="color: var(--brand);"></i>
                <span id="live-clock" class="font-medium tabular-nums">--:--:--</span>
            </div>
            <div class="flex items-center gap-2 px-3 py-2 erp-card-flat text-xs" style="color: var(--text-muted);">
                <i class="ti ti-refresh text-sm"></i>
                Updated {{ $lastUpdated }}
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-3 md:grid-cols-6 gap-3 fade-in-up fade-delay-1">
        <a href="{{ route('manager.operational.orders.index') }}" class="quick-action-btn">
            <div class="qa-icon" style="background: var(--info-bg); color: var(--info);">
                <i class="ti ti-receipt"></i>
            </div>
            <span class="qa-label">Pesanan</span>
        </a>
        <a href="{{ route('manager.inventory.products') }}" class="quick-action-btn">
            <div class="qa-icon" style="background: #fef3c7; color: #d97706;">
                <i class="ti ti-coffee"></i>
            </div>
            <span class="qa-label">Produk</span>
        </a>
        <a href="{{ route('manager.inventory.stock') }}" class="quick-action-btn">
            <div class="qa-icon" style="background: var(--danger-bg); color: var(--danger);">
                <i class="ti ti-package"></i>
            </div>
            <span class="qa-label">Stok</span>
        </a>
        <a href="{{ route('manager.operational.produksi') }}" class="quick-action-btn">
            <div class="qa-icon" style="background: #f5f3ff; color: #7c3aed;">
                <i class="ti ti-tools-kitchen-2"></i>
            </div>
            <span class="qa-label">Produksi</span>
        </a>
        <a href="{{ route('manager.inventory.recipes.index') }}" class="quick-action-btn">
            <div class="qa-icon" style="background: #fce7f3; color: #db2777;">
                <i class="ti ti-book"></i>
            </div>
            <span class="qa-label">Resep</span>
        </a>
        <a href="{{ route('manager.inventory.stock-batches.index') }}" class="quick-action-btn">
            <div class="qa-icon" style="background: var(--success-bg); color: var(--brand);">
                <i class="ti ti-stack-2"></i>
            </div>
            <span class="qa-label">Batch Stok</span>
        </a>
    </div>

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
         1ï¸âƒ£ EXECUTIVE SNAPSHOT â€” KPI CARDS
         â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    @if (App\Helpers\RoleHelper::hasAnyRoleIncludingDescendants(['Manager-Finance']))
    <section>
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
            {{-- Revenue Today --}}
            <div class="erp-card p-5 flex flex-col justify-between gap-3 cursor-pointer fade-in-up fade-delay-1" onclick="scrollToSection('sales-section')">
                <div class="flex items-center justify-between">
                    <span class="kpi-label">Revenue Hari Ini</span>
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: var(--brand-light);">
                        <i class="ti ti-currency-dollar text-lg" style="color: var(--brand);"></i>
                    </div>
                </div>
                <div>
                    <p class="kpi-value">Rp {{ \App\Helpers\NumberFormatter::short($revenueToday) }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="growth-badge {{ $revenueGrowth >= 0 ? 'growth-up' : 'growth-down' }}">
                            <i class="ti {{ $revenueGrowth >= 0 ? 'ti-arrow-up-right' : 'ti-arrow-down-right' }} text-xs"></i>
                            {{ abs($revenueGrowth) }}%
                        </span>
                        <span style="font-size:.65rem; color: var(--text-muted);">vs kemarin</span>
                    </div>
                </div>
                <div class="sparkline-container"><canvas id="spark-revenue"></canvas></div>
            </div>

            {{-- Revenue MTD --}}
            <div class="erp-card p-5 flex flex-col justify-between gap-3 cursor-pointer fade-in-up fade-delay-2" onclick="scrollToSection('sales-section')">
                <div class="flex items-center justify-between">
                    <span class="kpi-label">Revenue MTD</span>
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: var(--info-bg);">
                        <i class="ti ti-calendar-stats text-lg" style="color: var(--info);"></i>
                    </div>
                </div>
                <div>
                    <p class="kpi-value">Rp {{ \App\Helpers\NumberFormatter::short($revenueMTD) }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="growth-badge {{ $revenueMTDGrowth >= 0 ? 'growth-up' : 'growth-down' }}">
                            <i class="ti {{ $revenueMTDGrowth >= 0 ? 'ti-arrow-up-right' : 'ti-arrow-down-right' }} text-xs"></i>
                            {{ abs($revenueMTDGrowth) }}%
                        </span>
                        <span style="font-size:.65rem; color: var(--text-muted);">vs bulan lalu</span>
                    </div>
                </div>
                <div class="sparkline-container"><canvas id="spark-mtd"></canvas></div>
            </div>

            {{-- Total Transactions --}}
            <div class="erp-card p-5 flex flex-col justify-between gap-3 cursor-pointer fade-in-up fade-delay-3" onclick="scrollToSection('operational-section')">
                <div class="flex items-center justify-between">
                    <span class="kpi-label">Transaksi Hari Ini</span>
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: #f5f3ff;">
                        <i class="ti ti-receipt text-lg" style="color: #7c3aed;"></i>
                    </div>
                </div>
                <div>
                    <p class="kpi-value">{{ number_format($transactionsToday) }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="growth-badge {{ $transactionsGrowth >= 0 ? 'growth-up' : 'growth-down' }}">
                            <i class="ti {{ $transactionsGrowth >= 0 ? 'ti-arrow-up-right' : 'ti-arrow-down-right' }} text-xs"></i>
                            {{ abs($transactionsGrowth) }}%
                        </span>
                        <span style="font-size:.65rem; color: var(--text-muted);">vs kemarin</span>
                    </div>
                </div>
                <div class="sparkline-container"><canvas id="spark-trx"></canvas></div>
            </div>

            {{-- Items Sold --}}
            <div class="erp-card p-5 flex flex-col justify-between gap-3 cursor-pointer fade-in-up fade-delay-4" onclick="scrollToSection('sales-section')">
                <div class="flex items-center justify-between">
                    <span class="kpi-label">Item Terjual</span>
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: #fef3c7;">
                        <i class="ti ti-coffee text-lg" style="color: #d97706;"></i>
                    </div>
                </div>
                <div>
                    <p class="kpi-value">{{ number_format($itemsSoldToday) }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="growth-badge {{ $itemsSoldGrowth >= 0 ? 'growth-up' : 'growth-down' }}">
                            <i class="ti {{ $itemsSoldGrowth >= 0 ? 'ti-arrow-up-right' : 'ti-arrow-down-right' }} text-xs"></i>
                            {{ abs($itemsSoldGrowth) }}%
                        </span>
                        <span style="font-size:.65rem; color: var(--text-muted);">vs kemarin</span>
                    </div>
                </div>
                <div class="sparkline-container"><canvas id="spark-items"></canvas></div>
            </div>

            {{-- AOV --}}
            <div class="erp-card p-5 flex flex-col justify-between gap-3 cursor-pointer fade-in-up fade-delay-5" onclick="scrollToSection('financial-section')">
                <div class="flex items-center justify-between">
                    <span class="kpi-label">Avg. Order Value</span>
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: #fce7f3;">
                        <i class="ti ti-chart-bar text-lg" style="color: #db2777;"></i>
                    </div>
                </div>
                <div>
                    <p class="kpi-value">Rp {{ \App\Helpers\NumberFormatter::short($aovToday) }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="growth-badge {{ $aovGrowth >= 0 ? 'growth-up' : 'growth-down' }}">
                            <i class="ti {{ $aovGrowth >= 0 ? 'ti-arrow-up-right' : 'ti-arrow-down-right' }} text-xs"></i>
                            {{ abs($aovGrowth) }}%
                        </span>
                        <span style="font-size:.65rem; color: var(--text-muted);">vs kemarin</span>
                    </div>
                </div>
                <div class="sparkline-container"><canvas id="spark-aov"></canvas></div>
            </div>

            {{-- Gross Profit --}}
            <div class="erp-card p-5 flex flex-col justify-between gap-3 cursor-pointer fade-in-up fade-delay-6" onclick="scrollToSection('financial-section')">
                <div class="flex items-center justify-between">
                    <span class="kpi-label">Laba Kotor Hari Ini</span>
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: var(--success-bg);">
                        <i class="ti ti-trending-up text-lg" style="color: var(--success);"></i>
                    </div>
                </div>
                <div>
                    <p class="kpi-value">Rp {{ \App\Helpers\NumberFormatter::short($grossProfitToday) }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="growth-badge {{ $grossProfitGrowth >= 0 ? 'growth-up' : 'growth-down' }}">
                            <i class="ti {{ $grossProfitGrowth >= 0 ? 'ti-arrow-up-right' : 'ti-arrow-down-right' }} text-xs"></i>
                            {{ abs($grossProfitGrowth) }}%
                        </span>
                        <span style="font-size:.65rem; color: var(--text-muted);">vs kemarin</span>
                    </div>
                </div>
                <div class="sparkline-container"><canvas id="spark-profit"></canvas></div>
            </div>
        </div>
    </section>

    @endif

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
         TAB SWITCHER â€” Operasional / Marketing / Keuangan
         â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    <div x-data="{ tab: 'operasional' }" class="space-y-6">
        {{-- Tab Navigation --}}
        <div class="erp-card-flat p-1.5 flex gap-1 fade-in-up fade-delay-2" style="display: inline-flex; border-radius: 14px;">
            <button @click="tab = 'operasional'"
                :class="tab === 'operasional' ? 'tab-active' : 'tab-inactive'"
                class="tab-btn">
                <i class="ti ti-settings-2 text-base"></i>
                <span>Operasional</span>
            </button>
            <button @click="tab = 'marketing'"
                :class="tab === 'marketing' ? 'tab-active' : 'tab-inactive'"
                class="tab-btn">
                <i class="ti ti-chart-pie text-base"></i>
                <span>Marketing</span>
            </button>
            @if (App\Helpers\RoleHelper::hasAnyRoleIncludingDescendants(['Manager-Finance']))
            <button @click="tab = 'keuangan'"
                :class="tab === 'keuangan' ? 'tab-active' : 'tab-inactive'"
                class="tab-btn">
                <i class="ti ti-wallet text-base"></i>
                <span>Keuangan</span>
            </button>
            @endif
        </div>

        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
             TAB: OPERASIONAL
             â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
        <div x-show="tab === 'operasional'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
         2ï¸âƒ£ SALES PERFORMANCE
         â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    <section id="sales-section" class="dash-grid-3 fade-in-up fade-delay-3">
        {{-- Hourly Sales Chart --}}
        <div class="erp-card p-6 col-span-2 md:col-span-1 xl:col-span-1">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="section-title">Penjualan Per Jam</h3>
                    <p class="section-subtitle">Hari ini</p>
                </div>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: var(--brand-light);">
                    <i class="ti ti-chart-line text-base" style="color: var(--brand);"></i>
                </div>
            </div>
            <div class="chart-wrapper" style="height: 220px;">
                <canvas id="chartHourly"></canvas>
            </div>
        </div>

        {{-- 7-Day Sales Chart --}}
        <div class="erp-card p-6 col-span-2 md:col-span-1 xl:col-span-1">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="section-title">Penjualan 7 Hari</h3>
                    <p class="section-subtitle">Terakhir</p>
                </div>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: var(--info-bg);">
                    <i class="ti ti-chart-bar text-base" style="color: var(--info);"></i>
                </div>
            </div>
            <div class="chart-wrapper" style="height: 220px;">
                <canvas id="chart7Days"></canvas>
            </div>
        </div>

        {{-- Top Products --}}
        <div class="erp-card p-6 col-span-2 md:col-span-2 xl:col-span-1">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="section-title">Produk Terlaris</h3>
                    <p class="section-subtitle">Bulan ini</p>
                </div>
                <div class="flex gap-1">
                    <button onclick="switchTopProducts('qty')" id="btn-top-qty"
                        class="px-3 py-1 rounded-lg text-xs font-semibold transition-all"
                        style="background: var(--brand); color: white;">Qty</button>
                    <button onclick="switchTopProducts('revenue')" id="btn-top-revenue"
                        class="px-3 py-1 rounded-lg text-xs font-semibold transition-all"
                        style="background: var(--card-border); color: var(--text-secondary);">Revenue</button>
                </div>
            </div>
            <div class="space-y-0 custom-scroll overflow-y-auto" style="max-height: 240px;" id="top-products-list">
                @php $maxQty = $topProductsQty->max('qty') ?: 1; $maxRev = $topProductsQty->max('revenue') ?: 1; @endphp
                @forelse($topProductsQty as $i => $product)
                <div class="product-rank-row" data-qty="{{ $product->qty }}" data-revenue="{{ $product->revenue }}">
                    <span class="rank-number {{ $i === 0 ? 'rank-1' : ($i === 1 ? 'rank-2' : ($i === 2 ? 'rank-3' : 'rank-default')) }}">
                        {{ $i + 1 }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate" style="color: var(--text-primary);">{{ $product->name }}</p>
                        <div class="progress-bar-bg mt-1">
                            <div class="progress-bar-fill product-bar" style="width: {{ ($product->qty / $maxQty) * 100 }}%; background: var(--brand);"></div>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-bold product-value" style="color: var(--text-primary);">{{ number_format($product->qty) }}</span>
                        <span class="text-sm font-bold product-value-alt hidden" style="color: var(--text-primary);">Rp {{ \App\Helpers\NumberFormatter::short($product->revenue) }}</span>
                        <p class="text-xs product-sub" style="color: var(--text-muted);">unit</p>
                        <p class="text-xs product-sub-alt hidden" style="color: var(--text-muted);">revenue</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-8" style="color: var(--text-muted);">
                    <i class="ti ti-package-off text-3xl"></i>
                    <p class="text-sm mt-2">Belum ada data penjualan</p>
                </div>
                @endforelse
            </div>
        </div>
    </section>

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
         3ï¸âƒ£ OPERATIONAL STATUS + 4ï¸âƒ£ INVENTORY
         â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    <section id="operational-section" class="dash-grid-2 fade-in-up fade-delay-4">
        {{-- Operational Status --}}
        <div class="erp-card p-6">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="section-title">Status Operasional</h3>
                    <p class="section-subtitle">Real-time hari ini</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1 px-3 py-1.5 rounded-lg" style="background: var(--brand-light);">
                        <i class="ti ti-clock-hour-4 text-sm" style="color: var(--brand);"></i>
                        <span class="text-xs font-semibold" style="color: var(--brand);">{{ $ordersPerHour }}/jam</span>
                    </div>
                    <div class="flex items-center gap-1 px-3 py-1.5 rounded-lg" style="background: #fef3c7;">
                        <i class="ti ti-flame text-sm" style="color: #d97706;"></i>
                        <span class="text-xs font-semibold" style="color: #d97706;">Peak: {{ $peakHour }}</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
                <div class="rounded-xl p-4 text-center" style="background: #fffbeb; border: 1px solid #fef3c7;">
                    <div class="w-10 h-10 mx-auto rounded-full flex items-center justify-center mb-2" style="background: #fef3c7;">
                        <i class="ti ti-clock-pause text-lg" style="color: #d97706;"></i>
                    </div>
                    <p class="text-2xl font-bold" style="color: #d97706;">{{ $orderStatusCounts['waiting'] }}</p>
                    <p class="text-xs font-medium" style="color: #92400e;">Menunggu</p>
                </div>
                <div class="rounded-xl p-4 text-center" style="background: var(--info-bg); border: 1px solid #dbeafe;">
                    <div class="w-10 h-10 mx-auto rounded-full flex items-center justify-center mb-2" style="background: #dbeafe;">
                        <i class="ti ti-loader text-lg" style="color: var(--info);"></i>
                    </div>
                    <p class="text-2xl font-bold" style="color: var(--info);">{{ $orderStatusCounts['in_progress'] }}</p>
                    <p class="text-xs font-medium" style="color: #1e40af;">Diproses</p>
                </div>
                <div class="rounded-xl p-4 text-center" style="background: var(--success-bg); border: 1px solid var(--brand-soft);">
                    <div class="w-10 h-10 mx-auto rounded-full flex items-center justify-center mb-2" style="background: var(--brand-soft);">
                        <i class="ti ti-circle-check text-lg" style="color: var(--brand);"></i>
                    </div>
                    <p class="text-2xl font-bold" style="color: var(--brand);">{{ $orderStatusCounts['completed'] }}</p>
                    <p class="text-xs font-medium" style="color: #065f46;">Selesai</p>
                </div>
                <div class="rounded-xl p-4 text-center" style="background: var(--danger-bg); border: 1px solid #fecaca;">
                    <div class="w-10 h-10 mx-auto rounded-full flex items-center justify-center mb-2" style="background: #fecaca;">
                        <i class="ti ti-circle-x text-lg" style="color: var(--danger);"></i>
                    </div>
                    <p class="text-2xl font-bold" style="color: var(--danger);">{{ $orderStatusCounts['cancelled'] }}</p>
                    <p class="text-xs font-medium" style="color: #991b1b;">Batal</p>
                </div>
            </div>

            {{-- Orders timeline â€” REAL order count data --}}
            <div class="chart-wrapper" style="height: 120px;">
                <canvas id="chartOrdersTimeline"></canvas>
            </div>
        </div>

        {{-- Inventory Overview --}}
        <div class="erp-card p-6" id="inventory-section">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="section-title">Inventaris</h3>
                    <p class="section-subtitle">Overview stok bahan</p>
                </div>
                <a href="{{ route('manager.inventory.stock') }}" class="btn-erp btn-erp-outline text-xs">
                    <i class="ti ti-external-link text-sm"></i> Detail Stok
                </a>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-5">
                <div class="rounded-xl p-4" style="background: var(--brand-light); border: 1px solid var(--brand-soft);">
                    <p class="text-xs font-medium mb-1" style="color: var(--text-muted);">Nilai Inventaris</p>
                    <p class="text-lg font-bold" style="color: var(--brand);">Rp {{ \App\Helpers\NumberFormatter::short($totalInventoryValue) }}</p>
                </div>
                <div class="rounded-xl p-4" style="background: var(--danger-bg); border: 1px solid #fecaca;">
                    <p class="text-xs font-medium mb-1" style="color: var(--text-muted);">Item Kritis</p>
                    <p class="text-lg font-bold" style="color: var(--danger);">{{ $criticalStockItems }} item</p>
                </div>
            </div>

            <div class="flex gap-5 items-start">
                <div class="relative flex-shrink-0" style="width: 140px; height: 140px;">
                    <canvas id="chartStockDonut"></canvas>
                    <div class="donut-center">
                        <p class="text-lg font-bold" style="color: var(--text-primary);">{{ $totalStockItems }}</p>
                        <p class="text-xs" style="color: var(--text-muted);">Total</p>
                    </div>
                </div>

                <div class="flex-1 custom-scroll overflow-y-auto" style="max-height: 200px;">
                    @forelse($criticalStockList as $stock)
                    <div class="flex items-center justify-between py-2 border-b" style="border-color: var(--card-border);">
                        <div class="flex items-center gap-2">
                            <span class="status-dot" style="background: {{ $stock->unit_qty == 0 ? 'var(--danger)' : 'var(--warning)' }};"></span>
                            <span class="text-sm" style="color: var(--text-primary);">{{ $stock->name }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-semibold" style="color: {{ $stock->unit_qty == 0 ? 'var(--danger)' : 'var(--warning)' }};">
                                {{ $stock->unit_qty }} {{ $stock->unit->symbol ?? '' }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-6" style="color: var(--text-muted);">
                        <i class="ti ti-mood-happy text-2xl"></i>
                        <p class="text-sm mt-1">Semua stok aman</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </section>

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
         5ï¸âƒ£ PRODUKSI
         â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    <section class="dash-grid-3 fade-in-up fade-delay-5">
        {{-- Production KPI --}}
        <div class="erp-card p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="section-title">Produksi</h3>
                    <p class="section-subtitle">Ringkasan produksi</p>
                </div>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: #f5f3ff;">
                    <i class="ti ti-tools-kitchen-2 text-lg" style="color: #7c3aed;"></i>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div class="rounded-xl p-4 text-center" style="background: var(--brand-light); border: 1px solid var(--brand-soft);">
                    <p class="text-2xl font-bold" style="color: var(--brand);">{{ number_format($productionToday->batches ?? 0) }}</p>
                    <p class="text-xs font-medium" style="color: var(--text-muted);">Batch Hari Ini</p>
                    <p class="text-xs mt-1" style="color: var(--brand);">{{ number_format($productionToday->total_qty ?? 0) }} unit</p>
                </div>
                <div class="rounded-xl p-4 text-center" style="background: var(--info-bg); border: 1px solid #dbeafe;">
                    <p class="text-2xl font-bold" style="color: var(--info);">{{ number_format($productionMonth->batches ?? 0) }}</p>
                    <p class="text-xs font-medium" style="color: var(--text-muted);">Batch Bulan Ini</p>
                    <p class="text-xs mt-1" style="color: var(--info);">{{ number_format($productionMonth->total_qty ?? 0) }} unit</p>
                </div>
            </div>
            <p class="text-xs font-medium mb-2" style="color: var(--text-muted);">Tren Produksi 7 Hari</p>
            <div class="chart-wrapper" style="height: 80px;">
                <canvas id="chartProductionTrend"></canvas>
            </div>
        </div>

        {{-- Top Produced Products --}}
        <div class="erp-card p-6 col-span-2 md:col-span-2 xl:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="section-title">Produk Paling Banyak Diproduksi</h3>
                    <p class="section-subtitle">Bulan ini</p>
                </div>
            </div>
            <div class="space-y-0 custom-scroll overflow-y-auto" style="max-height: 280px;">
                @php $maxProdQty = $topProductions->max('total_qty') ?: 1; @endphp
                @forelse($topProductions as $i => $prod)
                <div class="product-rank-row">
                    <span class="rank-number {{ $i === 0 ? 'rank-1' : ($i === 1 ? 'rank-2' : ($i === 2 ? 'rank-3' : 'rank-default')) }}">
                        {{ $i + 1 }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate" style="color: var(--text-primary);">{{ $prod->product_name }}</p>
                        <div class="progress-bar-bg mt-1">
                            <div class="progress-bar-fill" style="width: {{ ($prod->total_qty / $maxProdQty) * 100 }}%; background: #7c3aed;"></div>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-bold" style="color: var(--text-primary);">{{ number_format($prod->total_qty) }}</span>
                        <p class="text-xs" style="color: var(--text-muted);">{{ $prod->batch_count }} batch</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-8" style="color: var(--text-muted);">
                    <i class="ti ti-tools-kitchen-2-off text-3xl"></i>
                    <p class="text-sm mt-2">Belum ada data produksi bulan ini</p>
                </div>
                @endforelse
            </div>
        </div>
    </section>

        </div>{{-- END TAB OPERASIONAL --}}

        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
             TAB: MARKETING
             â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
        <div x-show="tab === 'marketing'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">

    {{-- Marketing KPI Cards --}}
    <section>
        <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
            {{-- Total Customers --}}
            <div class="erp-card p-5 flex flex-col justify-between gap-3 fade-in-up fade-delay-1">
                <div class="flex items-center justify-between">
                    <span class="kpi-label">Total Pelanggan</span>
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: var(--info-bg);">
                        <i class="ti ti-users text-lg" style="color: var(--info);"></i>
                    </div>
                </div>
                <div>
                    <p class="kpi-value">{{ number_format($totalCustomers) }}</p>
                    <span style="font-size:.65rem; color: var(--text-muted);">terdaftar di toko ini</span>
                </div>
            </div>

            {{-- New Customers This Month --}}
            <div class="erp-card p-5 flex flex-col justify-between gap-3 fade-in-up fade-delay-2">
                <div class="flex items-center justify-between">
                    <span class="kpi-label">Pelanggan Baru</span>
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: var(--brand-light);">
                        <i class="ti ti-user-plus text-lg" style="color: var(--brand);"></i>
                    </div>
                </div>
                <div>
                    <p class="kpi-value">{{ number_format($newCustomersMonth) }}</p>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="growth-badge {{ $newCustomerGrowth >= 0 ? 'growth-up' : 'growth-down' }}">
                            <i class="ti {{ $newCustomerGrowth >= 0 ? 'ti-arrow-up-right' : 'ti-arrow-down-right' }} text-xs"></i>
                            {{ abs($newCustomerGrowth) }}%
                        </span>
                        <span style="font-size:.65rem; color: var(--text-muted);">vs bulan lalu</span>
                    </div>
                </div>
            </div>

            {{-- Repeat Rate --}}
            <div class="erp-card p-5 flex flex-col justify-between gap-3 fade-in-up fade-delay-3">
                <div class="flex items-center justify-between">
                    <span class="kpi-label">Repeat Rate</span>
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: #fce7f3;">
                        <i class="ti ti-repeat text-lg" style="color: #db2777;"></i>
                    </div>
                </div>
                <div>
                    <p class="kpi-value">{{ $repeatRate }}%</p>
                    <span style="font-size:.65rem; color: var(--text-muted);">{{ $repeatCustomers }} dari {{ $uniqueCustomersMonth }} pelanggan</span>
                </div>
            </div>

            {{-- Total Resellers --}}
            <div class="erp-card p-5 flex flex-col justify-between gap-3 fade-in-up fade-delay-4">
                <div class="flex items-center justify-between">
                    <span class="kpi-label">Total Reseller</span>
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: #f5f3ff;">
                        <i class="ti ti-building-store text-lg" style="color: #7c3aed;"></i>
                    </div>
                </div>
                <div>
                    <p class="kpi-value">{{ number_format($totalResellers) }}</p>
                    <span style="font-size:.65rem; color: var(--text-muted);">aktif di toko ini</span>
                </div>
            </div>

            {{-- Unique Buyers This Month --}}
            <div class="erp-card p-5 flex flex-col justify-between gap-3 fade-in-up fade-delay-5">
                <div class="flex items-center justify-between">
                    <span class="kpi-label">Pembeli Unik</span>
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: #fef3c7;">
                        <i class="ti ti-fingerprint text-lg" style="color: #d97706;"></i>
                    </div>
                </div>
                <div>
                    <p class="kpi-value">{{ number_format($uniqueCustomersMonth) }}</p>
                    <span style="font-size:.65rem; color: var(--text-muted);">bulan ini</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Top Customers + Order Channels --}}
    <section class="dash-grid-2 fade-in-up fade-delay-3">
        {{-- Top Customers --}}
        <div class="erp-card p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="section-title">Top Pelanggan</h3>
                    <p class="section-subtitle">Bulan ini berdasarkan spending</p>
                </div>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: var(--info-bg);">
                    <i class="ti ti-crown text-lg" style="color: var(--info);"></i>
                </div>
            </div>
            <div class="space-y-0 custom-scroll overflow-y-auto" style="max-height: 320px;">
                @php $maxSpent = $topCustomers->max('total_spent') ?: 1; @endphp
                @forelse($topCustomers as $i => $cust)
                <div class="product-rank-row">
                    <span class="rank-number {{ $i === 0 ? 'rank-1' : ($i === 1 ? 'rank-2' : ($i === 2 ? 'rank-3' : 'rank-default')) }}">
                        {{ $i + 1 }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate" style="color: var(--text-primary);">{{ $cust->name }}</p>
                        <div class="progress-bar-bg mt-1">
                            <div class="progress-bar-fill" style="width: {{ ($cust->total_spent / $maxSpent) * 100 }}%; background: var(--info);"></div>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-bold" style="color: var(--text-primary);">Rp {{ \App\Helpers\NumberFormatter::short($cust->total_spent) }}</span>
                        <p class="text-xs" style="color: var(--text-muted);">{{ $cust->order_count }} pesanan</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-8" style="color: var(--text-muted);">
                    <i class="ti ti-users-minus text-3xl"></i>
                    <p class="text-sm mt-2">Belum ada data pelanggan bulan ini</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Order Channels --}}
        <div class="erp-card p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="section-title">Channel Pesanan</h3>
                    <p class="section-subtitle">Sumber order bulan ini</p>
                </div>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: #fef3c7;">
                    <i class="ti ti-affiliate text-lg" style="color: #d97706;"></i>
                </div>
            </div>
            <div class="flex items-center justify-center mb-4" style="height: 180px;">
                <div class="relative" style="width: 170px; height: 170px;">
                    <canvas id="chartChannelDonut"></canvas>
                    <div class="donut-center">
                        <p class="text-sm font-bold" style="color: var(--text-primary);">{{ $orderChannels->sum('cnt') }}</p>
                        <p class="text-xs" style="color: var(--text-muted);">Pesanan</p>
                    </div>
                </div>
            </div>
            <div class="space-y-2">
                @php
                $channelColors = ['Langsung'=>'#10b981','POS'=>'#0C9044','Online'=>'#3b82f6','Kasir'=>'#f59e0b','Reseller'=>'#7c3aed','Website'=>'#db2777','WhatsApp'=>'#25D366'];
                @endphp
                @foreach($orderChannels as $ch)
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: {{ $channelColors[$ch->channel] ?? '#94a3b8' }};"></span>
                        <span style="color: var(--text-secondary);">{{ $ch->channel }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs" style="color: var(--text-muted);">{{ $ch->cnt }} pesanan</span>
                        <span class="font-semibold" style="color: var(--text-primary);">Rp {{ \App\Helpers\NumberFormatter::short($ch->revenue) }}</span>
                    </div>
                </div>
                @endforeach
                @if($orderChannels->isEmpty())
                <div class="text-center py-4" style="color: var(--text-muted);">
                    <p class="text-sm">Belum ada data pesanan bulan ini</p>
                </div>
                @endif
            </div>
        </div>
    </section>

    {{-- Reseller Performance --}}
    @if($resellerPerformance->count() > 0)
    <section class="fade-in-up fade-delay-4">
        <div class="erp-card p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="section-title">Performa Reseller</h3>
                    <p class="section-subtitle">Bulan ini berdasarkan revenue</p>
                </div>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: #f5f3ff;">
                    <i class="ti ti-building-store text-lg" style="color: #7c3aed;"></i>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                @php $maxResRevenue = $resellerPerformance->max('revenue') ?: 1; @endphp
                @foreach($resellerPerformance as $i => $res)
                <div class="rounded-xl p-4" style="border: 1px solid var(--card-border); background: var(--card-bg);">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold"
                            style="background: {{ $i === 0 ? '#fef3c7' : ($i === 1 ? '#e2e8f0' : '#f5f3ff') }};
                                   color: {{ $i === 0 ? '#d97706' : ($i === 1 ? '#475569' : '#7c3aed') }};">
                            {{ strtoupper(substr($res->name, 0, 2)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold truncate" style="color: var(--text-primary);">{{ $res->name }}</p>
                            <p class="text-xs" style="color: var(--text-muted);">{{ $res->order_count }} pesanan</p>
                        </div>
                    </div>
                    <p class="text-lg font-bold" style="color: var(--brand);">Rp {{ \App\Helpers\NumberFormatter::short($res->revenue) }}</p>
                    <div class="progress-bar-bg mt-2">
                        <div class="progress-bar-fill" style="width: {{ ($res->revenue / $maxResRevenue) * 100 }}%; background: #7c3aed;"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

        </div>{{-- END TAB MARKETING --}}

        {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
             TAB: KEUANGAN
             â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
        <div x-show="tab === 'keuangan'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">

    {{-- Revenue Target (moved from overview to Keuangan tab) --}}
    @if (App\Helpers\RoleHelper::hasAnyRoleIncludingDescendants(['Manager-Finance']))
    @if($revenueTargetDaily > 0)
    <section class="erp-card p-5 fade-in-up fade-delay-1">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: #fef3c7;">
                    <i class="ti ti-target text-xl" style="color: #d97706;"></i>
                </div>
                <div>
                    <h3 class="section-title">Target Revenue</h3>
                    <p class="section-subtitle">Berdasarkan rata-rata 30 hari (+10%)</p>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium" style="color: var(--text-secondary);">Target Harian</span>
                    <span class="text-sm font-bold" style="color: {{ $revenueDailyProgress >= 100 ? 'var(--success)' : 'var(--text-primary)' }};">
                        {{ $revenueDailyProgress }}%
                    </span>
                </div>
                <div class="target-progress-bg">
                    <div class="target-progress-fill {{ $revenueDailyProgress >= 100 ? 'over-target' : '' }}" style="width: 0%;" data-target-width="{{ $revenueDailyProgress }}"></div>
                </div>
                <div class="flex items-center justify-between mt-2">
                    <span class="text-xs" style="color: var(--text-muted);">Rp {{ \App\Helpers\NumberFormatter::short($revenueToday) }}</span>
                    <span class="text-xs" style="color: var(--text-muted);">Target: Rp {{ \App\Helpers\NumberFormatter::short($revenueTargetDaily) }}</span>
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium" style="color: var(--text-secondary);">Target Bulanan</span>
                    <span class="text-sm font-bold" style="color: {{ $revenueMonthlyProgress >= 100 ? 'var(--success)' : 'var(--text-primary)' }};">
                        {{ $revenueMonthlyProgress }}%
                    </span>
                </div>
                <div class="target-progress-bg">
                    <div class="target-progress-fill {{ $revenueMonthlyProgress >= 100 ? 'over-target' : '' }}" style="width: 0%;" data-target-width="{{ $revenueMonthlyProgress }}"></div>
                </div>
                <div class="flex items-center justify-between mt-2">
                    <span class="text-xs" style="color: var(--text-muted);">Rp {{ \App\Helpers\NumberFormatter::short($revenueMTD) }}</span>
                    <span class="text-xs" style="color: var(--text-muted);">Target: Rp {{ \App\Helpers\NumberFormatter::short($revenueTargetMonthly) }}</span>
                </div>
            </div>
        </div>
    </section>
    @endif
    @endif

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
         5ï¸âƒ£ PAYMENT & FINANCIAL + 6ï¸âƒ£ SMART ALERTS
         â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    @if (App\Helpers\RoleHelper::hasAnyRoleIncludingDescendants(['Manager-Finance']))
    <section id="financial-section" class="dash-grid-3 fade-in-up fade-delay-5">
        {{-- Payment Breakdown --}}
        <div class="erp-card p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="section-title">Metode Pembayaran</h3>
                    <p class="section-subtitle">Breakdown hari ini</p>
                </div>
            </div>
            <div class="flex items-center justify-center" style="height: 200px;">
                <div class="relative" style="width: 180px; height: 180px;">
                    <canvas id="chartPaymentDonut"></canvas>
                    <div class="donut-center">
                        <p class="text-sm font-bold" style="color: var(--text-primary);">{{ $paymentBreakdown->sum('count') }}</p>
                        <p class="text-xs" style="color: var(--text-muted);">Transaksi</p>
                    </div>
                </div>
            </div>
            <div class="space-y-2 mt-4">
                @php
                $paymentColors = ['Cash'=>'#10b981','QRIS'=>'#3b82f6','E-wallet'=>'#8b5cf6','Transfer'=>'#f59e0b','qris'=>'#3b82f6','cash'=>'#10b981','transfer'=>'#f59e0b','gopay'=>'#00AED6','ovo'=>'#4A3AFF','dana'=>'#108EE9'];
                @endphp
                @foreach($paymentBreakdown as $pay)
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background: {{ $paymentColors[$pay->payment_type] ?? '#94a3b8' }};"></span>
                        <span style="color: var(--text-secondary);">{{ ucfirst($pay->payment_type ?? 'Lainnya') }}</span>
                    </div>
                    <span class="font-semibold" style="color: var(--text-primary);">Rp {{ \App\Helpers\NumberFormatter::short($pay->total) }}</span>
                </div>
                @endforeach
                @if($paymentBreakdown->isEmpty())
                <div class="text-center py-4" style="color: var(--text-muted);">
                    <p class="text-sm">Belum ada transaksi hari ini</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Financial Summary --}}
        <div class="erp-card p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="section-title">Ringkasan Keuangan</h3>
                    <p class="section-subtitle">Hari ini</p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 rounded-xl" style="background: var(--brand-light);">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--brand-soft);">
                            <i class="ti ti-cash text-lg" style="color: var(--brand);"></i>
                        </div>
                        <div>
                            <p class="text-xs" style="color: var(--text-muted);">Revenue Kotor</p>
                            <p class="font-bold" style="color: var(--text-primary);">Rp {{ number_format($revenueToday, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between p-3 rounded-xl" style="background: var(--danger-bg);">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: #fecaca;">
                            <i class="ti ti-discount-2 text-lg" style="color: var(--danger);"></i>
                        </div>
                        <div>
                            <p class="text-xs" style="color: var(--text-muted);">Total Diskon</p>
                            <p class="font-bold" style="color: var(--danger);">- Rp {{ number_format($totalDiscountToday, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between p-3 rounded-xl" style="background: var(--success-bg);">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--brand-soft);">
                            <i class="ti ti-coin text-lg" style="color: var(--brand);"></i>
                        </div>
                        <div>
                            <p class="text-xs" style="color: var(--text-muted);">Laba Kotor</p>
                            <p class="font-bold" style="color: var(--brand);">Rp {{ number_format($grossProfitToday, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-lg font-bold" style="color: var(--brand);">{{ $grossMarginToday }}%</span>
                        <p class="text-xs" style="color: var(--text-muted);">margin</p>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <p class="text-xs font-medium mb-2" style="color: var(--text-muted);">Tren Laba 7 Hari</p>
                <div class="chart-wrapper" style="height: 80px;">
                    <canvas id="chartProfitTrend"></canvas>
                </div>
            </div>
        </div>

        {{-- Smart Alerts --}}
        <div class="erp-card p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="section-title">Smart Alerts</h3>
                    <p class="section-subtitle">Insight & rekomendasi</p>
                </div>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background: #fef3c7;">
                    <i class="ti ti-bell-ringing text-lg" style="color: #d97706;"></i>
                </div>
            </div>

            <div class="space-y-3">
                @foreach($alerts as $alert)
                <div class="alert-card alert-{{ $alert['severity'] }}">
                    <div class="flex-shrink-0 mt-0.5">
                        <i class="ti {{ $alert['icon'] }} text-xl" style="color: {{ $alert['severity'] === 'danger' ? 'var(--danger)' : ($alert['severity'] === 'warning' ? 'var(--warning)' : ($alert['severity'] === 'info' ? 'var(--info)' : 'var(--success)')) }};"></i>
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-sm" style="color: var(--text-primary);">{{ $alert['message'] }}</p>
                        @if($alert['action'])
                        <a href="{{ $alert['link'] }}" class="inline-flex items-center gap-1 mt-2 text-xs font-semibold transition-colors" style="color: {{ $alert['severity'] === 'danger' ? 'var(--danger)' : ($alert['severity'] === 'warning' ? 'var(--warning)' : 'var(--info)') }};">
                            {{ $alert['action'] }} <i class="ti ti-arrow-right text-xs"></i>
                        </a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Quick Stats --}}
            <div class="mt-5 pt-4" style="border-top: 1px solid var(--card-border);">
                <p class="text-xs font-medium mb-3" style="color: var(--text-muted);">Ringkasan Quick Stats</p>
                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3 rounded-xl text-center" style="background: var(--brand-light);">
                        <p class="text-lg font-bold" style="color: var(--brand);">{{ $totalTransaction }}</p>
                        <p class="text-xs" style="color: var(--text-muted);">Total Semua Transaksi</p>
                    </div>
                    <div class="p-3 rounded-xl text-center" style="background: var(--info-bg);">
                        <p class="text-lg font-bold" style="color: var(--info);">Rp {{ \App\Helpers\NumberFormatter::short($totalSales) }}</p>
                        <p class="text-xs" style="color: var(--text-muted);">Total Semua Revenue</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
         HISTORICAL SALES CHART
         â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    @if (App\Helpers\RoleHelper::hasAnyRoleIncludingDescendants(['Manager-Finance']))
    <section class="erp-card p-6 fade-in-up fade-delay-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
            <div>
                <h3 class="section-title">Tren Penjualan</h3>
                <p class="section-subtitle">Riwayat penjualan berdasarkan periode</p>
            </div>
            <div class="flex items-center gap-2">
                <select id="filterWaktu" onchange="updateSalesChart()" class="px-4 py-2 rounded-xl text-sm font-medium border-0" style="background: var(--brand-light); color: var(--brand);">
                    <option value="hari">Harian</option>
                    <option value="minggu" selected>Mingguan</option>
                    <option value="bulan">Bulanan</option>
                    <option value="tahun">Tahunan</option>
                </select>
            </div>
        </div>

        <div class="chart-wrapper" style="height: 280px;">
            <canvas id="salesChart"></canvas>
        </div>

        <div class="flex items-center justify-between mt-4">
            <button onclick="prevBatch()" class="btn-erp btn-erp-outline text-xs">
                <i class="ti ti-chevron-left text-sm"></i> Sebelumnya
            </button>
            <span id="chartRangeLabel" class="text-sm font-medium" style="color: var(--text-muted);">-</span>
            <button onclick="nextBatch()" class="btn-erp btn-erp-outline text-xs">
                Selanjutnya <i class="ti ti-chevron-right text-sm"></i>
            </button>
        </div>
    </section>
    @endif

        </div>{{-- END TAB KEUANGAN --}}
    </div>{{-- END x-data TAB WRAPPER --}}

</div>

@endsection

@section('vendor-script')
@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {

    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    // UTILITIES
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

    const COLORS = {
        brand: '#0C9044',
        brandSoft: '#d1fae5',
        info: '#3b82f6',
        infoSoft: '#dbeafe',
        warning: '#f59e0b',
        danger: '#ef4444',
        purple: '#7c3aed',
        pink: '#db2777',
        gray: '#94a3b8',
        graySoft: '#f1f5f9',
    };

    const formatRp = (val) => 'Rp ' + new Intl.NumberFormat('id-ID').format(val);

    const chartDefaults = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(15,23,42,0.9)',
                titleFont: { family: 'Poppins', size: 12 },
                bodyFont: { family: 'Poppins', size: 11 },
                padding: 12,
                cornerRadius: 10,
                displayColors: false,
            },
        },
    };

    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    // LIVE CLOCK
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

    function updateClock() {
        const now = new Date();
        const el = document.getElementById('live-clock');
        if (el) el.textContent = now.toLocaleTimeString('id-ID', { hour12: false });
    }
    updateClock();
    setInterval(updateClock, 1000);

    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    // AUTO-REFRESH (every 60 seconds)
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

    let refreshCountdown = 60;
    const countdownEl = document.getElementById('refresh-countdown');

    function tickRefresh() {
        refreshCountdown--;
        if (countdownEl) countdownEl.textContent = refreshCountdown + 's';
        if (refreshCountdown <= 0) {
            window.location.reload();
        }
    }
    setInterval(tickRefresh, 1000);

    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    // TARGET PROGRESS BAR ANIMATION
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

    document.querySelectorAll('[data-target-width]').forEach(el => {
        const targetWidth = el.getAttribute('data-target-width');
        setTimeout(() => { el.style.width = targetWidth + '%'; }, 300);
    });

    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    // SPARKLINES â€” ALL REAL DATA from controller
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

    const sparkRevenue = {!! json_encode($last7DaysRevenue) !!};
    const sparkTransactions = {!! json_encode($last7DaysTransactions) !!};
    const sparkItems = {!! json_encode($last7DaysItems) !!};
    const sparkAov = {!! json_encode($last7DaysAov) !!};
    const profitData = {!! json_encode($profitTrend) !!};

    function drawSparkline(canvasId, data, color) {
        const el = document.getElementById(canvasId);
        if (!el) return;
        new Chart(el, {
            type: 'line',
            data: {
                labels: data.map((_, i) => i),
                datasets: [{
                    data: data,
                    borderColor: color,
                    borderWidth: 2,
                    fill: true,
                    backgroundColor: color + '15',
                    tension: 0.4,
                    pointRadius: 0,
                    pointHitRadius: 0,
                }]
            },
            options: {
                ...chartDefaults,
                scales: { x: { display: false }, y: { display: false } },
                plugins: { ...chartDefaults.plugins, tooltip: { enabled: false } },
            }
        });
    }

    drawSparkline('spark-revenue', sparkRevenue, COLORS.brand);
    drawSparkline('spark-mtd', sparkRevenue, COLORS.info);
    drawSparkline('spark-trx', sparkTransactions, COLORS.purple);
    drawSparkline('spark-items', sparkItems, '#d97706');
    drawSparkline('spark-aov', sparkAov, COLORS.pink);
    drawSparkline('spark-profit', profitData, COLORS.brand);

    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    // HOURLY SALES LINE CHART
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

    const hourlyData = {!! json_encode($hourlySalesData) !!};
    const hourLabels = Array.from({length: 24}, (_, i) => `${String(i).padStart(2,'0')}:00`);

    if (document.getElementById('chartHourly')) {
        new Chart(document.getElementById('chartHourly'), {
            type: 'line',
            data: {
                labels: hourLabels,
                datasets: [{
                    data: hourlyData,
                    borderColor: COLORS.brand,
                    borderWidth: 2.5,
                    fill: true,
                    backgroundColor: COLORS.brand + '12',
                    tension: 0.35,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: COLORS.brand,
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                }]
            },
            options: {
                ...chartDefaults,
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 9, family: 'Poppins' }, color: '#94a3b8', maxTicksLimit: 8 } },
                    y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 10, family: 'Poppins' }, color: '#94a3b8', callback: v => formatRp(v) } },
                },
                plugins: { ...chartDefaults.plugins, tooltip: { ...chartDefaults.plugins.tooltip, callbacks: { label: (ctx) => formatRp(ctx.raw) } } },
            }
        });
    }

    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    // 7-DAY BAR CHART
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

    const last7Labels = {!! json_encode($last7DaysLabels) !!};
    const last7Data = {!! json_encode($last7DaysSales) !!};

    if (document.getElementById('chart7Days')) {
        new Chart(document.getElementById('chart7Days'), {
            type: 'bar',
            data: {
                labels: last7Labels,
                datasets: [{
                    data: last7Data,
                    backgroundColor: last7Data.map((v, i) => i === last7Data.length - 1 ? COLORS.brand : COLORS.brandSoft),
                    borderRadius: 8,
                    borderSkipped: false,
                    maxBarThickness: 40,
                }]
            },
            options: {
                ...chartDefaults,
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10, family: 'Poppins' }, color: '#94a3b8' } },
                    y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 10, family: 'Poppins' }, color: '#94a3b8', callback: v => formatRp(v) }, beginAtZero: true },
                },
                plugins: { ...chartDefaults.plugins, tooltip: { ...chartDefaults.plugins.tooltip, callbacks: { label: (ctx) => formatRp(ctx.raw) } } },
            }
        });
    }

    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    // ORDERS TIMELINE â€” REAL ORDER COUNT DATA
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

    const hourlyOrderCounts = {!! json_encode($hourlyOrderCounts) !!};

    if (document.getElementById('chartOrdersTimeline')) {
        new Chart(document.getElementById('chartOrdersTimeline'), {
            type: 'line',
            data: {
                labels: hourLabels,
                datasets: [{
                    label: 'Orders',
                    data: hourlyOrderCounts,
                    borderColor: COLORS.info,
                    borderWidth: 2,
                    fill: true,
                    backgroundColor: COLORS.info + '12',
                    tension: 0.35,
                    pointRadius: 0,
                }]
            },
            options: {
                ...chartDefaults,
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 9, family: 'Poppins' }, color: '#94a3b8', maxTicksLimit: 8 } },
                    y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 10, family: 'Poppins' }, color: '#94a3b8', stepSize: 1, callback: v => Math.round(v) === v ? v : '' }, beginAtZero: true },
                },
                plugins: { ...chartDefaults.plugins, tooltip: { ...chartDefaults.plugins.tooltip, callbacks: { label: (ctx) => ctx.raw + ' pesanan' } } },
            }
        });
    }

    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    // STOCK DONUT
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

    if (document.getElementById('chartStockDonut')) {
        new Chart(document.getElementById('chartStockDonut'), {
            type: 'doughnut',
            data: {
                labels: ['Aman', 'Menipis', 'Kritis'],
                datasets: [{
                    data: [{{ $healthyStockItems }}, {{ $lowStockItems }}, {{ $criticalStockItems }}],
                    backgroundColor: [COLORS.brand, COLORS.warning, COLORS.danger],
                    borderWidth: 0,
                }]
            },
            options: {
                ...chartDefaults,
                cutout: '70%',
                plugins: { ...chartDefaults.plugins, tooltip: { ...chartDefaults.plugins.tooltip, callbacks: { label: (ctx) => `${ctx.label}: ${ctx.raw} item` } } },
            }
        });
    }

    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    // PAYMENT DONUT
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

    const payLabels = {!! json_encode($paymentBreakdown->pluck('payment_type')->map(fn($v) => ucfirst($v ?? 'Lainnya'))) !!};
    const payData = {!! json_encode($paymentBreakdown->pluck('total')) !!};
    const payColors = payLabels.map(l => {
        const map = {'Cash':'#10b981','Qris':'#3b82f6','Transfer':'#f59e0b','Gopay':'#00AED6','Ovo':'#4A3AFF','Dana':'#108EE9','E-wallet':'#8b5cf6'};
        return map[l] || '#94a3b8';
    });

    if (document.getElementById('chartPaymentDonut')) {
        new Chart(document.getElementById('chartPaymentDonut'), {
            type: 'doughnut',
            data: {
                labels: payLabels,
                datasets: [{ data: payData, backgroundColor: payColors, borderWidth: 0 }]
            },
            options: {
                ...chartDefaults,
                cutout: '70%',
                plugins: { ...chartDefaults.plugins, tooltip: { ...chartDefaults.plugins.tooltip, callbacks: { label: (ctx) => `${ctx.label}: ${formatRp(ctx.raw)}` } } },
            }
        });
    }

    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    // PROFIT TREND LINE
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

    if (document.getElementById('chartProfitTrend')) {
        new Chart(document.getElementById('chartProfitTrend'), {
            type: 'line',
            data: {
                labels: ['','','','','','',''],
                datasets: [{
                    data: profitData,
                    borderColor: COLORS.brand,
                    borderWidth: 2.5,
                    fill: true,
                    backgroundColor: COLORS.brand + '12',
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 4,
                }]
            },
            options: {
                ...chartDefaults,
                scales: { x: { display: false }, y: { display: false } },
                plugins: { ...chartDefaults.plugins, tooltip: { ...chartDefaults.plugins.tooltip, callbacks: { label: (ctx) => formatRp(ctx.raw) } } },
            }
        });
    }

    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    // PRODUCTION TREND (7 days)
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

    const production7Days = {!! json_encode($production7Days) !!};

    if (document.getElementById('chartProductionTrend')) {
        new Chart(document.getElementById('chartProductionTrend'), {
            type: 'line',
            data: {
                labels: ['','','','','','',''],
                datasets: [{
                    data: production7Days,
                    borderColor: '#7c3aed',
                    borderWidth: 2.5,
                    fill: true,
                    backgroundColor: '#7c3aed15',
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 4,
                }]
            },
            options: {
                ...chartDefaults,
                scales: { x: { display: false }, y: { display: false } },
                plugins: { ...chartDefaults.plugins, tooltip: { ...chartDefaults.plugins.tooltip, callbacks: { label: (ctx) => ctx.raw + ' unit' } } },
            }
        });
    }

    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    // CHANNEL DONUT (Marketing tab)
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

    const channelLabels = {!! json_encode($orderChannels->pluck('channel')) !!};
    const channelData = {!! json_encode($orderChannels->pluck('cnt')) !!};
    const channelColorMap = {'Langsung':'#10b981','POS':'#0C9044','Online':'#3b82f6','Kasir':'#f59e0b','Reseller':'#7c3aed','Website':'#db2777','WhatsApp':'#25D366'};
    const channelChartColors = channelLabels.map(l => channelColorMap[l] || '#94a3b8');

    if (document.getElementById('chartChannelDonut')) {
        new Chart(document.getElementById('chartChannelDonut'), {
            type: 'doughnut',
            data: {
                labels: channelLabels,
                datasets: [{ data: channelData, backgroundColor: channelChartColors, borderWidth: 0 }]
            },
            options: {
                ...chartDefaults,
                cutout: '70%',
                plugins: { ...chartDefaults.plugins, tooltip: { ...chartDefaults.plugins.tooltip, callbacks: { label: (ctx) => `${ctx.label}: ${ctx.raw} pesanan` } } },
            }
        });
    }

    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    // HISTORICAL SALES CHART
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

    const chartLabels = {
        hari: {!! json_encode($penjualanHarian->pluck('tanggal')) !!},
        minggu: {!! json_encode($penjualanMingguan->pluck('minggu_ke')->map(fn($m) => 'Minggu ' . $m)) !!},
        bulan: {!! json_encode($penjualanBulanan->pluck('bulan')) !!},
        tahun: {!! json_encode($penjualanTahunan->pluck('tahun')) !!}
    };

    const chartData = {
        hari: {!! json_encode($penjualanHarian->pluck('total_penjualan')) !!},
        minggu: {!! json_encode($penjualanMingguan->pluck('total_penjualan')) !!},
        bulan: {!! json_encode($penjualanBulanan->pluck('total_penjualan')) !!},
        tahun: {!! json_encode($penjualanTahunan->pluck('total_penjualan')) !!}
    };

    let salesChart = null;
    let chartType = 'minggu';
    let currentBatchIndex = 0;
    const batchSizes = { hari: 7, minggu: 4, bulan: 6, tahun: 5 };

    function getBatchData(type, index) {
        const labels = chartLabels[type];
        const data = chartData[type];
        const size = batchSizes[type];
        const start = Math.max(0, labels.length - (index + 1) * size);
        const end = labels.length - index * size;
        return {
            labels: labels.slice(start, end),
            data: data.slice(start, end),
            range: `${labels[start] || '-'} â€” ${labels[end - 1] || '-'}`
        };
    }

    window.renderSalesChart = function(type = chartType, index = currentBatchIndex) {
        chartType = type;
        currentBatchIndex = index;
        const ctx = document.getElementById('salesChart');
        if (!ctx) return;
        if (salesChart) salesChart.destroy();

        const { labels, data, range } = getBatchData(type, index);
        document.getElementById('chartRangeLabel').innerText = range;

        salesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: data.map((_, i) => i === data.length - 1 ? COLORS.brand : COLORS.brandSoft),
                    borderRadius: 10,
                    borderSkipped: false,
                    maxBarThickness: 48,
                }]
            },
            options: {
                ...chartDefaults,
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11, family: 'Poppins' }, color: '#94a3b8' } },
                    y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 11, family: 'Poppins' }, color: '#94a3b8', callback: v => formatRp(v) }, beginAtZero: true },
                },
                plugins: { ...chartDefaults.plugins, tooltip: { ...chartDefaults.plugins.tooltip, callbacks: { label: (ctx) => formatRp(ctx.raw) } } },
            }
        });
    }

    window.updateSalesChart = function() {
        currentBatchIndex = 0;
        renderSalesChart(document.getElementById('filterWaktu').value);
    }

    window.prevBatch = function() {
        currentBatchIndex++;
        renderSalesChart(chartType, currentBatchIndex);
    }

    window.nextBatch = function() {
        if (currentBatchIndex > 0) {
            currentBatchIndex--;
            renderSalesChart(chartType, currentBatchIndex);
        }
    }

    renderSalesChart('minggu');

    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    // TOP PRODUCTS TOGGLE
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

    window.switchTopProducts = function(mode) {
        const btnQty = document.getElementById('btn-top-qty');
        const btnRev = document.getElementById('btn-top-revenue');

        if (mode === 'qty') {
            btnQty.style.background = COLORS.brand; btnQty.style.color = 'white';
            btnRev.style.background = 'var(--card-border)'; btnRev.style.color = 'var(--text-secondary)';
            document.querySelectorAll('.product-value').forEach(el => el.classList.remove('hidden'));
            document.querySelectorAll('.product-value-alt').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.product-sub').forEach(el => el.classList.remove('hidden'));
            document.querySelectorAll('.product-sub-alt').forEach(el => el.classList.add('hidden'));
        } else {
            btnRev.style.background = COLORS.brand; btnRev.style.color = 'white';
            btnQty.style.background = 'var(--card-border)'; btnQty.style.color = 'var(--text-secondary)';
            document.querySelectorAll('.product-value').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.product-value-alt').forEach(el => el.classList.remove('hidden'));
            document.querySelectorAll('.product-sub').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.product-sub-alt').forEach(el => el.classList.remove('hidden'));
        }
    }

    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
    // SMOOTH SCROLL
    // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

    window.scrollToSection = function(id) {
        const el = document.getElementById(id);
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

});
</script>
@endsection

