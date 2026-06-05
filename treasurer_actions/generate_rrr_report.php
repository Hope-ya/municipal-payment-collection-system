<?php
session_start();

require '../vendor/autoload.php';
require_once 'rrr_engrSect_report_helpers.php';
require_once 'rrr_treasury_report_helpers.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate; 
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

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



// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('RRR');

// Set default font
$spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(10);

// Set column widths
$sheet->getColumnDimension('A')->setWidth(15);
$sheet->getColumnDimension('B')->setWidth(18);
$sheet->getColumnDimension('C')->setWidth(18);
$sheet->getColumnDimension('D')->setWidth(18);
$sheet->getColumnDimension('E')->setWidth(18);
$sheet->getColumnDimension('F')->setWidth(18);
$sheet->getColumnDimension('G')->setWidth(18);
$sheet->getColumnDimension('H')->setWidth(18);
$sheet->getColumnDimension('I')->setWidth(18);
$sheet->getColumnDimension('J')->setWidth(18);
$sheet->getColumnDimension('K')->setWidth(18);
$sheet->getColumnDimension('L')->setWidth(18);
$sheet->getColumnDimension('M')->setWidth(18);
$sheet->getColumnDimension('N')->setWidth(18);
$sheet->getColumnDimension('O')->setWidth(18);
$sheet->getColumnDimension('P')->setWidth(18);
$sheet->getColumnDimension('Q')->setWidth(18);
$sheet->getColumnDimension('R')->setWidth(18);
$sheet->getColumnDimension('S')->setWidth(18);
$sheet->getColumnDimension('T')->setWidth(18);
$sheet->getColumnDimension('U')->setWidth(18);
$sheet->getColumnDimension('V')->setWidth(18);
$sheet->getColumnDimension('W')->setWidth(18);
$sheet->getColumnDimension('X')->setWidth(18);
$sheet->getColumnDimension('Y')->setWidth(18);
$sheet->getColumnDimension('Z')->setWidth(18);
$sheet->getColumnDimension('AA')->setWidth(18);
$sheet->getColumnDimension('AB')->setWidth(18);
$sheet->getColumnDimension('AC')->setWidth(18);
$sheet->getColumnDimension('AD')->setWidth(18);
$sheet->getColumnDimension('AE')->setWidth(18);
$sheet->getColumnDimension('AF')->setWidth(18);
$sheet->getColumnDimension('AG')->setWidth(18);
$sheet->getColumnDimension('AH')->setWidth(18);
$sheet->getColumnDimension('AI')->setWidth(18);
$sheet->getColumnDimension('AJ')->setWidth(18);
$sheet->getColumnDimension('AK')->setWidth(18);
$sheet->getColumnDimension('AL')->setWidth(18);
$sheet->getColumnDimension('AM')->setWidth(18);
$sheet->getColumnDimension('AN')->setWidth(18);
$sheet->getColumnDimension('AO')->setWidth(18);
$sheet->getColumnDimension('AP')->setWidth(18);
$sheet->getColumnDimension('AQ')->setWidth(18);
$sheet->getColumnDimension('AR')->setWidth(18);
$sheet->getColumnDimension('AS')->setWidth(18);
$sheet->getColumnDimension('AT')->setWidth(18);
$sheet->getColumnDimension('AU')->setWidth(18);
$sheet->getColumnDimension('AV')->setWidth(18);
$sheet->getColumnDimension('AW')->setWidth(18);
$sheet->getColumnDimension('AX')->setWidth(18);
$sheet->getColumnDimension('AY')->setWidth(18);
$sheet->getColumnDimension('AZ')->setWidth(18);
$sheet->getColumnDimension('BA')->setWidth(18);
$sheet->getColumnDimension('BB')->setWidth(18);
$sheet->getColumnDimension('BC')->setWidth(18);
$sheet->getColumnDimension('BD')->setWidth(18);
$sheet->getColumnDimension('BE')->setWidth(18);
$sheet->getColumnDimension('BF')->setWidth(18);
$sheet->getColumnDimension('BG')->setWidth(18);
$sheet->getColumnDimension('BH')->setWidth(18);
$sheet->getColumnDimension('BI')->setWidth(18);
$sheet->getColumnDimension('BJ')->setWidth(18); 

// Set row heights
$sheet->getRowDimension(1)->setRowHeight(15);
$sheet->getRowDimension(2)->setRowHeight(50);
$sheet->getRowDimension(3)->setRowHeight(15);
$sheet->getRowDimension(4)->setRowHeight(15);
$sheet->getRowDimension(5)->setRowHeight(15);
$sheet->getRowDimension(6)->setRowHeight(15);
$sheet->getRowDimension(7)->setRowHeight(15);
$sheet->getRowDimension(8)->setRowHeight(15);
$sheet->getRowDimension(9)->setRowHeight(15);
$sheet->getRowDimension(10)->setRowHeight(15);
$sheet->getRowDimension(11)->setRowHeight(15);
$sheet->getRowDimension(12)->setRowHeight(15);
$sheet->getRowDimension(13)->setRowHeight(15);
$sheet->getRowDimension(14)->setRowHeight(15);
$sheet->getRowDimension(15)->setRowHeight(15);
$sheet->getRowDimension(16)->setRowHeight(15);
$sheet->getRowDimension(17)->setRowHeight(15);
$sheet->getRowDimension(18)->setRowHeight(15);
$sheet->getRowDimension(19)->setRowHeight(15);
$sheet->getRowDimension(20)->setRowHeight(15);
$sheet->getRowDimension(21)->setRowHeight(15);
$sheet->getRowDimension(22)->setRowHeight(15);
$sheet->getRowDimension(23)->setRowHeight(50);
$sheet->getRowDimension(24)->setRowHeight(15);
$sheet->getRowDimension(25)->setRowHeight(15);
$sheet->getRowDimension(26)->setRowHeight(15);
$sheet->getRowDimension(27)->setRowHeight(15);
$sheet->getRowDimension(28)->setRowHeight(15);
$sheet->getRowDimension(29)->setRowHeight(15);
$sheet->getRowDimension(30)->setRowHeight(15);
$sheet->getRowDimension(31)->setRowHeight(15);
$sheet->getRowDimension(32)->setRowHeight(15);
$sheet->getRowDimension(33)->setRowHeight(15);
$sheet->getRowDimension(34)->setRowHeight(15);
$sheet->getRowDimension(35)->setRowHeight(15);
$sheet->getRowDimension(36)->setRowHeight(15);
$sheet->getRowDimension(37)->setRowHeight(15);
$sheet->getRowDimension(38)->setRowHeight(15);
$sheet->getRowDimension(39)->setRowHeight(15);

// Header style
$headerStyle = [
    'font' => [
        'bold' => true,
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
        ]
    ]
];

// Data style
$dataStyle = [
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_RIGHT,
    ],

];

// Month header style
$monthHeaderStyle = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_NONE,
            'color' => ['rgb' => '000000']
        ]
    ]
];

// Set header row
$sheet->setCellValue('A1', 'TREASURY');

$sheet->getStyle('A1')->applyFromArray($headerStyle);
$sheet->getStyle('A22')->applyFromArray($headerStyle);


// Helper function to style a cell range
function styleCells($sheet, $range, $fillColor)
{
    $style = $sheet->getStyle($range);

    // Font color black
    $style->getFont()->getColor()->setARGB(Color::COLOR_BLACK);

    // Center horizontally and vertically
    $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $style->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

    // Fill color
    $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($fillColor);

    // Thin border all around
    $style->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
}

// Set cell values for row 1
$sheet->setCellValue('B1', '582');
$sheet->setCellValue('C1', '582');
$sheet->setCellValue('D1', '582');
$sheet->setCellValue('E1', '582');
$sheet->setCellValue('F1', '');
$sheet->setCellValue('G1', '581');
$sheet->setCellValue('H1', '619');
$sheet->setCellValue('I1', '628');
$sheet->setCellValue('J1', '621');
$sheet->setCellValue('K1', '628');
$sheet->setCellValue('L1', '628');
$sheet->setCellValue('M1', '617');
$sheet->setCellValue('N1', '564');
$sheet->setCellValue('O1', '583');
$sheet->setCellValue('P1', '583');
$sheet->setCellValue('Q1', '605');
$sheet->setCellValue('R1', '605');
$sheet->setCellValue('S1', '628');
$sheet->setCellValue('T1', '601');
$sheet->setCellValue('U1', '603');
$sheet->setCellValue('V1', '');
$sheet->setCellValue('W1', '606');
$sheet->setCellValue('X1', '606');
$sheet->setCellValue('Y1', '605');
$sheet->setCellValue('Z1', '608');
$sheet->setCellValue('AA1', '608');
$sheet->setCellValue('AB1', '608');
$sheet->setCellValue('AC1', '605');
$sheet->setCellValue('AD1', '613');
$sheet->setCellValue('AE1', '613');
$sheet->setCellValue('AF1', '606');
$sheet->setCellValue('AG1', '628');
$sheet->setCellValue('AH1', '613');
$sheet->setCellValue('AI1', '613');
$sheet->setCellValue('AJ1', '613');
$sheet->setCellValue('AK1', '619');
$sheet->setCellValue('AL1', '613');
$sheet->setCellValue('AM1', '616');
$sheet->setCellValue('AN1', '613');
$sheet->setCellValue('AO1', '628');
$sheet->setCellValue('AP1', '617');
$sheet->setCellValue('AQ1', '613');
$sheet->setCellValue('AR1', '613');
$sheet->setCellValue('AS1', '636');
$sheet->setCellValue('AT1', '636');
$sheet->setCellValue('AU1', '637');
$sheet->setCellValue('AV1', '633');
$sheet->setCellValue('AW1', '639');
$sheet->setCellValue('AY1', '418');
$sheet->setCellValue('AZ1', '670');
$sheet->setCellValue('BA1', '106');
$sheet->setCellValue('BB1', '148');
$sheet->setCellValue('BC1', '628');
$sheet->setCellValue('BD1', '628');



// Center alignment & font color black for A1:BJ2
$sheet->getStyle('A1:BJ2')->applyFromArray([
    'font' => [
        'color' => ['rgb' => '000000'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical'   => Alignment::VERTICAL_CENTER,
        'wrapText'   => true,
    ],

]);

$sheet->getStyle('A1:BJ2')->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color'       => ['rgb' => '000000'],
        ],
    ],
]);


// Helper function to apply fill color to a column range
function setColumnFill($sheet, $column, $color)
{
    $sheet->getStyle("{$column}1:{$column}2")->getFill()->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setRGB($color);
}

// Apply your colors
setColumnFill($sheet, 'B', 'D6DCE4');
setColumnFill($sheet, 'C', 'D6DCE4');
setColumnFill($sheet, 'D', 'D6DCE4');
setColumnFill($sheet, 'E', 'D6DCE4');

setColumnFill($sheet, 'F', '9CC2E5');

setColumnFill($sheet, 'G', 'D6DCE4');

setColumnFill($sheet, 'H', 'FBE4D5');

setColumnFill($sheet, 'I', 'FFFF00'); // yellow

// white is 'FFFFFF'
setColumnFill($sheet, 'J', 'FFFFFF');
setColumnFill($sheet, 'K', 'FFFFFF');

setColumnFill($sheet, 'L', '8496B0');
setColumnFill($sheet, 'M', '8496B0');

setColumnFill($sheet, 'N', 'C8C8C8');
setColumnFill($sheet, 'O', 'C8C8C8');
setColumnFill($sheet, 'P', 'C8C8C8');

setColumnFill($sheet, 'Q', 'FFFFFF');

setColumnFill($sheet, 'R', '8EAADB');

setColumnFill($sheet, 'S', 'E5E5E5');

setColumnFill($sheet, 'T', 'DEEAF6');

setColumnFill($sheet, 'U', '92D050');
setColumnFill($sheet, 'V', '92D050');

setColumnFill($sheet, 'W', 'F7CAAC');
setColumnFill($sheet, 'X', 'F7CAAC');

setColumnFill($sheet, 'Y', 'FFFFFF');

setColumnFill($sheet, 'Z', 'F4B083');
setColumnFill($sheet, 'AA', 'F4B083');
setColumnFill($sheet, 'AB', 'F4B083');

setColumnFill($sheet, 'AC', 'C5E0B3');
setColumnFill($sheet, 'AD', 'C5E0B3');
setColumnFill($sheet, 'AE', 'C5E0B3');
setColumnFill($sheet, 'AF', 'C5E0B3');
setColumnFill($sheet, 'AG', 'C5E0B3');
setColumnFill($sheet, 'AH', 'C5E0B3');
setColumnFill($sheet, 'AI', 'C5E0B3');
setColumnFill($sheet, 'AJ', 'C5E0B3');

setColumnFill($sheet, 'AK', 'FFFF00'); // yellow
setColumnFill($sheet, 'AL', 'FFFF00');
setColumnFill($sheet, 'AM', 'FFFF00');
setColumnFill($sheet, 'AN', 'FFFF00');
setColumnFill($sheet, 'AO', 'FFFF00');
setColumnFill($sheet, 'AP', 'FFFF00');
setColumnFill($sheet, 'AQ', 'FFFF00');
setColumnFill($sheet, 'AR', 'FFFF00');

setColumnFill($sheet, 'AS', 'FFFFFF');
setColumnFill($sheet, 'AT', 'FFFFFF');
setColumnFill($sheet, 'AU', 'FFFFFF');
setColumnFill($sheet, 'AV', 'FFFFFF');
setColumnFill($sheet, 'AW', 'FFFFFF');
setColumnFill($sheet, 'AY', 'FFFFFF');
setColumnFill($sheet, 'AZ', 'FFFFFF');

setColumnFill($sheet, 'BA', 'FFFFFF');
setColumnFill($sheet, 'BB', 'FFFFFF');
setColumnFill($sheet, 'BC', 'FFFFFF');
setColumnFill($sheet, 'BD', 'FFFFFF');
setColumnFill($sheet, 'BE', 'FFFFFF');
setColumnFill($sheet, 'BF', 'FFFFFF');
setColumnFill($sheet, 'BG', 'FFFFFF');
setColumnFill($sheet, 'BH', 'FFFFFF');
setColumnFill($sheet, 'BI', 'FFFFFF');
setColumnFill($sheet, 'BJ', 'FFFFFF');






// Set column headers
$sheet->setCellValue('A2', "Month");

$sheet->setCellValue('B2', "Tax on \nBusiness Banks");
$sheet->getStyle('B2')->getAlignment()->setWrapText(true);

$sheet->setCellValue('C2', "Tax on \nBusiness \nContractors");
$sheet->getStyle('C2')->getAlignment()->setWrapText(true);

$sheet->setCellValue('D2', "Tax on \nBusiness \nOther Bus.");
$sheet->getStyle('D2')->getAlignment()->setWrapText(true);

$sheet->setCellValue('E2', "Tax on \nBusiness \nRetailer");
$sheet->getStyle('E2')->getAlignment()->setWrapText(true);

$sheet->setCellValue('F2', "Interest / \nSurcharge");
$sheet->getStyle('F2')->getAlignment()->setWrapText(true);

$sheet->setCellValue('G2', "Amusement Tax");

$sheet->setCellValue('H2', "Medical, Dental \nand Lab. Fee");
$sheet->getStyle('H2')->getAlignment()->setWrapText(true);

$sheet->setCellValue('I2', "Solemnization \nFee");
$sheet->getStyle('I2')->getAlignment()->setWrapText(true);

$sheet->setCellValue('J2', "Other Specific \nIncome of LGU \nRA 9048/10172 \nRA 9255");
$sheet->getStyle('J2')->getAlignment()->setWrapText(true);

$sheet->setCellValue('K2', "Municipal Lot");
$sheet->setCellValue('L2', "Electrical Fee");
$sheet->setCellValue('M2', "FSIF");
$sheet->setCellValue('N2', "Professional Tax");
$sheet->setCellValue('O2', "CTC Corp.");
$sheet->setCellValue('P2', "Community Tax");
$sheet->setCellValue('Q2', "Mayor's Permit");
$sheet->setCellValue('R2', "under the \nBuilding Code");
$sheet->getStyle('R2')->getAlignment()->setWrapText(true);

$sheet->setCellValue('S2', "Zoning \nClearance Fee");
$sheet->getStyle('S2')->getAlignment()->setWrapText(true);

$sheet->setCellValue('T2', "on Weight & \nMeasures");
$sheet->getStyle('T2')->getAlignment()->setWrapText(true);

$sheet->setCellValue('U2', "Tricycle's \nOperators Permit");
$sheet->getStyle('U2')->getAlignment()->setWrapText(true);

$sheet->setCellValue('V2', "Bicycle Permit");

$sheet->setCellValue('W2', "Cattle Registration \nFee (Ownership)");
$sheet->getStyle('W2')->getAlignment()->setWrapText(true);

$sheet->setCellValue('X2', "Cattle Registration \nFee (Transfer)");
$sheet->getStyle('X2')->getAlignment()->setWrapText(true);

$sheet->setCellValue('Y2', "Permit Fee");

$sheet->setCellValue('Z2', "Application for \nMarriage");
$sheet->getStyle('Z2')->getAlignment()->setWrapText(true);

$sheet->setCellValue('AA2', "Marriage License");

$sheet->setCellValue('AB2', "Secretary's Fee \n(Miscellaneous)");
$sheet->getStyle('AB2')->getAlignment()->setWrapText(true);

$sheet->setCellValue('AC2', "Burial Fee");
$sheet->setCellValue('AD2', "Death Certificate");
$sheet->setCellValue('AE2', "Birth Certificate");
$sheet->setCellValue('AF2', "Birth Certifacate (NB)");
$sheet->setCellValue('AG2', "Miscellaneous");
$sheet->setCellValue('AH2', "BREQS");
$sheet->setCellValue('AI2', "Marriage Certificate");
$sheet->setCellValue('AJ2', "Health Certificate");
$sheet->setCellValue('AK2', "Sanitary Permit");
$sheet->setCellValue('AL2', "Mayor's Clearance");
$sheet->setCellValue('AM2', "Garbage Fee");
$sheet->setCellValue('AN2', "Police Clearance");
$sheet->setCellValue('AO2', "Annotation Fee");
$sheet->setCellValue('AP2', "Inspection Fee");
$sheet->setCellValue('AQ2', "Certification Assesor");
$sheet->setCellValue('AR2', "Certification mto");

$sheet->setCellValue('AS2', "Market Stall \nGoodwill");
$sheet->getStyle('AS2')->getAlignment()->setWrapText(true);

$sheet->setCellValue('AT2', "Fixed Stall \nRental Fee");
$sheet->getStyle('AT2')->getAlignment()->setWrapText(true);

$sheet->setCellValue('AU2', "Slaughter House");
$sheet->setCellValue('AV2', "Cemeteries");
$sheet->setCellValue('AW2', "Water Works System");
$sheet->setCellValue('AX2', "Water Registration");
$sheet->getStyle('AX2')->getAlignment()->setWrapText(true);
$sheet->setCellValue('AY2', "Marilag ECO Park");
$sheet->setCellValue('AZ2', "STL");

$sheet->setCellValue('BA2', "Cash Excess \n(Payroll)");
$sheet->getStyle('BA2')->getAlignment()->setWrapText(true);

$sheet->setCellValue('BB2', "Cash Excess \n(AICS/PHILHEALTH)");
$sheet->getStyle('BB2')->getAlignment()->setWrapText(true);

$sheet->setCellValue('BC2', "BACKFILING");
$sheet->setCellValue('BD2', "Miscellaneous");
$sheet->setCellValue('BE2', "Basic");
$sheet->setCellValue('BF2', "Total");
$sheet->setCellValue('BG2', "IRA");

$sheet->setCellValue('BH2', "Local Collection \nTotal");
$sheet->getStyle('BH2')->getAlignment()->setWrapText(true);

$sheet->setCellValue('BI2', "RPT Collection");
$sheet->setCellValue('BJ2', "Other Collection");



// Example: set default value 0.00 and format for B3:BE18
$rangeTreasury = 'B3:BJ18';

// Fill empty cells with 0.00
foreach ($sheet->rangeToArray($rangeTreasury, null, true, true, true) as $rowIndex => $row) {
    foreach ($row as $col => $value) {
        if ($value === null || $value === '') {
            $sheet->setCellValue($col . $rowIndex, '-');
        }
    }
}

// Apply number format with thousand separator and 2 decimals
$sheet->getStyle($rangeTreasury)
    ->getNumberFormat()
    ->setFormatCode('#,##0.00');

$rangeTreasuryGrandTotal = 'B20:BJ20';

// Fill empty cells with 0.00
foreach ($sheet->rangeToArray($rangeTreasuryGrandTotal, null, true, true, true) as $rowIndex => $row) {
    foreach ($row as $col => $value) {
        if ($value === null || $value === '') {
            $sheet->setCellValue($col . $rowIndex, '-');
        }
    }
}

// Apply number format with thousand separator and 2 decimals
$sheet->getStyle($rangeTreasuryGrandTotal)
    ->getNumberFormat()
    ->setFormatCode('#,##0.00');

// Set months and quarters
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
    '',
    'GRAND TOTAL',
    '',

];



// Add months to column A
foreach ($months as $index => $month) {
    $row = $index + 3;
    $sheet->setCellValue('A' . $row, $month);

    // Style for month headers
    if (in_array($month, [
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
        'DECEMBER',
        'GRAND TOTAL'
    ])) {
        $sheet->getStyle('A' . $row)->applyFromArray($monthHeaderStyle);
    }

    // Style for quarter headers
    if (
        in_array($month, ['1ST QUARTER', '2ND QUARTER', '3RD QUARTER', '4TH QUARTER'])
        && $row <= 18
    ) {
        // Map each quarter to its fill color
        $quarterColors = [
            '1ST QUARTER'  => 'BDD6EE',
            '2ND QUARTER'  => 'F7CAAC',
            '3RD QUARTER'  => 'FFE598',
            '4TH QUARTER'  => 'C5E0B3',
        ];

        // Apply color for the entire row from A to BJ
        $sheet->getStyle('A' . $row . ':BJ' . $row)
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB($quarterColors[$month]);
    }
}



// Map specific rows to their corresponding quarter colors
$quarterRowColors = [
    27 => 'BDD6EE', // 1ST QUARTER
    31 => 'F7CAAC', // 2ND QUARTER
    35 => 'FFE598', // 3RD QUARTER
    39 => 'C5E0B3', // 4TH QUARTER
];

// Loop through the mapping and apply fill colors
foreach ($quarterRowColors as $rowNum => $color) {
    $sheet->getStyle('A' . $rowNum . ':J' . $rowNum)
        ->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()
        ->setRGB($color);
}


function columnRange($startCol, $endCol)
{
    $cols = [];
    $startIndex = Coordinate::columnIndexFromString($startCol);
    $endIndex   = Coordinate::columnIndexFromString($endCol);

    for ($i = $startIndex; $i <= $endIndex; $i++) {
        $cols[] = Coordinate::stringFromColumnIndex($i);
    }
    return $cols;
}

// TREASURY CONTENTS
// Get monthly totals for both
$totals = getAllTreasuryMonthlyTotals($conn, $year);


// Month → row mapping
$monthRowMapTreasury = [
    1 => 3,  // Jan
    2 => 4,  // Feb
    3 => 5,  // Mar
    4 => 7,  // Apr
    5 => 8,  // May
    6 => 9,  // Jun
    7 => 11, // Jul
    8 => 12, // Aug
    9 => 13, // Sep
    10 => 15, // Oct
    11 => 16, // Nov
    12 => 17, // Dec
];

foreach ($monthRowMapTreasury as $month => $row) {
    $sheet->setCellValue("B{$row}", $totals['business_tax_banks'][$month]);
    $sheet->setCellValue("C{$row}", $totals['business_tax_contractors'][$month]);
    $sheet->setCellValue("D{$row}", $totals['business_tax_banks'][$month]); // ⚠️ check if duplicate is intended
    $sheet->setCellValue("E{$row}", $totals['business_tax_retailers'][$month]);
    $sheet->setCellValue("F{$row}", $totals['business_tax_interest'][$month]);
    $sheet->setCellValue("G{$row}", $totals['amusement_tax'][$month]);
    $sheet->setCellValue("H{$row}", $totals['medical_dental_lab'][$month]);
    $sheet->setCellValue("I{$row}", $totals['solemnization'][$month]);
    $sheet->setCellValue("J{$row}", $totals['ra_acts'][$month]);
    $sheet->setCellValue("K{$row}", $totals['municipal_lot'][$month]);
    $sheet->setCellValue("L{$row}", $totals['electrical'][$month]);
    $sheet->setCellValue("M{$row}", $totals['fsif'][$month]);
    $sheet->setCellValue("N{$row}", $totals['prof_tax'][$month]);
    $sheet->setCellValue("O{$row}", $totals['community_tax_corp'][$month]);
    $sheet->setCellValue("P{$row}", $totals['community_tax_individual'][$month]);
    $sheet->setCellValue("Q{$row}", $totals['mayors_permit_fees'][$month]);
    $sheet->setCellValue("R{$row}", $totals['building_permit'][$month]);
    $sheet->setCellValue("S{$row}", $totals['zonal_location'][$month]);
    $sheet->setCellValue("T{$row}", $totals['weights_measures'][$month]);
    $sheet->setCellValue("U{$row}", $totals['tricycle'][$month]);
    $sheet->setCellValue("V{$row}", $totals['bicycle'][$month]);
    $sheet->setCellValue("W{$row}", $totals['cattle_registration_ownership'][$month]);
    $sheet->setCellValue("X{$row}", $totals['cattle_registration_transfer'][$month]);
    $sheet->setCellValue("Y{$row}", $totals['permit_fees'][$month]);
    $sheet->setCellValue("Z{$row}", $totals['application_marriage'][$month]);

    $sheet->setCellValue("AA{$row}", $totals['marriage_license'][$month]);
    $sheet->setCellValue("AB{$row}", $totals['secretary_fee'][$month]);
    $sheet->setCellValue("AC{$row}", $totals['burial_permit'][$month]);
    $sheet->setCellValue("AD{$row}", $totals['death_certificate'][$month]);
    $sheet->setCellValue("AE{$row}", $totals['birth_certificate'][$month]);
    $sheet->setCellValue("AF{$row}", $totals['birth_certificate_newborn'][$month]);
    $sheet->setCellValue("AG{$row}", $totals['misc_fees'][$month]);
    $sheet->setCellValue("AH{$row}", $totals['breqs'][$month]);
    $sheet->setCellValue("AI{$row}", $totals['marriage_cert'][$month]);
    $sheet->setCellValue("AJ{$row}", $totals['health_cert'][$month]);
    $sheet->setCellValue("AK{$row}", $totals['sanitary_permit'][$month]);
    $sheet->setCellValue("AL{$row}", $totals['mayor_clearance'][$month]);
    $sheet->setCellValue("AM{$row}", $totals['garbage'][$month]);
    $sheet->setCellValue("AN{$row}", $totals['police_clearance'][$month]);
    $sheet->setCellValue("AO{$row}", $totals['annotation_fees'][$month]);
    $sheet->setCellValue("AP{$row}", $totals['inspection'][$month]); 
    $sheet->setCellValue("AQ{$row}", $totals['assessor_cert'][$month]);
    $sheet->setCellValue("AR{$row}", $totals['mto_cert'][$month]);
    $sheet->setCellValue("AS{$row}", $totals['stall_goodwill'][$month]);
    $sheet->setCellValue("AT{$row}", $totals['stall_rentals'][$month]);
    $sheet->setCellValue("AU{$row}", $totals['slaughter_house'][$month]);
    $sheet->setCellValue("AV{$row}", $totals['cemeteries'][$month]);
    $sheet->setCellValue("AW{$row}", $totals['waterworks_dues'][$month]);
    $sheet->setCellValue("AX{$row}", $totals['waterworks_registration'][$month]);
    $sheet->setCellValue("AY{$row}", $totals['marilag_ecopark'][$month]);
    $sheet->setCellValue("AZ{$row}", $totals['stl'][$month]);

    $sheet->setCellValue("BA{$row}", $totals['cash_excess_payroll'][$month]);
    $sheet->setCellValue("BB{$row}", $totals['cash_excess_aics_phil'][$month]);
    $sheet->setCellValue("BC{$row}", $totals['backfilling_hauling'][$month]);
    $sheet->setCellValue("BD{$row}", $totals['misc_fees'][$month]); // ⚠️ check duplicate use
    $sheet->setCellValue("BE{$row}", $totals['basic'][$month]); // ⚠️ can be swapped if needed
    $sheet->setCellValue("BG{$row}", $totals['others'][$month]);
}


 





//TREASURY QTR FORMULAS

// Start and end columns
$startCol = 'B';
$endCol   = 'BE';

// 1ST QTR
foreach (columnRange($startCol, $endCol) as $col) {
    $sheet->setCellValue($col . 6, "=SUM({$col}3:{$col}5)");
}

// 2ND QTR
foreach (columnRange($startCol, $endCol) as $col) {
    $sheet->setCellValue($col . 10, "=SUM({$col}7:{$col}9)");
}

// 3RD QTR
foreach (columnRange($startCol, $endCol) as $col) {
    $sheet->setCellValue($col . 14, "=SUM({$col}11:{$col}13)");
}

// 4TH QTR
foreach (columnRange($startCol, $endCol) as $col) {
    $sheet->setCellValue($col . 18, "=SUM({$col}15:{$col}17)");
}

// Function to generate Excel column range
function grandTotalColumnRange($startCol, $endCol)
{
    $cols = [];
    $startIndex = Coordinate::columnIndexFromString($startCol);
    $endIndex   = Coordinate::columnIndexFromString($endCol);

    for ($i = $startIndex; $i <= $endIndex; $i++) {
        $cols[] = Coordinate::stringFromColumnIndex($i);
    }
    return $cols;
}

$startCol = 'B';
$endCol   = 'BJ';
$grandTotalRow = 20; // Change to your actual Grand Total row

foreach (grandTotalColumnRange($startCol, $endCol) as $col) {
    $sheet->setCellValue(
        $col . $grandTotalRow,
        "=SUM({$col}6,{$col}10,{$col}14,{$col}18)"
    );
}

// BF column - 92d050 (greenish)
$sheet->getStyle('BF3:BF20')->getFont()->getColor()->setRGB('92D050');

// BG column - red
$sheet->getStyle('BG3:BG20')->getFont()->getColor()->setRGB('FF0000');

// BH column - 385623 (dark green)
$sheet->getStyle('BH3:BH20')->getFont()->getColor()->setRGB('385623');

// BI column - c00000 (dark red)
$sheet->getStyle('BI3:BI20')->getFont()->getColor()->setRGB('C00000');

// BJ column - 7030a0 (purple)
$sheet->getStyle('BJ3:BJ20')->getFont()->getColor()->setRGB('7030A0');


//TOTAL
$sheet->setCellValue('BF3', '=SUM(B3:BE3)');
$sheet->setCellValue('BF4', '=SUM(B4:BE4)');
$sheet->setCellValue('BF5', '=SUM(B5:BE5)');
$sheet->setCellValue('BF6', '=SUM(B6:BE6)');
$sheet->setCellValue('BF7', '=SUM(B7:BE7)');
$sheet->setCellValue('BF8', '=SUM(B8:BE8)');
$sheet->setCellValue('BF9', '=SUM(B9:BE9)');
$sheet->setCellValue('BF10', '=SUM(B10:BE10)');
$sheet->setCellValue('BF11', '=SUM(B11:BE11)');
$sheet->setCellValue('BF12', '=SUM(B12:BE12)');
$sheet->setCellValue('BF13', '=SUM(B13:BE13)');
$sheet->setCellValue('BF14', '=SUM(B14:BE14)');
$sheet->setCellValue('BF15', '=SUM(B15:BE15)');
$sheet->setCellValue('BF16', '=SUM(B16:BE16)');
$sheet->setCellValue('BF17', '=SUM(B17:BE17)');
$sheet->setCellValue('BF18', '=SUM(B18:BE18)');

//IRA
$sheet->setCellValue('BG6', '=SUM(BG3:BG5)');
$sheet->setCellValue('BG10', '=SUM(BG7:BG9)');
$sheet->setCellValue('BG14', '=SUM(BG11:BG13)');
$sheet->setCellValue('BG18', '=SUM(BG15:BG17)');

//LOCAL COLL. TOTAL
$sheet->setCellValue('BH3', '=SUM(BC3:BD3,B3:AZ3)');
$sheet->setCellValue('BH4', '=SUM(BC4:BD4,B4:AZ4)');
$sheet->setCellValue('BH5', '=SUM(BC5:BD5,B5:AZ5)');
$sheet->setCellValue('BH6', '=SUM(BC6:BD6,B6:AZ6)');
$sheet->setCellValue('BH7', '=SUM(BC7:BD7,B7:AZ7)');
$sheet->setCellValue('BH8', '=SUM(BC8:BD8,B8:AZ8)');
$sheet->setCellValue('BH9', '=SUM(BC9:BD9,B9:AZ9)');
$sheet->setCellValue('BH10', '=SUM(BC10:BD10,B10:AZ10)');
$sheet->setCellValue('BH11', '=SUM(BC11:BD11,B11:AZ11)');
$sheet->setCellValue('BH12', '=SUM(BC12:BD12,B12:AZ12)');
$sheet->setCellValue('BH13', '=SUM(BC13:BD13,B13:AZ13)');
$sheet->setCellValue('BH14', '=SUM(BC14:BD14,B14:AZ14)');
$sheet->setCellValue('BH15', '=SUM(BC15:BD15,B15:AZ15)');
$sheet->setCellValue('BH16', '=SUM(BC16:BD16,B16:AZ16)');
$sheet->setCellValue('BH17', '=SUM(BC17:BD17,B17:AZ17)');
$sheet->setCellValue('BH18', '=SUM(BC18:BD18,B18:AZ18)');

//RPT COLL.
for ($row = 3; $row <= 18; $row++) {
    $sheet->setCellValue("BI{$row}", "=BE{$row}");
}

//OTHER COLL.
$sheet->setCellValue('BJ3', '=SUM(C24:D24,F24:G24,I24:J24,BA3:BB3)');
$sheet->setCellValue('BJ4', '=SUM(C25:D25,F25:G25,I25:J25,BA4:BB4)');
$sheet->setCellValue('BJ5', '=SUM(C26:D26,F26:G26,I26:J26,BA5:BB5)');
$sheet->setCellValue('BJ6', '=SUM(C27:D27,F27:G27,I27:J27,BA6:BB6)');
$sheet->setCellValue('BJ7', '=SUM(C28:D28,F28:G28,I28:J28,BA7:BB7)');
$sheet->setCellValue('BJ8', '=SUM(C29:D29,F29:G29,I29:J29,BA8:BB8)');
$sheet->setCellValue('BJ9', '=SUM(C30:D30,F30:G30,I30:J30,BA9:BB9)');
$sheet->setCellValue('BJ10', '=SUM(C31:D31,F31:G31,I31:J31,BA10:BB10)');
$sheet->setCellValue('BJ11', '=SUM(C32:D32,F32:G32,I32:J32,BA11:BB11)');
$sheet->setCellValue('BJ12', '=SUM(C33:D33,F33:G33,I33:J33,BA12:BB12)');
$sheet->setCellValue('BJ13', '=SUM(C34:D34,F34:G34,I34:J34,BA13:BB13)');
$sheet->setCellValue('BJ14', '=SUM(C35:D35,F35:G35,I35:J35,BA14:BB14)');
$sheet->setCellValue('BJ15', '=SUM(C36:D36,F36:G36,I36:J36,BA15:BB15)');
$sheet->setCellValue('BJ16', '=SUM(C37:D37,F37:G37,I37:J37,BA16:BB16)');
$sheet->setCellValue('BJ17', '=SUM(C38:D38,F38:G38,I38:J38,BA17:BB17)');
$sheet->setCellValue('BJ18', '=SUM(C39:D39,F39:G39,I39:J39,BA18:BB18)');


// Apply data style to all data cells
$sheet->getStyle('B3:BJ38')->applyFromArray($dataStyle);

// Set engineering section headers
$monthsEngr = [

    'ENGINEERING',
    'Month',
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

];

// Add months to column A
foreach ($monthsEngr as $index => $month) {
    $row = $index + 22;
    $sheet->setCellValue('A' . $row, $month);

    // Style for month headers
    if (in_array($month, [
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
    ])) {
        $sheet->getStyle('A' . $row)->applyFromArray($monthHeaderStyle);
    }
}


// Set header labels
$sheet->setCellValue('B23', 'Inspection Fee');
$sheet->setCellValue('C23', 'N.G. 5%');
$sheet->setCellValue('D23', '15%');

$sheet->setCellValue('E23', 'Building Fee');
$sheet->setCellValue('F23', 'N.G. 5%');
$sheet->setCellValue('G23', '15%');

$sheet->setCellValue('H23', 'Electrical Fee');
$sheet->setCellValue('I23', 'N.G. 5%');
$sheet->setCellValue('J23', '15%');

// Example: set default value 0.00 and format for B3:BE18
$rangeEngr = 'B24:J39';

// Fetch monthly totals
$inspectionTotals = getInspectionFeeMonthlyTotalsRRR($conn, $year);
$buildingTotals   = getBuildingPermitFeeMonthlyTotalsRRR($conn, $year);
$electricalTotals = getElectricalFeeMonthlyTotalsRRR($conn, $year);

// Partition them into 80% / 5% / 15%
$inspectionParts = calculateFeePartitionsRRR($inspectionTotals);
$buildingParts   = calculateFeePartitionsRRR($buildingTotals);
$electricalParts = calculateFeePartitionsRRR($electricalTotals);


// Starting row for January (B24, E24, H24)
$startRow = 24;
$monthRowMap = [
    1 => 24, // Jan
    2 => 25, // Feb
    3 => 26, // Mar
    4 => 28, // Apr
    5 => 29, // May
    6 => 30, // Jun
    7 => 32, // Jul
    8 => 33, // Aug
    9 => 34, // Sep
    10 => 36, // Oct
    11 => 37, // Nov
    12 => 38, // Dec
];

for ($month = 1; $month <= 12; $month++) {
    $row = $monthRowMap[$month];
    $idx = $month - 1;

    $sheet->setCellValue("B{$row}", $inspectionParts[$idx]['main'] ?? 0);
    $sheet->setCellValue("C{$row}", $inspectionParts[$idx]['ng5'] ?? 0);
    $sheet->setCellValue("D{$row}", $inspectionParts[$idx]['fifteen'] ?? 0);

    $sheet->setCellValue("E{$row}", $buildingParts[$idx]['main'] ?? 0);
    $sheet->setCellValue("F{$row}", $buildingParts[$idx]['ng5'] ?? 0);
    $sheet->setCellValue("G{$row}", $buildingParts[$idx]['fifteen'] ?? 0);

    $sheet->setCellValue("H{$row}", $electricalParts[$idx]['main'] ?? 0);
    $sheet->setCellValue("I{$row}", $electricalParts[$idx]['ng5'] ?? 0);
    $sheet->setCellValue("J{$row}", $electricalParts[$idx]['fifteen'] ?? 0);
}




//QUATERLY SUMS
//1ST QTR
$sheet->setCellValue('B27', '=SUM(B24:B26)');
$sheet->setCellValue('C27', '=SUM(C24:C26)');
$sheet->setCellValue('D27', '=SUM(D24:D26)');
$sheet->setCellValue('E27', '=SUM(E24:E26)');
$sheet->setCellValue('F27', '=SUM(F24:F26)');
$sheet->setCellValue('G27', '=SUM(G24:G26)');
$sheet->setCellValue('H27', '=SUM(H24:H26)');
$sheet->setCellValue('I27', '=SUM(I24:I26)');
$sheet->setCellValue('J27', '=SUM(J24:J26)');

//2ND QTR
$sheet->setCellValue('B31', '=SUM(B28:B30)');
$sheet->setCellValue('C31', '=SUM(C28:C30)');
$sheet->setCellValue('D31', '=SUM(D28:D30)');
$sheet->setCellValue('E31', '=SUM(E28:E30)');
$sheet->setCellValue('F31', '=SUM(F28:F30)');
$sheet->setCellValue('G31', '=SUM(G28:G30)');
$sheet->setCellValue('H31', '=SUM(H28:H30)');
$sheet->setCellValue('I31', '=SUM(I28:I30)');
$sheet->setCellValue('J31', '=SUM(J28:J30)');

//3RD QTR
$sheet->setCellValue('B35', '=SUM(B32:B34)');
$sheet->setCellValue('C35', '=SUM(C32:C34)');
$sheet->setCellValue('D35', '=SUM(D32:D34)');
$sheet->setCellValue('E35', '=SUM(E32:E34)');
$sheet->setCellValue('F35', '=SUM(F32:F34)');
$sheet->setCellValue('G35', '=SUM(G32:G34)');
$sheet->setCellValue('H35', '=SUM(H32:H34)');
$sheet->setCellValue('I35', '=SUM(I32:I34)');
$sheet->setCellValue('J35', '=SUM(J32:J34)');

//4TH QTR
$sheet->setCellValue('B39', '=SUM(B36:B38)');
$sheet->setCellValue('C39', '=SUM(C36:C38)');
$sheet->setCellValue('D39', '=SUM(D36:D38)');
$sheet->setCellValue('E39', '=SUM(E36:E38)');
$sheet->setCellValue('F39', '=SUM(F36:F38)');
$sheet->setCellValue('G39', '=SUM(G36:G38)');
$sheet->setCellValue('H39', '=SUM(H36:H38)');
$sheet->setCellValue('I39', '=SUM(I36:I38)');
$sheet->setCellValue('J39', '=SUM(J36:J38)');

// Fill empty cells with 0.00
foreach ($sheet->rangeToArray($rangeEngr, null, true, true, true) as $rowIndex => $row) {
    foreach ($row as $col => $value) {
        if ($value === null || $value === '') {
            $sheet->setCellValue($col . $rowIndex, '-');
        }
    }
}

// Apply number format with thousand separator and 2 decimals
$sheet->getStyle($rangeEngr)
    ->getNumberFormat()
    ->setFormatCode('#,##0.00');

// Common header style: black font, centered text, thin borders
$commonStyle = [
    'font' => [
        'color' => ['rgb' => '000000'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical'   => Alignment::VERTICAL_CENTER,
        'wrapText'   => true
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000']
        ]
    ]
];

// Apply style to the entire header range
$sheet->getStyle('A23:J23')->applyFromArray($commonStyle);

// Apply fill colors to specific column ranges
$sheet->getStyle('B23:D23')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E7E6E6');
$sheet->getStyle('E23:G23')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FBE4D5');
$sheet->getStyle('H23:J23')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2EFD9');






// Freeze panes (first two rows and first column)
$sheet->freezePane('B3');

// Set the year from the request
$year = isset($_POST['year']) ? $_POST['year'] : date('Y');

// Create Excel file
$writer = new Xlsx($spreadsheet);
$filename = 'RRR_Report_' . $year . '.xlsx';

// Send headers for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

$writer->save('php://output');
exit;
