/**
 * Hub Trash Page JavaScript
 * File: public/assets/js/admin/hubs/trash.js
 */

let trashTable = null;
let currentActionId = null;
let currentActionCode = null;

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
            { data: 'code', name: 'code' },
            { data: 'name', name: 'name' },
            { data: 'manager_name', name: 'manager_name' },
            { data: 'deleted_at', name: 'deleted_at' },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        order: [[1, 'desc']],
        pageLength: 10,
        responsive: true,
        language: {
            processing: '<div class="spinner-border text-primary spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>',
            search: "Search:",
            searchPlaceholder: "Search trashed hubs...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            zeroRecords: "No trashed hubs found",
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
        $('.hub-checkbox').prop('checked', isChecked);
        updateBulkActionsVisibility();
    });

    // Individual checkbox change
    $(document).on('change', '.hub-checkbox', function() {
        updateBulkActionsVisibility();
    });

    // Restore button click
    $(document).on('click', '.restore-btn', function(e) {
        e.preventDefault();
        currentActionId = $(this).data('id');
        currentActionCode = $(this).data('code');

        $('#restoreMessage').html(`Are you sure you want to restore hub <strong>${currentActionCode}</strong>?`);

        const modal = new bootstrap.Modal(document.getElementById('restoreModal'));
        modal.show();
    });

    // Confirm restore
    $('#confirmRestoreBtn').off('click').on('click', function() {
        if (!currentActionId) return;

        const btn = $(this);
        const originalText = btn.html();
        btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Restoring...');
        btn.prop('disabled', true);

        $.ajax({
            url: `/admin/hubs/restore/${currentActionId}`,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                _method: 'PUT'
            },
            success: function(response) {
                if (response.success) {
                    // IMPORTANT: Clear auto-save draft for this hub
                    clearHubDraft(currentActionId);

                    showNotification(response.message, 'success');
                    if (trashTable) {
                        trashTable.ajax.reload(null, false);
                    }
                } else {
                    showNotification(response.message, 'error');
                }

                const modal = bootstrap.Modal.getInstance(document.getElementById('restoreModal'));
                modal.hide();
                currentActionId = null;
            },
            error: function(xhr) {
                let message = 'Error restoring hub';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showNotification(message, 'error');

                const modal = bootstrap.Modal.getInstance(document.getElementById('restoreModal'));
                modal.hide();
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
        currentActionCode = $(this).data('code');

        $('#forceDeleteMessage').html(`Are you sure you want to permanently delete hub <strong>${currentActionCode}</strong>? This action cannot be undone.`);

        const modal = new bootstrap.Modal(document.getElementById('forceDeleteModal'));
        modal.show();
    });

    // Confirm force delete
    $('#confirmForceDeleteBtn').off('click').on('click', function() {
        if (!currentActionId) return;

        const btn = $(this);
        const originalText = btn.html();
        btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Deleting...');
        btn.prop('disabled', true);

        $.ajax({
            url: `/admin/hubs/force-delete/${currentActionId}`,
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Clear draft for this hub
                    clearHubDraft(currentActionId);

                    showNotification(response.message, 'success');
                    if (trashTable) {
                        trashTable.ajax.reload(null, false);
                    }
                } else {
                    showNotification(response.message, 'error');
                }

                const modal = bootstrap.Modal.getInstance(document.getElementById('forceDeleteModal'));
                modal.hide();
                currentActionId = null;
            },
            error: function(xhr) {
                let message = 'Error deleting hub';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showNotification(message, 'error');

                const modal = bootstrap.Modal.getInstance(document.getElementById('forceDeleteModal'));
                modal.hide();
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
            showNotification('Please select at least one hub to restore', 'error');
            return;
        }

        Swal.fire({
            title: 'Restore Selected Hubs',
            text: `Are you sure you want to restore ${selectedIds.length} hub(s)?`,
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
            showNotification('Please select at least one hub to delete', 'error');
            return;
        }

        Swal.fire({
            title: 'Permanently Delete Hubs',
            html: `Are you sure you want to permanently delete <strong>${selectedIds.length}</strong> hub(s)?<br><span class="text-danger">This action cannot be undone!</span>`,
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
 * Clear saved draft for a specific hub (fixes the auto-save restore message)
 */
function clearHubDraft(hubId) {
    // Clear main draft
    localStorage.removeItem('hub_edit_draft');

    // Clear any hub-specific drafts
    for (let i = 0; i < localStorage.length; i++) {
        const key = localStorage.key(i);
        if (key && (key.includes('hub_edit_draft') || key.includes(`hub_${hubId}`))) {
            localStorage.removeItem(key);
            console.log(`Cleared draft: ${key}`);
        }
    }
}

/**
 * Get selected hub IDs
 */
function getSelectedIds() {
    const ids = [];
    $('.hub-checkbox:checked').each(function() {
        ids.push($(this).val());
    });
    return ids;
}

/**
 * Update bulk actions visibility
 */
function updateBulkActionsVisibility() {
    const selectedCount = $('.hub-checkbox:checked').length;
    if (selectedCount > 0) {
        $('#bulkActions').addClass('show');
        $('#selectedCount').text(selectedCount);
    } else {
        $('#bulkActions').removeClass('show');
    }

    // Update select all checkbox
    const totalCheckboxes = $('.hub-checkbox').length;
    const checkedCheckboxes = $('.hub-checkbox:checked').length;
    $('#selectAll').prop('checked', totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes);
}

/**
 * Perform bulk restore
 */
function performBulkRestore(ids) {
    const btn = $('#bulkRestoreBtn');
    const originalText = btn.html();
    btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Restoring...');
    btn.prop('disabled', true);

    $.ajax({
        url: '/admin/hubs/bulk-restore',
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            ids: ids
        },
        success: function(response) {
            if (response.success) {
                // Clear drafts for all restored hubs
                ids.forEach(id => clearHubDraft(id));

                showNotification(response.message, 'success');
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
 * Perform bulk force delete
 */
function performBulkForceDelete(ids) {
    const btn = $('#bulkForceDeleteBtn');
    const originalText = btn.html();
    btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Deleting...');
    btn.prop('disabled', true);

    $.ajax({
        url: '/admin/hubs/bulk-force-delete',
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            ids: ids
        },
        success: function(response) {
            if (response.success) {
                // Clear drafts for all deleted hubs
                ids.forEach(id => clearHubDraft(id));

                showNotification(response.message, 'success');
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

    $('body').append(notification);

    setTimeout(() => {
        notification.fadeOut('slow', function() {
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

// Make functions globally accessible
window.clearHubDraft = clearHubDraft;
window.refreshTrashTable = function() {
    if (trashTable) trashTable.ajax.reload(null, false);
};
