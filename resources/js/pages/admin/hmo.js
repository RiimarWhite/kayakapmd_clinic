import { initTableColumnFilters, renderColumnFilterHeader } from '../../helpers/table-column-filter.js';

/**
 * Detailed Comment: Admin HMO Masterlist Controller.
 * Manages accredited HMO and corporate partner CRUD with DataTables, column sorting/filtering
 * modal dropdowns, picklist filtering, button loading states, and SweetAlert2 confirmation alerts.
 */
$(function () {
    let hmoTable = null;

    // Detailed Comment: Render custom column filter dropdown headers with text inputs and picklists
    $("#th_hmo_code").html(renderColumnFilterHeader('HMO Code', 1));
    $("#th_hmo_name").html(renderColumnFilterHeader('HMO Name', 2));
    $("#th_hmo_type").html(renderColumnFilterHeader('Type', 3, { picklist: ['HMO', 'GOVERNMENT', 'COMPANY'] }));
    $("#th_hmo_accre").html(renderColumnFilterHeader('Accreditation #', 4));
    $("#th_hmo_address").html(renderColumnFilterHeader('Address', 5));

    loadHmoTable();

    function setBtnLoading($btn, text) {
        if (!$btn || !$btn.length) return;
        $btn.data('original-html', $btn.html()).prop('disabled', true);
        $btn.html(`<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> ${text}`);
    }

    function resetBtnLoading($btn) {
        if (!$btn || !$btn.length) return;
        $btn.prop('disabled', false).html($btn.data('original-html'));
    }

    function loadHmoTable() {
        if ($.fn.DataTable.isDataTable("#admin_hmo_table")) {
            $("#admin_hmo_table").DataTable().clear().destroy();
        }

        hmoTable = $("#admin_hmo_table").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "/api/admin/fetch_hmo",
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
                                <button type="button" class="btn btn-sm btn-primary edit-hmo-btn"
                                    data-code="${data.hmocode}"
                                    data-name="${data.hmoname || ''}"
                                    data-type="${data.hmotype || 'HMO'}"
                                    data-accre="${data.accre_no || ''}"
                                    data-coa="${data.coacode || ''}"
                                    data-address="${data.hmoaddress || ''}"
                                    title="Edit HMO">
                                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                </button>
                                <button type="button" class="btn btn-sm btn-danger delete-hmo-btn"
                                    value="${data.hmocode}" data-name="${data.hmoname || data.hmocode}" title="Delete HMO">
                                    <i class="fa-solid fa-trash-can me-1"></i> Delete
                                </button>
                            </div>
                        `;
                    }
                },
                {
                    data: 'hmocode',
                    className: 'align-middle font-monospace fw-bold',
                    render: function (data) {
                        return data ? `<code>${data}</code>` : '-';
                    }
                },
                {
                    data: 'hmoname',
                    className: 'align-middle fw-semibold'
                },
                {
                    data: 'hmotype',
                    className: 'align-middle text-center',
                    render: function (data) {
                        const type = (data || 'HMO').toUpperCase();
                        if (type === 'GOVERNMENT') {
                            return `<span class="badge bg-success px-2 py-1"><i class="fa-solid fa-landmark me-1"></i> Government</span>`;
                        } else if (type === 'COMPANY') {
                            return `<span class="badge bg-secondary px-2 py-1"><i class="fa-solid fa-building me-1"></i> Company</span>`;
                        }
                        return `<span class="badge bg-primary px-2 py-1"><i class="fa-solid fa-heart-pulse me-1"></i> HMO</span>`;
                    }
                },
                {
                    data: 'accre_no',
                    className: 'align-middle',
                    render: function (data) {
                        return data || '<span class="text-muted fst-italic">None</span>';
                    }
                },
                {
                    data: 'hmoaddress',
                    className: 'align-middle small text-truncate',
                    render: function (data) {
                        return data ? `<span title="${data}">${data}</span>` : '<span class="text-muted fst-italic">Not specified</span>';
                    }
                }
            ],
            order: [[2, 'asc']],
            pageLength: 15,
            lengthChange: true,
            language: {
                emptyTable: "No HMO records found.",
                search: "Search all HMOs:"
            }
        });

        // Detailed Comment: Initialize dropdown column filters on the table
        initTableColumnFilters(hmoTable, '#admin_hmo_table');
    }

    // Detailed Comment: Open Add HMO Modal
    $("#btn_open_add_hmo").on("click", function () {
        $("#add_hmo_form")[0].reset();
        new bootstrap.Modal("#add_hmo_modal").show();
    });

    // Detailed Comment: Save New HMO with button loader
    $("#add_hmo_form").on("submit", function (e) {
        e.preventDefault();
        const $btn = $("#btn_save_add_hmo");
        setBtnLoading($btn, "Saving...");

        $.ajax({
            url: "/api/admin/add_hmo",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: $(this).serialize(),
            success: function () {
                resetBtnLoading($btn);
                bootstrap.Modal.getInstance("#add_hmo_modal").hide();
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'HMO provider added successfully!',
                    showConfirmButton: false,
                    timer: 2000
                });
                hmoTable.ajax.reload(null, false);
            },
            error: function (xhr) {
                resetBtnLoading($btn);
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to add HMO.";
                Swal.fire({ title: "Error", text: msg, icon: "error" });
            }
        });
    });

    // Detailed Comment: Open Edit HMO Modal with populated fields
    $(document).on("click", ".edit-hmo-btn", function () {
        const $this = $(this);
        $("#edit_original_hmocode").val($this.data("code"));
        $("#edit_hmocode").val($this.data("code"));
        $("#edit_hmoname").val($this.data("name"));
        $("#edit_hmotype").val($this.data("type"));
        $("#edit_accre_no").val($this.data("accre"));
        $("#edit_coacode").val($this.data("coa"));
        $("#edit_hmoaddress").val($this.data("address"));

        new bootstrap.Modal("#edit_hmo_modal").show();
    });

    // Detailed Comment: Save Updated HMO with button loader
    $("#edit_hmo_form").on("submit", function (e) {
        e.preventDefault();
        const $btn = $("#btn_save_edit_hmo");
        setBtnLoading($btn, "Updating...");

        $.ajax({
            url: "/api/admin/edit_hmo",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: $(this).serialize(),
            success: function () {
                resetBtnLoading($btn);
                bootstrap.Modal.getInstance("#edit_hmo_modal").hide();
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'HMO provider updated successfully!',
                    showConfirmButton: false,
                    timer: 2000
                });
                hmoTable.ajax.reload(null, false);
            },
            error: function (xhr) {
                resetBtnLoading($btn);
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to update HMO.";
                Swal.fire({ title: "Error", text: msg, icon: "error" });
            }
        });
    });

    // Detailed Comment: Delete HMO with confirmation dialog and loading animation
    $(document).on("click", ".delete-hmo-btn", function () {
        const code = $(this).val();
        const name = $(this).data("name");

        Swal.fire({
            title: "Confirm Deletion",
            text: `Are you sure you want to delete HMO "${name}" (${code})?`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete",
            confirmButtonColor: "#d33"
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: "Deleting...",
                    text: "Please wait while removing HMO record.",
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: "/api/admin/delete_hmo",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: { code: code },
                    success: function () {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'HMO provider removed successfully!',
                            showConfirmButton: false,
                            timer: 2000
                        });
                        hmoTable.ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to delete HMO.";
                        Swal.fire({ title: "Error", text: msg, icon: "error" });
                    }
                });
            }
        });
    });
});
