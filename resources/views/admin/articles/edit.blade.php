@extends('layouts.admin')

@section('title', 'Edit Article')

@section('page-title', 'Edit Article')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('admin.articles.index') }}" class="btn btn-secondary mb-3">
                <i class="fas fa-arrow-left"></i> Back to Articles
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-edit"></i> Edit Article Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.articles.update', $article->id) }}" method="POST" id="articleForm">
                        @csrf
                        @method('PUT')
                        
                        <!-- Title -->
                        <div class="mb-4">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title', $article->title) }}"
                                   placeholder="Enter article title..."
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description (Short Summary) -->
                        <div class="mb-4">
                            <label for="description" class="form-label">Short Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="3"
                                      maxlength="500"
                                      placeholder="Brief summary of the article (max 500 characters, optional - will be auto-generated if left empty)">{{ old('description', $article->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> This will appear in news previews. Leave empty to auto-generate from content.
                            </small>
                        </div>

                        <!-- Source URL -->
                        <div class="mb-4">
                            <label for="url" class="form-label">Source URL</label>
                            <input type="url" 
                                   class="form-control @error('url') is-invalid @enderror" 
                                   id="url" 
                                   name="url" 
                                   value="{{ old('url', $article->url) }}"
                                   placeholder="https://example.com/article">
                            @error('url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                <i class="fas fa-link"></i> Original article URL (optional)
                            </small>
                        </div>

                        <!-- Source Name -->
                        <div class="mb-4">
                            <label for="source" class="form-label">Source Name</label>
                            <input type="text" 
                                   class="form-control @error('source') is-invalid @enderror" 
                                   id="source" 
                                   name="source" 
                                   value="{{ old('source', $article->source) }}"
                                   placeholder="e.g., Reuters, Bloomberg, BBC">
                            @error('source')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                <i class="fas fa-newspaper"></i> Name of the news source (optional)
                            </small>
                        </div>
                        
                        @if($article->sentiment)
                        <!-- Current Sentiment (Read-only info) -->
                        <div class="mb-4">
                            <label class="form-label">Current Sentiment Analysis</label>
                            <div class="alert alert-info">
                                <strong>Sentiment:</strong> 
                                <span class="badge bg-{{ $article->sentiment == 'positive' ? 'success' : ($article->sentiment == 'negative' ? 'danger' : 'secondary') }}">
                                    {{ ucfirst($article->sentiment) }}
                                </span>
                                <strong class="ms-3">Score:</strong> {{ $article->sentiment_score }}
                                <strong class="ms-3">Confidence:</strong> {{ $article->sentiment_confidence }}%
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-robot"></i> Sentiment will be automatically re-analyzed if title or description changes.
                                </small>
                            </div>
                        </div>
                        @endif

                        <!-- Content -->
                        <div class="mb-4">
                            <label for="content" class="form-label">Full Content <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                      id="content" 
                                      name="content" 
                                      rows="15"
                                      placeholder="Write your article content here..."
                                      required>{{ old('content', $article->content) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> You can use HTML tags for formatting.
                            </small>
                        </div>

                        <!-- Category -->
                        <div class="mb-4">
                            <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select @error('category') is-invalid @enderror" 
                                    id="category" 
                                    name="category"
                                    required>
                                <option value="">Select Category</option>
                                <option value="logistics" {{ old('category', $article->category) == 'logistics' ? 'selected' : '' }}>
                                    Logistics
                                </option>
                                <option value="economy" {{ old('category', $article->category) == 'economy' ? 'selected' : '' }}>
                                    Economy
                                </option>
                                <option value="geopolitics" {{ old('category', $article->category) == 'geopolitics' ? 'selected' : '' }}>
                                    Geopolitics
                                </option>
                                <option value="weather" {{ old('category', $article->category) == 'weather' ? 'selected' : '' }}>
                                    Weather
                                </option>
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="mb-4">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" 
                                    name="status"
                                    required>
                                <option value="draft" {{ old('status', $article->status) == 'draft' ? 'selected' : '' }}>
                                    Draft (Save for later)
                                </option>
                                <option value="published" {{ old('status', $article->status) == 'published' ? 'selected' : '' }}>
                                    Published (Make it public)
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Article
                            </button>
                            <a href="{{ route('admin.articles.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <a href="{{ route('admin.articles.show', $article->id) }}" class="btn btn-info">
                                <i class="fas fa-eye"></i> Preview
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Article Info -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Article Info</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Author:</strong><br>
                        <i class="fas fa-user"></i> {{ $article->user->name }}
                    </p>
                    <p class="mb-2">
                        <strong>Created:</strong><br>
                        <i class="fas fa-calendar"></i> {{ $article->created_at->format('d M Y, H:i') }}
                    </p>
                    <p class="mb-2">
                        <strong>Last Updated:</strong><br>
                        <i class="fas fa-clock"></i> {{ $article->updated_at->format('d M Y, H:i') }}
                    </p>
                    @if($article->published_at)
                    <p class="mb-0">
                        <strong>Published:</strong><br>
                        <i class="fas fa-check-circle text-success"></i> {{ $article->published_at->format('d M Y, H:i') }}
                    </p>
                    @else
                    <p class="mb-0">
                        <strong>Published:</strong><br>
                        <i class="fas fa-times-circle text-danger"></i> Not published yet
                    </p>
                    @endif
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-question-circle"></i> Help & Tips</h5>
                </div>
                <div class="card-body">
                    <h6><i class="fas fa-lightbulb"></i> Editing Tips</h6>
                    <ul class="small">
                        <li>Review your changes carefully</li>
                        <li>Use Preview to see how it looks</li>
                        <li>Check spelling and grammar</li>
                        <li>Ensure all links work properly</li>
                    </ul>

                    <hr>

                    <h6><i class="fas fa-code"></i> HTML Formatting</h6>
                    <ul class="small mb-0">
                        <li><code>&lt;h2&gt;</code> - Heading</li>
                        <li><code>&lt;p&gt;</code> - Paragraph</li>
                        <li><code>&lt;strong&gt;</code> - Bold text</li>
                        <li><code>&lt;em&gt;</code> - Italic text</li>
                        <li><code>&lt;ul&gt;&lt;li&gt;</code> - Bullet list</li>
                        <li><code>&lt;a href=""&gt;</code> - Link</li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Warning</h5>
                </div>
                <div class="card-body">
                    <p class="small text-warning mb-0">
                        <i class="fas fa-info-circle"></i> Changing status to "Published" will make this article immediately visible to all users.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Warn before leaving if there are unsaved changes
    let formChanged = false;
    
    $('#articleForm :input').on('change input', function() {
        formChanged = true;
    });
    
    $(window).on('beforeunload', function() {
        if (formChanged) {
            return 'You have unsaved changes. Are you sure you want to leave?';
        }
    });
    
    $('#articleForm').on('submit', function() {
        formChanged = false;
    });

    // Character counter for content
    $('#content').on('input', function() {
        const length = $(this).val().length;
        const words = $(this).val().trim().split(/\s+/).length;
        console.log(`Characters: ${length}, Words: ${words}`);
    });
});
</script>
@endpush
