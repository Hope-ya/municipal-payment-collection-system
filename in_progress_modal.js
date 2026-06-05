function openInProgressModal(type, data) {
    const modal = document.getElementById("inProgressModal");
    const messageEl = document.getElementById("inProgressMessage");

    let message = "";
    switch(type) {
        case "monthly":
            message = `Insufficient data for the monthly report for ${data.month} ${data.year}`;
            break;
        case "quarterly":
            message = `Insufficient data for the quarterly report for ${data.quarter} of ${data.year}`;
            break;
        case "yearly":
            message = `Insufficient data for the yearly report for ${data.year}`;
            break;
        case "rrr":
            message = `Insufficient data for the RRR report for ${data.year}`;
            break;
    }

    messageEl.textContent = message;
    modal.classList.remove("hidden");
}

function closeInProgressModal() {
    document.getElementById("inProgressModal").classList.add("hidden");
}