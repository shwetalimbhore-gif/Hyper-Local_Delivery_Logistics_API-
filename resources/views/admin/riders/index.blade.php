@extends('layouts.admin')

@section('title', 'Manage Riders')

@push('styles')
<style>
    .riders-card {
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
<div class="card riders-card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="card-title mb-0">All Riders</h5>
            <div>
                <a href="{{ route('admin.riders.trash') }}" class="btn btn-secondary me-2">
                    <iconify-icon icon="solar:trash-bin-trash-line-duotone"></iconify-icon>
                    Trash
                </a>
                <a href="{{ route('admin.riders.create') }}" class="btn btn-primary">
                    <iconify-icon icon="solar:add-circle-line-duotone"></iconify-icon>
                    Create New Rider
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover" id="ridersTable" width="100%" data-ajax="{{ route('admin.riders.data') }}">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Hub</th>
                        <th>Vehicle</th>
                        <th>Status</th>
                        <th>Total Del.</th>
                        <th>Rating</th>
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
                <h5 class="mb-3">Move this rider to trash?</h5>
                <p id="softDeleteMessage" class="mb-2"></p>
                <div class="alert alert-info small">
                    <iconify-icon icon="solar:info-circle-line-duotone"></iconify-icon>
                    You can restore this rider later from the trash.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
let ridersTable = null;
let deleteRiderId = null;

$(document).ready(function() {
    initializeDataTable();
    setupEventListeners();
});

function initializeDataTable() {
    const tableElement = $('#ridersTable');

    if (!tableElement.length) return;

    // Destroy existing DataTable if any
    if ($.fn.DataTable && $.fn.dataTable.isDataTable(tableElement)) {
        ridersTable = tableElement.DataTable();
        ridersTable.destroy();
        tableElement.find('thead, tbody, tfoot').show();
    }

    ridersTable = tableElement.DataTable({
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
            { data: 'employee_id', name: 'employee_id' },
            { data: 'full_name', name: 'user.name' },
            { data: 'email', name: 'user.email' },
            { data: 'phone', name: 'user.phone' },
            { data: 'hub_name', name: 'hub.name' },
            { data: 'vehicle_badge', name: 'vehicle_type', orderable: false, searchable: false },
            { data: 'status_badge', name: 'status', orderable: false, searchable: false },
            { data: 'total_deliveries', name: 'total_deliveries' },
            { data: 'rating_display', name: 'rating', orderable: false, searchable: false },
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
            zeroRecords: "No riders found"
        },
        drawCallback: function() {
            if (typeof iconify !== 'undefined') {
                iconify.scan();
            }
        }
    });

    window.ridersDataTable = ridersTable;
}

function setupEventListeners() {
    // Soft delete button click
    $(document).on('click', '.soft-delete-btn', function(e) {
        e.preventDefault();
        deleteRiderId = $(this).data('id');
        let riderName = $(this).data('name');
        let employeeId = $(this).data('employee-id');

        $('#softDeleteMessage').html(`Are you sure you want to move rider <strong>${riderName}</strong> (${employeeId}) to trash?`);

        const modal = new bootstrap.Modal(document.getElementById('softDeleteModal'));
        modal.show();
    });

    // Confirm delete
    $('#confirmSoftDeleteBtn').off('click').on('click', function() {
        if (!deleteRiderId) return;

        // Show loading state
        const btn = $(this);
        const originalText = btn.html();
        btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Moving...');
        btn.prop('disabled', true);

        $.ajax({
            url: `/admin/riders/${deleteRiderId}`,
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showNotification(response.message, 'success');
                    if (ridersTable) {
                        ridersTable.ajax.reload(null, false);
                    }
                } else {
                    showNotification(response.message, 'error');
                }

                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('softDeleteModal'));
                modal.hide();
                deleteRiderId = null;
            },
            error: function(xhr) {
                let message = 'Error deleting rider';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showNotification(message, 'error');

                const modal = bootstrap.Modal.getInstance(document.getElementById('softDeleteModal'));
                modal.hide();
                deleteRiderId = null;
            },
            complete: function() {
                btn.html(originalText);
                btn.prop('disabled', false);
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
