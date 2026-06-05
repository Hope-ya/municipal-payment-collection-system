<?php
require '../backend/db_config.php';
$data = json_decode(file_get_contents("php://input"), true);
$stmt = $pdo->prepare("DELETE FROM receipt_batches WHERE id = ?");
$stmt->execute([$data['id']]);
