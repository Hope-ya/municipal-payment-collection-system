function generateQuarterlyReport() {
    const modal = document.getElementById('quarterModal');
    const yearSelect = document.getElementById('qtryearSelect');

    // Show the quarterly modal 
    modal.classList.remove('hidden');

    // Populate the year dropdown if it's empty
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

function handleQuarterBackdropClick(event) {
    if (event.target.id === 'quarterModal') {
        closeQuarterModal();
    }
}

function closeQuarterModal() {
    document.getElementById('quarterModal').classList.add('hidden');
}

function inProgressQuarter() {
    const quarter = document.getElementById('qtrSelect').value;
    const year = document.getElementById('qtryearSelect').value;

    // ✅ Pass the actual quarter value instead of monthName
    openInProgressModal("quarterly", { quarter: quarter, year: year });
    showToast('Insufficient data for Quarterly Report.', 'error');
}



async function generateSelectedQuarterlyReport() {
    const quarter = document.getElementById('qtrSelect').value;
    const year = document.getElementById('qtryearSelect').value;
    const qtrPreviewModal = document.getElementById('qtrReportPreview');
    const reportContent = document.getElementById('qtrReportContent');

    // Hide the quarter selection modal
    closeQuarterModal();

    // Show loading in preview modal
    qtrPreviewModal.classList.remove('hidden');
    reportContent.innerHTML = `
        <div class="text-center py-8 text-gray-500">
            <div class="animate-spin inline-block w-6 h-6 border-2 border-blue-500 border-t-transparent rounded-full mb-2"></div>
            <p>Generating report preview...</p>
        </div>
    `;

    try {
        // First fetch the preview data
        const previewResponse = await fetch(`treasurer_actions/preview_quarterly_report.php?quarter=${quarter}&year=${year}`);
        if (!previewResponse.ok) throw new Error(`HTTP error! Status: ${previewResponse.status}`);

        const html = await previewResponse.text();
        reportContent.innerHTML = html;

         // ✅ Apply audit log when preview is generated
        fetch('audit_actions/record_audit_ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=Generated quarterly report for Quarter ${quarter}, ${year}`
        });

        // Add download button functionality
        const downloadBtn = document.getElementById('qtrDownloadBtn');
        if (downloadBtn) {
            downloadBtn.onclick = () => {
                // ✅ Record audit before download
                fetch('audit_actions/record_audit_ajax.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        action: `Downloaded quarterly report for Quarter ${quarter}, ${year}`,
                        status: 'success'
                    })
                });

                // Create form dynamically for POST request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'treasurer_actions/generate_quarterly_report.php';

                const quarterInput = document.createElement('input');
                quarterInput.type = 'hidden';
                quarterInput.name = 'quarter';
                quarterInput.value = quarter;
                form.appendChild(quarterInput);

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


function closeQuarterlyReportPreview() {
    // Hide the report preview modal
    const qtrPreviewModal = document.getElementById('qtrReportPreview');
    if (qtrPreviewModal) qtrPreviewModal.classList.add('hidden');

    // Show the quarter selection modal
    generateQuarterlyReport();
}