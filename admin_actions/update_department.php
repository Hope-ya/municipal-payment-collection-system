<?php
header("Content-Type: application/json");
require_once "../backend/db_config.php";

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data["id"]) || empty($data["department"])) {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE departments SET department = ? WHERE id = ?");
    $stmt->execute([$data["department"], $data["id"]]);

    echo json_encode(["success" => true]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
