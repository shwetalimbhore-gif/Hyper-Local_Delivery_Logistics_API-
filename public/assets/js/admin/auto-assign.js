/**
 * Auto-Assign Functionality
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
            showAutoToast('Auto-assignment already in progress. Please wait.', 'info');
            return;
        }

        showAutoAssignConfirmation();
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
        confirmButtonText: '<iconify-icon icon="solar:smartphone-rotate-line-duotone" class="me-2"></iconify-icon>Yes, Auto-Assign Now!',
        cancelButtonText: '<iconify-icon icon="solar:close-circle-line-duotone" class="me-2"></iconify-icon>Cancel',
        width: '600px'
    }).then((result) => {
        if (result.isConfirmed) {
            performAutoAssign();
        }
    });
}

function performAutoAssign() {
    autoAssignInProgress = true;

    // Show progress modal
    const progressModal = new bootstrap.Modal(document.getElementById('autoAssignProgressModal'));
    progressModal.show();

    // Reset progress
    $('#assignProgressBar').css('width', '0%');
    $('#progressMessage').html('Searching for pending parcels and available riders...');
    $('#progressStats').html('Pending: -- | Assigned: -- | Failed: --');

    $.ajax({
        url: '/admin/parcels/auto-assign',
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            progressModal.hide();

            if (response.success) {
                showAutoAssignResults(response);
                showAutoToast(response.message || 'Auto-assignment completed successfully!', 'success');

                // Refresh DataTable
                if (typeof parcelsTable !== 'undefined' && parcelsTable) {
                    parcelsTable.ajax.reload(null, false);
                } else if (window.parcelsDataTable) {
                    window.parcelsDataTable.ajax.reload(null, false);
                }
            } else {
                showAutoToast(response.message || 'Auto-assignment failed', 'error');
            }
        },
        error: function(xhr) {
            progressModal.hide();

            let errorMsg = 'An error occurred during auto-assignment';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }

            showAutoToast(errorMsg, 'error');
            console.error('Auto-assign error:', xhr);
        },
        complete: function() {
            autoAssignInProgress = false;
        }
    });
}

function showAutoAssignResults(response) {
    const assigned = response.assigned || 0;
    const failed = response.failed || 0;

    let detailsHtml = '';
    if (response.details && response.details.length > 0) {
        detailsHtml = `
            <div class="mt-4">
                <h6 class="mb-3">
                    <iconify-icon icon="solar:clipboard-list-line-duotone" class="me-2"></iconify-icon>
                    Assignment Details (${response.details.length} parcels)
                </h6>
                <div class="assignment-details-scroll">
                    <table class="table table-sm table-hover details-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tracking Number</th>
                                <th>Assigned To</th>
                                <th>Weight</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${response.details.map((detail, index) => `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td><strong>${escapeHtml(detail.tracking)}</strong></td>
                                    <td>
                                        <iconify-icon icon="solar:user-circle-line-duotone" class="me-1"></iconify-icon>
                                        ${escapeHtml(detail.rider)}
                                    </td>
                                    <td>${detail.weight} kg</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    let resultHtml = `
        <div class="text-center">
            <iconify-icon icon="solar:checklist-line-duotone" class="result-icon ${assigned > 0 ? 'success' : 'warning'}" style="font-size: 64px;"></iconify-icon>
            <h4 class="mt-3 mb-4">Assignment Complete!</h4>

            <div class="row g-3 mb-4">
                <div class="col-6">
                    <div class="result-card bg-success">
                        <div class="card-body text-center">
                            <h3 class="mb-0">${assigned}</h3>
                            <small>Parcels Assigned</small>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="result-card bg-warning">
                        <div class="card-body text-center">
                            <h3 class="mb-0">${failed}</h3>
                            <small>Failed to Assign</small>
                        </div>
                    </div>
                </div>
            </div>

            ${response.message ? `
                <div class="alert alert-info">
                    <iconify-icon icon="solar:info-circle-line-duotone" class="me-2"></iconify-icon>
                    ${escapeHtml(response.message)}
                </div>
            ` : ''}

            ${detailsHtml}

            <div class="alert alert-secondary mt-3 small">
                <iconify-icon icon="solar:info-circle-line-duotone" class="me-2"></iconify-icon>
                <strong>Info:</strong> Only parcels with status "pending" and riders with status "available" were considered.
                ${assigned > 0 ? 'Assigned riders have been marked as "busy".' : ''}
            </div>
        </div>
    `;

    $('#resultContent').html(resultHtml);

    // Show result modal
    const resultModal = new bootstrap.Modal(document.getElementById('autoAssignResultModal'));
    resultModal.show();
}

function showAutoToast(message, type = 'success') {
    // Remove existing toasts
    $('.custom-toast').remove();

    const icon = type === 'success' ? 'check-circle-line-duotone' :
                 type === 'error' ? 'danger-circle-line-duotone' :
                 'info-circle-line-duotone';

    const toast = $(`
        <div class="custom-toast ${type}">
            <iconify-icon icon="solar:${icon}"></iconify-icon>
            <div class="toast-message">${escapeHtml(message)}</div>
            <iconify-icon icon="solar:close-circle-line-duotone" class="toast-close"></iconify-icon>
        </div>
    `);

    $('body').append(toast);

    // Auto close after 5 seconds
    setTimeout(() => {
        toast.fadeOut(300, function() {
            $(this).remove();
        });
    }, 5000);

    // Close on click
    toast.find('.toast-close').on('click', function() {
        toast.fadeOut(300, function() {
            $(this).remove();
        });
    });
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

// Export for debugging (optional)
window.autoAssign = {
    showToast: showAutoToast,
    isInProgress: () => autoAssignInProgress
};
