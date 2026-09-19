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
            }}
        ],
        columnDefs: [
            { target: 0, width: '1%', orderable: false, searchable: false, className: 'text-nowrap fw-bold text-center align-middle' },
            { target: 2, width: '1%', className: 'text-nowrap overflow-hidden align-middle text-center' }
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

                        // Tab 4: Rates, Tax & Billing
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
});
