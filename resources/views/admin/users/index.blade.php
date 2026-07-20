@extends('layouts.admin')

@section('title', 'User Management')
@section('page-title', 'User Management')

@section('content')
<div class="container-fluid">
    {{-- Success/Error Messages --}}
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

    <div class="card">
        <div class="card-header bg-white">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-0">
                        <i class="fas fa-users"></i> All Users 
                        <span class="badge bg-primary">{{ $users->total() }}</span>
                    </h5>
                </div>
                <div class="col-md-6 text-end">
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New User
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            {{-- Search & Filter Form --}}
            <form method="GET" action="{{ route('admin.users.index') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search by name or email..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="role" class="form-select">
                            <option value="">All Roles</option>
                            <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>User</option>
                            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
            
            {{-- User Table --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th style="width: 100px;">Role</th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 150px;">Last Login</th>
                            <th style="width: 100px;" class="text-center">Watchlists</th>
                            <th style="width: 220px;" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr id="user-row-{{ $user->id }}">
                            <td>{{ $user->id }}</td>
                            <td>
                                <strong>{{ $user->name }}</strong>
                                @if($user->id === auth()->id())
                                    <span class="badge bg-info ms-1">You</span>
                                @endif
                            </td>
                            <td>{{ $user->email }}</td>
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
                            <td>
                                <span class="badge status-badge-{{ $user->id }} {{ $user->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    @if($user->is_active)
                                        <i class="fas fa-check-circle"></i> Active
                                    @else
                                        <i class="fas fa-ban"></i> Inactive
                                    @endif
                                </span>
                            </td>
                            <td>
                                @if($user->last_login)
                                    <small>{{ $user->last_login->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted"><small>Never</small></span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $user->watchlistCount() }}</span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.users.show', $user) }}" 
                                       class="btn btn-sm btn-info" 
                                       title="View Details"
                                       data-bs-toggle="tooltip">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}" 
                                       class="btn btn-sm btn-warning" 
                                       title="Edit User"
                                       data-bs-toggle="tooltip">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    @if($user->id !== auth()->id())
                                        <button class="btn btn-sm {{ $user->is_active ? 'btn-secondary' : 'btn-success' }} toggle-status-btn" 
                                                data-user-id="{{ $user->id }}"
                                                data-user-name="{{ $user->name }}"
                                                data-current-status="{{ $user->is_active ? 'active' : 'inactive' }}"
                                                title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}"
                                                data-bs-toggle="tooltip">
                                            <i class="fas {{ $user->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                        </button>
                                        
                                        <button class="btn btn-sm btn-primary change-password-btn" 
                                                data-user-id="{{ $user->id }}"
                                                data-user-name="{{ $user->name }}"
                                                title="Change Password"
                                                data-bs-toggle="tooltip">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        
                                        <button class="btn btn-sm btn-danger delete-btn" 
                                                data-user-id="{{ $user->id }}"
                                                data-user-name="{{ $user->name }}"
                                                title="Delete User"
                                                data-bs-toggle="tooltip">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-users fa-3x mb-3 d-block"></i>
                                <p class="mb-0">No users found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            <div class="mt-3 d-flex justify-content-between align-items-center">
                <div>
                    <small class="text-muted">
                        Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users
                    </small>
                </div>
                <div>
                    {{ $users->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i> Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete user:</p>
                <p class="text-center"><strong id="delete-user-name"></strong></p>
                <p class="text-danger"><small><i class="fas fa-warning"></i> This action cannot be undone!</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="delete-form" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Yes, Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Change Password Modal --}}
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-key"></i> Change Password
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="change-password-form">
                <div class="modal-body">
                    <p>Change password for: <strong id="password-user-name"></strong></p>
                    
                    <div class="alert alert-danger d-none" id="password-error-alert"></div>
                    
                    <div class="mb-3">
                        <label class="form-label">New Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required minlength="8">
                        <small class="text-muted">Minimum 8 characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" required minlength="8">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="change-password-btn">
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Fix pagination button size */
.pagination {
    margin-bottom: 0;
}

.pagination .page-link {
    padding: 0.375rem 0.75rem;
    font-size: 0.9rem;
}

.pagination .page-item:first-child .page-link,
.pagination .page-item:last-child .page-link {
    border-radius: 0.25rem;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // CSRF token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    // Delete User
    $('.delete-btn').on('click', function() {
        const userId = $(this).data('user-id');
        const userName = $(this).data('user-name');
        
        $('#delete-user-name').text(userName);
        $('#delete-form').attr('action', `/admin/users/${userId}`);
        $('#deleteModal').modal('show');
    });
    
    // Toggle Status (Activate/Deactivate)
    $('.toggle-status-btn').on('click', function() {
        const $btn = $(this);
        const userId = $btn.data('user-id');
        const userName = $btn.data('user-name');
        const currentStatus = $btn.data('current-status');
        const action = currentStatus === 'active' ? 'deactivate' : 'activate';
        
        if (confirm(`Are you sure you want to ${action} user: ${userName}?`)) {
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            
            $.ajax({
                url: `/admin/users/${userId}/toggle-status`,
                method: 'POST',
                success: function(response) {
                    if (response.success) {
                        // Update status badge
                        const $statusBadge = $(`.status-badge-${userId}`);
                        if (response.is_active) {
                            $statusBadge.removeClass('bg-secondary').addClass('bg-success')
                                .html('<i class="fas fa-check-circle"></i> Active');
                            $btn.removeClass('btn-success').addClass('btn-secondary')
                                .html('<i class="fas fa-ban"></i>')
                                .attr('title', 'Deactivate')
                                .data('current-status', 'active');
                        } else {
                            $statusBadge.removeClass('bg-success').addClass('bg-secondary')
                                .html('<i class="fas fa-ban"></i> Inactive');
                            $btn.removeClass('btn-secondary').addClass('btn-success')
                                .html('<i class="fas fa-check"></i>')
                                .attr('title', 'Activate')
                                .data('current-status', 'inactive');
                        }
                        showToast('Success!', response.message, 'success');
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Failed to toggle status';
                    showToast('Error', message, 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        }
    });
    
    // Change Password
    let currentPasswordUserId = null;
    
    $('.change-password-btn').on('click', function() {
        currentPasswordUserId = $(this).data('user-id');
        const userName = $(this).data('user-name');
        
        $('#password-user-name').text(userName);
        $('#change-password-form')[0].reset();
        $('#password-error-alert').addClass('d-none');
        $('#changePasswordModal').modal('show');
    });
    
    $('#change-password-form').on('submit', function(e) {
        e.preventDefault();
        
        const $btn = $('#change-password-btn');
        const formData = {
            password: $('input[name="password"]').val(),
            password_confirmation: $('input[name="password_confirmation"]').val()
        };
        
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Changing...');
        $('#password-error-alert').addClass('d-none');
        
        $.ajax({
            url: `/admin/users/${currentPasswordUserId}/change-password`,
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#changePasswordModal').modal('hide');
                    showToast('Success!', response.message, 'success');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Failed to change password';
                $('#password-error-alert').text(message).removeClass('d-none');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-key"></i> Change Password');
            }
        });
    });
    
    // Toast notification
    function showToast(title, message, type = 'success') {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        const toast = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 80px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
                <i class="fas ${icon}"></i> <strong>${title}</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('body').append(toast);
        setTimeout(() => toast.alert('close'), 3000);
    }
});
</script>
@endpush
