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

    $stmt = $conn->prepare("UPDATE contact_submissions SET is_read = 1 WHERE id = ? AND is_read = 0");
    $stmt->bind_param("i", $contactId);
    $stmt->execute();

    $response['success'] = true;
    $response['message'] = 'Contact message marked as read';
    
    if ($stmt->affected_rows === 0) {
        $response['message'] = 'Message was already marked as read';
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
?>