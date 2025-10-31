<?php
// Contact Form Submission Handler for Gyce Signature Service

// Configuration
$recipient = "gycenotary@gmail.com"; // UPDATE THIS with your actual email
$subject = "New Contact Form Submission - Gyce Signature Service";
$redirect_url = "contact.html?success=1";

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize and collect form data
    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone']));
    $service = htmlspecialchars(trim($_POST['service']));
    $message = htmlspecialchars(trim($_POST['message']));
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($message)) {
        header("Location: contact.html?error=missing_fields");
        exit;
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: contact.html?error=invalid_email");
        exit;
    }
    
    // Format service name
    $service_names = [
        'mobile-notary' => 'Mobile Notary',
        'e-notary' => 'E-Notary (RON)',
        'loan-signing' => 'Loan Signing',
        'general-notary' => 'General Notary',
        'business-services' => 'Business Services',
        'other' => 'Other'
    ];
    $service_display = isset($service_names[$service]) ? $service_names[$service] : 'Not specified';
    
    // Compose email body
    $email_body = "New Contact Form Submission\n\n";
    $email_body .= "Name: $name\n";
    $email_body .= "Email: $email\n";
    $email_body .= "Phone: " . ($phone ? $phone : 'Not provided') . "\n";
    $email_body .= "Service Interested In: $service_display\n\n";
    $email_body .= "Message:\n$message\n\n";
    $email_body .= "---\n";
    $email_body .= "Submitted: " . date('F j, Y, g:i a') . "\n";
    $email_body .= "IP Address: " . $_SERVER['REMOTE_ADDR'] . "\n";
    
    // Set email headers
    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    // Send email
    if (mail($recipient, $subject, $email_body, $headers)) {
        // Success - redirect to contact page with success message
        header("Location: $redirect_url");
        exit;
    } else {
        // Error - redirect with error message
        header("Location: contact.html?error=send_failed");
        exit;
    }
    
} else {
    // If accessed directly, redirect to contact page
    header("Location: contact.html");
    exit;
}
?>
