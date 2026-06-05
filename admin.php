<?php
session_start();
include 'backend/db_config_notpdo.php';
include 'audit_actions/audit_functions.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['position'] !== 'Admin') {
    header("Location: index.php");
    exit();
}
$user = $_SESSION['user'];

// Store email in session if available
if (isset($_SESSION['user']['email'])) {
    $_SESSION['email'] = $_SESSION['user']['email'];
} else {
    // Redirect if email is missing
    header("Location: index.php");
    exit();
}

// Store ID in session if available
if (isset($_SESSION['user']['id'])) {
    $_SESSION['id'] = $_SESSION['user']['id'];
} else {
    // Redirect if ID is missing
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user']['id'];
$current_token = $_SESSION['session_token'];

// Fetch token from database
$stmt = $conn->prepare("SELECT session_token FROM santamaria_employees WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($db_token);
$stmt->fetch();
$stmt->close();

// If token does not match → logged in somewhere else
if ($db_token !== $current_token) {

    // Record logout due to new login
    recordAudit($conn, $user_id, strtolower($_SESSION['user']['position']), "Logged out (another device login)", "forced");

    session_destroy();

    header("Location: index.php?session_expired=1");
    exit;
}


// --- Preload Receipt Info ---
$agencyName = '';
$treasurerName = '';

// Fetch current values from the database (if exist)
$query = "SELECT agency_name, treasurer_name FROM receipt_info WHERE id = 1 LIMIT 1";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $agencyName = $row['agency_name'] ?? '';
    $treasurerName = $row['treasurer_name'] ?? '';
}




// Fetch org settings (id = 1, since only one row is needed)
$orgQuery = $conn->query("SELECT org_name, org_logo FROM organization_info WHERE id = 1 LIMIT 1");
$org = $orgQuery->fetch_assoc();

// Default fallback values
$orgName = $org['org_name'] ?? "Organization Name";
$orgLogo = $org['org_logo'] ?? "logo/logo.png";


$phone = $_SESSION['user']['phone'] ?? null;
$address = $_SESSION['user']['address'] ?? null;
$recovery_email = isset($user['recovery_email']) ? $user['recovery_email'] : "";


?>


<!DOCTYPE html>
<html lang="en" class="transition-colors duration-300">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admininistrator's Portal | <?php echo htmlspecialchars($orgName); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.2.0/remixicon.css">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

    <!-- Add this in your <head> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Organization Logo as Favicon -->
    <link rel="icon" type="image/png" href="<?php echo htmlspecialchars($orgLogo); ?>" />

    <style>
        main section {
            position: relative;
            z-index: 1;
            width: 100%;
            top: 0;
            left: 0;
        }

        .hidden {
            display: none !important;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        .fade-in {
            opacity: 1;
            transition: opacity 0.3s ease-in-out;
        }

        #auditLogsTable tr {
            background-color: white !important;
            color: #1f2937 !important;
            /* Tailwind gray-800 */
        }

        #auditLogsTable tr:hover {
            background-color: #f3f4f6 !important;
            /* Tailwind gray-100 */
        }
    </style>



</head>

<body class="bg-gray-50 text-gray-900 transition-colors duration-300 overflow-hidden">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside id="sidebar"
            class="bg-gray-100 shadow-md p-4 flex flex-col items-center justify-between transition-all duration-300">
            <div class="w-full">
                <div class="flex items-center space-x-2 mb-4 w-full">
                    <!-- Logo -->
                    <img src="<?php echo htmlspecialchars($orgLogo); ?>" alt="Logo" id="sidebar-logo" class="w-10 h-10">

                    <!-- Text content -->
                    <div>
                        <h2 class="text-xl font-bold sidebar-text leading-tight max-w-[160px]">
                            Municipal <br> Payment Portal
                        </h2>

                        <p class="text-sm text-gray-500 sidebar-text break-words leading-tight">
                            <?php echo htmlspecialchars($orgName); ?>
                        </p>
                    </div>
                </div>

                <br>
                <ul class="w-full text-gray-900">
                    <li class="mb-2">
                        <a href="#" id="dashboard-link"
                            class="flex items-center p-2 rounded hover:bg-gray-300 transition-all w-full">
                            <i class='ri-dashboard-line text-2xl'></i>
                            <span class="ml-2 sidebar-text">Dashboard</span>
                        </a>
                    </li>

                    <li class="mb-2">
                        <a href="#" id="employees-link"
                            class="flex items-center p-2 rounded hover:bg-gray-300 transition-all w-full">
                            <i class='ri-user-3-line text-2xl'></i>
                            <span class="ml-2 sidebar-text">Employees</span>
                        </a>
                    </li>

                    <li class="mb-2">
                        <a href="#" id="reports-link"
                            class="flex items-center p-2 rounded hover:bg-gray-300 transition-all w-full">
                            <i class='ri-file-chart-line text-2xl'></i>
                            <span class="ml-2 sidebar-text">Reports</span>
                        </a>
                    </li>

                    <li class="mb-2">
                        <a href="#" id="lgu-payment-link"
                            class="flex items-center p-2 rounded hover:bg-gray-300 transition-all w-full">
                            <i class='ri-bank-card-line text-2xl'></i>
                            <span class="ml-2 sidebar-text">LGU Payment References</span>
                        </a>
                    </li>

                    <li class="mb-2">
                        <a href="#" id="Audit-Logs-link"
                            class="flex items-center p-2 rounded hover:bg-gray-300 transition-all w-full">
                            <i class='ri-file-search-line text-2xl'></i>
                            <span class="ml-2 sidebar-text">Audit Logs</span>
                        </a>
                    </li>

                    <li class="mb-2">
                        <a href="#" id="settings-link"
                            class="flex items-center p-2 rounded hover:bg-gray-300 transition-all w-full">
                            <i class='ri-settings-3-line text-2xl'></i>
                            <span class="ml-2 sidebar-text">Settings</span>
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <div class="flex-1 flex flex-col">
            <!-- Topbar -->
            <header class="bg-gray-100  shadowbo-md p-4 flex justify-between items-center transition-all relative">
                <h1 class="text-lg font-semibold">Admininistrator's Portal</h1>
                <div class="flex items-center space-x-4 relative">


                    <!-- Profile Button -->
                    <button id="profile-btn" class="p-2 bg-gray-300 rounded flex items-center focus:outline-none">
    <img id="profile-img" src="profile.jpg" alt="Profile" class="w-8 h-8 rounded-full mr-2 hidden">
    
    <!-- Logout icon -->
    <i id="default-profile-icon" class="ri-logout-circle-r-line text-xl"></i>

    <span class="ml-2">
        <?php echo htmlspecialchars($user['firstname']); ?>
        <?php echo htmlspecialchars($user['lastname']); ?>
    </span>
</button>

                    <!-- Dropdown Menu -->
                    <div id="dropdown-menu" class="absolute top-14 right-0 bg-white  shadow-lg rounded-lg w-40 py-2 opacity-0 scale-95 invisible transform transition-all duration-200 z-50">
                        <button id="logout-btn" class="block w-full text-left px-4 py-2 text-gray-700  hover:bg-gray-100 ">
                            Logout
                        </button>
                    </div>


                </div>
            </header>




            <!-- Dashboard Section -->
            <div class="flex-1 p-6">
                <main id="main-content">
                    <section
                        id="dashboard-section"
                        class="mt-1 px-6 flex flex-col items-center">
                        <div
                            class=" w-full max-w-8xl transition-all duration-300 h-[80vh] max-h-screen overflow-y-auto">
                            <h2 class="text-lg sm:text-xl font-bold mb-4 text-gray-900">
                                Administrator's Dashboard | Welcome, Admin
                                <?php echo htmlspecialchars($user['firstname']); ?>
                                <?php echo htmlspecialchars($user['lastname']); ?>!
                            </h2>

                            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 sm:gap-6 min-w-[320px]">

                                <!-- LEFT SIDE -->
                                <div class="lg:col-span-2 space-y-4 sm:space-y-6">

                                    <!-- Summary Cards -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <!-- Total Transactions -->
                                        <div class="p-4 sm:p-6 bg-white text-gray-900 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 flex items-center justify-between">
                                            <div>
                                                <p class="text-xs sm:text-sm text-blue-600">Total Transactions</p>
                                                <h4 class="text-lg sm:text-xl font-bold" id="total-transactions">0</h4>
                                            </div>
                                            <button
                                                class="px-3 py-2 bg-blue-600 text-white text-xs sm:text-sm rounded hover:bg-blue-700"
                                                onclick="openAllSummModal('transactionsAllModal')">
                                                View Details
                                            </button>
                                        </div>

                                        <!-- Total Revenue -->
                                        <div class="p-4 sm:p-6 bg-white text-gray-900 rounded-lg shadow-md hover:shadow-lg transition-all duration-300 flex items-center justify-between">
                                            <div>
                                                <p class="text-xs sm:text-sm text-green-600">Total Revenue</p>
                                                <h4 class="text-lg sm:text-xl font-bold" id="total-revenue">₱0.00</h4>
                                            </div>
                                            <button
                                                class="px-3 py-2 bg-green-600 text-white text-xs sm:text-sm rounded hover:bg-green-700"
                                                onclick="openAllSummModal('revenueAllModal')">
                                                View Details
                                            </button>
                                        </div>

                                    </div>

                                    <!-- Revenue Chart -->
                                    <div class="bg-white p-4 sm:p-6 rounded-lg shadow-md hover:shadow-lg transition-all duration-300">
                                        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-4">
                                            <h3 class="text-base sm:text-lg font-semibold">Revenue Overview</h3>
                                            <div class="flex items-center mt-2">
                                                <label for="chartFilter" class="mr-2 text-xs sm:text-sm text-gray-600">View By:</label>
                                                <select id="chartFilter" class="text-xs sm:text-sm p-2 rounded-md border border-gray-300">
                                                    <option value="daily" selected>Daily</option>
                                                    <option value="weekly">Weekly</option>
                                                    <option value="monthly">Monthly</option>
                                                    <option value="quarterly">Quarterly</option>
                                                    <option value="yearly">Yearly</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="relative w-full aspect-[16/9]">
                                            <canvas id="revenueChart" class="absolute inset-0 w-full h-full"></canvas>
                                        </div>

                                    </div>
                                </div>


                                <div class="lg:col-span-2 space-y-6">

                                    <!-- Tabs -->
                                    <div class="border-b border-gray-300">
                                        <nav class="flex space-x-4">
                                            <button id="tabTopParticulars" class="px-4 py-2 text-sm font-medium text-blue-600 border-b-2 border-blue-600">Top Particulars</button>
                                            <button id="tabCollectorOverview" class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700">Top Collectors</button>
                                        </nav>
                                    </div>

                                    <!-- Top Particulars Content -->
                                    <div id="contentTopParticulars">
                                        <div class="p-6 bg-white rounded-lg shadow-md hover:shadow-lg transition">

                                            <!-- Filters -->
                                            <!-- Top Particulars Header and Filters -->
                                            <div class="mb-4">
                                                <!-- Title -->
                                                <h3 class="text-lg font-semibold mb-2">Top Particulars</h3>

                                                <!-- Filters -->
                                                <div class="flex flex-col sm:flex-row justify-end gap-2 mt-2 mb-2">

                                                    <select id="topParticularsType" class="text-sm p-2 rounded-md border border-gray-300">
                                                        <option value="amount">By Amount</option>
                                                        <option value="quantity">By Quantity</option>
                                                    </select>

                                                    <select id="topParticularsDate" class="text-sm p-2 rounded-md border border-gray-300">
                                                        <option value="monthly" selected>Monthly</option>
                                                        <option value="quarterly">Quarterly</option>
                                                        <option value="yearly">Yearly</option>
                                                    </select>

                                                    <select id="topParticularsSubFilter" class="text-sm p-2 rounded-md border border-gray-300"></select>

                                                    <select id="topParticularsView" class="text-sm p-2 rounded-md border border-gray-300">
                                                        <option value="chart" selected>Chart View</option>
                                                        <option value="list">List View</option>
                                                    </select>
                                                </div>
                                            </div>


                                            <!-- Chart -->
                                            <div id="topParticularsChartContainer" class="relative w-full aspect-[16/9]">
                                                <canvas id="topParticularsChart" class="w-full h-full"></canvas>
                                            </div>

                                            <!-- List -->
                                            <div id="topParticularsListContainer" class="hidden overflow-x-auto">
                                                <table class="w-full text-sm text-left text-gray-500">
                                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                                        <tr>
                                                            <th class="px-4 py-2">Rank</th>
                                                            <th class="px-4 py-2">Particular</th>
                                                            <th class="px-4 py-2">Quantity</th>
                                                            <th class="px-4 py-2">Amount (₱)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="topParticularsTableBody"></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Top Collectors Content -->
                                    <div id="contentCollectorOverview" class="p-6 bg-white rounded-lg shadow-md hover:shadow-lg transition hidden">

                                        <div class="flex flex-col sm:flex-row justify-between mb-4">
                                            <h3 class="text-lg font-semibold">Top Collectors</h3>

                                            <div class="flex flex-col sm:flex-row gap-2 mt-2">

                                                <select id="collectorOverviewType" class="text-sm p-2 rounded-md border border-gray-300">
                                                    <option value="amount">By Revenue</option>
                                                    <option value="transactions">By Transactions</option>
                                                </select>

                                                <select id="collectorOverviewTimeframe" class="text-sm p-2 rounded-md border border-gray-300">
                                                    <option value="all">All Time</option>
                                                    <option value="today">Today</option>
                                                    <option value="weekly">Weekly</option>
                                                    <option value="monthly">Monthly</option>
                                                    <option value="quarterly">Quarterly</option>
                                                    <option value="yearly">Yearly</option>
                                                </select>

                                                <select id="collectorOverviewYear" class="text-sm p-2 rounded-md border border-gray-300 hidden"></select>
                                                <select id="collectorOverviewMonth" class="text-sm p-2 rounded-md border border-gray-300 hidden"></select>
                                                <select id="collectorOverviewWeek" class="text-sm p-2 rounded-md border border-gray-300 hidden"></select>
                                                <select id="collectorOverviewQuarter" class="text-sm p-2 rounded-md border border-gray-300 hidden"></select>
                                            </div>
                                        </div>

                                        <!-- Table -->
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-sm text-left text-gray-500">
                                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                                    <tr>
                                                        <th class="px-4 py-2">Rank</th>
                                                        <th class="px-4 py-2">Collector</th>
                                                        <th class="px-4 py-2">Value</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="collectorOverviewTableBody"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Transactions Modal -->
                    <div id="transactionsAllModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                        <div class="bg-white rounded-lg w-11/12 md:w-3/4 lg:w-2/3 p-6 relative">
                            <button class="absolute top-4 right-4 text-gray-500 hover:text-gray-800 text-3xl font-bold" onclick="closeAllSummModal('transactionsAllModal')">&times;</button>

                            <h2 class="text-xl font-bold text-gray-800 mb-4">Transactions Summary</h2>

                            <!-- Timeframe Filters -->
                            <div class="flex flex-wrap gap-2 mb-4" id="transactionsFilters">
                                <button class="timeframe-btn px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700" onclick="setActiveFilter(this, 'transactions'); loadAllTransactionsData('daily')">Daily</button>
                                <button class="timeframe-btn px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700" onclick="setActiveFilter(this, 'transactions'); loadAllTransactionsData('weekly')">Weekly</button>
                                <button class="timeframe-btn px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700" onclick="setActiveFilter(this, 'transactions'); loadAllTransactionsData('monthly')">Monthly</button>
                                <button class="timeframe-btn px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700" onclick="setActiveFilter(this, 'transactions'); loadAllTransactionsData('quarterly')">Quarterly</button>
                                <button class="timeframe-btn px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700" onclick="setActiveFilter(this, 'transactions'); loadAllTransactionsData('yearly')">Yearly</button>
                            </div>

                            <div id="transactionsAllList" class="overflow-x-auto"></div>
                        </div>
                    </div>

                    <!-- Revenue Modal -->
                    <div id="revenueAllModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                        <div class="bg-white rounded-lg w-11/12 md:w-3/4 lg:w-2/3 p-6 relative">
                            <button class="absolute top-4 right-4 text-gray-500 hover:text-gray-800 text-3xl font-bold" onclick="closeAllSummModal('revenueAllModal')">&times;</button>

                            <h2 class="text-xl font-bold text-gray-800 mb-4">Revenue Summary</h2>

                            <div class="flex flex-wrap gap-2 mb-4" id="revenueFilters">
                                <button class="timeframe-btn px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700" onclick="setActiveFilter(this, 'revenue'); loadAllRevenueData('daily')">Daily</button>
                                <button class="timeframe-btn px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700" onclick="setActiveFilter(this, 'revenue'); loadAllRevenueData('weekly')">Weekly</button>
                                <button class="timeframe-btn px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700" onclick="setActiveFilter(this, 'revenue'); loadAllRevenueData('monthly')">Monthly</button>
                                <button class="timeframe-btn px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700" onclick="setActiveFilter(this, 'revenue'); loadAllRevenueData('quarterly')">Quarterly</button>
                                <button class="timeframe-btn px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700" onclick="setActiveFilter(this, 'revenue'); loadAllRevenueData('yearly')">Yearly</button>
                            </div>

                            <div id="revenueAllList" class="overflow-x-auto"></div>
                        </div>
                    </div>









                    <!-- Employees Section -->
                    <section id="employees-section"
                        class="mt-1 px-6 flex flex-col items-center">
                        <div
                            class="bg-white shadow-xl rounded-2xl p-8 w-full max-w-8xl transition-all duration-300 hover:shadow-2xl h-[80vh] max-h-screen overflow-y-auto">
                            <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 sm:mb-6">Employees</h2>

                            <!-- Filters -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                <div class="w-full">
                                    <label for="employee-position-filter" class="block text-sm font-medium text-gray-700 mb-1">
                                        Filter by Position:
                                    </label>
                                    <select id="employee-position-filter"
                                        class="w-full p-2 sm:p-3 border border-gray-300 rounded-md shadow-sm bg-white text-gray-900">
                                        <option value="">All</option>
                                        <option value="Treasurer">Treasurer</option>
                                        <option value="Collector">Collector</option>
                                        <option value="Accountant">Accountant</option>
                                        <option value="Admin">Admin</option>
                                    </select>
                                </div>

                                <div class="w-full">
                                    <label for="employee-name-search" class="block text-sm font-medium text-gray-700 mb-1">
                                        Search by Name:
                                    </label>
                                    <input type="text" id="employee-name-search"
                                        class="w-full p-2 sm:p-3 border border-gray-300 rounded-md shadow-sm bg-white text-gray-900"
                                        placeholder="Enter name">
                                </div>

                                <!-- Add Employee Button -->
                                <div class="flex justify-end items-end">
                                    <button id="add-employee-btn"
                                        class="px-5 py-3 border border-blue-600 text-blue-600 font-medium rounded-lg hover:bg-blue-600 hover:text-white transition-all flex items-center">
                                        + Add Employee
                                    </button>
                                </div>
                            </div>

                            <!-- Employee Table -->
                            <!-- Employee Table -->
                            <div class="overflow-x-auto max-h-[60vh] overflow-y-auto border border-gray-200 rounded-md">
                                <table class="min-w-full table-auto border-collapse text-sm">
                                    <thead class="sticky top-0 bg-gray-300 text-black">
                                        <tr>
                                            <th class="p-3 border border-gray-400 font-medium">ID</th>
                                            <th class="p-3 border border-gray-400 font-medium">First Name</th>
                                            <th class="p-3 border border-gray-400 font-medium">Last Name</th>
                                            <th class="p-3 border border-gray-400 font-medium">Email</th>
                                            <th class="p-3 border border-gray-400 font-medium">Position</th>
                                            <th class="p-3 border border-gray-400 font-medium">Phone Number</th>
                                            <th class="p-3 border border-gray-400 font-medium">Address</th>
                                            <th class="p-3 border border-gray-400 font-medium">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="employee-table-body" class="bg-white divide-y divide-gray-200">
                                        <!-- Employee data will be injected here -->
                                    </tbody>
                                </table>
                            </div>

                            <br>

                            <!-- Pagination Controls -->
                            <div id="pagination-controls" class="mt-4 flex justify-center flex-wrap gap-2"></div>

                            <!-- Add Employee Modal -->
                            <div id="employee-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-50">
                                <div class="bg-white p-6 rounded-lg shadow-lg w-[36rem]">
                                    <h3 class="text-xl font-bold text-gray-900 mb-4">Add Employee</h3>

                                    <form id="add-employee-form" method="POST">
                                        <div class="mb-3">
                                            <label for="first-name" class="block text-sm font-medium text-gray-700 mb-1">First Name:</label>
                                            <input type="text" id="first-name" name="firstname" class="w-full p-2 border border-gray-300 rounded-md">
                                        </div>

                                        <div class="mb-3">
                                            <label for="last-name" class="block text-sm font-medium text-gray-700 mb-1">Last Name:</label>
                                            <input type="text" id="last-name" name="lastname" class="w-full p-2 border border-gray-300 rounded-md">
                                        </div>

                                        <div class="mb-3">
                                            <label for="employee-position" class="block text-sm font-medium text-gray-700 mb-1">Position:</label>
                                            <select id="employee-position" name="position" class="w-full p-2 border border-gray-300 rounded-md">
                                                <option value="Treasurer" selected>Treasurer</option>
                                                <option value="Collector">Collector</option>
                                                <option value="Accountant">Accountant</option>
                                                <option value="Admin">Admin</option>
                                            </select>
                                        </div>

                                        <!-- Recipient Email Field -->
                                        <div class="mb-3">
                                            <label for="recipient-email" class="block text-sm font-medium text-gray-700 mb-1">Recipient Email:</label>
                                            <input type="email" id="recipient-email" name="recipient-email" placeholder="recipient@example.com"
                                                class="w-full p-2 border border-gray-300 rounded-md">
                                        </div>

                                        <!-- Section Divider -->
                                        <div class="my-4 border-t border-gray-300 relative mt-5">
                                            <span class="absolute -top-3 left-1/2 transform -translate-x-1/2 bg-white px-3 text-sm font-semibold text-gray-600">
                                                Employee Login Credentials
                                            </span>
                                        </div>

                                        <div class="mb-3">
                                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email:</label>
                                            <div class="relative flex items-center space-x-2">
                                                <div class="relative flex-1">
                                                    <!-- Masked email (visible to admin) -->
                                                    <input type="text" id="email-masked" placeholder="user@gmail.com"
                                                        class="w-full p-2 border border-gray-300 rounded-md bg-gray-100" disabled>

                                                    <!-- REAL email (hidden) -->
                                                    <input type="hidden" id="email" name="email">
                                                </div>

                                                <button type="button" id="generate-email"
                                                    class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                                                    Generate Email Address
                                                </button>

                                            </div>
                                        </div>

                                        <!-- Password Input with Show/Hide and Generate -->
                                        <div class="mb-3">
                                            <label for="employee-password" class="block text-sm font-medium text-gray-700 mb-1">
                                                Password:
                                            </label>
                                            <div class="relative flex items-center space-x-2">
                                                <div class="relative flex-1">
                                                    <!-- Masked password (always hidden) -->
                                                    <input type="password" id="password-masked" placeholder="********"
                                                        class="w-full p-2 border border-gray-300 rounded-md bg-gray-100" disabled>

                                                    <!-- REAL password (hidden) -->
                                                    <input type="hidden" id="employee-password" name="password">
                                                </div>

                                                <button type="button" id="generate-password"
                                                    class="px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm">
                                                    Generate Strong Password
                                                </button>
                                            </div>
                                        </div>


                                        <div class="flex justify-end space-x-2 mt-4">
                                            <button id="cancel-btn" type="button"
                                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                                            <button id="save-btn" type="submit"
                                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save</button>
                                        </div>
                                    </form>
                                </div>
                            </div>





                            <!-- Loading Overlay -->
                            <div id="loading-overlay" class="fixed inset-0 bg-gray/70 backdrop-blur-sm hidden z-[9999] flex items-center justify-center">
                                <div class="flex flex-col items-center">
                                    <svg class="animate-spin h-10 w-10 text-blue-600 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                        </path>
                                    </svg>
                                    <p class="text-gray-700 text-sm font-medium">Processing, please wait...</p>
                                </div>
                            </div>



                            <!-- Edit Employee Modal -->
                            <div id="edit-employee-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-50">
                                <div class="bg-white p-6 rounded-lg shadow-lg w-[36rem]">
                                    <h3 class="text-xl font-bold text-gray-900 mb-4">Update Employee</h3>

                                    <form id="edit-employee-form" method="POST">
                                        <input type="hidden" id="edit-employee-id">

                                        <div class="mb-3">
                                            <label for="edit-first-name" class="block text-sm font-medium text-gray-700 mb-1">First Name:</label>
                                            <input type="text" id="edit-first-name" name="first-name" class="w-full p-2 border border-gray-300 rounded-md">
                                        </div>

                                        <div class="mb-3">
                                            <label for="edit-last-name" class="block text-sm font-medium text-gray-700 mb-1">Last Name:</label>
                                            <input type="text" id="edit-last-name" name="last-name" class="w-full p-2 border border-gray-300 rounded-md">
                                        </div>

                                        <div class="mb-3">
                                            <label for="edit-employee-position" class="block text-sm font-medium text-gray-700 mb-1">Position:</label>
                                            <select id="edit-employee-position" name="employee-position" class="w-full p-2 border border-gray-300 rounded-md">
                                                <option value="Treasurer">Treasurer</option>
                                                <option value="Collector">Collector</option>
                                                <option value="Accountant">Accountant</option>
                                                <option value="Admin">Admin</option>
                                            </select>
                                        </div>

                                        <div class="flex justify-end space-x-2 mt-4">
                                            <button id="edit-cancel-btn" type="button"
                                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                                            <button id="edit-save-btn" type="submit"
                                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Delete Confirmation Modal -->
                            <div id="delete-employee-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-50">
                                <div class="bg-white p-6 rounded-lg shadow-lg w-[28rem]">
                                    <h3 class="text-xl font-bold text-gray-900 mb-4">Confirm Deletion</h3>
                                    <p class="text-gray-700 mb-6">
                                        Are you sure you want to delete this employee? This action cannot be undone.
                                    </p>

                                    <div class="flex justify-end space-x-2">
                                        <button id="delete-cancel-btn" type="button"
                                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                                            Cancel
                                        </button>
                                        <button id="delete-employee-btn" type="button"
                                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>





                    <section id="reports-section"
                        class="mt-1 px-6 flex flex-col items-center">
                        <div
                            class="bg-white shadow-xl rounded-2xl p-8 w-full max-w-8xl transition-all duration-300 hover:shadow-2xl h-[80vh] max-h-screen overflow-y-auto">
                            <h2 class="text-2xl font-bold text-gray-900 mb-6">Reports</h2>

                            <!-- Search and Filter -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6 items-end">
                                <div>
                                    <label for="report-type-filter" class="block text-sm font-medium text-gray-700">Filter by Type:</label>
                                    <select id="report-type-filter"
                                        class="w-full p-2 md:p-3 border border-gray-300 rounded-md shadow-sm bg-white text-gray-900">
                                        <option value="">All</option>
                                        <option value="Daily">Daily</option>
                                        <option value="Monthly">Monthly</option>
                                        <option value="Quarterly">Quarterly</option>
                                        <option value="Yearly">Yearly</option>
                                        <option value="Engineering Share">Engineering Share</option>
                                        <option value="RRR">RRR</option>
                                    </select>
                                </div>

                                <div>
                                    <label for="report-position-filter" class="block text-sm font-medium text-gray-700">Filter by Position:</label>
                                    <select id="report-position-filter"
                                        class="w-full p-2 md:p-3 border border-gray-300 rounded-md shadow-sm bg-white text-gray-900">
                                        <option value="">All</option>
                                        <option value="Collector">Collector</option>
                                        <option value="Treasurer">Treasurer</option>
                                    </select>
                                </div>

                                <!-- From Date -->
                                <div>
                                    <label for="report-date-from" class="block text-sm font-medium text-gray-700">From Date:</label>
                                    <input type="text" id="report-date-from"
                                        class="w-full p-2 md:p-3 border border-gray-300 rounded-md shadow-sm bg-white text-gray-900"
                                        placeholder="MM/DD/YYYY">
                                </div>

                                <!-- To Date -->
                                <div>
                                    <label for="report-date-to" class="block text-sm font-medium text-gray-700">To Date:</label>
                                    <input type="text" id="report-date-to"
                                        class="w-full p-2 md:p-3 border border-gray-300 rounded-md shadow-sm bg-white text-gray-900"
                                        placeholder="MM/DD/YYYY">
                                </div>

                                <!-- Reset Button -->
                                <div class="flex md:justify-end">
                                    <button id="resetFilters"
                                        class="px-5 py-3 border border-blue-600 text-blue-600 font-medium rounded-lg hover:bg-blue-600 hover:text-white transition-all flex items-center">
                                        Reset Filters
                                    </button>
                                </div>
                            </div>

                            <!-- Reports Table -->
                            <div class="overflow-x-auto max-h-[60vh] overflow-y-auto border border-gray-200 rounded-md">
                                <table class="min-w-full table-auto border-collapse text-sm">
                                    <thead class="sticky top-0 bg-gray-300 text-black">
                                        <tr>
                                            <th class="p-3 border border-gray-400 font-medium">Report Type</th>
                                            <th class="p-3 border border-gray-400 font-medium">Position</th>
                                            <th class="p-3 border border-gray-400 font-medium">Description</th>
                                            <th class="p-3 border border-gray-400 font-medium">Report Date</th>
                                            <th class="p-3 border border-gray-400 font-medium">Submitted On</th>
                                            <th class="p-3 border border-gray-400 font-medium">Submitted By</th>
                                            <th class="p-3 border border-gray-400 font-medium">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="reports-table-body" class="bg-white divide-y divide-gray-200">
                                        <!-- Reports will be loaded here -->
                                    </tbody>
                                </table>
                            </div>

                            <br>

                            <div id="reports-pagination" class="flex flex-wrap justify-center mt-5 space-x-1"></div>
                        </div>
                    </section>

                    <!-- Preview Modal -->
                    <div id="preview-modal"
                        class="fixed inset-0 z-50 hidden overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center p-4">
                        <div
                            class="bg-white rounded-lg shadow-xl transform transition-all w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden">
                            <div class="px-4 md:px-6 pt-4 md:pt-5 pb-4 overflow-y-auto">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Report Preview</h3>
                                <div id="preview-content" class="h-[60vh] md:h-[70vh] overflow-auto border rounded p-4 bg-gray-50">
                                    <!-- Preview content will be loaded here -->
                                </div>
                            </div>

                            <div
                                class="bg-gray-50 px-4 py-3 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-3 border-t border-gray-200">
                                <button id="close-preview-btn" type="button"
                                    class="mt-2 sm:mt-0 w-full sm:w-auto inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-100 focus:outline-none sm:text-sm">
                                    Close
                                </button>
                                <button id="download-report-btn" type="button"
                                    class="w-full sm:w-auto inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:text-sm">
                                    Download Report
                                </button>
                            </div>
                        </div>
                    </div>





                    <!-- LGU PAYMENT SECTION -->
                    <section id="lgu-payment-references-section"
                        class="mt-1 px-6 flex flex-col items-center">
                        <div
                            class="bg-white shadow-xl rounded-2xl p-8 w-full max-w-8xl transition-all duration-300 hover:shadow-2xl h-[80vh] max-h-screen overflow-y-auto">
                            <h2 class="text-2xl font-bold text-gray-900 mb-6">LGU Payment References</h2>

                            <!-- Search and Filter -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6 items-end">
                                <!-- Department Filter -->
                                <div>
                                    <label for="department-filter" class="block text-sm font-medium text-gray-700">Filter by Department:</label>
                                    <select id="department-filter"
                                        class="w-full p-2 md:p-3 border border-gray-300 rounded-md shadow-sm bg-white text-gray-900">
                                        <option value="">All Departments</option>
                                    </select>
                                </div>

                                <!-- Account Code Filter -->
                                <div>
                                    <label for="account-code-filter" class="block text-sm font-medium text-gray-700">Filter by Account Code:</label>
                                    <select id="account-code-filter"
                                        class="w-full p-2 md:p-3 border border-gray-300 rounded-md shadow-sm bg-white text-gray-900">
                                        <option value="">All Account Codes</option>
                                    </select>
                                </div>

                                <!-- Particulars Search -->
                                <div>
                                    <label for="particulars-search" class="block text-sm font-medium text-gray-700">Search by Particulars:</label>
                                    <input type="text" id="particulars-search" placeholder="e.g. certification, permit"
                                        class="w-full p-2 md:p-3 border border-gray-300 rounded-md shadow-sm bg-white text-gray-900">
                                </div>

                                <!-- Table View Selector -->
                                <div>
                                    <label for="table-view" class="block text-sm font-medium text-gray-700">Table View:</label>
                                    <select id="table-view"
                                        class="w-full p-2 md:p-3 border border-gray-300 rounded-md shadow-sm bg-white text-gray-900">
                                        <option value="lgu">LGU Payment Reference View</option>
                                        <option value="account-codes">Account Codes View</option>
                                        <option value="departments">Departments View</option>
                                        <option value="categorization">Payment Reference Categorization View</option>
                                    </select>
                                </div>

                                <!-- Add Dropdown -->
                                <div class="flex md:justify-end">
                                    <div class="inline-block text-left">
                                        <button id="add-dropdown-btn"
                                            class="px-5 py-3 border border-blue-600 text-blue-600 font-medium rounded-lg hover:bg-blue-600 hover:text-white transition-all flex items-center">
                                            + Add
                                            <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>

                                        <!-- Dropdown menu -->
                                        <div id="add-dropdown-menu"
                                            class="hidden absolute right-0 mt-2 w-64 bg-white border border-gray-200 rounded-lg shadow-lg z-50">
                                            <ul class="py-2">
                                                <li>
                                                    <button class="w-full text-left px-4 py-2 hover:bg-blue-50" id="add-payment-btn">
                                                        Add Payment Reference
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="w-full text-left px-4 py-2 hover:bg-blue-50" id="open-department-modal">
                                                        Add Department
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="w-full text-left px-4 py-2 hover:bg-blue-50" id="open-account-code-modal">
                                                        Add Account Code
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="w-full text-left px-4 py-2 hover:bg-blue-50" id="open-categorization-modal">
                                                        Add Payment Reference Categorization
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>



                            <!-- Account Code Modal -->
                            <div id="account-code-modal"
                                class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                                <div class="bg-white rounded-lg shadow-lg w-full max-w-md max-h-[60vh] flex flex-col overflow-hidden">
                                    <!-- Modal Header -->
                                    <div class="flex justify-between items-center p-4 border-b">
                                        <h2 class="text-xl font-bold text-gray-800">Add Account Code(s)</h2>
                                        <button id="close-account-code-modal" class="text-gray-600 hover:text-gray-900">
                                            <i class="ri-close-line text-2xl"></i>
                                        </button>
                                    </div>

                                    <!-- Scrollable Content -->
                                    <div class="p-4 overflow-y-auto flex-1">
                                        <form id="add-code-form" class="space-y-4">
                                            <div id="code-fields-container" class="space-y-3">
                                                <!-- Default input -->
                                                <div class="flex gap-2 code-field">
                                                    <input type="text" name="new-code[]" placeholder="Enter account code" required
                                                        class="w-full p-2 border border-gray-300 rounded-md bg-white text-gray-900">
                                                    <button type="button"
                                                        class="remove-code text-red-600 hover:text-red-800 hidden">
                                                        <i class="ri-delete-bin-line text-xl"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Fixed Footer Buttons -->
                                    <div class="p-4 border-t bg-white sticky bottom-0">
                                        <div class="flex flex-col sm:flex-row gap-2">
                                            <button type="button" id="add-another-code"
                                                class="flex-1 px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                                                + Add Another
                                            </button>

                                            <button type="submit" form="add-code-form"
                                                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                                Save Code(s)
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>





                            <!-- Department Modal -->
                            <div id="department-modal"
                                class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                                <div class="bg-white rounded-lg shadow-lg w-full max-w-md max-h-[60vh] flex flex-col overflow-hidden">
                                    <!-- Modal Header -->
                                    <div class="flex justify-between items-center p-4 border-b">
                                        <h2 class="text-xl font-bold text-gray-800">Add Department(s)</h2>
                                        <button id="close-department-modal" class="text-gray-600 hover:text-gray-900">
                                            <i class="ri-close-line text-2xl"></i>
                                        </button>
                                    </div>

                                    <!-- Scrollable Content -->
                                    <div class="p-4 overflow-y-auto flex-1">
                                        <form id="add-department-form" class="space-y-4">
                                            <div id="department-fields-container" class="space-y-3">
                                                <!-- Default input -->
                                                <div class="flex gap-2 department-field items-center">
                                                    <input type="text" name="new-department[]" placeholder="Enter department" required
                                                        class="w-full p-2 border border-gray-300 rounded-md bg-white text-gray-900">
                                                    <button type="button"
                                                        class="remove-department text-red-600 hover:text-red-800 hidden">
                                                        <i class="ri-delete-bin-line text-xl"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                    <!-- Fixed Footer Buttons -->
                                    <div class="p-4 border-t bg-white sticky bottom-0">
                                        <div class="flex flex-col sm:flex-row gap-2">
                                            <button type="button" id="add-another-department"
                                                class="flex-1 px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                                                + Add Another
                                            </button>

                                            <button type="submit" form="add-department-form"
                                                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                                Save Department(s)
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <!-- Categorization Modal -->
                            <div id="categorization-modal"
                                class="fixed inset-0 hidden bg-black bg-opacity-50 flex items-center justify-center z-50">
                                <div class="bg-white rounded-lg shadow-lg w-full max-w-md max-h-[60vh] flex flex-col overflow-hidden">
                                    <!-- Modal Header -->
                                    <div class="flex justify-between items-center p-4 border-b">
                                        <h2 class="text-xl font-bold text-gray-800">Add Categorization(s)</h2>
                                        <button id="close-categorization-modal" class="text-gray-600 hover:text-gray-900">
                                            <i class="ri-close-line text-2xl"></i>
                                        </button>
                                    </div>

                                    <!-- Scrollable Content -->
                                    <div class="p-4 overflow-y-auto flex-1">
                                        <form id="add-categorization-form" class="space-y-4">
                                            <div id="categorizationFieldsContainer" class="space-y-2">
                                                <div class="flex gap-2">
                                                    <input type="text" name="categorization[]" placeholder="Enter categorization"
                                                        class="flex-1 p-2 border border-gray-300 rounded-md bg-white text-gray-900" required>
                                                    <button type="button"
                                                        class="remove-field text-red-600 hover:text-red-800 hidden">
                                                        <i class="ri-delete-bin-line text-xl"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <!-- Fixed Footer Buttons -->
                                    <div class="p-4 border-t bg-white sticky bottom-0">
                                        <div class="flex flex-col sm:flex-row gap-2">
                                            <button type="button" id="addCategorizationField"
                                                class="flex-1 px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition">
                                                + Add Another
                                            </button>
                                            <button type="submit" form="add-categorization-form"
                                                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                                Save
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <!-- Scrollable Tables Container -->
                            <div class="overflow-x-auto max-h-[60vh] overflow-y-auto border border-gray-200 rounded-md">
                                <!-- LGU Payment References Table -->
                                <div id="lgu-view" class="min-w-full">
                                    <table class="min-w-full table-auto border-collapse text-sm">
                                        <thead class="sticky top-0 bg-gray-300 text-black">
                                            <tr>
                                                <th class="p-3 border border-gray-400 font-medium">Department</th>
                                                <th class="p-3 border border-gray-400 font-medium">Account Code</th>
                                                <th class="p-3 border border-gray-400 font-medium">Particulars</th>
                                                <th class="p-3 border border-gray-400 font-medium">Categorization</th>
                                                <th class="p-3 border border-gray-400 font-medium">Amount</th>
                                                <th class="p-3 border border-gray-400 font-medium">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="payment-table-body" class="bg-white divide-y divide-gray-200"></tbody>
                                    </table>
                                    <br>
                                    <div id="payment-pagination" class="mt-3"></div>
                                </div>

                                <!-- Account Codes Table -->
                                <div id="account-codes-view" class="hidden min-w-full">
                                    <table class="min-w-full table-auto border-collapse text-sm">
                                        <thead class="sticky top-0 bg-gray-300 text-black">
                                            <tr>
                                                <th class="p-3 border border-gray-400 font-medium">Account Code</th>
                                                <th class="p-3 border border-gray-400 font-medium">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="account-codes-table-body" class="bg-white divide-y divide-gray-200"></tbody>
                                    </table>
                                    <br>
                                    <div id="account-codes-pagination" class="mt-3"></div>
                                </div>

                                <!-- Departments Table -->
                                <div id="departments-view" class="hidden min-w-full">
                                    <table class="min-w-full table-auto border-collapse text-sm">
                                        <thead class="sticky top-0 bg-gray-300 text-black">
                                            <tr>
                                                <th class="p-3 border border-gray-400 font-medium">Department</th>
                                                <th class="p-3 border border-gray-400 font-medium">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="departments-table-body" class="bg-white divide-y divide-gray-200"></tbody>
                                    </table>
                                    <br>
                                    <div id="departments-pagination" class="mt-3"></div>
                                </div>

                                <!-- Categorization Table -->
                                <div id="categorization-view" class="hidden min-w-full">
                                    <table class="min-w-full table-auto border-collapse text-sm">
                                        <thead class="sticky top-0 bg-gray-300 text-black">
                                            <tr>
                                                <th class="p-3 border border-gray-400 font-medium">Categorization</th>
                                                <th class="p-3 border border-gray-400 font-medium">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="categorization-table-body" class="bg-white divide-y divide-gray-200"></tbody>
                                    </table>
                                    <br>
                                    <div id="categorization-pagination" class="mt-3"></div>
                                </div>
                            </div>
                        </div>
                    </section>


                    <!-- Modal for Adding Payment Reference -->
                    <div id="payment-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
                        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                            <h2 id="modal-title" class="text-xl font-bold text-gray-900 mb-4">Add Payment Reference</h2>

                            <!-- Department -->
                            <label class="block text-sm font-medium text-gray-700">Department</label>
                            <select id="department-select" class="w-full p-3 border border-gray-300 rounded-md shadow-sm">
                                <option value="">Choose Department</option>
                            </select>

                            <!-- Account Code -->
                            <label class="block text-sm font-medium text-gray-700 mt-2">Account Code</label>
                            <select id="account-code-select" class="w-full p-3 border border-gray-300 rounded-md shadow-sm">
                                <option value="">Choose Account Code</option>
                            </select>

                            <!-- Categorization -->
                            <label class="block text-sm font-medium text-gray-700 mt-2">Categorization</label>
                            <select id="categorization-select" class="w-full p-3 border border-gray-300 rounded-md shadow-sm">
                                <option value="">Choose Categorization</option>
                            </select>

                            <!-- Particular -->
                            <label class="block text-sm font-medium text-gray-700 mt-2">Particular</label>
                            <input type="text" id="particular-modal" class="w-full p-2 border rounded mb-4">

                            <!-- Amount -->
                            <label class="block text-sm font-medium text-gray-700">Amount</label>
                            <input type="text" id="particular-amount-modal" value="0" class="w-full p-2 border rounded mb-4" />

                            <div class="flex justify-end">
                                <button onclick="closeModal()" class="px-4 py-2 bg-gray-500 text-white rounded mr-2 hover:bg-gray-600 transition">
                                    Cancel
                                </button>
                                <button id="save-btn-lgu" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                                    Save
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Modal for Editing Payment Reference -->
                    <div id="update-payment-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
                        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
                            <h2 class="text-xl font-bold text-gray-900 mb-4">Update Payment Reference</h2>

                            <input type="hidden" id="edit-id">

                            <label class="block text-sm font-medium text-gray-700">Department</label>
                            <select id="edit-department" class="w-full p-3 border rounded-md">
                                <option value="">Choose Department</option>
                            </select>

                            <label class="block text-sm font-medium text-gray-700 mt-2">Account Code</label>
                            <select id="edit-account-code" class="w-full p-3 border rounded-md">
                                <option value="">Choose Account Code</option>
                            </select>

                            <label class="block text-sm font-medium text-gray-700 mt-2">Categorization</label>
                            <select id="edit-categorization" class="w-full p-3 border border-gray-300 rounded-md shadow-sm">
                                <option value="">Choose Categorization</option>
                            </select>

                            <label class="block text-sm font-medium text-gray-700 mt-2">Particular</label>
                            <input type="text" id="edit-particular" class="w-full p-2 border rounded mb-4">

                            <label class="block text-sm font-medium text-gray-700">Amount</label>
                            <input type="text" id="edit-amount" class="w-full p-2 border rounded mb-4">

                            <div class="flex justify-end">
                                <button onclick="closeModal()" class="px-4 py-2 bg-gray-500 text-white rounded mr-2 hover:bg-gray-600 transition">
                                    Cancel
                                </button>
                                <button id="update-btn-lgu" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                                    Save
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Confirmation Modal -->
                    <div id="deleteConfirmModal" class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
                        <div class="bg-white rounded-lg shadow-lg p-6 max-w-sm w-full">
                            <h2 class="text-lg font-bold mb-4">Delete Payment Reference</h2>
                            <p class="text-gray-700 mb-6">Are you sure you want to delete this payment reference? This action cannot be undone.</p>
                            <div class="flex justify-end space-x-3">
                                <button id="cancelDeleteBtn" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                                    Cancel
                                </button>
                                <button id="confirmDeleteBtn" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Account Code Modal -->
                    <div id="edit-code-modal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
                        <div class="bg-white rounded-lg p-6 w-96 shadow-lg">
                            <h2 class="text-lg font-bold mb-4">Edit Account Code</h2>
                            <input type="hidden" id="edit-code-id">
                            <input type="text" id="edit-code-value" class="w-full border rounded p-2 mb-4" placeholder="Enter new code">
                            <div class="flex justify-end gap-2">
                                <button id="cancel-edit-code" class="px-4 py-2 bg-gray-200 hover:bg-gray-400 rounded">Cancel</button>
                                <button id="save-edit-code" class="px-4 py-2 bg-blue-600 hover:bg-blue-800 text-white rounded">Save</button>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Account Code Modal -->
                    <div id="delete-code-modal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
                        <div class="bg-white rounded-lg p-6 w-96 shadow-lg">
                            <h2 class="text-lg font-bold mb-4">Delete Account Code</h2>
                            <p class="mb-4">Are you sure you want to delete this account code? This action cannot be undone.</p>
                            <input type="hidden" id="delete-code-id">
                            <div class="flex justify-end gap-2">
                                <button id="cancel-delete-code" class="px-4 py-2 bg-gray-200 hover:bg-gray-400 rounded">Cancel</button>
                                <button id="confirm-delete-code" class="px-4 py-2 bg-red-600 hover:bg-red-800 text-white rounded">Delete</button>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Department Modal -->
                    <div id="edit-department-modal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
                        <div class="bg-white rounded-lg p-6 w-96 shadow-lg">
                            <h2 class="text-lg font-bold mb-4">Edit Department</h2>
                            <input type="hidden" id="edit-department-id">
                            <input type="text" id="edit-department-value" class="w-full border rounded p-2 mb-4" placeholder="Enter new department">
                            <div class="flex justify-end gap-2">
                                <button id="cancel-edit-department" class="px-4 py-2 bg-gray-200 hover:bg-gray-400 rounded">Cancel</button>
                                <button id="save-edit-department" class="px-4 py-2 bg-blue-600 hover:bg-blue-800 text-white rounded">Save</button>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Department Modal -->
                    <div id="delete-department-modal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
                        <div class="bg-white rounded-lg p-6 w-96 shadow-lg">
                            <h2 class="text-lg font-bold mb-4">Delete Department</h2>
                            <p class="mb-4">Are you sure you want to delete this department? This action cannot be undone.</p>
                            <input type="hidden" id="delete-department-id">
                            <div class="flex justify-end gap-2">
                                <button id="cancel-delete-department" class="px-4 py-2 bg-gray-200 hover:bg-gray-400 rounded">Cancel</button>
                                <button id="confirm-delete-department" class="px-4 py-2 bg-red-600 hover:bg-red-800 text-white rounded">Delete</button>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Categorization Modal -->
                    <div id="edit-categorization-modal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
                        <div class="bg-white rounded-lg p-6 w-96 shadow-lg">
                            <h2 class="text-lg font-bold mb-4">Edit Categorization</h2>
                            <input type="hidden" id="edit-categorization-id">
                            <input type="text" id="edit-categorization-value" class="w-full border rounded p-2 mb-4" placeholder="Enter new categorization">
                            <div class="flex justify-end gap-2">
                                <button id="cancel-edit-categorization" class="px-4 py-2 bg-gray-200 hover:bg-gray-400 rounded">Cancel</button>
                                <button id="save-edit-categorization" class="px-4 py-2 bg-blue-600 hover:bg-blue-800 text-white rounded">Save</button>
                            </div>
                        </div>
                    </div>

                    <!-- Delete Categorization Modal -->
                    <div id="delete-categorization-modal" class="hidden fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
                        <div class="bg-white rounded-lg p-6 w-96 shadow-lg">
                            <h2 class="text-lg font-bold mb-4">Delete Categorization</h2>
                            <p class="mb-4">Are you sure you want to delete this categorization? This action cannot be undone.</p>
                            <input type="hidden" id="delete-categorization-id">
                            <div class="flex justify-end gap-2">
                                <button id="cancel-delete-categorization" class="px-4 py-2 bg-gray-200 hover:bg-gray-400 rounded">Cancel</button>
                                <button id="confirm-delete-categorization" class="px-4 py-2 bg-red-600 hover:bg-red-800 text-white rounded">Delete</button>
                            </div>
                        </div>
                    </div>





                    <!-- AUDIT LOGS SECTION -->
                    <section
                        id="audit-section"
                        class="mt-1 px-6 flex flex-col items-center">
                        <div
                            class="bg-white shadow-xl rounded-2xl p-8 w-full max-w-8xl transition-all duration-300 hover:shadow-2xl h-[80vh] max-h-screen overflow-y-auto">
                            <h2 class="text-xl sm:text-2xl font-bold mb-4 text-gray-900">Audit Logs</h2>

                            <!-- Filters -->
                            <div class="flex flex-col md:flex-row md:flex-wrap gap-3 mb-4">
                                <!-- Search by user -->
                                <input
                                    type="text"
                                    id="auditSearchInput"
                                    placeholder="Search by user..."
                                    class="px-3 py-2 border rounded-md text-sm w-full md:w-[200px]" />

                                <!-- Quick Filters -->
                                <select
                                    id="auditQuickFilter"
                                    class="px-3 py-2 border rounded-md text-sm w-full md:w-[180px]">
                                    <option value="all">Timeframe Filters</option>
                                    <option value="all">All</option>
                                    <option value="today">Today</option>
                                    <option value="select-day">Select Day</option>
                                    <option value="month">Month</option>
                                    <option value="year">Year</option>
                                    <option value="date-range">Date Range</option>
                                </select>

                                <!-- Dynamic filter inputs -->
                                <input
                                    type="date"
                                    id="auditSelectDay"
                                    class="hidden px-3 py-2 border rounded-md text-sm w-full md:w-[180px]" />
                                <select
                                    id="auditSelectMonth"
                                    class="hidden px-3 py-2 border rounded-md text-sm w-full md:w-[180px]">
                                    <option value="">Select Month</option>
                                    <option value="0">January</option>
                                    <option value="1">February</option>
                                    <option value="2">March</option>
                                    <option value="3">April</option>
                                    <option value="4">May</option>
                                    <option value="5">June</option>
                                    <option value="6">July</option>
                                    <option value="7">August</option>
                                    <option value="8">September</option>
                                    <option value="9">October</option>
                                    <option value="10">November</option>
                                    <option value="11">December</option>
                                </select>

                                <select
                                    id="auditSelectYear"
                                    class="hidden px-3 py-2 border rounded-md text-sm w-full md:w-[160px]"></select>

                                <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                                    <input
                                        type="date"
                                        id="auditRangeStart"
                                        class="hidden px-3 py-2 border rounded-md text-sm w-full sm:w-[180px]" />
                                    <input
                                        type="date"
                                        id="auditRangeEnd"
                                        class="hidden px-3 py-2 border rounded-md text-sm w-full sm:w-[180px]" />
                                </div>

                                <!-- Position Filter -->
                                <select
                                    id="auditPositionFilter"
                                    class="px-3 py-2 border rounded-md text-sm w-full md:w-[180px]">
                                    <option value="all">All Positions</option>
                                    <option value="admin">Admin</option>
                                    <option value="collector">Collector</option>
                                    <option value="treasurer">Treasurer</option>
                                    <option value="accountant">Accountant</option>
                                </select>
                            </div>

                            <!-- Scrollable Table Container -->
                            <div class="overflow-x-auto max-h-[60vh] overflow-y-auto border border-gray-200 rounded-md">
                                <table class="min-w-full table-auto border-collapse text-sm">
                                    <thead class="sticky top-0 bg-gray-300 text-black">
                                        <tr>
                                            <th class="p-3 border border-gray-400 font-medium">Timestamp</th>
                                            <th class="p-3 border border-gray-400 font-medium">User</th>
                                            <th class="p-3 border border-gray-400 font-medium">Position</th>
                                            <th class="p-3 border border-gray-400 font-medium">Action</th>
                                            <th class="p-3 border border-gray-400 font-medium">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        id="auditLogsTable"
                                        class="bg-white divide-y divide-gray-200">
                                        <!-- Data dynamically loaded -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div
                                id="auditPaginationControls"
                                class="w-full flex justify-center mt-6"></div>
                        </div>
                    </section>






                    <!-- settings -->
                    <section id="settings-section" class="mt-1 px-6 flex flex-col items-center">
                        <div class="flex justify-center mt--10 w-full">
                            <div
                                class="bg-white shadow-xl rounded-2xl p-8 w-full max-w-8xl transition-all duration-300 hover:shadow-2xl h-[80vh] max-h-screen overflow-y-auto">

                                <!-- ================= SETTINGS PAGE ================= -->
                                <h2 class="text-xl font-bold mb-4 text-gray-900">Profile Settings</h2>

                                <!-- Profile Overview -->
                                <div class="border-b border-gray-300 pb-6 mb-6">
                                    <div class="ml-1">
                                        <h2 id="name" class="text-2xl font-semibold text-gray-900">
                                            <?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?>
                                        </h2>
                                        <div class="flex items-center text-gray-500 text-sm space-x-1">
                                            <span><?php echo htmlspecialchars($user['position']); ?></span>
                                            <span>|</span>
                                            <span><?php echo htmlspecialchars($orgName); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact Info -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <p class="text-gray-600 text-sm">
                                        Recovery Email: <span id="recovery_email"><?php echo htmlspecialchars($user['recovery_email']); ?></span>
                                    </p>
                                    <p class="text-gray-600 text-sm">
                                        Phone: <span id="phone"><?php echo htmlspecialchars($user['phone']); ?></span>
                                    </p>
                                    <p class="text-gray-600 text-sm md:col-span-2">
                                        Address: <span id="address"><?php echo htmlspecialchars($user['address']); ?></span>
                                    </p>
                                </div>

                                <!-- Edit Profile Button -->
                                <div class="flex justify-end space-x-4 mt-6">
                                    <button id="edit-profile-button"
                                        class="bg-gray-700 text-white py-2 px-6 rounded-xl hover:bg-gray-800 text-sm font-medium">
                                        Edit Profile
                                    </button>
                                </div>

                                <!-- Modal -->
                                <div id="editProfileModal"
                                    class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                                    <div class="bg-white p-6 rounded-xl shadow-lg w-96">
                                        <h3 class="text-lg font-semibold mb-4 text-gray-900">Edit Profile</h3>

                                        <form id="editProfileForm" class="space-y-4">
                                            <div>
                                                <label class="block text-sm text-gray-700">First Name</label>
                                                <input type="text" name="firstname" value="<?php echo htmlspecialchars($user['firstname']); ?>"
                                                    class="w-full px-3 py-2 border rounded-lg" required>
                                            </div>
                                            <div>
                                                <label class="block text-sm text-gray-700">Last Name</label>
                                                <input type="text" name="lastname" value="<?php echo htmlspecialchars($user['lastname']); ?>"
                                                    class="w-full px-3 py-2 border rounded-lg" required>
                                            </div>
                                            <div>
                                                <label class="block text-sm text-gray-700">Recovery Email</label>
                                                <input type="email" name="recovery_email"
                                                    value="<?php echo htmlspecialchars($recovery_email); ?>"
                                                    class="w-full px-3 py-2 border rounded-lg" required>
                                            </div>
                                            <div>
                                                <label class="block text-sm text-gray-700">Phone</label>
                                                <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>"
                                                    class="w-full px-3 py-2 border rounded-lg">
                                            </div>
                                            <div>
                                                <label class="block text-sm text-gray-700">Address</label>
                                                <textarea name="address"
                                                    class="w-full px-3 py-2 border rounded-lg"><?php echo htmlspecialchars($user['address']); ?></textarea>
                                            </div>

                                            <div class="flex justify-end space-x-2 mt-4">
                                                <button type="button" id="cancelProfileModal"
                                                    class="px-4 py-2 bg-gray-400 text-white rounded-lg hover:bg-gray-500">Cancel</button>
                                                <button type="submit"
                                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save</button>
                                            </div>
                                        </form>

                                    </div>
                                </div>

                                <br>
                                <hr>

                                <!-- ================= ACCOUNT CREDENTIALS ================= -->
                                <h2 class="text-xl font-bold mt-8 mb-4 text-gray-900">Account Credentials</h2>

                                <div class="mb-4">
                                    <label class="block text-gray-900 mb-2">Update Login Information</label>

                                    <!-- Current Email -->
                                    <input type="email" id="currentEmail" value="<?php echo htmlspecialchars($user['email']); ?>" readonly
                                        class="w-full p-2 border rounded-md mb-2 bg-gray-100 cursor-not-allowed">

                                    <!-- New Email -->
                                    <input type="email" id="newEmail" placeholder="New Email"
                                        class="w-full p-2 border rounded-md mb-4">

                                    <!-- Current Password -->
                                    <div class="relative mb-2">
                                        <input type="password" id="currentPassword" placeholder="Current Password"
                                            class="w-full p-2 border rounded-md pr-10">
                                        <button type="button" onclick="toggleProfilePassword('currentPassword', this)"
                                            class="absolute right-2 top-2 text-gray-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 
                                4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- New Password -->
                                    <div class="relative mb-2">
                                        <input type="password" id="newPassword" placeholder="New Password"
                                            class="w-full p-2 border rounded-md pr-10">
                                        <button type="button" onclick="toggleProfilePassword('newPassword', this)"
                                            class="absolute right-2 top-2 text-gray-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 
                                4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Confirm Password -->
                                    <div class="relative mb-4">
                                        <input type="password" id="confirmPassword" placeholder="Confirm New Password"
                                            class="w-full p-2 border rounded-md pr-10">
                                        <button type="button" onclick="toggleProfilePassword('confirmPassword', this)"
                                            class="absolute right-2 top-2 text-gray-500">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor" stroke-width="2">
                                                <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 
                                4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Save Button -->
                                    <button id="updateCredentialsBtn"
                                        class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">
                                        Update Credentials
                                    </button>
                                </div>

                                <!-- ================= SESSION TIMEOUT SETTINGS ================= -->
                                <hr class="my-6">
                                <h2 class="text-xl font-bold mt-8 mb-4 text-gray-900">Session Timeout Settings</h2>
                                <div class="grid grid-cols-1 md:grid-cols-1 gap-4 mb-4">
                                    

                                    <div>
                                        <label class="block text-gray-700 mb-1">Auto Logout Time (minutes)</label>
                                        <select id="logoutTimeInput" class="w-full p-2 border rounded-md">
                                            <option value="never">Never</option>
                                            <option value="5">5 Minutes</option>
                                            <option value="10">10 Minutes</option>
                                            <option value="15" selected>15 Minutes</option>
                                            <option value="20">20 Minutes</option>
                                            <option value="30">30 Minutes</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="flex justify-end">
                                    <button id="saveTimeoutSettings"
                                        class="bg-green-600 text-white py-2 px-6 rounded-xl hover:bg-green-700">
                                        Save Timeout Settings
                                    </button>
                                </div>

                                <hr class="my-6">

                                <!-- ================= ORG INFO (ADMIN ONLY) ================= -->
                                <?php if ($user['position'] === 'Admin'): ?>
                                    <h2 class="text-xl font-bold mb-4 text-gray-900">Organization Information</h2>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                        <div>
                                            <label class="block text-gray-700 mb-1">Organization Name</label>
                                            <input type="text" id="orgName" placeholder="Enter organization name"
                                                class="w-full p-2 border rounded-md"
                                                value="<?php echo htmlspecialchars($orgName); ?>">
                                        </div>

                                        <div>
                                            <label class="block text-gray-700 mb-1">Organization Logo</label>
                                            <input type="file" id="orgLogo" accept="image/*"
                                                class="w-full p-2 border rounded-md">
                                        </div>
                                    </div>

                                    <div class="flex justify-end mb-8">
                                        <button id="saveOrgBtn"
                                            class="bg-blue-500 text-white py-2 px-6 rounded-xl hover:bg-blue-600">Save</button>
                                    </div>
                                    <hr class="my-6">

                                    <h2 class="text-xl font-bold mb-4 text-gray-900">Receipt Information</h2>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                        <div>
                                            <label class="block text-gray-700 mb-1">Agency Name</label>
                                            <input type="text" id="agencyName" placeholder="Enter agency name"
                                                class="w-full p-2 border rounded-md"
                                                value="<?php echo htmlspecialchars($agencyName); ?>">
                                        </div>

                                        <div>
                                            <label class="block text-gray-700 mb-1">Treasurer Name</label>
                                            <input type="text" id="treasurerName" placeholder="Enter treasurer name"
                                                class="w-full p-2 border rounded-md"
                                                value="<?php echo htmlspecialchars($treasurerName); ?>">
                                        </div>
                                    </div>

                                    <div class="flex justify-end mb-8">
                                        <button id="saveReceiptBtn"
                                            class="bg-blue-500 text-white py-2 px-6 rounded-xl hover:bg-blue-600">Save</button>
                                    </div>
                                    <hr class="my-6">



                                    <!-- ================= BACKUP & RESTORE ================= -->

                                    <h2 class="text-xl font-bold mt-8 mb-4 text-gray-900">Backup & Restore</h2>
                                    <div class="flex flex-col md:flex-row gap-4">
                                        <button id="backupBtn"
                                            class="bg-green-600 text-white py-2 px-6 rounded hover:bg-green-700">Backup Data</button>
                                        <input type="file" id="restoreFile" class="hidden" accept=".enc">

                                        <button id="restoreTrigger"
                                            class="bg-yellow-600 text-white py-2 px-6 rounded hover:bg-yellow-700">
                                            Restore Data
                                        </button>

                                    </div>
                                    <p class="text-sm text-gray-500 mt-2">Backup will generate a copy of the database. Restore will overwrite current data with selected file.</p>

                                    <!-- Restore Password Modal -->
                                    <div id="restoreModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
                                        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                                            <h2 class="text-lg font-bold text-gray-900 mb-4">Enter Your Password</h2>

                                            <input type="password" id="restorePassword"
                                                class="w-full border p-2 rounded mb-4"
                                                placeholder="Enter your password">

                                            <p class="text-sm text-gray-600 mb-6">
                                                Restoring will <span class="font-semibold text-red-600">overwrite the database</span>.
                                            </p>

                                            <div class="flex justify-end gap-2">
                                                <button id="cancelRestore" class="bg-gray-400 text-white py-2 px-4 rounded hover:bg-gray-500">
                                                    Cancel
                                                </button>
                                                <button id="confirmRestore" class="bg-red-600 text-white py-2 px-4 rounded hover:bg-red-700">
                                                    Confirm Restore
                                                </button>
                                            </div>
                                        </div>
                                    </div>



                                <?php endif; ?>

                            </div>
                        </div>
                    </section>



                </main>


            </div>

            <!-- 🔒 Screen Lockout Modal -->
            

            <!-- Toast Container -->
            <div id="toast-container" class="fixed top-5 right-5 flex flex-col space-y-2 z-50"></div>

            <!-- Logout Confirmation Modal -->
            <div id="logout-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-[9999]">
                <div class="bg-white  rounded-lg shadow-lg p-6 w-80">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Confirm Logout</h2>
                    <p class="text-sm text-gray-600 mb-6">Are you sure you want to log out?</p>
                    <div class="flex justify-end space-x-3">
                        <button id="cancel-logout" class="px-4 py-2 text-sm bg-gray-300 text-gray-800 rounded hover:bg-gray-400">Cancel</button>
                        <button id="confirm-logout" class="px-4 py-2 text-sm bg-red-600 text-white rounded hover:bg-red-700">Logout</button>
                    </div>
                </div>
            </div>




            <script src="toast_action/toast.js"></script>
            <script src="admin_actions/account_code.js"></script>
            <script src="admin_actions/departments.js"></script>
            <script src="admin_actions/payment_ref_category.js"></script>
            <script src="audit_actions/audit_logs.js"></script>
            <script src="admin_actions/top_particulars_data.js"></script>
            <script src="admin_actions/collector_overview.js"></script>
            <script src="treasurer_actions/dashboard_data.js"></script>
            <script src="treasurer_actions/dashboard_summary.js"></script>
            <script src="settings_action/settings_function.js"></script>
            <script src="screen_lockout.js"></script>

            <script>
                setInterval(() => {
                    fetch("check_session.php")
                        .then(res => res.json())
                        .then(data => {
                            if (!data.valid) {
                                // Session invalid → auto logout and reload
                                window.location.href = "index.php?session_expired=1";
                            }
                        });
                }, 3000); // every 10 seconds
            </script>


            <script>
                const logoutBtn = document.getElementById('logout-btn');
                const logoutModal = document.getElementById('logout-modal');
                const cancelLogout = document.getElementById('cancel-logout');
                const confirmLogout = document.getElementById('confirm-logout');

                // Show modal when logout is clicked
                logoutBtn.addEventListener('click', () => {
                    logoutModal.classList.remove('hidden');
                    logoutModal.classList.add('flex');
                });

                // Cancel logout
                cancelLogout.addEventListener('click', () => {
                    logoutModal.classList.remove('flex');
                    logoutModal.classList.add('hidden');
                });

                // Confirm logout (redirect to logout.php)
                confirmLogout.addEventListener('click', () => {
                    window.location.href = 'logout.php';
                });
            </script>

            <script>
                const profileBtn = document.getElementById("profile-btn");
                const dropdown = document.getElementById("dropdown-menu");

                profileBtn.addEventListener("click", () => {
                    dropdown.classList.toggle("invisible");
                    dropdown.classList.toggle("opacity-0");
                    dropdown.classList.toggle("scale-95");
                });

                // Optional: Click outside to close
                document.addEventListener("click", function(e) {
                    if (!profileBtn.contains(e.target) && !dropdown.contains(e.target)) {
                        dropdown.classList.add("invisible", "opacity-0", "scale-95");
                    }
                });

               

                // Toggle dropdown
                document.getElementById("add-dropdown-btn").addEventListener("click", function() {
                    document.getElementById("add-dropdown-menu").classList.toggle("hidden");
                });

                // Optional: Close dropdown when clicking outside
                window.addEventListener("click", function(e) {
                    const dropdown = document.getElementById("add-dropdown-menu");
                    const button = document.getElementById("add-dropdown-btn");
                    if (!button.contains(e.target) && !dropdown.contains(e.target)) {
                        dropdown.classList.add("hidden");
                    }
                });
            </script>

            <script>

            </script>



            <script>
                //ADDING THE EMPLOYEES
                const addEmployeeBtn = document.getElementById('add-employee-btn');
                const employeeModal = document.getElementById('employee-modal');
                const cancelBtn = document.getElementById('cancel-btn');
                const saveBtn = document.getElementById('save-btn');
                const togglePassword = document.getElementById('toggle-password');
                const addEmployeeForm = document.getElementById('add-employee-form');
                const passwordInput = document.getElementById('employee-password');
                const togglePasswordIcon = document.getElementById('toggle-password-icon');
                const generateBtn = document.getElementById('generate-password');

                // Modal show
                addEmployeeBtn.addEventListener('click', () => {
                    employeeModal.classList.remove('hidden');
                });

                // Modal hide
                cancelBtn.addEventListener('click', () => {
                    employeeModal.classList.add('hidden');
                    addEmployeeForm.reset();
                });


                function randomDigits(len = 4) {
                    return Math.floor(Math.random() * Math.pow(10, len)).toString();
                }

                function clean(str) {
                    return str.trim().toLowerCase().replace(/[^a-z]/g, "");
                }

                // Generate username variations
                function generateRandomEmail(first, last) {
                    first = clean(first);
                    last = clean(last);

                    const patterns = [
                        `${first}${last}`,
                        `${last}${first}`,
                        `${first}_${last}`,
                        `${last}_${first}`,
                        `${first}.${last}`,
                        `${last}.${first}`,
                    ];

                    const pattern = patterns[Math.floor(Math.random() * patterns.length)];
                    return `${pattern}${randomDigits(4)}@gmail.com`;
                }

                // Mask email (m****e@gmail.com style)
                function maskEmail(email) {
                    const [user, domain] = email.split("@");
                    if (user.length <= 2) return user[0] + "****@" + domain;

                    return user[0] + "****" + user[user.length - 1] + "@" + domain;
                }

                // Generate email
                document.getElementById("generate-email").addEventListener("click", function() {
                    const first = document.getElementById("first-name").value;
                    const last = document.getElementById("last-name").value;

                    if (!first || !last) {
                        showToast("Enter first and last name first.", "error");
                        return;
                    }

                    const realEmail = generateRandomEmail(first, last);
                    document.getElementById("email").value = realEmail; // real value (hidden)
                    document.getElementById("email-masked").value = maskEmail(realEmail); // masked value
                });

                // Generate password
                document.getElementById("generate-password").addEventListener("click", function() {
                    const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()";
                    let pass = "";
                    for (let i = 0; i < 12; i++)
                        pass += chars[Math.floor(Math.random() * chars.length)];

                    document.getElementById("employee-password").value = pass; // real password
                    document.getElementById("password-masked").value = "************"; // always masked
                });

                const loadingOverlay = document.getElementById('loading-overlay');
                saveBtn.addEventListener('click', async (event) => {
                    event.preventDefault();

                    const firstName = document.getElementById('first-name').value.trim();
                    const lastName = document.getElementById('last-name').value.trim();
                    const position = document.getElementById('employee-position').value;
                    const recipientEmail = document.getElementById('recipient-email').value.trim();
                    const email = document.getElementById('email').value.trim();
                    const password = passwordInput.value;

                    const errors = [];

                    if (!firstName) errors.push('First name is required');
                    if (!lastName) errors.push('Last name is required');
                    if (!recipientEmail) errors.push('Email is required');
                    if (!email) errors.push('Email is required');
                    if (!password) errors.push('Password is required');
                    else if (password.length < 8) errors.push('Password must be at least 8 characters');

                    if (errors.length > 0) {
                        showToast('Please fix the following errors:\n' + errors.join('\n'), 'error');
                        return;
                    }

                    // 🌀 Show loading overlay
                    loadingOverlay.classList.remove('hidden');

                    const formData = new FormData();
                    formData.append('firstname', firstName);
                    formData.append('lastname', lastName);
                    formData.append('position', position);
                    formData.append('recipient-email', recipientEmail);
                    formData.append('email', email);
                    formData.append('password', password);

                    try {
                        const response = await fetch('admin_actions/add_employee.php', {
                            method: 'POST',
                            body: formData,
                        });

                        const result = await response.json();
                        console.log('Server response:', result);

                        // 🌀 Hide loader
                        loadingOverlay.classList.add('hidden');

                        if (result.status === "success") {
                            showToast(result.message, 'success');
                            employeeModal.classList.add('hidden');
                            addEmployeeForm.reset();

                            // ✅ Record audit
                            const employeeName = formData.get('firstname') + " " + formData.get('lastname');
                            const position = formData.get('position');
                            const action = `Added Employee ${employeeName} as ${position}`;

                            fetch('audit_actions/record_audit_ajax.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        action
                                    })
                                })
                                .then(res => res.json())
                                .then(auditResult => console.log("Audit recorded:", auditResult))
                                .catch(err => console.error("Audit logging failed:", err));

                        } else {
                            showToast('Failed to add employee: ' + result.message, 'error');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showToast('Error: ' + error.message, 'error');
                        loadingOverlay.classList.add('hidden'); // Ensure loader hides on error
                    }
                });




                //-----------------------------------------------------------------------------------------------------------------------

                //DISPLAYING/ FILTERING EMPLOYEES TABLE FORM

                let currentPage = 1;
                let employeesPerPage = calculateEmployeesPerPage();

                function calculateEmployeesPerPage() {
                    const rowHeight = 70;
                    const availableHeight = window.innerHeight - 350;
                    const count = Math.floor(availableHeight / rowHeight);
                    return Math.max(count, 3);
                }

                window.addEventListener("resize", () => {
                    employeesPerPage = calculateEmployeesPerPage();
                    renderPage(currentPage);
                    renderPagination(filteredEmployees.length);
                });



                // Function to fetch employee data
                async function fetchEmployees() {
                    try {
                        const response = await fetch('admin_actions/get_employees.php');
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const employees = await response.json();
                        allEmployees = employees; // Store all employees
                        filteredEmployees = [...employees]; // Initialize filtered list
                        renderPage(currentPage); // Initial page render
                    } catch (error) {
                        console.error('Error fetching employees:', error);
                        document.getElementById('employee-table-body').innerHTML = '<p class="text-red-500">Failed to load employee data.</p>';
                    }
                }

                function maskEmail(email) {
                    const [username, domain] = email.split("@");

                    // Always use exactly 6 stars
                    const stars = "******";

                    // If username is only 1 character
                    if (username.length === 1) {
                        return username + stars + "@" + domain;
                    }

                    const firstChar = username[0];
                    const lastChar = username[username.length - 1];

                    return `${firstChar}${stars}${lastChar}@${domain}`;
                }

                function displayValue(value) {
                    return value && value !== "null" ? value : "-";
                }


                // Function to render employee rows based on page
                function renderPage(page) {
                    const start = (page - 1) * employeesPerPage;
                    const end = start + employeesPerPage;
                    const pageEmployees = filteredEmployees.slice(start, end);

                    const tableBody = document.getElementById('employee-table-body');
                    tableBody.innerHTML = ''; // Clear existing rows

                    pageEmployees.forEach(employee => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                    <td class="p-3 border border-gray-400 p-2">${displayValue(employee.id)}</td>
            <td class="p-3 border border-gray-400 p-2">${displayValue(employee.firstname)}</td>
            <td class="p-3 border border-gray-400 p-2">${displayValue(employee.lastname)}</td>
            <td class="p-3 border border-gray-400 p-2">
                ${employee.email ? maskEmail(employee.email) : "-"}
            </td>
            <td class="p-3 border border-gray-400 p-2">${displayValue(employee.position)}</td>
            <td class="p-3 border border-gray-400 p-2">${displayValue(employee.phone)}</td>
            <td class="p-3 border border-gray-400 p-2">${displayValue(employee.address)}</td>

                    <td class="p-3 border border-gray-400 p-2">
                      <center>
    <!-- Edit Icon Button -->
    <button class="edit-button text-blue-600 hover:text-blue-800" data-employee-id="${employee.id}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15.232 5.232l3.536 3.536M16.768 4.768a2 2 0 112.828 2.828L7 20H4v-3L16.768 4.768z" />
        </svg>
    </button>

    <!-- Delete Icon Button -->
    <button class="delete-button text-red-600 hover:text-red-800 ml-2" data-employee-id="${employee.id}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M10 4V2h4v2" />
        </svg>
    </button>
</center>



                    </td>
                    `;
                        tableBody.appendChild(row);
                    });

                    renderPagination(filteredEmployees.length); // Update pagination
                }

                // Function to render pagination
                function renderPagination(totalCount) {
                    const pagination = document.getElementById('pagination-controls');
                    pagination.innerHTML = '';

                    const totalPages = Math.ceil(totalCount / employeesPerPage);
                    if (totalPages <= 1) return;

                    // Wrapper for pagination buttons
                    const wrapper = document.createElement('div');
                    wrapper.className = 'flex justify-center items-center flex-wrap gap-1 mb-2';

                    const createPageButton = (label, page, disabled = false, isActive = false) => {
                        const btn = document.createElement('button');
                        btn.textContent = label;

                        if (disabled) {
                            btn.disabled = true;
                            btn.className = 'px-3 py-1 rounded-md text-sm border bg-gray-300 text-gray-500 cursor-not-allowed';
                        } else if (isActive) {
                            btn.className = 'px-3 py-1 rounded-md text-sm border bg-blue-600 text-white border-blue-600';
                        } else {
                            btn.className = 'px-3 py-1 rounded-md text-sm border bg-gray-200 text-gray-700 hover:bg-blue-500 hover:text-white transition';
                        }

                        btn.addEventListener('click', () => {
                            currentPage = page;
                            renderPage(currentPage);
                            renderPagination(totalCount); // Re-render pagination after page change
                        });

                        return btn;
                    };

                    // First & Previous
                    wrapper.appendChild(createPageButton('«', 1, currentPage === 1));
                    wrapper.appendChild(createPageButton('←', currentPage - 1, currentPage === 1));

                    // Page numbers (shows current ±1 pages)
                    let startPage = Math.max(1, currentPage - 1);
                    let endPage = Math.min(totalPages, startPage + 2);
                    if (endPage - startPage < 2 && startPage > 1) {
                        startPage = Math.max(1, endPage - 2);
                    }

                    for (let i = startPage; i <= endPage; i++) {
                        wrapper.appendChild(createPageButton(i, i, false, currentPage === i));
                    }

                    // Next & Last
                    wrapper.appendChild(createPageButton('→', currentPage + 1, currentPage === totalPages));
                    wrapper.appendChild(createPageButton('»', totalPages, currentPage === totalPages));

                    // Page label
                    const pageLabel = document.createElement('div');
                    pageLabel.className = 'text-sm text-gray-700 mt-2 text-center';
                    pageLabel.textContent = `Page ${currentPage} of ${totalPages}`;

                    // Append
                    pagination.className = 'flex flex-col items-center';
                    pagination.appendChild(wrapper);
                    pagination.appendChild(pageLabel);
                }


                // Search functionality
                document.getElementById('employee-name-search').addEventListener('input', () => {
                    filterAndSearchEmployees();
                });

                // Filter by Position functionality
                document.getElementById('employee-position-filter').addEventListener('change', () => {
                    filterAndSearchEmployees();
                });

                // Filter and search function
                function filterAndSearchEmployees() {
                    const searchQuery = document.getElementById('employee-name-search').value.toLowerCase();
                    const positionFilter = document.getElementById('employee-position-filter').value;

                    filteredEmployees = allEmployees.filter(employee => {
                        const matchesSearch = employee.firstname.toLowerCase().includes(searchQuery) || employee.lastname.toLowerCase().includes(searchQuery);
                        const matchesPosition = positionFilter ? employee.position === positionFilter : true;

                        return matchesSearch && matchesPosition;
                    });

                    renderPage(1); // Re-render with updated filtered employees
                }

                // Initial fetch of employees when the page loads
                fetchEmployees();







                // Add this code to your existing JavaScript, preferably after the fetchEmployees function

                let currentEmployeeId = null;

                document.addEventListener('click', async (e) => {
                    const editBtn = e.target.closest('.edit-button');
                    if (editBtn) {
                        const employeeId = editBtn.getAttribute('data-employee-id');
                        currentEmployeeId = employeeId;

                        try {
                            const response = await fetch(`admin_actions/get_employee.php?id=${employeeId}`);
                            const result = await response.json();

                            if (!response.ok || !result.success) {
                                throw new Error(result.error || 'Failed to fetch employee data');
                            }

                            const employee = result.data;

                            document.getElementById('edit-first-name').value = employee.firstname;
                            document.getElementById('edit-last-name').value = employee.lastname;
                            document.getElementById('edit-employee-position').value = employee.position;

                            document.getElementById('edit-employee-modal').classList.remove('hidden');

                        } catch (error) {
                            console.error('Error fetching employee data:', error);
                            showToast('Error: ' + error.message, 'error');
                        }
                    }
                });

                // Save edited employee
                document.getElementById('edit-save-btn').addEventListener('click', async (e) => {
                    e.preventDefault();

                    // Get values from modal
                    const firstName = document.getElementById('edit-first-name').value.trim();
                    const lastName = document.getElementById('edit-last-name').value.trim();
                    const position = document.getElementById('edit-employee-position').value;


                    // Validation
                    const errors = [];
                    if (!firstName) errors.push('First name is required');
                    if (!lastName) errors.push('Last name is required');


                    if (errors.length > 0) {
                        showToast('Please fix the following errors:\n\n' + errors.join('\n'), 'error');
                        return;
                    }


                    const formData = new FormData();
                    formData.append('id', currentEmployeeId);
                    formData.append('firstname', firstName);
                    formData.append('lastname', lastName);
                    formData.append('position', position);


                    try {
                        const response = await fetch('admin_actions/update_employee.php', {
                            method: 'POST',
                            body: formData,
                        });

                        const responseText = await response.text();

                        if (response.ok && responseText.includes("Employee updated successfully")) {
                            showToast('Employee updated successfully!', 'success');
                            document.getElementById('edit-employee-modal').classList.add('hidden');

                            // ✅ Record audit
                            const employeeName = formData.get('firstname') + " " + formData.get('lastname');
                            const action = `Updated ${employeeName}'s details`;

                            fetch('audit_actions/record_audit_ajax.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        action
                                    })
                                })
                                .then(res => res.json())
                                .then(auditResult => console.log("Audit recorded:", auditResult))
                                .catch(err => console.error("Audit logging failed:", err));

                        } else {
                            throw new Error(responseText || 'Failed to update employee');
                        }
                    } catch (error) {
                        console.error('Error updating employee:', error);
                        showToast(`Failed to update employee: ${error.message}`, 'error');
                    }

                });

                // Close modal
                document.getElementById('edit-cancel-btn').addEventListener('click', () => {
                    document.getElementById('edit-employee-modal').classList.add('hidden');
                });

                // Global variable to track which employee will be deleted
                let employeeToDelete = null;

                // Event delegation for delete button clicks
                document.addEventListener('click', function(e) {
                    if (e.target.closest('.delete-button')) {
                        const button = e.target.closest('.delete-button');
                        employeeToDelete = button.getAttribute('data-employee-id');
                        document.getElementById('delete-employee-modal').classList.remove('hidden');
                    }
                });

                // Cancel button hides modal
                document.getElementById('delete-cancel-btn').addEventListener('click', () => {
                    document.getElementById('delete-employee-modal').classList.add('hidden');
                    employeeToDelete = null;
                });

                // Confirm Delete
                document.getElementById('delete-employee-btn').addEventListener('click', async () => {
                    if (!employeeToDelete) return;

                    try {
                        const response = await fetch('admin_actions/delete_employee.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'id=' + encodeURIComponent(employeeToDelete)
                        });



                        const result = await response.json();
                        document.getElementById('delete-employee-modal').classList.add('hidden');

                        if (result.success) {
                            const employeeName = `${result.firstname} ${result.lastname}`;
                            showToast(`Employee ${employeeName} deleted successfully!`, 'success');

                            // Update table
                            allEmployees = allEmployees.filter(emp => emp.id != employeeToDelete);
                            filteredEmployees = [...allEmployees];
                            renderPage(currentPage);


                            // Record audit
                            const action = `Deleted employee ${employeeName}`;
                            fetch('audit_actions/record_audit_ajax.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        action
                                    })
                                })
                                .then(res => res.json())
                                .then(auditResult => console.log("Audit recorded:", auditResult))
                                .catch(err => console.error("Audit logging failed:", err));

                        } else {
                            showToast(`Failed to delete employee. (${result.error})`, 'error');
                        }

                    } catch (error) {
                        console.error('Error deleting employee:', error);
                        showToast('Server error occurred.', 'error');
                    }
                });
            </script>

            <script>
                let paymentData = [];

                document.addEventListener('DOMContentLoaded', async () => {
                    await loadPaymentReferences();
                    setupFilters();
                    setupButtons();
                });

                // Fetch and render all payment references
                async function loadPaymentReferences() {
                    try {
                        const response = await fetch('admin_actions/get_payment_references.php');
                        const result = await response.json();

                        if (!response.ok || !result.success) {
                            throw new Error(result.error || 'Failed to load payment references');
                        }

                        paymentData = result.data;

                        // ✅ Render with pagination only
                        renderPaymentPaginatedTable(
                            paymentData,
                            "payment-table-body",
                            "payment-pagination"
                        );

                    } catch (error) {
                        console.error('Error loading payment references:', error);
                        showToast('Error: ' + error.message, 'error');
                    }
                }


                document.addEventListener("DOMContentLoaded", () => {
                    const tableViewSelect = document.getElementById("table-view");

                    // Mapping select value → div ID
                    const views = {
                        "lgu": "lgu-view",
                        "account-codes": "account-codes-view",
                        "departments": "departments-view",
                        "categorization": "categorization-view"
                    };

                    function switchView(selected) {
                        // Hide all views
                        Object.values(views).forEach(viewId => {
                            document.getElementById(viewId).classList.add("hidden");
                        });
                        // Show selected
                        document.getElementById(views[selected]).classList.remove("hidden");

                        // Load data depending on selection
                        if (selected === "lgu") {
                            loadPaymentReferences();
                        } else if (selected === "account-codes") {
                            loadAccountCodesTable();
                        } else if (selected === "departments") {
                            loadDepartmentsTable();
                        } else if (selected === "categorization") {
                            loadCategorizationsTable();
                        }
                    }

                    // On load, default to LGU view
                    switchView("lgu");

                    // Change view on dropdown change
                    tableViewSelect.addEventListener("change", (e) => {
                        switchView(e.target.value);
                    });
                });







                // Global pagination settings for Payments
                let currentPaymentPage = 1;
                let paymentRowsPerPage = calculatePaymentRowsPerPage();

                function calculatePaymentRowsPerPage() {
                    const rowHeight = 70; // average payment row height
                    const availableHeight = window.innerHeight - 350;
                    // subtract header/search/modals/pagination depending on your layout

                    const count = Math.floor(availableHeight / rowHeight);

                    return Math.max(count, 3); // minimum of 4 rows for usability
                }

                window.addEventListener("resize", () => {

                    // Recalculate rows per page based on the new screen height
                    paymentRowsPerPage = calculatePaymentRowsPerPage();

                    if (paymentData && paymentData.length > 0) {

                        // Recalculate total pages
                        const totalPages = Math.ceil(paymentData.length / paymentRowsPerPage);

                        // Ensure currentPage does not exceed new totalPages
                        if (currentPaymentPage > totalPages) {
                            currentPaymentPage = totalPages;
                        }

                        // Re-render table with updated pagination
                        renderPaymentPaginatedTable(
                            paymentData,
                            "payment-table-body",
                            "payment-pagination"
                        );


                    }
                });




                // Main function to render table data with pagination
                function renderPaymentPaginatedTable(data, tableBodyId, paginationId) {
                    const tableBody = document.getElementById(tableBodyId);
                    tableBody.innerHTML = "";

                    const paginationContainer = document.getElementById(paginationId);
                    paginationContainer.innerHTML = "";

                    if (!data || data.length === 0) {
                        tableBody.innerHTML = `<tr><td colspan="6" class="text-center py-2 text-gray-500">No data available</td></tr>`;
                        return;
                    }

                    const totalPages = Math.ceil(data.length / paymentRowsPerPage);
                    const startIndex = (currentPaymentPage - 1) * paymentRowsPerPage;
                    const endIndex = startIndex + paymentRowsPerPage;
                    const pageData = data.slice(startIndex, endIndex);

                    // Append rows
                    pageData.forEach(ref => {
                        const row = document.createElement("tr");
                        row.className =
                            "border-b border-gray-300 hover:bg-gray-50";
                        row.innerHTML = `
            <td class="p-3 border border-gray-400 p-2">${ref.department || ""}</td>
            <td class="p-3 border border-gray-400 p-2">${ref.account_code || "NULL"}</td>
            <td class="p-3 border border-gray-400 p-2">${ref.particulars || ""}</td>
            <td class="p-3 border border-gray-400 p-2">${ref.categorization || ""}</td>
            <td class="p-3 border border-gray-400 p-2">₱${ref.amount || "NULL"}</td>
            <td class="p-3 border border-gray-400 p-2">
               <center>
    <!-- Edit Icon Button -->
    <button class="edit-payment-btn text-blue-600 hover:text-blue-800 transition" data-id="${ref.id}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15.232 5.232l3.536 3.536M16.768 4.768a2 2 0 112.828 2.828L7 20H4v-3L16.768 4.768z" />
        </svg>
    </button>

    <!-- Delete Icon Button -->
    <button class="delete-payment-btn text-red-600 hover:text-red-800 ml-2 transition" data-id="${ref.id}">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M10 4V2h4v2" />
        </svg>
    </button>
</center>

            </td>`;
                        tableBody.appendChild(row);
                    });

                    // Reattach handlers after rendering
                    attachEditHandlers();
                    attachDeleteHandlers();

                    // Pagination controls
                    displayPaymentPaginationControls(data, totalPages, tableBodyId, paginationId);
                }

                let paymentIdToDelete = null; // store which id to delete

                // Attach delete handlers
                function attachDeleteHandlers() {
                    document.querySelectorAll('.delete-payment-btn').forEach(button => {
                        button.addEventListener('click', () => {
                            paymentIdToDelete = button.dataset.id;
                            document.getElementById('deleteConfirmModal').classList.remove('hidden');
                        });
                    });
                }

                // Cancel button
                document.getElementById('cancelDeleteBtn').addEventListener('click', () => {
                    paymentIdToDelete = null;
                    document.getElementById('deleteConfirmModal').classList.add('hidden');
                });

                // Confirm button
                document.getElementById('confirmDeleteBtn').addEventListener('click', async () => {
                    if (!paymentIdToDelete) return;

                    try {
                        const response = await fetch('admin_actions/delete_payment_reference.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                id: paymentIdToDelete
                            })
                        });

                        const result = await response.json();

                        if (result.success) {
                            showToast('Payment reference deleted successfully!', 'success');
                            await loadPaymentReferences(); // reload table
                        } else {
                            showToast('Delete failed: ' + result.error, 'error');
                        }
                    } catch (error) {
                        showToast('An error occurred: ' + error.message, 'error');
                    }

                    paymentIdToDelete = null;
                    document.getElementById('deleteConfirmModal').classList.add('hidden');
                });


                // Pagination control generator
                function displayPaymentPaginationControls(data, totalPages, tableBodyId, paginationId) {
                    const container = document.getElementById(paginationId);
                    const wrapper = document.createElement("div");
                    wrapper.className = "flex justify-center items-center flex-wrap gap-1 mb-2";

                    const createPageButton = (label, page, disabled = false, isActive = false) => {
                        const btn = document.createElement("button");
                        btn.textContent = label;

                        if (disabled) {
                            btn.disabled = true;
                            btn.className =
                                "px-3 py-1 rounded-md text-sm border bg-gray-300 text-gray-500 cursor-not-allowed";
                        } else if (isActive) {
                            btn.className =
                                "px-3 py-1 rounded-md text-sm border bg-blue-600 text-white border-blue-600";
                        } else {
                            btn.className =
                                "px-3 py-1 rounded-md text-sm border bg-gray-200 text-gray-700 hover:bg-blue-500 hover:text-white transition";
                        }

                        btn.addEventListener("click", () => {
                            currentPaymentPage = page;
                            renderPaymentPaginatedTable(data, tableBodyId, paginationId);
                        });

                        return btn;
                    };

                    // First & Previous
                    wrapper.appendChild(createPageButton("«", 1, currentPaymentPage === 1));
                    wrapper.appendChild(createPageButton("←", currentPaymentPage - 1, currentPaymentPage === 1));

                    // Page numbers (show max 3 at a time)
                    let startPage = Math.max(1, currentPaymentPage - 1);
                    let endPage = Math.min(totalPages, startPage + 2);
                    if (endPage - startPage < 2 && startPage > 1) {
                        startPage = Math.max(1, endPage - 2);
                    }

                    for (let i = startPage; i <= endPage; i++) {
                        wrapper.appendChild(createPageButton(i, i, false, currentPaymentPage === i));
                    }

                    // Next & Last
                    wrapper.appendChild(
                        createPageButton("→", currentPaymentPage + 1, currentPaymentPage === totalPages)
                    );
                    wrapper.appendChild(
                        createPageButton("»", totalPages, currentPaymentPage === totalPages)
                    );

                    // Page label
                    const pageLabel = document.createElement("div");
                    pageLabel.className = "text-sm text-gray-700 mt-2 text-center";
                    pageLabel.textContent = `Page ${currentPaymentPage} of ${totalPages}`;

                    container.className = "flex flex-col items-center";
                    container.appendChild(wrapper);
                    container.appendChild(pageLabel);
                }





                // Global settings
                let rowsPerPage = getDynamicRowsPerPage();

                function getDynamicRowsPerPage() {
                    const height = window.innerHeight;

                    if (height < 600) return 3; // small screens
                    if (height < 800) return 5; // medium screens
                    if (height < 1000) return 7; // large screens
                    return 10; // extra large monitors
                }

                let currentLGUPage = {
                    accountCodes: 1,
                    departments: 1,
                    categorization: 1
                };

                let lastRowsPerPage = rowsPerPage;

                window.addEventListener("resize", () => {
                    const newRows = getDynamicRowsPerPage();

                    if (newRows !== lastRowsPerPage) {
                        rowsPerPage = newRows;
                        lastRowsPerPage = newRows;

                        // Re-render all LGU tables with updated rows per page
                        if (LGUAccountCodesData) {
                            renderLGUPaginatedTable(
                                LGUAccountCodesData,
                                "account-codes-table-body",
                                "account-codes-pagination",
                                "accountCodes"
                            );
                        }

                        if (LGUDepartmentsData) {
                            renderLGUPaginatedTable(
                                LGUDepartmentsData,
                                "departments-table-body",
                                "departments-pagination",
                                "departments"
                            );
                        }

                        if (LGUCategorizationData) {
                            renderLGUPaginatedTable(
                                LGUCategorizationData,
                                "categorization-table-body",
                                "categorization-pagination",
                                "categorization"
                            );
                        }
                    }
                });


                // Main function to render table data with pagination
                function renderLGUPaginatedTable(data, tableBodyId, paginationId, key) {
                    const tableBody = document.getElementById(tableBodyId);
                    tableBody.innerHTML = "";

                    const paginationContainer = document.getElementById(paginationId);
                    paginationContainer.innerHTML = "";

                    if (!data || data.length === 0) {
                        tableBody.innerHTML = `<tr><td colspan="6" class="text-center py-2 text-gray-500">No data available</td></tr>`;
                        return;
                    }

                    const totalPages = Math.ceil(data.length / rowsPerPage);
                    const startIndex = (currentLGUPage[key] - 1) * rowsPerPage;
                    const endIndex = startIndex + rowsPerPage;
                    const pageData = data.slice(startIndex, endIndex);

                    // Append rows
                    pageData.forEach(item => {
                        const row = document.createElement("tr");
                        row.className = "border-b border-gray-300 hover:bg-gray-50";

                        if (key === "accountCodes") {
                            row.innerHTML = `
<td class="p-3 border border-gray-400">${item.code || ""}</td>
<td class="p-3 border border-gray-400">
    <div class="flex justify-center items-center gap-3">
        <button class="edit-code-btn text-blue-600 hover:text-blue-800" data-id="${item.id}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15.232 5.232l3.536 3.536M16.768 4.768a2 2 0 112.828 2.828L7 20H4v-3L16.768 4.768z"/>
            </svg>
        </button>

        <button class="delete-code-btn text-red-600 hover:text-red-800" data-id="${item.id}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M10 4V2h4v2"/>
            </svg>
        </button>
    </div>
</td>
`;

                        } else if (key === "departments") {
                            row.innerHTML = `
<td class="p-3 border border-gray-400">${item.department || ""}</td>
<td class="p-3 border border-gray-400">
    <div class="flex justify-center items-center gap-3">
        <button class="edit-department-btn text-blue-600 hover:text-blue-800" data-id="${item.id}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15.232 5.232l3.536 3.536M16.768 4.768a2 2 0 112.828 2.828L7 20H4v-3L16.768 4.768z"/>
            </svg>
        </button>

        <button class="delete-department-btn text-red-600 hover:text-red-800" data-id="${item.id}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M10 4V2h4v2"/>
            </svg>
        </button>
    </div>
</td>`;

                        } else if (key === "categorization") {
                            row.innerHTML = `
<td class="p-3 border border-gray-400">${item.categorization || ""}</td>
<td class="p-3 border border-gray-400">
    <div class="flex justify-center items-center gap-3">
        <button class="edit-categorization-btn text-blue-600 hover:text-blue-800" data-id="${item.id}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15.232 5.232l3.536 3.536M16.768 4.768a2 2 0 112.828 2.828L7 20H4v-3L16.768 4.768z"/>
            </svg>
        </button>

        <button class="delete-categorization-btn text-red-600 hover:text-red-800" data-id="${item.id}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M10 4V2h4v2"/>
            </svg>
        </button>
    </div>
</td>`;

                        }


                        tableBody.appendChild(row);
                    });

                    // Create pagination controls
                    displayLGUPaginationControls(data, key, paginationContainer, totalPages, tableBodyId, paginationId);
                }

                // Pagination control generator
                function displayLGUPaginationControls(data, key, container, totalPages, tableBodyId, paginationId) {
                    const wrapper = document.createElement("div");
                    wrapper.className = "flex justify-center items-center flex-wrap gap-1 mb-2";

                    const createPageButton = (label, page, disabled = false, isActive = false) => {
                        const btn = document.createElement("button");
                        btn.textContent = label;

                        if (disabled) {
                            btn.disabled = true;
                            btn.className =
                                "px-3 py-1 rounded-md text-sm border bg-gray-300 text-gray-500 cursor-not-allowed";
                        } else if (isActive) {
                            btn.className =
                                "px-3 py-1 rounded-md text-sm border bg-blue-600 text-white border-blue-600";
                        } else {
                            btn.className =
                                "px-3 py-1 rounded-md text-sm border bg-gray-200 text-gray-700 hover:bg-blue-500 hover:text-white transition";
                        }

                        btn.addEventListener("click", () => {
                            currentLGUPage[key] = page;
                            renderLGUPaginatedTable(data, tableBodyId, paginationId, key);
                        });

                        return btn;
                    };

                    // First & Previous
                    wrapper.appendChild(createPageButton("«", 1, currentLGUPage[key] === 1));
                    wrapper.appendChild(createPageButton("←", currentLGUPage[key] - 1, currentLGUPage[key] === 1));

                    // Page numbers
                    let startPage = Math.max(1, currentLGUPage[key] - 1);
                    let endPage = Math.min(totalPages, startPage + 2);
                    if (endPage - startPage < 2 && startPage > 1) {
                        startPage = Math.max(1, endPage - 2);
                    }

                    for (let i = startPage; i <= endPage; i++) {
                        wrapper.appendChild(createPageButton(i, i, false, currentLGUPage[key] === i));
                    }

                    // Next & Last
                    wrapper.appendChild(createPageButton("→", currentLGUPage[key] + 1, currentLGUPage[key] === totalPages));
                    wrapper.appendChild(createPageButton("»", totalPages, currentLGUPage[key] === totalPages));

                    // Page label
                    const pageLabel = document.createElement("div");
                    pageLabel.className = "text-sm text-gray-700 mt-2 text-center";
                    pageLabel.textContent = `Page ${currentLGUPage[key]} of ${totalPages}`;

                    container.className = "flex flex-col items-center";
                    container.appendChild(wrapper);
                    container.appendChild(pageLabel);
                }


                // Setup filtering inputs
                function setupFilters() {
                    const departmentFilter = document.getElementById('department-filter');
                    const accountCodeFilter = document.getElementById('account-code-filter');
                    const particularsSearch = document.getElementById('particulars-search');

                    [departmentFilter, accountCodeFilter, particularsSearch].forEach(input => {
                        input.addEventListener('input', filterTable);
                        input.addEventListener('change', filterTable);
                    });
                }

                // Apply filters to table
                function filterTable() {
                    const department = document.getElementById('department-filter').value.toLowerCase();
                    const accountCode = document.getElementById('account-code-filter').value.toLowerCase();
                    const particulars = document.getElementById('particulars-search').value.toLowerCase();

                    const filtered = paymentData.filter(item => {
                        return (!department || (item.department || '').toLowerCase().includes(department)) &&
                            (!accountCode || (item.account_code || '').toLowerCase().includes(accountCode)) &&
                            (!particulars || (item.particulars || '').toLowerCase().includes(particulars));
                    });

                    // ✅ Render only filtered data with pagination
                    renderPaymentPaginatedTable(
                        filtered,
                        "payment-table-body",
                        "payment-pagination"
                    );
                }


                // Setup Add button
                function setupButtons() {
                    const addPaymentBtn = document.getElementById('add-payment-btn');
                    const paymentModal = document.getElementById('payment-modal');
                    const saveAddBtn = document.getElementById('save-btn-lgu');

                    // Open Add Modal
                    addPaymentBtn.addEventListener('click', () => paymentModal.classList.remove('hidden'));

                    // Save New Payment Reference
                    saveAddBtn.addEventListener('click', async () => {
                        const data = {
                            department: document.getElementById('department-select').value || null, // ✅ optional
                            account_code: document.getElementById('account-code-select').value || null, // ✅ optional
                            categorization: document.getElementById('categorization-select').value, // 🔹 required
                            particular: document.getElementById('particular-modal').value.trim(),
                            amount: document.getElementById('particular-amount-modal').value.trim()
                        };

                        // Validation (only check required fields)
                        if (!data.categorization || !data.particular || !data.amount) {
                            showToast('Please fill in Categorization, Particular, and Amount.', 'error');
                            return;
                        }

                        try {
                            const response = await fetch('admin_actions/create_payment_reference.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(data)
                            });

                            const result = await response.json();
                            if (result.success) {
                                showToast('Payment reference saved!', 'success');
                                closeModal('payment-modal');
                                await loadPaymentReferences();

                                // 👉 Record audit
                                const action = `Added ${data.particular} in LGU payment reference (Categorization: ${data.categorization})`;
                                fetch('audit_actions/record_audit_ajax.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        action
                                    })
                                }).catch(err => console.error("Audit logging failed:", err));
                            } else {
                                showToast('Failed to save: ' + result.error, 'error');
                            }
                        } catch (err) {
                            showToast('An error occurred: ' + err.message, 'error');
                        }
                    });

                    // Update Existing Payment Reference
                    document.getElementById('update-btn-lgu').addEventListener('click', async () => {
                        const id = document.getElementById('edit-id').value;
                        const department = document.getElementById('edit-department').value || null; // ✅ optional
                        const account_code = document.getElementById('edit-account-code').value || null; // ✅ optional
                        const categorization = document.getElementById('edit-categorization').value; // 🔹 required
                        const particulars = document.getElementById('edit-particular').value;
                        const amount = document.getElementById('edit-amount').value;

                        if (!categorization || !particulars || !amount) {
                            showToast('Please fill in Categorization, Particular, and Amount.', 'error');
                            return;
                        }

                        try {
                            const response = await fetch('admin_actions/update_payment_reference.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    id,
                                    department,
                                    account_code,
                                    categorization,
                                    particulars,
                                    amount
                                })
                            });

                            const result = await response.json();
                            if (result.success) {
                                showToast('Payment reference updated successfully!', 'success');
                                closeModal('update-payment-modal');
                                await loadPaymentReferences();

                                // 👉 Record audit
                                const action = `Updated ${particulars} in LGU payment reference (Categorization: ${categorization})`;
                                fetch('audit_actions/record_audit_ajax.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        action
                                    })
                                }).catch(err => console.error("Audit logging failed:", err));
                            } else {
                                showToast('Update failed: ' + result.error, 'error');
                            }
                        } catch (error) {
                            showToast('An error occurred: ' + error.message, 'error');
                        }
                    });
                }



                // Attach edit buttons after rendering
                function attachEditHandlers() {
                    document.querySelectorAll('.edit-payment-btn').forEach(button => {
                        button.addEventListener('click', async () => {
                            const id = button.dataset.id;
                            document.getElementById('update-payment-modal').classList.remove('hidden');

                            try {
                                const response = await fetch(`admin_actions/get_payment_reference.php?id=${id}`);
                                const data = await response.json();

                                document.getElementById('edit-id').value = id;
                                document.getElementById('edit-department').value = data.department;
                                document.getElementById('edit-account-code').value = data.account_code;
                                document.getElementById('edit-particular').value = data.particulars;
                                document.getElementById('edit-categorization').value = data.categorization;
                                document.getElementById('edit-amount').value = data.amount;
                            } catch (error) {
                                showToast('Failed to load data for editing: ' + error.message, 'error');
                            }

                        });
                    });
                }

                // Close any modal by ID
                function closeModal() {
                    document.getElementById('payment-modal').classList.add('hidden');
                    document.getElementById('update-payment-modal').classList.add('hidden');
                }
            </script>



            <script>
                // Function to Show Specific Section
                function showSection(section) {
                    document.querySelectorAll('main section').forEach(el => {
                        el.classList.add('hidden');
                        el.classList.remove('fade-in'); // Remove previous animation class
                    });

                    const selectedSection = document.getElementById(section + '-section');
                    selectedSection.classList.remove('hidden');
                    selectedSection.classList.add('fade-in'); // Add animation effect

                    localStorage.setItem('activeSection', section);
                }



                document.addEventListener("DOMContentLoaded", function() {
                    // Check if user already has an active section stored
                    let activeSection = localStorage.getItem('activeSection');

                    // If none exists (first time after login), set dashboard as default
                    if (!activeSection) {
                        activeSection = 'dashboard';
                        localStorage.setItem('activeSection', activeSection);
                    }

                    // Show the stored or default section
                    showSection(activeSection);
                });

                // Navigation Links
                document.getElementById('dashboard-link').addEventListener('click', () => showSection('dashboard'));
                document.getElementById('employees-link').addEventListener('click', () => showSection('employees'));
                document.getElementById('reports-link').addEventListener('click', () => showSection('reports'));
                document.getElementById('lgu-payment-link').addEventListener('click', () => showSection('lgu-payment-references'));
                document.getElementById('Audit-Logs-link').addEventListener('click', () => showSection('audit'));
                document.getElementById('settings-link').addEventListener('click', () => showSection('settings'));
            </script>


            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    const filterPeriod = document.getElementById("filter-period");
                    const filterParticulars = document.getElementById("filter-particulars");

                    const totalTransactionsEl = document.getElementById("total-transactions");
                    const totalRevenueEl = document.getElementById("total-revenue");

                    let revenueChart, topParticularsChart, collectorsChart;

                    function createChart(ctx, type, data, options) {
                        return new Chart(ctx, {
                            type: type,
                            data: data,
                            options: options
                        });
                    }

                    async function fetchData(url) {
                        const res = await fetch(url);
                        return await res.json();
                    }

                    async function loadDashboard() {
                        // Load total transactions and revenue
                        const stats = await fetchData("admin_actions/total_stats.php");
                        totalTransactionsEl.textContent = stats.total_transactions ?? 0;
                        totalRevenueEl.textContent = `₱${parseFloat(stats.total_revenue ?? 0).toLocaleString()}`;

                        // Load Revenue Overview
                        const period = filterPeriod.value;
                        const revenueData = await fetchData(`admin_actions/revenue_overview.php?filter=${period}`);

                        const ctx = document.getElementById('revenueChart').getContext('2d');

                        if (revenueChart) {
                            // Update existing chart with animation
                            revenueChart.data.labels = revenueData.labels;
                            revenueChart.data.datasets[0].data = revenueData.totals;

                            // Optional: smooth transition for line color
                            revenueChart.data.datasets[0].borderColor = '#3b82f6';
                            revenueChart.data.datasets[0].backgroundColor = 'rgba(59, 130, 246, 0.2)';

                            revenueChart.update({
                                duration: 800, // animation duration in ms
                                easing: "easeOutQuart"
                            });
                        } else {
                            // Create chart for the first time
                            revenueChart = new Chart(ctx, {
                                type: "line",
                                data: {
                                    labels: revenueData.labels,
                                    datasets: [{
                                        label: "Revenue",
                                        data: revenueData.totals,
                                        borderColor: '#3b82f6',
                                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                                        fill: true,
                                        tension: 0.4, // smoother curve
                                        pointRadius: 4,
                                        pointHoverRadius: 6,
                                        borderWidth: 2
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    animation: {
                                        duration: 800,
                                        easing: "easeOutQuart"
                                    },
                                    scales: {
                                        x: {
                                            grid: {
                                                display: false
                                            },
                                            ticks: {
                                                color: '#374151'
                                            }
                                        },
                                        y: {
                                            beginAtZero: true,
                                            ticks: {
                                                color: '#374151',
                                                callback: value => `₱${value.toLocaleString()}`
                                            },
                                            grid: {
                                                color: 'rgba(0,0,0,0.05)'
                                            }
                                        }
                                    },
                                    plugins: {
                                        legend: {
                                            labels: {
                                                color: '#1f2937'
                                            }
                                        },
                                        tooltip: {
                                            backgroundColor: 'rgba(17, 24, 39, 0.9)',
                                            titleColor: '#fff',
                                            bodyColor: '#f3f4f6'
                                        }
                                    }
                                }
                            });
                        }


                        // Load Particulars Checklist
                        const topData = await fetchData(`admin_actions/top_particulars.php`);
                        filterParticulars.innerHTML = '';
                        const allParticulars = topData.map(item => item.particular);

                        allParticulars.forEach(p => {
                            const checkbox = document.createElement("div");
                            checkbox.innerHTML = `
                <label class="flex items-center space-x-2">
                    <input type="checkbox" class="particular-filter" value="${p}" checked />
                    <span>${p}</span>
                </label>
            `;
                            filterParticulars.appendChild(checkbox);
                        });

                        loadTopParticulars();
                        loadCollectors();
                    }

                    async function loadTopParticulars() {
                        const selected = Array.from(document.querySelectorAll(".particular-filter:checked"))
                            .map(el => el.value)
                            .join(",");

                        const url = selected ?
                            `admin_actions/top_particulars.php?particulars=${encodeURIComponent(selected)}` :
                            `admin_actions/top_particulars.php`;

                        const topData = await fetchData(url);

                        // Destroy existing chart if present
                        if (topParticularsChart) topParticularsChart.destroy();

                        const ctx = document.getElementById('topParticularsChart').getContext('2d');
                        topParticularsChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: topData.map(item => item.particular),
                                datasets: [{
                                    label: "Times Sold",
                                    data: topData.map(item => item.sold_count),
                                    backgroundColor: "rgba(16, 185, 129, 0.7)",
                                    borderRadius: 6
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false, // ✅ Allows resizing inside a container
                                scales: {
                                    x: {
                                        ticks: {
                                            color: "#374151",
                                            maxRotation: 45,
                                            minRotation: 0,
                                            autoSkip: true
                                        },
                                        grid: {
                                            display: false
                                        }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            color: "#374151"
                                        },
                                        grid: {
                                            color: "rgba(0, 0, 0, 0.05)"
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        labels: {
                                            color: "#1F2937"
                                        }
                                    },
                                    tooltip: {
                                        backgroundColor: "rgba(17, 24, 39, 0.9)",
                                        titleColor: "#fff",
                                        bodyColor: "#f3f4f6"
                                    }
                                },
                                animation: {
                                    duration: 400,
                                    easing: "easeOutQuart"
                                }
                            }
                        });
                    }



                    async function loadCollectors() {
                        const period = filterPeriod.value;
                        const data = await fetchData(`admin_actions/collectors_revenue.php?filter=${period}`);

                        if (collectorsChart) collectorsChart.destroy();
                        collectorsChart = createChart(document.getElementById('collectorsChart').getContext('2d'), 'bar', {
                            labels: data.map(item => item.collector),
                            datasets: [{
                                label: "Total Collected",
                                data: data.map(item => item.total_collected),
                                backgroundColor: "rgba(245, 158, 11, 0.7)"
                            }]
                        }, {
                            responsive: true
                        });
                    }

                    filterPeriod.addEventListener("change", () => {
                        loadDashboard();
                    });

                    filterParticulars.addEventListener("change", (e) => {
                        if (e.target.classList.contains("particular-filter")) {
                            loadTopParticulars();
                        }
                    });

                    loadDashboard();
                });
            </script>

            <script>
                const fromPicker = flatpickr("#report-date-from", {
                    dateFormat: "m/d/Y", // what user sees
                    altInput: true, // display a friendly format
                    altFormat: "m/d/Y", // mm/dd/yyyy for UI
                    dateFormat: "Y-m-d" // actual value in the input (for DB)
                });

                const toPicker = flatpickr("#report-date-to", {
                    altInput: true,
                    altFormat: "m/d/Y",
                    dateFormat: "Y-m-d"
                });

                // Reset filters and clear Flatpickr fields
                document.getElementById("resetFilters").addEventListener("click", () => {
                    fromPicker.clear(); // clears from date
                    toPicker.clear(); // clears to date

                    // Optionally reset your table content
                    const tableBody = document.querySelector("#reportTable tbody");
                    if (tableBody) {
                        tableBody.innerHTML = ""; // clears table rows
                    }

                    // Optional toast or message
                    showToastTimeout("Filters and table reset!", "success");
                });

                document.addEventListener('DOMContentLoaded', function() {
                    document.getElementById('reports-section').addEventListener('click', loadReports);

                    // Filter event listeners
                    document.getElementById('report-type-filter').addEventListener('change', () => {
                        currentReportPage = 1;
                        loadReports();
                    });
                    document.getElementById('report-position-filter').addEventListener('change', () => {
                        currentReportPage = 1;
                        loadReports();
                    });
                    document.getElementById('report-date-from').addEventListener('change', () => {
                        currentReportPage = 1;
                        loadReports();
                    });
                    document.getElementById('report-date-to').addEventListener('change', () => {
                        currentReportPage = 1;
                        loadReports();
                    });

                    document.getElementById('close-preview-btn').addEventListener('click', () => {
                        document.getElementById('preview-modal').classList.add('hidden');
                    });

                    // Initial load
                    loadReports();
                });

                let currentPreviewReportId = null;

                let currentReportPage = 1;
                let reportsPerPage = calculateReportsPerPage();
                let allReportsData = [];

                function calculateReportsPerPage() {
                    const rowHeight = 70;
                    const availableHeight = window.innerHeight - 350;
                    const count = Math.floor(availableHeight / rowHeight);
                    return Math.max(count, 3); // at least 3 rows
                }



                window.addEventListener("resize", () => {
                    reportsPerPage = calculateReportsPerPage();
                    currentReportPage = 1; // Reset to avoid out-of-range pages
                    displayReportsPage();
                    displayPaginationControls();
                });




                function loadReports() {
                    const typeFilter = document.getElementById('report-type-filter').value;
                    const positionFilter = document.getElementById('report-position-filter').value;
                    const dateFrom = document.getElementById('report-date-from').value;
                    const dateTo = document.getElementById('report-date-to').value;

                    fetch('admin_actions/admin_get_reports.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                type: typeFilter,
                                position: positionFilter,
                                date_from: dateFrom,
                                date_to: dateTo
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.reports.length > 0) {
                                allReportsData = data.reports;
                            } else {
                                allReportsData = [];
                            }
                            displayReportsPage();
                            displayPaginationControls();
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                }

                function displayReportsPage() {
                    const tableBody = document.getElementById('reports-table-body');
                    tableBody.innerHTML = '';

                    if (allReportsData.length === 0) {
                        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="border border-gray-300 p-4 text-center">No reports found</td>
            </tr>
        `;
                        return;
                    }

                    const startIndex = (currentReportPage - 1) * reportsPerPage;
                    const endIndex = startIndex + reportsPerPage;
                    const pageReports = allReportsData.slice(startIndex, endIndex);


                    pageReports.forEach(report => {
                        const row = document.createElement('tr');
                        row.className = 'hover:bg-gray-50';

                        const formattedReportDate = new Date(report.report_date).toLocaleDateString();
                        const formattedSubmissionDate = new Date(report.submission_timestamp).toLocaleString();

                        row.innerHTML = `
            <td class="p-3 border border-gray-400 p-2">${report.report_type}</td>
            <td class="p-3 border border-gray-400 p-2 capitalize">${report.position}</td>
            <td class="p-3 border border-gray-400 p-2">${report.description}</td>
            <td class="p-3 border border-gray-400 p-2">${formattedReportDate}</td>
            <td class="p-3 border border-gray-400 p-2">${formattedSubmissionDate}</td>
            <td class="p-3 border border-gray-400 p-2">${report.user_name}</td>
            <td class="p-3 border border-gray-400 p-2">
    <div class="flex justify-center items-center space-x-2">
        <button onclick="previewReport(${report.report_id}, '${report.file_path}')" 
            class="text-blue-600 hover:text-blue-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
        </button>
        <button onclick="downloadReport(${report.report_id}, '${report.file_path}')" 
            class="text-green-600 hover:text-green-800">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
        </button>
    </div>
</td>

        `;
                        tableBody.appendChild(row);
                    });
                }

                function displayPaginationControls() {
                    const paginationContainer = document.getElementById('reports-pagination');
                    paginationContainer.innerHTML = '';

                    if (allReportsData.length <= reportsPerPage) return;

                    const totalPages = Math.ceil(allReportsData.length / reportsPerPage);

                    // Wrapper for pagination buttons
                    const wrapper = document.createElement('div');
                    wrapper.className = 'flex justify-center items-center flex-wrap gap-1 mb-2';

                    const createPageButton = (label, page, disabled = false, isActive = false) => {
                        const btn = document.createElement('button');
                        btn.textContent = label;

                        if (disabled) {
                            btn.disabled = true;
                            btn.className = 'px-3 py-1 rounded-md text-sm border bg-gray-300 text-gray-500 cursor-not-allowed';
                        } else if (isActive) {
                            btn.className = 'px-3 py-1 rounded-md text-sm border bg-blue-600 text-white border-blue-600';
                        } else {
                            btn.className = 'px-3 py-1 rounded-md text-sm border bg-gray-200 text-gray-700 hover:bg-blue-500 hover:text-white transition';
                        }

                        btn.addEventListener('click', () => {
                            currentReportPage = page;
                            displayReportsPage();
                            displayPaginationControls();
                        });

                        return btn;
                    };

                    // First & Previous
                    wrapper.appendChild(createPageButton('«', 1, currentReportPage === 1));
                    wrapper.appendChild(createPageButton('←', currentReportPage - 1, currentReportPage === 1));

                    // Page numbers
                    let startPage = Math.max(1, currentReportPage - 1);
                    let endPage = Math.min(totalPages, startPage + 2);
                    if (endPage - startPage < 2 && startPage > 1) {
                        startPage = Math.max(1, endPage - 2);
                    }

                    for (let i = startPage; i <= endPage; i++) {
                        wrapper.appendChild(createPageButton(i, i, false, currentReportPage === i));
                    }

                    // Next & Last
                    wrapper.appendChild(createPageButton('→', currentReportPage + 1, currentReportPage === totalPages));
                    wrapper.appendChild(createPageButton('»', totalPages, currentReportPage === totalPages));

                    // Page label below buttons
                    const pageLabel = document.createElement('div');
                    pageLabel.className = 'text-sm text-gray-700 mt-2 text-center';
                    pageLabel.textContent = `Page ${currentReportPage} of ${totalPages}`;

                    // Append
                    paginationContainer.className = 'flex flex-col items-center'; // Ensures center alignment
                    paginationContainer.appendChild(wrapper);
                    paginationContainer.appendChild(pageLabel);
                }




                async function previewReport(reportId, filePath) {
                    currentPreviewReportId = reportId;
                    const previewContent = document.getElementById('preview-content');

                    // Show loading state
                    previewContent.innerHTML = '<div class="flex justify-center items-center h-full">Loading preview...</div>';

                    // Show modal
                    document.getElementById('preview-modal').classList.remove('hidden');

                    // Set up download button for this report
                    document.getElementById('download-report-btn').onclick = () => downloadReport(reportId, filePath);

                    // 👉 Find the report object from allReportsData
                    const report = allReportsData.find(r => r.report_id === reportId);
                    let action = null;

                    if (report) {
                        const reportDate = new Date(report.report_date);
                        const year = reportDate.getFullYear();
                        const monthName = reportDate.toLocaleString('default', {
                            month: 'long'
                        });

                        if (report.report_type.toLowerCase().includes('daily') && report.position.toLowerCase() === 'collector') {
                            action = `Viewed daily report of Collector ${report.user_name}`;
                        } else if (report.report_type.toLowerCase().includes('monthly') && report.position.toLowerCase() === 'collector') {
                            action = `Viewed ${monthName} ${year} monthly report of Collector ${report.user_name}`;
                        } else if (report.report_type.toLowerCase().includes('monthly') && report.position.toLowerCase() === 'treasurer') {
                            action = `Viewed ${monthName} ${year} monthly report of Treasurer ${report.user_name}`;
                        } else if (report.report_type.toLowerCase().includes('quarterly') && report.position.toLowerCase() === 'treasurer') {
                            action = `Viewed Quarterly report ${year} of Treasurer ${report.user_name}`;
                        } else if (report.report_type.toLowerCase().includes('yearly') && report.position.toLowerCase() === 'treasurer') {
                            action = `Viewed Yearly ${year} report of Treasurer ${report.user_name}`;
                        } else if (report.report_type.toLowerCase().includes('engineering share') && report.position.toLowerCase() === 'treasurer') {
                            action = `Viewed Engineering Share ${year} report of Treasurer ${report.user_name}`;
                        } else if (report.report_type.toLowerCase().includes('rrr') && report.position.toLowerCase() === 'treasurer') {
                            action = `Viewed RRR ${year} report of Treasurer ${report.user_name}`;
                        }

                        // 👉 Record audit if action is determined
                        if (action) {
                            fetch('audit_actions/record_audit_ajax.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        action
                                    })
                                })
                                .then(res => res.json())
                                .then(auditResult => console.log("Audit recorded:", auditResult))
                                .catch(err => console.error("Audit logging failed:", err));
                        }
                    }

                    // Load preview based on file type
                    const fileExt = filePath.split('.').pop().toLowerCase();

                    if (fileExt === 'pdf') {
                        // For PDF files
                        previewContent.innerHTML = `
            <embed src="${filePath}" type="application/pdf" width="100%" height="100%">
        `;
                    } else if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExt)) {
                        // For image files
                        previewContent.innerHTML = `
            <img src="${filePath}" alt="Report Preview" class="max-w-full h-auto mx-auto">
        `;
                    } else if (['xls', 'xlsx'].includes(fileExt)) {
                        // For Excel files - use SheetJS to read and display
                        try {
                            const response = await fetch(filePath);
                            const arrayBuffer = await response.arrayBuffer();
                            const workbook = XLSX.read(arrayBuffer, {
                                type: "array"
                            });

                            // Clear previous content
                            previewContent.innerHTML = '';

                            // Process each sheet
                            workbook.SheetNames.forEach(sheetName => {
                                if (sheetName.toLowerCase().includes('transaction') || sheetName.toLowerCase().includes('daily') || sheetName.toLowerCase().includes('monthly') ||
                                    sheetName.toLowerCase().includes('quarterly') || sheetName.toLowerCase().includes('annual') || sheetName.toLowerCase().includes('engineering share') ||
                                    sheetName.toLowerCase().includes('rrr')) {
                                    const worksheet = workbook.Sheets[sheetName];

                                    // Create container for each sheet
                                    const sheetContainer = document.createElement('div');
                                    sheetContainer.className = 'mb-8 max-w-6xl mx-auto';

                                    // Add sheet title
                                    const sheetTitle = document.createElement('h4');
                                    sheetTitle.className = 'text-lg font-bold mb-2 text-center';
                                    sheetTitle.textContent = sheetName;
                                    sheetContainer.appendChild(sheetTitle);

                                    // Convert sheet to HTML table with proper styling
                                    const htmlString = XLSX.utils.sheet_to_html(worksheet, {
                                        header: '',
                                        footer: ''
                                    });

                                    // Create a div for the table and apply styling
                                    const tableContainer = document.createElement('div');
                                    tableContainer.className = 'bg-white p-5';
                                    tableContainer.innerHTML = htmlString;

                                    // Style the table
                                    const table = tableContainer.querySelector('table');
                                    if (table) {
                                        table.className = 'w-full border-collapse border border-gray-800 mb-8 text-xs';
                                        table.style.width = '100%';

                                        // Style table headers
                                        const thead = table.querySelector('thead');
                                        if (thead) {
                                            thead.className = 'bg-gray-100 font-bold text-center';

                                            const thElements = thead.querySelectorAll('th');
                                            thElements.forEach(th => {
                                                th.className = 'border border-gray-800 p-2';
                                            });
                                        }

                                        // Style table body
                                        const tbody = table.querySelector('tbody');
                                        if (tbody) {
                                            // Style table rows and cells
                                            const rows = tbody.querySelectorAll('tr');
                                            rows.forEach((row, index) => {
                                                // Alternate row colors
                                                if (index % 2 === 0) {
                                                    row.className = 'bg-white';
                                                } else {
                                                    row.className = 'bg-gray-100';
                                                }

                                                // Style cells
                                                const cells = row.querySelectorAll('td');
                                                cells.forEach((cell, cellIndex) => {
                                                    cell.className = 'border border-gray-800 p-2';

                                                    // Right-align for amount columns (assuming last column is amount)
                                                    if (cellIndex === cells.length - 1) {
                                                        cell.className += ' text-right';
                                                    }
                                                });
                                            });

                                            // Add special styling for summary rows like in the PHP template

                                        }
                                    }

                                    sheetContainer.appendChild(tableContainer);



                                    previewContent.appendChild(sheetContainer);
                                }
                            });

                            // If no relevant sheets found
                            if (previewContent.children.length === 0) {
                                previewContent.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-full">
                        <p class="mb-4">No relevant sheets found in the Excel file.</p>
                        <button onclick="downloadReport(${reportId}, '${filePath}')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Download to View
                        </button>
                    </div>
                `;
                            }

                        } catch (error) {
                            console.error('Error loading Excel file:', error);
                            previewContent.innerHTML = `
                <div class="flex flex-col items-center justify-center h-full">
                    <p class="mb-4">Failed to load Excel preview.</p>
                    <button onclick="downloadReport(${reportId}, '${filePath}')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Download to View
                    </button>
                </div>
            `;
                        }
                    } else if (['doc', 'docx'].includes(fileExt)) {
                        // For Word documents - cannot preview directly
                        previewContent.innerHTML = `
            <div class="flex flex-col items-center justify-center h-full">
                <p class="mb-4">This document type cannot be previewed directly.</p>
                <button onclick="downloadReport(${reportId}, '${filePath}')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Download to View
                </button>
            </div>
        `;
                    } else {
                        // Fallback for other file types
                        previewContent.innerHTML = `
            <div class="flex flex-col items-center justify-center h-full">
                <p class="mb-4">Preview not available for this file type.</p>
                <button onclick="downloadReport(${reportId}, '${filePath}')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Download File
                </button>
            </div>
        `;
                    }
                }



                function downloadReport(reportId, filePath) {
                    // Close preview modal if open
                    document.getElementById('preview-modal').classList.add('hidden');

                    // Create a temporary anchor element to trigger download
                    const a = document.createElement('a');
                    a.href = filePath;
                    a.download = filePath.split('/').pop();
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);

                    // 👉 Find the report object from allReportsData
                    const report = allReportsData.find(r => r.report_id === reportId);
                    let action = null;

                    if (report) {
                        const reportDate = new Date(report.report_date);
                        const year = reportDate.getFullYear();
                        const monthName = reportDate.toLocaleString('default', {
                            month: 'long'
                        });

                        if (report.report_type.toLowerCase().includes('daily') && report.position.toLowerCase() === 'collector') {
                            action = `Downloaded daily report of Collector ${report.user_name}`;
                        } else if (report.report_type.toLowerCase().includes('monthly') && report.position.toLowerCase() === 'collector') {
                            action = `Downloaded ${monthName} ${year} monthly report of Collector ${report.user_name}`;
                        } else if (report.report_type.toLowerCase().includes('monthly') && report.position.toLowerCase() === 'treasurer') {
                            action = `Downloaded ${monthName} ${year} monthly report of Treasurer ${report.user_name}`;
                        } else if (report.report_type.toLowerCase().includes('quarterly') && report.position.toLowerCase() === 'treasurer') {
                            action = `Downloaded Quarterly report ${year} of Treasurer ${report.user_name}`;
                        } else if (report.report_type.toLowerCase().includes('yearly') && report.position.toLowerCase() === 'treasurer') {
                            action = `Downloaded Yearly ${year} report of Treasurer ${report.user_name}`;
                        } else if (report.report_type.toLowerCase().includes('engineering share') && report.position.toLowerCase() === 'treasurer') {
                            action = `Downloaded Engineering Share ${year} report of Treasurer ${report.user_name}`;
                        } else if (report.report_type.toLowerCase().includes('rrr') && report.position.toLowerCase() === 'treasurer') {
                            action = `Downloaded RRR ${year} report of Treasurer ${report.user_name}`;
                        }

                        // 👉 Record audit if action is determined
                        if (action) {
                            fetch('audit_actions/record_audit_ajax.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        action
                                    })
                                })
                                .then(res => res.json())
                                .then(auditResult => console.log("Audit recorded:", auditResult))
                                .catch(err => console.error("Audit logging failed:", err));
                        }
                    }
                }
            </script>



</body>




</html>