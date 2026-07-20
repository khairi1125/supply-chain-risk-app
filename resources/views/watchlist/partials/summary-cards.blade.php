<div class="row mb-4">
    {{-- Total Watched Countries --}}
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card" style="border-radius: 10px;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Total Watched</h6>
                        <h3 class="mb-0" id="totalCountCard">{{ $stats['total_count'] }}</h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded">
                        <i class="fas fa-globe fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Low Risk Countries --}}
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card" style="border-radius: 10px;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Low Risk</h6>
                        <h3 class="mb-0 text-success" id="lowRiskCard">{{ $stats['low_risk'] }}</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded">
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Medium Risk Countries --}}
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card" style="border-radius: 10px;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Medium Risk</h6>
                        <h3 class="mb-0 text-warning" id="mediumRiskCard">{{ $stats['medium_risk'] }}</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded">
                        <i class="fas fa-exclamation-circle fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- High/Critical Risk Countries --}}
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card" style="border-radius: 10px;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">High Risk</h6>
                        <h3 class="mb-0 text-danger" id="highRiskCard">{{ $stats['high_risk'] + $stats['critical_risk'] }}</h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 p-3 rounded">
                        <i class="fas fa-triangle-exclamation fa-2x text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
