<?php
session_start();
require '../backend/db_config.php';

header('Content-Type: application/json');

// Check if collector is logged in
if (!isset($_SESSION['email'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    http_response_code(401);
    exit;
}

$collectorEmail = $_SESSION['email'];

try {
    // Get the oldest batch with quantity_left > 0 for this collector
    $stmt = $pdo->prepare("
        SELECT * FROM receipt_batches
        WHERE collector_email = ?
        AND quantity_left > 0
        ORDER BY id ASC
        LIMIT 1
    ");
    $stmt->execute([$collectorEmail]);
    $batch = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$batch) {
        echo json_encode(['status' => 'error', 'message' => 'No available receipt batches']);
        exit;
    }

    // Compute the next available PGL No. for this batch
    $used = $batch['quantity'] - $batch['quantity_left'];
    $nextPgl = $batch['serial_start'] + $used;

    echo json_encode([
        'status' => 'success',
        'pgl_no' => $nextPgl,
        'batch_id' => $batch['id']
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server error']);
}
