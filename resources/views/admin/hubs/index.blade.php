@extends('layouts.admin')

@section('title', 'Manage Hubs')

@push('styles')
<style>
    .hubs-card {
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
</style>
@endpush

@section('content')
<div class="card hubs-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title mb-0">All Hubs</h5>
            <div>
                <a href="{{ route('admin.hubs.trash') }}" class="btn btn-secondary me-2">
                    <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                    Trash
                </a>
                <a href="{{ route('admin.hubs.create') }}" class="btn btn-primary">
                    <iconify-icon icon="solar:add-circle-line-duotone"></iconify-icon>
                    Create New Hub
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover" id="hubsTable" width="100%" data-ajax="{{ route('admin.hubs.data') }}">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Manager</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Riders</th>
                        <th>Parcels</th>
                        <th>Status</th>
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="softDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <iconify-icon icon="solar:info-circle-line-duotone"></iconify-icon>
                    Move to Trash
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <iconify-icon icon="solar:trash-bin-trash-line-duotone" class="fs-1 text-warning mb-3"></iconify-icon>
                <h5 class="mb-3">Move this hub to trash?</h5>
                <p id="softDeleteMessage" class="mb-2"></p>
                <div class="alert alert-info small">
                    <iconify-icon icon="solar:info-circle-line-duotone"></iconify-icon>
                    You can restore this hub later from the trash.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="button" class="btn btn-warning" id="confirmSoftDeleteBtn">
                    <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                    Move to Trash
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let hubsTable = null;
let deleteHubId = null;

$(document).ready(function() {
    initializeDataTable();
    setupEventListeners();
});

function initializeDataTable() {
    const tableElement = $('#hubsTable');

    if (!tableElement.length) return;

    // Destroy existing DataTable if any
    if ($.fn.DataTable && $.fn.dataTable.isDataTable(tableElement)) {
        hubsTable = tableElement.DataTable();
        hubsTable.destroy();
        tableElement.find('thead, tbody, tfoot').show();
    }

    hubsTable = tableElement.DataTable({
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
            { data: 'id', name: 'id' },
            { data: 'code', name: 'code' },
            { data: 'name', name: 'name' },
            { data: 'manager_name', name: 'manager_name' },
            { data: 'phone', name: 'phone' },
            { data: 'email', name: 'email' },
            { data: 'riders_count', name: 'riders_count', orderable: false, searchable: false },
            { data: 'parcels_count', name: 'parcels_count', orderable: false, searchable: false },
            { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        pageLength: 10,
        responsive: true,
        language: {
            processing: '<div class="spinner-border text-primary spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>',
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            zeroRecords: "No hubs found"
        },
        drawCallback: function() {
            if (typeof iconify !== 'undefined') {
                iconify.scan();
            }
        }
    });

    window.hubsDataTable = hubsTable;
}

function setupEventListeners() {
    // Soft delete button click
    $(document).on('click', '.soft-delete-btn', function(e) {
        e.preventDefault();
        deleteHubId = $(this).data('id');
        let hubCode = $(this).data('code');

        $('#softDeleteMessage').html(`Are you sure you want to move hub <strong>${hubCode}</strong> to trash?`);

        const modal = new bootstrap.Modal(document.getElementById('softDeleteModal'));
        modal.show();
    });

    // Confirm delete
    $('#confirmSoftDeleteBtn').off('click').on('click', function() {
        if (!deleteHubId) return;

        $.ajax({
            url: `/admin/hubs/${deleteHubId}`,
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');
                    if (hubsTable) {
                        hubsTable.ajax.reload(null, false);
                    }
                } else {
                    showNotification(response.message, 'error');
                }

                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('softDeleteModal'));
                modal.hide();
                deleteHubId = null;
            },
            error: function(xhr) {
                let message = 'Error deleting hub';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showNotification(message, 'error');

                const modal = bootstrap.Modal.getInstance(document.getElementById('softDeleteModal'));
                modal.hide();
                deleteHubId = null;
            }
        });
    });

    // Toggle status with AJAX
    $(document).on('click', '.toggle-status-btn', function(e) {
        e.preventDefault();
        const url = $(this).attr('href');

        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');
                    if (hubsTable) {
                        hubsTable.ajax.reload(null, false);
                    }
                } else {
                    showNotification(response.message, 'error');
                }
            },
            error: function(xhr) {
                showNotification('Error toggling status', 'error');
            }
        });
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
