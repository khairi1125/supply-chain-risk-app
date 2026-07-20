@extends('layouts.admin')

@section('title', 'News Management')

@section('page-title', 'News Management')

@section('content')
<div class="container-fluid">
    <!-- Header with Create Button -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>📰 News Management</h2>
            <p class="text-muted">Manage and curate news for users</p>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('admin.articles.import') }}" class="btn btn-info">
                <i class="fas fa-download"></i> Import from GNews
            </a>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.articles.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by title or content..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            <option value="logistics" {{ request('category') == 'logistics' ? 'selected' : '' }}>Logistics</option>
                            <option value="economy" {{ request('category') == 'economy' ? 'selected' : '' }}>Economy</option>
                            <option value="geopolitics" {{ request('category') == 'geopolitics' ? 'selected' : '' }}>Geopolitics</option>
                            <option value="weather" {{ request('category') == 'weather' ? 'selected' : '' }}>Weather</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Author</label>
                        <select name="author" class="form-select">
                            <option value="">All Authors</option>
                            @foreach($authors as $author)
                                <option value="{{ $author->id }}" {{ request('author') == $author->id ? 'selected' : '' }}>
                                    {{ $author->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.articles.index') }}" class="btn btn-secondary">
                                <i class="fas fa-redo"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Articles Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Articles List</h5>
            <div class="d-flex gap-2" id="bulkActions" style="display: none !important;">
                <button class="btn btn-sm btn-success" id="btnPublishSelected">
                    <i class="fas fa-check-circle"></i> Publish Selected (<span id="selectedCount">0</span>)
                </button>
                <button class="btn btn-sm btn-danger" id="btnDeleteSelected">
                    <i class="fas fa-trash"></i> Delete Selected (<span id="selectedCountDelete">0</span>)
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($articles->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                </th>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Published Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($articles as $article)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input article-checkbox" 
                                           data-id="{{ $article->id }}" 
                                           data-status="{{ $article->status }}">
                                </td>
                                <td>{{ $article->id }}</td>
                                <td>
                                    <strong>{{ $article->title }}</strong>
                                    <br>
                                    <small class="text-muted">{{ Str::limit(strip_tags($article->content), 80) }}</small>
                                </td>
                                <td>
                                    <i class="fas fa-user-circle"></i> {{ $article->user->name }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $article->category_badge }}">
                                        {{ ucfirst($article->category) }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-{{ $article->status_badge }} toggle-status" 
                                            data-id="{{ $article->id }}"
                                            data-status="{{ $article->status }}">
                                        <i class="fas fa-circle"></i> {{ ucfirst($article->status) }}
                                    </button>
                                </td>
                                <td>
                                    @if($article->published_at)
                                        <small>{{ $article->published_at->format('d M Y, H:i') }}</small>
                                    @else
                                        <small class="text-muted">Not Published</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.articles.show', $article->id) }}" 
                                           class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.articles.edit', $article->id) }}" 
                                           class="btn btn-sm btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger delete-btn" 
                                                data-id="{{ $article->id }}"
                                                data-title="{{ $article->title }}"
                                                title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Showing {{ $articles->firstItem() ?? 0 }} to {{ $articles->lastItem() ?? 0 }} of {{ $articles->total() }} news
                    </div>
                    <div>
                        {{ $articles->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-newspaper fa-4x text-muted mb-3"></i>
                    <p class="text-muted">No news found. Import news from GNews!</p>
                    <a href="{{ route('admin.articles.import') }}" class="btn btn-primary mt-2">
                        <i class="fas fa-plus"></i> Import News
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="background-color: #1e1b4b; border: 1px solid rgba(124, 58, 237, 0.3);">
            <div class="modal-header" style="border-bottom: 1px solid rgba(124, 58, 237, 0.3);">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete article: <strong id="deleteArticleTitle"></strong>?</p>
                <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone!</p>
            </div>
            <div class="modal-footer" style="border-top: 1px solid rgba(124, 58, 237, 0.3);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .pagination {
        margin-bottom: 0;
    }
    
    .pagination .page-link {
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        background-color: #0f0f1a;
        border: 1px solid rgba(124, 58, 237, 0.3);
        color: #ffffff;
    }
    
    .pagination .page-link:hover {
        background-color: rgba(124, 58, 237, 0.2);
        border-color: #7c3aed;
        color: #ffffff;
    }
    
    .pagination .page-item.active .page-link {
        background-color: #7c3aed;
        border-color: #7c3aed;
        color: #ffffff;
    }
    
    .pagination .page-item.disabled .page-link {
        background-color: #0f0f1a;
        border-color: rgba(124, 58, 237, 0.2);
        color: #6b7280;
    }
    
    .form-check-input {
        cursor: pointer;
        width: 18px;
        height: 18px;
    }
    
    #selectAll {
        cursor: pointer;
        width: 20px;
        height: 20px;
    }
    
    #bulkActions {
        animation: fadeIn 0.3s ease;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Select All checkbox
    $('#selectAll').change(function() {
        $('.article-checkbox').prop('checked', $(this).is(':checked'));
        updateBulkActions();
    });
    
    // Individual checkbox change
    $('.article-checkbox').change(function() {
        updateBulkActions();
        
        // Update Select All checkbox state
        const total = $('.article-checkbox').length;
        const checked = $('.article-checkbox:checked').length;
        $('#selectAll').prop('checked', total === checked);
    });
    
    // Update bulk action buttons visibility and count
    function updateBulkActions() {
        const checked = $('.article-checkbox:checked').length;
        
        if (checked > 0) {
            $('#bulkActions').show();
            $('#selectedCount').text(checked);
            $('#selectedCountDelete').text(checked);
        } else {
            $('#bulkActions').hide();
        }
    }
    
    // Publish Selected
    $('#btnPublishSelected').click(function() {
        const selectedIds = [];
        $('.article-checkbox:checked').each(function() {
            selectedIds.push($(this).data('id'));
        });
        
        if (selectedIds.length === 0) {
            alert('Please select at least one article');
            return;
        }
        
        if (!confirm(`Publish ${selectedIds.length} selected article(s)?`)) {
            return;
        }
        
        // Show loading
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Publishing...');
        
        // Publish each article
        let completed = 0;
        selectedIds.forEach(function(id) {
            $.ajax({
                url: `/admin/articles/${id}/toggle-status`,
                method: 'POST',
                success: function(response) {
                    completed++;
                    if (completed === selectedIds.length) {
                        alert('Articles published successfully!');
                        location.reload();
                    }
                },
                error: function() {
                    completed++;
                    if (completed === selectedIds.length) {
                        alert('Some articles failed to publish. Please refresh the page.');
                        location.reload();
                    }
                }
            });
        });
    });
    
    // Delete Selected
    $('#btnDeleteSelected').click(function() {
        const selectedIds = [];
        $('.article-checkbox:checked').each(function() {
            selectedIds.push($(this).data('id'));
        });
        
        if (selectedIds.length === 0) {
            alert('Please select at least one article');
            return;
        }
        
        if (!confirm(`⚠️ DELETE ${selectedIds.length} article(s)? This action CANNOT be undone!`)) {
            return;
        }
        
        // Double confirm for safety
        if (!confirm('Are you ABSOLUTELY SURE? This will permanently delete the selected articles.')) {
            return;
        }
        
        // Show loading
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Deleting...');
        
        // Delete each article
        let completed = 0;
        selectedIds.forEach(function(id) {
            $.ajax({
                url: `/admin/articles/${id}`,
                method: 'POST',
                data: {
                    _method: 'DELETE'
                },
                success: function(response) {
                    completed++;
                    if (completed === selectedIds.length) {
                        alert('Articles deleted successfully!');
                        location.reload();
                    }
                },
                error: function() {
                    completed++;
                    if (completed === selectedIds.length) {
                        alert('Some articles failed to delete. Please refresh the page.');
                        location.reload();
                    }
                }
            });
        });
    });
    
    // Toggle Status (individual)
    $('.toggle-status').click(function() {
        const articleId = $(this).data('id');
        const currentStatus = $(this).data('status');
        const button = $(this);
        
        if (confirm('Are you sure you want to change the status of this article?')) {
            $.ajax({
                url: `/admin/articles/${articleId}/toggle-status`,
                method: 'POST',
                success: function(response) {
                    if (response.success) {
                        // Update button
                        const newStatus = response.status;
                        const badge = newStatus === 'published' ? 'success' : 'secondary';
                        button.removeClass('btn-success btn-secondary').addClass(`btn-${badge}`);
                        button.html(`<i class="fas fa-circle"></i> ${newStatus.charAt(0).toUpperCase() + newStatus.slice(1)}`);
                        button.data('status', newStatus);
                        
                        // Show success alert
                        alert(response.message);
                        location.reload();
                    }
                },
                error: function() {
                    alert('Failed to update article status');
                }
            });
        }
    });
    
    // Delete Article (individual)
    $('.delete-btn').click(function() {
        const articleId = $(this).data('id');
        const articleTitle = $(this).data('title');
        
        $('#deleteArticleTitle').text(articleTitle);
        $('#deleteForm').attr('action', `/admin/articles/${articleId}`);
        
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    });
});
</script>
@endpush
