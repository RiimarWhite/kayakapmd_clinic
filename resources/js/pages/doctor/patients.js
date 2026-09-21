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
                                <button type="button" class="btn btn-sm btn-info text-white masterlist-view" value="${data.pxrefno || ''}" data-pincode="${data.pincode || ''}" title="View Patient Details">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
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
                        return `<div class="text-center"><img src="${data.photo_path || '/images/blank_photo.png'}" class="rounded rounded-circle border shadow-sm" style="width: 40px; height: 40px; object-fit: cover;" alt="patient_photo"></div>`;
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
                dataSrc: 'history'
            },
            columns: [
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
                { targets: [0, 1, 3, 5], width: '1%', className: "text-nowrap text-center align-middle" },
                { targets: [2, 4], className: "align-middle" }
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

        $.ajax({
            url: "/api/admin/update_patient",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: $(form).serialize(),
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

        $.ajax({
            url: "/api/add_patient",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: $(form).serialize(),
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
});
