@extends('layouts.app')

@section('title', 'Currency Monitor')

@section('content')
<div class="container-fluid">
    <!-- Modern Page Header -->
    <div class="page-header">
        <h2>💱 Currency Impact Monitor</h2>
        <p>Track exchange rates that affect your import/export costs</p>
        <div class="alert mb-0">
            <i class="bi bi-info-circle"></i> <strong>Why this matters:</strong> 
            Jika USD/IDR naik 5%, biaya import barang dari Amerika bisa naik hingga 5%. Monitor perubahan kurs untuk antisipasi biaya.
            <br><small><i class="bi bi-clock"></i> Rate di-update 1x per hari | <i class="bi bi-globe"></i> 243+ negara tersedia</small>
        </div>
    </div>

    <!-- Quick Stats Cards - Compact -->
    <div class="row mb-3 g-3" id="statsCards" style="display: none;">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-gradient-primary">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Major Currencies</p>
                            <h4 class="mb-0 fw-bold text-primary" id="totalCurrencies">0</h4>
                        </div>
                        <i class="bi bi-cash-stack fs-2 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-gradient-success">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Menguat (vs USD)</p>
                            <h4 class="mb-0 fw-bold text-success" id="risingCount">0</h4>
                        </div>
                        <i class="bi bi-graph-up-arrow fs-2 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-gradient-danger">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Melemah (vs USD)</p>
                            <h4 class="mb-0 fw-bold text-danger" id="fallingCount">0</h4>
                        </div>
                        <i class="bi bi-graph-down-arrow fs-2 text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-gradient-info">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Stabil</p>
                            <h4 class="mb-0 fw-bold text-info" id="stableCount">0</h4>
                        </div>
                        <i class="bi bi-dash-circle fs-2 text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Skeleton for Stats -->
    <div class="row mb-3 g-3" id="statsLoading">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="placeholder-glow">
                        <span class="placeholder col-6"></span>
                        <span class="placeholder col-8"></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="placeholder-glow">
                        <span class="placeholder col-6"></span>
                        <span class="placeholder col-8"></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="placeholder-glow">
                        <span class="placeholder col-6"></span>
                        <span class="placeholder col-8"></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="placeholder-glow">
                        <span class="placeholder col-6"></span>
                        <span class="placeholder col-8"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Section -->
    <div class="filter-section">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control" id="searchCurrency" placeholder="Cari mata uang atau negara...">
                </div>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="filterTrend">
                    <option value="">Semua Trend</option>
                    <option value="up">📈 Menguat</option>
                    <option value="down">📉 Melemah</option>
                    <option value="stable">➡️ Stabil</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="baseCurrency">
                    <option value="USD">Base: USD</option>
                    <option value="IDR">Base: IDR</option>
                    <option value="EUR">Base: EUR</option>
                    <option value="CNY">Base: CNY</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="viewMode">
                    <option value="grid">🎴 Grid View</option>
                    <option value="table">📋 Table View</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" id="btnRefresh">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Last Updated Info -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted" id="lastUpdatedText">
                    <i class="bi bi-clock-history"></i> Loading...
                </small>
                <small class="text-muted">
                    <i class="bi bi-info-circle"></i> Menampilkan <span id="displayCount">0</span> mata uang utama
                </small>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="text-center py-5">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
        <p class="mt-3 text-muted">Loading currency data...</p>
    </div>

    <!-- Grid View -->
    <div id="gridView" class="row" style="display: none;">
        <!-- Cards will be dynamically inserted here -->
    </div>

    <!-- Table View -->
    <div id="tableView" style="display: none;">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Currency</th>
                                <th>Country</th>
                                <th>Rate (USD)</th>
                                <th>7-Day Change</th>
                                <th>Trend</th>
                                <th>Chart</th>
                                <th class="pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <!-- Rows will be dynamically inserted here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- No Results -->
    <div id="noResults" class="alert alert-info text-center" style="display: none;">
        <i class="bi bi-search fs-1"></i>
        <p class="mb-0 mt-2">No currencies found matching your criteria</p>
    </div>
</div>

<!-- Currency Detail Modal -->
<div class="modal fade" id="currencyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title d-flex align-items-center">
                    <img id="modalFlag" src="" alt="" class="rounded me-2" style="width: 40px; height: 30px;">
                    <div>
                        <div id="modalCurrencyCode" class="fw-bold"></div>
                        <small class="text-muted" id="modalCountryName"></small>
                    </div>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Business Impact Alert -->
                <div class="alert alert-light border mb-3">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-info-circle text-primary fs-5 me-2"></i>
                        <div>
                            <strong>Impact Bisnis:</strong>
                            <p class="mb-0 mt-1 small text-muted" id="modalImpactText">
                                Perubahan kurs mempengaruhi biaya import/export Anda secara langsung.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Data Source Info -->
                <div class="alert alert-secondary py-2 px-3 mb-3" style="font-size: 0.85rem;">
                    <i class="bi bi-database"></i> <strong>Sumber Data:</strong> 
                    ExchangeRate API - Rate resmi harian (official daily rate), di-update setiap tengah malam UTC.
                </div>

                <!-- Current Rate -->
                <div class="row mb-4">
                    <div class="col-6">
                        <div class="card bg-light border-0">
                            <div class="card-body text-center">
                                <p class="text-muted mb-1 small">Current Rate</p>
                                <h2 class="mb-0 fw-bold" id="modalCurrentRate">-</h2>
                                <small class="text-muted" id="modalRateLabel">USD</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card border-0" id="modalChangeCard">
                            <div class="card-body text-center">
                                <p class="text-muted mb-1 small">7-Day Change</p>
                                <h2 class="mb-0 fw-bold" id="modalChange">-</h2>
                                <small id="modalTrend">-</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart -->
                <div class="card border-0 bg-light mb-3">
                    <div class="card-body">
                        <h6 class="mb-3">7-Day Exchange Rate Trend</h6>
                        <div style="position: relative; height: 200px;">
                            <canvas id="modalChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Quick Conversion -->
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h6 class="mb-3">Quick Conversion Calculator</h6>
                        <div class="row g-2 align-items-center">
                            <div class="col-5">
                                <label class="form-label small text-muted mb-1">USD</label>
                                <input type="number" class="form-control" id="convertFrom" value="1" placeholder="Amount" min="0" step="0.01">
                            </div>
                            <div class="col-2 text-center">
                                <button class="btn btn-sm btn-outline-secondary w-100 mt-3" onclick="swapConversion()" title="Swap currencies">
                                    <i class="bi bi-arrow-left-right"></i>
                                </button>
                            </div>
                            <div class="col-5">
                                <label class="form-label small text-muted mb-1" id="convertToLabel">IDR</label>
                                <input type="text" class="form-control" id="convertTo" readonly placeholder="Result">
                            </div>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="bi bi-calculator"></i> Contoh: 1 USD = <span id="exampleRate">17.991,28</span> <span id="exampleCurrency">IDR</span>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Modern Light Theme for Currency Monitor */
    :root {
        --primary-blue: #4f46e5;
        --primary-light: #818cf8;
        --success-green: #10b981;
        --warning-orange: #f59e0b;
        --danger-red: #ef4444;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-600: #4b5563;
        --gray-800: #1f2937;
    }
    
    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        border-radius: 24px;
        padding: 2.5rem;
        margin-bottom: 2rem;
        color: white;
        box-shadow: 0 20px 60px rgba(245, 158, 11, 0.3);
        position: relative;
        overflow: hidden;
    }
    
    .page-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
        border-radius: 50%;
    }
    
    .page-header h2 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        position: relative;
        z-index: 1;
        color: white;
    }
    
    .page-header p {
        font-size: 1.1rem;
        opacity: 0.95;
        margin-bottom: 1rem;
        position: relative;
        z-index: 1;
    }
    
    .page-header .alert {
        background: rgba(255, 255, 255, 0.2);
        border: 2px solid rgba(255, 255, 255, 0.3);
        color: white;
        backdrop-filter: blur(10px);
    }
    
    /* Stats Cards with Modern Gradients */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
        color: white !important;
    }
    .bg-gradient-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        color: white !important;
    }
    .bg-gradient-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
        color: white !important;
    }
    .bg-gradient-info {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%) !important;
        color: white !important;
    }
    
    .bg-gradient-primary p,
    .bg-gradient-success p,
    .bg-gradient-danger p,
    .bg-gradient-info p {
        color: rgba(255, 255, 255, 0.9) !important;
    }
    
    .bg-gradient-primary h4,
    .bg-gradient-success h4,
    .bg-gradient-danger h4,
    .bg-gradient-info h4 {
        color: white !important;
    }
    
    /* Currency Cards with Modern Design */
    .currency-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid var(--gray-100) !important;
        border-radius: 16px;
        overflow: hidden;
    }
    
    .currency-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12) !important;
        border-color: var(--warning-orange) !important;
    }
    
    /* Filter Section */
    .filter-section {
        background: white;
        border-radius: 20px;
        padding: 1.75rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        margin-bottom: 2rem;
        border: 1px solid var(--gray-200);
    }
    
    .filter-section .input-group {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        border: 2px solid var(--gray-100);
        transition: all 0.3s ease;
    }
    
    .filter-section .input-group:focus-within {
        border-color: var(--warning-orange);
        box-shadow: 0 4px 20px rgba(245, 158, 11, 0.2);
    }
    
    .filter-section .input-group-text {
        background: white;
        border: none;
        padding: 0.85rem 1.2rem;
        color: var(--gray-600);
    }
    
    .filter-section .form-control,
    .filter-section .form-select {
        border: 2px solid var(--gray-100);
        border-radius: 16px;
        padding: 0.85rem 1.2rem;
        font-size: 1rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .filter-section .form-control:focus,
    .filter-section .form-select:focus {
        border-color: var(--warning-orange);
        box-shadow: 0 4px 20px rgba(245, 158, 11, 0.2);
        outline: none;
    }
    
    /* Refresh Button */
    .btn-primary {
        background: linear-gradient(135deg, var(--warning-orange) 0%, #d97706 100%);
        border: none;
        border-radius: 16px;
        padding: 0.85rem 1.5rem;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(245, 158, 11, 0.5);
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
    }
    
    /* Trend Indicators */
    .trend-up {
        color: var(--success-green);
        font-weight: 700;
    }
    .trend-down {
        color: var(--danger-red);
        font-weight: 700;
    }
    .trend-stable {
        color: var(--gray-600);
        font-weight: 700;
    }
    
    /* Table Styling */
    .table {
        color: var(--gray-800);
    }
    
    .table thead th {
        background: var(--gray-100);
        color: var(--gray-800);
        font-weight: 700;
        border-bottom: 2px solid var(--gray-200);
        padding: 1rem 1.25rem;
    }
    
    .table tbody tr {
        border-bottom: 1px solid var(--gray-100);
        transition: all 0.3s ease;
    }
    
    .table tbody tr:hover {
        background: var(--gray-50);
        transform: scale(1.01);
    }
    
    /* Modal Styling */
    .modal-content {
        border-radius: 24px;
        border: none;
        box-shadow: 0 25px 80px rgba(0, 0, 0, 0.2);
    }
    
    .modal-header {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        border-radius: 24px 24px 0 0;
        padding: 1.75rem 2rem;
        border-bottom: none;
    }
    
    .modal-header .btn-close {
        filter: brightness(0) invert(1);
        opacity: 0.9;
    }
    
    .modal-body {
        padding: 2rem;
        background: var(--gray-50);
    }
    
    /* Sparkline Charts */
    .sparkline {
        height: 40px;
        width: 100%;
    }
    
    /* Loading Spinner */
    .spinner-border {
        border-color: var(--warning-orange);
        border-right-color: transparent;
    }
    
    /* Alerts */
    .alert {
        border-radius: 16px;
        border: 2px solid;
        padding: 1.25rem 1.5rem;
    }
    
    .alert-info {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        border-color: #93c5fd;
        color: #1e40af;
    }
    
    /* Compact spacing */
    .row.g-2 > * {
        padding: 0.5rem;
    }
    
    .row.g-3 > * {
        padding: 0.75rem;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .page-header {
            padding: 1.5rem;
        }
        
        .page-header h2 {
            font-size: 1.75rem;
        }
        
        .filter-section {
            padding: 1.25rem;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
let allCurrencies = [];
let currentCurrency = null;
let modalChart = null;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadCurrencies();
    setupEventListeners();
});

function setupEventListeners() {
    // Search
    document.getElementById('searchCurrency').addEventListener('input', filterCurrencies);
    
    // Filters
    document.getElementById('filterTrend').addEventListener('change', filterCurrencies);
    
    // Base currency change
    document.getElementById('baseCurrency').addEventListener('change', function() {
        loadCurrencies(this.value);
    });
    
    // View mode toggle
    document.getElementById('viewMode').addEventListener('change', function() {
        toggleViewMode(this.value);
    });
    
    // Refresh button
    document.getElementById('btnRefresh').addEventListener('click', function() {
        const base = document.getElementById('baseCurrency').value;
        // Force refresh with cache clear
        loadCurrencies(base, true);
    });
    
    // Conversion calculator
    document.getElementById('convertFrom').addEventListener('input', updateConversion);
}

async function loadCurrencies(base = 'USD', forceRefresh = false) {
    const loadingState = document.getElementById('loadingState');
    const gridView = document.getElementById('gridView');
    const tableView = document.getElementById('tableView');
    const btnRefresh = document.getElementById('btnRefresh');
    const statsCards = document.getElementById('statsCards');
    const statsLoading = document.getElementById('statsLoading');
    
    // Show loading
    loadingState.style.display = 'block';
    gridView.style.display = 'none';
    tableView.style.display = 'none';
    statsCards.style.display = 'none';
    statsLoading.style.display = 'flex';
    btnRefresh.disabled = true;
    btnRefresh.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Loading...';
    
    try {
        const url = forceRefresh ? `/api/currency?base=${base}&refresh=true` : `/api/currency?base=${base}`;
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.success) {
            allCurrencies = data.data;
            displayCurrencies(allCurrencies);
            updateStats(allCurrencies);
            
            // Show data source indicator
            if (data.data_source === 'real-time') {
                console.log('✓ Using REAL-TIME data from ExchangeRate API');
            }
            
            // Show last updated time
            if (data.last_updated) {
                const updateTime = new Date(data.last_updated);
                const formattedTime = updateTime.toLocaleString('id-ID', {
                    dateStyle: 'medium',
                    timeStyle: 'short'
                });
                document.getElementById('lastUpdatedText').innerHTML = 
                    `<i class="bi bi-clock-history"></i> Terakhir update: ${formattedTime}`;
                document.getElementById('displayCount').textContent = data.total;
            }
            
            // Show view
            loadingState.style.display = 'none';
            const viewMode = document.getElementById('viewMode').value;
            toggleViewMode(viewMode);
        } else {
            throw new Error(data.message || 'Failed to load currencies');
        }
    } catch (error) {
        console.error('Error loading currencies:', error);
        loadingState.innerHTML = `
            <div class="alert alert-danger mx-auto" style="max-width: 600px;">
                <div class="text-center">
                    <i class="bi bi-exclamation-triangle fs-1"></i>
                    <h5 class="mt-3">Gagal Memuat Data Currency</h5>
                    <p class="text-muted">${error.message}</p>
                    <button class="btn btn-primary mt-2" onclick="loadCurrencies('USD', true)">
                        <i class="bi bi-arrow-clockwise"></i> Coba Lagi
                    </button>
                </div>
            </div>
        `;
    } finally {
        btnRefresh.disabled = false;
        btnRefresh.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Refresh';
    }
}

function displayCurrencies(currencies) {
    displayGridView(currencies);
    displayTableView(currencies);
}

function displayGridView(currencies) {
    const gridView = document.getElementById('gridView');
    gridView.innerHTML = '';
    
    if (currencies.length === 0) {
        document.getElementById('noResults').style.display = 'block';
        return;
    }
    
    document.getElementById('noResults').style.display = 'none';
    
    currencies.forEach(currency => {
        const trendIcon = getTrendIcon(currency.trend);
        const trendClass = getTrendClass(currency.trend);
        const changeColor = currency.change_7d > 0 ? 'success' : (currency.change_7d < 0 ? 'danger' : 'secondary');
        
        const card = `
            <div class="col-lg-3 col-md-4 col-sm-6 mb-3 currency-item" 
                 data-trend="${currency.trend}"
                 data-search="${currency.currency_code.toLowerCase()} ${currency.country_name.toLowerCase()}">
                <div class="card currency-card shadow-sm h-100">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center mb-2">
                            <img src="${currency.flag_url}" alt="${currency.country_name}" 
                                 class="rounded me-2" style="width: 40px; height: 28px; object-fit: cover;">
                            <div class="flex-grow-1">
                                <h6 class="mb-0 fw-bold">${currency.currency_code}</h6>
                                <small class="text-muted" style="font-size: 0.75rem;">${currency.country_name}</small>
                            </div>
                        </div>
                        
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">Rate</span>
                                <h6 class="mb-0 fw-bold" style="font-size: ${currency.rate >= 10000 ? '0.9rem' : '1.1rem'};">${formatRate(currency.rate)}</h6>
                            </div>
                            <div class="progress mt-1" style="height: 3px;">
                                <div class="progress-bar bg-${changeColor}" role="progressbar" 
                                     style="width: ${Math.min(Math.abs(currency.change_7d) * 10, 100)}%"></div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="${trendClass}" style="font-size: 0.85rem;" title="Perubahan 7 hari terakhir">
                                ${trendIcon} ${Math.abs(currency.change_7d).toFixed(2)}%
                            </span>
                            <button class="btn btn-sm btn-outline-primary" onclick='showCurrencyDetail(${JSON.stringify(currency)})'>
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        gridView.innerHTML += card;
    });
}

function displayTableView(currencies) {
    const tableBody = document.getElementById('tableBody');
    tableBody.innerHTML = '';
    
    currencies.forEach(currency => {
        const trendIcon = getTrendIcon(currency.trend);
        const trendClass = getTrendClass(currency.trend);
        const changeClass = currency.change_7d > 0 ? 'text-success' : (currency.change_7d < 0 ? 'text-danger' : 'text-secondary');
        
        const row = `
            <tr>
                <td class="ps-4">
                    <div class="d-flex align-items-center">
                        <img src="${currency.flag_url}" alt="${currency.country_name}" 
                             class="rounded me-2" style="width: 30px; height: 20px;">
                        <div>
                            <div class="fw-bold">${currency.currency_code}</div>
                            <small class="text-muted">${currency.currency_name}</small>
                        </div>
                    </div>
                </td>
                <td>${currency.country_name}</td>
                <td class="fw-bold">${formatRate(currency.rate)}</td>
                <td>
                    <span class="${changeClass} fw-bold">
                        ${currency.change_7d > 0 ? '+' : ''}${currency.change_7d.toFixed(2)}%
                    </span>
                </td>
                <td>
                    <span class="${trendClass}">${trendIcon}</span>
                </td>
                <td>
                    <canvas class="sparkline" data-currency="${currency.currency_code}"></canvas>
                </td>
                <td class="pe-4">
                    <button class="btn btn-sm btn-outline-primary" onclick='showCurrencyDetail(${JSON.stringify(currency)})'>
                        <i class="bi bi-eye"></i> View
                    </button>
                </td>
            </tr>
        `;
        
        tableBody.innerHTML += row;
    });
    
    // Render sparklines
    setTimeout(() => renderSparklines(currencies), 100);
}

function renderSparklines(currencies) {
    currencies.forEach(currency => {
        const canvas = document.querySelector(`canvas[data-currency="${currency.currency_code}"]`);
        if (canvas) {
            const ctx = canvas.getContext('2d');
            const history = Object.values(currency.history);
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: Object.keys(currency.history),
                    datasets: [{
                        data: history,
                        borderColor: currency.change_7d > 0 ? '#28a745' : (currency.change_7d < 0 ? '#dc3545' : '#6c757d'),
                        borderWidth: 2,
                        fill: false,
                        pointRadius: 0,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false }
                    },
                    scales: {
                        x: { display: false },
                        y: { display: false }
                    }
                }
            });
        }
    });
}

function updateStats(currencies) {
    document.getElementById('totalCurrencies').textContent = currencies.length;
    
    const rising = currencies.filter(c => c.trend === 'up').length;
    const falling = currencies.filter(c => c.trend === 'down').length;
    const stable = currencies.filter(c => c.trend === 'stable').length;
    
    document.getElementById('risingCount').textContent = rising;
    document.getElementById('fallingCount').textContent = falling;
    document.getElementById('stableCount').textContent = stable;
    
    // Show stats cards and hide loading skeleton
    document.getElementById('statsLoading').style.display = 'none';
    document.getElementById('statsCards').style.display = 'flex';
}

function filterCurrencies() {
    const searchTerm = document.getElementById('searchCurrency').value.toLowerCase();
    const trend = document.getElementById('filterTrend').value;
    
    const filtered = allCurrencies.filter(currency => {
        const matchSearch = !searchTerm || 
                          currency.currency_code.toLowerCase().includes(searchTerm) ||
                          currency.country_name.toLowerCase().includes(searchTerm);
        
        const matchTrend = !trend || currency.trend === trend;
        
        return matchSearch && matchTrend;
    });
    
    displayCurrencies(filtered);
    updateStats(filtered);
}

function toggleViewMode(mode) {
    const gridView = document.getElementById('gridView');
    const tableView = document.getElementById('tableView');
    
    if (mode === 'grid') {
        gridView.style.display = 'flex';
        tableView.style.display = 'none';
    } else {
        gridView.style.display = 'none';
        tableView.style.display = 'block';
    }
}

function showCurrencyDetail(currency) {
    currentCurrency = currency;
    
    // Set modal data
    document.getElementById('modalFlag').src = currency.flag_url;
    document.getElementById('modalCurrencyCode').textContent = currency.currency_code;
    document.getElementById('modalCountryName').textContent = currency.country_name;
    document.getElementById('modalCurrentRate').textContent = formatRate(currency.rate);
    document.getElementById('modalRateLabel').textContent = `per USD`;
    
    // Update conversion labels
    document.getElementById('convertToLabel').textContent = currency.currency_code;
    document.getElementById('exampleCurrency').textContent = currency.currency_code;
    
    // Format example rate with thousand separator
    let exampleRateFormatted;
    if (currency.rate >= 1000) {
        exampleRateFormatted = currency.rate.toLocaleString('id-ID', { 
            minimumFractionDigits: 2, 
            maximumFractionDigits: 2 
        });
    } else {
        exampleRateFormatted = currency.rate.toFixed(4);
    }
    document.getElementById('exampleRate').textContent = exampleRateFormatted;
    
    // Business impact message
    const impactText = document.getElementById('modalImpactText');
    const change = Math.abs(currency.change_7d);
    if (currency.change_7d > 1) {
        impactText.innerHTML = `<span class="text-danger">⚠️ ${currency.currency_code} menguat ${change.toFixed(2)}% - </span>Import dari ${currency.country_name} menjadi lebih mahal. Pertimbangkan hedging atau tunda pembelian.`;
    } else if (currency.change_7d < -1) {
        impactText.innerHTML = `<span class="text-success">✓ ${currency.currency_code} melemah ${change.toFixed(2)}% - </span>Import dari ${currency.country_name} menjadi lebih murah. Kesempatan baik untuk pembelian.`;
    } else {
        impactText.innerHTML = `${currency.currency_code} relatif stabil (${change.toFixed(2)}%). Biaya import/export tidak terpengaruh signifikan.`;
    }
    
    const changeClass = currency.change_7d > 0 ? 'text-success' : (currency.change_7d < 0 ? 'text-danger' : 'text-secondary');
    document.getElementById('modalChange').textContent = `${currency.change_7d > 0 ? '+' : ''}${currency.change_7d.toFixed(2)}%`;
    document.getElementById('modalChange').className = `mb-0 fw-bold ${changeClass}`;
    document.getElementById('modalTrend').textContent = getTrendIcon(currency.trend) + ' ' + currency.trend.toUpperCase();
    
    const cardClass = currency.change_7d > 0 ? 'bg-success-subtle' : (currency.change_7d < 0 ? 'bg-danger-subtle' : 'bg-light');
    document.getElementById('modalChangeCard').className = `card border-0 ${cardClass}`;
    
    // Render chart
    renderModalChart(currency);
    
    // Update conversion
    updateConversion();
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('currencyModal'));
    modal.show();
}

function renderModalChart(currency) {
    const ctx = document.getElementById('modalChart').getContext('2d');
    
    if (modalChart) {
        modalChart.destroy();
    }
    
    const history = Object.values(currency.history);
    const labels = Object.keys(currency.history);
    
    modalChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: `${currency.currency_code} Rate`,
                data: history,
                borderColor: currency.change_7d > 0 ? '#28a745' : (currency.change_7d < 0 ? '#dc3545' : '#6c757d'),
                backgroundColor: currency.change_7d > 0 ? 'rgba(40, 167, 69, 0.1)' : 'rgba(220, 53, 69, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 3,
                pointHoverRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            return `Rate: ${formatRate(context.parsed.y)}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    ticks: {
                        callback: function(value) {
                            return formatRate(value);
                        }
                    }
                }
            }
        }
    });
}

function updateConversion() {
    if (!currentCurrency) return;
    
    const fromValue = parseFloat(document.getElementById('convertFrom').value) || 0;
    const toValue = fromValue * currentCurrency.rate;
    
    // Format with thousand separator for better readability
    let formattedValue;
    if (toValue >= 1000) {
        // Use Indonesian format: 17.991,28
        formattedValue = toValue.toLocaleString('id-ID', { 
            minimumFractionDigits: 2, 
            maximumFractionDigits: 2 
        });
    } else {
        formattedValue = toValue.toFixed(4);
    }
    
    document.getElementById('convertTo').value = formattedValue;
}

function swapConversion() {
    if (!currentCurrency) return;
    
    const fromValue = parseFloat(document.getElementById('convertFrom').value) || 0;
    const toValue = fromValue / currentCurrency.rate;
    
    // Format both values with thousand separator
    let formattedFrom, formattedTo;
    
    if (toValue >= 1000) {
        formattedFrom = toValue.toLocaleString('id-ID', { 
            minimumFractionDigits: 2, 
            maximumFractionDigits: 2 
        });
    } else {
        formattedFrom = toValue.toFixed(4);
    }
    
    if (fromValue >= 1000) {
        formattedTo = fromValue.toLocaleString('id-ID', { 
            minimumFractionDigits: 2, 
            maximumFractionDigits: 2 
        });
    } else {
        formattedTo = fromValue.toFixed(4);
    }
    
    document.getElementById('convertFrom').value = formattedFrom;
    document.getElementById('convertTo').value = formattedTo;
}

// Helper functions
function formatRate(rate) {
    if (rate >= 10000) {
        // For large rates like IDR, VND - use thousand separator
        return rate.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    } else if (rate >= 1000) {
        return rate.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    } else if (rate >= 1) {
        return rate.toFixed(4);
    } else {
        return rate.toFixed(6);
    }
}

function getTrendIcon(trend) {
    return trend === 'up' ? '📈' : (trend === 'down' ? '📉' : '➡️');
}

function getTrendClass(trend) {
    return trend === 'up' ? 'trend-up' : (trend === 'down' ? 'trend-down' : 'trend-stable');
}
</script>
@endpush
