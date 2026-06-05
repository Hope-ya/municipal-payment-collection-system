<?php
// get_employee.php
require_once __DIR__ . '/../backend/db_config.php';

// Set headers first to prevent any output before them
header('Content-Type: application/json');

try {
    // Validate ID parameter
    if (!isset($_GET['id'])) {
        throw new Exception('Employee ID is required', 400);
    }

    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        throw new Exception('Invalid employee ID', 400);
    }

    // Query database
    $stmt = $pdo->prepare("SELECT id, firstname, lastname, position, email FROM santamaria_employees WHERE id = ?");
    $stmt->execute([$id]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$employee) {
        throw new Exception('Employee not found', 404);
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'data' => $employee
    ]);

} catch (Exception $e) {
    // Set appropriate HTTP status
    http_response_code($e->getCode() ?: 500);
    
    // Return error response
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}