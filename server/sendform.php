<?php
/**
 * WasteBarn Contact Form Handler
 * Sends form submissions via Resend API
 */

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set JSON response header
header('Content-Type: application/json');

// CORS headers (adjust as needed for your domain)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Function to load environment variables from .env file
function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remove quotes if present
            $value = trim($value, '"'');

            // Set environment variable
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
    return true;
}

// Load .env file from parent directory
$envPath = dirname(__DIR__) . '/.env';
if (!loadEnv($envPath)) {
    echo json_encode([
        'success' => false,
        'message' => 'Configuration file not found. Please contact the administrator.'
    ]);
    exit;
}

// Get Resend API key from environment
$resendApiKey = getenv('RESEND_API_KEY');
$recipientEmail = getenv('RECIPIENT_EMAIL') ?: 'your-email@wastebarn.com';
$fromEmail = getenv('FROM_EMAIL') ?: 'WasteBarn Contact Form <noreply@wastebarn.com>';

if (!$resendApiKey) {
    echo json_encode([
        'success' => false,
        'message' => 'Email service not configured. Please contact the administrator.'
    ]);
    exit;
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
    exit;
}

// Sanitize and validate form data
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Get form data
$firstName = sanitizeInput($_POST['firstName'] ?? '');
$lastName = sanitizeInput($_POST['lastName'] ?? '');
$email = sanitizeInput($_POST['email'] ?? '');
$phone = sanitizeInput($_POST['phone'] ?? '');
$address = sanitizeInput($_POST['address'] ?? '');
$material = sanitizeInput($_POST['material'] ?? '');
$roof = sanitizeInput($_POST['roof'] ?? '');
$purpose = sanitizeInput($_POST['purpose'] ?? '');
$message = sanitizeInput($_POST['message'] ?? '');

// Validate required fields
if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($material) || empty($roof) || empty($purpose)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill in all required fields.'
    ]);
    exit;
}

// Validate email format
if (!validateEmail($email)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please provide a valid email address.'
    ]);
    exit;
}

// Build email HTML
$emailHtml = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        h2 { color: #2c3e50; border-bottom: 3px solid #e67e22; padding-bottom: 10px; }
        .field { margin-bottom: 15px; }
        .label { font-weight: bold; color: #2c3e50; }
        .value { color: #555; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class='container'>
        <h2>New Quote Request from WasteBarn.com</h2>

        <div class='field'>
            <span class='label'>Name:</span>
            <span class='value'>$firstName $lastName</span>
        </div>

        <div class='field'>
            <span class='label'>Email:</span>
            <span class='value'>$email</span>
        </div>

        <div class='field'>
            <span class='label'>Phone:</span>
            <span class='value'>$phone</span>
        </div>

        <div class='field'>
            <span class='label'>Property Address:</span>
            <span class='value'>" . ($address ?: 'Not provided') . "</span>
        </div>

        <div class='field'>
            <span class='label'>Preferred Material:</span>
            <span class='value'>$material</span>
        </div>

        <div class='field'>
            <span class='label'>Preferred Roof:</span>
            <span class='value'>$roof</span>
        </div>

        <div class='field'>
            <span class='label'>Purpose:</span>
            <span class='value'>$purpose</span>
        </div>

        <div class='field'>
            <span class='label'>Project Details / Questions:</span>
            <div class='value'>" . ($message ?: 'No additional details provided') . "</div>
        </div>

        <div class='footer'>
            This email was sent from the WasteBarn.com contact form.
        </div>
    </div>
</body>
</html>
";

// Prepare Resend API request
$resendData = [
    'from' => $fromEmail,
    'to' => [$recipientEmail],
    'subject' => "New Quote Request from $firstName $lastName",
    'html' => $emailHtml,
    'reply_to' => $email
];

// Send request to Resend API
$ch = curl_init('https://api.resend.com/emails');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $resendApiKey,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($resendData));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Check for cURL errors
if ($curlError) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to connect to email service. Please try again later.'
    ]);
    exit;
}

// Check response
if ($httpCode >= 200 && $httpCode < 300) {
    echo json_encode([
        'success' => true,
        'message' => 'Thank you! Your quote request has been sent. We\'ll contact you within 24 hours.'
    ]);
} else {
    $errorData = json_decode($response, true);
    $errorMessage = $errorData['message'] ?? 'Failed to send email. Please try again later.';

    echo json_encode([
        'success' => false,
        'message' => 'There was an error sending your request. Please try again or contact us directly.'
    ]);
}
?>
