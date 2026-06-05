<?php
session_start();

require '../vendor/autoload.php';
require_once 'monthly_report_helpers.php'; // adjust path if needed


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

// Get data from POST request
$month = $_POST['month'] ?? '';
$year = $_POST['year'] ?? '';
 
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


// HELPERS
$weightMeasureTotal = getWeightMeasureTotal($conn, $month, $year);
$franchisingLicensingTotal = getFranchisingLicensingTotal($conn, $month, $year);
$mayorsPermitTotal = getMayorsPermitTotal($conn, $month, $year); 
$burialPermitTotal = getBurialPermitTotal($conn, $month, $year);
$sanitaryPermitTotal = getSanitaryPermitTotal($conn, $month, $year);
$marriageLicenseTotal = getMarriageLicenseTotal($conn, $month, $year);
$marriageApplicationTotal = getMarriageApplicationTotal($conn, $month, $year);
$policeClearanceTotal = getPoliceClearanceTotal($conn, $month, $year);
$deathCertTotal = getDeathCertTotal($conn, $month, $year);
$birthCertTotal = getBirthCertTotal($conn, $month, $year);
$marriageCertTotal = getMarriageCertTotal($conn, $month, $year);
$assesorCertTotal = getAssesorCertTotal($conn, $month, $year);
$MTOCertTotal = getMTOCertTotal($conn, $month, $year);
$healthCertTotal = getHealthCertTotal($conn, $month, $year);
$secretaryFeeTotal = getSecretaryFeeTotal($conn, $month, $year);
$otherMayorClearanceTotal = getOtherMayorClearanceTotal($conn, $month, $year);
$medicalDentalLabTotal = getMedicalDentalLabTotal($conn, $month, $year);
$solemnizationFeeTotal = getSolemnizationFeeTotal($conn, $month, $year);
$annotationFeeTotal = getAnnotationFeeTotal($conn, $month, $year);
$miscFeeTotal = getMiscFeeTotal($conn, $month, $year);
$RATotal = getRATotal($conn, $month, $year);
$cemeteriesTotal = getCemeteriesTotal($conn, $month, $year);
$inspectionFeeTotal = getInspectionFeeTotal($conn, $month, $year);
$electricalFeeTotal = getElectricalFeeTotal($conn, $month, $year);
$buildingPermitFeeTotal = getBuildingPermitFeeTotal($conn, $month, $year);
$zoningClearanceTotal = getZoningClearanceTotal($conn, $month, $year);
// Usage for each categorization function
$amusementTaxTotal = getAmusementTaxTotal($conn, $month, $year);
$businessTaxRetailersTotal = getBusinessTaxRetailersTotal($conn, $month, $year);
$businessTaxContractorsTotal = getBusinessTaxContractorsTotal($conn, $month, $year);
$businessTaxBanksTotal = getBusinessTaxBanksTotal($conn, $month, $year);
$businessTaxInterestTotal = getBusinessTaxInterestTotal($conn, $month, $year);
$communityTaxCorporationTotal = getCommunityTaxCorporationTotal($conn, $month, $year);
$communityTaxIndividualTotal = getCommunityTaxIndividualTotal($conn, $month, $year);
$cattleRegistrationTotal = getCattleRegistrationTotal($conn, $month, $year);
$garbageFeesTotal = getGarbageFeesTotal($conn, $month, $year);
$stlTotal = getSTLTotal($conn, $month, $year);
$backfillingHaulingTotal = getBackfillingHaulingTotal($conn, $month, $year);
$breqsTotal = getBREQSTotal($conn, $month, $year);
$interestIncomeTotal = getInterestIncomeTotal($conn, $month, $year);
$marilagEcoparkTotal = getMarilagEcoparkTotal($conn, $month, $year);
$municipalLotTotal = getMunicipalLotTotal($conn, $month, $year);
$stallGoodwillTotal = getStallGoodwillTotal($conn, $month, $year);
$stallRentalsTotal = getStallRentalsTotal($conn, $month, $year);
$waterWorksRegistrationTotal = getWaterWorksRegistrationTotal($conn, $month, $year);
$waterWorksMonthlyTotal = getWaterWorksMonthlyTotal($conn, $month, $year);
$slaughterTotal = getSlaughterhouseTotal($conn, $month, $year);
$birthCertNewBornTotal = getBirthCertNewBornTotal($conn, $month, $year);
$othersFeeTotal = getOthersFeeTotal($conn, $month, $year);



// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('MONTHLY');

// Set document properties
$spreadsheet->getProperties()
    ->setCreator($orgName)
    ->setLastModifiedBy($orgName)
    ->setTitle("Monthly Report of Revenue and Receipts")
    ->setSubject("Monthly Report")
    ->setDescription("Monthly report of revenue and receipts for $month $year");

// Set header information
$sheet->setCellValue('A1', 'Republic of the Philippines');
$sheet->setCellValue('A2', 'Province of Laguna');
$sheet->setCellValue('A3', $orgName);
$sheet->setCellValue('A5', 'MONTHLY REPORT OF REVENUE AND RECEIPTS');

// Merge cells for headers
$sheet->mergeCells('A1:E1');
$sheet->mergeCells('A2:E2');
$sheet->mergeCells('A3:E3');
$sheet->mergeCells('A5:E5');
// Merge cells A7 to C7
$sheet->mergeCells('A7:C7');
$sheet->getRowDimension(7)->setRowHeight(30); // adjust 30 to your preferred height
$sheet->getStyle('A7:E7')->getFont()->setBold(true);

$sheet->getStyle('A7:E7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A7:E7')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);


// Style for headers
$headerStyle = [
    'font' => [
        'bold' => true, 
        'size' => 12
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
    ]
];
$sheet->getStyle('A1:H5')->applyFromArray($headerStyle);

// Set column headers
$sheet->setCellValue('A7', 'REVENUE SOURCES');
$sheet->setCellValue('D7', 'ACCOUNT CODE');
$sheet->setCellValue('E7', strtoupper($month));
 // Dynamic month column header

// Define the report structure with formulas
$reportData = [
    // TAX ON BUSINESS
    [9, 'TAX ON BUSINESS', '', '', '', '=SUM(E10,E12,E18)'],
    [10, '', 'Amusement Tax ( Provincial Account)', '', '581', $amusementTaxTotal],
    [12, '', 'Business Taxes', '', '', '=SUM(E13:E16)'],
    [13, '', '', 'Retailers', '582', $businessTaxRetailersTotal],
    [14, '', '', 'Contractors', '582', $businessTaxContractorsTotal],
    [15, '', '', 'Banks & Other Financial Institutions', '582', $businessTaxBanksTotal],
    [16, '', '', 'Interest / Surcharge', '582', $businessTaxInterestTotal],
    
    // OTHER TAXES
    [18, 'OTHER TAXES:', '', '', '',  '=SUM(E19:E20)'],
    [19, '', 'Community Tax - Corporation', '', '', $communityTaxCorporationTotal],
    [20, '', 'Community Tax - Individual', '', '583', $communityTaxIndividualTotal],
    
    // REGULATORY FEES
    [22, 'REGULATORY FEES (PERMITS & LICENSES )', '', '','',  '=SUM(E24,E33,E37,E41)'],
    [24, '', 'Permits and Licenses', '', '', '=SUM(E25:E31)'],
    [25, '', '', 'Fees on weights & measures', '601', $weightMeasureTotal], // Updated with actual value
    [26, '', '', 'Franchising & licensing Fees', '603', $franchisingLicensingTotal],
    [27, '', '', 'Permit Fees/ Mayor\'s Permit Fees', '605', $mayorsPermitTotal],
    [28, '', '', 'Building Permit Fees', '605', $buildingPermitFeeTotal],
    [29, '', '', 'Zonal/Location Permit Fees', '628', $zoningClearanceTotal],
    [30, '', '', 'Burial Permit Fees', '605', $burialPermitTotal],
    [31, '', '', 'Sanitary Permit Fees', '619', $sanitaryPermitTotal],
    
    // Other Permits & Licenses
    [33, '', 'Other Permits & Licenses:', '', '', '=SUM(E34:E35)'],
    [34, '', '', 'Marriage License', '608', $marriageLicenseTotal],
    [35, '', '', 'Application for Marriage', '608', $marriageApplicationTotal],
    
    // Registration Fees 
    [37, '', 'Registration Fees:', '', '', '=SUM(E38:E39)'],
    [38, '', '', 'Cattle/Animal Registration fees', '606', $cattleRegistrationTotal],
    [39, '', '', 'Birth Certificate (New Born) Fees', '606', $birthCertNewBornTotal],
    
    // Inspection Fees
    [41, '', 'Inspection Fees', '', '617', $inspectionFeeTotal],
    
    // SERVICE/USER CHARGES
    [43, 'SERVICE/ USER CHARGES ( Service Income)', '', '','',  '=SUM(E46,E48,E56,E57,E58,E60)'],
    
    // Clearance and Certification Fees
    [45, '', 'Clearance and Certification Fees', '', '', ''],
    [46, '', '', 'Police Clearance', '613', $policeClearanceTotal],
    
    // Secretary's Fees
    [48, '', 'Secretary\'s Fees', '', '', '=SUM(E49:E55)'],
    [49, '', '', 'Death Certificate', '613', $deathCertTotal],
    [50, '', '', 'Birth Certificate', '613', $birthCertTotal],
    [51, '', '', 'Marriage Certificate', '613', $marriageCertTotal],
    [52, '', '', 'Assessor\'s Certificate', '613', $assesorCertTotal], 
    [53, '', '', 'MTO certificate', '613', $MTOCertTotal],
    [54, '', '', 'Health Certificate ( Medical)', '613', $healthCertTotal],
    [55, '', '', 'Secretary\'s Fees', '608', $secretaryFeeTotal],
    
    // Other Clearance & Cert.
    [56, '', 'Other Clearance & Cert./Mayor\'s Clearance', '', '613', $otherMayorClearanceTotal],
    [57, '', 'Garbage Fees', '', '616', $garbageFeesTotal],
    [58, '', 'Medical, Dental & Laboratory Fees', '', '619', $medicalDentalLabTotal],
    
    // Other Service Income
    [60, '', 'Other Service Income', '', '', '=SUM(E61:E70)'],
    [61, '', '', 'Solemnization Fees', '628', $solemnizationFeeTotal],
    [62, '', '', 'Electrical Fees', '628', $electricalFeeTotal],
    [63, '', '', 'Annotation Fees', '628', $annotationFeeTotal],
    [64, '', '', 'STL', '670', $stlTotal],
    [65, '', '', 'Backfilling/Hauling', '628', $backfillingHaulingTotal],
    [66, '', '', 'Miscellaneous Fees', '628', $miscFeeTotal],
    [67, '', '', 'RA 9048, 10172 & 9255', '621', $RATotal],
    [68, '', '', 'BREQS', '613', $breqsTotal],
    [69, '', '', 'Interest Income', '628', $interestIncomeTotal],
    [70, '', '', 'Marilag Ecopark', '418', $marilagEcoparkTotal],
    
    // RECEIPTS FROM ECONOMIC ENTERPRISES
    [72, 'RECEIPTS FROM ECONOMIC ENTERPRISES ( BUSINESS INCOME)', '', '','',  '=SUM(E73,E77,E81,E83)'],
    
    // Cemetery Operations
    [73, '', 'Cemetery Operations:', '', '', '=SUM(E74:E75)'],
    [74, '', '', 'Cemeteries', '633', $cemeteriesTotal],
    [75, '', '', 'Municipal Lot', '628', $municipalLotTotal],
    
    // Market Operations
    [77, '', 'Market Operations:', '', '', '=SUM(E78:E79)'],
    [78, '', '', 'Stall Goodwill', '636', $stallGoodwillTotal],
    [79, '', '', 'Stall Rentals', '636', $stallRentalsTotal],
    
    // Slaughter House Operations
    [81, '', 'Slaugther House Operations:', '', '637', $slaughterTotal],
    
    // Water Works System Operations
    [83, '', 'Water Works System Operations', '', '', '=SUM(E84:E85)'],
    [84, '', '', 'Registration', '', $waterWorksRegistrationTotal],
    [85, '', '', 'Monthly dues', '639', $waterWorksMonthlyTotal],
    
    // LOCAL SOURCES
    [87, 'LOCAL SOURCES', '', '', '', '=SUM(E72,E43,E22,E9)'],
    
    // OTHER INCOME/RECEIPTS
    [89, 'OTHER INCOME/RECEIPTS ( OTHER GENERAL INCOME)', '', '', ''],
    [90, '', 'IRA', '', '665', $othersFeeTotal],
    
    // GRAND TOTAL
    [92, '', '', 'GRAND TOTAL', '', '=SUM(E87,E90)'],
    

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


// Populate the sheet with data
foreach ($reportData as $row) {
    $rowNum = $row[0];
    $sheet->setCellValue('A'.$rowNum, $row[1]);
    if (!empty($row[2])) $sheet->setCellValue('B'.$rowNum, $row[2]);
    if (!empty($row[3])) $sheet->setCellValue('C'.$rowNum, $row[3]);
    if (!empty($row[4])) $sheet->setCellValue('D'.$rowNum, $row[4]);
    if (!empty($row[5])) $sheet->setCellValue('E'.$rowNum, $row[5]);
}



// Close database connection
$conn->close();

// Rest of your existing code for styling and output...
// Center align D and E columns (all rows used)
$highestRow = $sheet->getHighestRow();
$sheet->getStyle("D7:E$highestRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);



// Apply bold style to main headers
$boldStyle = [
    'font' => [
        'bold' => true
    ]
];
$sheet->getStyle('A9:A92')->applyFromArray($boldStyle);
$sheet->getStyle('A96:A100')->applyFromArray($boldStyle);

// Set column widths
$sheet->getColumnDimension('A')->setWidth(7); // Revenue Source titles
$sheet->getColumnDimension('B')->setWidth(7);  // Indentation for sub-rows
$sheet->getColumnDimension('C')->setWidth(45); // Sub-category labels
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getColumnDimension('E')->setWidth(15);

// Step 1: Set thin borders for all sides
$sheet->getStyle('A7:E92')->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN
        ]
    ]
]);

// Step 2: Override left and right borders to thick
$sheet->getStyle('A7:E92')->applyFromArray([
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
    $cellRange = "A{$row}:E{$row}";
    $sheet->getStyle($cellRange)->applyFromArray($thickBorderStyle);
} 

// Define the ranges where borders should be removed
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

$boldCells = [
    'B12','B33','B37','B45','B48','B60','B73','B77','B81','B83',
    'C92',
    'E9','E10','E12','E18','E22','E24','E33','E37','E41','E43','E46','E48',
    'E56','E57','E58','E60','E72','E73','E77','E81','E83','E87','E90','E92'
];

foreach ($boldCells as $cell) {
    $sheet->getStyle($cell)->getFont()->setBold(true);
}

// Green (#00b050)
$greenCells = ['E9', 'E22', 'E43', 'E72', 'C92'];
foreach ($greenCells as $cell) {
    $sheet->getStyle($cell)->getFont()->getColor()->setRGB('00B050');
}

// Blue (#0070c0)
$blueCells = [
    'E10','E12','E18','E24','E33','E37','E41','E46','E48',
    'E56','E57','E58','E60','E73','E77','E81','E83'
];
foreach ($blueCells as $cell) {
    $sheet->getStyle($cell)->getFont()->getColor()->setRGB('0070C0');
}

// Purple (#7030a0)
$purpleCells = ['E87', 'E90'];
foreach ($purpleCells as $cell) {
    $sheet->getStyle($cell)->getFont()->getColor()->setRGB('7030A0');
}

// Brown (#833c0b)
$sheet->getStyle('E92')->getFont()->getColor()->setRGB('833C0B');





$sheet->getStyle("E8:E100")->getNumberFormat()->setFormatCode('#,##0.00');


// Set filename and headers for download
$filename = "Monthly_Report_{$month}_{$year}.xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;