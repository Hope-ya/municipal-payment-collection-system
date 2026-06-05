<?php
// admin_actions/top_particulars.php

// Database connection
include '../backend/db_config_notpdo.php';

if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed."]));
}


// Query Top 10 Particulars by frequency
$sql = "SELECT 
            particulars, 
            COUNT(*) AS sold_count
        FROM transactions
        GROUP BY particulars
        ORDER BY sold_count DESC
        LIMIT 10";

$result = $conn->query($sql);

$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            "particular" => $row['particulars'],
            "sold_count" => (int)$row['sold_count']
        ];
    }

    echo json_encode($data);
} else {
    echo json_encode(["error" => "Query failed."]);
}

$conn->close();
