<?php
session_start();

// Database configuration
include 'backend/db_config.php';

// Check if user is logged in (adjust based on your authentication system)
if (!isset($_SESSION['email'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

// Create database connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $required_fields = ['position', 'report_type', 'report_date', 'report_file'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field]) && $field !== 'report_file') {
            echo json_encode(['success' => false, 'message' => "Please fill in all required fields"]);
            exit;
        }
    }

    // Handle file upload
    $upload_dir = 'uploads/reports/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_name = $_FILES['report_file']['name'];
    $file_tmp = $_FILES['report_file']['tmp_name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_ext = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];

    if (!in_array($file_ext, $allowed_ext)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed types: PDF, DOC, DOCX, XLS, XLSX']);
        exit;
    }

    // Generate unique filename
    $new_filename = uniqid('report_', true) . '.' . $file_ext;
    $file_path = $upload_dir . $new_filename;

    if (!move_uploaded_file($file_tmp, $file_path)) {
        echo json_encode(['success' => false, 'message' => 'File upload failed']);
        exit;
    }

    // Prepare data for database insertion
    $report_data = [
        'position' => $_POST['position'],
        'report_type' => $_POST['report_type'],
        'report_date' => $_POST['report_date'],
        'description' => $_POST['description'] ?? null,
        'file_path' => $file_path,
        'user_email' => $_SESSION['email']
    ];

    try {
        // Insert into database
        $stmt = $pdo->prepare("
            INSERT INTO reports 
            (position, report_type, report_date, description, file_path, user_email) 
            VALUES 
            (:position, :report_type, :report_date, :description, :file_path, :user_email)
        ");

        $stmt->execute($report_data);

        echo json_encode([
            'success' => true,
            'message' => 'Report submitted successfully',
            'report_id' => $pdo->lastInsertId()
        ]);
    } catch (PDOException $e) {
        // Delete the uploaded file if database insertion fails
        unlink($file_path);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
