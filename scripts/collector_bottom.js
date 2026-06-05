


// Modal functions
function openParticularsModal() {
    document.getElementById('particularsModal').classList.remove('hidden');
}

function closeParticularsModal() {
    document.getElementById('particularsModal').classList.add('hidden');
}

// Update total price for selected items
function updateTotal() {
    let total = 0;
    document.querySelectorAll('#particularsList input[type="checkbox"]:checked').forEach(checkbox => {
        total += parseFloat(checkbox.value);
    });
    document.getElementById('amount').value = total;
}

// Confirm selected particulars
function confirmParticulars() {
    updateTotal();
    closeParticularsModal();
}

// Filter Particulars
function filterParticulars() {
    let searchValue = document.getElementById('searchParticulars').value.toLowerCase();
    document.querySelectorAll('#particularsList li').forEach(item => {
        item.style.display = item.textContent.toLowerCase().includes(searchValue) ? 'block' : 'none';
    });
}

// Function to generate a unique reference number
function generateReferenceNumber() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const timestamp = Date.now().toString().slice(-5); // Ensures uniqueness
    const randomNum = Math.floor(1000 + Math.random() * 9000);

    return `REF-${year}${month}${day}-${timestamp}${randomNum}`;
}

// Function to set the reference number in the input field
function setNewReferenceNumber() {
    const referenceField = document.getElementById("referenceNumber");
    if (referenceField) {
        referenceField.value = generateReferenceNumber();
        console.log("New Reference Number:", referenceField.value); // Debugging log
    } else {
        console.error("Reference number field not found!");
    }
}

// Ensure the reference number is auto-generated when the page loads
document.addEventListener("DOMContentLoaded", () => {
    setTimeout(setNewReferenceNumber, 500); // Small delay to ensure DOM loads
});




// Function to process payment and update transaction table
// Function to process payment and update transaction table
function processPayment() {
    const payerName = document.getElementById('payerName').value.trim();
    const amount = document.getElementById('amount').value;
    const paymentType = document.getElementById('paymentType').value;
    const referenceNumber = document.getElementById('referenceNumber').value;
    const now = new Date();
    const date = now.toISOString().split('T')[0];
    const time = now.toLocaleTimeString();

    let selectedParticulars = [];
    document.querySelectorAll('#particularsList input[type="checkbox"]:checked').forEach(checkbox => {
        selectedParticulars.push(checkbox.nextElementSibling.textContent);
    });

    if (!payerName || !amount || !paymentType || !referenceNumber) {
        alert("Please fill out all fields.");
        return;
    }

    const transaction = { date, time, payerName, amount, paymentType, referenceNumber, particulars: selectedParticulars };
    let transactions = JSON.parse(localStorage.getItem('transactions')) || [];
    transactions.push(transaction);
    localStorage.setItem('transactions', JSON.stringify(transactions));

    updateTransactionTable();

    // Generate a new reference number
    setTimeout(setNewReferenceNumber, 500);

    // Reset form
    document.getElementById("paymentForm").reset();

    // Redirect to the Transactions section
    setTimeout(() => {
        showSection('transactions');
    }, 500);
}


// Update Transaction Table
// Update Transaction Table
// Function to update transaction table
function updateTransactionTable(filter = 'all') {
    const transactions = JSON.parse(localStorage.getItem('transactions')) || [];
    const filteredTransactions = transactions.filter(transaction => {
        const today = new Date();
        const transactionDate = new Date(transaction.date);
        if (filter === 'today') return transactionDate.toDateString() === today.toDateString();
        if (filter === 'week') return (today - transactionDate) / (1000 * 60 * 60 * 24) < 7;
        if (filter === 'month') return today.getMonth() === transactionDate.getMonth() && today.getFullYear() === transactionDate.getFullYear();
        if (filter === 'year') return today.getFullYear() === transactionDate.getFullYear();
        return true;
    });

    const tableBody = document.getElementById('transactionsTable');
    tableBody.innerHTML = filteredTransactions.map(transaction => `
<tr class="border-b transition-all hover:bg-gray-100 dark:hover:bg-gray-700">
<td class="border p-2">${transaction.date}</td>
<td class="border p-2">${transaction.time}</td>
<td class="border p-2">${transaction.payerName}</td>
<td class="border p-2">₱${transaction.amount}</td>
<td class="border p-2">${transaction.paymentType}</td>
<td class="border p-2">${transaction.referenceNumber}</td>
<td class="border p-2 flex space-x-2">
<button onclick="viewTransaction('${transaction.referenceNumber}')"
class="px-3 py-1 border border-blue-500 text-blue-500 rounded-md hover:bg-blue-500 hover:text-white transition-all flex items-center">
<i class="ri-eye-line mr-1"></i> View
</button>
<button onclick="printReceipt('${transaction.referenceNumber}')"
class="px-3 py-1 border border-green-500 text-green-500 rounded-md hover:bg-green-500 hover:text-white transition-all flex items-center">
<i class="ri-printer-line mr-1"></i> Print
</button>
</td>
</tr>
`).join('');
}

function viewTransaction(referenceNumber) {
    const transactions = JSON.parse(localStorage.getItem('transactions')) || [];
    const transaction = transactions.find(t => t.referenceNumber === referenceNumber);

    if (!transaction) {
        alert("Transaction not found.");
        return;
    }

    let particulars = transaction.particulars ? transaction.particulars.join(', ') : "None";

    alert(`Transaction Details:\n\n` +
        `Payer Name: ${transaction.payerName}\n` +
        `Amount: ₱${transaction.amount}\n` +
        `Payment Type: ${transaction.paymentType}\n` +
        `Reference Number: ${transaction.referenceNumber}\n` +
        `Particulars: ${particulars}\n` +
        `Date: ${transaction.date}`);
}
function viewTransaction(referenceNumber) {
    const transactions = JSON.parse(localStorage.getItem('transactions')) || [];
    const transaction = transactions.find(t => t.referenceNumber === referenceNumber);

    if (!transaction) {
        alert("Transaction not found.");
        return;
    }

    let particulars = transaction.particulars && transaction.particulars.length > 0
        ? transaction.particulars.map((p, index) => `${index + 1}. ${p}`).join('<br>')
        : "None";

    const modalContent = `
<div id="transactionModal" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50">
<div class="bg-white p-8 rounded-lg shadow-xl w-96">
<h2 class="text-2xl font-bold mb-4 text-gray-800">Transaction Details</h2>
<p class="text-gray-700"><strong>Payer Name:</strong> ${transaction.payerName}</p>
<p class="text-gray-700"><strong>Amount:</strong> ₱${transaction.amount}</p>
<p class="text-gray-700"><strong>Payment Type:</strong> ${transaction.paymentType}</p>
<p class="text-gray-700"><strong>Reference Number:</strong> ${transaction.referenceNumber}</p>
<p class="text-gray-700"><strong>Particulars:</strong><br>${particulars}</p>
<p class="text-gray-700"><strong>Date:</strong> ${transaction.date}</p>
<button onclick="closeTransactionModal()" class="mt-6 bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2 rounded-lg w-full">Close</button>
</div>
</div>`;

    document.body.insertAdjacentHTML('beforeend', modalContent);
}

function closeTransactionModal() {
    document.getElementById('transactionModal').remove();
}

// Function to print the transaction receipt
function printReceipt(referenceNumber) {
    const transactions = JSON.parse(localStorage.getItem('transactions')) || [];
    const transaction = transactions.find(t => t.referenceNumber === referenceNumber);

    if (!transaction) {
        alert("Transaction not found!");
        return;
    }

    // Get the current date and time
    const now = new Date();
    const formattedDate = now.toLocaleDateString(); // e.g., MM/DD/YYYY
    const formattedTime = now.toLocaleTimeString(); // e.g., HH:MM AM/PM

    // Assuming 'transaction.particulars' contains the dynamic list of items
    const particulars = transaction.particulars || []; // Ensure it defaults to an empty array if not available

    if (particulars.length === 0) {
        particulars.push({ name: 'No items', code: 'N/A', amount: 0 });
    }

    // Generate the particulars list dynamically
    let particularsList = particulars.map(item => `
<tr>
<td>${item.name || 'N/A'}</td>
<td>${item.code || 'N/A'}</td>
<td>₱${item.amount ? item.amount.toFixed(2) : '0.00'}</td>
</tr>
`).join('');

    // Calculate total amount
    let totalAmount = particulars.reduce((sum, item) => sum + (item.amount || 0), 0);

    // Generate the receipt layout HTML (focus on data layout, no style changes)
    const receiptHTML = `
<html>
<head>
<title>Official Receipt</title>
<style>
body { font-family: Arial, sans-serif; }
.receipt-container { width: 80%; margin: auto; padding: 20px; border: 2px solid black; }
.receipt-header { text-align: center; margin-bottom: 20px; }
.receipt-header img { width: 80px; }
.receipt-details { text-align: left; margin-bottom: 10px; }
.receipt-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
.receipt-table th, .receipt-table td { border: 1px solid black; padding: 8px; text-align: left; }
.receipt-footer { text-align: center; margin-top: 20px; }
.header-logos { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
</style>
</head>
<body onload="window.print(); setTimeout(() => { window.close(); }, 500);">
<div class="receipt-container">
<div class="receipt-header">
<div class="header-logos">
<img src="logo/republicofthephilippineslogo.png" alt="Left Logo">
<div>
<h2>OFFICIAL RECEIPT</h2>
<p><strong>Republic of the Philippines</strong></p>
<p><strong>OFFICE OF THE TREASURER</strong></p>
<p><strong>PROVINCE OF LAGUNA</strong></p>
</div>
<img src="logo/logo.png" alt="Right Logo">
</div>
</div>
<div class="receipt-details">
<p><strong>Date:</strong> ${formattedDate}</p>
<p><strong>Time:</strong> ${formattedTime}</p>
<p><strong>Agency:</strong> MTO - SML</p>
<p><strong>Customer Name:</strong> ${transaction.payerName || 'Unknown'}</p>
<p><strong>Reference Number:</strong> ${transaction.referenceNumber}</p>
<p><strong>Payment Type:</strong> ${transaction.paymentType || 'N/A'}</p>
</div>
<table class="receipt-table">
<thead>
<tr>
<th>Nature of Collection</th>
<th>Account Code</th>
<th>Amount</th>
</tr>
</thead>
<tbody>
${particularsList}
</tbody>
</table>
<p><strong>Total Amount Paid:</strong> ₱${totalAmount.toFixed(2)}</p>
<div class="receipt-footer">
<p>Received by: <strong>_______________________</strong></p>
<p><em>Collecting Officer</em></p>
</div>
</div>
</body>
</html>
`;

    // Open a new print window and print the receipt
    const printWindow = window.open('', '_blank');
    printWindow.document.write(receiptHTML);
    printWindow.document.close();
}



// Filter Transactions
function filterTransactions(filter) {
    updateTransactionTable(filter);
}

//particulars
function openParticularsModal() {
    const modal = document.getElementById("particularsModal");
    modal.classList.remove("opacity-0", "scale-90", "invisible");
    modal.classList.add("opacity-100", "scale-100");
}

function closeParticularsModal() {
    const modal = document.getElementById("particularsModal");
    modal.classList.remove("opacity-100", "scale-100");
    modal.classList.add("opacity-0", "scale-90", "invisible");
}


