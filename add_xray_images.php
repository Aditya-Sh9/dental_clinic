<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['xray_images'])) {
    $xray_id = intval($_POST['xray_id']);
    $notes = $conn->real_escape_string($_POST['notes'] ?? '');
    $user_id = $_SESSION['user']['id'];
    
    // Verify X-ray record exists
    $stmt = $conn->prepare("SELECT id FROM xray_records WHERE id = ?");
    $stmt->bind_param("i", $xray_id);
    $stmt->execute();
    
    if($stmt->get_result()->num_rows === 0) {
        $_SESSION['error'] = "Invalid X-ray record";
        header("Location: xrays.php");
        exit();
    }
    
    $upload_dir = 'uploads/xrays/';
    if(!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $uploaded_files = 0;
    $allowed_types = ['jpg', 'jpeg', 'png', 'dicom'];
    $max_file_size = 10 * 1024 * 1024; // 10MB
    
    foreach($_FILES['xray_images']['name'] as $key => $name) {
        $file_name = basename($name);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $tmp_name = $_FILES['xray_images']['tmp_name'][$key];
        $file_size = $_FILES['xray_images']['size'][$key];
        
        if(in_array($file_ext, $allowed_types) && $file_size <= $max_file_size) {
            $new_file_name = "xray_{$xray_id}_" . time() . "_{$key}.$file_ext";
            $file_path = $upload_dir . $new_file_name;
            
            if(move_uploaded_file($tmp_name, $file_path)) {
                $stmt = $conn->prepare("INSERT INTO xray_images 
                                      (xray_id, file_name, file_path, file_type, notes, uploaded_by)
                                      VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issssi", $xray_id, $file_name, $file_path, $file_ext, $notes, $user_id);
                
                if($stmt->execute()) {
                    $uploaded_files++;
                }
            }
        }
    }
    
    if($uploaded_files > 0) {
        $_SESSION['success'] = "Successfully uploaded $uploaded_files image(s)";
    } else {
        $_SESSION['error'] = "No images were uploaded";
    }
    
    header("Location: view_xray.php?id=$xray_id");
    exit();
}

header("Location: xrays.php");
exit();
?>