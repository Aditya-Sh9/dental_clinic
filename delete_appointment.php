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
        
        // Check existence
        $check = $conn->query("SELECT id FROM appointments WHERE id = $id");
        if($check->num_rows === 0) {
            throw new Exception("Appointment not found");
        }
        
        // Delete related treatment plans (will cascade to documents/notes)
        $conn->query("DELETE FROM treatment_plans WHERE appointment_id = $id");
        
        // Update treatments to remove appointment reference
        $conn->query("UPDATE treatments SET appointment_id = NULL WHERE appointment_id = $id");
        
        // Update xray records to remove appointment reference
        $conn->query("UPDATE xray_records SET appointment_id = NULL WHERE appointment_id = $id");
        
        // Delete recalls associated with this appointment
        $conn->query("DELETE FROM recalls WHERE appointment_id = $id");
        
        // Delete appointment
        $conn->query("DELETE FROM appointments WHERE id = $id");
        
        $conn->commit();
        $_SESSION['success'] = "Appointment and related records deleted successfully";
    } catch(Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
        error_log("Appointment Deletion Error: " . $e->getMessage(), 3, "logs/deletion_errors.log");
    }
    
    header("Location: appointments.php");
    exit();
}
?>