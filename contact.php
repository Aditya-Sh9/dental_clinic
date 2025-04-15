<?php
require_once 'db_config.php';

header('Content-Type: application/json');
$response = ['success' => false, 'message' => ''];

try {
    // Validate input
    $errors = [];
    
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name)) {
        $errors['name'] = 'Name is required';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Valid email is required';
    }

    if (empty($message)) {
        $errors['message'] = 'Message is required';
    }

    if (!empty($errors)) {
        $response['errors'] = $errors;
        throw new Exception('Please correct the form errors');
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO contact_submissions 
                          (name, email, phone, message, submitted_at, is_read) 
                          VALUES (?, ?, ?, ?, NOW(), 0)");
    $stmt->bind_param("ssss", $name, $email, $phone, $message);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Your message has been sent successfully!';
    } else {
        throw new Exception('Failed to save your message');
    }
    
    $stmt->close();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>