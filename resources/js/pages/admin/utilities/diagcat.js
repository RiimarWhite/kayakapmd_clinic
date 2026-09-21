import { initTableColumnFilters, renderColumnFilterHeader } from '../../../helpers/table-column-filter.js';

/**
 * Detailed Comment: Admin Diagnostic Categories Controller.
 * Manages category CRUD with DataTables, column sorting/filtering modal dropdowns,
 * modal dialogs, button loading states, and SweetAlert2 confirmation alerts.
 */
$(function () {
    let diagTable = null;

    // Detailed Comment: Render custom column filter dropdown headers before DataTables initialization
    $("#th_diagcat_refno").html(renderColumnFilterHeader('Category Ref No', 1));
    $("#th_diagcat_name").html(renderColumnFilterHeader('Category Name', 2));

    loadDiagCategoryTable();

    function setBtnLoading($btn, text) {
        if (!$btn || !$btn.length) return;
        $btn.data('original-html', $btn.html()).prop('disabled', true);
        $btn.html(`<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> ${text}`);
    }

    function resetBtnLoading($btn) {
        if (!$btn || !$btn.length) return;
        $btn.prop('disabled', false).html($btn.data('original-html'));
    }

    function loadDiagCategoryTable() {
        if ($.fn.DataTable.isDataTable("#diag_category_table")) {
            $("#diag_category_table").DataTable().clear().destroy();
        }

        diagTable = $("#diag_category_table").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "/api/fetch_diagnostic_category",
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
                                <button type="button" class="btn btn-sm btn-primary edit-diagcat"
                                    data-ref="${data.category_refno}" data-name="${data.category_name}" title="Edit Category">
                                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                </button>
                                <button type="button" class="btn btn-sm btn-danger delete-diagcat"
                                    value="${data.category_refno}" data-name="${data.category_name}" title="Delete Category">
                                    <i class="fa-solid fa-trash-can me-1"></i> Delete
                                </button>
                            </div>
                        `;
                    }
                },
                {
                    data: 'category_refno',
                    className: 'align-middle font-monospace text-muted',
                    render: function (data) {
                        return data ? `<code>${data}</code>` : '-';
                    }
                },
                {
                    data: 'category_name',
                    className: 'align-middle fw-bold'
                }
            ],
            order: [[2, 'asc']],
            pageLength: 15,
            lengthChange: true,
            language: {
                emptyTable: "No diagnostic categories found.",
                search: "Search all categories:"
            }
        });

        // Detailed Comment: Initialize dropdown column filters on the table
        initTableColumnFilters(diagTable, '#diag_category_table');
    }

    // Detailed Comment: Open Add Diagnostic Category Modal
    $("#btn_open_add_diagcat").on("click", function () {
        $("#add_diagcat_form")[0].reset();
        new bootstrap.Modal("#add_diagcat_modal").show();
    });

    // Detailed Comment: Save New Diagnostic Category with button loader
    $("#add_diagcat_form").on("submit", function (e) {
        e.preventDefault();
        const $btn = $("#btn_save_add_diagcat");
        setBtnLoading($btn, "Saving...");

        $.ajax({
            url: "/api/create_diagnostic_category",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { category_name: $("#add_diagcat_name").val().trim() },
            success: function () {
                resetBtnLoading($btn);
                bootstrap.Modal.getInstance("#add_diagcat_modal").hide();
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Diagnostic category created successfully!',
                    showConfirmButton: false,
                    timer: 2000
                });
                diagTable.ajax.reload(null, false);
            },
            error: function (xhr) {
                resetBtnLoading($btn);
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to create category.";
                Swal.fire({ title: "Error", text: msg, icon: "error" });
            }
        });
    });

    // Detailed Comment: Open Edit Diagnostic Category Modal
    $(document).on("click", ".edit-diagcat", function () {
        const ref = $(this).data("ref");
        const name = $(this).data("name");
        $("#edit_diagcat_refno").val(ref);
        $("#edit_diagcat_name").val(name);
        new bootstrap.Modal("#edit_diagcat_modal").show();
    });

    // Detailed Comment: Update Diagnostic Category with button loader
    $("#edit_diagcat_form").on("submit", function (e) {
        e.preventDefault();
        const $btn = $("#btn_save_edit_diagcat");
        setBtnLoading($btn, "Updating...");

        $.ajax({
            url: "/api/edit_diagnostic_category",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: {
                category_refno: $("#edit_diagcat_refno").val(),
                category_name: $("#edit_diagcat_name").val().trim()
            },
            success: function () {
                resetBtnLoading($btn);
                bootstrap.Modal.getInstance("#edit_diagcat_modal").hide();
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Diagnostic category updated successfully!',
                    showConfirmButton: false,
                    timer: 2000
                });
                diagTable.ajax.reload(null, false);
            },
            error: function (xhr) {
                resetBtnLoading($btn);
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to update category.";
                Swal.fire({ title: "Error", text: msg, icon: "error" });
            }
        });
    });

    // Detailed Comment: Delete Diagnostic Category with confirmation dialog and loading spinner
    $(document).on("click", ".delete-diagcat", function () {
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
                    text: "Please wait while deleting diagnostic category.",
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: "/api/delete_diagnostic_category",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: { category_refno: ref },
                    success: function () {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Category deleted successfully!',
                            showConfirmButton: false,
                            timer: 2000
                        });
                        diagTable.ajax.reload(null, false);
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
