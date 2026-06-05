<?php
header('Content-Type: application/json');

// Connect to DB using PDO
require_once __DIR__ . '/../backend/db_config.php';

// Get raw input
$data = json_decode(file_get_contents("php://input"), true);

// Validate presence of required fields (only these are required)
if (
    empty($data['particular']) ||
    empty($data['categorization']) // ✅ required
) {
    echo json_encode(["success" => false, "error" => "Categorization and Particular are required."]);
    exit;
}

// Assign sanitized values
// Assign sanitized values
$department = !empty($data['department']) ? $data['department'] : null; 
$account_code = !empty($data['account_code']) ? $data['account_code'] : null; 
$particular = $data['particular'];
$amount = isset($data['amount']) && $data['amount'] !== '' ? floatval($data['amount']) : null; // ✅ allow NULL
$categorization = $data['categorization'];


try {
    // Prepare the SQL using PDO
    $stmt = $pdo->prepare("INSERT INTO lgupaymentreferences 
        (department, account_code, particulars, amount, categorization) 
        VALUES (:department, :account_code, :particular, :amount, :categorization)");
    
    // Execute with parameters
    $stmt->execute([
        ':department' => $department,
        ':account_code' => $account_code,
        ':particular' => $particular,
        ':amount' => $amount,
        ':categorization' => $categorization
    ]);

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
