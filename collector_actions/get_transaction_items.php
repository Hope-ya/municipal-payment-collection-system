<?php
require '../backend/db_config_notpdo.php';

$pgl_no = $_GET['pgl_no'] ?? '';
$response = ['status' => 'error', 'items' => []];

if ($pgl_no) {
    $stmt = $conn->prepare("SELECT particular, notes, quantity, amount FROM transaction_items WHERE pgl_no = ?");
    $stmt->bind_param("s", $pgl_no);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);

    $response = ['status' => 'success', 'items' => $items];
}

echo json_encode($response);
?>
