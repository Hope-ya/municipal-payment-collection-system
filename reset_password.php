<?php
session_start();
include 'backend/db_config_notpdo.php';

$rawToken = $_GET['token'] ?? '';

if (!$rawToken) {
    die("Invalid reset token.");
}

// Hash the incoming token (same as forgot_password.php)
$hashedToken = hash("sha256", $rawToken);

// Fetch user using hashed token
$stmt = $conn->prepare("SELECT id, token_expiry FROM santamaria_employees WHERE password_reset_token = ?");
$stmt->bind_param("s", $hashedToken);
$stmt->execute();
$res = $stmt->get_result();

// ----------------------------------------------------
// INVALID TOKEN PAGE
// ----------------------------------------------------
if ($res->num_rows === 0) {

    // Fetch org details for logo
    $orgQuery = $conn->query("SELECT org_logo FROM organization_info WHERE id = 1 LIMIT 1");
    $org = $orgQuery->fetch_assoc();
    $orgLogo = $org['org_logo'] ?? "logo/logo.png";

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title>Invalid or Expired Link</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="icon" type="image/png" href="<?php echo htmlspecialchars($orgLogo); ?>" />
    </head>
    <body class="flex items-center justify-center min-h-screen bg-gray-100"
        style="background: linear-gradient(135deg, #92B1F5, #2563EB, #2159E2, #1D4ED8, #1E3A8A);">

        <div class="bg-white/90 backdrop-blur-md p-8 rounded-xl shadow-xl w-80 text-center space-y-5">
            <h2 class="text-2xl font-semibold text-red-600">Invalid or Expired Link</h2>
            <p class="text-gray-600 text-sm">
                The password reset link is invalid or has already been used.
                <br><br>
                Please request a new password reset link.
            </p>

            <a href="forgot_password.php"
                class="inline-block w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
                Request New Link
            </a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$user = $res->fetch_assoc();
$userId = $user['id'];

// ----------------------------------------------------
// CHECK EXPIRY
// ----------------------------------------------------
if (strtotime($user['token_expiry']) < time()) {

    // Fetch org details
    $orgQuery = $conn->query("SELECT org_logo FROM organization_info WHERE id = 1 LIMIT 1");
    $org = $orgQuery->fetch_assoc();
    $orgLogo = $org['org_logo'] ?? "logo/logo.png";

    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title>Reset Link Expired</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="icon" type="image/png" href="<?php echo htmlspecialchars($orgLogo); ?>" />
    </head>
    <body class="flex items-center justify-center min-h-screen bg-gray-100"
        style="background: linear-gradient(135deg, #92B1F5, #2563EB, #2159E2, #1D4ED8, #1E3A8A);">

        <div class="bg-white/90 backdrop-blur-md p-8 rounded-xl shadow-xl w-80 text-center space-y-5">
            <h2 class="text-2xl font-semibold text-yellow-600">Reset Link Expired</h2>
            <p class="text-gray-600 text-sm">
                Your password reset link has expired.
                <br><br>
                Please request a new reset link.
            </p>

            <a href="forgot_password.php"
                class="inline-block w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
                Request New Link
            </a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ----------------------------------------------------
// HANDLE PASSWORD UPDATE
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $newPass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("
        UPDATE santamaria_employees 
        SET password = ?, password_reset_token = NULL, token_expiry = NULL 
        WHERE id = ?
    ");
    $stmt->bind_param("si", $newPass, $userId);
    $stmt->execute();

    $_SESSION['toast'] = [
        'message' => 'Password reset successfully. You may now login.',
        'type' => 'success'
    ];

    header("Location: index.php");
    exit;
}

// ----------------------------------------------------
// Fetch org info for the page
// ----------------------------------------------------
$orgQuery = $conn->query("SELECT org_name, org_logo FROM organization_info WHERE id = 1 LIMIT 1");
$org = $orgQuery->fetch_assoc();

$orgName = $org['org_name'] ?? "Organization";
$orgLogo = $org['org_logo'] ?? "logo/logo.png";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="<?php echo htmlspecialchars($orgLogo); ?>" />
</head>

<body class="flex items-center justify-center min-h-screen"
    style="background: linear-gradient(135deg, #92B1F5, #2563EB, #2159E2, #1D4ED8, #1E3A8A);">

    <div class="bg-white/90 backdrop-blur-md p-8 rounded-xl shadow-xl w-80 text-center space-y-5">
        <h2 class="text-2xl font-semibold">Reset Password</h2>

        <form method="POST" class="space-y-3" autocomplete="new-password">
            <div class="relative">
                <input id="password" type="password" name="password" placeholder="Enter new password" required
                    class="w-full pr-10 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />

                <button id="togglePassword" type="button"
                    class="absolute right-2 top-1/2 -translate-y-1/2 p-1 rounded">
                    <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 block" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7S3.732 16.057 2.458 12z" />
                    </svg>

                    <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.964 9.964 0 012.223-3.434" />
                        <path d="M6.6 6.6A9.953 9.953 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.99 9.99 0 01-3.14 4.554" />
                        <path d="M3 3l18 18" />
                    </svg>
                </button>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700">
                Update Password
            </button>
        </form>
    </div>

    <script>
        const pwd = document.getElementById("password");
        const toggle = document.getElementById("togglePassword");
        const eyeOpen = document.getElementById("eyeOpen");
        const eyeClosed = document.getElementById("eyeClosed");

        toggle.addEventListener("click", () => {
            if (pwd.type === "password") {
                pwd.type = "text";
                eyeOpen.classList.add("hidden");
                eyeClosed.classList.remove("hidden");
            } else {
                pwd.type = "password";
                eyeOpen.classList.remove("hidden");
                eyeClosed.classList.add("hidden");
            }
        });
    </script>

</body>

</html>
