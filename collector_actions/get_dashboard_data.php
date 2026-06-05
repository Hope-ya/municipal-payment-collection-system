<?php
session_start();
include '../backend/db_config_notpdo.php';

// Make sure user is logged in
if (!isset($_SESSION['email'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$email = $_SESSION['email'];
$filter = $_GET['filter'] ?? 'daily'; // daily is default now

// Fetch total transactions and revenue
$query = "SELECT COUNT(*) AS total_transactions, COALESCE(SUM(amount), 0) AS total_revenue 
          FROM transactions 
          WHERE collector_email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

// Define grouping based on filter
switch ($filter) {
    case 'daily':
        // Daily for the current week (Monday-Sunday)
        $startOfWeek = date('Y-m-d', strtotime('monday this week'));
        $endOfWeek = date('Y-m-d', strtotime('sunday this week'));
        $whereDate = "AND DATE(date) BETWEEN '$startOfWeek' AND '$endOfWeek'";
        $groupBy = "DATE(date)";
        $labelFormat = "DATE_FORMAT(date, '%Y-%m-%d')";
        break;
        
    case 'weekly':
        // Weekly for current month
        $startOfMonth = date('Y-m-01');
        $endOfMonth = date('Y-m-t');
        $whereDate = "AND date BETWEEN '$startOfMonth' AND '$endOfMonth'";
        $groupBy = "WEEK(date, 1)"; // ISO week number
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

// Fetch revenue breakdown
$monthlyQuery = "SELECT $labelFormat AS label, SUM(amount) AS total
                 FROM transactions 
                 WHERE collector_email = ? $whereDate
                 GROUP BY $groupBy
                 ORDER BY MIN(date) ASC";

$stmt = $conn->prepare($monthlyQuery);
$stmt->bind_param("s", $email);
$stmt->execute();
$monthlyResult = $stmt->get_result();

$monthlyData = [];
while ($row = $monthlyResult->fetch_assoc()) {
    $monthlyData[] = ['label' => $row['label'], 'total' => $row['total']];
}

echo json_encode([
    'total_transactions' => $result['total_transactions'],
    'total_revenue' => number_format($result['total_revenue'], 2, '.', ''),
    'monthly_data' => $monthlyData
]);
?>
