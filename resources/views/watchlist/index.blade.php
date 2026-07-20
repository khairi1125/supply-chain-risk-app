@extends('layouts.app')

@section('title', 'My Watchlist - Supply Chain Risk Intelligence')

@section('page-title', 'My Watchlist')

@push('styles')
<style>
    /* Modern Light Theme for My Watchlist */
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
    .page-header-watchlist {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border-radius: 24px;
        padding: 2.5rem;
        margin-bottom: 2rem;
        color: white;
        box-shadow: 0 20px 60px rgba(59, 130, 246, 0.3);
        position: relative;
        overflow: hidden;
    }
    
    .page-header-watchlist::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
        border-radius: 50%;
    }
    
    .page-header-watchlist h2 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        position: relative;
        z-index: 1;
        color: white;
    }
    
    .page-header-watchlist p {
        font-size: 1.1rem;
        opacity: 0.95;
        margin: 0;
        position: relative;
        z-index: 1;
    }
    
    /* Summary Cards */
    .summary-card {
        border-radius: 16px;
        border: 2px solid var(--gray-100);
        padding: 1.75rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        background: white;
        transition: all 0.3s ease;
        margin-bottom: 1.5rem;
    }
    
    .summary-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
    
    .summary-card.total {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
        color: white;
    }
    
    .summary-card.low-risk {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border: none;
        color: white;
    }
    
    .summary-card.medium-risk {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        border: none;
        color: white;
    }
    
    .summary-card.high-risk {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        border: none;
        color: white;
    }
    
    .summary-card h3 {
        font-size: 2.25rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    
    .summary-card small {
        font-size: 0.95rem;
        font-weight: 600;
        opacity: 0.95;
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
        border-color: var(--primary-blue);
        box-shadow: 0 4px 20px rgba(59, 130, 246, 0.2);
        outline: none;
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
    
    /* Badges */
    .badge {
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.85rem;
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
    
    /* Buttons */
    .btn-primary {
        background: linear-gradient(135deg, var(--primary-blue) 0%, #2563eb 100%);
        border: none;
        border-radius: 16px;
        padding: 0.85rem 1.5rem;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.5);
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    }
    
    /* Cards */
    .card {
        border-radius: 16px;
        border: 2px solid var(--gray-200);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        background: white;
    }
    
    .card-header {
        background: var(--gray-100);
        border-bottom: 2px solid var(--gray-200);
        border-radius: 16px 16px 0 0;
        padding: 1.25rem 1.5rem;
    }
    
    /* Empty State */
    .empty-state-card {
        border-radius: 20px;
        border: 2px dashed var(--gray-200);
        padding: 3rem;
        text-align: center;
        background: var(--gray-50);
    }
    
    .empty-state-card i {
        font-size: 4rem;
        color: var(--gray-400);
        margin-bottom: 1.5rem;
    }
    
    /* Breadcrumb */
    .breadcrumb {
        background: white;
        border-radius: 12px;
        padding: 0.75rem 1.25rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        border: 1px solid var(--gray-200);
    }
    
    .breadcrumb-item a {
        color: var(--primary-blue);
        text-decoration: none;
        font-weight: 600;
    }
    
    .breadcrumb-item.active {
        color: var(--gray-600);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .page-header-watchlist {
            padding: 1.5rem;
        }
        
        .page-header-watchlist h2 {
            font-size: 1.75rem;
        }
        
        .filter-section {
            padding: 1.25rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    {{-- Breadcrumb Navigation --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard.index') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">My Watchlist</li>
        </ol>
    </nav>

    {{-- Page Header --}}
    <div class="page-header-watchlist d-flex justify-content-between align-items-center">
        <div>
            <h2>⭐ My Watchlist</h2>
            <p>Monitor your favorite countries in one place</p>
        </div>
        <button class="btn btn-light" id="addCountryBtn" style="font-weight: 600; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);">
            <i class="fas fa-plus"></i> Add Country
        </button>
    </div>

    @if(count($watchlists) === 0)
        {{-- Empty State --}}
        @include('watchlist.partials.empty-state')
    @else
        {{-- Summary Cards --}}
        @include('watchlist.partials.summary-cards', ['stats' => $summaryStats])

        {{-- Filters and Search --}}
        @include('watchlist.partials.filters', ['regions' => $regions])

        {{-- Main Content Grid --}}
        <div class="row">
            <div class="col-lg-8">
                {{-- Watchlist Table --}}
                @include('watchlist.partials.watchlist-table', ['watchlists' => $watchlists])
            </div>
            <div class="col-lg-4">
                {{-- Quick Statistics --}}
                @include('watchlist.partials.quick-stats', ['stats' => $summaryStats])

                {{-- Risk Distribution Chart --}}
                @include('watchlist.partials.risk-chart', ['stats' => $summaryStats])

                {{-- Recent Activity --}}
                @include('watchlist.partials.recent-activity', ['activity' => $recentActivity])
            </div>
        </div>
    @endif
</div>

{{-- Add Country Modal --}}
@include('watchlist.modals.add-country', ['countries' => $countries])
@endsection

@push('scripts')
<script src="{{ asset('js/watchlist.js') }}"></script>
@endpush
