<?php
header('Content-Type: application/json');

include '../backend/db_config.php';


try {
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get total collected amount
    $totalQuery = "SELECT SUM(amount) as total_collected FROM transactions";
    $totalStmt = $conn->query($totalQuery);
    $totalResult = $totalStmt->fetch(PDO::FETCH_ASSOC);

    // Get breakdown by payment type
    $breakdownQuery = "SELECT payment_type, SUM(amount) as total_amount 
                      FROM transactions 
                      GROUP BY payment_type 
                      ORDER BY total_amount DESC";
    $breakdownStmt = $conn->query($breakdownQuery);
    $breakdownResults = $breakdownStmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare response
    $response = [
        'status' => 'success',
        'total_collected' => $totalResult['total_collected'] ? (float)$totalResult['total_collected'] : 0,
        'breakdown' => $breakdownResults
    ];

    echo json_encode($response);
} catch (PDOException $e) {
    // Return error response
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage(),
        'total_collected' => 0,
        'breakdown' => []
    ]);
}

$conn = null;
