<?php
$host = "localhost";
$user = "root";
$password = ""; // Update this if you have a password
$database = "santamaria_db"; // Replace with your DB name

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
