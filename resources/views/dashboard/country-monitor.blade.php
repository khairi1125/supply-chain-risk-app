@extends('layouts.app')

@section('title', 'Country Monitor - Supply Chain Risk Intelligence')

@push('styles')
<style>
    /* Modern Light Theme for Country Monitor */
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
    
    /* Page Header Styling */
    .page-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 24px;
        padding: 2.5rem;
        margin-bottom: 2rem;
        color: white;
        box-shadow: 0 20px 60px rgba(102, 126, 234, 0.3);
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
        margin: 0;
        position: relative;
        z-index: 1;
    }
    
    /* Search & Filter Section */
    .search-filter-section {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        margin-bottom: 2rem;
        border: 1px solid var(--gray-200);
    }
    
    .search-filter-section .input-group {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        border: 2px solid var(--gray-100);
        transition: all 0.3s ease;
    }
    
    .search-filter-section .input-group:focus-within {
        border-color: var(--primary-blue);
        box-shadow: 0 4px 20px rgba(79, 70, 229, 0.2);
    }
    
    .search-filter-section .input-group-text {
        background: white;
        border: none;
        padding: 0.85rem 1.2rem;
        color: var(--gray-600);
    }
    
    .search-filter-section .form-control {
        border: none;
        padding: 0.85rem 1.2rem;
        font-size: 1rem;
    }
    
    .search-filter-section .form-control:focus {
        box-shadow: none;
    }
    
    .search-filter-section .form-select {
        border-radius: 16px;
        border: 2px solid var(--gray-100);
        padding: 0.85rem 1.2rem;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: white;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .search-filter-section .form-select:focus {
        border-color: var(--primary-blue);
        box-shadow: 0 4px 20px rgba(79, 70, 229, 0.2);
        outline: none;
    }
    
    /* Modern Country Cards */
    .country-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .country-card .card {
        border-radius: 20px;
        border: 2px solid var(--gray-100);
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: white;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        height: 100%;
    }
    
    .country-card .card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 20px 40px rgba(79, 70, 229, 0.15);
        border-color: var(--primary-blue);
    }
    
    .country-card .card-body {
        padding: 1.75rem;
    }
    
    /* Flag and Country Name Header */
    .country-header {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1.25rem;
        border-bottom: 2px solid var(--gray-100);
    }
    
    .country-flag {
        width: 50px;
        height: 38px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        margin-right: 1rem;
        border: 2px solid white;
    }
    
    .country-name h6 {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--gray-800);
        margin-bottom: 0.25rem;
    }
    
    .country-code {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        background: var(--gray-100);
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--gray-600);
        letter-spacing: 0.5px;
    }
    
    /* Info Items in Cards */
    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--gray-100);
    }
    
    .info-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    
    .info-label {
        color: var(--gray-600);
        font-size: 0.9rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .info-value {
        font-weight: 700;
        color: var(--gray-800);
        font-size: 1rem;
    }
    
    /* Modern Badges */
    .badge {
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.85rem;
        letter-spacing: 0.3px;
    }
    
    .badge.bg-secondary {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%) !important;
    }
    
    .badge.bg-info {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%) !important;
    }
    
    .badge.bg-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
    }
    
    .badge.bg-warning {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
    }
    
    .badge.bg-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
    }
    
    /* Risk Badge Specific */
    .risk-badge {
        width: 100%;
        display: block;
        text-align: center;
        padding: 0.75rem;
        font-size: 0.9rem;
        margin-bottom: 1rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    /* View Detail Button */
    .view-detail-btn {
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-light) 100%);
        border: none;
        border-radius: 12px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        font-size: 0.95rem;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
        width: 100%;
        letter-spacing: 0.3px;
    }
    
    .view-detail-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(79, 70, 229, 0.5);
        background: linear-gradient(135deg, #4338ca 0%, #6366f1 100%);
    }
    
    .view-detail-btn i {
        margin-right: 0.5rem;
    }
    
    /* No Results Alert */
    #noResults {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        border: 2px solid #93c5fd;
        border-radius: 16px;
        color: #1e40af;
        font-weight: 600;
        padding: 2rem;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.2);
    }
    
    /* Modal Styling */
    .modal-content {
        border-radius: 24px;
        border: none;
        box-shadow: 0 25px 80px rgba(0, 0, 0, 0.2);
    }
    
    .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 24px 24px 0 0;
        padding: 1.75rem 2rem;
        border-bottom: none;
    }
    
    .modal-header h5 {
        font-weight: 700;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
    }
    
    .modal-header .btn-close {
        filter: brightness(0) invert(1);
        opacity: 0.9;
    }
    
    .modal-body {
        padding: 2rem;
        background: var(--gray-50);
    }
    
    /* Info Cards in Modal */
    .modal-body .card {
        border-radius: 16px;
        border: 2px solid var(--gray-200);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    
    .modal-body .card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
    
    .modal-body .card.bg-primary,
    .modal-body .card.bg-success,
    .modal-body .card.bg-warning,
    .modal-body .card.bg-info {
        border: none;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    
    .modal-body .card-header {
        background: white;
        border-bottom: 2px solid var(--gray-200);
        border-radius: 16px 16px 0 0 !important;
        padding: 1.25rem 1.5rem;
    }
    
    .modal-body .card-header h5,
    .modal-body .card-header h6 {
        color: var(--gray-800);
        font-weight: 700;
        margin: 0;
    }
    
    /* Sentiment Cards */
    .bg-success-subtle {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%) !important;
        border: 2px solid #6ee7b7;
    }
    
    .bg-secondary-subtle {
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%) !important;
        border: 2px solid #d1d5db;
    }
    
    .bg-danger-subtle {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%) !important;
        border: 2px solid #fca5a5;
    }
    
    .bg-info-subtle {
        background: linear-gradient(135deg, #cffafe 0%, #a5f3fc 100%) !important;
        border: 2px solid #67e8f9;
    }
    
    /* Loading Spinner */
    .spinner-border {
        border-color: var(--primary-light);
        border-right-color: transparent;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .page-header {
            padding: 1.5rem;
        }
        
        .page-header h2 {
            font-size: 1.75rem;
        }
        
        .search-filter-section {
            padding: 1.25rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="page-header">
        <h2>🌍 Global Country Monitor</h2>
        <p>Monitor real-time risk scores for 250 countries worldwide</p>
    </div>

    <!-- Search & Filter Section -->
    <div class="search-filter-section">
        <div class="row g-3">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" 
                           class="form-control" 
                           id="searchCountry" 
                           placeholder="Search country name...">
                </div>
            </div>
            <div class="col-md-4">
                <select class="form-select" id="filterRegion">
                    <option value="All">🌐 All Regions</option>
                    @foreach($regions as $region)
                        <option value="{{ $region }}">{{ $region }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Countries Grid -->
    <div class="row g-4" id="countriesGrid">
        @foreach($countries as $country)
        <div class="col-xl-3 col-lg-4 col-md-6 country-card" 
             data-name="{{ strtolower($country->name) }}" 
             data-region="{{ $country->region }}">
            <div class="card">
                <div class="card-body">
                    <!-- Country Header -->
                    <div class="country-header">
                        <img src="{{ $country->flag_url }}" 
                             alt="{{ $country->name }}" 
                             class="country-flag">
                        <div class="country-name">
                            <h6>{{ $country->name }}</h6>
                            <span class="country-code">{{ $country->code }}</span>
                        </div>
                    </div>
                    
                    <!-- Country Info -->
                    <div class="mb-3">
                        <div class="info-item">
                            <span class="info-label">
                                <i class="bi bi-geo-alt-fill"></i> Region
                            </span>
                            <span class="badge bg-secondary">{{ $country->region }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">
                                <i class="bi bi-currency-exchange"></i> Currency
                            </span>
                            <span class="info-value">{{ $country->currency_code ?? 'N/A' }}</span>
                        </div>
                    </div>
                    
                    <!-- Risk Badge -->
                    <div class="mb-3">
                        <span class="badge bg-info risk-badge" data-code="{{ $country->code }}">
                            <i class="bi bi-hourglass-split"></i> Loading...
                        </span>
                    </div>
                    
                    <!-- View Detail Button -->
                    <button class="btn view-detail-btn" 
                            data-code="{{ $country->code }}">
                        <i class="bi bi-eye-fill"></i> View Details
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- No Results Message -->
    <div id="noResults" class="alert text-center mt-4" style="display: none;">
        <i class="bi bi-search" style="font-size: 2rem;"></i>
        <p class="mb-0 mt-2">No countries found matching your search criteria.</p>
    </div>
</div>

<!-- Country Detail Modal -->
<div class="modal fade" id="countryDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <img id="modalFlag" src="" alt="" style="width: 40px; height: 30px; margin-right: 10px;">
                    <span id="modalCountryName"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Loading State -->
                <div id="modalLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Loading country data...</p>
                </div>

                <!-- Content -->
                <div id="modalContent" style="display: none;">
                    <!-- ROW 1: Info Umum -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6 class="card-title"><i class="bi bi-people"></i> Population</h6>
                                    <h4 id="infoPopulation">-</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h6 class="card-title"><i class="bi bi-cash-stack"></i> GDP (Latest)</h6>
                                    <h4 id="infoGDP">-</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h6 class="card-title"><i class="bi bi-graph-up-arrow"></i> Inflation</h6>
                                    <h4 id="infoInflation">-</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title"><i class="bi bi-currency-exchange"></i> Currency</h6>
                                    <h4 id="infoCurrency">-</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ROW 2: Weather & Risk Score -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="bi bi-cloud-sun"></i> Current Weather</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-6">
                                            <p><strong>Temperature:</strong> <span id="weatherTemp">-</span>°C</p>
                                            <p><strong>Rainfall:</strong> <span id="weatherRain">-</span> mm</p>
                                        </div>
                                        <div class="col-6">
                                            <p><strong>Wind Speed:</strong> <span id="weatherWind">-</span> km/h</p>
                                            <p><strong>Condition:</strong> <span id="weatherCondition">-</span></p>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <span class="badge" id="weatherRiskBadge">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><i class="bi bi-shield-exclamation"></i> Risk Score</h5>
                                </div>
                                <div class="card-body text-center">
                                    <h1 class="display-1 mb-0" id="riskScoreDisplay">-</h1>
                                    <span class="badge" id="riskLevelBadge">-</span>
                                    <hr>
                                    <div class="row small">
                                        <div class="col-6 mb-2">
                                            <strong>Weather Risk:</strong> <span id="riskWeather">-</span>%
                                        </div>
                                        <div class="col-6 mb-2">
                                            <strong>Inflation Risk:</strong> <span id="riskInflation">-</span>%
                                        </div>
                                        <div class="col-6">
                                            <strong>Currency Risk:</strong> <span id="riskCurrency">-</span>%
                                        </div>
                                        <div class="col-6">
                                            <strong>News Risk:</strong> <span id="riskNews">-</span>%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ROW 3: Charts -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">GDP Trend (5 Years)</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="gdpChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0">Inflation Trend (5 Years)</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="inflationChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ROW 3.5: News Intelligence -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">📰 Recent News & Sentiment Analysis</h6>
                                    <div id="sentimentBadge"></div>
                                </div>
                                <div class="card-body">
                                    <!-- Sentiment Summary -->
                                    <div class="row mb-3">
                                        <div class="col-md-3">
                                            <div class="text-center p-2 bg-success-subtle rounded">
                                                <div class="fs-4 fw-bold text-success" id="sentimentPositive">0</div>
                                                <small class="text-muted">👍 Positive</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center p-2 bg-secondary-subtle rounded">
                                                <div class="fs-4 fw-bold text-secondary" id="sentimentNeutral">0</div>
                                                <small class="text-muted">➖ Neutral</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center p-2 bg-danger-subtle rounded">
                                                <div class="fs-4 fw-bold text-danger" id="sentimentNegative">0</div>
                                                <small class="text-muted">👎 Negative</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center p-2 bg-info-subtle rounded">
                                                <div class="fs-4 fw-bold text-info" id="sentimentTotal">0</div>
                                                <small class="text-muted">📊 Total Articles</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- News Articles List -->
                                    <div id="newsArticlesList">
                                        <div class="text-center text-muted py-4">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <p class="mt-2">Loading news...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ROW 4: Actions -->
                    <div class="row">
                        <div class="col-12">
                            <button class="btn btn-success me-2">
                                <i class="bi bi-star"></i> Add to Watchlist
                            </button>
                            <button class="btn btn-secondary">
                                <i class="bi bi-arrow-left-right"></i> Compare with Another Country
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
let gdpChartInstance = null;
let inflationChartInstance = null;

// Load risk scores for all countries
document.addEventListener('DOMContentLoaded', function() {
    loadAllRiskScores();
});

// Search functionality
document.getElementById('searchCountry').addEventListener('input', filterCountries);
document.getElementById('filterRegion').addEventListener('change', filterCountries);

function filterCountries() {
    const searchTerm = document.getElementById('searchCountry').value.toLowerCase();
    const selectedRegion = document.getElementById('filterRegion').value;
    const cards = document.querySelectorAll('.country-card');
    let visibleCount = 0;

    cards.forEach(card => {
        const name = card.getAttribute('data-name');
        const region = card.getAttribute('data-region');
        
        const matchesSearch = name.includes(searchTerm);
        const matchesRegion = selectedRegion === 'All' || region === selectedRegion;
        
        if (matchesSearch && matchesRegion) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    document.getElementById('noResults').style.display = visibleCount === 0 ? 'block' : 'none';
}

// Load risk scores
async function loadAllRiskScores() {
    const badges = document.querySelectorAll('.risk-badge');
    
    for (const badge of badges) {
        const code = badge.getAttribute('data-code');
        try {
            const response = await fetch(`/api/risk/${code}`);
            const data = await response.json();
            
            if (data.total_score !== undefined) {
                badge.textContent = `Risk: ${data.total_score}`;
                badge.className = `badge bg-${getRiskColorClass(data.risk_level)} risk-badge`;
            }
        } catch (error) {
            badge.textContent = 'N/A';
            badge.className = 'badge bg-secondary risk-badge';
        }
    }
}

// View Detail button click
document.querySelectorAll('.view-detail-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const code = this.getAttribute('data-code');
        showCountryDetail(code);
    });
});

// Show country detail modal
async function showCountryDetail(code) {
    const modal = new bootstrap.Modal(document.getElementById('countryDetailModal'));
    modal.show();
    
    // Show loading
    document.getElementById('modalLoading').style.display = 'block';
    document.getElementById('modalContent').style.display = 'none';
    
    try {
        const response = await fetch(`/api/countries/${code}`);
        const data = await response.json();
        
        // Populate modal
        document.getElementById('modalFlag').src = data.country.flag_url;
        document.getElementById('modalCountryName').textContent = data.country.name;
        
        // Info cards
        document.getElementById('infoPopulation').textContent = formatNumber(data.economic.population);
        document.getElementById('infoGDP').textContent = data.economic.latest_gdp ? '$' + formatNumber(data.economic.latest_gdp) : 'N/A';
        document.getElementById('infoInflation').textContent = data.economic.latest_inflation ? data.economic.latest_inflation + '%' : 'N/A';
        document.getElementById('infoCurrency').textContent = `${data.country.currency_code} (${data.country.currency_name})`;
        
        // Weather
        document.getElementById('weatherTemp').textContent = data.weather.temperature;
        document.getElementById('weatherRain').textContent = data.weather.rainfall;
        document.getElementById('weatherWind').textContent = data.weather.wind_speed;
        document.getElementById('weatherCondition').textContent = data.weather.weather_condition;
        
        const weatherBadge = document.getElementById('weatherRiskBadge');
        weatherBadge.textContent = `Weather Risk: ${data.weather.risk_level.toUpperCase()}`;
        weatherBadge.className = `badge bg-${getRiskColorClass(data.weather.risk_level)}`;
        
        // Risk Score
        document.getElementById('riskScoreDisplay').textContent = data.risk.total_score;
        document.getElementById('riskScoreDisplay').style.color = getRiskColorHex(data.risk.risk_level);
        
        const riskBadge = document.getElementById('riskLevelBadge');
        riskBadge.textContent = data.risk.risk_level.toUpperCase();
        riskBadge.className = `badge bg-${getRiskColorClass(data.risk.risk_level)} fs-5`;
        
        document.getElementById('riskWeather').textContent = data.risk.weather_score;
        document.getElementById('riskInflation').textContent = data.risk.inflation_score;
        document.getElementById('riskCurrency').textContent = data.risk.currency_score;
        document.getElementById('riskNews').textContent = data.risk.news_score;
        
        // Charts
        renderGDPChart(data.economic.gdp);
        renderInflationChart(data.economic.inflation);
        
        // News & Sentiment
        renderNews(data.news);
        
        // Hide loading, show content
        document.getElementById('modalLoading').style.display = 'none';
        document.getElementById('modalContent').style.display = 'block';
        
    } catch (error) {
        console.error('Error loading country detail:', error);
        alert('Failed to load country details. Please try again.');
        modal.hide();
    }
}

// Render GDP Chart
function renderGDPChart(data) {
    const ctx = document.getElementById('gdpChart').getContext('2d');
    
    if (gdpChartInstance) {
        gdpChartInstance.destroy();
    }
    
    gdpChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => d.year),
            datasets: [{
                label: 'GDP (USD)',
                data: data.map(d => d.value),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    ticks: {
                        callback: function(value) {
                            return '$' + (value / 1e9).toFixed(1) + 'B';
                        }
                    }
                }
            }
        }
    });
}

// Render Inflation Chart
function renderInflationChart(data) {
    const ctx = document.getElementById('inflationChart').getContext('2d');
    
    if (inflationChartInstance) {
        inflationChartInstance.destroy();
    }
    
    inflationChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => d.year),
            datasets: [{
                label: 'Inflation (%)',
                data: data.map(d => d.value),
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
}

// Helper functions
function getRiskColorClass(level) {
    const levels = {
        'low': 'success',
        'medium': 'info',
        'high': 'warning',
        'critical': 'danger'
    };
    return levels[level.toLowerCase()] || 'secondary';
}

function getRiskColorHex(level) {
    const colors = {
        'low': '#198754',
        'medium': '#0dcaf0',
        'high': '#ffc107',
        'critical': '#dc3545'
    };
    return colors[level.toLowerCase()] || '#6c757d';
}

function formatNumber(num) {
    if (!num) return 'N/A';
    if (num >= 1e9) return (num / 1e9).toFixed(2) + 'B';
    if (num >= 1e6) return (num / 1e6).toFixed(2) + 'M';
    if (num >= 1e3) return (num / 1e3).toFixed(2) + 'K';
    return num.toLocaleString();
}

// Render News & Sentiment
function renderNews(newsData) {
    const sentiment = newsData.sentiment;
    const articles = newsData.articles || [];
    
    // Update sentiment summary
    document.getElementById('sentimentPositive').textContent = sentiment.positive || 0;
    document.getElementById('sentimentNeutral').textContent = sentiment.neutral || 0;
    document.getElementById('sentimentNegative').textContent = sentiment.negative || 0;
    document.getElementById('sentimentTotal').textContent = sentiment.total || 0;
    
    // Update overall sentiment badge
    const badgeElement = document.getElementById('sentimentBadge');
    const overallSentiment = sentiment.overall_sentiment || 'neutral';
    const badgeColor = overallSentiment === 'positive' ? 'success' : (overallSentiment === 'negative' ? 'danger' : 'secondary');
    const sentimentIcon = overallSentiment === 'positive' ? '👍' : (overallSentiment === 'negative' ? '👎' : '➖');
    badgeElement.innerHTML = `<span class="badge bg-${badgeColor}">${sentimentIcon} Overall: ${overallSentiment.toUpperCase()}</span>`;
    
    // Render articles list
    const articlesList = document.getElementById('newsArticlesList');
    
    if (articles.length === 0) {
        articlesList.innerHTML = '<div class="text-center text-muted py-4"><p>No recent news articles found for this country.</p></div>';
        return;
    }
    
    let html = '<div class="list-group">';
    articles.forEach((article, index) => {
        const sentimentIcon = article.sentiment === 'positive' ? '👍' : (article.sentiment === 'negative' ? '👎' : '➖');
        const sentimentColor = article.sentiment === 'positive' ? 'success' : (article.sentiment === 'negative' ? 'danger' : 'secondary');
        const publishedDate = new Date(article.published_at).toLocaleDateString();
        
        html += `
            <a href="${article.url}" target="_blank" class="list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <h6 class="mb-1">${article.title}</h6>
                        <p class="mb-1 text-muted small">${article.description || 'No description available'}</p>
                        <small class="text-muted">
                            <i class="bi bi-newspaper"></i> ${article.source} • 
                            <i class="bi bi-calendar"></i> ${publishedDate}
                        </small>
                    </div>
                    <span class="badge bg-${sentimentColor} ms-2">${sentimentIcon} ${article.sentiment}</span>
                </div>
            </a>
        `;
    });
    html += '</div>';
    
    articlesList.innerHTML = html;
}
</script>
@endpush
