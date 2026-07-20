<div class="modal fade" id="addCountryModal" tabindex="-1" aria-labelledby="addCountryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCountryModalLabel">
                    <i class="fas fa-plus-circle"></i> Add Country to Watchlist
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addCountryForm">
                @csrf
                <div class="modal-body">
                    {{-- Country Search --}}
                    <div class="mb-3">
                        <label for="countrySearch" class="form-label">
                            Country <span class="text-danger">*</span>
                        </label>
                        <div class="position-relative">
                            <input 
                                type="text" 
                                class="form-control" 
                                id="countrySearch" 
                                placeholder="Search country by name..."
                                autocomplete="off"
                                required>
                            <input type="hidden" id="countrySelect" name="country_code">
                            <div id="countryDropdown" class="country-dropdown" style="display: none;">
                                <div class="country-dropdown-list" id="countryList">
                                    @foreach($countries as $country)
                                        <div class="country-item" 
                                             data-code="{{ $country->code }}" 
                                             data-name="{{ $country->name }}"
                                             data-flag="{{ $country->flag_url }}">
                                            <img src="{{ $country->flag_url }}" alt="{{ $country->name }}" class="country-flag">
                                            <span class="country-name">{{ $country->name }}</span>
                                            <span class="country-code">{{ $country->code }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> Type to search and select a country
                        </small>
                    </div>

                    {{-- Priority Selection --}}
                    <div class="mb-3">
                        <label for="prioritySelect" class="form-label">Priority <span class="text-danger">*</span></label>
                        <select class="form-select" id="prioritySelect" name="priority" required>
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>

                    {{-- Notes --}}
                    <div class="mb-3">
                        <label for="notesInput" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="notesInput" name="notes" rows="3" maxlength="500" placeholder="Add any notes about this country..."></textarea>
                        <div class="form-text">
                            <span id="charCount">0</span>/500 characters
                        </div>
                    </div>

                    {{-- Error Alert --}}
                    <div id="modalErrorAlert" class="alert alert-danger d-none" role="alert">
                        <i class="fas fa-exclamation-circle"></i> <span id="modalErrorMessage"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="addCountrySubmitBtn">
                        <i class="fas fa-plus"></i> Add to Watchlist
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Country Search Dropdown Styling */
.country-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    margin-top: 0.5rem;
    max-height: 300px;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    z-index: 1000;
}

.country-dropdown-list {
    padding: 0.5rem;
}

.country-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    cursor: pointer;
    border-radius: 8px;
    transition: all 0.2s ease;
    gap: 0.75rem;
}

.country-item:hover {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    transform: translateX(4px);
}

.country-item.selected {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
}

.country-flag {
    width: 32px;
    height: 24px;
    object-fit: cover;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    flex-shrink: 0;
}

.country-name {
    flex: 1;
    font-weight: 600;
    color: inherit;
}

.country-code {
    font-size: 0.85rem;
    color: #6b7280;
    font-weight: 500;
}

.country-item.selected .country-code {
    color: rgba(255, 255, 255, 0.9);
}

.country-item.hidden {
    display: none;
}

/* Scrollbar styling */
.country-dropdown::-webkit-scrollbar {
    width: 8px;
}

.country-dropdown::-webkit-scrollbar-track {
    background: #f3f4f6;
    border-radius: 10px;
}

.country-dropdown::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    border-radius: 10px;
}

.country-dropdown::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
}
</style>

<script>
// Country Search Functionality
document.addEventListener('DOMContentLoaded', function() {
    const countrySearch = document.getElementById('countrySearch');
    const countrySelect = document.getElementById('countrySelect');
    const countryDropdown = document.getElementById('countryDropdown');
    const countryList = document.getElementById('countryList');
    const notesInput = document.getElementById('notesInput');
    const charCount = document.getElementById('charCount');
    
    let selectedCountry = null;
    
    // Character counter for notes field
    if (notesInput && charCount) {
        notesInput.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    }
    
    // Show dropdown when input is focused or typed
    countrySearch.addEventListener('focus', function() {
        countryDropdown.style.display = 'block';
        filterCountries('');
    });
    
    countrySearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        countryDropdown.style.display = 'block';
        filterCountries(searchTerm);
    });
    
    // Filter countries based on search term
    function filterCountries(searchTerm) {
        const countryItems = countryList.querySelectorAll('.country-item');
        let visibleCount = 0;
        
        countryItems.forEach(item => {
            const countryName = item.dataset.name.toLowerCase();
            const countryCode = item.dataset.code.toLowerCase();
            
            if (countryName.includes(searchTerm) || countryCode.includes(searchTerm)) {
                item.classList.remove('hidden');
                visibleCount++;
            } else {
                item.classList.add('hidden');
            }
        });
        
        // Show "no results" message if needed
        if (visibleCount === 0) {
            // You can add a "no results" message here if desired
        }
    }
    
    // Handle country selection
    countryList.addEventListener('click', function(e) {
        const countryItem = e.target.closest('.country-item');
        if (countryItem) {
            // Remove previous selection
            countryList.querySelectorAll('.country-item').forEach(item => {
                item.classList.remove('selected');
            });
            
            // Add selection to clicked item
            countryItem.classList.add('selected');
            
            // Store selected country
            selectedCountry = {
                code: countryItem.dataset.code,
                name: countryItem.dataset.name,
                flag: countryItem.dataset.flag
            };
            
            // Update inputs
            countrySearch.value = selectedCountry.name;
            countrySelect.value = selectedCountry.code;
            
            // Hide dropdown
            countryDropdown.style.display = 'none';
        }
    });
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#countrySearch') && !e.target.closest('#countryDropdown')) {
            countryDropdown.style.display = 'none';
        }
    });
    
    // Reset form when modal is closed
    const modal = document.getElementById('addCountryModal');
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            countrySearch.value = '';
            countrySelect.value = '';
            selectedCountry = null;
            countryList.querySelectorAll('.country-item').forEach(item => {
                item.classList.remove('selected');
            });
            countryDropdown.style.display = 'none';
        });
    }
    
    // Validate that a country is selected before form submission
    const form = document.getElementById('addCountryForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!countrySelect.value) {
                e.preventDefault();
                alert('Please select a country from the list');
                countrySearch.focus();
                return false;
            }
        });
    }
});
</script>
