<?php
// get_report_kpis.php
header('Content-Type: application/json');

include '../backend/db_config_notpdo.php';
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Dates
$today = date('Y-m-d');
$weekStart = date('Y-m-d', strtotime('monday this week'));
$monthStart = date('Y-m-01');

// Total reports generated
$sqlToday = "SELECT COUNT(*) AS cnt FROM reports WHERE DATE(report_date) = '$today'";
$sqlWeek = "SELECT COUNT(*) AS cnt FROM reports WHERE DATE(report_date) >= '$weekStart'";
$sqlMonth = "SELECT COUNT(*) AS cnt FROM reports WHERE DATE(report_date) >= '$monthStart'";

$todayCount = $conn->query($sqlToday)->fetch_assoc()['cnt'] ?? 0;
$weekCount = $conn->query($sqlWeek)->fetch_assoc()['cnt'] ?? 0;
$monthCount = $conn->query($sqlMonth)->fetch_assoc()['cnt'] ?? 0;

// Most viewed (most common) report type
$sqlMostViewed = "
    SELECT report_type, COUNT(*) AS cnt 
    FROM reports 
    GROUP BY report_type 
    ORDER BY cnt DESC 
    LIMIT 1
";
$mostViewed = $conn->query($sqlMostViewed)->fetch_assoc();
$mostViewedType = $mostViewed['report_type'] ?? '—';

// Latest report generated (with reporter's name & position)
$sqlLatest = "
    SELECT r.report_id, r.report_type, r.file_path, e.firstname, e.lastname, e.position
    FROM reports r
    LEFT JOIN santamaria_employees e 
        ON r.user_email = e.email
    ORDER BY r.submission_timestamp DESC 
    LIMIT 1
";
$latest = $conn->query($sqlLatest)->fetch_assoc();

if ($latest) {
    $fullName = trim(($latest['firstname'] ?? '') . ' ' . ($latest['lastname'] ?? ''));
    $position = $latest['position'] ?? '';
    $latestName = $latest['report_type'] . ' - ' . $fullName . ($position ? " ({$position})" : '');
    $latestId = $latest['report_id'];
    $latestFilePath = $latest['file_path'] ?? null;
} else {
    $latestName = 'No report yet';
    $latestId = null;
    $latestFilePath = null;
}

$response = [
    'totalReports' => [
        'today' => (int)$todayCount,
        'week' => (int)$weekCount,
        'month' => (int)$monthCount
    ],
    'mostViewedType' => $mostViewedType,
    'latestReport' => [
        'name' => $latestName,
        'id' => $latestId,
        'filePath' => $latestFilePath
        // No direct link — frontend will trigger previewReport(id)
    ]
];

echo json_encode($response);
