/**
 * Admin Profile Page JavaScript
 */

// Upload profile image
function uploadProfileImage(input) {
    if (input.files && input.files[0]) {
        const formData = new FormData();
        formData.append('profile_image', input.files[0]);

        // Preview image
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#profilePreview').attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);

        // Upload
        $.ajax({
            url: $('#profileImageForm').attr('action'),
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if(response.success) {
                    showMessage('profileMessage', 'success', response.message);
                }
            },
            error: function(xhr) {
                showMessage('profileMessage', 'danger', xhr.responseJSON?.message || 'Upload failed');
            }
        });
    }
}

// Show message function
function showMessage(elementId, type, message) {
    let alertDiv = $(`#${elementId}`);
    alertDiv.removeClass('alert-success alert-danger').addClass(`alert alert-${type} alert-message`);
    alertDiv.html(`<iconify-icon icon="solar:${type === 'success' ? 'check-circle' : 'danger-circle'}-line-duotone"></iconify-icon> ${message}`);
    alertDiv.show();

    setTimeout(() => {
        alertDiv.fadeOut();
    }, 3000);
}

// Update profile form handler
function initProfileForm() {
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        const submitBtn = $('#saveProfileBtn');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

        $.ajax({
            url: $('#profileForm').attr('action'),
            method: "POST",
            data: $(this).serialize(),
            success: function(response) {
                if(response.success) {
                    showMessage('profileMessage', 'success', response.message);
                    setTimeout(() => location.reload(), 1500);
                }
            },
            error: function(xhr) {
                let errors = xhr.responseJSON?.errors;
                let message = errors ? Object.values(errors).flat().join(', ') : 'Update failed';
                showMessage('profileMessage', 'danger', message);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<iconify-icon icon="solar:save-line-duotone"></iconify-icon> Save Changes');
            }
        });
    });
}

// Change password form handler
function initPasswordForm() {
    $('#passwordForm').on('submit', function(e) {
        e.preventDefault();
        const submitBtn = $('#changePasswordBtn');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Changing...');

        $.ajax({
            url: $('#passwordForm').attr('action'),
            method: "POST",
            data: $(this).serialize(),
            success: function(response) {
                if(response.success) {
                    showMessage('passwordMessage', 'success', response.message);
                    $('#passwordForm')[0].reset();
                    setTimeout(() => {
                        window.location.href = "{{ route('login') }}";
                    }, 2000);
                }
            },
            error: function(xhr) {
                let message = xhr.responseJSON?.message || 'Password change failed';
                showMessage('passwordMessage', 'danger', message);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<iconify-icon icon="solar:lock-password-line-duotone"></iconify-icon> Change Password');
            }
        });
    });
}

// Document Ready
$(document).ready(function() {
    initProfileForm();
    initPasswordForm();
});
