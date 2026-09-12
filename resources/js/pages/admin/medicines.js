$(function () {
    loadMedicineTable();

    function loadMedicineTable() {
        $("#medicine_table").DataTable().destroy().clear();
        $("#medicine_table").DataTable({
            processing: true, serverSide: true,
            ajax: {
                url: "/api/fetch_medicine",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                dataSrc: 'medicines'
            },
            columns: [
                { data: null, render: function (data) { return `<button class="btn btn-sm btn-danger remove_drug" value="${data.medicine_refno}"><i class="fa-solid fa-trash"></i></button> <button class="btn btn-sm btn-primary edit_drug" value="${data.medicine_refno}"><i class="fa-solid fa-pen-to-square"></i></button>`; } },
                { data: "medicine_name" },
                { data: "philhealth_refno" }
            ],
            columnDefs: [
                { target: 0, width: "1%", orderable: false, searchable: false, className: 'text-center align-middle text-nowrap' },
                { targets: [1, 2], className: 'align-middle text-nowrap' },
                { target: 2, width: "1%" }
            ],
            language: { emptyTable: "No records yet." },
            order: [[1, 'asc']], select: { style: "single" }
        });
    }

    function openReferenceModal() {
        new bootstrap.Modal("#reference_modal").show();
        $("#drug_reference_table").DataTable().destroy().clear();
        $("#drug_reference_table").DataTable({
            processing: true, serverSide: true,
            ajax: { url: "/api/fetch_medicine_reference", type: "POST", headers: { "X-CSRF-TOKEN": $("meta[name=csrf-token]").attr("content") } },
            columns: [{ data: 'drug_code' }, { data: 'drug_dscr' }],
            language: { emptyTable: "No references yet." },
            order: [[1, 'asc']], select: { style: "single" }
        });
    }

    $("#ph_id").on("focus", openReferenceModal);
    $(document).on("click", "#import_refno", openReferenceModal);

    $("#clear_ph_id").on("click", function () { $("#ph_id").val(""); });

    $("#select_references").on("click", function () {
        const row = $("#drug_reference_table").DataTable().row({ selected: true }).data();
        if (!row) return;
        $("#ph_id").val(row["drug_code"]);
        if ($("#edrug_code").length) $("#edrug_code").val(row["drug_code"]);
        bootstrap.Modal.getInstance("#reference_modal").hide();
    });

    $("#admin_add_medicine_btn").on("click", function () {
        let form = document.getElementById("drug_form");
        if (!form.checkValidity()) return form.reportValidity();
        $.ajax({
            url: "/api/admin_add_medicine",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { med_name: $("#med_name").val(), ph_id: $("#ph_id").val() },
            success: function (response) {
                if (response.success) {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Record created!', showConfirmButton: false, timer: 1500 });
                    $("#drug_form")[0].reset();
                    loadMedicineTable();
                }
            }
        });
    });

    $(document).on("click", ".remove_drug", function () {
        Swal.fire({ title: "Confirmation", text: "Are you sure you want to remove this medicine?", icon: "warning", showCancelButton: true, confirmButtonText: "Confirm" })
            .then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "/api/admin_delete_medicine",
                        type: "POST",
                        headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                        data: { medicinerefno: $(this).val() },
                        success: function (response) {
                            if (response.success) {
                                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Record deleted!', showConfirmButton: false, timer: 1500 });
                                loadMedicineTable();
                            }
                        }
                    });
                }
            });
    });

    $(document).on("click", ".edit_drug", function () {
        let row = $("#medicine_table").DataTable().row({ selected: true }).data();
        if (!row) row = $("#medicine_table").DataTable().row($(this).closest('tr')).data();

        Swal.fire({
            title: "Edit Drug",
            html: `<div class="d-flex flex-column gap-2 text-start">
                <div><label class="form-label fw-bold">Medicine Name</label><input class="form-control" type="text" id="edrug_name" value="${row["medicine_name"] ?? ''}"></div>
                <div><label class="form-label fw-bold">Drug Code</label><div class="input-group"><input class="form-control" type="text" id="edrug_code" value="${row["philhealth_refno"] ?? ''}"><button class="btn btn-secondary" id="import_refno"><i class="fa-solid fa-list"></i></button></div></div>
            </div>`,
            confirmButtonText: "Update Details", showCancelButton: true,
            preConfirm: () => {
                const value = document.getElementById("edrug_name").value;
                if (!value) Swal.showValidationMessage("Drug name must not be empty.");
                return value;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/api/admin_edit_medicine",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: { medrefno: row["medicine_refno"], med_name: $("#edrug_name").val(), ph_id: $("#edrug_code").val() },
                    success: function () {
                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Record edited!', showConfirmButton: false, timer: 1500 });
                        loadMedicineTable();
                    }
                });
            }
        });
    });
});
