<?php
header('Content-Type: application/json');
require_once '../backend/db_config.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $input = json_decode(file_get_contents('php://input'), true);

    $sql = "
        SELECT 
            r.report_id, 
            r.position, 
            r.report_type, 
            r.report_date, 
            r.submission_timestamp, 
            CONCAT(e.firstname, ' ', e.lastname) AS user_name,
            r.file_path, 
            r.description
        FROM reports r
        LEFT JOIN santamaria_employees e 
            ON r.user_email = e.email
        WHERE 1=1
    ";

    $params = [];

    if (!empty($input['type'])) {
        $sql .= " AND r.report_type = :type";
        $params[':type'] = $input['type'];
    }

    if (!empty($input['position'])) {
        $sql .= " AND r.position = :position";
        $params[':position'] = $input['position'];
    }

    if (!empty($input['date_from'])) {
        $sql .= " AND r.report_date >= :date_from";
        $params[':date_from'] = $input['date_from'];
    }

    if (!empty($input['date_to'])) {
        $sql .= " AND r.report_date <= :date_to";
        $params[':date_to'] = $input['date_to'];
    }


    $sql .= " ORDER BY r.submission_timestamp DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'reports' => $reports]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
