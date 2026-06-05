<?php
session_start();
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

// Get parameters
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n') - 1;
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$startDate = "$year-" . str_pad($month + 1, 2, '0', STR_PAD_LEFT) . "-01";
$endDate = date("Y-m-t", strtotime($startDate));
$dateRange = date("F 1-t, Y", strtotime($startDate));

// Database connection
include '../backend/db_config_notpdo.php';
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Get logged in collector's info
$collectorEmail = $_SESSION['email'];
$collectorQuery = "SELECT firstname, lastname FROM santamaria_employees WHERE email = '$collectorEmail'";
$collectorResult = $conn->query($collectorQuery);
$collector = $collectorResult->fetch_assoc();
$collectorName = $collector['firstname'] . ' ' . $collector['lastname'];

$daysWithTransactionsQuery = "
    SELECT DISTINCT DATE(date_remitted) AS transaction_date 
    FROM transactions 
    WHERE collector_email = '$collectorEmail'
    AND DATE(date_remitted) BETWEEN '$startDate' AND '$endDate'
    ORDER BY transaction_date
";
$daysResult = $conn->query($daysWithTransactionsQuery);


// Get all receipt batches assigned to this collector
$batchesQuery = "SELECT id, serial_start, serial_end, quantity, quantity_left, created_at 
                 FROM receipt_batches 
                 WHERE collector_email = '$collectorEmail'
                 AND created_at <= '$endDate'";
$batchesResult = $conn->query($batchesQuery);

$allBatches = [];
while ($batch = $batchesResult->fetch_assoc()) {
    $allBatches[$batch['id']] = $batch; // key by batch ID
}

// Determine first usage date of each batch
$batchFirstUsage = [];
$allTxQuery = "
    SELECT pgl_no, DATE(date_remitted) AS transaction_date
    FROM transactions
    WHERE collector_email = '$collectorEmail'
    AND date_remitted IS NOT NULL
    ORDER BY date_remitted ASC, pgl_no ASC
";

$allTxResult = $conn->query($allTxQuery);

while ($tx = $allTxResult->fetch_assoc()) {
    $pglNo = $tx['pgl_no'];
    foreach ($allBatches as $batchId => $batch) {
        if ($pglNo >= $batch['serial_start'] && $pglNo <= $batch['serial_end']) {
            if (!isset($batchFirstUsage[$batchId])) {
                $batchFirstUsage[$batchId] = $tx['transaction_date'];
            }
            break;
        }
    }
}

// Sort batches by earliest usage date
uasort($allBatches, function ($a, $b) use ($batchFirstUsage) {
    $idA = $a['id'];
    $idB = $b['id'];
    $dateA = $batchFirstUsage[$idA] ?? '9999-12-31';
    $dateB = $batchFirstUsage[$idB] ?? '9999-12-31';
    return strtotime($dateA) <=> strtotime($dateB);
});

// Calculate ending balances from previous month for each batch
$previousMonthEnd = date("Y-m-t", strtotime("$startDate -1 month"));
$batchEndingBalances = [];
foreach ($allBatches as $batchId => $batch) {
    $serialStart = $batch['serial_start'];
    $serialEnd = $batch['serial_end'];

   $previousTransactionsQuery = "
    SELECT COUNT(*) AS used_count
    FROM transactions
    WHERE collector_email = '$collectorEmail'
    AND pgl_no BETWEEN '$serialStart' AND '$serialEnd'
    AND DATE(date_remitted) <= '$previousMonthEnd'
";

    $previousResult = $conn->query($previousTransactionsQuery);
    $previousData = $previousResult->fetch_assoc();
    $usedCount = $previousData['used_count'];

   $lastUsedQuery = "
    SELECT pgl_no
    FROM transactions
    WHERE collector_email = '$collectorEmail'
    AND pgl_no BETWEEN '$serialStart' AND '$serialEnd'
    AND date_remitted IS NOT NULL
    AND DATE(date_remitted) <= '$previousMonthEnd'
    ORDER BY pgl_no DESC
    LIMIT 1
";

    $lastUsedResult = $conn->query($lastUsedQuery);
    $lastUsed = $lastUsedResult->fetch_assoc();

    $endingFrom = $serialStart;
    $endingTo = $serialEnd;
    if ($lastUsed) {
        $endingFrom = getNextReceiptNumber($lastUsed['pgl_no']);
    }

    $batchEndingBalances[$batchId] = [
        'qty' => $batch['quantity'] - $usedCount,
        'from' => $endingFrom,
        'to' => $endingTo,
        'batch' => $batch
    ];
}

// Get all transactions for the month
$allTransactionsQuery = "
    SELECT pgl_no, amount, DATE(date_remitted) AS transaction_date
    FROM transactions
    WHERE collector_email = '$collectorEmail'
    AND date_remitted IS NOT NULL
    AND DATE(date_remitted) BETWEEN '$startDate' AND '$endDate'
    ORDER BY transaction_date, pgl_no
";

$allTransactionsResult = $conn->query($allTransactionsQuery);
$allTransactions = [];
while ($tx = $allTransactionsResult->fetch_assoc()) {
    $allTransactions[$tx['transaction_date']][] = $tx;
}

// --- Spreadsheet Setup (headers, styles) ---
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("Monthly");
$sheet->setCellValue("A1", "ACCOUNTABILITY FOR ACCOUNTABLE FORMS / MONTHLY REPORT");
$sheet->mergeCells("A1:L1");
$sheet->getStyle("A1")->getFont()->setBold(true);

$sheet->setCellValue("A3", "Date");
$sheet->setCellValue("B3", "Name of Forms");
$sheet->setCellValue("C3", "Beginning Balance");
$sheet->mergeCells("C3:E3");
$sheet->setCellValue("C4", "Qty");
$sheet->setCellValue("D4", "From");
$sheet->setCellValue("E4", "To");
$sheet->setCellValue("F3", "Issued");
$sheet->mergeCells("F3:H3");
$sheet->setCellValue("F4", "Qty");
$sheet->setCellValue("G4", "From");
$sheet->setCellValue("H4", "To");
$sheet->setCellValue("I3", "Amount");
$sheet->setCellValue("J3", "Ending Balance");
$sheet->mergeCells("J3:L3");
$sheet->setCellValue("J4", "Qty");
$sheet->setCellValue("K4", "From");
$sheet->setCellValue("L4", "To");

$headerStyle = [
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
];
$sheet->getStyle('A3:L4')->applyFromArray($headerStyle);

// --- Processing daily transactions ---
$row = 5;
$prevDayEnding = null;
$previousDate = null;

while ($day = $daysResult->fetch_assoc()) {
    $currentDate = $day['transaction_date'];
    $formattedDate = date("F j, Y", strtotime($currentDate));

    if ($previousDate !== null && $previousDate != $currentDate) $row++;
    $previousDate = $currentDate;

    $dateDisplayed = false;
    $dayTransactions = $allTransactions[$currentDate] ?? [];
    $transactionsByBatch = [];
    $remainingTx = $dayTransactions;

    foreach ($allBatches as $batchId => $batch) {
        $serialStart = $batch['serial_start'];
        $serialEnd = $batch['serial_end'];
        $batchTransactions = [];
        $remainingAfterBatch = [];

        foreach ($remainingTx as $transaction) {
            if ($transaction['pgl_no'] >= $serialStart && $transaction['pgl_no'] <= $serialEnd) {
                $batchTransactions[] = $transaction;
            } else {
                $remainingAfterBatch[] = $transaction;
            }
        }

        if (empty($batchTransactions)) continue;

        // Determine beginning balance
        $beginning = $batchEndingBalances[$batchId] ?? [
            'qty' => $batch['quantity'],
            'from' => $batch['serial_start'],
            'to' => $batch['serial_end']
        ];

        if ($prevDayEnding && $prevDayEnding['batch_id'] == $batchId) {
            $beginning['qty'] = $prevDayEnding['qty'];
            $beginning['from'] = $prevDayEnding['from'];
            $beginning['to'] = $prevDayEnding['to'];
        }

        $issuedQty = count($batchTransactions);
        $issuedFrom = $issuedQty > 0 ? $batchTransactions[0]['pgl_no'] : '';
        $issuedTo = $issuedQty > 0 ? $batchTransactions[$issuedQty - 1]['pgl_no'] : '';

        $endingQty = $beginning['qty'] - $issuedQty;
        $endingFrom = $endingQty > 0 ? getNextReceiptNumber($issuedTo) : '';
        $endingTo = $beginning['to'];

        $transactionsByBatch[] = [
            'batch_id' => $batchId,
            'beginning' => $beginning,
            'issued' => ['qty' => $issuedQty, 'from' => $issuedFrom, 'to' => $issuedTo, 'transactions' => $batchTransactions],
            'ending' => ['qty' => $endingQty, 'from' => $endingFrom, 'to' => $endingTo]
        ];

        $prevDayEnding = [
            'qty' => $endingQty,
            'from' => $endingFrom,
            'to' => $endingTo,
            'batch_id' => $batchId
        ];

        $remainingTx = $remainingAfterBatch;
        if (empty($remainingTx)) break;
    }

    foreach ($transactionsByBatch as $batchSegment) {
        $beginning = $batchSegment['beginning'];
        $issued = $batchSegment['issued'];
        $ending = $batchSegment['ending'];
        $segmentAmount = array_sum(array_column($issued['transactions'], 'amount'));

        if (!$dateDisplayed) {
            $sheet->setCellValue("A$row", $formattedDate);
            $dateDisplayed = true;
        } else {
            $sheet->setCellValue("A$row", "");
        }

        $sheet->setCellValue("B$row", "Official Receipt");
        $sheet->setCellValue("C$row", $beginning['qty']);
        $sheet->setCellValue("D$row", $beginning['from']);
        $sheet->setCellValue("E$row", $beginning['to']);
        $sheet->setCellValue("F$row", $issued['qty']);
        $sheet->setCellValue("G$row", $issued['from']);
        $sheet->setCellValue("H$row", $issued['to']);
        $sheet->setCellValue("I$row", $segmentAmount);
        $sheet->getStyle("I$row")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $sheet->setCellValue("J$row", $ending['qty']);
        $sheet->setCellValue("K$row", $ending['from']);
        $sheet->setCellValue("L$row", $ending['to']);
        $sheet->getStyle("A$row:L$row")->applyFromArray(['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]]);
        $row++;
        $sheet->getStyle("A$row:L$row")->applyFromArray(['borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]]);

        $batchEndingBalances[$batchSegment['batch_id']] = [
            'qty' => $ending['qty'],
            'from' => $ending['from'],
            'to' => $ending['to'],
            'batch' => $allBatches[$batchSegment['batch_id']]
        ];
    }
}

// Add empty row above total

$sheet->getStyle("A$row:L$row")->applyFromArray([
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
    ]
]);
$row++; // move to actual total row

// Calculate total amount
$totalAmount = array_sum(array_column(array_merge(...array_values($allTransactions)), 'amount'));

// Set total amount row values
$sheet->setCellValue("A$row", "Total Amount");
$sheet->mergeCells("A$row:G$row");
$sheet->setCellValue("H$row", $totalAmount);
$sheet->mergeCells("H$row:L$row");

// Apply number format
$sheet->getStyle("H$row")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

// Apply bold font
$sheet->getStyle("A$row:L$row")->getFont()->setBold(true);

// Center align total row contents
$sheet->getStyle("A$row:L$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A$row:L$row")->applyFromArray([
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
    ]
]);

// Signature
$row += 2;
$sheet->setCellValue("B$row", $collectorName);
$sheet->setCellValue("B" . ($row + 1), "Name & Signature");
$sheet->setCellValue("B" . ($row + 2), "Collector");
$sheet->setCellValue("J$row", $dateRange);
$sheet->setCellValue("J" . ($row + 1), "Date Covered");

// Auto-size columns
foreach (range('A', 'L') as $col) {
    if ($col === 'J') {
        $sheet->getColumnDimension($col)->setWidth(5); // Adjust 12 to desired width
    } else {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
}



// Output
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment;filename=Monthly_Accountability_Report_{$year}_" . ($month + 1) . ".xlsx");
header('Cache-Control: max-age=0');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;

// Helper function
function getNextReceiptNumber($currentNumber)
{
    if (preg_match('/(\d+)/', $currentNumber, $matches)) {
        $num = $matches[1];
        $nextNum = $num + 1;
        return str_replace($num, $nextNum, $currentNumber);
    }
    return $currentNumber . '-1';
}
