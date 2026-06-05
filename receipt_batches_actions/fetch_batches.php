<?php
session_start();
require '../backend/db_config.php';

header('Content-Type: application/json');

// Ensure collector is logged in
if (!isset($_SESSION['email'])) {
    echo json_encode(['error' => 'Unauthorized: Collector not logged in']);
    http_response_code(401);
    exit;
}

$collectorEmail = $_SESSION['email'];

// Fetch by ID (for editing)
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM receipt_batches WHERE id = ? AND collector_email = ?");
    $stmt->execute([$_GET['id'], $collectorEmail]);
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    exit;
}

// Handle optional search (by serial number)
$search = $_GET['search'] ?? '';
$search = trim($search);

if ($search !== '' && is_numeric($search)) {
    $stmt = $pdo->prepare("
        SELECT * FROM receipt_batches 
        WHERE collector_email = ? AND serial_start <= ? AND serial_end >= ?
        ORDER BY id DESC
    ");
    $stmt->execute([$collectorEmail, $search, $search]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM receipt_batches WHERE collector_email = ? ORDER BY id DESC");
    $stmt->execute([$collectorEmail]);
}

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
