<?php
header("Content-Type: application/json");
include '../backend/db_config_notpdo.php';

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!isset($data['updates'])) {
    echo json_encode(["status" => "error", "error" => "No update data received"]);
    exit;
}

$updates = $data['updates'];

$stmt = $conn->prepare("UPDATE transactions SET date_remitted = ? WHERE id = ?");


foreach ($updates as $u) {
    $newDate = date("Y-m-d", strtotime($u['newRemittedDate']));

    $stmt->bind_param("si", $newDate, $u['id']);
    $stmt->execute();
}

$stmt->close();
$conn->close();

echo json_encode(["status" => "success"]);
?>
