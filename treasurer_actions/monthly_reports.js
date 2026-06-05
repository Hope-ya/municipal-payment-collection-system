function generateMonthlyReport() {
    console.log('hoy');
    const modal = document.getElementById('monthModal');
    const monthSelect = document.getElementById('monthSelect');
    const yearSelect = document.getElementById('yearSelect');

    // Show the modal
    modal.classList.remove('hidden');

    // Preselect current month (1-based compatibility) 
    const currentMonth = new Date().getMonth();
    monthSelect.value = currentMonth;

    // Populate year dropdown only if not already populated
    if (yearSelect.options.length === 0) {
        const currentYear = new Date().getFullYear();
        for (let i = currentYear; i >= currentYear - 10; i--) {
            const option = document.createElement('option');
            option.value = i;
            option.textContent = i;
            yearSelect.appendChild(option);
        }
    }
}

function handleMonthBackdropClick(event) {
    if (event.target.id === 'monthModal') {
        closeMonthModal();
    }
}

function closeMonthModal() {
    document.getElementById('monthModal').classList.add('hidden');
}



async function generateSelectedMonthReport() {
    // When getting month from dropdown or current date:
    const month = parseInt(document.getElementById('monthSelect').value); // ensures 1–12

    const year = document.getElementById('yearSelect').value;
    const monthNames = ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"];
    const monthName = monthNames[month];

    const mthPreviewModal = document.getElementById('monthReportPreview');
    const reportContent = document.getElementById('monthReportContent');

    // Hide the month selection modal
    closeMonthModal();

    // Show loading in preview modal
    mthPreviewModal.classList.remove('hidden');
    reportContent.innerHTML = `
        <div class="text-center py-8 text-gray-500">
            <div class="animate-spin inline-block w-6 h-6 border-2 border-blue-500 border-t-transparent rounded-full mb-2"></div>
            <p>Generating report preview...</p>
        </div>
    `;

    try {
        // First fetch the preview data
        const previewResponse = await fetch(`treasurer_actions/preview_monthly_report.php?month=${month}&year=${year}`);
        if (!previewResponse.ok) throw new Error(`HTTP error! Status: ${previewResponse.status}`);

        const html = await previewResponse.text();
        reportContent.innerHTML = html;

        // ✅ Apply audit log when preview is generated
        fetch('audit_actions/record_audit_ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=Generated monthly report for ${monthName} ${year}`
        });

        // Add download button functionality
        const downloadBtn = document.getElementById('mthDownloadBtn');
        if (downloadBtn) {
            downloadBtn.onclick = () => {
                // ✅ Apply audit log when downloaded
                fetch('audit_actions/record_audit_ajax.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=Downloaded monthly report for ${monthName} ${year}`
                });

                // Create form dynamically for POST request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'treasurer_actions/generate_monthly_report.php';

                const monthInput = document.createElement('input');
                monthInput.type = 'hidden';
                monthInput.name = 'month';
                monthInput.value = monthName;
                form.appendChild(monthInput);

                const yearInput = document.createElement('input');
                yearInput.type = 'hidden';
                yearInput.name = 'year';
                yearInput.value = year;
                form.appendChild(yearInput);

                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            };

            downloadBtn.classList.remove('hidden');
        }
    } catch (error) {
        reportContent.innerHTML = `
            <div class="text-center py-8 text-red-500">
                <i class="ri-error-warning-line text-2xl mb-2"></i>
                <p>Error loading preview: ${error.message}</p>
            </div>
        `;
    }
}


function closeMonthlyReportPreview() {
    // Hide the report preview modal
    const mthPreviewModal = document.getElementById('monthReportPreview');
    if (mthPreviewModal) mthPreviewModal.classList.add('hidden');

    // Show the month selection modal
    generateMonthlyReport();
}

