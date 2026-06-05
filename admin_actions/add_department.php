<?php
header('Content-Type: application/json');
require_once '../backend/db_config.php';

try {
    $input = json_decode(file_get_contents("php://input"), true);

    // ✅ Validate input
    if (empty($input['departments']) || !is_array($input['departments'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Please provide at least one valid department.'
        ]);
        exit;
    }

    // ✅ Initialize PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $added = 0;
    $skipped = [];

    foreach ($input['departments'] as $department) {
        $department = trim($department);
        if ($department === '') continue;

        // ✅ Prevent duplicate entries (case-insensitive)
        $check = $pdo->prepare("SELECT id FROM departments WHERE LOWER(department) = LOWER(?)");
        $check->execute([$department]);

        if ($check->rowCount() === 0) {
            $stmt = $pdo->prepare("INSERT INTO departments (department) VALUES (?)");
            $stmt->execute([$department]);
            $added++;
        } else {
            $skipped[] = $department;
        }
    }

    // ✅ Response message
    if ($added > 0) {
        $message = "$added department(s) added successfully.";
        if (!empty($skipped)) {
            $message .= " Skipped duplicates: " . implode(', ', $skipped) . ".";
        }
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        echo json_encode(['success' => false, 'message' => 'All departments already exist.']);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while adding departments: ' . $e->getMessage()
    ]);
}
