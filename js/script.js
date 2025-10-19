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

// Form submission with PHP backend
document.getElementById('contactForm').addEventListener('submit', async function(e) {
    e.preventDefault();

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
