<?php
session_start();
// admin_actions/delete_payment_reference.php
require_once '../backend/db_config.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || !is_numeric($data['id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid ID']);
    exit;
}

$id = intval($data['id']);
$userId = $_SESSION['id'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'error' => 'User not authenticated']);
    exit;
}

try {
    // ✅ Fetch particulars for audit logging
    $stmt = $pdo->prepare("SELECT particulars FROM lgupaymentreferences WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(['success' => false, 'error' => 'Payment reference not found']);
        exit;
    }

    $particulars = $row['particulars'];

    // ✅ Delete the record
    $stmt = $pdo->prepare("DELETE FROM lgupaymentreferences WHERE id = :id");
    $stmt->execute(['id' => $id]);

    // ✅ Fetch user details (firstname, lastname, role)
    $userStmt = $pdo->prepare("SELECT firstname, lastname, position FROM santamaria_employees WHERE id = :id LIMIT 1");
    $userStmt->execute(['id' => $userId]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

    $userName = $user ? $user['firstname'] . ' ' . $user['lastname'] : 'Unknown User';
    $userRole = $user['position'] ?? 'Unknown Position';

    // ✅ Record audit log with full details
    $action = "Deleted payment reference: {$particulars}";
    $auditStmt = $pdo->prepare("
        INSERT INTO audit_logs (user_id, user_name, user_role, action, timestamp) 
        VALUES (:user_id, :user_name, :user_role, :action, NOW())
    ");
    $auditStmt->execute([
        'user_id'   => $userId,
        'user_name' => $userName,
        'user_role' => $userRole,
        'action'    => $action
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
