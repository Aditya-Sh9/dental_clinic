<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

// Get recall ID from URL
$recall_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($recall_id <= 0) {
    $_SESSION['error'] = "Invalid recall ID";
    header("Location: recalls.php");
    exit();
}

// Delete the recall
$query = "DELETE FROM recalls WHERE id = $recall_id";

if($conn->query($query)) {
    $_SESSION['success'] = "Recall deleted successfully!";
} else {
    $_SESSION['error'] = "Error deleting recall: " . $conn->error;
}

$conn->close();
header("Location: recalls.php");
exit();
?>