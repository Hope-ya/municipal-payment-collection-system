let logoutTime = 15 * 60 * 1000;  // default 15 minutes
let inactivityTimer;


// Load user preferences on page load
document.addEventListener("DOMContentLoaded", () => {
    fetch("load_timeout_settings.php")
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // 🟢 Handle "never" or 0 properly
                
                logoutTime = (parseInt(data.logout_time) === 0) ? "never" : data.logout_time * 60 * 1000;

                // Update dropdowns (ensure match to "never" option)
                
                document.getElementById("logoutTimeInput").value = (parseInt(data.logout_time) === 0) ? "never" : data.logout_time;
            } else {
                showToastTimeout("⚠️ " + data.message, "error");
            }
            resetInactivityTimer(); // start timers
        })
        .catch(() => {
            showToastTimeout("⚠️ Failed to load timeout settings. Using defaults.", "error");
            resetInactivityTimer();
        });
});

function resetInactivityTimer() {
    clearTimeout(inactivityTimer);
    if (logoutTime !== "never") {
        inactivityTimer = setTimeout(autoLogout, logoutTime);
    }
}



function autoLogout() {
    fetch("logout.php", { method: "POST" })
        .then(() => {
            showToastTimeout(
                "You have been logged out due to inactivity.",
                "error",
                () => window.location.href = "index.php"
            );
        })
        .catch(() => window.location.href = "index.php");
}

// Reset timers on user activity
["click", "mousemove", "keydown", "scroll"].forEach(event => {
    window.addEventListener(event, resetInactivityTimer);
});

resetInactivityTimer();



// Toast message helper
function showToastTimeout(message, type = 'success', callback = null) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `p-4 mb-2 rounded-lg shadow-lg text-white transform transition-all duration-500 ease-in-out opacity-0 -translate-x-5 
        ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
    toast.style.whiteSpace = 'pre-line';
    toast.textContent = message;

    container.appendChild(toast);

    requestAnimationFrame(() => {
        toast.classList.remove('opacity-0', '-translate-x-5');
        toast.classList.add('opacity-100', 'translate-x-0');
    });

    const duration = type === 'success' ? 3000 : 5000;

    setTimeout(() => {
        toast.classList.remove('opacity-100', 'translate-x-0');
        toast.classList.add('opacity-0', '-translate-x-5');
        setTimeout(() => {
            toast.remove();
            if (callback) callback();
        }, 500);
    }, duration);
}





// 🟢 Save Timeout Settings (handles "never")
document.getElementById("saveTimeoutSettings").addEventListener("click", function () {
    

  
    let logoutVal = document.getElementById("logoutTimeInput").value;

    // Convert "never" to 0 before saving
    const logoutToSave = (logoutVal === "never") ? 0 : logoutVal;

    fetch("save_timeout_settings.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `logout_time=${logoutToSave}`
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToastTimeout("Timeout settings saved!", "success");
            } else {
                showToastTimeout("⚠️ " + data.message, "error");
            }
        })
        .catch(() => showToastTimeout("⚠️ Failed to save settings.", "error"));
});
