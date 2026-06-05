<?php
header('Content-Type: application/json');
require_once '../backend/db_config.php';
session_start();

try {
    // Ensure user is logged in
    if (empty($_SESSION['email'])) {
        echo json_encode([
            'success' => false,
            'message' => 'User not logged in'
        ]);
        exit;
    }

    $loggedInEmail = $_SESSION['email'];

    // Database connection
    $pdo = new PDO("mysql:host={$host};dbname={$db}", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Base query
    $sql = "
        SELECT 
            report_id,
            report_type,
            report_date,
            submission_timestamp,
            file_path,
            description
        FROM reports
        WHERE user_email = :user_email
    ";

    $params = [':user_email' => $loggedInEmail];

    // Decode JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    $type = $input['type'] ?? '';
    $dateFrom = $input['date_from'] ?? '';
    $dateTo = $input['date_to'] ?? '';

    // Apply filters
    if (!empty($type)) {
        $sql .= " AND report_type = :type";
        $params[':type'] = $type;
    }

    if (!empty($dateFrom)) {
        $sql .= " AND report_date >= :date_from";
        $params[':date_from'] = $dateFrom;
    }

    if (!empty($dateTo)) {
        $sql .= " AND report_date <= :date_to";
        $params[':date_to'] = $dateTo;
    }

    $sql .= " ORDER BY submission_timestamp DESC";

    // Execute query
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Send response
    echo json_encode([
        'success' => true,
        'reports' => $reports
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
