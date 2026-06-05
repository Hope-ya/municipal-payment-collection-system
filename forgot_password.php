<?php
// forgot_password.php
session_start();
include 'backend/db_config_notpdo.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';



if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // -------------------------------------------
    // 1) Basic rate limiting: max 5 requests per hour per IP
    // -------------------------------------------
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $limitTime = date("Y-m-d H:i:s", strtotime("-1 hour"));

    // robustly check and handle DB errors
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as attempts 
                                FROM password_reset_requests 
                                WHERE ip_address = ? AND created_at > ?");
        $stmt->bind_param("ss", $ip, $limitTime);
        $stmt->execute();
        $rateRow = $stmt->get_result()->fetch_assoc();
        $attempts = isset($rateRow['attempts']) ? (int)$rateRow['attempts'] : 0;
        $stmt->close();
    } catch (Exception $e) {
        // if rate check fails, default to 0 attempts (fail open conservatively)
        $attempts = 0;
    }

    if ($attempts >= 5) {
        $_SESSION['toast'] = [
            'message' => 'Too many requests. Try again later.',
            'type' => 'error'
        ];
        header("Location: forgot_password.php");
        exit;
    }

    // Log this request (best-effort; don't block on failure)
    try {
        $ins = $conn->prepare("INSERT INTO password_reset_requests (ip_address, created_at) VALUES (?, NOW())");
        $ins->bind_param("s", $ip);
        $ins->execute();
        $ins->close();
    } catch (Exception $e) {
        // ignore logging errors
    }

    // -------------------------------------------
    // 2) Email enumeration protection: always respond with generic message
    // -------------------------------------------
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $genericResponse = [
        'message' => 'If the email is registered, a reset link has been sent.',
        'type' => 'success'
    ];

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // small optimization: don't reveal whether email exists, but reject obviously invalid email locally
        $_SESSION['toast'] = [
            'message' => 'Please enter a valid email address.',
            'type' => 'error'
        ];
        header("Location: forgot_password.php");
        exit;
    }

    // -------------------------------------------
    // 3) Lookup user by registered email (do not reveal existence)
    // -------------------------------------------
    $stmt = $conn->prepare("SELECT id, firstname, lastname, recovery_email 
                            FROM santamaria_employees 
                            WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        // Always respond generically — do not reveal if nothing was found
        $_SESSION['toast'] = $genericResponse;
        header("Location: forgot_password.php");
        exit;
    }

    $user = $res->fetch_assoc();
    $stmt->close();

    $userid = $user['id'];
    $firstname = $user['firstname'] ?? '';
    $lastname = $user['lastname'] ?? '';
    $recoveryEmail = $user['recovery_email'] ?? '';

    // If there's no recovery email for this account, behave generically and stop further processing.
    if (empty($recoveryEmail) || !filter_var($recoveryEmail, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['toast'] = $genericResponse;
        header("Location: forgot_password.php");
        exit;
    }

    // -------------------------------------------
    // 4) Secure token generation & hashed storage
    // -------------------------------------------
    // raw token goes into the emailed link; the hashed token is stored in DB
    try {
        $rawToken = bin2hex(random_bytes(32));         // 64 hex chars
    } catch (Exception $e) {
        // fallback if random_bytes fails
        $rawToken = bin2hex(openssl_random_pseudo_bytes(32));
    }
    $hashedToken = hash("sha256", $rawToken);
    $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    // Store hashed token and expiry using prepared statement
    try {
        $up = $conn->prepare("UPDATE santamaria_employees 
                              SET password_reset_token = ?, token_expiry = ?
                              WHERE id = ?");
        $up->bind_param("ssi", $hashedToken, $expiry, $userid);
        $up->execute();
        $up->close();
    } catch (Exception $e) {
        // If DB update fails, still return generic response (do not reveal details)
        $_SESSION['toast'] = $genericResponse;
        header("Location: forgot_password.php");
        exit;
    }

    // generate reset link with raw token
    // NOTE: update hostname/path for production environment
    $resetLink = "http://localhost/treasury/reset_password.php?token=" . urlencode($rawToken);

    // -------------------------------------------
    // 5) Prepare & Send Email (PHPMailer)
    // -------------------------------------------
    // Fetch organization info for branding (best-effort)
    try {
        $orgQuery = $conn->query("SELECT org_name, org_logo FROM organization_info WHERE id = 1 LIMIT 1");
        $org = $orgQuery ? $orgQuery->fetch_assoc() : null;
    } catch (Exception $e) {
        $org = null;
    }
    $orgName = $org['org_name'] ?? "Organization Name";
    $orgLogo = $org['org_logo'] ?? "logo/logo.png";

    // Send email (do not fail publicly if mail fails)
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        // TODO: move credentials to .env or config file for security
        $mail->Username = '0322-2198@lspu.edu.ph';
        $mail->Password = 'ztmq ymqi fque encr';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('0322-2198@lspu.edu.ph', 'Municipal Payment Portal');
        $mail->addAddress($recoveryEmail);
        $mail->Subject = 'Password Reset Request';
        $mail->isHTML(true);

        // embedded logo handling (best-effort)
        $cidLogo = 'orglogo';
        $logoCandidate = trim($orgLogo);
        if (!empty($logoCandidate) && file_exists($logoCandidate)) {
            $logoPath = $logoCandidate;
        } elseif (!empty($logoCandidate) && filter_var($logoCandidate, FILTER_VALIDATE_URL)) {
            // try to fetch remote logo to temp file
            $tmp = sys_get_temp_dir() . '/org_logo_' . uniqid() . '.png';
            $content = @file_get_contents($logoCandidate);
            if ($content !== false) {
                file_put_contents($tmp, $content);
                $logoPath = $tmp;
            } else {
                $logoPath = 'logo/logo.png';
            }
        } else {
            $logoPath = 'logo/logo.png';
        }

        // try to attach embedded image if available
        try {
            if (file_exists($logoPath)) {
                $mail->addEmbeddedImage($logoPath, $cidLogo);
            }
        } catch (Exception $e) {
            // ignore embed failure
        }

        // HTML email body
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
    .btn { display:inline-block; text-decoration:none; padding:12px 22px; border-radius:8px; font-weight:600; background:#3b43ff; color:#ffffff !important; }
  </style>
</head>
<body>
  <div class="container">
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
      <p>Hello <strong>' . htmlspecialchars($firstname . ' ' . $lastname, ENT_QUOTES, 'UTF-8') . '</strong>,</p>
      <p>We received a request to reset your password for the <strong>Municipal Payment Portal of ' . htmlspecialchars($orgName, ENT_QUOTES, 'UTF-8') . '</strong>.</p>
      <p>If you made this request, click the button below to set a new password. This link will expire in 10 minutes for security reasons.</p>
      <p style="text-align:center;margin:20px 0;">
        <a href="' . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . '" class="btn" target="_blank" rel="noopener">Reset Password</a>
      </p>
      <p>If you did not request a password reset, please ignore this email.</p>
      <p>Thank you,<br><strong>Municipal Payment Portal Administrator</strong></p>
    </div>
  </div>
</body>
</html>';

        // send the email (if this fails, we do not reveal details to the user)
        $mail->send();

    } catch (Exception $e) {
        // suppress details — do not expose SMTP or internal errors to the user
        // error_log("Password reset email send error: " . $e->getMessage());
    }

    // -------------------------------------------
    // 6) Final: show same generic response to avoid enumeration
    // -------------------------------------------
    $_SESSION['toast'] = $genericResponse;
    header("Location: forgot_password.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <?php
        // get org logo for favicon (best-effort)
        try {
            $orgQuery = $conn->query("SELECT org_name, org_logo FROM organization_info WHERE id = 1 LIMIT 1");
            $org = $orgQuery ? $orgQuery->fetch_assoc() : null;
        } catch (Exception $e) {
            $org = null;
        }
        $orgLogo = $org['org_logo'] ?? "logo/logo.png";
    ?>
    <link rel="icon" type="image/png" href="<?php echo htmlspecialchars($orgLogo); ?>" />
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100" style="background: linear-gradient(135deg, #92B1F5, #2563EB, #2159E2, #1D4ED8, #1E3A8A, #1E3A8A);">

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="fixed inset-0 bg-gray-200/70 backdrop-blur-sm hidden z-[9999] flex items-center justify-center">
        <div class="flex flex-col items-center">
            <svg class="animate-spin h-10 w-10 text-blue-600 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <p class="text-gray-700 text-sm font-medium">Processing, please wait...</p>
        </div>
    </div>

    <div class="bg-white/90 backdrop-blur-md p-8 rounded-xl shadow-xl w-80 text-center space-y-5 transition-all duration-300 hover:-translate-y-1">
        <h2 class="text-2xl font-semibold">Forgot Password</h2>
        <p class="text-gray-600 text-sm">Enter your registered email and we’ll send the reset link to your recovery email.</p>

        <form id="forgot-form" method="POST" class="space-y-3" novalidate>
            <input type="email" name="email" placeholder="Registered email" required
                class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />

            <button id="submit-btn" type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
                Send Reset Link
            </button>
        </form>

        <div class="text-sm">
            <a href="index.php" class="text-blue-600 hover:underline">Back to Login</a>
        </div>
    </div>

    <!-- Toast -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col items-end"></div>

    <script>
        const loadingOverlay = document.getElementById('loading-overlay');
        const forgotForm = document.getElementById('forgot-form');

        forgotForm.addEventListener('submit', () => {
            loadingOverlay.classList.remove('hidden');
        });

        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const colors = { success: 'bg-green-500', error: 'bg-red-500', warning: 'bg-yellow-500' };
            const toast = document.createElement('div');
            toast.className = `p-4 mb-2 rounded-lg text-white ${colors[type]}`;
            toast.textContent = message;
            container.appendChild(toast);
            setTimeout(() => toast.remove(), 4000);
        }

        <?php if (isset($_SESSION['toast'])): ?>
            document.addEventListener("DOMContentLoaded", function() {
                loadingOverlay.classList.add('hidden'); // hide overlay after redirect
                showToast("<?= htmlspecialchars($_SESSION['toast']['message'], ENT_QUOTES) ?>", "<?= htmlspecialchars($_SESSION['toast']['type'], ENT_QUOTES) ?>");
            });
            <?php unset($_SESSION['toast']); ?>
        <?php endif; ?>
    </script>
</body>
</html>
