<?php
session_start();
include '../backend/db_config_notpdo.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "SELECT a.timestamp, u.firstname, u.lastname, a.user_role, a.action, a.status
          FROM audit_logs a
          JOIN santamaria_employees u ON a.user_id = u.id
          ORDER BY a.timestamp DESC";
$result = $conn->query($query);
$logs = [];
while($row = $result->fetch_assoc()) {
    $logs[] = $row;
}
echo json_encode($logs);
?>
