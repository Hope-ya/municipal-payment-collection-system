<?php
header('Content-Type: application/json');
require_once 'backend/db_config.php';

// Read and decode JSON input
$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($data['report_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $report_id = $data['report_id'];

    // First get the file path
    $stmt = $pdo->prepare("SELECT file_path FROM reports WHERE report_id = :report_id");
    $stmt->execute([':report_id' => $report_id]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$report) {
        echo json_encode(['success' => false, 'message' => 'Report not found']);
        exit;
    }

    // Delete from database
    $stmt = $pdo->prepare("DELETE FROM reports WHERE report_id = :report_id");
    $stmt->execute([':report_id' => $report_id]);

    // Delete the file if it exists
    if (!empty($report['file_path']) && file_exists($report['file_path'])) {
        unlink($report['file_path']);
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
