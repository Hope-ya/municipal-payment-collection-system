<?php
session_start();

include 'backend/db_config_notpdo.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['position'] !== 'Collector') {
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


$email = $_SESSION["email"];

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
$position = $_SESSION['user']['position'] ?? null;
$recovery_email = isset($user['recovery_email']) ? $user['recovery_email'] : "";

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collector's Portal | <?php echo htmlspecialchars($orgName); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/3.2.0/remixicon.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
    <!-- Organization Logo as Favicon -->
    <link rel="icon" type="image/png" href="<?php echo htmlspecialchars($orgLogo); ?>" />

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

    <!-- Add this in your <head> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script src="scripts/collector_top.js"></script>
</head>

<body class="bg-gray-50  text-gray-900  transition-colors duration-300 overflow-hidden">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside id="sidebar"
            class="bg-gray-100  shadow-md p-4 flex flex-col items-center justify-between transition-all duration-300">
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

                            <p class="text-sm text-gray-500  sidebar-text break-words leading-tight">
                                <?php echo htmlspecialchars($orgName); ?>
                            </p>
                        </div>
                    </div>
                </div>
                <hr><br>
                <ul class="w-full text-gray-900 ">
                    <li class="mb-2">
                        <a href="#" id="dashboard-link"
                            class=" flex items-center p-2 rounded hover:bg-gray-300  transition-all w-full">
                            <i class='ri-dashboard-line text-2xl'></i> <!-- Icon size increased -->
                            <span class="ml-2 sidebar-text">Dashboard</span>
                        </a>
                    </li>

                    <li class="mb-2"><a href="#" id="batches-link"
                            class="flex items-center p-2 rounded hover:bg-gray-300  transition-all w-full"><i class="ri-file-paper-line text-2xl"></i><span
                                class="ml-2 sidebar-text">Receipt Batches</span></a></li>

                    <li class="mb-2"><a href="#" id="payments-link"
                            class="flex items-center p-2 rounded hover:bg-gray-300  transition-all w-full"><i
                                class='ri-money-dollar-circle-line text-2xl '></i><span
                                class="ml-2 sidebar-text">Payments</span></a></li>

                    <li class="mb-2"><a href="#" id="transactions-link"
                            class="flex items-center p-2 rounded hover:bg-gray-300  transition-all w-full"><i class="ri-exchange-box-line text-2xl"></i><span
                                class="ml-2 sidebar-text">Transactions</span></a></li>

                    <li class="mb-2">
                        <a href="#" id="reports-link"
                            class="flex items-center p-2 rounded hover:bg-gray-300  transition-all w-full">
                            <i class='ri-file-chart-line text-2xl'></i>
                            <span class="ml-2 sidebar-text">Reports</span>
                        </a>
                    </li>

                    <li class="mb-2"> <a href="#" id="settings-link"
                            class="flex items-center p-2 rounded hover:bg-gray-300  transition-all w-full">
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
                <h1 class="text-lg font-semibold">Collector's Portal</h1>
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



            <!-- Main Content -->
            <!-- Main Content -->
            <div class="flex-1 p-6">
                <main id="main-content">



                    <!--Dashboard-->
                    <section
                        id="dashboard-section"
                        class="mt-1 px-6 flex flex-col items-center">
                        <div
                            class="w-full max-w-8xl transition-all duration-300 h-[80vh] max-h-screen overflow-y-auto">
                            <h2 class="text-xl font-bold mb-4 text-gray-900 ">Collector's Dashboard | Welcome, Collector <?php echo htmlspecialchars($user['firstname']); ?> <?php echo htmlspecialchars($user['lastname']); ?>!</h2>

                            <div
                                class="grid grid-cols-1 lg:grid-cols-3 gap-6 p-2 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-200">
                                <div class="flex flex-col space-y-4 min-w-[300px] flex-shrink-0">
                                    <!-- Total Transactions Card -->
                                    <div class="p-6 bg-white  text-gray-900  rounded-lg shadow-md hover:shadow-lg text-left transition-all duration-300 hover:shadow-2xl flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-blue-600 ">Total Transactions</p>
                                            <h3 class="text-2xl font-bold text-gray-800 " id="total-transactions">0</h3>
                                        </div>
                                        <button class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition" onclick="openSummModal('transactionsModal')">View Details</button>

                                    </div>

                                    <!-- Total Revenue Card -->
                                    <div class="p-6 bg-white  text-gray-900  rounded-lg shadow-md hover:shadow-lg text-left transition-all duration-300 hover:shadow-2xl flex items-center justify-between">
                                        <div>
                                            <p class="text-sm text-green-600 ">Total Revenue</p>
                                            <h3 class="text-2xl font-bold text-gray-800 t-gray-200" id="total-revenue">₱0.00</h3>
                                        </div>
                                        <button class="px-4 py-2 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition" onclick="openSummModal('revenueModal')">View Details</button>


                                    </div>

                                    <!-- Payment Type Breakdown + Report Shortcuts Card -->
                                    <div class="p-6 bg-white  text-gray-900  rounded-lg shadow-md hover:shadow-lg transition-all duration-300 hover:shadow-2xl">

                                        <!-- Tabs -->
                                        <div class="flex border-b border-gray-200  mb-4">
                                            <button class="tab-btn px-3 py-1 text-sm font-medium border-b-2 border-blue-500 text-blue-500 focus:outline-none"
                                                onclick="switchTab('paymentTypeTab')">
                                                Payment Type Breakdown
                                            </button>
                                            <button class="tab-btn px-3 py-1 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-blue-500 focus:outline-none"
                                                onclick="switchTab('reportShortcutsTab')">
                                                Report Shortcuts
                                            </button>
                                        </div>

                                        <div id="paymentTypeTab" class="tab-content">
                                            <div class="flex flex-col md:flex-row md:justify-between md:items-center">
                                                <div class="mb-4 md:mb-0">
                                                    <p class="text-sm text-blue-600">Payment Type Breakdown</p>
                                                </div>

                                                <!-- Chart Container -->
                                                <div id="paymentTypeChartContainer"
                                                    class="relative w-full max-w-[280px] sm:max-w-[320px] md:max-w-[360px] aspect-square mx-auto md:mx-0">
                                                    <canvas id="paymentTypeChart" class="w-full h-full"></canvas>
                                                </div>
                                            </div>
                                        </div>





                                        <div id="reportShortcutsTab" class="tab-content hidden">
                                            <div class="grid grid-cols-1 gap-2">
                                                <button type="button"
                                                    onclick="scrollToTransactionsAndGenerateDaily()"
                                                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-3 rounded-md text-sm font-semibold text-center w-full">
                                                    Daily
                                                </button>


                                                <button type="button"
                                                    onclick="scrollToTransactionsAndOpenMonthModal()"
                                                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-3 rounded-md text-sm font-semibold text-center w-full">
                                                    Monthly
                                                </button>
                                            </div>
                                        </div>


                                    </div>



                                </div>





                                <!-- Transactions Modal -->
                                <div id="transactionsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                                    <div class="bg-white  rounded-lg w-11/12 md:w-3/4 lg:w-2/3 p-6 relative">
                                        <button class="absolute top-4 right-4 text-gray-500  hover:text-gray-800  text-3xl font-bold" onclick="closeSummModal('transactionsModal')">&times;</button>

                                        <h2 class="text-xl font-bold text-gray-800  mb-4">Transactions Summary</h2>

                                        <!-- Timeframe Filters -->
                                        <div class="flex flex-wrap gap-2 mb-4" id="transactionsFilters">
                                            <button class="timeframe-btn px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700" onclick="setActiveFilter(this, 'transactions'); loadTransactionsData('daily')">Daily</button>
                                            <button class="timeframe-btn px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700" onclick="setActiveFilter(this, 'transactions'); loadTransactionsData('weekly')">Weekly</button>
                                            <button class="timeframe-btn px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700" onclick="setActiveFilter(this, 'transactions'); loadTransactionsData('monthly')">Monthly</button>
                                            <button class="timeframe-btn px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700" onclick="setActiveFilter(this, 'transactions'); loadTransactionsData('quarterly')">Quarterly</button>
                                            <button class="timeframe-btn px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700" onclick="setActiveFilter(this, 'transactions'); loadTransactionsData('yearly')">Yearly</button>
                                        </div>

                                        <!-- Table injected by JS -->
                                        <div id="transactionsList" class="overflow-x-auto"></div>
                                    </div>
                                </div>

                                <!-- Revenue Modal -->
                                <div id="revenueModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                                    <div class="bg-white  rounded-lg w-11/12 md:w-3/4 lg:w-2/3 p-6 relative">
                                        <button class="absolute top-4 right-4 text-gray-500  hover:text-gray-800 text-3xl font-bold" onclick="closeSummModal('revenueModal')">&times;</button>

                                        <h2 class="text-xl font-bold text-gray-800  mb-4">Revenue Summary</h2>

                                        <!-- Timeframe Filters -->
                                        <div class="flex flex-wrap gap-2 mb-4" id="revenueFilters">
                                            <button class="timeframe-btn px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700" onclick="setActiveFilter(this, 'revenue'); loadRevenueData('daily')">Daily</button>
                                            <button class="timeframe-btn px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700" onclick="setActiveFilter(this, 'revenue'); loadRevenueData('weekly')">Weekly</button>
                                            <button class="timeframe-btn px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700" onclick="setActiveFilter(this, 'revenue'); loadRevenueData('monthly')">Monthly</button>
                                            <button class="timeframe-btn px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700" onclick="setActiveFilter(this, 'revenue'); loadRevenueData('quarterly')">Quarterly</button>
                                            <button class="timeframe-btn px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700" onclick="setActiveFilter(this, 'revenue'); loadRevenueData('yearly')">Yearly</button>
                                        </div>

                                        <!-- Table injected by JS -->
                                        <div id="revenueList" class="overflow-x-auto"></div>
                                    </div>
                                </div>




                                <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md hover:shadow-lg relative transition-all duration-300 hover:shadow-2xl flex flex-col">
                                    <div class="flex justify-between items-start flex-wrap gap-2">

                                        <h3 class="text-lg font-semibold text-gray-900">Revenue Overview
                                        </h3>
                                        <div class="flex items-center justify-end mt-2">
                                            <label for="chartFilter" class="mr-2 text-sm text-gray-600 ">View By:</label>
                                            <select id="chartFilter" class="text-sm p-2 rounded-md  border border-gray-300 ">
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

                    <section
                        id="batches-section"
                        class="mt-1 px-6 flex flex-col items-center">
                        <div
                            class="bg-white shadow-xl rounded-2xl p-8 w-full max-w-8xl transition-all duration-300 hover:shadow-2xl h-[80vh] max-h-screen overflow-y-auto">

                            <h2 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6">Receipt Batches</h2>

                            <!-- Search -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 sm:mb-6">
                                <div class="w-full">
                                    <label for="batch-search" class="block text-sm font-medium text-gray-700 mb-1">
                                        Search by Serial Number:
                                    </label>
                                    <input type="text" id="batch-search"
                                        class="w-full p-2 sm:p-3 border border-gray-300 rounded-md focus:ring focus:ring-blue-200"
                                        placeholder="Enter serial number">
                                </div>
                                <div class="flex justify-end items-end">
                                    <button id="add-batch-btn"
                                        class="px-5 py-3 border border-blue-600 text-blue-600 font-medium rounded-lg hover:bg-blue-600 hover:text-white transition-all flex items-center">
                                        + Add Batch
                                    </button>
                                </div>
                            </div>

                            <!-- Table -->
                            <div class="overflow-x-auto max-h-[60vh] overflow-y-auto border border-gray-200 rounded-md">
                                <table class="min-w-full table-auto border-collapse text-sm">
                                    <thead class="sticky top-0 bg-gray-300 text-black">
                                        <tr>
                                            <th class="p-3 border border-gray-400 font-medium">ID</th>
                                            <th class="p-3 border border-gray-400 font-medium">Quantity</th>
                                            <th class="p-3 border border-gray-400 font-medium">Quantity Left</th>
                                            <th class="p-3 border border-gray-400 font-medium">Start Serial</th>
                                            <th class="p-3 border border-gray-400 font-medium">End Serial</th>
                                            <th class="p-3 border border-gray-400 font-medium">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="batch-table-body" class="bg-white divide-y divide-gray-200">
                                        <!-- Data loads here -->
                                    </tbody>
                                </table>

                                <!-- Pagination -->

                            </div>
                            <div id="pagination-controls" class="mt-4 flex justify-center space-x-2"></div>
                        </div>
                    </section>



                    <!-- Add/Edit Modal -->
                    <div id="batch-modal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center z-50">
                        <div class="bg-white p-6 rounded-lg shadow-lg w-[36rem]">
                            <h3 id="modal-title" class="text-xl font-bold mb-4">Add Batch</h3>
                            <form id="batch-form" method="POST">
                                <input type="hidden" id="batch-id" name="batch-id">
                                <input type="hidden" id="collector-email" name="collector-email" value="<?php echo $_SESSION['email']; ?>">

                                <div class="mb-3">
                                    <label for="quantity" class="block text-sm font-medium mb-1">Quantity</label>
                                    <input type="number" id="quantity" name="quantity" class="w-full p-2 border rounded-md" required>
                                </div>

                                <div class="mb-3">
                                    <label for="quantity-left" class="block text-sm font-medium mb-1">Quantity Left</label>
                                    <input type="number" id="quantity-left" name="quantity-left" class="w-full p-2 border rounded-md" readonly>
                                </div>


                                <div class="mb-3">
                                    <label for="serial-start" class="block text-sm font-medium mb-1">Starting Serial No.</label>
                                    <input type="number" id="serial-start" name="serial-start" class="w-full p-2 border rounded-md" required>
                                </div>

                                <div class="mb-3">
                                    <label for="serial-end" class="block text-sm font-medium mb-1">Ending Serial No.</label>
                                    <input type="number" id="serial-end" name="serial-end" class="w-full p-2 border rounded-md" required>
                                </div>

                                <div class="flex justify-end space-x-2 mt-4">
                                    <button id="cancel-btn" type="button" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancel</button>
                                    <button id="save-btn" type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>




                    <!-- Payment Section -->
                    <!-- Payment Section -->
                    <section id="payments-section" class="mt--10 p--15">
                        <!-- Make this a form -->
                        <form id="paymentForm" onsubmit="processPayment(event)" method="POST">
                            <div id="paymentFormSection" class="flex justify-center items-center w-full px-10">
                                <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-8xl max-h-[80vh] overflow-y-auto transition-all duration-300">
                                    <h2 class="text-2xl font-bold text-left mb-4 text-gray-800">Payment Processing</h2>
                                    <hr> <br>

                                    <div class="space-y-4">
                                        <div>
                                            <label for="pglNum" class="block font-medium text-gray-800">PGL No.</label>
                                            <input type="text" id="pgl"
                                                class="w-full p-2 border rounded-md"
                                                placeholder="Enter PGL No." disabled required>
                                        </div>

                                        <div>
                                            <label for="payerName" class="block font-medium text-gray-800">Payer Name</label>
                                            <input type="text" id="payerName"
                                                class="w-full p-2 border rounded-md"
                                                placeholder="Enter payer name" required>
                                        </div>

                                        <div>
                                            <label class="block font-medium text-gray-800">Select Particulars</label>
                                            <button type="button"
                                                class="w-full p-2 border border-blue-500 text-blue-500 rounded-md hover:bg-blue-500 hover:text-white transition-all"
                                                onclick="openParticularsModal()">
                                                Select Particulars
                                            </button>
                                        </div>

                                        <div id="selectedParticularsDisplay" class="mt-2 text-sm text-gray-700"></div>

                                        <div id="violationContainer" class="space-y-4"></div>

                                        <div>
                                            <label for="amount" class="block font-medium text-gray-800">Amount</label>
                                            <input type="number" id="amount"
                                                step="0.01" min="0"
                                                class="w-full p-2 border rounded-md"
                                                placeholder="0.00"
                                                readonly>
                                        </div>

                                        <div>
                                            <label for="paymentType" class="block font-medium text-gray-800">Payment Type</label>
                                            <select id="paymentType"
                                                class="w-full p-2 border rounded-md" required>
                                                <option value="">Select Payment Type</option>
                                                <option value="Cash">Cash</option>
                                                <option value="Check">Check</option>
                                            </select>
                                        </div>

                                        <div id="checkNumberContainer" class="hidden">
                                            <label for="checkNumber" class="block font-medium text-gray-800">Check Number</label>
                                            <input type="text" id="checkNumber"
                                                class="w-full p-2 border rounded-md"
                                                placeholder="Enter Check Number">
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="flex flex-col sm:flex-row gap-3 mt-6">
                                            <button type="button" id="clearFormBtn"
                                                class="flex-1 py-2 border border-gray-400 text-gray-600 rounded-md hover:bg-gray-200 transition-all">
                                                Clear Form
                                            </button>

                                            <button type="submit" id="processPaymentBtn"
                                                class="flex-1 py-2 border border-green-500 text-green-500 rounded-md hover:bg-green-500 hover:text-white transition-all">
                                                Process Payment
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </section>

                    <!-- Particulars Modal -->
                    <div id="particularsModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
                        <div class="bg-white p-6 rounded-lg w-full max-w-3xl shadow-lg">

                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-xl font-bold text-gray-800">Select Payment Particulars</h2>
                                <button onclick="closeModal()" class="text-gray-500 hover:text-red-600 text-xl font-bold"><i class="ri-close-line text-2xl"></i></button>
                            </div>

                            <!-- Search Bar -->
                            <input
                                type="text"
                                id="searchParticulars"
                                oninput="filterParticulars()"
                                placeholder="Search by Particular / Department / Account Code"
                                class="w-full p-2 mb-4 border rounded-md" />

                            <!-- Filters -->
                            <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-2">
                                <input type="text" id="filterParticular" placeholder="Filter by Particular" class="p-2 border rounded-md">
                                <input type="text" id="filterDepartment" placeholder="Filter by Department" class="p-2 border rounded-md">
                                <input type="text" id="filterAccountCode" placeholder="Filter by Account Code" class="p-2 border rounded-md">
                            </div>

                            <!-- Dynamic List of Particulars -->
                            <ul id="particularsList" class="space-y-2 max-h-[300px] overflow-y-auto">
                                <!-- JS will populate items here -->
                            </ul>

                            <!-- Done Button -->
                            <div class="mt-6 text-right">
                                <button
                                    onclick="closeModal()"
                                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                    Done
                                </button>
                            </div>

                        </div>
                    </div>

                    <!-- Transaction Confirmation Modal -->
                    <div id="paymentConfirmModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
                        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-lg">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">Confirm Transaction</h3>
                            <div id="confirmTransactionDetails" class="mb-4 text-gray-700"></div>
                            <p class="text-yellow-600 font-medium mb-4">Are the transaction details correct? Click Yes to confirm payment.</p>
                            <div class="flex justify-end gap-2">
                                <button onclick="closeConfirmModal()" class="px-4 py-2 rounded-md border text-gray-800">No</button>
                                <button onclick="confirmPayment()" class="px-4 py-2 rounded-md bg-green-600 text-white hover:bg-green-700">Yes</button>
                            </div>
                        </div>
                    </div>





                    <!-- Transaction Page -->
                    <!-- Transaction Page -->
                    <section
                        id="transactions-section"
                        class="mt-1 px-6 flex flex-col items-center">
                        <div
                            class="bg-white shadow-xl rounded-2xl p-8 w-full max-w-8xl transition-all duration-300 hover:shadow-2xl h-[80vh] max-h-screen overflow-y-auto">





                            <h2 class="text-xl sm:text-2xl font-bold mb-4">Transactions</h2>

                            <!-- FILTER BAR -->
                            <div class="flex flex-wrap justify-between items-center gap-2 mb-4 border border-gray-300 rounded p-2">
                                <div class="flex flex-wrap gap-2">

                                    <button onclick="filterTransactions('today')"
                                        class="px-3 sm:px-4 py-2 border border-gray-300 rounded hover:bg-gray-200 transition-all text-sm sm:text-base">
                                        Today
                                    </button>

                                    <select onchange="filterTransactions(this.value)"
                                        class="px-3 sm:px-4 py-2 border border-gray-300 rounded text-sm sm:text-base">
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

                                    <select id="yearFilter" onchange="filterTransactions(this.value)"
                                        class="px-3 sm:px-4 py-2 border border-gray-300 rounded text-sm sm:text-base">
                                        <option value="">Select Year</option>
                                    </select>

                                    <!-- DATE PICKER -->
                                    <div class="relative z-20">
                                        <button id="openDatePicker"
                                            class="px-3 sm:px-4 py-2 border border-gray-300 rounded hover:bg-gray-200 transition-all text-sm sm:text-base">
                                            Select Day
                                        </button>

                                        <div id="datepickerContainer"
                                            class="absolute top-full left-0 bg-white border border-gray-300 rounded shadow-md mt-1 hidden overflow-auto max-h-64 z-50">
                                            <!-- Calendar content -->
                                        </div>

                                        <input type="hidden" id="selectedDate">
                                    </div>

                                    <!-- DATE RANGE -->
                                    <div class="relative inline-block text-left">
                                        <button onclick="toggleDateRangeDropdown()"
                                            class="px-3 sm:px-4 py-2 border border-gray-300 rounded hover:bg-gray-200 transition-all text-sm sm:text-base">
                                            Filter by Date Range
                                        </button>

                                        <div id="dateRangeDropdown"
                                            class="absolute z-10 mt-2 p-4 bg-white border border-gray-300 rounded shadow-lg hidden w-[95vw] sm:w-auto">
                                            <div class="flex flex-col sm:flex-row items-center gap-2">
                                                <label for="startDate" class="text-sm text-gray-700">From:</label>
                                                <input type="date" id="startDate" class="px-3 py-2 border border-gray-300 rounded">

                                                <label for="endDate" class="text-sm text-gray-700">To:</label>
                                                <input type="date" id="endDate" class="px-3 py-2 border border-gray-300 rounded">

                                                <button onclick="filterByDateRange()"
                                                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition-all">
                                                    Filter
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <input type="text" id="transaction-search"
                                        placeholder="Search by PGL No. or Payer Name"
                                        class="px-3 sm:px-4 py-2 border border-gray-300 rounded w-full sm:w-auto text-sm sm:text-base" />
                                </div>

                                <!-- REPORT BUTTON -->
                                <div class="relative flex flex-wrap gap-2 text-left">
                                    <button onclick="toggleDropdownReport()" id="reportButton"
                                        class="px-5 py-3 border border-blue-600 text-blue-600 font-medium rounded-lg hover:bg-blue-600 hover:text-white transition-all flex items-center">
                                        <i class="ri-file-list-3-line mr-1"></i> Generate Report
                                    </button>

                                    <div id="reportDropdown"
                                        class="hidden absolute z-10 mt-2 w-48 bg-white border border-gray-200 rounded shadow-lg">
                                        <button onclick="generateDailyReport()"
                                            class="block w-full text-left px-4 py-2 hover:bg-gray-100">Generate Daily Report</button>
                                        <button onclick="event.stopPropagation(); openMonthModal()"
                                            class="block w-full text-left px-4 py-2 hover:bg-gray-100">Generate Monthly Report</button>
                                    </div>
                                </div>
                            </div>





                            <!-- Month Modal -->
                            <div id="monthModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
                                <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md relative">
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

                            <!-- Report Preview Modal -->
                            <div id="reportPreview" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 hidden">
                                <div class="bg-white w-full max-w-6xl mx-auto rounded-lg shadow-lg flex flex-col max-h-[85vh] overflow-hidden">
                                    <div id="reportContent" class="bg-gray-100 p-4 rounded flex-1 overflow-y-auto">
                                        <!-- Report content here -->
                                    </div>

                                    <div class="flex justify-end gap-4 p-4 border-t bg-white sticky bottom-0 left-0 right-0">
                                        <button onclick="closeReportPreview()"
                                            class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">
                                            Back
                                        </button>
                                        <button id="downloadBtn"
                                            class="bg-blue-600 text-white px-4 py-2 rounded hidden hover:bg-blue-800"
                                            onclick="downloadMonthlyReport()">
                                            Download Report
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- TRANSACTIONS TABLE (SCROLLABLE) -->
                            <div class="overflow-x-auto max-h-[60vh] overflow-y-auto border border-gray-200 rounded-md">
                                <table class="min-w-full table-auto border-collapse text-sm">
                                    <thead class="sticky top-0 bg-gray-300 text-black">
                                        <tr>
                                            <th class="p-3 border border-gray-400 font-medium">PGL No.</th>
                                            <th class="p-3 border border-gray-400 font-medium">Released Date</th>
                                            <th class="p-3 border border-gray-400 font-medium">Time</th>
                                            <th class="p-3 border border-gray-400 font-medium">Remitted Date</th>
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

                        <!-- EDIT PAYOR NAME MODAL -->
                        <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden z-50">
                            <div class="bg-white p-8 rounded-xl shadow-2xl w-96"> <!-- Bigger width + padding -->

                                <h2 class="text-xl font-semibold mb-6">Edit Payor Name</h2> <!-- Larger text -->

                                <!-- Hidden fields -->
                                <input type="hidden" id="editTransactionId">
                                <input type="hidden" id="oldPayorName">
                                <input type="hidden" id="editPGLNo">

                                <label class="block text-base mb-2">Payor Name:</label> <!-- Larger label -->
                                <input type="text" id="editPayorName"
                                    class="border px-4 py-2 w-full rounded-lg text-base focus:outline-none focus:ring-2 focus:ring-blue-500"> <!-- Larger input -->

                                <div class="flex justify-end gap-3 mt-6"> <!-- Bigger space -->
                                    <button onclick="closeEditModal()"
                                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded-lg text-base">
                                        Cancel
                                    </button>

                                    <button onclick="saveEditedPayorName()"
                                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-base">
                                        Save
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- CANCEL TRANSACTION MODAL -->
                        <div id="cancelModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
                            <div class="bg-white p-6 rounded-lg shadow-xl w-90">

                                <h2 class="text-xl font-semibold text-red-600 mb-3">Cancel Transaction</h2>

                                <p class="text-gray-700 text-base mb-3">
                                    Are you sure you want to cancel this transaction?<br>
                                    <strong>This action cannot be undone.</strong>
                                </p>

                                <input type="hidden" id="cancelTransactionId">
                                <input type="hidden" id="cancelTransactionPGL">

                                <div class="flex justify-end gap-3 mt-4">
                                    <button onclick="closeCancelModal()"
                                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-3 py-1.5 rounded-lg text-sm">
                                        No, Go Back
                                    </button>

                                    <button onclick="confirmCancelTransaction()"
                                        class="bg-red-600 hover:bg-red-700 text-white p-3 rounded-lg text-sm">
                                        Yes, Cancel
                                    </button>
                                </div>
                            </div>
                        </div>




                    </section>

                    <!-- Daily Report Modal -->
                    <div id="dailyReportModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
                        <div class="bg-white w-11/12 md:w-10/12 rounded shadow-lg p-6 relative max-h-[90vh] overflow-hidden">

                            <h2 class="text-xl font-bold mb-4">Daily Report — Today’s Transactions</h2>

                            <p class="text-sm text-gray-700 mb-3">
                                <strong>Select only the transactions that were processed BEFORE your cut-off time.</strong><br>
                                Unchecking a transaction will automatically change its remitted date to the next day.
                            </p>

                            <!-- Close Button -->
                            <button onclick="closeDailyModal()" class="absolute top-3 right-3 text-gray-600 hover:text-black text-xl font-bold">
                                ✕
                            </button>

                            <!-- Scrollable Table Container -->
                            <div class="overflow-y-auto max-h-[65vh] border border-gray-300 rounded">
                                <table class="w-full text-sm">
                                    <thead class="bg-gray-200 text-gray-700 font-semibold sticky top-0 z-10">
                                        <tr>
                                            <th class="p-2 border">Select</th>
                                            <th class="p-2 border">PGL No.</th>
                                            <th class="p-2 border">Released Date</th>
                                            <th class="p-2 border">Time</th>
                                            <th class="p-2 border">Remitted Date</th>
                                            <th class="p-2 border">Payer Name</th>
                                            <th class="p-2 border">Particulars</th>
                                            <th class="p-2 border">Amount</th>
                                            <th class="p-2 border">Payment Type</th>
                                            <th class="p-2 border">Check Number</th>
                                        </tr>
                                    </thead>
                                    <tbody id="dailyReportTableBody"></tbody>
                                </table>
                            </div>

                            <!-- BUTTON SECTION -->
                            <div class="mt-5 flex justify-end">
                                <button
                                    onclick="finalizeDailyReport()"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg shadow font-medium">
                                    Generate Daily Report
                                </button>
                            </div>

                        </div>
                    </div>


                    <!-- Reports Section -->
                    <section
                        id="reports-section"
                        class="mt-1 px-6 flex flex-col items-center">
                        <div
                            class="bg-white shadow-xl rounded-2xl p-8 w-full max-w-8xl transition-all duration-300 hover:shadow-2xl h-[80vh] max-h-screen overflow-y-auto">
                            <h2 class="text-lg sm:text-xl md:text-2xl font-bold text-gray-900 mb-6 text-center sm:text-left">
                                Reports
                            </h2>

                            <!-- Tabs -->
                            <div class="mb-6 border-b border-gray-200">
                                <ul
                                    class="flex flex-nowrap space-x-2 sm:space-x-4 -mb-px min-w-max"
                                    id="reports-tabs"
                                    role="tablist">
                                    <li role="presentation">
                                        <button
                                            class="inline-block p-3 sm:p-4 border-b-2 rounded-t-lg text-sm sm:text-base"
                                            id="view-reports-tab"
                                            data-tabs-target="#view-reports"
                                            type="button"
                                            role="tab"
                                            aria-controls="view-reports"
                                            aria-selected="true">
                                            View Reports
                                        </button>
                                    </li>
                                    <li role="presentation">
                                        <button
                                            class="inline-block p-3 sm:p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 text-sm sm:text-base"
                                            id="submit-report-tab"
                                            data-tabs-target="#submit-report"
                                            type="button"
                                            role="tab"
                                            aria-controls="submit-report"
                                            aria-selected="false">
                                            Submit Report
                                        </button>
                                    </li>
                                </ul>
                            </div>

                            <!-- View Reports Tab -->
                            <div
                                class="hidden p-3 sm:p-4 rounded-lg bg-gray-50 flex-shrink-0"
                                id="view-reports"
                                role="tabpanel"
                                aria-labelledby="view-reports-tab">

                                <!-- Search and Filter -->
                                <div
                                    class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-6 items-end">
                                    <div>
                                        <label for="report-type-filter" class="block text-sm font-medium text-gray-700">
                                            Filter by Type:
                                        </label>
                                        <select
                                            id="report-type-filter"
                                            class="w-full p-2 sm:p-3 border border-gray-300 rounded-md bg-white text-gray-900">
                                            <option value="">All</option>
                                            <option value="daily">Daily</option>
                                            <option value="monthly">Monthly</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="report-date-from" class="block text-sm font-medium text-gray-700">
                                            From Date:
                                        </label>
                                        <input
                                            type="text"
                                            id="report-date-from"
                                            class="w-full p-2 sm:p-3 border border-gray-300 rounded-md bg-white text-gray-900"
                                            placeholder="MM/DD/YYYY">
                                    </div>

                                    <div>
                                        <label for="report-date-to" class="block text-sm font-medium text-gray-700">
                                            To Date:
                                        </label>
                                        <input
                                            type="text"
                                            id="report-date-to"
                                            class="w-full p-2 sm:p-3 border border-gray-300 rounded-md bg-white text-gray-900"
                                            placeholder="MM/DD/YYYY">
                                    </div>

                                    <div class="flex md:justify-end">
                                        <button
                                            id="resetFilters"
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
                                                <th class="p-3 border border-gray-400 font-medium">Report Date</th>
                                                <th class="p-3 border border-gray-400 font-medium">Submitted On</th>
                                                <th class="p-3 border border-gray-400 font-medium">Description</th>
                                                <th class="p-3 border border-gray-400 font-medium">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="reports-table-body" class="bg-white divide-y divide-gray-200"></tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <div id="reportsPagination" class="mt-4 text-center"></div>
                            </div>

                            <!-- Preview Modal -->
                            <div id="preview-modal" class="fixed inset-0 z-50 hidden overflow-y-auto">
                                <div class="flex items-center justify-center min-h-screen px-4 text-center">
                                    <!-- Overlay -->
                                    <div class="fixed inset-0 bg-black bg-opacity-50"></div>

                                    <!-- Modal -->
                                    <div
                                        class="inline-block bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all max-w-5xl w-full max-h-[90vh]">
                                        <div class="px-6 pt-5 pb-4">
                                            <h3 class="text-lg font-medium text-gray-900 mb-4">Report Preview</h3>
                                            <div id="preview-content" class="h-[60vh] overflow-auto">
                                                <!-- Preview content -->
                                            </div>
                                        </div>
                                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse">
                                            <button
                                                id="download-report-btn"
                                                type="button"
                                                class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-md shadow-sm">
                                                Download Report
                                            </button>
                                            <button
                                                id="close-preview-btn"
                                                type="button"
                                                class="mt-3 sm:mt-0 w-full sm:w-auto bg-white border border-gray-300 text-gray-700 font-medium px-4 py-2 rounded-md hover:bg-gray-100 shadow-sm">
                                                Close
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Report Tab -->
                            <div
                                class="hidden p-3 sm:p-4 rounded-lg bg-gray-50"
                                id="submit-report"
                                role="tabpanel"
                                aria-labelledby="submit-report-tab">

                                <div class="flex flex-col h-[50vh]">
                                    <form
                                        id="report-upload-form"
                                        action="submit_report.php"
                                        method="POST"
                                        enctype="multipart/form-data"
                                        class="flex flex-col h-full">
                                        <div class="overflow-y-auto overflow-x-hidden pr-2 flex-grow space-y-4">

                                            <!-- Position -->
                                            <div>
                                                <label for="report-position" class="block text-sm font-medium text-gray-700 mb-1">Position:</label>
                                                <select
                                                    id="report-position"
                                                    name="position"
                                                    required
                                                    class="w-full p-3 border border-gray-300 rounded-md bg-white text-gray-900">
                                                    <option value="Collector" selected>Collector</option>
                                                </select>
                                            </div>

                                            <!-- Report Type -->
                                            <div>
                                                <label for="report-type" class="block text-sm font-medium text-gray-700 mb-1">Report Type:</label>
                                                <select
                                                    id="report-type"
                                                    name="report_type"
                                                    required
                                                    class="w-full p-3 border border-gray-300 rounded-md bg-white text-gray-900">
                                                    <option value="">Select Report Type</option>
                                                    <option value="Daily">Daily</option>
                                                    <option value="Monthly">Monthly</option>
                                                </select>
                                            </div>

                                            <!-- Report Date -->
                                            <div>
                                                <label for="report-date" class="block text-sm font-medium text-gray-700 mb-1">Report Date:</label>
                                                <input
                                                    type="text"
                                                    id="report-date"
                                                    name="report_date"
                                                    required
                                                    class="w-full p-3 border border-gray-300 rounded-md bg-white text-gray-900"
                                                    placeholder="MM/DD/YYYY">
                                            </div>

                                            <!-- Description -->
                                            <div>
                                                <label for="report_description" class="block text-sm font-medium text-gray-700 mb-1">Description:</label>
                                                <textarea
                                                    id="report_description"
                                                    name="description"
                                                    rows="3"
                                                    class="w-full p-3 border border-gray-300 rounded-md bg-white text-gray-900"></textarea>
                                            </div>

                                            <!-- Upload -->
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Upload Report:</label>
                                                <div
                                                    id="drop-area"
                                                    class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-blue-500 transition-colors">
                                                    <div id="drop-content">
                                                        <svg
                                                            class="mx-auto h-10 w-10 text-gray-400"
                                                            fill="none"
                                                            viewBox="0 0 24 24"
                                                            stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                                        </svg>
                                                        <p class="mt-2 text-sm text-gray-600">Drag & drop your report file here</p>
                                                        <p class="mt-1 text-xs text-gray-500">or click to browse</p>
                                                        <input
                                                            type="file"
                                                            id="file-input"
                                                            name="report_file"
                                                            accept=".xlsx"
                                                            class="hidden"
                                                            required>
                                                    </div>
                                                    <div id="file-info" class="hidden mt-4">
                                                        <p id="file-name" class="text-sm font-medium text-gray-900"></p>
                                                        <p id="file-size" class="text-xs text-gray-500"></p>
                                                        <button
                                                            type="button"
                                                            id="remove-file"
                                                            class="mt-2 text-sm text-red-600 hover:text-red-800">
                                                            Remove
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Submit Button -->
                                        <div class="mt-4 pt-4">
                                            <button
                                                type="submit"
                                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-md transition">
                                                Submit Report
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </section>










                    <!--settings-->
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
                                    <button id="updateCredentialsBtn"
                                        class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">
                                        Update Credentials
                                    </button>
                                </div>

                                <!-- ================= SESSION TIMEOUT SETTINGS ================= -->
                                <hr class="my-6">
                                <h2 class="text-xl font-bold mt-8 mb-4 text-gray-900">Session Timeout Settings</h2>
                                <div class="grid grid-cols-1 md:grid-cols-1 gap-4 mb-4">
                                   

                                    <!-- Logout Time -->
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
                                        <button id="saveOrgBtn"
                                            class="bg-blue-500 text-white py-2 px-6 rounded-xl hover:bg-blue-600">Save</button>
                                    </div>

                                    <hr class="my-6">

                                    <!-- ================= BACKUP & RESTORE ================= -->
                                    <h2 class="text-xl font-bold mt-8 mb-4 text-gray-900">Backup & Restore</h2>
                                    <div class="flex flex-col md:flex-row gap-4">
                                        <button id="backupBtn"
                                            class="bg-green-600 text-white py-2 px-6 rounded hover:bg-green-700">Backup Data</button>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="file" id="restoreFile" class="hidden" accept=".sql,.zip,.json">
                                            <span
                                                class="bg-yellow-600 text-white py-2 px-6 rounded hover:bg-yellow-700 cursor-pointer">Restore Data</span>
                                        </label>
                                    </div>
                                    <p class="text-sm text-gray-500 mt-2">Backup will generate a copy of the database. Restore will overwrite current data with selected file.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </section>

                </main>
            </div>
        </div>

       


        <!-- Toast Container -->
        <div id="toast-container" class="fixed top-5 right-5 flex flex-col space-y-2 z-50"></div>

        <!-- Logout Confirmation Modal -->
        <div id="logout-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
            <div class="bg-white  rounded-lg shadow-lg p-6 w-80">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Confirm Logout</h2>
                <p class="text-sm text-gray-600  mb-6">Are you sure you want to log out?</p>
                <div class="flex justify-end space-x-3">
                    <button id="cancel-logout" class="px-4 py-2 text-sm bg-gray-300  text-gray-800  rounded hover:bg-gray-400 ">Cancel</button>
                    <button id="confirm-logout" class="px-4 py-2 text-sm bg-red-600 text-white rounded hover:bg-red-700">Logout</button>
                </div>
            </div>
        </div>



        <script src="screen_lockout.js"></script>
        <script src="toast_action/toast.js"></script>
        <script src="audit_actions/audit_logs.js"></script>
        <script src="settings_action/settings_function.js"></script>
        <script src="collector_actions/dashboard_analytics.js"></script>
        <script src="collector_actions/payment_type_chart.js"></script>
        <script src="scripts/collector_bottom.js"></script>
        <script src="collector_actions/monthly_report.js" defer></script>
        <script src="collector_actions/edit_transactions.js"></script>


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
            document.getElementById('batches-link').addEventListener('click', () => showSection('batches'));
            document.getElementById('payments-link').addEventListener('click', () => showSection('payments'));
            document.getElementById('transactions-link').addEventListener('click', () => showSection('transactions'));
            document.getElementById('settings-link').addEventListener('click', () => showSection('settings'));
            document.getElementById('reports-link').addEventListener('click', () => showSection('reports'));
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
            function switchTab(tabId) {
                document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
                document.getElementById(tabId).classList.remove('hidden');

                document.querySelectorAll('.tab-btn').forEach(btn => {
                    btn.classList.remove('border-blue-500', 'text-blue-500');
                    btn.classList.add('border-transparent', 'text-gray-500');
                });

                event.target.classList.remove('border-transparent', 'text-gray-500');
                event.target.classList.add('border-blue-500', 'text-blue-500');
            }

            function scrollToTransactionsAndOpenMonthModal() {
                const transactionsSection = document.getElementById('transactions-link');
                if (transactionsSection) {
                    // Show the transactions section
                    showSection('transactions');

                    // Scroll smoothly to it
                    transactionsSection.scrollIntoView({
                        behavior: 'smooth'
                    });

                    // Wait a tiny bit to ensure scrolling/rendering is done, then open modal
                    setTimeout(() => {
                        openMonthModal();
                    }, 300); // 300ms delay should be enough
                }
            }

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
            function scrollToTransactionsAndGenerateDaily() {
                // Scroll smoothly to the transactions section


                // Trigger the daily report generation
                generateDailyReport();
            }
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
            let allParticulars = [];

            function fetchParticulars() {
                fetch('collector_actions/get_particulars.php')
                    .then(res => res.json())
                    .then(data => {
                        allParticulars = data;
                        displayParticulars(data);
                    });
            }

            function displayParticulars(data) {
                const list = document.getElementById('particularsList');
                list.innerHTML = '';

                // Sort so selected items appear first
                const sortedData = [...data].sort((a, b) => {
                    const aSelected = selectedItems.some(sel => sel.label === a.particulars || sel.name === a.particulars);
                    const bSelected = selectedItems.some(sel => sel.label === b.particulars || sel.name === b.particulars);

                    if (aSelected && !bSelected) return -1; // a first
                    if (!aSelected && bSelected) return 1; // b first
                    return 0; // keep original order for the rest
                });

                sortedData.forEach(item => {
                    const isAlreadySelected = selectedItems.some(sel => sel.label === item.particulars || sel.name === item.particulars);

                    const li = document.createElement('li');
                    li.innerHTML = `
            <label>
                <input 
                    type="checkbox" 
                    value="${item.amount}" 
                    data-label="${item.particulars}" 
                    data-department="${item.department}" 
                    data-account-code="${item.account_code}"
                    ${isAlreadySelected ? 'checked' : ''}>
                <span>${item.particulars} (${item.department}, ${item.account_code}) - ₱${item.amount}</span>
            </label>
            <hr style = "width: 95%; margin-top: 8px;">
        `;

                    const checkbox = li.querySelector('input[type="checkbox"]');
                    checkbox.addEventListener('change', (e) => {
                        const label = e.target.dataset.label;
                        const amount = parseFloat(e.target.value);
                        const isTraffic = label.toLowerCase().includes("traffic violation fee");

                        if (e.target.checked) {
                            // Avoid duplicates
                            if (!selectedItems.some(i => i.label === label || i.name === label)) {
                                if (isTraffic) {
                                    showViolationInput(label, amount);
                                } else if (amount === 0) {
                                    selectedItems.push({
                                        name: label,
                                        label: label,
                                        amount: 0,
                                        quantity: 1,
                                        customAmount: 0
                                    });
                                } else {
                                    selectedItems.push({
                                        name: label,
                                        label: label,
                                        amount,
                                        quantity: 1
                                    });
                                }
                            }
                        } else {
                            // Remove from selected if unchecked
                            selectedItems = selectedItems.filter(i => i.label !== label && i.name !== label);
                        }

                        // Re-render so selected items move to top
                        displayParticulars(data);
                        updateSelectedDisplay();
                        updateTotal();
                    });

                    list.appendChild(li);
                });

                updateTotal();
            }





            function filterParticulars() {
                const text = (document.getElementById('searchParticulars').value || "").toLowerCase();
                const filterParticular = (document.getElementById('filterParticular').value || "").toLowerCase();
                const filterDepartment = (document.getElementById('filterDepartment').value || "").toLowerCase();
                const filterAccountCode = (document.getElementById('filterAccountCode').value || "").toLowerCase();

                const filtered = allParticulars.filter(item => {
                    const particular = (item.particulars || "").toLowerCase();
                    const department = (item.department || "").toLowerCase();
                    const accountCode = String(item.account_code || "");

                    return (
                        (particular.includes(text) || department.includes(text) || accountCode.includes(text)) &&
                        particular.includes(filterParticular) &&
                        department.includes(filterDepartment) &&
                        accountCode.includes(filterAccountCode)
                    );
                });

                displayParticulars(filtered);
            }





            document.getElementById('searchParticulars').addEventListener('input', filterParticulars);
            document.getElementById('filterParticular').addEventListener('input', filterParticulars);
            document.getElementById('filterDepartment').addEventListener('input', filterParticulars);
            document.getElementById('filterAccountCode').addEventListener('input', filterParticulars);

            window.addEventListener('DOMContentLoaded', fetchParticulars);

            let selectedItems = [];

            function openParticularsModal() {
                document.getElementById("particularsModal").classList.remove("hidden");
            }

            function closeModal() {
                document.getElementById("particularsModal").classList.add("hidden");
            }

            function confirmParticulars() {
                const checkboxes = document.querySelectorAll('#particularsList input[type="checkbox"]');
                const violationContainer = document.getElementById('violationContainer');

                selectedItems = []; // Reset selected items

                checkboxes.forEach(checkbox => {
                    const label = checkbox.dataset.label;
                    const value = parseFloat(checkbox.value);
                    const isTraffic = label.toLowerCase().includes('traffic violation fee');

                    const violationInputId = `violationInput-${label}`;

                    if (checkbox.checked) {
                        if (checkbox.checked) {
                            if (isTraffic) {
                                showViolationInput(label, value);
                                selectedItems.push({
                                    name: label,
                                    label: label,
                                    amount: value,
                                    isTraffic: true
                                });
                            } else if (value === 0) {
                                selectedItems.push({
                                    name: label,
                                    label: label,
                                    amount: 0,
                                    quantity: 1,
                                    customAmount: 0
                                });
                            } else {
                                selectedItems.push({
                                    name: label,
                                    label: label,
                                    amount: value,
                                    quantity: 1
                                });
                            }
                        }

                    } else {
                        // If unchecked and it's traffic, remove the input field if it exists

                        const existingInput = document.getElementById(violationInputId);
                        if (existingInput) {
                            existingInput.remove();
                        }
                    }

                    selectedItems.push({
                        name: label,
                        label: label,
                        amount,
                        quantity: 1
                    });
                });

                updateSelectedDisplay();
                closeModal();
            }




            function showViolationInput(label, amount) {
                const violationContainer = document.getElementById('violationContainer');

                // Add the traffic violation item to selectedItems right away (if not already present)
                const existingItemIndex = selectedItems.findIndex(item => item.label === label);
                if (existingItemIndex === -1) {
                    selectedItems.push({
                        name: label,
                        label: label,
                        amount: amount,
                        quantity: 1,
                        isTraffic: true
                    });
                }

                // Check if input already exists for this particular, if not, create a new one
                if (!document.getElementById(`violationInput-${label}`)) {
                    const violationInputHTML = `
            <div id="violationInput-${label}" class="bg-gray-100 p-4 rounded-lg shadow-md mb-4">
                <label for="violationCount-${label}" class="block text-sm font-semibold text-gray-800">
                    Enter number of traffic violations for ${label}:
                </label>
                <input type="number"
                       id="violationCount-${label}"
                       class="violationInput mt-2 p-2 border border-gray-300 rounded-lg w-full bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                       data-label="${label}"
                       data-amount="${amount}"
                       min="1"
                       value="1">
            </div>
        `;

                    // Append new violation input to the container
                    violationContainer.insertAdjacentHTML('beforeend', violationInputHTML);

                    // ✅ Add event listener with actual quantity parsing
                    const input = document.getElementById(`violationCount-${label}`);
                    input.addEventListener('input', (event) => {
                        const quantity = parseInt(event.target.value);
                        if (!isNaN(quantity) && quantity > 0) {
                            // Update the item in selectedItems
                            const index = selectedItems.findIndex(item => item.label === label);
                            if (index !== -1) {
                                selectedItems[index].quantity = quantity;
                            }

                            updateSelectedDisplay();
                            updateTotal();
                        }
                    });
                }

                updateSelectedDisplay();
            }

            document.getElementById('clearFormBtn').addEventListener('click', () => {
                const pglInput = document.getElementById('pgl');
                const savedPgl = pglInput.value; // ✅ store PGL number before clearing

                // 1. Clear other form fields
                document.getElementById('paymentForm').reset();

                // 2. Restore PGL number
                pglInput.value = savedPgl;

                // 3. Clear selected items array
                selectedItems = [];

                // 4. Uncheck all checkboxes in the Particulars modal
                document.querySelectorAll('#particularsList input[type="checkbox"]').forEach(cb => cb.checked = false);

                // 5. Clear displayed particulars and total
                document.getElementById('selectedParticularsDisplay').innerHTML = '';
                document.getElementById('violationContainer').innerHTML = '';
                document.getElementById('amount').value = '';

                // 6. Re-render the list to restore original order
                if (typeof displayParticulars === 'function' && typeof window.particularsData !== 'undefined') {
                    displayParticulars(window.particularsData);
                }

                // 7. Optional visual feedback
                showToastTimeout("Form has been cleared (PGL No. retained).", "success");
            });




            function updateTotal() {
                let total = 0;

                selectedItems.forEach(item => {
                    if (item.isTraffic) {
                        const input = document.getElementById(`violationCount-${item.label}`);
                        const quantity = parseInt(input?.value || "1");
                        if (!isNaN(quantity)) {
                            total += item.amount * quantity;
                        }
                    } else if (item.amount === 0) {
                        const quantity = item.quantity || 1;
                        total += (item.customAmount || 0) * quantity;
                    } else {
                        total += item.amount * (item.quantity || 1);
                    }
                });

                document.getElementById('amount').value = total.toFixed(2);
            }




            function updateSelectedDisplay() {
                const container = document.getElementById('selectedParticularsDisplay');
                const amountInput = document.getElementById('amount');
                if (!container || !amountInput) return;

                container.innerHTML = "";
                let total = 0;

                selectedItems.forEach(item => {
                    const div = document.createElement("div");
                    div.classList.add("flex", "items-start", "justify-between", "mb-4", "p-2", "border-b");

                    // Create inner container for item details
                    const innerDiv = document.createElement("div");
                    innerDiv.classList.add("flex", "flex-col", "w-full");

                    let subtotal = 0;

                    if (item.isTraffic) {
                        const input = document.getElementById(`violationCount-${item.label}`);
                        const quantity = parseInt(input?.value || "1");
                        subtotal = item.amount * quantity;
                        innerDiv.textContent = `${item.name} x ${quantity} = ₱${subtotal.toFixed(2)}`;
                        total += subtotal;

                    } else {
                        const quantity = item.quantity || 1;

                        if (item.amount === 0) {

                            // ✅ Custom price item handling
                            const customAmount = item.customAmount || 0;
                            const customDescription = item.customDescription || "";
                            subtotal = customAmount;
                            total += subtotal;

                            if (item.name.toLowerCase() === "others") {
                                innerDiv.innerHTML = `
                        <div class="flex flex-col space-y-2">
                            <div class="flex justify-between items-center">
                                <span>${item.name}</span>
                                <div class="flex items-center space-x-2 w-96">
                                    <span>Nature of Payment:</span>
                                    <input type="text" value="${customDescription}" 
                                        data-label="${item.label}" 
                                        class="flex-1 border rounded-md px-2 py-1 custom-desc-input" 
                                        placeholder="Specify nature of payment" required/>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center w-full mt-2">
                            <div class="flex items-center space-x-2 ml-auto">
                                <span>₱</span>
                                <input type="number" min="0" step="0.01" value="${customAmount}" 
                                    data-label="${item.label}" 
                                    class="w-24 text-right border rounded-md px-2 py-1 custom-price-input" required/>
                            </div>
                        </div>

                        <div class="text-right text-sm custom-subtotal">Subtotal: ₱${subtotal.toFixed(2)}</div>
                    `;

                                const customInput = innerDiv.querySelector('.custom-price-input');
                                const descInput = innerDiv.querySelector('.custom-desc-input');
                                const subtotalDiv = innerDiv.querySelector('.custom-subtotal');

                                customInput?.addEventListener('input', (e) => {
                                    let value = parseFloat(e.target.value);
                                    if (isNaN(value) || value < 0) value = 0;
                                    item.customAmount = value;
                                    subtotalDiv.textContent = `Subtotal: ₱${value.toFixed(2)}`;
                                    updateTotal();
                                });

                                descInput?.addEventListener('input', (e) => {
                                    item.customDescription = e.target.value;
                                });

                            } else {
                                // ✅ Regular custom-amount
                                innerDiv.innerHTML = `
                        <div class="flex justify-between items-center">
                            <span>${item.name}</span>
                            <div class="flex items-center space-x-2">
                                <span>₱</span>
                                <input type="number" min="0" step="0.01" value="${customAmount}" 
                                    data-label="${item.label}" 
                                    class="w-24 text-right border rounded-md px-2 py-1 custom-price-input" required/>
                            </div>
                        </div>
                        <div class="text-right text-sm custom-subtotal">Subtotal: ₱${subtotal.toFixed(2)}</div>
                    `;

                                const customInput = innerDiv.querySelector('.custom-price-input');
                                const subtotalDiv = innerDiv.querySelector('.custom-subtotal');

                                customInput?.addEventListener('input', (e) => {
                                    let value = parseFloat(e.target.value);
                                    if (isNaN(value) || value < 0) value = 0;
                                    item.customAmount = value;
                                    subtotalDiv.textContent = `Subtotal: ₱${value.toFixed(2)}`;
                                    updateTotal();
                                });
                            }

                        } else {
                            // ✅ Regular fixed-amount item with quantity
                            subtotal = item.amount * quantity;
                            total += subtotal;

                            innerDiv.innerHTML = `
                    <div class="flex justify-between items-center">
                        <span>${item.name} - ₱${item.amount.toFixed(2)}</span>
                        <div class="flex items-center space-x-2">
                            <button class="bg-gray-200 px-2 rounded" data-action="decrease" data-label="${item.label}">−</button>
                            <input type="number" min="1" value="${quantity}" data-label="${item.label}" class="w-12 text-center border rounded-md px-1 py-0.5" />
                            <button class="bg-gray-200 px-2 rounded" data-action="increase" data-label="${item.label}">+</button>
                        </div>
                    </div>
                    <div class="text-right text-sm">Subtotal: ₱${subtotal.toFixed(2)}</div>
                `;

                            const minusBtn = innerDiv.querySelector('[data-action="decrease"]');
                            const plusBtn = innerDiv.querySelector('[data-action="increase"]');
                            const quantityInput = innerDiv.querySelector('input[type="number"]');

                            minusBtn?.addEventListener('click', () => updateQuantity(item.label, -1));
                            plusBtn?.addEventListener('click', () => updateQuantity(item.label, 1));
                            quantityInput?.addEventListener('input', (e) => {
                                const value = parseInt(e.target.value);
                                if (!isNaN(value) && value > 0) setQuantity(item.label, value);
                            });
                        }
                    }

                    // ✅ Add Notes Field (NEW BLOCK)
                    // ✅ Notes Field Side-by-Side with Label


                    const notesContainer = document.createElement("div");
                    notesContainer.classList.add("w-60%", "flex", "items-start", "gap-2", "ml-3");

                    // Label container so text can break without breaking layout
                    const labelWrap = document.createElement("div");
                    labelWrap.classList.add("flex", "flex-col", "whitespace-nowrap");

                    const notesLabel = document.createElement("span");
                    notesLabel.classList.add("text-sm", "text-gray-600");
                    notesLabel.innerHTML = "Add notes<br>(optional):";

                    labelWrap.appendChild(notesLabel);

                    // Textarea
                    const notesInput = document.createElement("textarea");
                    notesInput.classList.add(
                        "flex-1",
                        "border",
                        "rounded-md",
                        "p-2",
                        "text-sm",
                        "resize-auto"
                    );
                    notesInput.rows = 2;
                    notesInput.placeholder = "Add notes here...";
                    notesInput.value = item.note || "";

                    notesInput.addEventListener("input", (e) => {
                        item.note = e.target.value;
                    });

                    notesContainer.appendChild(notesLabel);
                    notesContainer.appendChild(notesInput);

                    div.appendChild(innerDiv);
                    div.appendChild(notesContainer);

                    // ✅ Delete button
                    const deleteBtn = document.createElement("button");
                    deleteBtn.classList.add("mt-4");
                    deleteBtn.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 hover:text-red-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4m-4 0a1 1 0 00-1 1v1h6V4a1 1 0 00-1-1m-4 0h4" />
            </svg>
        `;
                    deleteBtn.classList.add("ml-4");
                    deleteBtn.addEventListener("click", () => {
                        selectedItems = selectedItems.filter(i => i.label !== item.label);
                        const checkbox = document.querySelector(`#particularsList input[data-label="${item.label}"]`);
                        if (checkbox) checkbox.checked = false;
                        updateSelectedDisplay();
                        updateTotal();
                    });

                    div.appendChild(deleteBtn);
                    container.appendChild(div);
                });

                amountInput.value = total.toFixed(2);
            }





            function updateQuantity(label, delta) {
                const index = selectedItems.findIndex(item => item.label === label && !item.isTraffic);
                if (index !== -1) {
                    selectedItems[index].quantity = Math.max(1, (selectedItems[index].quantity || 1) + delta);
                    updateSelectedDisplay();
                    updateTotal();
                }
            }

            function setQuantity(label, quantity) {
                const index = selectedItems.findIndex(item => item.label === label && !item.isTraffic);
                if (index !== -1 && quantity > 0) {
                    selectedItems[index].quantity = quantity;
                    updateSelectedDisplay();
                }
            }

            document.getElementById("paymentType").addEventListener("change", function() {
                const checkContainer = document.getElementById("checkNumberContainer");
                if (this.value === "Check") {
                    checkContainer.classList.remove("hidden");
                } else {
                    checkContainer.classList.add("hidden");
                }
            });
        </script>

        <script>
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


            // Show last active section or default
            document.addEventListener("DOMContentLoaded", function() {
                const activeSection = localStorage.getItem('activeSection') || 'dashboard';
                showSection(activeSection);
            });



            window.addEventListener("DOMContentLoaded", async () => {
                const pglInput = document.getElementById("pgl");

                try {
                    const res = await fetch("receipt_batches_actions/get_next_pgl.php");
                    const data = await res.json();

                    if (data.status === "success") {
                        pglInput.value = data.pgl_no;
                        pglInput.dataset.batchId = data.batch_id; // Set this early!
                    } else {
                        showToast("No available PGL No. You may be out of serials.", "error");
                        pglInput.disabled = true;
                    }
                } catch (error) {
                    console.error("Failed to fetch initial PGL No.:", error);
                    showToast("Failed to fetch initial PGL No.", "error");
                    pglInput.disabled = true;
                }
            });



            async function processPayment(event) {
                event.preventDefault();

                const payerName = document.getElementById("payerName").value;
                const amount = parseFloat(document.getElementById("amount").value);
                const paymentType = document.getElementById("paymentType").value;
                const pglInput = document.getElementById("pgl");
                const pglNo = pglInput.value;
                const batchId = pglInput.dataset.batchId;
                const checkNumber = document.getElementById("checkNumber").value.trim();

                if (!batchId) {
                    showToast("Missing batch ID. Please wait until the PGL No. is ready.", "error");
                    return;
                }

                if (paymentType === "Check" && checkNumber === "") {
                    showToast("Please enter the check number.", "error");
                    return;
                }

                // ✅ Validate custom amounts
                let invalidCustoms = [];
                for (const item of selectedItems) {
                    if (item.amount === 0) {
                        if (!item.customAmount || item.customAmount <= 0) {
                            invalidCustoms.push(item.name || item.label || "Unnamed");
                        }
                    }
                }
                if (invalidCustoms.length > 0) {
                    invalidCustoms.forEach(name => {
                        showToast(`Please enter a valid custom amount for "${name}".`, "error");
                    });
                    return;
                }

                // ✅ Validate "Others" description
                let missingDescriptions = [];
                for (const item of selectedItems) {
                    if ((item.name === "Others" || item.label === "Others") && (!item.customDescription || item.customDescription.trim() === "")) {
                        missingDescriptions.push(item.name || item.label);
                    }
                }
                if (missingDescriptions.length > 0) {
                    missingDescriptions.forEach(name => {
                        showToast(`Please provide a nature of payment for "${name}".`, "error");
                    });
                    return;
                }

                // ✅ Build particulars preview with NOTES
                let particularsList = selectedItems.map(item => {
                    const name = item.name || item.label || "Unnamed";
                    const qty = item.quantity || 1;
                    const baseAmount = item.amount === 0 ? (item.customAmount || 0) : item.amount;
                    const total = baseAmount * qty;

                    // ✅ If "Others", append custom description
                    const displayName = (name === "Others" && item.customDescription) ?
                        `Others (${item.customDescription})` :
                        name;

                    const notes = item.note && item.note.trim() !== "" ? item.note.trim() : "";

                    return `
            <li class="mb-2">
                <div><strong>${displayName}</strong> x ${qty} — ₱${total.toFixed(2)}</div>
                ${notes !== "" ? `<div class="text-gray-600 text-sm ml-5">Note: ${notes}</div>` : ""}
            </li>
        `;
                }).join("");

                const transactionDetails = `
        <p><strong>PGL No.:</strong> ${pglNo}</p>
        <p><strong>Payer Name:</strong> ${payerName}</p>
        <p><strong>Payment Type:</strong> ${paymentType}</p>
        ${paymentType === "Check" ? `<p><strong>Check Number:</strong> ${checkNumber}</p>` : ""}
        <p><strong>Total Amount:</strong> ₱${amount.toFixed(2)}</p>
        <p><strong>Particulars:</strong></p>
        <ul class="list-disc ml-5">${particularsList}</ul>
    `;

                document.getElementById("confirmTransactionDetails").innerHTML = transactionDetails;
                document.getElementById("paymentConfirmModal").classList.remove("hidden");

                // Store data temporarily for confirm step
                window.pendingPayment = {
                    payerName,
                    amount,
                    paymentType,
                    pglNo,
                    batchId,
                    checkNumber,
                    particulars: selectedItems
                };
            }




            async function confirmPayment() {
                const {
                    payerName,
                    amount,
                    paymentType,
                    pglNo,
                    batchId,
                    checkNumber,
                    particulars
                } = window.pendingPayment;

                try {
                    // ✅ Fix "Others" in main transaction particulars summary
                    const formattedParticulars = particulars.map(item => {
                        const name = item.name || item.label || "Unnamed";
                        const qty = item.quantity || 1;
                        let particularName = name;

                        if (name === "Others" && item.customDescription) {
                            particularName = `Others - ${item.customDescription}`;
                        }

                        return `${particularName} x ${qty}`;
                    }).join(", ");

                    const response = await fetch("collector_actions/save_transactions.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            payorname: payerName,
                            particulars: formattedParticulars,
                            amount,
                            payment_type: paymentType,
                            pgl_no: pglNo,
                            check_number: paymentType === "Check" ? checkNumber : null
                        })
                    });

                    const result = await response.json();
                    console.log("Save transaction response:", result); // 👈 debug

                    if (result.success) {
                        const transactionId = result.transaction_id;

                        // ✅ Save each particular individually
                        for (const item of particulars) {
                            try {
                                const name = item.name || item.label || "Unnamed";
                                const qty = item.quantity || 1;
                                let total = (item.amount === 0 ? (item.customAmount || 0) : item.amount) * qty;

                                // ✅ include categorization from reference
                                const categorization = item.categorization || "Uncategorized";

                                // ✅ If "Others", append custom description
                                let particularName = name;
                                if (name === "Others" && item.customDescription) {
                                    particularName = `Others - ${item.customDescription}`;
                                }

                                await fetch("collector_actions/save_transaction_item.php", {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/json"
                                    },
                                    body: JSON.stringify({
                                        pgl_no: pglNo,
                                        particular: particularName,
                                        quantity: qty,
                                        amount: total.toFixed(2),
                                        categorization,
                                        notes: item.note || "" // ✅ ADD THIS
                                    })
                                });

                            } catch (e) {
                                console.error("Failed to save item:", e);
                            }
                        }

                        // ✅ Only call if we have a valid ID
                        if (transactionId) {
                            viewTransaction(transactionId);
                        } else {
                            console.warn("⚠️ No transaction_id returned, skipping viewTransaction.");
                        }

                        showSection("transactions");

                        await fetch(`receipt_batches_actions/decrement_quantity.php?id=${batchId}`, {
                            method: "POST"
                        });

                        showToast("Transaction saved successfully!", "success");

                        // Reset form
                        document.getElementById("paymentForm").reset();
                        selectedItems = [];
                        updateSelectedDisplay();
                        document.getElementById("violationContainer").innerHTML = "";
                        document.getElementById("checkNumberContainer").classList.add("hidden");
                        document.querySelectorAll('#particularsList input[type="checkbox"]').forEach(cb => cb.checked = false);

                        // Fetch new PGL No.
                        const pglRes = await fetch("receipt_batches_actions/get_next_pgl.php");
                        const pglData = await pglRes.json();
                        if (pglData.status === "success") {
                            const pglInput = document.getElementById("pgl");
                            pglInput.value = pglData.pgl_no;
                            pglInput.dataset.batchId = pglData.batch_id;
                        } else {
                            showToast("No more available serial numbers.", "error");
                        }

                    } else {
                        showToast("Error: " + result.message, "error");
                    }
                } catch (error) {
                    console.error("Payment error:", error);
                    showToast("An unexpected error occurred. Please try again.", "error");
                }

                closeConfirmModal();
            }


            function closeConfirmModal() {
                document.getElementById("paymentConfirmModal").classList.add("hidden");
                window.pendingPayment = null;
            }

            // Function to handle the viewTransaction click
            function viewTransaction(transactionId) {
                // Use fetch to get the transaction details from your server

                fetch(`collector_actions/get_transactions.php?id=${transactionId}`)
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

            function numberToWords(number) {
                if (number === 0) return "Zero Pesos Only";

                if (number < 0) return "Minus " + numberToWords(Math.abs(number));

                const units = ["", "One", "Two", "Three", "Four", "Five", "Six", "Seven", "Eight", "Nine", "Ten", "Eleven", "Twelve", "Thirteen", "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eighteen", "Nineteen"];
                const tens = ["", "", "Twenty", "Thirty", "Forty", "Fifty", "Sixty", "Seventy", "Eighty", "Ninety"];

                function convert(num) {
                    if (num < 20) return units[num];
                    if (num < 100) {
                        const ten = Math.floor(num / 10);
                        const unit = num % 10;
                        return tens[ten] + (unit ? " " + units[unit] : "");
                    }
                    if (num < 1000) {
                        const hundred = Math.floor(num / 100);
                        const remainder = num % 100;
                        return units[hundred] + " Hundred" + (remainder ? " " + convert(remainder) : "");
                    }
                    if (num < 1000000) {
                        const thousand = Math.floor(num / 1000);
                        const remainder = num % 1000;
                        return convert(thousand) + " Thousand" + (remainder ? " " + convert(remainder) : "");
                    }
                    if (num < 1000000000) {
                        const million = Math.floor(num / 1000000);
                        const remainder = num % 1000000;
                        return convert(million) + " Million" + (remainder ? " " + convert(remainder) : "");
                    }
                    const billion = Math.floor(num / 1000000000);
                    const remainder = num % 1000000000;
                    return convert(billion) + " Billion" + (remainder ? " " + convert(remainder) : "");
                }

                // Split pesos and centavos
                const pesos = Math.floor(number);
                const centavos = Math.round((number - pesos) * 100);

                let words = "";
                if (pesos > 0) {
                    words += convert(pesos) + " Pesos";
                }

                if (centavos > 0) {
                    words += " & " + centavos + "/100 Pesos Only";
                } else {
                    words += " Only";
                }

                return words.trim();
            }


            function formatCurrency(amount) {
                return amount.toLocaleString('en-US', {
                    style: 'currency',
                    currency: 'PHP', // Philippine Peso
                });
            }

            async function populateReceipt(transaction) {
                try {
                    // Fetch transaction items (with particulars and amounts)
                    const res = await fetch(`collector_actions/get_transaction_items.php?pgl_no=${transaction.pgl_no}`);
                    const itemsData = await res.json();

                    let items = [];
                    if (itemsData.status === 'success' && itemsData.items.length > 0) {
                        items = itemsData.items; // [{ particular, amount }, ...]
                    } else {
                        // Fallback if API has no itemized data: use the particulars text only
                        items = transaction.particulars.split(',').map(p => ({
                            particular: p.trim(),
                            amount: ''
                        }));
                    }

                    const totalAmount = parseFloat(transaction.amount);
                    const amountInWords = numberToWords(totalAmount);
                    const formattedAmount = formatCurrency(totalAmount);


                    // Build rows with item subtotals
                    const particularsRows = items.map(item => {
                        let displayParticular = item.particular;

                        // ✅ If "Others", append description
                        if (item.particular === "Others" && item.customDescription) {
                            displayParticular = `Others - ${item.customDescription}`;
                        }

                        return `
        <tr>
            <td>${displayParticular}</td>
            <td></td>
            <td class="amount-field">${item.amount ? parseFloat(item.amount).toFixed(2) : ''}</td>
        </tr>
    `;
                    }).join('');


                    // Add empty rows up to 10 lines for consistent layout
                    const emptyRows = Array(10 - items.length).fill(null).map(() => `
            <tr>
                <td></td>
                <td></td>
                <td class="amount-field"></td>
            </tr>
        `).join('');

                    // Full receipt HTML
                    const receiptTemplate = `
<!DOCTYPE html>
<html>
<head> 
  <title>Receipt Print</title>
  <style>
    @page {
      size: 4in 8.5in; /* typical small receipt roll size */
      margin: 5mm;
    }
    body {
      margin: 0;
      padding: 0;
      font-family: "Courier New", Courier, monospace; /* Elite-like font */
      font-size: 13px; /* closer to Elite (12 CPI) */
      width: 4in; /* match paper width */
      position: relative;
      background: url("receipt-bg.jpg") no-repeat center top;
  background-size: cover; /* or contain */
    }
    .receipt {
      width: 100%;
      position: relative;
      margin-top: 0.3in;
    }
    .row {
      display: flex;
      justify-content: space-between;
    }
    .datename {
      position: absolute;
      top: 2.1in;   /* replaces <br> spacing */
      left: 20px;
    }
    #payorname {
      text-transform: uppercase;
      font-weight: bold;
       /* spacing between date and payor */
      margin-left: 50px;
    }
    #agencyname {
      text-transform: uppercase;
      font-weight: bold;
      margin-top: 0.2in; /* spacing between date and payor */
      margin-left: 50px;
    }
    .particulars {
      position: absolute;
      top: 3.2in;  /* aligns items block */
      left: 45px;
      width: calc(100% - 80px);
    }
    .items .row {
      margin-bottom: 3px;
    }
    .amount-collname {
      position: absolute;
      top: 5.3in;  /* aligns totals/collector */
      left: 45px;
      width: calc(100% - 80px);
    }
    .total {
      font-weight: bold;
      text-align: right;
    }
    .amount-words {
      font-size: 11px;
      margin-top: 0.3in;/* spacing after total */
      text-align: left;
    }
    .collector {
      position: absolute;
      text-align: right;
      margin-top: 7.0in; /* spacing after words */
      left: 2.2in;
      text-transform: uppercase;
      font-weight: bold;
    }
    .collector p {
      font-weight: normal;
      margin: 0;
    }
  </style>
</head>
<body>


  <div class="receipt">
    

    <!-- Date + Payor -->
    <div class="datename">
      <div class="row">
        <div>${formatDate(transaction.date)}</div>
      </div>
      <div id="agencyname"><?php echo htmlspecialchars($agencyName); ?></div>
      <div id="payorname">${transaction.payorname}</div>
    </div>

    

    <!-- Items -->
<div class="particulars">
  <div class="items">
    ${items.map(item => {
    let displayParticular = item.particular;

    if (item.particular === "Others" && item.customDescription) {
        displayParticular = `Others - ${item.customDescription}`;
    }

   let notesHtml = "";

if (item.notes && item.notes.trim() !== "") {
    notesHtml = `
        <div style="margin-left: 5px; font-size: 11px;">
            ${item.notes}
        </div>
    `;
}

return `
    <div class="row">
        <div>${displayParticular}</div>
        <div>₱${parseFloat(item.amount).toFixed(2)}</div>
    </div>
    ${notesHtml}
`;


}).join("")}

  </div>
</div>



    <!-- Total, amount in words, collector -->
    <div class="amount-collname">
      <div class="total">${formattedAmount}</div>
      <div class="amount-words">${amountInWords}</div>
      
    </div>
    <div class="collector">
        <?php echo htmlspecialchars($treasurerName); ?>
        <p style="font-size: 12px;">Municipal Treasurer</p>
      </div>
  </div>
</body>
</html>



`;



                    const newWindow = window.open('', '_blank');
                    if (!newWindow) {
                        showToast('Please allow popups for this website to print receipts.', 'error');
                        return;
                    }
                    newWindow.document.write(receiptTemplate);
                    newWindow.document.close();
                    newWindow.focus();
                    newWindow.print();

                } catch (error) {
                    console.error("Error fetching transaction items:", error);
                    showToast("Failed to fetch receipt details.", "error");
                }
            }
        </script>

        <script>
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


                const url = `collector_actions/get_transactions.php?start_date=${start}&end_date=${end}`;

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
        </script>

        <script>
            function toggleDateRangeDropdown() {
                const dropdown = document.getElementById('dateRangeDropdown');
                dropdown.classList.toggle('hidden');
            }
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
                let url = `collector_actions/get_transactions.php?filter=${filter}`;
                if (currentlySelectedDate && filter === currentlySelectedDate) {
                    url = `collector_actions/get_transactions.php?date=${currentlySelectedDate}`;
                } else if (filter !== 'today' && !filter.startsWith('week') && !filter.startsWith('year-') && Object.values(months).includes(filter)) {
                    url = `collector_actions/get_transactions.php?filter=${filter}`;
                } else if (filter === 'today' || filter.startsWith('week') || filter.startsWith('year-')) {
                    url = `collector_actions/get_transactions.php?filter=${filter}`;
                } else if (filter === 'all') {
                    url = `collector_actions/get_transactions.php?filter=all`;
                }

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            allTransactions = data.transactions;
                            allTransactionsOriginal = [...data.transactions]; // clone for resetting
                            currentPage = 1;
                            displayTransactions(allTransactions);
                        } else {
                            showToast(data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching transactions:', error);
                        showToast('Error fetching transactions: ' + error.message, 'error');
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

            let allTransactions = []; // This will hold the currently displayed transactions
            let allTransactionsOriginal = []; // This should remain unchanged

            const datepickerStyle = datepickerContainer.style;
            datepickerStyle.top = 'auto';
            datepickerStyle.bottom = 'calc(100% + 5px)'; // Position above the button with a small gap
            datepickerStyle.left = '0';
            datepickerStyle.transform = 'translateY(100%)'; // Adjust for the initial top-full

            let currentPage = 1;
            let transactionsPerPage = calculateTransactionsPerPage();

            // Dynamically compute rows per page based on screen height
            function calculateTransactionsPerPage() {
                const rowHeight = 70; // Adjust if your rows are taller/shorter
                const availableHeight = window.innerHeight - 400;
                const count = Math.floor(availableHeight / rowHeight);
                return Math.max(count, 4); // Minimum 4 rows to avoid empty tables
            }

            // Recalculate when window is resized
            window.addEventListener("resize", () => {
                transactionsPerPage = calculateTransactionsPerPage();
                displayTransactions(allTransactions);
            });

            function displayTransactions(transactions) {
                allTransactions = transactions;
                const transactionsTable = document.getElementById('transactionsTable');
                transactionsTable.innerHTML = ''; // Clear table rows

                const totalPages = Math.ceil(transactions.length / transactionsPerPage);
                const startIndex = (currentPage - 1) * transactionsPerPage;
                const endIndex = startIndex + transactionsPerPage;
                const paginated = transactions.slice(startIndex, endIndex);

                paginated.forEach(transaction => {
                    const row = document.createElement('tr');
                    row.classList.add('bg-white-200', 'border', 'border-gray-300');

                    const formattedParticulars = transaction.particulars.split(',').map(p => p.trim()).join(', ');

                    row.innerHTML = `
            <td class="p-3 border border-gray-400 p-2">${transaction.pgl_no}</td>
            <td class="p-3 border border-gray-400 p-2">${formatDate(transaction.date)}</td>
            <td class="p-3 border border-gray-400 p-2">${formatTime(transaction.time)}</td>
            <td class="p-3 border border-gray-400 p-2">${formatDate(transaction.date_remitted)}</td>
            <td class="p-3 border border-gray-400 p-2">${transaction.payorname}</td>
            <td class="p-3 border border-gray-400 p-2">${formattedParticulars}</td>
            <td class="p-3 border border-gray-400 p-2">₱${parseFloat(transaction.amount).toFixed(2)}</td>
            <td class="p-3 border border-gray-400 p-2">${transaction.payment_type}</td>
            <td class="p-3 border border-gray-400 p-2">${transaction.check_num || '-'}</td>
            <td class="p-3 border border-gray-400 p-2">
           <center class="flex items-center justify-center gap-0.5">
  
    <!-- VIEW BUTTON -->
    <button onclick="seeDetails(${transaction.id})"
        class="text-blue-500 hover:text-blue-700 p-1 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
    </button>

    <!-- EDIT BUTTON -->
    <button onclick="editTransaction(${transaction.id}, '${transaction.payorname}', '${transaction.pgl_no}')"
        class="text-yellow-500 hover:text-yellow-700 p-1 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M11 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M18.5 2.5l3 3L12 15l-4 1 1-4L18.5 2.5z" />
        </svg>
    </button>

    <!-- CANCEL BUTTON -->
    <button onclick="cancelTransaction(${transaction.id}, '${transaction.pgl_no}')"
        class="text-red-500 hover:text-red-700 p-1 transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</center>


            </td>
        `;
                    transactionsTable.appendChild(row);
                });

                addPaginationControls(totalPages);
            }

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

            document.getElementById('transaction-search').addEventListener('input', () => {
                filterAndSearchTransactions();
            });


            function filterAndSearchTransactions() {

                const searchQuery = document.getElementById('transaction-search').value.trim().toLowerCase();

                const filtered = allTransactionsOriginal.filter(transaction =>
                    transaction.pgl_no.toLowerCase().includes(searchQuery) ||
                    transaction.payorname.toLowerCase().includes(searchQuery)
                );

                currentPage = 1;
                displayTransactions(filtered);
            }












            async function seeDetails(id) {
                console.log("Clicked:", id);

                const transaction = allTransactions.find(t => t.id == id);
                if (!transaction) {
                    console.error("Transaction not found for ID:", id);
                    return;
                }

                try {
                    // Fetch itemized transaction items including notes
                    const res = await fetch(`collector_actions/get_transaction_items.php?pgl_no=${transaction.pgl_no}`);
                    const itemsData = await res.json();

                    let particularsList = '';

                    if (itemsData.status === 'success' && itemsData.items.length > 0) {

                        particularsList = itemsData.items.map(item => {
                            const amountFormatted = item.amount ? parseFloat(item.amount).toFixed(2) : "0.00";
                            const notes = item.notes ? item.notes.trim() : "";

                            return `
                    <li class="mb-2">
                        <div><strong>${item.particular}</strong> — ₱${amountFormatted}</div>
                        ${notes !== "" ? `<div class="text-gray-600 text-sm ml-3">Note: ${notes}</div>` : ""}
                    </li>
                `;
                        }).join('');

                    } else {
                        // fallback (no itemized breakdown)
                        particularsList = transaction.particulars
                            .split(',')
                            .map(item => `<li>${item.trim()}</li>`)
                            .join('');
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
        </script>
        <script>
            function toggleDropdownReport() {
                const dropdown = document.getElementById('reportDropdown');
                const isHidden = dropdown.classList.contains('hidden');

                // Toggle visibility
                dropdown.classList.toggle('hidden');

                if (!isHidden) {
                    // If dropdown will be hidden, remove the event listener
                    document.removeEventListener('click', handleOutsideClick);
                } else {
                    // Add listener only when dropdown is shown
                    setTimeout(() => {
                        document.addEventListener('click', handleOutsideClick);
                    }, 0);
                }
            }

            function handleOutsideClick(event) {
                const dropdown = document.getElementById('reportDropdown');
                const button = document.getElementById('reportButton');

                if (!dropdown.contains(event.target) && !button.contains(event.target)) {
                    dropdown.classList.add('hidden');
                    document.removeEventListener('click', handleOutsideClick);
                }
            }


            let dailyTransactions = []; // store editable list

            async function generateDailyReport() {
                document.getElementById("dailyReportModal").classList.remove("hidden");

                try {
                    const response = await fetch("collector_actions/get_daily_transactions.php");
                    const data = await response.json();

                    const tbody = document.getElementById("dailyReportTableBody");
                    tbody.innerHTML = "";



                    // ✅ Filter by remitted date = today
                    dailyTransactions = data.transactions.map(t => ({
                        id: t.id,
                        pgl_no: t.pgl_no,
                        date: t.date,
                        time: t.time,
                        date_remitted: t.date_remitted,
                        payorname: t.payorname,
                        particulars: t.particulars,
                        amount: t.amount,
                        payment_type: t.payment_type,
                        check_num: t.check_num,
                        modified_date_remitted: t.date_remitted
                    }));

                    dailyTransactions.forEach((tx, index) => {
                        let timeString = tx.time ? formatTime(tx.time) : "-";

                        const row = `
<tr class="border border-gray-300"
    id="row-${index}"
    data-id="${tx.id}"
    data-original-date="${tx.date}">
    <td class="border p-2 text-center">
        <input type="checkbox" checked onchange="toggleTransaction(${index})">
    </td>
    <td class="border p-2">${tx.pgl_no}</td>
    <td class="border p-2">${formatDate(tx.date)}</td>
    <td class="border p-2">${timeString}</td>
    <td class="border p-2 remitted-date-cell" id="remitted-${index}">
        ${formatDate(tx.date_remitted)}
    </td>
    <td class="border p-2">${tx.payorname}</td>
    <td class="border p-2">${tx.particulars}</td>
    <td class="border p-2">₱${parseFloat(tx.amount).toFixed(2)}</td>
    <td class="border p-2">${tx.payment_type}</td>
    <td class="border p-2">${tx.check_num || '-'}</td>
</tr>
`;
                        tbody.innerHTML += row;
                    });

                } catch (err) {
                    console.error(err);
                }
            }


            function toggleTransaction(index) {
                const checkbox = document.querySelector(`#row-${index} input[type='checkbox']`);
                const remittedCell = document.getElementById(`remitted-${index}`);

                let originalDate = dailyTransactions[index].date_remitted;

                if (!checkbox.checked) {
                    // Add +1 day
                    let newDate = new Date(originalDate);
                    newDate.setDate(newDate.getDate() + 1);

                    let formatted = formatDate(newDate.toISOString().split("T")[0]);
                    remittedCell.textContent = formatted;

                    dailyTransactions[index].modified_date_remitted =
                        newDate.toISOString().split("T")[0]; // ✅ matches DB column
                } else {
                    // Revert
                    remittedCell.textContent = formatDate(originalDate);

                    dailyTransactions[index].modified_date_remitted =
                        originalDate; // ✅ existing DB format
                }
            }

            function closeDailyModal() {
                document.getElementById("dailyReportModal").classList.add("hidden");
            }

            // Utility functions
            function formatDate(dateStr) {
                if (!dateStr) return "-";
                const date = new Date(dateStr);
                return date.toLocaleDateString("en-US", {
                    year: "numeric",
                    month: "long",
                    day: "numeric"
                });
            }

            function formatTime(timeStr) {
                const date = new Date("2000-01-01 " + timeStr);
                return date.toLocaleTimeString("en-US", {
                    hour: "numeric",
                    minute: "numeric",
                    hour12: true
                }).toLowerCase();
            }






            async function finalizeDailyReport() {
                document.getElementById("dailyReportModal").classList.add("hidden");
                console.log("Finalizing Daily Report...");

                const rows = document.querySelectorAll('#dailyReportTableBody tr');
                if (rows.length === 0) {
                    showToast("No transactions to generate report for", "error");
                    return;
                }

                const updates = [];

                rows.forEach(row => {
                    const original = row.dataset.originalDate;
                    const remittedCell = row.querySelector(".remitted-date-cell");

                    if (!remittedCell) {
                        console.warn("Missing remitted-date-cell class in row:", row);
                        return;
                    }

                    const current = remittedCell.textContent.trim();

                    if (original !== current) {
                        updates.push({
                            id: row.dataset.id,
                            newRemittedDate: current
                        });
                    }
                });

                console.log("Detected changes:", updates);

                if (updates.length > 0) {
                    try {
                        const response = await fetch("collector_actions/update_remitted_dates.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json"
                            },
                            body: JSON.stringify({
                                updates
                            })
                        });

                        const result = await response.json();

                        if (result.status !== "success") {
                            showToast("Failed to update remitted dates.", "error");
                            return;
                        }
                    } catch (err) {
                        console.error(err);
                        showToast("Network error while updating remitted dates.", "error");
                        return;
                    }
                } else {
                    showToast("No remitted dates changed.", "info");
                }

                console.log("Generating Daily Report Preview...");

                const reportData = {
                    date: new Date().toLocaleDateString(),
                    transactions: [],
                    totalAmount: 0
                };

                rows.forEach(row => {
                    const cells = row.querySelectorAll("td");

                    if (cells.length < 7) return; // safety

                    reportData.transactions.push({
                        orNumber: "",
                        date: cells[3].textContent.trim(), // Released Date
                        name: cells[4].textContent.trim(), // Payor Name
                        particulars: cells[5].textContent.trim(), // Particulars
                        amount: parseFloat(
                            cells[6].textContent.trim().replace(/[^\d.-]/g, "")
                        )
                    });
                });

                reportData.totalAmount = reportData.transactions.reduce(
                    (sum, txn) => sum + txn.amount,
                    0
                );

                showReportPreview(reportData);
            }



            function showReportPreview(reportData) {
                // Create modal for preview
                const modal = document.createElement('div');
                modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
                modal.innerHTML = `
        <div class="bg-white rounded-lg shadow-xl w-11/12 max-w-6xl h-5/6 flex flex-col">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 class="text-xl font-bold">Report Preview</h3>
                <button onclick="this.closest('.fixed').remove()" class="text-gray-500 hover:text-gray-700">
                    <i class="ri-close-line text-2xl"></i>
                </button>
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200">
                <nav class="flex space-x-2 px-4" id="reportTabs">
                    <button class="tab-btn px-4 py-2 text-sm font-medium bg-blue-500 text-white rounded-t" data-tab="transactions">Daily Transaction Sheet</button>
                    <button class="tab-btn px-4 py-2 text-sm font-medium bg-gray-200 rounded-t" data-tab="accountability">Daily Sheet (Accountability Forms)</button>
                </nav>
            </div>

            <!-- Tab Contents -->
            <div class="flex-1 overflow-hidden p-4">
                <div id="tab-transactions" class="tab-content h-full overflow-auto">
                    <div id="dailyTransactionPreview" class="overflow-auto"></div>
                </div>
                <div id="tab-accountability" class="tab-content hidden h-full overflow-auto">
                    <div id="dailySheetPreview" class="overflow-auto"></div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-end p-4 border-t gap-2">
                <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-200">
                    Back
                </button>
                <button onclick="downloadReports(${JSON.stringify(reportData).replace(/"/g, '&quot;')})" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Download Report
                </button>
            </div>
        </div>
    `;

                document.body.appendChild(modal);

                // Fetch preview data from server
                fetchPreviewData(reportData);

                // Tab switching logic
                modal.querySelectorAll('.tab-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const selectedTab = btn.dataset.tab;

                        // Toggle button styles
                        modal.querySelectorAll('.tab-btn').forEach(b => {
                            b.classList.remove('bg-blue-500', 'text-white');
                            b.classList.add('bg-gray-200');
                        });
                        btn.classList.add('bg-blue-500', 'text-white');
                        btn.classList.remove('bg-gray-200');

                        // Toggle content
                        modal.querySelectorAll('.tab-content').forEach(tc => tc.classList.add('hidden'));
                        modal.querySelector(`#tab-${selectedTab}`).classList.remove('hidden');
                    });
                });
            }


            function fetchPreviewData(reportData) {
                fetch('collector_actions/generate_collector_dailyReport.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            ...reportData,
                            preview: true
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.dailyTransaction) {
                            document.getElementById('dailyTransactionPreview').innerHTML = data.dailyTransaction;
                        }
                        if (data.dailySheet) {
                            document.getElementById('dailySheetPreview').innerHTML = data.dailySheet;
                        }

                        // ✅ Record audit for generating daily report
                        fetch('audit_actions/record_audit_ajax.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: new URLSearchParams({
                                action: 'Generated Daily Report',
                                status: 'success'
                            })
                        });

                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('dailyTransactionPreview').innerHTML = '<p class="text-red-500">Error loading preview</p>';
                        document.getElementById('dailySheetPreview').innerHTML = '<p class="text-red-500">Error loading preview</p>';
                    });
            }


            function downloadReports(reportData) {
                // Close the preview modal
                document.querySelector('.fixed.inset-0').remove();

                // Proceed with download
                fetch('collector_actions/generate_collector_dailyReport.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(reportData)
                    })
                    .then(response => response.blob())
                    .then(blob => {
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = `Daily_Collector_Report_${new Date().toISOString().split('T')[0]}.xlsx`;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);

                        // ✅ Record audit for downloading the report
                        fetch('audit_actions/record_audit_ajax.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: new URLSearchParams({
                                action: 'Downloaded Daily Report',
                                status: 'success'
                            })
                        });

                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Report generation failed', 'error');
                    });

                location.reload();
            }



            function generateMonthlyReport() {
                console.log("Generating Monthly Report...");
                // Add your actual monthly report logic here
                // Proceed with download

            }
        </script>


        <script>
            let revenueChartInstance = null;

            function formatWeekLabel(weekLabel) {
                // Example weekLabel: "2025-W32"
                const [year, week] = weekLabel.split('-W');
                const weekNumber = parseInt(week);

                // Get the first day of the year
                const firstDayOfYear = new Date(year, 0, 1);
                const daysOffset = (weekNumber - 1) * 7;

                // Calculate the start date of the week
                const firstDayOfWeek = new Date(firstDayOfYear.getTime());
                firstDayOfWeek.setDate(firstDayOfYear.getDate() + daysOffset);

                const month = firstDayOfWeek.toLocaleString('default', {
                    month: 'long'
                });
                // Calculate nth week in the month
                const firstDayOfMonth = new Date(firstDayOfWeek.getFullYear(), firstDayOfWeek.getMonth(), 1);
                const nthWeek = Math.ceil((firstDayOfWeek.getDate() + firstDayOfMonth.getDay()) / 7);

                // Suffix for the week number
                const suffix = nthWeek === 1 ? 'st' : nthWeek === 2 ? 'nd' : nthWeek === 3 ? 'rd' : 'th';
                return `${nthWeek}${suffix} Week (${month})`;
            }

            async function fetchAndRenderChart(filter = 'daily') {
                const response = await fetch(`collector_actions/get_dashboard_data.php?filter=${filter}`);
                const data = await response.json();

                if (data.error) {
                    showToast(data.error, 'error');
                    return;
                }


                document.getElementById('total-transactions').textContent = data.total_transactions;
                document.getElementById('total-revenue').textContent = `₱${parseFloat(data.total_revenue).toLocaleString()}`;

                let labels = data.monthly_data.map(item => item.label);
                const revenueData = data.monthly_data.map(item => parseFloat(item.total));

                if (filter === 'weekly') {
                    labels = labels.map(formatWeekLabel);
                }

                const ctx = document.getElementById('revenueChart').getContext('2d');

                if (revenueChartInstance !== null) {
                    revenueChartInstance.destroy();
                }

                revenueChartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Revenue',
                            data: revenueData,
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.2)',
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: value => `₱${value.toLocaleString()}`
                                }
                            }
                        }
                    }
                });
            }
            // Observe container to resize chart when zooming
            const observer = new ResizeObserver(() => revenueChart.resize());
            observer.observe(document.getElementById('revenueChartContainer'));

            document.addEventListener('DOMContentLoaded', () => {
                const filterSelect = document.getElementById('chartFilter');
                fetchAndRenderChart(filterSelect.value);

                filterSelect.addEventListener('change', () => {
                    fetchAndRenderChart(filterSelect.value);
                });
            });
        </script>

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

                const form = document.getElementById('report-upload-form');
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(form);
                    const fileInput = form.querySelector('input[type="file"]');
                    let action = "Submitted report";

                    if (fileInput && fileInput.files.length > 0) {
                        const filename = fileInput.files[0].name;

                        if (/daily_collector_report/i.test(filename)) {
                            action = "Submitted daily report";
                        } else if (/monthly_accountability_report/i.test(filename)) {
                            const match = filename.match(/(\d{4})[_-](\d{1,2})/);
                            if (match) {
                                const year = match[1];
                                const monthNum = parseInt(match[2], 10);
                                const monthNames = [
                                    "January", "February", "March", "April", "May", "June",
                                    "July", "August", "September", "October", "November", "December"
                                ];
                                action = `Submitted monthly report ${monthNames[monthNum-1]} ${year}`;
                            } else {
                                action = "Submitted monthly report";
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
                // Load reports when tab is clicked
                document.getElementById('view-reports-tab').addEventListener('click', loadReports);

                // Filter event listeners
                document.getElementById('report-type-filter').addEventListener('change', () => {
                    loadReports();
                });
                document.getElementById('report-date-from').addEventListener('change', () => {
                    loadReports();
                });
                document.getElementById('report-date-to').addEventListener('change', () => {
                    loadReports();
                });

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

                fetch('get_reports.php', {
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
                            if (sheetName.toLowerCase().includes('transaction') || sheetName.toLowerCase().includes('daily') || sheetName.toLowerCase().includes('monthly')) {
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

        <script>
            let editing = false;

            document.addEventListener("DOMContentLoaded", () => {
                loadBatches();

                const modal = document.getElementById("batch-modal");
                const form = document.getElementById("batch-form");
                const addBtn = document.getElementById("add-batch-btn");
                const cancelBtn = document.getElementById("cancel-btn");
                const searchInput = document.getElementById("batch-search");

                const quantityInput = document.getElementById("quantity");
                const quantityLeftInput = document.getElementById("quantity-left");
                const serialStartInput = document.getElementById("serial-start");
                const serialEndInput = document.getElementById("serial-end");
                const collectorEmail = document.getElementById("collector-email");



                addBtn.onclick = () => {
                    editing = false;
                    document.getElementById("modal-title").innerText = "Add Batch";
                    document.getElementById("batch-id").value = "";
                    form.reset();
                    quantityLeftInput.readOnly = true;
                    serialEndInput.value = "";
                    quantityLeftInput.value = "";
                    modal.classList.remove("hidden");
                    console.log(collectorEmail.value);
                };

                cancelBtn.onclick = () => {
                    modal.classList.add("hidden");
                };

                form.onsubmit = async (e) => {
                    e.preventDefault();
                    const id = document.getElementById("batch-id").value;

                    const quantity = parseInt(form.quantity.value);
                    const quantityLeft = editing ? parseInt(form["quantity-left"].value) : quantity;

                    const serialStart = form["serial-start"].value;
                    const serialEnd = form["serial-end"].value;
                    const collectorEmailVal = form["collector-email"].value;

                    const data = {
                        quantity,
                        quantity_left: quantityLeft,
                        serial_start: serialStart,
                        serial_end: serialEnd,
                        collectorEmail: collectorEmailVal
                    };

                    const url = editing ?
                        `receipt_batches_actions/edit_batch.php?id=${id}` :
                        `receipt_batches_actions/add_batch.php`;

                    try {
                        const res = await fetch(url, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json"
                            },
                            body: JSON.stringify(data),
                        });

                        if (res.ok) {
                            // 👉 Record audit
                            const action = editing ?
                                `Edited a receipt batch (serial no. ${serialStart} - ${serialEnd})` :
                                `Added a receipt batch (serial no. ${serialStart} - ${serialEnd})`;

                            await fetch("audit_actions/record_audit_ajax.php", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json"
                                },
                                body: JSON.stringify({
                                    action
                                }),
                            });

                            modal.classList.add("hidden");
                            location.reload();
                        } else {
                            const err = await res.json();
                            showToast("Error: " + (err.message || "Unknown error"), "error");
                        }
                    } catch (error) {
                        console.error(error);
                        showToast("Request failed. Check console.", "error");
                    }
                };


                searchInput.addEventListener("input", () => loadBatches(searchInput.value));

                function updateSerialEnd() {
                    const quantity = parseInt(quantityInput.value);
                    const serialStart = parseInt(serialStartInput.value);
                    if (!isNaN(quantity) && !isNaN(serialStart)) {
                        serialEndInput.value = serialStart + quantity - 1;
                    } else {
                        serialEndInput.value = "";
                    }
                }

                quantityInput.addEventListener("input", () => {
                    updateSerialEnd();
                    if (!editing) {
                        quantityLeftInput.value = quantityInput.value;
                    }
                });

                serialStartInput.addEventListener("input", updateSerialEnd);
            });

            let allBatches = [];
            let receiptBatchCurrentPage = 1; // renamed variable
            let itemsPerPage = calculateRowsPerPage();

            // Dynamically calculate rows per page
            function calculateRowsPerPage() {
                const rowHeight = 70; // approximate height of each row
                const availableHeight = window.innerHeight - 400; // adjust if needed
                const count = Math.floor(availableHeight / rowHeight);
                return Math.max(count, 3); // ensure minimum of 3 rows
            }

            // Recalculate when window is resized
            window.addEventListener("resize", () => {
                itemsPerPage = calculateRowsPerPage();
                displayBatches();
            });

            async function loadBatches(search = "") {
                const res = await fetch("receipt_batches_actions/fetch_batches.php?search=" + encodeURIComponent(search));
                const data = await res.json();
                allBatches = data;
                receiptBatchCurrentPage = 1;
                displayBatches();
            }

            function displayBatches() {
                const tbody = document.getElementById("batch-table-body");
                tbody.innerHTML = "";

                const startIndex = (receiptBatchCurrentPage - 1) * itemsPerPage;
                const endIndex = startIndex + itemsPerPage;
                const paginatedData = allBatches.slice(startIndex, endIndex);

                paginatedData.forEach(batch => {
                    const tr = document.createElement("tr");
                    tr.innerHTML = `
                <td class="border border-gray-400 p-2">${batch.id}</td>
                <td class="border border-gray-400 p-2">${batch.quantity}</td>
                <td class="border border-gray-400 p-2">${batch.quantity_left}</td>
                <td class="border border-gray-400 p-2">${batch.serial_start}</td>
                <td class="border border-gray-400 p-2">${batch.serial_end}</td>
                <td class="border border-gray-400 p-2 space-x-2">
                    <!-- reference screenshot: /mnt/data/ed77d216-d013-4d95-882a-656e4fd71ef5.png -->

<center>
    <!-- Edit (icon-only) -->
    <button
        class="edit-button text-blue-600 hover:text-blue-800 p-2 transition"
        aria-label="Edit batch"
        title="Edit"
        onclick="editBatch(${batch.id})"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15.232 5.232l3.536 3.536M16.768 4.768a2 2 0 112.828 2.828L7 20H4v-3L16.768 4.768z" />
        </svg>
    </button>

    <!-- Delete (icon-only) -->
    <button
        class="delete-button text-red-600 hover:text-red-800 p-2 ml-2 transition"
        aria-label="Delete batch"
        title="Delete"
        onclick="deleteBatch(${batch.id})"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M10 4V2h4v2" />
        </svg>
    </button>
</center>

                </td>
            `;
                    tbody.appendChild(tr);
                });

                const totalPages = Math.ceil(allBatches.length / itemsPerPage);
                addBatchPaginationControls(totalPages);
            }

            function addBatchPaginationControls(totalPages) {
                let paginationContainer = document.getElementById('pagination-controls');
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
                        receiptBatchCurrentPage = page; // updated here
                        displayBatches();
                    };
                    return btn;
                };

                controlsWrapper.appendChild(createButton('«', 'First Page', 1, receiptBatchCurrentPage === 1));
                controlsWrapper.appendChild(createButton('←', 'Previous Page', receiptBatchCurrentPage - 1, receiptBatchCurrentPage === 1));

                let startPage = Math.max(1, receiptBatchCurrentPage - 1);
                let endPage = Math.min(totalPages, startPage + 2);

                if (endPage - startPage < 2 && startPage > 1) {
                    startPage = Math.max(1, endPage - 2);
                }

                for (let i = startPage; i <= endPage; i++) {
                    const pageButton = document.createElement('button');
                    pageButton.textContent = i;
                    pageButton.className = `px-3 py-1 rounded-md text-sm border ${
                receiptBatchCurrentPage === i
                    ? 'bg-blue-600 text-white border-blue-600'
                    : 'bg-gray-200 text-gray-700 border-gray-300 hover:bg-blue-500 hover:text-white transition'
            }`;
                    pageButton.onclick = () => {
                        receiptBatchCurrentPage = i; // updated here
                        displayBatches();
                    };
                    controlsWrapper.appendChild(pageButton);
                }

                controlsWrapper.appendChild(createButton('→', 'Next Page', receiptBatchCurrentPage + 1, receiptBatchCurrentPage === totalPages));
                controlsWrapper.appendChild(createButton('»', 'Last Page', totalPages, receiptBatchCurrentPage === totalPages));

                const containerWrapper = document.createElement('div');
                containerWrapper.className = 'flex flex-col items-center';
                containerWrapper.appendChild(controlsWrapper);

                const pageLabel = document.createElement('div');
                pageLabel.className = 'text-sm text-gray-700 mt-2';
                pageLabel.textContent = `Page ${receiptBatchCurrentPage} of ${totalPages}`;
                containerWrapper.appendChild(pageLabel);

                paginationContainer.appendChild(containerWrapper);
            }

            // Load initial data
            loadBatches();

            // Search event
            document.getElementById('batch-search').addEventListener('input', function() {
                loadBatches(this.value);
            });

            async function editBatch(id) {
                const res = await fetch("receipt_batches_actions/fetch_batches.php?id=" + id);
                const batch = await res.json();

                const modal = document.getElementById("batch-modal");
                document.getElementById("modal-title").innerText = "Edit Batch";
                document.getElementById("batch-id").value = batch.id;
                document.getElementById("quantity").value = batch.quantity;
                document.getElementById("quantity-left").value = batch.quantity_left;
                document.getElementById("serial-start").value = batch.serial_start;
                document.getElementById("serial-end").value = batch.serial_end;

                document.getElementById("quantity-left").readOnly = false;
                modal.classList.remove("hidden");
                editing = true;
            }

            async function deleteBatch(id) {
                if (confirm("Are you sure you want to delete this batch?")) {
                    try {
                        // 👉 fetch batch details first
                        const resBatch = await fetch("receipt_batches_actions/fetch_batches.php?id=" + id);
                        const batch = await resBatch.json();

                        // delete the batch
                        await fetch("receipt_batches_actions/delete_batch.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json"
                            },
                            body: JSON.stringify({
                                id
                            }),
                        });

                        // 👉 record audit
                        const action = `Deleted a receipt batch (serial no. ${batch.serial_start} - ${batch.serial_end})`;
                        await fetch("audit_actions/record_audit_ajax.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json"
                            },
                            body: JSON.stringify({
                                action
                            }),
                        });

                        location.reload();
                    } catch (err) {
                        console.error(err);
                        showToast("Error deleting batch", "error");
                    }
                }
            }
        </script>






</body>

</html>