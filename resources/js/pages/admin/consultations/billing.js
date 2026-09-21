import { initTableColumnFilters, renderColumnFilterHeader } from '../../../helpers/table-column-filter.js';

/**
 * Detailed Comment: Admin Consultations Billing Management Controller.
 * Manages point-of-sale patient charges, active consultation auto-population,
 * dynamic amount calculations, column sorting/filtering modal dropdowns,
 * picklist filtering, button loading states, and SweetAlert2 confirmation alerts.
 */
$(function () {
    let billingTable = null;

    // Detailed Comment: Render custom column filter dropdown headers for each column
    $("#th_bill_date").html(renderColumnFilterHeader('Date', 1));
    $("#th_bill_refno").html(renderColumnFilterHeader('Consultation Ref', 2));
    $("#th_bill_pxname").html(renderColumnFilterHeader('Patient Name', 3));
    $("#th_bill_servicename").html(renderColumnFilterHeader('Service / Item', 4));
    $("#th_bill_category").html(renderColumnFilterHeader('Category', 5));
    $("#th_bill_paytype").html(renderColumnFilterHeader('Payment Type', 6, { picklist: ['CASH', 'HMO', 'CARD', 'FREE'] }));
    $("#th_bill_total").html(renderColumnFilterHeader('Total', 7));
    $("#th_bill_discount").html(renderColumnFilterHeader('Discount', 8));
    $("#th_bill_net").html(renderColumnFilterHeader('Net Total', 9));

    loadBillingTable();
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

    // Detailed Comment: Real-time math calculation for Add Billing form
    function calcAddTotals() {
        const qty = parseFloat($("#add_quantity").val()) || 0;
        const retail = parseFloat($("#add_retail").val()) || 0;
        const discount = parseFloat($("#add_discount").val()) || 0;

        const total = qty * retail;
        const netTotal = Math.max(0, total - discount);

        $("#add_total").val(total.toFixed(2));
        $("#add_net_total").val(netTotal.toFixed(2));
    }

    $(".calc-input-add").on("input change", calcAddTotals);

    // Detailed Comment: Real-time math calculation for Edit Billing form
    function calcEditTotals() {
        const qty = parseFloat($("#edit_quantity").val()) || 0;
        const retail = parseFloat($("#edit_retail").val()) || 0;
        const discount = parseFloat($("#edit_discount").val()) || 0;

        const total = qty * retail;
        const netTotal = Math.max(0, total - discount);

        $("#edit_total").val(total.toFixed(2));
        $("#edit_net_total").val(netTotal.toFixed(2));
    }

    $(".calc-input-edit").on("input change", calcEditTotals);

    // Detailed Comment: Fetch active consultations for auto-populating Add Billing modal
    function loadActiveConsultations() {
        $.ajax({
            url: "/api/admin/active_consultations",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            success: function (response) {
                if (response.success && response.consultations) {
                    const $select = $("#add_consultation_select");
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
    $("#add_consultation_select").on("change", function () {
        const $opt = $(this).find("option:selected");
        $("#add_consultationrefno").val($opt.data("ref") || '');
        $("#add_pxname").val($opt.data("pxname") || '');
        $("#add_pxcode_pin").val($opt.data("pin") || '');
        $("#add_docrefno").val($opt.data("docref") || '');
        $("#add_docname").val($opt.data("docname") || '');
    });

    function loadBillingTable() {
        if ($.fn.DataTable.isDataTable("#billing_table")) {
            $("#billing_table").DataTable().clear().destroy();
        }

        billingTable = $("#billing_table").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "/api/admin/fetch_billings",
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
                                <button type="button" class="btn btn-sm btn-primary edit-bill-btn"
                                    data-id="${data.id}"
                                    data-ref="${data.consultationrefno || ''}"
                                    data-px="${data.pxname || ''}"
                                    data-service="${data.servicename || ''}"
                                    data-category="${data.group_category || ''}"
                                    data-payment="${data.payment_type || 'CASH'}"
                                    data-qty="${data.quantity || 1}"
                                    data-retail="${data.retail || 0}"
                                    data-total="${data.total || 0}"
                                    data-discount="${data.discount || 0}"
                                    data-net="${data.net_total || 0}"
                                    title="Edit Charge">
                                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                </button>
                                <button type="button" class="btn btn-sm btn-danger delete-bill-btn"
                                    value="${data.id}" data-desc="${data.servicename} (${data.pxname})" title="Delete Charge">
                                    <i class="fa-solid fa-trash-can me-1"></i> Delete
                                </button>
                            </div>
                        `;
                    }
                },
                {
                    data: 'transdate',
                    className: 'align-middle small',
                    render: function (data) {
                        return data ? data.split('T')[0] : '-';
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
                    data: 'pxname',
                    className: 'align-middle fw-semibold'
                },
                {
                    data: 'servicename',
                    className: 'align-middle'
                },
                {
                    data: 'group_category',
                    className: 'align-middle small',
                    render: function (data) {
                        return data ? `<span class="badge bg-light text-dark border">${data}</span>` : '-';
                    }
                },
                {
                    data: 'payment_type',
                    className: 'align-middle text-center',
                    render: function (data) {
                        const type = (data || 'CASH').toUpperCase();
                        if (type === 'HMO') return `<span class="badge bg-primary px-2 py-1">HMO</span>`;
                        if (type === 'CARD') return `<span class="badge bg-info text-dark px-2 py-1">CARD</span>`;
                        if (type === 'FREE') return `<span class="badge bg-success px-2 py-1">FREE</span>`;
                        return `<span class="badge bg-secondary px-2 py-1">CASH</span>`;
                    }
                },
                {
                    data: 'total',
                    className: 'align-middle text-end font-monospace',
                    render: function (data) {
                        return '₱' + (parseFloat(data) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    }
                },
                {
                    data: 'discount',
                    className: 'align-middle text-end font-monospace text-muted',
                    render: function (data) {
                        return '₱' + (parseFloat(data) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    }
                },
                {
                    data: 'net_total',
                    className: 'align-middle text-end font-monospace fw-bold text-success',
                    render: function (data) {
                        return '₱' + (parseFloat(data) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    }
                }
            ],
            order: [[1, 'desc']],
            pageLength: 15,
            lengthChange: true,
            language: {
                emptyTable: "No billing charge records found.",
                search: "Search all charges:"
            }
        });

        // Detailed Comment: Initialize dropdown column filters on the table
        initTableColumnFilters(billingTable, '#billing_table');
    }

    // Detailed Comment: Open Add Billing Charge Modal
    $("#btn_open_add_billing").on("click", function () {
        $("#add_billing_form")[0].reset();
        calcAddTotals();
        loadActiveConsultations();
        new bootstrap.Modal("#add_billing_modal").show();
    });

    // Detailed Comment: Save New Billing Charge with button loader
    $("#add_billing_form").on("submit", function (e) {
        e.preventDefault();
        const $btn = $("#btn_save_add_billing");
        setBtnLoading($btn, "Saving...");

        $.ajax({
            url: "/api/admin/add_billing",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: $(this).serialize(),
            success: function () {
                resetBtnLoading($btn);
                bootstrap.Modal.getInstance("#add_billing_modal").hide();
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Billing charge added successfully!',
                    showConfirmButton: false,
                    timer: 2000
                });
                billingTable.ajax.reload(null, false);
            },
            error: function (xhr) {
                resetBtnLoading($btn);
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to save billing charge.";
                Swal.fire({ title: "Error", text: msg, icon: "error" });
            }
        });
    });

    // Detailed Comment: Open Edit Billing Charge Modal
    $(document).on("click", ".edit-bill-btn", function () {
        const $this = $(this);
        $("#edit_billing_id").val($this.data("id"));
        $("#edit_consultationrefno").val($this.data("ref"));
        $("#edit_pxname").val($this.data("px"));
        $("#edit_servicename").val($this.data("service"));
        $("#edit_group_category").val($this.data("category"));
        $("#edit_payment_type").val($this.data("payment"));
        $("#edit_quantity").val($this.data("qty"));
        $("#edit_retail").val($this.data("retail"));
        $("#edit_total").val($this.data("total"));
        $("#edit_discount").val($this.data("discount"));
        $("#edit_net_total").val($this.data("net"));

        new bootstrap.Modal("#edit_billing_modal").show();
    });

    // Detailed Comment: Save Updated Billing Charge with button loader
    $("#edit_billing_form").on("submit", function (e) {
        e.preventDefault();
        const $btn = $("#btn_save_edit_billing");
        setBtnLoading($btn, "Updating...");

        $.ajax({
            url: "/api/admin/edit_billing",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: $(this).serialize(),
            success: function () {
                resetBtnLoading($btn);
                bootstrap.Modal.getInstance("#edit_billing_modal").hide();
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Billing charge updated successfully!',
                    showConfirmButton: false,
                    timer: 2000
                });
                billingTable.ajax.reload(null, false);
            },
            error: function (xhr) {
                resetBtnLoading($btn);
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to update charge.";
                Swal.fire({ title: "Error", text: msg, icon: "error" });
            }
        });
    });

    // Detailed Comment: Delete Billing Charge with confirmation dialog and loading animation
    $(document).on("click", ".delete-bill-btn", function () {
        const id = $(this).val();
        const desc = $(this).data("desc");

        Swal.fire({
            title: "Confirm Deletion",
            text: `Are you sure you want to delete charge "${desc}"?`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete",
            confirmButtonColor: "#d33"
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: "Deleting...",
                    text: "Please wait while removing billing charge.",
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: "/api/admin/delete_billing",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: { id: id },
                    success: function () {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Billing charge removed successfully!',
                            showConfirmButton: false,
                            timer: 2000
                        });
                        billingTable.ajax.reload(null, false);
                    },
                    error: function (xhr) {
                        const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to delete charge.";
                        Swal.fire({ title: "Error", text: msg, icon: "error" });
                    }
                });
            }
        });
    });
});
