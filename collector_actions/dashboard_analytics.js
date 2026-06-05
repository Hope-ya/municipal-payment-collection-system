function closeSummModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.add('hidden');
}

function setActiveFilter(button, type) {
    const containerId = type === 'transactions' ? "transactionsFilters" : "revenueFilters";
    const container = document.getElementById(containerId);

    // Reset all buttons in this group
    container.querySelectorAll(".timeframe-btn").forEach(btn => {
        btn.classList.remove("ring-2", "ring-offset-2", "ring-black");
        btn.classList.add("opacity-70");
    });

    // Highlight active button
    button.classList.add("ring-2", "ring-offset-2", "ring-black");
    button.classList.remove("opacity-70");
}

function openSummModal(modalId) { 
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');

        // Auto-load daily and mark Daily button active
        if (modalId === "transactionsModal") {
            const dailyBtn = document.querySelector("#transactionsFilters .timeframe-btn:first-child");
            setActiveFilter(dailyBtn, 'transactions');
            loadTransactionsData('daily');
        } else if (modalId === "revenueModal") {
            const dailyBtn = document.querySelector("#revenueFilters .timeframe-btn:first-child");
            setActiveFilter(dailyBtn, 'revenue');
            loadRevenueData('daily');
        }
    }
}



function getTrendArrow(current, prev, isCurrency = false) {
    const diff = current - prev;
    let percent;

    if (prev === 0) {
        if (current === 0) {
            percent = "0%";
        } else {
            percent = "new"; // mark as new instead of fake 100%
        }
    } else {
        percent = ((diff / prev) * 100).toFixed(1) + "%";
    }

    if (diff > 0) {
        return `<span class="text-green-600 font-bold">
            ↑ ${isCurrency ? '₱' : ''}${Math.abs(diff).toLocaleString()} (${percent})
        </span>`;
    } else if (diff < 0) {
        return `<span class="text-red-600 font-bold">
            ↓ ${isCurrency ? '₱' : ''}${Math.abs(diff).toLocaleString()} (${percent})
        </span>`;
    } else {
        return `<span class="text-gray-500 font-bold">→ 0 (0%)</span>`;
    }
}

function getRowClass(diff) {
    if (diff > 0) {
        return "bg-green-50 dark:bg-green-900";
    } else if (diff < 0) {
        return "bg-red-50 dark:bg-red-900";
    } else {
        return "bg-gray-50 dark:bg-gray-800";
    }
}

// ---------------- Transactions ----------------
function loadTransactionsData(timeframe) {
    fetch(`collector_actions/fetch_transactions_summary.php?timeframe=${timeframe}`)
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById("transactionsList");
            container.innerHTML = `
                <table class="w-full text-left border-collapse rounded-lg overflow-hidden">
                    <thead>
                        <tr class="bg-gray-200 dark:bg-gray-700">
                            <th class="p-2">Time</th>
                            <th class="p-2">Transactions</th>
                            <th class="p-2">Trend</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            `;

            const tbody = container.querySelector("tbody");

            if (data.labels && data.counts) {
                data.labels.forEach((label, i) => {
                    const count = data.counts[i] ?? 0;
                    const prev = i > 0 ? (data.counts[i - 1] ?? 0) : 0;
                    const diff = count - prev;

                    const row = document.createElement("tr");
                    row.className = `${getRowClass(diff)} border-b dark:border-gray-600`;
                    row.innerHTML = `
                        <td class="p-2">${label}</td>
                        <td class="p-2">${count.toLocaleString()}</td>
                        <td class="p-2">${getTrendArrow(count, prev, false)}</td>
                    `;
                    tbody.appendChild(row);
                });
            }
        })
        .catch(err => console.error("Error loading transactions:", err));
}

// ---------------- Revenue ----------------
function loadRevenueData(timeframe) {
    fetch(`collector_actions/fetch_revenue_summary.php?timeframe=${timeframe}`)
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById("revenueList");
            container.innerHTML = `
                <table class="w-full text-left border-collapse rounded-lg overflow-hidden">
                    <thead>
                        <tr class="bg-gray-200 dark:bg-gray-700">
                            <th class="p-2">Time</th>
                            <th class="p-2">Revenue</th>
                            <th class="p-2">Trend</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            `;

            const tbody = container.querySelector("tbody");

            if (data.labels && data.amounts) {
                data.labels.forEach((label, i) => {
                    const amount = parseFloat(data.amounts[i] ?? 0);
                    const prev = i > 0 ? parseFloat(data.amounts[i - 1] ?? 0) : 0;
                    const diff = amount - prev;

                    const row = document.createElement("tr");
                    row.className = `${getRowClass(diff)} border-b dark:border-gray-600`;
                    row.innerHTML = `
                        <td class="p-2">${label}</td>
                        <td class="p-2">₱${amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                        <td class="p-2">${getTrendArrow(amount, prev, true)}</td>
                    `;
                    tbody.appendChild(row);
                });
            }
        })
        .catch(err => console.error("Error loading revenue:", err));
}
