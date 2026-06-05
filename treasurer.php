<?php
session_start();

include 'backend/db_config_notpdo.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['position'] !== 'Treasurer') {
    header("Location: index.php");
    exit();
}
$user = $_SESSION['user'];

if (isset($_SESSION['user']['email'])) {
    // Store the user's email in session if it's not already stored
    $_SESSION['email'] = $_SESSION['user']['email'];
} else {
    // Handle the case where the email is not available in the session
    // For example, log out the user or redirect them to a login page
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



// Fetch org settings (id = 1, since only one row is needed)
$orgQuery = $conn->query("SELECT org_name, org_logo FROM organization_info WHERE id = 1 LIMIT 1");
$org = $orgQuery->fetch_assoc();

// Default fallback values
$orgName = $org['org_name'] ?? "Organization Name";
$orgLogo = $org['org_logo'] ?? "logo/logo.png";


$phone = $_SESSION['user']['phone'] ?? null;
$address = $_SESSION['user']['address'] ?? null;
$position = $_SESSION['user']['position'] ?? null;
$recovery_email = isset($user['recovery_email']) ? $user['recovery_email'] : "";

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treasurer's Portal | <?php echo htmlspecialchars($orgName); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.2.0/remixicon.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- Add this in your <head> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Organization Logo as Favicon -->
    <link rel="icon" type="image/png" href="<?php echo htmlspecialchars($orgLogo); ?>" />
</head>

<body class="bg-gray-50  text-gray-900  transition-colors duration-300 overflow-hidden">


    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside id="sidebar"
            class="bg-gray-100 shadow-md p-4 flex flex-col items-center justify-between transition-all duration-300">
            <div class="w-full">
                <div class="flex items-center space-x-2 mb-4 w-full">
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
                        <a href="#" id="Transaction-Reports-Page-link"
                            class="flex items-center p-2 rounded hover:bg-gray-300 transition-all w-full">
                            <i class='ri-file-text-line text-2xl'></i>
                            <span class="ml-2 sidebar-text">Transaction</span>
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
            <header class="bg-gray-100 shadowbo-md p-4 flex justify-between items-center transition-all relative">
                <h1 class="text-lg font-semibold">Treasurer's Portal</h1>
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
                    <div id="dropdown-menu"
                        class="absolute top-14 right-0 bg-white shadow-lg rounded-lg w-40 py-2 opacity-0 scale-95 invisible transform transition-all duration-200 z-50">
                        <button id="logout-btn"
                            class="block w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-100">
                            Logout
                        </button>
                    </div>
                </div>
            </header>




            <!-- Main Content -->
            <div class="flex-1 p-6">
                <main id="main-content">



                    <!-- Dashboard (Light Mode Only) -->
                    <!-- Dashboard (Light Mode Only) -->
                    <section id="dashboard-section"
                        class="mt-1 px-6 flex flex-col items-center">
                        <div
                            class="w-full max-w-8xl transition-all duration-300 h-[80vh] max-h-screen overflow-y-auto">

                            <h2 class="text-xl font-bold mb-4 text-gray-900">
                                Treasurer's Dashboard | Welcome, Treasurer
                                <?php echo htmlspecialchars($user['firstname']); ?>
                                <?php echo htmlspecialchars($user['lastname']); ?>!
                            </h2>

                            <!-- Main Grid -->
                            <div
                                class="grid grid-cols-1 lg:grid-cols-3 gap-6 p-2 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-200">
                                <div class="flex flex-col space-y-4 min-w-[300px] flex-shrink-0">

                                    <!-- Total Transactions Card -->
                                    <div class="p-6 bg-white text-gray-900 rounded-lg shadow hover:shadow-xl transition flex justify-between">
                                        <div>
                                            <p class="text-sm text-blue-600">Total Transactions</p>
                                            <h3 class="text-2xl font-bold text-gray-800" id="total-transactions">0</h3>
                                        </div>
                                        <button
                                            class="px-4 py-2 bg-blue-600 text-white text-xs sm:text-sm rounded hover:bg-blue-700"
                                            onclick="openAllSummModal('transactionsAllModal')">
                                            View Details
                                        </button>
                                    </div>

                                    <!-- Total Revenue Card -->
                                    <div class="p-6 bg-white text-gray-900 rounded-lg shadow hover:shadow-xl transition flex justify-between">
                                        <div>
                                            <p class="text-sm text-green-600">Total Revenue</p>
                                            <h3 class="text-2xl font-bold text-gray-800" id="total-revenue">₱0.00</h3>
                                        </div>
                                        <button
                                            class="px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition"
                                            onclick="openAllSummModal('revenueAllModal')">
                                            View Details
                                        </button>
                                    </div>

                                    <!-- Report Shortcuts -->
                                    <div class="p-6 bg-white rounded-lg shadow hover:shadow-xl transition text-gray-900">
                                        <p class="text-sm text-blue-600">Report Shortcuts</p>

                                        <div class="grid grid-cols-1 gap-1.5 mt-1">
                                            <button onclick="scrollToTransactionsAndGenerateMonthly()"
                                                class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1.5 rounded-md text-xs font-medium w-full">
                                                Monthly
                                            </button>

                                            <button onclick="scrollToTransactionsAndGenerateQuarterly()"
                                                class="bg-green-500 hover:bg-green-600 text-white px-2 py-1.5 rounded-md text-xs font-medium w-full">
                                                Quarterly
                                            </button>

                                            <button onclick="scrollToTransactionsAndGenerateYearly()"
                                                class="bg-purple-500 hover:bg-purple-600 text-white px-2 py-1.5 rounded-md text-xs font-medium w-full">
                                                Yearly
                                            </button>

                                            <button onclick="scrollToTransactionsAndGenerateEngrShare()"
                                                class="bg-orange-500 hover:bg-orange-600 text-white px-2 py-1.5 rounded-md text-xs font-medium w-full">
                                                Engineering Share
                                            </button>

                                            <button onclick="scrollToTransactionsAndGenerateRRR()"
                                                class="bg-pink-500 hover:bg-pink-600 text-white px-2 py-1.5 rounded-md text-xs font-medium w-full">
                                                RRR
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- RIGHT SIDE: Revenue Chart -->
                                <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow hover:shadow-xl transition w-full">

                                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                                        <h3 class="text-lg font-semibold text-gray-900">Revenue Overview</h3>

                                        <div class="flex items-center gap-2">
                                            <label for="chartFilter" class="text-sm text-gray-600">View By:</label>
                                            <select id="chartFilter"
                                                class="text-sm p-2 border rounded-md border-gray-300 w-full sm:w-auto">
                                                <option value="daily" selected>Daily</option>
                                                <option value="weekly">Weekly</option>
                                                <option value="monthly">Monthly</option>
                                                <option value="quarterly">Quarterly</option>
                                                <option value="yearly">Yearly</option>
                                            </select>
                                        </div>
                                    </div>

                                    <center>
                                        <div id="revenueChartContainer" class="relative w-full aspect-[16/9] max-h-[550px]">
                                            <canvas id="revenueChart" class="w-full h-full"></canvas>
                                        </div>

                                    </center>
                                </div>

                            </div>
                        </div>
                    </section>


                    <!-- Transactions Modal -->
                    <div id="transactionsAllModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                        <div class="bg-white rounded-lg w-11/12 md:w-3/4 lg:w-2/3 p-6 relative">
                            <button class="absolute top-4 right-4 text-gray-500 hover:text-gray-800 text-3xl font-bold"
                                onclick="closeAllSummModal('transactionsAllModal')">&times;</button>

                            <h2 class="text-xl font-bold text-gray-800 mb-4">Transactions Summary</h2>

                            <!-- Timeframe Filters -->
                            <div class="flex flex-wrap gap-2 mb-4" id="transactionsFilters">
                                <button class="timeframe-btn px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700"
                                    onclick="setActiveFilter(this, 'transactions'); loadAllTransactionsData('daily')">Daily</button>
                                <button class="timeframe-btn px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700"
                                    onclick="setActiveFilter(this, 'transactions'); loadAllTransactionsData('weekly')">Weekly</button>
                                <button class="timeframe-btn px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700"
                                    onclick="setActiveFilter(this, 'transactions'); loadAllTransactionsData('monthly')">Monthly</button>
                                <button class="timeframe-btn px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700"
                                    onclick="setActiveFilter(this, 'transactions'); loadAllTransactionsData('quarterly')">Quarterly</button>
                                <button class="timeframe-btn px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700"
                                    onclick="setActiveFilter(this, 'transactions'); loadAllTransactionsData('yearly')">Yearly</button>
                            </div>

                            <div id="transactionsAllList" class="overflow-x-auto"></div>
                        </div>
                    </div>

                    <!-- Revenue Modal -->
                    <div id="revenueAllModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                        <div class="bg-white rounded-lg w-11/12 md:w-3/4 lg:w-2/3 p-6 relative">
                            <button class="absolute top-4 right-4 text-gray-500 hover:text-gray-800 text-3xl font-bold"
                                onclick="closeAllSummModal('revenueAllModal')">&times;</button>

                            <h2 class="text-xl font-bold text-gray-800 mb-4">Revenue Summary</h2>

                            <!-- Timeframe Filters -->
                            <div class="flex flex-wrap gap-2 mb-4" id="revenueFilters">
                                <button class="timeframe-btn px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700"
                                    onclick="setActiveFilter(this, 'revenue'); loadAllRevenueData('daily')">Daily</button>
                                <button class="timeframe-btn px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700"
                                    onclick="setActiveFilter(this, 'revenue'); loadAllRevenueData('weekly')">Weekly</button>
                                <button class="timeframe-btn px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700"
                                    onclick="setActiveFilter(this, 'revenue'); loadAllRevenueData('monthly')">Monthly</button>
                                <button class="timeframe-btn px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700"
                                    onclick="setActiveFilter(this, 'revenue'); loadAllRevenueData('quarterly')">Quarterly</button>
                                <button class="timeframe-btn px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700"
                                    onclick="setActiveFilter(this, 'revenue'); loadAllRevenueData('yearly')">Yearly</button>
                            </div>

                            <div id="revenueAllList" class="overflow-x-auto"></div>
                        </div>
                    </div>





                    <!--Transactions-->
                    <!-- Transaction Page -->
                    <section id="transactions-section" class="mt-1 px-6 flex flex-col items-center">
                        <div
                            class="bg-white shadow-xl rounded-2xl p-8 w-full max-w-8xl transition-all duration-300 hover:shadow-2xl h-[80vh] max-h-screen overflow-y-auto">
                            <h2 class="text-2xl font-bold mb-4">Transactions</h2>
                            <div class="flex flex-wrap justify-between items-center gap-2 mb-4 border border-gray-300 rounded p-2">
                                <div class="flex flex-wrap gap-2">

                                    <button onclick="filterTransactions('today')"
                                        class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-200 transition-all">
                                        Today
                                    </button>

                                    <select onchange="filterTransactions(this.value)"
                                        class="px-4 py-2 border border-gray-300 rounded">
                                        <option value="">Select Month</option>
                                        <option value="january">January</option>
                                        <option value="february">February</option>
                                        <option value="march">March</option>
                                        <option value="april">April</option>
                                        <option value="may">May</option>
                                        <option value="june">June</option>
                                        <option value="july">July</option>
                                        <option value="august">August</option>
                                        <option value="september">September</option>
                                        <option value="october">October</option>
                                        <option value="november">November</option>
                                        <option value="december">December</option>
                                    </select>

                                    <select id="quarterFilter" onchange="filterByQuarter(this.value)"
                                        class="px-4 py-2 border border-gray-300 rounded">
                                        <option value="">Select Quarter</option>
                                        <option value="q1">1st Quarter</option>
                                        <option value="q2">2nd Quarter</option>
                                        <option value="q3">3rd Quarter</option>
                                        <option value="q4">4th Quarter</option>
                                    </select>

                                    <select id="yearFilter" onchange="filterTransactions(this.value)"
                                        class="px-4 py-2 border border-gray-300 rounded">
                                        <option value="">Select Year</option>
                                    </select>

                                    <div class="relative">
                                        <button id="openDatePicker" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-200 transition-all z-10">
                                            Select Day
                                        </button>
                                        <div id="datepickerContainer" class="absolute top-full left-0 z-20 bg-white border border-gray-300 rounded shadow-md mt-1 hidden overflow-auto max-h-64"></div>
                                        <input type="hidden" id="selectedDate">
                                    </div>

                                    <div class="relative inline-block text-left ">
                                        <button onclick="toggleDateRangeDropdown()"
                                            class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-200 transition-all">
                                            Filter by Date Range
                                        </button>

                                        <div id="dateRangeDropdown" class="absolute z-10 mt-2 p-4 bg-white border border-gray-300 rounded shadow-lg hidden">
                                            <div class="flex items-center gap-2">
                                                <label for="startDate" class="text-sm text-gray-700">From:</label>
                                                <input type="date" id="startDate"
                                                    class="px-3 py-2 border border-gray-300 rounded">

                                                <label for="endDate" class="text-sm text-gray-700">To:</label>
                                                <input type="date" id="endDate"
                                                    class="px-3 py-2 border border-gray-300 rounded">

                                                <button onclick="filterByDateRange()"
                                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-all">
                                                    Filter
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <input type="text" id="transaction-search" placeholder="Search by PGL No. or Payer Name"
                                        class="px-4 py-2 border border-gray-300 rounded" />

                                </div>
                                <div class="relative inline-block text-left">
                                    <button onclick="toggleDropdownReport()" id="reportButton"
                                        class="px-5 py-3 border border-blue-600 text-blue-600 font-medium rounded-lg hover:bg-blue-600 hover:text-white transition-all flex items-center">
                                        <i class="ri-file-list-3-line mr-1"></i> Generate Report
                                    </button>

                                    <div id="reportDropdown" class="hidden absolute z-10 mt-2 w-48 bg-white border border-gray-200 rounded shadow-lg">
                                        <button onclick="generateMonthlyReport()" class="block w-full text-left px-4 py-2 hover:bg-gray-100">Generate Monthly Report</button>
                                        <button onclick="generateQuarterlyReport()" class="block w-full text-left px-4 py-2 hover:bg-gray-100">Generate Quarterly Report</button>
                                        <button onclick="generateYearlyReport()" class="block w-full text-left px-4 py-2 hover:bg-gray-100">Generate Yearly Report</button>
                                        <button onclick="generateEngrShareReport()" class="block w-full text-left px-4 py-2 hover:bg-gray-100">Generate Engineering Share Report</button>
                                        <button onclick="generateRRRReport()" class="block w-full text-left px-4 py-2 hover:bg-gray-100">Generate RRR Report</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Scrollable Table -->
                            <div class="overflow-x-auto max-h-[60vh] overflow-y-auto border border-gray-200 rounded-md">
                                <table class="min-w-full table-auto border-collapse text-sm">
                                    <thead class="sticky top-0 bg-gray-300 text-black">
                                        <tr>
                                            <th class="p-3 border border-gray-400 font-medium">PGL No.</th>
                                            <th class="p-3 border border-gray-400 font-medium">Date</th>
                                            <th class="p-3 border border-gray-400 font-medium">Time</th>
                                            <th class="p-3 border border-gray-400 font-medium">Payer Name</th>
                                            <th class="p-3 border border-gray-400 font-medium">Particulars</th>
                                            <th class="p-3 border border-gray-400 font-medium">Amount</th>
                                            <th class="p-3 border border-gray-400 font-medium">Payment Type</th>
                                            <th class="p-3 border border-gray-400 font-medium">Check Number</th>
                                            <th class="p-3 border border-gray-400 font-medium">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="transactionsTable" class="bg-white divide-y divide-gray-200"></tbody>


                                </table>
                            </div>
                            <div id="paginationControls" class="w-full flex justify-center mt-6"></div>

                        </div>

                        <!-- Modal for transaction details -->
                        <div id="transactionModal" class="fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50 hidden">
                            <div class="bg-white p-6 rounded-lg shadow-lg max-w-md w-full mx-auto">
                                <div id="modalContent" class="space-y-2 text-sm text-gray-800">
                                    <!-- Transaction details will be injected here -->
                                </div>
                                <div class="text-right mt-4">
                                    <button onclick="closeDetailsModal()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Close</button>
                                </div>
                            </div>
                        </div>

                        <!-- Month Modal -->
                        <div id="monthModal" onclick="handleMonthBackdropClick(event)" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
                            <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md relative">
                                <!-- Close Button -->
                                <button onclick="closeMonthModal()" class="absolute top-2 right-2 text-gray-600 hover:text-black">
                                    <i class="ri-close-line text-2xl"></i>
                                </button>

                                <div class="text-lg font-semibold text-gray-800 mb-4">
                                    Generate Monthly Report
                                </div>

                                <label for="monthSelect" class="block text-sm mb-1 text-gray-700">Select Month</label>
                                <select id="monthSelect" class="w-full p-2 border rounded mb-4">
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

                                <label for="yearSelect" class="block text-sm mb-1 text-gray-700">Select Year</label>
                                <select id="yearSelect" class="w-full p-2 border rounded mb-4"></select>

                                <button onclick="generateSelectedMonthReport()" class="w-full bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 transition">
                                    Generate
                                </button>
                            </div>
                        </div>

                        <!-- Quarter Modal -->
                        <div id="quarterModal" onclick="handleQuarterBackdropClick(event)" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
                            <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md relative">
                                <!-- Close Button -->
                                <button onclick="closeQuarterModal()" class="absolute top-2 right-2 text-gray-600 hover:text-black">
                                    <i class="ri-close-line text-2xl"></i>
                                </button>

                                <div class="text-lg font-semibold text-gray-800 mb-4">
                                    Generate Quarterly Report
                                </div>

                                <label for="qtrSelect" class="block text-sm mb-1 text-gray-700">Select Quarter</label>
                                <select id="qtrSelect" class="w-full p-2 border rounded mb-4">
                                    <option value="Q1">1st Quarter (Jan - Mar)</option>
                                    <option value="Q2">2nd Quarter (Apr - Jun)</option>
                                    <option value="Q3">3rd Quarter (Jul - Sep)</option>
                                    <option value="Q4">4th Quarter (Oct - Dec)</option>
                                </select>

                                <label for="qtryearSelect" class="block text-sm mb-1 text-gray-700">Select Year</label>
                                <select id="qtryearSelect" class="w-full p-2 border rounded mb-4"></select>

                                <button onclick="generateSelectedQuarterlyReport()" class="w-full bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 transition">
                                    Generate
                                </button>
                            </div>
                        </div>

                        <!-- Year Modal -->
                        <div id="yearModal" onclick="handleYearBackdropClick(event)" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
                            <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md relative">
                                <!-- Close Button -->
                                <button onclick="closeYearModal()" class="absolute top-2 right-2 text-gray-600 hover:text-black">
                                    <i class="ri-close-line text-2xl"></i>
                                </button>

                                <div class="text-lg font-semibold text-gray-800 mb-4">
                                    Generate Yearly Report
                                </div>

                                <label for="yearOnlySelect" class="block text-sm mb-1 text-gray-700">Select Year</label>
                                <select id="yearOnlySelect" class="w-full p-2 border rounded mb-4"></select>

                                <button onclick="generateSelectedYearlyReport()" class="w-full bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 transition">
                                    Generate
                                </button>
                            </div>
                        </div>

                        <!-- Engineering Share Modal -->
                        <div id="engrShareModal" onclick="handleEngrYearBackdropClick(event)" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
                            <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md relative">
                                <!-- Close Button -->
                                <button onclick="closeEngrYearModal()" class="absolute top-2 right-2 text-gray-600 hover:text-black">
                                    <i class="ri-close-line text-2xl"></i>
                                </button>

                                <div class="text-lg font-semibold text-gray-800 mb-4">
                                    Generate Engineering Share Report
                                </div>

                                <label for="engrYearOnlySelect" class="block text-sm mb-1 text-gray-700">Select Year</label>
                                <select id="engrYearOnlySelect" class="w-full p-2 border rounded mb-4"></select>

                                <button onclick="generateSelectedYearEngrShareReport()" class="w-full bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 transition">
                                    Generate
                                </button>
                            </div>
                        </div>

                        <!-- RRR Share Modal -->
                        <div id="RRRModal" onclick="handleRRRBackdropClick(event)" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
                            <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md relative">
                                <!-- Close Button -->
                                <button onclick="closeRRRModal()" class="absolute top-2 right-2 text-gray-600 hover:text-black">
                                    <i class="ri-close-line text-2xl"></i>
                                </button>

                                <div class="text-lg font-semibold text-gray-800 mb-4">
                                    Generate RRR Report
                                </div>

                                <label for="RRRYearOnlySelect" class="block text-sm mb-1 text-gray-700">Select Year</label>
                                <select id="RRRYearOnlySelect" class="w-full p-2 border rounded mb-4"></select>

                                <button onclick="generateSelectedYearRRRReport()" class="w-full bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600 transition">
                                    Generate
                                </button>
                            </div>
                        </div>


                        <!-- MONTHLY Report Preview Modal -->
                        <div id="monthReportPreview" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 hidden">
                            <div class="bg-white w-full max-w-6xl mx-auto rounded-lg shadow-lg overflow-hidden flex flex-col max-h-[90vh]">

                                <!-- Modal Header (optional) -->
                                <div class="px-6 pt-5 pb-3 border-b">
                                    <h3 class="text-lg font-semibold text-gray-800">Monthly Report Preview</h3>
                                </div>

                                <!-- Modal Body -->
                                <div id="monthReportContent" class="flex-1 overflow-y-auto bg-gray-100 p-4 rounded-b-lg">
                                    <!-- Report content will be inserted here dynamically -->
                                </div>

                                <!-- Fixed Footer Buttons -->
                                <div class="bg-gray-50 px-4 py-3 flex justify-end gap-3 border-t">
                                    <button onclick="closeMonthlyReportPreview()"
                                        class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500 transition">
                                        Back
                                    </button>
                                    <button id="mthDownloadBtn"
                                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-800 transition hidden">
                                        Download Report
                                    </button>
                                </div>
                            </div>
                        </div>


                        <!-- QUARTERLY Report Preview Modal -->
                        <div id="qtrReportPreview" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 hidden">
                            <div class="bg-white w-full max-w-6xl mx-auto rounded-lg shadow-lg overflow-hidden flex flex-col max-h-[90vh]">
                                <div class="px-6 pt-5 pb-3 border-b">
                                    <h3 class="text-lg font-semibold text-gray-800">Quarterly Report Preview</h3>
                                </div>
                                <div id="qtrReportContent" class="flex-1 overflow-y-auto bg-gray-100 p-4 rounded-b-lg">
                                    <!-- Report content will be inserted here dynamically -->
                                </div>
                                <div class="bg-gray-50 px-4 py-3 flex justify-end gap-3 border-t">
                                    <button onclick="closeQuarterlyReportPreview()"
                                        class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500 transition">
                                        Back
                                    </button>
                                    <button id="qtrDownloadBtn"
                                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-800 transition hidden">
                                        Download Report
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- YEARLY Report Preview Modal -->
                        <div id="yrReportPreview" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 hidden">
                            <div class="bg-white w-full max-w-6xl mx-auto rounded-lg shadow-lg overflow-hidden flex flex-col max-h-[90vh]">
                                <div class="px-6 pt-5 pb-3 border-b">
                                    <h3 class="text-lg font-semibold text-gray-800">Yearly Report Preview</h3>
                                </div>
                                <div id="yrReportContent" class="flex-1 overflow-y-auto bg-gray-100 p-4 rounded-b-lg">
                                    <!-- Report content will be inserted here dynamically -->
                                </div>
                                <div class="bg-gray-50 px-4 py-3 flex justify-end gap-3 border-t">
                                    <button onclick="closeYearlyReportPreview()"
                                        class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500 transition">
                                        Back
                                    </button>
                                    <button id="yrDownloadBtn"
                                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-800 transition hidden">
                                        Download Report
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- ENGR. SHARE Report Preview Modal -->
                        <div id="engrReportPreview" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 hidden">
                            <div class="bg-white w-full max-w-6xl mx-auto rounded-lg shadow-lg overflow-hidden flex flex-col max-h-[90vh]">
                                <div class="px-6 pt-5 pb-3 border-b">
                                    <h3 class="text-lg font-semibold text-gray-800">Engineer’s Share Report Preview</h3>
                                </div>
                                <div id="engrReportContent" class="flex-1 overflow-y-auto bg-gray-100 p-4 rounded-b-lg">
                                    <!-- Report content will be inserted here dynamically -->
                                </div>
                                <div class="bg-gray-50 px-4 py-3 flex justify-end gap-3 border-t">
                                    <button onclick="closeEngrReportPreview()"
                                        class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500 transition">
                                        Back
                                    </button>
                                    <button id="engrDownloadBtn"
                                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-800 transition hidden">
                                        Download Report
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- RRR Report Preview Modal -->
                        <div id="rrrReportPreview" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 hidden">
                            <div class="bg-white w-full max-w-6xl mx-auto rounded-lg shadow-lg overflow-hidden flex flex-col max-h-[90vh]">
                                <div class="px-6 pt-5 pb-3 border-b">
                                    <h3 class="text-lg font-semibold text-gray-800">RRR Report Preview</h3>
                                </div>
                                <div id="rrrReportContent" class="flex-1 overflow-y-auto bg-gray-100 p-4 rounded-b-lg">
                                    <!-- Report content will be inserted here dynamically -->
                                </div>
                                <div class="bg-gray-50 px-4 py-3 flex justify-end gap-3 border-t">
                                    <button onclick="closeRRRReportPreview()"
                                        class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500 transition">
                                        Back
                                    </button>
                                    <button id="rrrDownloadBtn"
                                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-800 transition hidden">
                                        Download Report
                                    </button>
                                </div>
                            </div>
                        </div>



                        <!-- GENERIC "In Progress" Report Modal -->
                        <div id="inProgressModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 hidden">
                            <div class="bg-white w-full max-w-lg mx-auto p-6 rounded-lg shadow-lg text-center">
                                <!-- Icon/Graphic -->
                                <div class="flex justify-center mb-4">
                                    <!-- Example animated spinner icon -->
                                    <svg class="animate-spin h-12 w-12 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                        </path>
                                    </svg>
                                </div>

                                <!-- Message -->
                                <h2 class="text-xl font-bold mb-2">In progress...</h2>
                                <p id="inProgressMessage" class="text-gray-700"></p>

                                <!-- Buttons -->
                                <div class="mt-6 flex justify-center">
                                    <button onclick="closeInProgressModal()"
                                        class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">
                                        Back
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>






                    <!-- Reports Section -->
                    <section id="reports-section" class="mt-1 px-6 flex flex-col items-center">
                        <div
                            class="bg-white shadow-xl rounded-2xl p-8 w-full max-w-8xl transition-all duration-300 hover:shadow-2xl h-[80vh] max-h-screen overflow-y-auto">
                            <h2 class="text-2xl font-bold text-gray-900 mb-6">Reports</h2>

                            <!-- Tabs -->
                            <div class="mb-6 border-b border-gray-200">
                                <ul class="flex flex-wrap -mb-px" id="reports-tabs" role="tablist">
                                    <li class="mr-2" role="presentation">
                                        <button class="inline-block p-4 border-b-2 rounded-t-lg" id="view-reports-tab" data-tabs-target="#view-reports" type="button" role="tab" aria-controls="view-reports" aria-selected="true">
                                            View Reports
                                        </button>
                                    </li>
                                    <li class="mr-2" role="presentation">
                                        <button class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300" id="submit-report-tab" data-tabs-target="#submit-report" type="button" role="tab" aria-controls="submit-report" aria-selected="false">
                                            Submit Report
                                        </button>
                                    </li>
                                </ul>
                            </div>

                            <!-- View Reports Tab -->
                            <div class="hidden p-4 rounded-lg bg-gray-50" id="view-reports" role="tabpanel" aria-labelledby="view-reports-tab">

                                <!-- Search and Filter -->
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6 items-end">
                                    <div>
                                        <label for="report-type-filter" class="block text-sm font-medium text-gray-700">Filter by Type:</label>
                                        <select id="report-type-filter" class="w-full p-3 border border-gray-300 rounded-md shadow-sm bg-white">
                                            <option value="">All</option>
                                            <option value="Monthly">Monthly</option>
                                            <option value="Quarterly">Quarterly</option>
                                            <option value="Yearly">Yearly</option>
                                            <option value="Engineering Share">Engineering Share</option>
                                            <option value="RRR">RRR</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="report-date-from" class="block text-sm font-medium text-gray-700">From Date:</label>
                                        <input type="text" id="report-date-from" class="w-full p-3 border border-gray-300 rounded-md shadow-sm bg-white" placeholder="MM/DD/YYYY">
                                    </div>
                                    <div>
                                        <label for="report-date-to" class="block text-sm font-medium text-gray-700">To Date:</label>
                                        <input type="text" id="report-date-to" class="w-full p-3 border border-gray-300 rounded-md shadow-sm bg-white" placeholder="MM/DD/YYYY">
                                    </div>
                                    <div class="flex md:justify-end">
                                        <button id="resetFilters" class="px-5 py-3 border border-blue-600 text-blue-600 font-medium rounded-lg hover:bg-blue-600 hover:text-white transition-all flex items-center">
                                            Reset Filters
                                        </button>
                                    </div>
                                </div>

                                <!-- Scrollable Reports Table -->
                                <div class="overflow-x-auto max-h-[60vh] overflow-y-auto border border-gray-200 rounded-md">
                                    <table class="min-w-full table-auto border-collapse text-sm">
                                        <thead class="sticky top-0 bg-gray-300 text-black">
                                            <tr>
                                                <th class="p-3 border border-gray-400 font-medium">Report Type</th>
                                                <th class="p-3 border border-gray-400 font-medium">Report Date</th>
                                                <th class="p-3 border border-gray-400 font-medium">Submitted On</th>
                                                <th class="p-3 border border-gray-400 font-medium">Description</th>
                                                <th class="p-3 border border-gray-400 font-medium">Actions</th>
                                            </tr>
                                        </thead>

                                        <tbody id="reports-table-body" class="bg-white divide-y divide-gray-200"></tbody>
                                    </table>

                                </div>
                                <div id="reportsPagination" class="mt-3"></div>

                            </div>


                            <!-- Preview Modal -->
                            <div id="preview-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
                                <div class="flex items-center justify-center min-h-screen px-4 text-center">
                                    <!-- Background overlay -->
                                    <div class="fixed inset-0 bg-black bg-opacity-50"></div>

                                    <!-- Modal container -->
                                    <div class="inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all max-w-5xl w-full max-h-[90vh]">
                                        <div class="px-6 pt-5 pb-4">
                                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Report Preview</h3>
                                            <div id="preview-content" class="h-[500px] overflow-auto"></div>
                                        </div>
                                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse">
                                            <button id="download-report-btn" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                                                Download Report
                                            </button>
                                            <button id="close-preview-btn" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                Close
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Report Tab -->
                            <div class="hidden p-4 rounded-lg bg-gray-50" id="submit-report" role="tabpanel" aria-labelledby="submit-report-tab">
                                <div class="flex flex-col h-[calc(100vh-350px)]">
                                    <form id="report-upload-form" action="submit_report.php" method="POST" enctype="multipart/form-data" class="flex flex-col h-[50vh]">
                                        <!-- Scrollable content area -->
                                        <div class="overflow-y-auto pr-2 flex-grow">
                                            <div class="mb-4">
                                                <label for="report-position" class="block text-sm font-medium text-gray-700 mb-2">Position:</label>
                                                <select id="report-position" name="position" required class="w-full p-3 border border-gray-300 rounded-md shadow-sm bg-white">
                                                    <option value="Treasurer" selected>Treasurer</option>
                                                </select>
                                            </div>

                                            <div class="mb-4">
                                                <label for="report-type" class="block text-sm font-medium text-gray-700 mb-2">Report Type:</label>
                                                <select id="report-type" name="report_type" required class="w-full p-3 border border-gray-300 rounded-md shadow-sm bg-white">
                                                    <option value="">Select Report Type</option>
                                                    <option value="Monthly">Monthly</option>
                                                    <option value="Quarterly">Quarterly</option>
                                                    <option value="Yearly">Yearly</option>
                                                    <option value="Engineering Share">Engineering Share</option>
                                                    <option value="RRR">RRR</option>
                                                </select>
                                            </div>

                                            <div class="mb-4">
                                                <label for="report-date" class="block text-sm font-medium text-gray-700 mb-2">Report Date:</label>
                                                <input type="text" id="report-date" name="report_date" required class="w-full p-3 border border-gray-300 rounded-md shadow-sm bg-white" placeholder="MM/DD/YYYY">
                                            </div>

                                            <div class="mb-4">
                                                <label for="report-description" class="block text-sm font-medium text-gray-700 mb-2">Description:</label>
                                                <textarea id="report-description" name="description" rows="3" class="w-full p-3 border border-gray-300 rounded-md shadow-sm bg-white"></textarea>
                                            </div>

                                            <!-- Drag and Drop Area -->
                                            <div class="mb-6">
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Upload Report:</label>
                                                <div id="drop-area" class="border-2 border-dashed border-gray-300 rounded-lg p-12 text-center cursor-pointer hover:border-blue-500 transition-colors duration-300">
                                                    <div id="drop-content">
                                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                        </svg>
                                                        <p class="mt-2 text-sm text-gray-600">Drag and drop your report file here</p>
                                                        <p class="mt-1 text-xs text-gray-500">or click to browse</p>
                                                        <input type="file" id="file-input" name="report_file" accept=".xlsx" class="hidden" required>
                                                    </div>
                                                    <div id="file-info" class="hidden mt-4">
                                                        <p class="text-sm font-medium text-gray-900" id="file-name"></p>
                                                        <p class="text-xs text-gray-500" id="file-size"></p>
                                                        <button type="button" id="remove-file" class="mt-2 text-sm text-red-600 hover:text-red-800">Remove</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Fixed submit button -->
                                        <div class="mt-auto pt-4">
                                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-md transition-colors duration-300">
                                                Submit Report
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                    </section>




                    <!--settings-->
                    <section id="settings-section" class="mt-1 px-6 flex flex-col items-center">
                        <div class="flex justify-center mt--10 w-full">
                            <div class="bg-white shadow-xl rounded-2xl p-8 w-full max-w-8xl transition-all duration-300 hover:shadow-2xl h-[80vh] max-h-screen overflow-y-auto">

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
                                                <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 
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
                                                <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 
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
                                                <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 
                             4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Save Button -->
                                    <button id="updateCredentialsBtn" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">
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
                                    <button id="saveTimeoutSettings" class="bg-green-600 text-white py-2 px-6 rounded-xl hover:bg-green-700">
                                        Save Timeout Settings
                                    </button>
                                </div>

                                <!-- ================= ORG INFO (ADMIN ONLY) ================= -->
                                <?php if ($user['position'] === 'Admin'): ?>
                                    <hr class="my-6">
                                    <h2 class="text-xl font-bold mb-4 text-gray-900">Organization Information</h2>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                        <div>
                                            <label class="block text-gray-700 mb-1">Organization Name</label>
                                            <input type="text" id="orgName" placeholder="Enter organization name"
                                                class="w-full p-2 border rounded-md">
                                        </div>
                                        <div>
                                            <label class="block text-gray-700 mb-1">Organization Logo</label>
                                            <input type="file" id="orgLogo" accept="image/*"
                                                class="w-full p-2 border rounded-md">
                                        </div>
                                    </div>

                                    <div class="flex justify-end mb-8">
                                        <button id="saveOrgBtn" class="bg-blue-500 text-white py-2 px-6 rounded-xl hover:bg-blue-600">Save</button>
                                    </div>
                                    <hr class="my-6">

                                    <!-- ================= BACKUP & RESTORE ================= -->
                                    <h2 class="text-xl font-bold mt-8 mb-4 text-gray-900">Backup & Restore</h2>
                                    <div class="flex flex-col md:flex-row gap-4">
                                        <button id="backupBtn"
                                            class="bg-green-600 text-white py-2 px-6 rounded hover:bg-green-700">Backup Data</button>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="file" id="restoreFile" class="hidden" accept=".sql,.zip,.json">
                                            <span class="bg-yellow-600 text-white py-2 px-6 rounded hover:bg-yellow-700 cursor-pointer">Restore Data</span>
                                        </label>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-2">Backup will generate a copy of the database. Restore will overwrite current data with selected file.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </section>

                </main>
            </div>

            

            <!-- Toast Container -->
            <div id="toast-container" class="fixed top-5 right-5 flex flex-col space-y-2 z-50"></div>

            <!-- Logout Confirmation Modal -->
            <div id="logout-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
                <div class="bg-white  rounded-lg shadow-lg p-6 w-80">
                    <h2 class="text-lg font-semibold text-gray-800  mb-4">Confirm Logout</h2>
                    <p class="text-sm text-gray-600 mb-6">Are you sure you want to log out?</p>
                    <div class="flex justify-end space-x-3">
                        <button id="cancel-logout" class="px-4 py-2 text-sm bg-gray-300  text-gray-800  rounded hover:bg-gray-400 ">Cancel</button>
                        <button id="confirm-logout" class="px-4 py-2 text-sm bg-red-600 text-white rounded hover:bg-red-700">Logout</button>
                    </div>
                </div>
            </div>


            <script src="screen_lockout.js"></script>
            <script src="in_progress_modal.js"></script>
            <script src="audit_actions/audit_logs.js"></script>
            <script src="toast_action/toast.js"></script>
            <script src="settings_action/settings_function.js"></script>
            <script src="treasurer_actions/monthly_reports.js"></script>
            <script src="treasurer_actions/quarterly_reports.js"></script>
            <script src="treasurer_actions/yearly_reports.js"></script>
            <script src="treasurer_actions/engrShare_reports.js"></script>
            <script src="treasurer_actions/RRR_reports.js"></script>
            <script src="treasurer_actions/dashboard_summary.js"></script>

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
                function toggleDropdownReport() {
                    const dropdown = document.getElementById('reportDropdown');
                    dropdown.classList.toggle('hidden');
                }

                // Close the dropdown if the user clicks outside
                document.addEventListener('click', function(event) {
                    const button = document.getElementById('reportButton');
                    const dropdown = document.getElementById('reportDropdown');

                    if (!button.contains(event.target) && !dropdown.contains(event.target)) {
                        dropdown.classList.add('hidden');
                    }
                });

                const toggleUnlockPassword = document.getElementById("toggleUnlockPassword");
                const unlockPasswordInput = document.getElementById("unlockPassword");
                const eyeOpen = document.getElementById("eyeOpen");
                const eyeClosed = document.getElementById("eyeClosed");

                toggleUnlockPassword.addEventListener("click", () => {
                    if (unlockPasswordInput.type === "password") {
                        unlockPasswordInput.type = "text";
                        eyeOpen.classList.add("hidden");
                        eyeClosed.classList.remove("hidden");
                    } else {
                        unlockPasswordInput.type = "password";
                        eyeOpen.classList.remove("hidden");
                        eyeClosed.classList.add("hidden");
                    }
                });
            </script>


            <script>
                function scrollToTransactionsAndGenerateMonthly() {
                    const transactionsSection = document.getElementById('Transaction-Reports-Page-link');
                    if (transactionsSection) {
                        showSection('transactions');
                        transactionsSection.scrollIntoView({
                            behavior: 'smooth'
                        });
                        setTimeout(() => {
                            generateMonthlyReport();
                        }, 300);
                    }
                }

                function scrollToTransactionsAndGenerateQuarterly() {
                    const transactionsSection = document.getElementById('Transaction-Reports-Page-link');
                    if (transactionsSection) {
                        showSection('transactions');
                        transactionsSection.scrollIntoView({
                            behavior: 'smooth'
                        });
                        setTimeout(() => {
                            generateQuarterlyReport();
                        }, 300);
                    }
                }

                function scrollToTransactionsAndGenerateYearly() {
                    const transactionsSection = document.getElementById('Transaction-Reports-Page-link');
                    if (transactionsSection) {
                        showSection('transactions');
                        transactionsSection.scrollIntoView({
                            behavior: 'smooth'
                        });
                        setTimeout(() => {
                            generateYearlyReport();
                        }, 300);
                    }
                }

                function scrollToTransactionsAndGenerateEngrShare() {
                    const transactionsSection = document.getElementById('Transaction-Reports-Page-link');
                    if (transactionsSection) {
                        showSection('transactions');
                        transactionsSection.scrollIntoView({
                            behavior: 'smooth'
                        });
                        setTimeout(() => {
                            generateEngrShareReport();
                        }, 300);
                    }
                }

                function scrollToTransactionsAndGenerateRRR() {
                    const transactionsSection = document.getElementById('Transaction-Reports-Page-link');
                    if (transactionsSection) {
                        showSection('transactions');
                        transactionsSection.scrollIntoView({
                            behavior: 'smooth'
                        });
                        setTimeout(() => {
                            generateRRRReport();
                        }, 300);
                    }
                }
            </script>



            <script>
                // Function to Show Specific Section
                function showSection(section) {
                    document.querySelectorAll('main section').forEach(el => {
                        el.classList.add('hidden');
                        el.classList.remove('fade-in');
                    });

                    const selectedSection = document.getElementById(section + '-section');
                    selectedSection.classList.remove('hidden');
                    selectedSection.classList.add('fade-in');

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
                document.getElementById('Transaction-Reports-Page-link').addEventListener('click', () => showSection('transactions'));

                document.getElementById('reports-link').addEventListener('click', () => showSection('reports'));
                document.getElementById('settings-link').addEventListener('click', () => showSection('settings'));














                document.getElementById('transactions-link').addEventListener('click', () => showSection('collections-summary'));
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
            </script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Check if collections section exists
                    const collectionsSection = document.getElementById('collections-section');
                    if (!collectionsSection) return;

                    // Function to format currency
                    function formatCurrency(amount) {
                        return '₱' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                    }

                    // Function to fetch and display collections data
                    function loadCollectionsData() {
                        console.log('Loading collections data...');

                        fetch('treasurer_actions/get_collections_data.php')
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Network response was not ok');
                                }
                                return response.json();
                            })
                            .then(data => {
                                console.log('Data received:', data);

                                if (data.status === 'error') {
                                    console.error('Server error:', data.message);
                                    return;
                                }

                                // Update total collected funds
                                const totalElement = document.getElementById('total-collected');
                                if (totalElement) {
                                    totalElement.textContent = formatCurrency(data.total_collected);
                                }

                                // Update breakdown table
                                const breakdownTable = document.getElementById('collections-breakdown');
                                if (breakdownTable) {
                                    breakdownTable.innerHTML = '';

                                    data.breakdown.forEach(item => {
                                        const row = document.createElement('tr');
                                        row.className = 'border border-gray-400';

                                        const typeCell = document.createElement('td');
                                        typeCell.className = 'border border-gray-400 p-2';
                                        typeCell.textContent = item.payment_type;

                                        const amountCell = document.createElement('td');
                                        amountCell.className = 'border border-gray-400 p-2';
                                        amountCell.textContent = formatCurrency(item.total_amount);

                                        row.appendChild(typeCell);
                                        row.appendChild(amountCell);
                                        breakdownTable.appendChild(row);
                                    });
                                }

                                // Update chart if data exists
                                if (data.breakdown && data.breakdown.length > 0) {
                                    updateCollectionsChart(data.breakdown);
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching collections data:', error);
                            });
                    }

                    // Function to update the chart
                    function updateCollectionsChart(breakdownData) {
                        const ctx = document.getElementById('collectionsChart');
                        if (!ctx) return;



                        const labels = breakdownData.map(item => item.payment_type);
                        const amounts = breakdownData.map(item => item.total_amount);

                        // Color palette for the chart
                        const backgroundColors = [
                            'rgba(54, 162, 235, 0.7)', // Blue
                            'rgba(255, 99, 132, 0.7)', // Red
                            'rgba(75, 192, 192, 0.7)', // Teal
                            'rgba(255, 206, 86, 0.7)', // Yellow
                            'rgba(153, 102, 255, 0.7)', // Purple
                            'rgba(255, 159, 64, 0.7)' // Orange
                        ];

                        window.collectionsChart = new Chart(ctx, {
                            type: 'pie',
                            data: {
                                labels: labels,
                                datasets: [{
                                    data: amounts,
                                    backgroundColor: backgroundColors,
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            color: window.matchMedia('(prefers-color-scheme: dark)').matches ? '#fff' : '#000'
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                const label = context.label || '';
                                                const value = context.raw || 0;
                                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                const percentage = Math.round((value / total) * 100);
                                                return `${label}: ${formatCurrency(value)} (${percentage}%)`;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }

                    // Load data immediately if section is visible
                    if (!collectionsSection.classList.contains('hidden')) {
                        loadCollectionsData();
                    }

                    // Set up observer to load data when section becomes visible
                    const observer = new MutationObserver(function(mutations) {
                        mutations.forEach(function(mutation) {
                            if (mutation.attributeName === 'class') {
                                if (!collectionsSection.classList.contains('hidden')) {
                                    loadCollectionsData();
                                }
                            }
                        });
                    });

                    observer.observe(collectionsSection, {
                        attributes: true
                    });
                });
            </script>
            <script>
                const months = {
                    'january': 'january',
                    'february': 'february',
                    'march': 'march',
                    'april': 'april',
                    'may': 'may',
                    'june': 'june',
                    'july': 'july',
                    'august': 'august',
                    'september': 'september',
                    'october': 'october',
                    'november': 'november',
                    'december': 'december'
                };

                const openDatePickerButton = document.getElementById('openDatePicker');
                const datepickerContainer = document.getElementById('datepickerContainer');
                const selectedDateInput = document.getElementById('selectedDate');
                let currentlySelectedDate = null;

                // NEW: Variables to track current view of calendar
                let calendarYear = new Date().getFullYear();
                let calendarMonth = new Date().getMonth();

                function formatDateForQuery(date) {
                    const yyyy = date.getFullYear();
                    const mm = String(date.getMonth() + 1).padStart(2, '0');
                    const dd = String(date.getDate()).padStart(2, '0');
                    return `${yyyy}-${mm}-${dd}`;
                }

                function renderCalendar() {
                    datepickerContainer.innerHTML = ''; // Clear previous calendar

                    const now = new Date();
                    const firstDayOfMonth = new Date(calendarYear, calendarMonth, 1);
                    const lastDayOfMonth = new Date(calendarYear, calendarMonth + 1, 0);
                    const daysInMonth = lastDayOfMonth.getDate();
                    const startingDay = firstDayOfMonth.getDay(); // 0 for Sunday, etc.

                    const calendarTable = document.createElement('table');
                    calendarTable.classList.add('w-full', 'text-sm');

                    // Header row with days
                    const headerRow = document.createElement('tr');
                    const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                    daysOfWeek.forEach(day => {
                        const th = document.createElement('th');
                        th.textContent = day;
                        th.classList.add('p-1', 'text-center', 'font-normal', 'text-gray-600');
                        headerRow.appendChild(th);
                    });
                    calendarTable.appendChild(headerRow);

                    let dayCounter = 1;
                    for (let i = 0; i < 6; i++) {
                        const weekRow = document.createElement('tr');
                        for (let j = 0; j < 7; j++) {
                            const dayCell = document.createElement('td');
                            dayCell.classList.add('p-1', 'text-center', 'cursor-pointer');

                            if (i === 0 && j < startingDay) {
                                // Empty cells before first day
                            } else if (dayCounter > daysInMonth) {
                                // Empty cells after last day
                            } else {
                                dayCell.textContent = dayCounter;
                                const currentDate = new Date(calendarYear, calendarMonth, dayCounter);
                                const formattedDate = formatDateForQuery(currentDate);

                                dayCell.addEventListener('click', () => {
                                    currentlySelectedDate = formattedDate;
                                    selectedDateInput.value = formattedDate;
                                    datepickerContainer.classList.add('hidden');
                                    filterTransactions(formattedDate);
                                });

                                if (currentDate.toDateString() === now.toDateString()) {
                                    dayCell.classList.add('font-semibold', 'text-blue-500');
                                }
                                if (currentlySelectedDate === formattedDate) {
                                    dayCell.classList.add('bg-blue-100', 'text-blue-700', 'rounded-full');
                                }

                                dayCounter++;
                            }
                            weekRow.appendChild(dayCell);
                        }
                        calendarTable.appendChild(weekRow);

                        if (dayCounter > daysInMonth) {
                            break;
                        }
                    }

                    // Navigation section
                    const navigation = document.createElement('div');
                    navigation.classList.add('flex', 'justify-between', 'p-2', 'items-center');

                    const prevButton = document.createElement('button');
                    prevButton.textContent = '<';
                    prevButton.classList.add('px-2', 'py-1', 'rounded', 'hover:bg-gray-200');
                    prevButton.addEventListener('click', () => {
                        if (calendarMonth === 0) {
                            calendarMonth = 11;
                            calendarYear--;
                        } else {
                            calendarMonth--;
                        }
                        renderCalendar();
                    });

                    const nextButton = document.createElement('button');
                    nextButton.textContent = '>';
                    nextButton.classList.add('px-2', 'py-1', 'rounded', 'hover:bg-gray-200');
                    nextButton.addEventListener('click', () => {
                        if (calendarMonth === 11) {
                            calendarMonth = 0;
                            calendarYear++;
                        } else {
                            calendarMonth++;
                        }
                        renderCalendar();
                    });

                    const monthYearDisplay = document.createElement('div');
                    monthYearDisplay.classList.add('flex', 'items-center', 'gap-2');

                    const monthSelect = document.createElement('select');
                    monthSelect.classList.add('border', 'rounded', 'px-1', 'py-0.5', 'text-sm');
                    for (let m = 0; m < 12; m++) {
                        const option = document.createElement('option');
                        option.value = m;
                        option.textContent = new Date(0, m).toLocaleString('default', {
                            month: 'long'
                        });
                        if (m === calendarMonth) option.selected = true;
                        monthSelect.appendChild(option);
                    }
                    monthSelect.addEventListener('change', (e) => {
                        calendarMonth = parseInt(e.target.value);
                        renderCalendar();
                    });

                    const yearSelect = document.createElement('select');
                    yearSelect.classList.add('border', 'rounded', 'px-1', 'py-0.5', 'text-sm');
                    const currentYear = new Date().getFullYear();
                    for (let y = currentYear - 10; y <= currentYear + 10; y++) {
                        const option = document.createElement('option');
                        option.value = y;
                        option.textContent = y;
                        if (y === calendarYear) option.selected = true;
                        yearSelect.appendChild(option);
                    }
                    yearSelect.addEventListener('change', (e) => {
                        calendarYear = parseInt(e.target.value);
                        renderCalendar();
                    });

                    monthYearDisplay.appendChild(monthSelect);
                    monthYearDisplay.appendChild(yearSelect);

                    navigation.appendChild(prevButton);
                    navigation.appendChild(monthYearDisplay);
                    navigation.appendChild(nextButton);

                    datepickerContainer.appendChild(navigation);
                    datepickerContainer.appendChild(calendarTable);
                }

                openDatePickerButton.addEventListener('click', () => {
                    datepickerContainer.classList.toggle('hidden');
                    if (!datepickerContainer.classList.contains('hidden')) {
                        renderCalendar();
                    }
                });

                document.addEventListener('click', (event) => {
                    if (!datepickerContainer.contains(event.target) && event.target !== openDatePickerButton) {
                        datepickerContainer.classList.add('hidden');
                    }
                });

                // Function to fetch transactions and display them
                function filterTransactions(filter) {
                    let url = `treasurer_actions/get_transactions.php?filter=${filter}`;
                    if (currentlySelectedDate && filter === currentlySelectedDate) {
                        url = `treasurer_actions/get_transactions.php?date=${currentlySelectedDate}`;
                    } else if (filter !== 'today' && !filter.startsWith('week') && !filter.startsWith('year-') && Object.values(months).includes(filter)) {
                        url = `treasurer_actions/get_transactions.php?filter=${filter}`;
                    } else if (filter === 'today' || filter.startsWith('week') || filter.startsWith('year-')) {
                        url = `treasurer_actions/get_transactions.php?filter=${filter}`;
                    } else if (filter === 'all') {
                        url = `treasurer_actions/get_transactions.php?filter=all`;
                    }

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                allTransactionsOriginal = data.transactions; // keep original full list
                                allTransactions = data.transactions; // for display
                                currentPage = 1;
                                displayTransactions(allTransactions);
                            } else {
                                showToast(data.message, 'error'); // show error as toast
                            }
                        });

                }



                function formatDate(dateString) {
                    const date = new Date(dateString);
                    const options = {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    };
                    return date.toLocaleDateString(undefined, options);
                }

                function formatTime(timeString) {
                    const date = new Date(`1970-01-01T${timeString}`);
                    let hours = date.getHours();
                    const minutes = date.getMinutes().toString().padStart(2, '0');
                    const ampm = hours >= 12 ? 'pm' : 'am';
                    hours = hours % 12 || 12;
                    return `${hours}:${minutes} ${ampm}`;
                }


                function formatDateForQuery(date) {
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    return `${year}-${month}-${day}`;
                }

                function populateYearFilter() {
                    const yearFilter = document.getElementById('yearFilter');
                    const currentYear = new Date().getFullYear();
                    const startYear = currentYear - 10;

                    for (let i = currentYear; i >= startYear; i--) {
                        const option = document.createElement('option');
                        option.value = `year-${i}`;
                        option.textContent = i;
                        yearFilter.appendChild(option);
                    }
                }

                populateYearFilter();
                filterTransactions('all');


                // Apply CSS fixes to datepickerContainer
                let allTransactions = []; // for current displayed list
                let allTransactionsOriginal = [];

                const datepickerStyle = datepickerContainer.style;
                datepickerStyle.top = 'auto';
                datepickerStyle.bottom = 'calc(100% + 5px)'; // Position above the button with a small gap
                datepickerStyle.left = '0';
                datepickerStyle.transform = 'translateY(100%)'; // Adjust for the initial top-full

                // Global variables for pagination
                let currentPage = 1;
                let transactionsPerPage = calculateTransactionsPerPage();

                // Dynamically compute rows per page based on screen height
                function calculateTransactionsPerPage() {
                    const rowHeight = 70; // Adjust if your rows are taller/shorter
                    const availableHeight = window.innerHeight - 350;
                    const count = Math.floor(availableHeight / rowHeight);
                    return Math.max(count, 4); // Minimum 4 rows to avoid empty tables
                }

                // Recalculate when window is resized
                window.addEventListener("resize", () => {
                    transactionsPerPage = calculateTransactionsPerPage();
                    displayTransactions(allTransactions);
                });



                function displayTransactions(transactions) {
                    const transactionsTable = document.getElementById('transactionsTable');
                    transactionsTable.innerHTML = '';

                    const totalPages = Math.ceil(transactions.length / transactionsPerPage);
                    const startIndex = (currentPage - 1) * transactionsPerPage;
                    const endIndex = startIndex + transactionsPerPage;
                    const paginatedTransactions = transactions.slice(startIndex, endIndex);

                    paginatedTransactions.forEach(transaction => {
                        const row = document.createElement('tr');
                        row.classList.add('bg-white-200', 'border', 'border-gray-300');

                        let formattedParticulars = transaction.particulars.split(',').map(item => item.trim()).join(', ');

                        row.innerHTML = `
            <td class="p-3 border border-gray-400 p-2">${transaction.pgl_no}</td>
            <td class="p-3 border border-gray-400 p-2">${formatDate(transaction.date)}</td>
            <td class="p-3 border border-gray-400 p-2">${formatTime(transaction.time)}</td>
            <td class="p-3 border border-gray-400 p-2">${transaction.payorname}</td>
            <td class="p-3 border border-gray-400 p-2">${formattedParticulars}</td>
            <td class="p-3 border border-gray-400 p-2">₱${parseFloat(transaction.amount).toFixed(2)}</td>
            <td class="p-3 border border-gray-400 p-2">${transaction.payment_type}</td>
            <td class="p-3 border border-gray-400 p-2">${transaction.check_num || '-'}</td>
            <td class="p-3 border border-gray-400 p-2">
            <center>
                <button onclick="seeDetails(${transaction.id})"
                    class="text-blue-500 hover:text-blue-700 p-1 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                </button>
            </center>
            </td>
        `;
                        transactionsTable.appendChild(row);
                    });

                    addPaginationControls(totalPages);
                }


                document.getElementById('transaction-search').addEventListener('input', function() {
                    const searchTerm = this.value.trim().toLowerCase();

                    const filteredTransactions = searchTerm === '' ?
                        allTransactionsOriginal // reset to full original list
                        :
                        allTransactionsOriginal.filter(transaction =>
                            transaction.pgl_no.toLowerCase().includes(searchTerm) ||
                            transaction.payorname.toLowerCase().includes(searchTerm)
                        );

                    currentPage = 1;
                    displayTransactions(filteredTransactions);
                });




                document.getElementById('paginationControls').appendChild(button);



                function addPaginationControls(totalPages) {
                    let paginationContainer = document.getElementById('paginationControls');
                    if (!paginationContainer) return;

                    paginationContainer.innerHTML = '';

                    const controlsWrapper = document.createElement('div');
                    controlsWrapper.className = 'flex justify-center items-center flex-wrap gap-1 mb-2';

                    const createButton = (label, title, page, disabled = false) => {
                        const btn = document.createElement('button');
                        btn.innerHTML = label;
                        btn.title = title;
                        btn.disabled = disabled;
                        btn.className = `px-3 py-1 rounded-md text-sm border ${
            disabled
                ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
                : 'bg-gray-200 text-gray-700 hover:bg-blue-500 hover:text-white transition'
        }`;
                        btn.onclick = () => {
                            currentPage = page;
                            displayTransactions(allTransactions);
                        };
                        return btn;
                    };

                    controlsWrapper.appendChild(createButton('«', 'First Page', 1, currentPage === 1));
                    controlsWrapper.appendChild(createButton('←', 'Previous Page', currentPage - 1, currentPage === 1));

                    let startPage = Math.max(1, currentPage - 1);
                    let endPage = Math.min(totalPages, startPage + 2);

                    if (endPage - startPage < 2 && startPage > 1) {
                        startPage = Math.max(1, endPage - 2);
                    }

                    for (let i = startPage; i <= endPage; i++) {
                        const pageButton = document.createElement('button');
                        pageButton.textContent = i;
                        pageButton.className = `px-3 py-1 rounded-md text-sm border ${
            currentPage === i
                ? 'bg-blue-600 text-white border-blue-600'
                : 'bg-gray-200 text-gray-700 border-gray-300 hover:bg-blue-500 hover:text-white transition'
        }`;
                        pageButton.onclick = () => {
                            currentPage = i;
                            displayTransactions(allTransactions);
                        };
                        controlsWrapper.appendChild(pageButton);
                    }

                    controlsWrapper.appendChild(createButton('→', 'Next Page', currentPage + 1, currentPage === totalPages));
                    controlsWrapper.appendChild(createButton('»', 'Last Page', totalPages, currentPage === totalPages));

                    const containerWrapper = document.createElement('div');
                    containerWrapper.className = 'flex flex-col items-center';

                    containerWrapper.appendChild(controlsWrapper);

                    const pageLabel = document.createElement('div');
                    pageLabel.className = 'text-sm text-gray-700 mt-2';
                    pageLabel.textContent = `Page ${currentPage} of ${totalPages}`;
                    containerWrapper.appendChild(pageLabel);

                    paginationContainer.appendChild(containerWrapper);
                }









                function toggleDateRangeDropdown() {
                    const dropdown = document.getElementById('dateRangeDropdown');
                    dropdown.classList.toggle('hidden');
                }

                function filterByDateRange() {
                    const start = document.getElementById('startDate').value;
                    const end = document.getElementById('endDate').value;

                    if (!start || !end) {
                        showToast('Please select both start and end dates.', 'error');
                        return;
                    }

                    if (new Date(start) > new Date(end)) {
                        showToast('Start date cannot be after end date.', 'error');
                        return;
                    }


                    const url = `treasurer_actions/get_transactions.php?start_date=${start}&end_date=${end}`;

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                displayTransactions(data.transactions);
                            } else {
                                showToast(data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching date range transactions:', error);
                            showToast('Error fetching date range transactions: ' + error.message, 'error');
                        });

                }

                function filterByQuarter() {
                    const quarter = document.getElementById('quarterFilter').value;

                    if (!quarter) {
                        showToast('Please select a quarter.', 'error');
                        return;
                    }

                    const url = `treasurer_actions/get_transactions.php?quarter=${quarter}`;

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                displayTransactions(data.transactions);
                            } else {
                                showToast(data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching quarter transactions:', error);
                            showToast('Error fetching quarter transactions: ' + error.message, 'error');
                        });
                }






                async function seeDetails(id) {
                    console.log("Clicked:", id);

                    const transaction = allTransactions.find(t => t.id == id); // NOTE: using == to match string/number
                    if (!transaction) {
                        console.error("Transaction not found for ID:", id);
                        return;
                    }

                    // Fetch transaction items for this PGL No.
                    try {
                        const res = await fetch(`collector_actions/get_transaction_items.php?pgl_no=${transaction.pgl_no}`);
                        const itemsData = await res.json();

                        let particularsList = '';
                        if (itemsData.status === 'success' && itemsData.items.length > 0) {
                            particularsList = itemsData.items.map(item => {
                                return `<li>${item.particular} — ₱${parseFloat(item.amount).toFixed(2)}</li>`;
                            }).join('');
                        } else {
                            particularsList = transaction.particulars.split(',').map(item => `<li>${item}</li>`).join('');
                        }

                        const modalContent = `
            <p><strong>PGL No.:</strong> ${transaction.pgl_no}</p>
            <p><strong>Payor's Name:</strong> ${transaction.payorname}</p>
            <p><strong>Date:</strong> ${formatDate(transaction.date)}</p>
            <p><strong>Time:</strong> ${formatTime(transaction.time)}</p>
            <p><strong>Payment Type:</strong> ${transaction.payment_type}</p>
            <p><strong>Total Amount:</strong> ₱${parseFloat(transaction.amount).toFixed(2)}</p>
            <p><strong>Particulars Paid:</strong></p>
            <ul class="list-disc ml-5">${particularsList}</ul>
        `;

                        document.getElementById("modalContent").innerHTML = modalContent;
                        document.getElementById("transactionModal").classList.remove("hidden");

                    } catch (error) {
                        console.error("Error fetching transaction items:", error);
                        showToast("Error fetching transaction details.", "error");
                    }
                }

                function closeDetailsModal() {
                    document.getElementById("transactionModal").classList.add("hidden");
                }





                // Function to handle the viewTransaction click
                function viewTransaction(transactionId) {
                    // Use fetch to get the transaction details from your server
                    fetch(`treasurer_actions/get_transactions.php?id=${transactionId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const transaction = data.transaction;
                                populateReceipt(transaction);
                            } else {
                                const errorMessage = data.message || 'An unknown error occurred';
                                showToast('Error: ' + errorMessage, 'error'); // Show error as toast
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching transaction:', error);
                            showToast('Failed to fetch transaction details.', 'error'); // User-friendly toast
                        });
                }
            </script>

            <script src="treasurer_actions/dashboard_data.js"></script>

            <script>
                // Tab functionality
                document.addEventListener('DOMContentLoaded', function() {
                    const tabs = document.querySelectorAll('[role="tab"]');

                    tabs.forEach(tab => {
                        tab.addEventListener('click', function() {
                            const target = document.querySelector(this.getAttribute('data-tabs-target'));

                            // Hide all tab panels
                            document.querySelectorAll('[role="tabpanel"]').forEach(panel => {
                                panel.classList.add('hidden');
                            });

                            // Deactivate all tabs
                            tabs.forEach(t => {
                                t.classList.remove('border-blue-600', 'text-blue-600');
                                t.classList.add('border-transparent');
                            });

                            // Show the selected tab panel
                            target.classList.remove('hidden');

                            // Activate the clicked tab
                            this.classList.add('border-blue-600', 'text-blue-600');
                            this.classList.remove('border-transparent');
                        });
                    });

                    // Activate the first tab by default
                    document.querySelector('[role="tab"][aria-selected="true"]').click();

                    // Drag and drop functionality
                    const dropArea = document.getElementById('drop-area');
                    const fileInput = document.getElementById('file-input');
                    const dropContent = document.getElementById('drop-content');
                    const fileInfo = document.getElementById('file-info');
                    const fileName = document.getElementById('file-name');
                    const fileSize = document.getElementById('file-size');
                    const removeFile = document.getElementById('remove-file');

                    // Prevent default drag behaviors
                    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                        dropArea.addEventListener(eventName, preventDefaults, false);
                    });

                    function preventDefaults(e) {
                        e.preventDefault();
                        e.stopPropagation();
                    }

                    // Highlight drop area when item is dragged over it
                    ['dragenter', 'dragover'].forEach(eventName => {
                        dropArea.addEventListener(eventName, highlight, false);
                    });

                    ['dragleave', 'drop'].forEach(eventName => {
                        dropArea.addEventListener(eventName, unhighlight, false);
                    });

                    function highlight() {
                        dropArea.classList.add('border-blue-500', 'bg-blue-50');
                    }

                    function unhighlight() {
                        dropArea.classList.remove('border-blue-500', 'bg-blue-50');
                    }

                    // Handle dropped files
                    dropArea.addEventListener('drop', handleDrop, false);

                    function handleDrop(e) {
                        const dt = e.dataTransfer;
                        const files = dt.files;
                        handleFiles(files);
                    }

                    // Handle file selection via click
                    dropArea.addEventListener('click', () => {
                        fileInput.click();
                    });

                    fileInput.addEventListener('change', function() {
                        handleFiles(this.files);
                    });

                    function handleFiles(files) {
                        if (files.length > 0) {
                            const file = files[0];
                            displayFileInfo(file);
                            fileInput.files = files; // Set the files for the form submission
                        }
                    }

                    function displayFileInfo(file) {
                        fileName.textContent = file.name;
                        fileSize.textContent = formatFileSize(file.size);
                        dropContent.classList.add('hidden');
                        fileInfo.classList.remove('hidden');
                    }

                    function formatFileSize(bytes) {
                        if (bytes === 0) return '0 Bytes';
                        const k = 1024;
                        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                        const i = Math.floor(Math.log(bytes) / Math.log(k));
                        return parseFloat((bytes / Math.pow(k, i)).toFixed(2) + ' ' + sizes[i]);
                    }

                    removeFile.addEventListener('click', function(e) {
                        e.stopPropagation();
                        fileInput.value = '';
                        dropContent.classList.remove('hidden');
                        fileInfo.classList.add('hidden');
                    });

                    // Form submission
                    const form = document.getElementById('report-upload-form');
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();

                        const formData = new FormData(form);
                        const fileInput = form.querySelector('input[type="file"]');
                        let action = "Submitted report";

                        if (fileInput && fileInput.files.length > 0) {
                            const filename = fileInput.files[0].name;

                            // Monthly Report: e.g., Monthly_Report_August_2025
                            if (/monthly_report/i.test(filename)) {
                                const match = filename.match(/Monthly_Report_([A-Za-z]+)_(\d{4})/i);
                                if (match) {
                                    const monthName = match[1];
                                    const year = match[2];
                                    action = `Submitted monthly report for ${monthName} ${year}`;
                                } else {
                                    action = "Submitted monthly report";
                                }

                                // Quarterly Report: e.g., Quarterly_Report_Q3_2025
                            } else if (/quarterly_report/i.test(filename)) {
                                const match = filename.match(/Quarterly_Report_(Q\d)_(\d{4})/i);
                                if (match) {
                                    const quarter = match[1];
                                    const year = match[2];
                                    action = `Submitted quarterly report for ${quarter} ${year}`;
                                } else {
                                    action = "Submitted quarterly report";
                                }

                                // Yearly Report: e.g., Yearly_Report_2025
                            } else if (/yearly_report/i.test(filename)) {
                                const match = filename.match(/Yearly_Report_(\d{4})/i);
                                if (match) {
                                    const year = match[1];
                                    action = `Submitted yearly report for ${year}`;
                                } else {
                                    action = "Submitted yearly report";
                                }

                                // Engineering Share Report: e.g., Engineering_Share_Report_2025
                            } else if (/engineering_share_report/i.test(filename)) {
                                const match = filename.match(/Engineering_Share_Report_(\d{4})/i);
                                if (match) {
                                    const year = match[1];
                                    action = `Submitted engineering share report for ${year}`;
                                } else {
                                    action = "Submitted engineering share report";
                                }

                                // RRR Report: e.g., RRR_Report_2025
                            } else if (/rrr_report/i.test(filename)) {
                                const match = filename.match(/RRR_Report_(\d{4})/i);
                                if (match) {
                                    const year = match[1];
                                    action = `Submitted RRR report for ${year}`;
                                } else {
                                    action = "Submitted RRR report";
                                }
                            }
                        }

                        // Submit report
                        fetch('submit_report.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    showToast('Report submitted successfully!', 'success');
                                    form.reset();
                                    dropContent.classList.remove('hidden');
                                    fileInfo.classList.add('hidden');

                                    // 👉 Record audit
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
                                    showToast('Error: ' + data.message, 'error');
                                }
                            })
                            .catch(err => {
                                console.error('Error:', err);
                                showToast('An error occurred while submitting the report.', 'error');
                            });
                    });

                });

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

                // Initialize Flatpickr for the report date input
                flatpickr("#report-date", {
                    altInput: true,
                    altFormat: "m/d/Y", // display format (MM/DD/YYYY)
                    dateFormat: "Y-m-d", // storage format for the database (YYYY-MM-DD)
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
                    // Load reports when tab is shown
                    document.getElementById('view-reports-tab').addEventListener('click', loadReports);

                    // Filter event listeners
                    document.getElementById('report-type-filter').addEventListener('change', loadReports);
                    document.getElementById('report-date-from').addEventListener('change', loadReports);
                    document.getElementById('report-date-to').addEventListener('change', loadReports);

                    // Preview modal buttons
                    document.getElementById('close-preview-btn').addEventListener('click', () => {
                        document.getElementById('preview-modal').classList.add('hidden');
                    });

                    // Initial load if this is the active tab
                    if (!document.getElementById('view-reports').classList.contains('hidden')) {
                        loadReports();
                    }
                });

                let currentPreviewReportId = null;

                let allReportsData = []; // Holds all loaded reports
                let currentReportsPage = 1;
                let reportsPerPage = calculateReportsPerPage();

                function calculateReportsPerPage() {
                    const rowHeight = 80; // Adjust if your row height is different
                    const availableHeight = window.innerHeight - 400;
                    const count = Math.floor(availableHeight / rowHeight);
                    return Math.max(count, 4); // Minimum of 4 rows per page
                }

                window.addEventListener("resize", () => {
                    reportsPerPage = calculateReportsPerPage();
                    displayReportsPage();
                });

                function loadReports() {
                    const typeFilter = document.getElementById('report-type-filter').value;
                    const dateFrom = document.getElementById('report-date-from').value;
                    const dateTo = document.getElementById('report-date-to').value;

                    fetch('treasurer_actions/get_treasurer_reports.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                type: typeFilter,
                                date_from: dateFrom,
                                date_to: dateTo
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            allReportsData = data.success ? data.reports : [];
                            currentReportsPage = 1;
                            displayReportsPage();
                        })
                        .catch(error => console.error('Error:', error));
                }

                function displayReportsPage() {
                    const tableBody = document.getElementById('reports-table-body');
                    tableBody.innerHTML = '';

                    if (allReportsData.length === 0) {
                        tableBody.innerHTML = `
            <tr>
                <td colspan="5" class="border border-gray-300 p-4 text-center">No reports found</td>
            </tr>
        `;
                        document.getElementById('reportsPagination').innerHTML = '';
                        return;
                    }

                    const totalPages = Math.ceil(allReportsData.length / reportsPerPage);
                    const startIndex = (currentReportsPage - 1) * reportsPerPage;
                    const endIndex = startIndex + reportsPerPage;
                    const pageReports = allReportsData.slice(startIndex, endIndex);

                    pageReports.forEach(report => {
                        const row = document.createElement('tr');
                        row.className = 'hover:bg-gray-50';

                        const formattedReportDate = new Date(report.report_date).toLocaleDateString();
                        const formattedSubmissionDate = new Date(report.submission_timestamp).toLocaleString();

                        row.innerHTML = `
           
            <td class="p-3 border border-gray-400 p-2 capitalize">${report.report_type}</td>
            <td class="p-3 border border-gray-400 p-2">${formattedReportDate}</td>
            <td class="p-3 border border-gray-400 p-2">${formattedSubmissionDate}</td>
            <td class="p-3 border border-gray-400 p-2">${report.description}</td>
            <td class="p-3 border border-gray-400 p-2">
                <div class="flex space-x-2 justify-center">
                    <button onclick="previewReport(${report.report_id}, '${report.file_path}')" class="text-blue-600 hover:text-blue-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                    </button>
                    <button onclick="downloadReport(${report.report_id}, '${report.file_path}')" class="text-green-600 hover:text-green-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                    </button>
                    <button onclick="confirmDeleteReport(${report.report_id})" class="text-red-600 hover:text-red-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </td>
        `;
                        tableBody.appendChild(row);
                    });

                    renderReportsPagination(totalPages);
                }

                function renderReportsPagination(totalPages) {
                    const paginationContainer = document.getElementById('reportsPagination');
                    paginationContainer.innerHTML = '';

                    const wrapper = document.createElement('div');
                    wrapper.className = 'flex justify-center items-center flex-wrap gap-1 mb-2';

                    const createPageButton = (label, page, disabled = false) => {
                        const btn = document.createElement('button');
                        btn.textContent = label;
                        btn.disabled = disabled;
                        btn.className = `px-3 py-1 rounded-md text-sm border ${
            disabled ? 'bg-gray-300 text-gray-500 cursor-not-allowed' :
            'bg-gray-200 text-gray-700 hover:bg-blue-500 hover:text-white transition'
        }`;
                        btn.onclick = () => {
                            currentReportsPage = page;
                            displayReportsPage();
                        };
                        return btn;
                    };

                    // First & Previous
                    wrapper.appendChild(createPageButton('«', 1, currentReportsPage === 1));
                    wrapper.appendChild(createPageButton('←', currentReportsPage - 1, currentReportsPage === 1));

                    // Page numbers
                    let startPage = Math.max(1, currentReportsPage - 1);
                    let endPage = Math.min(totalPages, startPage + 2);
                    if (endPage - startPage < 2 && startPage > 1) startPage = Math.max(1, endPage - 2);

                    for (let i = startPage; i <= endPage; i++) {
                        const btn = document.createElement('button');
                        btn.textContent = i;
                        btn.className = `px-3 py-1 rounded-md text-sm border ${
            currentReportsPage === i ? 'bg-blue-600 text-white border-blue-600' :
            'bg-gray-200 text-gray-700 border-gray-300 hover:bg-blue-500 hover:text-white transition'
        }`;
                        btn.onclick = () => {
                            currentReportsPage = i;
                            displayReportsPage();
                        };
                        wrapper.appendChild(btn);
                    }

                    // Next & Last
                    wrapper.appendChild(createPageButton('→', currentReportsPage + 1, currentReportsPage === totalPages));
                    wrapper.appendChild(createPageButton('»', totalPages, currentReportsPage === totalPages));

                    const pageLabel = document.createElement('div');
                    pageLabel.className = 'text-sm text-gray-700 mt-2 flex justify-center';
                    pageLabel.textContent = `Page ${currentReportsPage} of ${totalPages}`;


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
                                if (sheetName.toLowerCase().includes('transaction') || sheetName.toLowerCase().includes('monthly') || sheetName.toLowerCase().includes('quarterly') ||
                                    sheetName.toLowerCase().includes('annual') || sheetName.toLowerCase().includes('engineering share') || sheetName.toLowerCase().includes('rrr')) {
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

                    // Log the download (you could send this to your server)
                    fetch('log_downloads.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            report_id: reportId,
                            action: 'download'
                        })
                    });
                }

                function confirmDeleteReport(reportId) {
                    if (confirm('Are you sure you want to delete this report? This action cannot be undone.')) {
                        deleteReport(reportId);
                    }
                }

                function deleteReport(reportId) {
                    fetch('delete_report.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                report_id: reportId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showToast('Report deleted successfully', 'success');
                                loadReports(); // Refresh the table
                            } else {
                                showToast('Error deleting report: ' + data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showToast('An error occurred while deleting the report', 'error');
                        });
                }
            </script>
</body>

</html>