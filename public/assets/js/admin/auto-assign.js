/**
 * Auto-Assign Functionality - FIXED VERSION
 * File: assets/js/admin/auto-assign.js
 */

let autoAssignInProgress = false;

$(document).ready(function() {
    setupAutoAssignEvents();
});

function setupAutoAssignEvents() {
    // Auto-assign button click
    $('#autoAssignBtn').on('click', function(e) {
        e.preventDefault();

        if (autoAssignInProgress) {
            showSmallMessage('Auto-assignment already in progress. Please wait.', 'info');
            return;
        }

        showAutoAssignConfirmation();
    });

    // Quick assign button for individual parcels (dynamic)
    $(document).on('click', '.quick-assign-btn', function(e) {
        e.preventDefault();
        const button = $(this);
        const parcelId = button.data('id');
        const trackingNumber = button.data('tracking');

        showQuickAssignConfirmation(parcelId, trackingNumber, button);
    });
}

function showAutoAssignConfirmation() {
    Swal.fire({
        title: 'Auto-Assign Parcels',
        html: `
            <div class="text-start">
                <p><strong>This operation will:</strong></p>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <iconify-icon icon="solar:check-circle-line-duotone" class="text-success me-2"></iconify-icon>
                        Find all <strong>pending parcels</strong>
                    </li>
                    <li class="mb-2">
                        <iconify-icon icon="solar:user-check-line-duotone" class="text-info me-2"></iconify-icon>
                        Find <strong>available riders</strong> with sufficient capacity
                    </li>
                    <li class="mb-2">
                        <iconify-icon icon="solar:smartphone-rotate-line-duotone" class="text-primary me-2"></iconify-icon>
                        Assign parcels to best riders automatically
                    </li>
                    <li class="mb-2">
                        <iconify-icon icon="solar:bell-line-duotone" class="text-warning me-2"></iconify-icon>
                        Send notifications to assigned riders
                    </li>
                    <li class="mb-2">
                        <iconify-icon icon="solar:refresh-line-duotone" class="text-secondary me-2"></iconify-icon>
                        Update rider status to <strong>busy</strong>
                    </li>
                </ul>
                <div class="alert alert-warning mt-3">
                    <iconify-icon icon="solar:info-circle-line-duotone" class="me-2"></iconify-icon>
                    <strong>Note:</strong> Only riders with status "available" will receive assignments.
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Auto-Assign Now!',
        cancelButtonText: 'Cancel',
        width: '600px'
    }).then((result) => {
        if (result.isConfirmed) {
            performAutoAssign();
        }
    });
}

function performAutoAssign() {
    autoAssignInProgress = true;

    // Disable the button
    $('#autoAssignBtn').prop('disabled', true);

    // Show progress modal
    const progressModal = new bootstrap.Modal(document.getElementById('autoAssignProgressModal'));
    progressModal.show();

    // Reset progress
    $('#assignProgressBar').css('width', '0%');
    $('#progressMessage').html('Searching for pending parcels and available riders...');
    $('#progressStats').html('Pending: -- | Assigned: -- | Failed: --');

    // Animate progress bar
    let progress = 0;
    const progressInterval = setInterval(() => {
        progress += 10;
        if (progress <= 90) {
            $('#assignProgressBar').css('width', progress + '%');
        }
    }, 500);

    $.ajax({
        url: '/admin/parcels/auto-assign',
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        success: function(response) {
            clearInterval(progressInterval);
            $('#assignProgressBar').css('width', '100%');

            setTimeout(() => {
                progressModal.hide();

                // ✅ CRITICAL: Check if response has success flag
                if (response && response.success === true) {
                    // Show success message (small toast, not error)
                    showSmallMessage(response.message || 'Auto-assignment completed successfully!', 'success');
                    
                    // Show results in modal if there are assignments
                    if (response.assigned > 0) {
                        showAutoAssignResults(response);
                    }
                    
                    // ✅ CRITICAL: Immediately refresh DataTable
                    refreshDataTableAndUI();

                } else if (response && response.success === false) {
                    // Show error message only when actually failed
                    showSmallMessage(response.message || 'Auto-assignment failed', 'error');
                    
                    // Still refresh to show any partial updates
                    refreshDataTableAndUI();
                    
                } else {
                    // Handle unexpected response format
                    showSmallMessage('Auto-assignment completed', 'success');
                    refreshDataTableAndUI();
                }
            }, 500);
        },
        error: function(xhr) {
            clearInterval(progressInterval);
            progressModal.hide();

            let errorMsg = 'An error occurred during auto-assignment';
            
            // Try to extract error message from response
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            } else if (xhr.responseText) {
                try {
                    const parsed = JSON.parse(xhr.responseText);
                    if (parsed.message) errorMsg = parsed.message;
                } catch(e) {}
            }

            showSmallMessage(errorMsg, 'error');
            console.error('Auto-assign error:', xhr);
            
            // Still try to refresh table
            refreshDataTableAndUI();
        },
        complete: function() {
            autoAssignInProgress = false;
            $('#autoAssignBtn').prop('disabled', false);
        }
    });
}

// ✅ IMPROVED: Refresh DataTable and UI without page reload
function refreshDataTableAndUI() {
    console.log('Refreshing DataTable...');
    
    // Give a small delay to ensure backend has processed
    setTimeout(function() {
        let refreshed = false;
        
        // Method 1: Using global parcelsTable variable
        if (typeof parcelsTable !== 'undefined' && parcelsTable) {
            parcelsTable.ajax.reload(function() {
                console.log('DataTable reloaded successfully via parcelsTable');
                showSmallMessage('Table refreshed!', 'success');
            }, false);
            refreshed = true;
            return;
        }
        
        // Method 2: Using window.parcelsDataTable
        if (typeof window.parcelsDataTable !== 'undefined' && window.parcelsDataTable) {
            window.parcelsDataTable.ajax.reload(function() {
                console.log('DataTable reloaded via window.parcelsDataTable');
                showSmallMessage('Table refreshed!', 'success');
            }, false);
            refreshed = true;
            return;
        }
        
        // Method 3: Find DataTable by selector
        if ($.fn.DataTable && $('#parcelsTable').length) {
            const table = $('#parcelsTable').DataTable();
            if (table) {
                table.ajax.reload(function() {
                    console.log('DataTable reloaded by selector');
                    showSmallMessage('Table refreshed!', 'success');
                }, false);
                refreshed = true;
                return;
            }
        }
        
        // Method 4: If table exists but not initialized, try to reinitialize
        if ($('#parcelsTable').length && !refreshed) {
            console.log('Attempting to reinitialize DataTable');
            location.reload();
        }
        
        // Refresh icons after table reload
        setTimeout(() => {
            if (typeof iconify !== 'undefined') {
                iconify.scan();
            }
        }, 500);
        
    }, 300);
}

// Show small toast message (NOT a big modal)
function showSmallMessage(message, type = 'success') {
    // Remove existing toasts
    $('.custom-small-toast').remove();

    const icon = type === 'success' ? 'check-circle' : 
                 type === 'error' ? 'danger-circle' : 'info-circle';
    
    const bgColor = type === 'success' ? '#28a745' : 
                    type === 'error' ? '#dc3545' : '#17a2b8';

    const toast = $(`
        <div class="custom-small-toast" style="position: fixed; bottom: 20px; right: 20px; z-index: 99999; 
                    background: ${bgColor}; color: white; padding: 10px 18px; border-radius: 8px; 
                    display: flex; align-items: center; gap: 10px; 
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15); 
                    font-size: 14px; font-weight: 500;
                    animation: slideInRight 0.3s ease;">
            <iconify-icon icon="solar:${icon}-line-duotone" style="font-size: 18px;"></iconify-icon>
            <span>${escapeHtml(message)}</span>
            <button type="button" style="background: none; border: none; color: white; cursor: pointer; margin-left: 10px;" class="toast-close-btn">
                <iconify-icon icon="solar:close-circle-line-duotone" style="font-size: 16px;"></iconify-icon>
            </button>
        </div>
    `);

    $('body').append(toast);

    // Auto close after 4 seconds
    setTimeout(() => {
        toast.fadeOut(300, function() {
            $(this).remove();
        });
    }, 4000);

    // Close on button click
    toast.find('.toast-close-btn').on('click', function() {
        toast.fadeOut(300, function() {
            $(this).remove();
        });
    });
}

// Show quick assign confirmation for single parcel
function showQuickAssignConfirmation(parcelId, trackingNumber, button) {
    Swal.fire({
        title: 'Quick Assign Parcel',
        html: `
            <p>Find the best available rider for parcel <strong>${escapeHtml(trackingNumber)}</strong>?</p>
            <div class="alert alert-info mt-3 text-start">
                <iconify-icon icon="solar:info-circle-line-duotone" class="me-2"></iconify-icon>
                The system will automatically find the most suitable rider based on availability and capacity.
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        confirmButtonText: 'Assign Now',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            performQuickAssign(parcelId, trackingNumber, button);
        }
    });
}

// Perform quick assign for a single parcel
function performQuickAssign(parcelId, trackingNumber, button) {
    const originalHtml = button.html();
    button.html('<span class="spinner-border spinner-border-sm" role="status"></span>');
    button.prop('disabled', true);

    $.ajax({
        url: `/admin/parcels/${parcelId}/assign-best-rider`,
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                showSmallMessage(response.message || 'Parcel assigned successfully!', 'success');
                refreshDataTableAndUI();

                Swal.fire({
                    title: 'Success!',
                    text: response.message,
                    icon: 'success',
                    confirmButtonText: 'OK',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                showSmallMessage(response.message || 'No suitable rider found', 'error');
            }
        },
        error: function(xhr) {
            let message = 'Failed to assign rider';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            showSmallMessage(message, 'error');
        },
        complete: function() {
            button.html(originalHtml);
            button.prop('disabled', false);
        }
    });
}

// Show auto assign results in modal
function showAutoAssignResults(response) {
    const assigned = response.assigned || 0;
    const failed = response.failed || 0;

    // Only show modal if there are assignments
    if (assigned === 0 && failed === 0) {
        return;
    }

    let detailsHtml = '';
    if (response.details && response.details.length > 0) {
        detailsHtml = `
            <div class="mt-3" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-sm">
                    <thead>
                        <tr><th>Tracking</th><th>Rider</th><th>Weight</th></tr>
                    </thead>
                    <tbody>
                        ${response.details.map((detail, index) => `
                            <tr>
                                <td>${escapeHtml(detail.tracking)}</small>
                                <td>${escapeHtml(detail.rider)}</small>
                                <td>${detail.weight} kg</small>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    }

    let resultHtml = `
        <div class="text-center">
            <h4>Assignment Complete!</h4>
            <div class="row mt-3">
                <div class="col-6">
                    <div style="background: #28a745; color: white; padding: 15px; border-radius: 8px;">
                        <h3 class="mb-0">${assigned}</h3>
                        <small>Assigned</small>
                    </div>
                </div>
                <div class="col-6">
                    <div style="background: #ffc107; color: #333; padding: 15px; border-radius: 8px;">
                        <h3 class="mb-0">${failed}</h3>
                        <small>Failed</small>
                    </div>
                </div>
            </div>
            ${detailsHtml}
            <div class="alert alert-success mt-3">
                <iconify-icon icon="solar:refresh-line-duotone" class="me-2"></iconify-icon>
                Table has been refreshed automatically!
            </div>
        </div>
    `;

    $('#resultContent').html(resultHtml);
    const resultModal = new bootstrap.Modal(document.getElementById('autoAssignResultModal'));
    resultModal.show();
}

function escapeHtml(str) {
    if (!str) return '';
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// Add animation style
if (!$('#auto-toast-style').length) {
    const style = $('<style id="auto-toast-style">')
        .text(`
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            .custom-small-toast {
                font-family: system-ui, -apple-system, sans-serif;
            }
        `);
    $('head').append(style);
}

// Export for debugging
window.autoAssign = {
    refreshTable: refreshDataTableAndUI,
    isInProgress: () => autoAssignInProgress
};