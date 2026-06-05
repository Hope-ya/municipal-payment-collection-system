<?php
header('Content-Type: application/json');
include '../backend/db_config_notpdo.php';

date_default_timezone_set('Asia/Manila');

$today = date('Y-m-d');

$sql = "
    SELECT
        id,
        pgl_no,
        payorname,
        particulars,
        amount,
        payment_type,
        check_num,
        date,
        time,
        date_remitted
    FROM transactions
    WHERE DATE(date_remitted) = '$today'
    ORDER BY date_remitted DESC, time
";

$result = $conn->query($sql);

$transactions = [];
while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}

echo json_encode([
    'status' => 'success',
    'transactions' => $transactions
]);
?>
