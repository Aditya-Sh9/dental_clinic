<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

if(isset($_GET['doc_id']) && isset($_GET['plan_id'])) {
    $doc_id = intval($_GET['doc_id']);
    $plan_id = intval($_GET['plan_id']);
    $user_id = $_SESSION['user']['id'];
    
    // Verify document belongs to treatment plan
    $stmt = $conn->prepare("SELECT file_path FROM treatment_plan_documents 
                           WHERE id = ? AND treatment_plan_id = ?");
    $stmt->bind_param("ii", $doc_id, $plan_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $document = $result->fetch_assoc();
        
        // Delete from database
        $delete_stmt = $conn->prepare("DELETE FROM treatment_plan_documents WHERE id = ?");
        $delete_stmt->bind_param("i", $doc_id);
        
        if($delete_stmt->execute()) {
            // Delete file
            if(file_exists($document['file_path'])) {
                unlink($document['file_path']);
            }
            $_SESSION['success'] = "Document deleted successfully";
        } else {
            $_SESSION['error'] = "Error deleting document";
        }
    } else {
        $_SESSION['error'] = "Document not found";
    }
    
    header("Location: view_treatment_plan.php?id=$plan_id");
    exit();
}

header("Location: treatment_plans.php");
exit();
?>