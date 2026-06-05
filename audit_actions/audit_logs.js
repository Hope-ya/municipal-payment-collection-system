let allAuditLogs = [];
let currentAuditPage = 1;
let logsPerPage = calculateLogsPerPage();

function calculateLogsPerPage() {
    const rowHeight = 60; // adjust if needed
    const availableHeight = window.innerHeight - 350;
    const count = Math.floor(availableHeight / rowHeight);
    return Math.max(count, 5); // at least 5 logs per page
}

window.addEventListener("resize", () => {
    logsPerPage = calculateLogsPerPage();
    currentAuditPage = 1;
    displayAuditLogs(allAuditLogs);
});


async function loadAuditLogs() {
    const tbody = document.getElementById('auditLogsTable');
    if (!tbody) return;

    try {
        const res = await fetch('audit_actions/get_audit_logs.php');
        allAuditLogs = await res.json();
        currentAuditPage = 1; // reset to first page after refresh
        displayAuditLogs(allAuditLogs);
    } catch (error) {
        console.error("Failed to load audit logs:", error);
    }
}

function displayAuditLogs(logs) {
    const tbody = document.getElementById('auditLogsTable');
    tbody.innerHTML = '';

    const totalPages = Math.ceil(logs.length / logsPerPage);
    const startIndex = (currentAuditPage - 1) * logsPerPage;
    const endIndex = startIndex + logsPerPage;
    const paginatedLogs = logs.slice(startIndex, endIndex);

    paginatedLogs.forEach(log => {
        const tr = document.createElement('tr');
        tr.classList.add('bg-white-200', 'border', 'border-gray-300');
        tr.innerHTML = `
            <td class="p-3 border text-sm">${log.timestamp}</td>
            <td class="p-3 border text-sm">${log.firstname} ${log.lastname}</td>
            <td class="p-3 border text-sm">${log.user_role.charAt(0).toUpperCase() + log.user_role.slice(1)}</td>
            <td class="p-3 border text-sm">${log.action}</td>
            <td class="p-3 border text-sm capitalize">${log.status}</td>
        `;
        tbody.appendChild(tr);
    });

    addAuditPaginationControls(totalPages);
}

function addAuditPaginationControls(totalPages) {
    let paginationContainer = document.getElementById('auditPaginationControls');

    if (!paginationContainer) {
        const auditSection = document.getElementById('audit-section');
        if (!auditSection) return;

        paginationContainer = document.createElement('div');
        paginationContainer.id = 'auditPaginationControls';
        paginationContainer.className = 'w-full flex flex-col items-center mt-6';
        auditSection.appendChild(paginationContainer);
    } else {
        paginationContainer.innerHTML = '';
    }

    const controlsWrapper = document.createElement('div');
    controlsWrapper.className = 'flex justify-center items-center flex-wrap gap-1 mb-2';

    // First Page Button
    const firstButton = document.createElement('button');
    firstButton.innerHTML = '«';
    firstButton.title = 'First Page';
    firstButton.className = `px-3 py-1 rounded-md text-sm border ${currentAuditPage === 1
            ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
            : 'bg-gray-200 text-gray-700 hover:bg-blue-500 hover:text-white transition'
        }`;
    firstButton.disabled = currentAuditPage === 1;
    firstButton.onclick = () => {
        currentAuditPage = 1;
        displayAuditLogs(allAuditLogs);
    };
    controlsWrapper.appendChild(firstButton);

    // Previous Button
    const prevButton = document.createElement('button');
    prevButton.innerHTML = '←';
    prevButton.title = 'Previous Page';
    prevButton.className = `px-3 py-1 rounded-md text-sm border ${currentAuditPage === 1
            ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
            : 'bg-gray-200 text-gray-700 hover:bg-blue-500 hover:text-white transition'
        }`;
    prevButton.disabled = currentAuditPage === 1;
    prevButton.onclick = () => {
        if (currentAuditPage > 1) {
            currentAuditPage--;
            displayAuditLogs(allAuditLogs);
        }
    };
    controlsWrapper.appendChild(prevButton);

    // Dynamic Page Number Buttons (3 max)
    let startPage = Math.max(1, currentAuditPage - 1);
    let endPage = Math.min(totalPages, startPage + 2);

    if (endPage - startPage < 2 && startPage > 1) {
        startPage = Math.max(1, endPage - 2);
    }

    for (let i = startPage; i <= endPage; i++) {
        const pageButton = document.createElement('button');
        pageButton.textContent = i;
        pageButton.className = `px-3 py-1 rounded-md text-sm border ${currentAuditPage === i
                ? 'bg-blue-600 text-white border-blue-600'
                : 'bg-gray-200 text-gray-700 border-gray-300 hover:bg-blue-500 hover:text-white transition'
            }`;
        pageButton.onclick = () => {
            currentAuditPage = i;
            displayAuditLogs(allAuditLogs);
        };
        controlsWrapper.appendChild(pageButton);
    }

    // Next Button
    const nextButton = document.createElement('button');
    nextButton.innerHTML = '→';
    nextButton.title = 'Next Page';
    nextButton.className = `px-3 py-1 rounded-md text-sm border ${currentAuditPage === totalPages
            ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
            : 'bg-gray-200 text-gray-700 hover:bg-blue-500 hover:text-white transition'
        }`;
    nextButton.disabled = currentAuditPage === totalPages;
    nextButton.onclick = () => {
        if (currentAuditPage < totalPages) {
            currentAuditPage++;
            displayAuditLogs(allAuditLogs);
        }
    };
    controlsWrapper.appendChild(nextButton);

    // Last Page Button
    const lastButton = document.createElement('button');
    lastButton.innerHTML = '»';
    lastButton.title = 'Last Page';
    lastButton.className = `px-3 py-1 rounded-md text-sm border ${currentAuditPage === totalPages
            ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
            : 'bg-gray-200 text-gray-700 hover:bg-blue-500 hover:text-white transition'
        }`;
    lastButton.disabled = currentAuditPage === totalPages;
    lastButton.onclick = () => {
        currentAuditPage = totalPages;
        displayAuditLogs(allAuditLogs);
    };
    controlsWrapper.appendChild(lastButton);

    // Wrapper for controls + label
    const containerWrapper = document.createElement('div');
    containerWrapper.className = 'flex flex-col items-center';

    containerWrapper.appendChild(controlsWrapper);

    // Page indicator
    const pageLabel = document.createElement('div');
    pageLabel.className = 'text-sm text-gray-700 mt-2';
    pageLabel.textContent = `Page ${currentAuditPage} of ${totalPages}`;
    containerWrapper.appendChild(pageLabel);

    paginationContainer.appendChild(containerWrapper);
}

// Auto-refresh
if (document.getElementById('auditLogsTable')) {
    loadAuditLogs();
    setInterval(loadAuditLogs, 20000);
}


let filteredAuditLogs = [];

function applyAuditFilters() {
    let logs = [...allAuditLogs];

    const searchVal = document.getElementById('auditSearchInput').value.toLowerCase();
    const filterType = document.getElementById('auditQuickFilter').value;
    const positionFilter = document.getElementById('auditPositionFilter').value;

    // 🔎 Search by user
    if (searchVal) {
        logs = logs.filter(log =>
            `${log.firstname} ${log.lastname}`.toLowerCase().includes(searchVal)
        );
    }

    // 📌 Position filter
    if (positionFilter !== "all") {
        logs = logs.filter(log => log.user_role.toLowerCase() === positionFilter.toLowerCase());
    }

    // 📅 Date filters
    const today = new Date().toISOString().split('T')[0];

    if (filterType === "today") {
        logs = logs.filter(log => log.timestamp.startsWith(today));
    }

    if (filterType === "select-day") {
        const selectedDay = document.getElementById('auditSelectDay').value;
        if (selectedDay) {
            logs = logs.filter(log => log.timestamp.startsWith(selectedDay));
        }
    }

    if (filterType === "month") {
        const month = document.getElementById('auditSelectMonth').value;
        if (month !== "") {
            logs = logs.filter(log => new Date(log.timestamp).getMonth() == month);
        }
    }

    if (filterType === "year") {
        const year = document.getElementById('auditSelectYear').value;
        if (year) {
            logs = logs.filter(log => new Date(log.timestamp).getFullYear() == year);
        }
    }

    if (filterType === "date-range") {
        const start = document.getElementById('auditRangeStart').value;
        const end = document.getElementById('auditRangeEnd').value;
        if (start && end) {
            logs = logs.filter(log => {
                const d = new Date(log.timestamp).toISOString().split('T')[0];
                return d >= start && d <= end;
            });
        }
    }

    filteredAuditLogs = logs;
    currentAuditPage = 1;
    displayAuditLogs(filteredAuditLogs);
}

// 🎛 Handle filter dropdown show/hide
document.getElementById('auditQuickFilter').addEventListener('change', (e) => {
    document.getElementById('auditSelectDay').classList.add('hidden');
    document.getElementById('auditSelectMonth').classList.add('hidden');
    document.getElementById('auditSelectYear').classList.add('hidden');
    document.getElementById('auditRangeStart').classList.add('hidden');
    document.getElementById('auditRangeEnd').classList.add('hidden');

    if (e.target.value === "select-day") {
        document.getElementById('auditSelectDay').classList.remove('hidden');
    }
    if (e.target.value === "month") {
        document.getElementById('auditSelectMonth').classList.remove('hidden');
    }
    if (e.target.value === "year") {
        document.getElementById('auditSelectYear').classList.remove('hidden');
    }
    if (e.target.value === "date-range") {
        document.getElementById('auditRangeStart').classList.remove('hidden');
        document.getElementById('auditRangeEnd').classList.remove('hidden');
    }
    applyAuditFilters();
});

// 👂 Event listeners
document.getElementById('auditSearchInput').addEventListener('input', applyAuditFilters);
document.getElementById('auditSelectDay').addEventListener('change', applyAuditFilters);
document.getElementById('auditSelectMonth').addEventListener('change', applyAuditFilters);
document.getElementById('auditSelectYear').addEventListener('change', applyAuditFilters);
document.getElementById('auditRangeStart').addEventListener('change', applyAuditFilters);
document.getElementById('auditRangeEnd').addEventListener('change', applyAuditFilters);
document.getElementById('auditPositionFilter').addEventListener('change', applyAuditFilters);

// 🗓 Populate year dropdown (e.g., 2025 → 2015)
const yearSelect = document.getElementById('auditSelectYear');
const currentYear = new Date().getFullYear();
for (let y = currentYear; y >= 2015; y--) {
    const opt = document.createElement('option');
    opt.value = y;
    opt.textContent = y;
    yearSelect.appendChild(opt);
}

// Override loadAuditLogs to re-apply filters after fetch
async function loadAuditLogs() {
    const tbody = document.getElementById('auditLogsTable');
    if (!tbody) return;

    try {
        const res = await fetch('audit_actions/get_audit_logs.php');
        allAuditLogs = await res.json();
        applyAuditFilters(); // apply filters on fresh data
    } catch (error) {
        console.error("Failed to load audit logs:", error);
    }
}
