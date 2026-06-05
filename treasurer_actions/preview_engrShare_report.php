<?php
session_start();

require_once 'engrShare_report_helpers.php';

$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Database connection
include '../backend/db_config_notpdo.php';
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error); 

$treasurerEmail = $_SESSION['email'];
$treasurerQuery = "SELECT firstname, lastname FROM santamaria_employees WHERE email = '$treasurerEmail'";
$treasurerResult = $conn->query($treasurerQuery);
$treasurer = $treasurerResult->fetch_assoc();
$treasurerName = $treasurer['firstname'] . ' ' . $treasurer['lastname'];

// Fetch org settings (id = 1, since only one row is needed)
$orgQuery = $conn->query("SELECT org_name, org_logo FROM organization_info WHERE id = 1 LIMIT 1");
$org = $orgQuery->fetch_assoc();

$orgName = $org['org_name'];

// Get monthly totals
$inspectionTotals = getInspectionFeeMonthlyTotals($conn, $year);
$buildingTotals = getBuildingPermitFeeMonthlyTotals($conn, $year);
$electricalTotals = getElectricalFeeMonthlyTotals($conn, $year);

$months = [
    'JANUARY',
    'FEBRUARY',
    'MARCH',
    'APRIL',
    'MAY',
    'JUNE',
    'JULY',
    'AUGUST',
    'SEPTEMBER',
    'OCTOBER',
    'NOVEMBER',
    'DECEMBER'
];

// Calculate totals
$totalLGU = 0;
$totalNG = 0;
$totalOfficials = 0;
$totalInspection = 0;
$totalBuilding = 0;
$totalElectrical = 0;

for ($i = 0; $i < 12; $i++) {
    $totalInspection += $inspectionTotals[$i];
    $totalBuilding += $buildingTotals[$i];
    $totalElectrical += $electricalTotals[$i];

    $totalLGU += ($inspectionTotals[$i] * 0.8) + ($buildingTotals[$i] * 0.8) + ($electricalTotals[$i] * 0.8);
    $totalNG += ($inspectionTotals[$i] * 0.05) + ($buildingTotals[$i] * 0.05) + ($electricalTotals[$i] * 0.05);
    $totalOfficials += ($inspectionTotals[$i] * 0.15) + ($buildingTotals[$i] * 0.15) + ($electricalTotals[$i] * 0.15);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Engineering Share Report Preview</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-white p-6 text-[13px]">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-6 leading-tight">
            <p class="font-semibold text-[14px]">Republic of the Philippines</p>
            <p class="font-semibold text-[14px]">Province of Laguna</p>
            <p class="font-semibold text-[15px]"><?php echo $orgName; ?></p>
            <p class="italic font-semibold text-[14px]">REPORT ON BUILDING, ELECTRICAL AND INSPECTION FEES</p>
            <p class="text-[13px]">For Year <?php echo $year; ?></p>
            <p class="text-left font-bold my-4 text-[13px]">Treasurer: <?php echo $treasurerName; ?></p>
        </div>

        <!-- Scrollable Table Container -->
        <div class="overflow-x-auto border border-gray-400 rounded-md mb-4">
            <table class="min-w-full border-collapse text-[12px]">
                <!-- Header Rows -->
                <thead class="bg-gray-100 font-semibold sticky top-0 z-10">
                    <tr class="text-center">
                        <th class="border border-gray-400 px-2 py-1 sticky left-0 bg-gray-100 z-20">MONTH</th>
                        <th colspan="3" class="border border-gray-400 px-2 py-1">INSPECTION FEES</th>
                        <th colspan="3" class="border border-gray-400 px-2 py-1">BUILDING PERMIT FEES</th>
                        <th colspan="3" class="border border-gray-400 px-2 py-1">ELECTRICAL FEES</th>
                        <th class="border border-gray-400 px-2 py-1"></th>
                        <th class="border border-gray-400 px-2 py-1">INSPECTION</th>
                        <th class="border border-gray-400 px-2 py-1">BUILDING PERMIT</th>
                        <th class="border border-gray-400 px-2 py-1">ELECTRICAL</th>
                    </tr>
                    <tr class="text-center bg-gray-100">
                        <th class="border border-gray-400 px-2 py-1 sticky left-0 bg-gray-100 z-20"></th>
                        <th class="border border-gray-400 px-2 py-1">80% (LGU)</th>
                        <th class="border border-gray-400 px-2 py-1">5% (N.G)</th>
                        <th class="border border-gray-400 px-2 py-1">15% BLDG. OFFICIALS</th>
                        <th class="border border-gray-400 px-2 py-1">80% (LGU)</th>
                        <th class="border border-gray-400 px-2 py-1">5% (N.G)</th>
                        <th class="border border-gray-400 px-2 py-1">15% BLDG. OFFICIALS</th>
                        <th class="border border-gray-400 px-2 py-1">80% (LGU)</th>
                        <th class="border border-gray-400 px-2 py-1">5% (N.G)</th>
                        <th class="border border-gray-400 px-2 py-1">15% BLDG. OFFICIALS</th>
                        <th class="border border-gray-400 px-2 py-1"></th>
                        <th class="border border-gray-400 px-2 py-1"></th>
                        <th class="border border-gray-400 px-2 py-1"></th>
                        <th class="border border-gray-400 px-2 py-1"></th>
                    </tr>
                </thead>

                <!-- Data Rows -->
                <tbody>
                    <?php foreach ($months as $i => $month): ?>
                        <tr class="text-center hover:bg-gray-50">
                            <td class="border border-gray-400 px-2 py-1 font-medium sticky left-0 bg-white z-10"><?php echo $month; ?></td>
                            <td class="border border-gray-400 px-2 py-1"><?php echo number_format($inspectionTotals[$i] * 0.8, 2); ?></td>
                            <td class="border border-gray-400 px-2 py-1"><?php echo number_format($inspectionTotals[$i] * 0.05, 2); ?></td>
                            <td class="border border-gray-400 px-2 py-1"><?php echo number_format($inspectionTotals[$i] * 0.15, 2); ?></td>
                            <td class="border border-gray-400 px-2 py-1"><?php echo number_format($buildingTotals[$i] * 0.8, 2); ?></td>
                            <td class="border border-gray-400 px-2 py-1"><?php echo number_format($buildingTotals[$i] * 0.05, 2); ?></td>
                            <td class="border border-gray-400 px-2 py-1"><?php echo number_format($buildingTotals[$i] * 0.15, 2); ?></td>
                            <td class="border border-gray-400 px-2 py-1"><?php echo number_format($electricalTotals[$i] * 0.8, 2); ?></td>
                            <td class="border border-gray-400 px-2 py-1"><?php echo number_format($electricalTotals[$i] * 0.05, 2); ?></td>
                            <td class="border border-gray-400 px-2 py-1"><?php echo number_format($electricalTotals[$i] * 0.15, 2); ?></td>
                            <td class="border border-gray-400 px-2 py-1"></td>
                            <td class="border border-gray-400 px-2 py-1"><?php echo number_format($inspectionTotals[$i], 2); ?></td>
                            <td class="border border-gray-400 px-2 py-1"><?php echo number_format($buildingTotals[$i], 2); ?></td>
                            <td class="border border-gray-400 px-2 py-1"><?php echo number_format($electricalTotals[$i], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- Total Row -->
                    <tr class="font-semibold text-green-700 bg-gray-50 text-center">
                        <td class="border border-gray-400 px-2 py-1 sticky left-0 bg-gray-50 z-10">TOTAL</td>
                        <td class="border border-gray-400 px-2 py-1"><?php echo number_format($totalInspection * 0.8, 2); ?></td>
                        <td class="border border-gray-400 px-2 py-1"><?php echo number_format($totalInspection * 0.05, 2); ?></td>
                        <td class="border border-gray-400 px-2 py-1"><?php echo number_format($totalInspection * 0.15, 2); ?></td>
                        <td class="border border-gray-400 px-2 py-1"><?php echo number_format($totalBuilding * 0.8, 2); ?></td>
                        <td class="border border-gray-400 px-2 py-1"><?php echo number_format($totalBuilding * 0.05, 2); ?></td>
                        <td class="border border-gray-400 px-2 py-1"><?php echo number_format($totalBuilding * 0.15, 2); ?></td>
                        <td class="border border-gray-400 px-2 py-1"><?php echo number_format($totalElectrical * 0.8, 2); ?></td>
                        <td class="border border-gray-400 px-2 py-1"><?php echo number_format($totalElectrical * 0.05, 2); ?></td>
                        <td class="border border-gray-400 px-2 py-1"><?php echo number_format($totalElectrical * 0.15, 2); ?></td>
                        <td class="border border-gray-400 px-2 py-1"></td>
                        <td class="border border-gray-400 px-2 py-1"><?php echo number_format($totalInspection, 2); ?></td>
                        <td class="border border-gray-400 px-2 py-1"><?php echo number_format($totalBuilding, 2); ?></td>
                        <td class="border border-gray-400 px-2 py-1"><?php echo number_format($totalElectrical, 2); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Summary Table -->
        <div class="w-80 ml-auto border border-gray-400 rounded-md overflow-hidden text-[12px]">
            <div class="grid grid-cols-2 divide-x divide-gray-400 text-center bg-gray-100 font-semibold">
                <div class="py-1">CATEGORY</div>
                <div class="py-1">TOTAL</div>
            </div>
            <div class="grid grid-cols-2 divide-x divide-gray-400 border-t border-gray-400">
                <div class="px-2 py-1">80% (LGU)</div>
                <div class="px-2 py-1 font-bold"><?php echo number_format($totalLGU, 2); ?></div>
            </div>
            <div class="grid grid-cols-2 divide-x divide-gray-400 border-t border-gray-400">
                <div class="px-2 py-1">5% (N.G)</div>
                <div class="px-2 py-1 font-bold"><?php echo number_format($totalNG, 2); ?></div>
            </div>
            <div class="grid grid-cols-2 divide-x divide-gray-400 border-t border-gray-400">
                <div class="px-2 py-1">15% BLDG. OFFICIALS</div>
                <div class="px-2 py-1 font-bold"><?php echo number_format($totalOfficials, 2); ?></div>
            </div>
        </div>

        <!-- Prepared by -->
        <div class="text-center mt-10 text-[13px]">
            <p>Prepared by:</p>
            <div class="border-b border-black w-1/3 mx-auto mb-1 pb-1 font-semibold"><?php echo $treasurerName; ?></div>
            <p>Position: Municipal Treasurer</p>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>