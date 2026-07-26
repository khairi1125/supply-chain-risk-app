@extends('layouts.app')

@section('title', 'News Intelligence')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --bg-dark:       #f8fafc;
        --bg-card:       #ffffff;
        --bg-card2:      #f1f5f9;
        --border:        rgba(0,0,0,0.08);
        --accent-purple: #7c3aed;
        --accent-blue:   #2563eb;
        --positive:      #059669;
        --neutral:       #475569;
        --negative:      #dc2626;
        --text-primary:  #0f172a;
        --text-muted:    #64748b;
    }

    body, .container-fluid { font-family: 'Inter', sans-serif; }

    /* ─── PAGE HEADER ──────────────────────────────────────── */
    .ni-header {
        background: linear-gradient(135deg, #f3e8ff 0%, #ffffff 40%, #eff6ff 100%);
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 2rem 2.5rem;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
    }
    .ni-header::before {
        content: '';
        position: absolute;
        top: -60px; right: -60px;
        width: 300px; height: 300px;
        background: radial-gradient(circle, rgba(124,58,237,0.1) 0%, transparent 70%);
        border-radius: 50%;
    }
    .ni-header h1 { font-size: 1.8rem; font-weight: 800; color: var(--text-primary); margin: 0 0 0.25rem; }
    .ni-header p  { color: var(--text-muted); margin: 0; font-size: 0.9rem; }
    .ni-badge {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(124,58,237,0.1); border: 1px solid rgba(124,58,237,0.2);
        color: var(--accent-purple); padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 500;
        margin-top: 0.75rem;
    }

    /* ─── SENTIMENT STATS BAR ───────────────────────────────── */
    .stats-bar {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    .stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 1.1rem 1.25rem;
        display: flex; align-items: center; gap: 1rem;
        transition: transform .2s, box-shadow .2s;
    }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.4); }
    .stat-icon {
        width: 42px; height: 42px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem; flex-shrink: 0;
    }
    .stat-card.positive .stat-icon { background: rgba(16,185,129,.15); }
    .stat-card.neutral  .stat-icon { background: rgba(100,116,139,.15); }
    .stat-card.negative .stat-icon { background: rgba(239,68,68,.15); }
    .stat-card.total    .stat-icon { background: rgba(59,130,246,.15); }
    .stat-label { font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: .5px; }
    .stat-value { font-size: 1.6rem; font-weight: 700; color: var(--text-primary); line-height: 1; }
    .stat-pct   { font-size: 0.75rem; margin-top: 2px; }
    .stat-card.positive .stat-pct { color: var(--positive); }
    .stat-card.neutral  .stat-pct { color: var(--neutral); }
    .stat-card.negative .stat-pct { color: var(--negative); }
    .stat-card.total    .stat-pct { color: var(--accent-blue); }

    /* ─── FILTER BAR ────────────────────────────────────────── */
    .filter-bar {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 1rem 1.25rem;
        margin-bottom: 2rem;
        display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;
    }
    .filter-bar .form-control,
    .filter-bar .form-select {
        background: var(--bg-dark);
        border: 1px solid var(--border);
        color: var(--text-primary);
        border-radius: 10px;
        font-size: 0.875rem;
    }
    .filter-bar .form-control:focus,
    .filter-bar .form-select:focus {
        border-color: var(--accent-purple);
        box-shadow: 0 0 0 3px rgba(139,92,246,.2);
        background: var(--bg-dark);
        color: var(--text-primary);
    }
    .filter-bar .form-control::placeholder { color: var(--text-muted); }
    .filter-bar .form-select option { background: #ffffff; }
    .btn-search {
        background: linear-gradient(135deg, var(--accent-purple), var(--accent-blue));
        border: none; color: white;
        padding: 0.5rem 1.2rem; border-radius: 10px;
        font-weight: 600; font-size: 0.875rem;
        transition: opacity .2s;
        white-space: nowrap;
    }
    .btn-search:hover { opacity: 0.88; color: white; }
    .btn-refresh {
        background: var(--bg-dark);
        border: 1px solid var(--border); color: var(--text-muted);
        padding: 0.5rem 1rem; border-radius: 10px;
        font-size: 0.875rem; transition: all .2s;
        white-space: nowrap;
    }
    .btn-refresh:hover { border-color: var(--accent-purple); color: var(--accent-purple); }

    /* ─── SECTION TITLE ─────────────────────────────────────── */
    .section-label {
        font-size: 0.75rem; font-weight: 600; text-transform: uppercase;
        letter-spacing: 1px; color: var(--text-muted);
        margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;
    }
    .section-label::after { content: ''; flex: 1; height: 1px; background: var(--border); }

    /* ─── FEATURED CARD (1st article) ───────────────────────── */
    .featured-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 20px;
        overflow: hidden;
        margin-bottom: 1.5rem;
        display: grid;
        grid-template-columns: 1fr 420px;
        min-height: 300px;
        cursor: pointer;
        transition: box-shadow .3s, transform .3s;
        text-decoration: none;
    }
    .featured-card:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(0,0,0,0.1); }
    .featured-body {
        padding: 2rem 2rem;
        display: flex; flex-direction: column; justify-content: space-between;
    }
    .featured-img {
        position: relative; overflow: hidden;
    }
    .featured-img img {
        width: 100%; height: 100%; object-fit: cover;
        transition: transform .5s ease;
    }
    .featured-card:hover .featured-img img { transform: scale(1.05); }
    .featured-title {
        font-size: 1.4rem; font-weight: 700; color: var(--text-primary);
        line-height: 1.5; margin-bottom: 0.75rem;
        display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
    }
    .featured-desc {
        color: var(--text-muted); font-size: 0.9rem; line-height: 1.7;
        display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;
    }

    /* ─── NEWS GRID ─────────────────────────────────────────── */
    #newsGrid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.25rem;
    }
    .news-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: 16px;
        overflow: hidden;
        display: flex; flex-direction: column;
        transition: transform .25s, box-shadow .25s, border-color .25s;
        text-decoration: none;
        cursor: pointer;
    }
    .news-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.1);
        border-color: rgba(139,92,246,0.3);
    }
    /* Category image area */
    .card-img-wrap {
        width: 100%; height: 180px;
        position: relative; overflow: hidden; flex-shrink: 0;
    }
    .card-img-wrap img {
        width: 100%; height: 100%; object-fit: cover;
        transition: transform .4s ease;
    }
    .news-card:hover .card-img-wrap img { transform: scale(1.08); }
    /* Category placeholder fallback */
    .img-placeholder {
        width: 100%; height: 100%;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center; gap: 8px;
    }
    .img-placeholder .ph-icon { font-size: 3rem; }
    .img-placeholder .ph-label { font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; opacity: .7; }

    /* Category colors for placeholder */
    .ph-logistics   { background: linear-gradient(135deg, #e0f2fe, #bae6fd); color: #0369a1; }
    .ph-economy     { background: linear-gradient(135deg, #dcfce7, #bbf7d0); color: #15803d; }
    .ph-geopolitics { background: linear-gradient(135deg, #fee2e2, #fecaca); color: #b91c1c; }
    .ph-weather     { background: linear-gradient(135deg, #fef9c3, #fef08a); color: #a16207; }

    .card-body-content { padding: 1.1rem 1.1rem 1rem; flex: 1; display: flex; flex-direction: column; }
    .card-meta {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 0.6rem;
    }
    .card-title {
        font-size: 0.95rem; font-weight: 600; color: var(--text-primary);
        line-height: 1.5; margin-bottom: 0.5rem;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .card-desc {
        font-size: 0.8rem; color: var(--text-muted); line-height: 1.6;
        flex: 1;
        display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .card-footer-row {
        display: flex; justify-content: space-between; align-items: center;
        margin-top: 0.8rem; padding-top: 0.75rem;
        border-top: 1px solid var(--border);
        font-size: 0.75rem;
    }
    .source-badge {
        background: rgba(0,0,0,0.05);
        border-radius: 6px; padding: 2px 8px;
        color: var(--text-muted); font-size: 0.72rem;
        max-width: 130px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .time-label { color: var(--text-muted); }

    /* ─── SENTIMENT BADGES ──────────────────────────────────── */
    .sbadge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 600;
    }
    .sbadge.positive { background: rgba(16,185,129,.15); color: #34d399; border: 1px solid rgba(16,185,129,.25); }
    .sbadge.neutral  { background: rgba(100,116,139,.15); color: #94a3b8; border: 1px solid rgba(100,116,139,.25); }
    .sbadge.negative { background: rgba(239,68,68,.15);   color: #f87171; border: 1px solid rgba(239,68,68,.25); }

    /* ─── CATEGORY BADGE ────────────────────────────────────── */
    .catbadge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 8px; border-radius: 6px; font-size: 0.68rem; font-weight: 600;
        text-transform: uppercase; letter-spacing: .5px;
    }
    .catbadge.logistics   { background: rgba(59,130,246,.12);  color: #60a5fa; }
    .catbadge.economy     { background: rgba(16,185,129,.12);  color: #34d399; }
    .catbadge.geopolitics { background: rgba(239,68,68,.12);   color: #f87171; }
    .catbadge.weather     { background: rgba(245,158,11,.12);  color: #fbbf24; }

    /* ─── LOADING ───────────────────────────────────────────── */
    .loading-wrap { text-align: center; padding: 4rem 0; }
    .loading-spinner {
        width: 52px; height: 52px; border: 3px solid rgba(139,92,246,.2);
        border-top-color: var(--accent-purple);
        border-radius: 50%; animation: spin .8s linear infinite; margin: 0 auto 1rem;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .loading-wrap p { color: var(--text-muted); font-size: 0.9rem; }

    /* ─── CONFIDENCE BAR ────────────────────────────────────── */
    .conf-bar { height: 3px; background: rgba(255,255,255,0.07); border-radius: 2px; margin-top: 4px; overflow: hidden; }
    .conf-fill { height: 100%; border-radius: 2px; transition: width .6s ease; }
    .conf-fill.positive { background: var(--positive); }
    .conf-fill.neutral  { background: var(--neutral); }
    .conf-fill.negative { background: var(--negative); }

    /* ─── RESPONSIVE ────────────────────────────────────────── */
    @media (max-width: 992px) {
        #newsGrid { grid-template-columns: repeat(2, 1fr); }
        .featured-card { grid-template-columns: 1fr; }
        .featured-img { height: 200px; }
        .stats-bar { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 576px) {
        #newsGrid { grid-template-columns: 1fr; }
        .stats-bar { grid-template-columns: repeat(2, 1fr); }
        .filter-bar { flex-direction: column; }
        .filter-bar > * { width: 100%; }
    }

    /* ─── SKELETON LOADER ───────────────────────────────────── */
    .skeleton { background: linear-gradient(90deg, #1c2128 25%, #2d333b 50%, #1c2128 75%); background-size: 200% 100%; animation: shimmer 1.5s infinite; border-radius: 8px; }
    @keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

    /* ─── NO RESULTS ────────────────────────────────────────── */
    .no-results {
        text-align: center; padding: 4rem 1rem;
        color: var(--text-muted); display: none;
    }
    .no-results .icon { font-size: 3.5rem; opacity: .3; margin-bottom: 1rem; }

    /* sentinel top bar per sentimen */
    .news-card.positive { border-top: 3px solid var(--positive); }
    .news-card.neutral  { border-top: 3px solid var(--neutral); }
    .news-card.negative { border-top: 3px solid var(--negative); }
    .featured-card.positive { border-top: 3px solid var(--positive); }
    .featured-card.neutral  { border-top: 3px solid var(--neutral); }
    .featured-card.negative { border-top: 3px solid var(--negative); }
</style>
@endpush

@section('content')
<div class="container-fluid pb-5">

    {{-- ── PAGE HEADER ─────────────────────────────────────── --}}
    <div class="ni-header">
        <h1>📰 News Intelligence</h1>
        <p>Real-time global supply chain news with AI-powered sentiment analysis</p>
        <div class="ni-badge">
            <i class="bi bi-robot"></i> Lexicon-Based AI &nbsp;·&nbsp;
            <i class="bi bi-clock"></i> Updates every 6 hours &nbsp;·&nbsp;
            <i class="bi bi-newspaper"></i> Powered by GNews API
        </div>
    </div>

    {{-- ── STAT CARDS ───────────────────────────────────────── --}}
    <div class="stats-bar" id="statCards" style="display:none;">
        <div class="stat-card positive">
            <div class="stat-icon">😊</div>
            <div>
                <div class="stat-label">Positive</div>
                <div class="stat-value text-success" id="positiveCount">0</div>
                <div class="stat-pct" id="positivePercent">0%</div>
            </div>
        </div>
        <div class="stat-card neutral">
            <div class="stat-icon">😐</div>
            <div>
                <div class="stat-label">Neutral</div>
                <div class="stat-value" style="color:#94a3b8" id="neutralCount">0</div>
                <div class="stat-pct" id="neutralPercent">0%</div>
            </div>
        </div>
        <div class="stat-card negative">
            <div class="stat-icon">😟</div>
            <div>
                <div class="stat-label">Negative</div>
                <div class="stat-value text-danger" id="negativeCount">0</div>
                <div class="stat-pct" id="negativePercent">0%</div>
            </div>
        </div>
        <div class="stat-card total">
            <div class="stat-icon">📰</div>
            <div>
                <div class="stat-label">Total Articles</div>
                <div class="stat-value text-primary" id="totalArticles">0</div>
                <div class="stat-pct" id="overallSentiment" style="color:var(--accent-blue);">–</div>
            </div>
        </div>
    </div>

    {{-- skeleton for stat cards --}}
    <div class="stats-bar" id="statSkeleton">
        @for($i=0;$i<4;$i++)
        <div class="stat-card">
            <div class="skeleton" style="width:42px;height:42px;border-radius:12px;"></div>
            <div style="flex:1">
                <div class="skeleton" style="height:12px;width:60%;margin-bottom:8px;"></div>
                <div class="skeleton" style="height:28px;width:40%;"></div>
            </div>
        </div>
        @endfor
    </div>

    {{-- ── FILTER BAR ───────────────────────────────────────── --}}
    <div class="filter-bar">
        <input type="text" class="form-control" id="countryFilter" style="flex:2;min-width:200px;"
               list="countryOptions" placeholder="🔍  Search by country or topic…">
        <datalist id="countryOptions">
            @foreach($countries as $c)
                <option value="{{ $c->name }}">
            @endforeach
        </datalist>
        <button class="btn-search" id="btnSearchCountry">
            <i class="bi bi-search me-1"></i> Search
        </button>

        <select class="form-select" id="sentimentFilter" style="max-width:170px;">
            <option value="">All Sentiment</option>
            <option value="positive">😊 Positive</option>
            <option value="neutral">😐 Neutral</option>
            <option value="negative">😟 Negative</option>
        </select>
        <input type="text" class="form-control" id="searchNews" style="max-width:180px;" placeholder="Filter results…">
        <button class="btn-refresh" id="btnRefresh">
            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
        </button>
    </div>

    {{-- ── LOADING STATE ────────────────────────────────────── --}}
    <div class="loading-wrap" id="loadingState">
        <div class="loading-spinner"></div>
        <p>Fetching latest news with AI sentiment analysis…</p>
    </div>

    {{-- ── FEATURED CARD ────────────────────────────────────── --}}
    <div id="featuredWrap" style="display:none;">
        <div class="section-label">⭐ Featured Story</div>
        <a id="featuredCard" class="featured-card" href="#" target="_blank">
            <div class="featured-body">
                <div>
                    <div style="display:flex;gap:8px;margin-bottom:1rem;" id="featuredBadges"></div>
                    <div class="featured-title" id="featuredTitle">—</div>
                    <div class="featured-desc" id="featuredDesc">—</div>
                </div>
                <div style="margin-top:1.5rem;display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:0.8rem;color:var(--text-muted);">
                        <i class="bi bi-building me-1"></i><span id="featuredSource">—</span>
                    </span>
                    <span style="font-size:0.8rem;color:var(--text-muted);">
                        <i class="bi bi-clock me-1"></i><span id="featuredTime">—</span>
                    </span>
                </div>
                {{-- AI confidence bar --}}
                <div style="margin-top:0.75rem;">
                    <div style="display:flex;justify-content:space-between;font-size:0.72rem;color:var(--text-muted);margin-bottom:4px;">
                        <span>AI Sentiment Confidence</span>
                        <span id="featuredConf">—</span>
                    </div>
                    <div class="conf-bar">
                        <div class="conf-fill neutral" id="featuredConfBar" style="width:0%"></div>
                    </div>
                </div>
            </div>
            <div class="featured-img" id="featuredImgWrap">
                {{-- filled by JS --}}
            </div>
        </a>
    </div>

    {{-- ── NEWS GRID ─────────────────────────────────────────── --}}
    <div id="newsWrap" style="display:none;">
        <div class="section-label mt-2">📰 Latest Articles</div>
        <div id="newsGrid"></div>
    </div>

    {{-- ── NO RESULTS ───────────────────────────────────────── --}}
    <div class="no-results" id="noResults">
        <div class="icon">🔍</div>
        <h5 style="color:var(--text-primary);">No articles found</h5>
        <p>Try adjusting your filters or search term.</p>
    </div>

</div>
@endsection

@push('scripts')
<script>
// ─── STATE ──────────────────────────────────────────────────────────────────
let allNews   = [];
let currentCountry = '';

// ─── CATEGORY CONFIG ────────────────────────────────────────────────────────
const CAT_CONFIG = {
    logistics:   { icon: '🚢', label: 'Logistics',   class: 'ph-logistics',   badge: 'logistics'   },
    economy:     { icon: '📈', label: 'Economy',     class: 'ph-economy',     badge: 'economy'     },
    geopolitics: { icon: '🌐', label: 'Geopolitics', class: 'ph-geopolitics', badge: 'geopolitics' },
    weather:     { icon: '🌪️', label: 'Weather',     class: 'ph-weather',     badge: 'weather'     },
};
const SENT_CONFIG = {
    positive: { icon: '😊', label: 'Positive', cls: 'positive' },
    neutral:  { icon: '😐', label: 'Neutral',  cls: 'neutral'  },
    negative: { icon: '😟', label: 'Negative', cls: 'negative' },
};

// ─── INIT ────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    setupListeners();
    loadNews();
});

function setupListeners() {
    document.getElementById('btnSearchCountry').addEventListener('click', () => {
        currentCountry = document.getElementById('countryFilter').value.trim();
        loadNews();
    });
    document.getElementById('countryFilter').addEventListener('keypress', e => {
        if (e.key === 'Enter') {
            currentCountry = e.target.value.trim();
            loadNews();
        }
    });
    document.getElementById('sentimentFilter').addEventListener('change', () => renderAll(filterNews()));
    document.getElementById('searchNews').addEventListener('input',      () => renderAll(filterNews()));
    document.getElementById('btnRefresh').addEventListener('click', () => loadNews(true));
}

// ─── FETCH DATA ───────────────────────────────────────────────────────────────
async function loadNews(forceRefresh = false) {
    showLoading(true);

    const country = currentCountry || '';
    const limit   = 10;
    const url     = `/api/news/search?q=${encodeURIComponent(country)}&limit=${limit}${forceRefresh ? '&force_refresh=1' : ''}`;

    const ctrl    = new AbortController();
    const timeout = setTimeout(() => ctrl.abort(), 20000);

    try {
        const res  = await fetch(url, { signal: ctrl.signal });
        clearTimeout(timeout);
        const data = await res.json();

        if (data.success) {
            allNews = data.data.articles || [];
            updateStats(data.data.sentiment_analysis);
            renderAll(filterNews());
            showLoading(false);
        } else {
            throw new Error(data.message || 'Failed');
        }
    } catch (err) {
        clearTimeout(timeout);
        showError(err.name === 'AbortError'
            ? 'Request timeout — server might be busy. Try refreshing.'
            : err.message);
    }
}

// ─── FILTER ───────────────────────────────────────────────────────────────────
function filterNews() {
    const sent   = document.getElementById('sentimentFilter').value;
    const search = document.getElementById('searchNews').value.toLowerCase();

    return allNews.filter(a => {
        const matchSent   = !sent   || a.sentiment === sent;
        const matchSearch = !search || a.title.toLowerCase().includes(search)
                            || (a.description || '').toLowerCase().includes(search);
        return matchSent && matchSearch;
    });
}

// ─── RENDER ───────────────────────────────────────────────────────────────────
function renderAll(articles) {
    if (articles.length === 0) {
        document.getElementById('featuredWrap').style.display = 'none';
        document.getElementById('newsWrap').style.display     = 'none';
        document.getElementById('noResults').style.display    = 'block';
        return;
    }
    document.getElementById('noResults').style.display = 'none';
    renderFeatured(articles[0]);
    renderGrid(articles.slice(1));
    document.getElementById('featuredWrap').style.display = 'block';
    document.getElementById('newsWrap').style.display     = 'block';
}

// ─── FEATURED CARD ────────────────────────────────────────────────────────────
function renderFeatured(art) {
    const sent = SENT_CONFIG[art.sentiment] || SENT_CONFIG.neutral;
    const cat  = CAT_CONFIG[art.category]   || CAT_CONFIG.logistics;

    document.getElementById('featuredCard').href        = art.url || '#';
    document.getElementById('featuredCard').className   = `featured-card ${sent.cls}`;
    document.getElementById('featuredTitle').textContent = art.title;
    document.getElementById('featuredDesc').textContent  = art.description || '';
    document.getElementById('featuredSource').textContent = art.source || 'Unknown';
    document.getElementById('featuredTime').textContent   = timeAgo(art.published_at);
    document.getElementById('featuredConf').textContent   = `${art.sentiment_confidence}%`;

    const bar = document.getElementById('featuredConfBar');
    bar.className = `conf-fill ${sent.cls}`;
    bar.style.width = `${art.sentiment_confidence}%`;

    document.getElementById('featuredBadges').innerHTML =
        `<span class="sbadge ${sent.cls}">${sent.icon} ${sent.label}</span>
         <span class="catbadge ${art.category || 'logistics'}">${cat.icon} ${cat.label}</span>`;

    const imgWrap = document.getElementById('featuredImgWrap');
    if (art.image_url) {
        imgWrap.innerHTML = `<img src="${art.image_url}" alt="${art.title}" onerror="this.parentElement.innerHTML=getPlaceholder('${art.category}')">`;
    } else {
        imgWrap.innerHTML = getPlaceholder(art.category);
    }
}

// ─── NEWS GRID ────────────────────────────────────────────────────────────────
function renderGrid(articles) {
    const grid = document.getElementById('newsGrid');
    grid.innerHTML = '';

    articles.forEach(art => {
        const sent = SENT_CONFIG[art.sentiment] || SENT_CONFIG.neutral;
        const cat  = CAT_CONFIG[art.category]   || CAT_CONFIG.logistics;

        const imgHTML = art.image_url
            ? `<img src="${art.image_url}" alt="${escHtml(art.title)}" loading="lazy"
                    onerror="this.parentElement.innerHTML=getPlaceholder('${art.category}')">`
            : getPlaceholder(art.category);

        grid.innerHTML += `
        <a class="news-card ${sent.cls}" href="${art.url || '#'}" target="_blank">
            <div class="card-img-wrap">${imgHTML}</div>
            <div class="card-body-content">
                <div class="card-meta">
                    <span class="sbadge ${sent.cls}">${sent.icon} ${sent.label}</span>
                    <span class="catbadge ${art.category || 'logistics'}">${cat.icon} ${cat.label}</span>
                </div>
                <div class="card-title">${escHtml(art.title)}</div>
                <div class="card-desc">${escHtml(art.description || '')}</div>
                <div style="margin-top:6px;">
                    <div class="conf-bar">
                        <div class="conf-fill ${sent.cls}" style="width:${art.sentiment_confidence}%"></div>
                    </div>
                </div>
                <div class="card-footer-row">
                    <span class="source-badge" title="${escHtml(art.source || '')}">
                        <i class="bi bi-building me-1"></i>${escHtml(art.source || 'Unknown')}
                    </span>
                    <span class="time-label"><i class="bi bi-clock me-1"></i>${timeAgo(art.published_at)}</span>
                </div>
            </div>
        </a>`;
    });
}

// ─── STATS ────────────────────────────────────────────────────────────────────
function updateStats(s) {
    document.getElementById('positiveCount').textContent  = s.positive;
    document.getElementById('neutralCount').textContent   = s.neutral;
    document.getElementById('negativeCount').textContent  = s.negative;
    document.getElementById('totalArticles').textContent  = s.total;
    document.getElementById('positivePercent').textContent = `${s.positive_percentage}%`;
    document.getElementById('neutralPercent').textContent  = `${s.neutral_percentage}%`;
    document.getElementById('negativePercent').textContent = `${s.negative_percentage}%`;
    const os = SENT_CONFIG[s.overall_sentiment] || SENT_CONFIG.neutral;
    document.getElementById('overallSentiment').textContent = `${os.icon} Overall: ${os.label}`;
}

// ─── LOADING / ERROR ─────────────────────────────────────────────────────────
function showLoading(on) {
    document.getElementById('loadingState').style.display  = on ? 'block'  : 'none';
    document.getElementById('statSkeleton').style.display  = on ? 'grid'   : 'none';
    document.getElementById('statCards').style.display     = on ? 'none'   : 'grid';
    document.getElementById('featuredWrap').style.display  = on ? 'none'   : '';
    document.getElementById('newsWrap').style.display      = on ? 'none'   : '';
}
function showError(msg) {
    document.getElementById('loadingState').innerHTML = `
        <div style="display:inline-block;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);
                    border-radius:16px;padding:2rem 2.5rem;max-width:500px;">
            <div style="font-size:3rem;margin-bottom:1rem;">⚠️</div>
            <h5 style="color:#f87171;">Failed to Load News</h5>
            <p style="color:var(--text-muted);font-size:.875rem;">${msg}</p>
            <button onclick="loadNews(true)" class="btn-search mt-2">
                <i class="bi bi-arrow-clockwise me-1"></i> Try Again
            </button>
        </div>`;
}

// ─── HELPERS ─────────────────────────────────────────────────────────────────
function getPlaceholder(category) {
    const c = CAT_CONFIG[category] || CAT_CONFIG.logistics;
    return `<div class="img-placeholder ${c.class}">
                <span class="ph-icon">${c.icon}</span>
                <span class="ph-label" style="color:rgba(255,255,255,.5);">${c.label}</span>
            </div>`;
}

function escHtml(str) {
    if (!str) return '';
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function timeAgo(dateStr) {
    if (!dateStr) return '—';
    const secs = Math.floor((Date.now() - new Date(dateStr)) / 1000);
    if (secs < 60)    return 'Just now';
    if (secs < 3600)  return `${Math.floor(secs/60)}m ago`;
    if (secs < 86400) return `${Math.floor(secs/3600)}h ago`;
    return `${Math.floor(secs/86400)}d ago`;
}
</script>
@endpush
