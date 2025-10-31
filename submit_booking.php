<?php
// Appointment Booking Form Handler for Gyce Signature Service

// Configuration
$recipient = "gycenotary@gmail.com"; // UPDATE THIS with your actual email
$subject = "New Appointment Request - Gyce Signature Service";
$redirect_url = "book.html?success=1";
$upload_dir = "uploads/"; // Directory for uploaded files

// Create uploads directory if it doesn't exist
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize and collect form data
    $full_name = htmlspecialchars(trim($_POST['full_name']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $phone = htmlspecialchars(trim($_POST['phone']));
    $service_type = htmlspecialchars(trim($_POST['service_type']));
    $document_type = htmlspecialchars(trim($_POST['document_type']));
    $num_documents = htmlspecialchars(trim($_POST['num_documents']));
    $num_signers = htmlspecialchars(trim($_POST['num_signers']));
    $preferred_date = htmlspecialchars(trim($_POST['preferred_date']));
    $preferred_time = htmlspecialchars(trim($_POST['preferred_time']));
    $alternate_date = htmlspecialchars(trim($_POST['alternate_date']));
    $location_type = htmlspecialchars(trim($_POST['location_type']));
    $address = htmlspecialchars(trim($_POST['address']));
    $city = htmlspecialchars(trim($_POST['city']));
    $state = htmlspecialchars(trim($_POST['state']));
    $zip = htmlspecialchars(trim($_POST['zip']));
    $special_requests = htmlspecialchars(trim($_POST['special_requests']));
    
    // Validate required fields
    if (empty($full_name) || empty($email) || empty($phone) || empty($service_type) || 
        empty($preferred_date) || empty($preferred_time) || empty($location_type) || 
        empty($address) || empty($city) || empty($state) || empty($zip)) {
        header("Location: book.html?error=missing_fields");
        exit;
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: book.html?error=invalid_email");
        exit;
    }
    
    // Handle file upload
    $uploaded_file = "";
    if (isset($_FILES['document_upload']) && $_FILES['document_upload']['error'] == 0) {
        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'];
        $file_type = $_FILES['document_upload']['type'];
        $file_size = $_FILES['document_upload']['size'];
        $max_size = 10 * 1024 * 1024; // 10MB
        
        if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
            $file_extension = pathinfo($_FILES['document_upload']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['document_upload']['tmp_name'], $upload_path)) {
                $uploaded_file = $upload_path;
            }
        }
    }
    
    // Format service type
    $service_names = [
        'mobile-notary' => 'Mobile Notary (Come to me)',
        'e-notary' => 'E-Notary / RON (Remote Online)',
        'loan-signing' => 'Loan Signing Agent',
        'general-notary' => 'General Notary',
        'witness-service' => 'Witness Service',
        'other' => 'Other'
    ];
    $service_display = isset($service_names[$service_type]) ? $service_names[$service_type] : $service_type;
    
    // Format time preference
    $time_names = [
        'morning' => 'Morning (9 AM - 12 PM)',
        'afternoon' => 'Afternoon (12 PM - 5 PM)',
        'evening' => 'Evening (5 PM - 7 PM)',
        'flexible' => 'Flexible / Any Time'
    ];
    $time_display = isset($time_names[$preferred_time]) ? $time_names[$preferred_time] : $preferred_time;
    
    // Format location type
    $location_names = [
        'residence' => 'Residence',
        'office' => 'Office',
        'hospital' => 'Hospital/Medical Facility',
        'nursing-home' => 'Nursing Home',
        'remote' => 'Remote (E-Notary)',
        'other' => 'Other'
    ];
    $location_display = isset($location_names[$location_type]) ? $location_names[$location_type] : $location_type;
    
    // Compose email body
    $email_body = "NEW APPOINTMENT REQUEST\n";
    $email_body .= "========================\n\n";
    
    $email_body .= "PERSONAL INFORMATION\n";
    $email_body .= "-------------------\n";
    $email_body .= "Name: $full_name\n";
    $email_body .= "Email: $email\n";
    $email_body .= "Phone: $phone\n\n";
    
    $email_body .= "SERVICE DETAILS\n";
    $email_body .= "--------------\n";
    $email_body .= "Service Type: $service_display\n";
    $email_body .= "Document Type: " . ($document_type ? $document_type : 'Not specified') . "\n";
    $email_body .= "Number of Documents: " . ($num_documents ? $num_documents : 'Not specified') . "\n";
    $email_body .= "Number of Signers: " . ($num_signers ? $num_signers : 'Not specified') . "\n\n";
    
    $email_body .= "APPOINTMENT PREFERENCES\n";
    $email_body .= "----------------------\n";
    $email_body .= "Preferred Date: $preferred_date\n";
    $email_body .= "Preferred Time: $time_display\n";
    if ($alternate_date) {
        $email_body .= "Alternate Date: $alternate_date\n";
    }
    $email_body .= "\n";
    
    $email_body .= "LOCATION INFORMATION\n";
    $email_body .= "-------------------\n";
    $email_body .= "Location Type: $location_display\n";
    $email_body .= "Address: $address\n";
    $email_body .= "City: $city\n";
    $email_body .= "State: $state\n";
    $email_body .= "ZIP: $zip\n\n";
    
    if ($special_requests) {
        $email_body .= "SPECIAL REQUESTS / NOTES\n";
        $email_body .= "----------------------\n";
        $email_body .= "$special_requests\n\n";
    }
    
    if ($uploaded_file) {
        $email_body .= "DOCUMENT UPLOAD\n";
        $email_body .= "--------------\n";
        $email_body .= "File uploaded: $uploaded_file\n\n";
    }
    
    $email_body .= "---\n";
    $email_body .= "Submitted: " . date('F j, Y, g:i a') . "\n";
    $email_body .= "IP Address: " . $_SERVER['REMOTE_ADDR'] . "\n";
    
    // Set email headers
    $headers = "From: $email\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    // Send email
    if (mail($recipient, $subject, $email_body, $headers)) {
        // Success - redirect to booking page with success message
        header("Location: $redirect_url");
        exit;
    } else {
        // Error - redirect with error message
        header("Location: book.html?error=send_failed");
        exit;
    }
    
} else {
    // If accessed directly, redirect to booking page
    header("Location: book.html");
    exit;
}
?>
