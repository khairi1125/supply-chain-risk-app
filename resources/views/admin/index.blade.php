@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-danger">
                <div class="card-body">
                    <h2 class="mb-3">
                        <i class="fas fa-shield-halved text-danger"></i> 
                        Admin Control Panel
                    </h2>
                    <p class="text-muted mb-0">
                        Manage users, ports, articles, and system settings.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <i class="fas fa-users fa-3x text-primary mb-3"></i>
                    <h3 class="mb-2">125</h3>
                    <p class="text-muted mb-0">Total Users</p>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary mt-2">
                        Manage Users
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="fas fa-anchor fa-3x text-success mb-3"></i>
                    <h3 class="mb-2">1,247</h3>
                    <p class="text-muted mb-0">Total Ports</p>
                    <a href="{{ route('admin.ports.index') }}" class="btn btn-sm btn-outline-success mt-2">
                        Manage Ports
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="fas fa-newspaper fa-3x text-info mb-3"></i>
                    <h3 class="mb-2">87</h3>
                    <p class="text-muted mb-0">Total Articles</p>
                    <a href="{{ route('admin.articles.index') }}" class="btn btn-sm btn-outline-info mt-2">
                        Manage Articles
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="fas fa-chart-bar fa-3x text-warning mb-3"></i>
                    <h3 class="mb-2">2,345</h3>
                    <p class="text-muted mb-0">API Requests Today</p>
                    <button class="btn btn-sm btn-outline-warning mt-2">
                        View Logs
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt text-warning"></i> Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.users.create') }}" class="btn btn-primary w-100">
                                <i class="fas fa-user-plus"></i> Add New User
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.ports.create') }}" class="btn btn-success w-100">
                                <i class="fas fa-plus"></i> Add New Port
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('admin.articles.create') }}" class="btn btn-info w-100">
                                <i class="fas fa-pen"></i> Create Article
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <button class="btn btn-warning w-100">
                                <i class="fas fa-sync"></i> Refresh Cache
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity & System Info -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-clock-rotate-left text-primary"></i> 
                        Recent Activity
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><small>10 mins ago</small></td>
                                    <td>John Doe</td>
                                    <td>Created new port entry</td>
                                    <td><span class="badge bg-success">Success</span></td>
                                </tr>
                                <tr>
                                    <td><small>25 mins ago</small></td>
                                    <td>Admin User</td>
                                    <td>Updated user permissions</td>
                                    <td><span class="badge bg-success">Success</span></td>
                                </tr>
                                <tr>
                                    <td><small>1 hour ago</small></td>
                                    <td>Jane Smith</td>
                                    <td>Published new article</td>
                                    <td><span class="badge bg-success">Success</span></td>
                                </tr>
                                <tr>
                                    <td><small>2 hours ago</small></td>
                                    <td>System</td>
                                    <td>Automated data sync</td>
                                    <td><span class="badge bg-info">Completed</span></td>
                                </tr>
                                <tr>
                                    <td><small>3 hours ago</small></td>
                                    <td>Mike Johnson</td>
                                    <td>Failed login attempt</td>
                                    <td><span class="badge bg-danger">Failed</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-server text-success"></i> 
                        System Information
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Laravel Version
                            <span class="badge bg-primary">{{ app()->version() }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            PHP Version
                            <span class="badge bg-success">{{ PHP_VERSION }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Database
                            <span class="badge bg-info">MySQL</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Cache Status
                            <span class="badge bg-success">
                                <i class="fas fa-check"></i> Active
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Debug Mode
                            <span class="badge bg-warning">
                                {{ config('app.debug') ? 'ON' : 'OFF' }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie text-info"></i> 
                        Storage Usage
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Database</small>
                            <small>45%</small>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-primary" style="width: 45%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small>Files</small>
                            <small>62%</small>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: 62%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <small>Cache</small>
                            <small>28%</small>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-info" style="width: 28%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
