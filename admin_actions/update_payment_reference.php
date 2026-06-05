<?php
require_once __DIR__ . '/../backend/db_config.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    $id = intval($data['id']);
    $department = !empty($data['department']) ? $data['department'] : null; // ✅ allow NULL
    $accountCode = !empty($data['account_code']) ? $data['account_code'] : null; // ✅ allow NULL
    $categorization = $data['categorization']; // ✅ required
    $particulars = $data['particulars'];
    $amount = floatval($data['amount']);

    try {
        $pdo->beginTransaction();

        // ✅ Update lgupaymentreferences
        $stmt = $pdo->prepare("
            UPDATE lgupaymentreferences 
            SET department = :department, 
                account_code = :account_code, 
                categorization = :categorization, 
                particulars = :particulars, 
                amount = :amount 
            WHERE id = :id
        ");
        $stmt->execute([
            'department' => $department,
            'account_code' => $accountCode,
            'categorization' => $categorization,
            'particulars' => $particulars,
            'amount' => $amount,
            'id' => $id
        ]);

        // ✅ Also update transaction_items with the new categorization
        $stmt2 = $pdo->prepare("
            UPDATE transaction_items
            SET categorization = :categorization
            WHERE particular LIKE :particularPattern
        ");
        $stmt2->execute([
            'categorization' => $categorization,
            // transaction_items stores "Particular x qty" so use LIKE
            'particularPattern' => $particulars . '%'
        ]);

        $pdo->commit();

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
}
