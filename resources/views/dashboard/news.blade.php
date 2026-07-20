@extends('layouts.app')

@section('title', 'News Intelligence')

@push('styles')
<style>
    /* Modern Light Theme for News Intelligence */
    :root {
        --primary-blue: #4f46e5;
        --primary-light: #818cf8;
        --success-green: #10b981;
        --warning-orange: #f59e0b;
        --danger-red: #ef4444;
        --gray-50: #f9fafb;
        --gray-100: #f3f4f6;
        --gray-200: #e5e7eb;
        --gray-600: #4b5563;
        --gray-800: #1f2937;
    }
    
    /* Page Header */
    .page-header {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        border-radius: 24px;
        padding: 2.5rem;
        margin-bottom: 2rem;
        color: white;
        box-shadow: 0 20px 60px rgba(139, 92, 246, 0.3);
        position: relative;
        overflow: hidden;
    }
    
    .page-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
        border-radius: 50%;
    }
    
    .page-header h2 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        position: relative;
        z-index: 1;
        color: white;
    }
    
    .page-header p {
        font-size: 1.1rem;
        opacity: 0.95;
        margin-bottom: 1rem;
        position: relative;
        z-index: 1;
    }
    
    .page-header .alert {
        background: rgba(255, 255, 255, 0.2);
        border: 2px solid rgba(255, 255, 255, 0.3);
        color: white;
        backdrop-filter: blur(10px);
        border-radius: 16px;
    }
    
    /* Sentiment Cards */
    .sentiment-card {
        border-radius: 16px;
        border: 2px solid var(--gray-100);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    
    .sentiment-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
    
    /* Filter Section */
    .filter-section {
        background: white;
        border-radius: 20px;
        padding: 1.75rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        margin-bottom: 2rem;
        border: 1px solid var(--gray-200);
    }
    
    .filter-section .input-group {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        border: 2px solid var(--gray-100);
        transition: all 0.3s ease;
    }
    
    .filter-section .input-group:focus-within {
        border-color: #8b5cf6;
        box-shadow: 0 4px 20px rgba(139, 92, 246, 0.2);
    }
    
    .filter-section .input-group-text {
        background: white;
        border: none;
        padding: 0.85rem 1.2rem;
        color: var(--gray-600);
    }
    
    .filter-section .form-control,
    .filter-section .form-select {
        border: 2px solid var(--gray-100);
        border-radius: 16px;
        padding: 0.85rem 1.2rem;
        font-size: 1rem;
        transition: all 0.3s ease;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .filter-section .form-control:focus,
    .filter-section .form-select:focus {
        border-color: #8b5cf6;
        box-shadow: 0 4px 20px rgba(139, 92, 246, 0.2);
        outline: none;
    }
    
    /* News Article Cards */
    .news-card {
        border-radius: 16px;
        border: 2px solid var(--gray-100);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        background: white;
        overflow: hidden;
    }
    
    .news-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        border-color: #8b5cf6;
    }
    
    /* Sentiment Badges */
    .badge {
        padding: 0.5rem 1rem;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.85rem;
        letter-spacing: 0.3px;
    }
    
    .badge.bg-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
    }
    
    .badge.bg-danger {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
    }
    
    .badge.bg-info {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%) !important;
    }
    
    /* Buttons */
    .btn-primary {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        border: none;
        border-radius: 16px;
        padding: 0.85rem 1.5rem;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(139, 92, 246, 0.5);
        background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
    }
    
    /* Loading Spinner */
    .spinner-border {
        border-color: #8b5cf6;
        border-right-color: transparent;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .page-header {
            padding: 1.5rem;
        }
        
        .page-header h2 {
            font-size: 1.75rem;
        }
        
        .filter-section {
            padding: 1.25rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Modern Page Header -->
    <div class="page-header">
        <h2>📰 News Intelligence Dashboard</h2>
        <p>AI-powered sentiment analysis for supply chain news</p>
        <div class="alert mb-0">
            <i class="bi bi-info-circle"></i> <strong>Why this matters:</strong> 
            News sentiment helps predict supply chain disruptions. Negative news → Higher risk.
            <br><small><i class="bi bi-robot"></i> Powered by AI Lexicon-Based Sentiment Analysis | <i class="bi bi-clock"></i> Updates every 6 hours</small>
        </div>
    </div>

    <!-- Sentiment Overview Cards -->
    <div class="row mb-3 g-3" id="sentimentCards" style="display: none;">
        <div class="col-md-3">
            <div class="card sentiment-card">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Positive News</p>
                            <h4 class="mb-0 fw-bold text-success" id="positiveCount">0</h4>
                            <small class="text-muted" id="positivePercent">0%</small>
                        </div>
                        <i class="bi bi-emoji-smile fs-2 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card sentiment-card">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Neutral News</p>
                            <h4 class="mb-0 fw-bold text-info" id="neutralCount">0</h4>
                            <small class="text-muted" id="neutralPercent">0%</small>
                        </div>
                        <i class="bi bi-emoji-neutral fs-2 text-info"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card sentiment-card">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Negative News</p>
                            <h4 class="mb-0 fw-bold text-danger" id="negativeCount">0</h4>
                            <small class="text-muted" id="negativePercent">0%</small>
                        </div>
                        <i class="bi bi-emoji-frown fs-2 text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card sentiment-card">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Articles</p>
                            <h4 class="mb-0 fw-bold text-primary" id="totalArticles">0</h4>
                            <small class="text-muted" id="overallSentiment">-</small>
                        </div>
                        <i class="bi bi-newspaper fs-2 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Skeleton for Sentiment Cards -->
    <div class="row mb-3 g-3" id="sentimentLoading">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="placeholder-glow">
                        <span class="placeholder col-6"></span>
                        <span class="placeholder col-8"></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="placeholder-glow">
                        <span class="placeholder col-6"></span>
                        <span class="placeholder col-8"></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="placeholder-glow">
                        <span class="placeholder col-6"></span>
                        <span class="placeholder col-8"></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3">
                    <div class="placeholder-glow">
                        <span class="placeholder col-6"></span>
                        <span class="placeholder col-8"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <div class="row g-3">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-globe"></i>
                    </span>
                    <input type="text" class="form-control" id="countryFilter" list="countryOptions" placeholder="Search country or type any topic...">
                    <datalist id="countryOptions">
                        @foreach($countries as $country)
                            <option value="{{ $country->name }}">
                        @endforeach
                    </datalist>
                    <button class="btn btn-primary" type="button" id="btnSearchCountry">
                        <i class="bi bi-search"></i> Search API
                    </button>
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="sentimentFilter">
                <option value="">All Sentiment</option>
                <option value="positive">😊 Positive Only</option>
                <option value="neutral">😐 Neutral Only</option>
                <option value="negative">😟 Negative Only</option>
            </select>
        </div>
        <div class="col-md-2">
            <select class="form-select form-select-sm" id="limitFilter">
                <option value="10">10 Articles</option>
                <option value="20" selected>20 Articles</option>
                <option value="30">30 Articles</option>
                <option value="50">50 Articles</option>
            </select>
        </div>
        <div class="col-md-2">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control" id="searchNews" placeholder="Search news...">
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loadingState" class="text-center py-5">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
        <p class="mt-3 text-muted">Loading news articles...</p>
    </div>

    <!-- News Grid -->
    <div id="newsGrid" class="row" style="display: none;">
        <!-- News cards will be inserted here -->
    </div>

    <!-- No Results -->
    <div id="noResults" class="alert alert-info text-center" style="display: none;">
        <i class="bi bi-search fs-1"></i>
        <p class="mb-0 mt-2">No news articles found matching your criteria</p>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* News Grid Container - Use CSS Grid for equal heights */
    #newsGrid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 1.5rem;
        align-items: stretch;
    }
    
    .news-card {
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        background-color: #ffffffff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        overflow: hidden;
        height: 100%;
        position: relative;
    }
    
    .news-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 30px rgba(0, 13, 255, 0.3);
    }
    
    .news-card .card-body {
        display: flex;
        flex-direction: column;
        padding: 1.5rem;
        flex: 1;
        color: #e0e0e0;
    }
    
    .news-title {
        font-size: 1.1rem;
        font-weight: 600;
        line-height: 1.5;
        margin-bottom: 0.75rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .news-title a {
        color: #0015ffff;
        text-decoration: none;
        transition: color 0.2s ease;
    }

    .news-title a:hover {
        color: #ff0000ff;
    }
    
    .news-description {
        font-size: 0.9rem;
        line-height: 1.6;
        flex: 1;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 1.5rem;
        color: #000000ff;
    }
    
    .news-footer {
        margin-top: auto;
    }
    
    /* Update sentiment cards for dark theme */
    .sentiment-card {
        background-color: #1e1b4b;
        border: 1px solid rgba(124, 58, 237, 0.2);
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    }
    
    .sentiment-card .card-body {
        color: #e0e0e0;
    }
    
    .sentiment-card .text-muted {
        color: #b0b0b0 !important;
    }
</style>
@endpush

@push('scripts')
<script>
let allNews = [];
let currentCountry = '';

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    console.log('News Intelligence: Page loaded');
    console.log('News Intelligence: Setting up event listeners...');
    setupEventListeners();
    console.log('News Intelligence: Loading news...');
    loadNews();
});

function setupEventListeners() {
    // Check if refresh button exists (optional)
    const btnRefresh = document.getElementById('btnRefresh');
    if (btnRefresh) {
        btnRefresh.addEventListener('click', function() {
            loadNews(true);
        });
    }
    
    document.getElementById('btnSearchCountry').addEventListener('click', function() {
        currentCountry = document.getElementById('countryFilter').value;
        loadNews();
    });

    document.getElementById('countryFilter').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            currentCountry = this.value;
            loadNews();
        }
    });
    
    // Add event listener for limit filter
    document.getElementById('limitFilter').addEventListener('change', function() {
        loadNews(); // Reload with new limit
    });
    
    document.getElementById('sentimentFilter').addEventListener('change', filterNews);
    document.getElementById('searchNews').addEventListener('input', filterNews);
}

async function loadNews(forceRefresh = false) {
    console.log('loadNews called, forceRefresh:', forceRefresh);
    
    const loadingState = document.getElementById('loadingState');
    const newsGrid = document.getElementById('newsGrid');
    const sentimentCards = document.getElementById('sentimentCards');
    const sentimentLoading = document.getElementById('sentimentLoading');
    const btnRefresh = document.getElementById('btnRefresh'); // May be null
    const limit = document.getElementById('limitFilter').value;
    
    console.log('Elements found:', {
        loadingState: !!loadingState,
        newsGrid: !!newsGrid,
        sentimentCards: !!sentimentCards,
        sentimentLoading: !!sentimentLoading,
        limitFilter: limit
    });
    
    // Show loading
    loadingState.style.display = 'block';
    newsGrid.style.display = 'none';
    sentimentCards.style.display = 'none';
    sentimentLoading.style.display = 'flex';
    
    if (btnRefresh) {
        btnRefresh.disabled = true;
        btnRefresh.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Loading...';
    }
    
    try {
        const country = currentCountry || '';
        const url = `/api/news/search?q=${encodeURIComponent(country)}&limit=${limit}`;
        
        console.log('Fetching from URL:', url);
        
        // Add timeout to prevent hanging
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout
        
        const response = await fetch(url, {
            signal: controller.signal
        });
        
        clearTimeout(timeoutId);
        
        console.log('Response status:', response.status);
        
        const data = await response.json();
        
        console.log('Response data:', data);
        
        if (data.success) {
            allNews = data.data.articles;
            console.log('Total articles loaded:', allNews.length);
            
            displayNews(allNews);
            updateSentimentStats(data.data.sentiment_analysis);
            
            // Show content
            loadingState.style.display = 'none';
            newsGrid.style.display = 'grid';
            sentimentLoading.style.display = 'none';
            sentimentCards.style.display = 'flex';
            
            // Show cache info if available
            if (data.cached) {
                console.log('Loaded from cache');
            }
            
            console.log('News loaded successfully!');
        } else {
            throw new Error(data.message || 'Failed to load news');
        }
    } catch (error) {
        console.error('Error loading news:', error);
        
        let errorMessage = 'Terjadi kesalahan saat memuat berita';
        if (error.name === 'AbortError') {
            errorMessage = 'Request timeout. Server mungkin sedang sibuk.';
        }
        
        loadingState.innerHTML = `
            <div class="alert alert-danger mx-auto" style="max-width: 600px;">
                <div class="text-center">
                    <i class="bi bi-exclamation-triangle fs-1"></i>
                    <h5 class="mt-3">Gagal Memuat News</h5>
                    <p class="text-muted">${errorMessage}</p>
                    <p class="text-muted small">${error.message}</p>
                    <button class="btn btn-primary mt-2" onclick="loadNews(true)">
                        <i class="bi bi-arrow-clockwise"></i> Coba Lagi
                    </button>
                </div>
            </div>
        `;
    } finally {
        if (btnRefresh) {
            btnRefresh.disabled = false;
            btnRefresh.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Refresh';
        }
    }
}

function displayNews(articles) {
    const newsGrid = document.getElementById('newsGrid');
    newsGrid.innerHTML = '';
    
    if (articles.length === 0) {
        document.getElementById('noResults').style.display = 'block';
        return;
    }
    
    document.getElementById('noResults').style.display = 'none';
    
    articles.forEach(article => {
        const sentimentColor = getSentimentColor(article.sentiment);
        const sentimentIcon = getSentimentIcon(article.sentiment);
        const sentimentLabel = article.sentiment.charAt(0).toUpperCase() + article.sentiment.slice(1);
        
        const card = `
            <div class="news-card border-0 border-start border-4 border-${sentimentColor}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-${sentimentColor} bg-opacity-10 text-${sentimentColor} px-3 py-2 rounded-pill border border-${sentimentColor} border-opacity-25">
                            ${sentimentIcon} ${sentimentLabel}
                        </span>
                        <small class="text-muted"><i class="bi bi-clock me-1"></i>${timeAgo(article.published_at)}</small>
                    </div>
                    <h5 class="news-title">
                        <a href="${article.url}" target="_blank" class="stretched-link">
                            ${article.title}
                        </a>
                    </h5>
                    <p class="news-description">${article.description || 'No description available'}</p>
                    
                    <div class="news-footer">
                        ${article.sentiment_confidence ? `
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="text-muted font-monospace" style="font-size: 0.75rem;">AI CONFIDENCE</small>
                                    <small class="text-${sentimentColor} fw-bold">${article.sentiment_confidence}%</small>
                                </div>
                                <div class="progress" style="height: 4px; background-color: rgba(255,255,255,0.05);">
                                    <div class="progress-bar bg-${sentimentColor}" style="width: ${article.sentiment_confidence}%"></div>
                                </div>
                            </div>
                        ` : ''}
                        
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top border-secondary border-opacity-25">
                            <span class="badge bg-secondary bg-opacity-25 text-light fw-normal">
                                <i class="bi bi-building me-1"></i> ${article.source}
                            </span>
                            <span class="text-primary small fw-medium" style="position: relative; z-index: 2;">
                                Read <i class="bi bi-arrow-right ms-1"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        newsGrid.innerHTML += card;
    });
}

function updateSentimentStats(analysis) {
    document.getElementById('positiveCount').textContent = analysis.positive;
    document.getElementById('neutralCount').textContent = analysis.neutral;
    document.getElementById('negativeCount').textContent = analysis.negative;
    document.getElementById('totalArticles').textContent = analysis.total;
    
    document.getElementById('positivePercent').textContent = `${analysis.positive_percentage}%`;
    document.getElementById('neutralPercent').textContent = `${analysis.neutral_percentage}%`;
    document.getElementById('negativePercent').textContent = `${analysis.negative_percentage}%`;
    
    const overallIcon = getSentimentIcon(analysis.overall_sentiment);
    const overallLabel = analysis.overall_sentiment.charAt(0).toUpperCase() + analysis.overall_sentiment.slice(1);
    document.getElementById('overallSentiment').textContent = `${overallIcon} Overall: ${overallLabel}`;
}

function filterNews() {
    const sentimentFilter = document.getElementById('sentimentFilter').value;
    const searchTerm = document.getElementById('searchNews').value.toLowerCase();
    
    const filtered = allNews.filter(article => {
        const matchSentiment = !sentimentFilter || article.sentiment === sentimentFilter;
        const matchSearch = !searchTerm || 
                          article.title.toLowerCase().includes(searchTerm) ||
                          (article.description && article.description.toLowerCase().includes(searchTerm));
        
        return matchSentiment && matchSearch;
    });
    
    displayNews(filtered);
}

function getSentimentColor(sentiment) {
    return sentiment === 'positive' ? 'success' : (sentiment === 'negative' ? 'danger' : 'secondary');
}

function getSentimentIcon(sentiment) {
    return sentiment === 'positive' ? '😊' : (sentiment === 'negative' ? '😟' : '😐');
}

function timeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    
    if (seconds < 60) return 'Just now';
    if (seconds < 3600) return Math.floor(seconds / 60) + ' minutes ago';
    if (seconds < 86400) return Math.floor(seconds / 3600) + ' hours ago';
    return Math.floor(seconds / 86400) + ' days ago';
}
</script>
@endpush
