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
        
        // 1. First delete all associated documents and their files
        $docs = $conn->query("SELECT id, file_path FROM insurance_documents WHERE claim_id = $id");
        while($doc = $docs->fetch_assoc()) {
            if(file_exists($doc['file_path'])) {
                unlink($doc['file_path']);
            }
            $conn->query("DELETE FROM insurance_documents WHERE id = {$doc['id']}");
        }
        
        // 2. Then delete the claim itself
        $stmt = $conn->prepare("DELETE FROM insurance_claims WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        if($stmt->affected_rows === 0) {
            throw new Exception("Claim not found");
        }
        
        $conn->commit();
        $_SESSION['success'] = "Insurance claim and all related documents deleted successfully";
    } catch(Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error deleting claim: " . $e->getMessage();
        error_log("Claim Deletion Error: " . $e->getMessage(), 3, "logs/deletion_errors.log");
    }
}

header("Location: insurance.php");
exit();
?>