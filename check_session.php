<?php
session_start();
include 'backend/db_config_notpdo.php';

$user_id = $_SESSION['user']['id'] ?? 0;
$current_token = $_SESSION['session_token'] ?? '';

if ($user_id === 0) {
    echo json_encode(["valid" => false]);
    exit;
}

$stmt = $conn->prepare("SELECT session_token FROM santamaria_employees WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($db_token);
$stmt->fetch();
$stmt->close();

if ($db_token !== $current_token) {
    echo json_encode(["valid" => false]);
} else {
    echo json_encode(["valid" => true]);
}
exit;
?>
