/**
 * Initialize input restrictions (prevent unwanted characters)
 */
function initializeInputRestrictions() {
    // Name field: No numbers allowed
    $('#name').on('keypress', function(e) {
        const charCode = e.which ? e.which : e.keyCode;
        // Allow letters (A-Z, a-z), spaces, hyphens, dots, and control keys
        const char = String.fromCharCode(charCode);
        const allowedRegex = /^[a-zA-Z\s\-\.]$/;

        if (!allowedRegex.test(char) && charCode !== 8 && charCode !== 0 && charCode !== 13) {
            e.preventDefault();
            showValidationError('Only letters, spaces, hyphens, and dots are allowed in name field');
            return false;
        }
        return true;
    });

    // Employee ID: Only letters and numbers, max 6 characters, auto uppercase
    $('#employee_id').on('keypress', function(e) {
        const charCode = e.which ? e.which : e.keyCode;
        const char = String.fromCharCode(charCode);
        const currentValue = $(this).val();

        // Allow letters (A-Z, a-z) and numbers (0-9)
        if (!/^[A-Za-z0-9]$/.test(char) && charCode !== 8 && charCode !== 0 && charCode !== 13) {
            e.preventDefault();
            showValidationError('Employee ID can only contain letters and numbers', 'warning');
            return false;
        }

        // Limit to 6 characters
        if (currentValue.length >= 6 && charCode !== 8 && charCode !== 0 && charCode !== 13) {
            e.preventDefault();
            showValidationError('Employee ID cannot exceed 6 characters', 'warning');
            return false;
        }

        return true;
    });

    // Add input event for immediate uppercase conversion and formatting
    $('#employee_id').on('input', function() {
        let value = $(this).val();
        let uppercaseValue = value.toUpperCase();

        // Remove any non-alphanumeric characters
        uppercaseValue = uppercaseValue.replace(/[^A-Z0-9]/g, '');

        // Auto-replace first three characters with RID if they are not already
        if (uppercaseValue.length >= 3) {
            const firstThree = uppercaseValue.substring(0, 3);
            if (firstThree !== 'RID') {
                // Check if the first three characters are a case-insensitive match
                if (firstThree.toUpperCase() === 'RID') {
                    uppercaseValue = 'RID' + uppercaseValue.substring(3);
                } else if (uppercaseValue.length >= 3) {
                    // If it's not RID at all, force it to start with RID
                    uppercaseValue = 'RID' + uppercaseValue.substring(3);
                }
            }
        } else if (uppercaseValue.length === 2) {
            // If only 2 characters, check if they are 'RI'
            if (uppercaseValue !== 'RI') {
                uppercaseValue = 'RI' + uppercaseValue.substring(2);
            }
        } else if (uppercaseValue.length === 1) {
            // If only 1 character, it should be 'R'
            if (uppercaseValue !== 'R') {
                uppercaseValue = 'R';
            }
        }

        // Limit to 6 characters
        if (uppercaseValue.length > 6) {
            uppercaseValue = uppercaseValue.substring(0, 6);
        }

        // Update the field value if changed
        if ($(this).val() !== uppercaseValue) {
            $(this).val(uppercaseValue);
        }

        // Trigger validation
        validateAndFormatEmployeeId($(this));
    });

    // Phone field: Only numbers allowed, max 10 digits
    $('#phone').on('keypress', function(e) {
        const charCode = e.which ? e.which : e.keyCode;
        const currentValue = $(this).val();

        // Allow only numbers (0-9) and control keys
        if (charCode < 48 || charCode > 57) {
            if (charCode !== 8 && charCode !== 0 && charCode !== 13) {
                e.preventDefault();
                showValidationError('Only numbers are allowed in phone field');
                return false;
            }
        }

        // Prevent typing if already 10 digits
        if (currentValue.length >= 10 && charCode !== 8 && charCode !== 0 && charCode !== 13) {
            e.preventDefault();
            showValidationError('Phone number cannot exceed 10 digits');
            return false;
        }

        return true;
    });

    // Vehicle number: Allow letters and numbers only (will be auto-formatted)
    $('#vehicle_number').on('keypress', function(e) {
        const charCode = e.which ? e.which : e.keyCode;
        const char = String.fromCharCode(charCode);
        const currentValue = $(this).val();

        // Allow letters (A-Z, a-z) and numbers (0-9)
        if (!/^[A-Za-z0-9]$/.test(char) && charCode !== 8 && charCode !== 0 && charCode !== 13) {
            e.preventDefault();
            showValidationError('Vehicle number can only contain letters and numbers', 'warning');
            return false;
        }

        // Limit to 10 characters
        if (currentValue.length >= 10 && charCode !== 8 && charCode !== 0 && charCode !== 13) {
            e.preventDefault();
            showValidationError('Vehicle number cannot exceed 10 characters', 'warning');
            return false;
        }

        return true;
    });

    // License number: Allow letters and numbers only
    $('#license_number').on('keypress', function(e) {
        const charCode = e.which ? e.which : e.keyCode;
        const char = String.fromCharCode(charCode);
        const currentValue = $(this).val();

        // Allow letters (A-Z, a-z) and numbers (0-9)
        if (!/^[A-Za-z0-9]$/.test(char) && charCode !== 8 && charCode !== 0 && charCode !== 13) {
            e.preventDefault();
            showValidationError('License number can only contain letters and numbers', 'warning');
            return false;
        }

        // Limit to 15 characters
        if (currentValue.length >= 15 && charCode !== 8 && charCode !== 0 && charCode !== 13) {
            e.preventDefault();
            showValidationError('License number cannot exceed 15 characters', 'warning');
            return false;
        }

        return true;
    });

    // Weight and size fields: Only whole numbers (no decimals, no letters)
    $('#max_weight_capacity, #max_size_capacity').on('keypress', function(e) {
        const charCode = e.which ? e.which : e.keyCode;

        // Allow only numbers (0-9) and control keys
        if (charCode < 48 || charCode > 57) {
            if (charCode !== 8 && charCode !== 0 && charCode !== 13) {
                e.preventDefault();
                showValidationError('Only whole numbers are allowed (no decimals or letters)');
                return false;
            }
        }
        return true;
    });

    // Remove decimals from weight and size fields on input
    $('#max_weight_capacity, #max_size_capacity').on('input', function() {
        let value = $(this).val();
        if (value.includes('.')) {
            value = value.split('.')[0];
            $(this).val(value);
            showValidationError('Only whole numbers are allowed (no decimals)');
        }
        validateNumberField($(this));
    });

    // Prevent pasting invalid characters
    $('#name').on('paste', function(e) {
        const pastedText = e.originalEvent.clipboardData.getData('text');
        if (!/^[a-zA-Z\s\-\.]*$/.test(pastedText)) {
            e.preventDefault();
            showValidationError('Only letters, spaces, hyphens, and dots can be pasted in name field');
            return false;
        }
    });

    $('#employee_id').on('paste', function(e) {
        const pastedText = e.originalEvent.clipboardData.getData('text');
        // Convert to uppercase immediately
        let cleanedText = pastedText.toUpperCase();
        // Remove any non-alphanumeric characters
        cleanedText = cleanedText.replace(/[^A-Z0-9]/g, '');

        // Auto-format to start with RID
        if (cleanedText.length >= 3) {
            if (cleanedText.substring(0, 3) !== 'RID') {
                cleanedText = 'RID' + cleanedText.substring(3);
            }
        }

        // Check length
        if (cleanedText.length > 6) {
            e.preventDefault();
            showValidationError('Employee ID cannot exceed 6 characters');
            return false;
        }

        // Set the cleaned and formatted value
        setTimeout(() => {
            $(this).val(cleanedText);
            validateAndFormatEmployeeId($(this));
        }, 10);
    });

    $('#phone').on('paste', function(e) {
        const pastedText = e.originalEvent.clipboardData.getData('text');
        if (!/^\d*$/.test(pastedText)) {
            e.preventDefault();
            showValidationError('Only numbers can be pasted in phone field');
            return false;
        }
        // Check length after paste
        if (pastedText.length > 10) {
            e.preventDefault();
            showValidationError('Phone number cannot exceed 10 digits');
            return false;
        }
    });

    $('#vehicle_number').on('paste', function(e) {
        const pastedText = e.originalEvent.clipboardData.getData('text');
        // Allow letters and numbers only
        if (!/^[A-Za-z0-9]*$/.test(pastedText)) {
            e.preventDefault();
            showValidationError('Vehicle number can only contain letters and numbers');
            return false;
        }
        // Check length
        if (pastedText.length > 10) {
            e.preventDefault();
            showValidationError('Vehicle number cannot exceed 10 characters');
            return false;
        }
        // Will be formatted by the validateAndFormatVehicleNumber function
        setTimeout(() => {
            validateAndFormatVehicleNumber($(this));
        }, 10);
    });

    $('#license_number').on('paste', function(e) {
        const pastedText = e.originalEvent.clipboardData.getData('text');
        // Allow letters and numbers only
        if (!/^[A-Za-z0-9]*$/.test(pastedText)) {
            e.preventDefault();
            showValidationError('License number can only contain letters and numbers');
            return false;
        }
        // Check length
        if (pastedText.length > 15) {
            e.preventDefault();
            showValidationError('License number cannot exceed 15 characters');
            return false;
        }
        // Will be formatted by the validateAndFormatLicenseNumber function
        setTimeout(() => {
            validateAndFormatLicenseNumber($(this));
        }, 10);
    });
}
