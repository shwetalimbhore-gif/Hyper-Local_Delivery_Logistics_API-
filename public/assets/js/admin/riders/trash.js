/**
 * Rider Trash Page JavaScript
 * File: public/assets/js/admin/riders/trash.js
 */

let trashTable = null;
let currentActionId = null;
let currentActionName = null;

$(document).ready(function() {
    initializeTrashTable();
    setupEventListeners();
});

/**
 * Initialize DataTable for trash
 */
function initializeTrashTable() {
    const tableElement = $('#trashTable');

    if (!tableElement.length) return;

    // Destroy existing DataTable if any
    if ($.fn.DataTable && $.fn.dataTable.isDataTable(tableElement)) {
        trashTable = tableElement.DataTable();
        trashTable.destroy();
        tableElement.find('thead, tbody, tfoot').show();
    }

    trashTable = tableElement.DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: tableElement.data('ajax'),
            type: 'GET',
            error: function(xhr, error, thrown) {
                console.error('DataTable AJAX Error:', error);
                showNotification('Error loading data', 'error');
            }
        },
        columns: [
            { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
            { data: 'id', name: 'id' },
            { data: 'employee_id', name: 'employee_id' },
            { data: 'full_name', name: 'user.name' },
            { data: 'hub_name', name: 'hub.name' },
            { data: 'deleted_at', name: 'deleted_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[1, 'desc']],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        responsive: true,
        language: {
            processing: '<div class="spinner-border text-primary spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>',
            search: "Search:",
            searchPlaceholder: "Search trashed riders...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)",
            zeroRecords: "No trashed riders found",
            paginate: {
                first: '<iconify-icon icon="solar:double-alt-arrow-left-line-duotone"></iconify-icon>',
                last: '<iconify-icon icon="solar:double-alt-arrow-right-line-duotone"></iconify-icon>',
                next: '<iconify-icon icon="solar:alt-arrow-right-line-duotone"></iconify-icon>',
                previous: '<iconify-icon icon="solar:alt-arrow-left-line-duotone"></iconify-icon>'
            }
        },
        drawCallback: function() {
            if (typeof iconify !== 'undefined') {
                iconify.scan();
            }
            updateBulkActionsVisibility();
        }
    });

    window.trashDataTable = trashTable;
}

/**
 * Setup all event listeners
 */
function setupEventListeners() {
    // Select All checkbox
    $(document).on('change', '#selectAll', function() {
        const isChecked = $(this).prop('checked');
        $('.rider-checkbox').prop('checked', isChecked);
        updateBulkActionsVisibility();
    });

    // Individual checkbox change
    $(document).on('change', '.rider-checkbox', function() {
        updateBulkActionsVisibility();
    });

    // Restore button click
    $(document).on('click', '.restore-btn', function(e) {
        e.preventDefault();
        currentActionId = $(this).data('id');
        currentActionName = $(this).data('name');

        $('#restoreMessage').html(`Are you sure you want to restore rider <strong>${escapeHtml(currentActionName)}</strong>?`);

        const modal = new bootstrap.Modal(document.getElementById('restoreModal'));
        modal.show();
    });

    // Confirm restore - THIS NEEDS TO REFRESH THE TABLE
    $('#confirmRestoreBtn').off('click').on('click', function() {
        if (!currentActionId) return;

        const btn = $(this);
        const originalText = btn.html();
        btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Restoring...');
        btn.prop('disabled', true);

        $.ajax({
            url: `/admin/riders/restore/${currentActionId}`,
            method: 'PUT',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');

                    // ✅ THIS REFRESHES THE TABLE WITHOUT PAGE RELOAD
                    if (trashTable) {
                        trashTable.ajax.reload(null, false);
                    }

                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('restoreModal'));
                    modal.hide();
                } else {
                    showNotification(response.message, 'error');
                }
                currentActionId = null;
            },
            error: function(xhr) {
                let message = 'Error restoring rider';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showNotification(message, 'error');
                currentActionId = null;
            },
            complete: function() {
                btn.html(originalText);
                btn.prop('disabled', false);
            }
        });
    });

    // Force delete button click
    $(document).on('click', '.force-delete-btn', function(e) {
        e.preventDefault();
        currentActionId = $(this).data('id');
        currentActionName = $(this).data('name');

        $('#forceDeleteMessage').html(`Are you sure you want to permanently delete rider <strong>${escapeHtml(currentActionName)}</strong>? This action cannot be undone.`);

        const modal = new bootstrap.Modal(document.getElementById('forceDeleteModal'));
        modal.show();
    });

    // Confirm force delete - THIS NEEDS TO REFRESH THE TABLE
    $('#confirmForceDeleteBtn').off('click').on('click', function() {
        if (!currentActionId) return;

        const btn = $(this);
        const originalText = btn.html();
        btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Deleting...');
        btn.prop('disabled', true);

        $.ajax({
            url: `/admin/riders/force-delete/${currentActionId}`,
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');

                    // ✅ THIS REFRESHES THE TABLE WITHOUT PAGE RELOAD
                    if (trashTable) {
                        trashTable.ajax.reload(null, false);
                    }

                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('forceDeleteModal'));
                    modal.hide();
                } else {
                    showNotification(response.message, 'error');
                }
                currentActionId = null;
            },
            error: function(xhr) {
                let message = 'Error deleting rider';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showNotification(message, 'error');
                currentActionId = null;
            },
            complete: function() {
                btn.html(originalText);
                btn.prop('disabled', false);
            }
        });
    });

    // Bulk restore
    $('#bulkRestoreBtn').off('click').on('click', function() {
        const selectedIds = getSelectedIds();
        if (selectedIds.length === 0) {
            showNotification('Please select at least one rider to restore', 'error');
            return;
        }

        Swal.fire({
            title: 'Restore Selected Riders',
            text: `Are you sure you want to restore ${selectedIds.length} rider(s)?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Yes, restore them!'
        }).then((result) => {
            if (result.isConfirmed) {
                performBulkRestore(selectedIds);
            }
        });
    });

    // Bulk force delete
    $('#bulkForceDeleteBtn').off('click').on('click', function() {
        const selectedIds = getSelectedIds();
        if (selectedIds.length === 0) {
            showNotification('Please select at least one rider to delete', 'error');
            return;
        }

        Swal.fire({
            title: 'Permanently Delete Riders',
            html: `Are you sure you want to permanently delete <strong>${selectedIds.length}</strong> rider(s)?<br><span class="text-danger">This action cannot be undone!</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, delete forever!'
        }).then((result) => {
            if (result.isConfirmed) {
                performBulkForceDelete(selectedIds);
            }
        });
    });
}

/**
 * Get selected rider IDs
 */
function getSelectedIds() {
    const ids = [];
    $('.rider-checkbox:checked').each(function() {
        ids.push($(this).val());
    });
    return ids;
}

/**
 * Update bulk actions visibility
 */
function updateBulkActionsVisibility() {
    const selectedCount = $('.rider-checkbox:checked').length;
    if (selectedCount > 0) {
        $('#bulkActions').addClass('show');
        $('#selectedCount').text(selectedCount);
    } else {
        $('#bulkActions').removeClass('show');
    }

    // Update select all checkbox
    const totalCheckboxes = $('.rider-checkbox').length;
    const checkedCheckboxes = $('.rider-checkbox:checked').length;
    $('#selectAll').prop('checked', totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes);
}

/**
 * Perform bulk restore - THIS REFRESHES THE TABLE
 */
function performBulkRestore(ids) {
    const btn = $('#bulkRestoreBtn');
    const originalText = btn.html();
    btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Restoring...');
    btn.prop('disabled', true);

    $.ajax({
        url: '/admin/riders/bulk-restore',
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            ids: ids
        },
        success: function(response) {
            if (response.success) {
                showNotification(response.message, 'success');

                // ✅ THIS REFRESHES THE TABLE WITHOUT PAGE RELOAD
                if (trashTable) {
                    trashTable.ajax.reload(null, false);
                }
                $('#selectAll').prop('checked', false);
            } else {
                showNotification(response.message, 'error');
            }
        },
        error: function(xhr) {
            showNotification('Error performing bulk restore', 'error');
        },
        complete: function() {
            btn.html(originalText);
            btn.prop('disabled', false);
        }
    });
}

/**
 * Perform bulk force delete - THIS REFRESHES THE TABLE
 */
function performBulkForceDelete(ids) {
    const btn = $('#bulkForceDeleteBtn');
    const originalText = btn.html();
    btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Deleting...');
    btn.prop('disabled', true);

    $.ajax({
        url: '/admin/riders/bulk-force-delete',
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            ids: ids
        },
        success: function(response) {
            if (response.success) {
                showNotification(response.message, 'success');

                // ✅ THIS REFRESHES THE TABLE WITHOUT PAGE RELOAD
                if (trashTable) {
                    trashTable.ajax.reload(null, false);
                }
                $('#selectAll').prop('checked', false);
            } else {
                showNotification(response.message, 'error');
            }
        },
        error: function(xhr) {
            showNotification('Error performing bulk delete', 'error');
        },
        complete: function() {
            btn.html(originalText);
            btn.prop('disabled', false);
        }
    });
}

/**
 * Show notification message
 */
function showNotification(message, type = 'success') {
    $('.custom-notification').remove();

    const notification = $(`
        <div class="custom-notification ${type}">
            <iconify-icon icon="solar:${type === 'success' ? 'check-circle' : 'danger-circle'}-line-duotone"></iconify-icon>
            <span>${escapeHtml(message)}</span>
        </div>
    `);

    notification.css({
        position: 'fixed',
        top: '20px',
        right: '20px',
        zIndex: 9999,
        backgroundColor: type === 'success' ? '#10b981' : '#ef4444',
        color: 'white',
        border: 'none',
        padding: '12px 20px',
        borderRadius: '8px',
        fontSize: '14px',
        boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
        display: 'flex',
        alignItems: 'center',
        gap: '10px',
        animation: 'slideInRight 0.3s ease'
    });

    $('body').append(notification);

    setTimeout(() => {
        notification.fadeOut(300, function() {
            $(this).remove();
        });
    }, 5000);
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(str) {
    if (!str) return '';
    return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// Add animation styles
const style = document.createElement('style');
style.textContent = `
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

    .custom-notification {
        z-index: 10000;
    }
`;
document.head.appendChild(style);

// Make functions globally accessible
window.refreshTrashTable = function() {
    if (trashTable) {
        trashTable.ajax.reload(null, false);
    }
};
