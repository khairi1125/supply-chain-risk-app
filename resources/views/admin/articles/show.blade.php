@extends('layouts.admin')

@section('title', 'View Article')

@section('page-title', 'View Article')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <a href="{{ route('admin.articles.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Articles
            </a>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('admin.articles.edit', $article->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit Article
            </a>
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                <i class="fas fa-trash"></i> Delete
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <!-- Article Header -->
                    <div class="mb-4 pb-3 border-bottom" style="border-color: rgba(124, 58, 237, 0.3) !important;">
                        <h1 class="mb-3">{{ $article->title }}</h1>
                        <div class="d-flex flex-wrap gap-2 align-items-center text-muted">
                            <span>
                                <i class="fas fa-user"></i> {{ $article->user->name }}
                            </span>
                            <span>•</span>
                            <span>
                                <i class="fas fa-calendar"></i> 
                                @if($article->published_at)
                                    {{ $article->published_at->format('d M Y, H:i') }}
                                @else
                                    {{ $article->created_at->format('d M Y, H:i') }}
                                @endif
                            </span>
                            <span>•</span>
                            <span class="badge bg-{{ $article->category_badge }}">
                                {{ ucfirst($article->category) }}
                            </span>
                            <span class="badge bg-{{ $article->status_badge }}">
                                {{ ucfirst($article->status) }}
                            </span>
                        </div>
                    </div>

                    <!-- Article Content -->
                    <div class="article-content">
                        {!! nl2br($article->content) !!}
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Article Metadata -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Article Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td><strong>ID:</strong></td>
                            <td>{{ $article->id }}</td>
                        </tr>
                        <tr>
                            <td><strong>Author:</strong></td>
                            <td>{{ $article->user->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Category:</strong></td>
                            <td>
                                <span class="badge bg-{{ $article->category_badge }}">
                                    {{ ucfirst($article->category) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="badge bg-{{ $article->status_badge }}">
                                    {{ ucfirst($article->status) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td>{{ $article->created_at->format('d M Y, H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Updated:</strong></td>
                            <td>{{ $article->updated_at->format('d M Y, H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Published:</strong></td>
                            <td>
                                @if($article->published_at)
                                    {{ $article->published_at->format('d M Y, H:i') }}
                                @else
                                    <span class="text-muted">Not published</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-{{ $article->status === 'draft' ? 'success' : 'secondary' }} toggle-status">
                            <i class="fas fa-{{ $article->status === 'draft' ? 'check-circle' : 'times-circle' }}"></i>
                            {{ $article->status === 'draft' ? 'Publish Article' : 'Unpublish Article' }}
                        </button>
                        <a href="{{ route('admin.articles.edit', $article->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Article
                        </a>
                        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="fas fa-trash"></i> Delete Article
                        </button>
                    </div>
                </div>
            </div>

            <!-- Content Statistics -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Content Stats</h5>
                </div>
                <div class="card-body">
                    @php
                        $content = strip_tags($article->content);
                        $wordCount = str_word_count($content);
                        $charCount = strlen($content);
                        $readingTime = ceil($wordCount / 200); // Average reading speed
                    @endphp
                    <p class="mb-2">
                        <strong>Word Count:</strong> {{ number_format($wordCount) }} words
                    </p>
                    <p class="mb-2">
                        <strong>Character Count:</strong> {{ number_format($charCount) }} characters
                    </p>
                    <p class="mb-0">
                        <strong>Reading Time:</strong> ~{{ $readingTime }} min{{ $readingTime > 1 ? 's' : '' }}
                    </p>
                </div>
            </div>
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
                <p>Are you sure you want to delete this article?</p>
                <p class="text-warning"><strong>{{ $article->title }}</strong></p>
                <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone!</p>
            </div>
            <div class="modal-footer" style="border-top: 1px solid rgba(124, 58, 237, 0.3);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.articles.destroy', $article->id) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Article
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .article-content {
        line-height: 1.8;
        font-size: 1.05rem;
    }
    
    .article-content h1,
    .article-content h2,
    .article-content h3,
    .article-content h4,
    .article-content h5,
    .article-content h6 {
        margin-top: 1.5rem;
        margin-bottom: 1rem;
        font-weight: 600;
    }
    
    .article-content p {
        margin-bottom: 1rem;
    }
    
    .article-content ul,
    .article-content ol {
        margin-bottom: 1rem;
        padding-left: 2rem;
    }
    
    .article-content a {
        color: #7c3aed;
        text-decoration: underline;
    }
    
    .article-content a:hover {
        color: #8b5cf6;
    }
    
    .article-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 1rem 0;
    }
    
    .article-content blockquote {
        border-left: 4px solid #7c3aed;
        padding-left: 1rem;
        margin: 1rem 0;
        font-style: italic;
        color: #b0b0b0;
    }
    
    .article-content code {
        background-color: rgba(124, 58, 237, 0.1);
        padding: 0.2rem 0.4rem;
        border-radius: 4px;
        font-size: 0.9em;
    }
    
    .article-content pre {
        background-color: rgba(124, 58, 237, 0.1);
        padding: 1rem;
        border-radius: 8px;
        overflow-x: auto;
    }
    
    .article-content pre code {
        background-color: transparent;
        padding: 0;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle Status
    $('.toggle-status').click(function() {
        const articleId = {{ $article->id }};
        
        if (confirm('Are you sure you want to change the status of this article?')) {
            $.ajax({
                url: `/admin/articles/${articleId}/toggle-status`,
                method: 'POST',
                success: function(response) {
                    if (response.success) {
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
});
</script>
@endpush
