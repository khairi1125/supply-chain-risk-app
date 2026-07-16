@extends('layouts.app')

@section('title', 'Port Map')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0">🚢 Global Port Map</h2>
            <p class="text-muted">Interactive map showing major shipping ports worldwide</p>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" id="searchPort" placeholder="Search ports or countries...">
            </div>
        </div>
        <div class="col-md-3">
            <select class="form-select" id="filterRegion">
                <option value="">All Regions</option>
                <option value="Asia">Asia</option>
                <option value="Europe">Europe</option>
                <option value="Americas">Americas</option>
                <option value="Africa">Africa</option>
                <option value="Oceania">Oceania</option>
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

    <!-- Map View -->
    <div id="mapView">
        <div class="card">
            <div class="card-body p-0">
                <div id="portMap" style="height: 600px; width: 100%;"></div>
            </div>
        </div>
    </div>

    <!-- List View -->
    <div id="listView" style="display: none;">
        <div class="card">
            <div class="card-body">
                <div id="portListContainer">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading ports...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Port Stats -->
    <div class="row mt-3">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h3 class="mb-0" id="totalPorts">-</h3>
                    <small>Total Ports</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h3 class="mb-0" id="asiaPorts">-</h3>
                    <small>Asia Pacific</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h3 class="mb-0" id="europePorts">-</h3>
                    <small>Europe</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h3 class="mb-0" id="americasPorts">-</h3>
                    <small>Americas</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Port Detail Modal -->
<div class="modal fade" id="portDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="portModalTitle">Port Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="portDetailContent">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Port Information</h6>
                            <p><strong>Port Name:</strong> <span id="detailPortName">-</span></p>
                            <p><strong>Country:</strong> <span id="detailCountry">-</span></p>
                            <p><strong>Region:</strong> <span id="detailRegion">-</span></p>
                            <p><strong>Coordinates:</strong> <span id="detailCoords">-</span></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Current Weather</h6>
                            <div id="weatherLoading" class="text-center py-3">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                                <p class="small text-muted mt-2">Loading weather data...</p>
                            </div>
                            <div id="weatherContent" style="display: none;">
                                <p><strong>Temperature:</strong> <span id="detailTemp"></span>°C</p>
                                <p><strong>Condition:</strong> <span id="detailCondition"></span></p>
                                <p><strong>Wind Speed:</strong> <span id="detailWind"></span> km/h</p>
                                <p><strong>Rainfall:</strong> <span id="detailRain"></span> mm</p>
                            </div>
                        </div>
                    </div>
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
<style>
    .leaflet-popup-content {
        min-width: 200px;
    }
    .port-marker {
        background-color: #007bff;
        border: 2px solid white;
        border-radius: 50%;
        width: 12px;
        height: 12px;
    }
    .marker-cluster-small {
        background-color: rgba(110, 204, 57, 0.6);
    }
    .marker-cluster-small div {
        background-color: rgba(110, 204, 57, 0.8);
    }
    .marker-cluster-medium {
        background-color: rgba(241, 211, 87, 0.6);
    }
    .marker-cluster-medium div {
        background-color: rgba(241, 211, 87, 0.8);
    }
    .marker-cluster-large {
        background-color: rgba(253, 156, 115, 0.6);
    }
    .marker-cluster-large div {
        background-color: rgba(253, 156, 115, 0.8);
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script>
let map;
let markerClusterGroup;
let allPorts = [];

// Initialize map
document.addEventListener('DOMContentLoaded', function() {
    initMap();
    loadPorts();
    setupEventListeners();
});

function initMap() {
    // Set max bounds to prevent scrolling to empty world copies
    const worldBounds = [
        [-90, -180], // Southwest corner
        [90, 180]    // Northeast corner
    ];
    
    map = L.map('portMap', {
        worldCopyJump: true,  // Jump to real world when crossing dateline
        maxBounds: worldBounds,
        maxBoundsViscosity: 0.5  // Soft boundary
    }).setView([20, 0], 2);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 18,
        noWrap: true,  // Prevent tile wrapping
    }).addTo(map);
    
    // Initialize marker cluster group
    markerClusterGroup = L.markerClusterGroup({
        chunkedLoading: true,
        maxClusterRadius: 50,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false,
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
        }
    } catch (error) {
        console.error('Error loading ports:', error);
    }
}

function displayPortsOnMap(ports) {
    // Clear existing markers
    markerClusterGroup.clearLayers();
    
    ports.forEach(port => {
        const marker = L.marker([port.latitude, port.longitude])
            .bindPopup(`
                <div class="text-center">
                    <strong>${port.port_name}</strong><br>
                    <small>${port.country_name}</small><br>
                    <small class="text-muted">${port.region}</small><br>
                    <button class="btn btn-sm btn-primary mt-2" onclick="showPortDetail(${port.id})">
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
    
    // Count by region - handle both "North America" and "South America"
    const asiaPorts = ports.filter(p => p.region === 'Asia').length;
    const europePorts = ports.filter(p => p.region === 'Europe').length;
    const americasPorts = ports.filter(p => 
        p.region === 'North America' || p.region === 'South America' || p.region === 'Americas'
    ).length;
    
    document.getElementById('asiaPorts').textContent = asiaPorts;
    document.getElementById('europePorts').textContent = europePorts;
    document.getElementById('americasPorts').textContent = americasPorts;
}

async function showPortDetail(portId) {
    const modal = new bootstrap.Modal(document.getElementById('portDetailModal'));
    
    // Find port data from cache (instant)
    const port = allPorts.find(p => p.id === portId);
    
    if (port) {
        // Show port info immediately
        document.getElementById('detailPortName').textContent = port.port_name;
        document.getElementById('detailCountry').textContent = port.country_name_full || port.country_name;
        document.getElementById('detailRegion').textContent = port.region;
        document.getElementById('detailCoords').textContent = `${port.latitude}, ${port.longitude}`;
        
        // Show modal immediately
        modal.show();
        
        // Show weather loading
        document.getElementById('weatherLoading').style.display = 'block';
        document.getElementById('weatherContent').style.display = 'none';
        
        // Fetch weather data in background
        loadWeatherData(portId);
    } else {
        // Fallback: fetch from API if not in cache
        modal.show();
        try {
            const response = await fetch(`/api/ports/${portId}`);
            const data = await response.json();
            
            if (data.success) {
                const port = data.data.port;
                const weather = data.data.weather;
                
                document.getElementById('detailPortName').textContent = port.port_name;
                document.getElementById('detailCountry').textContent = port.country_name_full || port.country_name;
                document.getElementById('detailRegion').textContent = port.region;
                document.getElementById('detailCoords').textContent = `${port.latitude}, ${port.longitude}`;
                
                displayWeatherData(weather);
            }
        } catch (error) {
            console.error('Error loading port details:', error);
            alert('Failed to load port details');
            modal.hide();
        }
    }
}

async function loadWeatherData(portId) {
    try {
        const response = await fetch(`/api/ports/${portId}`);
        const data = await response.json();
        
        if (data.success && data.data.weather) {
            displayWeatherData(data.data.weather);
        } else {
            showWeatherError();
        }
    } catch (error) {
        console.error('Error loading weather:', error);
        showWeatherError();
    }
}

function displayWeatherData(weather) {
    document.getElementById('detailTemp').textContent = weather.temperature;
    document.getElementById('detailCondition').textContent = weather.weather_condition;
    document.getElementById('detailWind').textContent = weather.wind_speed;
    document.getElementById('detailRain').textContent = weather.rainfall;
    
    document.getElementById('weatherLoading').style.display = 'none';
    document.getElementById('weatherContent').style.display = 'block';
}

function showWeatherError() {
    document.getElementById('weatherLoading').innerHTML = '<p class="small text-danger">Failed to load weather data</p>';
}

function setupEventListeners() {
    // Search
    document.getElementById('searchPort').addEventListener('input', filterPorts);
    document.getElementById('filterRegion').addEventListener('change', filterPorts);
    
    // View toggle
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
}

function filterPorts() {
    const searchTerm = document.getElementById('searchPort').value.toLowerCase();
    const region = document.getElementById('filterRegion').value;
    
    let filtered = allPorts.filter(port => {
        const matchSearch = port.port_name.toLowerCase().includes(searchTerm) || 
                           port.country_name.toLowerCase().includes(searchTerm);
        
        let matchRegion = true;
        if (region) {
            if (region === 'Americas') {
                matchRegion = port.region === 'North America' || port.region === 'South America' || port.region === 'Americas';
            } else {
                matchRegion = port.region === region;
            }
        }
        
        return matchSearch && matchRegion;
    });
    
    displayPortsOnMap(filtered);
    
    if (document.getElementById('listView').style.display !== 'none') {
        renderPortList(filtered);
    }
}

function renderPortList(ports) {
    const container = document.getElementById('portListContainer');
    
    if (ports.length === 0) {
        container.innerHTML = '<p class="text-center text-muted">No ports found</p>';
        return;
    }
    
    let html = '<div class="list-group">';
    ports.forEach(port => {
        html += `
            <a href="#" class="list-group-item list-group-item-action" onclick="event.preventDefault(); showPortDetail(${port.id})">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1">${port.port_name}</h6>
                    <small class="text-muted">${port.region}</small>
                </div>
                <p class="mb-1">${port.country_name}</p>
                <small class="text-muted">📍 ${port.latitude}, ${port.longitude}</small>
            </a>
        `;
    });
    html += '</div>';
    
    container.innerHTML = html;
}
</script>
@endpush
