@extends('layouts.app')

@section('title', 'Weather Map')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    /* Modern Light Theme for Weather Map */
    :root {
        --primary-blue: #4f46e5;
        --primary-light: #818cf8;
        --success-green: #10b981;
        --warning-orange: #f59e0b;
        --danger-red: #ef4444;
        --critical-red: #dc2626;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-600: #4b5563;
        --gray-800: #1f2937;
    }
    
    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        border-radius: 24px;
        padding: 2.5rem;
        margin-bottom: 2rem;
        color: white;
        box-shadow: 0 20px 60px rgba(6, 182, 212, 0.3);
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
    
    /* Weather Stats Grid */
    .weather-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }
    
    .weather-stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.75rem 1.25rem;
        border: 2px solid var(--gray-200);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        text-align: center;
    }
    
    .weather-stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
    
    .weather-stat-card.critical {
        background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        border: none;
        color: white;
    }
    
    .weather-stat-card.high {
        background: linear-gradient(135deg, #ff8c00 0%, #ff7300 100%);
        border: none;
        color: white;
    }
    
    .weather-stat-card.medium {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        border: none;
        color: white;
    }
    
    .weather-stat-card.low {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border: none;
        color: white;
    }
    
    .weather-stat-card.no-data {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        border: none;
        color: white;
    }
    
    .weather-stat-card.total {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
        color: white;
    }
    
    .weather-stat-card h3 {
        font-size: 2.25rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .weather-stat-card small {
        font-size: 0.9rem;
        font-weight: 600;
        opacity: 0.95;
        letter-spacing: 0.3px;
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
    
    .filter-section .form-select {
        border: 2px solid var(--gray-100);
        border-radius: 16px;
        padding: 0.85rem 1.2rem;
        font-size: 1rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        background: white;
    }
    
    .filter-section .form-select:focus {
        border-color: #06b6d4;
        box-shadow: 0 4px 20px rgba(6, 182, 212, 0.2);
        outline: none;
    }
    
    .filter-section .btn {
        border-radius: 16px;
        padding: 0.85rem 1.5rem;
        font-weight: 600;
        border: 2px solid transparent;
        transition: all 0.3s ease;
    }
    
    .filter-section .btn-primary {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        border: none;
        box-shadow: 0 4px 15px rgba(6, 182, 212, 0.3);
    }
    
    .filter-section .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(6, 182, 212, 0.5);
    }
    
    /* Search Section */
    .search-section {
        background: white;
        border-radius: 20px;
        padding: 1.75rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        margin-bottom: 2rem;
        border: 1px solid var(--gray-200);
    }
    
    .search-section .input-group {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        border: 2px solid var(--gray-100);
        transition: all 0.3s ease;
    }
    
    .search-section .input-group:focus-within {
        border-color: #06b6d4;
        box-shadow: 0 4px 20px rgba(6, 182, 212, 0.2);
    }
    
    .search-section .input-group-text {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        border: none;
        color: white;
        padding: 0.85rem 1.2rem;
        font-weight: 600;
    }
    
    .search-section .form-control {
        border: none;
        padding: 0.85rem 1.2rem;
        font-size: 1rem;
    }
    
    .search-section .form-control:focus {
        box-shadow: none;
    }
    
    .search-section .btn {
        border-radius: 12px;
        padding: 0.85rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .search-section .btn-primary {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        border: none;
        box-shadow: 0 4px 15px rgba(6, 182, 212, 0.3);
    }
    
    .search-section .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(6, 182, 212, 0.5);
    }
    
    .search-section .btn-secondary {
        background: var(--gray-200);
        color: var(--gray-800);
        border: 2px solid var(--gray-300);
    }
    
    .search-section .btn-secondary:hover {
        background: var(--gray-300);
    }
    
    /* Map Card */
    #weatherMapCard {
        border-radius: 20px;
        border: 2px solid var(--gray-200);
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        background: white;
    }
    
    #weatherMapCard .card-body {
        padding: 0;
    }
    
    #weatherMap {
        border-radius: 20px;
    }
    
    /* Alert Info */
    .alert {
        border-radius: 16px;
        border: 2px solid;
        padding: 1.25rem 1.5rem;
        font-weight: 500;
    }
    
    .alert-warning {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        border-color: #fbbf24;
        color: #92400e;
    }
    
    /* Leaflet Popup Customization */
    .leaflet-popup-content-wrapper {
        background-color: #1e293b !important;
        color: #ffffff !important;
        border-radius: 16px !important;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3) !important;
        padding: 0.5rem;
    }
    
    .leaflet-popup-content {
        color: #ffffff !important;
        margin: 1rem;
    }
    
    .leaflet-popup-tip {
        background-color: #1e293b !important;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .page-header {
            padding: 1.5rem;
        }
        
        .page-header h2 {
            font-size: 1.75rem;
        }
        
        .weather-stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <h2>🌦️ Global Weather Monitoring</h2>
        <p>Real-time weather conditions affecting supply chain logistics worldwide</p>
    </div>

    <!-- Weather Stats Grid -->
    <div class="weather-stats-grid">
        <div class="weather-stat-card critical">
            <h3 id="criticalRiskCount">-</h3>
            <small>🔴 Critical Weather</small>
        </div>
        <div class="weather-stat-card high">
            <h3 id="highRiskCount">-</h3>
            <small>🟠 High Weather Risk</small>
        </div>
        <div class="weather-stat-card medium">
            <h3 id="mediumRiskCount">-</h3>
            <small>🟡 Medium Weather Risk</small>
        </div>
        <div class="weather-stat-card low">
            <h3 id="lowRiskCount">-</h3>
            <small>🟢 Low Weather Risk</small>
        </div>
        <div class="weather-stat-card no-data">
            <h3 id="noDataCount">-</h3>
            <small>⚪ No Data</small>
        </div>
        <div class="weather-stat-card total">
            <h3 id="totalLocations">-</h3>
            <small>📍 Total Countries</small>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <div class="row g-3">
            <div class="col-md-4">
                <select class="form-select" id="filterRisk">
                    <option value="">All Weather Risk Levels</option>
                    <option value="low">🟢 Low Weather Risk (Clear/Cloudy)</option>
                    <option value="medium">🟡 Medium Weather Risk (Light Rain)</option>
                    <option value="high">🟠 High Weather Risk (Heavy Rain/Wind)</option>
                    <option value="critical">🔴 Critical Weather (Storm/Extreme)</option>
                </select>
            </div>
            <div class="col-md-4">
                <select class="form-select" id="filterRegion">
                    <option value="">All Regions</option>
                    <option value="Africa">🌍 Africa</option>
                    <option value="Americas">🌎 Americas</option>
                    <option value="Asia">🌏 Asia</option>
                    <option value="Europe">🇪🇺 Europe</option>
                    <option value="Oceania">🏝️ Oceania</option>
                </select>
            </div>
            <div class="col-md-4">
                <button class="btn btn-primary w-100" id="btnRefresh">
                    <i class="bi bi-arrow-clockwise"></i> Refresh Data
                </button>
            </div>
        </div>
    </div>

    <!-- Country Search Section -->
    <div class="search-section">
        <div class="row g-3 align-items-center">
            <div class="col-md-9">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i> 🔎 Cari</span>
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="countrySearch" 
                               placeholder="🔍 Cari negara... (contoh: Indonesia, Singapore, United States)"
                               list="countryList">
                        <button class="btn btn-primary" id="btnSearchCountry" type="button">
                            <i class="bi bi-geo-alt-fill"></i> Cari
                        </button>
                        <button class="btn btn-outline-secondary" id="btnClearSearch" type="button">
                            <i class="bi bi-x-circle"></i> Clear
                        </button>
                    </div>
                    <datalist id="countryList">
                        <!-- Will be populated dynamically -->
                    </datalist>
                    <small class="text-muted d-block mt-2">
                        💡 Tip: Ketik nama negara dan tekan Enter untuk zoom ke lokasi negara tersebut
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Weather Map -->
    <div class="card mb-3" id="weatherMapCard">
        <div class="card-body p-0 position-relative">
            <div id="loadingOverlay" class="loading-overlay" style="display: none;">
                <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
                <h5 class="mt-3">⚡ Loading Weather Data...</h5>
                <p class="text-muted" id="loadingProgress">Checking cached weather data...</p>
                <small class="text-muted">First load may take 10-30 seconds. Subsequent loads are instant.</small>
            </div>
            <!-- Fullscreen Toggle Button -->
            <button id="weatherMapFullscreenBtn" class="btn btn-light btn-sm position-absolute" 
                    style="top: 10px; right: 10px; z-index: 1000; box-shadow: 0 2px 4px rgba(0,0,0,0.2);" 
                    title="Toggle Fullscreen">
                <i class="bi bi-arrows-fullscreen" id="weatherMapFullscreenIcon"></i>
            </button>
            <div id="weatherMap" style="height: calc(100vh - 280px); min-height: 500px; width: 100%;"></div>
        </div>
    </div>

    <!-- Weather Legend -->
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">🌦️ Weather Risk Levels (Based on Wind Speed & Rainfall)</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <span class="badge bg-success">🟢 Low Weather Risk</span>
                    <p class="small mb-0"><strong>Weather:</strong> Clear/Cloudy<br>
                    <strong>Wind:</strong> < 30 km/h<br>
                    <strong>Rain:</strong> < 10 mm<br>
                    <strong>Status:</strong> Safe operations<br>
                    <strong>Action:</strong> Normal logistics</p>
                </div>
                <div class="col-md-3">
                    <span class="badge bg-warning">🟡 Medium Weather Risk</span>
                    <p class="small mb-0"><strong>Weather:</strong> Light Rain<br>
                    <strong>Wind:</strong> 30-50 km/h<br>
                    <strong>Rain:</strong> 10-20 mm<br>
                    <strong>Status:</strong> Monitor closely<br>
                    <strong>Action:</strong> Prepare contingency</p>
                </div>
                <div class="col-md-3">
                    <span class="badge" style="background-color: #ff8c00;">🟠 High Weather Risk</span>
                    <p class="small mb-0"><strong>Weather:</strong> Heavy Rain/Wind<br>
                    <strong>Wind:</strong> 50-70 km/h<br>
                    <strong>Rain:</strong> 20-50 mm<br>
                    <strong>Status:</strong> Delays expected<br>
                    <strong>Action:</strong> Reroute if possible</p>
                </div>
                <div class="col-md-3">
                    <span class="badge bg-danger">🔴 Critical Weather</span>
                    <p class="small mb-0"><strong>Weather:</strong> Storm/Extreme<br>
                    <strong>Wind:</strong> > 70 km/h<br>
                    <strong>Rain:</strong> > 50 mm<br>
                    <strong>Status:</strong> Operations halted<br>
                    <strong>Action:</strong> Avoid area</p>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-12">
                    <p class="small text-muted mb-0">
                        <strong>ℹ️ Note:</strong> These are WEATHER RISK LEVELS only - based on wind speed and rainfall at each country location. 
                        This is different from the overall Country Risk Score (which includes economics, news sentiment, etc.). 
                        Use this to monitor current weather conditions affecting logistics operations.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Weather Detail Modal -->
<div class="modal fade" id="weatherDetailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span id="modalCityName"></span> Weather Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h3 id="modalTemp">-</h3>
                                <small class="text-muted">Temperature (°C)</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h3 id="modalRain">-</h3>
                                <small class="text-muted">Rainfall (mm)</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h3 id="modalWind">-</h3>
                                <small class="text-muted">Wind Speed (km/h)</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h3 id="modalCondition">-</h3>
                                <small class="text-muted">Condition</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="alert" id="modalRiskAlert">
                    <strong>Risk Level:</strong> <span id="modalRiskLevel"></span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
    #weatherMap {
        background-color: #e8f4f8;
    }
    
    /* Force all weather markers to be visible */
    .custom-weather-marker {
        position: relative !important;
        z-index: 1000 !important;
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    .custom-weather-marker > div {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    
    .weather-marker-low {
        background-color: #28a745 !important;
        border: 2px solid white !important;
        border-radius: 50% !important;
        width: 20px !important;
        height: 20px !important;
        display: block !important;
        box-shadow: 0 0 0 2px #28a745, 0 2px 6px rgba(0,0,0,0.3) !important;
        position: relative !important;
        z-index: 1000 !important;
    }
    .weather-marker-medium {
        background-color: #ffc107 !important;
        border: 2px solid white !important;
        border-radius: 50% !important;
        width: 22px !important;
        height: 22px !important;
        display: block !important;
        box-shadow: 0 0 0 2px #ffc107, 0 2px 6px rgba(0,0,0,0.3) !important;
        position: relative !important;
        z-index: 1001 !important;
    }
    .weather-marker-high {
        background-color: #ff8c00 !important;
        border: 2px solid white !important;
        border-radius: 50% !important;
        width: 24px !important;
        height: 24px !important;
        display: block !important;
        box-shadow: 0 0 0 2px #ff8c00, 0 2px 8px rgba(255,140,0,0.4) !important;
        animation: pulse-high 2s infinite;
        position: relative !important;
        z-index: 1002 !important;
    }
    .weather-marker-critical {
        background-color: #dc3545 !important;
        border: 2px solid white !important;
        border-radius: 50% !important;
        width: 26px !important;
        height: 26px !important;
        display: block !important;
        box-shadow: 0 0 0 2px #dc3545, 0 2px 10px rgba(220,53,69,0.5) !important;
        animation: pulse-critical 1.5s infinite;
        position: relative !important;
        z-index: 1003 !important;
    }
    .weather-marker-nodata {
        background-color: #6c757d !important;
        border: 2px solid white !important;
        border-radius: 50% !important;
        width: 18px !important;
        height: 18px !important;
        display: block !important;
        box-shadow: 0 0 0 2px #6c757d, 0 2px 4px rgba(0,0,0,0.2) !important;
        opacity: 0.7;
        position: relative !important;
        z-index: 999 !important;
    }
    @keyframes pulse-high {
        0% { 
            transform: scale(1); 
            box-shadow: 0 0 0 2px #ff8c00, 0 2px 8px rgba(255,140,0,0.4);
        }
        50% { 
            transform: scale(1.15); 
            box-shadow: 0 0 0 3px #ff8c00, 0 4px 12px rgba(255,140,0,0.6);
        }
        100% { 
            transform: scale(1); 
            box-shadow: 0 0 0 2px #ff8c00, 0 2px 8px rgba(255,140,0,0.4);
        }
    }
    @keyframes pulse-critical {
        0% { 
            transform: scale(1); 
            box-shadow: 0 0 0 2px #dc3545, 0 2px 10px rgba(220,53,69,0.5);
        }
        50% { 
            transform: scale(1.2); 
            box-shadow: 0 0 0 4px #dc3545, 0 4px 16px rgba(220,53,69,0.8);
        }
        100% { 
            transform: scale(1); 
            box-shadow: 0 0 0 2px #dc3545, 0 2px 10px rgba(220,53,69,0.5);
        }
    }
    
    /* Marker cluster styling */
    .marker-cluster-small {
        background-color: rgba(40, 167, 69, 0.6);
    }
    .marker-cluster-small div {
        background-color: rgba(40, 167, 69, 0.8) !important;
        color: #ffffff !important;
        font-weight: bold !important;
    }
    .marker-cluster-medium {
        background-color: rgba(255, 193, 7, 0.6);
    }
    .marker-cluster-medium div {
        background-color: rgba(255, 193, 7, 0.8) !important;
        color: #000000 !important;
        font-weight: bold !important;
    }
    .marker-cluster-large {
        background-color: rgba(220, 53, 69, 0.6);
    }
    .marker-cluster-large div {
        background-color: rgba(220, 53, 69, 0.8) !important;
        color: #ffffff !important;
        font-weight: bold !important;
    }
    /* Force text color on all marker clusters */
    .marker-cluster span {
        font-weight: bold !important;
    }
    
    /* CRITICAL: Ensure marker cluster group is always visible */
    .leaflet-marker-pane {
        z-index: 600 !important;
        opacity: 1 !important;
        visibility: visible !important;
    }
    
    .leaflet-marker-icon {
        opacity: 1 !important;
        visibility: visible !important;
        display: block !important;
    }
    
    .leaflet-popup-pane {
        z-index: 700 !important;
    }
    
    /* Ensure leaflet container is visible */
    .leaflet-container {
        background-color: #e8f4f8 !important;
    }
    
    /* Loading overlay */
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        flex-direction: column;
    }
    
    /* Fullscreen mode */
    #weatherMapCard.map-fullscreen {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 99999 !important;
        margin: 0 !important;
        padding: 0 !important;
        border-radius: 0 !important;
        border: none !important;
        max-width: 100vw !important;
    }
    
    #weatherMapCard.map-fullscreen .card-body {
        height: 100vh !important;
        width: 100vw !important;
        border-radius: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        max-width: 100vw !important;
    }
    
    #weatherMapCard.map-fullscreen #weatherMap {
        height: 100vh !important;
        width: 100vw !important;
        max-width: 100vw !important;
    }
    
    #weatherMapCard.map-fullscreen #weatherMapFullscreenBtn {
        top: 20px !important;
        right: 20px !important;
        z-index: 100000 !important;
    }
    
    /* Force container to allow overflow */
    body:has(#weatherMapCard.map-fullscreen) .container-fluid {
        overflow: visible !important;
        max-width: none !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script>
let map;
let markerClusterGroup;
let allWeatherData = [];

// Initialize map
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing...');
    try {
        initMap();
        loadWeatherData();
        setupEventListeners();
    } catch (error) {
        console.error('Initialization error:', error);
        alert('Failed to initialize weather map: ' + error.message);
    }
});

function initMap() {
    // Completely disable default Leaflet marker icons to prevent 404 errors
    L.Icon.Default.imagePath = 'https://unpkg.com/leaflet@1.9.4/dist/images/';
    
    map = L.map('weatherMap', {
        worldCopyJump: true,
    }).setView([20, 0], 2);
    
    // Use OpenStreetMap tiles with better error handling
    const tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        subdomains: ['a', 'b', 'c'],
        maxZoom: 19,
        minZoom: 1,
        errorTileUrl: '', // Use blank instead of broken tile
        keepBuffer: 2
    }).addTo(map);
    
    // Suppress tile loading errors from console
    tileLayer.on('tileerror', function(error, tile) {
        // Silently handle tile errors - they don't affect functionality
        tile.target.src = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';
    });
    
    // Initialize marker cluster group with custom icon creation
    markerClusterGroup = L.markerClusterGroup({
        chunkedLoading: true,
        maxClusterRadius: 80,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false,
        zoomToBoundsOnClick: true,
        iconCreateFunction: function(cluster) {
            var childCount = cluster.getChildCount();
            var c = ' marker-cluster-';
            if (childCount < 10) {
                c += 'small';
            } else if (childCount < 100) {
                c += 'medium';
            } else {
                c += 'large';
            }
            
            return new L.DivIcon({ 
                html: '<div><span>' + childCount + '</span></div>', 
                className: 'marker-cluster' + c, 
                iconSize: new L.Point(40, 40) 
            });
        }
    });
    
    console.log('✅ Map initialized with MarkerCluster for optimal performance');
}

async function loadWeatherData() {
    const btnRefresh = document.getElementById('btnRefresh');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const loadingProgress = document.getElementById('loadingProgress');
    
    btnRefresh.disabled = true;
    btnRefresh.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Loading...';
    loadingOverlay.style.display = 'flex';
    loadingProgress.textContent = 'Loading weather data...';
    
    try {
        console.log('Fetching weather data for all countries...');
        console.time('Total load time');
        
        // Check cache first (10 minutes for better UX)
        const cacheKey = 'weather_global_data';
        const cacheTimestamp = 'weather_global_timestamp';
        const cachedData = localStorage.getItem(cacheKey);
        const cachedTime = localStorage.getItem(cacheTimestamp);
        const now = Date.now();
        
        // Use cache if less than 10 minutes old (improved UX)
        if (cachedData && cachedTime && (now - parseInt(cachedTime)) < 600000) {
            console.log('✅ Using cached weather data from browser storage');
            loadingProgress.textContent = 'Loading from cache...';
            const data = JSON.parse(cachedData);
            allWeatherData = data.data;
            loadingOverlay.style.display = 'none';
            displayWeatherOnMap(allWeatherData);
            updateStats(allWeatherData);
            btnRefresh.disabled = false;
            btnRefresh.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Refresh Data';
            console.timeEnd('Total load time');
            return;
        }
        
        loadingProgress.textContent = 'Fetching from server... (< 1 second)';
        console.time('API fetch');
        
        // Reduced timeout to 10 seconds (API is fast with cache)
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 seconds
        
        const response = await fetch('/api/weather/global', {
            signal: controller.signal
        });
        
        clearTimeout(timeoutId);
        console.timeEnd('API fetch');
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        console.time('Parse JSON');
        const data = await response.json();
        console.timeEnd('Parse JSON');
        
        console.log('✅ Weather data received:', data.total, 'countries');
        
        if (data.success) {
            allWeatherData = data.data;
            
            // Show cache info to user
            const cacheInfo = data.from_cache > 0 
                ? `Loaded ${data.from_cache} from cache, ${data.from_api} fetched live`
                : `Loaded ${allWeatherData.length} countries`;
                
            console.log(`✅ ${cacheInfo}`);
            
            if (data.message) {
                console.log(`ℹ️  ${data.message}`);
            }
            
            if (allWeatherData.length === 0) {
                throw new Error('No weather data received from API');
            }
            
            // Cache the data in browser
            localStorage.setItem(cacheKey, JSON.stringify(data));
            localStorage.setItem(cacheTimestamp, now.toString());
            
            loadingOverlay.style.display = 'none';
            displayWeatherOnMap(allWeatherData);
            updateStats(allWeatherData);
        } else {
            throw new Error('API returned success: false');
        }
    } catch (error) {
        console.error('Error loading weather data:', error);
        loadingOverlay.style.display = 'none';
        
        let errorMessage = 'Failed to load weather data';
        if (error.name === 'AbortError') {
            errorMessage = 'Request timeout after 1 minute. The server is taking too long.\n\n' +
                          'This usually happens on first load. Please:\n' +
                          '1. Wait a few seconds and click "Refresh Data" button\n' +
                          '2. Or refresh the page (F5)\n\n' +
                          'The system is caching weather data in the background.';
        } else {
            errorMessage = 'Failed to load weather data: ' + error.message + 
                          '\n\nTry clicking "Refresh Data" button or refresh the page (F5).';
        }
        
        alert(errorMessage);
    } finally {
        btnRefresh.disabled = false;
        btnRefresh.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Refresh Data';
    }
}

function displayWeatherOnMap(weatherData) {
    console.log('📍 Displaying weather data with MarkerCluster:', weatherData.length, 'countries');
    console.time('Render markers');
    
    // STEP 1: Remove cluster group from map completely
    try {
        if (markerClusterGroup) {
            map.removeLayer(markerClusterGroup);
            console.log('✅ Step 1: Removed existing cluster group from map');
        }
    } catch (e) {
        console.log('ℹ️ No existing cluster group to remove');
    }
    
    // STEP 2: Clear all markers from cluster group
    if (markerClusterGroup) {
        markerClusterGroup.clearLayers();
        console.log('✅ Step 2: Cleared all markers from cluster group');
    }
    
    // STEP 3: Check if we have data
    if (weatherData.length === 0) {
        console.warn('⚠️ No weather data to display (might be filtered out)');
        map.invalidateSize();
        return;
    }
    
    // STEP 4: Create markers and add to cluster group
    console.log(`📌 Step 4: Creating ${weatherData.length} markers...`);
    
    let markersAdded = 0;
    let markersFailed = 0;
    
    weatherData.forEach(weather => {
        // Determine marker color and size based on risk level
        let bgColor, size, riskEmoji;
        switch(weather.risk_level) {
            case 'critical':
                bgColor = '#dc3545';
                size = 26;
                riskEmoji = '🔴';
                break;
            case 'high':
                bgColor = '#ff8c00';
                size = 24;
                riskEmoji = '🟠';
                break;
            case 'medium':
                bgColor = '#ffc107';
                size = 22;
                riskEmoji = '🟡';
                break;
            case 'low':
                bgColor = '#28a745';
                size = 20;
                riskEmoji = '🟢';
                break;
            default:
                bgColor = '#6c757d';
                size = 18;
                riskEmoji = '⚪';
        }
        
        const iconHtml = `<div style="
            background-color: ${bgColor};
            border: 2px solid white;
            border-radius: 50%;
            width: ${size}px;
            height: ${size}px;
            box-shadow: 0 0 0 2px ${bgColor}, 0 2px 6px rgba(0,0,0,0.4);
        "></div>`;
        
        const icon = L.divIcon({
            html: iconHtml,
            iconSize: [size, size],
            iconAnchor: [size/2, size/2],
            popupAnchor: [0, -(size/2)],
            className: `custom-weather-marker weather-marker-${weather.risk_level}`
        });
        
        try {
            const lat = parseFloat(weather.latitude);
            const lng = parseFloat(weather.longitude);
            
            // Validate coordinates
            if (isNaN(lat) || isNaN(lng)) {
                console.error(`❌ Invalid coordinates for ${weather.country_name}: [${weather.latitude}, ${weather.longitude}]`);
                markersFailed++;
                return;
            }
            
            const marker = L.marker([lat, lng], { icon: icon })
                .bindPopup(`
                    <div class="text-center">
                        <strong>${weather.country_name}</strong><br>
                        <small>${weather.region} • ${weather.country_code}</small><br>
                        <hr class="my-2">
                        <span class="badge bg-${getRiskBadgeColor(weather.risk_level)}">${riskEmoji} ${weather.risk_level.toUpperCase()}</span><br>
                        <hr class="my-2">
                        🌡️ ${weather.temperature}°C<br>
                        🌧️ ${weather.rainfall}mm<br>
                        💨 ${weather.wind_speed}km/h<br>
                        ☁️ ${weather.weather_condition}<br>
                        <small class="text-muted">Lat: ${lat.toFixed(2)}, Lng: ${lng.toFixed(2)}</small><br>
                        <button class="btn btn-sm btn-primary mt-2" onclick="showWeatherDetail('${weather.country_name}', '${weather.country_code}')">
                            View Details
                        </button>
                    </div>
                `);
            
            // Add marker to cluster group
            markerClusterGroup.addLayer(marker);
            markersAdded++;
            
            // Log first few markers for debugging
            if (markersAdded <= 3) {
                console.log(`  ✓ Marker ${markersAdded}: ${weather.country_name} at [${lat}, ${lng}] - ${weather.risk_level}`);
            }
        } catch (error) {
            console.error(`❌ Failed to create marker for ${weather.country_name}:`, error);
            markersFailed++;
        }
    });
    
    console.log(`✅ Step 4 Complete: ${markersAdded} markers created, ${markersFailed} failed`);
    
    // STEP 5: Add cluster group to map
    console.log('📌 Step 5: Adding cluster group to map...');
    map.addLayer(markerClusterGroup);
    
    // STEP 6: Verify and refresh
    const clusterCount = markerClusterGroup.getLayers().length;
    console.log(`✅ Step 6: Verification - Cluster group has ${clusterCount} markers`);
    
    if (clusterCount !== markersAdded) {
        console.error(`❌ MISMATCH: Created ${markersAdded} markers but cluster has ${clusterCount}`);
    }
    
    // Force cluster refresh
    markerClusterGroup.refreshClusters();
    console.log('🔄 Clusters refreshed');
    
    console.timeEnd('Render markers');
    console.log(`✅ ========== RENDER COMPLETE ==========`);
    
    // Force map refresh
    setTimeout(() => {
        map.invalidateSize();
        console.log('🔄 Map invalidated');
    }, 100);
}

function getRiskBadgeColor(riskLevel) {
    switch(riskLevel) {
        case 'critical': return 'danger';
        case 'high': return 'warning';
        case 'medium': return 'warning';
        case 'low': return 'success';
        default: return 'secondary';
    }
}

function updateStats(weatherData) {
    const lowRisk = weatherData.filter(w => w.risk_level === 'low').length;
    const mediumRisk = weatherData.filter(w => w.risk_level === 'medium').length;
    const highRisk = weatherData.filter(w => w.risk_level === 'high').length;
    const criticalRisk = weatherData.filter(w => w.risk_level === 'critical').length;
    const noData = weatherData.filter(w => !w.risk_level || w.risk_level === 'unknown').length;
    
    document.getElementById('lowRiskCount').textContent = lowRisk;
    document.getElementById('mediumRiskCount').textContent = mediumRisk;
    document.getElementById('highRiskCount').textContent = highRisk;
    document.getElementById('criticalRiskCount').textContent = criticalRisk;
    document.getElementById('noDataCount').textContent = noData;
    document.getElementById('totalLocations').textContent = weatherData.length;
    
    // Populate country search datalist
    populateCountryList(weatherData);
}

function populateCountryList(weatherData) {
    const datalist = document.getElementById('countryList');
    datalist.innerHTML = '';
    
    // Sort countries alphabetically
    const sortedCountries = weatherData
        .map(w => w.country_name)
        .sort((a, b) => a.localeCompare(b));
    
    sortedCountries.forEach(country => {
        const option = document.createElement('option');
        option.value = country;
        datalist.appendChild(option);
    });
}

function searchCountry() {
    const searchInput = document.getElementById('countrySearch');
    const searchTerm = searchInput.value.trim().toLowerCase();
    
    if (!searchTerm) {
        alert('⚠️ Silakan masukkan nama negara yang ingin dicari');
        return;
    }
    
    // Find country in data (case insensitive, partial match)
    const country = allWeatherData.find(w => 
        w.country_name.toLowerCase().includes(searchTerm) ||
        w.country_code.toLowerCase().includes(searchTerm)
    );
    
    if (country) {
        console.log(`🔍 Found country: ${country.country_name}`);
        
        // Zoom to country with animation
        map.flyTo(
            [parseFloat(country.latitude), parseFloat(country.longitude)], 
            6, // Zoom level for country detail
            {
                duration: 1.5, // Animation duration in seconds
                easeLinearity: 0.5
            }
        );
        
        // Find and open the marker popup after animation completes
        setTimeout(() => {
            // Find marker in cluster group
            markerClusterGroup.eachLayer(marker => {
                const pos = marker.getLatLng();
                if (Math.abs(pos.lat - parseFloat(country.latitude)) < 0.01 && 
                    Math.abs(pos.lng - parseFloat(country.longitude)) < 0.01) {
                    // Zoom cluster to show this specific marker
                    markerClusterGroup.zoomToShowLayer(marker, function() {
                        marker.openPopup();
                        console.log(`✅ Opened popup for ${country.country_name}`);
                    });
                }
            });
        }, 1600); // Wait for animation to complete
        
        // Highlight search input as success
        searchInput.classList.remove('is-invalid');
        searchInput.classList.add('is-valid');
        
        // Show success message
        showSearchMessage(`✅ Ditemukan: ${country.country_name} (${country.country_code})`, 'success');
    } else {
        console.log(`❌ Country not found: ${searchTerm}`);
        
        // Highlight search input as error
        searchInput.classList.remove('is-valid');
        searchInput.classList.add('is-invalid');
        
        // Show error message
        showSearchMessage(`❌ Negara "${searchTerm}" tidak ditemukan. Coba nama lain.`, 'danger');
    }
}

function clearSearch() {
    const searchInput = document.getElementById('countrySearch');
    searchInput.value = '';
    searchInput.classList.remove('is-valid', 'is-invalid');
    
    // Reset map view to world
    map.flyTo([20, 0], 2, {
        duration: 1.5,
        easeLinearity: 0.5
    });
    
    console.log('🔄 Search cleared, reset to world view');
}

function showSearchMessage(message, type) {
    // Create or update toast notification
    let toast = document.getElementById('searchToast');
    
    if (!toast) {
        // Create toast container if it doesn't exist
        const toastContainer = document.createElement('div');
        toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        toastContainer.innerHTML = `
            <div id="searchToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body" id="searchToastBody"></div>
                    <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        document.body.appendChild(toastContainer);
        toast = document.getElementById('searchToast');
    }
    
    // Update toast content and color
    const toastBody = document.getElementById('searchToastBody');
    toastBody.textContent = message;
    
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    
    // Show toast
    const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
    bsToast.show();
}

function showWeatherDetail(countryName, countryCode) {
    const weather = allWeatherData.find(w => w.country_code === countryCode);
    
    if (weather) {
        document.getElementById('modalCityName').textContent = countryName;
        document.getElementById('modalTemp').textContent = weather.temperature + '°C';
        document.getElementById('modalRain').textContent = weather.rainfall + 'mm';
        document.getElementById('modalWind').textContent = weather.wind_speed + 'km/h';
        document.getElementById('modalCondition').textContent = weather.weather_condition;
        document.getElementById('modalRiskLevel').textContent = weather.risk_level.toUpperCase();
        
        const alertBox = document.getElementById('modalRiskAlert');
        const alertClass = weather.risk_level === 'critical' ? 'danger' : 
                          weather.risk_level === 'high' ? 'warning' : 
                          weather.risk_level === 'medium' ? 'warning' : 'success';
        alertBox.className = `alert alert-${alertClass}`;
        
        const modal = new bootstrap.Modal(document.getElementById('weatherDetailModal'));
        modal.show();
    }
}

function setupEventListeners() {
    // Risk level filter
    document.getElementById('filterRisk').addEventListener('change', filterWeather);
    
    // Region filter
    document.getElementById('filterRegion').addEventListener('change', filterWeather);
    
    // Refresh button
    document.getElementById('btnRefresh').addEventListener('click', loadWeatherData);
    
    // Fullscreen toggle
    document.getElementById('weatherMapFullscreenBtn').addEventListener('click', toggleWeatherMapFullscreen);
    
    // Country search
    document.getElementById('btnSearchCountry').addEventListener('click', searchCountry);
    document.getElementById('countrySearch').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchCountry();
        }
    });
    document.getElementById('btnClearSearch').addEventListener('click', clearSearch);
}

function toggleWeatherMapFullscreen() {
    const mapCard = document.getElementById('weatherMapCard');
    const icon = document.getElementById('weatherMapFullscreenIcon');
    
    // Get all container elements to hide
    const header = document.querySelector('.row.mb-4'); // Title row
    const statsCards = document.querySelectorAll('.row.mb-3')[0]; // Stats cards
    const filterRow = document.querySelectorAll('.row.mb-3')[1]; // Filters
    const legendCard = document.querySelector('.card:has(.card-header)'); // Legend
    
    console.log('Toggling fullscreen...', mapCard.classList.contains('map-fullscreen'));
    
    if (mapCard.classList.contains('map-fullscreen')) {
        // Exit fullscreen
        mapCard.classList.remove('map-fullscreen');
        icon.className = 'bi bi-arrows-fullscreen';
        document.body.style.overflow = '';
        
        // Show hidden elements
        if (header) header.style.display = '';
        if (statsCards) statsCards.style.display = '';
        if (filterRow) filterRow.style.display = '';
        if (legendCard) legendCard.style.display = '';
        
        console.log('Exited fullscreen');
    } else {
        // Enter fullscreen
        mapCard.classList.add('map-fullscreen');
        icon.className = 'bi bi-fullscreen-exit';
        document.body.style.overflow = 'hidden';
        
        // Hide elements
        if (header) header.style.display = 'none';
        if (statsCards) statsCards.style.display = 'none';
        if (filterRow) filterRow.style.display = 'none';
        if (legendCard) legendCard.style.display = 'none';
        
        console.log('Entered fullscreen');
    }
    
    // Refresh map to fit new size
    setTimeout(() => {
        map.invalidateSize();
        console.log('Map size invalidated');
    }, 100);
}

function filterWeather() {
    const riskFilter = document.getElementById('filterRisk').value;
    const regionFilter = document.getElementById('filterRegion').value;
    
    let filtered = allWeatherData.filter(weather => {
        const matchRisk = !riskFilter || weather.risk_level === riskFilter;
        const matchRegion = !regionFilter || weather.region === regionFilter;
        return matchRisk && matchRegion;
    });
    
    console.log('🔍 Filter applied:', { riskFilter, regionFilter, resultCount: filtered.length });
    
    // Log filtered countries for debugging
    if (filtered.length > 0 && filtered.length <= 5) {
        console.log('📍 Filtered countries:', filtered.map(w => ({
            name: w.country_name,
            risk: w.risk_level,
            lat: w.latitude,
            lng: w.longitude
        })));
    }
    
    displayWeatherOnMap(filtered);
    updateStats(filtered);
    
    // Auto-zoom to filtered markers if 1-3 results
    if (filtered.length >= 1 && filtered.length <= 3) {
        setTimeout(() => {
            autoZoomToMarkers(filtered);
        }, 500);
    }
}

function autoZoomToMarkers(weatherData) {
    if (weatherData.length === 0) return;
    
    try {
        if (weatherData.length === 1) {
            // Single marker: zoom to it directly
            const location = weatherData[0];
            console.log(`🎯 Auto-zooming to: ${location.country_name} at [${location.latitude}, ${location.longitude}]`);
            
            map.flyTo(
                [parseFloat(location.latitude), parseFloat(location.longitude)], 
                5, // Zoom level to see the country
                {
                    duration: 1.5,
                    easeLinearity: 0.5
                }
            );
        } else {
            // Multiple markers: fit bounds to show all
            const bounds = L.latLngBounds(
                weatherData.map(w => [parseFloat(w.latitude), parseFloat(w.longitude)])
            );
            
            console.log(`🎯 Auto-fitting bounds to ${weatherData.length} markers`);
            
            map.flyToBounds(bounds, {
                padding: [50, 50],
                duration: 1.5,
                easeLinearity: 0.5
            });
        }
    } catch (error) {
        console.error('❌ Error auto-zooming:', error);
    }
}
</script>
@endpush
