<?php
session_start();

require '../vendor/autoload.php';
require_once 'yearly_report_helpers.php';
 
 
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);
$year = $data['year'] ?? '';

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
$sheet->setTitle('ANNUAL');

// Set document properties
$spreadsheet->getProperties()
    ->setCreator("Municipality of Santa Maria")
    ->setTitle("Annual Report of Revenue and Receipts")
    ->setDescription("Annual report for {$year}");

// Set headers and formatting (similar to the ANNUAL sheet in Excel)
$sheet->mergeCells('A1:H1');
$sheet->setCellValue('A1', 'Republic of the Philippines');
$sheet->getStyle('A1')->getFont()->setBold(true);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->mergeCells('A2:H2');
$sheet->setCellValue('A2', 'Province of Laguna');
$sheet->getStyle('A2')->getFont()->setBold(true);
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->mergeCells('A3:H3');
$sheet->setCellValue('A3', $orgName);
$sheet->getStyle('A3')->getFont()->setBold(true);
$sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->mergeCells('A5:H5');
$sheet->setCellValue('A5', 'ANNUAL REPORT OF REVENUE AND RECEIPTS');
$sheet->getStyle('A5')->getFont()->setBold(true);
$sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Set column headers
$sheet->setCellValue('A7', 'REVENUE SOURCES');
$sheet->setCellValue('D7', '1ST QUARTER');
$sheet->setCellValue('E7', '2ND QUARTER');
$sheet->setCellValue('F7', '3RD QUARTER');
$sheet->setCellValue('G7', '4TH QUARTER');
$sheet->setCellValue('H7', 'TOTAL');

$sheet->mergeCells('A7:C7');
$sheet->getRowDimension(7)->setRowHeight(30);
$sheet->getStyle('A7:H7')->getFont()->setBold(true);
$sheet->getStyle('A7:H7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A7:H7')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

// Apply styling to headers
$headerStyle = [
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN]]
];
$sheet->getStyle('A7:H7')->applyFromArray($headerStyle);

//-----------------------------------------------------------------------------------------------------

// CONTENT VALUES - You'll need to implement these helper functions similar to the quarterly report

//Permits and Licenses
$yrWeightMeasureTotals = getWeightMeasureQuarterlyTotals($conn, $year);
$qtr1WeightMeasure = $yrWeightMeasureTotals[0];
$qtr2WeightMeasure = $yrWeightMeasureTotals[1];
$qtr3WeightMeasure = $yrWeightMeasureTotals[2];
$qtr4WeightMeasure = $yrWeightMeasureTotals[3];
$sheet->setCellValue('D25', $qtr1WeightMeasure);
$sheet->setCellValue('E25', $qtr2WeightMeasure);
$sheet->setCellValue('F25', $qtr3WeightMeasure);
$sheet->setCellValue('G25', $qtr4WeightMeasure);

$yrFranchisingLicensingTotals = getFranchisingLicensingQuarterlyTotals($conn, $year);
$qtr1FranchisingLicensing = $yrFranchisingLicensingTotals[0];
$qtr2FranchisingLicensing = $yrFranchisingLicensingTotals[1];
$qtr3FranchisingLicensing = $yrFranchisingLicensingTotals[2];
$qtr4FranchisingLicensing = $yrFranchisingLicensingTotals[3];
$sheet->setCellValue('D26', $qtr1FranchisingLicensing);
$sheet->setCellValue('E26', $qtr2FranchisingLicensing);
$sheet->setCellValue('F26', $qtr3FranchisingLicensing);
$sheet->setCellValue('G26', $qtr4FranchisingLicensing);

$yrMayorsPermitTotals = getMayorsPermitQuarterlyTotals($conn, $year);
$qtr1MayorsPermit = $yrMayorsPermitTotals[0];
$qtr2MayorsPermit = $yrMayorsPermitTotals[1];
$qtr3MayorsPermit = $yrMayorsPermitTotals[2];
$qtr4MayorsPermit = $yrMayorsPermitTotals[3];
$sheet->setCellValue('D27', $qtr1MayorsPermit);
$sheet->setCellValue('E27', $qtr2MayorsPermit);
$sheet->setCellValue('F27', $qtr3MayorsPermit);
$sheet->setCellValue('G27', $qtr4MayorsPermit);

$yrBurialPermitTotals = getBurialPermitQuarterlyTotals($conn, $year);
$qtr1BurialPermit = $yrBurialPermitTotals[0];
$qtr2BurialPermit = $yrBurialPermitTotals[1];
$qtr3BurialPermit = $yrBurialPermitTotals[2];
$qtr4BurialPermit = $yrBurialPermitTotals[3];
$sheet->setCellValue('D30', $qtr1BurialPermit);
$sheet->setCellValue('E30', $qtr2BurialPermit);
$sheet->setCellValue('F30', $qtr3BurialPermit);
$sheet->setCellValue('G30', $qtr4BurialPermit);

$yrSanitaryPermitTotals = getSanitaryPermitQuarterlyTotals($conn, $year);
$qtr1Sanitary = $yrSanitaryPermitTotals[0];
$qtr2Sanitary = $yrSanitaryPermitTotals[1];
$qtr3Sanitary = $yrSanitaryPermitTotals[2];
$qtr4Sanitary = $yrSanitaryPermitTotals[3];
$sheet->setCellValue('D31', $qtr1Sanitary); // Q1
$sheet->setCellValue('E31', $qtr2Sanitary); // Q2
$sheet->setCellValue('F31', $qtr3Sanitary); // Q3
$sheet->setCellValue('G31', $qtr4Sanitary); // Q4

$yrMarriageLicenseTotals = getMarriageLicenseQuarterlyTotals($conn, $year);
$qtr1MarriageLicense = $yrMarriageLicenseTotals[0];
$qtr2MarriageLicense = $yrMarriageLicenseTotals[1];
$qtr3MarriageLicense = $yrMarriageLicenseTotals[2];
$qtr4MarriageLicense = $yrMarriageLicenseTotals[3];
$sheet->setCellValue('D34', $qtr1MarriageLicense); // Q1
$sheet->setCellValue('E34', $qtr2MarriageLicense); // Q2
$sheet->setCellValue('F34', $qtr3MarriageLicense); // Q3
$sheet->setCellValue('G34', $qtr4MarriageLicense); // Q4

$yrMarriageApplicationTotals = getMarriageApplicationQuarterlyTotals($conn, $year);
$qtr1MarriageApplication = $yrMarriageApplicationTotals[0];
$qtr2MarriageApplication = $yrMarriageApplicationTotals[1];
$qtr3MarriageApplication = $yrMarriageApplicationTotals[2];
$qtr4MarriageApplication = $yrMarriageApplicationTotals[3];
$sheet->setCellValue('D35', $qtr1MarriageApplication); // Q1
$sheet->setCellValue('E35', $qtr2MarriageApplication); // Q2
$sheet->setCellValue('F35', $qtr3MarriageApplication); // Q3
$sheet->setCellValue('G35', $qtr4MarriageApplication); // Q4

$yrPoliceClearanceTotals = getPoliceClearanceQuarterlyTotals($conn, $year);
$qtr1PoliceClearance = $yrPoliceClearanceTotals[0];
$qtr2PoliceClearance = $yrPoliceClearanceTotals[1];
$qtr3PoliceClearance = $yrPoliceClearanceTotals[2];
$qtr4PoliceClearance = $yrPoliceClearanceTotals[3];
$sheet->setCellValue('D46', $qtr1PoliceClearance); // Q1
$sheet->setCellValue('E46', $qtr2PoliceClearance); // Q2
$sheet->setCellValue('F46', $qtr3PoliceClearance); // Q3
$sheet->setCellValue('G46', $qtr4PoliceClearance);

$yrDeathCertTotals = getDeathCertQuarterlyTotals($conn, $year);
$qtr1DeathCert = $yrDeathCertTotals[0];
$qtr2DeathCert = $yrDeathCertTotals[1];
$qtr3DeathCert = $yrDeathCertTotals[2];
$qtr4DeathCert = $yrDeathCertTotals[3];
$sheet->setCellValue('D49', $qtr1DeathCert); // Q1
$sheet->setCellValue('E49', $qtr2DeathCert); // Q2
$sheet->setCellValue('F49', $qtr3DeathCert); // Q3
$sheet->setCellValue('G49', $qtr4DeathCert);

$yrBirthCertTotals = getBirthCertQuarterlyTotals($conn, $year);
$qtr1BirthCert = $yrBirthCertTotals[0];
$qtr2BirthCert = $yrBirthCertTotals[1];
$qtr3BirthCert = $yrBirthCertTotals[2];
$qtr4BirthCert = $yrBirthCertTotals[3];
$sheet->setCellValue('D50', $qtr1BirthCert); // Q1
$sheet->setCellValue('E50', $qtr2BirthCert); // Q2
$sheet->setCellValue('F50', $qtr3BirthCert); // Q3
$sheet->setCellValue('G50', $qtr4BirthCert);

$yrMarriageCertTotals = getMarriageCertQuarterlyTotals($conn, $year);
$qtr1MarriageCert = $yrMarriageCertTotals[0];
$qtr2MarriageCert = $yrMarriageCertTotals[1];
$qtr3MarriageCert = $yrMarriageCertTotals[2];
$qtr4MarriageCert = $yrMarriageCertTotals[3];
$sheet->setCellValue('D51', $qtr1MarriageCert); // Q1
$sheet->setCellValue('E51', $qtr2MarriageCert); // Q2
$sheet->setCellValue('F51', $qtr3MarriageCert); // Q3
$sheet->setCellValue('G51', $qtr4MarriageCert);

$yrAssesorCertTotals = getAssesorCertQuarterlyTotals($conn, $year);
$qtr1AssesorCert = $yrAssesorCertTotals[0];
$qtr2AssesorCert = $yrAssesorCertTotals[1];
$qtr3AssesorCert = $yrAssesorCertTotals[2];
$qtr4AssesorCert = $yrAssesorCertTotals[3];
$sheet->setCellValue('D52', $qtr1AssesorCert); // Q1
$sheet->setCellValue('E52', $qtr2AssesorCert); // Q2
$sheet->setCellValue('F52', $qtr3AssesorCert); // Q3
$sheet->setCellValue('G52', $qtr4AssesorCert);

$yrMTOCertTotals = getMTOCertQuarterlyTotals($conn, $year);
$qtr1MTOCert = $yrMTOCertTotals[0];
$qtr2MTOCert = $yrMTOCertTotals[1];
$qtr3MTOCert = $yrMTOCertTotals[2];
$qtr4MTOCert = $yrMTOCertTotals[3];
$sheet->setCellValue('D53', $qtr1MTOCert); // Q1
$sheet->setCellValue('E53', $qtr2MTOCert); // Q2
$sheet->setCellValue('F53', $qtr3MTOCert); // Q3
$sheet->setCellValue('G53', $qtr4MTOCert);

$yrHealthCertTotals = getHealthCertQuarterlyTotals($conn, $year);
$qtr1HealthCert = $yrHealthCertTotals[0];
$qtr2HealthCert = $yrHealthCertTotals[1];
$qtr3HealthCert = $yrHealthCertTotals[2];
$qtr4HealthCert = $yrHealthCertTotals[3];
$sheet->setCellValue('D54', $qtr1HealthCert); // Q1
$sheet->setCellValue('E54', $qtr2HealthCert); // Q2
$sheet->setCellValue('F54', $qtr3HealthCert); // Q3
$sheet->setCellValue('G54', $qtr4HealthCert);

$yrSecretaryFeeTotals = getSecretaryFeeQuarterlyTotals($conn, $year);
$qtr1SecretaryFee = $yrSecretaryFeeTotals[0];
$qtr2SecretaryFee = $yrSecretaryFeeTotals[1];
$qtr3SecretaryFee = $yrSecretaryFeeTotals[2];
$qtr4SecretaryFee = $yrSecretaryFeeTotals[3];
$sheet->setCellValue('D55', $qtr1SecretaryFee); // Q1
$sheet->setCellValue('E55', $qtr2SecretaryFee); // Q2
$sheet->setCellValue('F55', $qtr3SecretaryFee); // Q3
$sheet->setCellValue('G55', $qtr4SecretaryFee);

$yrOtherMayorClearanceTotals = getOtherMayorClearanceQuarterlyTotals($conn, $year);
$qtr1OtherMayorClearance = $yrOtherMayorClearanceTotals[0];
$qtr2OtherMayorClearance = $yrOtherMayorClearanceTotals[1];
$qtr3OtherMayorClearance = $yrOtherMayorClearanceTotals[2];
$qtr4OtherMayorClearance = $yrOtherMayorClearanceTotals[3];
$sheet->setCellValue('D56', $qtr1OtherMayorClearance); // Q1
$sheet->setCellValue('E56', $qtr2OtherMayorClearance); // Q2
$sheet->setCellValue('F56', $qtr3OtherMayorClearance); // Q3
$sheet->setCellValue('G56', $qtr4OtherMayorClearance);

$yrMedicalDentalLabTotals = getMedicalDentalLabQuarterlyTotals($conn, $year);
$qtr1MedicalDentalLab = $yrMedicalDentalLabTotals[0];
$qtr2MedicalDentalLab = $yrMedicalDentalLabTotals[1];
$qtr3MedicalDentalLab = $yrMedicalDentalLabTotals[2];
$qtr4MedicalDentalLab = $yrMedicalDentalLabTotals[3];
$sheet->setCellValue('D58', $qtr1MedicalDentalLab); // Q1
$sheet->setCellValue('E58', $qtr2MedicalDentalLab); // Q2
$sheet->setCellValue('F58', $qtr3MedicalDentalLab); // Q3
$sheet->setCellValue('G58', $qtr4MedicalDentalLab);

$yrSolemnizationFeeTotals = getSolemnizationFeeQuarterlyTotals($conn, $year);
$qtr1SolemnizationFee = $yrSolemnizationFeeTotals[0];
$qtr2SolemnizationFee = $yrSolemnizationFeeTotals[1];
$qtr3SolemnizationFee = $yrSolemnizationFeeTotals[2];
$qtr4SolemnizationFee = $yrSolemnizationFeeTotals[3];
$sheet->setCellValue('D61', $qtr1SolemnizationFee); // Q1
$sheet->setCellValue('E61', $qtr2SolemnizationFee); // Q2
$sheet->setCellValue('F61', $qtr3SolemnizationFee); // Q3
$sheet->setCellValue('G61', $qtr4SolemnizationFee);

$yrAnnotationFeeTotals = getAnnotationFeeQuarterlyTotals($conn, $year);
$qtr1AnnotationFee = $yrAnnotationFeeTotals[0];
$qtr2AnnotationFee = $yrAnnotationFeeTotals[1];
$qtr3AnnotationFee = $yrAnnotationFeeTotals[2];
$qtr4AnnotationFee = $yrAnnotationFeeTotals[3];
$sheet->setCellValue('D63', $qtr1AnnotationFee); // Q1
$sheet->setCellValue('E63', $qtr2AnnotationFee); // Q2
$sheet->setCellValue('F63', $qtr3AnnotationFee); // Q3
$sheet->setCellValue('G63', $qtr4AnnotationFee);

$yrMiscFeeTotals = getMiscFeeQuarterlyTotals($conn, $year);
$qtr1MiscFee = $yrMiscFeeTotals[0];
$qtr2MiscFee = $yrMiscFeeTotals[1];
$qtr3MiscFee = $yrMiscFeeTotals[2];
$qtr4MiscFee = $yrMiscFeeTotals[3];
$sheet->setCellValue('D66', $qtr1MiscFee); // Q1
$sheet->setCellValue('E66', $qtr2MiscFee); // Q2
$sheet->setCellValue('F66', $qtr3MiscFee); // Q3
$sheet->setCellValue('G66', $qtr4MiscFee);

$yrRATotals = getRAQuarterlyTotals($conn, $year);
$qtr1RA = $yrRATotals[0];
$qtr2RA = $yrRATotals[1];
$qtr3RA = $yrRATotals[2];
$qtr4RA = $yrRATotals[3];
$sheet->setCellValue('D67', $qtr1RA); // Q1
$sheet->setCellValue('E67', $qtr2RA); // Q2
$sheet->setCellValue('F67', $qtr3RA); // Q3
$sheet->setCellValue('G67', $qtr4RA);

$yrCemeteriesTotals = getCemeteriesQuarterlyTotals($conn, $year);
$qtr1Cemeteries = $yrCemeteriesTotals[0];
$qtr2Cemeteries = $yrCemeteriesTotals[1];
$qtr3Cemeteries = $yrCemeteriesTotals[2];
$qtr4Cemeteries = $yrCemeteriesTotals[3];
$sheet->setCellValue('D74', $qtr1Cemeteries); // Q1
$sheet->setCellValue('E74', $qtr2Cemeteries); // Q2
$sheet->setCellValue('F74', $qtr3Cemeteries); // Q3
$sheet->setCellValue('G74', $qtr4Cemeteries);

$yrInspectionFeeTotals = getInspectionFeeQuarterlyTotals($conn, $year);
$qtr1InspectionFee = $yrInspectionFeeTotals[0];
$qtr2InspectionFee = $yrInspectionFeeTotals[1];
$qtr3InspectionFee = $yrInspectionFeeTotals[2];
$qtr4InspectionFee = $yrInspectionFeeTotals[3];
$sheet->setCellValue('D41', $qtr1InspectionFee); // Q1
$sheet->setCellValue('E41', $qtr2InspectionFee); // Q2
$sheet->setCellValue('F41', $qtr3InspectionFee); // Q3
$sheet->setCellValue('G41', $qtr4InspectionFee);

$yrElectricalFeeTotals = getElectricalFeeQuarterlyTotals($conn, $year);
$qtr1ElectricalFee = $yrElectricalFeeTotals[0];
$qtr2ElectricalFee = $yrElectricalFeeTotals[1];
$qtr3ElectricalFee = $yrElectricalFeeTotals[2];
$qtr4ElectricalFee = $yrElectricalFeeTotals[3];
$sheet->setCellValue('D62', $qtr1ElectricalFee); // Q1
$sheet->setCellValue('E62', $qtr2ElectricalFee); // Q2
$sheet->setCellValue('F62', $qtr3ElectricalFee); // Q3
$sheet->setCellValue('G62', $qtr4ElectricalFee);

$yrBuildingPermitFeeTotals = getBuildingPermitFeeQuarterlyTotals($conn, $year);
$qtr1BuildingPermitFee = $yrBuildingPermitFeeTotals[0];
$qtr2BuildingPermitFee = $yrBuildingPermitFeeTotals[1];
$qtr3BuildingPermitFee = $yrBuildingPermitFeeTotals[2];
$qtr4BuildingPermitFee = $yrBuildingPermitFeeTotals[3];
$sheet->setCellValue('D28', $qtr1BuildingPermitFee); // Q1
$sheet->setCellValue('E28', $qtr2BuildingPermitFee); // Q2
$sheet->setCellValue('F28', $qtr3BuildingPermitFee); // Q3
$sheet->setCellValue('G28', $qtr4BuildingPermitFee);

$yrZoningClearanceTotals = getZoningClearanceQuarterlyTotals($conn, $year);
$qtr1ZoningClearance = $yrZoningClearanceTotals[0];
$qtr2ZoningClearance = $yrZoningClearanceTotals[1];
$qtr3ZoningClearance = $yrZoningClearanceTotals[2];
$qtr4ZoningClearance = $yrZoningClearanceTotals[3];
$sheet->setCellValue('D29', $qtr1ZoningClearance); // Q1
$sheet->setCellValue('E29', $qtr2ZoningClearance); // Q2
$sheet->setCellValue('F29', $qtr3ZoningClearance); // Q3
$sheet->setCellValue('G29', $qtr4ZoningClearance);

// Birth Certificate (New Born) Fees
$yrBirthCertNewBornTotals = getBirthCertificateNewBornQuarterlyTotals($conn, $year);
$qtr1BirthCertNewBorn = $yrBirthCertNewBornTotals[0];
$qtr2BirthCertNewBorn = $yrBirthCertNewBornTotals[1];
$qtr3BirthCertNewBorn = $yrBirthCertNewBornTotals[2];
$qtr4BirthCertNewBorn = $yrBirthCertNewBornTotals[3];
$sheet->setCellValue('D39', $qtr1BirthCertNewBorn); // Q1
$sheet->setCellValue('E39', $qtr2BirthCertNewBorn); // Q2
$sheet->setCellValue('F39', $qtr3BirthCertNewBorn); // Q3
$sheet->setCellValue('G39', $qtr4BirthCertNewBorn); // Q4

// Amusement Tax
$yrAmusementTaxTotals = getAmusementTaxQuarterlyTotals($conn, $year);
$qtr1AmusementTax = $yrAmusementTaxTotals[0];
$qtr2AmusementTax = $yrAmusementTaxTotals[1];
$qtr3AmusementTax = $yrAmusementTaxTotals[2];
$qtr4AmusementTax = $yrAmusementTaxTotals[3];
$sheet->setCellValue('D10', $qtr1AmusementTax);
$sheet->setCellValue('E10', $qtr2AmusementTax);
$sheet->setCellValue('F10', $qtr3AmusementTax);
$sheet->setCellValue('G10', $qtr4AmusementTax);

// Business Taxes - Banks
$yrBusinessTaxesBanksTotals = getBusinessTaxesBanksQuarterlyTotals($conn, $year);
$qtr1BusinessTaxesBanks = $yrBusinessTaxesBanksTotals[0];
$qtr2BusinessTaxesBanks = $yrBusinessTaxesBanksTotals[1];
$qtr3BusinessTaxesBanks = $yrBusinessTaxesBanksTotals[2];
$qtr4BusinessTaxesBanks = $yrBusinessTaxesBanksTotals[3];
$sheet->setCellValue('D15', $qtr1BusinessTaxesBanks);
$sheet->setCellValue('E15', $qtr2BusinessTaxesBanks);
$sheet->setCellValue('F15', $qtr3BusinessTaxesBanks);
$sheet->setCellValue('G15', $qtr4BusinessTaxesBanks);

// Business Taxes - Contractors
$yrBusinessTaxesContractorsTotals = getBusinessTaxesContractorsQuarterlyTotals($conn, $year);
$qtr1BusinessTaxesContractors = $yrBusinessTaxesContractorsTotals[0];
$qtr2BusinessTaxesContractors = $yrBusinessTaxesContractorsTotals[1];
$qtr3BusinessTaxesContractors = $yrBusinessTaxesContractorsTotals[2];
$qtr4BusinessTaxesContractors = $yrBusinessTaxesContractorsTotals[3];
$sheet->setCellValue('D14', $qtr1BusinessTaxesContractors);
$sheet->setCellValue('E14', $qtr2BusinessTaxesContractors);
$sheet->setCellValue('F14', $qtr3BusinessTaxesContractors);
$sheet->setCellValue('G14', $qtr4BusinessTaxesContractors);

// Business Taxes - Retailers
$yrBusinessTaxesRetailersTotals = getBusinessTaxesRetailersQuarterlyTotals($conn, $year);
$qtr1BusinessTaxesRetailers = $yrBusinessTaxesRetailersTotals[0];
$qtr2BusinessTaxesRetailers = $yrBusinessTaxesRetailersTotals[1];
$qtr3BusinessTaxesRetailers = $yrBusinessTaxesRetailersTotals[2];
$qtr4BusinessTaxesRetailers = $yrBusinessTaxesRetailersTotals[3];
$sheet->setCellValue('D13', $qtr1BusinessTaxesRetailers);
$sheet->setCellValue('E13', $qtr2BusinessTaxesRetailers);
$sheet->setCellValue('F13', $qtr3BusinessTaxesRetailers);
$sheet->setCellValue('G13', $qtr4BusinessTaxesRetailers);

// Community Tax - Corporation
$yrCommunityTaxCorporationTotals = getCommunityTaxCorporationQuarterlyTotals($conn, $year);
$qtr1CommunityTaxCorporation = $yrCommunityTaxCorporationTotals[0];
$qtr2CommunityTaxCorporation = $yrCommunityTaxCorporationTotals[1];
$qtr3CommunityTaxCorporation = $yrCommunityTaxCorporationTotals[2];
$qtr4CommunityTaxCorporation = $yrCommunityTaxCorporationTotals[3];
$sheet->setCellValue('D19', $qtr1CommunityTaxCorporation);
$sheet->setCellValue('E19', $qtr2CommunityTaxCorporation);
$sheet->setCellValue('F19', $qtr3CommunityTaxCorporation);
$sheet->setCellValue('G19', $qtr4CommunityTaxCorporation);

// Community Tax - Individual
$yrCommunityTaxIndividualTotals = getCommunityTaxIndividualQuarterlyTotals($conn, $year);
$qtr1CommunityTaxIndividual = $yrCommunityTaxIndividualTotals[0];
$qtr2CommunityTaxIndividual = $yrCommunityTaxIndividualTotals[1];
$qtr3CommunityTaxIndividual = $yrCommunityTaxIndividualTotals[2];
$qtr4CommunityTaxIndividual = $yrCommunityTaxIndividualTotals[3];
$sheet->setCellValue('D20', $qtr1CommunityTaxIndividual);
$sheet->setCellValue('E20', $qtr2CommunityTaxIndividual);
$sheet->setCellValue('F20', $qtr3CommunityTaxIndividual);
$sheet->setCellValue('G20', $qtr4CommunityTaxIndividual);

// Garbage Fees
$yrGarbageFeesTotals = getGarbageFeesQuarterlyTotals($conn, $year);
$qtr1GarbageFees = $yrGarbageFeesTotals[0];
$qtr2GarbageFees = $yrGarbageFeesTotals[1];
$qtr3GarbageFees = $yrGarbageFeesTotals[2];
$qtr4GarbageFees = $yrGarbageFeesTotals[3];
$sheet->setCellValue('D57', $qtr1GarbageFees);
$sheet->setCellValue('E57', $qtr2GarbageFees);
$sheet->setCellValue('F57', $qtr3GarbageFees);
$sheet->setCellValue('G57', $qtr4GarbageFees);

// Interest Income
$yrInterestIncomeTotals = getInterestIncomeQuarterlyTotals($conn, $year);
$qtr1InterestIncome = $yrInterestIncomeTotals[0];
$qtr2InterestIncome = $yrInterestIncomeTotals[1];
$qtr3InterestIncome = $yrInterestIncomeTotals[2];
$qtr4InterestIncome = $yrInterestIncomeTotals[3];
$sheet->setCellValue('D69', $qtr1InterestIncome);
$sheet->setCellValue('E69', $qtr2InterestIncome);
$sheet->setCellValue('F69', $qtr3InterestIncome);
$sheet->setCellValue('G69', $qtr4InterestIncome);

// Marilag Ecopark
$yrMarilagEcoparkTotals = getMarilagEcoparkQuarterlyTotals($conn, $year);
$qtr1MarilagEcopark = $yrMarilagEcoparkTotals[0];
$qtr2MarilagEcopark = $yrMarilagEcoparkTotals[1];
$qtr3MarilagEcopark = $yrMarilagEcoparkTotals[2];
$qtr4MarilagEcopark = $yrMarilagEcoparkTotals[3];
$sheet->setCellValue('D70', $qtr1MarilagEcopark);
$sheet->setCellValue('E70', $qtr2MarilagEcopark);
$sheet->setCellValue('F70', $qtr3MarilagEcopark);
$sheet->setCellValue('G70', $qtr4MarilagEcopark);

// Municipal Lot
$yrMunicipalLotTotals = getMunicipalLotQuarterlyTotals($conn, $year);
$qtr1MunicipalLot = $yrMunicipalLotTotals[0];
$qtr2MunicipalLot = $yrMunicipalLotTotals[1];
$qtr3MunicipalLot = $yrMunicipalLotTotals[2];
$qtr4MunicipalLot = $yrMunicipalLotTotals[3];
$sheet->setCellValue('D75', $qtr1MunicipalLot);
$sheet->setCellValue('E75', $qtr2MunicipalLot);
$sheet->setCellValue('F75', $qtr3MunicipalLot);
$sheet->setCellValue('G75', $qtr4MunicipalLot);

// Slaughter House Operations
$yrSlaughterHouseTotals = getSlaughterHouseOperationsQuarterlyTotals($conn, $year);
$qtr1SlaughterHouse = $yrSlaughterHouseTotals[0];
$qtr2SlaughterHouse = $yrSlaughterHouseTotals[1];
$qtr3SlaughterHouse = $yrSlaughterHouseTotals[2];
$qtr4SlaughterHouse = $yrSlaughterHouseTotals[3];
$sheet->setCellValue('D81', $qtr1SlaughterHouse);
$sheet->setCellValue('E81', $qtr2SlaughterHouse);
$sheet->setCellValue('F81', $qtr3SlaughterHouse);
$sheet->setCellValue('G81', $qtr4SlaughterHouse);

// Stall Goodwill
$yrStallGoodwillTotals = getStallGoodwillQuarterlyTotals($conn, $year);
$qtr1StallGoodwill = $yrStallGoodwillTotals[0];
$qtr2StallGoodwill = $yrStallGoodwillTotals[1];
$qtr3StallGoodwill = $yrStallGoodwillTotals[2];
$qtr4StallGoodwill = $yrStallGoodwillTotals[3];
$sheet->setCellValue('D78', $qtr1StallGoodwill);
$sheet->setCellValue('E78', $qtr2StallGoodwill);
$sheet->setCellValue('F78', $qtr3StallGoodwill);
$sheet->setCellValue('G78', $qtr4StallGoodwill);

// Stall Rentals
$yrStallRentalsTotals = getStallRentalsQuarterlyTotals($conn, $year);
$qtr1StallRentals = $yrStallRentalsTotals[0];
$qtr2StallRentals = $yrStallRentalsTotals[1];
$qtr3StallRentals = $yrStallRentalsTotals[2];
$qtr4StallRentals = $yrStallRentalsTotals[3];
$sheet->setCellValue('D79', $qtr1StallRentals);
$sheet->setCellValue('E79', $qtr2StallRentals);
$sheet->setCellValue('F79', $qtr3StallRentals);
$sheet->setCellValue('G79', $qtr4StallRentals);

// STL
$yrSTLTotals = getSTLQuarterlyTotals($conn, $year);
$qtr1STL = $yrSTLTotals[0];
$qtr2STL = $yrSTLTotals[1];
$qtr3STL = $yrSTLTotals[2];
$qtr4STL = $yrSTLTotals[3];
$sheet->setCellValue('D64', $qtr1STL);
$sheet->setCellValue('E64', $qtr2STL);
$sheet->setCellValue('F64', $qtr3STL);
$sheet->setCellValue('G64', $qtr4STL);

// Water Works Monthly Dues
$yrWaterWorksMonthlyDuesTotals = getWaterWorksMonthlyDuesQuarterlyTotals($conn, $year);
$qtr1WaterWorksMonthlyDues = $yrWaterWorksMonthlyDuesTotals[0];
$qtr2WaterWorksMonthlyDues = $yrWaterWorksMonthlyDuesTotals[1];
$qtr3WaterWorksMonthlyDues = $yrWaterWorksMonthlyDuesTotals[2];
$qtr4WaterWorksMonthlyDues = $yrWaterWorksMonthlyDuesTotals[3];
$sheet->setCellValue('D85', $qtr1WaterWorksMonthlyDues);
$sheet->setCellValue('E85', $qtr2WaterWorksMonthlyDues);
$sheet->setCellValue('F85', $qtr3WaterWorksMonthlyDues);
$sheet->setCellValue('G85', $qtr4WaterWorksMonthlyDues);

// Water Works Registration
$yrWaterWorksRegistrationTotals = getWaterWorksRegistrationQuarterlyTotals($conn, $year);
$qtr1WaterWorksRegistration = $yrWaterWorksRegistrationTotals[0];
$qtr2WaterWorksRegistration = $yrWaterWorksRegistrationTotals[1];
$qtr3WaterWorksRegistration = $yrWaterWorksRegistrationTotals[2];
$qtr4WaterWorksRegistration = $yrWaterWorksRegistrationTotals[3];
$sheet->setCellValue('D84', $qtr1WaterWorksRegistration);
$sheet->setCellValue('E84', $qtr2WaterWorksRegistration);
$sheet->setCellValue('F84', $qtr3WaterWorksRegistration);
$sheet->setCellValue('G84', $qtr4WaterWorksRegistration);

// Get Interest / Surcharge totals per quarter
$yrInterestSurchargeTotals = getInterestSurchargeQuarterlyTotals($conn, $year);
$qtr1InterestSurcharge = $yrInterestSurchargeTotals[0];
$qtr2InterestSurcharge = $yrInterestSurchargeTotals[1];
$qtr3InterestSurcharge = $yrInterestSurchargeTotals[2];
$qtr4InterestSurcharge = $yrInterestSurchargeTotals[3];
// Write to Excel sheet (row/column references are just examples)
$sheet->setCellValue('D16', $qtr1InterestSurcharge); // Q1
$sheet->setCellValue('E16', $qtr2InterestSurcharge); // Q2
$sheet->setCellValue('F16', $qtr3InterestSurcharge); // Q3
$sheet->setCellValue('G16', $qtr4InterestSurcharge); // Q4

// Get BREQS totals per quarter
$yrBREQSTotals = getBREQSQuarterlyTotals($conn, $year);
$qtr1BREQS = $yrBREQSTotals[0];
$qtr2BREQS = $yrBREQSTotals[1];
$qtr3BREQS = $yrBREQSTotals[2]; 
$qtr4BREQS = $yrBREQSTotals[3];
// Write to Excel sheet (adjust cell references as needed)
$sheet->setCellValue('D68', $qtr1BREQS); // Q1
$sheet->setCellValue('E68', $qtr2BREQS); // Q2
$sheet->setCellValue('F68', $qtr3BREQS); // Q3
$sheet->setCellValue('G68', $qtr4BREQS); // Q4


// Get Backfilling/Hauling totals per quarter
$yrBackfillingHaulingTotals = getBackfillingHaulingQuarterlyTotals($conn, $year);
$qtr1BackfillingHauling = $yrBackfillingHaulingTotals[0];
$qtr2BackfillingHauling = $yrBackfillingHaulingTotals[1];
$qtr3BackfillingHauling = $yrBackfillingHaulingTotals[2];
$qtr4BackfillingHauling = $yrBackfillingHaulingTotals[3];
// Write to Excel sheet (adjust the row number as needed)
$sheet->setCellValue('D65', $qtr1BackfillingHauling); // Q1
$sheet->setCellValue('E65', $qtr2BackfillingHauling); // Q2
$sheet->setCellValue('F65', $qtr3BackfillingHauling); // Q3
$sheet->setCellValue('G65', $qtr4BackfillingHauling); // Q4

// Get yearly totals broken down by quarter
$yrCattleAnimalRegistrationTotals = getCattleAnimalRegistrationQuarterlyTotals($conn, $year);
// Assign each quarter to a separate variable
$qtr1CattleAnimalRegistration = $yrCattleAnimalRegistrationTotals[0];
$qtr2CattleAnimalRegistration = $yrCattleAnimalRegistrationTotals[1];
$qtr3CattleAnimalRegistration = $yrCattleAnimalRegistrationTotals[2];
$qtr4CattleAnimalRegistration = $yrCattleAnimalRegistrationTotals[3];
// Write to Excel sheet (adjust the row number as needed)
$sheet->setCellValue('D38', $qtr1CattleAnimalRegistration); // Q1
$sheet->setCellValue('E38', $qtr2CattleAnimalRegistration); // Q2
$sheet->setCellValue('F38', $qtr3CattleAnimalRegistration); // Q3
$sheet->setCellValue('G38', $qtr4CattleAnimalRegistration); // Q4

// Get yearly totals broken down by quarter
$yrOthersFeeTotals = getOthersFeeQuarterlyTotals($conn, $year);
// Assign each quarter to a separate variable
$qtr1OthersFee = $yrOthersFeeTotals[0];
$qtr2OthersFee = $yrOthersFeeTotals[1];
$qtr3OthersFee = $yrOthersFeeTotals[2];
$qtr4OthersFee = $yrOthersFeeTotals[3];
// Write to Excel sheet (adjust the row number as needed)
$sheet->setCellValue('D89', $qtr1OthersFee); // Q1
$sheet->setCellValue('E89', $qtr2OthersFee); // Q2
$sheet->setCellValue('F89', $qtr3OthersFee); // Q3
$sheet->setCellValue('G89', $qtr4OthersFee); // Q4


//-------------------------------------------------------------------------------------------------------

// Add the main content structure (similar to ANNUAL sheet)
$content = [
    // TAX ON BUSINESS section
    [9, 'TAX ON BUSINESS', '', '', '', '', '', '', ''],
    [10, '', 'Amusement Tax (Provincial Account)', '', '', '', '', '', ''],
    [12, '', 'Business Taxes', '', '', '', '', '', ''],
    [13, '', '', 'Retailers', '', '', '', '', ''],
    [14, '', '', 'Contractors', '', '', '', '', ''],
    [15, '', '', 'Banks & Other Financial Institutions', '', '', '', '', ''],
    [16, '', '', 'Interest / Surcharge', '', '', '', '', ''],

    // OTHER TAXES section
    [18, 'OTHER TAXES:', '', '', '', '', '', '', ''],
    [19, '', 'Community Tax - Corporation', '', '', '', '', '', ''],
    [20, '', 'Community Tax - Individual', '', '', '', '', '', ''],

    // REGULATORY FEES section
    [22, 'REGULATORY FEES (PERMITS & LICENSES)', '', '', '', '', '', '', ''],
    [24, '', 'Permits and Licenses', '', '', '', '', '', ''],
    [25, '', '', 'Fees on weights & measures', '', '', '', '', ''],
    [26, '', '', 'Franchising & licensing Fees', '', '', '', '', ''],
    [27, '', '', 'Permit Fees/ Mayor\'s Permit Fees', '', '', '', '', ''],
    [28, '', '', 'Building Permit Fees', '', '', '', '', ''],
    [29, '', '', 'Zonal/Location Permit Fees', '', '', '', '', ''],
    [30, '', '', 'Burial Permit Fees', '', '', '', '', ''],
    [31, '', '', 'Sanitary Permit Fees', '', '', '', '', ''],

    // Other Permits & Licenses
    [33, '', 'Other Permits & Licenses:', '', '', '', '', '', ''],
    [34, '', '', 'Marriage License', '', '', '', '', ''],
    [35, '', '', 'Application for Marriage', '', '', '', '', ''],

    // Registration Fees
    [37, '', 'Registration Fees:', '', '', '', '', '', ''],
    [38, '', '', 'Cattle/Animal Registration fees', '', '', '', '', ''],
    [39, '', '', 'Birth Certificate (New Born) Fees', '', '', '', '', ''],

    // Inspection Fees
    [41, '', 'Inspection Fees', '', '', '', '', '', ''],

    // SERVICE/USER CHARGES section
    [43, 'SERVICE/ USER CHARGES (Service Income)', '', '', '', '', '', '', ''],
    [45, '', 'Clearance and Certification Fees', '', '', '', '', '', ''],
    [46, '', '', 'Police Clearance', '', '', '', '', ''],

    // Secretary's Fees
    [48, '', 'Secretary\'s Fees', '', '', '', '', '', ''],
    [49, '', '', 'Death Certificate', '', '', '', '', ''],
    [50, '', '', 'Birth Certificate', '', '', '', '', ''],
    [51, '', '', 'Marriage Certificate', '', '', '', '', ''],
    [52, '', '', 'Assessor\'s Certificate', '', '', '', '', ''],
    [53, '', '', 'MTO certificate', '', '', '', '', ''],
    [54, '', '', 'Health Certificate (Medical)', '', '', '', '', ''],
    [55, '', '', 'Secretary\'s Fees', '', '', '', '', ''],

    // Other Clearance & Cert.
    [56, '', 'Other Clearance & Cert./Mayor\'s Clearance', '', '', '', '', '', ''],
    [57, '', 'Garbage Fees', '', '', '', '', '', ''],
    [58, '', 'Medical, Dental & Laboratory Fees', '', '', '', '', '', ''],

    // Other Service Income
    [60, '', 'Other Service Income', '', '', '', '', '', ''],
    [61, '', '', 'Solemnization Fees', '', '', '', '', ''],
    [62, '', '', 'Electrical Fees', '', '', '', '', ''],
    [63, '', '', 'Annotation Fees', '', '', '', '', ''],
    [64, '', '', 'STL', '', '', '', '', ''],
    [65, '', '', 'Backfilling/Hauling', '', '', '', '', ''],
    [66, '', '', 'Miscellaneous Fees', '', '', '', '', ''],
    [67, '', '', 'RA 9048, 10172 & 9255', '', '', '', '', ''],
    [68, '', '', 'BREQS', '', '', '', '', ''],
    [69, '', '', 'Interest Income', '', '', '', '', ''],
    [70, '', '', 'Marilag Ecopark', '', '', '', '', ''],

    // RECEIPTS FROM ECONOMIC ENTERPRISES section
    [72, 'RECEIPTS FROM ECONOMIC ENTERPRISES (BUSINESS INCOME)', '', '', '', '', '', '', ''],

    // Cemetery Operations
    [73, '', 'Cemetery Operations:', '', '', '', '', '', ''],
    [74, '', '', 'Cemeteries', '', '', '', '', ''],
    [75, '', '', 'Municipal Lot', '', '', '', '', ''],

    // Market Operations
    [77, '', 'Market Operations:', '', '', '', '', '', ''],
    [78, '', '', 'Stall Goodwill', '', '', '', '', ''],
    [79, '', '', 'Stall Rentals', '', '', '', '', ''],

    // Slaughter House Operations
    [81, '', 'Slaughter House Operations:', '', '', '', '', '', ''],

    // Water Works System Operations
    [83, '', 'Water Works System Operations', '', '', '', '', '', ''],
    [84, '', '', 'Registration', '', '', '', '', ''],
    [85, '', '', 'Monthly dues', '', '', '', '', ''],

    // LOCAL SOURCES section
    [87, 'LOCAL SOURCES', '', '', '', '', '', '', ''],

    // OTHER INCOME section
    [88, 'OTHER INCOME/RECEIPTS (OTHER GENERAL INCOME)', '', '', '', '', '', '', ''],
    [89, '', 'IRA', '', '', '', '', '', ''],
    [90, '', 'IRA - BHAS', '', '', '', '', '', ''],

    // GRAND TOTAL
    [92, '', '', 'GRAND TOTAL', '', '', '', '', ''],

    
];

// --- Cell content ---

$sheet->setCellValue('E98', $treasurerName); // Dynamic name of treasurer

$sheet->setCellValue('E99', 'Municipal Treasurer');

// --- Formatting ---

$sheet->getStyle('E98')->getFont()->setUnderline(true);

// Italicize positions
$sheet->getStyle('E99')->getFont()->setItalic(true);

// Center-align names and positions
$sheet->getStyle('E98')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

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
    if (!empty($item[8])) $sheet->setCellValue('H' . $row, $item[8]);

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
$sheet->setCellValue('G9', '=SUM(G10,G12,G18)');
$sheet->setCellValue('H9', '=SUM(D9:G9)');

$sheet->setCellValue('H10', '=SUM(D10:G10)');

$sheet->setCellValue('D12', '=SUM(D13:D16)');
$sheet->setCellValue('E12', '=SUM(E13:E16)');
$sheet->setCellValue('F12', '=SUM(F13:F16)');
$sheet->setCellValue('G12', '=SUM(G13:G16)');
$sheet->setCellValue('H12', '=SUM(D12:G12)');

$sheet->setCellValue('H13', '=SUM(D13:G13)');
$sheet->setCellValue('H14', '=SUM(D14:G14)');
$sheet->setCellValue('H15', '=SUM(D15:G15)');
$sheet->setCellValue('H16', '=SUM(D16:G16)');

// OTHER TAXES section
$sheet->setCellValue('D18', '=SUM(D19:D20)');
$sheet->setCellValue('E18', '=SUM(E19:E20)');
$sheet->setCellValue('F18', '=SUM(F19:F20)');
$sheet->setCellValue('G18', '=SUM(G19:G20)');
$sheet->setCellValue('H18', '=SUM(D18:G18)');

$sheet->setCellValue('H19', '=SUM(D19:G19)');
$sheet->setCellValue('H20', '=SUM(D20:G20)');

// REGULATORY FEES section
$sheet->setCellValue('D22', '=SUM(D24,D33,D37,D41)');
$sheet->setCellValue('E22', '=SUM(E24,E33,E37,E41)');
$sheet->setCellValue('F22', '=SUM(F24,F33,F37,F41)');
$sheet->setCellValue('G22', '=SUM(G24,G33,G37,G41)');
$sheet->setCellValue('H22', '=SUM(D22:G22)');

$sheet->setCellValue('D24', '=SUM(D25:D31)');
$sheet->setCellValue('E24', '=SUM(E25:E31)');
$sheet->setCellValue('F24', '=SUM(F25:F31)');
$sheet->setCellValue('G24', '=SUM(G25:G31)');
$sheet->setCellValue('H24', '=SUM(D24:G24)');

$sheet->setCellValue('H25', '=SUM(D25:G25)');
$sheet->setCellValue('H26', '=SUM(D26:G26)');
$sheet->setCellValue('H27', '=SUM(D27:G27)');
$sheet->setCellValue('H28', '=SUM(D28:G28)');
$sheet->setCellValue('H29', '=SUM(D29:G29)');
$sheet->setCellValue('H30', '=SUM(D30:G30)');
$sheet->setCellValue('H31', '=SUM(D31:G31)');

// Other Permits & Licenses
$sheet->setCellValue('D33', '=SUM(D34:D35)');
$sheet->setCellValue('E33', '=SUM(E34:E35)');
$sheet->setCellValue('F33', '=SUM(F34:F35)');
$sheet->setCellValue('G33', '=SUM(G34:G35)');
$sheet->setCellValue('H33', '=SUM(D33:G33)');

$sheet->setCellValue('H34', '=SUM(D34:G34)');
$sheet->setCellValue('H35', '=SUM(D35:G35)');

// Registration Fees
$sheet->setCellValue('D37', '=SUM(D38:D39)');
$sheet->setCellValue('E37', '=SUM(E38:E39)');
$sheet->setCellValue('F37', '=SUM(F38:F39)');
$sheet->setCellValue('G37', '=SUM(G38:G39)');
$sheet->setCellValue('H37', '=SUM(D37:G37)');

$sheet->setCellValue('H38', '=SUM(D38:G38)');
$sheet->setCellValue('H39', '=SUM(D39:G39)');

// Inspection Fees
$sheet->setCellValue('H41', '=SUM(D41:G41)');

// SERVICE/USER CHARGES section
$sheet->setCellValue('D43', '=SUM(D46,D48,D56,D57,D58,D60)');
$sheet->setCellValue('E43', '=SUM(E46,E48,E56,E57,E58,E60)');
$sheet->setCellValue('F43', '=SUM(F46,F48,F56,F57,F58,F60)');
$sheet->setCellValue('G43', '=SUM(G46,G48,G56,G57,G58,G60)');
$sheet->setCellValue('H43', '=SUM(D43:G43)');

// Clearance and Certification Fees
$sheet->setCellValue('H46', '=SUM(D46:G46)');

// Secretary's Fees
$sheet->setCellValue('D48', '=SUM(D49:D55)');
$sheet->setCellValue('E48', '=SUM(E49:E55)');
$sheet->setCellValue('F48', '=SUM(F49:F55)');
$sheet->setCellValue('G48', '=SUM(G49:G55)');
$sheet->setCellValue('H48', '=SUM(D48:G48)');

$sheet->setCellValue('H49', '=SUM(D49:G49)');
$sheet->setCellValue('H50', '=SUM(D50:G50)');
$sheet->setCellValue('H51', '=SUM(D51:G51)');
$sheet->setCellValue('H52', '=SUM(D52:G52)');
$sheet->setCellValue('H53', '=SUM(D53:G53)');
$sheet->setCellValue('H54', '=SUM(D54:G54)');
$sheet->setCellValue('H55', '=SUM(D55:G55)');

// Other Clearance & Cert.
$sheet->setCellValue('H56', '=SUM(D56:G56)');
$sheet->setCellValue('H57', '=SUM(D57:G57)');
$sheet->setCellValue('H58', '=SUM(D58:G58)');

// Other Service Income
$sheet->setCellValue('D60', '=SUM(D61:D70)');
$sheet->setCellValue('E60', '=SUM(E61:E70)');
$sheet->setCellValue('F60', '=SUM(F61:F70)');
$sheet->setCellValue('G60', '=SUM(G61:G70)');
$sheet->setCellValue('H60', '=SUM(D60:G60)');

$sheet->setCellValue('H61', '=SUM(D61:G61)');
$sheet->setCellValue('H62', '=SUM(D62:G62)');
$sheet->setCellValue('H63', '=SUM(D63:G63)');
$sheet->setCellValue('H64', '=SUM(D64:G64)');
$sheet->setCellValue('H65', '=SUM(D65:G65)');
$sheet->setCellValue('H66', '=SUM(D66:G66)');
$sheet->setCellValue('H67', '=SUM(D67:G67)');
$sheet->setCellValue('H68', '=SUM(D68:G68)');
$sheet->setCellValue('H69', '=SUM(D69:G69)');
$sheet->setCellValue('H70', '=SUM(D70:G70)');

// RECEIPTS FROM ECONOMIC ENTERPRISES
$sheet->setCellValue('D72', '=SUM(D73,D77,D81,D83)');
$sheet->setCellValue('E72', '=SUM(E73,E77,E81,E83)');
$sheet->setCellValue('F72', '=SUM(F73,F77,F81,F83)');
$sheet->setCellValue('G72', '=SUM(G73,G77,G81,G83)');
$sheet->setCellValue('H72', '=SUM(D72:G72)');

// Cemetery Operations
$sheet->setCellValue('D73', '=SUM(D74:D75)');
$sheet->setCellValue('E73', '=SUM(E74:E75)');
$sheet->setCellValue('F73', '=SUM(F74:F75)');
$sheet->setCellValue('G73', '=SUM(G74:G75)');
$sheet->setCellValue('H73', '=SUM(D73:G73)');

$sheet->setCellValue('H74', '=SUM(D74:G74)');
$sheet->setCellValue('H75', '=SUM(D75:G75)');

// Market Operations
$sheet->setCellValue('D77', '=SUM(D78:D79)');
$sheet->setCellValue('E77', '=SUM(E78:E79)');
$sheet->setCellValue('F77', '=SUM(F78:F79)');
$sheet->setCellValue('G77', '=SUM(G78:G79)');
$sheet->setCellValue('H77', '=SUM(D77:G77)');

$sheet->setCellValue('H78', '=SUM(D78:G78)');
$sheet->setCellValue('H79', '=SUM(D79:G79)');

// Slaughter House Operations
$sheet->setCellValue('H81', '=SUM(D81:G81)');

// Water Works System Operations
$sheet->setCellValue('D83', '=SUM(D84:D85)');
$sheet->setCellValue('E83', '=SUM(E84:E85)');
$sheet->setCellValue('F83', '=SUM(F84:F85)');
$sheet->setCellValue('G83', '=SUM(G84:G85)');
$sheet->setCellValue('H83', '=SUM(D83:G83)');

$sheet->setCellValue('H84', '=SUM(D84:G84)');
$sheet->setCellValue('H85', '=SUM(D85:G85)');

// LOCAL SOURCES
$sheet->setCellValue('D87', '=SUM(D72,D43,D22,D9)');
$sheet->setCellValue('E87', '=SUM(E72,E43,E22,E9)');
$sheet->setCellValue('F87', '=SUM(F72,F43,F22,F9)');
$sheet->setCellValue('G87', '=SUM(G72,G43,G22,G9)');
$sheet->setCellValue('H87', '=SUM(D87:G87)');

// OTHER INCOME/RECEIPTS
$sheet->setCellValue('H89', '=SUM(D89:G80)');
$sheet->setCellValue('H90', '=SUM(D90:G90)');

// GRAND TOTAL
$sheet->setCellValue('D92', '=D87+D90');
$sheet->setCellValue('E92', '=E87+E90');
$sheet->setCellValue('F92', '=F87+F90');
$sheet->setCellValue('G92', '=G87+G90');
$sheet->setCellValue('H92', '=IF(SUM(D92:G92)=SUM(H87,H90),H87+H90,"INCORRECT")');

// Set column widths
$sheet->getColumnDimension('A')->setWidth(7);
$sheet->getColumnDimension('B')->setWidth(7);
$sheet->getColumnDimension('C')->setWidth(45);
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getColumnDimension('E')->setWidth(15);
$sheet->getColumnDimension('F')->setWidth(15);
$sheet->getColumnDimension('G')->setWidth(15);
$sheet->getColumnDimension('H')->setWidth(15);

// Apply borders
$sheet->getStyle('A7:H92')->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN
        ]
    ]
]);

// Apply thick outside borders
$sheet->getStyle('A7:H92')->applyFromArray([
    'borders' => [
        'outline' => [
            'borderStyle' => Border::BORDER_THICK
        ]
    ]
]);

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

$sheet->getStyle("H7:H92")->applyFromArray([
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
    'A78:C80', 'A82:C82', 'A84:C86'
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

// Center align D to H columns
$highestRow = $sheet->getHighestRow();
$sheet->getStyle("D7:H$highestRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Define the rows that need thick borders
$thickBorderRows = [7, 9, 18, 24, 33, 37, 41, 43, 46, 48, 56, 57, 58, 60, 72, 73, 77, 81, 83, 87, 89, 90, 91, 92];

// Apply thick borders to each specified row
foreach ($thickBorderRows as $row) {
    $cellRange = "A{$row}:H{$row}";
    $sheet->getStyle($cellRange)->applyFromArray([
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THICK
            ]
        ]
    ]);
}

$boldCells = [
    'B12','B22','B24','B33','B37','B45','B48','B60','B72','B73','B77','B81','B83',
    'C92'
    
];

foreach ($boldCells as $cell) {
    $sheet->getStyle($cell)->getFont()->setBold(true);
}

// Apply color coding
// Green (#00b050)
$greenCells = ['E9', 'E22', 'E43', 'E72', 'C92', 'D9', 'D22', 'D43', 'D72', 'F9', 'F22', 'F43', 'F72', 'G9', 'G22', 'G43', 'G72', 'H9', 'H22', 'H43', 'H72'];

foreach ($greenCells as $cell) {
    $sheet->getStyle($cell)->getFont()
        ->getColor()->setRGB('00B050');
    $sheet->getStyle($cell)->getFont()
        ->setBold(true);
}

// Blue (#0070C0)
$columns = ['D', 'E', 'F', 'G', 'H'];
$baseBlueCells = [
    'E10','E12','E18','E24','E33','E37','E41','E46','E48',
    'E56','E57','E58','E60','E73','E77','E81','E83'
];

$allBlueCells = $baseBlueCells;
foreach ($columns as $col) {
    foreach ($baseBlueCells as $cell) {
        $row = preg_replace('/[^0-9]/', '', $cell);
        $allBlueCells[] = $col . $row;
    }
}

foreach ($allBlueCells as $cell) {
    $sheet->getStyle($cell)->getFont()->getColor()->setRGB('0070C0');
    $sheet->getStyle($cell)->getFont()->setBold(true);
}

// Purple (#7030A0)
$purpleCells = ['E87', 'E90', 'D87', 'D90', 'F87', 'F90', 'G87', 'G90', 'H87', 'H90'];
foreach ($purpleCells as $cell) {
    $sheet->getStyle($cell)->getFont()
        ->setBold(true)
        ->getColor()->setRGB('7030A0');
}

// Brown (#833C0B)
$brownCells = ['D92', 'E92', 'F92', 'G92', 'H92'];
foreach ($brownCells as $cell) {
    $sheet->getStyle($cell)->getFont()
        ->setBold(true)
        ->getColor()->setRGB('833C0B');
}

// Format numbers
$sheet->getStyle("D7:H100")->getNumberFormat()->setFormatCode('#,##0.00');

// Create Excel file and send to browser
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Annual_Report_' . $year . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;