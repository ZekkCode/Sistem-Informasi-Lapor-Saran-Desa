/**
 * Custom form validation tooltip dengan design Padelegan
 * Menggunakan HTML5 Constraint Validation API
 */

// Pesan validasi bahasa Indonesia
const validationMessages = {
    valueMissing: {
        default: 'Field ini wajib diisi.',
        username: 'Username harus diisi.',
        password: 'Password harus diisi.',
        email: 'Email harus diisi.',
        select: 'Pilih salah satu opsi.',
        checkbox: 'Centang untuk melanjutkan.',
        file: 'Pilih minimal 1 file.',
    },
    tooShort: {
        default: (min) => `Minimal ${min} karakter.`,
        password: (min) => `Password minimal ${min} karakter.`,
    },
    tooLong: {
        default: (max) => `Maksimal ${max} karakter.`,
    },
    typeMismatch: {
        email: 'Format email tidak valid. Contoh: nama@email.com',
        url: 'Format URL tidak valid. Contoh: https://example.com',
    },
    patternMismatch: {
        default: 'Format tidak sesuai.',
    },
};

function getCustomMessage(input) {
    const validity = input.validity;
    const inputType = input.type;
    const inputName = input.name;
    
    // Value missing (required field empty)
    if (validity.valueMissing) {
        if (inputName === 'username') return validationMessages.valueMissing.username;
        if (inputName === 'password') return validationMessages.valueMissing.password;
        if (inputType === 'email') return validationMessages.valueMissing.email;
        if (inputType === 'select-one' || inputType === 'select-multiple') return validationMessages.valueMissing.select;
        if (inputType === 'checkbox' || inputType === 'radio') return validationMessages.valueMissing.checkbox;
        if (inputType === 'file') return validationMessages.valueMissing.file;
        return validationMessages.valueMissing.default;
    }
    
    // Too short
    if (validity.tooShort) {
        const minLength = input.minLength;
        if (inputName === 'password') return validationMessages.tooShort.password(minLength);
        return validationMessages.tooShort.default(minLength);
    }
    
    // Too long
    if (validity.tooLong) {
        const maxLength = input.maxLength;
        return validationMessages.tooLong.default(maxLength);
    }
    
    // Type mismatch
    if (validity.typeMismatch) {
        if (inputType === 'email') return validationMessages.typeMismatch.email;
        if (inputType === 'url') return validationMessages.typeMismatch.url;
    }
    
    // Pattern mismatch
    if (validity.patternMismatch) {
        return input.title || validationMessages.patternMismatch.default;
    }
    
    // Default browser message
    return input.validationMessage;
}

function isElementVisible(el) {
    if (el.disabled) {
        return false;
    }
    // Check if element or any ancestor is hidden
    return el.offsetParent !== null && !el.closest('[style*="display: none"], [hidden]');
}

function getTargetContainer(input) {
    return input.closest('.field') || input.parentElement;
}

export function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        // Prevent default HTML5 validation bubble
        form.setAttribute('novalidate', '');
        
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        
        inputs.forEach(input => {
            // Validate on blur
            input.addEventListener('blur', () => {
                if (isElementVisible(input)) {
                    validateField(input);
                }
            });
            
            // Clear error on input
            input.addEventListener('input', () => {
                if (input.checkValidity()) {
                    clearFieldError(input);
                }
            });

            // Clear or update on change (vital for select, file, radio, checkbox)
            input.addEventListener('change', () => {
                if (input.checkValidity()) {
                    clearFieldError(input);
                } else if (isElementVisible(input)) {
                    validateField(input);
                }
            });
        });
        
        // Custom submit handler
        form.addEventListener('submit', (e) => {
            let isValid = true;
            let firstInvalidField = null;
            
            inputs.forEach(input => {
                if (!isElementVisible(input)) {
                    return;
                }

                if (!validateField(input)) {
                    isValid = false;
                    if (!firstInvalidField) {
                        firstInvalidField = input;
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                
                // Focus first invalid field
                if (firstInvalidField) {
                    firstInvalidField.focus();
                    
                    // Scroll to field with offset for fixed header
                    const rect = firstInvalidField.getBoundingClientRect();
                    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                    const targetY = rect.top + scrollTop - 120;
                    
                    window.scrollTo({
                        top: targetY,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
}

export function validateField(input) {
    // Skip if disabled or not visible
    if (input.disabled || !isElementVisible(input)) {
        return true;
    }
    
    // Check validity
    const isValid = input.checkValidity();
    
    if (!isValid) {
        const customMessage = getCustomMessage(input);
        showFieldError(input, customMessage);
        return false;
    } else {
        clearFieldError(input);
        return true;
    }
}

export function showFieldError(input, message) {
    // Mark input as invalid
    input.setAttribute('aria-invalid', 'true');
    
    const container = getTargetContainer(input);
    if (!container) return;

    // Remove any excess duplicate tooltips in container
    const existingTooltips = container.querySelectorAll('.validation-tooltip');
    let tooltip = existingTooltips[0] || null;

    for (let i = 1; i < existingTooltips.length; i++) {
        existingTooltips[i].remove();
    }
    
    if (!tooltip) {
        tooltip = document.createElement('div');
        tooltip.className = 'validation-tooltip';
        tooltip.setAttribute('role', 'alert');
        tooltip.setAttribute('aria-live', 'polite');
        container.appendChild(tooltip);
    }
    
    // Set message
    tooltip.textContent = message;
    
    // Show with animation
    requestAnimationFrame(() => {
        tooltip.classList.add('is-visible');
    });
}

export function clearFieldError(input) {
    // Remove invalid state
    input.removeAttribute('aria-invalid');
    
    const container = getTargetContainer(input);
    if (!container) return;

    const tooltips = container.querySelectorAll('.validation-tooltip');
    tooltips.forEach(tooltip => {
        tooltip.classList.remove('is-visible');
        setTimeout(() => {
            if (!tooltip.classList.contains('is-visible')) {
                tooltip.remove();
            }
        }, 180);
    });
}

// Attach globally for Alpine components if needed
if (typeof window !== 'undefined') {
    window.validateFormField = validateField;
    window.clearFormFieldError = clearFieldError;
}
