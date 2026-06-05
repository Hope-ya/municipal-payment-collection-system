function generateYearlyReport() {
    const modal = document.getElementById('yearModal');
    const yearSelect = document.getElementById('yearOnlySelect');

    // Show modal
    modal.classList.remove('hidden');
 
    // Populate year dropdown if empty
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

function closeYearModal() {
    document.getElementById('yearModal').classList.add('hidden');
}

function handleYearBackdropClick(event) {
    if (event.target.id === 'yearModal') {
        closeYearModal();
    }
}

function inProgressYear(){
    const year = document.getElementById('yearOnlySelect').value;
    openInProgressModal("yearly", { year: year });
    showToast('Insufficient data for Yearly Report.', 'error');
}


async function generateSelectedYearlyReport() {
    const year = document.getElementById('yearOnlySelect').value;
    const yrPreviewModal = document.getElementById('yrReportPreview');
    const reportContent = document.getElementById('yrReportContent');

    // Close the modal
    closeYearModal();

    // Show loading in preview modal
    yrPreviewModal.classList.remove('hidden');
    reportContent.innerHTML = `
        <div class="text-center py-8 text-gray-500">
            <div class="animate-spin inline-block w-6 h-6 border-2 border-blue-500 border-t-transparent rounded-full mb-2"></div>
            <p>Generating report preview...</p>
        </div>
    `;

    try {
        // First fetch the preview data
        const previewResponse = await fetch(`treasurer_actions/preview_yearly_report.php?year=${year}`);
        if (!previewResponse.ok) throw new Error(`HTTP error! Status: ${previewResponse.status}`);
        
        const html = await previewResponse.text();
        reportContent.innerHTML = html;

        // ✅ Apply record audit for yearly report preview
        fetch('audit_actions/record_audit_ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: `Generated yearly report for ${year}` })
        });

        // Add download button functionality
        const downloadBtn = document.getElementById('yrDownloadBtn');
        if (downloadBtn) {
            downloadBtn.onclick = () => {
                // Send request to PHP to generate and download the yearly report
                fetch('treasurer_actions/generate_yearly_report.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ year: year })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.blob();
                })
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `Yearly_Report_${year}.xlsx`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);

                    // ✅ Apply record audit for yearly report download
                    fetch('audit_actions/record_audit_ajax.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: `Downloaded yearly report for ${year}` })
                    });
                })
                .catch(error => {
                    console.error('Error generating yearly report:', error);
                    alert('Error generating report. Please try again.');
                });
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



function closeYearlyReportPreview() {
    // Hide the report preview modal
    const yrPreviewModal = document.getElementById('yrReportPreview');
    if (yrPreviewModal) yrPreviewModal.classList.add('hidden');

    // Show the year selection modal
    generateYearlyReport();
}