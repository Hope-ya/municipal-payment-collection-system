<?php
session_start();

include 'backend/db_config_notpdo.php';

// If user was logged out because of another session login
if (!empty($_GET['session_expired'])) {
    $_SESSION['toast'] = [
        'type' => 'warning',
        'message' => "You were logged out because your account was logged in from another device."
    ];
}

// Normal logout message
if (!empty($_GET['logout'])) { 
    $_SESSION['toast'] = [
        'type' => 'success',
        'message' => "You have been logged out successfully."
    ];
}

// Fetch org settings (id = 1, since only one row is needed)
$orgQuery = $conn->query("SELECT org_name, org_logo FROM organization_info WHERE id = 1 LIMIT 1");
$org = $orgQuery->fetch_assoc();

// Default fallback values
$orgName = $org['org_name'] ?? "Organization Name";
$orgLogo = $org['org_logo'] ?? "logo/logo.png";
?>
<!DOCTYPE html>
<html lang="en">
 
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($orgName); ?> - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="<?php echo htmlspecialchars($orgLogo); ?>" />
</head>

<body class="min-h-screen flex items-center justify-center"
    style="background: linear-gradient(135deg, #92B1F5, #2563EB, #2159E2, #1D4ED8, #1E3A8A, #1E3A8A);">
    <div class="bg-white/90 backdrop-blur-md p-8 rounded-xl shadow-xl w-80 text-center space-y-5 transition-all duration-300 hover:-translate-y-1">
        <img src="<?php echo htmlspecialchars($orgLogo); ?>" alt="Logo" id="sidebar-logo" class="w-20 mx-auto">
        <h2 class="text-2xl font-semibold text-gray-800">Login</h2>

        <form action="login.php" method="POST" class="space-y-4">
            <!-- Email -->
            <input type="text" name="email" placeholder="Email" required autofocus
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />

            <!-- Password with eye toggle -->
            <div class="relative">
                <input type="password" id="password" name="password"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Password" required>

                <button type="button" id="togglePassword"
                    class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-600 hover:text-gray-800">
                    <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 
                            4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.956 
                            9.956 0 012.293-4.157m3.367-2.474A9.956 
                            9.956 0 0112 5c4.478 0 8.268 2.943 9.542 
                            7a9.955 9.955 0 01-4.043 5.176M15 
                            12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 3l18 18" />
                    </svg>
                </button>
            </div>

            <!-- Forgot Password link -->
            <div class="text-right">
                <a href="forgot_password.php" class="text-sm text-blue-600 hover:underline">Forgot Password?</a>
            </div>

            <!-- Login button -->
            <button type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded-lg font-medium hover:bg-blue-700 transition">
                Login
            </button>
        </form>
    </div>

    <!-- Toast Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col items-end"></div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // If redirected after logout, clear stored section
            const params = new URLSearchParams(window.location.search);
            if (params.get("logout") === "1") {
                localStorage.removeItem("activeSection");
            }
        });
    </script>


    <script>
        const passwordInput = document.getElementById("password");
        const toggleButton = document.getElementById("togglePassword");
        const eyeOpen = document.getElementById("eyeOpen");
        const eyeClosed = document.getElementById("eyeClosed");

        toggleButton.addEventListener("click", () => {
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                eyeOpen.classList.add("hidden");
                eyeClosed.classList.remove("hidden"); 
            } else {
                passwordInput.type = "password";
                eyeOpen.classList.remove("hidden");
                eyeClosed.classList.add("hidden");
            }
        });

        // Toast
        function showToast(message, type = 'success', reloadAfterSuccess = true) {
            const container = document.getElementById('toast-container');
            const colors = {
                success: 'bg-green-500',
                error: 'bg-red-500',
                warning: 'bg-yellow-500',
                info: 'bg-blue-500'
            };
            const toast = document.createElement('div');
            toast.className = `flex items-center gap-3 p-4 mb-2 rounded-lg shadow-lg text-white transform transition-all duration-500 ease-in-out opacity-0 -translate-x-5 ${colors[type]}`;
            toast.innerHTML = `<span>${message}</span>`;
            container.appendChild(toast);
            requestAnimationFrame(() => toast.classList.replace('opacity-0', 'opacity-100'));
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }

        <?php if (isset($_SESSION['toast'])): ?>
            document.addEventListener("DOMContentLoaded", function() {
                showToast("<?= $_SESSION['toast']['message'] ?>", "<?= $_SESSION['toast']['type'] ?>");
            });
            <?php unset($_SESSION['toast']); ?>
        <?php endif; ?>
    </script>
</body>
 
</html>