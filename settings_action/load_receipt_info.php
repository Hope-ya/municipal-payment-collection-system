<?php
session_start();
header('Content-Type: application/json');
include '../backend/db_config_notpdo.php';

// Check DB connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Ensure only logged-in Admin can view receipt info
if (!isset($_SESSION['user']) || strtolower(trim($_SESSION['user']['position'] ?? '')) !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Fetch existing receipt information
$query = "SELECT agency_name, treasurer_name FROM receipt_info WHERE id = 1";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'agencyName' => $data['agency_name'],
        'treasurerName' => $data['treasurer_name']
    ]);
} else {
    // Default empty response if no record yet
    echo json_encode([
        'success' => true,
        'agencyName' => '',
        'treasurerName' => ''
    ]);
}

$conn->close();
exit;
?>
