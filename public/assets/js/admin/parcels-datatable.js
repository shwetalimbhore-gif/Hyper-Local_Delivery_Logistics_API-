/**
 * Admin Parcels DataTable JavaScript
 */

// Confirm delete function
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this parcel?')) {
        window.location.href = '/admin/parcels/' + id;
    }
}

// Confirm soft delete function
function confirmSoftDelete(id, trackingNumber) {
    if (confirm(`Move parcel ${trackingNumber} to trash?`)) {
        document.getElementById(`delete-form-${id}`).submit();
    }
}

// Initialize Parcels DataTable
function initParcelsDataTable() {
    if ($.fn.DataTable && $('#parcelsTable').length) {
        $('#parcelsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: $('#parcelsTable').data('ajax') || "/admin/parcels/datatable",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'tracking_number', name: 'tracking_number' },
                { data: 'sender_name', name: 'sender_name' },
                { data: 'receiver_name', name: 'receiver_name' },
                { data: 'weight', name: 'weight' },
                { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false },
                { data: 'rider_name', name: 'rider_name', orderable: false },
                { data: 'created_date', name: 'created_at' },
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
                    title: 'Parcels_Report'
                },
                {
                    extend: 'pdf',
                    text: '<iconify-icon icon="solar:file-text-line-duotone"></iconify-icon> PDF',
                    className: 'btn btn-danger btn-sm',
                    title: 'Parcels_Report'
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

// Document Ready
$(document).ready(function() {
    initParcelsDataTable();
});
