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
                <div id="portDetailLoading" class="text-center py-4">
                    <div class="spinner-border" role="status"></div>
                </div>
                <div id="portDetailContent" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Port Information</h6>
                            <p><strong>Port Name:</strong> <span id="detailPortName"></span></p>
                            <p><strong>Country:</strong> <span id="detailCountry"></span></p>
                            <p><strong>Region:</strong> <span id="detailRegion"></span></p>
                            <p><strong>Coordinates:</strong> <span id="detailCoords"></span></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Current Weather</h6>
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
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let map;
let markers = [];
let allPorts = [];

// Initialize map
document.addEventListener('DOMContentLoaded', function() {
    initMap();
    loadPorts();
    setupEventListeners();
});

function initMap() {
    map = L.map('portMap').setView([20, 0], 2);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 18,
    }).addTo(map);
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
    markers.forEach(marker => map.removeLayer(marker));
    markers = [];
    
    ports.forEach(port => {
        const marker = L.marker([port.latitude, port.longitude])
            .addTo(map)
            .bindPopup(`
                <div class="text-center">
                    <strong>${port.port_name}</strong><br>
                    <small>${port.country_name}</small><br>
                    <button class="btn btn-sm btn-primary mt-2" onclick="showPortDetail(${port.id})">
                        View Details
                    </button>
                </div>
            `);
        
        markers.push(marker);
    });
}

function updateStats(ports) {
    document.getElementById('totalPorts').textContent = ports.length;
    document.getElementById('asiaPorts').textContent = ports.filter(p => p.region === 'Asia').length;
    document.getElementById('europePorts').textContent = ports.filter(p => p.region === 'Europe').length;
    document.getElementById('americasPorts').textContent = ports.filter(p => p.region === 'Americas').length;
}

async function showPortDetail(portId) {
    const modal = new bootstrap.Modal(document.getElementById('portDetailModal'));
    modal.show();
    
    document.getElementById('portDetailLoading').style.display = 'block';
    document.getElementById('portDetailContent').style.display = 'none';
    
    try {
        const response = await fetch(`/api/ports/${portId}`);
        const data = await response.json();
        
        if (data.success) {
            const port = data.data.port;
            const weather = data.data.weather;
            
            document.getElementById('detailPortName').textContent = port.port_name;
            document.getElementById('detailCountry').textContent = port.country_name_full;
            document.getElementById('detailRegion').textContent = port.region;
            document.getElementById('detailCoords').textContent = `${port.latitude}, ${port.longitude}`;
            document.getElementById('detailTemp').textContent = weather.temperature;
            document.getElementById('detailCondition').textContent = weather.weather_condition;
            document.getElementById('detailWind').textContent = weather.wind_speed;
            document.getElementById('detailRain').textContent = weather.rainfall;
            
            document.getElementById('portDetailLoading').style.display = 'none';
            document.getElementById('portDetailContent').style.display = 'block';
        }
    } catch (error) {
        console.error('Error loading port details:', error);
        alert('Failed to load port details');
        modal.hide();
    }
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
        const matchRegion = !region || port.region === region;
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
