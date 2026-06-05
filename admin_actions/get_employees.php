<?php
include '../backend/db_config_notpdo.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error); 
}

$sql = "SELECT id, firstname, lastname, position, email, phone, address FROM santamaria_employees";
$result = $conn->query($sql);

$employees = array();
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $employees[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($employees);

$conn->close();
?>