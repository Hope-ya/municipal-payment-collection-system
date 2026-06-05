<?php
session_start();
require_once "backend/db_config.php"; // adjust if needed

if (!isset($_SESSION['id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}
 
$userId = $_SESSION['id'];

// Treat "never" (string) or 0 as integer 0
$logoutTime = isset($_POST['logout_time']) && $_POST['logout_time'] !== 'never' ? intval($_POST['logout_time']) : 0;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if user already has saved settings
    $stmt = $pdo->prepare("SELECT id FROM user_timeout_settings WHERE user_id = ?");
    $stmt->execute([$userId]);

    if ($stmt->rowCount() > 0) {
        // Update existing record
        $update = $pdo->prepare("UPDATE user_timeout_settings SET logout_time = ? WHERE user_id = ?");
        $update->execute([$logoutTime, $userId]);
    } else {
        // Insert new record
        $insert = $pdo->prepare("INSERT INTO user_timeout_settings (user_id, logout_time) VALUES (?, ?)");
        $insert->execute([$userId, $logoutTime]);
    }

    echo json_encode(["success" => true, "message" => "Settings saved successfully"]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "DB Error: " . $e->getMessage()]);
}
?>
