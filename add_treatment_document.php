<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['document'])) {
    $treatment_plan_id = intval($_POST['treatment_plan_id']);
    $user_id = $_SESSION['user']['id'];
    
    $upload_dir = 'uploads/treatment_plans/';
    if(!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $file_name = basename($_FILES['document']['name']);
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $new_file_name = "treatment_plan_{$treatment_plan_id}_" . time() . ".$file_ext";
    $file_path = $upload_dir . $new_file_name;
    
    $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
    $max_file_size = 5 * 1024 * 1024; // 5MB
    
    if($_FILES['document']['size'] > $max_file_size) {
        echo json_encode(['success' => false, 'error' => 'File size exceeds 5MB limit']);
        exit();
    }
    
    if(!in_array($file_ext, $allowed_types)) {
        echo json_encode(['success' => false, 'error' => 'Invalid file type']);
        exit();
    }
    
    if(move_uploaded_file($_FILES['document']['tmp_name'], $file_path)) {
        $stmt = $conn->prepare("INSERT INTO treatment_plan_documents 
                              (treatment_plan_id, file_name, file_path, file_type, uploaded_by)
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssi", $treatment_plan_id, $file_name, $file_path, $file_ext, $user_id);
        
        if($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            unlink($file_path);
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'File upload error']);
    }
    exit();
}

header("HTTP/1.1 400 Bad Request");
echo json_encode(['success' => false, 'error' => 'Invalid request']);
?>