<?php
// admin_actions/total_stats.php

// Database connection
include '../backend/db_config_notpdo.php';

if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed."]));
}

// Query total transactions and total revenue
$sql = "SELECT 
            COUNT(*) AS total_transactions, 
            IFNULL(SUM(amount), 0) AS total_revenue 
        FROM transactions";
 
$result = $conn->query($sql);

if ($result) {
    $data = $result->fetch_assoc();
    echo json_encode([
        "total_transactions" => (int)$data['total_transactions'],
        "total_revenue" => number_format((float)$data['total_revenue'], 2, '.', '')
    ]);
} else {
    echo json_encode(["error" => "Query failed."]);
}

$conn->close();
 