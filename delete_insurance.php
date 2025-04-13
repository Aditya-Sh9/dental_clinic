<?php
session_start();
if(!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_config.php';

if(isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    try {
        $conn->begin_transaction();
        
        // Delete insurance documents and files
        $docs = $conn->query("SELECT id, file_path FROM insurance_documents WHERE insurance_id = $id");
        while($doc = $docs->fetch_assoc()) {
            if(file_exists($doc['file_path'])) {
                unlink($doc['file_path']);
            }
            $conn->query("DELETE FROM insurance_documents WHERE id = {$doc['id']}");
        }
        
        // Delete claims (should cascade from schema changes)
        $conn->query("DELETE FROM insurance_claims WHERE insurance_id = $id");
        
        // Delete the insurance policy
        $conn->query("DELETE FROM patient_insurance WHERE id = $id");
        
        $conn->commit();
        $_SESSION['success'] = "Insurance policy and all related records deleted successfully";
    } catch(Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Deletion failed: " . $e->getMessage();
        error_log("Insurance Deletion Error: " . $e->getMessage(), 3, "logs/deletion_errors.log");
    }
    
    header("Location: insurance.php");
    exit();
}
?>