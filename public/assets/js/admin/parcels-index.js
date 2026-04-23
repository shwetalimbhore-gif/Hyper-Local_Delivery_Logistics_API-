/**
 * Admin Parcels Index Page JavaScript
 */

// Confirm soft delete function
function confirmSoftDelete(id, trackingNumber) {
    $('#softDeleteMessage').html(`Parcel <strong>${trackingNumber}</strong> will be moved to trash.`);
    $('#softDeleteForm').attr('action', `/admin/parcels/${id}`);
    const modal = new bootstrap.Modal(document.getElementById('softDeleteModal'));
    modal.show();
}

// Initialize Parcels DataTable
function initParcelsDataTable() {
    if ($.fn.DataTable && $('#parcelsTable').length) {
        $('#parcelsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: $('#parcelsTable').data('ajax') || "{{ route('admin.parcels.data') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'tracking_number', name: 'tracking_number' },
                { data: 'sender_name', name: 'sender_name' },
                { data: 'receiver_name', name: 'receiver_name' },
                { data: 'weight', name: 'weight' },
                { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false },
                { data: 'rider_name', name: 'rider_name', orderable: false },
                { data: 'created_at', name: 'created_at' },
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
        });/**
 * Admin Parcels Index Page JavaScript
 */

// Confirm soft delete function
function confirmSoftDelete(id, trackingNumber) {
    $('#softDeleteMessage').html(`Parcel <strong>${trackingNumber}</strong> will be moved to trash.`);
    $('#softDeleteForm').attr('action', `/admin/parcels/${id}`);
    const modal = new bootstrap.Modal(document.getElementById('softDeleteModal'));
    modal.show();
}

// Initialize Parcels DataTable
function initParcelsDataTable() {
    if ($.fn.DataTable && $('#parcelsTable').length) {
        $('#parcelsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: $('#parcelsTable').data('ajax') || "{{ route('admin.parcels.data') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'tracking_number', name: 'tracking_number' },
                { data: 'sender_name', name: 'sender_name' },
                { data: 'receiver_name', name: 'receiver_name' },
                { data: 'weight', name: 'weight' },
                { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false },
                { data: 'rider_name', name: 'rider_name', orderable: false },
                { data: 'created_at', name: 'created_at' },
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
    }
}

// Document Ready
$(document).ready(function() {
    initParcelsDataTable();
});
