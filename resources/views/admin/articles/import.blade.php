@extends('layouts.admin')

@section('title', 'Import News Articles')

@section('page-title', 'Import News from GNews API')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <a href="{{ route('admin.articles.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Articles
            </a>
        </div>
    </div>

    <!-- Search Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-search"></i> Search News</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Search Query</label>
                    <input type="text" id="searchQuery" class="form-control" 
                           placeholder="E.g., supply chain, shipping, logistics, trade..." 
                           value="supply chain logistics trade">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i> Use keywords related to supply chain, economy, geopolitics, or weather
                    </small>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Max Results</label>
                    <select id="maxResults" class="form-select">
                        <option value="10">10 articles</option>
                        <option value="20" selected>20 articles</option>
                        <option value="30">30 articles</option>
                        <option value="50">50 articles</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button id="fetchNewsBtn" class="btn btn-primary w-100">
                        <i class="fas fa-download"></i> Fetch News
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Search Buttons -->
    <div class="card mb-4">
        <div class="card-body">
            <h6 class="mb-3"><i class="fas fa-bolt"></i> Quick Search</h6>
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-sm btn-outline-primary quick-search" data-query="supply chain disruption">
                    Supply Chain Disruption
                </button>
                <button class="btn btn-sm btn-outline-info quick-search" data-query="global trade economy">
                    Global Trade & Economy
                </button>
                <button class="btn btn-sm btn-outline-warning quick-search" data-query="geopolitics international relations">
                    Geopolitics
                </button>
                <button class="btn btn-sm btn-outline-danger quick-search" data-query="weather climate disaster">
                    Weather & Climate
                </button>
                <button class="btn btn-sm btn-outline-success quick-search" data-query="shipping port logistics">
                    Shipping & Ports
                </button>
                <button class="btn btn-sm btn-outline-secondary quick-search" data-query="oil gas energy prices">
                    Energy & Oil
                </button>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="card" style="display: none;">
        <div class="card-body text-center py-5">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted">Fetching news from GNews API...</p>
        </div>
    </div>

    <!-- News Results -->
    <div id="newsResults" style="display: none;">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-newspaper"></i> News Articles</h5>
                <span id="resultsCount" class="badge bg-primary">0 articles</span>
            </div>
            <div class="card-body">
                <div id="newsList"></div>
            </div>
        </div>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-newspaper fa-4x text-muted mb-3"></i>
            <h5 class="text-muted">No News Loaded</h5>
            <p class="text-muted mb-0">Use the search above to fetch news articles from GNews API</p>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .news-item {
        border: 1px solid rgba(124, 58, 237, 0.2);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        background-color: #ffffff;
        transition: all 0.3s ease;
    }
    
    .news-item:hover {
        border-color: rgba(124, 58, 237, 0.5);
        background-color: #f8f9fa;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(124, 58, 237, 0.2);
    }
    
    .news-item.imported {
        opacity: 0.7;
        background-color: rgba(16, 185, 129, 0.05);
        border-color: rgba(16, 185, 129, 0.4);
    }
    
    .news-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }
    
    .news-description {
        color: #374151;
        margin-bottom: 1rem;
        line-height: 1.6;
    }
    
    .news-meta {
        font-size: 0.85rem;
        color: #6b7280;
    }
    
    .news-meta a {
        color: #7c3aed;
        text-decoration: none;
    }
    
    .news-meta a:hover {
        color: #6d28d9;
        text-decoration: underline;
    }
    
    .category-badge {
        cursor: pointer;
    }
</style>
@endpush

@push('scripts')
<script>
let currentNews = [];

$(document).ready(function() {
    // Quick search buttons
    $('.quick-search').click(function() {
        const query = $(this).data('query');
        $('#searchQuery').val(query);
        $('#fetchNewsBtn').click();
    });
    
    // Fetch news button
    $('#fetchNewsBtn').click(function() {
        const query = $('#searchQuery').val().trim();
        const limit = $('#maxResults').val();
        
        if (!query) {
            alert('Please enter a search query');
            return;
        }
        
        fetchNews(query, limit);
    });
    
    // Enter key on search input
    $('#searchQuery').keypress(function(e) {
        if (e.which === 13) {
            $('#fetchNewsBtn').click();
        }
    });
});

function fetchNews(query, limit) {
    // Show loading
    $('#emptyState').hide();
    $('#newsResults').hide();
    $('#loadingState').show();
    
    $.ajax({
        url: '{{ route("admin.articles.fetch-news") }}',
        method: 'POST',
        data: {
            query: query,
            limit: limit
        },
        success: function(response) {
            if (response.success) {
                currentNews = response.data;
                displayNews(currentNews);
            } else {
                alert('Failed to fetch news');
                $('#loadingState').hide();
                $('#emptyState').show();
            }
        },
        error: function() {
            alert('Error fetching news. Please try again.');
            $('#loadingState').hide();
            $('#emptyState').show();
        }
    });
}

function displayNews(news) {
    $('#loadingState').hide();
    
    if (news.length === 0) {
        $('#emptyState').show();
        return;
    }
    
    $('#resultsCount').text(news.length + ' articles');
    
    let html = '';
    news.forEach((article, index) => {
        html += `
            <div class="news-item" id="news-${index}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="news-title">${article.title}</div>
                    <button class="btn btn-sm btn-success import-btn" 
                            data-index="${index}"
                            data-title="${escapeHtml(article.title)}"
                            data-description="${escapeHtml(article.description)}"
                            data-url="${escapeHtml(article.url)}"
                            data-source="${escapeHtml(article.source)}">
                        <i class="fas fa-download"></i> Import
                    </button>
                </div>
                <div class="news-description">${article.description}</div>
                <div class="news-meta d-flex flex-wrap gap-2 align-items-center">
                    <span><i class="fas fa-newspaper"></i> ${article.source}</span>
                    <span>•</span>
                    <span><i class="fas fa-clock"></i> ${formatDate(article.published_at)}</span>
                    <span>•</span>
                    <a href="${article.url}" target="_blank" class="text-primary">
                        <i class="fas fa-external-link-alt"></i> Read Original
                    </a>
                    <span>•</span>
                    <span>Category:</span>
                    <select class="form-select form-select-sm d-inline-block" style="width: auto;" data-index="${index}">
                        <option value="logistics">Logistics</option>
                        <option value="economy">Economy</option>
                        <option value="geopolitics">Geopolitics</option>
                        <option value="weather">Weather</option>
                    </select>
                </div>
            </div>
        `;
    });
    
    $('#newsList').html(html);
    $('#newsResults').show();
    
    // Attach import handlers
    $('.import-btn').click(function() {
        const index = $(this).data('index');
        const category = $(`.form-select[data-index="${index}"]`).val();
        importArticle(index, category);
    });
}

function importArticle(index, category) {
    const article = currentNews[index];
    const button = $(`.import-btn[data-index="${index}"]`);
    
    // Disable button
    button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Importing...');
    
    $.ajax({
        url: '{{ route("admin.articles.import-news") }}',
        method: 'POST',
        data: {
            title: article.title,
            description: article.description,
            content: article.description,  // Full content
            url: article.url,
            source: article.source,
            category: category
        },
        success: function(response) {
            if (response.success) {
                // Mark as imported
                $(`#news-${index}`).addClass('imported');
                button.removeClass('btn-success').addClass('btn-secondary')
                      .html('<i class="fas fa-check"></i> Imported')
                      .prop('disabled', true);
                
                // Show success notification with sentiment
                const sentimentInfo = response.sentiment ? 
                    ` (Sentiment: ${response.sentiment.sentiment} - ${response.sentiment.confidence}%)` : '';
                showNotification('Success!', response.message + sentimentInfo, 'success');
            } else {
                alert(response.message);
                button.prop('disabled', false).html('<i class="fas fa-download"></i> Import');
            }
        },
        error: function(xhr) {
            const message = xhr.responseJSON?.message || 'Failed to import article';
            alert(message);
            button.prop('disabled', false).html('<i class="fas fa-download"></i> Import');
        }
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
    
    if (diffHours < 1) {
        return 'Just now';
    } else if (diffHours < 24) {
        return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    } else {
        const diffDays = Math.floor(diffHours / 24);
        return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
    }
}

function showNotification(title, message, type) {
    const bgColor = type === 'success' ? '#10b981' : '#ef4444';
    const notification = $(`
        <div class="alert alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 99999; min-width: 300px; background-color: ${bgColor}; color: white; border: none;">
            <strong>${title}</strong> ${message}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    $('body').append(notification);
    
    setTimeout(() => {
        notification.alert('close');
    }, 5000);
}
</script>
@endpush
