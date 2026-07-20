<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel - Supply Chain Risk')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .sidebar {
            min-height: 100vh;
            height: 100vh;
            background: linear-gradient(180deg, #4a00e0 0%, #8e2de2 50%, #7c3aed 100%);
            box-shadow: 4px 0 20px rgba(124, 58, 237, 0.4);
            width: 260px;
            flex-shrink: 0;
            position: fixed;
            top: 0;
            left: 0;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .sidebar .nav-link {
            color: #ffffff;
            padding: 12px 20px;
            margin: 5px 10px;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .sidebar .nav-link:hover {
            background-color: rgba(124, 58, 237, 0.3);
            color: #fff;
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.2);
        }
        
        .sidebar .nav-link.active {
            background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%);
            color: #fff;
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.4);
            font-weight: 600;
        }
        
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        
        .navbar {
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            background: #ffffff !important;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .navbar .navbar-brand {
            color: #1f2937 !important;
            font-weight: 600;
        }
        
        .navbar .nav-link {
            color: #374151 !important;
        }
        
        .navbar .dropdown-menu {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }
        
        .navbar .dropdown-item {
            color: #374151;
        }
        
        .navbar .dropdown-item:hover {
            background-color: #f3f4f6;
            color: #1f2937;
        }
        
        .content-wrapper {
            background-color: #f8f9fa;
            min-height: 100vh;
            margin-left: 260px;
            width: calc(100% - 260px);
            padding: 0;
        }
        
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }
        
        .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            color: #1f2937;
        }
        
        .card-body {
            color: #374151;
        }
        
        .brand-logo {
            font-size: 1.2rem;
            font-weight: 700;
            color: #fff;
            padding: 25px 20px;
            text-align: center;
            border-bottom: 2px solid rgba(124, 58, 237, 0.3);
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        
        .brand-logo i {
            color: #a78bfa;
        }
        
        .badge-admin {
            background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%);
            color: white;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.7rem;
            margin-left: 8px;
            box-shadow: 0 2px 8px rgba(124, 58, 237, 0.4);
        }
        
        /* Text colors for light theme */
        h1, h2, h3, h4, h5, h6 {
            color: #1f2937 !important;
        }
        
        p, span, div {
            color: #374151;
        }
        
        .text-muted {
            color: #6b7280 !important;
        }
        
        .text-primary {
            color: #7c3aed !important;
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
            color: #06b6d4 !important;
        }
        
        /* Table styling */
        .table {
            color: #374151;
        }
        
        .table thead th {
            background-color: #f9fafb;
            color: #1f2937;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .table tbody tr {
            border-bottom: 1px solid #f3f4f6;
        }
        
        .table tbody tr:hover {
            background-color: #f9fafb;
        }
        
        /* Alert styling */
        .alert {
            border: 1px solid #e5e7eb;
        }
        
        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .alert-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        /* Button styling */
        .btn-primary {
            background: linear-gradient(135deg, #7c3aed 0%, #8b5cf6 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #6d28d9 0%, #7c3aed 100%);
            box-shadow: 0 6px 20px rgba(124, 58, 237, 0.5);
        }
        
        /* Form styling */
        .form-control {
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            color: #1f2937;
        }
        
        .form-control:focus {
            background-color: #ffffff;
            border-color: #667eea;
            color: #1f2937;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .form-control::placeholder {
            color: #9ca3af;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="brand-logo">
            <i class="fas fa-shield-halved"></i> Admin Panel
        </div>
        
        <nav class="nav flex-column mt-4">
            <a class="nav-link {{ request()->routeIs('admin.index') ? 'active' : '' }}" href="{{ route('admin.index') }}">
                <i class="fas fa-dashboard"></i> Dashboard
            </a>
            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                <i class="fas fa-users"></i> User Management
            </a>
            <a class="nav-link {{ request()->routeIs('admin.ports.index') || request()->routeIs('admin.ports.create') || request()->routeIs('admin.ports.edit') || request()->routeIs('admin.ports.show') ? 'active' : '' }}" href="{{ route('admin.ports.index') }}">
                <i class="fas fa-anchor"></i> Port Management
            </a>
            <a class="nav-link {{ request()->routeIs('admin.ports.map') ? 'active' : '' }}" href="{{ route('admin.ports.map') }}">
                <i class="fas fa-map-marked-alt"></i> Port Map
            </a>
            <a class="nav-link {{ request()->routeIs('admin.articles.*') ? 'active' : '' }}" href="{{ route('admin.articles.index') }}">
                <i class="fas fa-newspaper"></i> News Management
            </a>
            
            <div class="dropdown-divider mx-3 my-3" style="border-color: rgba(255,255,255,0.2);"></div>
        </nav>
    </div>
    
    <!-- Main Content Wrapper -->
    <div class="content-wrapper">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1">
                    @yield('page-title', 'Admin Dashboard')
                    <span class="badge-admin">ADMIN</span>
                </span>
            
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-shield"></i> {{ auth()->user()->name }}
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
        
        <!-- Content -->
        <div class="p-4">
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
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery (for AJAX) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <script>
        // Setup AJAX CSRF Token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>
