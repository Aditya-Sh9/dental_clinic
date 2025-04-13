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
        
        // Delete documents and files
        $docs = $conn->query("SELECT id, file_path FROM treatment_plan_documents WHERE treatment_plan_id = $id");
        while($doc = $docs->fetch_assoc()) {
            if(file_exists($doc['file_path'])) {
                unlink($doc['file_path']);
            }
            $conn->query("DELETE FROM treatment_plan_documents WHERE id = {$doc['id']}");
        }
        
        // Delete notes
        $conn->query("DELETE FROM treatment_plan_notes WHERE treatment_plan_id = $id");
        
        // Delete plan
        $conn->query("DELETE FROM treatment_plans WHERE id = $id");
        
        $conn->commit();
        $_SESSION['success'] = "Treatment plan and all related files deleted";
    } catch(Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
    
    header("Location: treatment_plans.php");
    exit();
}
?>