@extends('layouts.admin')

@section('title', 'Port Map - Admin')

@section('page-title', 'Port Map')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
<style>
    /* Modern Premium Theme for Admin Port Map */
    :root {
        --primary-gradient-start: #667eea;
        --primary-gradient-end: #764ba2;
        --success-gradient-start: #11998e;
        --success-gradient-end: #38ef7d;
        --warning-gradient-start: #f2994a;
        --warning-gradient-end: #f2c94c;
        --info-gradient-start: #4facfe;
        --info-gradient-end: #00f2fe;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-300: #d1d5db;
        --gray-600: #4b5563;
        --gray-800: #1f2937;
        --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.04);
        --shadow-md: 0 4px 20px rgba(0, 0, 0, 0.08);
        --shadow-lg: 0 10px 40px rgba(0, 0, 0, 0.12);
        --shadow-xl: 0 20px 60px rgba(0, 0, 0, 0.15);
    }
    
    /* Page Header Enhanced */
    .port-map-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 24px;
        padding: 2.5rem;
        margin-bottom: 2rem;
        color: white;
        box-shadow: var(--shadow-xl);
        position: relative;
        overflow: hidden;
    }
    
    .port-map-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
        border-radius: 50%;
        animation: float 20s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% { transform: translate(0, 0); }
        50% { transform: translate(20px, -20px); }
    }
    
    .port-map-header h2 {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        position: relative;
        z-index: 2;
    }
    
    .port-map-header p {
        font-size: 1.1rem;
        opacity: 0.95;
        margin: 0;
        position: relative;
        z-index: 2;
    }
    
    /* Controls Section */
    .controls-card {
        background: white;
        border-radius: 20px;
        padding: 1.75rem;
        box-shadow: var(--shadow-md);
        margin-bottom: 2rem;
        border: 2px solid rgba(102, 126, 234, 0.1);
    }
    
    .controls-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--primary-gradient-start), var(--primary-gradient-end));
    }
    
    .form-control, .form-select {
        border: 2px solid var(--gray-200);
        border-radius: 12px;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: var(--primary-gradient-start);
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .btn-group .btn {
        border-radius: 12px;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-group .btn.active {
        background: linear-gradient(135deg, var(--primary-gradient-start), var(--primary-gradient-end));
        color: white;
        border-color: var(--primary-gradient-start);
    }
    
    /* Map Card */
    #portMapCard {
        border-radius: 20px;
        overflow: hidden;
        box-shadow: var(--shadow-lg);
        border: 2px solid rgba(102, 126, 234, 0.15);
    }
    
    #portMapCard::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--primary-gradient-start), var(--primary-gradient-end));
        z-index: 1000;
    }
    
    /* Stats Cards */
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s ease;
        border: 2px solid rgba(102, 126, 234, 0.1);
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
    }
    
    .stat-card.bg-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
    }
    
    .stat-card.bg-success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        border: none;
        color: white;
    }
    
    .stat-card.bg-warning {
        background: linear-gradient(135deg, #f2994a 0%, #f2c94c 100%);
        border: none;
        color: white;
    }
    
    .stat-card.bg-info {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        border: none;
        color: white;
    }
    
    .stat-card h3 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    /* Marker Clusters */
    .marker-cluster-small, .marker-cluster-medium, .marker-cluster-large {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.6), rgba(118, 75, 162, 0.6)) !important;
        border: 3px solid rgba(255, 255, 255, 0.8) !important;
    }
    
    .marker-cluster div {
        background: linear-gradient(135deg, var(--primary-gradient-start), var(--primary-gradient-end)) !important;
        color: white !important;
        font-weight: 700 !important;
    }
    
    /* Popup */
    .leaflet-popup-content-wrapper {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%) !important;
        color: white !important;
        border-radius: 16px !important;
        box-shadow: var(--shadow-xl) !important;
    }
    
    .leaflet-popup-content {
        color: white !important;
    }
    
    .leaflet-popup-tip {
        background: #1e293b !important;
    }
    
    /* Fullscreen */
    #portMapCard.map-fullscreen {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 99999 !important;
        margin: 0 !important;
        border-radius: 0 !important;
    }
    
    #portMapCard.map-fullscreen #portMap {
        height: 100vh !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="port-map-header">
        <h2>🚢 Global Port Map</h2>
        <p>Interactive map showing all ports in the system</p>
    </div>

    <!-- Controls -->
    <div class="controls-card position-relative">
        <div class="row g-3">
            <div class="col-md-3">
                <input type="text" class="form-control" id="searchPort" placeholder="Search ports...">
            </div>
            <div class="col-md-2">
                <select class="form-select" id="filterRegion">
                    <option value="">All Regions</option>
                    <option value="Asia">Asia</option>
                    <option value="Europe">Europe</option>
                    <option value="Americas">Americas</option>
                    <option value="Africa">Africa</option>
                    <option value="Oceania">Oceania</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="filterStatus">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="filterCountry">
                    <option value="">All Countries</option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="btn-group w-100" role="group">
                    <button type="button" class="btn btn-outline-primary active" id="viewMap">
                        <i class="bi bi-map"></i> Map
                    </button>
                    <button type="button" class="btn btn-outline-primary" id="viewList">
                        <i class="bi bi-list"></i> List
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Map View -->
    <div id="mapView">
        <div class="card" id="portMapCard">
            <div class="card-body p-0 position-relative">
                <button id="portMapFullscreenBtn" class="btn btn-light btn-sm position-absolute" 
                        style="top: 10px; right: 10px; z-index: 1000;">
                    <i class="bi bi-arrows-fullscreen"></i>
                </button>
                <div id="portMap" style="height: calc(100vh - 350px); min-height: 500px;"></div>
            </div>
        </div>
    </div>

    <!-- List View -->
    <div id="listView" style="display: none;">
        <div class="card">
            <div class="card-body">
                <div id="portListContainer">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-2">Loading ports...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mt-3">
        <div class="col-md-2">
            <div class="stat-card bg-primary">
                <h3 id="totalPorts">-</h3>
                <small>Total Ports</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card bg-success">
                <h3 id="activePorts">-</h3>
                <small>Active</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card bg-warning">
                <h3 id="inactivePorts">-</h3>
                <small>Inactive</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card bg-info">
                <h3 id="asiaPorts">-</h3>
                <small>Asia</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card bg-success">
                <h3 id="europePorts">-</h3>
                <small>Europe</small>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stat-card bg-primary">
                <h3 id="americasPorts">-</h3>
                <small>Americas</small>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="portDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title">Port Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Port Information</h6>
                        <p><strong>Port Name:</strong> <span id="detailPortName">-</span></p>
                        <p><strong>Code:</strong> <span id="detailCode">-</span></p>
                        <p><strong>Country:</strong> <span id="detailCountry">-</span></p>
                        <p><strong>Region:</strong> <span id="detailRegion">-</span></p>
                        <p><strong>Status:</strong> <span id="detailStatus"></span></p>
                        <p><strong>Coordinates:</strong> <span id="detailCoords">-</span></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Weather Data</h6>
                        <div id="weatherLoading" class="text-center py-3">
                            <div class="spinner-border spinner-border-sm"></div>
                            <p class="small text-muted mt-2">Loading...</p>
                        </div>
                        <div id="weatherContent" style="display: none;">
                            <p><strong>Temperature:</strong> <span id="detailTemp"></span>°C</p>
                            <p><strong>Condition:</strong> <span id="detailCondition"></span></p>
                            <p><strong>Wind:</strong> <span id="detailWind"></span> km/h</p>
                            <p><strong>Rainfall:</strong> <span id="detailRain"></span> mm</p>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="#" id="editPortLink" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Edit Port
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script>
let map, markerClusterGroup, allPorts = [];

document.addEventListener('DOMContentLoaded', function() {
    initMap();
    loadPorts();
    setupEventListeners();
});

function initMap() {
    map = L.map('portMap').setView([20, 0], 2);
    
    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '© OpenStreetMap © CARTO',
        maxZoom: 19
    }).addTo(map);
    
    markerClusterGroup = L.markerClusterGroup({
        chunkedLoading: true,
        maxClusterRadius: 50,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: true,
        zoomToBoundsOnClick: true
    });
}

async function loadPorts() {
    try {
        const response = await fetch('/api/ports');
        const data = await response.json();
        
        if (data.success) {
            allPorts = data.data;
            displayPortsOnMap(allPorts);
            updateStats(allPorts);
            populateCountryFilter(allPorts);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function populateCountryFilter(ports) {
    const countries = [...new Set(ports.map(p => p.country_name))].sort();
    const select = document.getElementById('filterCountry');
    
    countries.forEach(country => {
        const option = document.createElement('option');
        option.value = country;
        option.textContent = country;
        select.appendChild(option);
    });
}

function displayPortsOnMap(ports) {
    markerClusterGroup.clearLayers();
    
    ports.forEach(port => {
        const statusBadge = port.is_active ? 
            '<span class="badge bg-success">Active</span>' : 
            '<span class="badge bg-danger">Inactive</span>';
        
        const marker = L.marker([port.latitude, port.longitude])
            .bindPopup(`
                <div class="text-center">
                    <strong style="color: white;">${port.port_name}</strong><br>
                    <small style="color: #e2e8f0;">${port.country_name}</small><br>
                    <small style="color: #cbd5e1;">${port.region}</small><br>
                    ${statusBadge}<br>
                    <button class="btn btn-sm btn-light mt-2" onclick="showPortDetail(${port.id})">
                        View Details
                    </button>
                </div>
            `);
        
        markerClusterGroup.addLayer(marker);
    });
    
    map.addLayer(markerClusterGroup);
}

function updateStats(ports) {
    document.getElementById('totalPorts').textContent = ports.length;
    
    const active = ports.filter(p => p.is_active).length;
    document.getElementById('activePorts').textContent = active;
    document.getElementById('inactivePorts').textContent = ports.length - active;
    
    const asia = ports.filter(p => p.region === 'Asia').length;
    const europe = ports.filter(p => p.region === 'Europe').length;
    const americas = ports.filter(p => 
        p.region === 'North America' || p.region === 'South America' || p.region === 'Americas'
    ).length;
    
    document.getElementById('asiaPorts').textContent = asia;
    document.getElementById('europePorts').textContent = europe;
    document.getElementById('americasPorts').textContent = americas;
}

async function showPortDetail(portId) {
    const modal = new bootstrap.Modal(document.getElementById('portDetailModal'));
    const port = allPorts.find(p => p.id === portId);
    
    if (port) {
        const statusBadge = port.is_active ? 
            '<span class="badge bg-success">Active</span>' : 
            '<span class="badge bg-danger">Inactive</span>';
        
        document.getElementById('detailPortName').textContent = port.port_name;
        document.getElementById('detailCode').textContent = port.code || 'N/A';
        document.getElementById('detailCountry').textContent = port.country_name;
        document.getElementById('detailRegion').textContent = port.region;
        document.getElementById('detailStatus').innerHTML = statusBadge;
        document.getElementById('detailCoords').textContent = `${port.latitude}, ${port.longitude}`;
        document.getElementById('editPortLink').href = `/admin/ports/${portId}/edit`;
        
        modal.show();
        
        document.getElementById('weatherLoading').style.display = 'block';
        document.getElementById('weatherContent').style.display = 'none';
        
        try {
            const response = await fetch(`/api/ports/${portId}`);
            const data = await response.json();
            
            if (data.success && data.data.weather) {
                const w = data.data.weather;
                document.getElementById('detailTemp').textContent = w.temperature;
                document.getElementById('detailCondition').textContent = w.weather_condition;
                document.getElementById('detailWind').textContent = w.wind_speed;
                document.getElementById('detailRain').textContent = w.rainfall;
                
                document.getElementById('weatherLoading').style.display = 'none';
                document.getElementById('weatherContent').style.display = 'block';
            }
        } catch (error) {
            document.getElementById('weatherLoading').innerHTML = '<p class="text-danger">Failed to load</p>';
        }
    }
}

function setupEventListeners() {
    document.getElementById('searchPort').addEventListener('input', filterPorts);
    document.getElementById('filterRegion').addEventListener('change', filterPorts);
    document.getElementById('filterStatus').addEventListener('change', filterPorts);
    document.getElementById('filterCountry').addEventListener('change', filterPorts);
    
    document.getElementById('viewMap').addEventListener('click', function() {
        document.getElementById('mapView').style.display = 'block';
        document.getElementById('listView').style.display = 'none';
        this.classList.add('active');
        document.getElementById('viewList').classList.remove('active');
        setTimeout(() => map.invalidateSize(), 100);
    });
    
    document.getElementById('viewList').addEventListener('click', function() {
        document.getElementById('mapView').style.display = 'none';
        document.getElementById('listView').style.display = 'block';
        this.classList.add('active');
        document.getElementById('viewMap').classList.remove('active');
        renderPortList(allPorts);
    });
    
    document.getElementById('portMapFullscreenBtn').addEventListener('click', function() {
        const mapCard = document.getElementById('portMapCard');
        mapCard.classList.toggle('map-fullscreen');
        setTimeout(() => map.invalidateSize(), 100);
    });
}

function filterPorts() {
    const search = document.getElementById('searchPort').value.toLowerCase();
    const region = document.getElementById('filterRegion').value;
    const status = document.getElementById('filterStatus').value;
    const country = document.getElementById('filterCountry').value;
    
    let filtered = allPorts.filter(port => {
        const matchSearch = port.port_name.toLowerCase().includes(search) || 
                           port.country_name.toLowerCase().includes(search);
        const matchRegion = !region || port.region === region || 
                           (region === 'Americas' && ['North America', 'South America', 'Americas'].includes(port.region));
        const matchStatus = !status || (status === 'active' && port.is_active) || (status === 'inactive' && !port.is_active);
        const matchCountry = !country || port.country_name === country;
        
        return matchSearch && matchRegion && matchStatus && matchCountry;
    });
    
    displayPortsOnMap(filtered);
    updateStats(filtered);
}

function renderPortList(ports) {
    const container = document.getElementById('portListContainer');
    
    if (ports.length === 0) {
        container.innerHTML = '<p class="text-center text-muted">No ports found</p>';
        return;
    }
    
    let html = '<div class="list-group">';
    ports.forEach(port => {
        const statusBadge = port.is_active ? 
            '<span class="badge bg-success">Active</span>' : 
            '<span class="badge bg-danger">Inactive</span>';
        
        html += `
            <a href="#" class="list-group-item list-group-item-action" onclick="event.preventDefault(); showPortDetail(${port.id})">
                <div class="d-flex w-100 justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1">${port.port_name}</h6>
                        <p class="mb-1">${port.country_name}</p>
                        <small class="text-muted">📍 ${port.latitude}, ${port.longitude}</small>
                    </div>
                    <div>
                        ${statusBadge}
                    </div>
                </div>
            </a>
        `;
    });
    html += '</div>';
    
    container.innerHTML = html;
}
</script>
@endpush
