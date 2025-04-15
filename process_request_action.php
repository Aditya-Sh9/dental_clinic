<?php
session_start();
require_once 'db_config.php';
require_once 'send_mail.php';

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $requestId = $_POST['id'] ?? 0;
    
    // Get the request details first
    $stmt = $conn->prepare("SELECT * FROM appointment_requests WHERE id = ?");
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $request = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$request) {
        echo json_encode(['success' => false, 'message' => 'Request not found']);
        exit();
    }
    
    if ($action == 'approve') {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Check if patient exists
            $patientStmt = $conn->prepare("SELECT id FROM patients WHERE email = ?");
            $patientStmt->bind_param("s", $request['email']);
            $patientStmt->execute();
            $patient = $patientStmt->get_result()->fetch_assoc();
            $patientStmt->close();
            
            $patientId = null;
            if (!$patient) {
                // Create new patient
                $insertPatient = $conn->prepare("INSERT INTO patients (name, email, phone, dob) 
                                              VALUES (?, ?, ?, ?)");
                $insertPatient->bind_param("ssss", 
                    $request['name'], 
                    $request['email'], 
                    $request['phone'], 
                    $request['dob']
                );
                
                if (!$insertPatient->execute()) {
                    throw new Exception("Failed to create patient record");
                }
                
                $patientId = $conn->insert_id;
                $insertPatient->close();
            } else {
                $patientId = $patient['id'];
            }
            
            // Create appointment
            $appointmentStmt = $conn->prepare("INSERT INTO appointments 
                (patient_id, appointment_date, appointment_time, reason, doctor_id) 
                VALUES (?, ?, ?, ?, 1)");
            $appointmentStmt->bind_param("isss", 
                $patientId,
                $request['preferred_date'],
                $request['preferred_time'],
                $request['service']
            );
            
            if (!$appointmentStmt->execute()) {
                throw new Exception("Failed to create appointment");
            }
            $appointmentStmt->close();
            
            // Update request status
            // Update request status
            $updateStmt = $conn->prepare("UPDATE appointment_requests SET status = 'approved' WHERE id = ?");
            $updateStmt->bind_param("i", $requestId);
            
            if (!$updateStmt->execute()) {
                throw new Exception("Failed to update request status");
            }
            $updateStmt->close();
            
            // Commit transaction
            $conn->commit();
            
            // Send approval email
            $to = $request['email'];
            $subject = "Your Appointment at Toothly Clinic Has Been Approved";
            $body = "
                <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                        .header { background-color: #4CAF50; color: white; padding: 10px; text-align: center; }
                        .content { padding: 20px; }
                        .footer { margin-top: 20px; font-size: 0.8em; color: #666; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h2>Appointment Approved</h2>
                        </div>
                        <div class='content'>
                            <p>Dear " . htmlspecialchars($request['name']) . ",</p>
                            <p>We're pleased to inform you that your appointment request has been approved.</p>
                            <p><strong>Appointment Details:</strong></p>
                            <ul>
                                <li>Date: " . date('F j, Y', strtotime($request['preferred_date'])) . "</li>
                                <li>Time: " . date('g:i A', strtotime($request['preferred_time'])) . "</li>
                                <li>Service: " . htmlspecialchars($request['service']) . "</li>
                            </ul>
                            <p>If you need to reschedule or cancel, please contact us at least 24 hours in advance.</p>
                            <p>We look forward to seeing you!</p>
                            <p>Best regards,<br>The Toothly Clinic Team</p>
                        </div>
                        <div class='footer'>
                            <p>This is an automated message. Please do not reply directly to this email.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";
            
            $mailSent = sendEmail($to, $subject, $body);
            
            echo json_encode([
                'success' => true,
                'message' => 'Appointment approved and scheduled' . ($mailSent ? '' : ' but email failed to send')
            ]);
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        
    } elseif ($action == 'reject') {
        // Update the status to rejected and update timestamp
        $stmt = $conn->prepare("UPDATE appointment_requests SET status = 'rejected', updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $requestId);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Appointment rejected']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error rejecting appointment']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
?>