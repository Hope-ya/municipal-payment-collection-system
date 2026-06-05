function generateRRRReport() {
    console.log('Generating RRR Report');
    const modal = document.getElementById('RRRModal');
    const yearSelect = document.getElementById('RRRYearOnlySelect');

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

function closeRRRModal() {
    document.getElementById('RRRModal').classList.add('hidden');
}

function handleRRRBackdropClick(event) {
    if (event.target.id === 'RRRModal') {
        closeRRRModal();
    }
}


function inProgressRRR(){
    const year = document.getElementById('RRRYearOnlySelect').value;
    openInProgressModal("rrr", { year: year });
    showToast('Insufficient data for RRR Report.', 'error');
}


async function generateSelectedYearRRRReport() {
    const year = document.getElementById('RRRYearOnlySelect').value;
    const rrrPreviewModal = document.getElementById('rrrReportPreview');
    const reportContent = document.getElementById('rrrReportContent');

    // Hide the month selection modal
    closeRRRModal();

    // Show loading in preview modal
    rrrPreviewModal.classList.remove('hidden');
    reportContent.innerHTML = `
        <div class="text-center py-8 text-gray-500">
            <div class="animate-spin inline-block w-6 h-6 border-2 border-blue-500 border-t-transparent rounded-full mb-2"></div>
            <p>Generating report preview...</p>
        </div>
    `;

    try {
        // First fetch the preview data
        const previewResponse = await fetch(`treasurer_actions/preview_rrr_report.php?year=${year}`);
        if (!previewResponse.ok) throw new Error(`HTTP error! Status: ${previewResponse.status}`);
        
        const html = await previewResponse.text();
        reportContent.innerHTML = html;

          // ✅ Record audit: generated report
        fetch('audit_actions/record_audit_ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: `Generated RRR report for ${year}` })
        });
        
        // Add download button functionality
        const downloadBtn = document.getElementById('rrrDownloadBtn');
        if (downloadBtn) {
            downloadBtn.onclick = () => {
                fetch('treasurer_actions/generate_rrr_report.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ year: year })
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.blob();
                })
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `RRR_Report_${year}.xlsx`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);

                    // 🔹 Record audit: Downloaded report
                    fetch('audit_actions/record_audit_ajax.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: `Downloaded RRR report for ${year}` })
                    });
                })
                .catch(error => {
                    console.error('Error generating report:', error);
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



function closeRRRReportPreview() {
    // Hide the report preview modal
    const rrrPreviewModal = document.getElementById('rrrReportPreview');
    if (rrrPreviewModal) rrrPreviewModal.classList.add('hidden');

    // Show the year selection modal
    generateRRRReport();
}