<?php
require_once 'db_config.php';

// Initialize response array
$response = ['success' => false, 'message' => ''];

try {
    // Validate input
    $errors = [];
    
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $dob = $_POST['dob'] ?? null;
    $preferred_date = $_POST['preferred_date'] ?? '';
    $preferred_time = $_POST['preferred_time'] ?? '';
    $service = trim($_POST['service'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name)) {
        $errors['name'] = 'Full name is required';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Valid email is required';
    }

    if (empty($phone)) {
        $errors['phone'] = 'Phone number is required';
    }

    if (empty($preferred_date)) {
        $errors['preferred_date'] = 'Preferred date is required';
    }

    if (empty($preferred_time)) {
        $errors['preferred_time'] = 'Preferred time is required';
    }

    if (empty($service)) {
        $errors['service'] = 'Service is required';
    }

    if (!empty($errors)) {
        $response['errors'] = $errors;
        throw new Exception('Please correct the form errors');
    }

    // Create appointment request (status will be 'pending' by default)
    $stmt = $conn->prepare("INSERT INTO appointment_requests 
                          (name, email, phone, dob, preferred_date, preferred_time, service, message, status) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("ssssssss", $name, $email, $phone, $dob, $preferred_date, $preferred_time, $service, $message);
    $stmt->execute();

    $response['success'] = true;
    $response['message'] = 'Your appointment request has been submitted successfully. We will contact you shortly to confirm.';

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);