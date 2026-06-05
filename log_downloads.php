<?php
header('Content-Type: application/json');
require_once 'backend/db_config.php';

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get the JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (empty($input['report_id']) || empty($input['action'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare the SQL statement
    $stmt = $pdo->prepare("
        INSERT INTO download_logs 
        (report_id, action, download_time, user_email) 
        VALUES 
        (:report_id, :action, NOW(), :user_email)
    ");

    // Get user email from session (adjust based on your authentication)
    $user_email = $_SESSION['email'] ?? 'anonymous';

    // Execute the query
    $stmt->execute([
        ':report_id' => $input['report_id'],
        ':action' => $input['action'],
        ':email' => $user_email
    ]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>