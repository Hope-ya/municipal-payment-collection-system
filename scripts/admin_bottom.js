//darkmode







// Function to Show Specific Section
function showSection(section) {
    document.querySelectorAll('main section').forEach(el => {
        el.classList.add('hidden');
        el.classList.remove('fade-in'); // Remove previous animation class
    });

    const selectedSection = document.getElementById(section + '-section');
    selectedSection.classList.remove('hidden');
    selectedSection.classList.add('fade-in'); // Add animation effect

    localStorage.setItem('activeSection', section);
}



// Show last active section or default
document.addEventListener("DOMContentLoaded", function () {
    const activeSection = localStorage.getItem('activeSection') || 'dashboard';
    showSection(activeSection);
});

// Navigation Links
document.getElementById('dashboard-link').addEventListener('click', () => showSection('dashboard'));
document.getElementById('employees-link').addEventListener('click', () => showSection('employees'));
document.getElementById('reports-link').addEventListener('click', () => showSection('reports'));
document.getElementById('lgu-payment-link').addEventListener('click', () => showSection('lgu-payment-references'));
document.getElementById('Audit-Logs-link').addEventListener('click', () => showSection('audit'));
document.getElementById('settings-link').addEventListener('click', () => showSection('settings'));









//dashboard
document.addEventListener("DOMContentLoaded", function () {
    const transactions = JSON.parse(localStorage.getItem('transactions')) || [];

    // Display total transactions
    document.getElementById('total-transactions').innerText = transactions.length;

    // Calculate total revenue
    const totalRevenue = transactions.reduce((sum, t) => sum + (parseFloat(t.amount) || 0), 0);
    document.getElementById('total-revenue').innerText = `₱${totalRevenue.toFixed(2)}`;

    // Generate Revenue Chart
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const labels = transactions.map(t => t.date);
    const data = transactions.map(t => parseFloat(t.amount) || 0);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Revenue Over Time',
                data: data,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderWidth: 2,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { title: { display: true, text: 'Date' } },
                y: { title: { display: true, text: 'Revenue (₱)' } }
            }
        }
    });
});



document.addEventListener("DOMContentLoaded", function () {
    // Profile Image Upload
    const profileImage = document.getElementById("profile-image");
    const uploadProfile = document.getElementById("upload-profile");

    uploadProfile.addEventListener("change", function (event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                profileImage.src = e.target.result;
                localStorage.setItem("profileImage", e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    // Load saved profile data
    if (localStorage.getItem("profileImage")) {
        profileImage.src = localStorage.getItem("profileImage");
    }
    const fields = ["name", "position", "employee-info", "email", "phone", "address"];
    fields.forEach(field => {
        if (localStorage.getItem(field)) {
            document.getElementById(field).textContent = localStorage.getItem(field);
        }
    });

    // Create Background Overlay (z-index: 999)
    const modalOverlay = document.createElement("div");
    modalOverlay.id = "modal-overlay";
    Object.assign(modalOverlay.style, {
        position: "fixed",
        top: "0",
        left: "0",
        width: "100%",
        height: "100%",
        background: "rgba(0, 0, 0, 0.5)", // Semi-transparent black
        display: "none",
        zIndex: "999", // Just behind the modal
    });

    // Create Edit Profile Modal (z-index: 1000)
    const editModal = document.createElement("div");
    editModal.id = "edit-modal";
    Object.assign(editModal.style, {
        position: "fixed",
        top: "50%",
        left: "50%",
        transform: "translate(-50%, -50%)",
        width: "400px",
        padding: "20px",
        background: "white",
        borderRadius: "8px",
        boxShadow: "0 4px 10px rgba(0, 0, 0, 0.2)",
        zIndex: "1000", // Ensures it's in front of everything
        display: "none",
    });

    editModal.innerHTML = `
    <h2 class="text-lg font-semibold mb-4">Edit Profile</h2>
    <label class="block">Name</label>
    <input id="edit-name" class="w-full p-2 border rounded mb-2" type="text">
    <label class="block">Position</label>
    <input id="edit-position" class="w-full p-2 border rounded mb-2" type="text">
    <label class="block">Employee Info</label>
    <input id="edit-employee-info" class="w-full p-2 border rounded mb-2" type="text">
    <label class="block">Email</label>
    <input id="edit-email" class="w-full p-2 border rounded mb-2" type="text">
    <label class="block">Phone</label>
    <input id="edit-phone" class="w-full p-2 border rounded mb-2" type="text">
    <label class="block">Address</label>
    <input id="edit-address" class="w-full p-2 border rounded mb-2" type="text">
    <div class="flex justify-end mt-4">
        <button id="save-edit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Save</button>
        <button id="close-edit" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-700 transition ml-2">Cancel</button>
    </div>
`;

    modalOverlay.appendChild(editModal);
    document.body.appendChild(modalOverlay);

    // Function to Show the Modal
    function openModal() {
        modalOverlay.style.display = "flex";
        modalOverlay.style.alignItems = "center";
        modalOverlay.style.justifyContent = "center";
        editModal.style.display = "block";
    }

    // Function to Close the Modal
    function closeModal() {
        modalOverlay.style.display = "none";
        editModal.style.display = "none";
    }

    // Open modal when clicking "Edit Profile"
    document.getElementById("edit-button").addEventListener("click", function () {
        fields.forEach(field => {
            document.getElementById("edit-" + field).value = document.getElementById(field).textContent;
        });
        openModal();
    });

    // Save changes and close modal
    document.getElementById("save-edit").addEventListener("click", function () {
        fields.forEach(field => {
            const newValue = document.getElementById("edit-" + field).value;
            document.getElementById(field).textContent = newValue;
            localStorage.setItem(field, newValue);
        });
        closeModal();
    });

    // Close modal when clicking "Cancel" or clicking outside
    document.getElementById("close-edit").addEventListener("click", closeModal);
    modalOverlay.addEventListener("click", function (event) {
        if (event.target === modalOverlay) {
            closeModal();
        }
    });

    // Dark Mode Toggle
    const darkModeSwitch = document.getElementById("darkModeSwitch");
    const darkModeToggle = darkModeSwitch.nextElementSibling;
    darkModeSwitch.addEventListener("change", function () {
        document.documentElement.classList.toggle("dark", darkModeSwitch.checked);
        localStorage.setItem("theme", darkModeSwitch.checked ? "dark" : "light");
        darkModeToggle.classList.toggle("justify-end", darkModeSwitch.checked);
    });

    // Apply Dark Mode on Load
    if (localStorage.getItem("theme") === "dark") {
        document.documentElement.classList.add("dark");
        darkModeSwitch.checked = true;
        darkModeToggle.classList.add("justify-end");
    }

    // Change Password
    document.getElementById("changePasswordBtn").addEventListener("click", function () {
        const currentPassword = document.getElementById("currentPassword").value;
        const newPassword = document.getElementById("newPassword").value;
        const confirmPassword = document.getElementById("confirmPassword").value;

        if (!currentPassword || !newPassword || !confirmPassword) {
            alert("Please fill in all fields.");
            return;
        }
        if (newPassword !== confirmPassword) {
            alert("New passwords do not match.");
            return;
        }
        alert("Password changed successfully!");
    });

    // Logout
    document.getElementById("logoutBtn").addEventListener("click", function () {
        alert("Logging out...");
        window.location.href = "login.html"; // Redirect to login page
    });
});
