/**
 * Admin Parcels JavaScript
 */

// Confirm soft delete
function confirmSoftDelete(id, trackingNumber) {
    $('#softDeleteMessage').html(`Parcel <strong>${trackingNumber}</strong> will be moved to trash.`);
    $('#softDeleteForm').attr('action', `/admin/parcels/${id}`);
    $('#softDeleteModal').modal('show');
}

// Confirm restore
function confirmRestore(id, trackingNumber) {
    if (confirm(`Restore parcel ${trackingNumber}?`)) {
        document.getElementById(`restore-form-${id}`).submit();
    }
}

// Confirm force delete
function confirmForceDelete(id, trackingNumber) {
    if (confirm(`Permanently delete parcel ${trackingNumber}? This action cannot be undone.`)) {
        document.getElementById(`force-delete-form-${id}`).submit();
    }
}

// Initialize DataTable
function initParcelsDataTable() {
    if ($.fn.DataTable && $('#parcelsTable').length) {
        $('#parcelsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: $('#parcelsTable').data('ajax'),
            columns: [
                { data: 'id', name: 'id' },
                { data: 'tracking_number', name: 'tracking_number' },
                { data: 'sender_name', name: 'sender_name' },
                { data: 'receiver_name', name: 'receiver_name' },
                { data: 'weight', name: 'weight' },
                { data: 'status_badge', name: 'status_badge', orderable: false },
                { data: 'rider_name', name: 'rider_name', orderable: false },
                { data: 'created_date', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false }
            ],
            order: [[0, 'desc']],
            pageLength: 15,
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                zeroRecords: "No records found"
            },
            dom: 'Bfrtip',
            buttons: [
                { extend: 'excel', text: 'Excel', className: 'btn btn-success btn-sm' },
                { extend: 'pdf', text: 'PDF', className: 'btn btn-danger btn-sm' },
                { extend: 'print', text: 'Print', className: 'btn btn-secondary btn-sm' }
            ]
        });
    }
}

// Document Ready
$(document).ready(function() {
    initParcelsDataTable();
});/**
 * Admin Parcels JavaScript
 */

// Confirm soft delete
function confirmSoftDelete(id, trackingNumber) {
    $('#softDeleteMessage').html(`Parcel <strong>${trackingNumber}</strong> will be moved to trash.`);
    $('#softDeleteForm').attr('action', `/admin/parcels/${id}`);
    $('#softDeleteModal').modal('show');
}

// Confirm restore
function confirmRestore(id, trackingNumber) {
    if (confirm(`Restore parcel ${trackingNumber}?`)) {
        document.getElementById(`restore-form-${id}`).submit();
    }
}

// Confirm force delete
function confirmForceDelete(id, trackingNumber) {
    if (confirm(`Permanently delete parcel ${trackingNumber}? This action cannot be undone.`)) {
        document.getElementById(`force-delete-form-${id}`).submit();
    }
}

// Initialize DataTable
function initParcelsDataTable() {
    if ($.fn.DataTable && $('#parcelsTable').length) {
        $('#parcelsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: $('#parcelsTable').data('ajax'),
            columns: [
                { data: 'id', name: 'id' },
                { data: 'tracking_number', name: 'tracking_number' },
                { data: 'sender_name', name: 'sender_name' },
                { data: 'receiver_name', name: 'receiver_name' },
                { data: 'weight', name: 'weight' },
                { data: 'status_badge', name: 'status_badge', orderable: false },
                { data: 'rider_name', name: 'rider_name', orderable: false },
                { data: 'created_date', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false }
            ],
            order: [[0, 'desc']],
            pageLength: 15,
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                zeroRecords: "No records found"
            },
            dom: 'Bfrtip',
            buttons: [
                { extend: 'excel', text: 'Excel', className: 'btn btn-success btn-sm' },
                { extend: 'pdf', text: 'PDF', className: 'btn btn-danger btn-sm' },
                { extend: 'print', text: 'Print', className: 'btn btn-secondary btn-sm' }
            ]
        });
    }
}

// Document Ready
$(document).ready(function() {
    initParcelsDataTable();
});
