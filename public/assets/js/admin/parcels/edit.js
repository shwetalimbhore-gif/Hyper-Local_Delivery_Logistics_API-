/**
 * Admin Parcels Edit Page JavaScript
 */

function autoAssignRider() {
    let weight = parseFloat(document.getElementById('weight').value) || 0;
    let size = parseFloat(document.getElementById('size').value) || 0;
    let hubId = document.getElementById('sourceHubId').value;

    if (weight === 0) {
        showValidationError('weight', 'Please enter parcel weight first');
        return;
    }

    if (size === 0) {
        showValidationError('size', 'Please enter parcel size first');
        return;
    }

    if (!hubId) {
        showValidationError('sourceHubId', 'Please select a source hub first');
        return;
    }

    let autoAssignBtn = document.getElementById('autoAssignBtn');
    let originalText = autoAssignBtn.innerHTML;
    setButtonLoading(autoAssignBtn, true, 'Finding best rider...');

    $.ajax({
        url: $('#autoAssignBtn').data('url') || '/admin/parcels/find-rider',
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            weight: weight,
            size: size,
            hub_id: hubId,
            parcel_id: $('#autoAssignBtn').data('parcel-id'),
            assign: 1
        },
        success: function(response) {
            if (response.success && response.rider) {
                handleAutoAssignSuccess(response);
            } else {
                handleNoRiderFound(weight, size, hubId);
            }
        },
        error: function(xhr) {
            let errorMsg = xhr.responseJSON?.message || xhr.responseJSON?.error || 'Unknown error occurred';
            showAlertMessage(errorMsg, 'danger');
        },
        complete: function() {
            setButtonLoading(autoAssignBtn, false, originalText);
        }
    });
}

function showValidationError(fieldId, message) {
    let field = document.getElementById(fieldId);
    field.focus();
    showAlertMessage(message, 'danger');
}

function setButtonLoading(button, isLoading, loadingText) {
    if (isLoading) {
        button.disabled = true;
        button.classList.add('btn-loading');
        button.innerHTML = `<span class="spinner-border spinner-border-sm"></span> ${loadingText}`;
    } else {
        button.disabled = false;
        button.classList.remove('btn-loading');
        button.innerHTML = loadingText;
    }
}

function handleAutoAssignSuccess(response) {
    $('#riderSelect').val(response.rider.id);

    let assignedStatusId = $('#statusSelect option[data-status-slug="assigned"]').val();
    if (assignedStatusId) {
        $('#statusSelect').val(assignedStatusId);
    }

    showAlertMessage('Rider auto-assigned successfully. Parcel status is now Assigned and rider is Busy.', 'success');
    showRiderDetailsModal(response.rider);
}

function showRiderDetailsModal(rider) {
    let modalHtml = `
        <div class="modal fade" id="riderDetailsModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Rider Auto-Assigned</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-borderless">
                            <tr><th width="40%">Name:</th><td>${rider.name}</td></tr>
                            <tr><th>Employee ID:</th><td>${rider.employee_id}</td></tr>
                            <tr><th>Max Weight:</th><td>${rider.max_weight_capacity} kg</td></tr>
                            <tr><th>Max Size:</th><td>${rider.max_size_capacity} cm3</td></tr>
                            <tr><th>Rating:</th><td>${rider.rating ?? 'N/A'}</td></tr>
                            <tr><th>Status:</th><td><span class="badge bg-warning text-dark">${rider.status}</span></td></tr>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    `;

    $('#riderDetailsModal').remove();
    $('body').append(modalHtml);
    $('#riderDetailsModal').modal('show');
}

function handleNoRiderFound(weight, size, hubId) {
    let message = `No available rider found!\n\nParcel Requirements:\n- Weight: ${weight} kg\n- Size: ${size} cm3\n- Hub ID: ${hubId}\n\nPlease check rider availability, capacity, and hub assignment.`;
    alert(message);
}

function showAlertMessage(message, type) {
    let alertDiv = $('#autoAssignMessage');
    alertDiv.removeClass('alert-success alert-danger alert-info')
        .addClass(`alert alert-${type === 'success' ? 'success' : 'danger'} auto-assign-message`)
        .html(`<iconify-icon icon="solar:${type === 'success' ? 'check-circle' : 'danger-circle'}-line-duotone"></iconify-icon> ${message}`);
    alertDiv.show();

    setTimeout(function() {
        alertDiv.fadeOut();
    }, 3000);
}

function initFormSubmit() {
    const form = document.getElementById('parcelForm');
    const submitBtn = document.getElementById('submitBtn');

    if (form && submitBtn) {
        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Updating...';
        });
    }
}

function filterRidersBySelectedHub() {
    const selectedHubId = $('#sourceHubId').val();
    const riderSelect = $('#riderSelect');

    riderSelect.find('option').each(function() {
        const option = $(this);
        const riderHubId = option.data('hub');

        if (!option.val()) {
            option.show();
            return;
        }

        option.toggle(String(riderHubId) === String(selectedHubId));
    });

    const selectedOption = riderSelect.find('option:selected');
    if (selectedOption.val() && String(selectedOption.data('hub')) !== String(selectedHubId)) {
        riderSelect.val('');
    }
}

$(document).ready(function() {
    initFormSubmit();
    filterRidersBySelectedHub();
    $('#sourceHubId').on('change', filterRidersBySelectedHub);
});
