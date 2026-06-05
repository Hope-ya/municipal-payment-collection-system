<?php

include '../backend/db_config_notpdo.php';

// Order by the 'particular' column alphabetically
$query = "SELECT * FROM lgupaymentreferences ORDER BY particulars ASC";
$result = $conn->query($query);

$particulars = array();
while ($row = $result->fetch_assoc()) {
    $particulars[] = $row;
}

header('Content-Type: application/json');
echo json_encode($particulars);
?>

