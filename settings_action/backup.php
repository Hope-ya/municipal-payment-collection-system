<?php
session_start();


// Only Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['position'] !== 'Admin') {
    http_response_code(403);
    exit("Unauthorized");
}

date_default_timezone_set("Asia/Manila");

// DB credentials
$host = "localhost";
$user = "root";
$pass = "";
$db   = "santamaria_db";

// Paths
$mysqldump = "C:\\xampp\\mysql\\bin\\mysqldump.exe";
$timestamp = date("Y-m-d_H-i-s");

$sqlFile = __DIR__ . "/backup_raw_$timestamp.sql";
$encFile = __DIR__ . "/backup_$timestamp.enc";

// Load keys
$keyFile = __DIR__ . "/../secure_keys/keys.json";
$keys = json_decode(file_get_contents($keyFile), true);

$activeKeyId = $keys["active_key_id"];
$hexKey = $keys["keys"][$activeKeyId];

$encryptionKey = hex2bin($hexKey);
if ($encryptionKey === false) {
    exit("Invalid encryption key format in keys.json");
}

// 1. Create raw SQL dump
$command = "\"$mysqldump\" -h $host -u $user --password=\"\" $db > \"$sqlFile\" 2>&1";
exec($command, $output, $returnCode);

if ($returnCode !== 0 || !file_exists($sqlFile)) {
    exit("Backup failed: " . implode("\n", $output));
}

// 2. Encrypt SQL file
$iv = random_bytes(16);
$plaintext = file_get_contents($sqlFile);
$ciphertext = openssl_encrypt(
    $plaintext,
    "AES-256-CBC",
    $encryptionKey,
    OPENSSL_RAW_DATA,
    $iv
);

file_put_contents($encFile, json_encode([
    "key_id" => $activeKeyId,
    "iv"     => base64_encode($iv),
    "data"   => base64_encode($ciphertext)
]));

// Remove raw SQL
unlink($sqlFile);

// Log audit
$conn = new mysqli("localhost", "root", "", "santamaria_db");
$userId = $_SESSION['user']['id'];
$adminName = $_SESSION['user']['firstname'] . " " . $_SESSION['user']['lastname'];
$position = $_SESSION['user']['position'];
$action = $conn->real_escape_string("Downloaded encrypted backup");

$conn->query("INSERT INTO audit_logs (user_id, user_name, user_role, action, timestamp)
              VALUES ($userId, '$adminName', '$position', '$action', NOW())");
$conn->close();

// 3. Download encrypted file
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"backup_$timestamp.enc\"");
header("Content-Length: " . filesize($encFile));

readfile($encFile);
unlink($encFile);
exit;
?>
