// Elements
const accountCodeModal = document.getElementById("account-code-modal");
const openAccountCodeBtn = document.getElementById("open-account-code-modal");
const closeAccountCodeBtn = document.getElementById("close-account-code-modal");
const addCodeForm = document.getElementById("add-code-form");
const codeFieldsContainer = document.getElementById("code-fields-container");
const addAnotherBtn = document.getElementById("add-another-code");

// ✅ Open modal
openAccountCodeBtn.addEventListener("click", () => {
    accountCodeModal.classList.remove("hidden");
});

// ✅ Close modal
closeAccountCodeBtn.addEventListener("click", () => {
    accountCodeModal.classList.add("hidden");
});

// ✅ Close when clicking outside
window.addEventListener("click", (e) => {
    if (e.target === accountCodeModal) {
        accountCodeModal.classList.add("hidden");
    }
});

// ✅ Add another input field
addAnotherBtn.addEventListener("click", () => {
    const fieldDiv = document.createElement("div");
    fieldDiv.className = "flex gap-2 code-field";

    fieldDiv.innerHTML = `
        <input type="text" name="new-code[]" placeholder="Enter account code" required
            class="w-full p-2 border border-gray-300 rounded-md bg-white text-gray-900">
        <button type="button" class="remove-code text-red-600 hover:text-red-800">
                <i class="ri-delete-bin-line text-xl"></i>
            </button>
    `;

    codeFieldsContainer.appendChild(fieldDiv);
});

codeFieldsContainer.addEventListener("click", (e) => {
    const removeBtn = e.target.closest(".remove-code");
    if (removeBtn) {
        const field = removeBtn.closest(".code-field");
        field.classList.add("opacity-0", "transition", "duration-200");
        setTimeout(() => field.remove(), 200);
    }

});




// ✅ Unified Account Code Loader
async function loadAccountCodes() {
    try {
        const res = await fetch("admin_actions/get_account_codes.php");
        const data = await res.json();

        if (data.success && Array.isArray(data.codes)) {
            // Filter dropdown
            const filterDropdown = document.getElementById("account-code-filter");
            filterDropdown.innerHTML = '<option value="">All Account Codes</option>';

            // Add modal dropdowns
            const addSelect = document.getElementById("account-code-select");
            const editSelect = document.getElementById("edit-account-code");
            addSelect.innerHTML = '<option value="">Choose Account Code</option>';
            editSelect.innerHTML = '<option value="">Choose Account Code</option>';

            // Append codes to all dropdowns
            data.codes.forEach(({ code }) => {
                const filterOption = document.createElement("option");
                filterOption.value = code;
                filterOption.textContent = code;
                filterDropdown.appendChild(filterOption);

                const addOption = document.createElement("option");
                addOption.value = code;
                addOption.textContent = code;
                addSelect.appendChild(addOption);

                const editOption = addOption.cloneNode(true);
                editSelect.appendChild(editOption);
            });
        }
    } catch (error) {
        console.error("Error loading account codes:", error);
    }
}

let isSubmitting = false;

// ✅ Handle multiple code submission
addCodeForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (isSubmitting) return;
    isSubmitting = true;

    const codeInputs = document.querySelectorAll('input[name="new-code[]"]');
    if (!codeInputs.length) {
        showToast("Please enter at least one account code.", "error");
        return;
    }

    const codes = Array.from(codeInputs)
        .map(input => input?.value?.trim() || "")
        .filter(code => code !== "");

    if (codes.length === 0) {
        showToast("Please enter at least one valid account code.", "error");
        return;
    }

    try {
        const res = await fetch("admin_actions/add_account_code.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ codes }),
        });

        const data = await res.json();

        if (data.success) {
            showToast("Account code(s) added successfully!", "success");

            // Reset only after successful add
            codeFieldsContainer.innerHTML = `
                <div class="flex gap-2 code-field">
                    <input type="text" name="new-code[]" placeholder="Enter account code" required
                        class="w-full p-2 border border-gray-300 rounded-md bg-white text-gray-900">
                   <button type="button" class="remove-code text-red-600 hover:text-red-800">
                <i class="ri-delete-bin-line text-xl"></i>
            </button>
                </div>
            `;

            accountCodeModal.classList.add("hidden");

            await loadAccountCodes();
            await loadAccountCodesTable();
        } else {
            showToast("Error: " + data.message, "error");
        }
    } catch (error) {
        console.error("Error adding account codes:", error);
        showToast("Something went wrong. Please try again.", "error");
    }
    isSubmitting = false;
});


// Load codes on page load
document.addEventListener("DOMContentLoaded", loadAccountCodes);

async function loadAccountCodesTable() {
    try {
        const res = await fetch("admin_actions/get_account_codes.php");
        const data = await res.json();

        if (data.success) {
            renderLGUPaginatedTable(data.codes, "account-codes-table-body", "account-codes-pagination", "accountCodes");
        }
    } catch (err) {
        console.error("Error loading account codes:", err);
    }
}


function renderAccountCodes(data) {
    const tableBody = document.getElementById("account-codes-table-body");
    tableBody.innerHTML = "";

    data.forEach(code => {
        const row = document.createElement("tr");
        row.className ="border-b border-gray-300 hover:bg-gray-50";

        row.innerHTML = `
            <td class="p-3 border border-gray-400">${code.code || ''}</td>
            <td class="p-3 border border-gray-400 flex items-center gap-2">

            <center>
                <!-- Edit Icon -->
                <button class="edit-code-btn text-blue-600 hover:text-blue-800" data-id="${code.id}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.232 5.232l3.536 3.536M16.768 4.768a2 2 0 112.828 2.828L7 20H4v-3L16.768 4.768z"/>
                    </svg>
                </button>

                <!-- Delete Icon -->
                <button class="delete-code-btn text-red-600 hover:text-red-800" data-id="${code.id}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M10 4V2h4v2"/>
                    </svg>
                </button>
            </center>
            </td>
        `;

        tableBody.appendChild(row);
    });
}




// Elements
const editCodeModal = document.getElementById("edit-code-modal");
const deleteCodeModal = document.getElementById("delete-code-modal");

const editCodeId = document.getElementById("edit-code-id");
const editCodeValue = document.getElementById("edit-code-value");

const deleteCodeId = document.getElementById("delete-code-id");

// Open Edit Modal
document.addEventListener("click", (e) => {
    const editBtn = e.target.closest(".edit-code-btn");
    if (editBtn) {
        const row = editBtn.closest("tr");
        const code = row.querySelector("td").textContent.trim();
        const id = editBtn.dataset.id;

        editCodeId.value = id;
        editCodeValue.value = code;

        editCodeModal.classList.remove("hidden");
    }
});

// Save Edit
document.getElementById("save-edit-code").addEventListener("click", async () => {
    const id = editCodeId.value;
    const newCode = editCodeValue.value.trim();

    if (!newCode) {
        showToast("Code cannot be empty!", "error");
        return;
    }

    const res = await fetch("admin_actions/update_account_code.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id, code: newCode }),
    });

    const data = await res.json();
    if (data.success) {
        showToast("Account code edited successfully!", "success");
        editCodeModal.classList.add("hidden");
        loadAccountCodesTable();
    } else {
        alert("Error: " + data.message);
    }
});

// Cancel Edit
document.getElementById("cancel-edit-code").addEventListener("click", () => {
    editCodeModal.classList.add("hidden");
});

// Open Delete Modal
document.addEventListener("click", (e) => {
    const deleteBtn = e.target.closest(".delete-code-btn");
    if (deleteBtn) {
        const id = deleteBtn.dataset.id;

        deleteCodeId.value = id;
        deleteCodeModal.classList.remove("hidden");
    }
});
// Confirm Delete
document.getElementById("confirm-delete-code").addEventListener("click", async () => {
    const id = deleteCodeId.value;

    const res = await fetch("admin_actions/delete_account_code.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id }),
    });

    const data = await res.json();
    if (data.success) {
        showToast("Account code deleted successfully!", "success");
        deleteCodeModal.classList.add("hidden");
        loadAccountCodesTable();
    } else {
        alert("Error: " + data.message);
    }
});

// Cancel Delete
document.getElementById("cancel-delete-code").addEventListener("click", () => {
    deleteCodeModal.classList.add("hidden");
});






