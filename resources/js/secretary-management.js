$(function () {
    if ($("#management_modal").length > 0 || $("#secretary_profile_modal").length > 0) {
        loadDetails();
    }

    $("#account_tab").on("click", function () {
        loadDetails();
    });

    $("#secretary_profile_modal").on("show.bs.modal", function () {
        loadDetails();
    });

    // Detailed Comment: Self-service profile save handler from Secretary Management modal
    $("#save_profile_btn").on("click", function () {
        const form = document.getElementById("account_form");
        if (form && !form.checkValidity()) return form.reportValidity();

        $.ajax({
            url: "/api/secretary/update_profile",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: $("#account_form").serialize(),
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title: "Success",
                        text: "Secretary profile updated successfully.",
                        icon: "success"
                    });
                    loadDetails();
                } else {
                    Swal.fire({ title: "Error", text: response.message || "Failed to update profile.", icon: "error" });
                }
            },
            error: function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to update profile.";
                Swal.fire({ title: "Error", text: msg, icon: "error" });
            }
        });
    });

    // Detailed Comment: Self-service profile save handler from dedicated My Secretary Profile modal
    $("#save_secretary_profile_btn").on("click", function () {
        const form = document.getElementById("secretary_profile_form");
        if (form && !form.checkValidity()) return form.reportValidity();

        $.ajax({
            url: "/api/secretary/update_profile",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: $("#secretary_profile_form").serialize(),
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title: "Success",
                        text: "Secretary profile updated successfully.",
                        icon: "success"
                    });
                    loadDetails();
                } else {
                    Swal.fire({ title: "Error", text: response.message || "Failed to update profile.", icon: "error" });
                }
            },
            error: function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to update profile.";
                Swal.fire({ title: "Error", text: msg, icon: "error" });
            }
        });
    });

    function loadDetails() {
        $.ajax({
            url: "/api/fetch_secretary",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            success: function (response) {
                if (response.success && response.user) {
                    const u = response.user;
                    // Populate Secretary Management modal fields
                    $("#sec_fullname").text([u.secfname, u.secmname, u.seclname, u.secsuffix].filter(Boolean).join(' '));
                    $("#sec_fname").val(u.secfname || '');
                    $("#sec_mname").val(u.secmname || '');
                    $("#sec_lname").val(u.seclname || '');
                    $("#sec_suffix").val(u.secsuffix || '');
                    $("#sec_username_input").val(u.username || '');
                    $("#sec_gender_input").val(u.secgender ? u.secgender.toUpperCase() : 'MALE');
                    $("#sec_bday_input").val(u.secbday || '');
                    $("#sec_contact").val(u.seccontactno || '');
                    $("#sec_email").val(u.secemail || '');
                    $("#sec_adrs").val(u.secadrs || '');

                    // Populate dedicated My Secretary Profile modal fields
                    $("#my_secfname").val(u.secfname || '');
                    $("#my_secmname").val(u.secmname || '');
                    $("#my_seclname").val(u.seclname || '');
                    $("#my_secsuffix").val(u.secsuffix || '');
                    $("#my_secusername").val(u.username || '');
                    $("#my_secgender").val(u.secgender ? u.secgender.toUpperCase() : 'MALE');
                    $("#my_secbday").val(u.secbday || '');
                    $("#my_seccontactno").val(u.seccontactno || '');
                    $("#my_secemail").val(u.secemail || '');
                    $("#my_secadrs").val(u.secadrs || '');
                }
            }
        });
    }

    $("#info_tab").on("click", function () {
        $.ajax({
            url: "fetch_doctor_info",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: {
                docrefno: $("#choose_doctor").val()
            },
            success: function (response) {
                if (response.success) {
                    $("#doc_fullname").text([response.doctor.docfname, response.doctor.docmname, response.doctor.doclname, response.doctor.suffix].filter(Boolean).join(' '));
                    $("#doc_contact").text(response.doctor.cellno);
                    $("#doc_email").text(response.doctor.emailadd);
                    $("#doc_title").text(response.doctor.titlename);
                    $("#doc_lic").text(response.doctor.Licno);
                    $("#doc_phic").text(response.doctor.phicno);
                    $("#doc_s2").text(response.doctor.S2no);
                }
            }
        })
    });

    $("#questions_tab").on("click", function () {
        $("#doctor_questions_tab").DataTable().clear().destroy();
        $("#doctor_questions_tab").DataTable({
            ajax: {
                url: "/api/fetch_doctor_questions",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content",
                    ),
                },
                data: {
                    docrefno: $("#choose_doctor").val(),
                },
                dataSrc: "docquestions",
            },
            columns: [
                {
                    data: null,
                    render: function (data) {
                        return `<button class="btn btn-sm btn-primary edit-question" value="${data.docquestionrefno}"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                            <button class="btn btn-sm btn-danger delete-question" value="${data.docquestionrefno}"><i class="fa-solid fa-trash-can"></i> Delete</button>`;
                    },
                },
                { data: "question" },
            ],
            columnDefs: [
                {
                    targets: 0,
                    width: "1%",
                    orderable: false,
                    searchable: false,
                    className: "text-nowrap text-center align-middle",
                },
            ],
            language: {
                emptyTable: "No saved questions yet.",
            },
            lengthChange: false,
            paging: true,
            searching: false,
            ordering: false,
            responsive: true,
        });
    });

    $("#create_question_btn").on("click", function () {
        var form = document.getElementById("create_question_form");

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        $.ajax({
            url: "create_question",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: {
                docrefno: $("#choose_doctor").val(),
                question: $("#dquestion").val(),
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title: "Success",
                        text: "Question added successfully.",
                        icon: "success",
                        confirmButtonText: "Okay",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $("#questions_tab").trigger("click");
                            $("#create_question_form")[0].reset();
                        }
                    });
                }
            },
        });
    });

    $("#schedules_tab").on("click", function () {
        $.ajax({
            url: "fetch_doctor_schedules",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: {
                docrefno: $("#choose_doctor").val()
            },
            success: function (response) {
                if (response.success) {
                    renderSchedules(response.schedules);
                }
            }
        });

        // new Calendar($("#schedules_calendar")[0], {
        //     plugins: [listPlugin, interactionPlugin, bootstrap5Plugin],
        //     themeSystem: "bootstrap5",
        //     initialView: "listWeek",
        //     headerToolbar: false,
        //     listDaySideFormat: false,
        //     events: function (fetchInfo, successCallback, failureCallback) {
        //         $.ajax({
        //             url: "fetch_doctor_schedules",
        //             type: "POST",
        //             headers: {
        //                 "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
        //                     "content",
        //                 ),
        //             },
        //             data: { docrefno: $("#choose_doctor").val() },
        //             success: function (response) {
        //                 const events = response.schedules.map(shift => ({
        //                     daysOfWeek: [ mapWeekdayToNumber(shift.day) ],
        //                     startTime: shift.start,
        //                     endTime: shift.end,
        //                 }));
        //                 successCallback(events);
        //             },
        //             error: function () {
        //                 failureCallback();
        //             },
        //         });
        //     },

        // }).render();
    });

    function renderSchedules(schedules) {
        const grouped = groupByDay(schedules);
        const body = $("#schedules_calendar tbody");

        body.empty();

        Object.keys(grouped).forEach(day => {
            const dayRow = $(`
                <tr class="table-success">
                    <td colspan="3"><strong>${day}</strong></td>
                </tr>
            `);

            body.append(dayRow);

            grouped[day].forEach(sched => {
                const schedRow = $(`
                    <tr class="align-middle schedule-row" data-id="${sched.schedrefno}">
                        <td style="width: 1%;" class="text-nowrap">
                            <button class="btn btn-sm btn-primary edit-schedule" value="${sched.schedrefno}">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </button>

                            <button class="btn btn-sm btn-danger delete-schedule" value="${sched.schedrefno}">
                                <i class="fa-solid fa-trash"></i> Delete
                            </button>
                        </td>

                        <td class="ps-4">
                            <strong>${convert24To12(sched.start)} - ${convert24To12(sched.end)}</strong>
                        </td>

                        <td class="w-50">
                            ${sched.notes ?? ''}
                        </td>
                    </tr>
                `);

                body.append(schedRow);
            });
        });
    }

    function groupByDay(schedules) {
        return schedules.reduce((acc, sched) => {
            if (!acc[sched.day]) {
                acc[sched.day] = [];
            }

            acc[sched.day].push(sched);

            return acc;
        }, {});
    }

    function convert24To12(time) {
        const [hour, minute] = time.split(":");
        const h = parseInt(hour, 10);
        const suffix = h >= 12 ? 'PM' : 'AM';
        const hour12 = h % 12 || 12;
        return `${hour12}:${minute} ${suffix}`;
    }

    $(document).on("click", ".edit-schedule", function () {
        const modalEl = document.getElementById("manage_doctor_modal");
        const bsModal = bootstrap.Modal.getInstance(modalEl);

        const schedrefno = $(this).val();

        $.ajax({
            url: "fetch_schedule_refno",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: {
                schedrefno: schedrefno
            },
            success: function (response) {
                if (response.success) {
                    bsModal.hide();
                    Swal.fire({
                        title: "Edit Schedule",
                        html: `
                            <select class="form-select mb-3" id="esched_day" name="esched_day" value="${response.sched.day}" disabled>
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                                <option value="Saturday">Saturday</option>
                                <option value="Sunday">Sunday</option>
                            </select>

                            <div class="input-group mb-3">
                                <input class="form-control" type="time" id="esched_start" name="esched_start">
                                <span class="input-group-text">To</span>
                                <input class="form-control" type="time" id="esched_end" name="esched_end">
                            </div>

                            <textarea class="form-control" id="esched_notes" name="esched_notes"></textarea>
                        `,
                        confirmButtonText: "Update",
                        showCancelButton: true,
                        focusConfirm: false,
                        didOpen: () => {
                            document.getElementById("esched_day").value = response.sched.day;
                            document.getElementById("esched_start").value = response.sched.start;
                            document.getElementById("esched_end").value = response.sched.end;
                            document.getElementById("esched_notes").value = response.sched.notes;
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "edit_schedule",
                                type: "POST",
                                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                                data: {
                                    schedrefno: schedrefno,
                                    day: $("#esched_day").val(),
                                    start: $("#esched_start").val(),
                                    end: $("#esched_end").val(),
                                    notes: $("#esched_notes").val()
                                },
                                success: function (response) {
                                    bsModal.show();
                                    $("#schedules_tab").trigger("click");
                                }
                            })
                        } else {
                            bsModal.show();
                        }
                    });
                }
            }
        });
    });

    $(document).on("click", ".delete-schedule", function () {
        Swal.fire({
            title: "Confirmation",
            text: "Are you sure you want to delete this schedule?",
            icon: "warning",
            confirmButtonText: "Confirm",
            showCancelButton: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "delete_schedule",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: {
                        schedrefno: $(this).val()
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                title: "Success",
                                text: "Successfully deleted schedule.",
                                icon: "success"
                            });
                        }

                        $("#schedules_tab").trigger("click");
                    }
                });
            }
        });
    });

    $("#add_schedule_btn").on("click", function () {
        let formData = $("#create_schedule_form").serialize();
        formData +=
            "&docrefno=" + encodeURIComponent($("#choose_doctor").val());

        $.ajax({
            url: "create_doctor_schedules",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: formData,
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title: "Success",
                        text: "Schedule created.",
                        icon: "success",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $("#create_schedule_form")[0].reset();
                        }

                        $("#schedules_tab").trigger("click");
                    });
                }
            },
        });
    });

    // SERVICES GROUP MANAGEMENT AREA
    let groupTable; // GLOBAL TABLE NAME

    $("#services_group_management_tab").on("click", function () {
        $("#group_management_tab").DataTable().clear().destroy();
        groupTable = $("#group_management_tab").DataTable({
            ajax: {
                url: "fetchGroupManagement",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content",
                    ),
                },
                dataSrc: "groupManagement",
            },
            columns: [
                {
                    data: null,
                    render: function (data) {
                        return `<button class="btn btn-sm btn-primary edit-group-btn" data-token="${data.token}"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                            <button class="btn btn-sm btn-danger remove-group-btn" data-token="${data.token}"><i class="fa-solid fa-trash-can"></i> Delete</button>`;
                    },
                },
                { data: "servicegroup_refno" },
                { data: "servicegroup_name" },
                { data: "servicegroup_dscr" },
            ],
            columnDefs: [
                {
                    targets: 0,
                    width: "1%",
                    orderable: false,
                    searchable: false,
                    className: "text-nowrap text-center align-middle",
                },
            ],
            language: {
                emptyTable: "No saved questions yet.",
            },
            lengthChange: false,
            paging: true,
            searching: false,
            ordering: false,
            responsive: true,
        });
    });

    $("#create_group_btn").on("click", function () {
        var form = document.getElementById("create_group_form");

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // 🔄 SHOW LOADING
        Swal.fire({
            title: "Please wait...",
            text: "Saving service group",
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        $.ajax({
            url: "createGroupManagement",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: {
                servicegroup_name: $("#servicegroup_name").val(),
                servicegroup_dscr: $("#servicegroup_dscr").val(),
            },
            success: function (response) {
                Swal.close(); // ✅ CLOSE LOADING

                if (response.success) {
                    Swal.fire({
                        title: "Success",
                        text: "Services Group added successfully.",
                        icon: "success",
                        confirmButtonText: "Okay",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $("#services_group_management_tab").trigger(
                                "click",
                            );
                            $("#create_group_form")[0].reset();
                        }
                    });
                }
            },
            error: function (xhr) {
                Swal.close(); // ✅ CLOSE LOADING

                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: xhr.responseJSON?.message || "Something went wrong!",
                });
            },
        });
    });

    $("#group_management_tab").on("click", ".edit-group-btn", function () {
        const token = $(this).data("token");

        $.ajax({
            type: "POST",
            url: "editGroupManagement",
            data: {
                token: token,
            },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            dataType: "json",
            beforeSend: function () {
                Swal.fire({
                    title: "Please wait...",
                    text: "Processing request",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                });
            },

            success: function (response) {
                const editGroupModal = new bootstrap.Modal("#editGroupModal");

                if (response.status == true) {
                    Swal.close(); // ✅ close loading
                    editGroupModal.show();
                    $("#edit_group_token").val(token);
                    $("#edit_servicegroup_name").val(
                        response.message["servicegroup_name"],
                    );
                    $("#edit_servicegroup_dscr").val(
                        response.message["servicegroup_dscr"],
                    );
                }
            },

            error: function (xhr, status, error) {
                Swal.close(); // ✅ close loading

                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: xhr.responseJSON?.message || "Something went wrong",
                });
            },
        });
    });

    $("#group_management_tab").on("click", ".remove-group-btn", function () {
        const token = $(this).data("token");

        // Step 1: Ask for confirmation
        Swal.fire({
            title: "Are you sure?",
            text: "This will permanently delete the group!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "Cancel",
        }).then((result) => {
            if (result.isConfirmed) {
                // Step 2: Proceed with AJAX deletion
                $.ajax({
                    type: "POST", // or "DELETE" if your route supports it
                    url: "deleteGroupManagement", // change to your deletion route
                    data: { token: token },
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content",
                        ),
                    },
                    dataType: "json",
                    beforeSend: function () {
                        Swal.fire({
                            title: "Deleting...",
                            text: "Please wait",
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => Swal.showLoading(),
                        });
                    },
                    success: function (response) {
                        Swal.close();
                        if (response.status == true) {
                            groupTable.ajax.reload(null, false);
                            Swal.fire({
                                icon: "success",
                                title: "Deleted!",
                                text:
                                    response.message ||
                                    "Group deleted successfully",
                            });
                            // Optionally remove the row from table
                            // $(this).closest('tr').remove(); // careful with `this` scope
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text:
                                    response.message ||
                                    "Failed to delete group",
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        Swal.close();
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text:
                                xhr.responseJSON?.message ||
                                "Something went wrong",
                        });
                    },
                });
            }
        });
    });

    $(document).on("click", "#update_group_btn", function () {
        const token = $("#edit_group_token").val();
        const edit_servicegroup_name = $("#edit_servicegroup_name").val();
        const edit_servicegroup_dscr = $("#edit_servicegroup_dscr").val();

        //Optional: Simple validation
        if (!edit_servicegroup_name || !edit_servicegroup_dscr) {
            Swal.fire({
                icon: "warning",
                title: "Validation Error",
                text: "Please fill in all fields",
            });
            return;
        }

        // Step 1: Ask for confirmation before update
        Swal.fire({
            title: "Are you sure?",
            text: "This will update the group details!",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#aaa",
            confirmButtonText: "Yes, update it!",
            cancelButtonText: "Cancel",
        }).then((result) => {
            if (result.isConfirmed) {
                // Step 2: Proceed with AJAX update
                $.ajax({
                    type: "POST",
                    url: "updateGroupManagement", // your update route
                    data: {
                        token: token,
                        servicegroup_name: edit_servicegroup_name,
                        servicegroup_dscr: edit_servicegroup_dscr,
                    },
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content",
                        ),
                    },
                    dataType: "json",
                    beforeSend: function () {
                        Swal.fire({
                            title: "Updating...",
                            text: "Please wait",
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => Swal.showLoading(),
                        });
                    },
                    success: function (response) {
                        Swal.close();
                        if (response.status == true) {
                            // Reload the DataTable to reflect changes
                            groupTable.ajax.reload(null, false);

                            Swal.fire({
                                icon: "success",
                                title: "Updated!",
                                text:
                                    response.message ||
                                    "Group updated successfully",
                            });

                            // Close the edit modal
                            $("#editGroupModal").modal("hide");
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text:
                                    response.message ||
                                    "Failed to update group",
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        Swal.close();
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text:
                                xhr.responseJSON?.message ||
                                "Something went wrong",
                        });
                    },
                });
            }
        });
    });

    // END

    // SERVICES MANAGEMENT AREA

    let servicesTable; // GLOBAL TABLE NAME

    $("#services_management_tab").on("click", function () {
        if ($.fn.DataTable.isDataTable("#services_management_table_tab")) {
            $("#services_management_table_tab").DataTable().clear().destroy();
        }

        servicesTable = $("#services_management_table_tab").DataTable({
            ajax: {
                url: "fetchDoctorServices",
                type: "GET",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content",
                    ),
                },
                dataSrc: "doctorServices",
            },
            columns: [
                {
                    data: null,
                    render: function (data) {
                        return `<button class="btn btn-sm btn-primary edit-service-btn" data-token="${data.token}">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-danger remove-service-btn" data-token="${data.token}">
                                <i class="fa-solid fa-trash-can"></i> Delete
                            </button>`;
                    },
                },
                { data: "servicename" },
                { data: "docfullname" },
                { data: "servicedscr" },
                { data: "category" },
                { data: "servicecharge" },
            ],
            columnDefs: [
                {
                    targets: 0,
                    width: "1%",
                    orderable: false,
                    searchable: false,
                    className: "text-nowrap text-center align-middle",
                },
            ],
            language: {
                emptyTable: "No doctor services yet.",
            },
            lengthChange: false,
            paging: true,
            searching: false,
            ordering: false,
            responsive: true,
        });
    });

    $("#create_services_btn").on("click", function () {
        var form = document.getElementById("create_services_form");

        // Validate the form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // Collect form data
        const serviceName = $("#service_name").val();
        const serviceCharge = $("#service_charge").val();
        const serviceCategory = $("#service_category").val();
        const serviceDscr = $("#service_dscr").val();
        const choose_doctor = $("#choose_doctor").val();

        // 🔄 SHOW LOADING
        Swal.fire({
            title: "Please wait...",
            text: "Saving service...",
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading(),
        });

        // AJAX request
        $.ajax({
            url: "createServicesManagement", // Laravel route for saving service
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: {
                servicename: serviceName,
                servicecharge: serviceCharge,
                category: serviceCategory,
                servicedscr: serviceDscr,
                docrefno: choose_doctor,
            },
            dataType: "json",
            success: function (response) {
                Swal.close(); // ✅ CLOSE LOADING

                if (response.status) {
                    servicesTable.ajax.reload(null, false);
                    Swal.fire({
                        title: "Success",
                        text: response.message || "Service added successfully.",
                        icon: "success",
                        confirmButtonText: "Okay",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Reset the form
                            form.reset();

                            // Optional: Reload services table if using DataTable
                            if (typeof servicesTable !== "undefined") {
                                servicesTable.ajax.reload(null, false);
                            }
                        }
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: response.message || "Failed to add service.",
                    });
                }
            },
            error: function (xhr) {
                Swal.close();
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: xhr.responseJSON?.message || "Something went wrong!",
                });
            },
        });
    });

    $("#services_management_table_tab").on(
        "click",
        ".edit-service-btn",
        function () {
            const token = $(this).data("token");

            $.ajax({
                type: "POST",
                url: "editServiceManagement",
                data: { token: token },
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content",
                    ),
                },
                dataType: "json",
                beforeSend: function () {
                    Swal.fire({
                        title: "Loading...",
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading(),
                    });
                },
                success: function (response) {
                    const editServiceModal = new bootstrap.Modal(
                        "#editServiceModal",
                    );
                    Swal.close();

                    if (response.status) {
                        const data = response.data;

                        $("#edit_service_token").val(token);
                        $("#edit_servicerefno").val(data.servicerefno);
                        $("#edit_servicename").val(data.servicename);
                        $("#edit_servicecharge").val(data.servicecharge);
                        $("#edit_service_category").val(data.category);
                        $("#edit_servicedscr").val(data.servicedscr);

                        new bootstrap.Modal("#editServiceModal").show();
                    } else {
                        Swal.fire("Error", response.message, "error");
                    }
                },
                error: function () {
                    Swal.fire("Error", "Something went wrong", "error");
                },
            });
        },
    );

    $(document).on("click", "#update_service_btn", function () {
        const token = $("#edit_service_token").val();
        const servicename = $("#edit_servicename").val();
        const servicecharge = $("#edit_servicecharge").val();
        const category = $("#edit_service_category").val();
        const servicedscr = $("#edit_servicedscr").val();

        // ✅ Simple validation
        if (!servicename || !servicecharge || !category || !servicedscr) {
            Swal.fire({
                icon: "warning",
                title: "Validation Error",
                text: "Please fill in all fields",
            });
            return;
        }

        // ✅ Confirmation
        Swal.fire({
            title: "Are you sure?",
            text: "This will update the service details!",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Yes, update it!",
            cancelButtonText: "Cancel",
        }).then((result) => {
            if (result.isConfirmed) {
                // 🔄 Loading
                Swal.fire({
                    title: "Updating...",
                    text: "Please wait",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading(),
                });

                $.ajax({
                    type: "POST",
                    url: "updateServiceManagement",
                    data: {
                        token: token,
                        servicename: servicename,
                        servicecharge: servicecharge,
                        category: category,
                        servicedscr: servicedscr,
                    },
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content",
                        ),
                    },
                    dataType: "json",
                    success: function (response) {
                        // after ajax success
                        const modalEl =
                            document.getElementById("editServiceModal");
                        const modal =
                            bootstrap.Modal.getInstance(modalEl) ||
                            new bootstrap.Modal(modalEl);

                        modal.hide();
                        Swal.close();

                        servicesTable.ajax.reload(null, false);
                        if (response.status) {
                            Swal.fire({
                                icon: "success",
                                title: "Updated!",
                                text:
                                    response.message ||
                                    "Service updated successfully",
                            });


                        } else {
                            Swal.fire("Error", response.message, "error");
                        }
                    },
                    error: function () {
                        Swal.close();
                        Swal.fire("Error", "Something went wrong", "error");
                    },
                });
            }
        });
    });

    $("#services_management_table_tab").on(
        "click",
        ".remove-service-btn",
        function () {
            const token = $(this).data("token");

            Swal.fire({
                title: "Are you sure?",
                text: "This service will be permanently deleted!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Yes, delete it!",
            }).then((result) => {
                if (result.isConfirmed) {
                    // 🔄 SHOW LOADING
                    Swal.fire({
                        title: "Deleting...",
                        text: "Please wait",
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    $.ajax({
                        type: "POST",
                        url: "deleteServiceManagement",
                        data: { token: token },
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                                "content",
                            ),
                        },
                        dataType: "json",
                        success: function (response) {
                            Swal.close(); // ✅ close loading

                            if (response.status) {
                                Swal.fire({
                                    icon: "success",
                                    title: "Deleted!",
                                    text:
                                        response.message ||
                                        "Service deleted successfully",
                                });

                                servicesTable.ajax.reload(null, false);
                            } else {
                                Swal.fire({
                                    icon: "error",
                                    title: "Error",
                                    text:
                                        response.message ||
                                        "Failed to delete service",
                                });
                            }
                        },
                        error: function () {
                            Swal.close(); // ✅ close loading

                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: "Something went wrong",
                            });
                        },
                    });
                }
            });
        },
    );

    function loadServiceCategories() {
        $.ajax({
            url: "fetchGroupManagementCategory",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            dataType: "json",
            success: function (response) {
                const select = $("#service_category, #edit_service_category");

                select.find("option:not(:first)").remove();

                if (
                    response.groupManagement &&
                    response.groupManagement.length > 0
                ) {
                    response.groupManagement.forEach(function (item) {
                        select.append(
                            $("<option>", {
                                value: item.servicegroup_name,
                                text: item.servicegroup_name,
                            }),
                        );
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error("Error fetching categories:", error);
            },
        });
    }

    // Call the function on page load
    if ($("#secretaryPage").length > 0) {
        loadServiceCategories();
    }
    // END
});
