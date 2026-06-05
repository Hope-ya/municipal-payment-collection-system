<?php
header('Content-Type: application/json');
require_once '../backend/db_config.php';

try {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!isset($input['categorizations']) || !is_array($input['categorizations']) || empty($input['categorizations'])) {
        echo json_encode(['success' => false, 'message' => 'No categorizations provided.']);
        exit;
    }

    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $added = 0;
    foreach ($input['categorizations'] as $cat) {
        $cat = trim($cat);
        if ($cat === '') continue;

        $check = $pdo->prepare("SELECT id FROM categorizations WHERE categorization = ?");
        $check->execute([$cat]);
        if ($check->rowCount() === 0) {
            $stmt = $pdo->prepare("INSERT INTO categorizations (categorization) VALUES (?)");
            $stmt->execute([$cat]);
            $added++;
        }
    }

    echo json_encode(['success' => true, 'message' => "$added categorization(s) added successfully."]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
