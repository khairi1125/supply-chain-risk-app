@extends('layouts.admin')

@section('title', 'User Details')
@section('page-title', 'User Details')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
            <li class="breadcrumb-item active">{{ $user->name }}</li>
        </ol>
    </nav>

    <div class="row">
        {{-- User Information --}}
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user"></i> User Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="bg-info bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" 
                             style="width: 100px; height: 100px;">
                            <i class="fas fa-user fa-3x text-info"></i>
                        </div>
                    </div>
                    
                    <table class="table table-sm">
                        <tr>
                            <th style="width: 40%;">ID</th>
                            <td>{{ $user->id }}</td>
                        </tr>
                        <tr>
                            <th>Name</th>
                            <td><strong>{{ $user->name }}</strong></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>Role</th>
                            <td>
                                @if($user->role === 'admin')
                                    <span class="badge bg-danger">
                                        <i class="fas fa-shield-halved"></i> Admin
                                    </span>
                                @else
                                    <span class="badge bg-primary">
                                        <i class="fas fa-user"></i> User
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                @if($user->is_active)
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
                            <th>Email Verified</th>
                            <td>
                                @if($user->email_verified_at)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check"></i> Verified
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="fas fa-clock"></i> Pending
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Last Login</th>
                            <td>
                                @if($user->last_login)
                                    {{ $user->last_login->format('M d, Y H:i') }}
                                    <br>
                                    <small class="text-muted">
                                        ({{ $user->last_login->diffForHumans() }})
                                    </small>
                                @else
                                    <span class="text-muted">Never logged in</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Registered</th>
                            <td>
                                {{ $user->created_at->format('M d, Y') }}
                                <br>
                                <small class="text-muted">
                                    ({{ $user->created_at->diffForHumans() }})
                                </small>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="d-grid gap-2 mt-3">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit User
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Statistics & Activity --}}
        <div class="col-md-8">
            {{-- Statistics --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card border-primary">
                        <div class="card-body text-center">
                            <i class="fas fa-list fa-3x text-primary mb-3"></i>
                            <h3 class="mb-2">{{ $watchlistCount }}</h3>
                            <p class="text-muted mb-0">Countries in Watchlist</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-success">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-line fa-3x text-success mb-3"></i>
                            <h3 class="mb-2">{{ $recentActivity->count() }}</h3>
                            <p class="text-muted mb-0">Total Activities</p>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Recent Activity --}}
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-history"></i> Recent Activity
                    </h5>
                </div>
                <div class="card-body">
                    @if($recentActivity->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Action</th>
                                        <th>Description</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentActivity as $activity)
                                    <tr>
                                        <td>
                                            @if(str_contains($activity->action, 'added'))
                                                <span class="badge bg-success">
                                                    <i class="fas fa-plus"></i> Added
                                                </span>
                                            @elseif(str_contains($activity->action, 'removed'))
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-minus"></i> Removed
                                                </span>
                                            @elseif(str_contains($activity->action, 'refreshed'))
                                                <span class="badge bg-primary">
                                                    <i class="fas fa-sync"></i> Refreshed
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-info"></i> Activity
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $activity->description }}</td>
                                        <td>
                                            <small>
                                                {{ \Carbon\Carbon::parse($activity->created_at)->format('M d, Y H:i') }}
                                            </small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                            <p class="mb-0">No activity recorded yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
