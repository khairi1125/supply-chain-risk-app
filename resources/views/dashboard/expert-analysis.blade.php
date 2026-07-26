@extends('layouts.app')

@section('title', 'Expert Analysis')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    .expert-analysis-container {
        font-family: 'Inter', sans-serif;
        color: #374151; /* Dark gray text for light theme */
    }
    .page-header {
        margin-bottom: 30px;
    }
    .page-header h2 {
        font-weight: 700;
        color: #111827;
        margin-bottom: 5px;
    }
    .page-header p {
        color: #6b7280;
    }
    
    .article-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        transition: all 0.2s ease;
    }
    .article-card:hover {
        border-color: #93c5fd;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        transform: translateY(-2px);
    }
    
    .article-meta {
        display: flex;
        gap: 15px;
        font-size: 0.85rem;
        color: #6b7280;
        margin-bottom: 12px;
    }
    
    .article-meta span {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .article-category {
        background: #eff6ff;
        color: #2563eb;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .article-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 12px;
        line-height: 1.4;
    }
    
    .article-desc {
        color: #4b5563;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 20px;
    }
    
    .article-content {
        background: #f8fafc;
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
        font-size: 0.95rem;
        line-height: 1.7;
        color: #334155;
        border-left: 4px solid #3b82f6;
    }

    .article-content a {
        color: #2563eb;
        text-decoration: none;
    }
    .article-content a:hover {
        text-decoration: underline;
    }
    
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: #ffffff;
        border-radius: 12px;
        border: 1px dashed #cbd5e1;
    }
    .empty-state i {
        font-size: 48px;
        color: #94a3b8;
        margin-bottom: 16px;
    }
    
    /* Pagination Styles */
    .pagination-wrapper {
        margin-top: 30px;
    }
    .pagination {
        margin-bottom: 0;
    }
    .page-item.active .page-link {
        background-color: #3b82f6;
        border-color: #3b82f6;
    }
    .page-link {
        color: #475569;
    }
    .page-link:hover {
        color: #1d4ed8;
        background-color: #f1f5f9;
    }
    .page-item.disabled .page-link {
        background-color: rgba(15, 23, 42, 0.5);
        border-color: rgba(255, 255, 255, 0.05);
        color: #64748b;
    }
</style>
@endpush

@section('content')
<div class="container-fluid expert-analysis-container">
    <div class="page-header">
        <h2><i class="fas fa-file-contract text-primary me-2"></i> Expert Analysis</h2>
        <p>Laporan eksklusif dan analisis mendalam dari tim ahli kami.</p>
    </div>

    @if($articles->count() > 0)
        <div class="row">
            <div class="col-12">
                @foreach($articles as $article)
                    <div class="article-card">
                        <div class="article-meta">
                            <span class="article-category">{{ $article->category }}</span>
                            <span><i class="far fa-clock"></i> {{ \Carbon\Carbon::parse($article->published_at)->diffForHumans() }}</span>
                            <span><i class="far fa-user"></i> {{ $article->user->name ?? 'Admin' }}</span>
                        </div>
                        
                        <h3 class="article-title">{{ $article->title }}</h3>
                        <p class="article-desc">{{ $article->description }}</p>
                        
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#content-{{ $article->id }}">
                            <i class="fas fa-book-open"></i> Baca Selengkapnya
                        </button>
                        
                        <div class="collapse mt-3" id="content-{{ $article->id }}">
                            <div class="article-content">
                                {!! $article->content !!}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        
        <div class="pagination-wrapper d-flex justify-content-center">
            {{ $articles->links('pagination::bootstrap-5') }}
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-folder-open"></i>
            <h4>Belum Ada Artikel</h4>
            <p class="text-muted">Saat ini belum ada artikel analisis yang diterbitkan oleh tim ahli kami.</p>
        </div>
    @endif
</div>
@endsection
