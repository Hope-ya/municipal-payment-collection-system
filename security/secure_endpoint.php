<?php
// Prevent direct URL access to this file
if (!defined('APP_SECURE')) {
    http_response_code(403);
    exit("Forbidden");
}

// --- SECURITY HARDENING ---

// Force HTTPS
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") {
    http_response_code(403);
    exit("HTTPS required");
}

// Strengthen session
session_set_cookie_params([
    'lifetime' => 0,
    'httponly' => true,
    'samesite' => 'Strict',
    'secure' => true
]);
session_start();

// Must be logged in
if (!isset($_SESSION['user']) || !isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

// Basic CSRF check
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || 
        $_SERVER['HTTP_X_CSRF_TOKEN'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        echo json_encode(["error" => "Invalid CSRF token"]);
        exit();
    }
}



// Prevent JSON responses from being cached
header("Cache-Control: no-store");
header("Content-Type: application/json");
