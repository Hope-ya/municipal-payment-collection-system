<?php
session_start();
require_once "backend/db_config_notpdo.php";

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode([
        "success" => false, 
        "toast" => ["type" => "error", "message" => "Not logged in"]
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    $password = $_POST['password'];
    $userId = $_SESSION['id'];

    $stmt = $conn->prepare("SELECT password FROM santamaria_employees WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result && password_verify($password, $result['password'])) {
        echo json_encode([
            "success" => true, 
            "toast" => ["type" => "success", "message" => "Screen unlocked successfully"]
        ]);
    } else {
        echo json_encode([
            "success" => false, 
            "toast" => ["type" => "error", "message" => "Incorrect password, please try again"]
        ]);
    }
}
?>
