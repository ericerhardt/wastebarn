# WasteBarn Website

## Installation Instructions

1. Extract the zip file to your desired location
2. Upload all files to your web hosting server via FTP or cPanel File Manager
3. Make sure to maintain the folder structure:
   - index.html (root)
   - css/styles.css
   - js/script.js
   - server/sendform.php
   - images/ (all image files)
   - .env (configuration file)
   - .htaccess (security file)

## Configuration

### Step 1: Set Up Environment Variables
1. Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```

2. Edit the `.env` file and add your configuration:
   ```
   RESEND_API_KEY=your_actual_resend_api_key
   RECIPIENT_EMAIL=your-email@wastebarn.com
   FROM_EMAIL=WasteBarn Contact Form <noreply@wastebarn.com>
   ```

### Step 2: Get Resend API Key
1. Sign up for a Resend account at https://resend.com
2. Verify your domain or use their test domain
3. Get your API key from https://resend.com/api-keys
4. Add the API key to your `.env` file

### Step 3: Server Requirements
- PHP 7.4 or higher
- cURL extension enabled
- Apache or Nginx web server

### Step 4: Security
- The `.htaccess` file protects your `.env` file from being accessed via web browser
- Never commit your `.env` file to version control
- Keep your Resend API key confidential

## File Structure
```
wastebarn-site/
├── index.html              # Main HTML file
├── .env.example            # Environment variables template
├── .env                    # Your actual configuration (create this)
├── .htaccess               # Security configuration
├── css/
│   └── styles.css          # All CSS styles
├── js/
│   └── script.js           # JavaScript functionality
├── server/
│   └── sendform.php        # PHP form handler with Resend integration
├── images/
│   ├── logo.png            # WasteBarn logo
│   ├── hero-bg.jpg         # Hero section background
│   └── project-*.jpg       # Project images
└── README.md               # This file
```

## How It Works

1. User fills out the contact form on the website
2. JavaScript (script.js) captures the form submission
3. Form data is sent to server/sendform.php via AJAX
4. PHP script reads the Resend API key from .env file
5. PHP sends the email via Resend API
6. User receives success/error message

## Testing the Form

1. Make sure your .env file is configured correctly
2. Open the website in a browser
3. Fill out the contact form
4. Submit and check for success message
5. Verify email arrives at your recipient address

## Troubleshooting

### Form not submitting
- Check browser console for JavaScript errors
- Verify server/sendform.php is accessible
- Check PHP error logs

### Email not sending
- Verify Resend API key is correct in .env
- Check that cURL is enabled in PHP
- Verify your domain is verified in Resend (or use test mode)
- Check PHP error logs for detailed error messages

### .env file not loading
- Ensure .env file is in the root directory (same level as index.html)
- Check file permissions (should be readable by PHP)
- Verify the file is named exactly `.env` (not `.env.txt`)

## Domain Setup
- Point your domain (wastebarn.com) to your hosting server
- Update DNS records as provided by your hosting provider
- Verify domain in Resend for production email sending

## Features
- Fully responsive design
- Smooth scrolling navigation
- PHP-based contact form with Resend API integration
- Environment-based configuration
- SEO optimized
- HOA compliant enclosure showcase
- Secure .env file protection

## Browser Support
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Support
For questions or issues, contact support@wastebarn.com

## License
© 2025 WasteBarn.com - All rights reserved
