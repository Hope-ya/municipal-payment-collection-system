// Elements
const departmentModal = document.getElementById("department-modal");
const openDepartmentBtn = document.getElementById("open-department-modal");
const closeDepartmentBtn = document.getElementById("close-department-modal");
const addDepartmentForm = document.getElementById("add-department-form");
// Add multiple department fields dynamically
const departmentFieldsContainer = document.getElementById("department-fields-container");
const addAnotherDepartmentBtn = document.getElementById("add-another-department");

// Add multiple department fields dynamically
addAnotherDepartmentBtn.addEventListener("click", () => {
    const newField = document.createElement("div");
    newField.className = "flex gap-2 department-field items-center";
    newField.innerHTML = `
        <input type="text" name="new-department[]" placeholder="Enter department" required
            class="w-full p-2 border border-gray-300 rounded-md bg-white text-gray-900">
        <button type="button" class="remove-department text-red-600 hover:text-red-800">
                <i class="ri-delete-bin-line text-xl"></i>
            </button>
    `;
    departmentFieldsContainer.appendChild(newField);
    updateRemoveButtonsVisibility();
});

// Handle removing department fields with fade-out animation
departmentFieldsContainer.addEventListener("click", (e) => {
    const removeBtn = e.target.closest(".remove-department");
    if (removeBtn) {
        const field = removeBtn.closest(".department-field");

        // Add fade-out animation
        field.classList.add("opacity-0", "transition", "duration-200");

        // Remove after animation completes
        setTimeout(() => {
            field.remove();
            updateRemoveButtonsVisibility();
        }, 200); // Matches duration-200 (200ms)
    }
});


// ✅ Show remove button only when more than 1 field
function updateRemoveButtonsVisibility() {
    const removeButtons = departmentFieldsContainer.querySelectorAll(".remove-department");
    if (removeButtons.length > 1) {
        removeButtons.forEach(btn => btn.classList.remove("hidden"));
    } else {
        removeButtons.forEach(btn => btn.classList.add("hidden"));
    }
}

// ✅ Initial call to ensure correct visibility when modal loads
document.addEventListener("DOMContentLoaded", () => {
    updateRemoveButtonsVisibility();
});


// Open modal
openDepartmentBtn.addEventListener("click", () => {
    departmentModal.classList.remove("hidden");
    loadDepartments(); // refresh list every time modal opens
});

// Close modal
closeDepartmentBtn.addEventListener("click", () => {
    departmentModal.classList.add("hidden");
});

// Close when clicking outside
window.addEventListener("click", (e) => {
    if (e.target === departmentModal) {
        departmentModal.classList.add("hidden");
    }
});

// Load Departments (for filter, add modal, edit modal)
async function loadDepartments() {
    try {
        const res = await fetch("admin_actions/get_departments.php");
        const data = await res.json();

        const filterDropdown = document.getElementById("department-filter");
        const addSelect = document.getElementById("department-select"); // Add Payment Reference modal
        const editSelect = document.getElementById("edit-department"); // Edit Payment Reference modal

        // Reset defaults
        if (filterDropdown) filterDropdown.innerHTML = '<option value="">All Departments</option>';
        if (addSelect) addSelect.innerHTML = '<option value="">Choose Department</option>';
        if (editSelect) editSelect.innerHTML = '<option value="">Choose Department</option>';

        if (data.success && data.departments.length > 0) {
            data.departments.forEach(dep => {
                const depName = dep.department; // ✅ use `department` consistently

                // Filter dropdown
                if (filterDropdown) {
                    const opt1 = document.createElement("option");
                    opt1.value = depName;
                    opt1.textContent = depName;
                    filterDropdown.appendChild(opt1);
                }

                // Add modal dropdown
                if (addSelect) {
                    const opt2 = document.createElement("option");
                    opt2.value = depName;
                    opt2.textContent = depName;
                    addSelect.appendChild(opt2);
                }

                // Edit modal dropdown
                if (editSelect) {
                    const opt3 = document.createElement("option");
                    opt3.value = depName;
                    opt3.textContent = depName;
                    editSelect.appendChild(opt3);
                }
            });
        }
    } catch (error) {
        console.error("Error loading departments:", error);
    }
}

// Handle Add Department Form
addDepartmentForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    // ✅ Collect all input values
    const deptInputs = document.querySelectorAll('input[name="new-department[]"]');
    const departments = Array.from(deptInputs)
        .map(input => input.value.trim())
        .filter(v => v !== "");

    if (departments.length === 0) {
        showToast("Please enter at least one department.", "error");
        return;
    }

    try {
        const res = await fetch("admin_actions/add_department.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ departments }), // send as array
        });

        const data = await res.json();

        if (data.success) {
            showToast("Department(s) added successfully!", "success");
            addDepartmentForm.reset();
            departmentModal.classList.add("hidden");

            // 🔄 Refresh dropdowns everywhere
            await loadDepartments();
        } else {
            alert("Error: " + data.message);
        }
    } catch (error) {
        console.error("Error adding department:", error);
        showToast("Something went wrong. Please try again.", "error");
    }
});


// Auto-load on page ready
document.addEventListener("DOMContentLoaded", () => {
    loadDepartments();
});


// Load Departments
async function loadDepartmentsTable() {
    try {
        const res = await fetch("admin_actions/get_departments.php");
        const data = await res.json();

        if (data.success) {
            
            renderLGUPaginatedTable(data.departments, "departments-table-body", "departments-pagination", "departments");
        }
    } catch (err) {
        console.error("Error loading departments:", err);
    }
}

function renderDepartments(data) {
    const tableBody = document.getElementById("departments-table-body");
    tableBody.innerHTML = "";

    data.forEach(dep => {
        const row = document.createElement("tr");
        row.className = "border-b border-gray-300 hover:bg-gray-50";

        row.innerHTML = `
            <td class="p-2">${dep.department || ''}</td>
            <td class="p-2 flex items-center gap-2">

                <!-- Edit Icon -->
                <button class="edit-department-btn text-blue-600 hover:text-blue-800" data-id="${dep.id}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.232 5.232l3.536 3.536M16.768 4.768a2 2 0 112.828 2.828L7 20H4v-3L16.768 4.768z"/>
                    </svg>
                </button>

                <!-- Delete Icon -->
                <button class="delete-department-btn text-red-600 hover:text-red-800" data-id="${dep.id}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M10 4V2h4v2"/>
                    </svg>
                </button>

            </td>
        `;

        tableBody.appendChild(row);
    });
}


// Elements
const editDepartmentModal = document.getElementById("edit-department-modal");
const deleteDepartmentModal = document.getElementById("delete-department-modal");

const editDepartmentId = document.getElementById("edit-department-id");
const editDepartmentValue = document.getElementById("edit-department-value");

const deleteDepartmentId = document.getElementById("delete-department-id");

// Open Edit Modal
document.addEventListener("click", (e) => {
    const editBtn = e.target.closest(".edit-department-btn");
    if (editBtn) {
        const row = e.target.closest("tr");
        const dep = row.querySelector("td").textContent.trim();
        const id = e.target.dataset.id;

        editDepartmentId.value = id;
        editDepartmentValue.value = dep;

        editDepartmentModal.classList.remove("hidden");
    }
});

// Save Edit
document.getElementById("save-edit-department").addEventListener("click", async () => {
    const id = editDepartmentId.value;
    const newDep = editDepartmentValue.value.trim();

    if (!newDep) {
        showToast("Department cannot be empty!", "error");
        return;
    }

    const res = await fetch("admin_actions/update_department.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id, department: newDep }),
    });

    const data = await res.json();
    if (data.success) {
        showToast("Department edited successfully!", "success");
        editDepartmentModal.classList.add("hidden");
        loadDepartmentsTable();
    } else {
        alert("Error: " + data.message);
    }
});

// Cancel Edit
document.getElementById("cancel-edit-department").addEventListener("click", () => {
    editDepartmentModal.classList.add("hidden");
});

// Open Delete Modal
document.addEventListener("click", (e) => {
    const deleteBtn = e.target.closest(".delete-department-btn");
    if (deleteBtn) {
        const id = e.target.dataset.id;
        deleteDepartmentId.value = id;
        deleteDepartmentModal.classList.remove("hidden");
    }
});

// Confirm Delete
document.getElementById("confirm-delete-department").addEventListener("click", async () => {
    const id = deleteDepartmentId.value;

    const res = await fetch("admin_actions/delete_department.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id }),
    });

    const data = await res.json();
    if (data.success) {
        showToast("Department deleted successfully!", "success");
        deleteDepartmentModal.classList.add("hidden");
        loadDepartmentsTable();
    } else {
        alert("Error: " + data.message);
    }
});

// Cancel Delete
document.getElementById("cancel-delete-department").addEventListener("click", () => {
    deleteDepartmentModal.classList.add("hidden");
});



