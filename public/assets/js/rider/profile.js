/**
 * Rider Profile Page JavaScript
 */

// Toggle between view and edit mode
function initEditModeToggle() {
    const editProfileBtn = document.getElementById('editProfileBtn');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const viewMode = document.getElementById('viewMode');
    const editMode = document.getElementById('editMode');

    if (editProfileBtn) {
        editProfileBtn.addEventListener('click', function() {
            if (viewMode) viewMode.style.display = 'none';
            if (editMode) editMode.style.display = 'block';
        });
    }

    if (cancelEditBtn) {
        cancelEditBtn.addEventListener('click', function() {
            if (viewMode) viewMode.style.display = 'block';
            if (editMode) editMode.style.display = 'none';
        });
    }
}

// Preview profile image before upload
function initImagePreview() {
    const imageInput = document.getElementById('profileImageInput');
    const profilePreview = document.getElementById('profilePreview');

    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    if (profilePreview) {
                        profilePreview.src = event.target.result;
                    }
                };
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    }
}

// Auto-hide alerts
function initAlerts() {
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
}

// Form submission loading state
function initFormSubmit() {
    const editForm = document.getElementById('editMode');
    if (editForm) {
        editForm.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
            }
        });
    }
}

// Document Ready
$(document).ready(function() {
    initEditModeToggle();
    initImagePreview();
    initAlerts();
    initFormSubmit();
});
