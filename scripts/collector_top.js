tailwind.config = {
    darkMode: 'class'
};


// dashboard




//settings
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

    // Edit Profile Modal
    const editModal = document.createElement("div");
    editModal.id = "edit-modal";
    editModal.classList.add("hidden", "fixed", "inset-0", "bg-black", "bg-opacity-50", "flex", "items-center", "justify-center");
    editModal.innerHTML = `
<div class="bg-white p-6 rounded-lg w-96 shadow-xl">
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
</div>
`;
    document.body.appendChild(editModal);

    document.getElementById("edit-button").addEventListener("click", function () {
        fields.forEach(field => {
            document.getElementById("edit-" + field).value = document.getElementById(field).textContent;
        });
        editModal.classList.remove("hidden");
    });

    document.getElementById("save-edit").addEventListener("click", function () {
        fields.forEach(field => {
            const newValue = document.getElementById("edit-" + field).value;
            document.getElementById(field).textContent = newValue;
            localStorage.setItem(field, newValue);
        });
        editModal.classList.add("hidden");
    });

    document.getElementById("close-edit").addEventListener("click", function () {
        editModal.classList.add("hidden");
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

