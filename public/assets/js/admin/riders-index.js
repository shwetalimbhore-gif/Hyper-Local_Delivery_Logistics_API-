/**
 * Admin Riders Index Page JavaScript
 */

// Confirm soft delete function
function confirmSoftDelete(id, name, employeeId) {
    $('#softDeleteMessage').html(`Rider <strong>${name}</strong> (${employeeId}) will be moved to trash.`);
    $('#softDeleteForm').attr('action', `/admin/riders/${id}`);
    const modal = new bootstrap.Modal(document.getElementById('softDeleteModal'));
    modal.show();
}

// Initialize Riders DataTable
function initRidersDataTable() {
    if ($.fn.DataTable && $('#ridersTable').length) {
        $('#ridersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: $('#ridersTable').data('ajax') || "{{ route('admin.riders.data') }}",
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
            pageLength: 15,
            lengthMenu: [[10, 15, 25, 50, -1], [10, 15, 25, 50, "All"]],
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                zeroRecords: "No records found",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            },
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excel',
                    text: '<iconify-icon icon="solar:file-text-line-duotone"></iconify-icon> Excel',
                    className: 'btn btn-success btn-sm',
                    title: 'Riders_Report'
                },
                {
                    extend: 'pdf',
                    text: '<iconify-icon icon="solar:file-text-line-duotone"></iconify-icon> PDF',
                    className: 'btn btn-danger btn-sm',
                    title: 'Riders_Report'
                },
                {
                    extend: 'print',
                    text: '<iconify-icon icon="solar:printer-line-duotone"></iconify-icon> Print',
                    className: 'btn btn-secondary btn-sm'
                }
            ]
        });
    }
}

// Auto-hide alerts after 5 seconds
function initAlerts() {
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
}

// Document Ready
$(document).ready(function() {
    initRidersDataTable();
    initAlerts();
});
