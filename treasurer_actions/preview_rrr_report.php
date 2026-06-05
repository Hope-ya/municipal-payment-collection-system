<?php
// preview_rrr_report.php
session_start();
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

include '../backend/db_config_notpdo.php';
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Get all the totals from helper functions
require 'rrr_treasury_report_helpers.php';
require 'rrr_engrSect_report_helpers.php';
$totals = getAllTreasuryMonthlyTotals($conn, $year);

$inspectionTotals = getInspectionFeeMonthlyTotalsRRR($conn, $year);
$buildingTotals = getBuildingPermitFeeMonthlyTotalsRRR($conn, $year);
$electricalTotals = getElectricalFeeMonthlyTotalsRRR($conn, $year);

// Calculate partitions (80%/5%/15%)
$inspectionParts = calculateFeePartitionsRRR($inspectionTotals);
$buildingParts = calculateFeePartitionsRRR($buildingTotals);
$electricalParts = calculateFeePartitionsRRR($electricalTotals);

// Get collector info
$treasurerEmail = $_SESSION['email'];
$treasurerQuery = "SELECT firstname, lastname FROM santamaria_employees WHERE email = '$treasurerEmail'";
$treasurerResult = $conn->query($treasurerQuery);
$treasurer = $treasurerResult->fetch_assoc();
$treasurerName = $treasurer['firstname'] . ' ' . $treasurer['lastname'];

// Fetch org settings (id = 1, since only one row is needed)
$orgQuery = $conn->query("SELECT org_name, org_logo FROM organization_info WHERE id = 1 LIMIT 1");
$org = $orgQuery->fetch_assoc();

$orgName = $org['org_name'];

// Calculate quarterly totals
// Define quarters
$quarters = [
    '1ST QUARTER' => [1, 2, 3],
    '2ND QUARTER' => [4, 5, 6],
    '3RD QUARTER' => [7, 8, 9],
    '4TH QUARTER' => [10, 11, 12]
];

// Calculate quarterly totals per category
$quarterTotals = [];
foreach ($quarters as $qName => $months) {
    foreach ($totals as $category => $monthly) {
        $quarterTotals[$qName][$category] = 0.00;
        foreach ($months as $month) {
            if (isset($monthly[$month])) {
                $quarterTotals[$qName][$category] += (float)$monthly[$month];
            }
        }
    }
}

// Calculate grand totals per category
$grandTotals = [];
foreach ($totals as $category => $monthly) {
    $grandTotals[$category] = array_sum($monthly);
}


// Helper function to format values
function formatValue($value)
{
    return $value == 0 ? '-' : number_format($value, 2);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RRR Report Preview</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .qtr-1 {
            background-color: #bdd6ee;
        }

        .qtr-2 {
            background-color: #f7caac;
        }

        .qtr-3 {
            background-color: #ffe598;
        }

        .qtr-4 {
            background-color: #c5e0b3;
        }

        /* Make first column sticky */
        .sticky-col {
            position: sticky;
            left: 0;
            background-color: white;
            /* Prevent overlap transparency */
            z-index: 2;
            /* Keep above other cells */
        }

        /* Ensure header stays above body cells */
        .sticky-header {
            position: sticky;
            left: 0;
            background-color: #f3f4f6;
            /* Tailwind gray-100 equivalent */
            z-index: 3;
        }
    </style>
</head>

<body class="bg-white p-5">
    <div class="max-w-full mx-auto overflow-x-auto">
        <!-- Header -->
        <div class="text-center mb-6 leading-tight">
            <p class="font-semibold text-[14px]">Republic of the Philippines</p>
            <p class="font-semibold text-[14px]">Province of Laguna</p>
            <p class="font-semibold text-[15px]"><?php echo $orgName; ?></p>
            <p class="italic font-semibold text-[14px]">REMITTANCE AND REVENUE REPORT</p>
            <p class="text-[13px]">For Year <?php echo $year; ?></p>
            <p class="text-left font-bold my-4 text-[13px]">Treasurer: <?php echo $treasurerName; ?></p>
        </div>

        <table class="w-full border-collapse border border-gray-800 mb-8 text-xs">
            <thead>
                <tr class="bg-gray-100 font-bold text-center">
                    <th class="sticky-header border border-gray-800 p-1">Month</th>
                    <th class="border border-gray-800 p-1">Tax on Business Banks</th>
                    <th class="border border-gray-800 p-1">Tax on Business Contractors</th>
                    <th class="border border-gray-800 p-1">Tax on Business Other Bus.</th>
                    <th class="border border-gray-800 p-1">Tax on Business Retailer</th>
                    <th class="border border-gray-800 p-1">Interest / Surcharge</th>
                    <th class="border border-gray-800 p-1">Amusement Tax</th>
                    <th class="border border-gray-800 p-1">Medical, Dental and Lab. Fee</th>
                    <th class="border border-gray-800 p-1">Solemnization Fee</th>
                    <th class="border border-gray-800 p-1">Other Specific Income of LGU RA 9048/10172 RA 9255</th>
                    <th class="border border-gray-800 p-1">Municipal Lot</th>
                    <th class="border border-gray-800 p-1">Electrical Fee</th>
                    <th class="border border-gray-800 p-1">FSIF</th>
                    <th class="border border-gray-800 p-1">Professional Tax</th>
                    <th class="border border-gray-800 p-1">CTC Corp.</th>
                    <th class="border border-gray-800 p-1">Community Tax</th>
                    <th class="border border-gray-800 p-1">Mayor's Permit</th>
                    <th class="border border-gray-800 p-1">under the Building Code</th>
                    <th class="border border-gray-800 p-1">Zoning Clearance Fee</th>
                    <th class="border border-gray-800 p-1">on Weight & Measures</th>
                    <th class="border border-gray-800 p-1">Tricycle's Operators Permit</th>
                    <th class="border border-gray-800 p-1">Bicycle Permit</th>
                    <th class="border border-gray-800 p-1">Cattle Registration Fee (Ownership)</th>
                    <th class="border border-gray-800 p-1">Cattle Registration Fee (Transfer)</th>
                    <th class="border border-gray-800 p-1">Permit Fee</th>
                    <th class="border border-gray-800 p-1">Application for Marriage</th>
                    <th class="border border-gray-800 p-1">Marriage License</th>
                    <th class="border border-gray-800 p-1">Secretary's Fee (Miscellaneous)</th>
                    <th class="border border-gray-800 p-1">Burial Fee</th>
                    <th class="border border-gray-800 p-1">Death Certificate</th>
                    <th class="border border-gray-800 p-1">Birth Certificate</th>
                    <th class="border border-gray-800 p-1">Birth Certifacate (NB)</th>
                    <th class="border border-gray-800 p-1">Miscellaneous</th>
                    <th class="border border-gray-800 p-1">BREQS</th>
                    <th class="border border-gray-800 p-1">Marriage Certificate</th>
                    <th class="border border-gray-800 p-1">Health Certificate</th>
                    <th class="border border-gray-800 p-1">Sanitary Permit</th>
                    <th class="border border-gray-800 p-1">Mayor's Clearance</th>
                    <th class="border border-gray-800 p-1">Garbage Fee</th>
                    <th class="border border-gray-800 p-1">Police Clearance</th>
                    <th class="border border-gray-800 p-1">Annotation Fee</th>
                    <th class="border border-gray-800 p-1">Inspection Fee</th>
                    <th class="border border-gray-800 p-1">Certification Assesor</th>
                    <th class="border border-gray-800 p-1">Certification mto</th>
                    <th class="border border-gray-800 p-1">Market Stall Goodwill</th>
                    <th class="border border-gray-800 p-1">Fixed Stall Rental Fee</th>
                    <th class="border border-gray-800 p-1">Slaughter House</th>
                    <th class="border border-gray-800 p-1">Cemeteries</th>
                    <th class="border border-gray-800 p-1">Water Works System</th>
                    <th class="border border-gray-800 p-1">Water Registration</th>
                    <th class="border border-gray-800 p-1">Marilag ECO Park</th>
                    <th class="border border-gray-800 p-1">STL</th>
                    <th class="border border-gray-800 p-1">Cash Excess (Payroll)</th>
                    <th class="border border-gray-800 p-1">Cash Excess (AICS/PHILHEALTH)</th>
                    <th class="border border-gray-800 p-1">BACKFILING</th>
                    <th class="border border-gray-800 p-1">Miscellaneous</th>
                    <th class="border border-gray-800 p-1">Basic</th>
                    <th class="border border-gray-800 p-1">Total</th>
                    <th class="border border-gray-800 p-1">IRA</th>
                    <th class="border border-gray-800 p-1">Local Collection Total</th>
                    <th class="border border-gray-800 p-1">RPT Collection</th>
                    <th class="border border-gray-800 p-1">Other Collection</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $months = [
                    'JANUARY',
                    'FEBRUARY',
                    'MARCH',
                    '1ST QUARTER',
                    'APRIL',
                    'MAY',
                    'JUNE',
                    '2ND QUARTER',
                    'JULY',
                    'AUGUST',
                    'SEPTEMBER',
                    '3RD QUARTER',
                    'OCTOBER',
                    'NOVEMBER',
                    'DECEMBER',
                    '4TH QUARTER',
                    'GRAND TOTAL'
                ];

                foreach ($months as $month) {
                    $isQuarter = stripos($month, 'QUARTER') !== false;
                    $isGrandTotal = strtoupper($month) === 'GRAND TOTAL';
                    $qtrClass = '';

                    if ($isQuarter) {
                        if (preg_match('/(\d)/', $month, $matches)) {
                            $qtrNum = $matches[1];
                            $qtrClass = "qtr-{$qtrNum}";
                        }
                    }

                    echo '<tr class="' . $qtrClass . '">';
                    echo '<td class="sticky-col border border-gray-800 p-1 font-bold">' . $month . '</td>';


                    if ($isQuarter) {
                        $qName = $month;
                        $qIndex = array_search($qName, ['1ST QUARTER', '2ND QUARTER', '3RD QUARTER', '4TH QUARTER']);
                        $inspTotal = array_sum(array_column(array_slice($inspectionParts, $qIndex * 3, 3), 'main'));
                        $inspNG5 = array_sum(array_column(array_slice($inspectionParts, $qIndex * 3, 3), 'ng5'));
                        $insp15 = array_sum(array_column(array_slice($inspectionParts, $qIndex * 3, 3), 'fifteen'));

                        $bldgTotal = array_sum(array_column(array_slice($buildingParts, $qIndex * 3, 3), 'main'));
                        $bldgNG5 = array_sum(array_column(array_slice($buildingParts, $qIndex * 3, 3), 'ng5'));
                        $bldg15 = array_sum(array_column(array_slice($buildingParts, $qIndex * 3, 3), 'fifteen'));

                        $elecTotal = array_sum(array_column(array_slice($electricalParts, $qIndex * 3, 3), 'main'));
                        $elecNG5 = array_sum(array_column(array_slice($electricalParts, $qIndex * 3, 3), 'ng5'));
                        $elec15 = array_sum(array_column(array_slice($electricalParts, $qIndex * 3, 3), 'fifteen'));

                        $totalNG5and15 = 0; // Initialize total

                        // Loop through all 4 quarters
                        foreach (['1ST QUARTER', '2ND QUARTER', '3RD QUARTER', '4TH QUARTER'] as $qName) {
                            $qIndex = array_search($qName, ['1ST QUARTER', '2ND QUARTER', '3RD QUARTER', '4TH QUARTER']);

                            $inspNG5 = array_sum(array_column(array_slice($inspectionParts, $qIndex * 3, 3), 'ng5'));
                            $insp15 = array_sum(array_column(array_slice($inspectionParts, $qIndex * 3, 3), 'fifteen'));

                            $bldgNG5 = array_sum(array_column(array_slice($buildingParts, $qIndex * 3, 3), 'ng5'));
                            $bldg15 = array_sum(array_column(array_slice($buildingParts, $qIndex * 3, 3), 'fifteen'));

                            $elecNG5 = array_sum(array_column(array_slice($electricalParts, $qIndex * 3, 3), 'ng5'));
                            $elec15 = array_sum(array_column(array_slice($electricalParts, $qIndex * 3, 3), 'fifteen'));

                            // Add to total
                            $totalNG5and15 += ($elecNG5 + $elec15 + $bldgNG5 + $bldg15 + $inspNG5 + $insp15);
                        }

                        // Display quarter totals
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['business_tax_banks'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['business_tax_contractors'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['business_tax_banks'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['business_tax_retailers'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['business_tax_interest'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['amusement_tax'] ?? 0) . '</td>';

                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['medical_dental_lab'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['solemnization'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['ra_acts'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['municipal_lot'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['electrical'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['fsif'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['prof_tax'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['community_tax_corp'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['community_tax_individual'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['mayors_permit_fees'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['building_permit'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['zonal_location'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['weights_measures'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['tricycle'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['bicycle'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['cattle_registration_ownership'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['cattle_registration_transfer'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['permit_fees'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['application_marriage'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['marriage_license'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['secretary_fee'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['burial_permit'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['death_certificate'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['birth_certificate'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['birth_certificate_newborn'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['misc_fees'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['breqs'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['marriage_cert'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['health_cert'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['sanitary_permit'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['mayor_clearance'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['garbage'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['police_clearance'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['annotation_fees'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['inspection'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['assessor_cert'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['mto_cert'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['stall_rentals'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['stall_goodwill'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['slaughter_house'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['cemeteries'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['waterworks_dues'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['waterworks_registration'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['marilag_ecopark'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['stl'] ?? 0) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['cash_excess_payroll'] ?? 0) . '</td>'; // Cash Excess Payroll
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['cash_excess_aics_phil'] ?? 0) . '</td>'; // Cash Excess AICS
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['backfilling_hauling']) . '</td>'; // BACKFILING
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['misc_fees']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['basic']) . '</td>'; // Basic

                        // Calculate totals for the quarter 
                        $total = 0;
                        foreach ($quarterTotals[$qName] as $amount) {
                            $total += $amount;
                        }
                        echo '<td class="border border-gray-800 p-1 text-right font-bold">' . formatValue($total) . '</td>';

                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['others']) . '</td>'; // IRA

                        // Local Collection Total (sum of most columns except IRA, RPT, etc.)
                        $localCollection = $total;
                        echo '<td class="border border-gray-800 p-1 text-right font-bold">' . formatValue($localCollection) . '</td>';

                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['basic']) . '</td>'; // RPT Collection
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($quarterTotals[$qName]['cash_excess_aics_phil'] + $quarterTotals[$qName]['cash_excess_payroll']
                            + $elecNG5 + $elec15 + $bldgNG5 + $bldg15 +
                            $inspNG5 + $insp15) . '</td>'; // Other Collection
                    } elseif ($isGrandTotal) {
                        // Display grand totals
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['business_tax_banks']) . '</td>'; // Banks
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['business_tax_contractors']) . '</td>'; // Contractors
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['business_tax_banks']) . '</td>'; // Banks
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['business_tax_retailers']) . '</td>'; // Retailers
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['business_tax_interest']) . '</td>'; // Interest/Surcharge
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['amusement_tax']) . '</td>'; // Amusement Tax

                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['medical_dental_lab']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['solemnization']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['ra_acts']) . '</td>'; // RA 9048, 10172 & 9255

                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['municipal_lot']) . '</td>'; // Municipal Lot
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['electrical']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['fsif']) . '</td>'; // FSIF
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['prof_tax']) . '</td>'; // Professional Tax

                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['community_tax_corp']) . '</td>'; // CTC Corp
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['community_tax_individual']) . '</td>'; // Community Tax Individual

                        // Permit Fees / Mayor's Permit Fees split
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['mayors_permit_fees']) . '</td>'; // Mayor’s Permit Fees
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['building_permit']) . '</td>'; // Building Permit
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['zonal_location']) . '</td>'; // Zoning
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['weights_measures']) . '</td>'; // Weights & Measures

                        // Specials
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['tricycle']) . '</td>'; // Tricycle
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['bicycle']) . '</td>'; // Bicycle
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['cattle_registration_ownership']) . '</td>'; // Cattle Ownership
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['cattle_registration_transfer']) . '</td>'; // Cattle Transfer
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['permit_fees']) . '</td>'; // Permit Fees group

                        // Civil registry
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['application_marriage']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['marriage_license']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['secretary_fee']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['burial_permit']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['death_certificate']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['birth_certificate']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['birth_certificate_newborn']) . '</td>';

                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['misc_fees']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['breqs']) . '</td>'; // BREQS
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['marriage_cert']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['health_cert']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['sanitary_permit']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['mayor_clearance']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['garbage']) . '</td>'; // Garbage Fees
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['police_clearance']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['annotation_fees']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['inspection']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['assessor_cert']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['mto_cert']) . '</td>';

                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['stall_rentals']) . '</td>'; // Market Stall
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['stall_goodwill']) . '</td>'; // Fixed Stall
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['slaughter_house']) . '</td>'; // Slaughter House
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['cemeteries']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['waterworks_dues']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['waterworks_registration']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['marilag_ecopark']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['stl']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['cash_excess_payroll']) . '</td>'; // Cash Excess Payroll (if not categorized)
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['cash_excess_aics_phil']) . '</td>'; // Cash Excess AICS
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['backfilling_hauling']) . '</td>'; // Backfilling

                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['misc_fees']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['basic']) . '</td>'; // Basic

                        // Calculate grand total
                        $grandTotal = 0;
                        foreach ($grandTotals as $amount) {
                            $grandTotal += $amount;
                        }
                        echo '<td class="border border-gray-800 p-1 text-right font-bold">' . formatValue($grandTotal) . '</td>';

                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['others']) . '</td>'; // IRA

                        // Local Collection Total
                        $localCollection = $grandTotal;
                        echo '<td class="border border-gray-800 p-1 text-right font-bold">' . formatValue($localCollection) . '</td>';

                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['basic']) . '</td>'; // RPT Collection
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($grandTotals['cash_excess_aics_phil'] + $grandTotals['cash_excess_payroll'] + $totalNG5and15) . '</td>'; // Other Collection
                    } else {
                        $monthNum = array_search($month, ['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER']) + 1;
                        $monthNumEngr = array_search($month, ['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER']);


                        // Display monthly data
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['business_tax_banks'][$monthNum]) . '</td>'; // Banks
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['business_tax_contractors'][$monthNum]) . '</td>'; // Contractors
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['business_tax_banks'][$monthNum]) . '</td>'; // Banks
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['business_tax_retailers'][$monthNum]) . '</td>'; // Retailer
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['business_tax_interest'][$monthNum]) . '</td>'; // Interest/Surcharge
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['amusement_tax'][$monthNum]) . '</td>'; // Amusement Tax
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['medical_dental_lab'][$monthNum]) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['solemnization'][$monthNum]) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['ra_acts'][$monthNum]) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['municipal_lot'][$monthNum]) . '</td>'; // Municipal Lot
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['electrical'][$monthNum]) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['fsif'][$monthNum]) . '</td>'; // FSIF
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['prof_tax'][$monthNum]) . '</td>'; // Professional Tax
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['community_tax_corp'][$monthNum]) . '</td>'; // CTC Corp
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['community_tax_individual'][$monthNum]) . '</td>'; // Community Tax Individual
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['mayors_permit_fees'][$monthNum]) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['building_permit'][$monthNum]) . '</td>'; // Building Code
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['zonal_location'][$monthNum]) . '</td>'; // Zoning
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['weights_measures'][$monthNum]) . '</td>'; // Weights & Measures
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['tricycle'][$monthNum]) . '</td>'; // Tricycle Permit
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['bicycle'][$monthNum]) . '</td>'; // Bicycle Permit
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['cattle_registration_ownership'][$monthNum]) . '</td>'; // Cattle Ownership
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['cattle_registration_transfer'][$monthNum]) . '</td>'; // Cattle Transfer
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['permit_fees'][$monthNum]) . '</td>'; // Permit Fees (grouped)
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['application_marriage'][$monthNum]) . '</td>'; // Marriage Application
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['marriage_license'][$monthNum]) . '</td>'; // Marriage License
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['secretary_fee'][$monthNum]) . '</td>'; // Secretary Fee
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['burial_permit'][$monthNum]) . '</td>'; // Burial Fee
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['death_certificate'][$monthNum]) . '</td>'; // Death Cert
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['birth_certificate'][$monthNum]) . '</td>'; // Birth Cert
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['birth_certificate_newborn'][$monthNum]) . '</td>'; // Birth Cert NB
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['misc_fees'][$monthNum]) . '</td>'; // Misc Fees
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['breqs'][$monthNum]) . '</td>'; // BREQS
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['marriage_cert'][$monthNum]) . '</td>'; // Marriage Cert
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['health_cert'][$monthNum]) . '</td>'; // Health Cert
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['sanitary_permit'][$monthNum]) . '</td>'; // Sanitary Permit
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['mayor_clearance'][$monthNum]) . '</td>'; // Mayor’s Clearance
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['garbage'][$monthNum]) . '</td>'; // Garbage
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['police_clearance'][$monthNum]) . '</td>'; // Police Clearance
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['annotation_fees'][$monthNum]) . '</td>'; // Annotation Fees
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['inspection'][$monthNum]) . '</td>'; // Inspection Fee
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['assessor_cert'][$monthNum]) . '</td>'; // Assessor Cert
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['mto_cert'][$monthNum]) . '</td>'; // MTO Cert
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['stall_rentals'][$monthNum]) . '</td>'; // Market Stall Rentals
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['stall_goodwill'][$monthNum]) . '</td>'; // Stall Goodwill (Fixed Stall)
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['slaughter_house'][$monthNum]) . '</td>'; // Slaughter House
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['cemeteries'][$monthNum]) . '</td>'; // Cemeteries
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['waterworks_dues'][$monthNum]) . '</td>'; // Water Works
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['waterworks_registration'][$monthNum]) . '</td>'; // Water Registration
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['marilag_ecopark'][$monthNum]) . '</td>'; // Marilag ECO Park
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['stl'][$monthNum]) . '</td>'; // STL
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['cash_excess_payroll'][$monthNum]) . '</td>'; // Cash Excess Payroll
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['cash_excess_aics_phil'][$monthNum]) . '</td>'; // Cash Excess AICS
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['backfilling_hauling'][$monthNum]) . '</td>'; // BACKFILING
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['misc_fees'][$monthNum]) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['basic'][$monthNum]) . '</td>'; // Basic

                        // Calculate monthly total
                        $monthTotal = 0;
                        foreach ($totals as $category => $monthly) {
                            $monthTotal += $monthly[$monthNum];
                        }
                        echo '<td class="border border-gray-800 p-1 text-right font-bold">' . formatValue($monthTotal) . '</td>';

                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['others'][$monthNum]) . '</td>'; // IRA

                        // Local Collection Total
                        $localCollection = $monthTotal;
                        echo '<td class="border border-gray-800 p-1 text-right font-bold">' . formatValue($localCollection) . '</td>';

                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($totals['basic'][$monthNum]) . '</td>'; // RPT Collection
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue(
                            ($totals['cash_excess_aics_phil'][$monthNum] ?? 0) +
                                ($totals['cash_excess_payroll'][$monthNum] ?? 0) +
                                ($electricalParts[$monthNumEngr]['fifteen'] ?? 0) +
                                ($electricalParts[$monthNumEngr]['ng5'] ?? 0) +
                                ($buildingParts[$monthNumEngr]['fifteen'] ?? 0) +
                                ($buildingParts[$monthNumEngr]['ng5'] ?? 0) +
                                ($inspectionParts[$monthNumEngr]['fifteen'] ?? 0) +
                                ($inspectionParts[$monthNumEngr]['ng5'] ?? 0)
                        ) . '</td>';
                    }

                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>

        <!-- Engineering Section -->
        <table class="w-full border-collapse border border-gray-800 mb-8 text-xs">
            <thead>
                <tr class="bg-gray-100 font-bold text-center">
                    <th class="border border-gray-800 p-1" colspan="10">ENGINEERING</th>
                </tr>
                <tr class="bg-gray-100 font-bold text-center">
                    <th class=" sticky-header border border-gray-800 p-1">Month</th>
                    <th class="border border-gray-800 p-1" colspan="3">Inspection Fee</th>
                    <th class="border border-gray-800 p-1" colspan="3">Building Fee</th>
                    <th class="border border-gray-800 p-1" colspan="3">Electrical Fee</th>
                </tr>
                <tr class="bg-gray-100 font-bold text-center">
                    <th class="sticky-header border border-gray-800 p-1"></th>
                    <th class="border border-gray-800 p-1">80%</th>
                    <th class="border border-gray-800 p-1">5%</th>
                    <th class="border border-gray-800 p-1">15%</th>
                    <th class="border border-gray-800 p-1">80%</th>
                    <th class="border border-gray-800 p-1">5%</th>
                    <th class="border border-gray-800 p-1">15%</th>
                    <th class="border border-gray-800 p-1">80%</th>
                    <th class="border border-gray-800 p-1">5%</th>
                    <th class="border border-gray-800 p-1">15%</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Get engineering totals


                $monthsEngr = [
                    'JANUARY',
                    'FEBRUARY',
                    'MARCH',
                    '1ST QUARTER',
                    'APRIL',
                    'MAY',
                    'JUNE',
                    '2ND QUARTER',
                    'JULY',
                    'AUGUST',
                    'SEPTEMBER',
                    '3RD QUARTER',
                    'OCTOBER',
                    'NOVEMBER',
                    'DECEMBER',
                    '4TH QUARTER'
                ];

                foreach ($monthsEngr as $month) {
                    $isQuarter = strpos($month, 'QUARTER') !== false;
                    $qtrClass = '';

                    if ($isQuarter) {
                        $qtrNum = substr($month, 0, 1); // Get 1st, 2nd, etc.
                        $qtrClass = "qtr-{$qtrNum}";
                    }

                    echo '<tr class="' . $qtrClass . '">';
                    echo '<td class="sticky-col border border-gray-800 p-1 font-bold">' . $month . '</td>';

                    if ($isQuarter) {
                        $qName = $month;
                        $qIndex = array_search($qName, ['1ST QUARTER', '2ND QUARTER', '3RD QUARTER', '4TH QUARTER']);

                        // Inspection Fee
                        $inspTotal = array_sum(array_column(array_slice($inspectionParts, $qIndex * 3, 3), 'main'));
                        $inspNG5 = array_sum(array_column(array_slice($inspectionParts, $qIndex * 3, 3), 'ng5'));
                        $insp15 = array_sum(array_column(array_slice($inspectionParts, $qIndex * 3, 3), 'fifteen'));

                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($inspTotal) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($inspNG5) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($insp15) . '</td>';

                        // Building Fee
                        $bldgTotal = array_sum(array_column(array_slice($buildingParts, $qIndex * 3, 3), 'main'));
                        $bldgNG5 = array_sum(array_column(array_slice($buildingParts, $qIndex * 3, 3), 'ng5'));
                        $bldg15 = array_sum(array_column(array_slice($buildingParts, $qIndex * 3, 3), 'fifteen'));

                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($bldgTotal) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($bldgNG5) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($bldg15) . '</td>';

                        // Electrical Fee
                        $elecTotal = array_sum(array_column(array_slice($electricalParts, $qIndex * 3, 3), 'main'));
                        $elecNG5 = array_sum(array_column(array_slice($electricalParts, $qIndex * 3, 3), 'ng5'));
                        $elec15 = array_sum(array_column(array_slice($electricalParts, $qIndex * 3, 3), 'fifteen'));

                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($elecTotal) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($elecNG5) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($elec15) . '</td>';
                    } else {
                        $monthNum = array_search($month, ['JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE', 'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER']);

                        // Inspection Fee
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($inspectionParts[$monthNum]['main']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($inspectionParts[$monthNum]['ng5']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($inspectionParts[$monthNum]['fifteen']) . '</td>';

                        // Building Fee
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($buildingParts[$monthNum]['main']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($buildingParts[$monthNum]['ng5']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($buildingParts[$monthNum]['fifteen']) . '</td>';

                        // Electrical Fee
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($electricalParts[$monthNum]['main']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($electricalParts[$monthNum]['ng5']) . '</td>';
                        echo '<td class="border border-gray-800 p-1 text-right">' . formatValue($electricalParts[$monthNum]['fifteen']) . '</td>';
                    }

                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>

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