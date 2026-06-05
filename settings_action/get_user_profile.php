<?php
session_start();
include '../backend/db_config_notpdo.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check session
if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['id'];

// Fetch user info
$sql = "SELECT firstname, lastname, position, email, recovery_email, phone, address 
        FROM santamaria_employees 
        WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'User not found']);
}
$stmt->close();
$conn->close();
?>
