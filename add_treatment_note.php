<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $treatment_plan_id = intval($_POST['treatment_plan_id']);
    $note = $conn->real_escape_string($_POST['note']);
    $user_id = $_SESSION['user']['id'];
    
    $stmt = $conn->prepare("INSERT INTO treatment_plan_notes 
                          (treatment_plan_id, user_id, note)
                          VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $treatment_plan_id, $user_id, $note);
    
    if($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }
    exit();
}

header("HTTP/1.1 400 Bad Request");
echo json_encode(['success' => false, 'error' => 'Invalid request']);
?>