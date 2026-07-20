@extends('layouts.admin')

@section('title', 'Add New Port')
@section('page-title', 'Add New Port')

@section('content')
<div class="container-fluid">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.index') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.ports.index') }}">Ports</a></li>
            <li class="breadcrumb-item active">Create</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-anchor"></i> Add New Port
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.ports.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="port_name" class="form-label">
                                        Port Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('port_name') is-invalid @enderror" 
                                           id="port_name" 
                                           name="port_name" 
                                           value="{{ old('port_name') }}" 
                                           required
                                           placeholder="e.g., Port of Singapore">
                                    @error('port_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="code" class="form-label">
                                        Port Code
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('code') is-invalid @enderror" 
                                           id="code" 
                                           name="code" 
                                           value="{{ old('code') }}" 
                                           maxlength="10"
                                           placeholder="e.g., SGSIN">
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="country_code" class="form-label">
                                        Country <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('country_code') is-invalid @enderror" 
                                            id="country_code" 
                                            name="country_code" 
                                            required>
                                        <option value="">-- Select Country --</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->code }}" 
                                                    data-name="{{ $country->name }}"
                                                    {{ old('country_code') == $country->code ? 'selected' : '' }}>
                                                {{ $country->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('country_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="country_name" class="form-label">
                                        Country Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('country_name') is-invalid @enderror" 
                                           id="country_name" 
                                           name="country_name" 
                                           value="{{ old('country_name') }}" 
                                           required
                                           readonly>
                                    @error('country_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="region" class="form-label">
                                        Region <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('region') is-invalid @enderror" 
                                            id="region" 
                                            name="region" 
                                            required>
                                        <option value="">-- Select Region --</option>
                                        <option value="Asia" {{ old('region') == 'Asia' ? 'selected' : '' }}>Asia</option>
                                        <option value="Europe" {{ old('region') == 'Europe' ? 'selected' : '' }}>Europe</option>
                                        <option value="Americas" {{ old('region') == 'Americas' ? 'selected' : '' }}>Americas</option>
                                        <option value="Africa" {{ old('region') == 'Africa' ? 'selected' : '' }}>Africa</option>
                                        <option value="Oceania" {{ old('region') == 'Oceania' ? 'selected' : '' }}>Oceania</option>
                                        <option value="Other" {{ old('region') == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('region')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="latitude" class="form-label">
                                        Latitude <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('latitude') is-invalid @enderror" 
                                           id="latitude" 
                                           name="latitude" 
                                           value="{{ old('latitude') }}" 
                                           step="0.0000001"
                                           min="-90"
                                           max="90"
                                           required
                                           placeholder="e.g., 1.290270">
                                    @error('latitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Range: -90 to 90</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="longitude" class="form-label">
                                        Longitude <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('longitude') is-invalid @enderror" 
                                           id="longitude" 
                                           name="longitude" 
                                           value="{{ old('longitude') }}" 
                                           step="0.0000001"
                                           min="-180"
                                           max="180"
                                           required
                                           placeholder="e.g., 103.851959">
                                    @error('longitude')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Range: -180 to 180</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="port_type" class="form-label">
                                Port Type
                            </label>
                            <select class="form-select @error('port_type') is-invalid @enderror" 
                                    id="port_type" 
                                    name="port_type">
                                <option value="">-- Select Type --</option>
                                <option value="Seaport" {{ old('port_type') == 'Seaport' ? 'selected' : '' }}>Seaport</option>
                                <option value="Container Port" {{ old('port_type') == 'Container Port' ? 'selected' : '' }}>Container Port</option>
                                <option value="Cargo Port" {{ old('port_type') == 'Cargo Port' ? 'selected' : '' }}>Cargo Port</option>
                                <option value="Fishing Port" {{ old('port_type') == 'Fishing Port' ? 'selected' : '' }}>Fishing Port</option>
                                <option value="Naval Port" {{ old('port_type') == 'Naval Port' ? 'selected' : '' }}>Naval Port</option>
                                <option value="Other" {{ old('port_type') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('port_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save"></i> Create Port
                            </button>
                            <a href="{{ route('admin.ports.index') }}" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-fill country name when country is selected
    $('#country_code').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const countryName = selectedOption.data('name');
        $('#country_name').val(countryName);
    });
});
</script>
@endpush
