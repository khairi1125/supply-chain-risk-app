<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Supply Chain Risk Intelligence')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        /*
         * ========================================
         * MODERN LIGHT THEME COLOR PALETTE
         * ========================================
         * 
         * PRIMARY THEME COLOR (Blue):
         * - Primary: #2563eb (Professional blue for sidebar, primary actions)
         * - Primary Dark: #1e40af (Hover states, emphasis)
         * - Primary Light: rgba(37, 99, 235, 0.2) (Subtle backgrounds, hover effects)
         * 
         * NEUTRAL COLORS:
         * - Background: #ffffff (White - main content areas)
         * - Background Secondary: #f9fafb (Light gray - alternating rows, subtle contrast)
         * - Border: #e5e7eb (Light gray borders)
         * - Border Dark: #d1d5db (Form controls, defined boundaries)
         * 
         * TEXT HIERARCHY:
         * - Primary Text: #111827 (Headings, emphasis)
         * - Body Text: #374151 (Main content)
         * - Secondary Text: #6b7280 (Labels, captions)
         * - Muted Text: #9ca3af (Placeholder text, disabled states)
         * 
         * SEMANTIC COLORS:
         * - Success: #10b981 (Green - success messages, positive indicators)
         * - Warning: #f59e0b (Amber - warnings, caution states)
         * - Danger: #ef4444 (Red - errors, destructive actions)
         * - Info: #3b82f6 (Blue - informational messages)
         * 
         * SHADOW SYSTEM:
         * - Subtle: rgba(0, 0, 0, 0.05) (Card resting state)
         * - Medium: rgba(0, 0, 0, 0.1) (Elevated components)
         * - Prominent: rgba(37, 99, 235, 0.15) (Hover states with blue tint)
         * ========================================
         */
        
        /* Base Theme Foundation */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #ffffff;
        }
        
        .sidebar {
            min-height: 100vh;
            height: 100vh;
            background: #242424ff;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
            width: 260px;
            flex-shrink: 0;
            position: fixed;
            top: 0;
            left: 0;
            overflow-y: auto;
            z-index: 1000;
        }
        
        /* Custom scrollbar for sidebar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }
        
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }
        
        /* Add margin to content to compensate for fixed sidebar */
        .content-wrapper {
            margin-left: 260px;
            background-color: #ffffff;
            min-height: 100vh;
            padding: 0;
        }
        
        .sidebar .nav-link {
            color: #ffffff;
            padding: 12px 20px;
            margin: 5px 10px;
            border-radius: 12px;
            transition: all 0.3s ease;
            white-space: nowrap;
            display: flex;
            align-items: center;
            font-weight: 500;
        }
        
        .sidebar .nav-link:hover {
            background-color: rgba(37, 99, 235, 0.2);
            color: #fff;
            transform: translateX(5px);
            box-shadow: none;
        }
        
        .sidebar .nav-link.active {
            background: #1e40af;
            color: #fff;
            box-shadow: none;
            font-weight: 600;
            border-left: 4px solid #ffffff;
        }
        
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        
        .navbar {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            background: #ffffff !important;
            position: sticky;
            top: 0;
            z-index: 999;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .navbar .navbar-brand {
            color: #333333 !important;
            font-weight: 600;
        }
        
        .navbar .nav-link {
            color: #333333 !important;
        }
        
        .navbar .dropdown-menu {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .navbar .dropdown-item {
            color: #333333;
        }
        
        .navbar .dropdown-item:hover {
            background-color: rgba(37, 99, 235, 0.1);
            color: #2563eb;
        }
        
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background-color: #ffffff;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
            border-color: #2563eb;
        }
        
        .card-header {
            background-color: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            color: #111827;
        }
        
        .card-body {
            color: #374151;
        }
        
        .card-footer {
            background-color: #f9fafb;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
        }
        
        .brand-logo {
            font-size: 1.2rem;
            font-weight: 700;
            color: #fff;
            padding: 25px 20px;
            text-align: center;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
            text-shadow: none;
        }
        
        .brand-logo i {
            color: #ffffff;
        }
        
        /* Text Color Hierarchy */
        h1, h2, h3, h4, h5, h6 {
            color: #111827 !important;
        }
        
        p, span, div {
            color: #374151;
        }
        
        .text-muted {
            color: #9ca3af !important;
        }
        
        .text-primary {
            color: #2563eb !important;
        }
        
        .text-success {
            color: #10b981 !important;
        }
        
        .text-warning {
            color: #f59e0b !important;
        }
        
        .text-danger {
            color: #ef4444 !important;
        }
        
        .text-info {
            color: #3b82f6 !important;
        }
        
        /* Table styling */
        .table {
            color: #374151;
            background-color: #ffffff;
        }
        
        .table thead th {
            background-color: #f9fafb;
            color: #111827;
            border-bottom: 2px solid #2563eb;
            font-weight: 600;
        }
        
        .table tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }
        
        .table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .table tbody tr:hover {
            background-color: rgba(37, 99, 235, 0.05);
        }
        
        /* Alert styling */
        .alert {
            border: 1px solid #e5e7eb;
            border-left: 4px solid;
        }
        
        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            color: #065f46;
            border-color: #10b981;
            border-left-color: #10b981;
        }
        
        .alert-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: #991b1b;
            border-color: #ef4444;
            border-left-color: #ef4444;
        }
        
        .alert-warning {
            background-color: rgba(245, 158, 11, 0.1);
            color: #92400e;
            border-color: #f59e0b;
            border-left-color: #f59e0b;
        }
        
        .alert-info {
            background-color: rgba(59, 130, 246, 0.1);
            color: #1e3a8a;
            border-color: #3b82f6;
            border-left-color: #3b82f6;
        }
        
        /* Button styling */
        .btn-primary {
            background: #2563eb;
            border: none;
            color: #ffffff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        
        .btn-primary:hover {
            background: #1e40af;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        
        .btn-secondary {
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            color: #374151;
        }
        
        .btn-secondary:hover {
            background-color: #f9fafb;
            border-color: #2563eb;
            color: #2563eb;
        }
        
        /* Form styling */
        .form-control {
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            color: #374151;
            border-radius: 8px;
        }
        
        .form-control:focus {
            background-color: #ffffff;
            border-color: #2563eb;
            color: #374151;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .form-control::placeholder {
            color: #9ca3af;
        }
        
        /* List group styling */
        .list-group-item {
            background-color: #ffffff;
            color: #374151;
            border-color: #e5e7eb;
        }
        
        .list-group-item:hover {
            background-color: rgba(37, 99, 235, 0.05);
            border-color: #2563eb;
        }
        
        .list-group-item.active {
            background-color: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
        }
        
        .list-group-item-action {
            color: #374151;
        }
        
        .list-group-item-action:hover {
            background-color: rgba(37, 99, 235, 0.05);
            color: #2563eb;
        }
        
        /* Loading states and spinners */
        .spinner-border {
            color: #2563eb;
            border-color: #2563eb;
            border-right-color: transparent;
        }
        
        .loading-overlay {
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(4px);
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        
        .progress-bar {
            background-color: #2563eb;
        }
        
        /* Badge styling */
        .badge {
            border-radius: 6px;
            font-weight: 500;
            padding: 4px 8px;
        }
        
        .badge-primary {
            background-color: #2563eb;
            color: #ffffff;
        }
        
        .badge-success {
            background-color: #10b981;
            color: #ffffff;
        }
        
        .badge-warning {
            background-color: #f59e0b;
            color: #ffffff;
        }
        
        .badge-danger {
            background-color: #ef4444;
            color: #ffffff;
        }
        
        .badge-info {
            background-color: #3b82f6;
            color: #ffffff;
        }
        
        /* Modal styling */
        .modal-content {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .modal-header {
            background-color: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            color: #111827;
            border-radius: 16px 16px 0 0;
        }
        
        .modal-footer {
            background-color: #f9fafb;
            border-top: 1px solid #e5e7eb;
            border-radius: 0 0 16px 16px;
        }
        
        .modal-body {
            color: #374151;
        }
        
        /* Dropdown styling */
        .dropdown-menu {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .dropdown-item {
            color: #374151;
            transition: all 0.2s ease;
        }
        
        .dropdown-item:hover {
            background-color: rgba(37, 99, 235, 0.1);
            color: #2563eb;
        }
        
        .dropdown-divider {
            border-color: #e5e7eb;
        }
        
        /* Tooltip and Popover styling */
        .tooltip-inner {
            background-color: #111827;
            color: #ffffff;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .popover {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .popover-header {
            background-color: #f9fafb;
            color: #111827;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .popover-body {
            color: #374151;
        }
        
        /* Breadcrumb styling */
        .breadcrumb {
            background-color: transparent;
        }
        
        .breadcrumb-item {
            color: #6b7280;
        }
        
        .breadcrumb-item.active {
            color: #111827;
        }
        
        .breadcrumb-item a {
            color: #2563eb;
            text-decoration: none;
        }
        
        .breadcrumb-item a:hover {
            color: #1e40af;
            text-decoration: underline;
        }
        
        /* Pagination styling */
        .pagination {
            margin: 0;
        }
        
        .page-link {
            color: #2563eb;
            background-color: #ffffff;
            border-color: #e5e7eb;
        }
        
        .page-link:hover {
            color: #1e40af;
            background-color: rgba(37, 99, 235, 0.05);
            border-color: #2563eb;
        }
        
        .page-item.active .page-link {
            background-color: #2563eb;
            border-color: #2563eb;
            color: #ffffff;
        }
        
        .page-item.disabled .page-link {
            color: #9ca3af;
            background-color: #f9fafb;
            border-color: #e5e7eb;
        }
        
        /* Input group styling */
        .input-group-text {
            background-color: #f9fafb;
            border: 1px solid #d1d5db;
            color: #6b7280;
        }
        
        /* Select styling */
        .form-select {
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            color: #374151;
            border-radius: 8px;
        }
        
        .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        /* Checkbox and Radio styling */
        .form-check-input {
            border-color: #d1d5db;
        }
        
        .form-check-input:checked {
            background-color: #2563eb;
            border-color: #2563eb;
        }
        
        .form-check-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        /* Accessibility - Focus indicators */
        *:focus-visible {
            outline: 2px solid #2563eb;
            outline-offset: 2px;
        }
        
        button:focus-visible,
        a:focus-visible,
        input:focus-visible,
        select:focus-visible,
        textarea:focus-visible {
            outline: 2px solid #2563eb;
            outline-offset: 2px;
        }
        
        /* Responsive Design - Mobile (< 768px) */
        @media (max-width: 767px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .content-wrapper {
                margin-left: 0;
            }
            
            .sidebar .nav-link {
                padding: 14px 20px;
                font-size: 16px;
            }
            
            .brand-logo {
                padding: 20px;
                font-size: 1.1rem;
            }
            
            .card {
                border-radius: 12px;
            }
            
            .navbar {
                padding: 10px 15px;
            }
        }
        
        /* Responsive Design - Tablet (768px - 1024px) */
        @media (min-width: 768px) and (max-width: 1024px) {
            .sidebar {
                width: 200px;
            }
            
            .content-wrapper {
                margin-left: 200px;
            }
            
            .sidebar .nav-link {
                padding: 10px 15px;
                font-size: 14px;
            }
            
            .sidebar .nav-link i {
                width: 18px;
                margin-right: 8px;
            }
            
            .brand-logo {
                font-size: 1rem;
                padding: 20px 15px;
            }
        }
        
        /* Reduced Motion Support */
        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }
        
        /* High Contrast Mode Support */
        @media (prefers-contrast: high) {
            .card {
                border-width: 2px;
            }
            
            .btn {
                border-width: 2px;
            }
            
            .form-control {
                border-width: 2px;
            }
        }
        
        /* Dashboard specific styling */
        .dashboard-stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
        }
        
        .dashboard-stat-card:hover {
            background: linear-gradient(135deg, #ffffff 0%, rgba(37, 99, 235, 0.05) 100%);
        }
        
        .stat-icon {
            color: #2563eb;
            font-size: 2rem;
        }
        
        /* Map components styling */
        .leaflet-popup-content-wrapper {
            background-color: #ffffff;
            color: #374151;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .leaflet-popup-tip {
            background-color: #ffffff;
        }
        
        .map-control-panel {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 15px;
        }
        
        /* Chart styling */
        .chart-container {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 20px;
        }
        
        /* Data table specific styling */
        .datatable-wrapper {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 20px;
        }
        
        /* News card styling */
        .news-card {
            border-left: 4px solid #2563eb;
            transition: all 0.3s ease;
        }
        
        .news-card:hover {
            border-left-color: #1e40af;
            box-shadow: 0 8px 16px rgba(37, 99, 235, 0.2);
        }
        
        /* Risk indicator styling */
        .risk-low {
            color: #10b981;
            background-color: rgba(16, 185, 129, 0.1);
            border-color: #10b981;
        }
        
        .risk-medium {
            color: #f59e0b;
            background-color: rgba(245, 158, 11, 0.1);
            border-color: #f59e0b;
        }
        
        .risk-high {
            color: #ef4444;
            background-color: rgba(239, 68, 68, 0.1);
            border-color: #ef4444;
        }
        
        /* Weather widget styling */
        .weather-widget {
            background: linear-gradient(135deg, #ffffff 0%, rgba(37, 99, 235, 0.05) 100%);
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            padding: 20px;
        }
        
        .weather-icon {
            color: #2563eb;
            font-size: 3rem;
        }
        
        /* Currency widget styling */
        .currency-pair {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            transition: all 0.3s ease;
        }
        
        .currency-pair:hover {
            background-color: rgba(37, 99, 235, 0.05);
            border-color: #2563eb;
        }
        
        /* Admin panel styling */
        .admin-card {
            border-top: 3px solid #2563eb;
        }
        
        .admin-action-btn {
            color: #2563eb;
            transition: all 0.2s ease;
        }
        
        .admin-action-btn:hover {
            color: #1e40af;
            transform: scale(1.1);
        }
        
        /* Scrollbar styling for main content */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f9fafb;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Sidebar (Fixed) -->
    <div class="sidebar">
        <div class="brand-logo">
            <i class="fas fa-ship-fast"></i> Supply Chain Risk
        </div>
            
        <nav class="nav flex-column mt-4">
            <a class="nav-link {{ request()->routeIs('dashboard.index') ? 'active' : '' }}" href="{{ route('dashboard.index') }}">
                <i class="fas fa-dashboard"></i> Dashboard
            </a>
            <a class="nav-link {{ request()->routeIs('country.monitor') ? 'active' : '' }}" href="{{ route('country.monitor') }}">
                <i class="fas fa-globe"></i> Country Monitor
            </a>
            <a class="nav-link {{ request()->routeIs('port.map') ? 'active' : '' }}" href="{{ route('port.map') }}">
                <i class="fas fa-ship"></i> Port Map
            </a>
            <a class="nav-link {{ request()->routeIs('weather.map') ? 'active' : '' }}" href="{{ route('weather.map') }}">
                <i class="fas fa-cloud-sun-rain"></i> Weather Map
            </a>
            <a class="nav-link {{ request()->routeIs('currency') ? 'active' : '' }}" href="{{ route('currency') }}">
                <i class="fas fa-coins"></i> Currency
            </a>
            <a class="nav-link {{ request()->routeIs('news') ? 'active' : '' }}" href="{{ route('news') }}">
                <i class="bi bi-newspaper"></i> News Intelligence
            </a>
            <a class="nav-link {{ request()->routeIs('compare') ? 'active' : '' }}" href="{{ route('compare') }}">
                <i class="fas fa-code-compare"></i> Compare Countries
            </a>
            <a class="nav-link {{ request()->routeIs('watchlist.index') ? 'active' : '' }}" href="{{ route('watchlist.index') }}">
                <i class="fas fa-star"></i> My Watchlist
            </a>
        </nav>
    </div>
    
    <!-- Main Content Wrapper (with left margin for fixed sidebar) -->
    <div class="content-wrapper">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1">@yield('page-title', 'Dashboard')</span>
                    
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i> {{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">

                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <!-- Page Content with Padding -->
            <div style="padding: 20px;">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif
                
                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif
                
                @yield('content')
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <!-- jQuery (for AJAX) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <script>
        // Setup AJAX CSRF Token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        // Mobile sidebar toggle functionality
        (function() {
            // Create mobile menu button if on mobile viewport
            if (window.innerWidth < 768) {
                const navbar = document.querySelector('.navbar .container-fluid');
                const sidebar = document.querySelector('.sidebar');
                
                // Create toggle button
                const toggleBtn = document.createElement('button');
                toggleBtn.className = 'btn btn-link d-md-none';
                toggleBtn.innerHTML = '<i class="fas fa-bars"></i>';
                toggleBtn.style.cssText = 'color: #333; font-size: 1.5rem; padding: 0; border: none;';
                
                // Insert at start of navbar
                navbar.insertBefore(toggleBtn, navbar.firstChild);
                
                // Toggle sidebar on click
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                });
                
                // Close sidebar when clicking outside
                document.addEventListener('click', function(e) {
                    if (sidebar.classList.contains('show') && 
                        !sidebar.contains(e.target) && 
                        !toggleBtn.contains(e.target)) {
                        sidebar.classList.remove('show');
                    }
                });
            }
        })();
    </script>
    
    @stack('scripts')
</body>
</html>
