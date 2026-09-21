import { initTableColumnFilters, renderColumnFilterHeader } from '../../../helpers/table-column-filter.js';

/**
 * Detailed Comment: Admin Charge Categories Controller.
 * Manages category CRUD with interactive DataTables, column sorting/filtering modal dropdowns,
 * modal forms, button loading states, and SweetAlert2 confirmation alerts.
 */
$(function () {
    let categoryTable = null;

    // Detailed Comment: Render custom column filter dropdown headers before DataTables initialization
    $("#th_cat_refno").html(renderColumnFilterHeader('Category Ref No', 1));
    $("#th_cat_name").html(renderColumnFilterHeader('Category Name', 2));

    loadCategoryTable();

    function setBtnLoading($btn, text) {
        if (!$btn || !$btn.length) return;
        $btn.data('original-html', $btn.html()).prop('disabled', true);
        $btn.html(`<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> ${text}`);
    }

    function resetBtnLoading($btn) {
        if (!$btn || !$btn.length) return;
        $btn.prop('disabled', false).html($btn.data('original-html'));
    }

    function loadCategoryTable() {
        if ($.fn.DataTable.isDataTable("#charge_category_table")) {
            $("#charge_category_table").DataTable().clear().destroy();
        }

        categoryTable = $("#charge_category_table").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "/api/fetch_charge_categories",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") }
            },
            columns: [
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-center align-middle',
                    render: function (data) {
                        return `
                            <div class="d-inline-flex gap-1">
                                <button type="button" class="btn btn-sm btn-primary edit-category"
                                    data-ref="${data.categoryrefno}" data-name="${data.categoryname}" title="Edit Category">
                                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                </button>
                                <button type="button" class="btn btn-sm btn-danger delete-category"
                                    value="${data.categoryrefno}" data-name="${data.categoryname}" title="Delete Category">
                                    <i class="fa-solid fa-trash-can me-1"></i> Delete
                                </button>
                            </div>
                        `;
                    }
                },
                {
                    data: 'categoryrefno',
                    className: 'align-middle font-monospace text-muted',
                    render: function (data) {
                        return data ? `<code>${data}</code>` : '-';
                    }
                },
                {
                    data: 'categoryname',
                    className: 'align-middle fw-bold'
                }
            ],
            order: [[2, 'asc']],
            pageLength: 15,
            lengthChange: true,
            language: {
                emptyTable: "No charge categories found.",
                search: "Search all categories:"
            }
        });

        // Detailed Comment: Initialize dropdown column filters on the table
        initTableColumnFilters(categoryTable, '#charge_category_table');
    }

    // Detailed Comment: Open Add Category Modal
    $("#btn_open_add_category").on("click", function () {
        $("#add_charge_category_form")[0].reset();
        new bootstrap.Modal("#add_charge_category_modal").show();
    });

    // Detailed Comment: Save New Charge Category with button loader
    $("#add_charge_category_form").on("submit", function (e) {
        e.preventDefault();
        const $btn = $("#btn_save_add_category");
        setBtnLoading($btn, "Saving...");

        $.ajax({
            url: "/api/create_charge_category",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { categoryname: $("#add_categoryname").val().trim() },
            success: function () {
                resetBtnLoading($btn);
                bootstrap.Modal.getInstance("#add_charge_category_modal").hide();
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Charge category created successfully!',
                    showConfirmButton: false,
                    timer: 2000
                });
                categoryTable.ajax.reload(null, false);
            },
            error: function (xhr) {
                resetBtnLoading($btn);
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to create category.";
                Swal.fire({ title: "Error", text: msg, icon: "error" });
            }
        });
    });

    // Detailed Comment: Open Edit Category Modal with pre-populated values
    $(document).on("click", ".edit-category", function () {
        const ref = $(this).data("ref");
        const name = $(this).data("name");
        $("#edit_categoryrefno").val(ref);
        $("#edit_categoryname").val(name);
        new bootstrap.Modal("#edit_charge_category_modal").show();
    });

    // Detailed Comment: Update Category with button loader
    $("#edit_charge_category_form").on("submit", function (e) {
        e.preventDefault();
        const $btn = $("#btn_save_edit_category");
        setBtnLoading($btn, "Updating...");

        $.ajax({
            url: "/api/edit_charge_category",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: {
                ecategoryrefno: $("#edit_categoryrefno").val(),
                categoryname: $("#edit_categoryname").val().trim()
            },
            success: function () {
                resetBtnLoading($btn);
                bootstrap.Modal.getInstance("#edit_charge_category_modal").hide();
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Charge category updated successfully!',
                    showConfirmButton: false,
                    timer: 2000
                });
                categoryTable.ajax.reload(null, false);
            },
            error: function (xhr) {
                resetBtnLoading($btn);
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to update category.";
                Swal.fire({ title: "Error", text: msg, icon: "error" });
            }
        });
    });

    // Detailed Comment: Delete Category with confirmation dialog and loading spinner
    $(document).on("click", ".delete-category", function () {
        const ref = $(this).val();
        const name = $(this).data("name");

        Swal.fire({
            title: "Confirm Deletion",
            text: `Are you sure you want to delete category "${name}"? This action cannot be undone.`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete",
            confirmButtonColor: "#d33"
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: "Deleting...",
                    text: "Please wait while deleting category.",
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: "/api/delete_charge_category",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: { categoryrefno: ref },
                    success: function () {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Category deleted successfully!',
                            showConfirmButton: false,
                            timer: 2000
                        });
                        categoryTable.ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to delete category.";
                        Swal.fire({ title: "Error", text: msg, icon: "error" });
                    }
                });
            }
        });
    });
});
