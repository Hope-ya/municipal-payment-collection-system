<?php
require '../backend/db_config_notpdo.php';

header('Content-Type: application/json');

$batch_id = $_GET['id'] ?? null;

if (!$batch_id || !is_numeric($batch_id)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid batch ID']);
    exit;
}

// Optional: You may want to check collector ownership for extra security
session_start();
if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}
$collectorEmail = $_SESSION['email'];

$query = "
    UPDATE receipt_batches 
    SET quantity_left = quantity_left - 1 
    WHERE id = ? 
    AND quantity_left > 0 
    AND collector_email = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("is", $batch_id, $collectorEmail);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "No rows updated (maybe already 0 or wrong collector)"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
}
?>
