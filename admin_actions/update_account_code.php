<?php
header('Content-Type: application/json');
require_once '../backend/db_config.php';

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;
$code = trim($data['code'] ?? '');

if (!$id || !$code) {
    echo json_encode(["success" => false, "message" => "Missing fields"]);
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("UPDATE account_codes SET code = ? WHERE id = ?");
    $stmt->execute([$code, $id]);

    echo json_encode(["success" => true]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
