<?php
session_start();
include '../backend/db_config_notpdo.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$filter = $_GET['filter'] ?? 'daily'; // daily is default now

// Fetch total transactions and revenue (no collector filter)
$query = "SELECT COUNT(*) AS total_transactions, COALESCE(SUM(amount), 0) AS total_revenue 
          FROM transactions";
$result = $conn->query($query)->fetch_assoc();

// Define grouping based on filter
switch ($filter) {
    case 'daily':
        // Daily for the current week (Monday-Sunday)
        $startOfWeek = date('Y-m-d', strtotime('monday this week'));
        $endOfWeek = date('Y-m-d', strtotime('sunday this week'));
        $whereDate = "WHERE DATE(date) BETWEEN '$startOfWeek' AND '$endOfWeek'";
        $groupBy = "DATE(date)";
        $labelFormat = "DATE_FORMAT(date, '%Y-%m-%d')";
        break;
        
    case 'weekly':
        // Weekly for current month
        $startOfMonth = date('Y-m-01');
        $endOfMonth = date('Y-m-t');
        $whereDate = "WHERE date BETWEEN '$startOfMonth' AND '$endOfMonth'";
        $groupBy = "WEEK(date, 1)";
        $labelFormat = "CONCAT(YEAR(date), '-W', WEEK(date, 1))";
        break;
        
    case 'monthly':
        $whereDate = "";
        $groupBy = "DATE_FORMAT(date, '%Y-%m')";
        $labelFormat = "DATE_FORMAT(date, '%Y-%m')";
        break;
        
    case 'quarterly':
        $whereDate = "";
        $groupBy = "CONCAT(YEAR(date), '-Q', QUARTER(date))";
        $labelFormat = $groupBy;
        break;
        
    case 'yearly':
        $whereDate = "";
        $groupBy = "YEAR(date)";
        $labelFormat = "YEAR(date)";
        break;
        
    default:
        $whereDate = "";
        $groupBy = "DATE_FORMAT(date, '%Y-%m')";
        $labelFormat = "DATE_FORMAT(date, '%Y-%m')";
}

// Fetch revenue breakdown (no collector filter)
$monthlyQuery = "SELECT $labelFormat AS label, SUM(amount) AS total
                 FROM transactions
                 $whereDate
                 GROUP BY $groupBy
                 ORDER BY MIN(date) ASC";

$monthlyResult = $conn->query($monthlyQuery);

$monthlyData = [];
while ($row = $monthlyResult->fetch_assoc()) {
    $monthlyData[] = ['label' => $row['label'], 'total' => $row['total']];
}

echo json_encode([
    
    'monthly_data' => $monthlyData
]);
?>
