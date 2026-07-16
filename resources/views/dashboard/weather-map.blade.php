@extends('layouts.app')

@section('title', 'Weather Map')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0">🌦️ Global Weather Monitoring</h2>
            <p class="text-muted">Real-time weather conditions affecting supply chain logistics worldwide</p>
        </div>
    </div>

    <!-- Weather Stats Cards -->
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h3 class="mb-0" id="highRiskCount">-</h3>
                    <small>🔴 High Risk Areas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h3 class="mb-0" id="mediumRiskCount">-</h3>
                    <small>🟡 Medium Risk Areas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h3 class="mb-0" id="lowRiskCount">-</h3>
                    <small>🟢 Low Risk Areas</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h3 class="mb-0" id="totalLocations">-</h3>
                    <small>📍 Total Locations</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="row mb-3">
        <div class="col-md-4">
            <select class="form-select" id="filterRisk">
                <option value="">All Risk Levels</option>
                <option value="low">🟢 Low Risk</option>
                <option value="medium">🟡 Medium Risk</option>
                <option value="high">🔴 High Risk</option>
            </select>
        </div>
        <div class="col-md-4">
            <select class="form-select" id="filterCondition">
                <option value="">All Weather Conditions</option>
                <option value="clear">☀️ Clear</option>
                <option value="cloudy">☁️ Cloudy</option>
                <option value="rain">🌧️ Rain</option>
                <option value="storm">⛈️ Storm</option>
            </select>
        </div>
        <div class="col-md-4">
            <button class="btn btn-primary w-100" id="btnRefresh">
                <i class="bi bi-arrow-clockwise"></i> Refresh Data
            </button>
        </div>
    </div>

    <!-- Weather Map -->
    <div class="card mb-3">
        <div class="card-body p-0">
            <div id="weatherMap" style="height: 600px; width: 100%;"></div>
        </div>
    </div>

    <!-- Weather Legend -->
    <div class="card">
        <div class="card-header">
            <h6 class="mb-0">🌡️ Weather Risk Legend</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <span class="badge bg-success">🟢 Low Risk</span>
                    <p class="small mb-0">Good weather conditions<br>Safe for shipping operations</p>
                </div>
                <div class="col-md-3">
                    <span class="badge bg-warning">🟡 Medium Risk</span>
                    <p class="small mb-0">Moderate weather conditions<br>Monitor closely</p>
                </div>
                <div class="col-md-3">
                    <span class="badge bg-danger">🔴 High Risk</span>
                    <p class="small mb-0">Severe weather conditions<br>Delays expected</p>
                </div>
                <div class="col-md-3">
                    <span class="badge bg-secondary">ℹ️ Risk Factors</span>
                    <p class="small mb-0">• Heavy rainfall (>10mm)<br>• Strong winds (>50km/h)<br>• Extreme temperatures</p>
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
<style>
    .weather-marker-low {
        background-color: #28a745;
        border: 2px solid white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
    }
    .weather-marker-medium {
        background-color: #ffc107;
        border: 2px solid white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
    }
    .weather-marker-high {
        background-color: #dc3545;
        border: 2px solid white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.2); opacity: 0.7; }
        100% { transform: scale(1); opacity: 1; }
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let map;
let markers = [];
let allWeatherData = [];

// Initialize map
document.addEventListener('DOMContentLoaded', function() {
    initMap();
    loadWeatherData();
    setupEventListeners();
});

function initMap() {
    const worldBounds = [
        [-90, -180],
        [90, 180]
    ];
    
    map = L.map('weatherMap', {
        worldCopyJump: true,
        maxBounds: worldBounds,
        maxBoundsViscosity: 0.5
    }).setView([20, 0], 2);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 18,
        noWrap: true,
    }).addTo(map);
}

async function loadWeatherData() {
    try {
        const response = await fetch('/api/weather/global');
        const data = await response.json();
        
        if (data.success) {
            allWeatherData = data.data;
            displayWeatherOnMap(allWeatherData);
            updateStats(allWeatherData);
        }
    } catch (error) {
        console.error('Error loading weather data:', error);
        alert('Failed to load weather data. Please try again.');
    }
}

function displayWeatherOnMap(weatherData) {
    // Clear existing markers
    markers.forEach(marker => map.removeLayer(marker));
    markers = [];
    
    weatherData.forEach(weather => {
        const icon = L.divIcon({
            className: `weather-marker-${weather.risk_level}`,
            iconSize: [20, 20]
        });
        
        const riskEmoji = weather.risk_level === 'high' ? '🔴' : 
                         weather.risk_level === 'medium' ? '🟡' : '🟢';
        
        const marker = L.marker([weather.latitude, weather.longitude], { icon: icon })
            .addTo(map)
            .bindPopup(`
                <div class="text-center">
                    <strong>${weather.city}, ${weather.country}</strong><br>
                    <small>${riskEmoji} ${weather.risk_level.toUpperCase()} RISK</small><br>
                    <hr class="my-2">
                    🌡️ ${weather.temperature}°C<br>
                    🌧️ ${weather.rainfall}mm<br>
                    💨 ${weather.wind_speed}km/h<br>
                    <button class="btn btn-sm btn-primary mt-2" onclick="showWeatherDetail('${weather.city}', '${weather.country}', ${weather.latitude}, ${weather.longitude})">
                        View Details
                    </button>
                </div>
            `);
        
        markers.push(marker);
    });
}

function updateStats(weatherData) {
    const lowRisk = weatherData.filter(w => w.risk_level === 'low').length;
    const mediumRisk = weatherData.filter(w => w.risk_level === 'medium').length;
    const highRisk = weatherData.filter(w => w.risk_level === 'high').length;
    
    document.getElementById('lowRiskCount').textContent = lowRisk;
    document.getElementById('mediumRiskCount').textContent = mediumRisk;
    document.getElementById('highRiskCount').textContent = highRisk;
    document.getElementById('totalLocations').textContent = weatherData.length;
}

function showWeatherDetail(city, country, lat, lon) {
    const weather = allWeatherData.find(w => w.city === city && w.country === country);
    
    if (weather) {
        document.getElementById('modalCityName').textContent = `${city}, ${country}`;
        document.getElementById('modalTemp').textContent = weather.temperature + '°C';
        document.getElementById('modalRain').textContent = weather.rainfall + 'mm';
        document.getElementById('modalWind').textContent = weather.wind_speed + 'km/h';
        document.getElementById('modalCondition').textContent = weather.weather_condition;
        document.getElementById('modalRiskLevel').textContent = weather.risk_level.toUpperCase();
        
        const alertBox = document.getElementById('modalRiskAlert');
        alertBox.className = `alert alert-${weather.risk_level === 'high' ? 'danger' : weather.risk_level === 'medium' ? 'warning' : 'success'}`;
        
        const modal = new bootstrap.Modal(document.getElementById('weatherDetailModal'));
        modal.show();
    }
}

function setupEventListeners() {
    // Risk level filter
    document.getElementById('filterRisk').addEventListener('change', filterWeather);
    
    // Weather condition filter
    document.getElementById('filterCondition').addEventListener('change', filterWeather);
    
    // Refresh button
    document.getElementById('btnRefresh').addEventListener('click', function() {
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Loading...';
        
        loadWeatherData().then(() => {
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Refresh Data';
        });
    });
}

function filterWeather() {
    const riskFilter = document.getElementById('filterRisk').value;
    const conditionFilter = document.getElementById('filterCondition').value.toLowerCase();
    
    let filtered = allWeatherData.filter(weather => {
        const matchRisk = !riskFilter || weather.risk_level === riskFilter;
        const matchCondition = !conditionFilter || weather.weather_condition.toLowerCase().includes(conditionFilter);
        return matchRisk && matchCondition;
    });
    
    displayWeatherOnMap(filtered);
    updateStats(filtered);
}
</script>
@endpush
