<?php
session_start();
header('Content-Type: application/json');
 
include '../backend/db_config_notpdo.php';

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit();
}


// ✅ If ID is provided, return a single transaction
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM transactions");
    $stmt->bind_param("is", $id, $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $transaction = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'transaction' => $transaction]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Transaction not found']);
    }

    $stmt->close();
    $conn->close();
    exit();
}

// 🌐 Otherwise, filter multiple transactions
$filter = $_GET['filter'] ?? '';
$date = $_GET['date'] ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$quarter = $_GET['quarter'] ?? '';
$whereClause = '';

$quarter = $_GET['quarter'] ?? '';
$whereClause = '';

if ($startDate && $endDate) {
    $whereClause = "WHERE DATE(date) BETWEEN '$startDate' AND '$endDate'";
} elseif ($quarter) {
    switch ($quarter) {
        case 'q1': // Jan - Mar
            $whereClause = "WHERE MONTH(date) BETWEEN 1 AND 3";
            break;
        case 'q2': // Apr - Jun
            $whereClause = "WHERE MONTH(date) BETWEEN 4 AND 6";
            break;
        case 'q3': // Jul - Sep
            $whereClause = "WHERE MONTH(date) BETWEEN 7 AND 9";
            break;
        case 'q4': // Oct - Dec
            $whereClause = "WHERE MONTH(date) BETWEEN 10 AND 12";
            break;
    }
} elseif ($date) {
    $whereClause = "WHERE DATE(date) = '$date'";
} else {
    switch ($filter) {
        case 'today':
            $whereClause = "WHERE DATE(date) = CURDATE()";
            break;
        case 'week1':
            $whereClause = "WHERE WEEK(date, 1) = WEEK(CURDATE(), 1) AND DAYOFMONTH(date) <= 7";
            break;
        case 'week2':
            $whereClause = "WHERE WEEK(date, 1) = WEEK(CURDATE(), 1) AND DAYOFMONTH(date) BETWEEN 8 AND 14";
            break;
        case 'week3':
            $whereClause = "WHERE WEEK(date, 1) = WEEK(CURDATE(), 1) AND DAYOFMONTH(date) BETWEEN 15 AND 21";
            break;
        case 'week4':
            $whereClause = "WHERE WEEK(date, 1) = WEEK(CURDATE(), 1) AND DAYOFMONTH(date) BETWEEN 22 AND 28";
            break;
        case 'week5':
            $whereClause = "WHERE WEEK(date, 1) = WEEK(CURDATE(), 1) AND DAYOFMONTH(date) > 28";
            break;
        case 'january':
        case 'february':
        case 'march':
        case 'april':
        case 'may':
        case 'june':
        case 'july':
        case 'august':
        case 'september': 
        case 'october':
        case 'november':
        case 'december':
            $monthNum = date('m', strtotime("1 $filter"));
            $whereClause = "WHERE MONTH(date) = $monthNum";
            break;
        default:
            if (strpos($filter, 'year-') === 0) {
                $year = str_replace('year-', '', $filter);
                $whereClause = "WHERE YEAR(date) = $year";
            }
            break;
    }
}


$sql = "SELECT * FROM transactions $whereClause ORDER BY id DESC";
$result = $conn->query($sql);

if (!$result) {
    echo json_encode(['status' => 'error', 'message' => 'Query failed: ' . $conn->error]);
    exit();
}

$transactions = [];

while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}

echo json_encode(['status' => 'success', 'transactions' => $transactions]);

$conn->close();
