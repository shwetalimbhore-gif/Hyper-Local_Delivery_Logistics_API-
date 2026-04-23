/**
 * Admin Parcels Trash Page JavaScript
 */

// Show restore confirmation modal
function showRestoreModal(id, trackingNumber) {
    $('#restoreMessage').html(`Parcel <strong>${trackingNumber}</strong> will be restored.`);
    $('#restoreForm').attr('action', `/admin/parcels/${id}/restore`);
    const modal = new bootstrap.Modal(document.getElementById('restoreModal'));
    modal.show();
}

// Show force delete confirmation modal
function showForceDeleteModal(id, trackingNumber) {
    $('#forceDeleteMessage').html(`Parcel <strong>${trackingNumber}</strong> will be permanently deleted.`);
    $('#forceDeleteForm').attr('action', `/admin/parcels/${id}/force-delete`);
    const modal = new bootstrap.Modal(document.getElementById('forceDeleteModal'));
    modal.show();
}

// Auto-hide alerts after 5 seconds
function initAlerts() {
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
}

// Initialize DataTable (optional)
function initTrashDataTable() {
    if ($.fn.DataTable && $('#trashTable').length) {
        $('#trashTable').DataTable({
            responsive: true,
            order: [[0, 'desc']],
            pageLength: 15,
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                zeroRecords: "No deleted parcels found"
            }
        });
    }
}

// Document Ready
$(document).ready(function() {
    initAlerts();
    // initTrashDataTable(); // Uncomment if you want DataTable instead of Laravel pagination
});
