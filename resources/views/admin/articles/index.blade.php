@extends('layouts.admin')

@section('title', 'Article Management')
@section('page-title', 'Article Management')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-newspaper"></i> All Articles</h5>
            <a href="{{ route('admin.articles.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Article
            </a>
        </div>
        <div class="card-body">
            <p class="text-muted">Article management interface will be implemented in next phase.</p>
        </div>
    </div>
</div>
@endsection
