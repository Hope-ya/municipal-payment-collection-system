<?php
require_once '../backend/db_config_notpdo.php';
session_start();
header('Content-Type: application/json');

// Decode incoming JSON data
$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (
    !isset($data["quantity"], $data["serial_start"], $data["serial_end"], $data["collectorEmail"])
) {
    echo json_encode(["status" => "error", "message" => "Missing required fields."]);
    exit;
}

$quantity = intval($data["quantity"]);
$serial_start = intval($data["serial_start"]);
$serial_end = intval($data["serial_end"]);
$email = $data["collectorEmail"];
$quantity_left = $quantity; // Initial value

// ✅ Check if batch with the same serial range exists first
$check = $conn->prepare("SELECT id FROM receipt_batches WHERE serial_start = ? AND serial_end = ?");
$check->bind_param("ii", $serial_start, $serial_end);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Batch already exists with this serial range."]);
    exit;
}
$check->close();

// ✅ Proceed with insert
$sql = "INSERT INTO receipt_batches (quantity, quantity_left, serial_start, serial_end, collector_email) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiis", $quantity, $quantity_left, $serial_start, $serial_end, $email);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
}
$stmt->close();
$conn->close();
