<?php
session_start();

$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

include '../backend/db_config_notpdo.php';
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Get all the quarterly totals from helper functions 
require_once 'yearly_report_helpers.php';

// Get quarterly totals for each category (similar to generate_yearly_report.php)
$yrWeightMeasureTotals = getWeightMeasureQuarterlyTotals($conn, $year);
$yrFranchisingLicensingTotals = getFranchisingLicensingQuarterlyTotals($conn, $year);
$yrMayorsPermitTotals = getMayorsPermitQuarterlyTotals($conn, $year);
$yrBurialPermitTotals = getBurialPermitQuarterlyTotals($conn, $year);
$yrSanitaryPermitTotals = getSanitaryPermitQuarterlyTotals($conn, $year);
$yrMarriageLicenseTotals = getMarriageLicenseQuarterlyTotals($conn, $year);
$yrMarriageApplicationTotals = getMarriageApplicationQuarterlyTotals($conn, $year);
$yrPoliceClearanceTotals = getPoliceClearanceQuarterlyTotals($conn, $year);
$yrDeathCertTotals = getDeathCertQuarterlyTotals($conn, $year);
$yrBirthCertTotals = getBirthCertQuarterlyTotals($conn, $year);
$yrMarriageCertTotals = getMarriageCertQuarterlyTotals($conn, $year);
$yrAssesorCertTotals = getAssesorCertQuarterlyTotals($conn, $year);
$yrMTOCertTotals = getMTOCertQuarterlyTotals($conn, $year);
$yrHealthCertTotals = getHealthCertQuarterlyTotals($conn, $year);
$yrSecretaryFeeTotals = getSecretaryFeeQuarterlyTotals($conn, $year);
$yrOtherMayorClearanceTotals = getOtherMayorClearanceQuarterlyTotals($conn, $year);
$yrMedicalDentalLabTotals = getMedicalDentalLabQuarterlyTotals($conn, $year);
$yrSolemnizationFeeTotals = getSolemnizationFeeQuarterlyTotals($conn, $year);
$yrAnnotationFeeTotals = getAnnotationFeeQuarterlyTotals($conn, $year);
$yrMiscFeeTotals = getMiscFeeQuarterlyTotals($conn, $year);
$yrRATotals = getRAQuarterlyTotals($conn, $year);
$yrCemeteriesTotals = getCemeteriesQuarterlyTotals($conn, $year);
$yrInspectionFeeTotals = getInspectionFeeQuarterlyTotals($conn, $year);
$yrElectricalFeeTotals = getElectricalFeeQuarterlyTotals($conn, $year);
$yrBuildingPermitFeeTotals = getBuildingPermitFeeQuarterlyTotals($conn, $year);
$yrZoningClearanceTotals = getZoningClearanceQuarterlyTotals($conn, $year);

$yrBirthCertNewBornTotals = getBirthCertificateNewBornQuarterlyTotals($conn, $year);
$yrAmusementTaxTotals = getAmusementTaxQuarterlyTotals($conn, $year);
$yrBusinessTaxesBanksTotals = getBusinessTaxesBanksQuarterlyTotals($conn, $year);
$yrBusinessTaxesContractorsTotals = getBusinessTaxesContractorsQuarterlyTotals($conn, $year);
$yrBusinessTaxesRetailersTotals = getBusinessTaxesRetailersQuarterlyTotals($conn, $year);
$yrCommunityTaxCorporationTotals = getCommunityTaxCorporationQuarterlyTotals($conn, $year);
$yrCommunityTaxIndividualTotals = getCommunityTaxIndividualQuarterlyTotals($conn, $year);
$yrCattleAnimalRegistrationTotals = getCattleAnimalRegistrationQuarterlyTotals($conn, $year);
$yrGarbageFeesTotals = getGarbageFeesQuarterlyTotals($conn, $year);
$yrInterestIncomeTotals = getInterestIncomeQuarterlyTotals($conn, $year);
$yrMarilagEcoparkTotals = getMarilagEcoparkQuarterlyTotals($conn, $year);
$yrMunicipalLotTotals = getMunicipalLotQuarterlyTotals($conn, $year);
$yrSlaughterHouseTotals = getSlaughterHouseOperationsQuarterlyTotals($conn, $year);
$yrStallGoodwillTotals = getStallGoodwillQuarterlyTotals($conn, $year);
$yrStallRentalsTotals = getStallRentalsQuarterlyTotals($conn, $year);
$yrSTLTotals = getSTLQuarterlyTotals($conn, $year);
$yrWaterWorksMonthlyDuesTotals = getWaterWorksMonthlyDuesQuarterlyTotals($conn, $year);
$yrWaterWorksRegistrationTotals = getWaterWorksRegistrationQuarterlyTotals($conn, $year);
$yrInterestSurchargeTotals = getInterestSurchargeQuarterlyTotals($conn, $year);
$yrBREQSTotals = getBREQSQuarterlyTotals($conn, $year);
$yrBackfillingHaulingTotals = getBackfillingHaulingQuarterlyTotals($conn, $year);
$yrOthersFeeTotals = getOthersFeeQuarterlyTotals($conn, $year);



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
    <title>Yearly Report Preview</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Remove the existing CSS styles and replace with Tailwind classes */
    </style>
</head>

<body class="bg-white p-5">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-6 leading-tight">
            <p class="font-semibold text-[14px]">Republic of the Philippines</p>
            <p class="font-semibold text-[14px]">Province of Laguna</p>
            <p class="font-semibold text-[15px]"><?php echo $orgName; ?></p>
            <p class="italic font-semibold text-[14px]">ANNUAL REPORT OF REVENUE AND RECEIPTS</p>
            <p class="text-[13px]">For Year <?php echo $year; ?></p>
            <p class="text-left font-bold my-4 text-[13px]">Treasurer: <?php echo $treasurerName; ?></p>
        </div>

        <table class="w-full border-collapse border border-gray-800 mb-8 text-xs">
            <thead>
                <tr class="bg-gray-100 font-bold text-center">
                    <th class="w-[30%] border border-gray-800 p-2">REVENUE SOURCES</th>
                    <th class="w-[14%] border border-gray-800 p-2">1ST QUARTER</th>
                    <th class="w-[14%] border border-gray-800 p-2">2ND QUARTER</th>
                    <th class="w-[14%] border border-gray-800 p-2">3RD QUARTER</th>
                    <th class="w-[14%] border border-gray-800 p-2">4TH QUARTER</th>
                    <th class="w-[14%] border border-gray-800 p-2">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <!-- TAX ON BUSINESS -->
                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">TAX ON BUSINESS</td>
                    <td class="border border-gray-800 p-2 text-green-600">
                        <?php echo number_format($yrAmusementTaxTotals[0] + $yrBusinessTaxesRetailersTotals[0] + $yrBusinessTaxesContractorsTotals[0] + $yrBusinessTaxesBanksTotals[0] + $yrInterestSurchargeTotals[0] + $yrCommunityTaxCorporationTotals[0] + $yrCommunityTaxIndividualTotals[0], 2); ?>
                    </td>
                    <td class="border border-gray-800 p-2 text-green-600">
                        <?php echo number_format($yrAmusementTaxTotals[1] + $yrBusinessTaxesRetailersTotals[1] + $yrBusinessTaxesContractorsTotals[1] + $yrBusinessTaxesBanksTotals[1] + $yrInterestSurchargeTotals[1] + $yrCommunityTaxCorporationTotals[1] + $yrCommunityTaxIndividualTotals[1], 2); ?>
                    </td>
                    <td class="border border-gray-800 p-2 text-green-600">
                        <?php echo number_format($yrAmusementTaxTotals[2] + $yrBusinessTaxesRetailersTotals[2] + $yrBusinessTaxesContractorsTotals[2] + $yrBusinessTaxesBanksTotals[2] + $yrInterestSurchargeTotals[2] + $yrCommunityTaxCorporationTotals[2] + $yrCommunityTaxIndividualTotals[2], 2); ?>
                    </td>
                    <td class="border border-gray-800 p-2 text-green-600">
                        <?php echo number_format($yrAmusementTaxTotals[3] + $yrBusinessTaxesRetailersTotals[3] + $yrBusinessTaxesContractorsTotals[3] + $yrBusinessTaxesBanksTotals[3] + $yrInterestSurchargeTotals[3] + $yrCommunityTaxCorporationTotals[3] + $yrCommunityTaxIndividualTotals[3], 2); ?>
                    </td>
                    <td class="border border-gray-800 p-2 text-green-600">
                        <?php echo number_format(
                            array_sum($yrAmusementTaxTotals) +
                                array_sum($yrBusinessTaxesRetailersTotals) +
                                array_sum($yrBusinessTaxesContractorsTotals) +
                                array_sum($yrBusinessTaxesBanksTotals) +
                                array_sum($yrInterestSurchargeTotals) +
                                array_sum($yrCommunityTaxCorporationTotals) +
                                array_sum($yrCommunityTaxIndividualTotals),
                            2
                        ); ?>
                    </td>

                </tr>
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Amusement Tax (Provincial Account)</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrAmusementTaxTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrAmusementTaxTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrAmusementTaxTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrAmusementTaxTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrAmusementTaxTotals), 2); ?></td>
                </tr>

                <tr class="bg-gray-100 font-bold">
                    <td class="border border-gray-800 p-2">Business Taxes</td>
                    <td class="border border-gray-800 p-2 text-blue-600">
                        <?php echo number_format($yrBusinessTaxesRetailersTotals[0] + $yrBusinessTaxesContractorsTotals[0] + $yrBusinessTaxesBanksTotals[0] + $yrInterestSurchargeTotals[0], 2); ?>
                    </td>
                    <td class="border border-gray-800 p-2 text-blue-600">
                        <?php echo number_format($yrBusinessTaxesRetailersTotals[1] + $yrBusinessTaxesContractorsTotals[1] + $yrBusinessTaxesBanksTotals[1] + $yrInterestSurchargeTotals[1], 2); ?>
                    </td>
                    <td class="border border-gray-800 p-2 text-blue-600">
                        <?php echo number_format($yrBusinessTaxesRetailersTotals[2] + $yrBusinessTaxesContractorsTotals[2] + $yrBusinessTaxesBanksTotals[2] + $yrInterestSurchargeTotals[2], 2); ?>
                    </td>
                    <td class="border border-gray-800 p-2 text-blue-600">
                        <?php echo number_format($yrBusinessTaxesRetailersTotals[3] + $yrBusinessTaxesContractorsTotals[3] + $yrBusinessTaxesBanksTotals[3] + $yrInterestSurchargeTotals[3], 2); ?>
                    </td>
                    <td class="border border-gray-800 p-2 text-blue-600 font-bold">
                        <?php echo number_format(
                            array_sum($yrBusinessTaxesRetailersTotals) +
                                array_sum($yrBusinessTaxesContractorsTotals) +
                                array_sum($yrBusinessTaxesBanksTotals) +
                                array_sum($yrInterestSurchargeTotals),
                            2
                        ); ?>
                    </td>
                </tr>
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Retailers</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBusinessTaxesRetailersTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBusinessTaxesRetailersTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBusinessTaxesRetailersTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBusinessTaxesRetailersTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrBusinessTaxesRetailersTotals), 2); ?></td>
                </tr>
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Contractors</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBusinessTaxesContractorsTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBusinessTaxesContractorsTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBusinessTaxesContractorsTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBusinessTaxesContractorsTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrBusinessTaxesContractorsTotals), 2); ?></td>
                </tr>
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Banks & Other Financial Institutions</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBusinessTaxesBanksTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBusinessTaxesBanksTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBusinessTaxesBanksTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBusinessTaxesBanksTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrBusinessTaxesBanksTotals), 2); ?></td>
                </tr>
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Interest / Surcharge</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrInterestSurchargeTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrInterestSurchargeTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrInterestSurchargeTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrInterestSurchargeTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrInterestSurchargeTotals), 2); ?></td>
                </tr>


                <!-- OTHER TAXES -->
                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">OTHER TAXES:</td>
                    <td class="border border-gray-800 p-2 text-green-600">
                        <?php echo number_format($yrCommunityTaxCorporationTotals[0] + $yrCommunityTaxIndividualTotals[0], 2); ?>
                    </td>
                    <td class="border border-gray-800 p-2 text-green-600">
                        <?php echo number_format($yrCommunityTaxCorporationTotals[1] + $yrCommunityTaxIndividualTotals[1], 2); ?>
                    </td>
                    <td class="border border-gray-800 p-2 text-green-600">
                        <?php echo number_format($yrCommunityTaxCorporationTotals[2] + $yrCommunityTaxIndividualTotals[2], 2); ?>
                    </td>
                    <td class="border border-gray-800 p-2 text-green-600">
                        <?php echo number_format($yrCommunityTaxCorporationTotals[3] + $yrCommunityTaxIndividualTotals[3], 2); ?>
                    </td>
                    <td class="border border-gray-800 p-2 text-green-600 font-bold">
                        <?php echo number_format(array_sum($yrCommunityTaxCorporationTotals) + array_sum($yrCommunityTaxIndividualTotals), 2); ?>
                    </td>
                </tr>

                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Community Tax - Corporation</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrCommunityTaxCorporationTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrCommunityTaxCorporationTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrCommunityTaxCorporationTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrCommunityTaxCorporationTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrCommunityTaxCorporationTotals), 2); ?></td>
                </tr>

                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Community Tax - Individual</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrCommunityTaxIndividualTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrCommunityTaxIndividualTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrCommunityTaxIndividualTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrCommunityTaxIndividualTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrCommunityTaxIndividualTotals), 2); ?></td>
                </tr>


                <!-- REGULATORY FEES -->
                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">REGULATORY FEES (PERMITS & LICENSES)</td>
                    <td class="border border-gray-800 p-2 text-green-600">
                        <?php echo number_format(
                            $yrWeightMeasureTotals[0] + $yrFranchisingLicensingTotals[0] + $yrMayorsPermitTotals[0] +
                                $yrBurialPermitTotals[0] + $yrSanitaryPermitTotals[0] + $yrMarriageLicenseTotals[0] +
                                $yrMarriageApplicationTotals[0] + $yrBuildingPermitFeeTotals[0] + $yrZoningClearanceTotals[0] +
                                $yrCattleAnimalRegistrationTotals[0] + $yrBirthCertNewBornTotals[0] + $yrInspectionFeeTotals[0],
                            2
                        ); ?>
                    </td>
                    <td class="border border-gray-800 p-2 text-green-600">
                        <?php echo number_format(
                            $yrWeightMeasureTotals[1] + $yrFranchisingLicensingTotals[1] + $yrMayorsPermitTotals[1] +
                                $yrBurialPermitTotals[1] + $yrSanitaryPermitTotals[1] + $yrMarriageLicenseTotals[1] +
                                $yrMarriageApplicationTotals[1] + $yrBuildingPermitFeeTotals[1] + $yrZoningClearanceTotals[1] +
                                $yrCattleAnimalRegistrationTotals[1] + $yrBirthCertNewBornTotals[1] + $yrInspectionFeeTotals[1],
                            2
                        ); ?>
                    </td>
                    <td class="border border-gray-800 p-2 text-green-600">
                        <?php echo number_format(
                            $yrWeightMeasureTotals[2] + $yrFranchisingLicensingTotals[2] + $yrMayorsPermitTotals[2] +
                                $yrBurialPermitTotals[2] + $yrSanitaryPermitTotals[2] + $yrMarriageLicenseTotals[2] +
                                $yrMarriageApplicationTotals[2] + $yrBuildingPermitFeeTotals[2] + $yrZoningClearanceTotals[2] +
                                $yrCattleAnimalRegistrationTotals[2] + $yrBirthCertNewBornTotals[2] + $yrInspectionFeeTotals[2],
                            2
                        ); ?>
                    </td>
                    <td class="border border-gray-800 p-2 text-green-600">
                        <?php echo number_format(
                            $yrWeightMeasureTotals[3] + $yrFranchisingLicensingTotals[3] + $yrMayorsPermitTotals[3] +
                                $yrBurialPermitTotals[3] + $yrSanitaryPermitTotals[3] + $yrMarriageLicenseTotals[3] +
                                $yrMarriageApplicationTotals[3] + $yrBuildingPermitFeeTotals[3] + $yrZoningClearanceTotals[3] +
                                $yrCattleAnimalRegistrationTotals[3] + $yrBirthCertNewBornTotals[3] + $yrInspectionFeeTotals[3],
                            2
                        ); ?>
                    </td>
                    <td class="border border-gray-800 p-2 text-green-600 font-bold">
                        <?php echo number_format(
                            array_sum($yrWeightMeasureTotals) + array_sum($yrFranchisingLicensingTotals) +
                                array_sum($yrMayorsPermitTotals) + array_sum($yrBurialPermitTotals) +
                                array_sum($yrSanitaryPermitTotals) + array_sum($yrMarriageLicenseTotals) +
                                array_sum($yrMarriageApplicationTotals) + array_sum($yrBuildingPermitFeeTotals) +
                                array_sum($yrZoningClearanceTotals) + array_sum($yrCattleAnimalRegistrationTotals) +
                                array_sum($yrBirthCertNewBornTotals) + array_sum($yrInspectionFeeTotals),
                            2
                        ); ?>
                    </td>
                </tr>

                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Fees on weights & measures</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrWeightMeasureTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrWeightMeasureTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrWeightMeasureTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrWeightMeasureTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrWeightMeasureTotals), 2); ?></td>
                </tr>

                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Franchising & licensing Fees</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrFranchisingLicensingTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrFranchisingLicensingTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrFranchisingLicensingTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrFranchisingLicensingTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrFranchisingLicensingTotals), 2); ?></td>
                </tr>

                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Permit Fees / Mayor's Permit Fees</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMayorsPermitTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMayorsPermitTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMayorsPermitTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMayorsPermitTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrMayorsPermitTotals), 2); ?></td>
                </tr>

                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Building Permit Fees</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBuildingPermitFeeTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBuildingPermitFeeTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBuildingPermitFeeTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBuildingPermitFeeTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrBuildingPermitFeeTotals), 2); ?></td>
                </tr>

                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Zonal / Location Permit Fees</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrZoningClearanceTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrZoningClearanceTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrZoningClearanceTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrZoningClearanceTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrZoningClearanceTotals), 2); ?></td>
                </tr>

                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Burial Permit Fees</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBurialPermitTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBurialPermitTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBurialPermitTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBurialPermitTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrBurialPermitTotals), 2); ?></td>
                </tr>

                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">Sanitary Permit Fees</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrSanitaryPermitTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrSanitaryPermitTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrSanitaryPermitTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrSanitaryPermitTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrSanitaryPermitTotals), 2); ?></td>
                </tr>

                <tr class="bg-gray-100 font-bold">
                    <td class="border border-gray-800 p-2">Other Permits & Licenses:</td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrMarriageLicenseTotals[0] + $yrMarriageApplicationTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrMarriageLicenseTotals[1] + $yrMarriageApplicationTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrMarriageLicenseTotals[2] + $yrMarriageApplicationTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrMarriageLicenseTotals[3] + $yrMarriageApplicationTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600 font-bold"><?php echo number_format(array_sum($yrMarriageLicenseTotals) + array_sum($yrMarriageApplicationTotals), 2); ?></td>
                </tr>
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Marriage License</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMarriageLicenseTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMarriageLicenseTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMarriageLicenseTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMarriageLicenseTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrMarriageLicenseTotals), 2); ?></td>
                </tr>
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Application for Marriage</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMarriageApplicationTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMarriageApplicationTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMarriageApplicationTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMarriageApplicationTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrMarriageApplicationTotals), 2); ?></td>
                </tr>

                <tr class="bg-gray-100 font-bold">
                    <td class="border border-gray-800 p-2">Registration Fees:</td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrCattleAnimalRegistrationTotals[0] + $yrBirthCertNewBornTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrCattleAnimalRegistrationTotals[1] + $yrBirthCertNewBornTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrCattleAnimalRegistrationTotals[2] + $yrBirthCertNewBornTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrCattleAnimalRegistrationTotals[3] + $yrBirthCertNewBornTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600 font-bold"><?php echo number_format(array_sum($yrCattleAnimalRegistrationTotals) + array_sum($yrBirthCertNewBornTotals), 2); ?></td>
                </tr>
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Cattle/Animal Registration Fees</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrCattleAnimalRegistrationTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrCattleAnimalRegistrationTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrCattleAnimalRegistrationTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrCattleAnimalRegistrationTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrCattleAnimalRegistrationTotals), 2); ?></td>
                </tr>
                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Birth Certificate (New Born) Fees</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBirthCertNewBornTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBirthCertNewBornTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBirthCertNewBornTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBirthCertNewBornTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrBirthCertNewBornTotals), 2); ?></td>
                </tr>

                <tr class="bg-gray-100 font-bold">
                    <td class="border border-gray-800 p-2">Inspection Fees</td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrInspectionFeeTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrInspectionFeeTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrInspectionFeeTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrInspectionFeeTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600 font-bold"><?php echo number_format(array_sum($yrInspectionFeeTotals), 2); ?></td>
                </tr>


                <!-- SERVICE/USER CHARGES -->
                <!-- REGULATORY FEES -->
                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">REGULATORY FEES (PERMITS & LICENSES)</td>
                    <td class="border border-gray-800 p-2 text-green-600">
                        <?php echo number_format(
                            $yrWeightMeasureTotals[0] + $yrFranchisingLicensingTotals[0] + $yrMayorsPermitTotals[0] +
                                $yrBurialPermitTotals[0] + $yrSanitaryPermitTotals[0] + $yrMarriageLicenseTotals[0] +
                                $yrMarriageApplicationTotals[0] + $yrBuildingPermitFeeTotals[0] + $yrZoningClearanceTotals[0] +
                                $yrCattleAnimalRegistrationTotals[0] + $yrBirthCertNewBornTotals[0] + $yrInspectionFeeTotals[0],
                            2
                        ); ?>
                    </td>
                    <td class="border border-gray-800 p-2 text-green-600">
                        <?php echo number_format(
                            $yrWeightMeasureTotals[1] + $yrFranchisingLicensingTotals[1] + $yrMayorsPermitTotals[1] +
                                $yrBurialPermitTotals[1] + $yrSanitaryPermitTotals[1] + $yrMarriageLicenseTotals[1] +
                                $yrMarriageApplicationTotals[1] + $yrBuildingPermitFeeTotals[1] + $yrZoningClearanceTotals[1] +
                                $yrCattleAnimalRegistrationTotals[1] + $yrBirthCertNewBornTotals[1] + $yrInspectionFeeTotals[1],
                            2
                        ); ?>
                    </td>
                    <td class="border border-gray-800 p-2 text-green-600">
                        <?php echo number_format(
                            $yrWeightMeasureTotals[2] + $yrFranchisingLicensingTotals[2] + $yrMayorsPermitTotals[2] +
                                $yrBurialPermitTotals[2] + $yrSanitaryPermitTotals[2] + $yrMarriageLicenseTotals[2] +
                                $yrMarriageApplicationTotals[2] + $yrBuildingPermitFeeTotals[2] + $yrZoningClearanceTotals[2] +
                                $yrCattleAnimalRegistrationTotals[2] + $yrBirthCertNewBornTotals[2] + $yrInspectionFeeTotals[2],
                            2
                        ); ?>
                    </td>
                    <td class="border border-gray-800 p-2 text-green-600">
                        <?php echo number_format(
                            $yrWeightMeasureTotals[3] + $yrFranchisingLicensingTotals[3] + $yrMayorsPermitTotals[3] +
                                $yrBurialPermitTotals[3] + $yrSanitaryPermitTotals[3] + $yrMarriageLicenseTotals[3] +
                                $yrMarriageApplicationTotals[3] + $yrBuildingPermitFeeTotals[3] + $yrZoningClearanceTotals[3] +
                                $yrCattleAnimalRegistrationTotals[3] + $yrBirthCertNewBornTotals[3] + $yrInspectionFeeTotals[3],
                            2
                        ); ?>
                    </td>
                    <td class="border border-gray-800 p-2 text-green-600 font-bold">
                        <?php echo number_format(
                            array_sum($yrWeightMeasureTotals) + array_sum($yrFranchisingLicensingTotals) +
                                array_sum($yrMayorsPermitTotals) + array_sum($yrBurialPermitTotals) +
                                array_sum($yrSanitaryPermitTotals) + array_sum($yrMarriageLicenseTotals) +
                                array_sum($yrMarriageApplicationTotals) + array_sum($yrBuildingPermitFeeTotals) +
                                array_sum($yrZoningClearanceTotals) + array_sum($yrCattleAnimalRegistrationTotals) +
                                array_sum($yrBirthCertNewBornTotals) + array_sum($yrInspectionFeeTotals),
                            2
                        ); ?>
                    </td>
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
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrPoliceClearanceTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrPoliceClearanceTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrPoliceClearanceTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrPoliceClearanceTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrPoliceClearanceTotals), 2); ?></td>
                </tr>


                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">SECRETARY FEES</td>
                    <td class="border border-gray-800 p-2 text-blue-600">
                        <?php echo number_format(
                            $yrDeathCertTotals[0] + $yrBirthCertTotals[0] + $yrMarriageCertTotals[0] +
                                $yrAssesorCertTotals[0] + $yrMTOCertTotals[0] + $yrHealthCertTotals[0] +
                                $yrSecretaryFeeTotals[0],
                            2
                        ); ?>
                    </td>
                    <td class="border border-gray-800 p-2 text-blue-600">
                        <?php echo number_format(
                            $yrDeathCertTotals[1] + $yrBirthCertTotals[1] + $yrMarriageCertTotals[1] +
                                $yrAssesorCertTotals[1] + $yrMTOCertTotals[1] + $yrHealthCertTotals[1] +
                                $yrSecretaryFeeTotals[1],
                            2
                        ); ?>
                    </td>
                    <td class="border border-gray-800 p-2 text-blue-600">
                        <?php echo number_format(
                            $yrDeathCertTotals[2] + $yrBirthCertTotals[2] + $yrMarriageCertTotals[2] +
                                $yrAssesorCertTotals[2] + $yrMTOCertTotals[2] + $yrHealthCertTotals[2] +
                                $yrSecretaryFeeTotals[2],
                            2
                        ); ?>
                    </td>
                    <td class="border border-gray-800 p-2 text-blue-600">
                        <?php echo number_format(
                            $yrDeathCertTotals[3] + $yrBirthCertTotals[3] + $yrMarriageCertTotals[3] +
                                $yrAssesorCertTotals[3] + $yrMTOCertTotals[3] + $yrHealthCertTotals[3] +
                                $yrSecretaryFeeTotals[3],
                            2
                        ); ?>
                    </td>
                    <td class="border border-gray-800 p-2 text-blue-600 font-bold">
                        <?php echo number_format(
                            array_sum($yrDeathCertTotals) + array_sum($yrBirthCertTotals) + array_sum($yrMarriageCertTotals) +
                                array_sum($yrAssesorCertTotals) + array_sum($yrMTOCertTotals) + array_sum($yrHealthCertTotals) +
                                array_sum($yrSecretaryFeeTotals),
                            2
                        ); ?>
                    </td>
                </tr>


                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Death Certificate</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrDeathCertTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrDeathCertTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrDeathCertTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrDeathCertTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrDeathCertTotals), 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Birth Certificate</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBirthCertTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBirthCertTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBirthCertTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBirthCertTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrBirthCertTotals), 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Marriage Certificate</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMarriageCertTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMarriageCertTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMarriageCertTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMarriageCertTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrMarriageCertTotals), 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Assessor's Certificate</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrAssesorCertTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrAssesorCertTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrAssesorCertTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrAssesorCertTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrAssesorCertTotals), 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">MTO Certificate</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMTOCertTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMTOCertTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMTOCertTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMTOCertTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrMTOCertTotals), 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Health Certificate (Medical)</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrHealthCertTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrHealthCertTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrHealthCertTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrHealthCertTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrHealthCertTotals), 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Secretary Fees</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrSecretaryFeeTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrSecretaryFeeTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrSecretaryFeeTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrSecretaryFeeTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrSecretaryFeeTotals), 2); ?></td>
                </tr>



                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">Other Clearance & Cert./Mayor's Clearance</td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrOtherMayorClearanceTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrOtherMayorClearanceTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrOtherMayorClearanceTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrOtherMayorClearanceTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format(array_sum($yrOtherMayorClearanceTotals), 2); ?></td>
                </tr>
                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">Garbage Fees</td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrGarbageFeesTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrGarbageFeesTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrGarbageFeesTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrGarbageFeesTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600 font-bold"><?php echo number_format(array_sum($yrGarbageFeesTotals), 2); ?></td>
                </tr>
                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">Medical, Dental & Laboratory Fees</td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrMedicalDentalLabTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrMedicalDentalLabTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrMedicalDentalLabTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrMedicalDentalLabTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format(array_sum($yrMedicalDentalLabTotals), 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2"></td>
                    <td class="border border-gray-800 p-2 text-right"></td>
                    <td class="border border-gray-800 p-2 text-right"></td>
                    <td class="border border-gray-800 p-2 text-right"></td>
                    <td class="border border-gray-800 p-2 text-right"></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"></td>
                </tr>

                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">Other Service Income</td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrSolemnizationFeeTotals[0] + $yrAnnotationFeeTotals[0] +
                                                                                $yrMiscFeeTotals[0] + $yrRATotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrSolemnizationFeeTotals[1] + $yrAnnotationFeeTotals[1] +
                                                                                $yrMiscFeeTotals[1] + $yrRATotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrSolemnizationFeeTotals[2] + $yrAnnotationFeeTotals[2] +
                                                                                $yrMiscFeeTotals[2] + $yrRATotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrSolemnizationFeeTotals[3] + $yrAnnotationFeeTotals[3] +
                                                                                $yrMiscFeeTotals[3] + $yrRATotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrSolemnizationFeeTotals[0] + $yrAnnotationFeeTotals[0] +
                                                                                $yrMiscFeeTotals[0] + $yrRATotals[0], 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Solemnization Fees</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrSolemnizationFeeTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrSolemnizationFeeTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrSolemnizationFeeTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrSolemnizationFeeTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrSolemnizationFeeTotals), 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Electrical Fees</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrElectricalFeeTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrElectricalFeeTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrElectricalFeeTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrElectricalFeeTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrElectricalFeeTotals), 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Annotation Fees</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrAnnotationFeeTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrAnnotationFeeTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrAnnotationFeeTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrAnnotationFeeTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrAnnotationFeeTotals), 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">STL</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrSTLTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrSTLTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrSTLTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrSTLTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrSTLTotals), 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Backfiling / Hauling</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBackfillingHaulingTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBackfillingHaulingTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBackfillingHaulingTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBackfillingHaulingTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrBackfillingHaulingTotals), 2); ?></td>
                </tr>


                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Miscellaneous Fees</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMiscFeeTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMiscFeeTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMiscFeeTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMiscFeeTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrMiscFeeTotals), 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">RA 9048, 10172 & 9255</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrRATotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrRATotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrRATotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrRATotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrRATotals), 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">BREQS</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBREQSTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBREQSTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBREQSTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrBREQSTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrBREQSTotals), 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Interest Income</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrInterestIncomeTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrInterestIncomeTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrInterestIncomeTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrInterestIncomeTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrInterestIncomeTotals), 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Marilag Ecopark</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMarilagEcoparkTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMarilagEcoparkTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMarilagEcoparkTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMarilagEcoparkTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrMarilagEcoparkTotals), 2); ?></td>
                </tr>


                <!-- RECEIPTS FROM ECONOMIC ENTERPRISES -->
                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">RECEIPTS FROM ECONOMIC ENTERPRISES (BUSINESS INCOME)</td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format($yrCemeteriesTotals[0] + $yrMunicipalLotTotals[0] + $yrStallGoodwillTotals[0] + $yrStallRentalsTotals[0] + $yrSlaughterHouseTotals[0] + $yrWaterWorksRegistrationTotals[0] + $yrWaterWorksMonthlyDuesTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format($yrCemeteriesTotals[1] + $yrMunicipalLotTotals[1] + $yrStallGoodwillTotals[1] + $yrStallRentalsTotals[1] + $yrSlaughterHouseTotals[1] + $yrWaterWorksRegistrationTotals[1] + $yrWaterWorksMonthlyDuesTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format($yrCemeteriesTotals[2] + $yrMunicipalLotTotals[2] + $yrStallGoodwillTotals[2] + $yrStallRentalsTotals[2] + $yrSlaughterHouseTotals[2] + $yrWaterWorksRegistrationTotals[2] + $yrWaterWorksMonthlyDuesTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format($yrCemeteriesTotals[3] + $yrMunicipalLotTotals[3] + $yrStallGoodwillTotals[3] + $yrStallRentalsTotals[3] + $yrSlaughterHouseTotals[3] + $yrWaterWorksRegistrationTotals[3] + $yrWaterWorksMonthlyDuesTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-green-600 font-bold"><?php echo number_format(array_sum($yrCemeteriesTotals) + array_sum($yrMunicipalLotTotals) + array_sum($yrStallGoodwillTotals) + array_sum($yrStallRentalsTotals) + array_sum($yrSlaughterHouseTotals) + array_sum($yrWaterWorksRegistrationTotals) + array_sum($yrWaterWorksMonthlyDuesTotals), 2); ?></td>
                </tr>

                <tr class="bg-gray-100 font-bold">
                    <td class="border border-gray-800 p-2">Cemetery Operations:</td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrCemeteriesTotals[0] + $yrMunicipalLotTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrCemeteriesTotals[1] + $yrMunicipalLotTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrCemeteriesTotals[2] + $yrMunicipalLotTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrCemeteriesTotals[3] + $yrMunicipalLotTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600 font-bold"><?php echo number_format(array_sum($yrCemeteriesTotals) + array_sum($yrMunicipalLotTotals), 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Cemeteries</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrCemeteriesTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrCemeteriesTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrCemeteriesTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrCemeteriesTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrCemeteriesTotals), 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Municipal Lot</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMunicipalLotTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMunicipalLotTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMunicipalLotTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrMunicipalLotTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrMunicipalLotTotals), 2); ?></td>
                </tr>

                <tr class="bg-gray-100 font-bold">
                    <td class="border border-gray-800 p-2">Market Operations:</td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrStallGoodwillTotals[0] + $yrStallRentalsTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrStallGoodwillTotals[1] + $yrStallRentalsTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrStallGoodwillTotals[2] + $yrStallRentalsTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrStallGoodwillTotals[3] + $yrStallRentalsTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600 font-bold"><?php echo number_format(array_sum($yrStallGoodwillTotals) + array_sum($yrStallRentalsTotals), 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Stall Goodwill</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrStallGoodwillTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrStallGoodwillTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrStallGoodwillTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrStallGoodwillTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrStallGoodwillTotals), 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Stall Rentals</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrStallRentalsTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrStallRentalsTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrStallRentalsTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrStallRentalsTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrStallRentalsTotals), 2); ?></td>
                </tr>

                <tr class="bg-gray-100 font-bold">
                    <td class="border border-gray-800 p-2">Slaughter House Operations:</td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrSlaughterHouseTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrSlaughterHouseTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrSlaughterHouseTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrSlaughterHouseTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600 font-bold"><?php echo number_format(array_sum($yrSlaughterHouseTotals), 2); ?></td>
                </tr>

                <tr class="bg-gray-100 font-bold">
                    <td class="border border-gray-800 p-2">Water Works System Operations:</td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrWaterWorksRegistrationTotals[0] + $yrWaterWorksMonthlyDuesTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrWaterWorksRegistrationTotals[1] + $yrWaterWorksMonthlyDuesTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrWaterWorksRegistrationTotals[2] + $yrWaterWorksMonthlyDuesTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600"><?php echo number_format($yrWaterWorksRegistrationTotals[3] + $yrWaterWorksMonthlyDuesTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-blue-600 font-bold"><?php echo number_format(array_sum($yrWaterWorksRegistrationTotals) + array_sum($yrWaterWorksMonthlyDuesTotals), 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Registration</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrWaterWorksRegistrationTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrWaterWorksRegistrationTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrWaterWorksRegistrationTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrWaterWorksRegistrationTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrWaterWorksRegistrationTotals), 2); ?></td>
                </tr>

                <tr class="pl-10">
                    <td class="border border-gray-800 p-2">Monthly Dues</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrWaterWorksMonthlyDuesTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrWaterWorksMonthlyDuesTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrWaterWorksMonthlyDuesTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrWaterWorksMonthlyDuesTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrWaterWorksMonthlyDuesTotals), 2); ?></td>
                </tr>



                <!-- LOCAL SOURCES -->
                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">LOCAL SOURCES</td>
                    <td class="border border-gray-800 p-2 text-purple-600"><?php echo number_format(
                                                                                ($yrAmusementTaxTotals[0] + $yrBusinessTaxesRetailersTotals[0] + $yrBusinessTaxesContractorsTotals[0] + $yrBusinessTaxesBanksTotals[0] + $yrInterestSurchargeTotals[0] + $yrCommunityTaxCorporationTotals[0] + $yrCommunityTaxIndividualTotals[0])
                                                                                    + ($yrWeightMeasureTotals[0] + $yrFranchisingLicensingTotals[0] + $yrMayorsPermitTotals[0] + $yrBurialPermitTotals[0] + $yrSanitaryPermitTotals[0] + $yrMarriageLicenseTotals[0] + $yrMarriageApplicationTotals[0] + $yrBuildingPermitFeeTotals[0] + $yrZoningClearanceTotals[0] + $yrCattleAnimalRegistrationTotals[0] + $yrBirthCertNewBornTotals[0] + $yrInspectionFeeTotals[0])
                                                                                    + ($yrPoliceClearanceTotals[0] + $yrDeathCertTotals[0] + $yrBirthCertTotals[0] + $yrMarriageCertTotals[0] + $yrAssesorCertTotals[0] + $yrMTOCertTotals[0] + $yrHealthCertTotals[0] + $yrSecretaryFeeTotals[0] + $yrOtherMayorClearanceTotals[0] + $yrGarbageFeesTotals[0] + $yrMedicalDentalLabTotals[0] + $yrSolemnizationFeeTotals[0] + $yrAnnotationFeeTotals[0] + $yrMiscFeeTotals[0] + $yrRATotals[0] + $yrElectricalFeeTotals[0] + $yrSTLTotals[0] + $yrBackfillingHaulingTotals[0] + $yrBREQSTotals[0] + $yrInterestIncomeTotals[0] + $yrMarilagEcoparkTotals[0])
                                                                                    + ($yrCemeteriesTotals[0] + $yrMunicipalLotTotals[0] + $yrStallGoodwillTotals[0] + $yrStallRentalsTotals[0] + $yrSlaughterHouseTotals[0] + $yrWaterWorksRegistrationTotals[0] + $yrWaterWorksMonthlyDuesTotals[0]),
                                                                                2
                                                                            ); ?></td>

                    <td class="border border-gray-800 p-2 text-purple-600"><?php echo number_format(
                                                                                ($yrAmusementTaxTotals[1] + $yrBusinessTaxesRetailersTotals[1] + $yrBusinessTaxesContractorsTotals[1] + $yrBusinessTaxesBanksTotals[1] + $yrInterestSurchargeTotals[1] + $yrCommunityTaxCorporationTotals[1] + $yrCommunityTaxIndividualTotals[1])
                                                                                    + ($yrWeightMeasureTotals[1] + $yrFranchisingLicensingTotals[1] + $yrMayorsPermitTotals[1] + $yrBurialPermitTotals[1] + $yrSanitaryPermitTotals[1] + $yrMarriageLicenseTotals[1] + $yrMarriageApplicationTotals[1] + $yrBuildingPermitFeeTotals[1] + $yrZoningClearanceTotals[1] + $yrCattleAnimalRegistrationTotals[1] + $yrBirthCertNewBornTotals[1] + $yrInspectionFeeTotals[1])
                                                                                    + ($yrPoliceClearanceTotals[1] + $yrDeathCertTotals[1] + $yrBirthCertTotals[1] + $yrMarriageCertTotals[1] + $yrAssesorCertTotals[1] + $yrMTOCertTotals[1] + $yrHealthCertTotals[1] + $yrSecretaryFeeTotals[1] + $yrOtherMayorClearanceTotals[1] + $yrGarbageFeesTotals[1] + $yrMedicalDentalLabTotals[1] + $yrSolemnizationFeeTotals[1] + $yrAnnotationFeeTotals[1] + $yrMiscFeeTotals[1] + $yrRATotals[1] + $yrElectricalFeeTotals[1] + $yrSTLTotals[1] + $yrBackfillingHaulingTotals[1] + $yrBREQSTotals[1] + $yrInterestIncomeTotals[1] + $yrMarilagEcoparkTotals[1])
                                                                                    + ($yrCemeteriesTotals[1] + $yrMunicipalLotTotals[1] + $yrStallGoodwillTotals[1] + $yrStallRentalsTotals[1] + $yrSlaughterHouseTotals[1] + $yrWaterWorksRegistrationTotals[1] + $yrWaterWorksMonthlyDuesTotals[1]),
                                                                                2
                                                                            ); ?></td>

                    <td class="border border-gray-800 p-2 text-purple-600"><?php echo number_format(
                                                                                ($yrAmusementTaxTotals[2] + $yrBusinessTaxesRetailersTotals[2] + $yrBusinessTaxesContractorsTotals[2] + $yrBusinessTaxesBanksTotals[2] + $yrInterestSurchargeTotals[2] + $yrCommunityTaxCorporationTotals[2] + $yrCommunityTaxIndividualTotals[2])
                                                                                    + ($yrWeightMeasureTotals[2] + $yrFranchisingLicensingTotals[2] + $yrMayorsPermitTotals[2] + $yrBurialPermitTotals[2] + $yrSanitaryPermitTotals[2] + $yrMarriageLicenseTotals[2] + $yrMarriageApplicationTotals[2] + $yrBuildingPermitFeeTotals[2] + $yrZoningClearanceTotals[2] + $yrCattleAnimalRegistrationTotals[2] + $yrBirthCertNewBornTotals[2] + $yrInspectionFeeTotals[2])
                                                                                    + ($yrPoliceClearanceTotals[2] + $yrDeathCertTotals[2] + $yrBirthCertTotals[2] + $yrMarriageCertTotals[2] + $yrAssesorCertTotals[2] + $yrMTOCertTotals[2] + $yrHealthCertTotals[2] + $yrSecretaryFeeTotals[2] + $yrOtherMayorClearanceTotals[2] + $yrGarbageFeesTotals[2] + $yrMedicalDentalLabTotals[2] + $yrSolemnizationFeeTotals[2] + $yrAnnotationFeeTotals[2] + $yrMiscFeeTotals[2] + $yrRATotals[2] + $yrElectricalFeeTotals[2] + $yrSTLTotals[2] + $yrBackfillingHaulingTotals[2] + $yrBREQSTotals[2] + $yrInterestIncomeTotals[2] + $yrMarilagEcoparkTotals[2])
                                                                                    + ($yrCemeteriesTotals[2] + $yrMunicipalLotTotals[2] + $yrStallGoodwillTotals[2] + $yrStallRentalsTotals[2] + $yrSlaughterHouseTotals[2] + $yrWaterWorksRegistrationTotals[2] + $yrWaterWorksMonthlyDuesTotals[2]),
                                                                                2
                                                                            ); ?></td>
                    <td class="border border-gray-800 p-2 text-purple-600"><?php echo number_format(
                                                                                ($yrAmusementTaxTotals[3] + $yrBusinessTaxesRetailersTotals[3] + $yrBusinessTaxesContractorsTotals[3] + $yrBusinessTaxesBanksTotals[3] + $yrInterestSurchargeTotals[3] + $yrCommunityTaxCorporationTotals[3] + $yrCommunityTaxIndividualTotals[3])
                                                                                    + ($yrWeightMeasureTotals[3] + $yrFranchisingLicensingTotals[3] + $yrMayorsPermitTotals[3] + $yrBurialPermitTotals[3] + $yrSanitaryPermitTotals[3] + $yrMarriageLicenseTotals[3] + $yrMarriageApplicationTotals[3] + $yrBuildingPermitFeeTotals[3] + $yrZoningClearanceTotals[3] + $yrCattleAnimalRegistrationTotals[3] + $yrBirthCertNewBornTotals[3] + $yrInspectionFeeTotals[3])
                                                                                    + ($yrPoliceClearanceTotals[3] + $yrDeathCertTotals[3] + $yrBirthCertTotals[3] + $yrMarriageCertTotals[3] + $yrAssesorCertTotals[3] + $yrMTOCertTotals[3] + $yrHealthCertTotals[3] + $yrSecretaryFeeTotals[3] + $yrOtherMayorClearanceTotals[3] + $yrGarbageFeesTotals[3] + $yrMedicalDentalLabTotals[3] + $yrSolemnizationFeeTotals[3] + $yrAnnotationFeeTotals[3] + $yrMiscFeeTotals[3] + $yrRATotals[3] + $yrElectricalFeeTotals[3] + $yrSTLTotals[3] + $yrBackfillingHaulingTotals[3] + $yrBREQSTotals[3] + $yrInterestIncomeTotals[3] + $yrMarilagEcoparkTotals[3])
                                                                                    + ($yrCemeteriesTotals[3] + $yrMunicipalLotTotals[3] + $yrStallGoodwillTotals[3] + $yrStallRentalsTotals[3] + $yrSlaughterHouseTotals[3] + $yrWaterWorksRegistrationTotals[3] + $yrWaterWorksMonthlyDuesTotals[3]),
                                                                                2
                                                                            ); ?></td>

                    <td class="border border-gray-800 p-2 text-purple-600">
                        <?php echo number_format(
                            (array_sum($yrAmusementTaxTotals) + array_sum($yrBusinessTaxesRetailersTotals) + array_sum($yrBusinessTaxesContractorsTotals) + array_sum($yrBusinessTaxesBanksTotals) + array_sum($yrInterestSurchargeTotals) + array_sum($yrCommunityTaxCorporationTotals) + array_sum($yrCommunityTaxIndividualTotals))
                                + (array_sum($yrWeightMeasureTotals) + array_sum($yrFranchisingLicensingTotals) + array_sum($yrMayorsPermitTotals) + array_sum($yrBurialPermitTotals) + array_sum($yrSanitaryPermitTotals) + array_sum($yrMarriageLicenseTotals) + array_sum($yrMarriageApplicationTotals) + array_sum($yrBuildingPermitFeeTotals) + array_sum($yrZoningClearanceTotals) + array_sum($yrCattleAnimalRegistrationTotals) + array_sum($yrBirthCertNewBornTotals) + array_sum($yrInspectionFeeTotals))
                                + (array_sum($yrPoliceClearanceTotals) + array_sum($yrDeathCertTotals) + array_sum($yrBirthCertTotals) + array_sum($yrMarriageCertTotals) + array_sum($yrAssesorCertTotals) + array_sum($yrMTOCertTotals) + array_sum($yrHealthCertTotals) + array_sum($yrSecretaryFeeTotals) + array_sum($yrOtherMayorClearanceTotals) + array_sum($yrGarbageFeesTotals) + array_sum($yrMedicalDentalLabTotals) + array_sum($yrSolemnizationFeeTotals) + array_sum($yrAnnotationFeeTotals) + array_sum($yrMiscFeeTotals) + array_sum($yrRATotals) + array_sum($yrElectricalFeeTotals) + array_sum($yrSTLTotals) + array_sum($yrBackfillingHaulingTotals) + array_sum($yrBREQSTotals) + array_sum($yrInterestIncomeTotals) + array_sum($yrMarilagEcoparkTotals))
                                + (array_sum($yrCemeteriesTotals) + array_sum($yrMunicipalLotTotals) + array_sum($yrStallGoodwillTotals) + array_sum($yrStallRentalsTotals) + array_sum($yrSlaughterHouseTotals) + array_sum($yrWaterWorksRegistrationTotals) + array_sum($yrWaterWorksMonthlyDuesTotals)),
                            2
                        ); ?>
                    </td>

                </tr>

                </tr>
                </tr>

                <!-- OTHER INCOME -->
                <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-800 p-2">OTHER INCOME/RECEIPTS (OTHER GENERAL INCOME)</td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format($yrOthersFeeTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format($yrOthersFeeTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format($yrOthersFeeTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format($yrOthersFeeTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-green-600"><?php echo number_format(array_sum($yrOthersFeeTotals), 2); ?></td>
                </tr>
                <tr class="pl-5">
                    <td class="border border-gray-800 p-2">IRA</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrOthersFeeTotals[0], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrOthersFeeTotals[1], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrOthersFeeTotals[2], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($yrOthersFeeTotals[3], 2); ?></td>
                    <td class="border border-gray-800 p-2 text-right font-bold"><?php echo number_format(array_sum($yrOthersFeeTotals), 2); ?></td>
                </tr>

                <!-- GRAND TOTAL -->
                <!-- GRAND TOTAL -->
                <tr class="bg-gray-300 font-bold">
                    <td class="border border-gray-800 p-2">GRAND TOTAL</td>

                    <td class="border border-gray-800 p-2 text-amber-800">
                        <?php echo number_format(
                            ($yrAmusementTaxTotals[0] + $yrBusinessTaxesRetailersTotals[0] + $yrBusinessTaxesContractorsTotals[0] + $yrBusinessTaxesBanksTotals[0] + $yrInterestSurchargeTotals[0] + $yrCommunityTaxCorporationTotals[0] + $yrCommunityTaxIndividualTotals[0])
                                + ($yrWeightMeasureTotals[0] + $yrFranchisingLicensingTotals[0] + $yrMayorsPermitTotals[0] + $yrBurialPermitTotals[0] + $yrSanitaryPermitTotals[0] + $yrMarriageLicenseTotals[0] + $yrMarriageApplicationTotals[0] + $yrBuildingPermitFeeTotals[0] + $yrZoningClearanceTotals[0] + $yrCattleAnimalRegistrationTotals[0] + $yrBirthCertNewBornTotals[0] + $yrInspectionFeeTotals[0])
                                + ($yrPoliceClearanceTotals[0] + $yrDeathCertTotals[0] + $yrBirthCertTotals[0] + $yrMarriageCertTotals[0] + $yrAssesorCertTotals[0] + $yrMTOCertTotals[0] + $yrHealthCertTotals[0] + $yrSecretaryFeeTotals[0] + $yrOtherMayorClearanceTotals[0] + $yrGarbageFeesTotals[0] + $yrMedicalDentalLabTotals[0] + $yrSolemnizationFeeTotals[0] + $yrAnnotationFeeTotals[0] + $yrMiscFeeTotals[0] + $yrRATotals[0] + $yrElectricalFeeTotals[0] + $yrSTLTotals[0] + $yrBackfillingHaulingTotals[0] + $yrBREQSTotals[0] + $yrInterestIncomeTotals[0] + $yrMarilagEcoparkTotals[0])
                                + ($yrCemeteriesTotals[0] + $yrMunicipalLotTotals[0] + $yrStallGoodwillTotals[0] + $yrStallRentalsTotals[0] + $yrSlaughterHouseTotals[0] + $yrWaterWorksRegistrationTotals[0] + $yrWaterWorksMonthlyDuesTotals[0]) + $yrOthersFeeTotals[0],
                            2
                        ); ?>
                    </td>

                    <td class="border border-gray-800 p-2 text-amber-800">
                        <?php echo number_format(
                            ($yrAmusementTaxTotals[1] + $yrBusinessTaxesRetailersTotals[1] + $yrBusinessTaxesContractorsTotals[1] + $yrBusinessTaxesBanksTotals[1] + $yrInterestSurchargeTotals[1] + $yrCommunityTaxCorporationTotals[1] + $yrCommunityTaxIndividualTotals[1])
                                + ($yrWeightMeasureTotals[1] + $yrFranchisingLicensingTotals[1] + $yrMayorsPermitTotals[1] + $yrBurialPermitTotals[1] + $yrSanitaryPermitTotals[1] + $yrMarriageLicenseTotals[1] + $yrMarriageApplicationTotals[1] + $yrBuildingPermitFeeTotals[1] + $yrZoningClearanceTotals[1] + $yrCattleAnimalRegistrationTotals[1] + $yrBirthCertNewBornTotals[1] + $yrInspectionFeeTotals[1])
                                + ($yrPoliceClearanceTotals[1] + $yrDeathCertTotals[1] + $yrBirthCertTotals[1] + $yrMarriageCertTotals[1] + $yrAssesorCertTotals[1] + $yrMTOCertTotals[1] + $yrHealthCertTotals[1] + $yrSecretaryFeeTotals[1] + $yrOtherMayorClearanceTotals[1] + $yrGarbageFeesTotals[1] + $yrMedicalDentalLabTotals[1] + $yrSolemnizationFeeTotals[1] + $yrAnnotationFeeTotals[1] + $yrMiscFeeTotals[1] + $yrRATotals[1] + $yrElectricalFeeTotals[1] + $yrSTLTotals[1] + $yrBackfillingHaulingTotals[1] + $yrBREQSTotals[1] + $yrInterestIncomeTotals[1] + $yrMarilagEcoparkTotals[1])
                                + ($yrCemeteriesTotals[1] + $yrMunicipalLotTotals[1] + $yrStallGoodwillTotals[1] + $yrStallRentalsTotals[1] + $yrSlaughterHouseTotals[1] + $yrWaterWorksRegistrationTotals[1] + $yrWaterWorksMonthlyDuesTotals[1]) + $yrOthersFeeTotals[1],
                            2
                        ); ?>
                    </td>

                    <td class="border border-gray-800 p-2 text-amber-800">
                        <?php echo number_format(
                            ($yrAmusementTaxTotals[2] + $yrBusinessTaxesRetailersTotals[2] + $yrBusinessTaxesContractorsTotals[2] + $yrBusinessTaxesBanksTotals[2] + $yrInterestSurchargeTotals[2] + $yrCommunityTaxCorporationTotals[2] + $yrCommunityTaxIndividualTotals[2])
                                + ($yrWeightMeasureTotals[2] + $yrFranchisingLicensingTotals[2] + $yrMayorsPermitTotals[2] + $yrBurialPermitTotals[2] + $yrSanitaryPermitTotals[2] + $yrMarriageLicenseTotals[2] + $yrMarriageApplicationTotals[2] + $yrBuildingPermitFeeTotals[2] + $yrZoningClearanceTotals[2] + $yrCattleAnimalRegistrationTotals[2] + $yrBirthCertNewBornTotals[2] + $yrInspectionFeeTotals[2])
                                + ($yrPoliceClearanceTotals[2] + $yrDeathCertTotals[2] + $yrBirthCertTotals[2] + $yrMarriageCertTotals[2] + $yrAssesorCertTotals[2] + $yrMTOCertTotals[2] + $yrHealthCertTotals[2] + $yrSecretaryFeeTotals[2] + $yrOtherMayorClearanceTotals[2] + $yrGarbageFeesTotals[2] + $yrMedicalDentalLabTotals[2] + $yrSolemnizationFeeTotals[2] + $yrAnnotationFeeTotals[2] + $yrMiscFeeTotals[2] + $yrRATotals[2] + $yrElectricalFeeTotals[2] + $yrSTLTotals[2] + $yrBackfillingHaulingTotals[2] + $yrBREQSTotals[2] + $yrInterestIncomeTotals[2] + $yrMarilagEcoparkTotals[2])
                                + ($yrCemeteriesTotals[2] + $yrMunicipalLotTotals[2] + $yrStallGoodwillTotals[2] + $yrStallRentalsTotals[2] + $yrSlaughterHouseTotals[2] + $yrWaterWorksRegistrationTotals[2] + $yrWaterWorksMonthlyDuesTotals[2]) + $yrOthersFeeTotals[2],
                            2
                        ); ?>
                    </td>

                    <td class="border border-gray-800 p-2 text-amber-800">
                        <?php echo number_format(
                            ($yrAmusementTaxTotals[3] + $yrBusinessTaxesRetailersTotals[3] + $yrBusinessTaxesContractorsTotals[3] + $yrBusinessTaxesBanksTotals[3] + $yrInterestSurchargeTotals[3] + $yrCommunityTaxCorporationTotals[3] + $yrCommunityTaxIndividualTotals[3])
                                + ($yrWeightMeasureTotals[3] + $yrFranchisingLicensingTotals[3] + $yrMayorsPermitTotals[3] + $yrBurialPermitTotals[3] + $yrSanitaryPermitTotals[3] + $yrMarriageLicenseTotals[3] + $yrMarriageApplicationTotals[3] + $yrBuildingPermitFeeTotals[3] + $yrZoningClearanceTotals[3] + $yrCattleAnimalRegistrationTotals[3] + $yrBirthCertNewBornTotals[3] + $yrInspectionFeeTotals[3])
                                + ($yrPoliceClearanceTotals[3] + $yrDeathCertTotals[3] + $yrBirthCertTotals[3] + $yrMarriageCertTotals[3] + $yrAssesorCertTotals[3] + $yrMTOCertTotals[3] + $yrHealthCertTotals[3] + $yrSecretaryFeeTotals[3] + $yrOtherMayorClearanceTotals[3] + $yrGarbageFeesTotals[3] + $yrMedicalDentalLabTotals[3] + $yrSolemnizationFeeTotals[3] + $yrAnnotationFeeTotals[3] + $yrMiscFeeTotals[3] + $yrRATotals[3] + $yrElectricalFeeTotals[3] + $yrSTLTotals[3] + $yrBackfillingHaulingTotals[3] + $yrBREQSTotals[3] + $yrInterestIncomeTotals[3] + $yrMarilagEcoparkTotals[3])
                                + ($yrCemeteriesTotals[3] + $yrMunicipalLotTotals[3] + $yrStallGoodwillTotals[3] + $yrStallRentalsTotals[3] + $yrSlaughterHouseTotals[3] + $yrWaterWorksRegistrationTotals[3] + $yrWaterWorksMonthlyDuesTotals[3]) + $yrOthersFeeTotals[3],
                            2
                        ); ?>
                    </td>


                    <td class="border border-gray-800 p-2 text-amber-800 font-bold">
                        <?php echo number_format(
                            (array_sum($yrAmusementTaxTotals) + array_sum($yrBusinessTaxesRetailersTotals) + array_sum($yrBusinessTaxesContractorsTotals) + array_sum($yrBusinessTaxesBanksTotals) + array_sum($yrInterestSurchargeTotals) + array_sum($yrCommunityTaxCorporationTotals) + array_sum($yrCommunityTaxIndividualTotals))
                                + (array_sum($yrWeightMeasureTotals) + array_sum($yrFranchisingLicensingTotals) + array_sum($yrMayorsPermitTotals) + array_sum($yrBurialPermitTotals) + array_sum($yrSanitaryPermitTotals) + array_sum($yrMarriageLicenseTotals) + array_sum($yrMarriageApplicationTotals) + array_sum($yrBuildingPermitFeeTotals) + array_sum($yrZoningClearanceTotals) + array_sum($yrCattleAnimalRegistrationTotals) + array_sum($yrBirthCertNewBornTotals) + array_sum($yrInspectionFeeTotals))
                                + (array_sum($yrPoliceClearanceTotals) + array_sum($yrDeathCertTotals) + array_sum($yrBirthCertTotals) + array_sum($yrMarriageCertTotals) + array_sum($yrAssesorCertTotals) + array_sum($yrMTOCertTotals) + array_sum($yrHealthCertTotals) + array_sum($yrSecretaryFeeTotals) + array_sum($yrOtherMayorClearanceTotals) + array_sum($yrGarbageFeesTotals) + array_sum($yrMedicalDentalLabTotals) + array_sum($yrSolemnizationFeeTotals) + array_sum($yrAnnotationFeeTotals) + array_sum($yrMiscFeeTotals) + array_sum($yrRATotals) + array_sum($yrElectricalFeeTotals) + array_sum($yrSTLTotals) + array_sum($yrBackfillingHaulingTotals) + array_sum($yrBREQSTotals) + array_sum($yrInterestIncomeTotals) + array_sum($yrMarilagEcoparkTotals))
                                + (array_sum($yrCemeteriesTotals) + array_sum($yrMunicipalLotTotals) + array_sum($yrStallGoodwillTotals) + array_sum($yrStallRentalsTotals) + array_sum($yrSlaughterHouseTotals) + array_sum($yrWaterWorksRegistrationTotals) + array_sum($yrWaterWorksMonthlyDuesTotals)) + array_sum($yrOthersFeeTotals),
                            2
                        ); ?>
                    </td>
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