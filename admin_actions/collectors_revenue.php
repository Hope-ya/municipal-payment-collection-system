<?php
// admin_actions/collectors_revenue.php

// Database connection
include '../backend/db_config_notpdo.php';

if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed."]));
}



// Query revenue per collector
$sql = "SELECT 
            collector_email, 
            SUM(amount) AS total_collected
        FROM transactions
        GROUP BY collector_email
        ORDER BY total_collected DESC";

$result = $conn->query($sql);

$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            "collector" => $row['collector_email'],
            "total_collected" => (float)$row['total_collected']
        ];
    }

    echo json_encode($data);
} else {
    echo json_encode(["error" => "Query failed."]);
}

$conn->close();
?>
