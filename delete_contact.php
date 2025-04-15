<?php
require_once 'db_config.php';

session_start();
if(!isset($_SESSION['user'])) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$response = ['success' => false, 'message' => ''];

try {
    $contactId = $_POST['id'] ?? 0;

    if (empty($contactId)) {
        throw new Exception('Invalid contact ID');
    }

    // First check if the contact exists
    $checkStmt = $conn->prepare("SELECT id FROM contact_submissions WHERE id = ?");
    $checkStmt->bind_param("i", $contactId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows === 0) {
        throw new Exception('Contact message not found');
    }

    // Delete the contact
    $deleteStmt = $conn->prepare("DELETE FROM contact_submissions WHERE id = ?");
    $deleteStmt->bind_param("i", $contactId);
    $deleteStmt->execute();

    if ($deleteStmt->affected_rows > 0) {
        $response['success'] = true;
        $response['message'] = 'Contact message deleted successfully';
    } else {
        throw new Exception('Failed to delete contact message');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
?>