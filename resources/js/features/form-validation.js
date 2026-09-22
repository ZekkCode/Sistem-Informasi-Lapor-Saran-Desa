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

export function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        // Prevent default HTML5 validation bubble
        form.setAttribute('novalidate', '');
        
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        
        inputs.forEach(input => {
            // Validate on blur
            input.addEventListener('blur', () => validateField(input));
            
            // Clear error on input
            input.addEventListener('input', () => clearFieldError(input));
        });
        
        // Custom submit handler
        form.addEventListener('submit', (e) => {
            let isValid = true;
            let firstInvalidField = null;
            
            inputs.forEach(input => {
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

function validateField(input) {
    // Skip if disabled
    if (input.disabled) {
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

function showFieldError(input, message) {
    // Mark input as invalid
    input.setAttribute('aria-invalid', 'true');
    
    // Find or create error tooltip
    let tooltip = input.parentElement.querySelector('.validation-tooltip');
    
    if (!tooltip) {
        tooltip = document.createElement('div');
        tooltip.className = 'validation-tooltip';
        tooltip.setAttribute('role', 'alert');
        tooltip.setAttribute('aria-live', 'polite');
        
        // Insert after input or after control wrapper
        const targetElement = input.closest('.field') || input.parentElement;
        targetElement.appendChild(tooltip);
    }
    
    // Set message
    tooltip.textContent = message;
    
    // Show with animation
    requestAnimationFrame(() => {
        tooltip.classList.add('is-visible');
    });
}

function clearFieldError(input) {
    // Remove invalid state
    input.removeAttribute('aria-invalid');
    
    // Hide tooltip
    const tooltip = input.parentElement.querySelector('.validation-tooltip');
    if (tooltip) {
        tooltip.classList.remove('is-visible');
        
        // Remove after animation
        setTimeout(() => {
            if (!tooltip.classList.contains('is-visible')) {
                tooltip.remove();
            }
        }, 200);
    }
}
