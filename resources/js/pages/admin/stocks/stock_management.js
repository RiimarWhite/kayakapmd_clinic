$(function () {
    loadStocksTable();

    function loadStocksTable() {
        if ($.fn.DataTable.isDataTable("#stocks_table")) {
            $("#stocks_table").DataTable().clear().destroy();
        }

        $("#stocks_table").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "/api/fetch_stocks",
                type: "POST",
                headers: { 'X-CSRF-TOKEN': $("meta[name='csrf-token']").attr("content") },
            },
            columns: [
                {
                    data: 'prodcode',
                    render: function (data) {
                        return `
                            <button class="btn btn-sm btn-success edit_item" value="${data}"><i class="fa-solid fa-pen-to-square"></i></button>
                            <button class="btn btn-sm btn-danger delete_item" value="${data}"><i class="fa-solid fa-trash"></i></button>
                            <button class="btn btn-sm btn-primary"><i class="fa-solid fa-eye"></i></button>
                        `;
                    }
                },
                { data: 'prod_itemdscr' },
                { data: 'item_grouping' },
                { data: 'phic_reference_code', render: function (data) { return data != "" ? data : "<span class='text-secondary'>NONE</span>"; }},
                { data: 'price_regular' },
                { data: 'price_phic' },
                { data: 'price_hmo' },
                { data: 'price_others' },
            ],
            columnDefs: [
                { target: 0, width: '1%', className: 'text-center text-nowrap align-middle', orderable: false },
                { target: [2, 3], width: '1%' },
                { targets: [4, 5, 6, 7], width: '1%', className: 'align-middle text-nowrap', type: 'num' },
                { targets: [1, 2, 3], className: 'align-middle text-nowrap'}
            ],
            language: {
                emptyTable: 'No items to display yet.'
            },
            order: [[1, 'asc']]
        });
    }

    $("#add_item_modal").on("shown.bs.modal", function () {
        $("#add_item_form")[0].reset();

        if ($("#item_group").val() == "DRUGS AND MEDS") {
            $("#refcode").removeClass("d-none").addClass("d-block");
            $("#drug_fields").removeClass("d-none").addClass("d-flex");
        } else {
            $("#refcode").removeClass("d-block").addClass("d-none");
            $("#drug_fields").removeClass("d-flex").addClass("d-none");
        }
    });

    $("#item_group").on("change", function () {
        const drugFields = $("#drug_fields");
        const refCode = $("#refcode");
        const addFields = $("#additional_fields");

        if ($(this).val() == "DRUGS AND MEDS") {
            drugFields.removeClass("d-none").addClass("d-flex");
            showRefcode();
        } else if ($(this).val() == "DIAGNOSTIC") {
            drugFields.removeClass("d-flex").addClass("d-none");
            refCode.removeClass("d-none").addClass("d-block");
            // addFields.removeClass("d-none").addClass("d-flex");
            showRefcode();
        } else {
            drugFields.removeClass("d-flex").addClass("d-none");
            // addFields.removeClass("d-flex").addClass("d-none");
            showRefcode(false);
        }

        function showRefcode(show = true) {
            if (show) {
                refCode.removeClass("d-none").addClass("d-block");
            } else {
                refCode.removeClass("d-block").addClass("d-none");
            }
        }
    });

    $("#eitem_group").on("change", function () {
        const drugFields = $("#edrug_fields");
        const refCode = $("#erefcode");

        if ($(this).val() == "DRUGS AND MEDS") {
            drugFields.removeClass("d-none").addClass("d-flex");
            showRefcode();
        } else if ($(this).val() == "DIAGNOSTIC") {
            drugFields.removeClass("d-flex").addClass("d-none");
            refCode.removeClass("d-none").addClass("d-block");
            showRefcode();
        } else {
            drugFields.removeClass("d-flex").addClass("d-none");
            showRefcode(false);
        }

        function showRefcode(show = true) {
            if (show) {
                refCode.removeClass("d-none").addClass("d-block");
            } else {
                refCode.removeClass("d-block").addClass("d-none");
            }
        }
    });

    $("#drug_generic, #edrug_generic").autocomplete({
        source: function (request, response) {
            $.ajax({
                url: "/api/fetch_drugref",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: { term: request.term },
                success: function (data) {
                    response(data.generic);
                }
            });
        },
        minLength: 1,
        appendTo: $(this).closest('.modal'),
        select: function (event, ui) {
            $("#drug_generic").val(ui.item.label)
            $("#edrug_generic").val(ui.item.label)
            return false;
        }
    });

    $("#ref_code").on("change", function () {
        const generic = $("#drug_generic");
        const dosage = $("#drug_dosage");

        if ($(this).val() == "") {
            generic.val("");
            return;
        }

        $.ajax({
            url: "/api/fetch_drug_details",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { phic_reference_code: $(this).val(), generic: null },
            success: function (response) {
                if (response.success) {
                    generic.val(response.drug.generic_description);
                    dosage.val(response.drug.strength_description);
                }
            }
        });
    });

    $("#ref_code, #eref_code").autocomplete({
        source: function (request, response) {
            var input = $(this.element);
            var modal = input.closest('.modal');
            var group = modal.find('select[id$="item_group"]').val();

            if (group === "DIAGNOSTIC") {
                $.ajax({
                    url: "/api/fetch_diagnostic_reference",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: { term: request.term },
                    success: function (data) { response(data.diagnostic); }
                });
            } else {
                response([]);
            }
        },
        minLength: 1,
        appendTo: $(this).closest('.modal'),
        select: function (event, ui) {
            $("#ref_code").val(ui.item.value);
            $("#eref_code").val(ui.item.value);
            return false;
        }
    });

    $("#eref_code").on("change", function () {
        const generic = $("#edrug_generic");
        const dosage = $("#edrug_dosage");

        if ($(this).val() == "") {
            generic.val("");
            return;
        }

        $.ajax({
            url: "/api/fetch_drug_details",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { phic_reference_code: $(this).val(), generic: null },
            success: function (response) {
                if (response.success) {
                    generic.val(response.drug.generic_description);
                    dosage.val(response.drug.strength_description);
                }
            }
        });
    });

    $("#drug_generic").on("change", function () {
        const refcode = $("#ref_code");
        const dosage = $("#drug_dosage");

        if ($(this).val() == "") {
            refcode.val("");
            dosage.val("");
            return;
        }

        $.ajax({
            url: "/api/fetch_drug_details",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { generic: $(this).val() },
            success: function (response) {
                if (response.success) {
                    refcode.val(response.drug.drug_code);
                    dosage.val(response.drug.strength_description);
                }
            }
        });
    });

    $("#edrug_generic").on("change", function () {
        const refcode = $("#eref_code");
        const dosage = $("#edrug_dosage");

        if ($(this).val() == "") {
            refcode.val("");
            dosage.val("");
            return;
        }

        $.ajax({
            url: "/api/fetch_drug_details",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { generic: $(this).val() },
            success: function (response) {
                if (response.success) {
                    refcode.val(response.drug.drug_code);
                    dosage.val(response.drug.strength_description);
                }
            }
        });
    });

    $("#save_item").on("click", function () {
        $.ajax({
            url: "/api/save_stock_item",
            type: "POST",
            header: { 'X-CSRF-TOKEN': $("meta[name='csrf-token']").attr("content") },
            data: $("#add_item_form").serialize(),
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title: "Product Added!",
                        text: "The product has been successfully added.",
                        icon: "success"
                    }).then(() => {
                        bootstrap.Modal.getInstance("#add_item_modal").hide();
                        $("#stocks_table").DataTable().ajax.reload();
                    });
                }
            }
        });
    });

    $(document).on("click", ".edit_item", function () {
        const modal = new bootstrap.Modal("#edit_item_modal");

        $.ajax({
            url: "/api/fetch_stock_item",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { prodcode: $(this).val() },
            success: function (response) {
                $("#edit_item_form")[0].reset();

                const item = response.item;

                $("#prodcode").val(item.prodcode);

                $("#eitem_group").val(item.item_grouping);
                $("#eitem_dscr").val(item.prod_itemdscr);
                $("#eprice_regular").val(item.price_regular);
                $("#eprice_phic").val(item.price_phic);
                $("#eprice_hmo").val(item.price_hmo);
                $("#eprice_others").val(item.price_others);

                if (item.item_grouping == "DRUGS AND MEDS") {
                    $("#edrug_fields").removeClass("d-none").addClass("d-flex");
                    $("#erefcode").removeClass("d-none").addClass("d-block");
                    $("#eref_code").val(item.phic_reference_code);
                    $("#edrug_generic").val(item.drug_generic);
                    $("#edrug_brand").val(item.drug_brand);
                    $("#edrug_dosage").val(item.drug_dosage);
                    $("#edrug_group").val(item.drug_grouping);
                } else {
                    $("#edrug_fields").removeClass("d-flex").addClass("d-none");
                    $("#erefcoce").removeClass("d-block").addClass("d-none");
                }
            }
        })

        modal.show();
    });

    $("#edit_item").on("click", function () {
        var form = new FormData($("#edit_item_form")[0]);
        form.append("prodcode", $("#prodcode").val());

        $.ajax({
            url: "/api/edit_stock_item",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: form,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    return Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Product updated',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            }
        });
    });

    $(document).on("click", ".delete_item", function () {
        Swal.fire({
            title: "Confirmation",
            text: "Are you sure you want to delete this product?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Confirm"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/api/delete_stock_item",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: { prodcode: $(this).val() },
                    success: function (response) {
                        if (response.success) {
                            $("#stocks_table").DataTable().ajax.reload();

                            return Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Product deleted',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        }
                    }
                });
            }
        });
    });

    // Inventory-related
    $("#is_inventory").on("change", function () {
        const quantity_entry = $("#quantity-entry");

        if ($(this).prop("checked")) {
            quantity_entry.removeClass("d-none").addClass("d-block");
        } else {
            quantity_entry.removeClass("d-block").addClass("d-none");
        }
    });
});
