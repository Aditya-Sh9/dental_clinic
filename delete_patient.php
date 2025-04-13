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
        
        // 1. Delete xray images and records
        $xrays = $conn->query("SELECT id FROM xray_records WHERE patient_id = $id");
        while($xray = $xrays->fetch_assoc()) {
            $images = $conn->query("SELECT file_path FROM xray_images WHERE xray_id = {$xray['id']}");
            while($img = $images->fetch_assoc()) {
                if(file_exists($img['file_path'])) {
                    unlink($img['file_path']);
                }
            }
            $conn->query("DELETE FROM xray_images WHERE xray_id = {$xray['id']}");
        }
        $conn->query("DELETE FROM xray_records WHERE patient_id = $id");
        
        // 2. Delete treatment plan documents and files
        $docs = $conn->query("SELECT id, file_path FROM treatment_plan_documents 
                             WHERE treatment_plan_id IN 
                             (SELECT id FROM treatment_plans WHERE patient_id = $id)");
        while($doc = $docs->fetch_assoc()) {
            if(file_exists($doc['file_path'])) {
                unlink($doc['file_path']);
            }
            $conn->query("DELETE FROM treatment_plan_documents WHERE id = {$doc['id']}");
        }
        
        // 3. Delete treatment plan notes
        $conn->query("DELETE FROM treatment_plan_notes WHERE treatment_plan_id IN 
                     (SELECT id FROM treatment_plans WHERE patient_id = $id)");
        
        // 4. Delete treatment plans
        $conn->query("DELETE FROM treatment_plans WHERE patient_id = $id");
        
        // 5. Delete insurance documents and files
        $ins_docs = $conn->query("SELECT id, file_path FROM insurance_documents 
                                WHERE patient_id = $id");
        while($doc = $ins_docs->fetch_assoc()) {
            if(file_exists($doc['file_path'])) {
                unlink($doc['file_path']);
            }
            $conn->query("DELETE FROM insurance_documents WHERE id = {$doc['id']}");
        }
        
        // 6. Delete insurance claims
        $conn->query("DELETE FROM insurance_claims WHERE patient_id = $id");
        
        // 7. Delete patient insurance
        $conn->query("DELETE FROM patient_insurance WHERE patient_id = $id");
        
        // 8. Delete appointments
        $conn->query("DELETE FROM appointments WHERE patient_id = $id");
        
        // 9. Delete recalls
        $conn->query("DELETE FROM recalls WHERE patient_id = $id");
        
        // 10. Delete treatments
        $conn->query("DELETE FROM treatments WHERE patient_id = $id");
        
        // Finally delete the patient
        $stmt = $conn->prepare("DELETE FROM patients WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        if($stmt->affected_rows === 0) {
            throw new Exception("Patient not found");
        }
        
        $conn->commit();
        $_SESSION['success'] = "Patient and all related records deleted successfully";
    } catch(Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Deletion failed: " . $e->getMessage();
        error_log("Patient Deletion Error: " . $e->getMessage(), 3, "logs/deletion_errors.log");
    }
    
    header("Location: patients.php");
    exit();
}
?>