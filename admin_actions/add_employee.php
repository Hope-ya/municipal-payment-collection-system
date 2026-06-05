<?php
// add_employee.php

// Database connection (non-PDO)
include '../backend/db_config_notpdo.php';

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

header('Content-Type: application/json');

// === SMTP Configuration ===
$smtpHost = 'smtp.gmail.com';
$smtpPort = 587;
$smtpEncryption = PHPMailer::ENCRYPTION_STARTTLS; // TLS
$smtpUsername = '0322-2198@lspu.edu.ph';           // your sending email
$smtpPassword = 'ztmq ymqi fque encr';             // Gmail App Password
$fromEmail = $smtpUsername;
$fromName = 'Payment Portal Administrator';
// ===========================

// ------------------------------- CHANGE THIS ------------------------------------
$portalLink = 'http://localhost/treasury/index.php'; // login button target
// ------------------------------- CHANGE THIS ------------------------------------

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  echo json_encode(["status" => "error", "message" => "Invalid request method."]);
  exit();
}

// === Sanitize Inputs ===
$firstname = mysqli_real_escape_string($conn, $_POST["firstname"] ?? '');
$lastname  = mysqli_real_escape_string($conn, $_POST["lastname"] ?? '');
$position  = mysqli_real_escape_string($conn, $_POST["position"] ?? '');
$email     = mysqli_real_escape_string($conn, $_POST["email"] ?? '');
$password  = mysqli_real_escape_string($conn, $_POST["password"] ?? '');
$recipientEmail = mysqli_real_escape_string($conn, $_POST["recipient-email"] ?? '');


// === Basic Validation ===
if (empty($firstname) || empty($lastname) || empty($position) || empty($email) || empty($password) || empty($recipientEmail)) {
  echo json_encode(["status" => "error", "message" => "All fields are required."]);
  $conn->close();
  exit();
}


// === Hash Password for DB ===
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// === Fetch Org Settings (id = 1) ===
$orgName = "Organization Name";
$orgLogo = "../logo/logo.png";
$safeOrgLogo = $orgLogo; // fallback

$orgQuery = $conn->query("SELECT org_name, org_logo FROM organization_info WHERE id = 1 LIMIT 1");
if ($orgQuery && $orgQuery->num_rows > 0) {
  $org = $orgQuery->fetch_assoc();
  $orgName = $org['org_name'] ?? $orgName;
  $orgLogo = $org['org_logo'] ?? $orgLogo;

  // Detect if logo is stored as binary (BLOB) or just a file path
  if (!empty($orgLogo)) {
    if (!str_contains($orgLogo, '/') && !str_contains($orgLogo, '\\')) {
      // Binary logo from BLOB (check MIME type)
      $imageInfo = @getimagesizefromstring($orgLogo);
      if ($imageInfo !== false) {
        $mimeType = $imageInfo['mime']; // e.g. "image/png" or "image/jpeg"
        $base64Logo = base64_encode($orgLogo);
        $safeOrgLogo = "data:$mimeType;base64,$base64Logo";
      } else {
        $safeOrgLogo = "    ../logo/logo.png"; // fallback if detection fails
      }
    } else {
      // Path or URL
      $safeOrgLogo = htmlspecialchars($orgLogo, ENT_QUOTES, 'UTF-8');
    }
  } else {
    $safeOrgLogo = "../logo/logo.png"; // fallback
  }
}

// === Insert into Database ===
$sql = "INSERT INTO santamaria_employees (firstname, lastname, position, email, password, created_at, updated_at)
        VALUES ('$firstname', '$lastname', '$position', '$email', '$hashed_password', NOW(), NOW())";

if ($conn->query($sql) !== TRUE) {
  echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
  $conn->close();
  exit();
}

// === Determine Manual Path Based on Position ===
$posLower = strtolower($position);
$manualMap = [
  'admin'      => '../manuals/user_manual_admin.pdf',
  'collector'  => '../manuals/user_manual_collector.pdf',
  'treasurer'  => '../manuals/user_manual_treasurer.pdf',
  'accountant' => '../manuals/user_manual_accountant.pdf'
];
$manualPath = $manualMap[$posLower] ?? null;

// === Prepare PHPMailer and Send Email ===
$mail = new PHPMailer(true);
try {
  // SMTP setup
  $mail->isSMTP();
  $mail->Host       = $smtpHost;
  $mail->SMTPAuth   = true;
  $mail->Username   = $smtpUsername;
  $mail->Password   = $smtpPassword;
  $mail->SMTPSecure = $smtpEncryption;
  $mail->Port       = $smtpPort;

  // Recipients
  $mail->setFrom($fromEmail, $fromName);
  $mail->addAddress($recipientEmail, $firstname . ' ' . $lastname);

  // Attach manual if exists
  if ($manualPath && file_exists($manualPath)) {
    $mail->addAttachment($manualPath);
  }

  // === Embed the organization logo in the email ===
  // === Embed the organization logo in the email ===
$cidLogo = 'orglogo';

// Check what kind of logo we got from DB
if (filter_var($safeOrgLogo, FILTER_VALIDATE_URL)) {
    // Case 1: It's a full URL (e.g., https://domain.com/logo.png)
    // Download temporarily so PHPMailer can embed it
    $tempLogoPath = sys_get_temp_dir() . '/org_logo_' . uniqid() . '.png';
    $logoContent = @file_get_contents($safeOrgLogo);
    if ($logoContent !== false) {
        file_put_contents($tempLogoPath, $logoContent);
        $logoPath = $tempLogoPath;
    } else {
        $logoPath = '../logo/logo.png'; // fallback
    }

} elseif (file_exists($safeOrgLogo)) {
    // Case 2: It’s a valid local path
    $logoPath = $safeOrgLogo;

} elseif (file_exists('../' . ltrim($safeOrgLogo, './'))) {
    // Case 3: Stored as relative path (e.g., "uploads/logo.png")
    $logoPath = '../' . ltrim($safeOrgLogo, './');

} else {
    // Case 4: Fallback if nothing works
    $logoPath = '../logo/logo.png';
}

$mail->addEmbeddedImage($logoPath, $cidLogo);


  // Email content
  $mail->isHTML(true);
  $mail->Subject = "Welcome to the Municipal Payment Portal of $orgName";

  $mail->Body = '
<!doctype html>
<html>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <style>
    body { margin:0; padding:0; background-color:#f9fafb; font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; color:#111827; }
    .container { max-width:600px; margin:20px auto; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 6px 18px rgba(17,24,39,0.08); }
    .header { background:#3b43ff; color:#ffffff; padding:20px; display:flex; align-items:center; gap:16px; }
    .logo-container { flex-shrink:0; display:flex; align-items:center; justify-content:center; width:56px; height:56px; border-radius:9999px; background:#ffffff; overflow:hidden; }
    .logo-container img { width:56px; height:56px; object-fit:cover; border-radius:9999px; display:block; }
    .header-text { margin-left:20px; line-height:1.3; }
    .content { padding:22px; color:#111827; line-height:1.6; font-size:15px; }
    .cred { background:#f3f4f6; padding:12px; border-radius:8px; margin:12px 0; font-size:14px; }
    .btn { display:inline-block; text-decoration:none; padding:12px 22px; border-radius:8px; font-weight:600; background:#3b43ff; color:#ffffff !important; }
    @media only screen and (max-width: 640px) {
      .header { flex-direction:column; text-align:center; padding:16px; }
      .header-text { margin-left:0; margin-top:8px; }
      .logo-container { width:48px; height:48px; }
      .logo-container img { width:48px; height:48px; }
      .content { padding:16px; font-size:14px; }
    }
  </style>
</head>
<body>
  <div class="container" role="article" aria-label="Welcome email">
    <div class="header">
      <div class="logo-container">
        <img src="cid:' . $cidLogo . '" alt="Logo">
      </div>
      <div class="header-text">
        <div style="font-size:18px;font-weight:700;margin:0;color:#ffffff;">
          Municipal Payment Portal of<br>' . htmlspecialchars($orgName, ENT_QUOTES, 'UTF-8') . '
        </div>
      </div>
    </div>
 
    <div class="content">
      <p style="margin:0 0 12px 0;">Good day, <strong>' . htmlspecialchars($firstname . ' ' . $lastname, ENT_QUOTES, 'UTF-8') . '</strong>!</p>

      <p style="margin:0 0 12px 0;">This is the Administrator of the <strong>Municipal Payment Portal of ' . htmlspecialchars($orgName, ENT_QUOTES, 'UTF-8') . '</strong>.</p>

      <p style="margin:0 0 12px 0;">You have been registered in the system as a/an <strong>' . htmlspecialchars($position, ENT_QUOTES, 'UTF-8') . '</strong>. These are your login credentials:</p>

      <div class="cred">
        <strong>Email:</strong> ' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '<br>
        <strong>Password:</strong> ' . htmlspecialchars($password, ENT_QUOTES, 'UTF-8') . '
      </div>

      <p style="margin:0 0 12px 0;">For your security, please keep this email confidential. It is strongly advised to change your password upon your initial login.</p>

      <p style="margin:0 0 12px 0;">To get you started with the <strong>Municipal Payment Portal of ' . htmlspecialchars($orgName, ENT_QUOTES, 'UTF-8') . '</strong>, please see the attached user manual for guidelines on how to use the payment portal.</p>
      
      <p style="margin:0 0 20px 0;">Thank you and have a good day.</p>

      <p style="margin:0 0 20px 0;">Click the button below to redirect to the payment portal.</p>

      <p style="text-align:center;margin:0 0 8px 0;">
        <a href="' . htmlspecialchars($portalLink, ENT_QUOTES, 'UTF-8') . '" class="btn" target="_blank" rel="noopener">Login Now</a>
      </p>
    </div>
  </div>
</body>
</html>
';

  $mail->send();

  echo json_encode(["status" => "success", "message" => "Employee added successfully and email sent!"]);
} catch (Exception $e) {
  $errorInfo = $mail->ErrorInfo ?? $e->getMessage();
  echo json_encode(["status" => "warning", "message" => "Employee added but email not sent: " . $errorInfo]);
}

// Close DB
$conn->close();
exit();
 