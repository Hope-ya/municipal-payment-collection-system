<?php
// collector_actions/save_transaction_item.php
header('Content-Type: application/json');

include '../backend/db_config_notpdo.php';
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

$pglNo = $conn->real_escape_string($data['pgl_no']);
$particular = $conn->real_escape_string($data['particular']);
$quantity = (int)$data['quantity'];
$amount = (float)$data['amount'];

// ✅ Capture notes if existing
$notes = isset($data['notes']) ? $conn->real_escape_string($data['notes']) : "";

// 🔍 Step 1: Get the transaction date
$dateResult = $conn->query("SELECT date FROM transactions WHERE pgl_no = '$pglNo' LIMIT 1");

if ($dateResult && $dateResult->num_rows > 0) {
    $row = $dateResult->fetch_assoc();
    $date = $row['date'];
} else {
    echo json_encode(['success' => false, 'message' => 'Transaction date not found']);
    exit();
}

// 🔍 Step 2: Get categorization from lgupaymentreferences by matching "particulars"
$categorization = "Uncategorized";
$catResult = $conn->query("SELECT categorization FROM lgupaymentreferences WHERE particulars = '$particular' LIMIT 1");

if ($catResult && $catResult->num_rows > 0) {
    $catRow = $catResult->fetch_assoc();
    $categorization = $catRow['categorization'];
}

// ✅ Step 3: Insert including NOTES field
$sql = "INSERT INTO transaction_items (pgl_no, date, particular, notes, quantity, amount, categorization) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ssssids",
    $pglNo,
    $date,
    $particular,
    $notes,
    $quantity,
    $amount,
    $categorization
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();

