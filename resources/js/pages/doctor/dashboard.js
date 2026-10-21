$(function () {
    // Load today's patients table
    $("#todays_patients_table").DataTable().clear().destroy();
    $("#todays_patients_table").DataTable({
        ajax: {
            url: "/api/fetch_todays_patients",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $("meta[name='csrf-token']").attr('content') },
            dataSrc: function (json) {
                $("#patient_count").text("Count: " + json.count);
                window._dashboardPatients = json.patients || [];
                return json.patients;
            }
        },
        columns: [
            { data: 'queueno' },
            { data: null, render: function (data) { return `${[data.patientname, data.pxmidname, data.pxlastname, data.pxsuffix].filter(v => v).join(' ')}`; } },
            { data: 'status', render: function (data) {
                let b = 'bg-secondary fs-6';
                if (data === 'WAITING') b = 'bg-warning text-white text-dark fs-6';
                if (data === 'IN_CONSULTATION') b = 'bg-info text-white fs-6';
                if (data === 'COMPLETED') b = 'bg-success fs-6';
                if (data === 'UNSCHEDULED') b = 'bg-primary fs-6';
                if (data === 'CANCELLED' || data === 'NO_SHOW') b = 'bg-danger fs-6';
                return `<span class="badge ${b}">${data}</span>`;
            }},
            {
                data: null,
                orderable: false,
                className: "text-center align-middle text-nowrap",
                render: function (data, type, row, meta) {
                    return `<button type="button" class="btn btn-sm btn-primary view-consultation-details-btn btn-view-details" data-index="${meta.row}" data-consultationrefno="${row.consultationrefno || ''}" title="View Consultation Details">
                        <i class="fa-solid fa-eye me-1"></i> Details
                    </button>`;
                }
            }
        ],
        columnDefs: [
            { target: 0, width: '1%', orderable: false, searchable: false, className: 'text-nowrap fw-bold text-center align-middle' },
            { target: 2, width: '1%', className: 'text-nowrap overflow-hidden align-middle text-center' },
            { target: 3, width: '1%', className: 'text-nowrap text-center align-middle' }
        ],
        language: { emptyTable: "No patients yet." },
        pageLength: 30, lengthChange: false, paging: true, searching: false, ordering: false, responsive: true
    });

    // Detailed Comment: Helper functions for button loading spinner state
    function setBtnLoading($btn, text) {
        $btn.data('orig-text', $btn.html());
        $btn.prop('disabled', true).html(`<span class="spinner-border spinner-border-sm me-1" role="status"></span>${text}`);
    }
    function resetBtnLoading($btn) {
        $btn.prop('disabled', false).html($btn.data('orig-text') || '');
    }

    // Detailed Comment: Load doctor schedules from backend and populate calendar table
    function loadDoctorSchedules() {
        $.ajax({
            url: "/api/fetch_doctor_schedules_dashboard",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                renderSchedules(response.schedules || []);
            },
            error: function () {
                $("#schedules_calendar tbody").html('<tr><td colspan="2" class="text-danger text-center py-2">Failed to load schedules.</td></tr>');
            }
        });
    }

    loadDoctorSchedules();

    // Detailed Comment: Render schedules grouped by day with 12-hour formatted time and Edit/Delete action buttons
    function renderSchedules(schedules) {
        const body = $("#schedules_calendar tbody");
        body.empty();

        if (!schedules || schedules.length === 0) {
            body.html('<tr><td colspan="2" class="text-muted text-center py-3"><i class="fa-solid fa-calendar-xmark me-1"></i> No clinic schedules set yet. Click <strong>Add Schedule</strong> above to set your hours.</td></tr>');
            return;
        }

        const daysOrder = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
        const grouped = schedules.reduce((acc, s) => {
            if (!acc[s.day]) acc[s.day] = [];
            acc[s.day].push(s);
            return acc;
        }, {});

        daysOrder.forEach(day => {
            if (grouped[day] && grouped[day].length > 0) {
                body.append(`<tr class="table-success"><td colspan="2"><strong>${day}</strong></td></tr>`);
                grouped[day].forEach(sched => {
                    const row = $(`
                        <tr class="align-middle schedule-row" data-id="${sched.schedrefno}">
                            <td class="ps-3">
                                <strong><i class="fa-regular fa-clock me-1 text-success"></i> ${convert24To12(sched.start)} - ${convert24To12(sched.end)}</strong>
                            </td>
                            <td style="width: 1%;" class="text-nowrap text-end pe-2">
                                <button class="btn btn-sm btn-outline-primary edit-doctor-schedule me-1" value="${sched.schedrefno}" title="Edit Schedule">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-doctor-schedule" value="${sched.schedrefno}" title="Delete Schedule">
                                    <i class="fa-solid fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    `);
                    body.append(row);
                });
            }
        });
    }

    function convert24To12(time) {
        if (!time) return '';
        const parts = time.split(":");
        const h = parseInt(parts[0], 10);
        const minute = parts[1] || '00';
        return `${h % 12 || 12}:${minute} ${h >= 12 ? 'PM' : 'AM'}`;
    }

    // Detailed Comment: Open Add Schedule modal
    $("#add_doctor_schedule_btn").on("click", function () {
        $("#doctor_add_schedule_form")[0].reset();
        new bootstrap.Modal(document.getElementById("doctor_add_schedule_modal")).show();
    });

    // Detailed Comment: Submit new schedule for authenticated doctor
    $("#save_doctor_schedule_btn").on("click", function () {
        const form = document.getElementById("doctor_add_schedule_form");
        if (!form.checkValidity()) return form.reportValidity();

        const start = $("#add_sched_start").val();
        const end = $("#add_sched_end").val();
        if (start >= end) {
            return Swal.fire({ title: "Invalid Time Range", text: "Start time must be earlier than end time.", icon: "warning" });
        }

        const $btn = $(this);
        setBtnLoading($btn, "Saving...");

        $.ajax({
            url: "/api/doctor/create_schedule",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: $("#doctor_add_schedule_form").serialize(),
            success: function (response) {
                if (response.success) {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Schedule created successfully!', showConfirmButton: false, timer: 1500 });
                    bootstrap.Modal.getInstance(document.getElementById("doctor_add_schedule_modal")).hide();
                    loadDoctorSchedules();
                } else {
                    Swal.fire({ title: "Error", text: response.message || "Failed to create schedule.", icon: "error" });
                }
            },
            error: function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to create schedule.";
                Swal.fire({ title: "Error", text: msg, icon: "error" });
            },
            complete: function () {
                resetBtnLoading($btn);
            }
        });
    });

    // Detailed Comment: Open Edit Schedule modal and populate with existing schedule details
    $(document).on("click", ".edit-doctor-schedule", function () {
        const schedrefno = $(this).val();
        const $btn = $(this);
        setBtnLoading($btn, "");

        $.ajax({
            url: "/api/doctor/fetch_schedule_refno",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { schedrefno: schedrefno },
            success: function (response) {
                if (response.success && response.sched) {
                    const s = response.sched;
                    $("#edit_sched_refno").val(s.schedrefno);
                    $("#edit_sched_day").val(s.day);
                    $("#edit_sched_start").val(s.start ? s.start.substring(0, 5) : '');
                    $("#edit_sched_end").val(s.end ? s.end.substring(0, 5) : '');
                    new bootstrap.Modal(document.getElementById("doctor_edit_schedule_modal")).show();
                } else {
                    Swal.fire({ title: "Error", text: response.message || "Schedule details not found.", icon: "error" });
                }
            },
            error: function () {
                Swal.fire({ title: "Error", text: "Failed to retrieve schedule details.", icon: "error" });
            },
            complete: function () {
                resetBtnLoading($btn);
            }
        });
    });

    // Detailed Comment: Submit schedule update
    $("#update_doctor_schedule_btn").on("click", function () {
        const form = document.getElementById("doctor_edit_schedule_form");
        if (!form.checkValidity()) return form.reportValidity();

        const start = $("#edit_sched_start").val();
        const end = $("#edit_sched_end").val();
        if (start >= end) {
            return Swal.fire({ title: "Invalid Time Range", text: "Start time must be earlier than end time.", icon: "warning" });
        }

        const $btn = $(this);
        setBtnLoading($btn, "Updating...");

        $.ajax({
            url: "/api/doctor/edit_schedule",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: $("#doctor_edit_schedule_form").serialize(),
            success: function (response) {
                if (response.success) {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Schedule updated successfully!', showConfirmButton: false, timer: 1500 });
                    bootstrap.Modal.getInstance(document.getElementById("doctor_edit_schedule_modal")).hide();
                    loadDoctorSchedules();
                } else {
                    Swal.fire({ title: "Error", text: response.message || "Failed to update schedule.", icon: "error" });
                }
            },
            error: function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to update schedule.";
                Swal.fire({ title: "Error", text: msg, icon: "error" });
            },
            complete: function () {
                resetBtnLoading($btn);
            }
        });
    });

    // Detailed Comment: Delete clinic schedule with SweetAlert2 confirmation
    $(document).on("click", ".delete-doctor-schedule", function () {
        const schedrefno = $(this).val();

        Swal.fire({
            title: "Delete Schedule?",
            text: "Are you sure you want to delete this clinic schedule? Patients will no longer be booked into this slot.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Yes, Delete"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/api/doctor/delete_schedule",
                    type: "POST",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: { schedrefno: schedrefno },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Schedule deleted successfully.', showConfirmButton: false, timer: 1500 });
                            loadDoctorSchedules();
                        } else {
                            Swal.fire({ title: "Error", text: response.message || "Failed to delete schedule.", icon: "error" });
                        }
                    },
                    error: function (xhr) {
                        const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to delete schedule.";
                        Swal.fire({ title: "Error", text: msg, icon: "error" });
                    }
                });
            }
        });
    });

    // Detailed Comment: Self-service Doctor Profile modal lifecycle (populate 5 tabs and save updates)
    const modalEl = document.getElementById("doctor_profile_modal");
    if (modalEl) {
        modalEl.addEventListener("show.bs.modal", () => {
            $.ajax({
                url: "/api/fetch_doctor_data", type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                success: function (response) {
                    if (response.success && response.user) {
                        const u = response.user;
                        // Tab 1: Personal & Account
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

                        // Tab 2: Licenses & Accreditations
                        $("#prof_licno").val(u.Licno || '');
                        $("#prof_licnoexpiry").val(u.licnoexpiry || '');
                        $("#prof_phicno").val(u.phicno || '');
                        $("#prof_phicexpiry").val(u.phicexpiry || '');
                        $("#prof_phicname").val(u.phicname || '');
                        $("#prof_phicrate").val(u.phicrate || 0);
                        $("#prof_phicenable").prop('checked', !!(u.phicenable == 1 || u.phicenable === true));
                        $("#prof_s2no").val(u.S2no || '');
                        $("#prof_ptr").val(u.PTR || '');

                        // Tab 3: Practice & Clinic
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

                        // Detailed Comment: Tab 4: Rates, Tax & Billing - accurately populate from doctor record
                        const pfVal = u.pfrate !== undefined && u.pfrate !== null ? u.pfrate : (u.consultationfee || 0);
                        const rodVal = u.rodrate !== undefined && u.rodrate !== null ? u.rodrate : 0;
                        const taxVal = u.tax !== undefined && u.tax !== null ? u.tax : (u.taxpercent || 0);
                        const vatVal = u.vatrate !== undefined && u.vatrate !== null ? u.vatrate : (u.VAT || 0);
                        const coaVal = u.coacode || '';
                        const acctVal = u.accountno || (u.bankacct || '');
                        const vatableVal = !!(u.vatable == 1 || u.vatable === true);
                        const autoVatVal = !!(u.autoAddVAT == 1 || u.autoAddVAT === true);
                        const hospOrVal = !!(u.issuehospOR == 1 || u.issuehospOR === true);

                        // Set profile modal fields
                        $("#prof_pfrate").val(parseFloat(pfVal).toFixed(2));
                        $("#prof_rodrate").val(parseFloat(rodVal).toFixed(2));
                        $("#prof_tax").val(parseFloat(taxVal).toFixed(2));
                        $("#prof_vatrate").val(parseFloat(vatVal).toFixed(2));
                        $("#prof_coacode").val(coaVal);
                        $("#prof_accountno").val(acctVal);
                        $("#prof_vatable").prop('checked', vatableVal);
                        $("#prof_autoAddVAT").prop('checked', autoVatVal);
                        $("#prof_issuehospOR").prop('checked', hospOrVal);

                        // Also synchronize dashboard widget fields
                        $("#doctor_pfrate").val(parseFloat(pfVal).toFixed(2));
                        $("#doctor_rodrate").val(parseFloat(rodVal).toFixed(2));
                        $("#doctor_tax").val(parseFloat(taxVal).toFixed(2));
                        $("#doctor_vatrate").val(parseFloat(vatVal).toFixed(2));
                        $("#doctor_coacode").val(coaVal);
                        $("#doctor_accountno").val(acctVal);
                        $("#doctor_vatable").prop('checked', vatableVal);
                        $("#doctor_autoAddVAT").prop('checked', autoVatVal);
                        $("#doctor_issuehospOR").prop('checked', hospOrVal);

                        // Tab 5: System Settings & Notes
                        $("#prof_quevisible").prop('checked', !!(u.quevisible == 1 || u.quevisible === true || u.quevisible === undefined));
                        $("#prof_allowtextresult").prop('checked', !!(u.allowtextresult == 1 || u.allowtextresult === true));
                        $("#prof_allowdocsystem").prop('checked', !!(u.allowdocsystem == 1 || u.allowdocsystem === true));
                        $("#prof_disabletext").prop('checked', !!(u.disabletext == 1 || u.disabletext === true));
                        $("#prof_otherinfo").val(u.otherinfo || '');
                        $("#prof_biodata").val(u.biodata || '');

                        // Legacy view elements
                        const map = {
                            "#doc_fullname": [u.docfname, u.docmname, u.doclname, u.suffix].filter(Boolean).join(" "),
                            "#doc_username": u.username,
                            "#doc_title": u.titlename,
                            "#doc_expertise": u.expertise,
                            "#doc_contact": u.cellno,
                            "#doc_email": u.emailadd,
                            "#doc_clinicroom": u.clinicroom,
                            "#doc_clinichours": u.clinichours,
                            "#doc_lic": u.Licno,
                            "#doc_lic_expiry": u.licnoexpiry,
                            "#doc_phic": u.phicno,
                            "#doc_phic_expiry": u.phicexpiry,
                            "#doc_s2": u.S2no,
                            "#doc_ptr": u.PTR
                        };
                        $.each(map, (sel, val) => $(sel).text(val ?? ""));
                    }
                }
            });
        });

        $("#save_doctor_profile_btn").off("click").on("click", function () {
            const form = document.getElementById("doctor_profile_form");
            if (form && !form.checkValidity()) return form.reportValidity();

            const $btn = $(this);
            const originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving...');

            $.ajax({
                url: "/api/doctor/update_profile",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: $("#doctor_profile_form").serialize(),
                success: function (response) {
                    if (response.success) {
                        // Detailed Comment: Two-way sync - update dashboard billing widget with saved profile rates
                        $("#doctor_pfrate").val($("#prof_pfrate").val());
                        $("#doctor_rodrate").val($("#prof_rodrate").val());
                        $("#doctor_tax").val($("#prof_tax").val());
                        $("#doctor_vatrate").val($("#prof_vatrate").val());
                        $("#doctor_coacode").val($("#prof_coacode").val());
                        $("#doctor_accountno").val($("#prof_accountno").val());
                        $("#doctor_vatable").prop('checked', $("#prof_vatable").is(':checked'));
                        $("#doctor_autoAddVAT").prop('checked', $("#prof_autoAddVAT").is(':checked'));
                        $("#doctor_issuehospOR").prop('checked', $("#prof_issuehospOR").is(':checked'));

                        Swal.fire({
                            title: "Success",
                            text: "Doctor profile updated successfully.",
                            icon: "success"
                        });
                    } else {
                        Swal.fire({ title: "Error", text: response.message || "Failed to update profile.", icon: "error" });
                    }
                },
                error: function (xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to update profile.";
                    Swal.fire({ title: "Error", text: msg, icon: "error" });
                },
                complete: function () {
                    $btn.prop('disabled', false).html(originalHtml);
                }
            });
        });
    }

    /**
     * Detailed Comment: Save default doctor professional/consultation fee handler.
     * Posts to /api/doctor/update_fee to update both doctors and doctorsrights tables,
     * ensuring default auto-charges on new patient consultations.
     */
    $("#save_doctor_fee_btn").on("click", function () {
        const fee = $("#doctor_pfrate").val();
        if (fee === "" || parseFloat(fee) < 0) {
            return Swal.fire({ title: "Validation Error", text: "Please enter a valid consultation fee amount.", icon: "warning" });
        }

        const $btn = $(this);
        setBtnLoading($btn, "Updating...");

        $.ajax({
            url: "/api/doctor/update_fee",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            // Detailed Comment: Send all configured Rates, Tax & Billing fields
            data: $("#doctor_fee_form").serialize(),
            success: function (response) {
                if (response.success) {
                    // Detailed Comment: Two-way sync - update modal Tab 4 inputs with saved dashboard billing rates
                    $("#prof_pfrate").val($("#doctor_pfrate").val());
                    $("#prof_rodrate").val($("#doctor_rodrate").val());
                    $("#prof_tax").val($("#doctor_tax").val());
                    $("#prof_vatrate").val($("#doctor_vatrate").val());
                    $("#prof_coacode").val($("#doctor_coacode").val());
                    $("#prof_accountno").val($("#doctor_accountno").val());
                    $("#prof_vatable").prop('checked', $("#doctor_vatable").is(':checked'));
                    $("#prof_autoAddVAT").prop('checked', $("#doctor_autoAddVAT").is(':checked'));
                    $("#prof_issuehospOR").prop('checked', $("#doctor_issuehospOR").is(':checked'));

                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "success",
                        title: "Default consultation fee and billing rates updated!",
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    Swal.fire({ title: "Error", text: response.message || "Failed to update consultation fee.", icon: "error" });
                }
            },
            error: function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to update fee.";
                Swal.fire({ title: "Error", text: msg, icon: "error" });
            },
            complete: function () {
                resetBtnLoading($btn);
            }
        });
    });

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
     * for doctor inspecting patient consultation details directly from Today's Patients or Consultation History on the dashboard (Task R4).
     */
    function openConsultationDetailsModal(record) {
        if (!record) return;

        // 1. Header Badges
        const dateStr = record.consultation_date ? record.consultation_date.substring(0, 16) : 'N/A';
        const cref = record.consultationrefno || record.caseno || 'N/A';
        const status = (record.status || 'N/A').toUpperCase();
        // Detailed Comment: Identify patient payment classification (PHIC YAKAP, HMO, OTHERS, REGULAR)
        const rawType = (record.modeofpayment || record.patient_type || (record.hmocode ? 'HMO' : (record.phic_pin ? 'PHIC YAKAP' : (record.classification === 'PHIC' ? 'PHIC YAKAP' : (record.classification || 'REGULAR'))))).toUpperCase();
        const isPhic = rawType.includes('PHIC') || Boolean(record.phic_pin);
        const ptype = isPhic ? 'PHIC YAKAP' : (rawType === 'HMO' ? 'HMO' : (rawType === 'OTHERS' ? 'OTHERS' : 'REGULAR'));

        const statusBadges = {
            WAITING: "bg-warning text-white",
            IN_CONSULTATION: "bg-info text-white",
            FOR_BILLING: "bg-primary text-white",
            COMPLETED: "bg-success text-white",
            DONE: "bg-success text-white",
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

        // Detailed Comment: Populate Patient Classification badge & dynamic fields row (Task R4)
        const rawClass = record.classification || 'MEDICAL';
        $("#vcd_classification_badge").text(`Class: ${rawClass}`).removeClass('d-none');

        const classKey = rawClass.toUpperCase();
        let classDetails = [];
        if ((classKey === 'MEDICAL' || classKey === 'GENERAL') && record.subclassification) {
            classDetails.push(`<strong>Sub-classification:</strong> ${record.subclassification}`);
        } else if (classKey === 'OBSTETRICS' || classKey === 'GYNECOLOGY') {
            let obScores = [];
            if (record.gravida !== null && record.gravida !== undefined && record.gravida !== '') obScores.push(`G: ${record.gravida}`);
            if (record.para !== null && record.para !== undefined && record.para !== '') obScores.push(`P: ${record.para}`);
            if (record.abortion !== null && record.abortion !== undefined && record.abortion !== '') obScores.push(`A: ${record.abortion}`);
            if (record.iufd !== null && record.iufd !== undefined && record.iufd !== '') obScores.push(`IUFD: ${record.iufd}`);
            if (record.died !== null && record.died !== undefined && record.died !== '') obScores.push(`Died: ${record.died}`);
            if (obScores.length > 0) classDetails.push(`<strong>OB Scores:</strong> ${obScores.join(', ')}`);
            if (record.lmp_date) classDetails.push(`<strong>LMP Date:</strong> ${record.lmp_date}`);
            if (record.delivery_procedure) classDetails.push(`<strong>Delivery:</strong> ${record.delivery_procedure}`);
            if (record.pathologic) classDetails.push(`<span class="badge bg-danger">Pathologic Case</span>`);
        } else if (classKey === 'NEW BORN') {
            if (record.infacilitydelivery) classDetails.push(`<strong>Source:</strong> ${record.infacilitydelivery}`);
            if (record.delivery_procedure) classDetails.push(`<strong>Delivery:</strong> ${record.delivery_procedure}`);
            if (record.pathologic) classDetails.push(`<span class="badge bg-danger">Pathologic Case</span>`);
        }

        let $classRow = $("#vcd_classification_fields_row");
        if ($classRow.length === 0) {
            $("#vcd_bp").closest(".row").after('<div id="vcd_classification_fields_row" class="mt-2 p-2 bg-light rounded border small d-none"></div>');
            $classRow = $("#vcd_classification_fields_row");
        }
        if (classDetails.length > 0) {
            $classRow.html(classDetails.join(' | ')).removeClass('d-none');
        } else {
            $classRow.addClass('d-none').empty();
        }

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

        // Detailed Comment: Answered Doctor's Questions rendering in Impressions & Diagnosis tab (Task R4)
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

        // Detailed Comment: 8. Tab 6: Patient Permanent Medical History population (Task R4)
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
            // Detailed Comment: Fallback fetch using POST with CSRF header to prevent 405 Method Not Allowed exceptions
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

    /**
     * Detailed Comment: Helper to open dedicated #preview_modal for Radiology and Laboratory files from view modal (Task R4)
     */
    function openDedicatedPreviewModal(url, title) {
        if (!url || url === "#") return;
        const isImage = /\.(jpg|jpeg|png|gif|webp)(\?.*)?$/i.test(url);
        const modalTitle = title || (isImage ? "Image Preview" : "Document Preview");

        $("#preview_title .modal-title, #preview_modal .modal-title").text(modalTitle);

        const docPreview = document.getElementById("docPreview");
        const imgPreview = document.getElementById("imgPreview");
        const downloadBtn = document.getElementById("preview_download_btn");

        if (downloadBtn) {
            downloadBtn.href = url;
            downloadBtn.classList.remove("d-none");
        }

        if (isImage) {
            if (docPreview) {
                docPreview.src = "";
                docPreview.style.display = "none";
            }
            if (imgPreview) {
                imgPreview.src = url;
                imgPreview.classList.remove("d-none");
            }
        } else {
            if (imgPreview) {
                imgPreview.src = "";
                imgPreview.classList.add("d-none");
            }
            if (docPreview) {
                docPreview.src = url;
                docPreview.style.display = "block";
            }
        }

        const modalEl = document.getElementById("preview_modal");
        if (modalEl && window.bootstrap && window.bootstrap.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    $(document).on("click", "#vcd_rad_preview_btn, #vcd_lab_preview_btn", function (e) {
        e.preventDefault();
        const url = $(this).attr("href");
        if (!url || url === "#") return;
        const isRad = $(this).attr("id") === "vcd_rad_preview_btn";
        openDedicatedPreviewModal(url, isRad ? "Radiology Document Preview" : "Laboratory Document Preview");
    });

    /**
     * Detailed Comment: Focus unblurring on modal show and hide transitions (Task R4/R5).
     * Prevents 'Blocked aria-hidden on an element because its descendant retained focus' console warnings.
     */
    $(document).on("show.bs.modal hide.bs.modal", ".modal", function () {
        if (document.activeElement && typeof document.activeElement.blur === "function") {
            document.activeElement.blur();
        }
    });

    /**
     * Detailed Comment: Opens the dedicated Patient Consultations History Modal for a patient
     * selected from Today's Patients table on the Doctor Dashboard (Task R4).
     * Fetches previous consultation records via /api/fetch_patient_medhistory and renders them in DataTable.
     */
    function openPatientConsultationsHistoryModal(patientData) {
        if (!patientData) return;

        const patientName = patientData.patientname || [patientData.pxlastname, patientData.pxfirstname, patientData.pxmidname].filter(Boolean).join(', ') || 'Patient';
        $("#pch_patient_name").text(`- ${patientName}`);

        const pxRef = patientData.pxrefno || '';
        const pinCode = patientData.pincode || patientData.pin || '';
        const consulRef = patientData.consultationrefno || '';

        if ($.fn.DataTable.isDataTable("#patient_consultations_history_table")) {
            $("#patient_consultations_history_table").DataTable().clear().destroy();
        }

        $("#patient_consultations_history_table").DataTable({
            processing: false,
            serverSide: false,
            ajax: {
                url: "/api/fetch_patient_medhistory",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: {
                    consultationrefno: consulRef,
                    pxrefno: pxRef,
                    pincode: pinCode
                },
                dataSrc: function (json) {
                    const list = (json && (json.medhistory || json.history || json.data)) ? (json.medhistory || json.history || json.data) : [];
                    window._dashboardMedhistoryRecords = list;
                    return list;
                },
                error: function (xhr, error, thrown) {
                    console.warn("fetch_patient_medhistory error:", error, thrown);
                }
            },
            columns: [
                {
                    data: null,
                    defaultContent: "",
                    orderable: false,
                    className: "text-center align-middle text-nowrap",
                    render: function (data, type, row, meta) {
                        return `<button type="button" class="btn btn-sm btn-primary view-consultation-details-btn btn-view-details" data-index="${meta.row}" data-consultationrefno="${row.consultationrefno || ''}" title="View Consultation Details">
                            <i class="fa-solid fa-eye me-1"></i> Details
                        </button>`;
                    }
                },
                {
                    data: 'photo_path',
                    defaultContent: "/images/blank_photo.png",
                    orderable: false,
                    className: "text-center align-middle",
                    render: function (data) {
                        const src = (data && !data.includes('blank_photo.png')) ? data : '/images/blank_photo.png';
                        return `<img src="${src}" class="rounded-circle border shadow-sm" style="width: 36px; height: 36px; object-fit: cover;" onerror="this.src='/images/blank_photo.png'" alt="Patient">`;
                    }
                },
                {
                    data: 'consultation_date',
                    defaultContent: "N/A",
                    className: "align-middle text-nowrap fw-semibold",
                    render: function (data) {
                        if (!data) return '<span class="text-muted">N/A</span>';
                        return `<span>${data.split(' ')[0]}</span>`;
                    }
                },
                {
                    data: 'reasonforconsultation',
                    defaultContent: "--",
                    className: "align-middle",
                    render: function (data) {
                        if (!data) return '<span class="text-muted">--</span>';
                        return `<span>${$('<div>').text(data).html()}</span>`;
                    }
                },
                {
                    data: null,
                    defaultContent: "--",
                    className: "align-middle",
                    render: function (data, type, row) {
                        const diag = row.finadiagnosis || row.diagnosis || row.impression || '';
                        if (!diag) return '<span class="text-muted">--</span>';
                        return `<span>${$('<div>').text(diag).html()}</span>`;
                    }
                },
                {
                    data: 'status',
                    defaultContent: "PENDING",
                    className: "text-center align-middle",
                    render: function (data) {
                        const st = (data || 'PENDING').toUpperCase();
                        if (st === 'COMPLETED' || st === 'DONE') {
                            return '<span class="badge bg-success">COMPLETED</span>';
                        } else if (st === 'CANCELLED') {
                            return '<span class="badge bg-danger">CANCELLED</span>';
                        }
                        return '<span class="badge bg-warning text-dark">PENDING</span>';
                    }
                },
                {
                    data: null,
                    defaultContent: "Attending Doctor",
                    className: "align-middle small",
                    render: function (data, type, row) {
                        return row.docname || row.docrefno || 'Attending Doctor';
                    }
                }
            ],
            language: { emptyTable: "No past consultations recorded for this patient." },
            pageLength: 10,
            lengthChange: false,
            ordering: false,
            responsive: true
        });

        const modalEl = document.getElementById("patient_consultations_history_modal");
        if (modalEl && window.bootstrap && window.bootstrap.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }
    window.openPatientConsultationsHistoryModal = openPatientConsultationsHistoryModal;

    // Detailed Comment: Delegate click handler to open Patient Consultations History Modal from Today's Patients table (Task R4)
    $(document).on("click", "#todays_patients_table .view-consultation-details-btn, #todays_patients_table .btn-view-details", function (e) {
        e.stopPropagation();
        const table = $("#todays_patients_table").DataTable();
        const tr = $(this).closest("tr");
        const rowData = table.row(tr).data() || (window._dashboardPatients ? window._dashboardPatients[$(this).data("index")] : null);
        if (rowData) {
            openPatientConsultationsHistoryModal(rowData);
        }
    });

    $(document).on("click", "#todays_patients_table tbody tr", function (e) {
        if ($(e.target).closest("button").length > 0) return;
        const table = $("#todays_patients_table").DataTable();
        const rowData = table.row(this).data();
        if (rowData) {
            openPatientConsultationsHistoryModal(rowData);
        }
    });

    // Detailed Comment: Delegate click handler to open the full Consultation Details Viewer modal from Patient Consultation History table (Task R4)
    $(document).on("click", "#patient_consultations_history_table .view-consultation-details-btn, #patient_consultations_history_table .btn-view-details", function (e) {
        e.stopPropagation();
        const table = $("#patient_consultations_history_table").DataTable();
        const tr = $(this).closest("tr");
        let rowData = table.row(tr).data();
        const idx = $(this).data("index");
        const refno = $(this).data("consultationrefno") || $(this).attr("data-consultationrefno") || (rowData ? rowData.consultationrefno : null);

        if (!rowData && idx !== undefined && window._dashboardMedhistoryRecords && window._dashboardMedhistoryRecords[idx]) {
            rowData = window._dashboardMedhistoryRecords[idx];
        }

        if (rowData && rowData.rx_items) {
            openConsultationDetailsModal(rowData);
        } else if (refno) {
            $.ajax({
                url: "/api/fetch_patient_medhistory",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: { consultationrefno: refno },
                success: function (res) {
                    const list = res.medhistory || res.history || res.data || [];
                    if (list.length > 0) {
                        const matched = list.find(item => item.consultationrefno === refno) || list[0];
                        openConsultationDetailsModal(matched);
                    } else if (rowData) {
                        openConsultationDetailsModal(rowData);
                    }
                },
                error: function () {
                    if (rowData) openConsultationDetailsModal(rowData);
                }
            });
        } else if (rowData) {
            openConsultationDetailsModal(rowData);
        }
    });

    $(document).on("click", "#patient_consultations_history_table tbody tr", function (e) {
        if ($(e.target).closest("button").length > 0) return;
        const table = $("#patient_consultations_history_table").DataTable();
        const rowData = table.row(this).data();
        if (rowData) {
            const refno = rowData.consultationrefno;
            if (rowData.rx_items) {
                openConsultationDetailsModal(rowData);
            } else if (refno) {
                $.ajax({
                    url: "/api/fetch_patient_medhistory",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: { consultationrefno: refno },
                    success: function (res) {
                        const list = res.medhistory || res.history || res.data || [];
                        const matched = list.find(item => item.consultationrefno === refno) || rowData;
                        openConsultationDetailsModal(matched);
                    },
                    error: function () {
                        openConsultationDetailsModal(rowData);
                    }
                });
            } else {
                openConsultationDetailsModal(rowData);
            }
        }
    });

    /**
     * Detailed Comment: Elevation of nested modal backdrop z-index.
     * When #view_consultation_details_modal or #preview_modal is stacked over another open modal,
     * elevate the top backdrop so it sits between parent and child modal.
     */
    $(document).on("show.bs.modal shown.bs.modal", "#patient_consultations_history_modal", function () {
        const $otherModals = $(".modal.show").not("#patient_consultations_history_modal");
        if ($otherModals.length > 0) {
            $(".modal-backdrop").last().css("z-index", 1055);
        }
    });

    $(document).on("show.bs.modal shown.bs.modal", "#view_consultation_details_modal", function () {
        const $otherModals = $(".modal.show").not("#view_consultation_details_modal");
        if ($otherModals.length > 0) {
            $(".modal-backdrop").last().css("z-index", 1065);
        }
    });

    $(document).on("show.bs.modal shown.bs.modal", "#preview_modal", function () {
        const $otherModals = $(".modal.show").not("#preview_modal");
        if ($otherModals.length > 0) {
            $(".modal-backdrop").last().css("z-index", 1075);
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
