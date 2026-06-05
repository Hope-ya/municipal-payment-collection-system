<?php
header('Content-Type: application/json');
include '../backend/db_config_notpdo.php';

// Read JSON input
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$id = $data['id'];
$newName = $data['payorname'] ?? "";

// Validate
if (!$id || $newName == "") {
    echo json_encode(["status" => "error", "message" => "Invalid input"]);
    exit;
}

$stmt = $conn->prepare("UPDATE transactions SET payorname = ? WHERE id = ?");
$stmt->bind_param("si", $newName, $id);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error"]);
}

$stmt->close();
$conn->close();
?>
