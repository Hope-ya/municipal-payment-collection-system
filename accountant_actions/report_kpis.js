function loadReportKPIs() {
    fetch('accountant_actions/get_report_kpis.php')
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                console.error(data.error);
                return;
            }

            // Total reports
            document.getElementById('total-reports').textContent =
                `${data.totalReports.today} / ${data.totalReports.week} / ${data.totalReports.month}`;

            // Most viewed type
            document.getElementById('most-viewed-type').textContent = data.mostViewedType;

            // Latest report
            const latestLink = document.getElementById('latest-report-link');
            latestLink.textContent = data.latestReport.name;
            latestLink.href = 'javascript:void(0)'; // Prevent page reload

            // Bind click to preview latest report
            latestLink.addEventListener('click', () => {
                if (data.latestReport && data.latestReport.id) {
                    previewReportDash(data.latestReport.id, data.latestReport.filePath);
                } else {
                    alert('No latest report available to preview.');
                }
            });
        })
        .catch(err => console.error('Error loading KPIs:', err));
}

document.addEventListener('DOMContentLoaded', loadReportKPIs);

document.getElementById('close-preview-btn-dash').addEventListener('click', () => {
    document.getElementById('preview-modal-dashboard').classList.add('hidden');
});

async function previewReportDash(reportId, filePath) {
    currentPreviewReportId = reportId;
    const previewContentDash = document.getElementById('preview-content-dashboard');

    // Show loading state
    previewContentDash.innerHTML = '<div class="flex justify-center items-center h-full">Loading preview...</div>';

    // Show modal
    document.getElementById('preview-modal-dashboard').classList.remove('hidden');



    // Load preview based on file type
    const fileExt = filePath.split('.').pop().toLowerCase();

    if (fileExt === 'pdf') {
        // For PDF files
        previewContentDash.innerHTML = `
            <embed src="${filePath}" type="application/pdf" width="100%" height="100%">
        `;
    } else if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExt)) {
        // For image files
        previewContentDash.innerHTML = `
            <img src="${filePath}" alt="Report Preview" class="max-w-full h-auto mx-auto">
        `;
    } else if (['xls', 'xlsx'].includes(fileExt)) {
        // For Excel files - use SheetJS to read and display
        try {
            const response = await fetch(filePath);
            const arrayBuffer = await response.arrayBuffer();
            const workbook = XLSX.read(arrayBuffer, {
                type: "array"
            });

            // Clear previous content
            previewContentDash.innerHTML = '';

            // Process each sheet
            workbook.SheetNames.forEach(sheetName => {
                if (sheetName.toLowerCase().includes('transaction') || sheetName.toLowerCase().includes('daily') || sheetName.toLowerCase().includes('monthly') ||
                    sheetName.toLowerCase().includes('quarterly') || sheetName.toLowerCase().includes('annual') || sheetName.toLowerCase().includes('engineering share') ||
                    sheetName.toLowerCase().includes('rrr')) {
                    const worksheet = workbook.Sheets[sheetName];

                    // Create container for each sheet
                    const sheetContainer = document.createElement('div');
                    sheetContainer.className = 'mb-8 max-w-6xl mx-auto';

                    // Add sheet title
                    const sheetTitle = document.createElement('h4');
                    sheetTitle.className = 'text-lg font-bold mb-2 text-center';
                    sheetTitle.textContent = sheetName;
                    sheetContainer.appendChild(sheetTitle);

                    // Convert sheet to HTML table with proper styling
                    const htmlString = XLSX.utils.sheet_to_html(worksheet, {
                        header: '',
                        footer: ''
                    });

                    // Create a div for the table and apply styling
                    const tableContainer = document.createElement('div');
                    tableContainer.className = 'bg-white p-5';
                    tableContainer.innerHTML = htmlString;

                    // Style the table
                    const table = tableContainer.querySelector('table');
                    if (table) {
                        table.className = 'w-full border-collapse border border-gray-800 mb-8 text-xs';
                        table.style.width = '100%';

                        // Style table headers
                        const thead = table.querySelector('thead');
                        if (thead) {
                            thead.className = 'bg-gray-100 font-bold text-center';

                            const thElements = thead.querySelectorAll('th');
                            thElements.forEach(th => {
                                th.className = 'border border-gray-800 p-2';
                            });
                        }

                        // Style table body
                        const tbody = table.querySelector('tbody');
                        if (tbody) {
                            // Style table rows and cells
                            const rows = tbody.querySelectorAll('tr');
                            rows.forEach((row, index) => {
                                // Alternate row colors
                                if (index % 2 === 0) {
                                    row.className = 'bg-white';
                                } else {
                                    row.className = 'bg-gray-100';
                                }

                                // Style cells
                                const cells = row.querySelectorAll('td');
                                cells.forEach((cell, cellIndex) => {
                                    cell.className = 'border border-gray-800 p-2';

                                    // Right-align for amount columns (assuming last column is amount)
                                    if (cellIndex === cells.length - 1) {
                                        cell.className += ' text-right';
                                    }
                                });
                            });

                            // Add special styling for summary rows like in the PHP template

                        }
                    }

                    sheetContainer.appendChild(tableContainer);



                    previewContentDash.appendChild(sheetContainer);
                }
            });

            // If no relevant sheets found
            if (previewContentDash.children.length === 0) {
                previewContentDash.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-full">
                        <p class="mb-4">No relevant sheets found in the Excel file.</p>
                        <button onclick="downloadReport(${reportId}, '${filePath}')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Download to View
                        </button>
                    </div>
                `;
            }

        } catch (error) {
            console.error('Error loading Excel file:', error);
            previewContentDash.innerHTML = `
                <div class="flex flex-col items-center justify-center h-full">
                    <p class="mb-4">Failed to load Excel preview.</p>
                    <button onclick="downloadReport(${reportId}, '${filePath}')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Download to View
                    </button>
                </div>
            `;
        }
    } else if (['doc', 'docx'].includes(fileExt)) {
        // For Word documents - cannot preview directly
        previewContentDash.innerHTML = `
            <div class="flex flex-col items-center justify-center h-full">
                <p class="mb-4">This document type cannot be previewed directly.</p>
                <button onclick="downloadReport(${reportId}, '${filePath}')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Download to View
                </button>
            </div>
        `;
    } else {
        // Fallback for other file types
        previewContentDash.innerHTML = `
            <div class="flex flex-col items-center justify-center h-full">
                <p class="mb-4">Preview not available for this file type.</p>
                <button onclick="downloadReport(${reportId}, '${filePath}')" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Download File
                </button>
            </div>
        `;
    }
}
