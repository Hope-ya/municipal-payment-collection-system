<?php
session_start();
$quarter = isset($_GET['quarter']) ? $_GET['quarter'] : 'Q1';
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Determine months based on quarter
switch ($quarter) {
    case 'Q1':
        $months = ['JANUARY', 'FEBRUARY', 'MARCH'];
        break;
    case 'Q2':
        $months = ['APRIL', 'MAY', 'JUNE'];
        break;
    case 'Q3':
        $months = ['JULY', 'AUGUST', 'SEPTEMBER'];
        break;
    case 'Q4':
        $months = ['OCTOBER', 'NOVEMBER', 'DECEMBER'];
        break;
    default:
        $months = ['JANUARY', 'FEBRUARY', 'MARCH'];
}
 
include '../backend/db_config_notpdo.php';
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Get all the totals from helper functions
require_once 'quarterly_report_helpers.php';

// Fetch all the quarterly data
// ===== Usage Code for Quarterly Totals =====

// Weight and Measure
$qtrWeightMeasure = getWeightMeasureMonthlyTotals($conn, $quarter, $year);
$qtrWeightMeasureTotal = array_sum($qtrWeightMeasure);

// Franchising & Licensing
$qtrFranchisingLicensing = getFranchisingLicensingMonthlyTotals($conn, $quarter, $year);
$qtrFranchisingLicensingTotal = array_sum($qtrFranchisingLicensing);

// Mayor's Permit
$qtrMayorsPermit = getMayorsPermitMonthlyTotals($conn, $quarter, $year);
$qtrMayorsPermitTotal = array_sum($qtrMayorsPermit);

// Burial Permit
$qtrBurialPermit = getBurialPermitMonthlyTotals($conn, $quarter, $year);
$qtrBurialPermitTotal = array_sum($qtrBurialPermit);

// Sanitary Permit
$qtrSanitaryPermit = getSanitaryPermitMonthlyTotals($conn, $quarter, $year);
$qtrSanitaryPermitTotal = array_sum($qtrSanitaryPermit);

// Marriage License
$qtrMarriageLicense = getMarriageLicenseMonthlyTotals($conn, $quarter, $year);
$qtrMarriageLicenseTotal = array_sum($qtrMarriageLicense);

// Marriage Application
$qtrMarriageApplication = getMarriageApplicationMonthlyTotals($conn, $quarter, $year);
$qtrMarriageApplicationTotal = array_sum($qtrMarriageApplication);

// Police Clearance
$qtrPoliceClearance = getPoliceClearanceMonthlyTotals($conn, $quarter, $year);
$qtrPoliceClearanceTotal = array_sum($qtrPoliceClearance);

// Death Certificate
$qtrDeathCert = getDeathCertMonthlyTotals($conn, $quarter, $year);
$qtrDeathCertTotal = array_sum($qtrDeathCert);

// Birth Certificate
$qtrBirthCert = getBirthCertMonthlyTotals($conn, $quarter, $year);
$qtrBirthCertTotal = array_sum($qtrBirthCert);

// Marriage Certificate
$qtrMarriageCert = getMarriageCertMonthlyTotals($conn, $quarter, $year);
$qtrMarriageCertTotal = array_sum($qtrMarriageCert);

// Assessor's Certificate
$qtrAssesorCert = getAssesorCertMonthlyTotals($conn, $quarter, $year);
$qtrAssesorCertTotal = array_sum($qtrAssesorCert);

// MTO Certificate
$qtrMTOCert = getMTOCertMonthlyTotals($conn, $quarter, $year);
$qtrMTOCertTotal = array_sum($qtrMTOCert);

// Health Certificate
$qtrHealthCert = getHealthCertMonthlyTotals($conn, $quarter, $year);
$qtrHealthCertTotal = array_sum($qtrHealthCert);

// Secretary Fee
$qtrSecretaryFee = getSecretaryFeeMonthlyTotals($conn, $quarter, $year);
$qtrSecretaryFeeTotal = array_sum($qtrSecretaryFee);

// Other Mayor Clearance
$qtrOtherMayorClearance = getOtherMayorClearanceMonthlyTotals($conn, $quarter, $year);
$qtrOtherMayorClearanceTotal = array_sum($qtrOtherMayorClearance);

// Medical, Dental, Laboratory
$qtrMedicalDentalLab = getMedicalDentalLabMonthlyTotals($conn, $quarter, $year);
$qtrMedicalDentalLabTotal = array_sum($qtrMedicalDentalLab);

// Solemnization Fee
$qtrSolemnizationFee = getSolemnizationFeeMonthlyTotals($conn, $quarter, $year);
$qtrSolemnizationFeeTotal = array_sum($qtrSolemnizationFee);

// Annotation Fee
$qtrAnnotationFee = getAnnotationFeeMonthlyTotals($conn, $quarter, $year);
$qtrAnnotationFeeTotal = array_sum($qtrAnnotationFee);

// Miscellaneous Fee
$qtrMiscFee = getMiscFeeMonthlyTotals($conn, $quarter, $year);
$qtrMiscFeeTotal = array_sum($qtrMiscFee);

// RA
$qtrRA = getRAMonthlyTotals($conn, $quarter, $year);
$qtrRATotal = array_sum($qtrRA);

// Cemeteries
$qtrCemeteries = getCemeteriesMonthlyTotals($conn, $quarter, $year);
$qtrCemeteriesTotal = array_sum($qtrCemeteries);

// Inspection Fee
$qtrInspectionFee = getInspectionFeeMonthlyTotals($conn, $quarter, $year);
$qtrInspectionFeeTotal = array_sum($qtrInspectionFee);

// Electrical Fee
$qtrElectricalFee = getElectricalFeeMonthlyTotals($conn, $quarter, $year);
$qtrElectricalFeeTotal = array_sum($qtrElectricalFee);

// Building Permit Fee
$qtrBuildingPermitFee = getBuildingPermitFeeMonthlyTotals($conn, $quarter, $year);
$qtrBuildingPermitFeeTotal = array_sum($qtrBuildingPermitFee);

// Zoning Clearance
$qtrZoningClearance = getZoningClearanceMonthlyTotals($conn, $quarter, $year);
$qtrZoningClearanceTotal = array_sum($qtrZoningClearance);

// Amusement Tax
$qtrAmusementTax = getAmusementTaxMonthlyTotals($conn, $quarter, $year);
$qtrAmusementTaxTotal = array_sum($qtrAmusementTax);

// Backfilling & Hauling
$qtrBackfillingHauling = getBackfillingHaulingMonthlyTotals($conn, $quarter, $year);
$qtrBackfillingHaulingTotal = array_sum($qtrBackfillingHauling);

// BREQS
$qtrBREQS = getBREQSMonthlyTotals($conn, $quarter, $year);
$qtrBREQSTotal = array_sum($qtrBREQS);

// Business Taxes (Banks)
$qtrBusinessTaxesBanks = getBusinessTaxesBanksMonthlyTotals($conn, $quarter, $year);
$qtrBusinessTaxesBanksTotal = array_sum($qtrBusinessTaxesBanks);

// Business Taxes (Contractors)
$qtrBusinessTaxesContractors = getBusinessTaxesContractorsMonthlyTotals($conn, $quarter, $year);
$qtrBusinessTaxesContractorsTotal = array_sum($qtrBusinessTaxesContractors);

// Business Taxes (Interest)
$qtrBusinessTaxesInterest = getBusinessTaxesInterestMonthlyTotals($conn, $quarter, $year);
$qtrBusinessTaxesInterestTotal = array_sum($qtrBusinessTaxesInterest);

// Business Taxes (Retailers)
$qtrBusinessTaxesRetailers = getBusinessTaxesRetailersMonthlyTotals($conn, $quarter, $year);
$qtrBusinessTaxesRetailersTotal = array_sum($qtrBusinessTaxesRetailers);

// Cattle Animal Registration
$qtrCattleAnimalRegistration = getCattleAnimalRegistrationMonthlyTotals($conn, $quarter, $year);
$qtrCattleAnimalRegistrationTotal = array_sum($qtrCattleAnimalRegistration);

// Community Tax (Corporation)
$qtrCommunityTaxCorporation = getCommunityTaxCorporationMonthlyTotals($conn, $quarter, $year);
$qtrCommunityTaxCorporationTotal = array_sum($qtrCommunityTaxCorporation);

// Community Tax (Individual)
$qtrCommunityTaxIndividual = getCommunityTaxIndividualMonthlyTotals($conn, $quarter, $year);
$qtrCommunityTaxIndividualTotal = array_sum($qtrCommunityTaxIndividual);

// Garbage Fees
$qtrGarbageFees = getGarbageFeesMonthlyTotals($conn, $quarter, $year);
$qtrGarbageFeesTotal = array_sum($qtrGarbageFees);

// Interest Income
$qtrInterestIncome = getInterestIncomeMonthlyTotals($conn, $quarter, $year);
$qtrInterestIncomeTotal = array_sum($qtrInterestIncome);

// Marilag Ecopark
$qtrMarilagEcopark = getMarilagEcoparkMonthlyTotals($conn, $quarter, $year);
$qtrMarilagEcoparkTotal = array_sum($qtrMarilagEcopark);

// Municipal Lot
$qtrMunicipalLot = getMunicipalLotMonthlyTotals($conn, $quarter, $year);
$qtrMunicipalLotTotal = array_sum($qtrMunicipalLot);

// Slaughter House Operations
$qtrSlaughterHouseOperations = getSlaughterHouseOperationsMonthlyTotals($conn, $quarter, $year);
$qtrSlaughterHouseOperationsTotal = array_sum($qtrSlaughterHouseOperations);

// Stall Goodwill
$qtrStallGoodwill = getStallGoodwillMonthlyTotals($conn, $quarter, $year);
$qtrStallGoodwillTotal = array_sum($qtrStallGoodwill);

// Stall Rentals
$qtrStallRentals = getStallRentalsMonthlyTotals($conn, $quarter, $year);
$qtrStallRentalsTotal = array_sum($qtrStallRentals);

// STL
$qtrSTL = getSTLMonthlyTotals($conn, $quarter, $year);
$qtrSTLTotal = array_sum($qtrSTL);

// WaterWorks Monthly Dues
$qtrWaterWorksMonthlyDues = getWaterWorksMonthlyDuesMonthlyTotals($conn, $quarter, $year);
$qtrWaterWorksMonthlyDuesTotal = array_sum($qtrWaterWorksMonthlyDues);

// WaterWorks Registration
$qtrWaterWorksRegistration = getWaterWorksRegistrationMonthlyTotals($conn, $quarter, $year);
$qtrWaterWorksRegistrationTotal = array_sum($qtrWaterWorksRegistration);

// Birth Certificate (Newborn)
$qtrBirthCertNewBorn = getBirthCertNewBornMonthlyTotals($conn, $quarter, $year);
$qtrBirthCertNewBornTotal = array_sum($qtrBirthCertNewBorn);

$qtrOthersFee = getOthersFeeMonthlyTotals($conn, $quarter, $year);
$qtrOthersFeeTotal = array_sum($qtrOthersFee);


// Get treasurer info
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
    <title>Quarterly Report Preview</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-white p-5">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-6 leading-tight">
            <p class="font-semibold text-[14px]">Republic of the Philippines</p>
            <p class="font-semibold text-[14px]">Province of Laguna</p>
            <p class="font-semibold text-[15px]"><?php echo $orgName; ?></p>
            <p class="italic font-semibold text-[14px]">QUARTERLY REPORT OF REVENUE AND RECEIPTS</p>
            <p class="text-[13px]">For <?php echo $quarter. ' ' .$year; ?></p>
            <p class="text-left font-bold my-4 text-[13px]">Treasurer: <?php echo $treasurerName; ?></p>
        </div>

        <table class="w-full border-collapse border border-gray-800 mb-8 text-xs">
            <thead>
                <tr class="bg-gray-100 font-bold text-center">
                    <th class="w-[30%] border border-gray-800 p-2">REVENUE SOURCES</th>
                    <th class="w-[15%] border border-gray-800 p-2"><?php echo $months[0]; ?></th>
                    <th class="w-[15%] border border-gray-800 p-2"><?php echo $months[1]; ?></th>
                    <th class="w-[15%] border border-gray-800 p-2"><?php echo $months[2]; ?></th>
                    <th class="w-[15%] border border-gray-800 p-2">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <!-- TAX ON BUSINESS -->
                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">TAX ON BUSINESS</td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format($qtrAmusementTax[0] + $qtrBusinessTaxesRetailers[0] + $qtrBusinessTaxesContractors[0] + $qtrBusinessTaxesBanks[0] + $qtrBusinessTaxesInterest[0] + $qtrCommunityTaxCorporation[0] + $qtrCommunityTaxIndividual[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format($qtrAmusementTax[1] + $qtrBusinessTaxesRetailers[1] + $qtrBusinessTaxesContractors[1] + $qtrBusinessTaxesBanks[1] + $qtrBusinessTaxesInterest[1] + $qtrCommunityTaxCorporation[1] + $qtrCommunityTaxIndividual[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format($qtrAmusementTax[2] + $qtrBusinessTaxesRetailers[2] + $qtrBusinessTaxesContractors[2] + $qtrBusinessTaxesBanks[2] + $qtrBusinessTaxesInterest[2] + $qtrCommunityTaxCorporation[2] + $qtrCommunityTaxIndividual[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format($qtrAmusementTaxTotal + $qtrBusinessTaxesRetailersTotal + $qtrBusinessTaxesContractorsTotal + $qtrBusinessTaxesBanksTotal + $qtrBusinessTaxesInterestTotal + $qtrCommunityTaxCorporationTotal + $qtrCommunityTaxIndividualTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Amusement Tax (Provincial Account)</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrAmusementTax[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrAmusementTax[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrAmusementTax[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrAmusementTaxTotal, 2); ?></td>
                </tr>
                <tr class="bg-gray-100 font-bold">
                    <td class="border border-gray-800 p-2">Business Taxes</td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrBusinessTaxesRetailers[0] + $qtrBusinessTaxesContractors[0] + $qtrBusinessTaxesBanks[0] + $qtrBusinessTaxesInterest[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrBusinessTaxesRetailers[1] + $qtrBusinessTaxesContractors[1] + $qtrBusinessTaxesBanks[1] + $qtrBusinessTaxesInterest[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrBusinessTaxesRetailers[2] + $qtrBusinessTaxesContractors[2] + $qtrBusinessTaxesBanks[2] + $qtrBusinessTaxesInterest[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrBusinessTaxesRetailersTotal + $qtrBusinessTaxesContractorsTotal + $qtrBusinessTaxesBanksTotal + $qtrBusinessTaxesInterestTotal, 2); ?></td>
                </tr>
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Retailers</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrBusinessTaxesRetailers[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrBusinessTaxesRetailers[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrBusinessTaxesRetailers[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrBusinessTaxesRetailersTotal, 2); ?></td>
                </tr>
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Contractors</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrBusinessTaxesContractors[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrBusinessTaxesContractors[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrBusinessTaxesContractors[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrBusinessTaxesContractorsTotal, 2); ?></td>
                </tr>
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Banks & Other Financial Institutions</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrBusinessTaxesBanks[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrBusinessTaxesBanks[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrBusinessTaxesBanks[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrBusinessTaxesBanksTotal, 2); ?></td>
                </tr>
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Interest / Surcharge</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrBusinessTaxesInterest[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrBusinessTaxesInterest[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrBusinessTaxesInterest[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrBusinessTaxesInterestTotal, 2); ?></td>
                </tr>

                <!-- OTHER TAXES -->
                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">OTHER TAXES:</td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format($qtrCommunityTaxCorporation[0] + $qtrCommunityTaxIndividual[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format($qtrCommunityTaxCorporation[1] + $qtrCommunityTaxIndividual[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format($qtrCommunityTaxCorporation[2] + $qtrCommunityTaxIndividual[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format($qtrCommunityTaxCorporationTotal + $qtrCommunityTaxIndividualTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Community Tax - Corporation</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrCommunityTaxCorporation[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrCommunityTaxCorporation[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrCommunityTaxCorporation[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrCommunityTaxCorporationTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Community Tax - Individual</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrCommunityTaxIndividual[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrCommunityTaxIndividual[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrCommunityTaxIndividual[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrCommunityTaxIndividualTotal, 2); ?></td>
                </tr>

                <!-- REGULATORY FEES -->
                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">REGULATORY FEES (PERMITS & LICENSES)</td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format($qtrWeightMeasure[0] + $qtrFranchisingLicensing[0] + $qtrMayorsPermit[0] + $qtrBurialPermit[0] + $qtrSanitaryPermit[0] + $qtrMarriageLicense[0] + $qtrMarriageApplication[0] + $qtrBuildingPermitFee[0] + $qtrZoningClearance[0] + $qtrCattleAnimalRegistration[0] + $qtrBirthCertNewBorn[0] + $qtrInspectionFee[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format($qtrWeightMeasure[1] + $qtrFranchisingLicensing[1] + $qtrMayorsPermit[1] + $qtrBurialPermit[1] + $qtrSanitaryPermit[1] + $qtrMarriageLicense[1] + $qtrMarriageApplication[1] + $qtrBuildingPermitFee[1] + $qtrZoningClearance[1] + $qtrCattleAnimalRegistration[1] + $qtrBirthCertNewBorn[1] + $qtrInspectionFee[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format($qtrWeightMeasure[2] + $qtrFranchisingLicensing[2] + $qtrMayorsPermit[2] + $qtrBurialPermit[2] + $qtrSanitaryPermit[2] + $qtrMarriageLicense[2] + $qtrMarriageApplication[2] + $qtrBuildingPermitFee[2] + $qtrZoningClearance[2] + $qtrCattleAnimalRegistration[2] + $qtrBirthCertNewBorn[2] + $qtrInspectionFee[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format($qtrWeightMeasureTotal + $qtrFranchisingLicensingTotal + $qtrMayorsPermitTotal + $qtrBurialPermitTotal + $qtrSanitaryPermitTotal + $qtrMarriageLicenseTotal + $qtrMarriageApplicationTotal + $qtrBuildingPermitFeeTotal + $qtrZoningClearanceTotal + $qtrCattleAnimalRegistrationTotal + $qtrBirthCertNewBornTotal + $qtrInspectionFeeTotal, 2); ?></td>
                </tr>
                <tr class="bg-gray-100 font-bold">
                    <td class="border border-gray-800 p-2">Permits and Licenses</td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrWeightMeasure[0] + $qtrFranchisingLicensing[0] + $qtrMayorsPermit[0] + $qtrBurialPermit[0] + $qtrSanitaryPermit[0] + $qtrBuildingPermitFee[0] + $qtrZoningClearance[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrWeightMeasure[1] + $qtrFranchisingLicensing[1] + $qtrMayorsPermit[1] + $qtrBurialPermit[1] + $qtrSanitaryPermit[1] + $qtrBuildingPermitFee[1] + $qtrZoningClearance[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrWeightMeasure[2] + $qtrFranchisingLicensing[2] + $qtrMayorsPermit[2] + $qtrBurialPermit[2] + $qtrSanitaryPermit[2] + $qtrBuildingPermitFee[2] + $qtrZoningClearance[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrWeightMeasureTotal + $qtrFranchisingLicensingTotal + $qtrMayorsPermitTotal + $qtrBurialPermitTotal + $qtrSanitaryPermitTotal + $qtrBuildingPermitFeeTotal + $qtrZoningClearanceTotal, 2); ?></td>
                </tr>
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Fees on weights & measures</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrWeightMeasure[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrWeightMeasure[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrWeightMeasure[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrWeightMeasureTotal, 2); ?></td>
                </tr>
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Franchising & licensing Fees</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrFranchisingLicensing[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrFranchisingLicensing[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrFranchisingLicensing[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrFranchisingLicensingTotal, 2); ?></td>
                </tr>
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Permit Fees/ Mayor's Permit Fees</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrMayorsPermit[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrMayorsPermit[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrMayorsPermit[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrMayorsPermitTotal, 2); ?></td>
                </tr>
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Building Permit Fees</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrBuildingPermitFee[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrBuildingPermitFee[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrBuildingPermitFee[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrBuildingPermitFeeTotal, 2); ?></td>
                </tr>
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Zonal/Location Permit Fees</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrZoningClearance[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrZoningClearance[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrZoningClearance[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrZoningClearanceTotal, 2); ?></td>
                </tr>
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Burial Permit Fees</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrBurialPermit[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrBurialPermit[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrBurialPermit[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrBurialPermitTotal, 2); ?></td>
                </tr>
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Sanitary Permit Fees</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrSanitaryPermit[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrSanitaryPermit[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrSanitaryPermit[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrSanitaryPermitTotal, 2); ?></td>
                </tr>


                <tr class="bg-gray-100 font-bold">
                    <td class="border border-gray-800 p-2">Other Permits & Licenses:</td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrMarriageLicense[0] + $qtrMarriageApplication[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrMarriageLicense[1] + $qtrMarriageApplication[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrMarriageLicense[2] + $qtrMarriageApplication[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrMarriageLicenseTotal + $qtrMarriageApplicationTotal, 2); ?></td>
                </tr>
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Marriage License</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrMarriageLicense[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrMarriageLicense[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrMarriageLicense[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrMarriageLicenseTotal, 2); ?></td>
                </tr>
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Application for Marriage</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrMarriageApplication[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrMarriageApplication[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrMarriageApplication[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrMarriageApplicationTotal, 2); ?></td>
                </tr>
                <tr class="bg-gray-100 font-bold">
                    <td class="border border-gray-800 p-2">Registration Fees:</td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrCattleAnimalRegistration[0] + $qtrBirthCertNewBorn[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrCattleAnimalRegistration[1] + $qtrBirthCertNewBorn[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrCattleAnimalRegistration[2] + $qtrBirthCertNewBorn[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrCattleAnimalRegistrationTotal + $qtrBirthCertNewBornTotal, 2); ?></td>
                </tr>
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Cattle/Animal Registration fees</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrCattleAnimalRegistration[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrCattleAnimalRegistration[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrCattleAnimalRegistration[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrCattleAnimalRegistrationTotal, 2); ?></td>
                </tr>
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Birth Certificate (New Born) Fees</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrBirthCertNewBorn[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrBirthCertNewBorn[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrBirthCertNewBorn[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrBirthCertNewBornTotal, 2); ?></td>
                </tr>

                <tr class="bg-gray-100 font-bold">
                    <td class="border border-gray-800 p-2">Inspection Fees</td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrInspectionFee[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrInspectionFee[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrInspectionFee[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrInspectionFeeTotal, 2); ?></td>
                </tr>


                <!-- SERVICE/USER CHARGES -->
                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">SERVICE/ USER CHARGES (Service Income)</td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format(
                                                                                $qtrPoliceClearance[0] + $qtrDeathCert[0] + $qtrBirthCert[0] +
                                                                                    $qtrMarriageCert[0] + $qtrAssesorCert[0] + $qtrMTOCert[0] +
                                                                                    $qtrHealthCert[0] + $qtrSecretaryFee[0] + $qtrOtherMayorClearance[0] + $qtrGarbageFees[0] +
                                                                                    $qtrMedicalDentalLab[0] + $qtrSolemnizationFee[0] + $qtrAnnotationFee[0] +
                                                                                $qtrMiscFee[0] + $qtrRA[0] + $qtrElectricalFee[0] + $qtrSTL[0] + $qtrBackfillingHauling[0] + $qtrBREQS[0] + $qtrInterestIncome[0] + $qtrMarilagEcopark[0],
                                                                                2
                                                                            ); ?></td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format(
                                                                                $qtrPoliceClearance[1] + $qtrDeathCert[1] + $qtrBirthCert[1] +
                                                                                    $qtrMarriageCert[1] + $qtrAssesorCert[1] + $qtrMTOCert[1] +
                                                                                    $qtrHealthCert[1] + $qtrSecretaryFee[1] + $qtrOtherMayorClearance[1] + $qtrGarbageFees[1] +
                                                                                    $qtrMedicalDentalLab[1] + $qtrSolemnizationFee[1] + $qtrAnnotationFee[1] +
                                                                                $qtrMiscFee[1] + $qtrRA[1] + $qtrElectricalFee[1] + $qtrSTL[1] + $qtrBackfillingHauling[1] + $qtrBREQS[1] + $qtrInterestIncome[1] + $qtrMarilagEcopark[1],
                                                                                2
                                                                            ); ?></td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format(
                                                                                $qtrPoliceClearance[2] + $qtrDeathCert[2] + $qtrBirthCert[2] +
                                                                                    $qtrMarriageCert[2] + $qtrAssesorCert[2] + $qtrMTOCert[2] +
                                                                                    $qtrHealthCert[2] + $qtrSecretaryFee[2] + $qtrOtherMayorClearance[2] + $qtrGarbageFees[2] +
                                                                                    $qtrMedicalDentalLab[2] + $qtrSolemnizationFee[2] + $qtrAnnotationFee[2] +
                                                                                $qtrMiscFee[2] + $qtrRA[2] + $qtrElectricalFee[2] + $qtrSTL[2] + $qtrBackfillingHauling[2] + $qtrBREQS[2] + $qtrInterestIncome[2] + $qtrMarilagEcopark[2],
                                                                                2
                                                                            ); ?></td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format(
                                                                                $qtrPoliceClearanceTotal + $qtrDeathCertTotal + $qtrBirthCertTotal +
                                                                                    $qtrMarriageCertTotal + $qtrAssesorCertTotal + $qtrMTOCertTotal +
                                                                                    $qtrHealthCertTotal + $qtrSecretaryFeeTotal + $qtrOtherMayorClearanceTotal + $qtrGarbageFeesTotal +
                                                                                    $qtrMedicalDentalLabTotal + $qtrSolemnizationFeeTotal + $qtrAnnotationFeeTotal +
                                                                                $qtrMiscFeeTotal + $qtrRATotal +  $qtrElectricalFeeTotal + $qtrSTLTotal + $qtrBackfillingHaulingTotal + $qtrBREQSTotal + $qtrInterestIncomeTotal + $qtrMarilagEcoparkTotal,
                                                                                2
                                                                            ); ?></td>
                </tr>
                <tr class="bg-gray-100 font-bold">
                    <td class="border border-gray-800 p-2">Clearance and Certification Fees</td>
                    <td class="border border-gray-800 p-2 text-green-600"></td>
                    <td class="border border-gray-800 p-2 text-green-600"></td>
                    <td class="border border-gray-800 p-2 text-green-600"></td>
                    <td class="border border-gray-800 p-2 text-green-600"></td>
                </tr>
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Police Clearance</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrPoliceClearance[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrPoliceClearance[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrPoliceClearance[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrPoliceClearanceTotal, 2); ?></td>
                </tr>

                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">Secretary Fees</td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrDeathCert[0] + $qtrBirthCert[0] +
                                                                                $qtrMarriageCert[0] + $qtrAssesorCert[0] + $qtrMTOCert[0] +
                                                                                $qtrHealthCert[0] + $qtrSecretaryFee[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrDeathCert[1] + $qtrBirthCert[1] +
                                                                                $qtrMarriageCert[1] + $qtrAssesorCert[1] + $qtrMTOCert[1] +
                                                                                $qtrHealthCert[1] + $qtrSecretaryFee[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrDeathCert[2] + $qtrBirthCert[2] +
                                                                                $qtrMarriageCert[2] + $qtrAssesorCert[2] + $qtrMTOCert[2] +
                                                                                $qtrHealthCert[2] + $qtrSecretaryFee[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrDeathCertTotal + $qtrBirthCertTotal +
                                                                                $qtrMarriageCertTotal + $qtrAssesorCertTotal + $qtrMTOCertTotal +
                                                                                $qtrHealthCertTotal + $qtrSecretaryFeeTotal, 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Death Certificate</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrDeathCert[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrDeathCert[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrDeathCert[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrDeathCertTotal, 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Birth Certificate</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrBirthCert[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrBirthCert[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrBirthCert[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrBirthCertTotal, 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Marriage Certificate</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrMarriageCert[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrMarriageCert[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrMarriageCert[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrMarriageCertTotal, 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Assesor's Certificate</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrAssesorCert[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrAssesorCert[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrAssesorCert[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrAssesorCertTotal, 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">MTO Certificate</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrMTOCert[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrMTOCert[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrMTOCert[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrMTOCertTotal, 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Health Certificate (Medical)</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrHealthCert[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrHealthCert[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrHealthCert[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrHealthCertTotal, 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Secretary Fees</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrSecretaryFee[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrSecretaryFee[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrSecretaryFee[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrSecretaryFeeTotal, 2); ?></td>
                </tr>

                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">Other Clearance & Cert./Mayor's Clearance</td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrOtherMayorClearance[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrOtherMayorClearance[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrOtherMayorClearance[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrOtherMayorClearanceTotal, 2); ?></td>
                </tr>
                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">Garbage Fees</td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrGarbageFees[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrGarbageFees[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrGarbageFees[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrGarbageFeesTotal, 2); ?></td>
                </tr>
                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">Medical, Dental & Laboratory Fees</td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrMedicalDentalLab[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrMedicalDentalLab[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrMedicalDentalLab[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrMedicalDentalLabTotal, 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2"></td>
                    <td class="border border-gray-800 p-2 text-right"></td>
                    <td class="border border-gray-800 p-2 text-right"></td>
                    <td class="border border-gray-800 p-2 text-right"></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"></td>
                </tr>

                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">Other Service Income</td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrSolemnizationFee[0] + $qtrAnnotationFee[0] +
                                                                                $qtrMiscFee[0] + $qtrRA[0] + $qtrElectricalFee[0] + $qtrSTL[0] + $qtrBackfillingHauling[0] + $qtrBREQS[0] + $qtrInterestIncome[0] + $qtrMarilagEcopark[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrSolemnizationFee[1] + $qtrAnnotationFee[1] +
                                                                                $qtrMiscFee[1] + $qtrRA[1] + $qtrElectricalFee[1] + $qtrSTL[1] + $qtrBackfillingHauling[1] + $qtrBREQS[1] + $qtrInterestIncome[1] + $qtrMarilagEcopark[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrSolemnizationFee[2] + $qtrAnnotationFee[2] +
                                                                                $qtrMiscFee[2] + $qtrRA[2] + $qtrElectricalFee[2] + $qtrSTL[2] + $qtrBackfillingHauling[2] + $qtrBREQS[2] + $qtrInterestIncome[2] + $qtrMarilagEcopark[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrSolemnizationFeeTotal + $qtrAnnotationFeeTotal +
                                                                                $qtrMiscFeeTotal + $qtrRATotal +  $qtrElectricalFeeTotal + $qtrSTLTotal + $qtrBackfillingHaulingTotal + $qtrBREQSTotal + $qtrInterestIncomeTotal + $qtrMarilagEcoparkTotal, 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Solemnization Fees</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrSolemnizationFee[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrSolemnizationFee[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrSolemnizationFee[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrSolemnizationFeeTotal, 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Electrical Fees</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrElectricalFee[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrElectricalFee[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrElectricalFee[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrElectricalFeeTotal, 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Annotation Fees</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrAnnotationFee[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrAnnotationFee[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrAnnotationFee[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrAnnotationFeeTotal, 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">STL</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrSTL[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrSTL[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrSTL[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrSTLTotal, 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Backfiling / Hauling</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrBackfillingHauling[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrBackfillingHauling[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrBackfillingHauling[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrBackfillingHaulingTotal, 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Miscellaneous Fees</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrMiscFee[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrMiscFee[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrMiscFee[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrMiscFeeTotal, 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">RA 9048, 10172 & 9255</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrRA[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrRA[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrRA[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrRATotal, 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">BREQS</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrBREQS[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrBREQS[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrBREQS[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrBREQSTotal, 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Interest Income</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrInterestIncome[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrInterestIncome[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrInterestIncome[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrInterestIncomeTotal, 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Marilag Ecopark</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrMarilagEcopark[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrMarilagEcopark[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrMarilagEcopark[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrMarilagEcoparkTotal, 2); ?></td>
                </tr>

                <!-- RECEIPTS FROM ECONOMIC ENTERPRISES -->
                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">RECEIPTS FROM ECONOMIC ENTERPRISES (BUSINESS INCOME)</td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format($qtrCemeteries[0] + $qtrMunicipalLot[0] + $qtrStallGoodwill[0] + $qtrStallRentals[0] + $qtrSlaughterHouseOperations[0] + $qtrWaterWorksRegistration[0] + $qtrWaterWorksMonthlyDues[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format($qtrCemeteries[1] + $qtrMunicipalLot[1] + $qtrStallGoodwill[1] + $qtrStallRentals[1] + $qtrSlaughterHouseOperations[1] + $qtrWaterWorksRegistration[1] + $qtrWaterWorksMonthlyDues[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format($qtrCemeteries[2] + $qtrMunicipalLot[2] + $qtrStallGoodwill[2] + $qtrStallRentals[2] + $qtrSlaughterHouseOperations[2] + $qtrWaterWorksRegistration[2] + $qtrWaterWorksMonthlyDues[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format($qtrCemeteriesTotal + $qtrMunicipalLotTotal + $qtrStallGoodwillTotal + $qtrStallRentalsTotal + $qtrSlaughterHouseOperationsTotal + $qtrWaterWorksRegistrationTotal + $qtrWaterWorksMonthlyDuesTotal, 2); ?></td>
                </tr>
                <tr class="bg-gray-100 font-bold">
                    <td class="border border-gray-800 p-2">Cemetery Operations:</td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrCemeteries[0] + $qtrMunicipalLot[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrCemeteries[1] + $qtrMunicipalLot[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrCemeteries[2] + $qtrMunicipalLot[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrCemeteriesTotal + $qtrMunicipalLotTotal, 2); ?></td>
                </tr>
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Cemeteries</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrCemeteries[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrCemeteries[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrCemeteries[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrCemeteriesTotal, 2); ?></td>
                </tr>
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Municipal Lot</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrMunicipalLot[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrMunicipalLot[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrMunicipalLot[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrMunicipalLotTotal, 2); ?></td>
                </tr>

                <tr class="bg-gray-100 font-bold">
                    <td class="border border-gray-800 p-2">Market Operations:</td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrStallGoodwill[0] + $qtrStallRentals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrStallGoodwill[1] + $qtrStallRentals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrStallGoodwill[2] + $qtrStallRentals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrStallGoodwillTotal + $qtrStallRentalsTotal, 2); ?></td>
                </tr>
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Stall Goodwill</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrStallGoodwill[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrStallGoodwill[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrStallGoodwill[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrStallGoodwillTotal, 2); ?></td>
                </tr>
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Stall Rentals</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrStallRentals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrStallRentals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrStallRentals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrStallRentalsTotal, 2); ?></td>
                </tr>

                <!-- ADD Slaughter House AND Water Works-->

                <tr class="bg-gray-100 font-bold">
                    <td class="border border-gray-800 p-2">Slaughter House Operations:</td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrSlaughterHouseOperations[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrSlaughterHouseOperations[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrSlaughterHouseOperations[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrSlaughterHouseOperationsTotal, 2); ?></td>
                </tr>

                <tr class="bg-gray-100 font-bold">
                    <td class="border border-gray-800 p-2">Water Works System Operations:</td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrWaterWorksRegistration[0] + $qtrWaterWorksMonthlyDues[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrWaterWorksRegistration[1] + $qtrWaterWorksMonthlyDues[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrWaterWorksRegistration[2] + $qtrWaterWorksMonthlyDues[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($qtrWaterWorksRegistrationTotal + $qtrWaterWorksMonthlyDuesTotal, 2); ?></td>
                </tr>
                
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Registration</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrWaterWorksRegistration[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrWaterWorksRegistration[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrWaterWorksRegistration[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrWaterWorksRegistrationTotal, 2); ?></td>
                </tr>
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Monthly dues</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrWaterWorksMonthlyDues[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrWaterWorksMonthlyDues[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrWaterWorksMonthlyDues[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrWaterWorksMonthlyDuesTotal, 2); ?></td>
                </tr>

                <!-- LOCAL SOURCES -->
                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">LOCAL SOURCES</td>
                    <td class="border border-gray-800 p-2 text-purple-600"><?php echo number_format(
                                                                                ($qtrAmusementTax[0] + $qtrBusinessTaxesRetailers[0] + $qtrBusinessTaxesContractors[0] + $qtrBusinessTaxesBanks[0] + $qtrBusinessTaxesInterest[0] + $qtrCommunityTaxCorporation[0] + $qtrCommunityTaxIndividual[0])
                                                                                + ($qtrWeightMeasure[0] + $qtrFranchisingLicensing[0] + $qtrMayorsPermit[0] + $qtrBurialPermit[0] + $qtrSanitaryPermit[0] + $qtrMarriageLicense[0] + $qtrMarriageApplication[0] + $qtrBuildingPermitFee[0] + $qtrZoningClearance[0] + $qtrCattleAnimalRegistration[0] + $qtrBirthCertNewBorn[0] + $qtrInspectionFee[0]) 
                                                                                + ($qtrPoliceClearance[0] + $qtrDeathCert[0] + $qtrBirthCert[0] +
                                                                                    $qtrMarriageCert[0] + $qtrAssesorCert[0] + $qtrMTOCert[0] +
                                                                                    $qtrHealthCert[0] + $qtrSecretaryFee[0] + $qtrOtherMayorClearance[0] + $qtrGarbageFees[0] +
                                                                                    $qtrMedicalDentalLab[0] + $qtrSolemnizationFee[0] + $qtrAnnotationFee[0] +
                                                                                $qtrMiscFee[0] + $qtrRA[0] + $qtrElectricalFee[0] + $qtrSTL[0] + $qtrBackfillingHauling[0] + $qtrBREQS[0] + $qtrInterestIncome[0] + $qtrMarilagEcopark[0])
                                                                                + ($qtrCemeteries[0] + $qtrMunicipalLot[0] + $qtrStallGoodwill[0] + $qtrStallRentals[0] + $qtrSlaughterHouseOperations[0] + $qtrWaterWorksRegistration[0] + $qtrWaterWorksMonthlyDues[0]),
                                                                                2
                                                                            ); ?></td>
                    <td class="border border-gray-800 p-2 text-purple-600"><?php echo number_format(
                                                                                ($qtrAmusementTax[1] + $qtrBusinessTaxesRetailers[1] + $qtrBusinessTaxesContractors[1] + $qtrBusinessTaxesBanks[1] + $qtrBusinessTaxesInterest[1] + $qtrCommunityTaxCorporation[1] + $qtrCommunityTaxIndividual[1])
                                                                                + ($qtrWeightMeasure[1] + $qtrFranchisingLicensing[1] + $qtrMayorsPermit[1] + $qtrBurialPermit[1] + $qtrSanitaryPermit[1] + $qtrMarriageLicense[1] + $qtrMarriageApplication[1] + $qtrBuildingPermitFee[1] + $qtrZoningClearance[1] + $qtrCattleAnimalRegistration[1] + $qtrBirthCertNewBorn[1] + $qtrInspectionFee[1]) 
                                                                                + ($qtrPoliceClearance[1] + $qtrDeathCert[1] + $qtrBirthCert[1] +
                                                                                    $qtrMarriageCert[1] + $qtrAssesorCert[1] + $qtrMTOCert[1] +
                                                                                    $qtrHealthCert[1] + $qtrSecretaryFee[1] + $qtrOtherMayorClearance[1] + $qtrGarbageFees[1] +
                                                                                    $qtrMedicalDentalLab[1] + $qtrSolemnizationFee[1] + $qtrAnnotationFee[1] +
                                                                                $qtrMiscFee[1] + $qtrRA[1] + $qtrElectricalFee[1] + $qtrSTL[1] + $qtrBackfillingHauling[1] + $qtrBREQS[1] + $qtrInterestIncome[1] + $qtrMarilagEcopark[1])
                                                                                + ($qtrCemeteries[1] + $qtrMunicipalLot[1] + $qtrStallGoodwill[1] + $qtrStallRentals[1] + $qtrSlaughterHouseOperations[1] + $qtrWaterWorksRegistration[1] + $qtrWaterWorksMonthlyDues[1]),
                                                                                2
                                                                            ); ?></td>
                    <td class="border border-gray-800 p-2 text-purple-600"><?php echo number_format(
                                                                                ($qtrAmusementTax[2] + $qtrBusinessTaxesRetailers[2] + $qtrBusinessTaxesContractors[2] + $qtrBusinessTaxesBanks[2] + $qtrBusinessTaxesInterest[2] + $qtrCommunityTaxCorporation[2] + $qtrCommunityTaxIndividual[2])
                                                                                + ($qtrWeightMeasure[2] + $qtrFranchisingLicensing[2] + $qtrMayorsPermit[2] + $qtrBurialPermit[2] + $qtrSanitaryPermit[2] + $qtrMarriageLicense[2] + $qtrMarriageApplication[2] + $qtrBuildingPermitFee[2] + $qtrZoningClearance[2] + $qtrCattleAnimalRegistration[2] + $qtrBirthCertNewBorn[2] + $qtrInspectionFee[2]) 
                                                                                + ($qtrPoliceClearance[2] + $qtrDeathCert[2] + $qtrBirthCert[2] +
                                                                                    $qtrMarriageCert[2] + $qtrAssesorCert[2] + $qtrMTOCert[2] +
                                                                                    $qtrHealthCert[2] + $qtrSecretaryFee[2] + $qtrOtherMayorClearance[2] + $qtrGarbageFees[2] +
                                                                                    $qtrMedicalDentalLab[2] + $qtrSolemnizationFee[2] + $qtrAnnotationFee[2] +
                                                                                $qtrMiscFee[2] + $qtrRA[2] + $qtrElectricalFee[2] + $qtrSTL[2] + $qtrBackfillingHauling[2] + $qtrBREQS[2] + $qtrInterestIncome[2] + $qtrMarilagEcopark[2])
                                                                                + ($qtrCemeteries[2] + $qtrMunicipalLot[2] + $qtrStallGoodwill[2] + $qtrStallRentals[2] + $qtrSlaughterHouseOperations[2] + $qtrWaterWorksRegistration[2] + $qtrWaterWorksMonthlyDues[2]),
                                                                                2
                                                                            ); ?></td>
                    <td class="border border-gray-800 p-2 text-purple-600"><?php echo number_format(
                                                                                ($qtrAmusementTaxTotal + $qtrBusinessTaxesRetailersTotal + $qtrBusinessTaxesContractorsTotal + $qtrBusinessTaxesBanksTotal + $qtrBusinessTaxesInterestTotal + $qtrCommunityTaxCorporationTotal + $qtrCommunityTaxIndividualTotal)
                                                                                + ($qtrWeightMeasureTotal + $qtrFranchisingLicensingTotal + $qtrMayorsPermitTotal + $qtrBurialPermitTotal + $qtrSanitaryPermitTotal + $qtrMarriageLicenseTotal + $qtrMarriageApplicationTotal + $qtrBuildingPermitFeeTotal + $qtrZoningClearanceTotal + $qtrCattleAnimalRegistrationTotal + $qtrBirthCertNewBornTotal + $qtrInspectionFeeTotal)
                                                                                + ($qtrPoliceClearanceTotal + $qtrDeathCertTotal + $qtrBirthCertTotal +
                                                                                    $qtrMarriageCertTotal + $qtrAssesorCertTotal + $qtrMTOCertTotal +
                                                                                    $qtrHealthCertTotal + $qtrSecretaryFeeTotal + $qtrOtherMayorClearanceTotal + $qtrGarbageFeesTotal +
                                                                                    $qtrMedicalDentalLabTotal + $qtrSolemnizationFeeTotal + $qtrAnnotationFeeTotal +
                                                                                $qtrMiscFeeTotal + $qtrRATotal +  $qtrElectricalFeeTotal + $qtrSTLTotal + $qtrBackfillingHaulingTotal + $qtrBREQSTotal + $qtrInterestIncomeTotal + $qtrMarilagEcoparkTotal)
                                                                                + ($qtrCemeteriesTotal + $qtrMunicipalLotTotal + $qtrStallGoodwillTotal + $qtrStallRentalsTotal + $qtrSlaughterHouseOperationsTotal + $qtrWaterWorksRegistrationTotal + $qtrWaterWorksMonthlyDuesTotal),
                                                                                2
                                                                            ); ?></td>
                </tr>

                <!-- OTHER INCOME -->
                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">OTHER INCOME/RECEIPTS (OTHER GENERAL INCOME)</td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format($qtrOthersFee[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format($qtrOthersFee[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format($qtrOthersFee[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format($qtrOthersFeeTotal, 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">IRA</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrOthersFee[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrOthersFee[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($qtrOthersFee[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format($qtrOthersFeeTotal, 2); ?></td>
                </tr>

                <!-- GRAND TOTAL -->
                <tr class="bg-gray-300 font-bold">
                    <td class="border border-gray-800 p-2">GRAND TOTAL</td>
                    <td class="border border-gray-800 p-2 text-amber-800"><?php echo number_format(
                                                                                ($qtrAmusementTax[0] + $qtrBusinessTaxesRetailers[0] + $qtrBusinessTaxesContractors[0] + $qtrBusinessTaxesBanks[0] + $qtrBusinessTaxesInterest[0] + $qtrCommunityTaxCorporation[0] + $qtrCommunityTaxIndividual[0])
                                                                                + ($qtrWeightMeasure[0] + $qtrFranchisingLicensing[0] + $qtrMayorsPermit[0] + $qtrBurialPermit[0] + $qtrSanitaryPermit[0] + $qtrMarriageLicense[0] + $qtrMarriageApplication[0] + $qtrBuildingPermitFee[0] + $qtrZoningClearance[0] + $qtrCattleAnimalRegistration[0] + $qtrBirthCertNewBorn[0] + $qtrInspectionFee[0]) 
                                                                                + ($qtrPoliceClearance[0] + $qtrDeathCert[0] + $qtrBirthCert[0] +
                                                                                    $qtrMarriageCert[0] + $qtrAssesorCert[0] + $qtrMTOCert[0] +
                                                                                    $qtrHealthCert[0] + $qtrSecretaryFee[0] + $qtrOtherMayorClearance[0] + $qtrGarbageFees[0] +
                                                                                    $qtrMedicalDentalLab[0] + $qtrSolemnizationFee[0] + $qtrAnnotationFee[0] +
                                                                                $qtrMiscFee[0] + $qtrRA[0] + $qtrElectricalFee[0] + $qtrSTL[0] + $qtrBackfillingHauling[0] + $qtrBREQS[0] + $qtrInterestIncome[0] + $qtrMarilagEcopark[0])
                                                                                + ($qtrCemeteries[0] + $qtrMunicipalLot[0] + $qtrStallGoodwill[0] + $qtrStallRentals[0] + $qtrSlaughterHouseOperations[0] + $qtrWaterWorksRegistration[0] + $qtrWaterWorksMonthlyDues[0]) + $qtrOthersFee[0],
                                                                                2
                                                                            ); ?></td>
                    <td class="border border-gray-800 p-2 text-amber-800"><?php echo number_format(
                                                                                ($qtrAmusementTax[1] + $qtrBusinessTaxesRetailers[1] + $qtrBusinessTaxesContractors[1] + $qtrBusinessTaxesBanks[1] + $qtrBusinessTaxesInterest[1] + $qtrCommunityTaxCorporation[1] + $qtrCommunityTaxIndividual[1])
                                                                                + ($qtrWeightMeasure[1] + $qtrFranchisingLicensing[1] + $qtrMayorsPermit[1] + $qtrBurialPermit[1] + $qtrSanitaryPermit[1] + $qtrMarriageLicense[1] + $qtrMarriageApplication[1] + $qtrBuildingPermitFee[1] + $qtrZoningClearance[1] + $qtrCattleAnimalRegistration[1] + $qtrBirthCertNewBorn[1] + $qtrInspectionFee[1]) 
                                                                                + ($qtrPoliceClearance[1] + $qtrDeathCert[1] + $qtrBirthCert[1] +
                                                                                    $qtrMarriageCert[1] + $qtrAssesorCert[1] + $qtrMTOCert[1] +
                                                                                    $qtrHealthCert[1] + $qtrSecretaryFee[1] + $qtrOtherMayorClearance[1] + $qtrGarbageFees[1] +
                                                                                    $qtrMedicalDentalLab[1] + $qtrSolemnizationFee[1] + $qtrAnnotationFee[1] +
                                                                                $qtrMiscFee[1] + $qtrRA[1] + $qtrElectricalFee[1] + $qtrSTL[1] + $qtrBackfillingHauling[1] + $qtrBREQS[1] + $qtrInterestIncome[1] + $qtrMarilagEcopark[1])
                                                                                + ($qtrCemeteries[1] + $qtrMunicipalLot[1] + $qtrStallGoodwill[1] + $qtrStallRentals[1] + $qtrSlaughterHouseOperations[1] + $qtrWaterWorksRegistration[1] + $qtrWaterWorksMonthlyDues[1]) + $qtrOthersFee[1],
                                                                                2
                                                                            ); ?></td>
                    <td class="border border-gray-800 p-2 text-amber-800"><?php echo number_format(
                                                                                ($qtrAmusementTax[2] + $qtrBusinessTaxesRetailers[2] + $qtrBusinessTaxesContractors[2] + $qtrBusinessTaxesBanks[2] + $qtrBusinessTaxesInterest[2] + $qtrCommunityTaxCorporation[2] + $qtrCommunityTaxIndividual[2])
                                                                                + ($qtrWeightMeasure[2] + $qtrFranchisingLicensing[2] + $qtrMayorsPermit[2] + $qtrBurialPermit[2] + $qtrSanitaryPermit[2] + $qtrMarriageLicense[2] + $qtrMarriageApplication[2] + $qtrBuildingPermitFee[2] + $qtrZoningClearance[2] + $qtrCattleAnimalRegistration[2] + $qtrBirthCertNewBorn[2] + $qtrInspectionFee[2]) 
                                                                                + ($qtrPoliceClearance[2] + $qtrDeathCert[2] + $qtrBirthCert[2] +
                                                                                    $qtrMarriageCert[2] + $qtrAssesorCert[2] + $qtrMTOCert[2] +
                                                                                    $qtrHealthCert[2] + $qtrSecretaryFee[2] + $qtrOtherMayorClearance[2] + $qtrGarbageFees[2] +
                                                                                    $qtrMedicalDentalLab[2] + $qtrSolemnizationFee[2] + $qtrAnnotationFee[2] +
                                                                                $qtrMiscFee[2] + $qtrRA[2] + $qtrElectricalFee[2] + $qtrSTL[2] + $qtrBackfillingHauling[2] + $qtrBREQS[2] + $qtrInterestIncome[2] + $qtrMarilagEcopark[2])
                                                                                + ($qtrCemeteries[2] + $qtrMunicipalLot[2] + $qtrStallGoodwill[2] + $qtrStallRentals[2] + $qtrSlaughterHouseOperations[2] + $qtrWaterWorksRegistration[2] + $qtrWaterWorksMonthlyDues[2]) + $qtrOthersFee[2],
                                                                                2
                                                                            ); ?></td>
                    <td class="border border-gray-800 p-2 text-amber-800"><?php echo number_format(
                                                                                ($qtrAmusementTaxTotal + $qtrBusinessTaxesRetailersTotal + $qtrBusinessTaxesContractorsTotal + $qtrBusinessTaxesBanksTotal + $qtrBusinessTaxesInterestTotal + $qtrCommunityTaxCorporationTotal + $qtrCommunityTaxIndividualTotal)
                                                                                + ($qtrWeightMeasureTotal + $qtrFranchisingLicensingTotal + $qtrMayorsPermitTotal + $qtrBurialPermitTotal + $qtrSanitaryPermitTotal + $qtrMarriageLicenseTotal + $qtrMarriageApplicationTotal + $qtrBuildingPermitFeeTotal + $qtrZoningClearanceTotal + $qtrCattleAnimalRegistrationTotal + $qtrBirthCertNewBornTotal + $qtrInspectionFeeTotal)
                                                                                + ($qtrPoliceClearanceTotal + $qtrDeathCertTotal + $qtrBirthCertTotal +
                                                                                    $qtrMarriageCertTotal + $qtrAssesorCertTotal + $qtrMTOCertTotal +
                                                                                    $qtrHealthCertTotal + $qtrSecretaryFeeTotal + $qtrOtherMayorClearanceTotal + $qtrGarbageFeesTotal +
                                                                                    $qtrMedicalDentalLabTotal + $qtrSolemnizationFeeTotal + $qtrAnnotationFeeTotal +
                                                                                $qtrMiscFeeTotal + $qtrRATotal +  $qtrElectricalFeeTotal + $qtrSTLTotal + $qtrBackfillingHaulingTotal + $qtrBREQSTotal + $qtrInterestIncomeTotal + $qtrMarilagEcoparkTotal)
                                                                                + ($qtrCemeteriesTotal + $qtrMunicipalLotTotal + $qtrStallGoodwillTotal + $qtrStallRentalsTotal + $qtrSlaughterHouseOperationsTotal + $qtrWaterWorksRegistrationTotal + $qtrWaterWorksMonthlyDuesTotal) + $qtrOthersFeeTotal,
                                                                                2
                                                                            ); ?></td>                                                         
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