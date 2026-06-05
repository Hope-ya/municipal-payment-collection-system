// Elements
const categorizationModal = document.getElementById("categorization-modal");
const openCategorizationBtn = document.getElementById("open-categorization-modal");
const closeCategorizationBtn = document.getElementById("close-categorization-modal");
const addCategorizationForm = document.getElementById("add-categorization-form");


// Open modal
openCategorizationBtn.addEventListener("click", () => {
    categorizationModal.classList.remove("hidden");
    loadCategorizations();
});

// Close modal
closeCategorizationBtn.addEventListener("click", () => {
    categorizationModal.classList.add("hidden");
});

document.addEventListener("DOMContentLoaded", () => {
    const categorizationFieldsContainer = document.getElementById("categorizationFieldsContainer");
    const addCategorizationFieldBtn = document.getElementById("addCategorizationField");

    // Safety check
    if (!categorizationFieldsContainer || !addCategorizationFieldBtn) return;

    // Add new input field
    addCategorizationFieldBtn.addEventListener("click", (e) => {
        e.preventDefault(); // prevent any accidental form submission

        const fieldGroup = document.createElement("div");
        fieldGroup.className = "flex gap-2 items-center";
        fieldGroup.innerHTML = `
            <input type="text" name="categorization[]" placeholder="Enter categorization"
                class="flex-1 p-2 border border-gray-300 rounded-md bg-white text-gray-900" required>
            <button type="button" class="remove-field text-red-600 hover:text-red-800">
                <i class="ri-delete-bin-line text-xl"></i>
            </button>
        `;
        categorizationFieldsContainer.appendChild(fieldGroup);

        updateRemoveButtons();
    });

    // Remove input field (with fade-out animation)
    categorizationFieldsContainer.addEventListener("click", (e) => {
        const removeBtn = e.target.closest(".remove-field");
        if (removeBtn) {
            const field = removeBtn.closest(".flex");

            // Add fade-out transition
            field.classList.add("opacity-0", "transition", "duration-200");

            // Wait for animation to finish before removing
            setTimeout(() => {
                field.remove();
                updateRemoveButtons();
            }, 200); // Match duration-200 (200ms)
        }
    });


    function updateRemoveButtons() {
        const removeButtons = categorizationFieldsContainer.querySelectorAll(".remove-field");
        removeButtons.forEach(btn => btn.classList.remove("hidden"));
        if (removeButtons.length === 1) removeButtons[0].classList.add("hidden");
    }
});



// Close when clicking outside
window.addEventListener("click", (e) => {
    if (e.target === categorizationModal) {
        categorizationModal.classList.add("hidden");
    }
});

// Load categorizations
async function loadCategorizations() {
    try {
        const res = await fetch("admin_actions/get_categorizations.php");
        const data = await res.json();

        const addSelect = document.getElementById("categorization-select");
        const editSelect = document.getElementById("edit-categorization");

        if (addSelect) addSelect.innerHTML = '<option value="">Choose Categorization</option>';
        if (editSelect) editSelect.innerHTML = '<option value="">Choose Categorization</option>';

        if (data.success && data.categorizations.length > 0) {
            data.categorizations.forEach(cat => {
                const opt1 = document.createElement("option");
                opt1.value = cat.categorization;
                opt1.textContent = cat.categorization;
                if (addSelect) addSelect.appendChild(opt1);

                if (editSelect) {
                    const opt2 = opt1.cloneNode(true);
                    editSelect.appendChild(opt2);
                }
            });
        }
    } catch (error) {
        console.error("Error loading categorizations:", error);
    }
}

// Handle form submission (multiple categorizations)
addCategorizationForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const inputs = [...categorizationFieldsContainer.querySelectorAll('input[name="categorization[]"]')];
    const categorizations = inputs.map(i => i.value.trim()).filter(v => v !== "");

    if (categorizations.length === 0) {
        showToast("Please enter at least one categorization.", "error");
        return;
    }

    try {
        const res = await fetch("admin_actions/add_categorization.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ categorizations }),
        });
        const data = await res.json();

        if (data.success) {
            showToast(data.message, "success");
            addCategorizationForm.reset();
            categorizationFieldsContainer.innerHTML = `
                <div class="flex gap-2">
                    <input type="text" name="categorization[]" placeholder="Enter categorization"
                        class="flex-1 p-2 border border-gray-300 rounded-md bg-white text-gray-900" required>
                    <button type="button" class="remove-field text-red-600 hover:text-red-800 hidden">
                        <i class="ri-delete-bin-line text-xl"></i>
                    </button>
                </div>
            `;
            categorizationModal.classList.add("hidden");
            loadCategorizations();
        } else {
            alert("Error: " + data.message);
        }
    } catch (error) {
        console.error("Error adding categorizations:", error);
        showToast("Something went wrong. Please try again.", "error");
    }
});

document.addEventListener("DOMContentLoaded", () => {
    loadCategorizations();
});


// Load Categorizations
async function loadCategorizationsTable() {
    try {
        const res = await fetch("admin_actions/get_categorizations.php");
        const data = await res.json();

        if (data.success) {
            
            renderLGUPaginatedTable(data.categorizations, "categorization-table-body", "categorization-pagination", "categorization");
        }
    } catch (err) {
        console.error("Error loading categorizations:", err);
    }
}

function renderCategorizations(data) {
    const tableBody = document.getElementById("categorization-table-body");
    tableBody.innerHTML = "";

    data.forEach(cat => {
        const row = document.createElement("tr");
        row.className = "border-b border-gray-300 hover:bg-gray-50";
        row.innerHTML = `
            <td class="p-2">${cat.categorization || ''}</td>
            <td class="p-2">
                <button class="edit-categorization-btn px-2 py-1 bg-blue-100 text-blue-600 rounded-md text-xs" data-id="${cat.id}">Edit</button>
                <button class="delete-categorization-btn px-2 py-1 bg-red-100 text-red-600 rounded-md text-xs" data-id="${cat.id}">Delete</button>
            </td>
        `;
        tableBody.appendChild(row);
    });
    

    // Attach Listeners
    document.querySelectorAll(".edit-categorization-btn").forEach(btn => {
        btn.addEventListener("click", () => editCategorization(btn.dataset.id));
    });

    document.querySelectorAll(".delete-categorization-btn").forEach(btn => {
        btn.addEventListener("click", () => deleteCategorization(btn.dataset.id));
    });
}

// Elements
const editCategorizationModal = document.getElementById("edit-categorization-modal");
const deleteCategorizationModal = document.getElementById("delete-categorization-modal");

const editCategorizationId = document.getElementById("edit-categorization-id");
const editCategorizationValue = document.getElementById("edit-categorization-value");

const deleteCategorizationId = document.getElementById("delete-categorization-id");

// Open Edit Modal
document.addEventListener("click", (e) => {
    const editBtn = e.target.closest(".edit-categorization-btn");
    if (editBtn) {
        const row = editBtn.closest("tr");
        const cat = row.querySelector("td").textContent.trim();
        const id = editBtn.dataset.id;

        editCategorizationId.value = id;
        editCategorizationValue.value = cat;

        console.log("Rendered categorization:", cat, id);

        editCategorizationModal.classList.remove("hidden");
    }
});

// Save Edit
document.getElementById("save-edit-categorization").addEventListener("click", async () => {
    const id = editCategorizationId.value;
    const newCat = editCategorizationValue.value.trim();

    if (!newCat) {
        showToast("Categorization cannot be empty!", "error");
        return;
    }

    const res = await fetch("admin_actions/update_categorization.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id, categorization: newCat }),
    });

    const data = await res.json();
    if (data.success) {
        showToast("Categorization edited successfully!", "success");
        editCategorizationModal.classList.add("hidden");
        loadCategorizationsTable();
    } else {
        alert("Error: " + data.message);
    }
});

// Cancel Edit
document.getElementById("cancel-edit-categorization").addEventListener("click", () => {
    editCategorizationModal.classList.add("hidden");
});

// Open Delete Modal
document.addEventListener("click", (e) => {
    const deleteBtn = e.target.closest(".delete-categorization-btn");
    if (deleteBtn) {
        const id = e.target.dataset.id;
        deleteCategorizationId.value = id;
        deleteCategorizationModal.classList.remove("hidden");
    }
});

// Confirm Delete
document.getElementById("confirm-delete-categorization").addEventListener("click", async () => {
    const id = deleteCategorizationId.value;

    const res = await fetch("admin_actions/delete_categorization.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id }),
    });

    const data = await res.json();
    if (data.success) {
        showToast("Categorization deleted successfully!", "success");
        deleteCategorizationModal.classList.add("hidden");
        loadCategorizationsTable();
    } else {
        alert("Error: " + data.message);
    }
});

// Cancel Delete
document.getElementById("cancel-delete-categorization").addEventListener("click", () => {
    deleteCategorizationModal.classList.add("hidden");
});
