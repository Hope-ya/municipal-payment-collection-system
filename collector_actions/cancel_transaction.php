<?php
header('Content-Type: application/json');
include '../backend/db_config_notpdo.php';

// Read JSON input
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$id = $data['id'] ?? null;

if (!$id) {
    echo json_encode(["status" => "error", "message" => "Invalid transaction ID"]);
    exit;
}

// STEP 1 — Fetch the PGL No. of the transaction
$stmt = $conn->prepare("SELECT pgl_no FROM transactions WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Transaction not found"]);
    exit;
}

$row = $result->fetch_assoc();
$pglNo = $row['pgl_no'];

$stmt->close();

// STEP 2 — Cancel the transaction
$stmt = $conn->prepare("
    UPDATE transactions 
    SET payorname = 'Cancelled',
        particulars = '-',
        amount = 0.00,
        payment_type = '-'
    WHERE id = ?
");

$stmt->bind_param("i", $id);
$transaction_update_success = $stmt->execute();
$stmt->close();


// STEP 3 — Cancel items linked to this PGL No.
$stmt = $conn->prepare("
    UPDATE transaction_items
    SET particular = '-',
        notes = '-',
        amount = 0.00
    WHERE pgl_no = ?
");
$stmt->bind_param("s", $pglNo);
$items_update_success = $stmt->execute();
$stmt->close();

$conn->close();

// Final result
if ($transaction_update_success && $items_update_success) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error"]);
}
?>
