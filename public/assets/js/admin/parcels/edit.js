/**
 * Admin Parcels Edit Page JavaScript
 * Handles status changes, rider assignment, auto-assign, and quick status update
 */

$(document).ready(function() {
    // Initialize all handlers
    handleStatusChange();
    initQuickStatusChange();
    filterRidersByHub();

    // Handle status change event
    $('#statusSelect').on('change', function() {
        handleStatusChange();
    });

    // Handle form submission
    $('#parcelForm').on('submit', function(e) {
        // Validate before submit
        if (!validateFormBeforeSubmit()) {
            e.preventDefault();
            return false;
        }

        // Show loading state
        $('#submitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Updating...');
    });

    // Filter riders by hub
    $('#sourceHubId').on('change', function() {
        filterRidersByHub();
    });
});

/**
 * Handle status change - show/hide failure reason field
 */
function handleStatusChange() {
    var selectedOption = $('#statusSelect option:selected');
    var statusSlug = selectedOption.data('status-slug');

    // Check if failure reason field exists, if not create it
    if ($('#failureReasonGroup').length === 0) {
        // Add failure reason field dynamically
        var failureHtml = `
            <div id="failureReasonGroup" class="mb-3" style="display: none;">
                <label class="form-label text-danger">Failure Reason <span class="text-danger">*</span></label>
                <select name="failure_reason" id="failureReason" class="form-control">
                    <option value="">-- Select Reason --</option>
                    <option value="Wrong Address">Wrong Address</option>
                    <option value="Receiver Not Available">Receiver Not Available</option>
                    <option value="Phone Not Reachable">Phone Not Reachable</option>
                    <option value="Location Not Found">Location Not Found</option>
                    <option value="Parcel Damaged">Parcel Damaged</option>
                    <option value="Refused by Receiver">Refused by Receiver</option>
                    <option value="Weather Issues">Weather Issues</option>
                    <option value="Vehicle Problem">Vehicle Problem</option>
                </select>
                <small class="text-muted">Please select the reason for delivery failure</small>
            </div>
        `;

        // Insert after status select
        $('#statusSelect').closest('.mb-3').after(failureHtml);
    }

    // Show/hide failure reason for failed delivery
    if (statusSlug === 'failed-delivery') {
        $('#failureReasonGroup').show();
        $('#failureReason').prop('required', true);
    } else {
        $('#failureReasonGroup').hide();
        $('#failureReason').prop('required', false);
    }
}

/**
 * Filter riders by selected hub
 */
function filterRidersByHub() {
    var selectedHubId = $('#sourceHubId').val();
    var currentRiderId = $('#riderSelect').val();

    if (!selectedHubId) {
        $('#riderSelect option').show();
        return;
    }

    $('#riderSelect option').each(function() {
        var option = $(this);
        var riderHubId = option.data('hub');

        if (!option.val()) {
            option.show();
            return;
        }

        if (riderHubId && riderHubId != selectedHubId) {
            option.hide();
            if (option.val() == currentRiderId) {
                $('#riderSelect').val('');
                showAlertMessage('Selected rider is not available for this hub. Please select another rider.', 'warning');
            }
        } else {
            option.show();
        }
    });
}

/**
 * Auto assign rider based on weight, size, and hub
 */
function autoAssignRider() {
    var weight = parseFloat($('#weight').val()) || 0;
    var size = parseFloat($('#size').val()) || 0;
    var hubId = $('#sourceHubId').val();
    var parcelId = $('#autoAssignBtn').data('parcel-id');

    // Validation
    if (weight <= 0) {
        showAlertMessage('Please enter a valid weight (minimum 0.1 kg)', 'danger');
        $('#weight').focus();
        return;
    }

    if (size <= 0) {
        showAlertMessage('Please enter a valid size (minimum 0.1 cm³)', 'danger');
        $('#size').focus();
        return;
    }

    if (!hubId) {
        showAlertMessage('Please select a source hub first', 'danger');
        $('#sourceHubId').focus();
        return;
    }

    // Show loading state
    var autoAssignBtn = $('#autoAssignBtn');
    var originalText = autoAssignBtn.html();
    autoAssignBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Finding...');

    // Clear any existing error messages
    $('#autoAssignMessage').hide();

    $.ajax({
        url: $('#autoAssignBtn').data('url'),
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            weight: weight,
            size: size,
            hub_id: hubId,
            parcel_id: parcelId,
            assign: 1
        },
        success: function(response) {
            console.log('Auto assign response:', response);

            if (response.success && response.rider) {
                // Success - Update the UI without refresh

                // 1. Update the rider select dropdown
                $('#riderSelect').val(response.rider.id);

                // 2. Auto-set status to assigned
                var assignedStatusId = $('#statusSelect option[data-status-slug="assigned"]').val();
                if (assignedStatusId) {
                    $('#statusSelect').val(assignedStatusId);
                    // Update the quick status select as well
                    $('#quickStatusSelect').val(assignedStatusId);
                }

                // 3. Update the status display if there's a badge
                if ($('.current-status-badge').length) {
                    var statusText = $('#statusSelect option:selected').text();
                    $('.current-status-badge').html('<span class="badge bg-primary">' + statusText + '</span>');
                }

                // 4. Show success message (not error)
                showAlertMessage('✓ Rider "' + response.rider.name + '" assigned successfully! Status set to "Assigned".', 'success');

                // 5. Show rider details modal (optional)
                showRiderDetails(response.rider);

                // 6. Trigger a custom event to update any other components
                $(document).trigger('parcel-auto-assigned', [response.rider]);

            } else {
                // No rider found - show warning, not error
                showAlertMessage('⚠️ No available rider found for these requirements. Please check rider capacity and hub assignment.', 'warning');
            }
        },
        error: function(xhr) {
            console.error('Auto assign error:', xhr);
            var errorMsg = 'Failed to assign rider';
            try {
                var response = JSON.parse(xhr.responseText);
                errorMsg = response.message || response.error || 'Failed to assign rider';
            } catch(e) {
                errorMsg = xhr.statusText || 'Failed to assign rider';
            }
            // Show as warning instead of error
            showAlertMessage('⚠️ ' + errorMsg, 'warning');
        },
        complete: function() {
            // Re-enable button
            autoAssignBtn.prop('disabled', false).html(originalText);

            // Re-enable the submit button if it was disabled
            $('#submitBtn').prop('disabled', false);
        }
    });
}

/**
 * Show rider details in a modal
 */
function showRiderDetails(rider) {
    // Check if modal exists, if not create it
    if ($('#riderDetailsModal').length === 0) {
        var modalHtml = `
            <div class="modal fade" id="riderDetailsModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title">Rider Auto-Assigned</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body" id="riderDetailsBody">
                            <!-- Dynamic content -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        $('body').append(modalHtml);
    }

    var detailsHtml = `
        <table class="table table-borderless">
            <tr><th width="35%">Name:</th><td><strong>${rider.name}</strong></td>
            </tr>
            <tr><th>Employee ID:</th><td>${rider.employee_id || 'N/A'}</td>
            </tr>
            <tr><th>Max Capacity:</th><td>${rider.max_weight_capacity || 0} kg / ${rider.max_size_capacity || 0} cm³</td>
            </tr>
            <tr><th>Status:</th><td><span class="badge bg-${rider.status === 'available' ? 'success' : 'warning'}">${rider.status}</span></td>
            </tr>
            <tr><th>Rating:</th><td>${rider.rating || 'N/A'} ★</td>
            </tr>
        </table>
    `;

    $('#riderDetailsBody').html(detailsHtml);
    $('#riderDetailsModal').modal('show');
}

/**
 * Validate form before submit
 */
function validateFormBeforeSubmit() {
    var statusSlug = $('#statusSelect option:selected').data('status-slug');
    var failureReason = $('#failureReason').val();

    // Check if failure reason is required
    if (statusSlug === 'failed-delivery' && (!failureReason || failureReason === '')) {
        showAlertMessage('Please select a failure reason for failed delivery', 'danger');
        $('#failureReason').focus();
        return false;
    }

    return true;
}

function showAlertMessage(message, type) {
    var alertDiv = $('#autoAssignMessage');

    if (alertDiv.length === 0) {
        // Create alert container if it doesn't exist
        var container = $('<div id="autoAssignMessage" class="auto-assign-message" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;"></div>');
        $('body').append(container);
        alertDiv = container;
    }

    // Set icon based on type
    var icon = '';
    var bgClass = '';
    switch(type) {
        case 'success':
            icon = '✅';
            bgClass = 'bg-success';
            break;
        case 'danger':
            icon = '❌';
            bgClass = 'bg-danger';
            break;
        case 'warning':
            icon = '⚠️';
            bgClass = 'bg-warning';
            break;
        default:
            icon = 'ℹ️';
            bgClass = 'bg-info';
    }

    // Create toast notification
    var toastHtml = `
        <div class="toast-notification ${bgClass} text-white p-3 mb-2 rounded shadow" style="animation: slideInRight 0.3s ease;">
            <div class="d-flex align-items-center">
                <div class="me-2 fs-4">${icon}</div>
                <div class="flex-grow-1">${message}</div>
                <button type="button" class="btn-close btn-close-white" onclick="$(this).closest('.toast-notification').remove()"></button>
            </div>
        </div>
    `;

    alertDiv.append(toastHtml);

    // Auto remove after 5 seconds
    setTimeout(function() {
        alertDiv.children().first().fadeOut(500, function() {
            $(this).remove();
        });
    }, 5000);
}

// Add CSS animation for toast notifications
$('<style>')
    .prop('type', 'text/css')
    .html(`
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        .toast-notification {
            animation: slideInRight 0.3s ease;
            cursor: pointer;
        }
        .toast-notification:hover {
            opacity: 0.9;
        }
    `)
    .appendTo('head');

/**
 * Quick Status Change Functionality
 * Allows admin to change parcel status without updating entire form
 */
function initQuickStatusChange() {
    // Show/hide failure reason based on selected status
    $('#quickStatusSelect').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var statusSlug = selectedOption.data('status-slug');
        var currentStatusSlug = $('#statusSelect option:selected').data('status-slug');

        if (statusSlug === 'failed-delivery') {
            $('#quickFailureReasonDiv').show();
            $('#quickFailureReason').prop('required', true);
        } else {
            $('#quickFailureReasonDiv').hide();
            $('#quickFailureReason').prop('required', false);
        }

        // Show warning if status is being changed directly
        if (statusSlug && statusSlug !== currentStatusSlug) {
            showQuickStatusMessage('Status will be changed from current status to ' + selectedOption.text(), 'info');
        }
    });

    // Handle quick status button click
    $('#quickStatusBtn').on('click', function() {
        var newStatusId = $('#quickStatusSelect').val();
        var newStatusText = $('#quickStatusSelect option:selected').text();
        var newStatusSlug = $('#quickStatusSelect option:selected').data('status-slug');
        var failureReason = $('#quickFailureReason').val();
        var notes = $('#quickStatusNotes').val();
        var parcelId = $('#quickStatusBtn').data('parcel-id') || $('meta[name="parcel-id"]').attr('content');

        // Get parcel ID from data attribute or from URL
        if (!parcelId) {
            var urlParts = window.location.pathname.split('/');
            parcelId = urlParts[urlParts.indexOf('parcels') + 1];
        }

        // Validate
        if (!newStatusId) {
            showQuickStatusMessage('Please select a status to update', 'danger');
            return;
        }

        if (newStatusSlug === 'failed-delivery' && (!failureReason || failureReason === '')) {
            showQuickStatusMessage('Please select a failure reason for failed delivery', 'danger');
            return;
        }

        // Confirm status change
        if (!confirm('Are you sure you want to change the status to "' + newStatusText + '"?')) {
            return;
        }

        // Disable button and show loading
        var $btn = $(this);
        var originalText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Updating...');

        // Make AJAX request to update status
        $.ajax({
            url: '/admin/parcels/' + parcelId + '/update-status',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                status_id: newStatusId,
                failure_reason: failureReason,
                notes: notes,
                _method: 'PUT'
            },
            success: function(response) {
                if (response.success) {
                    showQuickStatusMessage(response.message, 'success');

                    // Update the main status select to match
                    $('#statusSelect').val(newStatusId);

                    // Update the quick status select current value
                    $('#quickStatusSelect').val(newStatusId);

                    // Update any status badges on the page
                    if (response.parcel && response.parcel.status) {
                        $('.current-status-badge').html('<span class="badge" style="background-color: ' + response.parcel.status.color_code + '">' + response.parcel.status.display_name + '</span>');
                    }

                    // Reload page after 2 seconds to show updated data
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    showQuickStatusMessage(response.error || 'Failed to update status', 'danger');
                }
            },
            error: function(xhr) {
                var errorMsg = xhr.responseJSON?.error || xhr.responseJSON?.message || 'An error occurred';
                showQuickStatusMessage(errorMsg, 'danger');
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });
}

/**
 * Show quick status message
 */
function showQuickStatusMessage(message, type) {
    var $messageDiv = $('#quickStatusMessage');
    if ($messageDiv.length === 0) {
        // Create message div if it doesn't exist
        $('.card-body').prepend('<div id="quickStatusMessage" class="alert" style="display: none;"></div>');
        $messageDiv = $('#quickStatusMessage');
    }

    $messageDiv.removeClass('alert-success alert-danger alert-warning alert-info')
        .addClass('alert alert-' + (type === 'success' ? 'success' : type === 'danger' ? 'danger' : type === 'warning' ? 'warning' : 'info'))
        .html('<iconify-icon icon="solar:' + (type === 'success' ? 'check-circle' : type === 'danger' ? 'danger-circle' : 'info-circle') + '-line-duotone"></iconify-icon> ' + message)
        .fadeIn(300);

    // Auto hide after 5 seconds
    setTimeout(function() {
        $messageDiv.fadeOut(500);
    }, 5000);
}

// Global function for auto assign (called from on-click)
window.autoAssignRider = autoAssignRider;
