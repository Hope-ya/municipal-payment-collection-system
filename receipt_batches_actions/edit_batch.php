<?php
require '../backend/db_config.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$id = $_GET['id'] ?? null;

if (
    !$id || !is_numeric($id) ||
    !isset($data['quantity'], $data['quantity_left'], $data['serial_start'], $data['serial_end'])
) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid input.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE receipt_batches SET quantity = ?, quantity_left = ?, serial_start = ?, serial_end = ? WHERE id = ?");
    $stmt->execute([
        $data['quantity'],
        $data['quantity_left'],
        $data['serial_start'],
        $data['serial_end'],
        $id
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Batch updated successfully.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
