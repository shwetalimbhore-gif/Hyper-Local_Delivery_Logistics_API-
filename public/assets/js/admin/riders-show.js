/**
 * Admin Riders Show Page JavaScript
 */

// Show delete confirmation modal
function showDeleteModal(riderId, riderName) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    document.getElementById('deleteRiderName').innerHTML = `<strong>${riderName}</strong>`;
    document.getElementById('deleteForm').action = `/admin/riders/${riderId}`;
    modal.show();
}

// Document Ready
$(document).ready(function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
