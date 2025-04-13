<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

if(isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // First delete associated images
    $conn->query("DELETE FROM xray_images WHERE xray_id = $id");
    
    // Then delete the xray record
    if($conn->query("DELETE FROM xray_records WHERE id = $id")) {
        $_SESSION['success'] = "X-ray record deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting x-ray record";
    }
}

header("Location: xrays.php");
exit();
?>