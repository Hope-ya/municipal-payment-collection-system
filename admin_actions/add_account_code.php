<?php
header("Content-Type: application/json");
require_once "../backend/db_config.php";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $input = json_decode(file_get_contents("php://input"), true);
    $codes = $input["codes"] ?? [];

    if (empty($codes) || !is_array($codes)) {
        echo json_encode(["success" => false, "message" => "No account codes provided."]);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO account_codes (code) VALUES (:code)");
    $inserted = 0;

    foreach ($codes as $code) {
        $code = trim($code);
        if ($code === "") continue;

        try {
            $stmt->execute([":code" => $code]);
            $inserted++;
        } catch (PDOException $e) {
            // skip duplicates silently or handle individually
            if ($e->getCode() != 23000) throw $e;
        }
    }

    echo json_encode([
        "success" => true,
        "inserted" => $inserted,
        "message" => "$inserted code(s) added successfully."
    ]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
