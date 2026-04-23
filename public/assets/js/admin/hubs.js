/**
 * Admin Hubs JavaScript
 */

// Show delete confirmation modal
function showDeleteModal(id, name, code) {
    document.getElementById('hubName').textContent = name;
    document.getElementById('hubCode').textContent = code;
    document.getElementById('deleteForm').action = '/admin/hubs/' + id;

    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Initialize DataTable
function initHubsDataTable() {
    if ($.fn.DataTable && $('#hubsTable').length) {
        $('#hubsTable').DataTable({
            responsive: true,
            order: [[0, 'asc']],
            pageLength: 15,
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                zeroRecords: "No hubs found"
            },
            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'excel',
                    text: '<iconify-icon icon="solar:file-text-line-duotone"></iconify-icon> Excel',
                    className: 'btn btn-success btn-sm',
                    title: 'Hubs_Report'
                },
                {
                    extend: 'pdf',
                    text: '<iconify-icon icon="solar:file-text-line-duotone"></iconify-icon> PDF',
                    className: 'btn btn-danger btn-sm',
                    title: 'Hubs_Report'
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
    initHubsDataTable();
});
