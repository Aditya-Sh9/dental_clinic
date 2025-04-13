<?php
header('Content-Type: application/json');

// Database configuration (alternative to email)
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'dental_clinic';

// Validate input
$errors = [];
$data = [];

if (empty($_POST['name'])) {
    $errors['name'] = 'Name is required.';
}

if (empty($_POST['email'])) {
    $errors['email'] = 'Email is required.';
} elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Email is invalid.';
}

if (empty($_POST['message'])) {
    $errors['message'] = 'Message is required.';
}

if (!empty($errors)) {
    $data['success'] = false;
    $data['errors'] = $errors;
    echo json_encode($data);
    exit;
}

// Process the form - Choose either EMAIL or DATABASE storage:

/**************** OPTION 1: EMAIL SOLUTION ****************/
// $to = 'adityasharma.reach@gmail.com'; // Change this to your real email
// $subject = 'New Contact Form Submission';
// $message = "Name: {$_POST['name']}\n";
// $message .= "Email: {$_POST['email']}\n";
// $message .= "Phone: " . (isset($_POST['phone']) ? $_POST['phone'] : 'Not provided') . "\n\n";
// $message .= "Message:\n{$_POST['message']}";
// $headers = "From: {$_POST['email']}";

// if (mail($to, $subject, $message, $headers)) {
//     $data['success'] = true;
//     $data['message'] = 'Thank you! Your message has been sent.';
// } else {
//     $data['success'] = false;
//     $data['message'] = 'Sorry, there was an error sending your message.';
// }

/**************** OPTION 2: DATABASE SOLUTION ****************/

try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $conn->prepare("INSERT INTO contact_submissions 
                          (name, email, phone, message, submitted_at) 
                          VALUES (:name, :email, :phone, :message, NOW())");
    
    $stmt->execute([
        ':name' => $_POST['name'],
        ':email' => $_POST['email'],
        ':phone' => $_POST['phone'] ?? null,
        ':message' => $_POST['message']
    ]);
    
    $data['success'] = true;
    $data['message'] = 'Thank you! Your message has been submitted.';
} catch(PDOException $e) {
    $data['success'] = false;
    $data['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($data);


?>