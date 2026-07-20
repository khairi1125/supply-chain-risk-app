@extends('layouts.app')

@section('title', 'Compare Countries')

@push('styles')
<style>
    /* Modern Light Theme for Compare Countries */
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
        background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
        border-radius: 24px;
        padding: 2.5rem;
        margin-bottom: 2rem;
        color: white;
        box-shadow: 0 20px 60px rgba(236, 72, 153, 0.3);
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
        margin: 0;
        position: relative;
        z-index: 1;
    }
    
    /* Selection Card */
    .selection-card {
        border-radius: 20px;
        border: 2px solid var(--gray-200);
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        background: white;
        overflow: hidden;
        margin-bottom: 2rem;
    }
    
    .selection-card .card-header {
        background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
        color: white;
        border: none;
        padding: 1.75rem 2rem;
    }
    
    .selection-card .card-header h5 {
        font-weight: 700;
        margin: 0;
    }
    
    .selection-card .card-body {
        padding: 2rem;
    }
    
    /* Search Box */
    .search-box {
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: 2px solid var(--gray-100);
        transition: all 0.3s ease;
    }
    
    .search-box:focus-within {
        border-color: #ec4899;
        box-shadow: 0 6px 30px rgba(236, 72, 153, 0.2);
    }
    
    .search-box .input-group-text {
        background: white;
        border: none;
        padding: 1rem 1.5rem;
        color: var(--gray-600);
    }
    
    .search-box .form-control {
        border: none;
        padding: 1rem 1.5rem;
        font-size: 1.1rem;
    }
    
    .search-box .form-control:focus {
        box-shadow: none;
    }
    
    /* Country Grid Cards */
    .country-select-card {
        border-radius: 16px;
        border: 2px solid var(--gray-100);
        padding: 1.25rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
    }
    
    .country-select-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        border-color: #ec4899;
    }
    
    .country-select-card.selected {
        background: linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%);
        border-color: #ec4899;
        box-shadow: 0 8px 25px rgba(236, 72, 153, 0.3);
    }
    
    .country-select-card img {
        width: 60px;
        height: 45px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        margin-bottom: 0.75rem;
    }
    
    .country-select-card h6 {
        font-weight: 700;
        color: var(--gray-800);
        margin-bottom: 0.25rem;
    }
    
    .country-select-card small {
        color: var(--gray-600);
        font-size: 0.85rem;
    }
    
    /* Selected Pills */
    .selected-pill {
        background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
        color: white;
        padding: 0.75rem 1.25rem;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(236, 72, 153, 0.3);
    }
    
    .selected-pill .btn-close {
        filter: brightness(0) invert(1);
        opacity: 0.9;
        font-size: 0.7rem;
    }
    
    /* Buttons */
    .btn-primary {
        background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
        border: none;
        border-radius: 16px;
        padding: 0.85rem 1.5rem;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(236, 72, 153, 0.3);
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(236, 72, 153, 0.5);
        background: linear-gradient(135deg, #db2777 0%, #be185d 100%);
    }
    
    .btn-outline-primary {
        border: 2px solid #ec4899;
        color: #ec4899;
        border-radius: 16px;
        padding: 0.85rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-outline-primary:hover {
        background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
        border-color: #ec4899;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(236, 72, 153, 0.3);
    }
    
    /* Tabs */
    .nav-tabs {
        border: none;
        gap: 0.5rem;
    }
    
    .nav-tabs .nav-link {
        border: 2px solid var(--gray-200);
        border-radius: 12px 12px 0 0;
        color: var(--gray-600);
        font-weight: 600;
        padding: 1rem 1.5rem;
        transition: all 0.3s ease;
    }
    
    .nav-tabs .nav-link:hover {
        border-color: #ec4899;
        color: #ec4899;
    }
    
    .nav-tabs .nav-link.active {
        background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
        color: white;
        border-color: #ec4899;
        box-shadow: 0 4px 15px rgba(236, 72, 153, 0.3);
    }
    
    /* Comparison Cards */
    .comparison-card {
        border-radius: 16px;
        border: 2px solid var(--gray-200);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        background: white;
        transition: all 0.3s ease;
    }
    
    .comparison-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
    
    /* Loading Spinner */
    .spinner-border {
        border-color: #ec4899;
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
        
        .selection-card .card-body {
            padding: 1.25rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <h2>🔄 Compare Countries</h2>
        <p>Compare multiple countries side-by-side for better decision making</p>
    </div>

    <!-- Country Selection -->
    <div class="selection-card">
        <div class="card-header">
            <h5><i class="bi bi-search"></i> Select Countries to Compare (Max 4)</h5>
        </div>
        <div class="card-body">
            <!-- Search Box -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="input-group input-group-lg search-box">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" 
                               class="form-control" 
                               id="countrySearchBox" 
                               placeholder="Search countries... (e.g., Indonesia, Singapore, Malaysia)">
                        <button class="btn btn-outline-secondary" id="btnClearSearch" type="button">
                            <i class="bi bi-x-circle"></i> Clear
                        </button>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        💡 Tip: Search dan click negara untuk select. Maximum 4 negara.
                    </small>
                </div>
            </div>

            <!-- Selected Countries Pills -->
            <div class="row mb-3" id="selectedCountriesContainer" style="display: none;">
                <div class="col-12">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <strong class="me-2">Selected:</strong>
                        <div id="selectedCountriesPills" class="d-flex gap-2 flex-wrap">
                            <!-- Pills will be added here -->
                        </div>
                        <button class="btn btn-primary btn-sm" id="btnCompareSelected">
                            <i class="bi bi-arrow-left-right"></i> Compare (<span id="selectedCount">0</span>)
                        </button>
                    </div>
                </div>
            </div>

            <!-- Countries Grid -->
            <div class="row" id="countriesGrid" style="max-height: 500px; overflow-y: auto;">
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Loading countries...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="text-center py-5" style="display: none;">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
        <h5 class="mt-3">Loading comparison data...</h5>
        <p class="text-muted">Fetching data from multiple sources</p>
    </div>

    <!-- Comparison Results -->
    <div id="comparisonResults" style="display: none;">
        <!-- Back Button -->
        <div class="row mb-3">
            <div class="col-12">
                <button class="btn btn-outline-primary" id="btnBackToSelection">
                    <i class="bi bi-arrow-left"></i> Back to Selection
                </button>
            </div>
        </div>

        <!-- Quick Stats Summary -->
        <div class="row mb-4" id="quickStats">
            <!-- Will be populated dynamically -->
        </div>

        <!-- Detailed Comparison Tabs -->
        <div class="card">
            <div class="card-header bg-white">
                <ul class="nav nav-tabs card-header-tabs" id="comparisonTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="overview-tab" data-bs-toggle="tab" href="#overview" role="tab">
                            <i class="bi bi-info-circle"></i> Overview
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="weather-tab" data-bs-toggle="tab" href="#weather" role="tab">
                            <i class="bi bi-cloud-sun"></i> Weather
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="economy-tab" data-bs-toggle="tab" href="#economy" role="tab">
                            <i class="bi bi-currency-exchange"></i> Economy
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="news-tab" data-bs-toggle="tab" href="#news" role="tab">
                            <i class="bi bi-newspaper"></i> News Sentiment
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="ports-tab" data-bs-toggle="tab" href="#ports" role="tab">
                            <i class="bi bi-ship"></i> Ports
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="comparisonTabContent">
                    <!-- Overview Tab -->
                    <div class="tab-pane fade show active" id="overview" role="tabpanel">
                        <div class="row" id="overviewContent">
                            <!-- Will be populated dynamically -->
                        </div>
                    </div>

                    <!-- Weather Tab -->
                    <div class="tab-pane fade" id="weather" role="tabpanel">
                        <div class="row" id="weatherContent">
                            <!-- Will be populated dynamically -->
                        </div>
                    </div>

                    <!-- Economy Tab -->
                    <div class="tab-pane fade" id="economy" role="tabpanel">
                        <div class="row" id="economyContent">
                            <!-- Will be populated dynamically -->
                        </div>
                        <div class="row mt-4">
                            <div class="col-12">
                                <canvas id="currencyChart" style="max-height: 400px;"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- News Sentiment Tab -->
                    <div class="tab-pane fade" id="news" role="tabpanel">
                        <div class="row" id="newsContent">
                            <!-- Will be populated dynamically -->
                        </div>
                        <div class="row mt-4">
                            <div class="col-12">
                                <canvas id="sentimentChart" style="max-height: 400px;"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Ports Tab -->
                    <div class="tab-pane fade" id="ports" role="tabpanel">
                        <div class="row" id="portsContent">
                            <!-- Will be populated dynamically -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="text-center py-5">
        <i class="bi bi-arrow-left-right" style="font-size: 5rem; color: #6c757d; opacity: 0.3;"></i>
        <h4 class="text-muted mt-3">No Countries Selected</h4>
        <p class="text-muted">Select 2-4 countries from the dropdown above to start comparing</p>
    </div>
</div>
@endsection

@push('scripts')
<style>
/* Country Card Styles */
.country-card {
    border: 2px solid #e0e0e0;
    border-radius: 12px;
    padding: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
    position: relative;
    overflow: hidden;
}

.country-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    border-color: #3498db;
}

.country-card.selected {
    border-color: #3498db;
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
}

.country-card.selected::after {
    content: "✓";
    position: absolute;
    top: 10px;
    right: 10px;
    background: #3498db;
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 18px;
}

.country-card .flag-emoji {
    font-size: 2.5rem;
    display: block;
    text-align: center;
    margin-bottom: 8px;
}

.country-card .country-name {
    font-weight: 600;
    font-size: 0.95rem;
    text-align: center;
    margin-bottom: 5px;
    color: #2c3e50;
}

.country-card .country-info {
    font-size: 0.75rem;
    color: #7f8c8d;
    text-align: center;
}

.country-card.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.country-card.disabled:hover {
    transform: none;
    box-shadow: none;
}

/* Selected Pills */
.selected-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 15px;
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    color: white;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 500;
    box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
    animation: slideIn 0.3s ease;
}

.selected-pill .remove-btn {
    background: rgba(255,255,255,0.3);
    border: none;
    color: white;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.2s;
}

.selected-pill .remove-btn:hover {
    background: rgba(255,255,255,0.5);
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Region Headers */
.region-header {
    background: linear-gradient(135deg, #34495e 0%, #2c3e50 100%);
    color: white;
    padding: 10px 15px;
    border-radius: 8px;
    margin: 20px 0 15px 0;
    font-weight: 600;
    font-size: 1.1rem;
    position: sticky;
    top: 0;
    z-index: 10;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

/* Scrollbar Styling */
#countriesGrid::-webkit-scrollbar {
    width: 8px;
}

#countriesGrid::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

#countriesGrid::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

#countriesGrid::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
<script>
let allCountries = [];
let selectedCountries = [];
let comparisonData = {};
let currencyChart = null;
let sentimentChart = null;
const MAX_SELECTION = 4;

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Compare Countries page loaded');
    loadCountriesList();
    setupEventListeners();
});

async function loadCountriesList() {
    try {
        console.log('📡 Fetching countries list...');
        // Request 500 (max allowed) to get all countries
        const response = await fetch('/api/countries?per_page=500');
        const data = await response.json();
        
        console.log('API Response:', data);
        
        // Handle paginated response
        if (data.data && Array.isArray(data.data)) {
            allCountries = data.data;
        } else if (Array.isArray(data)) {
            allCountries = data;
        } else {
            console.error('Unexpected API response format:', data);
            alert('Failed to load countries list: Invalid data format');
            return;
        }
        
        renderCountriesGrid(allCountries);
        console.log(`✅ Loaded ${allCountries.length} countries`);
        
        // Show total in console
        if (allCountries.length < 200) {
            console.warn(`⚠️ Only ${allCountries.length} countries loaded. Expected ~250. Check database.`);
        }
    } catch (error) {
        console.error('Error loading countries:', error);
        alert('Failed to load countries list: ' + error.message);
    }
}

function renderCountriesGrid(countries) {
    const grid = document.getElementById('countriesGrid');
    grid.innerHTML = '';
    
    // Group countries by region
    const regions = {};
    countries.forEach(country => {
        if (!regions[country.region]) {
            regions[country.region] = [];
        }
        regions[country.region].push(country);
    });
    
    // Sort each region's countries alphabetically
    Object.keys(regions).forEach(region => {
        regions[region].sort((a, b) => a.name.localeCompare(b.name));
    });
    
    // Render by region
    const regionOrder = ['Asia', 'Europe', 'Africa', 'Americas', 'Oceania'];
    regionOrder.forEach(region => {
        if (!regions[region]) return;
        
        // Region header
        const headerCol = document.createElement('div');
        headerCol.className = 'col-12';
        headerCol.innerHTML = `<div class="region-header">${getRegionEmoji(region)} ${region} (${regions[region].length})</div>`;
        grid.appendChild(headerCol);
        
        // Country cards
        regions[region].forEach(country => {
            const col = document.createElement('div');
            col.className = 'col-md-2 col-sm-3 col-6 mb-3';
            
            const flagEmoji = getFlagEmoji(country.flag_url);
            const isSelected = selectedCountries.some(c => c.code === country.code);
            const isDisabled = !isSelected && selectedCountries.length >= MAX_SELECTION;
            
            col.innerHTML = `
                <div class="country-card ${isSelected ? 'selected' : ''} ${isDisabled ? 'disabled' : ''}" 
                     data-code="${country.code}" 
                     onclick="toggleCountrySelection('${country.code}')">
                    <div class="flag-container" style="text-align: center; margin-bottom: 10px;">
                        <img src="${country.flag_url}" 
                             alt="${country.name}" 
                             style="width: 60px; height: 40px; object-fit: cover; border-radius: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.15);"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <div class="flag-emoji" style="display: none; font-size: 3rem;">${flagEmoji}</div>
                    </div>
                    <div class="country-name">${country.name}</div>
                    <div class="country-info">
                        <small>${country.region}</small>
                    </div>
                </div>
            `;
            grid.appendChild(col);
        });
    });
    
    console.log(`✅ Rendered ${countries.length} countries grouped by region`);
}

function toggleCountrySelection(code) {
    const country = allCountries.find(c => c.code === code);
    if (!country) return;
    
    const index = selectedCountries.findIndex(c => c.code === code);
    
    if (index > -1) {
        // Remove selection
        selectedCountries.splice(index, 1);
        console.log(`❌ Removed ${country.name} from selection`);
    } else {
        // Add selection
        if (selectedCountries.length >= MAX_SELECTION) {
            alert(`⚠️ Maximum ${MAX_SELECTION} countries dapat dipilih!`);
            return;
        }
        selectedCountries.push(country);
        console.log(`✅ Added ${country.name} to selection`);
    }
    
    updateSelectedPills();
    updateCountryCards();
}

function updateSelectedPills() {
    const container = document.getElementById('selectedCountriesContainer');
    const pillsContainer = document.getElementById('selectedCountriesPills');
    const countSpan = document.getElementById('selectedCount');
    
    if (selectedCountries.length === 0) {
        container.style.display = 'none';
        return;
    }
    
    container.style.display = 'block';
    countSpan.textContent = selectedCountries.length;
    
    pillsContainer.innerHTML = '';
    selectedCountries.forEach(country => {
        const pill = document.createElement('div');
        pill.className = 'selected-pill';
        pill.innerHTML = `
            <img src="${country.flag_url}" 
                 alt="${country.name}" 
                 style="width: 30px; height: 20px; object-fit: cover; border-radius: 3px;"
                 onerror="this.outerHTML='${getFlagEmoji(country.flag_url)}';">
            <span>${country.name}</span>
            <button class="remove-btn" onclick="toggleCountrySelection('${country.code}')" type="button">×</button>
        `;
        pillsContainer.appendChild(pill);
    });
}

function updateCountryCards() {
    document.querySelectorAll('.country-card').forEach(card => {
        const code = card.dataset.code;
        const isSelected = selectedCountries.some(c => c.code === code);
        const isDisabled = !isSelected && selectedCountries.length >= MAX_SELECTION;
        
        card.classList.toggle('selected', isSelected);
        card.classList.toggle('disabled', isDisabled);
    });
}

function filterCountries(searchTerm) {
    const filtered = allCountries.filter(country => 
        country.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        country.code.toLowerCase().includes(searchTerm.toLowerCase())
    );
    renderCountriesGrid(filtered);
}

function getRegionEmoji(region) {
    const emojis = {
        'Asia': '🌏',
        'Europe': '🇪🇺',
        'Africa': '🌍',
        'Americas': '🌎',
        'Oceania': '🏝️'
    };
    return emojis[region] || '🌐';
}

function setupEventListeners() {
    // Search functionality
    const searchBox = document.getElementById('countrySearchBox');
    searchBox.addEventListener('input', function(e) {
        const searchTerm = e.target.value.trim();
        if (searchTerm) {
            filterCountries(searchTerm);
        } else {
            renderCountriesGrid(allCountries);
        }
    });
    
    // Clear search
    document.getElementById('btnClearSearch').addEventListener('click', function() {
        searchBox.value = '';
        renderCountriesGrid(allCountries);
    });
    
    // Compare button
    document.getElementById('btnCompareSelected').addEventListener('click', compareCountries);
    
    // Back to selection button
    document.getElementById('btnBackToSelection').addEventListener('click', function() {
        document.getElementById('comparisonResults').style.display = 'none';
        document.getElementById('emptyState').style.display = 'none';
        
        // Scroll to top
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

async function compareCountries() {
    if (selectedCountries.length < 2) {
        alert('⚠️ Please select at least 2 countries to compare');
        return;
    }
    
    if (selectedCountries.length > MAX_SELECTION) {
        alert(`⚠️ Maximum ${MAX_SELECTION} countries can be compared at once`);
        return;
    }
    
    // Get selected country codes
    const countryCodes = selectedCountries.map(c => c.code);
    console.log('🔄 Comparing countries:', countryCodes);
    
    // Show loading
    document.getElementById('loadingOverlay').style.display = 'block';
    document.getElementById('emptyState').style.display = 'none';
    document.getElementById('comparisonResults').style.display = 'none';
    
    try {
        // Fetch comparison data
        await fetchComparisonData(countryCodes);
        
        // Display results
        displayComparisonResults();
        
        document.getElementById('loadingOverlay').style.display = 'none';
        document.getElementById('comparisonResults').style.display = 'block';
        
        // Scroll to results
        document.getElementById('comparisonResults').scrollIntoView({ behavior: 'smooth' });
    } catch (error) {
        console.error('Comparison error:', error);
        document.getElementById('loadingOverlay').style.display = 'none';
        document.getElementById('emptyState').style.display = 'block';
        alert('Failed to load comparison data: ' + error.message);
    }
}

async function fetchComparisonData(countryCodes) {
    comparisonData = {};
    
    // Fetch all currencies once (more efficient)
    let allCurrencies = [];
    try {
        const currencyResponse = await fetch('/api/currency');
        const currencyDataAll = await currencyResponse.json();
        console.log('Currency API response:', currencyDataAll);
        allCurrencies = currencyDataAll.data || [];
    } catch (e) {
        console.error('Failed to fetch currencies:', e);
    }
    
    for (const code of countryCodes) {
        console.log(`Fetching data for ${code}...`);
        
        const country = allCountries.find(c => c.code === code);
        
        // Fetch weather data
        const weatherResponse = await fetch(`/api/weather/${country.latitude}/${country.longitude}`);
        const weatherData = await weatherResponse.json();
        
        // Find currency for this country from pre-fetched list
        // API returns `rate` field (e.g., IDR per 1 USD = 15000)
        const currencyEntry = allCurrencies.find(c => c.country_code === code);
        const currencyData = currencyEntry ? {
            country_code: code,
            rate: currencyEntry.rate,            // e.g., 15000 (IDR per 1 USD)
            currency_code: currencyEntry.currency_code,
            currency_name: currencyEntry.currency_name,
            change_7d: currencyEntry.change_7d
        } : {
            country_code: code,
            rate: null,
            currency_code: country.currency_code,
            currency_name: country.currency_name,
            change_7d: 0
        };
        
        // Fetch news sentiment
        const newsResponse = await fetch(`/api/news/search?q=${encodeURIComponent(country.name)}&limit=10`);
        const newsData = await newsResponse.json();
        
        // Fetch ports data for this country
        const portsResponse = await fetch(`/api/ports/country/${code}`);
        const portsData = await portsResponse.json();
        
        comparisonData[code] = {
            country: country,
            weather: weatherData.data || {},
            currency: currencyData,
            news: newsData.data || {},
            ports: portsData.success ? portsData.data : []
        };
        
        console.log(`✅ Data for ${country.name}:`, comparisonData[code]);
    }
}

function displayComparisonResults() {
    // Display quick stats
    displayQuickStats();
    
    // Display detailed tabs
    displayOverviewTab();
    displayWeatherTab();
    displayEconomyTab();
    displayNewsTab();
    displayPortsTab();
}

function displayQuickStats() {
    const container = document.getElementById('quickStats');
    container.innerHTML = '';
    
    Object.keys(comparisonData).forEach(code => {
        const data = comparisonData[code];
        const country = data.country;
        const weather = data.weather;
        
        // Get flag emoji
        const flagEmoji = getFlagEmoji(country.flag_url);
        
        const riskBadge = getRiskBadge(weather.risk_level);
        
        const col = document.createElement('div');
        col.className = `col-md-${12 / Object.keys(comparisonData).length}`;
        col.innerHTML = `
            <div class="card bg-light border-0">
                <div class="card-body text-center">
                    <div style="font-size: 3rem;">${flagEmoji}</div>
                    <h5 class="mt-2 mb-1">${country.name}</h5>
                    <small class="text-muted">${country.region}</small>
                    <hr>
                    <div class="d-flex justify-content-around mt-3">
                        <div>
                            <small class="text-muted d-block">Weather Risk</small>
                            ${riskBadge}
                        </div>
                        <div>
                            <small class="text-muted d-block">Temperature</small>
                            <strong>${weather.temperature || '-'}°C</strong>
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(col);
    });
}

function displayOverviewTab() {
    const container = document.getElementById('overviewContent');
    container.innerHTML = '';
    
    Object.keys(comparisonData).forEach(code => {
        const data = comparisonData[code];
        const country = data.country;
        
        const col = document.createElement('div');
        col.className = `col-md-${12 / Object.keys(comparisonData).length}`;
        col.innerHTML = `
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-center mb-3">
                        <img src="${country.flag_url}" alt="${country.name}" 
                             style="width: 100px; height: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <div style="font-size: 4rem; display: none;">${getFlagEmoji(country.flag_url)}</div>
                        <h4 class="mt-3">${country.name}</h4>
                        <span class="badge bg-secondary">${country.code}</span>
                    </div>
                    <table class="table table-sm">
                        <tr>
                            <td class="text-muted">Region</td>
                            <td class="text-end"><strong>${country.region}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Currency</td>
                            <td class="text-end"><strong>${country.currency_code}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Coordinates</td>
                            <td class="text-end"><small>${country.latitude}, ${country.longitude}</small></td>
                        </tr>
                    </table>
                </div>
            </div>
        `;
        container.appendChild(col);
    });
}

function displayWeatherTab() {
    const container = document.getElementById('weatherContent');
    container.innerHTML = '';
    
    Object.keys(comparisonData).forEach(code => {
        const data = comparisonData[code];
        const country = data.country;
        const weather = data.weather;
        
        const riskBadge = getRiskBadge(weather.risk_level);
        const weatherIcon = getWeatherIcon(weather.weather_condition);
        const flagEmoji = getFlagEmoji(country.flag_url);
        
        const col = document.createElement('div');
        col.className = `col-md-${12 / Object.keys(comparisonData).length}`;
        col.innerHTML = `
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">${flagEmoji} ${country.name}</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div style="font-size: 3rem;">${weatherIcon}</div>
                        <h3>${weather.temperature || '-'}°C</h3>
                        <p class="text-muted">${weather.weather_condition || 'Unknown'}</p>
                        ${riskBadge}
                    </div>
                    <table class="table table-sm">
                        <tr>
                            <td><i class="bi bi-droplet"></i> Rainfall</td>
                            <td class="text-end"><strong>${weather.rainfall || 0}mm</strong></td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-wind"></i> Wind Speed</td>
                            <td class="text-end"><strong>${weather.wind_speed || 0}km/h</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        `;
        container.appendChild(col);
    });
}

function displayEconomyTab() {
    const container = document.getElementById('economyContent');
    container.innerHTML = '';
    
    const labels = [];
    const rates = [];
    const colors = ['#3498db', '#e74c3c', '#2ecc71', '#f39c12'];
    
    Object.keys(comparisonData).forEach((code, index) => {
        const data = comparisonData[code];
        const country = data.country;
        const currency = data.currency;
        
        const flagEmoji = getFlagEmoji(country.flag_url);
        // `rate` = how many local currency per 1 USD (e.g., IDR: 15000, MYR: 4.5)
        const rate = currency.rate || 0;
        const changeClass = currency.change_7d > 0 ? 'text-success' : (currency.change_7d < 0 ? 'text-danger' : 'text-muted');
        const changeArrow = currency.change_7d > 0 ? '▲' : (currency.change_7d < 0 ? '▼' : '—');
        
        labels.push(country.name);
        rates.push(rate);
        
        const col = document.createElement('div');
        col.className = `col-md-${12 / Object.keys(comparisonData).length}`;
        col.innerHTML = `
            <div class="card h-100" style="border-left: 4px solid ${colors[index]};">
                <div class="card-body">
                    <h6>${flagEmoji} ${country.name}</h6>
                    <hr>
                    <div>
                        <small class="text-muted">Currency</small>
                        <h4>${currency.currency_code || country.currency_code}</h4>
                        <p class="text-muted mb-1">${currency.currency_name || country.currency_name || '-'}</p>
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">1 USD =</small>
                        <h3>${rate > 0 ? formatCurrency(rate) : 'N/A'} <small class="text-muted fs-6">${currency.currency_code || ''}</small></h3>
                    </div>
                    <div class="mt-2">
                        <small class="${changeClass}">
                            ${changeArrow} ${Math.abs(currency.change_7d || 0).toFixed(2)}% (7d)
                        </small>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(col);
    });
    
    // Draw currency chart
    drawCurrencyChart(labels, rates, colors);
}

function displayNewsTab() {
    const container = document.getElementById('newsContent');
    container.innerHTML = '';
    
    const labels = [];
    const positiveData = [];
    const negativeData = [];
    const neutralData = [];
    const colors = ['#3498db', '#e74c3c', '#2ecc71', '#f39c12'];
    
    Object.keys(comparisonData).forEach((code, index) => {
        const data = comparisonData[code];
        const country = data.country;
        const news = data.news;
        
        const flagEmoji = getFlagEmoji(country.flag_url);
        
        // Calculate sentiment distribution
        const totalArticles = news.articles?.length || 0;
        const positive = news.articles?.filter(a => a.sentiment === 'positive').length || 0;
        const negative = news.articles?.filter(a => a.sentiment === 'negative').length || 0;
        const neutral = totalArticles - positive - negative;
        
        labels.push(country.name);
        positiveData.push(positive);
        negativeData.push(negative);
        neutralData.push(neutral);
        
        const sentimentScore = totalArticles > 0 ? ((positive - negative) / totalArticles * 100).toFixed(1) : 0;
        const sentimentBadge = getSentimentBadge(sentimentScore);
        
        const col = document.createElement('div');
        col.className = `col-md-${12 / Object.keys(comparisonData).length}`;
        col.innerHTML = `
            <div class="card h-100" style="border-left: 4px solid ${colors[index]};">
                <div class="card-body">
                    <h6>${flagEmoji} ${country.name}</h6>
                    <div class="mt-3 text-center">
                        <small class="text-muted">News Sentiment Score</small>
                        <h2>${sentimentScore}</h2>
                        ${sentimentBadge}
                    </div>
                    <hr>
                    <table class="table table-sm">
                        <tr>
                            <td><span class="badge bg-success">Positive</span></td>
                            <td class="text-end"><strong>${positive}</strong></td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-secondary">Neutral</span></td>
                            <td class="text-end"><strong>${neutral}</strong></td>
                        </tr>
                        <tr>
                            <td><span class="badge bg-danger">Negative</span></td>
                            <td class="text-end"><strong>${negative}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        `;
        container.appendChild(col);
    });
    
    // Draw sentiment chart
    drawSentimentChart(labels, positiveData, neutralData, negativeData);
}

function displayPortsTab() {
    const container = document.getElementById('portsContent');
    container.innerHTML = '';
    
    const colors = ['#3498db', '#e74c3c', '#2ecc71', '#f39c12'];
    
    Object.keys(comparisonData).forEach((code, index) => {
        const data = comparisonData[code];
        const country = data.country;
        const ports = data.ports || [];
        
        const flagEmoji = getFlagEmoji(country.flag_url);
        
        // Get top 5 largest ports (by harbor_size if available)
        const majorPorts = ports.slice(0, 5);
        
        const col = document.createElement('div');
        col.className = `col-md-${12 / Object.keys(comparisonData).length}`;
        col.innerHTML = `
            <div class="card h-100" style="border-left: 4px solid ${colors[index]};">
                <div class="card-body">
                    <h6>${flagEmoji} ${country.name}</h6>
                    <div class="mt-3 text-center">
                        <h1 class="display-4 mb-0">${ports.length}</h1>
                        <small class="text-muted">Total Ports</small>
                    </div>
                    <hr>
                    ${ports.length > 0 ? `
                        <div>
                            <strong class="mb-2 d-block">Major Ports:</strong>
                            <ul class="list-unstyled">
                                ${majorPorts.map(port => `
                                    <li class="mb-2">
                                        <i class="bi bi-geo-alt-fill text-primary"></i>
                                        <strong>${port.port_name}</strong>
                                        <br>
                                        <small class="text-muted ms-3">
                                            ${port.harbor_size ? `<span class="badge bg-info">${port.harbor_size}</span>` : ''}
                                            ${port.world_port_index ? `WPI: ${port.world_port_index}` : ''}
                                        </small>
                                    </li>
                                `).join('')}
                            </ul>
                            ${ports.length > 5 ? `<small class="text-muted">+ ${ports.length - 5} more ports</small>` : ''}
                        </div>
                    ` : `
                        <div class="text-center text-muted py-3">
                            <i class="bi bi-info-circle"></i>
                            <p class="mb-0">No port data available</p>
                        </div>
                    `}
                </div>
            </div>
        `;
        container.appendChild(col);
    });
}

function drawCurrencyChart(labels, rates, colors) {
    const ctx = document.getElementById('currencyChart');
    
    if (currencyChart) {
        currencyChart.destroy();
    }
    
    // Check if rates vary widely (more than 100x difference)
    const maxRate = Math.max(...rates.filter(r => r > 0));
    const minRate = Math.min(...rates.filter(r => r > 0));
    const useLogScale = (maxRate / minRate) > 100;
    
    currencyChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Exchange Rate to USD',
                data: rates,
                backgroundColor: colors,
                borderColor: colors,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Currency Exchange Rates Comparison' + (useLogScale ? ' (Logarithmic Scale)' : '')
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const rate = context.parsed.y;
                            const currencyCode = context.dataset.label;
                            return `1 USD = ${formatCurrency(rate)} ${labels[context.dataIndex]}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: useLogScale ? 'logarithmic' : 'linear',
                    beginAtZero: false,
                    title: {
                        display: true,
                        text: useLogScale ? 'Rate to USD (Log Scale)' : 'Rate to USD'
                    },
                    ticks: {
                        callback: function(value) {
                            // Format large numbers with thousand separators
                            if (value >= 1000) {
                                return new Intl.NumberFormat('id-ID').format(value);
                            }
                            return value.toFixed(2);
                        }
                    }
                }
            }
        }
    });
}

function drawSentimentChart(labels, positive, neutral, negative) {
    const ctx = document.getElementById('sentimentChart');
    
    if (sentimentChart) {
        sentimentChart.destroy();
    }
    
    sentimentChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Positive',
                    data: positive,
                    backgroundColor: '#2ecc71',
                    borderColor: '#27ae60',
                    borderWidth: 1
                },
                {
                    label: 'Neutral',
                    data: neutral,
                    backgroundColor: '#95a5a6',
                    borderColor: '#7f8c8d',
                    borderWidth: 1
                },
                {
                    label: 'Negative',
                    data: negative,
                    backgroundColor: '#e74c3c',
                    borderColor: '#c0392b',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                title: {
                    display: true,
                    text: 'News Sentiment Distribution'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.dataset.label || '';
                            const value = context.parsed.y;
                            const countryIndex = context.dataIndex;
                            
                            // Calculate total for percentage
                            const total = positive[countryIndex] + neutral[countryIndex] + negative[countryIndex];
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            
                            return `${label}: ${value} articles (${percentage}%)`;
                        },
                        footer: function(tooltipItems) {
                            if (tooltipItems.length > 0) {
                                const countryIndex = tooltipItems[0].dataIndex;
                                const total = positive[countryIndex] + neutral[countryIndex] + negative[countryIndex];
                                return `Total: ${total} articles`;
                            }
                            return '';
                        }
                    }
                },
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                x: {
                    stacked: true,
                    title: {
                        display: true,
                        text: 'Country'
                    }
                },
                y: {
                    stacked: true,
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Articles'
                    },
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            return Number.isInteger(value) ? value : '';
                        }
                    }
                }
            }
        }
    });
}

// Helper functions
function getFlagEmoji(flag_url) {
    if (!flag_url) return '🏴';
    
    // Extract country code from flag URL (e.g., https://flagcdn.com/w320/id.png -> ID)
    const match = flag_url.match(/\/([a-z]{2})\.png$/i);
    if (match) {
        const cc = match[1].toUpperCase();
        // Convert to flag emoji using regional indicator symbols
        return String.fromCodePoint(...[...cc].map(c => 0x1F1E6 - 65 + c.charCodeAt(0)));
    }
    return '🏴';
}

function getRiskBadge(level) {
    const badges = {
        'low': '<span class="badge bg-success">🟢 Low Risk</span>',
        'medium': '<span class="badge bg-warning">🟡 Medium Risk</span>',
        'high': '<span class="badge" style="background-color: #ff8c00;">🟠 High Risk</span>',
        'critical': '<span class="badge bg-danger">🔴 Critical Risk</span>'
    };
    return badges[level] || '<span class="badge bg-secondary">Unknown</span>';
}

function getWeatherIcon(condition) {
    const icons = {
        'Clear Sky': '☀️',
        'Partly Cloudy': '⛅',
        'Cloudy': '☁️',
        'Rain': '🌧️',
        'Drizzle': '🌦️',
        'Thunderstorm': '⛈️',
        'Snow': '❄️',
        'Foggy': '🌫️'
    };
    return icons[condition] || '🌤️';
}

function getSentimentBadge(score) {
    if (score >= 50) return '<span class="badge bg-success">Very Positive</span>';
    if (score >= 20) return '<span class="badge bg-info">Positive</span>';
    if (score >= -20) return '<span class="badge bg-secondary">Neutral</span>';
    if (score >= -50) return '<span class="badge bg-warning">Negative</span>';
    return '<span class="badge bg-danger">Very Negative</span>';
}

function formatCurrency(value) {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 4
    }).format(value);
}
</script>
@endpush
