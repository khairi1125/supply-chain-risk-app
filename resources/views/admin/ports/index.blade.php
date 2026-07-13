@extends('layouts.admin')

@section('title', 'Port Management')
@section('page-title', 'Port Management')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-anchor"></i> All Ports</h5>
            <a href="{{ route('admin.ports.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Port
            </a>
        </div>
        <div class="card-body">
            <p class="text-muted">Port management interface will be implemented in next phase.</p>
        </div>
    </div>
</div>
@endsection
