$(function () {
    loadCharges();

    function loadCharges() {
        $.ajax({
            url: "/api/fetch_charge_categories",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            success: function (response) {
                $("#charge_catg").empty().append($("<option>", { value: "", text: "Select category", disabled: true, selected: true }));
                response.categories.forEach(c => $("#charge_catg").append($("<option>", { value: c.categoryrefno, text: c.categoryname })));
            }
        });

        $("#charges_table").DataTable().destroy().clear();
        $("#charges_table").DataTable({
            ajax: {
                url: "/api/fetch_charges",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                dataSrc: "charges"
            },
            columns: [
                { data: null, render: function (data) { return `<div class="d-flex gap-1"><button class="btn btn-sm btn-primary edit_charge" value='${data.chargerefno}'><i class="fa-solid fa-pen-to-square"></i> Edit</button><button class="btn btn-sm btn-danger delete_charge" value='${data.chargerefno}'><i class="fa-solid fa-trash-can"></i> Delete</button></div>`; } },
                { data: 'charge_name' }, { data: 'categoryname' }, { data: 'charge_amount' }
            ],
            columnDefs: [
                { target: 0, width: '1%', orderable: false, className: 'text-nowrap text-truncate align-middle' },
                { target: 3, width: '10%', className: 'text-nowrap text-end' }
            ],
            ordering: true, paging: true, searching: true, responsive: true,
        });
    }

    $("#create_charge_btn").on("click", function () {
        let form = document.getElementById("charges_form");
        if (!form.checkValidity()) return form.reportValidity();
        $.ajax({
            url: "/api/create_charge",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: $("#charges_form").serialize(),
            success: function (response) {
                if (response.success) {
                    Swal.fire({ title: "Success", text: "Charge successfully created.", icon: "success" });
                    loadCharges();
                }
            }
        });
    });

    $(document).on("click", ".edit_charge", function () {
        const chargeModal = new bootstrap.Modal("#edit_charge_modal");
        $.ajax({
            url: "/api/fetch_charge_categories",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            success: function (response) {
                $("#echarge_catg").empty().append($("<option>", { value: "", text: "Select category", disabled: true, selected: true }));
                response.categories.forEach(c => $("#echarge_catg").append($("<option>", { value: c.categoryrefno, text: c.categoryname })));
            }
        });
        $.ajax({
            url: "/api/fetch_specific_charges",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { chargerefno: $(this).val() },
            success: function (response) {
                $("#echarge_name").val(response.charges[0].charge_name);
                $("#echarge_amt").val(response.charges[0].charge_amount);
                $("#echarge_catg").val(response.charges[0].charge_category);
            }
        });
        $("#update_charge_btn").val($(this).val());
        chargeModal.show();
    });

    $("#update_charge_btn").on("click", function () {
        $.ajax({
            url: "/api/edit_charge",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { chargerefno: $(this).val(), charge_name: $("#echarge_name").val(), charge_amount: $("#echarge_amt").val(), charge_category: $("#echarge_catg").val() },
            success: function (response) {
                if (response.success) {
                    Swal.fire({ title: "Success", text: "Charge successfully updated.", icon: "success" });
                    bootstrap.Modal.getInstance(document.getElementById("edit_charge_modal")).hide();
                    loadCharges();
                }
            }
        });
    });

    $(document).on("click", ".delete_charge", function () {
        Swal.fire({ title: "Confirmation", text: "Are you sure you want to delete this charge?", icon: "warning", showCancelButton: true, confirmButtonText: "Confirm" })
            .then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "/api/delete_charge",
                        type: "POST",
                        headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                        data: { chargerefno: $(this).val() },
                        success: function () {
                            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Charge deleted!', showConfirmButton: false, timer: 1500 });
                            loadCharges();
                        }
                    });
                }
            });
    });
});
