<?php
session_start();
header('Content-Type: application/json');
include '../backend/db_config_notpdo.php'; // DB connection

$collectorEmail = $_SESSION['email'] ?? '';

if (!$collectorEmail) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$timeframe = $_GET['timeframe'] ?? 'daily';

// ---------- Labels ----------
$labels = [];
switch ($timeframe) {
    case 'daily':
        for ($i = 6; $i >= 0; $i--) {
            $labels[] = date('D', strtotime("-$i days"));
        }
        break;

    case 'weekly':
        $firstDay = date('Y-m-01');
        $lastDay  = date('Y-m-t');
        $weekNum  = 1;
        $weekStart = $firstDay;
        while (strtotime($weekStart) <= strtotime($lastDay)) {
            $weekEnd = date('Y-m-d', strtotime($weekStart . ' +6 days'));
            if (strtotime($weekEnd) > strtotime($lastDay)) {
                $weekEnd = $lastDay;
            }
            $labels[] = "Week " . $weekNum++;
            $weekStart = date('Y-m-d', strtotime($weekEnd . ' +1 day'));
        }
        break;

    case 'monthly':
        for ($i = 0; $i < 12; $i++) {
            $labels[] = date('M', mktime(0, 0, 0, $i + 1, 1));
        }
        break;

    case 'quarterly':
        $labels = ['Q1','Q2','Q3','Q4'];
        break;

    case 'yearly':
        $labels = [
            date('Y', strtotime('-4 years')),
            date('Y', strtotime('-3 years')),
            date('Y', strtotime('-2 years')),
            date('Y', strtotime('-1 year')),
            date('Y')
        ];
        break;
}

// ---------- Fetch Revenue ----------
function fetchSums($conn, $collectorEmail, $timeframe) {
    $data = [];

    switch ($timeframe) {
        case 'daily':
            for ($i = 6; $i >= 0; $i--) {
                $day = date('Y-m-d', strtotime("-$i days"));
                $stmt = $conn->prepare("
                    SELECT SUM(amount) as total 
                    FROM transactions 
                    WHERE collector_email = ? AND DATE(date) = ?
                ");
                $stmt->bind_param("ss", $collectorEmail, $day);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                $data[] = (float)($result['total'] ?? 0);
            }
            break;

        case 'weekly':
            $monthStart = date('Y-m-01');
            $monthEnd   = date('Y-m-t');
            $weekStart  = $monthStart;
            while (strtotime($weekStart) <= strtotime($monthEnd)) {
                $weekEnd = date('Y-m-d', strtotime($weekStart . ' +6 days'));
                if (strtotime($weekEnd) > strtotime($monthEnd)) {
                    $weekEnd = $monthEnd;
                }
                $stmt = $conn->prepare("
                    SELECT SUM(amount) as total 
                    FROM transactions 
                    WHERE collector_email = ? AND DATE(date) BETWEEN ? AND ?
                ");
                $stmt->bind_param("sss", $collectorEmail, $weekStart, $weekEnd);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                $data[] = (float)($result['total'] ?? 0);
                $weekStart = date('Y-m-d', strtotime($weekEnd . ' +1 day'));
            }
            break;

        case 'monthly':
            for ($i = 0; $i < 12; $i++) {
                $month = date('Y-m', strtotime(date('Y-01-01') . " +$i month"));
                $stmt = $conn->prepare("
                    SELECT SUM(amount) as total 
                    FROM transactions 
                    WHERE collector_email = ? AND DATE_FORMAT(date, '%Y-%m') = ?
                ");
                $stmt->bind_param("ss", $collectorEmail, $month);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                $data[] = (float)($result['total'] ?? 0);
            }
            break;

        case 'quarterly':
            $year = date('Y');
            for ($q = 1; $q <= 4; $q++) {
                $startMonth   = ($q - 1) * 3 + 1;
                $quarterStart = "$year-$startMonth-01";
                $quarterEnd   = date('Y-m-t', strtotime($quarterStart . ' +2 months'));
                $stmt = $conn->prepare("
                    SELECT SUM(amount) as total 
                    FROM transactions 
                    WHERE collector_email = ? AND DATE(date) BETWEEN ? AND ?
                ");
                $stmt->bind_param("sss", $collectorEmail, $quarterStart, $quarterEnd);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                $data[] = (float)($result['total'] ?? 0);
            }
            break;
 
        case 'yearly':
            for ($i = 4; $i >= 0; $i--) {
                $year = date('Y', strtotime("-$i year"));
                $stmt = $conn->prepare("
                    SELECT SUM(amount) as total 
                    FROM transactions 
                    WHERE collector_email = ? AND YEAR(date) = ?
                ");
                $stmt->bind_param("ss", $collectorEmail, $year);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                $data[] = (float)($result['total'] ?? 0);
            }
            break;
    }

    return $data;
}

$amounts = fetchSums($conn, $collectorEmail, $timeframe);

echo json_encode([
    'labels' => $labels,
    'amounts' => $amounts
]);
