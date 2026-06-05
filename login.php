<?php
session_start();
include 'backend/db_config_notpdo.php'; // MySQLi
include 'backend/db_config.php'; // PDO
include 'audit_actions/audit_functions.php';

// Brute force settings
$max_attempts = 5;
$lockout_time = 15; // seconds
$ip = $_SERVER['REMOTE_ADDR'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    //----------------------------------------------------
    // 1. CHECK IF IP IS LOCKED OUT
    //----------------------------------------------------
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS attempts 
        FROM login_attempts 
        WHERE ip_address = ? 
        AND attempt_time > (NOW() - INTERVAL ? MINUTE)
    ");
    $stmt->bind_param("si", $ip, $lockout_time);
    $stmt->execute();
    $stmt->bind_result($attempts);
    $stmt->fetch();
    $stmt->close();

    if ($attempts >= $max_attempts) {
        $_SESSION['toast'] = [
            'type'    => 'error',
            'message' => "Too many failed attempts. Please try again after 15 minutes."
        ];

        recordAudit($conn, 0, 'system', "IP $ip locked due to too many failed login attempts", 'locked');
        header("Location: index.php");
        exit;
    }

    //----------------------------------------------------
    // 2. CHECK IF USER EXISTS
    //----------------------------------------------------
    $stmt = $conn->prepare("SELECT * FROM santamaria_employees WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // USER FOUND
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        //----------------------------------------------------
        // 3. VERIFY PASSWORD
        //----------------------------------------------------
        if (password_verify($password, $user['password'])) {

            //----------------------------------------------------
            // 4. GENERATE AND SAVE NEW SESSION TOKEN
            //----------------------------------------------------
            $new_token = bin2hex(random_bytes(32));

            $update = $conn->prepare("UPDATE santamaria_employees SET session_token = ? WHERE id = ?");
            $update->bind_param("si", $new_token, $user['id']);
            $update->execute();
            $update->close();

            $_SESSION['session_token'] = $new_token;

            //----------------------------------------------------
            // 5. SET SESSION USER
            //----------------------------------------------------
            $_SESSION['user'] = $user;

            //----------------------------------------------------
            // 6. CLEAR IP FAILED ATTEMPTS
            //----------------------------------------------------
            $clear = $conn->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
            $clear->bind_param("s", $ip);
            $clear->execute();
            $clear->close();

            //----------------------------------------------------
            // 7. RECORD SUCCESS LOGIN
            //----------------------------------------------------
            recordAudit($conn, $user['id'], strtolower($user['position']), 'Logged in', 'success');

            //----------------------------------------------------
            // 8. LOAD TIMEOUT SETTINGS
            //----------------------------------------------------
            $stmt = $pdo->prepare("SELECT logout_time FROM user_timeout_settings WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            if ($stmt->rowCount() > 0) {
                $settings = $stmt->fetch(PDO::FETCH_ASSOC);

                $_SESSION['logout_time'] = $settings['logout_time'];
            } else {

                $_SESSION['logout_time'] = 15;
            }

            //----------------------------------------------------
            // 9. REDIRECT BASED ON ROLE
            //----------------------------------------------------
            switch (strtolower($user['position'])) {
                case 'treasurer':
                    header("Location: treasurer.php");
                    exit;
                case 'collector':
                    header("Location: collectors.php");
                    exit;
                case 'accountant':
                    header("Location: accountant.php");
                    exit;
                case 'admin':
                    header("Location: admin.php");
                    exit;
                default:
                    $_SESSION['toast'] = ['type' => 'error', 'message' => 'Unknown employee position.'];
                    header("Location: index.php");
                    exit;
            }
        }

        //--------------------------------------------------------
        // ❌ WRONG PASSWORD
        //--------------------------------------------------------
        $log = $conn->prepare("INSERT INTO login_attempts (email, ip_address) VALUES (?, ?)");
        $log->bind_param("ss", $email, $ip);
        $log->execute();
        $log->close();

        recordAudit($conn, $user['id'], strtolower($user['position']), 'Failed login attempt', 'failed');

        $remaining = $max_attempts - ($attempts + 1);

        $_SESSION['toast'] = [
            'type' => $remaining > 0 ? 'warning' : 'error',
            'message' => $remaining > 0
                ? "Invalid email or password. You have $remaining attempt(s) left."
                : "Too many failed attempts. Please try again after 15 minutes."
        ];


        header("Location: index.php");
        exit;
    }

    //--------------------------------------------------------
    // ❌ USER NOT FOUND
    //--------------------------------------------------------
    $log = $conn->prepare("INSERT INTO login_attempts (email, ip_address) VALUES (?, ?)");
    $log->bind_param("ss", $email, $ip);
    $log->execute();
    $log->close();

    recordAudit($conn, 0, 'unknown', "Failed login attempt for email: $email", 'failed');

    $remaining = $max_attempts - ($attempts + 1);

    $_SESSION['toast'] = [
        'type' => $remaining > 0 ? 'warning' : 'error',
        'message' => $remaining > 0
            ? "Invalid email or password. You have $remaining attempt(s) left."
            : "Too many failed attempts. Please try again after 15 minutes."
    ];


    header("Location: index.php");
    exit;
}
