<?php
// admin_actions/revenue_overview.php

// Database connection
include '../backend/db_config_notpdo.php';
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed."]));
}

// Get filter type
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'daily'; // default = daily

$today = date('Y-m-d');
$currentYear = date('Y');

// Query for different filters
switch ($filter) {
    case 'daily':
        $sql = "SELECT DATE(date) as label, SUM(amount) as total
                FROM transactions
                WHERE date = '$today'
                GROUP BY label
                ORDER BY label ASC";
        break;

    case 'monthly':
        $sql = "SELECT DATE_FORMAT(date, '%Y-%m-%d') as label, SUM(amount) as total
                FROM transactions
                WHERE MONTH(date) = MONTH(CURDATE()) AND YEAR(date) = YEAR(CURDATE())
                GROUP BY label
                ORDER BY label ASC";
        break;

    case 'quarterly':
        $currentQuarter = ceil(date('n') / 3);
        $sql = "SELECT CONCAT('Q', QUARTER(date)) as label, SUM(amount) as total
                FROM transactions
                WHERE YEAR(date) = $currentYear
                GROUP BY QUARTER(date)
                HAVING label = 'Q$currentQuarter'
                ORDER BY label ASC";
        break;

    case 'yearly':
        $sql = "SELECT DATE_FORMAT(date, '%M') as label, SUM(amount) as total
                FROM transactions
                WHERE YEAR(date) = $currentYear
                GROUP BY MONTH(date)
                ORDER BY MONTH(date) ASC";
        break;

    default:
        $sql = "SELECT DATE(date) as label, SUM(amount) as total
                FROM transactions
                GROUP BY label
                ORDER BY label ASC";
}

$result = $conn->query($sql);

$labels = [];
$totals = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $labels[] = $row['label'];
        $totals[] = (float)$row['total'];
    }

    echo json_encode([
        "labels" => $labels,
        "totals" => $totals
    ]);
} else {
    echo json_encode(["error" => "Query failed."]);
}

$conn->close();
?>
