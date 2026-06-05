<?php
session_start();

require '../vendor/autoload.php';
require_once 'engrShare_report_helpers.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment; 
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color; 

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

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Engineering Share');

// Default font
$spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

// === HEADER ===
$sheet->mergeCells('A1:J1')->setCellValue('A1', 'Republic of the Philippines');
$sheet->mergeCells('A2:J2')->setCellValue('A2', 'Province of Laguna');
$sheet->mergeCells('A3:J3')->setCellValue('A3', $orgName);
$sheet->mergeCells('A4:J4')->setCellValue('A4', '');
$sheet->mergeCells('A5:J5')->setCellValue('A5', 'REPORT ON BUILDING, ELECTRICAL AND INSPECTION FEES');

$sheet->getStyle('A1:A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1:A5')->getFont()->setBold(true);

// Style the title
$sheet->getStyle('A5')->applyFromArray([
    'font' => [
        'bold' => true,
        'italic' => true,
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
    ],
]);

// Row heights for header
$sheet->getRowDimension(1)->setRowHeight(18);
$sheet->getRowDimension(2)->setRowHeight(18);
$sheet->getRowDimension(3)->setRowHeight(18);
$sheet->getRowDimension(5)->setRowHeight(20);

// === TABLE HEADER ===
$sheet->mergeCells('A7')->setCellValue('A7', 'MONTH');
$sheet->mergeCells('B7:D7')->setCellValue('B7', 'INSPECTION FEES');
$sheet->mergeCells('E7:G7')->setCellValue('E7', 'BUILDING PERMIT FEES');
$sheet->mergeCells('H7:J7')->setCellValue('H7', 'ELECTRICAL FEES');
$sheet->setCellValue('B8', '80% (LGU)');
$sheet->setCellValue('C8', '5% (N.G)');
$sheet->setCellValue('D8', "15% BLDG.\nOFFICIALS");
$sheet->getStyle('D8')->getAlignment()->setWrapText(true);
$sheet->setCellValue('E8', '80% (LGU)');
$sheet->setCellValue('F8', '5% (N.G)');
$sheet->setCellValue('G8', "15% BLDG.\nOFFICIALS");
$sheet->getStyle('G8')->getAlignment()->setWrapText(true);
$sheet->setCellValue('H8', '80% (LGU)');
$sheet->setCellValue('I8', '5% (N.G)');
$sheet->setCellValue('J8', "15% BLDG.\nOFFICIALS");
$sheet->getStyle('J8')->getAlignment()->setWrapText(true);
$sheet->setCellValue('L8', 'INSPECTION');
$sheet->setCellValue('M8', "BUILDING \nPERMIT");
$sheet->getStyle('M8')->getAlignment()->setWrapText(true);
$sheet->setCellValue('N8', 'ELECTRICAL');

// Header styling
$sheet->getStyle('A7:N8')->getFont()->setBold(true);
$sheet->getStyle('A7:N8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
$sheet->getStyle('A7:N8')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$sheet->getRowDimension(7)->setRowHeight(18);
$sheet->getRowDimension(8)->setRowHeight(50);

// === DATA ROWS ===
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

$inspectionTotals = getInspectionFeeMonthlyTotals($conn, $year);
$buildingTotals = getBuildingPermitFeeMonthlyTotals($conn, $year);
$electricalTotals = getElectricalFeeMonthlyTotals($conn, $year);

$startRow = 9;
foreach ($months as $i => $month) { 
    $row = $startRow + $i;
    
    // Month name
    $sheet->setCellValue('A' . $row, $month);
    
    // Starting totals for each category (0.00)
    $sheet->setCellValue('L' . $row, $inspectionTotals[$i]);
    $sheet->setCellValue('M' . $row, value: $buildingTotals[$i]);
    $sheet->setCellValue('N' . $row, $electricalTotals[$i]);
    
    // Partition formulas
    // Inspections group
    $sheet->setCellValue('B' . $row, '=L' . $row . '*0.80');
    $sheet->setCellValue('C' . $row, '=L' . $row . '*0.05');
    $sheet->setCellValue('D' . $row, '=L' . $row . '*0.15');
    
    // Building permit group
    $sheet->setCellValue('E' . $row, '=M' . $row . '*0.80');
    $sheet->setCellValue('F' . $row, '=M' . $row . '*0.05');
    $sheet->setCellValue('G' . $row, '=M' . $row . '*0.15');
    
    // Electrical group
    $sheet->setCellValue('H' . $row, '=N' . $row . '*0.80');
    $sheet->setCellValue('I' . $row, '=N' . $row . '*0.05');
    $sheet->setCellValue('J' . $row, '=N' . $row . '*0.15');
    
    // Apply number format with comma and 2 decimal places
    $sheet->getStyle("B{$row}:N{$row}")
          ->getNumberFormat()
          ->setFormatCode('#,##0.00');
    
    // Row height
    $sheet->getRowDimension($row)->setRowHeight(18);
}



// Borders for data table
$sheet->getStyle('A9:N20')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$sheet->getStyle('B9:N20')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

// === TOTAL ROW ===
$totalRow = 22;
$sheet->setCellValue('A' . $totalRow, 'TOTAL');
$sheet->getStyle('A' . $totalRow)->getFont()->setBold(true);
$sheet->setCellValue('B' . $totalRow, '=SUM(B9:B20)');
$sheet->setCellValue('C' . $totalRow, '=SUM(C9:C20)');
$sheet->setCellValue('D' . $totalRow, '=SUM(D9:D20)');
$sheet->setCellValue('E' . $totalRow, '=SUM(E9:E20)');
$sheet->setCellValue('F' . $totalRow, '=SUM(F9:F20)');
$sheet->setCellValue('G' . $totalRow, '=SUM(G9:G20)');
$sheet->setCellValue('H' . $totalRow, '=SUM(H9:H20)');
$sheet->setCellValue('I' . $totalRow, '=SUM(I9:I20)');
$sheet->setCellValue('J' . $totalRow, '=SUM(J9:J20)');
$sheet->setCellValue('L' . $totalRow, '=SUM(L9:L20)');
$sheet->setCellValue('M' . $totalRow, '=SUM(M9:M20)');
$sheet->setCellValue('N' . $totalRow, '=SUM(N9:N20)');

$sheet->getStyle('A' . $totalRow . ':N' . $totalRow)->getFont()->setBold(true)->getColor()->setRGB('00B050');
$sheet->getStyle('A' . $totalRow . ':N' . $totalRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
$sheet->getRowDimension($totalRow)->setRowHeight(18);

// === BOTTOM TOTALS ===
$sheet->mergeCells('A24:B24');
$sheet->setCellValue('C24', 'TOTAL');
$sheet->getStyle('C24')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->mergeCells('A25:B25')->setCellValue('A25', '80% (LGU)');
$sheet->mergeCells('A26:B26')->setCellValue('A26', '5% (N.G)');
$sheet->mergeCells('A27:B27')->setCellValue('A27', '15% BLDG OFFICIALS');
$sheet->setCellValue('C25', '=SUM(B22,E22,H22)'); // 80% LGU
$sheet->setCellValue('C26', '=SUM(C22,F22,I22)'); // 5% N.G
$sheet->setCellValue('C27', '=SUM(D22,G22,J22)'); // 15% BLDG OFFICIALS
$sheet->getStyle('C24:C27')->getFont()->setBold(true);

$sheet->getRowDimension(24)->setRowHeight(18);
$sheet->getRowDimension(25)->setRowHeight(18);
$sheet->getRowDimension(26)->setRowHeight(18);
$sheet->getRowDimension(27)->setRowHeight(18);
$sheet->getStyle('A24:C27')->getBorders()->getAllBorders()->setBorderStyle(
    Border::BORDER_THIN
);



// === PREPARED BY ===
$sheet->setCellValue('A29', 'Prepared by:');
$sheet->setCellValue('B29', $treasurerName);
$sheet->mergeCells('B29:C29')->setCellValue('B29', $treasurerName);
$sheet->getStyle('B29')->getFont()->setBold(true);
$sheet->getStyle('B29')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Column widths
$widths = [15, 12, 12, 12, 12, 12, 12, 12, 12, 12, 5, 12, 15, 12];
foreach (range('A', 'N') as $i => $col) {
    $sheet->getColumnDimension($col)->setWidth($widths[$i]);
}

$sheet->getStyle('L7:N7')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_NONE);
$sheet->getStyle('A5:J5')->getBorders()->getOutline()->setBorderStyle(
    Border::BORDER_THICK
);
$sheet->getStyle('A7:J7')->getBorders()->getAllBorders()->setBorderStyle(
    Border::BORDER_THICK
);

$sheet->getStyle('A8:A20')->getBorders()->getOutline()->setBorderStyle(
    Border::BORDER_THICK
);

$sheet->getStyle('B8:D20')->getBorders()->getOutline()->setBorderStyle(
    Border::BORDER_THICK
);

$sheet->getStyle('E8:G20')->getBorders()->getOutline()->setBorderStyle(
    Border::BORDER_THICK
);

$sheet->getStyle('H8:J20')->getBorders()->getOutline()->setBorderStyle(
    Border::BORDER_THICK
);

$sheet->getStyle('L8:L20')->getBorders()->getOutline()->setBorderStyle(
    Border::BORDER_THICK
);

$sheet->getStyle('M8:M20')->getBorders()->getOutline()->setBorderStyle(
    Border::BORDER_THICK
);

$sheet->getStyle('N8:N20')->getBorders()->getOutline()->setBorderStyle(
    Border::BORDER_THICK
);


$highestRow = $sheet->getHighestRow();
$sheet->getStyle('K1:K' . $highestRow)
    ->getBorders()
    ->getAllBorders()
    ->setBorderStyle(Border::BORDER_NONE);


// Output
$filename = "Engineering_Share_Report_{$year}.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
