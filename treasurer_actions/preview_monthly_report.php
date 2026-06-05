<?php
session_start();
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n') - 1;
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$monthNames = ["January", "February", "March", "April", "May", "June", 
               "July", "August", "September", "October", "November", "December"];
$monthName = $monthNames[$month];

include '../backend/db_config_notpdo.php';
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Get all the totals from helper functions
require 'monthly_report_helpers.php';

 
 
// HELPERS
$weightMeasureTotal = getWeightMeasureTotal($conn, $monthName, $year);
$franchisingLicensingTotal = getFranchisingLicensingTotal($conn, $monthName, $year);
$mayorsPermitTotal = getMayorsPermitTotal($conn, $monthName, $year); 
$burialPermitTotal = getBurialPermitTotal($conn, $monthName, $year);
$sanitaryPermitTotal = getSanitaryPermitTotal($conn, $monthName, $year);
$marriageLicenseTotal = getMarriageLicenseTotal($conn, $monthName, $year);
$marriageApplicationTotal = getMarriageApplicationTotal($conn, $monthName, $year);
$policeClearanceTotal = getPoliceClearanceTotal($conn, $monthName, $year);
$deathCertTotal = getDeathCertTotal($conn, $monthName, $year);
$birthCertTotal = getBirthCertTotal($conn, $monthName, $year);
$marriageCertTotal = getMarriageCertTotal($conn, $monthName, $year);
$assesorCertTotal = getAssesorCertTotal($conn, $monthName, $year);
$MTOCertTotal = getMTOCertTotal($conn, $monthName, $year);
$healthCertTotal = getHealthCertTotal($conn, $monthName, $year);
$secretaryFeeTotal = getSecretaryFeeTotal($conn, $monthName, $year);
$otherMayorClearanceTotal = getOtherMayorClearanceTotal($conn, $monthName, $year);
$medicalDentalLabTotal = getMedicalDentalLabTotal($conn, $monthName, $year);
$solemnizationFeeTotal = getSolemnizationFeeTotal($conn, $monthName, $year);
$annotationFeeTotal = getAnnotationFeeTotal($conn, $monthName, $year);
$miscFeeTotal = getMiscFeeTotal($conn, $monthName, $year);
$RATotal = getRATotal($conn, $monthName, $year);
$cemeteriesTotal = getCemeteriesTotal($conn, $monthName, $year);
$inspectionFeeTotal = getInspectionFeeTotal($conn, $monthName, $year);
$electricalFeeTotal = getElectricalFeeTotal($conn, $monthName, $year);
$buildingPermitFeeTotal = getBuildingPermitFeeTotal($conn, $monthName, $year);
$zoningClearanceTotal = getZoningClearanceTotal($conn, $monthName, $year);

// Usage for each categorization function
$amusementTaxTotal = getAmusementTaxTotal($conn, $monthName, $year);
$businessTaxRetailersTotal = getBusinessTaxRetailersTotal($conn, $monthName, $year);
$businessTaxContractorsTotal = getBusinessTaxContractorsTotal($conn, $monthName, $year);
$businessTaxBanksTotal = getBusinessTaxBanksTotal($conn, $monthName, $year);
$businessTaxInterestTotal = getBusinessTaxInterestTotal($conn, $monthName, $year);
$communityTaxCorporationTotal = getCommunityTaxCorporationTotal($conn, $monthName, $year);
$communityTaxIndividualTotal = getCommunityTaxIndividualTotal($conn, $monthName, $year);
$cattleRegistrationTotal = getCattleRegistrationTotal($conn, $monthName, $year);
$garbageFeesTotal = getGarbageFeesTotal($conn, $monthName, $year);
$stlTotal = getSTLTotal($conn, $monthName, $year);
$backfillingHaulingTotal = getBackfillingHaulingTotal($conn, $monthName, $year);
$breqsTotal = getBREQSTotal($conn, $monthName, $year);
$interestIncomeTotal = getInterestIncomeTotal($conn, $monthName, $year);
$marilagEcoparkTotal = getMarilagEcoparkTotal($conn, $monthName, $year);
$municipalLotTotal = getMunicipalLotTotal($conn, $monthName, $year);
$stallGoodwillTotal = getStallGoodwillTotal($conn, $monthName, $year);
$stallRentalsTotal = getStallRentalsTotal($conn, $monthName, $year);
$waterWorksRegistrationTotal = getWaterWorksRegistrationTotal($conn, $monthName, $year);
$waterWorksMonthlyTotal = getWaterWorksMonthlyTotal($conn, $monthName, $year);
$slaughterTotal = getSlaughterhouseTotal($conn, $monthName, $year);
$birthCertNewBornTotal = getBirthCertNewBornTotal($conn, $monthName, $year);
$othersFeeTotal = getOthersFeeTotal($conn, $monthName, $year);

 
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Report Preview</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white p-5">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-6 leading-tight">
            <p class="font-semibold text-[14px]">Republic of the Philippines</p>
            <p class="font-semibold text-[14px]">Province of Laguna</p>
            <p class="font-semibold text-[15px]"><?php echo $orgName; ?></p>
            <p class="italic font-semibold text-[14px]">MONTHLY REPORT OF REVENUE AND RECEIPTS</p>
            <p class="text-[13px]">For <?php echo $monthName. ' ' .$year; ?></p>
            <p class="text-left font-bold my-4 text-[13px]">Treasurer: <?php echo $treasurerName; ?></p>
        </div>

        <table class="w-full border-collapse border border-gray-800 mb-8 text-xs">
            <thead>
                <tr class="bg-gray-100 font-bold text-center">
                    <th class="w-[60%] border border-gray-800 p-2">REVENUE SOURCES</th>
                    <th class="w-[20%] border border-gray-800 p-2">ACCOUNT CODE</th>
                    <th class="w-[20%] border border-gray-800 p-2">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                <!-- TAX ON BUSINESS -->
                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">TAX ON BUSINESS</td>
                    <td class="border border-gray-800 p-2"></td>
                    <td class="border border-gray-800 p-2 text-right text-green-600"><?php echo number_format($amusementTaxTotal + $businessTaxRetailersTotal + $businessTaxContractorsTotal
                    + $businessTaxBanksTotal + $businessTaxInterestTotal + $communityTaxCorporationTotal + $communityTaxIndividualTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Amusement Tax (Provincial Account)</td>
                    <td class="border border-gray-800 p-2 text-center">581</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($amusementTaxTotal, 2); ?></td>
                </tr>
                <tr class="bg-gray-100 font-bold">
                    <td class="border border-gray-800 p-2">Business Taxes</td>
                    <td class="border border-gray-800 p-2"></td>
                    <td class="border border-gray-800 p-2 text-right text-blue-600"><?php echo number_format($businessTaxRetailersTotal + $businessTaxContractorsTotal
                    + $businessTaxBanksTotal + $businessTaxInterestTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Retailers</td>
                    <td class="border border-gray-800 p-2 text-center">582</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($businessTaxRetailersTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Contractors</td>
                    <td class="border border-gray-800 p-2 text-center">582</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($businessTaxContractorsTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Banks & Other Financial Institutions</td>
                    <td class="border border-gray-800 p-2 text-center">582</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($businessTaxBanksTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Interest / Surcharge</td>
                    <td class="border border-gray-800 p-2 text-center">582</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($businessTaxInterestTotal, 2); ?></td>
                </tr>

                <!-- OTHER TAXES -->
                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">OTHER TAXES</td>
                    <td class="border border-gray-800 p-2"></td>
                    <td class="border border-gray-800 p-2 text-right text-green-600"><?php echo number_format($communityTaxCorporationTotal + $communityTaxIndividualTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Community Tax - Corporation</td>
                    <td class="border border-gray-800 p-2"></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($communityTaxCorporationTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Community Tax - Individual</td>
                    <td class="border border-gray-800 p-2 text-center">583</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($communityTaxIndividualTotal, 2); ?></td>
                </tr>

                <!-- REGULATORY FEES -->
                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">REGULATORY FEES (PERMITS & LICENSES)</td>
                    <td class="border border-gray-800 p-2"></td>
                    <td class="border border-gray-800 p-2 text-right text-green-600"><?php echo number_format(
                        $weightMeasureTotal + $franchisingLicensingTotal + $mayorsPermitTotal + 
                        $burialPermitTotal + $sanitaryPermitTotal + $marriageLicenseTotal + 
                        $marriageApplicationTotal + $buildingPermitFeeTotal + $zoningClearanceTotal + $cattleRegistrationTotal 
                        + $birthCertNewBornTotal + $inspectionFeeTotal, 2); ?></td>
                </tr>
                <tr class="bg-gray-100 font-bold">
                    <td class="border border-gray-800 p-2">Permits and Licenses</td>
                    <td class="border border-gray-800 p-2"></td>
                    <td class="border border-gray-800 p-2 text-right text-blue-600"><?php echo number_format(
                        $weightMeasureTotal + $franchisingLicensingTotal + $mayorsPermitTotal + 
                        $burialPermitTotal + $sanitaryPermitTotal + $buildingPermitFeeTotal + $zoningClearanceTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Fees on weights & measures</td>
                    <td class="border border-gray-800 p-2 text-center">601</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($weightMeasureTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Franchising & licensing Fees</td>
                    <td class="border border-gray-800 p-2 text-center">603</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($franchisingLicensingTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Permit Fees/ Mayor's Permit Fees</td>
                    <td class="border border-gray-800 p-2 text-center">605</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($mayorsPermitTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Building Permit Fees</td>
                    <td class="border border-gray-800 p-2 text-center">605</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($buildingPermitFeeTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Zonal/Location Permit Fees</td>
                    <td class="border border-gray-800 p-2 text-center">628</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($zoningClearanceTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Burial Permit Fees</td>
                    <td class="border border-gray-800 p-2 text-center">605</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($burialPermitTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Sanitary Permit Fees</td>
                    <td class="border border-gray-800 p-2 text-center">619</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($sanitaryPermitTotal, 2); ?></td>
                </tr>
                <tr class="bg-gray-100 font-bold">
                    <td class="border border-gray-800 p-2">Other Permits & Licenses</td>
                    <td class="border border-gray-800 p-2"></td>
                    <td class="border border-gray-800 p-2 text-right text-blue-600"><?php echo number_format($marriageLicenseTotal + $marriageApplicationTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Marriage License</td>
                    <td class="border border-gray-800 p-2 text-center">608</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($marriageLicenseTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Application for Marriage</td>
                    <td class="border border-gray-800 p-2 text-center">608</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($marriageApplicationTotal, 2); ?></td>
                </tr>
                <tr class="bg-gray-100 font-bold">
                    <td class="border border-gray-800 p-2">Registration Fees</td>
                    <td class="border border-gray-800 p-2"></td>
                    <td class="border border-gray-800 p-2 text-right text-blue-600"><?php echo number_format($cattleRegistrationTotal + $birthCertNewBornTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Cattle/Animal Registration fees</td>
                    <td class="border border-gray-800 p-2 text-center">606</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($cattleRegistrationTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Birth Certificate (New Born) Fees</td>
                    <td class="border border-gray-800 p-2 text-center">606</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($birthCertNewBornTotal, 2); ?></td>
                </tr>
                <tr class="bg-gray-100 font-bold">
                    <td class="border border-gray-800 p-2">Inspection Fees</td>
                    <td class="border border-gray-800 p-2 text-center">617</td>
                    <td class="border border-gray-800 p-2 text-right text-blue-600"><?php echo number_format($inspectionFeeTotal, 2); ?></td>
                </tr>

                <!-- SERVICE/USER CHARGES -->
                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">SERVICE/ USER CHARGES (Service Income)</td>
                    <td class="border border-gray-800 p-2"></td>
                    <td class="border border-gray-800 p-2 text-right text-green-600"><?php echo number_format(
                        $policeClearanceTotal + $deathCertTotal + $birthCertTotal + $marriageCertTotal + 
                        $assesorCertTotal + $MTOCertTotal + $healthCertTotal + 
                        $secretaryFeeTotal + $otherMayorClearanceTotal + $garbageFeesTotal + $medicalDentalLabTotal + $solemnizationFeeTotal + $annotationFeeTotal + $miscFeeTotal + 
                        $RATotal+ $electricalFeeTotal + $stlTotal + $breqsTotal + $interestIncomeTotal + $marilagEcoparkTotal + $backfillingHaulingTotal, 2); ?></td>
                </tr>
                <tr class="bg-gray-100 font-bold">
                    <td class="border border-gray-800 p-2">Clearance and Certification Fees</td>
                    <td class="border border-gray-800 p-2"></td>
                    <td class="border border-gray-800 p-2 text-right text-blue-600"><?php echo number_format($policeClearanceTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Police Clearance</td>
                    <td class="border border-gray-800 p-2 text-center">613</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($policeClearanceTotal, 2); ?></td>
                </tr>
                <tr class="bg-gray-100 font-bold">
                    <td class="border border-gray-800 p-2">Secretary's Fees</td>
                    <td class="border border-gray-800 p-2"></td>
                    <td class="border border-gray-800 p-2 text-right text-blue-600"><?php echo number_format(
                        $deathCertTotal + $birthCertTotal + $marriageCertTotal + 
                        $assesorCertTotal + $MTOCertTotal + $healthCertTotal + 
                        $secretaryFeeTotal
                        , 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Death Certificate</td>
                    <td class="border border-gray-800 p-2 text-center">613</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($deathCertTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Birth Certificate</td>
                    <td class="border border-gray-800 p-2 text-center">613</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($birthCertTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Marriage Certificate</td>
                    <td class="border border-gray-800 p-2 text-center">613</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($marriageCertTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Assessor's Certificate</td>
                    <td class="border border-gray-800 p-2 text-center">613</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($assesorCertTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">MTO certificate</td>
                    <td class="border border-gray-800 p-2 text-center">613</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($MTOCertTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Health Certificate (Medical)</td>
                    <td class="border border-gray-800 p-2 text-center">613</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($healthCertTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Secretary's Fees</td>
                    <td class="border border-gray-800 p-2 text-center">608</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($secretaryFeeTotal, 2); ?></td>
                </tr>
                <tr class="pl-5 bg-gray-100">
                    <td class="border border-gray-800 p-2 font-bold">Other Clearance & Cert./Mayor's Clearance</td>
                    <td class="border border-gray-800 p-2 text-center">613</td>
                    <td class="border border-gray-800 p-2 text-right text-blue-600 font-bold"><?php echo number_format($otherMayorClearanceTotal, 2); ?></td>
                </tr>
                <tr class="pl-5 bg-gray-100">
                    <td class="border border-gray-800 p-2 font-bold">Garbage Fees</td>
                    <td class="border border-gray-800 p-2 text-center">616</td>
                    <td class="border border-gray-800 p-2 text-right text-blue-600 font-bold"><?php echo number_format($garbageFeesTotal, 2); ?></td>
                </tr>
                <tr class="pl-5 bg-gray-100">
                    <td class="border border-gray-800 p-2 font-bold">Medical, Dental & Laboratory Fees</td>
                    <td class="border border-gray-800 p-2 text-center">619</td>
                    <td class="border border-gray-800 p-2 text-right text-blue-600 font-bold"><?php echo number_format($medicalDentalLabTotal, 2); ?></td>
                </tr>
                <tr class="bg-gray-100 font-bold">
                    <td class="border border-gray-800 p-2">Other Service Income</td>
                    <td class="border border-gray-800 p-2"></td>
                    <td class="border border-gray-800 p-2 text-right text-blue-600"><?php echo number_format(
                        $solemnizationFeeTotal + $annotationFeeTotal + $miscFeeTotal + 
                        $RATotal+ $electricalFeeTotal + $stlTotal + $breqsTotal + $interestIncomeTotal + $marilagEcoparkTotal + $backfillingHaulingTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Solemnization Fees</td>
                    <td class="border border-gray-800 p-2 text-center">628</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($solemnizationFeeTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Electrical Fees</td>
                    <td class="border border-gray-800 p-2 text-center">628</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($electricalFeeTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Annotation Fees</td>
                    <td class="border border-gray-800 p-2 text-center">628</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($annotationFeeTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">STL</td>
                    <td class="border border-gray-800 p-2 text-center">670</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($stlTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Backfilling/Hauling</td>
                    <td class="border border-gray-800 p-2 text-center">628</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($backfillingHaulingTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Miscellaneous Fees</td>
                    <td class="border border-gray-800 p-2 text-center">628</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($miscFeeTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">RA 9048, 10172 & 9255</td>
                    <td class="border border-gray-800 p-2 text-center">621</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($RATotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">BREQS</td>
                    <td class="border border-gray-800 p-2 text-center">613</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($breqsTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Interest Income</td>
                    <td class="border border-gray-800 p-2 text-center">628</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($interestIncomeTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Marilag Ecopark</td>
                    <td class="border border-gray-800 p-2 text-center">418</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($marilagEcoparkTotal, 2); ?></td>
                </tr>

                <!-- BUSINESS INCOME -->
                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">RECEIPTS FROM ECONOMIC ENTERPRISES (BUSINESS INCOME)</td>
                    <td class="border border-gray-800 p-2"></td>
                    <td class="border border-gray-800 p-2 text-right text-green-600"><?php echo number_format($cemeteriesTotal + $municipalLotTotal + $stallGoodwillTotal + $stallRentalsTotal 
                    + $slaughterTotal + $waterWorksRegistrationTotal + $waterWorksMonthlyTotal, 2); ?></td>
                </tr>
                <tr class="bg-gray-100 font-bold">
                    <td class="border border-gray-800 p-2">Cemetery Operations</td>
                    <td class="border border-gray-800 p-2"></td>
                    <td class="border border-gray-800 p-2 text-right text-blue-600"><?php echo number_format($cemeteriesTotal + $municipalLotTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Cemeteries</td>
                    <td class="border border-gray-800 p-2 text-center">633</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($cemeteriesTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Municipal Lot</td>
                    <td class="border border-gray-800 p-2 text-center">628</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($municipalLotTotal, 2); ?></td>
                </tr>
                <tr class="bg-gray-100 font-bold">
                    <td class="border border-gray-800 p-2">Market Operations</td>
                    <td class="border border-gray-800 p-2"></td>
                    <td class="border border-gray-800 p-2 text-right text-blue-600"><?php echo number_format($stallGoodwillTotal + $stallRentalsTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Stall Goodwill</td>
                    <td class="border border-gray-800 p-2 text-center">636</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($stallGoodwillTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Stall Rentals</td>
                    <td class="border border-gray-800 p-2 text-center">636</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($stallRentalsTotal, 2); ?></td>
                </tr>
                <tr class="bg-gray-100 font-bold">
                    <td class="border border-gray-800 p-2">Slaughter House Operations</td>
                    <td class="border border-gray-800 p-2 text-center">637</td>
                    <td class="border border-gray-800 p-2 text-right text-blue-600"><?php echo number_format($slaughterTotal, 2); ?></td>
                </tr>
                <tr class="bg-gray-100 font-bold">
                    <td class="border border-gray-800 p-2">Water Works System Operations</td>
                    <td class="border border-gray-800 p-2"></td>
                    <td class="border border-gray-800 p-2 text-right text-blue-600"><?php echo number_format($waterWorksRegistrationTotal + $waterWorksMonthlyTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Registration</td>
                    <td class="border border-gray-800 p-2"></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($waterWorksRegistrationTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Monthly dues</td>
                    <td class="border border-gray-800 p-2 text-center">639</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($waterWorksMonthlyTotal, 2); ?></td>
                </tr>

                <!-- LOCAL SOURCES -->
                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">LOCAL SOURCES</td>
                    <td class="border border-gray-800 p-2"></td>
                    <td class="border border-gray-800 p-2 text-right text-purple-600"><?php echo number_format(
                    // Sum of all local revenue sources
                        ($amusementTaxTotal + $businessTaxRetailersTotal + $businessTaxContractorsTotal
                    + $businessTaxBanksTotal + $businessTaxInterestTotal + $communityTaxCorporationTotal + $communityTaxIndividualTotal)
                    + ($weightMeasureTotal + $franchisingLicensingTotal + $mayorsPermitTotal + 
                        $burialPermitTotal + $sanitaryPermitTotal + $marriageLicenseTotal + 
                        $marriageApplicationTotal + $buildingPermitFeeTotal + $zoningClearanceTotal + $cattleRegistrationTotal 
                        + $birthCertNewBornTotal + $inspectionFeeTotal) + ($policeClearanceTotal + $deathCertTotal + $birthCertTotal + $marriageCertTotal + 
                        $assesorCertTotal + $MTOCertTotal + $healthCertTotal + 
                        $secretaryFeeTotal + $otherMayorClearanceTotal + $garbageFeesTotal + $medicalDentalLabTotal + $solemnizationFeeTotal + $annotationFeeTotal + $miscFeeTotal + 
                        $RATotal+ $electricalFeeTotal + $stlTotal + $breqsTotal + $interestIncomeTotal + $marilagEcoparkTotal + $backfillingHaulingTotal)
                        + ($cemeteriesTotal + $municipalLotTotal + $stallGoodwillTotal + $stallRentalsTotal 
                    + $slaughterTotal + $waterWorksRegistrationTotal + $waterWorksMonthlyTotal), 2); ?></td>
                </tr> 

                <!-- OTHER INCOME -->
                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">OTHER INCOME/RECEIPTS (OTHER GENERAL INCOME)</td>
                    <td class="border border-gray-800 p-2"></td>
                    <td class="border border-gray-800 p-2 text-right text-green-600"><?php echo number_format($othersFeeTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">IRA</td>
                    <td class="border border-gray-800 p-2 text-center">665</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($othersFeeTotal, 2); ?></td>
                </tr>

                <!-- GRAND TOTAL -->
                <!-- GRAND TOTAL -->
                <tr class="bg-gray-300 font-bold">
                    <td class="border border-gray-800 p-2" colspan="2">GRAND TOTAL</td>
                    <td class="border border-gray-800 p-2 text-right text-amber-800"><?php echo number_format(
        // Same as Local Sources since Other Income is currently 0
                        ($amusementTaxTotal + $businessTaxRetailersTotal + $businessTaxContractorsTotal
                    + $businessTaxBanksTotal + $businessTaxInterestTotal + $communityTaxCorporationTotal + $communityTaxIndividualTotal)
                    + ($weightMeasureTotal + $franchisingLicensingTotal + $mayorsPermitTotal + 
                        $burialPermitTotal + $sanitaryPermitTotal + $marriageLicenseTotal + 
                        $marriageApplicationTotal + $buildingPermitFeeTotal + $zoningClearanceTotal + $cattleRegistrationTotal 
                        + $birthCertNewBornTotal + $inspectionFeeTotal) + ($policeClearanceTotal + $deathCertTotal + $birthCertTotal + $marriageCertTotal + 
                        $assesorCertTotal + $MTOCertTotal + $healthCertTotal + 
                        $secretaryFeeTotal + $otherMayorClearanceTotal + $garbageFeesTotal + $medicalDentalLabTotal + $solemnizationFeeTotal + $annotationFeeTotal + $miscFeeTotal + 
                        $RATotal+ $electricalFeeTotal + $stlTotal + $breqsTotal + $interestIncomeTotal + $marilagEcoparkTotal + $backfillingHaulingTotal)
                        + ($cemeteriesTotal + $municipalLotTotal + $stallGoodwillTotal + $stallRentalsTotal 
                    + $slaughterTotal + $waterWorksRegistrationTotal + $waterWorksMonthlyTotal) + $othersFeeTotal, 2); ?></td>
                </tr>
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