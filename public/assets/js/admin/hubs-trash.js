/**
 * Admin Hubs Trash Page JavaScript
 */

// Restore hub function
function restoreHub(id, code) {
    if (confirm(`Restore hub ${code}? This will move it back to active hubs.`)) {
        document.getElementById(`restore-form-${id}`).submit();
    }
}

// Force delete hub function
function forceDeleteHub(id, code) {
    if (confirm(`Permanently delete hub ${code}? This action cannot be undone.`)) {
        document.getElementById(`force-delete-form-${id}`).submit();
    }
}

// Initialize any DataTable if needed
function initTrashTable() {
    if ($.fn.DataTable && $('#trashHubsTable').length) {
        $('#trashHubsTable').DataTable({
            responsive: true,
            order: [[0, 'desc']],
            pageLength: 15,
            language: {
                search: "Search:",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                zeroRecords: "No deleted hubs found"
            }
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
    initTrashTable();
    initAlerts();
});
