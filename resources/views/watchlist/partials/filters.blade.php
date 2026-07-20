<div class="card mb-4" style="border-radius: 10px;">
    <div class="card-body">
        <div class="row g-3">
            {{-- Search Input --}}
            <div class="col-md-4">
                <label for="searchInput" class="form-label">Search Country</label>
                <input type="text" class="form-control" id="searchInput" placeholder="Search by country name">
            </div>

            {{-- Region Filter --}}
            <div class="col-md-3">
                <label for="regionFilter" class="form-label">Region</label>
                <select class="form-select" id="regionFilter">
                    <option value="">All Regions</option>
                    @foreach($regions as $region)
                        <option value="{{ $region }}">{{ $region }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Risk Level Filter --}}
            <div class="col-md-3">
                <label for="riskFilter" class="form-label">Risk Level</label>
                <select class="form-select" id="riskFilter">
                    <option value="">All Levels</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="critical">Critical</option>
                </select>
            </div>

            {{-- Clear Filters Button --}}
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-outline-secondary w-100" id="clearFiltersBtn">
                    <i class="fas fa-times"></i> Clear
                </button>
            </div>
        </div>
    </div>
</div>
