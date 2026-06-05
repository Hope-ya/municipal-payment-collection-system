<?php
session_start();
require_once "backend/db_config.php";

if (!isset($_SESSION['id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}
 
$userId = $_SESSION['id'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
 
    $stmt = $pdo->prepare("SELECT logout_time FROM user_timeout_settings WHERE user_id = ?");
    $stmt->execute([$userId]);

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo json_encode([
            "success" => true,
            "logout_time" => (int)$row['logout_time']
        ]);
    } else {
        echo json_encode([
            "success" => true,
            "logout_time" => 15
        ]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "DB Error: " . $e->getMessage()]);
}
?>
