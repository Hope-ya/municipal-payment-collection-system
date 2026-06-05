<?php
header('Content-Type: application/json');

// Database connection
include '../backend/db_config_notpdo.php';
if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// Get filters from request
$type = $_GET['type'] ?? 'amount'; // 'amount' or 'quantity'
$dateFilter = $_GET['date'] ?? 'monthly'; // 'monthly', 'quarterly', 'yearly'
$sub = $_GET['sub'] ?? ''; // month number, quarter number, or year


// Build date condition
$dateCondition = "";
$currentYear = date("Y");

if ($sub !== 'all') { // Only apply filter if not "all"
    if ($dateFilter === 'monthly' && $sub) {
        $month = intval($sub);
        $dateCondition = "AND MONTH(date) = $month AND YEAR(date) = $currentYear";

    } elseif ($dateFilter === 'quarterly' && $sub) {
        $quarter = intval($sub);
        $monthStart = ($quarter - 1) * 3 + 1;
        $monthEnd = $monthStart + 2;
        $dateCondition = "AND MONTH(date) BETWEEN $monthStart AND $monthEnd AND YEAR(date) = $currentYear";

    } elseif ($dateFilter === 'yearly' && $sub) {
        $year = intval($sub);
        $dateCondition = "AND YEAR(date) = $year";
    }
}

// Order by total amount or quantity
$orderBy = ($type === 'quantity') ? 'SUM(quantity)' : 'SUM(amount)';

// SQL query
$sql = "
    SELECT particular, 
           SUM(quantity) AS total_quantity, 
           SUM(amount) AS total_amount
    FROM transaction_items
    WHERE 1 $dateCondition
    GROUP BY particular
    ORDER BY $orderBy DESC
    LIMIT 10
";

$result = $conn->query($sql);
$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'particular' => $row['particular'],
            'quantity' => (int)$row['total_quantity'],
            'amount' => (float)$row['total_amount']
        ];
    }
}

echo json_encode($data);
$conn->close();
?>
