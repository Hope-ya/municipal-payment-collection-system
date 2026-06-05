<?php
session_start();
include 'backend/db_config_notpdo.php'; 
include 'audit_actions/audit_functions.php';

if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
    // Record logout event
    recordAudit($conn, $user['id'], strtolower($user['position']), 'Logged out', 'success');
}
 
// Destroy session
session_destroy();

// Don't redirect if called via fetch
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo json_encode(["success" => true]);
    exit;
}
 
// If logout was triggered via link or button, redirect
// If logout was triggered via link or button, redirect
header("Location: index.php?logout=1");
exit;


