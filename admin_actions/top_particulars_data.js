const tabTopParticulars = document.getElementById('tabTopParticulars');
const tabCollectorOverview = document.getElementById('tabCollectorOverview');
const contentTopParticulars = document.getElementById('contentTopParticulars');
const contentCollectorOverview = document.getElementById('contentCollectorOverview');

function activateTab(tab) {
    if (tab === 'top') {
        contentTopParticulars.classList.remove('hidden');
        contentCollectorOverview.classList.add('hidden');
        tabTopParticulars.classList.add('text-blue-600', 'border-b-2', 'border-blue-600');
        tabCollectorOverview.classList.remove('text-blue-600', 'border-b-2', 'border-blue-600');
        tabCollectorOverview.classList.add('text-gray-500');
    } else {
        contentTopParticulars.classList.add('hidden');
        contentCollectorOverview.classList.remove('hidden');
        tabCollectorOverview.classList.add('text-blue-600', 'border-b-2', 'border-blue-600');
        tabTopParticulars.classList.remove('text-blue-600', 'border-b-2', 'border-blue-600');
        tabTopParticulars.classList.add('text-gray-500');
    }
}

tabTopParticulars.addEventListener('click', () => activateTab('top'));
tabCollectorOverview.addEventListener('click', () => activateTab('collector'));


let topParticularsChart; 

const typeSelect = document.getElementById("topParticularsType");
const dateSelect = document.getElementById("topParticularsDate");
const subFilterSelect = document.getElementById("topParticularsSubFilter");

// Populate sub-filter options dynamically
function updateSubFilter() {
    subFilterSelect.innerHTML = ""; // Clear old options

    const currentYear = new Date().getFullYear();
    const currentMonth = new Date().getMonth() + 1; // 1–12
    const currentQuarter = Math.ceil(currentMonth / 3);

    // Add "All" option first
    const allOpt = document.createElement("option");
    allOpt.value = "all";
    allOpt.textContent = "All";
    subFilterSelect.appendChild(allOpt);

    if (dateSelect.value === "monthly") {
        const months = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];
        months.forEach((m, i) => {
            const opt = document.createElement("option");
            opt.value = i + 1; // Month number
            opt.textContent = m;
            if (i + 1 === currentMonth) opt.selected = true;
            subFilterSelect.appendChild(opt);
        });

    } else if (dateSelect.value === "quarterly") {
        ["Q1", "Q2", "Q3", "Q4"].forEach((q, i) => {
            const opt = document.createElement("option");
            opt.value = i + 1; // Quarter number
            opt.textContent = q;
            if (i + 1 === currentQuarter) opt.selected = true;
            subFilterSelect.appendChild(opt);
        });

    } else if (dateSelect.value === "yearly") {
        for (let y = currentYear; y >= currentYear - 5; y--) {
            const opt = document.createElement("option");
            opt.value = y;
            opt.textContent = y;
            if (y === currentYear) opt.selected = true;
            subFilterSelect.appendChild(opt);
        }
    }
}


const viewSelect = document.getElementById("topParticularsView");
const chartContainer = document.getElementById("topParticularsChartContainer");
const listContainer = document.getElementById("topParticularsListContainer");
const tableBody = document.getElementById("topParticularsTableBody");

viewSelect.addEventListener("change", () => {
    if (viewSelect.value === "chart") {
        chartContainer.classList.remove("hidden");
        listContainer.classList.add("hidden");
    } else {
        chartContainer.classList.add("hidden");
        listContainer.classList.remove("hidden");
        populateTopParticularsList(); // populate table when switching to list
    }
});

// Populate list table
function populateTopParticularsList() {
    fetch(`admin_actions/top_particulars_data.php?type=${typeSelect.value}&date=${dateSelect.value}&sub=${subFilterSelect.value}`)
        .then(res => res.json())
        .then(data => {
            tableBody.innerHTML = "";
            data.forEach((item, index) => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td class="px-4 py-2">${index + 1}</td>
                    <td class="px-4 py-2">${item.particular}</td>
                    <td class="px-4 py-2">${item.quantity}</td>
                    <td class="px-4 py-2">₱${item.amount.toLocaleString()}</td>
                `;
                tableBody.appendChild(tr);
            });
        });
}

// Update loadTopParticulars to also update list when in list view
function loadTopParticulars() {
    const type = typeSelect.value;
    const date = dateSelect.value;
    const sub = subFilterSelect.value;

    fetch(`admin_actions/top_particulars_data.php?type=${type}&date=${date}&sub=${sub}`)
        .then(res => res.json())
        .then(data => {
            if (viewSelect.value === "chart") {
                const labels = data.map(item => item.particular);
                const values = type === "quantity" ? data.map(item => item.quantity) : data.map(item => item.amount);

                if (topParticularsChart) topParticularsChart.destroy();

                const ctx = document.getElementById("topParticularsChart").getContext("2d");
                topParticularsChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: type === "quantity" ? "Quantity" : "Amount (₱)",
                            data: values,
                            backgroundColor: '#3b82f6'
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: {
                                ticks: {
                                    callback: function (value) {
                                        return type === "amount" ? '₱' + value.toLocaleString() : value;
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                populateTopParticularsList();
            }
        });
}


// Event listeners
typeSelect.addEventListener("change", loadTopParticulars);
dateSelect.addEventListener("change", () => {
    updateSubFilter();
    loadTopParticulars();
});
subFilterSelect.addEventListener("change", loadTopParticulars);

// Init
updateSubFilter();
loadTopParticulars();