@extends('layouts.admin')

@section('title', 'Port Details')
@section('page-title', 'Port Details')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.ports.index') }}">Ports</a></li>
            <li class="breadcrumb-item active">{{ $port->port_name }}</li>
        </ol>
    </nav>

    <div class="row">
        {{-- Port Information --}}
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-anchor"></i> Port Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" 
                             style="width: 100px; height: 100px;">
                            <i class="fas fa-anchor fa-3x text-info"></i>
                        </div>
                    </div>
                    
                    <table class="table table-sm">
                        <tr>
                            <th style="width: 40%;">ID</th>
                            <td>{{ $port->id }}</td>
                        </tr>
                        <tr>
                            <th>Port Name</th>
                            <td><strong>{{ $port->port_name }}</strong></td>
                        </tr>
                        <tr>
                            <th>Port Code</th>
                            <td>
                                @if($port->code)
                                    <code>{{ $port->code }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Country</th>
                            <td>
                                {{ $port->country_name }}
                                <span class="badge bg-primary">{{ $port->country_code }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Region</th>
                            <td>
                                <span class="badge bg-info">{{ $port->region }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Port Type</th>
                            <td>
                                @if($port->port_type)
                                    {{ $port->port_type }}
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($port->is_active)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> Active
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-ban"></i> Inactive
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Created</th>
                            <td>
                                {{ $port->created_at->format('M d, Y H:i') }}
                                <br>
                                <small class="text-muted">
                                    ({{ $port->created_at->diffForHumans() }})
                                </small>
                            </td>
                        </tr>
                        <tr>
                            <th>Last Updated</th>
                            <td>
                                {{ $port->updated_at->format('M d, Y H:i') }}
                                <br>
                                <small class="text-muted">
                                    ({{ $port->updated_at->diffForHumans() }})
                                </small>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="d-grid gap-2 mt-3">
                        <a href="{{ route('admin.ports.edit', $port) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Port
                        </a>
                        <a href="{{ route('admin.ports.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Location Map --}}
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-map-marked-alt"></i> Location
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-3">
                        <tr>
                            <th style="width: 30%;">Latitude</th>
                            <td><code>{{ number_format($port->latitude, 7) }}</code></td>
                        </tr>
                        <tr>
                            <th>Longitude</th>
                            <td><code>{{ number_format($port->longitude, 7) }}</code></td>
                        </tr>
                        <tr>
                            <th>Coordinates</th>
                            <td>
                                <code>{{ number_format($port->latitude, 7) }}, {{ number_format($port->longitude, 7) }}</code>
                                <button class="btn btn-sm btn-outline-primary ms-2" 
                                        onclick="copyCoordinates()"
                                        title="Copy coordinates">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </td>
                        </tr>
                    </table>
                    
                    {{-- Map Container --}}
                    <div id="map" style="height: 400px; border-radius: 8px;"></div>
                    
                    <div class="mt-3">
                        <a href="https://www.google.com/maps?q={{ $port->latitude }},{{ $port->longitude }}" 
                           target="_blank" 
                           class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-external-link-alt"></i> Open in Google Maps
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
$(document).ready(function() {
    // Initialize map
    const map = L.map('map').setView([{{ $port->latitude }}, {{ $port->longitude }}], 10);
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(map);
    
    // Add marker for port
    const marker = L.marker([{{ $port->latitude }}, {{ $port->longitude }}]).addTo(map);
    
    // Add popup
    marker.bindPopup(`
        <div class="text-center">
            <strong>{{ $port->port_name }}</strong><br>
            <small>{{ $port->country_name }}</small><br>
            <small class="text-muted">{{ number_format($port->latitude, 4) }}, {{ number_format($port->longitude, 4) }}</small>
        </div>
    `).openPopup();
});

// Copy coordinates to clipboard
function copyCoordinates() {
    const coords = '{{ number_format($port->latitude, 7) }}, {{ number_format($port->longitude, 7) }}';
    navigator.clipboard.writeText(coords).then(function() {
        alert('Coordinates copied to clipboard!');
    }, function(err) {
        alert('Failed to copy coordinates');
    });
}
</script>
@endpush
