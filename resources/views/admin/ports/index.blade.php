@extends('layouts.admin')

@section('title', 'Port Management')
@section('page-title', 'Port Management')

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
                        <i class="fas fa-anchor"></i> All Ports 
                        <span class="badge bg-success">{{ $ports->total() }}</span>
                    </h5>
                </div>
                <div class="col-md-6 text-end">
                    <a href="{{ route('admin.ports.create') }}" class="btn btn-success">
                        <i class="fas fa-plus"></i> Add New Port
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            {{-- Search & Filter Form --}}
            <form method="GET" action="{{ route('admin.ports.index') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search port or country..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="country" class="form-select">
                            <option value="">All Countries</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->country_code }}" 
                                    {{ request('country') == $country->country_code ? 'selected' : '' }}>
                                    {{ $country->country_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="region" class="form-select">
                            <option value="">All Regions</option>
                            @foreach($regions as $region)
                                <option value="{{ $region }}" {{ request('region') == $region ? 'selected' : '' }}>
                                    {{ $region }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('admin.ports.index') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
            
            {{-- Port Table --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Port Name</th>
                            <th style="width: 100px;">Code</th>
                            <th>Country</th>
                            <th>Region</th>
                            <th style="width: 120px;">Coordinates</th>
                            <th style="width: 100px;">Type</th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 180px;" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ports as $port)
                        <tr id="port-row-{{ $port->id }}">
                            <td>{{ $port->id }}</td>
                            <td><strong>{{ $port->port_name }}</strong></td>
                            <td>
                                @if($port->code)
                                    <code>{{ $port->code }}</code>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $port->country_name }}</td>
                            <td>
                                <span class="badge bg-info">{{ $port->region }}</span>
                            </td>
                            <td>
                                <small>
                                    {{ number_format($port->latitude, 4) }},
                                    {{ number_format($port->longitude, 4) }}
                                </small>
                            </td>
                            <td>
                                @if($port->port_type)
                                    {{ $port->port_type }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge status-badge-{{ $port->id }} {{ $port->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    @if($port->is_active)
                                        <i class="fas fa-check-circle"></i> Active
                                    @else
                                        <i class="fas fa-ban"></i> Inactive
                                    @endif
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.ports.show', $port) }}" 
                                       class="btn btn-sm btn-info" 
                                       title="View Details"
                                       data-bs-toggle="tooltip">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.ports.edit', $port) }}" 
                                       class="btn btn-sm btn-warning" 
                                       title="Edit Port"
                                       data-bs-toggle="tooltip">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-sm {{ $port->is_active ? 'btn-secondary' : 'btn-success' }} toggle-status-btn" 
                                            data-port-id="{{ $port->id }}"
                                            data-port-name="{{ $port->port_name }}"
                                            data-current-status="{{ $port->is_active ? 'active' : 'inactive' }}"
                                            title="{{ $port->is_active ? 'Deactivate' : 'Activate' }}"
                                            data-bs-toggle="tooltip">
                                        <i class="fas {{ $port->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-btn" 
                                            data-port-id="{{ $port->id }}"
                                            data-port-name="{{ $port->port_name }}"
                                            title="Delete Port"
                                            data-bs-toggle="tooltip">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="fas fa-anchor fa-3x mb-3 d-block"></i>
                                <p class="mb-0">No ports found.</p>
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
                        Showing {{ $ports->firstItem() ?? 0 }} to {{ $ports->lastItem() ?? 0 }} of {{ $ports->total() }} ports
                    </small>
                </div>
                <div>
                    {{ $ports->links('pagination::bootstrap-5') }}
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
                <p>Are you sure you want to delete port:</p>
                <p class="text-center"><strong id="delete-port-name"></strong></p>
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
    
    // Delete Port
    $('.delete-btn').on('click', function() {
        const portId = $(this).data('port-id');
        const portName = $(this).data('port-name');
        
        $('#delete-port-name').text(portName);
        $('#delete-form').attr('action', `/admin/ports/${portId}`);
        $('#deleteModal').modal('show');
    });
    
    // Toggle Status (Activate/Deactivate)
    $('.toggle-status-btn').on('click', function() {
        const $btn = $(this);
        const portId = $btn.data('port-id');
        const portName = $btn.data('port-name');
        const currentStatus = $btn.data('current-status');
        const action = currentStatus === 'active' ? 'deactivate' : 'activate';
        
        if (confirm(`Are you sure you want to ${action} port: ${portName}?`)) {
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            
            $.ajax({
                url: `/admin/ports/${portId}/toggle-status`,
                method: 'POST',
                success: function(response) {
                    if (response.success) {
                        const $statusBadge = $(`.status-badge-${portId}`);
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
