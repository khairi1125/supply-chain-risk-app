@extends('layouts.app')

@section('title', 'Country Monitor - Supply Chain Risk Intelligence')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0">🌍 Global Country Monitor</h2>
            <p class="text-muted">Monitor real-time risk scores for 250 countries worldwide</p>
        </div>
    </div>

    <!-- Search & Filter Section -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" id="searchCountry" placeholder="Search country name...">
            </div>
        </div>
        <div class="col-md-4">
            <select class="form-select" id="filterRegion">
                <option value="All">All Regions</option>
                @foreach($regions as $region)
                    <option value="{{ $region }}">{{ $region }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Countries Grid -->
    <div class="row" id="countriesGrid">
        @foreach($countries as $country)
        <div class="col-md-3 col-sm-6 mb-4 country-card" 
             data-name="{{ strtolower($country->name) }}" 
             data-region="{{ $country->region }}">
            <div class="card h-100 shadow-sm hover-shadow">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <img src="{{ $country->flag_url }}" alt="{{ $country->name }}" 
                             class="rounded" style="width: 40px; height: 30px; object-fit: cover; margin-right: 10px;">
                        <div>
                            <h6 class="mb-0">{{ $country->name }}</h6>
                            <small class="text-muted">{{ $country->code }}</small>
                        </div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Region:</small>
                        <span class="badge bg-secondary">{{ $country->region }}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Currency:</small>
                        <strong>{{ $country->currency_code ?? 'N/A' }}</strong>
                    </div>
                    <div class="mb-3">
                        <span class="badge bg-info risk-badge" data-code="{{ $country->code }}">
                            Loading...
                        </span>
                    </div>
                    <button class="btn btn-primary btn-sm w-100 view-detail-btn" 
                            data-code="{{ $country->code }}">
                        <i class="bi bi-eye"></i> View Detail
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- No Results Message -->
    <div id="noResults" class="alert alert-info text-center" style="display: none;">
        No countries found matching your search criteria.
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

@push('styles')
<style>
    .hover-shadow:hover {
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
        transform: translateY(-2px);
        transition: all 0.3s;
    }
    .country-card {
        transition: all 0.3s;
    }
    
    /* Ensure sidebar stays consistent */
    body .sidebar {
        width: 260px !important;
        min-width: 260px !important;
        max-width: 260px !important;
    }
</style>
@endpush

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
</script>
@endpush
