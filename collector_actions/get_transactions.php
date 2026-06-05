<?php

session_start();
header('Content-Type: application/json');

include '../backend/db_config_notpdo.php';


if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit();
}

$userEmail = $_SESSION['email'] ?? ($_GET['email'] ?? '');

if (!$userEmail) {
    echo json_encode(['status' => 'error', 'message' => 'User email not provided']);
    exit();
}

// ✅ If ID is provided, return a single transaction
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM transactions WHERE id = ? AND collector_email = ?");
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
$whereClause = '';

if ($startDate && $endDate) {
    $whereClause = "WHERE DATE(date) BETWEEN '$startDate' AND '$endDate' AND collector_email = '$userEmail'";
} elseif ($date) {
    $whereClause = "WHERE DATE(date) = '$date' AND collector_email = '$userEmail'";
} else {
    switch ($filter) {
        case 'today':
            $whereClause = "WHERE DATE(date) = CURDATE() AND collector_email = '$userEmail'";
            break;
        case 'week1':
            $whereClause = "WHERE WEEK(date, 1) = WEEK(CURDATE(), 1) AND DAYOFMONTH(date) <= 7 AND collector_email = '$userEmail'";
            break;
        case 'week2':
            $whereClause = "WHERE WEEK(date, 1) = WEEK(CURDATE(), 1) AND DAYOFMONTH(date) BETWEEN 8 AND 14 AND collector_email = '$userEmail'";
            break;
        case 'week3':
            $whereClause = "WHERE WEEK(date, 1) = WEEK(CURDATE(), 1) AND DAYOFMONTH(date) BETWEEN 15 AND 21 AND collector_email = '$userEmail'";
            break;
        case 'week4':
            $whereClause = "WHERE WEEK(date, 1) = WEEK(CURDATE(), 1) AND DAYOFMONTH(date) BETWEEN 22 AND 28 AND collector_email = '$userEmail'";
            break;
        case 'week5':
            $whereClause = "WHERE WEEK(date, 1) = WEEK(CURDATE(), 1) AND DAYOFMONTH(date) > 28 AND collector_email = '$userEmail'";
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
            $whereClause = "WHERE MONTH(date) = $monthNum AND collector_email = '$userEmail'";
            break;
        default:
            if (strpos($filter, 'year-') === 0) {
                $year = str_replace('year-', '', $filter);
                $whereClause = "WHERE YEAR(date) = $year AND collector_email = '$userEmail'";
            } else {
                $whereClause = "WHERE collector_email = '$userEmail'";
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
