<?php
include '../backend/db_config_notpdo.php';

$type = $_GET['type'] ?? 'amount';
$timeframe = $_GET['timeframe'] ?? 'all';
$year = isset($_GET['year']) ? (int)$_GET['year'] : date("Y");
$month = isset($_GET['month']) ? (int)$_GET['month'] : date("n");
$week = isset($_GET['week']) ? (int)$_GET['week'] : null;
$quarter = isset($_GET['quarter']) ? (int)$_GET['quarter'] : null;

$where = "";

switch ($timeframe) {
    case 'today':
        $where = "WHERE DATE(t.date) = CURDATE()";
        break;

    case 'weekly':
        if ($year && $month && $week) {
            // First day and last day of month
            $firstDayOfMonth = new DateTime("$year-$month-01");
            $lastDayOfMonth = clone $firstDayOfMonth;
            $lastDayOfMonth->modify("last day of this month");

            // Calculate start of week (relative to month)
            $startDate = clone $firstDayOfMonth;
            $startDate->modify("+" . ($week - 1) . " weeks");

            // End date = start date + 6 days
            $endDate = clone $startDate;
            $endDate->modify("+6 days");

            // Clamp to month boundaries
            if ($startDate < $firstDayOfMonth) {
                $startDate = clone $firstDayOfMonth;
            }
            if ($endDate > $lastDayOfMonth) {
                $endDate = clone $lastDayOfMonth;
            }

            $startStr = $startDate->format("Y-m-d 00:00:00");
            $endStr = $endDate->format("Y-m-d 23:59:59");

            $where = "WHERE t.date BETWEEN '$startStr' AND '$endStr'";
        }
        break;

    case 'monthly':
        $where = "WHERE YEAR(t.date) = $year AND MONTH(t.date) = $month";
        break;

    case 'quarterly':
        if ($quarter) {
            $where = "WHERE YEAR(t.date) = $year AND QUARTER(t.date) = $quarter";
        }
        break;

    case 'yearly':
        $where = "WHERE YEAR(t.date) = $year";
        break;

    default:
        $where = ""; // all time
}

if ($type === 'amount') {
    $sql = "
        SELECT e.firstname, e.lastname, SUM(t.amount) AS value
        FROM transactions t
        JOIN santamaria_employees e ON e.email = t.collector_email
        $where
        GROUP BY t.collector_email 
        ORDER BY value DESC
        LIMIT 10
    ";
} else {
    $sql = "
        SELECT e.firstname, e.lastname, COUNT(t.id) AS value
        FROM transactions t
        JOIN santamaria_employees e ON e.email = t.collector_email
        $where
        GROUP BY t.collector_email
        ORDER BY value DESC
        LIMIT 10
    ";
}

$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'collector' => $row['firstname'] . ' ' . $row['lastname'],
        'value' => $type === 'amount' ? number_format($row['value'], 2) : (int)$row['value']
    ];
}

header('Content-Type: application/json');
echo json_encode($data);
