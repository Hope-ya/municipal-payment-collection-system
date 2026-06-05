// Open month modal function
function openMonthModal() {
    const modal = document.getElementById('monthModal');
    const monthSelect = document.getElementById('monthSelect');
    const yearSelect = document.getElementById('yearSelect');

    modal.classList.remove('hidden');

    // Preselect current month (1-based for PHP compatibility)
    const currentMonth = new Date().getMonth();
    monthSelect.value = currentMonth;

    // Populate year dropdown if not already populated
    if (yearSelect.options.length === 0) {
        const currentYear = new Date().getFullYear();
        for (let i = 0; i <= 10; i++) {
            const year = currentYear - i;
            const option = new Option(year, year);
            yearSelect.add(option);
        }
        yearSelect.value = currentYear;
    }

    // Reset form state
    document.getElementById('reportPreview')?.classList.add('hidden');
    document.getElementById('downloadBtn')?.classList.add('hidden');
}

// Close month modal function
function closeMonthModal() {
    document.getElementById('monthModal').classList.add('hidden');
}

// Helper array for month names
const monthNames = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
];

async function generateSelectedMonthReport() {
    const monthIndex = parseInt(document.getElementById('monthSelect').value);
    const year = document.getElementById('yearSelect').value;
    const monthName = monthNames[monthIndex]; // convert number to name
    const previewModal = document.getElementById('reportPreview');
    const reportContent = document.getElementById('reportContent');

    closeMonthModal();

    previewModal.classList.remove('hidden');
    reportContent.innerHTML = `
        <div class="text-center py-8 text-gray-500">
            <div class="animate-spin inline-block w-6 h-6 border-2 border-blue-500 border-t-transparent rounded-full mb-2"></div>
            <p>Generating report preview...</p>
        </div>
    `;

    try {
        const response = await fetch(`collector_actions/preview_monthly_report.php?preview=1&month=${monthIndex}&year=${year}`);
        if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);

        const html = await response.text();
        reportContent.innerHTML = html;

        // ✅ Record audit with month name
        fetch('audit_actions/record_audit_ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: `Generated monthly report for ${monthName} ${year}`,
                status: 'success'
            })
        });

        document.getElementById('downloadBtn')?.classList.remove('hidden');
    } catch (error) {
        reportContent.innerHTML = `
            <div class="text-center py-8 text-red-500">
                <i class="ri-error-warning-line text-2xl mb-2"></i>
                <p>Error loading preview: ${error.message}</p>
            </div>
        `;
    }
}

// Download full monthly report
function downloadMonthlyReport() {
    const monthIndex = parseInt(document.getElementById('monthSelect').value);
    const year = document.getElementById('yearSelect').value;
    const monthName = monthNames[monthIndex];

    // Record audit before download
    fetch('audit_actions/record_audit_ajax.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            action: `Downloaded monthly report for ${monthName} ${year}`,
            status: 'success'
        })
    }).finally(() => {
        window.location.href = `collector_actions/generate_monthly_report.php?month=${monthIndex}&year=${year}`;
    });
}


// Close modal if clicking outside modal content
document.addEventListener('click', function (e) {
    const modal = document.getElementById('monthModal');
    const modalContent = modal?.querySelector('.bg-white');
    if (modal && !modal.classList.contains('hidden') && !modalContent.contains(e.target)) {
        closeMonthModal();
    }
});

function closeReportPreview() {
    // Hide the report preview modal
    const previewModal = document.getElementById('reportPreview');
    if (previewModal) previewModal.classList.add('hidden');

    // Show the month selection modal
    openMonthModal();
}

