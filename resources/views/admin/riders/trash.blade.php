@extends('layouts.admin')

@section('title', 'Trashed Riders')

@push('styles')
<style>
    .trash-card {
        border-radius: 10px;
        border: none;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }

    .table th {
        font-weight: 600;
        color: #555;
        border-top: none;
    }

    .table td {
        vertical-align: middle;
    }

    .btn-group .btn {
        padding: 0.25rem 0.5rem;
    }

    .bulk-actions {
        margin-bottom: 15px;
        padding: 10px 15px;
        background: #f8f9fa;
        border-radius: 8px;
        display: none;
        align-items: center;
        justify-content: space-between;
    }

    .bulk-actions.show {
        display: flex;
    }
</style>
@endpush

@section('content')
<div class="card trash-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title mb-0">
                <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                Trashed Riders
            </h5>
            <div>
                <a href="{{ route('admin.riders.index') }}" class="btn btn-primary">
                    <iconify-icon icon="solar:arrow-left-line-duotone"></iconify-icon>
                    Back to Riders
                </a>
            </div>
        </div>

        <!-- Bulk Actions -->
        <div class="bulk-actions" id="bulkActions">
            <div>
                <span id="selectedCount">0</span> rider(s) selected
            </div>
            <div>
                <button type="button" class="btn btn-success btn-sm" id="bulkRestoreBtn">
                    <iconify-icon icon="solar:refresh-line-duotone"></iconify-icon>
                    Restore Selected
                </button>
                <button type="button" class="btn btn-danger btn-sm" id="bulkForceDeleteBtn">
                    <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                    Delete Forever
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover" id="trashTable" width="100%" data-ajax="{{ route('admin.riders.trash-data') }}">
                <thead>
                    <tr>
                        <th width="50">
                            <input type="checkbox" id="selectAll">
                        </th>
                        <th>ID</th>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Hub</th>
                        <th>Deleted At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- DataTables will populate this via AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Restore Confirmation Modal -->
<div class="modal fade" id="restoreModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white">
                    <iconify-icon icon="solar:refresh-line-duotone"></iconify-icon>
                    Restore Rider
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <iconify-icon icon="solar:refresh-circle-line-duotone" class="fs-1 text-success mb-3"></iconify-icon>
                <h5 class="mb-3">Restore this rider?</h5>
                <p id="restoreMessage" class="mb-2"></p>
                <div class="alert alert-info small">
                    <iconify-icon icon="solar:info-circle-line-duotone"></iconify-icon>
                    The rider will be restored to the main list.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmRestoreBtn">
                    <iconify-icon icon="solar:refresh-line-duotone"></iconify-icon>
                    Restore
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Force Delete Confirmation Modal -->
<div class="modal fade" id="forceDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white">
                    <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                    Permanently Delete Rider
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <iconify-icon icon="solar:danger-circle-line-duotone" class="fs-1 text-danger mb-3"></iconify-icon>
                <h5 class="mb-3">Permanently delete this rider?</h5>
                <p id="forceDeleteMessage" class="mb-2"></p>
                <div class="alert alert-danger small">
                    <iconify-icon icon="solar:info-circle-line-duotone"></iconify-icon>
                    <strong>Warning:</strong> This action cannot be undone!
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmForceDeleteBtn">
                    <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                    Permanently Delete
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let trashTable = null;
let currentActionId = null;
let currentActionName = null;

$(document).ready(function() {
    initializeTrashTable();
    setupEventListeners();
});

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
        responsive: true,
        language: {
            processing: '<div class="spinner-border text-primary spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>',
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            zeroRecords: "No trashed riders found"
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

        $('#restoreMessage').html(`Are you sure you want to restore rider <strong>${currentActionName}</strong>?`);

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
            url: `/admin/riders/restore/${currentActionId}`,
            method: 'PUT',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
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
                let message = 'Error restoring rider';
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
        currentActionName = $(this).data('name');

        $('#forceDeleteMessage').html(`Are you sure you want to permanently delete rider <strong>${currentActionName}</strong>? This action cannot be undone.`);

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
            url: `/admin/riders/force-delete/${currentActionId}`,
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
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
                let message = 'Error deleting rider';
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

function getSelectedIds() {
    const ids = [];
    $('.rider-checkbox:checked').each(function() {
        ids.push($(this).val());
    });
    return ids;
}

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

function performBulkRestore(ids) {
    $.ajax({
        url: "{{ route('admin.riders.bulk-restore') }}",
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            ids: ids
        },
        success: function(response) {
            if (response.success) {
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
        }
    });
}

function performBulkForceDelete(ids) {
    $.ajax({
        url: "{{ route('admin.riders.bulk-force-delete') }}",
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            ids: ids
        },
        success: function(response) {
            if (response.success) {
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
        }
    });
}

function showNotification(message, type = 'success') {
    $('.custom-notification').remove();

    const notification = $(`
        <div class="custom-notification alert alert-${type === 'success' ? 'success' : 'danger'}">
            <iconify-icon icon="solar:${type === 'success' ? 'check-circle' : 'danger-circle'}-line-duotone"></iconify-icon>
            <span>${message}</span>
        </div>
    `);

    notification.css({
        position: 'fixed',
        top: '20px',
        right: '20px',
        zIndex: 9999,
        backgroundColor: type === 'success' ? '#28a745' : '#dc3545',
        color: 'white',
        border: 'none',
        padding: '12px 20px',
        borderRadius: '8px',
        fontSize: '14px',
        boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
        display: 'flex',
        alignItems: 'center',
        gap: '10px',
        zIndex: 10000
    });

    $('body').append(notification);

    setTimeout(() => {
        notification.fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
}
</script>
@endpush
