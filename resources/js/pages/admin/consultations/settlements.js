import { initTableColumnFilters, renderColumnFilterHeader } from '../../../helpers/table-column-filter.js';

/**
 * Detailed Comment: Admin Consultations Settlements Management Controller.
 * Manages cashier settlements, itemized fee breakdowns, coverage deductions,
 * dynamic amount calculations, column sorting/filtering modal dropdowns,
 * button loading states, and SweetAlert2 confirmation alerts.
 */
$(function () {
    let settlementsTable = null;

    // Detailed Comment: Render custom column filter dropdown headers for each column
    $("#th_stl_date").html(renderColumnFilterHeader('Date', 1));
    $("#th_stl_refno").html(renderColumnFilterHeader('Consultation Ref', 2));
    $("#th_stl_doc").html(renderColumnFilterHeader('Attending Doctor', 3));
    $("#th_stl_gross").html(renderColumnFilterHeader('Gross Total', 4));
    $("#th_stl_cash").html(renderColumnFilterHeader('Cash', 5));
    $("#th_stl_card").html(renderColumnFilterHeader('Card / CTA', 6));
    $("#th_stl_hmo").html(renderColumnFilterHeader('HMO', 7));
    $("#th_stl_phic").html(renderColumnFilterHeader('PhilHealth', 8));
    $("#th_stl_payable").html(renderColumnFilterHeader('Net Payable', 9));

    loadSettlementsTable();
    loadActiveConsultations();

    function setBtnLoading($btn, text) {
        if (!$btn || !$btn.length) return;
        $btn.data('original-html', $btn.html()).prop('disabled', true);
        $btn.html(`<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> ${text}`);
    }

    function resetBtnLoading($btn) {
        if (!$btn || !$btn.length) return;
        $btn.prop('disabled', false).html($btn.data('original-html'));
    }

    // Detailed Comment: Real-time math calculation for Add Settlement form
    function calcAddTotals() {
        const pf = parseFloat($("#add_stl_doctorspf").val()) || 0;
        const meds = parseFloat($("#add_stl_meds").val()) || 0;
        const lab = parseFloat($("#add_stl_lab").val()) || 0;
        const others = parseFloat($("#add_stl_others").val()) || 0;

        const vat = parseFloat($("#add_stl_less_vat").val()) || 0;
        const discount = parseFloat($("#add_stl_less_discount").val()) || 0;
        const hmo = parseFloat($("#add_stl_less_hmo").val()) || 0;
        const phic = parseFloat($("#add_stl_less_phic").val()) || 0;

        const gross = pf + meds + lab + others;
        const deductions = vat + discount + hmo + phic;
        const netPayable = Math.max(0, gross - deductions);

        $("#add_stl_total_gross").val(gross.toFixed(2));
        $("#add_stl_net_payable").val(netPayable.toFixed(2));
    }

    $(".calc-stl-add").on("input change", calcAddTotals);

    // Detailed Comment: Real-time math calculation for Edit Settlement form
    function calcEditTotals() {
        const pf = parseFloat($("#edit_stl_doctorspf").val()) || 0;
        const meds = parseFloat($("#edit_stl_meds").val()) || 0;
        const lab = parseFloat($("#edit_stl_lab").val()) || 0;
        const others = parseFloat($("#edit_stl_others").val()) || 0;

        const vat = parseFloat($("#edit_stl_less_vat").val()) || 0;
        const discount = parseFloat($("#edit_stl_less_discount").val()) || 0;
        const hmo = parseFloat($("#edit_stl_less_hmo").val()) || 0;
        const phic = parseFloat($("#edit_stl_less_phic").val()) || 0;

        const gross = pf + meds + lab + others;
        const deductions = vat + discount + hmo + phic;
        const netPayable = Math.max(0, gross - deductions);

        $("#edit_stl_total_gross").val(gross.toFixed(2));
        $("#edit_stl_net_payable").val(netPayable.toFixed(2));
    }

    $(".calc-stl-edit").on("input change", calcEditTotals);

    // Detailed Comment: Fetch active consultations for auto-populating Add Settlement modal
    function loadActiveConsultations() {
        $.ajax({
            url: "/api/admin/active_consultations",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            success: function (response) {
                if (response.success && response.consultations) {
                    const $select = $("#add_stl_consultation_select");
                    $select.empty().append('<option value="" selected disabled>-- Choose Active Consultation --</option>');
                    response.consultations.forEach(function (c) {
                        $select.append(`
                            <option value="${c.consultationrefno}"
                                data-ref="${c.consultationrefno}"
                                data-pxname="${c.pxname}"
                                data-pin="${c.pxcode_pin || ''}"
                                data-docref="${c.docrefno || ''}"
                                data-docname="${c.docname || ''}">
                                ${c.pxname} (${c.consultationrefno}) - Dr. ${c.docname || 'N/A'}
                            </option>
                        `);
                    });
                }
            }
        });
    }

    // Detailed Comment: Handle Consultation selection change
    $("#add_stl_consultation_select").on("change", function () {
        const $opt = $(this).find("option:selected");
        $("#add_stl_consultationrefno").val($opt.data("ref") || '');
        $("#add_stl_docname").val($opt.data("docname") || '');
        $("#add_stl_docrefno").val($opt.data("docref") || '');
        $("#add_stl_pincode").val($opt.data("pin") || '');
    });

    function loadSettlementsTable() {
        if ($.fn.DataTable.isDataTable("#settlements_table")) {
            $("#settlements_table").DataTable().clear().destroy();
        }

        settlementsTable = $("#settlements_table").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "/api/admin/fetch_settlements",
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
                                <button type="button" class="btn btn-sm btn-primary edit-stl-btn"
                                    data-ref="${data.consultationrefno}"
                                    data-doc="${data.docname || ''}"
                                    data-pf="${data.total_doctorspf || 0}"
                                    data-meds="${data.total_meds || 0}"
                                    data-lab="${data.total_lab || 0}"
                                    data-others="${data.total_others || 0}"
                                    data-gross="${data.total_gross || 0}"
                                    data-vat="${data.less_vat || 0}"
                                    data-discount="${data.less_discount || 0}"
                                    data-hmo="${data.less_hmo || 0}"
                                    data-phic="${data.less_phic || 0}"
                                    data-payable="${data.net_payable || 0}"
                                    data-cash="${data.payment_cash || 0}"
                                    data-card="${data.payment_card || 0}"
                                    data-cta="${data.cta_type || ''}"
                                    title="Edit Settlement">
                                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                </button>
                                <button type="button" class="btn btn-sm btn-danger delete-stl-btn"
                                    value="${data.consultationrefno}" title="Delete Settlement">
                                    <i class="fa-solid fa-trash-can me-1"></i> Delete
                                </button>
                            </div>
                        `;
                    }
                },
                {
                    data: 'created',
                    className: 'align-middle small',
                    render: function (data, type, row) {
                        const val = data || row.cashier_date;
                        return val ? val.split('T')[0].split(' ')[0] : '-';
                    }
                },
                {
                    data: 'consultationrefno',
                    className: 'align-middle font-monospace',
                    render: function (data) {
                        return data ? `<code>${data}</code>` : '-';
                    }
                },
                {
                    data: 'docname',
                    className: 'align-middle fw-semibold',
                    render: function (data) {
                        return data ? `Dr. ${data}` : '<span class="text-muted fst-italic">None</span>';
                    }
                },
                {
                    data: 'total_gross',
                    className: 'align-middle text-end font-monospace',
                    render: function (data, type, row) {
                        const val = parseFloat(data || row.net_total) || 0;
                        return '₱' + val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    }
                },
                {
                    data: 'payment_cash',
                    className: 'align-middle text-end font-monospace',
                    render: function (data, type, row) {
                        const val = parseFloat(data || row.cash) || 0;
                        return '₱' + val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    }
                },
                {
                    data: 'payment_card',
                    className: 'align-middle text-end font-monospace',
                    render: function (data, type, row) {
                        const val = parseFloat(data || row.cta) || 0;
                        return '₱' + val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    }
                },
                {
                    data: 'less_hmo',
                    className: 'align-middle text-end font-monospace',
                    render: function (data, type, row) {
                        const val = parseFloat(data || row.hmo) || 0;
                        return '₱' + val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    }
                },
                {
                    data: 'less_phic',
                    className: 'align-middle text-end font-monospace',
                    render: function (data, type, row) {
                        const val = parseFloat(data || row.phic) || 0;
                        return '₱' + val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    }
                },
                {
                    data: 'net_payable',
                    className: 'align-middle text-end font-monospace fw-bold text-success',
                    render: function (data) {
                        const val = parseFloat(data) || 0;
                        return '₱' + val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    }
                }
            ],
            order: [[1, 'desc']],
            pageLength: 15,
            lengthChange: true,
            language: {
                emptyTable: "No settlement records found.",
                search: "Search all settlements:"
            }
        });

        // Detailed Comment: Initialize dropdown column filters on the table
        initTableColumnFilters(settlementsTable, '#settlements_table');
    }

    // Detailed Comment: Open Add Settlement Modal
    $("#btn_open_add_settlement").on("click", function () {
        $("#add_settlement_form")[0].reset();
        calcAddTotals();
        loadActiveConsultations();
        new bootstrap.Modal("#add_settlement_modal").show();
    });

    // Detailed Comment: Save New Settlement Record with button loader
    $("#add_settlement_form").on("submit", function (e) {
        e.preventDefault();
        const $btn = $("#btn_save_add_settlement");
        setBtnLoading($btn, "Saving...");

        $.ajax({
            url: "/api/admin/add_settlement",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: $(this).serialize(),
            success: function () {
                resetBtnLoading($btn);
                bootstrap.Modal.getInstance("#add_settlement_modal").hide();
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Settlement record saved successfully!',
                    showConfirmButton: false,
                    timer: 2000
                });
                settlementsTable.ajax.reload(null, false);
            },
            error: function (xhr) {
                resetBtnLoading($btn);
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to save settlement record.";
                Swal.fire({ title: "Error", text: msg, icon: "error" });
            }
        });
    });

    // Detailed Comment: Open Edit Settlement Modal
    $(document).on("click", ".edit-stl-btn", function () {
        const $this = $(this);
        $("#edit_stl_consultationrefno").val($this.data("ref"));
        $("#edit_stl_docname").val($this.data("doc"));
        $("#edit_stl_doctorspf").val($this.data("pf"));
        $("#edit_stl_meds").val($this.data("meds"));
        $("#edit_stl_lab").val($this.data("lab"));
        $("#edit_stl_others").val($this.data("others"));
        $("#edit_stl_total_gross").val($this.data("gross"));
        $("#edit_stl_less_vat").val($this.data("vat"));
        $("#edit_stl_less_discount").val($this.data("discount"));
        $("#edit_stl_less_hmo").val($this.data("hmo"));
        $("#edit_stl_less_phic").val($this.data("phic"));
        $("#edit_stl_net_payable").val($this.data("payable"));
        $("#edit_stl_payment_cash").val($this.data("cash"));
        $("#edit_stl_payment_card").val($this.data("card"));
        $("#edit_stl_cta_type").val($this.data("cta"));

        new bootstrap.Modal("#edit_settlement_modal").show();
    });

    // Detailed Comment: Save Updated Settlement Record with button loader
    $("#edit_settlement_form").on("submit", function (e) {
        e.preventDefault();
        const $btn = $("#btn_save_edit_settlement");
        setBtnLoading($btn, "Updating...");

        $.ajax({
            url: "/api/admin/edit_settlement",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: $(this).serialize(),
            success: function () {
                resetBtnLoading($btn);
                bootstrap.Modal.getInstance("#edit_settlement_modal").hide();
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Settlement record updated successfully!',
                    showConfirmButton: false,
                    timer: 2000
                });
                settlementsTable.ajax.reload(null, false);
            },
            error: function (xhr) {
                resetBtnLoading($btn);
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to update settlement.";
                Swal.fire({ title: "Error", text: msg, icon: "error" });
            }
        });
    });

    // Detailed Comment: Delete Settlement Record with confirmation dialog and loading animation
    $(document).on("click", ".delete-stl-btn", function () {
        const ref = $(this).val();

        Swal.fire({
            title: "Confirm Deletion",
            text: `Are you sure you want to delete settlement for consultation "${ref}"?`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete",
            confirmButtonColor: "#d33"
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: "Deleting...",
                    text: "Please wait while removing settlement record.",
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: "/api/admin/delete_settlement",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: { consultationrefno: ref },
                    success: function () {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Settlement record removed successfully!',
                            showConfirmButton: false,
                            timer: 2000
                        });
                        settlementsTable.ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to delete settlement.";
                        Swal.fire({ title: "Error", text: msg, icon: "error" });
                    }
                });
            }
        });
    });
});
