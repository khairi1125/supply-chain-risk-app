@extends('layouts.admin')

@section('title', 'Create Article')

@section('page-title', 'Create New Article')

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
                    <h5 class="mb-0"><i class="fas fa-newspaper"></i> Article Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.articles.store') }}" method="POST" id="articleForm">
                        @csrf
                        
                        <!-- Title -->
                        <div class="mb-4">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}"
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
                                      placeholder="Brief summary of the article (max 500 characters, optional - will be auto-generated if left empty)">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> This will appear in news previews and search results. Leave empty to auto-generate.
                            </small>
                        </div>

                        <!-- Source URL -->
                        <div class="mb-4">
                            <label for="url" class="form-label">Source URL</label>
                            <input type="url" 
                                   class="form-control @error('url') is-invalid @enderror" 
                                   id="url" 
                                   name="url" 
                                   value="{{ old('url') }}"
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
                                   value="{{ old('source') }}"
                                   placeholder="e.g., Reuters, Bloomberg, BBC">
                            @error('source')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                <i class="fas fa-newspaper"></i> Name of the news source (optional)
                            </small>
                        </div>

                        <!-- Content -->
                        <div class="mb-4">
                            <label for="content" class="form-label">Full Content <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                      id="content" 
                                      name="content" 
                                      rows="15"
                                      placeholder="Write your article content here..."
                                      required>{{ old('content') }}</textarea>
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
                                <option value="logistics" {{ old('category') == 'logistics' ? 'selected' : '' }}>
                                    Logistics
                                </option>
                                <option value="economy" {{ old('category') == 'economy' ? 'selected' : '' }}>
                                    Economy
                                </option>
                                <option value="geopolitics" {{ old('category') == 'geopolitics' ? 'selected' : '' }}>
                                    Geopolitics
                                </option>
                                <option value="weather" {{ old('category') == 'weather' ? 'selected' : '' }}>
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
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>
                                    Draft (Save for later)
                                </option>
                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>
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
                                <i class="fas fa-save"></i> Create Article
                            </button>
                            <a href="{{ route('admin.articles.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Help Section -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-question-circle"></i> Help & Tips</h5>
                </div>
                <div class="card-body">
                    <h6><i class="fas fa-lightbulb"></i> Writing Tips</h6>
                    <ul class="small">
                        <li>Write a clear, descriptive title</li>
                        <li>Use headings to organize content</li>
                        <li>Keep paragraphs short and readable</li>
                        <li>Add relevant examples and data</li>
                    </ul>

                    <hr>

                    <h6><i class="fas fa-tags"></i> Categories</h6>
                    <ul class="small mb-0">
                        <li><strong>Logistics:</strong> Shipping, ports, transportation</li>
                        <li><strong>Economy:</strong> Markets, trade, finance</li>
                        <li><strong>Geopolitics:</strong> Politics, international relations</li>
                        <li><strong>Weather:</strong> Climate, natural disasters</li>
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
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Status Info</h5>
                </div>
                <div class="card-body">
                    <p class="small mb-2">
                        <span class="badge bg-secondary">Draft</span><br>
                        Article will be saved but not visible to users. You can edit it later.
                    </p>
                    <p class="small mb-0">
                        <span class="badge bg-success">Published</span><br>
                        Article will be immediately available to all users.
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
    // Auto-save to localStorage every 30 seconds
    setInterval(function() {
        const title = $('#title').val();
        const content = $('#content').val();
        const category = $('#category').val();
        
        if (title || content) {
            localStorage.setItem('article_draft', JSON.stringify({
                title: title,
                content: content,
                category: category,
                timestamp: new Date().toISOString()
            }));
            console.log('Draft auto-saved');
        }
    }, 30000);

    // Restore draft on page load
    const draft = localStorage.getItem('article_draft');
    if (draft) {
        const data = JSON.parse(draft);
        const timeDiff = (new Date() - new Date(data.timestamp)) / 1000 / 60; // minutes
        
        if (timeDiff < 60 && confirm('Found an auto-saved draft from ' + Math.round(timeDiff) + ' minutes ago. Restore it?')) {
            $('#title').val(data.title);
            $('#content').val(data.content);
            $('#category').val(data.category);
        }
    }

    // Clear draft after successful submission
    $('#articleForm').on('submit', function() {
        localStorage.removeItem('article_draft');
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
