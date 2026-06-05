<?php

include __DIR__ . '/../backend/db_config_notpdo.php';


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error); 
}

function recordAudit($conn, $userId, $userRole, $action, $status = 'success') {
    $fullName = 'Unknown User';
    $success = false;

    if ($userId > 0) {
        $stmt = $conn->prepare("SELECT firstname, lastname FROM santamaria_employees WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $userId);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result && $result->num_rows === 1) {
                    $user = $result->fetch_assoc();
                    $fullName = $user['firstname'] . ' ' . $user['lastname'];
                }
                if ($result) $result->free();
            }
            $stmt->close();
        }
    }

    // Insert audit record
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, user_name, user_role, action, status) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("issss", $userId, $fullName, $userRole, $action, $status);
        $success = $stmt->execute();
        $stmt->close();
    }

    return $success;  // ✅ IMPORTANT RETURN
}
