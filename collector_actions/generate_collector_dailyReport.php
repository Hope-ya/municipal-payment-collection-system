<?php
session_start();
date_default_timezone_set('Asia/Manila'); // Adjust to your timezone

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

// Database connection
include '../backend/db_config_notpdo.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$loggedInEmail = $_SESSION['email'];

// Fetch transactions from database including PGL numbers
$transactionsQuery = "SELECT pgl_no, date, payorname as name, particulars, amount 
                     FROM transactions 
                     WHERE DATE(date_remitted) = CURDATE() 
                     AND collector_email = '$loggedInEmail' 
                     ORDER BY pgl_no";

$transactionsResult = $conn->query($transactionsQuery);

// Fetch check payments from transactions table
$sql = "SELECT check_num, payorname, amount 
        FROM transactions 
        WHERE payment_type = 'Check' 
        AND DATE(date_remitted) = CURDATE() 
        AND collector_email = '$loggedInEmail'";

$checkResult = $conn->query($sql);

// Fetch total cash from transactions table (non-check payments)
$cashTotalQuery = "SELECT SUM(amount) as total_cash 
                   FROM transactions 
                   WHERE (payment_type != 'Check' AND payment_type = 'Cash') ";
$cashResult = $conn->query($cashTotalQuery);
$totalCash = $cashResult->fetch_assoc()['total_cash'] ?? 0;

// Update AF No. 51 data to use actual PGL numbers from database
$pglRangeQuery = "SELECT MIN(pgl_no) as first_pgl, MAX(pgl_no) as last_pgl 
                  FROM transactions 
                  WHERE DATE(date_remitted) = CURDATE()";
$pglRangeResult = $conn->query($pglRangeQuery);
$pglRange = $pglRangeResult->fetch_assoc();

$cashTotalQuery = "SELECT SUM(amount) as total_cash FROM transactions WHERE payment_type ='Cash'  AND collector_email = '$loggedInEmail' ";
$cashResult = $conn->query($cashTotalQuery);
$totalCash = $cashResult->fetch_assoc()['total_cash'] ?? 0;

$checkQuery = "SELECT check_num, payorname, amount FROM transactions WHERE payment_type = 'Check' AND DATE(date_remitted) = CURDATE()  AND collector_email = '$loggedInEmail' ";
$checkResult = $conn->query($checkQuery);
$totalChecks = 0;

if ($checkResult->num_rows > 0) {
    $checkResult->data_seek(0);
    while ($check = $checkResult->fetch_assoc()) {
        $totalChecks += $check['amount'];
    }
}

function generatePreviewData($conn)
{
    global $loggedInEmail;

    $today = date('Y-m-d');

    // Fetch all active batches for this collector (with beginning balances)
    $allBatchesQuery = "
        SELECT 
            rb.id,
            rb.serial_start,
            rb.serial_end,
            rb.quantity,
            rb.quantity_left,
            COALESCE((
                SELECT COUNT(*) 
                FROM transactions t
                WHERE t.collector_email = '$loggedInEmail'
                AND t.date_remitted < '$today'
                AND t.pgl_no BETWEEN rb.serial_start AND rb.serial_end
            ), 0) as issued_before
        FROM receipt_batches rb
        WHERE rb.collector_email = '$loggedInEmail'
        AND DATE(rb.created_at) <= '$today'
        ORDER BY rb.created_at, rb.serial_start
    ";

    $allBatchesResult = $conn->query($allBatchesQuery);
    $allBatches = [];

    while ($batch = $allBatchesResult->fetch_assoc()) {
        $totalQty = (int)$batch['quantity'];
        $issuedBefore = (int)$batch['issued_before'];

        $batch['beginning_qty'] = max(0, $totalQty - $issuedBefore);
        $batch['beginning_from'] = (int)$batch['serial_start'] + $issuedBefore;
        $batch['beginning_to'] = (int)$batch['serial_end'];
        $batch['issued_today'] = 0;

        $allBatches[] = $batch;
    }

    // Fetch all transactions for today
    $transactionsQuery = "
        SELECT pgl_no, date, payorname AS name, particulars, amount
        FROM transactions
        WHERE DATE(date_remitted) = '$today'
        AND collector_email = '$loggedInEmail'
        ORDER BY pgl_no
    ";
    $transactionsResult = $conn->query($transactionsQuery);
    $dayTransactions = [];
    while ($tx = $transactionsResult->fetch_assoc()) {
        $tx['pgl_no'] = (int)$tx['pgl_no'];
        $dayTransactions[] = $tx;
    }

    // Allocate transactions across multiple batches
    $transactionsByBatch = [];
    $remainingTx = $dayTransactions;

    foreach ($allBatches as &$batch) {
        $batchTransactions = [];
        $stillRemainingTx = [];

        foreach ($remainingTx as $tx) {
            if ($tx['pgl_no'] >= $batch['serial_start'] && $tx['pgl_no'] <= $batch['serial_end']) {
                $batchTransactions[] = $tx;
            } else {
                $stillRemainingTx[] = $tx;
            }
        }

        if (!empty($batchTransactions)) {
            $issuedQty = count($batchTransactions);
            $issuedFrom = min(array_column($batchTransactions, 'pgl_no'));
            $issuedTo = max(array_column($batchTransactions, 'pgl_no'));

            $endingQty = max(0, $batch['beginning_qty'] - $issuedQty);
            $endingFrom = $endingQty > 0 ? ($issuedTo + 1) : '';
            $endingTo = $endingQty > 0 ? $batch['beginning_to'] : '';

            $transactionsByBatch[] = [
                'batch_id' => $batch['id'],
                'beginning' => [
                    'qty' => $batch['beginning_qty'],
                    'from' => $batch['beginning_from'],
                    'to' => $batch['beginning_to']
                ],
                'issued' => [
                    'qty' => $issuedQty,
                    'from' => $issuedFrom,
                    'to' => $issuedTo,
                    'transactions' => $batchTransactions
                ],
                'ending' => [
                    'qty' => $endingQty,
                    'from' => $endingFrom,
                    'to' => $endingTo
                ]
            ];

            // Update batch beginning for next iteration
            $batch['beginning_qty'] = $endingQty;
            $batch['beginning_from'] = $endingFrom;
            $batch['beginning_to'] = $endingTo;
        }

        $remainingTx = $stillRemainingTx;
        if (empty($remainingTx)) break;
    }
    unset($batch);

    // Fetch totals
    $cashTotalQuery = "SELECT SUM(amount) as total_cash FROM transactions WHERE payment_type ='Cash' AND collector_email = '$loggedInEmail'";
    $cashResult = $conn->query($cashTotalQuery);
    $totalCash = $cashResult->fetch_assoc()['total_cash'] ?? 0;

    $checkQuery = "SELECT check_num, payorname, amount FROM transactions WHERE payment_type = 'Check' AND DATE(date_remitted) = '$today' AND collector_email = '$loggedInEmail'";
    $checkResult = $conn->query($checkQuery);
    $totalChecks = 0;

    if ($checkResult->num_rows > 0) {
        $checkResult->data_seek(0);
        while ($check = $checkResult->fetch_assoc()) {
            $totalChecks += $check['amount'];
        }
        // Reset pointer for later use
        $checkResult->data_seek(0);
    }

    // Start building Daily Sheet HTML
    $dailySheetHTML = '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Daily Sheet Preview</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-white p-5">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-5">
                <div class="text-lg font-bold">ACCOUNTABILITY FOR ACCOUNTABLE FORMS / DAILY REPORT</div>
                <div class="text-base">For ' . date('F j, Y') . '</div>
            </div>';

    // Accountability for Accountable Forms Section
    $dailySheetHTML .= '<table class="w-full border-collapse border border-gray-800 text-xs mb-8">
                    <thead>
                        <tr class="bg-gray-100 font-bold text-center">
                            <th rowspan="2" class="border border-gray-800 p-2">Name of Forms</th>
                            <th colspan="3" class="border border-gray-800 p-2">Beginning Balance</th>
                            <th colspan="3" class="border border-gray-800 p-2">Receipt</th>
                            <th colspan="3" class="border border-gray-800 p-2">Issued</th>
                            <th rowspan="2" class="border border-gray-800 p-2">Amount</th>
                            <th colspan="3" class="border border-gray-800 p-2">Ending Balance</th>
                        </tr>
                        <tr class="bg-gray-100 font-bold text-center">
                            <th class="border border-gray-800 p-2">Qty</th>
                            <th class="border border-gray-800 p-2">From</th>
                            <th class="border border-gray-800 p-2">To</th>
                            <th class="border border-gray-800 p-2">Qty</th>
                            <th class="border border-gray-800 p-2">From</th>
                            <th class="border border-gray-800 p-2">To</th>
                            <th class="border border-gray-800 p-2">Qty</th>
                            <th class="border border-gray-800 p-2">From</th>
                            <th class="border border-gray-800 p-2">To</th>
                            <th class="border border-gray-800 p-2">Qty</th>
                            <th class="border border-gray-800 p-2">From</th>
                            <th class="border border-gray-800 p-2">To</th>
                        </tr>
                    </thead>
                    <tbody>';

    // Build rows for each batch with issued transactions today
    $totalAmount = 0;
    if (!empty($transactionsByBatch)) {
        foreach ($transactionsByBatch as $batchSegment) {
            $beginning = $batchSegment['beginning'];
            $issued = $batchSegment['issued'];
            $ending = $batchSegment['ending'];

            $segmentAmount = array_sum(array_column($issued['transactions'], 'amount'));
            $totalAmount += $segmentAmount;

            $dailySheetHTML .= '<tr class="text-center">
                        <td class="border border-gray-800 p-2">A/F 51</td>
                        <td class="border border-gray-800 p-2">' . $beginning['qty'] . '</td>
                        <td class="border border-gray-800 p-2">' . $beginning['from'] . '</td>
                        <td class="border border-gray-800 p-2">' . $beginning['to'] . '</td>
                        <td class="border border-gray-800 p-2">' . $beginning['qty'] . '</td>
                        <td class="border border-gray-800 p-2">' . $beginning['from'] . '</td>
                        <td class="border border-gray-800 p-2">' . $beginning['to'] . '</td>
                        <td class="border border-gray-800 p-2">' . $issued['qty'] . '</td>
                        <td class="border border-gray-800 p-2">' . $issued['from'] . '</td>
                        <td class="border border-gray-800 p-2">' . $issued['to'] . '</td>
                        <td class="border border-gray-800 p-2 text-right">' . number_format($segmentAmount, 2) . '</td>
                        <td class="border border-gray-800 p-2">' . $ending['qty'] . '</td>
                        <td class="border border-gray-800 p-2">' . $ending['from'] . '</td>
                        <td class="border border-gray-800 p-2">' . $ending['to'] . '</td>
                    </tr>';
        }

        // Add total row
        $dailySheetHTML .= '<tr class="font-bold text-center">
                    <td colspan="10" class="border border-gray-800 p-2">Total Amount</td>
                    <td class="border border-gray-800 p-2 text-right">' . number_format($totalAmount, 2) . '</td>
                    <td colspan="3" class="border border-gray-800 p-2"></td>
                </tr>';
    } else {
        $dailySheetHTML .= '<tr><td colspan="14" class="border border-gray-800 p-2 text-center">No accountable forms used today</td></tr>';
    }

    $dailySheetHTML .= '</tbody></table>';

    // Summary of Collections section
    $dailySheetHTML .= '<div class="mb-8 border border-gray-800 p-2">
                <h4 class="font-bold mb-2">D. SUMMARY OF COLLECTIONS AND REMITTANCES/DEPOSITS</h4>
                <div class="space-y-2 text-sm">
                    <div>Beginning Balance___________200___P</div>
                    <div class="flex">
                        <span class="w-48">Add. Collections</span>
                        <span>Cash ________________________ ' . number_format($totalCash, 2) . '</span>
                    </div>
                    <div class="flex">
                        <span class="w-48"></span>
                        <span>Check ________________________ ' . number_format($totalChecks, 2) . '</span>
                    </div>
                    <div class="flex">
                        <span class="w-48"></span>
                        <span>Total ________________________ ' . number_format($totalCash + $totalChecks, 2) . '</span>
                    </div>
                    <div>Less Remittance/Deposit to Cashier</div>
                    <div>Treasurer/Depository Bank ___________________</div>
                    <div>BALANCE ___________________</div>
                </div>
            </div>';

    // List of Checks section
    $dailySheetHTML .= '<div class="mb-8">
                <h4 class="font-bold mb-2">LIST OF CHECKS</h4>
                <table class="w-full border-collapse border border-gray-800 text-xs">
                    <thead>
                        <tr class="bg-gray-100 font-bold text-center">
                            <th colspan="2" class="border border-gray-800 p-2">Bank / Check Number</th>
                            <th colspan="3" class="border border-gray-800 p-2">Account Name</th>
                            <th colspan="2" class="border border-gray-800 p-2">Amount</th>
                        </tr>
                    </thead>
                    <tbody>';

    if ($checkResult->num_rows > 0) {
        $checkResult->data_seek(0);
        while ($check = $checkResult->fetch_assoc()) {
            $dailySheetHTML .= '<tr>
                        <td colspan="2" class="border border-gray-800 p-2">' . $check['check_num'] . '</td>
                        <td colspan="3" class="border border-gray-800 p-2">' . $check['payorname'] . '</td>
                        <td colspan="2" class="border border-gray-800 p-2 text-right">' . number_format($check['amount'], 2) . '</td>
                    </tr>';
        }
    } else {
        $dailySheetHTML .= '<tr><td colspan="7" class="border border-gray-800 p-2 text-center">No check payments for today</td></tr>';
    }
    $dailySheetHTML .= '</tbody></table></div>';

    // Certification section
    $dailySheetHTML .= '<div class="mb-8 border border-gray-800 p-2">
                <h4 class="font-bold mb-2">CERTIFICATION</h4>
                <div class="space-y-1 text-sm">
                    <div>I hereby certify that foregoing report of collections and</div>
                    <div>deposits and accountability for accountable forms is true and correct</div>
                </div>
            </div>';

    // Verification section
    $dailySheetHTML .= '<div class="mb-8 border border-gray-800 p-2">
                <h4 class="font-bold mb-2">VERIFICATION AND ACKNOWLEDGEMENT</h4>
                <div class="space-y-1 text-sm">
                    <div>I hereby certify that the foregoing report of collections</div>
                    <div>has been verified and acknowledge receipt of</div>
                    <div>___________________________(P______________)</div>
                </div>
            </div>';

    // Signatures
    $dailySheetHTML .= '<div class="flex justify-between mt-4">
                <div class="text-center">
                    <div class="font-bold">LEO ANNE LUCILLO</div>
                    <div>Name & Signature</div>
                    <div>Accountable Officer</div>
                    <div>' . date('F j, Y') . '</div>
                </div>
                <div class="text-center">
                    <div class="font-bold">MA.THERESA L. LONTOK</div>
                    <div>Name & Signature</div>
                    <div>Cashier/Treasurer</div>
                    <div>' . date('F j, Y') . '</div>
                </div>
            </div>';

    $dailySheetHTML .= '</div></body></html>';

    // Daily Transaction Sheet (unchanged from your original code)
    $transactionsQuery = "SELECT pgl_no, date, payorname as name, particulars, amount 
                         FROM transactions 
                         WHERE date_remitted = '$today'  AND collector_email = '$loggedInEmail' 
                         ORDER BY pgl_no";
    $transactionsResult = $conn->query($transactionsQuery);

    $dailyTransactionHTML = '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Daily Transaction Sheet Preview</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-white p-5">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-5">
                <div class="text-lg font-bold">DAILY TRANSACTION SHEET</div>
                <div class="text-base">For ' . date('F j, Y') . '</div>
            </div>';

    // Daily Transaction Section
    $dailyTransactionHTML .= '<table class="w-full border-collapse border border-gray-800 text-xs">
                    <thead>
                        <tr class="bg-gray-100 font-bold text-center">
                            <th class="border border-gray-800 p-2">OR No.</th>
                            <th class="border border-gray-800 p-2">DATE</th>
                            <th class="border border-gray-800 p-2">NAME</th>
                            <th class="border border-gray-800 p-2">PARTICULARS</th>
                            <th class="border border-gray-800 p-2">AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody>';

    $totalAmount = 0;
    if ($transactionsResult->num_rows > 0) {
        while ($transaction = $transactionsResult->fetch_assoc()) {
            $totalAmount += $transaction['amount'];
            $dailyTransactionHTML .= '<tr>
                        <td class="border border-gray-800 p-2">' . $transaction['pgl_no'] . '</td>
                        <td class="border border-gray-800 p-2">' . date('F j, Y', strtotime($transaction['date'])) . '</td>
                        <td class="border border-gray-800 p-2">' . $transaction['name'] . '</td>
                        <td class="border border-gray-800 p-2">' . str_replace(',', '<br>', $transaction['particulars']) . '</td>
                        <td class="border border-gray-800 p-2 text-right">' . number_format($transaction['amount'], 2) . '</td>
                    </tr>';
        }
    } else {
        $dailyTransactionHTML .= '<tr><td colspan="5" class="border border-gray-800 p-2 text-center">No transactions for today</td></tr>';
    }

    $dailyTransactionHTML .= '<tr class="font-bold">
                <td colspan="3" class="border border-gray-800 p-2">TOTAL</td>
                <td colspan="2" class="border border-gray-800 p-2 text-right">' . number_format($totalAmount, 2) . '</td>
            </tr>
            </tbody>
        </table>
    </div></body></html>';

    return [
        'dailyTransaction' => $dailyTransactionHTML,
        'dailySheet' => $dailySheetHTML
    ];
}


// Get the POST data
$data = json_decode(file_get_contents('php://input'), true);

// Check if this is a preview request
$isPreview = isset($data['preview']) && $data['preview'] === true;

if ($isPreview) {
    // For preview, return HTML representations of the sheets
    $previewData = generatePreviewData($conn);
    header('Content-Type: application/json');
    echo json_encode($previewData);
    exit;
}


// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set document properties
$spreadsheet->getProperties()
    ->setCreator("Collector System")
    ->setTitle("Collector's Daily Report")
    ->setSubject("Daily Transaction Report");

// Daily Transaction Sheet
$sheet->setTitle('Daily Transaction');

// Header style
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
        'size' => 12,
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '4472C4'],
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000'],
        ],
    ],
];

// Set headers - Changed "OR NO." to "PGL No."
$sheet->setCellValue('A1', 'OR No.');
$sheet->setCellValue('B1', 'DATE');
$sheet->setCellValue('C1', 'NAME');
$sheet->setCellValue('D1', 'PARTICULARS');
$sheet->setCellValue('E1', 'AMOUNT');
$sheet->getStyle('A1:E1')->applyFromArray($headerStyle);
$sheet->getRowDimension(1)->setRowHeight(25);

// Data style
$dataStyle = [
    'alignment' => [
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'borders' => [
        'outline' => [
            'borderStyle' => Border::BORDER_THIN,
        ],
    ],
];

// Add transaction data from database
$row = 2;
if ($transactionsResult->num_rows > 0) {
    while ($transaction = $transactionsResult->fetch_assoc()) {
        // Use actual PGL number from database
        $sheet->setCellValue('A' . $row, $transaction['pgl_no']);

        // Format date as "Month Day, Year" (e.g., "April 26, 2025")
        $date = new DateTime($transaction['date']);
        $formattedDate = $date->format('F j, Y');
        $sheet->setCellValue('B' . $row, $formattedDate);

        $sheet->setCellValue('C' . $row, $transaction['name']);

        // Format particulars with line breaks
        $particulars = str_replace(',', "\n", $transaction['particulars']);
        $sheet->setCellValue('D' . $row, $particulars);

        // REQUIRED FORMATTING
        $sheet->getStyle('D' . $row)
            ->getAlignment()
            ->setWrapText(true)
            ->setVertical(Alignment::VERTICAL_TOP);

        // Auto-adjust row height based on line breaks
        $lineCount = substr_count($particulars, "\n") + 1;
        $sheet->getRowDimension($row)->setRowHeight(15 * $lineCount);

        // Amount formatting
        $sheet->setCellValue('E' . $row, $transaction['amount']);
        $sheet->getStyle('E' . $row)
            ->getNumberFormat()
            ->setFormatCode('#,##0.00');

        $row++;
    }
} else {
    $sheet->setCellValue('A2', 'No transactions for today');
    $sheet->mergeCells('A2:E2');
    $row++;
}
// Total row style
$totalStyle = [
    'font' => [
        'bold' => true,
    ],
    'borders' => [
        'top' => [
            'borderStyle' => Border::BORDER_THIN,
        ],
    ],
];

// Format total amount
$sheet->getStyle('E' . $row)
    ->getNumberFormat()
    ->setFormatCode('#,##0.00');
$sheet->getStyle('E' . $row)
    ->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

// Calculate total from actual transactions
$totalAmount = 0;
if ($transactionsResult->num_rows > 0) {
    // Reset pointer and recalculate
    $transactionsResult->data_seek(0);
    while ($transaction = $transactionsResult->fetch_assoc()) {
        $totalAmount += $transaction['amount'];
    }
}

// Add total row
$sheet->setCellValue('C' . $row, 'TOTAL');
$sheet->setCellValue('E' . $row, $totalAmount); // Use calculated total instead of POST data

$sheet->getStyle('C' . $row . ':E' . $row)->applyFromArray($totalStyle);



// Auto-size columns with minimum widths
$sheet->getColumnDimension('A')->setWidth(10);  // PGL No.
$sheet->getColumnDimension('B')->setWidth(20);  // DATE
$sheet->getColumnDimension('C')->setWidth(25);  // NAME (wider for longer names)
$sheet->getColumnDimension('D')->setWidth(40);  // PARTICULARS
$sheet->getColumnDimension('E')->setWidth(15);  // AMOUNT

// Freeze header row
$sheet->freezePane('A2');

// ... (previous code remains the same until the Daily sheet section)

$collectorEmail = $_SESSION['email'];
$today = date('Y-m-d');
$reportDate = isset($_GET['reportDate']) ? $_GET['reportDate'] : $today;

// Database connection
include '../backend/db_config_notpdo.php';
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fetch all active batches for this collector (with beginning balances)
$allBatchesQuery = "
    SELECT 
        rb.id,
        rb.serial_start,
        rb.serial_end,
        rb.quantity,
        rb.quantity_left,
        COALESCE((
            SELECT COUNT(*) 
            FROM transactions t
            WHERE t.collector_email = '$collectorEmail'
            AND t.date_remitted < '$reportDate'
            AND t.pgl_no BETWEEN rb.serial_start AND rb.serial_end
        ), 0) as issued_before
    FROM receipt_batches rb
    WHERE rb.collector_email = '$collectorEmail'
    AND DATE(rb.created_at) <= '$reportDate'
    ORDER BY rb.created_at, rb.serial_start
";

$allBatchesResult = $conn->query($allBatchesQuery);
$allBatches = [];

while ($batch = $allBatchesResult->fetch_assoc()) {
    $totalQty = (int)$batch['quantity'];
    $issuedBefore = (int)$batch['issued_before'];

    $batch['beginning_qty'] = max(0, $totalQty - $issuedBefore);
    $batch['beginning_from'] = (int)$batch['serial_start'] + $issuedBefore;
    $batch['beginning_to'] = (int)$batch['serial_end'];
    $batch['issued_today'] = 0;

    $allBatches[] = $batch;
}

// Fetch all transactions for the report date
$transactionsQuery = "
    SELECT pgl_no, date, payorname AS name, particulars, amount
    FROM transactions
    WHERE date_remitted = '$reportDate'
    AND collector_email = '$collectorEmail'
    ORDER BY pgl_no
";
$transactionsResult = $conn->query($transactionsQuery);
$dayTransactions = [];
while ($tx = $transactionsResult->fetch_assoc()) {
    $tx['pgl_no'] = (int)$tx['pgl_no'];
    $dayTransactions[] = $tx;
}

// Allocate transactions across multiple batches
$transactionsByBatch = [];
$remainingTx = $dayTransactions;

foreach ($allBatches as &$batch) {
    $batchTransactions = [];
    $stillRemainingTx = [];

    foreach ($remainingTx as $tx) {
        if ($tx['pgl_no'] >= $batch['serial_start'] && $tx['pgl_no'] <= $batch['serial_end']) {
            $batchTransactions[] = $tx;
        } else {
            $stillRemainingTx[] = $tx;
        }
    }

    if (!empty($batchTransactions)) {
        $issuedQty = count($batchTransactions);
        $issuedFrom = min(array_column($batchTransactions, 'pgl_no'));
        $issuedTo = max(array_column($batchTransactions, 'pgl_no'));

        $endingQty = max(0, $batch['beginning_qty'] - $issuedQty);
        $endingFrom = $endingQty > 0 ? ($issuedTo + 1) : '';
        $endingTo = $endingQty > 0 ? $batch['beginning_to'] : '';

        $transactionsByBatch[] = [
            'batch_id' => $batch['id'],
            'beginning' => [
                'qty' => $batch['beginning_qty'],
                'from' => $batch['beginning_from'],
                'to' => $batch['beginning_to']
            ],
            'issued' => [
                'qty' => $issuedQty,
                'from' => $issuedFrom,
                'to' => $issuedTo,
                'transactions' => $batchTransactions
            ],
            'ending' => [
                'qty' => $endingQty,
                'from' => $endingFrom,
                'to' => $endingTo
            ]
        ];

        // Update batch beginning for next iteration
        $batch['beginning_qty'] = $endingQty;
        $batch['beginning_from'] = $endingFrom;
        $batch['beginning_to'] = $endingTo;
    }

    $remainingTx = $stillRemainingTx;
    if (empty($remainingTx)) break;
}
unset($batch);

// DAILY SHEET GENERATION
$dailySheet = $spreadsheet->createSheet();
$dailySheet->setTitle("Daily");

$dailySheet->setCellValue("A1", "ACCOUNTABILITY FOR ACCOUNTABLE FORMS / DAILY REPORT");
$dailySheet->mergeCells("A1:N1");
$dailySheet->getStyle("A1")->getFont()->setBold(true);

// Headers
$headers = [
    ['A3:A4', 'Name of Forms'],
    ['B3:D3', 'Beginning Balance'],
    ['B4', 'Qty'],
    ['C4', 'From'],
    ['D4', 'To'],
    ['E3:G3', 'Receipt'],
    ['E4', 'Qty'],
    ['F4', 'From'],
    ['G4', 'To'],
    ['H3:J3', 'Issued'],
    ['H4', 'Qty'],
    ['I4', 'From'],
    ['J4', 'To'],
    ['K3:K4', 'Amount'],
    ['L3:N3', 'Ending Balance'],
    ['L4', 'Qty'],
    ['M4', 'From'],
    ['N4', 'To']
];
foreach ($headers as $header) {
    if (strpos($header[0], ':') !== false) {
        $dailySheet->setCellValue(explode(':', $header[0])[0], $header[1]);
        $dailySheet->mergeCells($header[0]);
    } else {
        $dailySheet->setCellValue($header[0], $header[1]);
    }
}
$dailySheet->getStyle('A3:N4')->applyFromArray([
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
]);

// Populate batches dynamically
$row = 5;
$totalAmount = 0; // Initialize total

foreach ($transactionsByBatch as $batchSegment) {
    $beginning = $batchSegment['beginning'];
    $issued = $batchSegment['issued'];
    $ending = $batchSegment['ending'];

    $segmentAmount = array_sum(array_column($issued['transactions'], 'amount'));
    $totalAmount += $segmentAmount; // Accumulate total

    $dailySheet->setCellValue("A{$row}", "A/F 51");
    $dailySheet->setCellValue("B{$row}", $beginning['qty']);
    $dailySheet->setCellValue("C{$row}", $beginning['from']);
    $dailySheet->setCellValue("D{$row}", $beginning['to']);
    $dailySheet->setCellValue("E{$row}", $beginning['qty']);
    $dailySheet->setCellValue("F{$row}", $beginning['from']);
    $dailySheet->setCellValue("G{$row}", $beginning['to']);
    $dailySheet->setCellValue("H{$row}", $issued['qty']);
    $dailySheet->setCellValue("I{$row}", $issued['from']);
    $dailySheet->setCellValue("J{$row}", $issued['to']);
    $dailySheet->setCellValue("K{$row}", $segmentAmount);
    $dailySheet->getStyle("K{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
    $dailySheet->setCellValue("L{$row}", $ending['qty']);
    $dailySheet->setCellValue("M{$row}", $ending['from']);
    $dailySheet->setCellValue("N{$row}", $ending['to']);

    $dailySheet->getStyle("A{$row}:N{$row}")->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ]);

    $row++;
}

// Total row
$dailySheet->setCellValue("A{$row}", "Total Amount");
$dailySheet->mergeCells("A{$row}:J{$row}");
$dailySheet->setCellValue("K{$row}", $totalAmount); // Uses accumulated total
$dailySheet->getStyle("K{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
$dailySheet->getStyle("A{$row}:N{$row}")->getFont()->setBold(true);
$dailySheet->getStyle("A{$row}:K{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$dailySheet->getStyle("A{$row}:N{$row}")->applyFromArray([
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
]);

// Apply bold font
$dailySheet->getStyle("A{$row}:N{$row}")->getFont()->setBold(true);

// Center align label and total
$dailySheet->getStyle("A{$row}:K{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Add borders for the total row
$dailySheet->getStyle("A{$row}:N{$row}")->applyFromArray([
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
]);


// Apply dynamic style to batch rows
// After populating the rows dynamically
$lastRow = $row - 1; // last filled row

if ($lastRow >= 5) {
    // Apply borders & alignment
    $dailySheet->getStyle("A5:N{$lastRow}")->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER
        ]
    ]);

    // Apply number format to Amount column (K)
    $dailySheet->getStyle("K5:K{$lastRow}")
        ->getNumberFormat()
        ->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);
}

// Apply header style
$dailyHeaderStyle = [
    'font' => ['bold' => true, 'size' => 12],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
];
$dailySheet->getStyle('A1:N4')->applyFromArray($dailyHeaderStyle);


// Set column widths for Daily sheet (A-N)
$dailySheet->getColumnDimension('A')->setWidth(15);
$dailySheet->getColumnDimension('B')->setWidth(8);
$dailySheet->getColumnDimension('C')->setWidth(15);
$dailySheet->getColumnDimension('D')->setWidth(15);
$dailySheet->getColumnDimension('E')->setWidth(8);
$dailySheet->getColumnDimension('F')->setWidth(15);
$dailySheet->getColumnDimension('G')->setWidth(15);
$dailySheet->getColumnDimension('H')->setWidth(8);
$dailySheet->getColumnDimension('I')->setWidth(15);
$dailySheet->getColumnDimension('J')->setWidth(15);
$dailySheet->getColumnDimension('K')->setWidth(15);
$dailySheet->getColumnDimension('L')->setWidth(8);
$dailySheet->getColumnDimension('M')->setWidth(15);
$dailySheet->getColumnDimension('N')->setWidth(15);


// Create a centered alignment style
$centerStyle = [
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ]
];

// Assume $row currently points to the row after the last batch entry
$startRow = $row + 1; // leave one empty row before summary and check list

// --- SUMMARY SECTION (LEFT SIDE) ---
$summaryHeaderRow = $startRow;
$dailySheet->setCellValue('A' . $summaryHeaderRow, 'D. SUMMARY OF COLLECTIONS AND REMITTANCES/DEPOSITS');
$dailySheet->mergeCells('A' . $summaryHeaderRow . ':G' . $summaryHeaderRow);
$dailySheet->getStyle('A' . $summaryHeaderRow)->getFont()->setBold(true);

$summaryRow = $summaryHeaderRow + 2;
$dailySheet->setCellValue('A' . $summaryRow, 'Beginning Balance___________200___P');
$dailySheet->mergeCells('A' . $summaryRow . ':G' . $summaryRow);

$summaryRow++;
$dailySheet->setCellValue('A' . $summaryRow, 'Add. Collections');
$dailySheet->mergeCells('A' . $summaryRow . ':B' . $summaryRow);

$dailySheet->setCellValue('C' . $summaryRow, 'Cash');
$dailySheet->setCellValue('D' . $summaryRow, $totalCash ?: 0);
$dailySheet->mergeCells('D' . $summaryRow . ':E' . $summaryRow);
$dailySheet->getStyle('D' . $summaryRow)->getNumberFormat()->setFormatCode('#,##0.00');

$summaryRow++;
$dailySheet->setCellValue('C' . $summaryRow, 'Check');
$dailySheet->setCellValue('D' . $summaryRow, $totalChecks ?: 0);
$dailySheet->mergeCells('D' . $summaryRow . ':E' . $summaryRow);
$dailySheet->getStyle('D' . $summaryRow)->getNumberFormat()->setFormatCode('#,##0.00');

$summaryRow++;
$dailySheet->setCellValue('C' . $summaryRow, 'Total');
$dailySheet->setCellValue('D' . $summaryRow, ($totalCash + $totalChecks) ?: 0);
$dailySheet->mergeCells('D' . $summaryRow . ':E' . $summaryRow);
$dailySheet->getStyle('D' . $summaryRow)->getNumberFormat()->setFormatCode('#,##0.00');

$summaryRow++;
$dailySheet->setCellValue('A' . $summaryRow, 'Less Remittance/Deposit to Cashier');
$dailySheet->mergeCells('A' . $summaryRow . ':C' . $summaryRow);

$summaryRow++;
$dailySheet->setCellValue('C' . $summaryRow, 'Treasurer/Depository Bank  ___________________');
$dailySheet->mergeCells('C' . $summaryRow . ':F' . $summaryRow);

$summaryRow++;
$dailySheet->setCellValue('C' . $summaryRow, 'BALANCE ___________________');
$dailySheet->mergeCells('C' . $summaryRow . ':F' . $summaryRow);

$summaryEndRow = $summaryRow;

// Apply borders to summary box
$dailySheet->getStyle('A' . $summaryHeaderRow . ':G' . $summaryEndRow + 1)->applyFromArray([
    'borders' => [
        'outline' => [ // only the outer box
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000'],
        ],
    ],
]);





// --- CHECK LIST SECTION (RIGHT SIDE) ---
$checkHeaderRow = $summaryHeaderRow;
$dailySheet->setCellValue('H' . $checkHeaderRow, 'LIST OF CHECKS');
$dailySheet->mergeCells('H' . $checkHeaderRow . ':N' . $checkHeaderRow);
$dailySheet->getStyle('H' . $checkHeaderRow)->getFont()->setBold(true);

// Header for check columns
$checkHeaderRow2 = $checkHeaderRow + 1;
$dailySheet->setCellValue('H' . $checkHeaderRow2, 'Bank / Check Number');
$dailySheet->mergeCells('H' . $checkHeaderRow2 . ':I' . $checkHeaderRow2);

$dailySheet->setCellValue('J' . $checkHeaderRow2, 'Account Name');
$dailySheet->mergeCells('J' . $checkHeaderRow2 . ':L' . $checkHeaderRow2);

$dailySheet->setCellValue('M' . $checkHeaderRow2, 'Amount');
$dailySheet->mergeCells('M' . $checkHeaderRow2 . ':N' . $checkHeaderRow2);
$dailySheet->getStyle('H' . $checkHeaderRow2 . ':N' . $checkHeaderRow2)->applyFromArray($centerStyle);

// Populate check data
$checkRow = $checkHeaderRow2 + 1;

if ($checkResult->num_rows > 0) {
    $checkResult->data_seek(0);
    while ($check = $checkResult->fetch_assoc()) {
        $dailySheet->setCellValue('H' . $checkRow, $check['check_num'] ?: 'N/A');
        $dailySheet->mergeCells('H' . $checkRow . ':I' . $checkRow);

        $dailySheet->setCellValue('J' . $checkRow, $check['payorname'] ?: 'Unknown Payor');
        $dailySheet->mergeCells('J' . $checkRow . ':L' . $checkRow);

        $amount = is_numeric($check['amount']) ? $check['amount'] : 0.00;
        $dailySheet->setCellValue('M' . $checkRow, $amount);
        $dailySheet->mergeCells('M' . $checkRow . ':N' . $checkRow);
        $dailySheet->getStyle('M' . $checkRow)->getNumberFormat()->setFormatCode('#,##0.00');

        // ✅ Center align text horizontally and vertically for the entire row range
        $dailySheet->getStyle('H' . $checkRow . ':N' . $checkRow)
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        $checkRow++;
    }
} else {
    $dailySheet->setCellValue('H' . $checkRow, 'No check payments for today');
    $dailySheet->mergeCells('H' . $checkRow . ':N' . $checkRow);
    $dailySheet->getStyle('H' . $checkRow)->getFont()->setItalic(true);
    $dailySheet->getStyle('H' . $checkRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $checkRow += 8; // maintain box height
}

// Apply borders to check section
$checkEndRow = $checkRow - 1;
$dailySheet->getStyle('H' . $checkHeaderRow . ':N' . $checkEndRow)->applyFromArray([
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
]);

// Apply an outline border around the VERIFICATION AND ACKNOWLEDGEMENT section
$dailySheet->getStyle('H17:N21')->applyFromArray([
    'borders' => [
        'outline' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000'], // black border
        ],
    ],
]);







// --- CERTIFICATION (LEFT) AND VERIFICATION (RIGHT) ---
$afterSideBySideRow = max($summaryEndRow, $checkEndRow) + 2;

// Add a right-side outline border on column N (rows 8 to $afterSideBySideRow)
$dailySheet->getStyle('N8:N' . $afterSideBySideRow)->applyFromArray([
    'borders' => [
        'right' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000'], // black border
        ],
    ],
]);


// Left: Certification
$dailySheet->setCellValue('A' . $afterSideBySideRow, 'CERTIFICATION');
$dailySheet->mergeCells('A' . $afterSideBySideRow . ':G' . $afterSideBySideRow);
$dailySheet->setCellValue('A' . ($afterSideBySideRow + 1), 'I hereby certify that foregoing report of collections and');
$dailySheet->mergeCells('A' . ($afterSideBySideRow + 1) . ':G' . ($afterSideBySideRow + 1));
$dailySheet->setCellValue('A' . ($afterSideBySideRow + 2), 'deposits and accountability for accountable forms is true and correct');
$dailySheet->mergeCells('A' . ($afterSideBySideRow + 2) . ':G' . ($afterSideBySideRow + 2));

// Right: Verification
$dailySheet->setCellValue('H' . $afterSideBySideRow, 'VERIFICATION AND ACKNOWLEDGEMENT');
$dailySheet->mergeCells('H' . $afterSideBySideRow . ':N' . $afterSideBySideRow);
$dailySheet->setCellValue('H' . ($afterSideBySideRow + 1), 'I hereby certify that the foregoing report of collections');
$dailySheet->mergeCells('H' . ($afterSideBySideRow + 1) . ':N' . ($afterSideBySideRow + 1));
$dailySheet->setCellValue('H' . ($afterSideBySideRow + 2), 'has been verified and acknowledge receipt of');
$dailySheet->mergeCells('H' . ($afterSideBySideRow + 2) . ':N' . ($afterSideBySideRow + 2));
$dailySheet->setCellValue('H' . ($afterSideBySideRow + 3), '___________________________(P______________)');
$dailySheet->mergeCells('H' . ($afterSideBySideRow + 3) . ':N' . ($afterSideBySideRow + 3));


// --- SIGNATURES AND DATES ---
$signRow = $afterSideBySideRow + 5;

// Left side (Accountable Officer)
$dailySheet->setCellValue('A' . $signRow, 'LEO ANNE LUCILLO');
$dailySheet->mergeCells("A{$signRow}:C{$signRow}");
$dailySheet->setCellValue('D' . $signRow, '=TODAY()');
$dailySheet->mergeCells("D{$signRow}:G{$signRow}");

// Right side (Cashier/Treasurer)
$dailySheet->setCellValue('H' . $signRow, 'MA.THERESA L. LONTOK');
$dailySheet->mergeCells("H{$signRow}:K{$signRow}");
$dailySheet->setCellValue('L' . $signRow, '=TODAY()');
$dailySheet->mergeCells("L{$signRow}:N{$signRow}");

// Labels below signatures
$labelRow = $signRow + 1;
$dailySheet->setCellValue("A{$labelRow}", 'Name & Signature');
$dailySheet->mergeCells("A{$labelRow}:C{$labelRow}");
$dailySheet->setCellValue("D{$labelRow}", 'Date');
$dailySheet->mergeCells("D{$labelRow}:G{$labelRow}");

$dailySheet->setCellValue("H{$labelRow}", 'Name & Signature');
$dailySheet->mergeCells("H{$labelRow}:K{$labelRow}");
$dailySheet->setCellValue("L{$labelRow}", 'Date');
$dailySheet->mergeCells("L{$labelRow}:N{$labelRow}");

// Titles row
$titleRow = $labelRow + 1;
$dailySheet->setCellValue("A{$titleRow}", 'Accountable Officer');
$dailySheet->mergeCells("A{$titleRow}:C{$titleRow}");
$dailySheet->setCellValue("H{$titleRow}", 'Cashier/Treasurer');
$dailySheet->mergeCells("H{$titleRow}:K{$titleRow}");

// --- Styling ---
$signatureRange = "A{$signRow}:N{$titleRow}";

// Center alignment and vertical middle
$dailySheet->getStyle($signatureRange)->getAlignment()->setHorizontal('center')->setVertical('center');

// Bold names and titles
$dailySheet->getStyle("A{$signRow}:C{$signRow}")
    ->getFont()->setBold(true);
$dailySheet->getStyle("H{$signRow}:K{$signRow}")
    ->getFont()->setBold(true);
$dailySheet->getStyle("A{$titleRow}:C{$titleRow}")
    ->getFont()->setBold(true);
$dailySheet->getStyle("H{$titleRow}:K{$titleRow}")
    ->getFont()->setBold(true);



// Apply borders dynamically for certification and verification sections
$dailySheet->getStyle('A' . $afterSideBySideRow . ':G' . $titleRow)->applyFromArray([
    'borders' => [
        'outline' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000'],
        ],
    ],
]);

$dailySheet->getStyle('H' . $signRow . ':N' . $titleRow)->applyFromArray([
    'borders' => [
        'outline' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000'],
        ],
    ],
]);

// Center-align all signature sections horizontally
$dailySheet->getStyle('A' . $titleRow . ':N' . $titleRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);


// ... (rest of the code remains the same)






// Set headers for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Daily_Collector_Report_' . date('Y-m-d') . '.xlsx"');
header('Cache-Control: max-age=0');

// Create writer and output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;

function getNextReceiptNumber($currentNumber)
{
    if (preg_match('/(\d+)/', $currentNumber, $matches)) {
        $num = $matches[1];
        $nextNum = $num + 1;
        return str_replace($num, $nextNum, $currentNumber);
    }
    return $currentNumber . '-1';
}
