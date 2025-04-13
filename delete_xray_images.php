<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

if(isset($_GET['image_id']) && isset($_GET['xray_id'])) {
    $image_id = intval($_GET['image_id']);
    $xray_id = intval($_GET['xray_id']);
    
    // Verify image belongs to X-ray record
    $stmt = $conn->prepare("SELECT file_path FROM xray_images WHERE id = ? AND xray_id = ?");
    $stmt->bind_param("ii", $image_id, $xray_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $image = $result->fetch_assoc();
        
        // Delete from database
        $delete_stmt = $conn->prepare("DELETE FROM xray_images WHERE id = ?");
        $delete_stmt->bind_param("i", $image_id);
        
        if($delete_stmt->execute()) {
            // Delete file
            if(file_exists($image['file_path'])) {
                unlink($image['file_path']);
            }
            $_SESSION['success'] = "Image deleted successfully";
        } else {
            $_SESSION['error'] = "Error deleting image";
        }
    } else {
        $_SESSION['error'] = "Image not found";
    }
    
    header("Location: view_xray.php?id=$xray_id");
    exit();
}

header("Location: xrays.php");
exit();
?>