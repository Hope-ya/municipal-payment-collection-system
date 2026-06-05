<?php
session_start();
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

include '../backend/db_config_notpdo.php'; // DB connection
include '../audit_actions/audit_functions.php'; // recordAudit() function

// ----------------- Check session -----------------
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Collector not logged in.']);
    exit;
}
 
$user = $_SESSION['user'];
$collectorEmail = $user['email'];
$userId = $user['id'];
$userRole = strtolower($user['position']);

// ----------------- Get POST data -----------------
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['payorname'], $data['particulars'], $data['amount'], $data['payment_type'], $data['pgl_no'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit;
}

$payorname = $data['payorname'];
$particulars = $data['particulars'];
$amount = $data['amount'];
$paymentType = $data['payment_type'];
$pglNo = $data['pgl_no'];
$checkNumber = isset($data['check_number']) ? $data['check_number'] : null;

// ----------------- Prepare statement -----------------
if ($paymentType === 'Check' && $checkNumber) {
    $stmt = $conn->prepare("INSERT INTO transactions (payorname, particulars, amount, payment_type, pgl_no, check_num, collector_email) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("ssdssss", $payorname, $particulars, $amount, $paymentType, $pglNo, $checkNumber, $collectorEmail);
} else {
    $stmt = $conn->prepare("INSERT INTO transactions (payorname, particulars, amount, payment_type, pgl_no, collector_email) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("ssdsss", $payorname, $particulars, $amount, $paymentType, $pglNo, $collectorEmail);
}

// ----------------- Execute and audit -----------------
try {
    if ($stmt->execute()) {
        $transaction_id = $conn->insert_id;

        // ✅ Record audit for successful payment
        $action = "Process Payment for: $payorname";
        recordAudit($conn, $userId, $userRole, $action, 'success');

        echo json_encode([
            'success' => true,
            'message' => 'Transaction saved successfully.',
            'transaction_id' => $transaction_id
        ]);
    } else {
        // Record failed payment attempt in audit
        $action = "Failed Payment Attempt for: $payorname";
        recordAudit($conn, $userId, $userRole, $action, 'failed');

        echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $stmt->error]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$stmt->close();
$conn->close();
