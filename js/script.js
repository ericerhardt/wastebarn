// Smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

const phoneInput = document.getElementById('phone');
const phoneValidationMessage = document.getElementById('phoneValidationMessage');

const formatPhoneDigits = (digits) => {
    if (!digits) {
        return '';
    }

    const area = digits.slice(0, 3);
    const prefix = digits.slice(3, 6);
    const line = digits.slice(6, 10);

    let formatted = `(${area}`;

    if (digits.length >= 3) {
        formatted = `(${area})`;
    }

    if (prefix) {
        formatted += ` ${prefix}`;
    }

    if (line) {
        formatted += `-${line}`;
    }

    return formatted;
};

const applyPhoneFormatting = () => {
    if (!phoneInput) {
        return { digits: '', formatted: '' };
    }

    const digits = phoneInput.value.replace(/\D/g, '').slice(0, 10);
    const formatted = formatPhoneDigits(digits);

    if (phoneInput.value !== formatted) {
        const isActive = document.activeElement === phoneInput;
        let cursorPositionFromEnd = 0;

        if (isActive && typeof phoneInput.selectionStart === 'number') {
            cursorPositionFromEnd = phoneInput.value.length - phoneInput.selectionStart;
        }

        phoneInput.value = formatted;

        if (isActive && typeof phoneInput.selectionStart === 'number') {
            let newCursorPosition = formatted.length - cursorPositionFromEnd;
            newCursorPosition = Math.max(0, Math.min(formatted.length, newCursorPosition));
            phoneInput.setSelectionRange(newCursorPosition, newCursorPosition);
        }
    }

    return { digits, formatted };
};

const validatePhoneField = (showRequiredMessage = false, providedDigits = undefined) => {
    if (!phoneInput || !phoneValidationMessage) {
        return true;
    }

    const { digits } = typeof providedDigits === 'string'
        ? { digits: providedDigits }
        : applyPhoneFormatting();

    let message = '';

    if (!digits.length) {
        message = 'Phone number is required.';
    } else if (digits.length !== 10) {
        message = 'Enter a 10-digit phone number.';
    }

    const showMessage = !!message && showRequiredMessage;
    phoneValidationMessage.textContent = showMessage ? message : '';
    phoneInput.classList.toggle('invalid', showMessage);

    return !message;
};

if (phoneInput) {
    applyPhoneFormatting();
    phoneInput.addEventListener('input', () => {
        const { digits } = applyPhoneFormatting();
        validatePhoneField(false, digits);
    });
    phoneInput.addEventListener('blur', () => validatePhoneField(true));
}

// Form submission with PHP backend
const contactForm = document.getElementById('contactForm');
if (contactForm) {
    contactForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        if (!validatePhoneField(true)) {
            if (phoneInput) {
                phoneInput.focus();
            }
            return;
        }

        const submitButton = this.querySelector('.submit-button');
        const formMessage = document.getElementById('formMessage');

        // Disable submit button
        submitButton.disabled = true;
        submitButton.textContent = 'Sending...';

        // Hide any previous messages
        formMessage.style.display = 'none';

        // Get form data
        const formData = new FormData(this);

        try {
            const response = await fetch('server/sendform.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                formMessage.textContent = result.message || 'Thank you! Your quote request has been sent. We\'ll contact you within 24 hours.';
                formMessage.className = 'form-message success';
                formMessage.style.display = 'block';
                this.reset();
                validatePhoneField(); // clear phone validation styles after reset
            } else {
                throw new Error(result.message || 'Failed to send email');
            }
        } catch (error) {
            formMessage.textContent = error.message || 'Sorry, there was an error sending your request. Please try again or call us directly.';
            formMessage.className = 'form-message error';
            formMessage.style.display = 'block';
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Send Quote Request';
        }
    });
}
