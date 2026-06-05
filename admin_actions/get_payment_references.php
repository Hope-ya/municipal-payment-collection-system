<?php
// admin_actions/get_payment_references.php

// Database configuration
include '../backend/db_config_notpdo.php';

// Set headers first to prevent any output before them
header('Content-Type: application/json');

try {
    // Create database connection
   
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Prepare and execute query
    $sql = "SELECT * FROM lgupaymentreferences ORDER BY particulars ASC";
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    // Fetch all results
    $references = [];
    while ($row = $result->fetch_assoc()) {
        // Ensure consistent data format
        $references[] = [
            'id' => $row['id'] ?? null,
            'department' => $row['department'] ?? '',
            'account_code' => $row['account_code'] ?? '',
            'categorization' => $row['categorization'] ?? '', // ✅ Added
            'particulars' => $row['particulars'] ?? '',
            'amount' => $row['amount'] ?? 0
        ];
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'data' => $references
    ]);

} catch (Exception $e) {
    // Return error response
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    // Close connections
    if (isset($conn)) {
        $conn->close();
    }
}
