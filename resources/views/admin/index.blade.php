@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Admin Dashboard')

@push('styles')
<style>
    /* Modern Light Theme Dashboard */
    body {
        background: #ffffff;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }
    
    .dashboard-container {
        padding: 2rem;
        max-width: 1400px;
        margin: 0 auto;
    }
    
    /* Welcome Header with Gradient */
    .welcome-header {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
        border-radius: 24px;
        padding: 2.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 20px 60px rgba(102, 126, 234, 0.15);
        border: 1px solid rgba(102, 126, 234, 0.1);
        position: relative;
        overflow: hidden;
    }
    
    .welcome-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(102, 126, 234, 0.1) 0%, transparent 70%);
        border-radius: 50%;
    }
    
    .welcome-header h1 {
        font-size: 2.5rem;
        font-weight: 700;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 0.5rem;
    }
    
    .welcome-header p {
        color: #6b7280;
        font-size: 1.1rem;
        margin: 0;
    }
    
    /* Modern Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.06);
        border: 1px solid #f0f0f0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        min-height: 320px;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(180deg, var(--card-color) 0%, var(--card-color-light) 100%);
    }
    
    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
    }
    
    .stat-card.primary {
        --card-color: #667eea;
        --card-color-light: #764ba2;
    }
    
    .stat-card.success {
        --card-color: #10b981;
        --card-color-light: #059669;
    }
    
    .stat-card.warning {
        --card-color: #f59e0b;
        --card-color-light: #d97706;
    }
    
    .stat-card.info {
        --card-color: #06b6d4;
        --card-color-light: #0891b2;
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-bottom: 1.5rem;
        background: linear-gradient(135deg, var(--card-color) 0%, var(--card-color-light) 100%);
        color: white;
        box-shadow: 0 8px 20px rgba(var(--card-color-rgb), 0.3);
        flex-shrink: 0;
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.5rem;
        line-height: 1.2;
    }
    
    .stat-label {
        color: #6b7280;
        font-size: 0.95rem;
        font-weight: 500;
        margin-bottom: 1rem;
        line-height: 1.4;
        min-height: 20px;
    }
    
    .stat-meta {
        display: flex;
        align-items: flex-start;
        color: #9ca3af;
        font-size: 0.85rem;
        margin-bottom: 1rem;
        min-height: 40px;
        line-height: 1.4;
        flex-wrap: wrap;
        gap: 0.25rem;
    }
    
    .stat-button {
        background: linear-gradient(135deg, var(--card-color) 0%, var(--card-color-light) 100%);
        color: white;
        border: none;
        padding: 0.6rem 1.2rem;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
        margin-top: auto;
    }
    
    .stat-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(var(--card-color-rgb), 0.4);
    }
    
    /* Quick Actions */
    .quick-actions {
        background: #ffffff;
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.06);
        border: 1px solid #f0f0f0;
    }
    
    .quick-actions h3 {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .action-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .action-btn {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
        border: 2px solid #e5e7eb;
        padding: 1.5rem;
        border-radius: 16px;
        text-decoration: none;
        color: #1f2937;
        font-weight: 600;
        text-align: center;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.8rem;
    }
    
    .action-btn i {
        font-size: 2rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    .action-btn:hover {
        transform: translateY(-4px);
        border-color: #667eea;
        box-shadow: 0 12px 30px rgba(102, 126, 234, 0.2);
    }
    
    /* Two Column Layout */
    .dashboard-row {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }
    
    /* Activity Feed */
    .activity-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.06);
        border: 1px solid #f0f0f0;
    }
    
    .activity-card h3 {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .activity-item {
        padding: 1.2rem;
        border-radius: 12px;
        margin-bottom: 0.8rem;
        background: #f9fafb;
        border-left: 3px solid #e5e7eb;
        transition: all 0.3s ease;
    }
    
    .activity-item:hover {
        background: #f3f4f6;
        border-left-color: #667eea;
        transform: translateX(4px);
    }
    
    .activity-time {
        color: #9ca3af;
        font-size: 0.85rem;
        margin-bottom: 0.3rem;
    }
    
    .activity-user {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.2rem;
    }
    
    .activity-action {
        color: #6b7280;
        font-size: 0.95rem;
    }
    
    .activity-badge {
        display: inline-block;
        padding: 0.3rem 0.8rem;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-left: 0.5rem;
    }
    
    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }
    
    .badge-info {
        background: #dbeafe;
        color: #1e40af;
    }
    
    /* System Info */
    .system-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.06);
        border: 1px solid #f0f0f0;
        margin-bottom: 2rem;
    }
    
    .system-card h3 {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .system-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .system-item:last-child {
        border-bottom: none;
    }
    
    .system-label {
        color: #6b7280;
        font-size: 0.95rem;
        font-weight: 500;
    }
    
    .system-value {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.4rem 1rem;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    /* Responsive */
    @media (max-width: 1024px) {
        .dashboard-row {
            grid-template-columns: 1fr;
        }
        
        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        }
    }
    
    @media (max-width: 768px) {
        .welcome-header h1 {
            font-size: 2rem;
        }
        
        .stat-number {
            font-size: 2rem;
        }
        
        .action-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="dashboard-container">
    <!-- Welcome Header -->
    <div class="welcome-header">
        <h1>👋 Welcome back, Admin!</h1>
        <p>Here's what's happening with your platform today.</p>
    </div>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-number">{{ number_format($stats['total_users']) }}</div>
            <div class="stat-label">Total Users</div>
            <div class="stat-meta">
                <i class="fas fa-user-shield me-1"></i>
                {{ $stats['admin_users'] }} admins · {{ $stats['regular_users'] }} users
            </div>
            <a href="{{ route('admin.users.index') }}" class="stat-button">
                Manage Users
            </a>
        </div>
        
        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-anchor"></i>
            </div>
            <div class="stat-number">{{ number_format($stats['total_ports']) }}</div>
            <div class="stat-label">Total Ports</div>
            <div class="stat-meta">
                <i class="fas fa-check-circle me-1"></i>
                {{ number_format($stats['active_ports']) }} active ports
            </div>
            <a href="{{ route('admin.ports.index') }}" class="stat-button">
                Manage Ports
            </a>
        </div>
        
        <div class="stat-card warning">
            <div class="stat-icon">
                <i class="fas fa-newspaper"></i>
            </div>
            <div class="stat-number">{{ number_format($stats['total_articles']) }}</div>
            <div class="stat-label">Total Articles</div>
            <div class="stat-meta">
                <i class="fas fa-check me-1"></i>
                {{ number_format($stats['published_articles']) }} published
            </div>
            <a href="{{ route('admin.articles.index') }}" class="stat-button">
                Manage Articles
            </a>
        </div>
        
        <div class="stat-card info">
            <div class="stat-icon">
                <i class="fas fa-globe"></i>
            </div>
            <div class="stat-number">{{ number_format(\DB::table('countries')->count()) }}</div>
            <div class="stat-label">Countries</div>
            <div class="stat-meta">
                <i class="fas fa-map-marked-alt me-1"></i>
                Global coverage
            </div>
            <button class="stat-button" disabled style="opacity: 0.7; cursor: not-allowed;">
                View Statistics
            </button>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="quick-actions">
        <h3>
            <i class="fas fa-bolt"></i>
            Quick Actions
        </h3>
        <div class="action-grid">
            <a href="{{ route('admin.users.create') }}" class="action-btn">
                <i class="fas fa-user-plus"></i>
                <span>Add New User</span>
            </a>
            <a href="{{ route('admin.ports.create') }}" class="action-btn">
                <i class="fas fa-plus-circle"></i>
                <span>Add New Port</span>
            </a>
            <a href="{{ route('admin.articles.import') }}" class="action-btn">
                <i class="fas fa-download"></i>
                <span>Import News</span>
            </a>
            <a href="{{ route('admin.ports.map') }}" class="action-btn">
                <i class="fas fa-map"></i>
                <span>View Port Map</span>
            </a>
        </div>
    </div>
    
    <!-- Two Column Layout -->
    <div class="dashboard-row">
        <!-- Recent Activity -->
        <div class="activity-card">
            <h3>
                <i class="fas fa-history"></i>
                Recent Activity
            </h3>
            
            @forelse($recentActivities as $activity)
            <div class="activity-item">
                <div class="activity-time">
                    <i class="far fa-clock"></i> {{ $activity['time']->diffForHumans() }}
                </div>
                <div class="activity-user">
                    <i class="fas fa-{{ $activity['icon'] }}"></i> {{ $activity['user'] }}
                </div>
                <div class="activity-action">
                    {{ $activity['action'] }}
                    @if($activity['status'] === 'success')
                        <span class="activity-badge badge-success">Success</span>
                    @else
                        <span class="activity-badge badge-info">{{ ucfirst($activity['status']) }}</span>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center py-4">
                <i class="fas fa-inbox fa-3x" style="color: #d1d5db;"></i>
                <p class="text-muted mt-2">No recent activity</p>
            </div>
            @endforelse
        </div>
        
        <!-- System Info -->
        <div>
            <div class="system-card">
                <h3>
                    <i class="fas fa-server"></i>
                    System Information
                </h3>
                
                <div class="system-item">
                    <span class="system-label">Laravel Version</span>
                    <span class="system-value">{{ app()->version() }}</span>
                </div>
                
                <div class="system-item">
                    <span class="system-label">PHP Version</span>
                    <span class="system-value">{{ PHP_VERSION }}</span>
                </div>
                
                <div class="system-item">
                    <span class="system-label">Database</span>
                    <span class="system-value">MySQL</span>
                </div>
                
                <div class="system-item">
                    <span class="system-label">Cache Status</span>
                    <span class="system-value">
                        <i class="fas fa-check-circle"></i> Active
                    </span>
                </div>
                
                <div class="system-item">
                    <span class="system-label">Debug Mode</span>
                    <span class="system-value" style="background: {{ config('app.debug') ? 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)' : 'linear-gradient(135deg, #10b981 0%, #059669 100%)' }}">
                        {{ config('app.debug') ? 'ON' : 'OFF' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
