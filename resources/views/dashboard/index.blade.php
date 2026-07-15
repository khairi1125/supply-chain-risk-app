@extends('layouts.app')

@section('title', 'Dashboard - Supply Chain Risk Intelligence')
@section('page-title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="mb-3">
                        <i class="fas fa-wave-square text-primary"></i> 
                        Welcome back, {{ auth()->user()->name }}!
                    </h2>
                    <p class="text-muted mb-0">
                        Monitor global supply chain risks in real-time with data from multiple sources.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Countries Monitored</h6>
                            <h2 class="mb-0 mt-2">{{ $stats['total_countries'] }}</h2>
                        </div>
                        <div>
                            <i class="fas fa-globe fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Active Ports</h6>
                            <h2 class="mb-0 mt-2">{{ $stats['total_ports'] }}</h2>
                        </div>
                        <div>
                            <i class="fas fa-anchor fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Risk Alerts</h6>
                            <h2 class="mb-0 mt-2">{{ $stats['high_risk_countries'] }}</h2>
                        </div>
                        <div>
                            <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">News Updates</h6>
                            <h2 class="mb-0 mt-2">{{ $stats['total_news'] }}</h2>
                        </div>
                        <div>
                            <i class="fas fa-newspaper fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Section -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line text-primary"></i> 
                        Global Risk Trends (Last 30 Days)
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="riskTrendChart" height="80"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie text-success"></i> 
                        Risk Distribution
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="riskDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent News & High Risk Countries -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-newspaper text-info"></i> 
                        Recent News
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Supply Chain Disruption in Asia</h6>
                                <small>2h ago</small>
                            </div>
                            <p class="mb-1 small text-muted">Major port delays affecting global shipping...</p>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Currency Volatility Alert</h6>
                                <small>5h ago</small>
                            </div>
                            <p class="mb-1 small text-muted">Emerging markets facing exchange rate pressure...</p>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">Weather Impact on Logistics</h6>
                                <small>8h ago</small>
                            </div>
                            <p class="mb-1 small text-muted">Severe weather conditions affecting transport...</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-circle text-danger"></i> 
                        High Risk Countries
                    </h5>
                </div>
                <div class="card-body">
                    @if($highRiskCountries->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Country</th>
                                    <th>Risk Score</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($highRiskCountries as $country)
                                <tr>
                                    <td>
                                        <img src="{{ $country->flag_url }}" alt="{{ $country->name }}" style="width: 20px; height: 15px; margin-right: 5px;">
                                        {{ $country->name }}
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $country->total_score >= 76 ? 'danger' : 'warning' }}">
                                            {{ number_format($country->total_score, 1) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-{{ $country->total_score >= 76 ? 'danger' : 'warning' }}">
                                            {{ ucfirst($country->risk_level) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p class="text-muted text-center">No high risk countries at the moment.</p>
                    <p class="text-center">
                        <a href="{{ route('country.monitor') }}" class="btn btn-sm btn-primary">
                            View All Countries
                        </a>
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Risk Trend Chart
    const ctxLine = document.getElementById('riskTrendChart').getContext('2d');
    new Chart(ctxLine, {
        type: 'line',
        data: {
            labels: ['Day 1', 'Day 5', 'Day 10', 'Day 15', 'Day 20', 'Day 25', 'Day 30'],
            datasets: [{
                label: 'Average Risk Score',
                data: [45, 52, 48, 55, 60, 58, 62],
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
    
    // Risk Distribution Chart
    const ctxPie = document.getElementById('riskDistributionChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: ['Critical', 'High', 'Medium', 'Low'],
            datasets: [{
                data: [12, 28, 45, 110],
                backgroundColor: ['#dc3545', '#fd7e14', '#ffc107', '#28a745']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
@endpush
