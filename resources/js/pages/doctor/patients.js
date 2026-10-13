import { initAddressCascade } from '../../helpers/address-cascade.js';

$(function () {
    /**
     * Detailed Comment: Initialize PSGC address cascading helpers for Add and Edit Patient modals.
     */
    const addPatientAddressCascade = initAddressCascade({
        regionSel: '#region',
        provSel: '#province',
        munSel: '#muncity',
        brgySel: '#brgy',
        zipInput: '#zipcode',
        streetInput: '#streetadrs',
        fullAddressInput: '#address'
    });

    const editPatientAddressCascade = initAddressCascade({
        regionSel: '#edit_region',
        provSel: '#edit_province',
        munSel: '#edit_muncity',
        brgySel: '#edit_brgy',
        zipInput: '#edit_zipcode',
        streetInput: '#edit_streetadrs',
        fullAddressInput: '#edit_address'
    });

    /**
     * Detailed Comment: Button loading state helper functions.
     * Preserves original inner HTML in data attribute and renders a Bootstrap spinner.
     */
    function setBtnLoading($btn, loadingText = "") {
        if (!$btn || $btn.length === 0) return;
        const originalHtml = $btn.html();
        $btn.data('original-html', originalHtml).prop('disabled', true);
        const text = loadingText ? ` ${loadingText}` : '';
        $btn.html(`<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>${text}`);
    }

    function resetBtnLoading($btn) {
        if (!$btn || $btn.length === 0) return;
        const originalHtml = $btn.data('original-html');
        if (originalHtml) {
            $btn.html(originalHtml);
        }
        $btn.prop('disabled', false);
    }

    const isAdmin = $("#auth_is_admin").val() === "1";

    loadMasterlist();

    /**
     * Detailed Comment: Initialize Patient Masterlist DataTable.
     * Queries /api/fetch_consultation_masterlist (DoctorController::fetchAllPatients),
     * displaying all pxmasterlist records without duplicates, and attaches role-aware action buttons.
     */
    function loadMasterlist() {
        if ($.fn.DataTable.isDataTable("#masterlist_table")) {
            $("#masterlist_table").DataTable().clear().destroy();
        }

        $("#masterlist_table").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "/api/fetch_consultation_masterlist",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr('content') },
            },
            columns: [
                {
                    data: null,
                    render: function (data) {
                        let btns = `
                            <div class="d-flex gap-1 justify-content-center">
                                <!-- Detailed Comment: Removed direct Details button per R6 specification; masterlist rows are pxmasterlist records without default single consultation context. Consultation details are accessed via Consultation History modal -->
                                <!-- Detailed Comment: View Patient details action trigger modal -->
                                <button type="button" class="btn btn-sm btn-info text-white masterlist-view" value="${data.pxrefno || ''}" data-pincode="${data.pincode || ''}" title="View Patient Details">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                                <!-- Detailed Comment: Consultation History trigger modal -->
                                <button type="button" class="btn btn-sm btn-secondary masterlist-history" value="${data.pincode || ''}" data-pxrefno="${data.pxrefno || ''}" data-consultationrefno="${data.consultationrefno || ''}" title="Consultation History">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </button>`;

                        if (isAdmin) {
                            btns += `
                                <button type="button" class="btn btn-sm btn-primary masterlist-edit" value="${data.pxrefno || ''}" title="Edit Patient Details">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger masterlist-delete" value="${data.pxrefno || ''}" data-name="${data.patientname || ''}" title="Delete Patient Record">
                                    <i class="fa-solid fa-trash"></i>
                                </button>`;
                        }

                        btns += `</div>`;
                        return btns;
                    }
                },
                {
                    data: null,
                    render: function (data) {
                        return `<div class="text-center"><img src="${data.photo_path || '/images/blank_photo.png'}" class="rounded rounded-circle border shadow-sm" style="width: 40px; height: 40px; object-fit: cover;" alt="patient_photo" onerror="this.src='/images/blank_photo.png'"></div>`;
                    }
                },
                {
                    data: null,
                    render: function (data) {
                        const nameParts = [data.pxlastname, data.pxfirstname, data.pxmidname, data.pxsuffix].filter(Boolean);
                        const displayName = nameParts.length > 0 ? nameParts.join(', ').toUpperCase() : (data.patientname || 'N/A');
                        return `<span class="fw-bold text-dark">${displayName}</span>`;
                    }
                },
                {
                    data: null,
                    render: function (data) {
                        return `<div><span class="fw-semibold text-primary">${data.pincode || 'N/A'}</span><br><small class="text-muted">${data.pxrefno || ''}</small></div>`;
                    }
                },
                {
                    data: 'mobilenumber',
                    render: function (data) {
                        return data ? data : `<span class="text-muted">N/A</span>`;
                    }
                },
                {
                    data: 'emailaddress',
                    render: function (data) {
                        return data ? data : `<span class="text-muted">N/A</span>`;
                    }
                },
                {
                    data: 'latest_consult_date',
                    defaultContent: '',
                    render: function (data, type, row) {
                        const dateVal = data || row.consultation_date || row.last_consultation;
                        if (!dateVal || dateVal === "1901-01-01 00:00:00") {
                            return `<div class="text-center"><span class="badge bg-secondary">No visit yet</span></div>`;
                        }
                        try {
                            const formatted = new Date(dateVal.replace(" ", "T")).toLocaleDateString("en-US", { month: "short", day: "2-digit", year: "numeric" });
                            return `<div class="text-center"><span class="badge bg-info text-white">${formatted}</span></div>`;
                        } catch (e) {
                            return `<div class="text-center">${dateVal}</div>`;
                        }
                    }
                }
            ],
            columnDefs: [
                { targets: [0, 1, 6], orderable: false, searchable: false, className: 'text-nowrap text-center align-middle' },
                { targets: [2, 3, 4, 5], className: 'text-nowrap align-middle' }
            ],
            order: [[2, 'asc']],
            language: { emptyTable: "No patient records registered in masterlist yet." },
            pageLength: 10,
            lengthChange: false,
            paging: true,
            searching: true,
            ordering: true,
            responsive: true
        });
    }

    /**
     * Detailed Comment: View Patient Details modal trigger.
     * Queries /api/fetch_patient_details and populates all 3 tabs of #viewPatientModal with spinner feedback.
     */
    $(document).on("click", ".masterlist-view", function () {
        const $btn = $(this);
        const pxrefno = $btn.val();
        const pincode = $btn.data("pincode");

        setBtnLoading($btn, "");

        $.ajax({
            url: "/api/fetch_patient_details",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { pxrefno: pxrefno, pincode: pincode },
            success: function (response) {
                if (response.success && response.patient) {
                    const p = response.patient;

                    // Header Info
                    $("#view_patient_header_name").text(p.patientname || [p.pxfirstname, p.pxmidname, p.pxlastname].filter(Boolean).join(' '));
                    $("#view_patient_header_pin").text(p.pincode || 'N/A');
                    $("#view_patient_header_ref").text(p.pxrefno || 'N/A');
                    $("#view_patient_photo").attr("src", response.photo_url || '/images/blank_photo.png');

                    // Tab 1: Personal & Identity
                    $("#view_pincode").val(p.pincode || '');
                    $("#view_pxrefno").val(p.pxrefno || '');
                    $("#view_phic_pin").val(p.phic_pin || 'N/A');
                    $("#view_ipd_pincode").val(p.ipd_pincode || 'N/A');
                    $("#view_pxfirstname").val(p.pxfirstname || '');
                    $("#view_pxmidname").val(p.pxmidname || '');
                    $("#view_pxlastname").val(p.pxlastname || '');
                    $("#view_pxsuffix").val(p.pxsuffix || '');
                    $("#view_gender").val(p.gender || 'N/A');
                    $("#view_birthday").val(p.birthday || 'N/A');
                    $("#view_age").val(p.age || '0');
                    $("#view_religion").val(p.religion || 'N/A');
                    $("#view_nationality").val(p.nationality || 'FILIPINO');
                    $("#view_ispwd").val(p.ispwd == 1 ? 'Yes (PWD)' : 'No');
                    $("#view_senior_idno").val(p.senior_idno || 'N/A');
                    $("#view_classification").val(p.classification || 'REGULAR');

                    // Tab 2: Contact & Address
                    $("#view_mobilenumber").val(p.mobilenumber || 'N/A');
                    $("#view_emailaddress").val(p.emailaddress || 'N/A');
                    $("#view_address").val(p.address || 'N/A');
                    $("#view_streetadrs").val(p.streetadrs || 'N/A');
                    $("#view_brgy").val(p.brgy || 'N/A');
                    $("#view_muncity").val(p.muncity || 'N/A');
                    $("#view_province").val(p.province || 'N/A');
                    $("#view_zipcode").val(p.zipcode || 'N/A');
                    $("#view_region").val(p.region || 'N/A');
                    $("#view_country").val(p.country || 'PHILIPPINES');

                    // Tab 3: Clinic & Audit
                    $("#view_last_consultation").val(p.last_consultation || 'N/A');
                    $("#view_last_docname").val(p.last_docname || 'N/A');
                    $("#view_followupdate").val(p.followupdate || 'N/A');
                    $("#view_followupcheckup").val(p.followupcheckup || 'N/A');
                    $("#view_recordedby").val(p.recordedby || 'N/A');
                    $("#view_recordeddate").val(p.recordeddate || 'N/A');
                    $("#view_updatedby").val(p.updatedby || 'N/A');
                    $("#view_updated").val(p.updated || 'N/A');

                    // Detailed Comment: Tab 4 - Medical History Loader for Patient Details Modal with View action button
                    const $histTbody = $("#view_medhistory_table tbody");
                    $histTbody.html('<tr><td colspan="7" class="text-center py-2"><div class="spinner-border spinner-border-sm text-primary me-1"></div> Loading medical history...</td></tr>');
                    $("#view_medhistory_count").text("Loading...");

                    $.ajax({
                        url: "/api/fetch_patient_medhistory",
                        type: "POST",
                        headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                        data: { pxrefno: p.pxrefno, pincode: p.pincode },
                        success: function (histRes) {
                            $histTbody.empty();
                            const history = histRes.history || histRes.medhistory || [];
                            window._docViewMedhistoryRecords = history;
                            $("#view_medhistory_count").text(`${history.length} records`);

                            if (history.length === 0) {
                                $histTbody.append('<tr><td colspan="7" class="text-center text-muted py-3">No consultation history records found.</td></tr>');
                                return;
                            }

                            const badges = {
                                WAITING: "bg-warning text-white",
                                IN_CONSULTATION: "bg-info text-white",
                                FOR_BILLING: "bg-primary text-white",
                                COMPLETED: "bg-success text-white",
                                UNSCHEDULED: "bg-secondary text-white",
                                CANCELLED: "bg-danger text-white",
                                NO_SHOW: "bg-danger text-white"
                            };

                            history.forEach((item, idx) => {
                                const photo = item.photo_path || '/images/blank_photo.png';
                                const dateStr = item.consultation_date ? item.consultation_date.substring(0, 16) : 'N/A';
                                const reason = item.reasonforconsultation || 'No chief complaint recorded.';
                                const status = item.status || 'N/A';
                                const recordedby = item.recordedby || 'N/A';
                                const recordeddate = item.recordeddate ? item.recordeddate.substring(0, 10) : 'N/A';
                                const badgeClass = badges[status] || "bg-secondary text-white";

                                $histTbody.append(`
                                    <tr>
                                        <td class="text-center align-middle">
                                            <button type="button" class="btn btn-sm btn-primary view-consultation-details-btn btn-view-details" data-source="view_medhistory" data-index="${idx}" data-consultationrefno="${item.consultationrefno || ''}" title="View Consultation Details">
                                                <i class="fa-solid fa-eye me-1"></i> Details
                                            </button>
                                        </td>
                                        <td class="text-center align-middle">
                                            <img src="${photo}" alt="patient" class="rounded rounded-circle border shadow-sm" style="width: 36px; height: 36px; object-fit: cover;" onerror="this.src='/images/blank_photo.png'">
                                        </td>
                                        <td class="fw-semibold text-nowrap align-middle">${dateStr}</td>
                                        <td class="align-middle">${reason}</td>
                                        <td class="align-middle text-center"><span class="badge ${badgeClass}">${status}</span></td>
                                        <td class="text-nowrap align-middle">${recordedby}</td>
                                        <td class="text-nowrap align-middle">${recordeddate}</td>
                                    </tr>
                                `);
                            });
                        },
                        error: function () {
                            $histTbody.html('<tr><td colspan="7" class="text-center text-danger py-3">Failed to load medical history.</td></tr>');
                            $("#view_medhistory_count").text("0 records");
                        }
                    });

                    const modal = new bootstrap.Modal(document.getElementById("viewPatientModal"));
                    modal.show();
                } else {
                    Swal.fire({ title: "Error", text: response.message || "Failed to load patient details.", icon: "error" });
                }
            },
            error: function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to load patient details.";
                Swal.fire({ title: "Error", text: msg, icon: "error" });
            },
            complete: function () {
                resetBtnLoading($btn);
            }
        });
    });

    /**
     * Detailed Comment: Patient Consultation History modal handler.
     * Shows patient consultation history in #patientMedhistoryModal using #medhistorytable.
     */
    $(document).on("click", ".masterlist-history", function () {
        const $btn = $(this);
        const pincode = $btn.val();
        const pxrefno = $btn.data('pxrefno') || '';
        const consultationrefno = $btn.data('consultationrefno') || '';

        setBtnLoading($btn, "");

        $("#mpincode").val(pincode);
        $("#patientMedhistoryModal").data('pxrefno', pxrefno);
        $("#patientMedhistoryModal").data('consultationrefno', consultationrefno);

        if ($.fn.DataTable.isDataTable("#medhistorytable")) {
            $("#medhistorytable").DataTable().clear().destroy();
        }

        $("#medhistorytable").DataTable({
            processing: true,
            ajax: {
                url: "/api/fetch_patient_medhistory",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: {
                    pincode: pincode,
                    pxrefno: pxrefno,
                    consultationrefno: consultationrefno
                },
                dataSrc: function (json) {
                    const list = json.history || json.medhistory || json.data || [];
                    window._docMedhistoryRecords = list;
                    return list;
                }
            },
            columns: [
                {
                    data: null,
                    orderable: false,
                    className: "text-center align-middle text-nowrap",
                    render: function (data, type, row, meta) {
                        return `<button type="button" class="btn btn-sm btn-primary view-consultation-details-btn btn-view-details" data-source="medhistorytable" data-index="${meta.row}" data-consultationrefno="${row.consultationrefno || ''}" title="View Consultation Details">
                            <i class="fa-solid fa-eye me-1"></i> Details
                        </button>`;
                    }
                },
                {
                    data: 'photo_path',
                    render: function (data) {
                        return `<img src="${data || '/images/blank_photo.png'}" style="height: 50px; width: 50px; object-fit: cover;" class="rounded rounded-circle border shadow-sm" alt="patient_photo">`;
                    }
                },
                {
                    data: 'consultation_date',
                    render: function (data) {
                        if (!data || data === "1901-01-01 00:00:00") return '<span class="text-muted">N/A</span>';
                        try {
                            return new Date(data.replace(" ", "T")).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
                        } catch (e) {
                            return data;
                        }
                    }
                },
                { data: 'reasonforconsultation', defaultContent: '<span class="text-muted">N/A</span>' },
                {
                    data: 'status',
                    render: function (data) {
                        const badges = { WAITING: "bg-warning text-white", IN_CONSULTATION: "bg-info text-white", FOR_BILLING: "bg-primary text-white", COMPLETED: "bg-success text-white", UNSCHEDULED: "bg-primary text-white", CANCELLED: "bg-danger text-white", NO_SHOW: "bg-danger text-white" };
                        return `<span class="badge ${badges[data] || 'bg-secondary'}">${data || 'N/A'}</span>`;
                    }
                },
                { data: 'recordedby', defaultContent: '<span class="text-muted">N/A</span>' },
                {
                    data: 'recordeddate',
                    render: function (data) {
                        if (!data) return '<span class="text-muted">N/A</span>';
                        try {
                            return new Date(data.replace(" ", "T")).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
                        } catch (e) {
                            return data;
                        }
                    }
                }
            ],
            columnDefs: [
                { targets: [0, 1, 2, 4, 6], width: '1%', className: "text-nowrap text-center align-middle" },
                { targets: [3, 5], className: "align-middle" }
            ],
            language: { emptyTable: "No consultation history found for this patient." },
            paging: true,
            pageLength: 10,
            ordering: false
        });

        const medModal = new bootstrap.Modal(document.getElementById("patientMedhistoryModal"));
        medModal.show();
        resetBtnLoading($btn);
    });

    /**
     * Detailed Comment: Admin Edit Patient Modal trigger.
     * Loads patient record into form and displays #editPatientModal with loading feedback.
     */
    $(document).on("click", ".masterlist-edit", function () {
        const $btn = $(this);
        const pxrefno = $btn.val();

        setBtnLoading($btn, "");

        $.ajax({
            url: "/api/fetch_patient_details",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { pxrefno: pxrefno },
            success: function (response) {
                if (response.success && response.patient) {
                    const p = response.patient;
                    $("#edit_pxrefno").val(p.pxrefno);
                    $("#edit_display_pxrefno").val(p.pxrefno);
                    $("#edit_pincode").val(p.pincode);
                    $("#edit_phic_pin").val(p.phic_pin || '');
                    $("#edit_ipd_pincode").val(p.ipd_pincode || '');

                    $("#edit_pxfirstname").val(p.pxfirstname || '');
                    $("#edit_pxmidname").val(p.pxmidname || '');
                    $("#edit_pxlastname").val(p.pxlastname || '');
                    $("#edit_pxsuffix").val(p.pxsuffix || '');

                    $("#edit_gender").val(p.gender ? p.gender.toUpperCase() : 'MALE');
                    $("#edit_birthday").val(p.birthday ? p.birthday.split(' ')[0] : '');
                    $("#edit_religion").val(p.religion || '');
                    $("#edit_nationality").val(p.nationality || 'FILIPINO');

                    $("#edit_ispwd").val(p.ispwd == 1 ? '1' : '0');
                    $("#edit_senior_idno").val(p.senior_idno || '');

                    $("#edit_mobilenumber").val(p.mobilenumber || '');
                    $("#edit_emailaddress").val(p.emailaddress || '');
                    $("#edit_country").val(p.country || 'PHILIPPINES');

                    // Detailed Comment: Populate cascading PSGC address selects in edit patient modal
                    editPatientAddressCascade.setAddressValues({
                        region: p.region || '',
                        province: p.province || '',
                        muncity: p.muncity || '',
                        brgy: p.brgy || '',
                        streetadrs: p.streetadrs || '',
                        zipcode: p.zipcode || '',
                        address: p.address || ''
                    });

                    $("#edit_classification").val(p.classification || '');
                    $("#edit_followupdate").val(p.followupdate ? p.followupdate.split(' ')[0] : '');
                    $("#edit_followupcheckup").val(p.followupcheckup || '');

                    // Detailed Comment: Populate existing patient photo preview in edit modal
                    const photoSrc = response.photo_url || (p.photo_path ? `/patient/photo/${p.photo_path.split('/').pop()}` : '/images/blank_photo.png');
                    $("#edit_patient_picture_preview").prop("src", photoSrc);
                    $("#edit_photo_path").val(p.photo_path || '');
                    $("#edit_photo_base64").val('');
                    $("#edit_patient_image").val('');

                    const editModal = new bootstrap.Modal(document.getElementById("editPatientModal"));
                    editModal.show();
                } else {
                    Swal.fire({ title: "Error", text: response.message || "Failed to load patient record.", icon: "error" });
                }
            },
            error: function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to load patient record.";
                Swal.fire({ title: "Error", text: msg, icon: "error" });
            },
            complete: function () {
                resetBtnLoading($btn);
            }
        });
    });

    /**
     * Detailed Comment: Admin Save Edit Patient Form submission.
     * POSTs to /api/admin/update_patient with spinner feedback and reloads DataTable.
     */
    $("#editPatientForm").on("submit", function (e) {
        e.preventDefault();

        const form = this;
        if (!form.checkValidity()) {
            return form.reportValidity();
        }

        const $btn = $("#save_edit_patient_btn");
        setBtnLoading($btn, "Saving...");

        // Detailed Comment: Use FormData so binary patient image uploads and base64 payloads are sent reliably
        const formData = new FormData(form);

        $.ajax({
            url: "/api/admin/update_patient",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "success",
                        title: "Patient updated successfully!",
                        showConfirmButton: false,
                        timer: 1500
                    });

                    const modalEl = document.getElementById("editPatientModal");
                    if (modalEl) {
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                    }

                    $("#masterlist_table").DataTable().ajax.reload(null, false);
                } else {
                    Swal.fire({ title: "Error", text: response.message || "Failed to update patient.", icon: "error" });
                }
            },
            error: function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to update patient.";
                Swal.fire({ title: "Error", text: msg, icon: "error" });
            },
            complete: function () {
                resetBtnLoading($btn);
            }
        });
    });

    /**
     * Detailed Comment: Admin Delete Patient handler.
     * Displays confirmation prompt, removes patient from pxmasterlist via /api/admin/delete_patient,
     * while preserving medical audit records in pxwalkinconsultation.
     */
    $(document).on("click", ".masterlist-delete", function () {
        const $btn = $(this);
        const pxrefno = $btn.val();
        const patientName = $btn.data("name") || "this patient";

        Swal.fire({
            title: "Delete Patient?",
            html: `Are you sure you want to remove <strong>${patientName}</strong> from the patient masterlist?<br><br><small class="text-muted">Note: Historical consultation records will be preserved for medical audit and regulatory compliance.</small>`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete patient",
            confirmButtonColor: "#d33",
            cancelButtonText: "Cancel"
        }).then((result) => {
            if (result.isConfirmed) {
                setBtnLoading($btn, "");

                $.ajax({
                    url: "/api/admin/delete_patient",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: { pxrefno: pxrefno },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                toast: true,
                                position: "top-end",
                                icon: "success",
                                title: response.message || "Patient deleted successfully!",
                                showConfirmButton: false,
                                timer: 1500
                            });
                            $("#masterlist_table").DataTable().ajax.reload(null, false);
                        } else {
                            Swal.fire({ title: "Error", text: response.message || "Failed to delete patient.", icon: "error" });
                        }
                    },
                    error: function (xhr) {
                        const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to delete patient.";
                        Swal.fire({ title: "Error", text: msg, icon: "error" });
                    },
                    complete: function () {
                        resetBtnLoading($btn);
                    }
                });
            }
        });
    });

    /**
     * Detailed Comment: Add New Patient modal trigger and submission handler with loading spinner.
     */
    $("#add_patient_modal_btn").on("click", function () {
        const form = document.getElementById("add_patient_form");
        if (form) form.reset();
        addPatientAddressCascade.reset();
        // Detailed Comment: Reset photo preview and hidden fields when opening Add Patient modal
        $("#add_patient_picture_preview").prop("src", "/images/blank_photo.png");
        $("#add_photo_path").val("");
        $("#add_photo_base64").val("");
        $("#add_patient_image").val("");
        const modal = new bootstrap.Modal(document.getElementById("add_patient_modal"));
        modal.show();
    });

    $("#add_patient_btn").on("click", function () {
        const form = document.getElementById("add_patient_form");
        if (!form.checkValidity()) {
            return form.reportValidity();
        }

        const $btn = $(this);
        setBtnLoading($btn, "Saving...");

        // Detailed Comment: Use FormData to send photo binary/base64 along with demographic fields
        const formData = new FormData(form);

        $.ajax({
            url: "/api/add_patient",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'New patient registered successfully!',
                        showConfirmButton: false,
                        timer: 1500
                    });

                    const modalEl = document.getElementById("add_patient_modal");
                    if (modalEl) {
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                    }

                    form.reset();
                    $("#add_patient_picture_preview").prop("src", "/images/blank_photo.png");
                    $("#add_photo_path").val("");
                    $("#add_photo_base64").val("");
                    $("#masterlist_table").DataTable().ajax.reload(null, false);
                } else {
                    Swal.fire({ title: "Error", text: response.message || "Failed to register patient.", icon: "error" });
                }
            },
            error: function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to register patient.";
                Swal.fire({ title: "Error", text: msg, icon: "error" });
            },
            complete: function () {
                resetBtnLoading($btn);
            }
        });
    });

    // Detailed Comment: Patient Photo upload and webcam capture handlers for Add and Edit Modals
    let activeCameraTarget = 'add'; // 'add' or 'edit'
    let cameraStream = null;

    $("#add_upload_patient_image").on("click", function () {
        $("#add_patient_image").trigger("click");
    });
    $("#add_patient_image").on("change", function () {
        handleImageFileSelect(this, "#add_patient_picture_preview", "#add_photo_base64", "#add_photo_path");
    });

    $("#edit_upload_patient_image").on("click", function () {
        $("#edit_patient_image").trigger("click");
    });
    $("#edit_patient_image").on("change", function () {
        handleImageFileSelect(this, "#edit_patient_picture_preview", "#edit_photo_base64", "#edit_photo_path");
    });

    function handleImageFileSelect(input, previewEl, base64El, pathEl) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                $(previewEl).prop("src", e.target.result);
                $(base64El).val(e.target.result);
                $(pathEl).val("");
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    $("#add_take_photo").on("click", function () {
        activeCameraTarget = 'add';
        openCameraModal();
    });
    $("#edit_take_photo").on("click", function () {
        activeCameraTarget = 'edit';
        openCameraModal();
    });

    function openCameraModal() {
        const modalEl = document.getElementById("takePhotoModal");
        if (!modalEl) return;
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function (stream) {
                    cameraStream = stream;
                    const video = document.getElementById("camera_preview");
                    if (video) {
                        video.srcObject = stream;
                        video.play();
                    }
                })
                .catch(function (err) {
                    console.error("Camera access error:", err);
                    Swal.fire({
                        title: "Camera Access Error",
                        text: "Could not access camera. Please check device permissions.",
                        icon: "error"
                    });
                });
        }
    }

    $("#save_photo").on("click", function () {
        const video = document.getElementById("camera_preview");
        const canvas = document.getElementById("camera_canvas");
        if (!video || !canvas) return;

        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;
        const ctx = canvas.getContext("2d");
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        const dataUrl = canvas.toDataURL("image/png");

        if (activeCameraTarget === 'add') {
            $("#add_patient_picture_preview").prop("src", dataUrl);
            $("#add_photo_base64").val(dataUrl);
            $("#add_photo_path").val("");
            $("#add_patient_image").val("");
        } else {
            $("#edit_patient_picture_preview").prop("src", dataUrl);
            $("#edit_photo_base64").val(dataUrl);
            $("#edit_photo_path").val("");
            $("#edit_patient_image").val("");
        }

        stopCameraStream();
        const modalEl = document.getElementById("takePhotoModal");
        if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }
    });

    function stopCameraStream() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
            cameraStream = null;
        }
    }

    $("#takePhotoModal").on("hidden.bs.modal", function () {
        stopCameraStream();
    });

    // Detailed Comment: Self-service Doctor Profile modal lifecycle
    const modalEl = document.getElementById("doctor_profile_modal");
    if (modalEl) {
        modalEl.addEventListener("show.bs.modal", () => {
            $.ajax({
                url: "/api/fetch_doctor_data",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                success: function (response) {
                    if (response.success && response.user) {
                        const u = response.user;
                        $("#prof_docfname").val(u.docfname || '');
                        $("#prof_docmname").val(u.docmname || '');
                        $("#prof_doclname").val(u.doclname || '');
                        $("#prof_suffix").val(u.suffix || '');
                        $("#prof_titlename").val(u.titlename || 'MD');
                        $("#prof_username").val(u.username || '');
                        $("#prof_new_password").val('');
                        $("#prof_emailadd").val(u.emailadd || '');
                        $("#prof_cellno").val(u.cellno || '');
                        $("#prof_adrs").val(u.adrs || '');
                        $("#prof_licno").val(u.Licno || '');
                        $("#prof_licnoexpiry").val(u.licnoexpiry || '');
                        $("#prof_phicno").val(u.phicno || '');
                        $("#prof_phicexpiry").val(u.phicexpiry || '');
                        $("#prof_phicname").val(u.phicname || '');
                        $("#prof_phicrate").val(u.phicrate || 0);
                        $("#prof_phicenable").prop('checked', !!(u.phicenable == 1 || u.phicenable === true));
                        $("#prof_s2no").val(u.S2no || '');
                        $("#prof_ptr").val(u.PTR || '');
                        $("#prof_expertise").val(u.expertise || '');
                        $("#prof_proftype").val(u.proftype || 'ATTENDING');
                        $("#prof_department").val(u.department || '');
                        $("#prof_profgroup").val(u.profgroup || '');
                        $("#prof_catg").val(u.catg || '');
                        $("#prof_station").val(u.station || '');
                        $("#prof_groupname").val(u.groupname || '');
                        $("#prof_clinicroom").val(u.clinicroom || '');
                        $("#prof_clinichours").val(u.clinichours || '');
                        $("#prof_hospadrs").val(u.hospadrs || '');
                        $("#prof_consultationfee").val(u.consultationfee || 0);
                        $("#prof_emergencyfee").val(u.emergencyfee || 0);
                        $("#prof_admissionfee").val(u.admissionfee || 0);
                        $("#prof_tin").val(u.tin || '');
                        $("#prof_taxpercent").val(u.taxpercent || 0);
                        $("#prof_withholdingtax").val(u.withholdingtax || 0);
                        $("#prof_bankacct").val(u.bankacct || '');
                        $("#prof_slcode").val(u.slcode || '');
                        $("#prof_autoAddVAT").prop('checked', !!(u.autoAddVAT == 1 || u.autoAddVAT === true));
                        $("#prof_issuehospOR").prop('checked', !!(u.issuehospOR == 1 || u.issuehospOR === true));
                        $("#prof_quevisible").prop('checked', !!(u.quevisible == 1 || u.quevisible === true || u.quevisible === undefined));
                        $("#prof_allowtextresult").prop('checked', !!(u.allowtextresult == 1 || u.allowtextresult === true));
                        $("#prof_allowdocsystem").prop('checked', !!(u.allowdocsystem == 1 || u.allowdocsystem === true));
                        $("#prof_disabletext").prop('checked', !!(u.disabletext == 1 || u.disabletext === true));
                        $("#prof_otherinfo").val(u.otherinfo || '');
                        $("#prof_biodata").val(u.biodata || '');
                    }
                }
            });
        });

        $("#save_doctor_profile_btn").off("click").on("click", function () {
            const form = document.getElementById("doctor_profile_form");
            if (form && !form.checkValidity()) return form.reportValidity();

            const $btn = $(this);
            setBtnLoading($btn, "Saving...");

            $.ajax({
                url: "/api/doctor/update_profile",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: $("#doctor_profile_form").serialize(),
                success: function (response) {
                    if (response.success) {
                        Swal.fire({ title: "Success", text: "Doctor profile updated successfully.", icon: "success" });
                    } else {
                        Swal.fire({ title: "Error", text: response.message || "Failed to update profile.", icon: "error" });
                    }
                },
                error: function (xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to update profile.";
                    Swal.fire({ title: "Error", text: msg, icon: "error" });
                },
                complete: function () {
                    resetBtnLoading($btn);
                }
            });
        });
    }

    /**
     * Detailed Comment: Normalizes laboratory and radiology attachment paths into valid URLs.
     * Supports absolute HTTP/HTTPS links, storage routes, and maps relative paths to /preview-file/{path}.
     */
    function resolveAttachmentUrl(urlOrPath) {
        if (!urlOrPath) return null;
        if (urlOrPath.startsWith('http://') || urlOrPath.startsWith('https://')) {
            return urlOrPath;
        }
        const basePath = window.location.pathname.startsWith('/kayakapmd_clinic') ? '/kayakapmd_clinic' : '';
        if (urlOrPath.startsWith('/preview-file/')) {
            return `${basePath}${urlOrPath}`;
        }
        if (urlOrPath.startsWith('preview-file/')) {
            return `${basePath}/${urlOrPath}`;
        }
        const cleanPath = urlOrPath.replace(/^\/+/, '');
        return `${basePath}/preview-file/${cleanPath}`;
    }

    /**
     * Detailed Comment: Populate and display the comprehensive Consultation Details Modal
     * for doctor/admin users inspecting patient consultation histories from the Patients Masterlist.
     * Maps patient demographics, clinical vitals, Impressions & Diagnosis, Rx, Diagnostic requests,
     * Radiology/Lab files, and Patient Charges into #view_consultation_details_modal.
     */
    function openConsultationDetailsModal(record) {
        if (!record) return;

        // 1. Header Badges
        const dateStr = record.consultation_date ? record.consultation_date.substring(0, 16) : 'N/A';
        const cref = record.consultationrefno || record.caseno || 'N/A';
        const status = record.status || 'N/A';
        // Detailed Comment: Identify patient payment classification (PHIC YAKAP, HMO, OTHERS, REGULAR)
        const rawType = (record.modeofpayment || record.patient_type || (record.hmocode ? 'HMO' : (record.phic_pin ? 'PHIC YAKAP' : (record.classification === 'PHIC' ? 'PHIC YAKAP' : (record.classification || 'REGULAR'))))).toUpperCase();
        const isPhic = rawType.includes('PHIC') || Boolean(record.phic_pin);
        const ptype = isPhic ? 'PHIC YAKAP' : (rawType === 'HMO' ? 'HMO' : (rawType === 'OTHERS' ? 'OTHERS' : 'REGULAR'));

        const statusBadges = {
            WAITING: "bg-warning text-white",
            IN_CONSULTATION: "bg-info text-white",
            FOR_BILLING: "bg-primary text-white",
            COMPLETED: "bg-success text-white",
            UNSCHEDULED: "bg-secondary text-white",
            CANCELLED: "bg-danger text-white",
            NO_SHOW: "bg-danger text-white"
        };
        const typeBadges = {
            REGULAR: "bg-warning text-dark",
            'PHIC YAKAP': "bg-success text-white",
            PHIC: "bg-success text-white",
            HMO: "bg-info text-white",
            OTHERS: "bg-secondary text-white"
        };

        $("#vcd_consultdate_badge").text(`Date: ${dateStr}`);
        $("#vcd_consultref_badge").text(`Ref: ${cref}`);
        $("#vcd_status_badge")
            .text(`Status: ${status}`)
            .attr('class', `badge ${statusBadges[status] || 'bg-secondary text-white'} fs-6`);
        $("#vcd_patient_type_badge")
            .text(`Type: ${ptype}`)
            .attr('class', `badge ${typeBadges[ptype] || 'bg-secondary text-white'} fs-6`);

        // 2. Patient Demographics & Vitals
        $("#vcd_docname").text(record.docname ? `Dr. ${record.docname}` : (record.docfname ? `Dr. ${record.docfname} ${record.doclname || ''}` : 'None Assigned'));
        $("#vcd_photo").attr('src', record.photo_path || '/images/blank_photo.png');
        $("#vcd_name").text(record.patientname || [record.pxlastname, record.pxfirstname, record.pxmidname].filter(Boolean).join(', ') || 'N/A');
        $("#vcd_pincode").text(record.pincode || record.pin || 'N/A');
        $("#vcd_pxrefno").text(record.pxrefno || 'N/A');
        $("#vcd_sex").text(record.gender || record.sex || 'N/A');
        $("#vcd_bday").text(record.birthday || record.birthdate || 'N/A');
        $("#vcd_age").text(record.age ? `${record.age} yrs` : 'N/A');
        $("#vcd_cellno").text(record.mobilenumber || record.contactno || 'N/A');
        $("#vcd_address").text(record.address || 'N/A');

        $("#vcd_weight").text(record.weight ? `${record.weight} ${record.wunit || 'kg'}` : '--');
        $("#vcd_height").text(record.height ? `${record.height} ${record.hunit || 'cm'}` : '--');
        $("#vcd_temp").text(record.temp ? `${record.temp} ${record.tempunit || '°C'}` : '--');
        $("#vcd_resprate").text(record.respiratoryrate ? `${record.respiratoryrate} cpm` : '--');
        $("#vcd_pulserate").text(record.pulserate ? `${record.pulserate} bpm` : '--');
        const bp = (record.bpnumerator && record.bpdenominator) ? `${record.bpnumerator}/${record.bpdenominator} mmHg` : '--';
        $("#vcd_bp").text(bp);

        // 3. Tab 1: Impressions & Diagnosis
        $("#vcd_chief_complaint").val(record.reasonforconsultation || 'No chief complaint recorded.');
        $("#vcd_impressions").val(record.impression || 'No impressions recorded.');
        $("#vcd_diagnosis").val(record.finadiagnosis || record.diagnosis || 'No final diagnosis recorded.');

        // Detailed Comment: Standardized admission status check supporting both foradmit and isforadmission schema keys
        if (parseInt(record.foradmit || record.isforadmission) === 1) {
            $("#vcd_admit_badge").text('For Admission').removeClass('bg-secondary').addClass('bg-danger');
        } else {
            $("#vcd_admit_badge").text('Not for Admission').removeClass('bg-danger').addClass('bg-secondary');
        }
        $("#vcd_admit_instructions").val(record.foradmit_instructions || record.admisionnotes || record.instructions || '');

        // Detailed Comment: Answered Doctor's Questions rendering in Impressions & Diagnosis tab
        const renderAnsweredQuestions = (answers) => {
            let $qBox = $("#vcd_doctor_questions_box");
            if ($qBox.length === 0) {
                $("#vcd_admit_box").after(`
                    <div class="border rounded p-3 bg-light mt-2 d-none" id="vcd_doctor_questions_box">
                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-clipboard-question text-primary me-2"></i> Answered Doctor's Questions</h6>
                        <div id="vcd_answered_questions_list" class="d-flex flex-column gap-2"></div>
                    </div>
                `);
                $qBox = $("#vcd_doctor_questions_box");
            }
            if (answers && answers.length > 0) {
                let aHtml = '';
                answers.forEach(a => {
                    aHtml += `<div class="p-2 border rounded bg-white">
                        <strong class="d-block text-dark">${a.question || 'Doctor Question'}</strong>
                        <span class="text-secondary">${a.answer || 'No answer recorded'}</span>
                    </div>`;
                });
                $("#vcd_answered_questions_list").html(aHtml);
                $qBox.removeClass('d-none');
            } else {
                $("#vcd_answered_questions_list").empty();
                $qBox.addClass('d-none');
            }
        };

        if (record.answers && record.answers.length > 0) {
            renderAnsweredQuestions(record.answers);
        } else if (record.consultationrefno) {
            $.ajax({
                url: "/api/fetch_consultation",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: { consultationrefno: record.consultationrefno },
                success: function (res) {
                    if (res && res.answers) {
                        renderAnsweredQuestions(res.answers);
                    } else {
                        renderAnsweredQuestions([]);
                    }
                },
                error: function () {
                    renderAnsweredQuestions([]);
                }
            });
        } else {
            renderAnsweredQuestions([]);
        }

        // 4. Tab 2: Rx & Instructions
        const basePath = window.location.pathname.startsWith('/kayakapmd_clinic') ? '/kayakapmd_clinic' : '';
        if (record.consultationrefno) {
            $("#vcd_print_rx_btn").attr("href", `${basePath}/print_pdf?type=rx&consultationrefno=${record.consultationrefno}`).removeClass('disabled');
            $("#vcd_print_diag_btn").attr("href", `${basePath}/print_pdf?type=diagnostics&consultationrefno=${record.consultationrefno}`).removeClass('disabled');
        } else {
            $("#vcd_print_rx_btn, #vcd_print_diag_btn").attr("href", "#").addClass('disabled');
        }

        const $rxTbody = $("#vcd_rx_table tbody");
        $rxTbody.empty();
        const rxItems = record.rx_items || record.prescriptions || [];
        if (rxItems.length === 0) {
            $rxTbody.append('<tr><td colspan="4" class="text-center text-muted">No prescriptions recorded.</td></tr>');
        } else {
            rxItems.forEach(item => {
                const isDispensed = (item.dispensed == 1 || item.dispensed_flag == 1 || item.is_dispensed == 1);
                $rxTbody.append(`
                    <tr>
                        <td class="fw-semibold">${item.item_dscr || item.medname || item.med_name || 'N/A'}</td>
                        <td>${item.remarks || item.sig || item.dosage || 'N/A'}</td>
                        <td class="text-center">${item.qty || item.quantity || 1}</td>
                        <td class="text-center">${isDispensed ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>'}</td>
                    </tr>
                `);
            });
        }
        $("#vcd_general_instructions").val(record.instructions || '');

        // 5. Tab 3: Diagnostic Requests
        const $diagTbody = $("#vcd_diagnostics_table tbody");
        $diagTbody.empty();
        const diagItems = record.diagnostic_items || record.diagnostics || [];
        if (diagItems.length === 0) {
            $diagTbody.append('<tr><td colspan="3" class="text-center text-muted">No diagnostic requests recorded.</td></tr>');
        } else {
            diagItems.forEach(item => {
                const reqDate = item.requested_at || item.created_at || item.date_requested || item.diagnosticdate || '';
                const formattedDate = reqDate ? (reqDate.length > 16 ? reqDate.substring(0, 16) : reqDate) : 'N/A';
                const notes = item.notes || item.remarks || '';
                $diagTbody.append(`
                    <tr>
                        <td class="fw-semibold">
                            ${item.item_dscr || item.requestname || item.exam_name || 'N/A'}
                            ${notes ? `<br><small class="text-muted"><i class="fa-solid fa-info-circle me-1"></i>${notes}</small>` : ''}
                        </td>
                        <td><span class="badge bg-info text-white">${item.item_grouping || item.category || 'DIAGNOSTIC'}</span></td>
                        <td><small class="text-muted">${formattedDate}</small></td>
                    </tr>
                `);
            });
        }

        // 6. Tab 4: Radiology & Laboratory
        const rawRad = record.radiology_url || record.radiologypath;
        const radUrl = resolveAttachmentUrl(rawRad);
        if (radUrl) {
            $("#vcd_rad_status").html('<span class="text-success"><i class="fa-solid fa-check-circle me-1"></i> Document attached</span>');
            $("#vcd_rad_preview_btn").attr('href', radUrl).removeClass('d-none');
        } else {
            $("#vcd_rad_status").text('No radiology file uploaded.');
            $("#vcd_rad_preview_btn").attr('href', '#').addClass('d-none');
        }

        const rawLab = record.laboratory_url || record.laboratorypath;
        const labUrl = resolveAttachmentUrl(rawLab);
        if (labUrl) {
            $("#vcd_lab_status").html('<span class="text-success"><i class="fa-solid fa-check-circle me-1"></i> Document attached</span>');
            $("#vcd_lab_preview_btn").attr('href', labUrl).removeClass('d-none');
        } else {
            $("#vcd_lab_status").text('No laboratory file uploaded.');
            $("#vcd_lab_preview_btn").attr('href', '#').addClass('d-none');
        }

        // 7. Tab 5: Patient Charges
        const $chargesTbody = $("#vcd_charges_table tbody");
        $chargesTbody.empty();
        const charges = record.charges || [];
        let totalAmt = 0;

        if (charges.length === 0) {
            $chargesTbody.append('<tr><td colspan="5" class="text-center text-muted">No charges recorded.</td></tr>');
        } else {
            charges.forEach(c => {
                const qty = parseFloat(c.qty || c.quantity) || 1;
                const unitPrice = parseFloat(c.cost_ave || c.retails || c.unit_price || 0);
                const subTotal = parseFloat(c.totalamt || c.total_amt || (qty * unitPrice));
                totalAmt += subTotal;

                $chargesTbody.append(`
                    <tr>
                        <td class="fw-semibold">${c.item_dscr || c.item_name || 'N/A'}</td>
                        <td><span class="badge bg-secondary">${c.item_grouping || c.category || 'CHARGE'}</span></td>
                        <td class="text-center">${qty}</td>
                        <td class="text-end">PHP ${unitPrice.toFixed(2)}</td>
                        <td class="text-end fw-bold">PHP ${subTotal.toFixed(2)}</td>
                    </tr>
                `);
            });
        }
        if (totalAmt === 0 && record.charges_total) {
            totalAmt = parseFloat(record.charges_total) || 0;
        }
        $("#vcd_charges_total").text(totalAmt.toFixed(2));

        // Detailed Comment: Tab 6: Patient Permanent Medical History population in Patients page details modal
        const populateMedhistoryFields = (med) => {
            med = med || {};
            $("#vcd_allergies").text(med.allergies || record.allergies || "None recorded");
            $("#vcd_injections").text(med.injections_immunization || med.injections || record.injections_immunization || record.injections || "None recorded");
            $("#vcd_maintenance_meds").text(med.maintenance_medications || med.maintenance_meds || record.maintenance_medications || record.maintenance_meds || "None recorded");
            $("#vcd_past_medical_history").text(med.past_medical_history || record.past_medical_history || "None recorded");
            $("#vcd_surgical_history").text(med.surgical_history || record.surgical_history || "None recorded");
            $("#vcd_family_history").text(med.family_history || record.family_history || "None recorded");
            $("#vcd_clinical_notes").text(med.notes || med.clinical_notes || record.notes || record.clinical_notes || "None recorded");
        };

        if (record.permanent_medhistory) {
            populateMedhistoryFields(record.permanent_medhistory);
        } else if (record.allergies || record.past_medical_history || record.surgical_history) {
            populateMedhistoryFields(record);
        } else if (record.pincode || record.pxrefno || record.consultationrefno) {
            $.ajax({
                url: "/api/fetch_patient_medical_history",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: {
                    pincode: record.pincode || record.pin || '',
                    pxrefno: record.pxrefno || '',
                    consultationrefno: record.consultationrefno || ''
                },
                success: function (res) {
                    if (res && res.success && res.data) {
                        populateMedhistoryFields(res.data);
                    } else {
                        populateMedhistoryFields({});
                    }
                },
                error: function () {
                    populateMedhistoryFields({});
                }
            });
        } else {
            populateMedhistoryFields({});
        }

        // Activate first vertical tab (Impressions & Diagnosis)
        const firstTab = document.querySelector('button[data-bs-target="#vcd_pane_impdiag"]');
        if (firstTab && window.bootstrap && window.bootstrap.Tab) {
            window.bootstrap.Tab.getOrCreateInstance(firstTab).show();
        }

        // Show modal
        const modalEl = document.getElementById("view_consultation_details_modal");
        if (modalEl && window.bootstrap && window.bootstrap.Modal) {
            const modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    }
    window.openConsultationDetailsModal = openConsultationDetailsModal;
    window.populateAndShowConsultationDetails = openConsultationDetailsModal;

    // Detailed Comment: Delegate click handler to open the consultation details viewer modal in Patients page
    // Supports both .view-consultation-details-btn and .btn-view-details trigger selectors
    $(document).on("click", ".view-consultation-details-btn, .btn-view-details", function () {
        const idx = $(this).data("index");
        const source = $(this).data("source");
        const refno = $(this).data("consultationrefno") || $(this).attr("data-consultationrefno") || $(this).val();
        const pincode = $(this).data("pincode");
        const pxrefno = $(this).data("pxrefno");

        let record = null;
        if (source === "view_medhistory" && window._docViewMedhistoryRecords && window._docViewMedhistoryRecords[idx]) {
            record = window._docViewMedhistoryRecords[idx];
        } else if (source === "medhistorytable" && window._docMedhistoryRecords && window._docMedhistoryRecords[idx]) {
            record = window._docMedhistoryRecords[idx];
        } else if (window._docMedhistoryRecords && window._docMedhistoryRecords[idx]) {
            record = window._docMedhistoryRecords[idx];
        } else if (window._docViewMedhistoryRecords && window._docViewMedhistoryRecords[idx]) {
            record = window._docViewMedhistoryRecords[idx];
        }

        if (record) {
            openConsultationDetailsModal(record);
        } else if (refno || pincode || pxrefno) {
            $.ajax({
                url: "/api/fetch_patient_medhistory",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: { consultationrefno: refno, pincode: pincode, pxrefno: pxrefno },
                success: function (res) {
                    const list = res.medhistory || res.history || res.data || [];
                    if (list.length > 0) {
                        // Detailed Comment: Exact consultation matching by consultationrefno to prevent naive list[0] bias
                        const matched = (refno ? list.find(item => item.consultationrefno === refno) : null) || list[0];
                        openConsultationDetailsModal(matched);
                    }
                }
            });
        }
    });

    /**
     * Detailed Comment: Elevation of nested modal backdrop z-index.
     * When #view_consultation_details_modal is stacked over another open modal
     * (such as #patientMedhistoryModal or #viewPatientModal),
     * elevate the top backdrop to z-index 1065 so it sits between parent and child modal.
     */
    $(document).on("show.bs.modal shown.bs.modal", "#view_consultation_details_modal", function () {
        const $otherModals = $(".modal.show").not("#view_consultation_details_modal");
        if ($otherModals.length > 0) {
            $(".modal-backdrop").last().css("z-index", 1065);
        }
    });

    /**
     * Detailed Comment: Robust 3-step nested modal dismissal & backdrop cleanup handler.
     * Step a: Restore body modal-open class and overflow lock if any parent modal is still visible.
     * Step b: Ensure visible parent modal maintains overflow-y: auto and focus for smooth scrolling.
     * Step c: Prune any orphaned .modal-backdrop elements if backdrop count exceeds visible modal count.
     */
    $(document).on("hidden.bs.modal", "#view_consultation_details_modal", function () {
        const $remainingModals = $(".modal.show");
        if ($remainingModals.length > 0) {
            // Step a: Restore body modal-open
            $("body").addClass("modal-open").css("overflow", "hidden");

            // Step b: Restore parent modal scrollability
            const $topModal = $remainingModals.last();
            $topModal.css("overflow-y", "auto").focus();

            // Step c: Prune excess orphan backdrops
            const $backdrops = $(".modal-backdrop");
            if ($backdrops.length > $remainingModals.length) {
                $backdrops.slice($remainingModals.length).remove();
            }
            $(".modal-backdrop").last().css("z-index", 1050);
        } else {
            $(".modal-backdrop").remove();
            $("body").removeClass("modal-open").css({ "overflow": "", "padding-right": "" });
        }
    });

    /**
     * Detailed Comment: Global safety catch-all for any modal dismissal.
     * Cleans up all orphaned backdrops and restores body styling when zero modals remain open.
     */
    $(document).on("hidden.bs.modal", ".modal", function () {
        if ($(".modal.show").length === 0) {
            $(".modal-backdrop").remove();
            $("body").removeClass("modal-open").css({ "overflow": "", "padding-right": "" });
        }
    });
});
