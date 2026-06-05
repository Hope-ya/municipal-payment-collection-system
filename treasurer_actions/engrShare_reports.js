function generateEngrShareReport() {
    console.log('Generating Engineering Share Report');
    const modal = document.getElementById('engrShareModal');
    const engrYearSelect = document.getElementById('engrYearOnlySelect');

    // Show modal
    modal.classList.remove('hidden');
 
    // Populate year dropdown if empty
    if (engrYearSelect.options.length === 0) {
        const currentYear = new Date().getFullYear();
        for (let i = currentYear; i >= currentYear - 10; i--) {
            const option = document.createElement('option');
            option.value = i;
            option.textContent = i;
            engrYearSelect.appendChild(option);
        }
    }
}

function closeEngrYearModal() {
    document.getElementById('engrShareModal').classList.add('hidden');
}

function handleEngrYearBackdropClick(event) {
    if (event.target.id === 'engrShareModal') {
        closeEngrYearModal();
    }
}

async function generateSelectedYearEngrShareReport() {
    const year = document.getElementById('engrYearOnlySelect').value;
    const engrPreviewModal = document.getElementById('engrReportPreview');
    const reportContent = document.getElementById('engrReportContent');

    // Hide the year selection modal
    closeEngrYearModal();

    // Show loading in preview modal
    engrPreviewModal.classList.remove('hidden');
    reportContent.innerHTML = `
        <div class="text-center py-8 text-gray-500">
            <div class="animate-spin inline-block w-6 h-6 border-2 border-blue-500 border-t-transparent rounded-full mb-2"></div>
            <p>Generating report preview...</p>
        </div>
    `;

    try {
        // First fetch the preview data
        const previewResponse = await fetch(`treasurer_actions/preview_engrShare_report.php?year=${year}`);
        if (!previewResponse.ok) throw new Error(`HTTP error! Status: ${previewResponse.status}`);
        
        const html = await previewResponse.text();
        reportContent.innerHTML = html;

        // 🔹 Record audit: Generated preview
        fetch('audit_actions/record_audit_ajax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: `Generated engineering share report for ${year}` })
        });

        // Add download button functionality
        const downloadBtn = document.getElementById('engrDownloadBtn');
        if (downloadBtn) {
            downloadBtn.onclick = () => {
                // Send request to PHP to generate and download the yearly report
                fetch('treasurer_actions/generate_engrShare_report.php', {
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
                    a.download = `Engineering_Share_Report_${year}.xlsx`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);

                    // 🔹 Record audit: Downloaded report
                    fetch('audit_actions/record_audit_ajax.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: `Downloaded engineering share report for ${year}` })
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


function closeEngrReportPreview() {
    // Hide the report preview modal
    const engrPreviewModal = document.getElementById('engrReportPreview');
    if (engrPreviewModal) engrPreviewModal.classList.add('hidden');

    // Show the year selection modal
    generateEngrShareReport();
}