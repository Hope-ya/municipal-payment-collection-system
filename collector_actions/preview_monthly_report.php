<?php
session_start();
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n') - 1;
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$startDate = "$year-" . str_pad($month + 1, 2, '0', STR_PAD_LEFT) . "-01";
$endDate = date("Y-m-t", strtotime($startDate));
$dateRange = date("F 1-t, Y", strtotime($startDate));

include '../backend/db_config_notpdo.php';
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$collectorEmail = $_SESSION['email'];
$collectorQuery = "SELECT firstname, lastname FROM santamaria_employees WHERE email = '$collectorEmail'";
$collectorResult = $conn->query($collectorQuery);
$collector = $collectorResult->fetch_assoc();
$collectorName = $collector['firstname'] . ' ' . $collector['lastname'];

// Get all days with transactions in the month
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
$allTxQuery = "SELECT pgl_no, DATE(date_remitted) AS transaction_date
               FROM transactions
               WHERE collector_email = '$collectorEmail'
               ORDER BY date_remitted ASC, pgl_no ASC";

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
uasort($allBatches, function($a, $b) use ($batchFirstUsage) {
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

   $previousTransactionsQuery = "SELECT COUNT(*) AS used_count
                              FROM transactions
                              WHERE collector_email = '$collectorEmail'
                              AND pgl_no BETWEEN '$serialStart' AND '$serialEnd'
                              AND DATE(date_remitted) <= '$previousMonthEnd'";

    $previousResult = $conn->query($previousTransactionsQuery);
    $previousData = $previousResult->fetch_assoc();
    $usedCount = $previousData['used_count'];

   $lastUsedQuery = "SELECT pgl_no 
                  FROM transactions 
                  WHERE collector_email = '$collectorEmail'
                  AND pgl_no BETWEEN '$serialStart' AND '$serialEnd'
                  AND DATE(date_remitted) <= '$previousMonthEnd'
                  ORDER BY pgl_no DESC 
                  LIMIT 1";

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
$allTransactionsQuery = "SELECT pgl_no, amount, DATE(date_remitted) as transaction_date
                        FROM transactions
                        WHERE collector_email = '$collectorEmail'
                        AND DATE(date_remitted) BETWEEN '$startDate' AND '$endDate'
                        ORDER BY transaction_date, pgl_no";

$allTransactionsResult = $conn->query($allTransactionsQuery);
$allTransactions = [];
while ($tx = $allTransactionsResult->fetch_assoc()) {
    $allTransactions[$tx['transaction_date']][] = $tx;
}

// Fetch org settings
$orgQuery = $conn->query("SELECT org_name, org_logo FROM organization_info WHERE id = 1 LIMIT 1");
$org = $orgQuery->fetch_assoc();
$orgName = $org['org_name'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Accountability Report Preview</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white p-5">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-5">
            <div class="text-lg font-bold">Republic of the Philippines</div>
            <div class="text-lg font-bold">Province of Laguna</div>
            <div class="text-lg font-bold"><?php echo $orgName; ?></div>
            <div class="text-lg font-bold">ACCOUNTABILITY FOR ACCOUNTABLE FORMS / MONTHLY REPORT</div>
            <div class="text-base">For <?php echo $dateRange; ?></div>
            <div class="text-left font-bold my-4">Collector: <?php echo $collectorName; ?></div>
        </div>

        <table class="w-full border-collapse border border-gray-800 mb-8 text-xs">
            <thead>
                <tr class="bg-gray-100 font-bold text-center">
                    <th class="border border-gray-800 p-2">Date</th>
                    <th class="border border-gray-800 p-2">Name of Forms</th>
                    <th class="border border-gray-800 p-2" colspan="3">Beginning Balance</th>
                    <th class="border border-gray-800 p-2" colspan="3">Issued</th>
                    <th class="border border-gray-800 p-2">Amount</th>
                    <th class="border border-gray-800 p-2" colspan="3">Ending Balance</th>
                </tr>
                <tr class="bg-gray-100 font-bold text-center">
                    <th class="border border-gray-800 p-2"></th>
                    <th class="border border-gray-800 p-2"></th>
                    <th class="border border-gray-800 p-2">Qty</th>
                    <th class="border border-gray-800 p-2">From</th>
                    <th class="border border-gray-800 p-2">To</th>
                    <th class="border border-gray-800 p-2">Qty</th>
                    <th class="border border-gray-800 p-2">From</th>
                    <th class="border border-gray-800 p-2">To</th>
                    <th class="border border-gray-800 p-2"></th>
                    <th class="border border-gray-800 p-2">Qty</th>
                    <th class="border border-gray-800 p-2">From</th>
                    <th class="border border-gray-800 p-2">To</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalAmount = 0;
                $prevDayEnding = null;
                $previousDate = null;
                $row = 0;

                // Reset days result for processing
                $daysResult->data_seek(0);
                
                while ($day = $daysResult->fetch_assoc()) {
                    $currentDate = $day['transaction_date'];
                    $formattedDate = date("F j, Y", strtotime($currentDate));

                    if ($previousDate !== null && $previousDate != $currentDate) {
                        echo "<tr><td colspan='12' class='border border-gray-800 p-2 bg-gray-50'></td></tr>";
                        $row++;
                    }
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
                        $segmentAmount = array_sum(array_column($batchTransactions, 'amount'));
                        $totalAmount += $segmentAmount;

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
                        ?>
                        <tr class="<?php echo $row % 2 === 0 ? 'bg-white' : 'bg-gray-50'; ?>">
                            <td class="border border-gray-800 p-2"><?php echo !$dateDisplayed ? $formattedDate : ''; ?></td>
                            <td class="border border-gray-800 p-2">Official Receipt</td>
                            <td class="border border-gray-800 p-2 text-right"><?php echo $beginning['qty']; ?></td>
                            <td class="border border-gray-800 p-2"><?php echo $beginning['from']; ?></td>
                            <td class="border border-gray-800 p-2"><?php echo $beginning['to']; ?></td>
                            <td class="border border-gray-800 p-2 text-right"><?php echo $issued['qty']; ?></td>
                            <td class="border border-gray-800 p-2"><?php echo $issued['from']; ?></td>
                            <td class="border border-gray-800 p-2"><?php echo $issued['to']; ?></td>
                            <td class="border border-gray-800 p-2 text-right"><?php echo number_format($segmentAmount, 2); ?></td>
                            <td class="border border-gray-800 p-2 text-right"><?php echo $ending['qty'] > 0 ? $ending['qty'] : ''; ?></td>
                            <td class="border border-gray-800 p-2"><?php echo $ending['qty'] > 0 ? $ending['from'] : ''; ?></td>
                            <td class="border border-gray-800 p-2"><?php echo $ending['qty'] > 0 ? $ending['to'] : ''; ?></td>
                        </tr>
                        <?php
                        $dateDisplayed = true;
                        $row++;

                        $batchEndingBalances[$batchSegment['batch_id']] = [
                            'qty' => $ending['qty'],
                            'from' => $ending['from'],
                            'to' => $ending['to'],
                            'batch' => $allBatches[$batchSegment['batch_id']]
                        ];
                    }
                }
                ?>

                <!-- Total Amount Row -->
                <tr class="bg-gray-300 font-bold">
                    <td class="border border-gray-800 p-2" colspan="8">Total Amount</td>
                    <td class="border border-gray-800 p-2 text-right"><?php echo number_format($totalAmount, 2); ?></td>
                    <td class="border border-gray-800 p-2" colspan="3"></td>
                </tr>
            </tbody>
        </table>

        <div class="flex justify-between mt-12">
            <div class="text-center w-[45%]">
                <p>Prepared by:</p>
                <div class="border-b border-black w-4/5 mx-auto mb-1 pb-1"><?php echo $collectorName; ?></div>
                <p>Position: Collector</p>
            </div>
            <div class="text-center w-[45%]">
                <p>Date Covered:</p>
                <div class="border-b border-black w-4/5 mx-auto mb-1 pb-1"><?php echo $dateRange; ?></div>
                <p>Monthly Report</p>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();

// Helper function to get next receipt number in sequence
function getNextReceiptNumber($currentNumber)
{
    if (preg_match('/(\d+)/', $currentNumber, $matches)) {
        $num = $matches[1];
        $nextNum = $num + 1;
        return str_replace($num, $nextNum, $currentNumber);
    }
    return $currentNumber . '-1';
}