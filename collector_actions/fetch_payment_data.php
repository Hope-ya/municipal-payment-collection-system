<?php
session_start();
header('Content-Type: application/json');
include '../backend/db_config_notpdo.php';

// Get logged-in user's email
$userEmail = $_SESSION['email'] ?? '';

if (!$userEmail) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

// Query sum of amounts grouped by payment type for this collector
$sql = "SELECT payment_type, SUM(amount) AS total
        FROM transactions
        WHERE collector_email = ?
        AND payment_type IN ('Cash','Check')
        GROUP BY payment_type";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['error' => 'Database prepare failed']);
    exit;
}

$stmt->bind_param("s", $userEmail);
$stmt->execute();
$result = $stmt->get_result();

// Initialize totals
$paymentData = ['Cash' => 0, 'Check' => 0];

// Fill totals from query
while ($row = $result->fetch_assoc()) {
    $paymentData[$row['payment_type']] = (float)$row['total'];
}

// Return JSON
echo json_encode($paymentData);
?>
