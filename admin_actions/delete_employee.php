<?php
// delete_employee.php
include '../backend/db_config_notpdo.php';

header('Content-Type: application/json');
error_reporting(0); // Disable warnings
ini_set('display_errors', 0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'invalid_request']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'invalid_id']);
    exit;
}

// Fetch employee name
$stmt = $conn->prepare("SELECT firstname, lastname FROM santamaria_employees WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();
$stmt->close();

if (!$employee) {
    echo json_encode(['success' => false, 'error' => 'not_found']);
    exit;
}

// Delete record
$stmt = $conn->prepare("DELETE FROM santamaria_employees WHERE id = ?");
$stmt->bind_param("i", $id);
$success = $stmt->execute();
$stmt->close();
$conn->close();

if ($success) {
    echo json_encode([
        'success' => true,
        'firstname' => $employee['firstname'],
        'lastname' => $employee['lastname']
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'delete_failed']);
}
?>
