document.addEventListener("DOMContentLoaded", function () {
    const totalTransactionsEl = document.getElementById("total-transactions");
    const totalRevenueEl = document.getElementById("total-revenue");

    async function fetchData(url) {
        const res = await fetch(url);
        return await res.json(); 
    }

    async function loadStats() {
        const stats = await fetchData("admin_actions/total_stats.php");
        totalTransactionsEl.textContent = stats.total_transactions ?? 0;
        totalRevenueEl.textContent = `₱${parseFloat(stats.total_revenue ?? 0).toLocaleString()}`;
    }

    //keep the revenueChartInstance

    let revenueChartInstance = null;

    function formatWeekLabel(weekLabel) {
        const [year, week] = weekLabel.split('-W');
        const weekNumber = parseInt(week);
        const firstDayOfYear = new Date(year, 0, 1);
        const daysOffset = (weekNumber - 1) * 7;
        const firstDayOfWeek = new Date(firstDayOfYear.getTime());
        firstDayOfWeek.setDate(firstDayOfYear.getDate() + daysOffset);
        const month = firstDayOfWeek.toLocaleString('default', { month: 'long' });
        const firstDayOfMonth = new Date(firstDayOfWeek.getFullYear(), firstDayOfWeek.getMonth(), 1);
        const nthWeek = Math.ceil((firstDayOfWeek.getDate() + firstDayOfMonth.getDay()) / 7);
        const suffix = nthWeek === 1 ? 'st' : nthWeek === 2 ? 'nd' : nthWeek === 3 ? 'rd' : 'th';
        return `${nthWeek}${suffix} Week (${month})`;
    }

    async function loadRevenueChart(filter = 'daily') {
        const response = await fetch(`treasurer_actions/dashboard_chart.php?filter=${filter}`);
        const data = await response.json();

        if (data.error) {
            alert(data.error);
            return;
        }

        let labels = data.monthly_data.map(item => item.label);
        const revenueData = data.monthly_data.map(item => parseFloat(item.total));

        if (filter === 'weekly') {
            labels = labels.map(formatWeekLabel);
        }

        const ctx = document.getElementById('revenueChart').getContext('2d');

        if (revenueChartInstance !== null) {
            revenueChartInstance.destroy();
        }

        revenueChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue',
                    data: revenueData,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => `₱${value.toLocaleString()}`
                        }
                    }
                }
            }
        });
    }

    // ✅ No nested DOMContentLoaded
    const filterSelect = document.getElementById('chartFilter');
    loadRevenueChart(filterSelect.value);

    filterSelect.addEventListener('change', () => {
        loadRevenueChart(filterSelect.value);
    });

    // Initial load
    loadStats();
});
