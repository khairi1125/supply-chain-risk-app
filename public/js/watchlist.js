/**
 * Watchlist JavaScript Module
 * Handles AJAX operations, filtering, sorting, and UI updates
 */

$(document).ready(function() {
    // Cache DOM elements for performance
    const $addCountryBtn = $('#addCountryBtn');
    const $addFirstCountryBtn = $('#addFirstCountryBtn');
    const $addCountryModal = $('#addCountryModal');
    const $addCountryForm = $('#addCountryForm');
    const $searchInput = $('#searchInput');
    const $regionFilter = $('#regionFilter');
    const $riskFilter = $('#riskFilter');
    const $clearFiltersBtn = $('#clearFiltersBtn');
    const $watchlistTable = $('#watchlistTable tbody');
    
    // CSRF token is already set globally in layouts/app.blade.php
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    // ==========================================
    // MODAL HANDLERS (Task 14.2)
    // ==========================================

    // Open modal when clicking Add Country button
    $addCountryBtn.on('click', function() {
        $addCountryModal.modal('show');
    });

    // Open modal when clicking Add First Country (empty state)
    $addFirstCountryBtn.on('click', function() {
        $addCountryModal.modal('show');
    });

    // Handle Add Country Form Submission (AJAX)
    $addCountryForm.on('submit', function(e) {
        e.preventDefault();

        const formData = {
            country_code: $('#countrySelect').val(),
            priority: $('#prioritySelect').val(),
            notes: $('#notesInput').val(),
        };

        // Validate
        if (!formData.country_code) {
            showModalError('Please select a country');
            return;
        }

        // Disable submit button and show loading
        const $submitBtn = $('#addCountrySubmitBtn');
        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adding...');

        // AJAX POST request
        $.ajax({
            url: '/api/watchlist',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Success - reload page to show updated watchlist
                    $addCountryModal.modal('hide');
                    showToast('Success!', 'Country added to watchlist successfully', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showModalError(response.message || 'Failed to add country');
                    $submitBtn.prop('disabled', false).html('<i class="fas fa-plus"></i> Add to Watchlist');
                }
            },
            error: function(xhr) {
                let errorMessage = 'An error occurred';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showModalError(errorMessage);
                console.error('Add country error:', xhr);
                $submitBtn.prop('disabled', false).html('<i class="fas fa-plus"></i> Add to Watchlist');
            }
        });
    });

    // Reset form when modal is closed
    $addCountryModal.on('hidden.bs.modal', function() {
        $addCountryForm[0].reset();
        $('#charCount').text('0');
        $('#modalErrorAlert').addClass('d-none');
    });

    // ==========================================
    // DELETE FUNCTIONALITY (Task 14.3)
    // ==========================================

    $(document).on('click', '.deleteBtn', function() {
        const watchlistId = $(this).data('id');
        const countryName = $(this).data('country');
        const $row = $(this).closest('tr');

        // Show confirmation dialog
        if (confirm(`Are you sure you want to remove ${countryName} from your watchlist?`)) {
            const $btn = $(this);
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            // AJAX DELETE request
            $.ajax({
                url: `/api/watchlist/${watchlistId}`,
                method: 'DELETE',
                success: function(response) {
                    if (response.success) {
                        // Remove row from table with animation
                        $row.fadeOut(300, function() {
                            $(this).remove();
                            updateSummaryCards();
                            updateRiskChart();
                        });
                        showToast('Success!', 'Country removed from watchlist', 'success');
                    } else {
                        showToast('Error', response.message, 'error');
                        $btn.prop('disabled', false).html('<i class="fas fa-trash"></i>');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to remove country';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    showToast('Error', errorMessage, 'error');
                    console.error('Delete error:', xhr);
                    $btn.prop('disabled', false).html('<i class="fas fa-trash"></i>');
                }
            });
        }
    });

    // ==========================================
    // REFRESH FUNCTIONALITY (Task 14.4)
    // ==========================================

    $(document).on('click', '.refreshBtn', function() {
        const watchlistId = $(this).data('id');
        const $btn = $(this);
        const $row = $(this).closest('tr');

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        // AJAX POST request to refresh
        $.ajax({
            url: `/api/watchlist/${watchlistId}/refresh`,
            method: 'POST',
            success: function(response) {
                if (response.success) {
                    showToast('Success!', 'Data refreshed successfully', 'success');
                    // Reload page to show fresh data
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Error', response.message, 'error');
                    $btn.prop('disabled', false).html('<i class="fas fa-rotate"></i>');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Failed to refresh data';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showToast('Error', errorMessage, 'error');
                console.error('Refresh error:', xhr);
                $btn.prop('disabled', false).html('<i class="fas fa-rotate"></i>');
            }
        });
    });

    // ==========================================
    // FILTERING (Task 15.1, 15.2)
    // ==========================================

    // Search filter with debounce
    let searchTimeout;
    $searchInput.on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 300);
    });

    // Region and risk level filters
    $regionFilter.on('change', applyFilters);
    $riskFilter.on('change', applyFilters);

    // Clear filters (Task 15.3)
    $clearFiltersBtn.on('click', function() {
        $searchInput.val('');
        $regionFilter.val('');
        $riskFilter.val('');
        applyFilters();
    });

    function applyFilters() {
        const searchTerm = $searchInput.val().toLowerCase();
        const selectedRegion = $regionFilter.val().toLowerCase();
        const selectedRisk = $riskFilter.val().toLowerCase();

        let visibleCount = 0;

        $watchlistTable.find('tr').each(function() {
            const $row = $(this);
            const country = $row.data('country');
            const region = $row.data('region') ? $row.data('region').toLowerCase() : '';
            const riskLevel = $row.data('risk-level') ? $row.data('risk-level').toLowerCase() : '';

            let showRow = true;

            // Apply search filter
            if (searchTerm && !country.includes(searchTerm)) {
                showRow = false;
            }

            // Apply region filter
            if (selectedRegion && region !== selectedRegion) {
                showRow = false;
            }

            // Apply risk level filter
            if (selectedRisk && riskLevel !== selectedRisk) {
                showRow = false;
            }

            if (showRow) {
                $row.show();
                visibleCount++;
            } else {
                $row.hide();
            }
        });
    }

    // ==========================================
    // SORTING (Task 15.4)
    // ==========================================

    let currentSort = { column: null, direction: 'asc' };

    $('.sortable').on('click', function() {
        const sortType = $(this).data('sort');
        
        // Toggle direction
        if (currentSort.column === sortType) {
            currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
        } else {
            currentSort.column = sortType;
            currentSort.direction = 'asc';
        }

        // Update sort icons
        $('.sortable i').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');
        $(this).find('i').removeClass('fa-sort')
            .addClass(currentSort.direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down');

        // Sort rows
        const rows = $watchlistTable.find('tr').get();
        rows.sort(function(a, b) {
            let valA, valB;

            if (sortType === 'country') {
                valA = $(a).data('country');
                valB = $(b).data('country');
            } else if (sortType === 'risk') {
                valA = parseFloat($(a).data('risk-score')) || 0;
                valB = parseFloat($(b).data('risk-score')) || 0;
            }

            if (currentSort.direction === 'asc') {
                return valA > valB ? 1 : valA < valB ? -1 : 0;
            } else {
                return valA < valB ? 1 : valA > valB ? -1 : 0;
            }
        });

        $.each(rows, function(index, row) {
            $watchlistTable.append(row);
        });
    });

    // ==========================================
    // VIEW DASHBOARD NAVIGATION (Task 16.4)
    // ==========================================

    $(document).on('click', '.viewDashboardBtn', function() {
        const countryCode = $(this).data('country-code');
        window.location.href = `/country-monitor?code=${countryCode}`;
    });

    // ==========================================
    // HELPER FUNCTIONS (Task 16.1, 16.2, 16.3)
    // ==========================================

    function updateSummaryCards() {
        // Recalculate from visible rows
        let total = 0, low = 0, medium = 0, high = 0, critical = 0;

        $watchlistTable.find('tr:visible').each(function() {
            total++;
            const riskLevel = $(this).data('risk-level');
            if (riskLevel === 'low') low++;
            else if (riskLevel === 'medium') medium++;
            else if (riskLevel === 'high') high++;
            else if (riskLevel === 'critical') critical++;
        });

        $('#totalCountCard').text(total);
        $('#lowRiskCard').text(low);
        $('#mediumRiskCard').text(medium);
        $('#highRiskCard').text(high + critical);
    }

    function updateRiskChart() {
        if (window.riskChart) {
            // Recalculate from visible rows
            let low = 0, medium = 0, high = 0, critical = 0;

            $watchlistTable.find('tr').each(function() {
                const riskLevel = $(this).data('risk-level');
                if (riskLevel === 'low') low++;
                else if (riskLevel === 'medium') medium++;
                else if (riskLevel === 'high') high++;
                else if (riskLevel === 'critical') critical++;
            });

            window.riskChart.data.datasets[0].data = [low, medium, high, critical];
            window.riskChart.update();
        }
    }

    function showModalError(message) {
        $('#modalErrorMessage').text(message);
        $('#modalErrorAlert').removeClass('d-none');
    }

    function showToast(title, message, type = 'success') {
        // Use Bootstrap alerts as toast alternative
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        const toast = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 80px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
                <i class="fas ${icon}"></i> <strong>${title}</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);

        $('body').append(toast);

        setTimeout(() => {
            toast.alert('close');
        }, 3000);
    }
});
