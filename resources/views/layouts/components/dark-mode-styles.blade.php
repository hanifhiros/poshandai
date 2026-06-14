{{-- ============================================
    POS DARK MODE — Theme: posdark
    Professional dark theme optimized for
    prolonged POS operation in low-light
    ============================================ --}}
<style id="pos-dark-mode">
    /* CSS Custom Properties */
    [data-theme="posdark"] {
        --pos-bg: #0d1117;
        --pos-surface: #161b22;
        --pos-surface-alt: #1c2128;
        --pos-surface-hover: #21262d;
        --pos-border: #30363d;
        --pos-border-muted: #21262d;
        --pos-text: #e6edf3;
        --pos-text-dim: #c9d1d9;
        --pos-text-secondary: #8b949e;
        --pos-text-muted: #6e7681;
        --pos-text-faint: #484f58;
        --pos-green: #2ea043;
        --pos-green-bright: #3fb950;
        --pos-green-soft: rgba(46,160,67,0.12);
    }

    /* Smooth theme transition */
    [data-theme] body,
    [data-theme] aside,
    [data-theme] .bg-white,
    [data-theme] .prod-card,
    [data-theme] .cart-item,
    [data-theme] .card-payment {
        transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
    }

    /* === FOUNDATIONS === */
    [data-theme="posdark"] body {
        background-color: var(--pos-bg) !important;
        color: var(--pos-text) !important;
    }

    /* White surfaces → dark */
    [data-theme="posdark"] .bg-white {
        background-color: var(--pos-surface) !important;
    }
    [data-theme="posdark"] .bg-slate-50,
    [data-theme="posdark"] .bg-slate-50\/50 {
        background-color: var(--pos-bg) !important;
    }
    [data-theme="posdark"] .bg-slate-100 {
        background-color: var(--pos-surface-hover) !important;
    }
    [data-theme="posdark"] .bg-gray-50 {
        background-color: var(--pos-bg) !important;
    }
    [data-theme="posdark"] .bg-gray-100 {
        background-color: var(--pos-surface) !important;
    }

    /* === TEXT COLORS === */
    [data-theme="posdark"] .text-slate-800,
    [data-theme="posdark"] .text-gray-800 { color: var(--pos-text) !important; }
    [data-theme="posdark"] .text-slate-700,
    [data-theme="posdark"] .text-gray-700 { color: var(--pos-text-dim) !important; }
    [data-theme="posdark"] .text-slate-600,
    [data-theme="posdark"] .text-gray-600 { color: #b1bac4 !important; }
    [data-theme="posdark"] .text-slate-500,
    [data-theme="posdark"] .text-gray-500 { color: var(--pos-text-secondary) !important; }
    [data-theme="posdark"] .text-slate-400,
    [data-theme="posdark"] .text-gray-400 { color: var(--pos-text-muted) !important; }
    [data-theme="posdark"] .text-slate-300,
    [data-theme="posdark"] .text-gray-300 { color: var(--pos-text-faint) !important; }
    [data-theme="posdark"] .text-gray-200 { color: #484f58 !important; }
    [data-theme="posdark"] .text-gray-900 { color: var(--pos-text) !important; }
    [data-theme="posdark"] .text-\[\#3A3A3A\] { color: var(--pos-text) !important; }

    /* === BORDERS === */
    [data-theme="posdark"] .border-slate-200\/80,
    [data-theme="posdark"] .border-slate-200,
    [data-theme="posdark"] .border-gray-200 {
        border-color: var(--pos-border) !important;
    }
    [data-theme="posdark"] .border-slate-100,
    [data-theme="posdark"] .border-slate-50,
    [data-theme="posdark"] .border-gray-100,
    [data-theme="posdark"] .border-gray-50 {
        border-color: var(--pos-border-muted) !important;
    }
    [data-theme="posdark"] .border-dashed {
        border-color: var(--pos-border) !important;
    }
    [data-theme="posdark"] hr {
        border-color: var(--pos-border-muted) !important;
    }

    /* === GREEN ACCENTS === */
    [data-theme="posdark"] .bg-green-50,
    [data-theme="posdark"] .bg-green-100 {
        background-color: var(--pos-green-soft) !important;
    }
    [data-theme="posdark"] .bg-emerald-50,
    [data-theme="posdark"] .bg-emerald-100 {
        background-color: rgba(16,185,129,0.1) !important;
    }
    [data-theme="posdark"] .border-emerald-200 {
        border-color: rgba(16,185,129,0.2) !important;
    }
    [data-theme="posdark"] .border-green-200 {
        border-color: rgba(46,160,67,0.25) !important;
    }
    [data-theme="posdark"] .border-green-500 {
        border-color: var(--pos-green) !important;
    }
    [data-theme="posdark"] .text-emerald-700 { color: var(--pos-green-bright) !important; }
    [data-theme="posdark"] .text-emerald-600 { color: var(--pos-green) !important; }
    [data-theme="posdark"] .text-green-700 { color: var(--pos-green-bright) !important; }
    [data-theme="posdark"] .text-green-600 { color: var(--pos-green) !important; }
    [data-theme="posdark"] .text-\[\#0C9044\] { color: var(--pos-green-bright) !important; }
    [data-theme="posdark"] .shadow-green-200 { box-shadow: 0 4px 14px rgba(46,160,67,0.12) !important; }

    /* === RED ACCENTS === */
    [data-theme="posdark"] .bg-red-50,
    [data-theme="posdark"] .bg-red-100 {
        background-color: rgba(248,81,73,0.1) !important;
    }
    [data-theme="posdark"] .text-red-300 { color: rgba(248,81,73,0.5) !important; }

    /* === BLUE ACCENTS === */
    [data-theme="posdark"] .bg-blue-100 {
        background-color: rgba(56,139,253,0.12) !important;
    }
    [data-theme="posdark"] .text-blue-500,
    [data-theme="posdark"] .text-blue-600 { color: #58a6ff !important; }

    /* === PURPLE ACCENTS === */
    [data-theme="posdark"] .bg-purple-100 {
        background-color: rgba(163,113,247,0.12) !important;
    }

    /* === ORANGE ACCENTS === */
    [data-theme="posdark"] .text-orange-500 { color: #d29922 !important; }

    /* === HOVER STATES === */
    [data-theme="posdark"] .hover\:bg-slate-50:hover,
    [data-theme="posdark"] .hover\:bg-gray-50:hover {
        background-color: var(--pos-surface-hover) !important;
    }
    [data-theme="posdark"] .hover\:text-slate-700:hover,
    [data-theme="posdark"] .hover\:text-slate-600:hover,
    [data-theme="posdark"] .hover\:text-green-700:hover {
        color: var(--pos-text) !important;
    }
    [data-theme="posdark"] .hover\:bg-red-50:hover {
        background-color: rgba(248,81,73,0.1) !important;
    }
    [data-theme="posdark"] .hover\:bg-green-100:hover {
        background-color: rgba(46,160,67,0.18) !important;
    }
    [data-theme="posdark"] .hover\:border-green-200:hover {
        border-color: rgba(46,160,67,0.35) !important;
    }

    /* === INPUTS & FORM CONTROLS === */
    [data-theme="posdark"] input:not([type="checkbox"]):not([type="radio"]),
    [data-theme="posdark"] select,
    [data-theme="posdark"] textarea {
        background-color: var(--pos-bg) !important;
        border-color: var(--pos-border) !important;
        color: var(--pos-text) !important;
    }
    [data-theme="posdark"] input::placeholder,
    [data-theme="posdark"] textarea::placeholder {
        color: var(--pos-text-muted) !important;
    }
    [data-theme="posdark"] input:focus,
    [data-theme="posdark"] select:focus,
    [data-theme="posdark"] textarea:focus {
        border-color: rgba(46,160,67,0.5) !important;
        box-shadow: 0 0 0 2px rgba(46,160,67,0.12) !important;
    }

    /* === SIDEBAR === */
    [data-theme="posdark"] aside {
        background-color: var(--pos-surface) !important;
        border-color: var(--pos-border) !important;
    }

    /* === PRODUCT CARDS === */
    [data-theme="posdark"] .prod-card {
        background-color: var(--pos-surface) !important;
        border-color: var(--pos-border-muted) !important;
    }
    [data-theme="posdark"] .prod-card:hover {
        border-color: rgba(46,160,67,0.4) !important;
        box-shadow: 0 8px 25px -5px rgba(0,0,0,0.4) !important;
    }
    [data-theme="posdark"] .prod-card .aspect-square {
        background-color: var(--pos-surface-alt) !important;
    }
    [data-theme="posdark"] .bg-white\/70 {
        background-color: rgba(13,17,23,0.75) !important;
    }
    [data-theme="posdark"] .product-card-pos:hover {
        box-shadow: 0 8px 25px -5px rgba(0,0,0,0.4), 0 4px 10px -6px rgba(0,0,0,0.2) !important;
    }

    /* === CART ITEMS === */
    [data-theme="posdark"] .cart-item {
        border-color: var(--pos-border-muted) !important;
    }
    [data-theme="posdark"] .cart-item:hover {
        background-color: var(--pos-surface-hover) !important;
    }

    /* === QTY BUTTONS === */
    [data-theme="posdark"] .qty-btn {
        border-color: var(--pos-border) !important;
        color: var(--pos-text-secondary) !important;
    }
    [data-theme="posdark"] .qty-btn:hover:not(:disabled) {
        background-color: var(--pos-green-soft) !important;
        color: var(--pos-green-bright) !important;
    }

    /* === MODALS === */
    [data-theme="posdark"] .bg-black\/40 {
        background-color: rgba(0,0,0,0.65) !important;
    }
    [data-theme="posdark"] .shadow-2xl {
        box-shadow: 0 25px 60px rgba(0,0,0,0.5) !important;
    }

    /* === CHECKOUT SPECIFIC === */
    [data-theme="posdark"] .step-inactive {
        background-color: var(--pos-border) !important;
        color: var(--pos-text-secondary) !important;
    }
    [data-theme="posdark"] .card-payment {
        background-color: var(--pos-surface) !important;
        border-color: var(--pos-border) !important;
    }
    [data-theme="posdark"] .card-payment:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.3) !important;
    }
    [data-theme="posdark"] .card-payment.selected {
        border-color: var(--pos-green) !important;
        background-color: var(--pos-green-soft) !important;
    }
    [data-theme="posdark"] .confirm-card {
        background: var(--pos-surface) !important;
    }
    [data-theme="posdark"] .confirm-body {
        background: var(--pos-surface) !important;
    }
    [data-theme="posdark"] .confirm-overlay {
        background: rgba(0,0,0,0.6) !important;
    }

    /* === SCROLLBARS === */
    [data-theme="posdark"] .pos-scroll::-webkit-scrollbar-thumb {
        background: var(--pos-border) !important;
    }
    [data-theme="posdark"] .pos-scroll::-webkit-scrollbar-thumb:hover {
        background: var(--pos-text-faint) !important;
    }
    [data-theme="posdark"] ::-webkit-scrollbar { width: 6px; }
    [data-theme="posdark"] ::-webkit-scrollbar-track { background: transparent; }
    [data-theme="posdark"] ::-webkit-scrollbar-thumb {
        background: var(--pos-border);
        border-radius: 999px;
    }

    /* === KEYBOARD BADGES === */
    [data-theme="posdark"] .kbd {
        background-color: var(--pos-surface-hover) !important;
        border-color: var(--pos-border) !important;
        color: var(--pos-text-secondary) !important;
    }

    /* === DaisyUI COMPONENTS === */
    [data-theme="posdark"] .btn-outline {
        border-color: var(--pos-border) !important;
        color: var(--pos-text-secondary) !important;
    }
    [data-theme="posdark"] .btn-outline:hover {
        background-color: var(--pos-surface-hover) !important;
        color: var(--pos-text) !important;
    }
    [data-theme="posdark"] .shadow-sm {
        box-shadow: 0 1px 3px rgba(0,0,0,0.3) !important;
    }
    [data-theme="posdark"] .shadow-lg {
        box-shadow: 0 10px 25px rgba(0,0,0,0.35) !important;
    }

    /* === CATEGORY PILLS === */
    [data-theme="posdark"] .cat-pill {
        border-color: var(--pos-border) !important;
        color: var(--pos-text-secondary) !important;
    }
    [data-theme="posdark"] .cat-pill:hover:not(.cat-active) {
        background-color: var(--pos-surface-hover) !important;
        color: var(--pos-text) !important;
    }

    /* === CHECKOUT PAYMENT BUTTONS === */
    [data-theme="posdark"] .quick-btn.btn-outline {
        border-color: var(--pos-border) !important;
        color: var(--pos-text-secondary) !important;
    }
    [data-theme="posdark"] .border-2 {
        border-color: var(--pos-border) !important;
    }

    /* === CHECKOUT CUSTOMER TYPE BUTTONS === */
    [data-theme="posdark"] button[class*="border-gray-200"]:not(.selected):not([class*="border-green"]) {
        border-color: var(--pos-border) !important;
    }

    /* === TOAST (dynamic elements) === */
    [data-theme="posdark"] .bg-\[\#3A3A3A\] {
        background-color: #484f58 !important;
    }
</style>
