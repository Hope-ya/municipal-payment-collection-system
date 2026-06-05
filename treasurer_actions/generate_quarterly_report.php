<?php
session_start();

require '../vendor/autoload.php';
require_once 'quarterly_report_helpers.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;


// Get the POST data 
$quarter = $_POST['quarter'] ?? '';
$year = $_POST['year'] ?? '';


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
        // Default to Q1 if quarter is missing or invalid
        $months = ['JANUARY', 'FEBRUARY', 'MARCH'];
        $quarter = 'Q1';
}
 
// Database connection
include '../backend/db_config_notpdo.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$treasurerEmail = $_SESSION['email'];
$treasurerQuery = "SELECT firstname, lastname FROM santamaria_employees WHERE email = '$treasurerEmail'";
$treasurerResult = $conn->query($treasurerQuery);
$treasurer = $treasurerResult->fetch_assoc();
$treasurerName = $treasurer['firstname'] . ' ' . $treasurer['lastname'];

// Fetch org settings (id = 1, since only one row is needed)
$orgQuery = $conn->query("SELECT org_name, org_logo FROM organization_info WHERE id = 1 LIMIT 1");
$org = $orgQuery->fetch_assoc();

$orgName = $org['org_name'];



// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setTitle('QUARTERLY');

// Set document properties
$spreadsheet->getProperties()
    ->setCreator("Municipality of Santa Maria")
    ->setTitle("Quarterly Report of Revenue and Receipts")
    ->setDescription("Quarterly report for {$quarter} {$year}");

// Set headers and formatting (similar to the QUARTERLY sheet in Excel)
$sheet->mergeCells('A1:G1');
$sheet->setCellValue('A1', 'Republic of the Philippines');
$sheet->getStyle('A1')->getFont()->setBold(true);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->mergeCells('A2:G2');
$sheet->setCellValue('A2', 'Province of Laguna');
$sheet->getStyle('A2')->getFont()->setBold(true);
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->mergeCells('A3:G3');
$sheet->setCellValue('A3',  $orgName);
$sheet->getStyle('A3')->getFont()->setBold(true);
$sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->mergeCells('A5:G5');
$sheet->setCellValue('A5', 'QUARTERLY REPORT OF REVENUE AND RECEIPTS');
$sheet->getStyle('A5')->getFont()->setBold(true);
$sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);


// Set column headers
$sheet->setCellValue('A7', 'REVENUE SOURCES');
$sheet->setCellValue('D7', $months[0]);
$sheet->setCellValue('E7', $months[1]);
$sheet->setCellValue('F7', $months[2]);
$sheet->setCellValue('G7', 'TOTAL');

$sheet->mergeCells('A7:C7');
$sheet->getRowDimension(7)->setRowHeight(30); // adjust 30 to your preferred height
$sheet->getStyle('A7:G7')->getFont()->setBold(true);

$sheet->getStyle('A7:G7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A7:G7')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

// Apply styling to headers
$headerStyle = [
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN]]
];
$sheet->getStyle('A7:G7')->applyFromArray($headerStyle);

//-----------------------------------------------------------------------------------------------------

//CONTENT VALUES

// HELPERS 
// REGULATORY FEES
$qtrWeightMeasure = getWeightMeasureMonthlyTotals($conn, $quarter, $year);
$qtrWeightMeasureTotal = array_sum($qtrWeightMeasure);
$sheet->setCellValue('D25', $qtrWeightMeasure[0]);
$sheet->setCellValue('E25', $qtrWeightMeasure[1]);
$sheet->setCellValue('F25', $qtrWeightMeasure[2]);
$sheet->setCellValue('G25', $qtrWeightMeasureTotal);

$qtrFranchisingLicensing = getFranchisingLicensingMonthlyTotals($conn, $quarter, $year);
$qtrFranchisingLicensingTotal = array_sum($qtrFranchisingLicensing);
$sheet->setCellValue('D26', $qtrFranchisingLicensing[0]);
$sheet->setCellValue('E26', $qtrFranchisingLicensing[1]);
$sheet->setCellValue('F26', $qtrFranchisingLicensing[2]);
$sheet->setCellValue('G26', $qtrFranchisingLicensingTotal);

$qtrMayorsPermit = getMayorsPermitMonthlyTotals($conn, $quarter, $year);
$qtrMayorsPermitTotal = array_sum($qtrMayorsPermit);
$sheet->setCellValue('D27', $qtrMayorsPermit[0]);
$sheet->setCellValue('E27', $qtrMayorsPermit[1]);
$sheet->setCellValue('F27', $qtrMayorsPermit[2]);
$sheet->setCellValue('G27', $qtrMayorsPermitTotal);

$qtrBurialPermit = getBurialPermitMonthlyTotals($conn, $quarter, $year);
$qtrBurialPermitTotal = array_sum($qtrBurialPermit);
$sheet->setCellValue('D30', $qtrBurialPermit[0]);
$sheet->setCellValue('E30', $qtrBurialPermit[1]);
$sheet->setCellValue('F30', $qtrBurialPermit[2]);
$sheet->setCellValue('G30', $qtrBurialPermitTotal);

$qtrSanitaryPermit = getSanitaryPermitMonthlyTotals($conn, $quarter, $year);
$qtrSanitaryPermitTotal = array_sum($qtrSanitaryPermit);
$sheet->setCellValue('D31', $qtrSanitaryPermit[0]);
$sheet->setCellValue('E31', $qtrSanitaryPermit[1]);
$sheet->setCellValue('F31', $qtrSanitaryPermit[2]);
$sheet->setCellValue('G31', $qtrSanitaryPermitTotal);


// Other Permits & Licenses
$qtrMarriageLicense = getMarriageLicenseMonthlyTotals($conn, $quarter, $year);
$qtrMarriageLicenseTotal = array_sum($qtrMarriageLicense);
$sheet->setCellValue('D34', $qtrMarriageLicense[0]);
$sheet->setCellValue('E34', $qtrMarriageLicense[1]);
$sheet->setCellValue('F34', $qtrMarriageLicense[2]);
$sheet->setCellValue('G34', $qtrMarriageLicenseTotal);

$qtrMarriageApplication = getMarriageApplicationMonthlyTotals($conn, $quarter, $year);
$qtrMarriageApplicationTotal = array_sum($qtrMarriageApplication);
$sheet->setCellValue('D35', $qtrMarriageApplication[0]);
$sheet->setCellValue('E35', $qtrMarriageApplication[1]);
$sheet->setCellValue('F35', $qtrMarriageApplication[2]);
$sheet->setCellValue('G35', $qtrMarriageApplicationTotal);

// Clearance and Certification Fees
$qtrPoliceClearance = getPoliceClearanceMonthlyTotals($conn, $quarter, $year);
$qtrPoliceClearanceTotal = array_sum($qtrPoliceClearance);
$sheet->setCellValue('D46', $qtrPoliceClearance[0]);
$sheet->setCellValue('E46', $qtrPoliceClearance[1]);
$sheet->setCellValue('F46', $qtrPoliceClearance[2]);
$sheet->setCellValue('G46', $qtrPoliceClearanceTotal);

// Secretary's Fees
$qtrDeathCert = getDeathCertMonthlyTotals($conn, $quarter, $year);
$qtrDeathCertTotal = array_sum($qtrDeathCert);
$sheet->setCellValue('D49', $qtrDeathCert[0]);
$sheet->setCellValue('E49', $qtrDeathCert[1]);
$sheet->setCellValue('F49', $qtrDeathCert[2]);
$sheet->setCellValue('G49', $qtrDeathCertTotal);

$qtrBirthCert = getBirthCertMonthlyTotals($conn, $quarter, $year);
$qtrBirthCertTotal = array_sum($qtrBirthCert);
$sheet->setCellValue('D50', $qtrBirthCert[0]);
$sheet->setCellValue('E50', $qtrBirthCert[1]);
$sheet->setCellValue('F50', $qtrBirthCert[2]);
$sheet->setCellValue('G50', $qtrBirthCertTotal);

$qtrMarriageCert = getMarriageCertMonthlyTotals($conn, $quarter, $year);
$qtrMarriageCertTotal = array_sum($qtrMarriageCert);
$sheet->setCellValue('D51', $qtrMarriageCert[0]); // Month 1
$sheet->setCellValue('E51', $qtrMarriageCert[1]); // Month 2
$sheet->setCellValue('F51', $qtrMarriageCert[2]); // Month 3
$sheet->setCellValue('G51', $qtrMarriageCertTotal); // Quarter Total

$qtrAssesorCert = getAssesorCertMonthlyTotals($conn, $quarter, $year);
$qtrAssesorCertTotal = array_sum($qtrAssesorCert);
$sheet->setCellValue('D52', $qtrAssesorCert[0]); // 1st month
$sheet->setCellValue('E52', $qtrAssesorCert[1]); // 2nd month
$sheet->setCellValue('F52', $qtrAssesorCert[2]); // 3rd month
$sheet->setCellValue('G52', $qtrAssesorCertTotal); // Quarter total

$qtrMTOCert = getMTOCertMonthlyTotals($conn, $quarter, $year);
$qtrMTOCertTotal = array_sum($qtrMTOCert);
$sheet->setCellValue('D53', $qtrMTOCert[0]); // 1st month
$sheet->setCellValue('E53', $qtrMTOCert[1]); // 2nd month
$sheet->setCellValue('F53', $qtrMTOCert[2]); // 3rd month
$sheet->setCellValue('G53', $qtrMTOCertTotal); // Quarter total

$qtrHealthCert = getHealthCertMonthlyTotals($conn, $quarter, $year);
$qtrHealthCertTotal = array_sum($qtrHealthCert);
$sheet->setCellValue('D54', $qtrHealthCert[0]); // First month of quarter
$sheet->setCellValue('E54', $qtrHealthCert[1]); // Second month
$sheet->setCellValue('F54', $qtrHealthCert[2]); // Third month
$sheet->setCellValue('G54', $qtrHealthCertTotal); // Total

$qtrSecretaryFee = getSecretaryFeeMonthlyTotals($conn, $quarter, $year);
$qtrSecretaryFeeTotal = array_sum($qtrSecretaryFee);
$sheet->setCellValue('D55', $qtrSecretaryFee[0]); // Jan/Apr/Jul/Oct
$sheet->setCellValue('E55', $qtrSecretaryFee[1]); // Feb/May/Aug/Nov
$sheet->setCellValue('F55', $qtrSecretaryFee[2]); // Mar/Jun/Sep/Dec
$sheet->setCellValue('G55', $qtrSecretaryFeeTotal); // Total for the quarter

// Other Clearance & Cert.
$qtrMayorClearance = getOtherMayorClearanceMonthlyTotals($conn, $quarter, $year);
$qtrMayorClearanceTotal = array_sum($qtrMayorClearance);
$sheet->setCellValue('D56', $qtrMayorClearance[0]); // 1st month of quarter
$sheet->setCellValue('E56', $qtrMayorClearance[1]); // 2nd month
$sheet->setCellValue('F56', $qtrMayorClearance[2]); // 3rd month
$sheet->setCellValue('G56', $qtrMayorClearanceTotal); // Total

$qtrMedicalDentalLab = getMedicalDentalLabMonthlyTotals($conn, $quarter, $year);
$qtrMedicalDentalLabTotal = array_sum($qtrMedicalDentalLab);
$sheet->setCellValue('D58', $qtrMedicalDentalLab[0]);
$sheet->setCellValue('E58', $qtrMedicalDentalLab[1]);
$sheet->setCellValue('F58', $qtrMedicalDentalLab[2]);
$sheet->setCellValue('G58', $qtrMedicalDentalLabTotal);

// Other Service Income
$qtrSolemnizationFee = getSolemnizationFeeMonthlyTotals($conn, $quarter, $year);
$qtrSolemnizationFeeTotal = array_sum($qtrSolemnizationFee);
$sheet->setCellValue('D61', $qtrSolemnizationFee[0]);
$sheet->setCellValue('E61', $qtrSolemnizationFee[1]);
$sheet->setCellValue('F61', $qtrSolemnizationFee[2]);
$sheet->setCellValue('G61', $qtrSolemnizationFeeTotal);

$qtrAnnotationFee = getAnnotationFeeMonthlyTotals($conn, $quarter, $year);
$qtrAnnotationFeeTotal = array_sum($qtrAnnotationFee);
$sheet->setCellValue('D63', $qtrAnnotationFee[0]); 
$sheet->setCellValue('E63', $qtrAnnotationFee[1]);
$sheet->setCellValue('F63', $qtrAnnotationFee[2]);
$sheet->setCellValue('G63', $qtrAnnotationFeeTotal);

$qtrMiscFee = getMiscFeeMonthlyTotals($conn, $quarter, $year);
$qtrMiscFeeTotal = array_sum($qtrMiscFee);
$sheet->setCellValue('D66', $qtrMiscFee[0]);
$sheet->setCellValue('E66', $qtrMiscFee[1]);
$sheet->setCellValue('F66', $qtrMiscFee[2]);
$sheet->setCellValue('G66', $qtrMiscFeeTotal);

$qtrRATotal = getRAMonthlyTotals($conn, $quarter, $year);
$qtrRATotalSum = array_sum($qtrRATotal);
$sheet->setCellValue('D67', $qtrRATotal[0]); // January
$sheet->setCellValue('E67', $qtrRATotal[1]); // February
$sheet->setCellValue('F67', $qtrRATotal[2]); // March
$sheet->setCellValue('G67', $qtrRATotalSum); // Total

// Cemetery Operations
$qtrCemeteriesTotal = getCemeteriesMonthlyTotals($conn, $quarter, $year);
$qtrCemeteriesTotalSum = array_sum($qtrCemeteriesTotal);
$sheet->setCellValue('D74', $qtrCemeteriesTotal[0]); // 1st month in quarter
$sheet->setCellValue('E74', $qtrCemeteriesTotal[1]); // 2nd month
$sheet->setCellValue('F74', $qtrCemeteriesTotal[2]); // 3rd month
$sheet->setCellValue('G74', $qtrCemeteriesTotalSum); // Total

$qtrInspectionFeeTotal = getInspectionFeeMonthlyTotals($conn, $quarter, $year);
$qtrInspectionFeeTotalSum = array_sum($qtrInspectionFeeTotal);
$sheet->setCellValue('D41', $qtrInspectionFeeTotal[0]); // 1st month in quarter
$sheet->setCellValue('E41', $qtrInspectionFeeTotal[1]); // 2nd month
$sheet->setCellValue('F41', $qtrInspectionFeeTotal[2]); // 3rd month
$sheet->setCellValue('G41', $qtrInspectionFeeTotalSum); // Total

$qtrElectricalFeeTotal = getElectricalFeeMonthlyTotals($conn, $quarter, $year);
$qtrElectricalFeeTotalSum = array_sum($qtrElectricalFeeTotal);
$sheet->setCellValue('D62', $qtrElectricalFeeTotal[0]); // 1st month in quarter
$sheet->setCellValue('E62', $qtrElectricalFeeTotal[1]); // 2nd month
$sheet->setCellValue('F62', $qtrElectricalFeeTotal[2]); // 3rd month
$sheet->setCellValue('G62', $qtrElectricalFeeTotalSum); // Total

$qtrBuildingPermitFeeTotal = getBuildingPermitFeeMonthlyTotals($conn, $quarter, $year);
$qtrBuildingPermitFeeTotalSum = array_sum($qtrBuildingPermitFeeTotal);
$sheet->setCellValue('D28', $qtrBuildingPermitFeeTotal[0]); // 1st month in quarter
$sheet->setCellValue('E28', $qtrBuildingPermitFeeTotal[1]); // 2nd month
$sheet->setCellValue('F28', $qtrBuildingPermitFeeTotal[2]); // 3rd month
$sheet->setCellValue('G28', $qtrBuildingPermitFeeTotalSum); // Total

$qtrZoningClearanceTotal = getZoningClearanceMonthlyTotals($conn, $quarter, $year);
$qtrZoningClearanceTotalSum = array_sum($qtrZoningClearanceTotal);
$sheet->setCellValue('D29', $qtrZoningClearanceTotal[0]); // 1st month in quarter
$sheet->setCellValue('E29', $qtrZoningClearanceTotal[1]); // 2nd month
$sheet->setCellValue('F29', $qtrZoningClearanceTotal[2]); // 3rd month
$sheet->setCellValue('G29', $qtrZoningClearanceTotalSum); // Total

// Amusement Tax (Provincial Account)
$qtrAmusementTaxTotal = getAmusementTaxMonthlyTotals($conn, $quarter, $year);
$qtrAmusementTaxTotalSum = array_sum($qtrAmusementTaxTotal);
$sheet->setCellValue('D10', $qtrAmusementTaxTotal[0]); // 1st month
$sheet->setCellValue('E10', $qtrAmusementTaxTotal[1]); // 2nd month
$sheet->setCellValue('F10', $qtrAmusementTaxTotal[2]); // 3rd month
$sheet->setCellValue('G10', $qtrAmusementTaxTotalSum); // Total

// Backfilling/Hauling
$qtrBackfillingHaulingTotal = getBackfillingHaulingMonthlyTotals($conn, $quarter, $year);
$qtrBackfillingHaulingTotalSum = array_sum($qtrBackfillingHaulingTotal);
$sheet->setCellValue('D65', $qtrBackfillingHaulingTotal[0]);
$sheet->setCellValue('E65', $qtrBackfillingHaulingTotal[1]);
$sheet->setCellValue('F65', $qtrBackfillingHaulingTotal[2]);
$sheet->setCellValue('G65', $qtrBackfillingHaulingTotalSum);

// BREQS
$qtrBREQSTotal = getBREQSMonthlyTotals($conn, $quarter, $year);
$qtrBREQSTotalSum = array_sum($qtrBREQSTotal);
$sheet->setCellValue('D68', $qtrBREQSTotal[0]);
$sheet->setCellValue('E68', $qtrBREQSTotal[1]);
$sheet->setCellValue('F68', $qtrBREQSTotal[2]);
$sheet->setCellValue('G68', $qtrBREQSTotalSum);

// Business Taxes – Banks
$qtrBusTaxBanksTotal = getBusinessTaxesBanksMonthlyTotals($conn, $quarter, $year);
$qtrBusTaxBanksTotalSum = array_sum($qtrBusTaxBanksTotal);
$sheet->setCellValue('D15', $qtrBusTaxBanksTotal[0]);
$sheet->setCellValue('E15', $qtrBusTaxBanksTotal[1]);
$sheet->setCellValue('F15', $qtrBusTaxBanksTotal[2]);
$sheet->setCellValue('G15', $qtrBusTaxBanksTotalSum);

// Business Taxes – Contractors
$qtrBusTaxContractorsTotal = getBusinessTaxesContractorsMonthlyTotals($conn, $quarter, $year);
$qtrBusTaxContractorsTotalSum = array_sum($qtrBusTaxContractorsTotal);
$sheet->setCellValue('D14', $qtrBusTaxContractorsTotal[0]);
$sheet->setCellValue('E14', $qtrBusTaxContractorsTotal[1]);
$sheet->setCellValue('F14', $qtrBusTaxContractorsTotal[2]);
$sheet->setCellValue('G14', $qtrBusTaxContractorsTotalSum);

// Business Taxes – Interest/Surcharge
$qtrBusTaxInterestTotal = getBusinessTaxesInterestMonthlyTotals($conn, $quarter, $year);
$qtrBusTaxInterestTotalSum = array_sum($qtrBusTaxInterestTotal);
$sheet->setCellValue('D16', $qtrBusTaxInterestTotal[0]);
$sheet->setCellValue('E16', $qtrBusTaxInterestTotal[1]);
$sheet->setCellValue('F16', $qtrBusTaxInterestTotal[2]);
$sheet->setCellValue('G16', $qtrBusTaxInterestTotalSum);

// Business Taxes – Retailers
$qtrBusTaxRetailersTotal = getBusinessTaxesRetailersMonthlyTotals($conn, $quarter, $year);
$qtrBusTaxRetailersTotalSum = array_sum($qtrBusTaxRetailersTotal);
$sheet->setCellValue('D13', $qtrBusTaxRetailersTotal[0]);
$sheet->setCellValue('E13', $qtrBusTaxRetailersTotal[1]);
$sheet->setCellValue('F13', $qtrBusTaxRetailersTotal[2]);
$sheet->setCellValue('G13', $qtrBusTaxRetailersTotalSum);

// Cattle/Animal Registration
$qtrCattleRegTotal = getCattleAnimalRegistrationMonthlyTotals($conn, $quarter, $year);
$qtrCattleRegTotalSum = array_sum($qtrCattleRegTotal);
$sheet->setCellValue('D38', $qtrCattleRegTotal[0]);
$sheet->setCellValue('E38', $qtrCattleRegTotal[1]);
$sheet->setCellValue('F38', $qtrCattleRegTotal[2]);
$sheet->setCellValue('G38', $qtrCattleRegTotalSum);

// Community Tax – Corporation
$qtrCommunityCorpTotal = getCommunityTaxCorporationMonthlyTotals($conn, $quarter, $year);
$qtrCommunityCorpTotalSum = array_sum($qtrCommunityCorpTotal);
$sheet->setCellValue('D19', $qtrCommunityCorpTotal[0]);
$sheet->setCellValue('E19', $qtrCommunityCorpTotal[1]);
$sheet->setCellValue('F19', $qtrCommunityCorpTotal[2]);
$sheet->setCellValue('G19', $qtrCommunityCorpTotalSum);

// Community Tax – Individual
$qtrCommunityIndTotal = getCommunityTaxIndividualMonthlyTotals($conn, $quarter, $year);
$qtrCommunityIndTotalSum = array_sum($qtrCommunityIndTotal);
$sheet->setCellValue('D20', $qtrCommunityIndTotal[0]);
$sheet->setCellValue('E20', $qtrCommunityIndTotal[1]);
$sheet->setCellValue('F20', $qtrCommunityIndTotal[2]);
$sheet->setCellValue('G20', $qtrCommunityIndTotalSum);

// Garbage Fees
$qtrGarbageTotal = getGarbageFeesMonthlyTotals($conn, $quarter, $year);
$qtrGarbageTotalSum = array_sum($qtrGarbageTotal);
$sheet->setCellValue('D57', $qtrGarbageTotal[0]);
$sheet->setCellValue('E57', $qtrGarbageTotal[1]);
$sheet->setCellValue('F57', $qtrGarbageTotal[2]);
$sheet->setCellValue('G57', $qtrGarbageTotalSum);

// Interest Income
$qtrInterestIncomeTotal = getInterestIncomeMonthlyTotals($conn, $quarter, $year);
$qtrInterestIncomeTotalSum = array_sum($qtrInterestIncomeTotal);
$sheet->setCellValue('D69', $qtrInterestIncomeTotal[0]);
$sheet->setCellValue('E69', $qtrInterestIncomeTotal[1]);
$sheet->setCellValue('F69', $qtrInterestIncomeTotal[2]);
$sheet->setCellValue('G69', $qtrInterestIncomeTotalSum);

// Marilag Ecopark
$qtrMarilagTotal = getMarilagEcoparkMonthlyTotals($conn, $quarter, $year);
$qtrMarilagTotalSum = array_sum($qtrMarilagTotal);
$sheet->setCellValue('D70', $qtrMarilagTotal[0]);
$sheet->setCellValue('E70', $qtrMarilagTotal[1]);
$sheet->setCellValue('F70', $qtrMarilagTotal[2]);
$sheet->setCellValue('G70', $qtrMarilagTotalSum);

// Municipal Lot
$qtrMunicipalLotTotal = getMunicipalLotMonthlyTotals($conn, $quarter, $year);
$qtrMunicipalLotTotalSum = array_sum($qtrMunicipalLotTotal);
$sheet->setCellValue('D75', $qtrMunicipalLotTotal[0]);
$sheet->setCellValue('E75', $qtrMunicipalLotTotal[1]);
$sheet->setCellValue('F75', $qtrMunicipalLotTotal[2]);
$sheet->setCellValue('G75', $qtrMunicipalLotTotalSum);

// Slaughter House Operations
$qtrSlaughterTotal = getSlaughterHouseOperationsMonthlyTotals($conn, $quarter, $year);
$qtrSlaughterTotalSum = array_sum($qtrSlaughterTotal);
$sheet->setCellValue('D81', $qtrSlaughterTotal[0]);
$sheet->setCellValue('E81', $qtrSlaughterTotal[1]);
$sheet->setCellValue('F81', $qtrSlaughterTotal[2]);
$sheet->setCellValue('G81', $qtrSlaughterTotalSum);

// Stall Goodwill
$qtrStallGoodwillTotal = getStallGoodwillMonthlyTotals($conn, $quarter, $year);
$qtrStallGoodwillTotalSum = array_sum($qtrStallGoodwillTotal);
$sheet->setCellValue('D78', $qtrStallGoodwillTotal[0]);
$sheet->setCellValue('E78', $qtrStallGoodwillTotal[1]);
$sheet->setCellValue('F78', $qtrStallGoodwillTotal[2]);
$sheet->setCellValue('G78', $qtrStallGoodwillTotalSum);

// Stall Rentals
$qtrStallRentalsTotal = getStallRentalsMonthlyTotals($conn, $quarter, $year);
$qtrStallRentalsTotalSum = array_sum($qtrStallRentalsTotal);
$sheet->setCellValue('D79', $qtrStallRentalsTotal[0]);
$sheet->setCellValue('E79', $qtrStallRentalsTotal[1]);
$sheet->setCellValue('F79', $qtrStallRentalsTotal[2]);
$sheet->setCellValue('G79', $qtrStallRentalsTotalSum);

// STL
$qtrSTLTotal = getSTLMonthlyTotals($conn, $quarter, $year);
$qtrSTLTotalSum = array_sum($qtrSTLTotal);
$sheet->setCellValue('D64', $qtrSTLTotal[0]);
$sheet->setCellValue('E64', $qtrSTLTotal[1]);
$sheet->setCellValue('F64', $qtrSTLTotal[2]);
$sheet->setCellValue('G64', $qtrSTLTotalSum);

// Water Works Monthly Dues
$qtrWaterDuesTotal = getWaterWorksMonthlyDuesMonthlyTotals($conn, $quarter, $year);
$qtrWaterDuesTotalSum = array_sum($qtrWaterDuesTotal);
$sheet->setCellValue('D84', $qtrWaterDuesTotal[0]);
$sheet->setCellValue('E84', $qtrWaterDuesTotal[1]);
$sheet->setCellValue('F84', $qtrWaterDuesTotal[2]);
$sheet->setCellValue('G84', $qtrWaterDuesTotalSum);

// Water Works Registration
$qtrWaterRegTotal = getWaterWorksRegistrationMonthlyTotals($conn, $quarter, $year);
$qtrWaterRegTotalSum = array_sum($qtrWaterRegTotal);
$sheet->setCellValue('D85', $qtrWaterRegTotal[0]);
$sheet->setCellValue('E85', $qtrWaterRegTotal[1]);
$sheet->setCellValue('F85', $qtrWaterRegTotal[2]);
$sheet->setCellValue('G85', $qtrWaterRegTotalSum);

// Birth Certificate (New Born) Fees
$qtrBirthCertNewBornTotal = getBirthCertNewBornMonthlyTotals($conn, $quarter, $year);
$qtrBirthCertNewBornSum = array_sum($qtrBirthCertNewBornTotal);
$sheet->setCellValue('D39', $qtrBirthCertNewBornTotal[0]); // 1st month
$sheet->setCellValue('E39', $qtrBirthCertNewBornTotal[1]); // 2nd month
$sheet->setCellValue('F39', $qtrBirthCertNewBornTotal[2]); // 3rd month
$sheet->setCellValue('G39', $qtrBirthCertNewBornSum);      // Quarter total

$qtrOthersFeeTotal = getOthersFeeMonthlyTotals($conn, $quarter, $year);
$qtrOthersFeeSum = array_sum($qtrOthersFeeTotal);
$sheet->setCellValue('D90', $qtrOthersFeeTotal[0]); // 1st month
$sheet->setCellValue('E90', $qtrOthersFeeTotal[1]); // 2nd month
$sheet->setCellValue('F90', $qtrOthersFeeTotal[2]); // 3rd month
$sheet->setCellValue('G90', $qtrOthersFeeSum);      // Quarter total


//------------------------------------------------------------------------------------------------------

// Add the main content structure (similar to QUARTERLY sheet)
$content = [
    // TAX ON BUSINESS section
    [9, 'TAX ON BUSINESS', '', '', '', '', '', ''],
    [10, '', 'Amusement Tax (Provincial Account)', '', '', '', '', ''],
    [12, '', 'Business Taxes', '', '', '', '', ''],
    [13, '', '', 'Retailers', '', '', '', ''],
    [14, '', '', 'Contractors', '', '', '', ''],
    [15, '', '', 'Banks & Other Financial Institutions', '', '', '', ''],
    [16, '', '', 'Interest / Surcharge', '', '', '', ''],

    // OTHER TAXES section
    [18, 'OTHER TAXES:', '', '', '', '', '', ''],
    [19, '', 'Community Tax - Corporation', '', '', '', '', ''],
    [20, '', 'Community Tax - Individual', '', '', '', '', ''],

    // REGULATORY FEES section
    [22, 'REGULATORY FEES (PERMITS & LICENSES)', '', '', '', '', '', ''],
    [24, '', 'Permits and Licenses', '', '', '', '', ''],
    [25, '', '', 'Fees on weights & measures', '', '', '', ''],
    [26, '', '', 'Franchising & licensing Fees', '', '', '', ''],
    [27, '', '', 'Permit Fees/ Mayor\'s Permit Fees', '', '', '', ''],
    [28, '', '', 'Building Permit Fees', '', '', '', ''],
    [29, '', '', 'Zonal/Location Permit Fees', '', '', '', ''],
    [30, '', '', 'Burial Permit Fees', '', '', '', ''],
    [31, '', '', 'Sanitary Permit Fees', '', '', '', ''],

    // Other Permits & Licenses
    [33, '', 'Other Permits & Licenses:', '', '', '', '', ''],
    [34, '', '', 'Marriage License', '', '', '', ''],
    [35, '', '', 'Application for Marriage', '', '', '', ''],

    // Registration Fees
    [37, '', 'Registration Fees:', '', '', '', '', ''],
    [38, '', '', 'Cattle/Animal Registration fees', '', '', '', ''],
    [39, '', '', 'Birth Certificate (New Born) Fees', '', '', '', ''],

    // Inspection Fees
    [41, '', 'Inspection Fees', '', '', '', '', ''],

    // SERVICE/USER CHARGES section
    [43, 'SERVICE/ USER CHARGES (Service Income)', '', '', '', '', '', ''],

    // Clearance and Certification Fees
    [45, '', 'Clearance and Certification Fees', '', '', '', '', ''],
    [46, '', '', 'Police Clearance', '', '', '', ''],

    // Secretary's Fees
    [48, '', 'Secretary\'s Fees', '', '', '', '', ''],
    [49, '', '', 'Death Certificate', '', '', '', ''],
    [50, '', '', 'Birth Certificate', '', '', '', ''],
    [51, '', '', 'Marriage Certificate', '', '', '', ''],
    [52, '', '', 'Assessor\'s Certificate', '', '', '', ''],
    [53, '', '', 'MTO certificate', '', '', '', ''],
    [54, '', '', 'Health Certificate (Medical)', '', '', '', ''],
    [55, '', '', 'Secretary\'s Fees', '', '', '', ''],

    // Other Clearance & Cert.
    [56, '', 'Other Clearance & Cert./Mayor\'s Clearance', '', '', '', '', ''],
    [57, '', 'Garbage Fees', '', '', '', '', ''],
    [58, '', 'Medical, Dental & Laboratory Fees', '', '', '', '', ''],

    // Other Service Income
    [60, '', 'Other Service Income', '', '', '', '', ''],
    [61, '', '', 'Solemnization Fees', '', '', '', ''],
    [62, '', '', 'Electrical Fees', '', '', '', ''],
    [63, '', '', 'Annotation Fees', '', '', '', ''],
    [64, '', '', 'STL', '', '', '', ''],
    [65, '', '', 'Backfilling/Hauling', '', '', '', ''],
    [66, '', '', 'Miscellaneous Fees', '', '', '', ''],
    [67, '', '', 'RA 9048, 10172 & 9255', '', '', '', ''],
    [68, '', '', 'BREQS', '', '', '', ''],
    [69, '', '', 'Interest Income', '', '', '', ''],
    [70, '', '', 'Marilag Ecopark', '', '', '', ''],

    // RECEIPTS FROM ECONOMIC ENTERPRISES section
    [72, 'RECEIPTS FROM ECONOMIC ENTERPRISES (BUSINESS INCOME)', '', '', '', '', '', ''],

    // Cemetery Operations
    [73, '', 'Cemetery Operations:', '', '', '', '', ''],
    [74, '', '', 'Cemeteries', '', '', '', ''],
    [75, '', '', 'Municipal Lot', '', '', '', ''],

    // Market Operations
    [77, '', 'Market Operations:', '', '', '', '', ''],
    [78, '', '', 'Stall Goodwill', '', '', '', ''],
    [79, '', '', 'Stall Rentals', '', '', '', ''],

    // Slaughter House Operations
    [81, '', 'Slaughter House Operations:', '', '', '', '', ''],

    // Water Works System Operations
    [83, '', 'Water Works System Operations', '', '', '', '', ''],
    [84, '', '', 'Registration', '', '', '', ''],
    [85, '', '', 'Monthly dues', '', '', '', ''],

    // LOCAL SOURCES section
    [87, 'LOCAL SOURCES', '', '', '', '', '', ''],

    // OTHER INCOME section
    [89, 'OTHER INCOME/RECEIPTS (OTHER GENERAL INCOME)', '', '', '', '', '', ''],
    [90, '', 'IRA', '', '', '', '', ''],

    // GRAND TOTAL
    [92, '', '', 'GRAND TOTAL', '', '', '', ''],

    // Footer
];

// --- Cell content ---
$sheet->setCellValue('D95', 'Certified Correct by:');

$sheet->setCellValue('C98', 'Lorena L. De Torres');
$sheet->setCellValue('E98', $treasurerName); // Dynamic name of treasurer

$sheet->setCellValue('C99', 'Encoder');
$sheet->setCellValue('E99', 'Municipal Treasurer');

// --- Formatting ---
// Certified Correct by: in italic
$sheet->getStyle('D95')->getFont()->setItalic(true);

// Underline names
$sheet->getStyle('C98')->getFont()->setUnderline(true);
$sheet->getStyle('E98')->getFont()->setUnderline(true);

// Italicize positions
$sheet->getStyle('C99')->getFont()->setItalic(true);
$sheet->getStyle('E99')->getFont()->setItalic(true);

// Center-align names and positions
$sheet->getStyle('C98')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('E98')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->getStyle('C99')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('E99')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Optional: Set column widths for visual balance
$sheet->getColumnDimension('B')->setWidth(30);
$sheet->getColumnDimension('F')->setWidth(30);

$currentRow = 9; // starting row

foreach ($content as $item) {
    $row = is_numeric($item[0]) ? $item[0] : $currentRow;

    $sheet->setCellValue('A' . $row, $item[1]);
    if (!empty($item[2])) $sheet->setCellValue('B' . $row, $item[2]);
    if (!empty($item[3])) $sheet->setCellValue('C' . $row, $item[3]);
    if (!empty($item[4])) $sheet->setCellValue('D' . $row, $item[4]);
    if (!empty($item[5])) $sheet->setCellValue('E' . $row, $item[5]);
    if (!empty($item[6])) $sheet->setCellValue('F' . $row, $item[6]);
    if (!empty($item[7])) $sheet->setCellValue('G' . $row, $item[7]);

    // Apply bold style to main categories
    if (!empty($item[1])) {
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
    }

    if (!is_numeric($item[0])) {
        $currentRow++;
    }
}


// Add formulas for TOTAL columns (similar to Excel)
// TAX ON BUSINESS section
$sheet->setCellValue('D9', '=SUM(D10,D12,D18)');
$sheet->setCellValue('E9', '=SUM(E10,E12,E18)');
$sheet->setCellValue('F9', '=SUM(F10,F12,F18)');
$sheet->setCellValue('G9', '=SUM(D9:F9)');


$sheet->setCellValue('G10', '=SUM(D10:F10)');

$sheet->setCellValue('D12', '=SUM(D13:D16)');
$sheet->setCellValue('E12', '=SUM(E13:E16)');
$sheet->setCellValue('F12', '=SUM(F13:F16)');
$sheet->setCellValue('G12', '=SUM(D12:F12)');


$sheet->setCellValue('G13', '=SUM(D13:F13)');


$sheet->setCellValue('G14', '=SUM(D14:F14)');


$sheet->setCellValue('G15', '=SUM(D15:F15)');


$sheet->setCellValue('G16', '=SUM(D16:F16)');

// OTHER TAXES section
$sheet->setCellValue('D18', '=SUM(D19:D20)');
$sheet->setCellValue('E18', '=SUM(E19:E20)');
$sheet->setCellValue('F18', '=SUM(F19:F20)');
$sheet->setCellValue('G18', '=SUM(D18:F18)');


$sheet->setCellValue('G19', '=SUM(D19:F19)');


$sheet->setCellValue('G20', '=SUM(D20:F20)');

// REGULATORY FEES section
$sheet->setCellValue('D22', '=SUM(D24,D33,D37,D41)');
$sheet->setCellValue('E22', '=SUM(E24,E33,E37,E41)');
$sheet->setCellValue('F22', '=SUM(F24,F33,F37,F41)');
$sheet->setCellValue('G22', '=SUM(D22:F22)');

$sheet->setCellValue('D24', '=SUM(D25:D31)');
$sheet->setCellValue('E24', '=SUM(E25:E31)');
$sheet->setCellValue('F24', '=SUM(F25:F31)');
$sheet->setCellValue('G24', '=SUM(D24:F24)');


$sheet->setCellValue('G25', '=SUM(D25:F25)');


$sheet->setCellValue('G26', '=SUM(D26:F26)');


$sheet->setCellValue('G27', '=SUM(D27:F27)');


$sheet->setCellValue('G28', '=SUM(D28:F28)');


$sheet->setCellValue('G29', '=SUM(D29:F29)');


$sheet->setCellValue('G30', '=SUM(D30:F30)');


$sheet->setCellValue('G31', '=SUM(D31:F31)');

// Other Permits & Licenses
$sheet->setCellValue('D33', '=SUM(D34:D35)');
$sheet->setCellValue('E33', '=SUM(E34:E35)');
$sheet->setCellValue('F33', '=SUM(F34:F35)');
$sheet->setCellValue('G33', '=SUM(D33:F33)');


$sheet->setCellValue('G34', '=SUM(D34:F34)');


$sheet->setCellValue('G35', '=SUM(D35:F35)');

// Registration Fees
$sheet->setCellValue('D37', '=SUM(D38:D39)');
$sheet->setCellValue('E37', '=SUM(E38:E39)');
$sheet->setCellValue('F37', '=SUM(F38:F39)');
$sheet->setCellValue('G37', '=SUM(D37:F37)');


$sheet->setCellValue('G38', '=SUM(D38:F38)');


$sheet->setCellValue('G39', '=SUM(D39:F39)');

// Inspection Fees

$sheet->setCellValue('G41', '=SUM(D41:F41)');

// SERVICE/USER CHARGES section
$sheet->setCellValue('D43', '=SUM(D46,D48,D56,D57,D58,D60)');
$sheet->setCellValue('E43', '=SUM(E46,E48,E56,E57,E58,E60)');
$sheet->setCellValue('F43', '=SUM(F46,F48,F56,F57,F58,F60)');
$sheet->setCellValue('G43', '=SUM(D43:F43)');

// Clearance and Certification Fees

$sheet->setCellValue('G46', '=SUM(D46:F46)');

// Secretary's Fees
$sheet->setCellValue('D48', '=SUM(D49:D55)');
$sheet->setCellValue('E48', '=SUM(E49:E55)');
$sheet->setCellValue('F48', '=SUM(F49:F55)');
$sheet->setCellValue('G48', '=SUM(D48:F48)');


$sheet->setCellValue('G49', '=SUM(D49:F49)');


$sheet->setCellValue('G50', '=SUM(D50:F50)');


$sheet->setCellValue('G51', '=SUM(D51:F51)');


$sheet->setCellValue('G52', '=SUM(D52:F52)');


$sheet->setCellValue('G53', '=SUM(D53:F53)');


$sheet->setCellValue('G54', '=SUM(D54:F54)');


$sheet->setCellValue('G55', '=SUM(D55:F55)');

// Other Clearance & Cert.

$sheet->setCellValue('G56', '=SUM(D56:F56)');


$sheet->setCellValue('G57', '=SUM(D57:F57)');


$sheet->setCellValue('G58', '=SUM(D58:F58)');

// Other Service Income
$sheet->setCellValue('D60', '=SUM(D61:D70)');
$sheet->setCellValue('E60', '=SUM(E61:E70)');
$sheet->setCellValue('F60', '=SUM(F61:F70)');
$sheet->setCellValue('G60', '=SUM(D60:F60)');


$sheet->setCellValue('G61', '=SUM(D61:F61)');


$sheet->setCellValue('G62', '=SUM(D62:F62)');


$sheet->setCellValue('G63', '=SUM(D63:F63)');


$sheet->setCellValue('G64', '=SUM(D64:F64)');

$sheet->setCellValue('G65', '=SUM(D65:F65)');


$sheet->setCellValue('G66', '=SUM(D66:F66)');


$sheet->setCellValue('G67', '=SUM(D67:F67)');


$sheet->setCellValue('G68', '=SUM(D68:F68)');


$sheet->setCellValue('G69', '=SUM(D69:F69)');


$sheet->setCellValue('G70', '=SUM(D70:F70)');

// RECEIPTS FROM ECONOMIC ENTERPRISES
$sheet->setCellValue('D72', '=SUM(D73,D77,D81,D83)');
$sheet->setCellValue('E72', '=SUM(E73,E77,E81,E83)');
$sheet->setCellValue('F72', '=SUM(F73,F77,F81,F83)');
$sheet->setCellValue('G72', '=SUM(D72:F72)');

// Cemetery Operations
$sheet->setCellValue('D73', '=SUM(D74:D75)');
$sheet->setCellValue('E73', '=SUM(E74:E75)');
$sheet->setCellValue('F73', '=SUM(F74:F75)');
$sheet->setCellValue('G73', '=SUM(D73:F73)');


$sheet->setCellValue('G74', '=SUM(D74:F74)');


$sheet->setCellValue('G75', '=SUM(D75:F75)');

// Market Operations
$sheet->setCellValue('D77', '=SUM(D78:D79)');
$sheet->setCellValue('E77', '=SUM(E78:E79)');
$sheet->setCellValue('F77', '=SUM(F78:F79)');
$sheet->setCellValue('G77', '=SUM(D77:F77)');


$sheet->setCellValue('G78', '=SUM(D78:F78)');


$sheet->setCellValue('G79', '=SUM(D79:F79)');

// Slaughter House Operations

$sheet->setCellValue('G81', '=SUM(D81:F81)');

// Water Works System Operations
$sheet->setCellValue('D83', '=SUM(D84:D85)');
$sheet->setCellValue('E83', '=SUM(E84:E85)');
$sheet->setCellValue('F83', '=SUM(F84:F85)');
$sheet->setCellValue('G83', '=SUM(D83:F83)');


$sheet->setCellValue('G84', '=SUM(D84:F84)');


$sheet->setCellValue('G85', '=SUM(D85:F85)');

// LOCAL SOURCES
$sheet->setCellValue('D87', '=SUM(D72,D43,D22,D9)');
$sheet->setCellValue('E87', '=SUM(E72,E43,E22,E9)');
$sheet->setCellValue('F87', '=SUM(F72,F43,F22,F9)');
$sheet->setCellValue('G87', '=SUM(D87:F87)');

// OTHER INCOME/RECEIPTS

$sheet->setCellValue('G90', '=SUM(D90:F90)');

// GRAND TOTAL
$sheet->setCellValue('D92', '=D87+D90');
$sheet->setCellValue('E92', '=E87+E90');
$sheet->setCellValue('F92', '=F87+F90');
$sheet->setCellValue('G92', '=IF(SUM(D92:F92)=SUM(G87,G90),G87+G90,"INCORRECT")');

// Set column widths
$sheet->getColumnDimension('A')->setWidth(7); // Revenue Source titles
$sheet->getColumnDimension('B')->setWidth(7);  // Indentation for sub-rows
$sheet->getColumnDimension('C')->setWidth(45); // Sub-category labels
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getColumnDimension('E')->setWidth(15);
$sheet->getColumnDimension('F')->setWidth(15);
$sheet->getColumnDimension('G')->setWidth(15);

// Step 1: Set thin borders for all sides
$sheet->getStyle('A7:G92')->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN
        ]
    ]
]);

// Step 2: Override left and right borders to thick
$sheet->getStyle('A7:G92')->applyFromArray([
    'borders' => [
        'left' => [
            'borderStyle' => Border::BORDER_THICK
        ],
        'right' => [
            'borderStyle' => Border::BORDER_THICK
        ],
        'top' => [
            'borderStyle' => Border::BORDER_THICK
        ],
        'bottom' => [
            'borderStyle' => Border::BORDER_THICK
        ]
    ]
]);

// Center align D and E columns (all rows used)
$highestRow = $sheet->getHighestRow();
$sheet->getStyle("D7:G$highestRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);


$lastRow = $sheet->getHighestRow();



// Apply thick outside border for columns A–C
$sheet->getStyle("A7:C92")->applyFromArray([
    'borders' => [
        'outline' => [
            'borderStyle' => Border::BORDER_THICK,
        ],
    ],
]);

// Apply thick outside border for columns D–G
$sheet->getStyle("D7:D92")->applyFromArray([
    'borders' => [
        'left' => [
            'borderStyle' => Border::BORDER_THICK,
        ],
        'right' => [
            'borderStyle' => Border::BORDER_THICK,
        ],
    ],
]);

$sheet->getStyle("E7:E92")->applyFromArray([
    'borders' => [
        'left' => [
            'borderStyle' => Border::BORDER_THICK,
        ],
        'right' => [
            'borderStyle' => Border::BORDER_THICK,
        ],
    ],
]);

$sheet->getStyle("F7:F92")->applyFromArray([
    'borders' => [
        'left' => [
            'borderStyle' => Border::BORDER_THICK,
        ],
        'right' => [
            'borderStyle' => Border::BORDER_THICK,
        ],
    ],
]);

$sheet->getStyle("G7:G92")->applyFromArray([
    'borders' => [
        'left' => [
            'borderStyle' => Border::BORDER_THICK,
        ],
        'right' => [
            'borderStyle' => Border::BORDER_THICK,
        ],
    ],
]);

$clearBorderRanges = [
    'A10:C17', 'A19:C23', 'A25:C32', 'A34:C36', 'A38:C40', 'A42:C42',
    'A44:C45', 'A47:C47', 'A49:C55', 'A59:E59', 'A61:C71', 'A74:C76',
    'A78:C80', 'A82:C82', 'A84:C86', 'A88:C89', 'A91:C91'
];

// Define no-border style
$noBorderStyle = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_NONE,
        ],
    ],
];

// Apply no-border style to each range
foreach ($clearBorderRanges as $range) {
    $sheet->getStyle($range)->applyFromArray($noBorderStyle);
}

// Define the rows that need thick borders
$thickBorderRows = [7, 9, 18, 24, 33, 37, 41, 43, 46, 48, 56, 57, 58, 60, 72, 73, 77, 81, 83, 87, 90, 92];

// Define thick border style
$thickBorderStyle = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THICK
        ]
    ]
];

// Apply thick borders to each specified row from column A to E
foreach ($thickBorderRows as $row) {
    $cellRange = "A{$row}:G{$row}";
    $sheet->getStyle($cellRange)->applyFromArray($thickBorderStyle);
}

$boldCells = [
    'B12','B22','B24','B33','B37','B45','B48','B60','B72','B73','B77','B81','B83',
    'C92'
    
];

foreach ($boldCells as $cell) {
    $sheet->getStyle($cell)->getFont()->setBold(true);
}


// Green (#00b050)
$greenCells = ['E9', 'E22', 'E43', 'E72', 'C92', 'D9', 'D22', 'D43', 'D72', 'F9', 'F22', 'F43', 'F72', 'G9', 'G22', 'G43', 'G72'];

foreach ($greenCells as $cell) {
    $sheet->getStyle($cell)->getFont()
        ->getColor()->setRGB('00B050');

    $sheet->getStyle($cell)->getFont()
        ->setBold(true);
}


// Blue (#0070C0)
$columns = ['D', 'F', 'G'];
$baseBlueCells = [
    'E10','E12','E18','E24','E33','E37','E41','E46','E48',
    'E56','E57','E58','E60','E73','E77','E81','E83'
];

$allBlueCells = $baseBlueCells; // Start with original E-column cells

// Add D, F, G versions of the same row numbers
foreach ($columns as $col) {
    foreach ($baseBlueCells as $cell) {
        $row = preg_replace('/[^0-9]/', '', $cell); // extract the number
        $allBlueCells[] = $col . $row;
    }
}

// Now apply the styles
foreach ($allBlueCells as $cell) {
    $sheet->getStyle($cell)->getFont()->getColor()->setRGB('0070C0');
    $sheet->getStyle($cell)->getFont()->setBold(true);
}




// Purple (#7030A0)
$purpleCells = ['E87', 'E90', 'D87', 'D90', 'F87', 'F90', 'G87', 'G90'];
foreach ($purpleCells as $cell) {
    $sheet->getStyle($cell)->getFont()
        ->setBold(true)
        ->getColor()->setRGB('7030A0');
}

// Brown (#833C0B)
$brownCells = ['D92', 'E92', 'F92', 'G92'];
foreach ($brownCells as $cell) {
    $sheet->getStyle($cell)->getFont()
        ->setBold(true)
        ->getColor()->setRGB('833C0B');
}





$sheet->getStyle("D9:G100")->getNumberFormat()->setFormatCode('#,##0.00');



// Create Excel file and send to browser
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Quarterly_Report_' . $quarter . '_' . $year . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
