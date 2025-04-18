<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

// Include PHPMailer manually (without Composer)
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Email sending function
function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'adityasharma.reach@gmail.com'; // Replace with your email
        $mail->Password   = 'qbev umtt spzo tqny'; // Replace with app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->SMTPDebug  = 2; // Enable verbose debug output
        $mail->Debugoutput = function($str, $level) {
            error_log("SMTP debug level $level: $str");
        };
        
        // Recipients
        $mail->setFrom('no-reply@brightsmiledental.com', 'Toothly');
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        if(!$mail->send()) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
            return false;
        }
        
        return true;
    } catch (Exception $e) {
        error_log("PHPMailer Exception: " . $e->getMessage());
        return false;
    }
}
// Handle add recall
if(isset($_POST['add_recall'])) {
    $patient_id = $conn->real_escape_string($_POST['patient_id']);
    $recall_type = $conn->real_escape_string($_POST['recall_type']);
    $recall_date = $conn->real_escape_string($_POST['recall_date']);
    $due_date = $conn->real_escape_string($_POST['due_date']);
    $notes = $conn->real_escape_string($_POST['notes']);

    $query = "INSERT INTO recalls (patient_id, recall_type, recall_date, due_date, notes) 
              VALUES ('$patient_id', '$recall_type', '$recall_date', '$due_date', '$notes')";
    
    if($conn->query($query)) {
        $_SESSION['success'] = "Recall added successfully!";
        
        // Send notification email if checkbox was checked
        if(isset($_POST['send_notification'])) {
            sendRecallNotification($conn->insert_id, $conn);
        }
    } else {
        $_SESSION['error'] = "Error adding recall: " . $conn->error;
    }
    header("Location: recalls.php");
    exit();
}

// Handle update recall
if(isset($_POST['update_recall'])) {
    $recall_id = intval($_POST['recall_id']);
    $patient_id = $conn->real_escape_string($_POST['patient_id']);
    $recall_type = $conn->real_escape_string($_POST['recall_type']);
    $recall_date = $conn->real_escape_string($_POST['recall_date']);
    $due_date = $conn->real_escape_string($_POST['due_date']);
    $status = $conn->real_escape_string($_POST['status']);
    $notes = $conn->real_escape_string($_POST['notes']);

    $query = "UPDATE recalls SET 
              patient_id = '$patient_id',
              recall_type = '$recall_type',
              recall_date = '$recall_date',
              due_date = '$due_date',
              status = '$status',
              notes = '$notes'
              WHERE id = $recall_id";
    
    if($conn->query($query)) {
        $_SESSION['success'] = "Recall updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating recall: " . $conn->error;
    }
    header("Location: recalls.php");
    exit();
}

// Handle complete recall
if(isset($_GET['action']) && $_GET['action'] === 'complete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "UPDATE recalls SET status = 'completed' WHERE id = $id";
    
    if($conn->query($query)) {
        $_SESSION['success'] = "Recall marked as completed!";
    } else {
        $_SESSION['error'] = "Error completing recall: " . $conn->error;
    }
    header("Location: recalls.php");
    exit();
}

// Handle send notification
if(isset($_GET['action']) && $_GET['action'] === 'send_notification' && isset($_GET['id'])) {
    $recall_id = intval($_GET['id']);
    
    if(sendRecallNotification($recall_id, $conn)) {
        $_SESSION['success'] = "Recall notification sent successfully to patient!";
    } else {
        $_SESSION['error'] = "Failed to send recall notification. Check error logs for details.";
    }
    
    header("Location: recalls.php");
    exit();
}

// Function to send recall notification
function sendRecallNotification($recall_id, $conn) {
    // Get recall and patient details
    $query = "SELECT r.*, p.name as patient_name, p.email 
              FROM recalls r
              JOIN patients p ON r.patient_id = p.id
              WHERE r.id = $recall_id";
    $result = $conn->query($query);
    
    if($result->num_rows === 0) {
        error_log("Recall not found with ID: $recall_id");
        return false;
    }
    
    $recall = $result->fetch_assoc();
    
    if(empty($recall['email'])) {
        error_log("No email found for patient: {$recall['patient_name']}");
        return false;
    }
    
    // Email content
    $to = $recall['email'];
    $subject = "Dental Recall Reminder - Toothly";
    $message = "
    <html>
    <head>
        <title>Dental Recall Reminder</title>
    </head>
    <body>
        <h2>Dear {$recall['patient_name']},</h2>
        <p>This is a friendly reminder about your upcoming dental {$recall['recall_type']}.</p>
        <p><strong>Due Date:</strong> " . date('F j, Y', strtotime($recall['due_date'])) . "</p>
        <p><strong>Recall Type:</strong> {$recall['recall_type']}</p>
        " . ($recall['notes'] ? "<p><strong>Notes:</strong> {$recall['notes']}</p>" : "") . "
        <p>Please contact our office to schedule your appointment.</p>
        <p>Best regards,<br>Toothly Team</p>
    </body>
    </html>
    ";
    
    try {
        return sendEmail($to, $subject, $message);
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        return false;
    }
}

$conn->close();
?>