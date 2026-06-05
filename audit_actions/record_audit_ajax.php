<?php
session_start();
header('Content-Type: application/json');

// Remove all buffered output
while (ob_get_level()) {
    ob_end_clean();
}

include '../backend/db_config_notpdo.php';
include '../audit_actions/audit_functions.php';

$response = [];

// Ensure session is valid
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$userId = $_SESSION['user']['id'];
$userRole = strtolower($_SESSION['user']['position']);

// Read input
$rawInput = file_get_contents("php://input");
$data = json_decode($rawInput, true);

// Force safe defaults
$action = isset($data['action']) ? trim($data['action']) : '';
$status = isset($data['status']) ? trim($data['status']) : 'success';

if ($action === '') {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit();
}
 
// recordAudit() MUST NOT echo anything
$ok = recordAudit($conn, $userId, $userRole, $action, $status);

// Output clean JSON
echo json_encode([
    'success' => $ok,
    'action' => $action
]);
exit();
