// Open modal and populate fields
function editTransaction(id, oldPayorName, pgl_no) {
    document.getElementById("editTransactionId").value = id;
    document.getElementById("oldPayorName").value = oldPayorName;
    document.getElementById("editPGLNo").value = pgl_no;

    // Set the input field value
    document.getElementById("editPayorName").value = oldPayorName;

    // Show modal
    document.getElementById("editModal").classList.remove("hidden");
}

// Close modal
function closeEditModal() {
    document.getElementById("editModal").classList.add("hidden");
}

// Save updated payor name + record audit log
function saveEditedPayorName() {
    let id = document.getElementById("editTransactionId").value;
    let oldName = document.getElementById("oldPayorName").value;
    let newName = document.getElementById("editPayorName").value;
    let pgl_no = document.getElementById("editPGLNo").value;

    // STEP 1 — Update payor name
    fetch("collector_actions/update_payorname.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id: id, payorname: newName })
    })
    .then(res => res.json())
    .then(data => {
        // STEP 2 — Construct audit log message
        let auditAction;

        if (data.status === "success") {
            auditAction = `Edited payor name "${oldName}" to "${newName}" for transaction ${pgl_no}`;
            showToast("Payor name edited successfully.", "success");
        } else {
            auditAction = `Failed attempt to edit payor name "${oldName}" to "${newName}" for transaction ${pgl_no}`;
            showToast("Editing payor name failed.", "error");
        }

        // STEP 3 — Send audit record
        return fetch("audit_actions/record_audit_ajax.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                action: auditAction,
                status: data.status
            })
        });
    })
    .then(auditResponse => auditResponse.json())
    .then(() => {
        closeEditModal();
        location.reload();
    })
    .catch(err => {
        console.error(err);
       
    });
}

// Open Cancel Confirmation Modal
function cancelTransaction(id, pgl_no) {
    document.getElementById("cancelTransactionId").value = id;
    // store pgl_no if you want to record audit logs
    document.getElementById("cancelTransactionPGL").value = pgl_no;
    document.getElementById("cancelModal").classList.remove("hidden");
}

// Close cancel modal
function closeCancelModal() {
    document.getElementById("cancelModal").classList.add("hidden");
}

function confirmCancelTransaction() {
    let id = document.getElementById("cancelTransactionId").value;
    let pgl_no = document.getElementById("cancelTransactionPGL").value;

    fetch("collector_actions/cancel_transaction.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id: id })
    })
    .then(res => res.json())
    .then(data => {

        if (data.status === "success") {
            showToast("Transaction cancelled successfully.", "success");

            // ✅ Must return this fetch!
            return fetch("audit_actions/record_audit_ajax.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    action: `Cancelled transaction ${pgl_no}`,
                    status: "success"
                })
            });
        } else {
            showToast("Failed to cancel transaction.", "error");
            return Promise.resolve(); // prevents chaining issues
        }

    })
    .then(() => {
        // ✅ Reload ONLY after audit logging finishes
        closeCancelModal();
        location.reload();
    })
    .catch(err => {
        console.error(err);
    });
}
