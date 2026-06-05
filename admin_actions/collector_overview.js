function toggleTimeframeInputs() {
    const timeframe = document.getElementById('collectorOverviewTimeframe').value;

    // hide all first
    document.getElementById('collectorOverviewYear').classList.add('hidden');
    document.getElementById('collectorOverviewMonth').classList.add('hidden');
    document.getElementById('collectorOverviewWeek').classList.add('hidden');
    document.getElementById('collectorOverviewQuarter').classList.add('hidden');

    // show relevant inputs
    if (['weekly','monthly','quarterly','yearly'].includes(timeframe)) {
        document.getElementById('collectorOverviewYear').classList.remove('hidden');
    }
    if (timeframe === 'weekly') {
        document.getElementById('collectorOverviewMonth').classList.remove('hidden');
        document.getElementById('collectorOverviewWeek').classList.remove('hidden');
    }
    if (timeframe === 'monthly') {
        document.getElementById('collectorOverviewMonth').classList.remove('hidden');
    }
    if (timeframe === 'quarterly') {
        document.getElementById('collectorOverviewQuarter').classList.remove('hidden');
    }
}

function loadCollectorOverview(type = 'amount') {
    const timeframe = document.getElementById('collectorOverviewTimeframe').value;
    const year = document.getElementById('collectorOverviewYear').value;
    const month = document.getElementById('collectorOverviewMonth').value;
    const week = document.getElementById('collectorOverviewWeek').value;
    const quarter = document.getElementById('collectorOverviewQuarter').value;

    const params = new URLSearchParams({
        type, timeframe, year, month, week, quarter
    });

    fetch(`admin_actions/get_collector_overview.php?${params.toString()}`)
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('collectorOverviewTableBody');
            tbody.innerHTML = '';
            data.forEach((item, index) => {
                tbody.innerHTML += `
                    <tr class="bg-white border-b">
                        <td class="px-4 py-2">${index + 1}</td>
                        <td class="px-4 py-2">${item.collector}</td>
                        <td class="px-4 py-2">${item.value}</td>
                    </tr>
                `;
            });
        });
}

// Filters change event
['collectorOverviewType', 'collectorOverviewTimeframe',
 'collectorOverviewYear', 'collectorOverviewMonth', 'collectorOverviewWeek', 'collectorOverviewQuarter'
].forEach(id => {
    document.getElementById(id).addEventListener('change', () => {
        toggleTimeframeInputs();
        loadCollectorOverview(
            document.getElementById('collectorOverviewType').value
        );
    });
});

// Initial Load
toggleTimeframeInputs();
loadCollectorOverview();
