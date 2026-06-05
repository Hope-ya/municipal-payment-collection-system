<?php
// update_employee.php
require_once __DIR__ . '/../backend/db_config.php'; // Your database connection file

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Method not allowed']));
}

// Get and validate input
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$firstname = trim(filter_input(INPUT_POST, 'firstname', FILTER_SANITIZE_STRING));
$lastname = trim(filter_input(INPUT_POST, 'lastname', FILTER_SANITIZE_STRING));
$position = trim(filter_input(INPUT_POST, 'position', FILTER_SANITIZE_STRING));

if (!$id || !$firstname || !$lastname || !$position) {
    http_response_code(400);
    die(json_encode(['error' => 'Missing required fields']));
}

try {
    // Update employee without email and password
    $stmt = $pdo->prepare("UPDATE santamaria_employees 
                          SET firstname = ?, lastname = ?, position = ?, updated_at = NOW() 
                          WHERE id = ?");
    $stmt->execute([$firstname, $lastname, $position, $id]);
    
    echo json_encode(['success' => 'Employee updated successfully!']);
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
}
