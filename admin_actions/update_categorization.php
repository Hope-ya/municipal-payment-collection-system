<?php
header("Content-Type: application/json");
require_once "../backend/db_config.php";

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data["id"]) || empty($data["categorization"])) {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE categorizations SET categorization = ? WHERE id = ?");
    $stmt->execute([$data["categorization"], $data["id"]]);

    echo json_encode(["success" => true]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
 