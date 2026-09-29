import { initTableColumnFilters, renderColumnFilterHeader } from '../../../helpers/table-column-filter.js';

/**
 * Detailed Comment: Admin Stocks & Services Management Controller.
 * Handles item/service masterlist with custom column filter and sorting headers (mirroring admin/hmo),
 * dynamic drug categorization with searchable Select2 generic dropdown from dw_lib_meds_generic,
 * automatic drug item naming based on "( Brand - Generic Dosage )", and CRUD operations with SweetAlert2.
 */
$(function () {
    let stocksTable = null;
    let isPopulatingEditModal = false;

    // Detailed Comment: Render custom column filter and ordering dropdown headers
    $("#th_stock_desc").html(renderColumnFilterHeader('Item Description', 1));
    $("#th_stock_group").html(renderColumnFilterHeader('Category', 2, {
        picklist: ['DRUGS AND MEDS', 'SUPPLIES', 'PROCEDURES', 'DIAGNOSTIC', 'IMAGING', 'PROFESSIONAL FEE']
    }));
    $("#th_stock_phic").html(renderColumnFilterHeader('PhilHealth Reference Code', 3));
    $("#th_stock_reg").html(renderColumnFilterHeader('Price (Regular)', 4, { alignEnd: true }));
    $("#th_stock_phic_price").html(renderColumnFilterHeader('Price (PHIC)', 5, { alignEnd: true }));
    $("#th_stock_hmo").html(renderColumnFilterHeader('Price (HMO)', 6, { alignEnd: true }));
    $("#th_stock_others").html(renderColumnFilterHeader('Price (Others)', 7, { alignEnd: true }));

    // Initialize the main Stocks & Services masterlist table
    loadStocksTable();

    // Initialize Select2 searchable dropdowns for generic drugs
    initGenericSelect2();

    /**
     * Detailed Comment: Helper to set button loading state during AJAX submissions
     */
    function setBtnLoading($btn, text) {
        if (!$btn || !$btn.length) return;
        $btn.data('orig-html', $btn.html()).prop('disabled', true);
        $btn.html(`<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> ${text}`);
    }

    /**
     * Detailed Comment: Helper to reset button loading state
     */
    function resetBtnLoading($btn) {
        if (!$btn || !$btn.length) return;
        $btn.prop('disabled', false).html($btn.data('orig-html'));
    }

    /**
     * Detailed Comment: Auto-generates standard medicine display name based on "( Brand - Generic Dosage )" structure.
     * If Brand is provided: `${brand} - ${generic} ${dosage}`.
     * If Brand is empty: `${generic} ${dosage}`.
     */
    function generateDrugName(brand, generic, dosage) {
        brand = (brand || '').trim();
        generic = (generic || '').trim();
        dosage = (dosage || '').trim();

        const genDosage = [generic, dosage].filter(Boolean).join(' ');
        if (brand && genDosage) {
            return `${brand} - ${genDosage}`;
        } else if (genDosage) {
            return genDosage;
        } else if (brand) {
            return brand;
        }
        return '';
    }

    /**
     * Detailed Comment: Updates the generated drug item name in the Add Item modal
     */
    function updateAddDrugName() {
        const brand = $("#drug_brand").val();
        const generic = $("#drug_generic").val();
        const dosage = $("#drug_dosage").val();
        const generated = generateDrugName(brand, generic, dosage);
        if (generated) {
            $("#item_dscr").val(generated);
        }
    }

    /**
     * Detailed Comment: Updates the generated drug item name in the Edit Item modal
     */
    function updateEditDrugName() {
        if (isPopulatingEditModal) return;
        const brand = $("#edrug_brand").val();
        const generic = $("#edrug_generic").val();
        const dosage = $("#edrug_dosage").val();
        const generated = generateDrugName(brand, generic, dosage);
        if (generated) {
            $("#eitem_dscr").val(generated);
        }
    }

    /**
     * Detailed Comment: Initializes Select2 searchable dropdowns referencing dw_lib_meds_generic for generic drug names.
     */
    function initGenericSelect2() {
        $("#drug_generic").select2({
            width: '100%',
            dropdownParent: $("#add_item_modal"),
            placeholder: "-- Search Generic Name --",
            allowClear: true,
            ajax: {
                url: "/api/fetch_drugref",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { term: params.term || '' };
                },
                processResults: function (data) {
                    if (data.results && data.results.length) {
                        return { results: data.results };
                    }
                    const list = data.generic || [];
                    return {
                        results: list.map(item => ({
                            id: item.value || item.label,
                            text: item.label || item.value
                        }))
                    };
                },
                cache: true
            }
        });

        $("#edrug_generic").select2({
            width: '100%',
            dropdownParent: $("#edit_item_modal"),
            placeholder: "-- Search Generic Name --",
            allowClear: true,
            ajax: {
                url: "/api/fetch_drugref",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { term: params.term || '' };
                },
                processResults: function (data) {
                    if (data.results && data.results.length) {
                        return { results: data.results };
                    }
                    const list = data.generic || [];
                    return {
                        results: list.map(item => ({
                            id: item.value || item.label,
                            text: item.label || item.value
                        }))
                    };
                },
                cache: true
            }
        });
    }

    /**
     * Detailed Comment: Loads DataTables for Stocks & Services with custom column filter integration
     */
    function loadStocksTable() {
        if ($.fn.DataTable.isDataTable("#stocks_table")) {
            $("#stocks_table").DataTable().clear().destroy();
        }

        stocksTable = $("#stocks_table").DataTable({
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
                    orderable: false,
                    searchable: false,
                    className: 'text-center align-middle text-nowrap',
                    render: function (data) {
                        return `
                            <div class="d-inline-flex gap-1">
                                <button type="button" class="btn btn-sm btn-success edit_item" value="${data}" title="Edit Item">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger delete_item" value="${data}" title="Delete Item">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        `;
                    }
                },
                {
                    data: 'prod_itemdscr',
                    className: 'align-middle fw-semibold'
                },
                {
                    data: 'item_grouping',
                    className: 'align-middle text-nowrap',
                    render: function (data) {
                        return data ? `<span class="badge bg-light text-dark border px-2 py-1">${data}</span>` : '-';
                    }
                },
                {
                    data: 'phic_reference_code',
                    className: 'align-middle font-monospace',
                    render: function (data) {
                        return (data && data !== "") ? `<code>${data}</code>` : "<span class='text-muted fst-italic'>NONE</span>";
                    }
                },
                {
                    data: 'price_regular',
                    className: 'align-middle text-end font-monospace',
                    render: function (data) { return data ? parseFloat(data).toFixed(2) : '0.00'; }
                },
                {
                    data: 'price_phic',
                    className: 'align-middle text-end font-monospace',
                    render: function (data) { return data ? parseFloat(data).toFixed(2) : '0.00'; }
                },
                {
                    data: 'price_hmo',
                    className: 'align-middle text-end font-monospace',
                    render: function (data) { return data ? parseFloat(data).toFixed(2) : '0.00'; }
                },
                {
                    data: 'price_others',
                    className: 'align-middle text-end font-monospace',
                    render: function (data) { return data ? parseFloat(data).toFixed(2) : '0.00'; }
                },
            ],
            columnDefs: [
                { target: 0, width: '1%', className: 'text-center text-nowrap align-middle', orderable: false, searchable: false },
                { targets: [1, 2, 3], className: 'align-middle' },
                { targets: [4, 5, 6, 7], width: '1%', className: 'align-middle text-end text-nowrap font-monospace', type: 'num' }
            ],
            language: {
                emptyTable: 'No items or services to display yet.',
                search: "Search all items:"
            },
            order: [[1, 'asc']],
            pageLength: 15,
            lengthChange: true
        });

        // Detailed Comment: Initialize custom dropdown column filters on stocks table
        initTableColumnFilters(stocksTable, '#stocks_table');
    }

    // Modal shown handler: resets form and hides conditional fields
    $("#add_item_modal").on("shown.bs.modal", function () {
        $("#add_item_form")[0].reset();
        $("#drug_generic").val(null).trigger('change');
        $("#item_group").trigger("change");
    });

    /**
     * Detailed Comment: Category change listener on Add Item Modal.
     * When Category is "DRUGS AND MEDS", display drug fields (Generic, Brand, Dosage) ABOVE Name and PhilHealth code.
     */
    $("#item_group").on("change", function () {
        const drugFields = $("#drug_fields");
        const refCode = $("#refcode");

        if ($(this).val() === "DRUGS AND MEDS") {
            drugFields.removeClass("d-none").addClass("d-flex");
            refCode.removeClass("d-none").addClass("d-block");
        } else if ($(this).val() === "DIAGNOSTIC") {
            drugFields.removeClass("d-flex").addClass("d-none");
            refCode.removeClass("d-none").addClass("d-block");
        } else {
            drugFields.removeClass("d-flex").addClass("d-none");
            refCode.removeClass("d-block").addClass("d-none");
        }
    });

    /**
     * Detailed Comment: Category change listener on Edit Item Modal.
     */
    $("#eitem_group").on("change", function () {
        const drugFields = $("#edrug_fields");
        const refCode = $("#erefcode");

        if ($(this).val() === "DRUGS AND MEDS") {
            drugFields.removeClass("d-none").addClass("d-flex");
            refCode.removeClass("d-none").addClass("d-block");
        } else if ($(this).val() === "DIAGNOSTIC") {
            drugFields.removeClass("d-flex").addClass("d-none");
            refCode.removeClass("d-none").addClass("d-block");
        } else {
            drugFields.removeClass("d-flex").addClass("d-none");
            refCode.removeClass("d-block").addClass("d-none");
        }
    });

    // Auto-generate name on Add Modal inputs
    $("#drug_brand, #drug_dosage").on("input", function () {
        updateAddDrugName();
    });

    // Detailed Comment: When Generic changes in Add modal, fetch drug details (strength, code) and update generated name
    $("#drug_generic").on("select2:select change", function () {
        const genericVal = $(this).val();
        if (!genericVal) return;

        $.ajax({
            url: "/api/fetch_drug_details",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { generic: genericVal },
            success: function (response) {
                if (response.success && response.drug) {
                    if (response.drug.drug_code && !$("#ref_code").val()) {
                        $("#ref_code").val(response.drug.drug_code);
                    }
                    if (response.drug.strength_description && !$("#drug_dosage").val()) {
                        $("#drug_dosage").val(response.drug.strength_description);
                    }
                }
                updateAddDrugName();
            }
        });
    });

    // Auto-generate name on Edit Modal inputs
    $("#edrug_brand, #edrug_dosage").on("input", function () {
        updateEditDrugName();
    });

    // Detailed Comment: When Generic changes in Edit modal, fetch drug details and update generated name
    $("#edrug_generic").on("select2:select change", function () {
        if (isPopulatingEditModal) return;
        const genericVal = $(this).val();
        if (!genericVal) return;

        $.ajax({
            url: "/api/fetch_drug_details",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { generic: genericVal },
            success: function (response) {
                if (response.success && response.drug) {
                    if (response.drug.drug_code && !$("#eref_code").val()) {
                        $("#eref_code").val(response.drug.drug_code);
                    }
                    if (response.drug.strength_description && !$("#edrug_dosage").val()) {
                        $("#edrug_dosage").val(response.drug.strength_description);
                    }
                }
                updateEditDrugName();
            }
        });
    });

    // PhilHealth code autocomplete for Diagnostic items
    $("#ref_code, #eref_code").autocomplete({
        source: function (request, response) {
            const input = $(this.element);
            const modal = input.closest('.modal');
            const group = modal.find('select[id$="item_group"]').val();

            if (group === "DIAGNOSTIC") {
                $.ajax({
                    url: "/api/fetch_diagnostic_reference",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: { term: request.term },
                    success: function (data) { response(data.diagnostic || []); }
                });
            } else {
                response([]);
            }
        },
        minLength: 1,
        appendTo: $(this).closest('.modal'),
        select: function (event, ui) {
            $(this).val(ui.item.value);
            return false;
        }
    });

    /**
     * Detailed Comment: Saves a new product or service item to inventory and stocks masterlist
     */
    $("#save_item").on("click", function () {
        const $btn = $(this);
        const itemDscr = $("#item_dscr").val();

        if (!itemDscr) {
            return Swal.fire({
                title: "Validation Error",
                text: "Item Name is required.",
                icon: "warning"
            });
        }

        setBtnLoading($btn, "Saving...");

        $.ajax({
            url: "/api/save_stock_item",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $("meta[name='csrf-token']").attr("content") },
            data: $("#add_item_form").serialize(),
            success: function (response) {
                resetBtnLoading($btn);
                if (response.success) {
                    Swal.fire({
                        title: "Product Added!",
                        text: "The item or service has been successfully created.",
                        icon: "success",
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        const modalEl = document.getElementById("add_item_modal");
                        const modalInst = bootstrap.Modal.getInstance(modalEl);
                        if (modalInst) modalInst.hide();
                        stocksTable.ajax.reload();
                    });
                } else {
                    Swal.fire({
                        title: "Failed",
                        text: response.message || "Failed to add product.",
                        icon: "error"
                    });
                }
            },
            error: function () {
                resetBtnLoading($btn);
                Swal.fire({
                    title: "Server Error",
                    text: "An error occurred while saving the item.",
                    icon: "error"
                });
            }
        });
    });

    /**
     * Detailed Comment: Fetches existing item details and opens the Edit Item modal
     */
    $(document).on("click", ".edit_item", function () {
        const prodcode = $(this).val();
        const modal = new bootstrap.Modal("#edit_item_modal");

        $.ajax({
            url: "/api/fetch_stock_item",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { prodcode: prodcode },
            success: function (response) {
                if (!response.item) return;

                isPopulatingEditModal = true;
                $("#edit_item_form")[0].reset();

                const item = response.item;
                $("#prodcode").val(item.prodcode);
                $("#eitem_group").val(item.item_grouping).trigger("change");
                $("#eitem_dscr").val(item.prod_itemdscr);
                $("#eprice_regular").val(item.price_regular);
                $("#eprice_phic").val(item.price_phic);
                $("#eprice_hmo").val(item.price_hmo);
                $("#eprice_others").val(item.price_others);

                if (item.item_grouping === "DRUGS AND MEDS") {
                    $("#eref_code").val(item.phic_reference_code || '');
                    $("#edrug_brand").val(item.drug_brand || '');
                    $("#edrug_dosage").val(item.drug_dosage || '');
                    $("#edrug_group").val(item.drug_grouping || item.drug_group || '');

                    if (item.drug_generic) {
                        const option = new Option(item.drug_generic, item.drug_generic, true, true);
                        $("#edrug_generic").empty().append(option).trigger('change');
                    } else {
                        $("#edrug_generic").val(null).trigger('change');
                    }
                } else if (item.item_grouping === "DIAGNOSTIC") {
                    $("#eref_code").val(item.phic_reference_code || '');
                    $("#edrug_generic").val(null).trigger('change');
                } else {
                    $("#edrug_generic").val(null).trigger('change');
                }

                isPopulatingEditModal = false;
                modal.show();
            }
        });
    });

    /**
     * Detailed Comment: Updates existing product or service details
     */
    $("#edit_item").on("click", function () {
        const $btn = $(this);
        const itemDscr = $("#eitem_dscr").val();

        if (!itemDscr) {
            return Swal.fire({
                title: "Validation Error",
                text: "Item Name is required.",
                icon: "warning"
            });
        }

        setBtnLoading($btn, "Updating...");

        const form = new FormData($("#edit_item_form")[0]);
        form.append("prodcode", $("#prodcode").val());

        $.ajax({
            url: "/api/edit_stock_item",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: form,
            processData: false,
            contentType: false,
            success: function (response) {
                resetBtnLoading($btn);
                if (response.success) {
                    const modalEl = document.getElementById("edit_item_modal");
                    const modalInst = bootstrap.Modal.getInstance(modalEl);
                    if (modalInst) modalInst.hide();

                    stocksTable.ajax.reload();

                    return Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Product updated successfully',
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    Swal.fire({
                        title: "Failed",
                        text: response.message || "Failed to update product.",
                        icon: "error"
                    });
                }
            },
            error: function () {
                resetBtnLoading($btn);
                Swal.fire({
                    title: "Server Error",
                    text: "An error occurred while updating the item.",
                    icon: "error"
                });
            }
        });
    });

    /**
     * Detailed Comment: Deletes a product or service item from inventory
     */
    $(document).on("click", ".delete_item", function () {
        const prodcode = $(this).val();

        Swal.fire({
            title: "Confirm Deletion",
            text: "Are you sure you want to delete this product/service?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete it",
            confirmButtonColor: "#d33"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/api/delete_stock_item",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: { prodcode: prodcode },
                    success: function (response) {
                        if (response.success) {
                            stocksTable.ajax.reload();

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

    // Inventory checkbox toggle
    $("#is_inventory").on("change", function () {
        const quantityEntry = $("#quantity-entry");
        if ($(this).prop("checked")) {
            quantityEntry.removeClass("d-none").addClass("d-block");
        } else {
            quantityEntry.removeClass("d-block").addClass("d-none");
        }
    });
});
