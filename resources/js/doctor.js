$(function () {
    if ($("#doctorPage").length) {
        loadEverything();
    }

    function loadEverything() {
        $("#todays_patients_table").DataTable().clear().destroy();
        $("#todays_patients_table").DataTable({
            ajax: {
                url: "fetch_todays_patients",
                type: "POST",
                headers: { 'X-CSRF-TOKEN': $("meta[name='csrf-token']").attr('content') },
                dataSrc: function (json) {
                    $("#patient_count").text("Count: " + json.count);
                    return json.patients;
                }
            },
            columns: [
                { data: 'queueno' },
                {
                    data: null,
                    render: function (data) {
                        return `${[data.patientname, data.pxmidname, data.pxlastname, data.pxsuffix].filter(v => v).join(' ')}`;
                    }
                },
                {
                    data: 'status',
                    render: function (data) {
                        let badgeClass = 'bg-secondary fs-6'; // Default

                        if (data === 'WAITING') badgeClass = 'bg-warning text-white text-dark fs-6';
                        if (data === 'IN_CONSULTATION') badgeClass = 'bg-info text-white fs-6';
                        if (data === 'COMPLETED') badgeClass = 'bg-success fs-6';
                        if (data === 'UNSCHEDULED') badgeClass = 'bg-primary fs-6';
                        if (data === 'CANCELLED' || data === 'NO_SHOW') badgeClass = 'bg-danger fs-6';

                        return `<span class="badge ${badgeClass}">${data}</span>`;
                    }
                }
            ],
            columnDefs: [
                {
                    target: 0,
                    width: '1%',
                    orderable: false,
                    searchable: false,
                    className: 'text-nowrap fw-bold text-center align-middle'
                },
                {
                    target: 2,
                    width: '1%',
                    className: 'text-nowrap overflow-hidden align-middle text-center',
                }
            ],
            language: {
                emptyTable: "No patients yet."
            },
            pageLength: 30,
            lengthChange: false,
            paging: true,
            searching: false,
            ordering: false,
            responsive: true
        });

        $.ajax({
            url: "fetch_doctor_schedules_dashboard",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                renderSchedules(response.schedules);
            }
        });
    }

    function renderSchedules(schedules) {
        const body = $("#schedules_calendar tbody");
        body.empty();

        if (!schedules || schedules.length === 0) {
            body.html('<tr><td colspan="2" class="text-muted text-center py-3"><i class="fa-solid fa-calendar-xmark me-1"></i> No clinic schedules set yet.</td></tr>');
            return;
        }

        const daysOrder = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
        const grouped = groupByDay(schedules);

        daysOrder.forEach(day => {
            if (grouped[day] && grouped[day].length > 0) {
                body.append(`<tr class="table-success"><td colspan="2"><strong>${day}</strong></td></tr>`);
                grouped[day].forEach(sched => {
                    const schedRow = $(`
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
                    body.append(schedRow);
                });
            }
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
        if (!time) return '';
        const [hour, minute] = time.split(":");
        const h = parseInt(hour, 10);
        const suffix = h >= 12 ? 'PM' : 'AM';
        const hour12 = h % 12 || 12;
        return `${hour12}:${minute} ${suffix}`;
    }

    // Detailed Comment: Open Add Schedule modal
    $(document).on("click", "#add_doctor_schedule_btn", function () {
        if ($("#doctor_add_schedule_form").length) {
            $("#doctor_add_schedule_form")[0].reset();
            new bootstrap.Modal(document.getElementById("doctor_add_schedule_modal")).show();
        }
    });

    // Detailed Comment: Save schedule
    $(document).on("click", "#save_doctor_schedule_btn", function () {
        const form = document.getElementById("doctor_add_schedule_form");
        if (!form.checkValidity()) return form.reportValidity();

        const start = $("#add_sched_start").val();
        const end = $("#add_sched_end").val();
        if (start >= end) {
            return Swal.fire({ title: "Invalid Time Range", text: "Start time must be earlier than end time.", icon: "warning" });
        }

        $.ajax({
            url: "/api/doctor/create_schedule",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: $("#doctor_add_schedule_form").serialize(),
            success: function (response) {
                if (response.success) {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Schedule created successfully!', showConfirmButton: false, timer: 1500 });
                    bootstrap.Modal.getInstance(document.getElementById("doctor_add_schedule_modal")).hide();
                    loadDashboard();
                } else {
                    Swal.fire({ title: "Error", text: response.message || "Failed to create schedule.", icon: "error" });
                }
            }
        });
    });

    // Detailed Comment: Edit schedule
    $(document).on("click", ".edit-doctor-schedule", function () {
        const schedrefno = $(this).val();
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
                }
            }
        });
    });

    // Detailed Comment: Update schedule
    $(document).on("click", "#update_doctor_schedule_btn", function () {
        const form = document.getElementById("doctor_edit_schedule_form");
        if (!form.checkValidity()) return form.reportValidity();

        const start = $("#edit_sched_start").val();
        const end = $("#edit_sched_end").val();
        if (start >= end) {
            return Swal.fire({ title: "Invalid Time Range", text: "Start time must be earlier than end time.", icon: "warning" });
        }

        $.ajax({
            url: "/api/doctor/edit_schedule",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: $("#doctor_edit_schedule_form").serialize(),
            success: function (response) {
                if (response.success) {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Schedule updated successfully!', showConfirmButton: false, timer: 1500 });
                    bootstrap.Modal.getInstance(document.getElementById("doctor_edit_schedule_modal")).hide();
                    loadDashboard();
                }
            }
        });
    });

    // Detailed Comment: Delete schedule
    $(document).on("click", ".delete-doctor-schedule", function () {
        const schedrefno = $(this).val();
        Swal.fire({
            title: "Delete Schedule?",
            text: "Are you sure you want to delete this clinic schedule?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
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
                            loadDashboard();
                        }
                    }
                });
            }
        });
    });

    // Consult tab
    $("#consult_tab").on("click", function () {
        $("#consul_date").val(new Date().toISOString().split('T')[0]);
        getPatientsFromDate($("#consul_date").val());
    });

    // Date and date navigation
    $("#consul_date").on("change", function () {
        getPatientsFromDate($(this).val());
    });

    $("#previous").on("click", function () {
        let dateInp = $("#consul_date").val();
        let date = new Date(dateInp);
        date.setDate(date.getDate() - 1);

        $("#consul_date").val(date.toISOString().split("T")[0]);
        getPatientsFromDate($("#consul_date").val());
    });

    $("#next").on("click", function () {
        let dateInp = $("#consul_date").val();
        let date = new Date(dateInp);
        date.setDate(date.getDate() + 1);

        $("#consul_date").val(date.toISOString().split("T")[0]);
        getPatientsFromDate($("#consul_date").val());
    });

    function getPatientsFromDate(value) {
        $("#consultation_table").DataTable().clear().destroy();
        $("#consultation_table").DataTable({
            ajax: {
                url: "fetch_doctor_consultations",
                type: "POST",
                data: {
                    date: value
                },
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                dataSrc: 'patients'
            },
            select: {
                style: 'single'
            },
            columns: [
                {
                    data: null,
                    render: function (data, type, row) {
                        return `<span data-consultation="${row.consultationrefno}">${(data.patientname + ' ' + data.pxmidname + ' ' + data.pxlastname).trim()}</span>`;
                    }
                },
                {
                    data: 'status',
                    render: function (data) {
                        let badgeClass = 'bg-secondary fs-6'; // Default

                        if (data === 'WAITING') badgeClass = 'bg-warning text-white text-dark fs-6';
                        if (data === 'IN_CONSULTATION') badgeClass = 'bg-info text-white fs-6';
                        if (data === 'COMPLETED') badgeClass = 'bg-success fs-6';
                        if (data === 'UNSCHEDULED') badgeClass = 'bg-primary fs-6';
                        if (data === 'CANCELLED' || data === 'NO_SHOW') badgeClass = 'bg-danger fs-6';

                        return `<span class="badge ${badgeClass}">${data}</span>`;
                    }
                }
            ],
            columnDefs: [
                {
                    target: 1,
                    width: '1%',
                    orderable: false,
                    searchable: false,
                    className: 'text-nowrap text-center align-middle'
                }
            ],
            language: {
                emptyTable: "No patients yet."
            },
            lengthChange: false,
            paging: true,
            searching: false,
            ordering: false,
            responsive: true,
            info: false
        });
    }

    let rowConsultationRefno = null;
    $("#consultation_table").on("select.dt", function (e, dt, type, indexes) {
        let rowData = dt.row(indexes[0]).data();
        rowConsultationRefno = rowData.consultationrefno;

        $.ajax({
            url: "fetch_patient_data",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr('content') },
            data: { consultationrefno: rowConsultationRefno },
            success: function (response) {
                $("#px_photo_preview").prop("src", response.patient.photo_path);
                $("#pxname").text([response.patient.patientname, response.patient.pxmidname, response.patient.pxlastname, response.patient.pxsuffix].filter(v => v).join(' '));
                $("#pxsex").text(response.patient.gender);
                $("#pxbday").text(new Date((response.patient.birthday).replace(" ", "T")).toLocaleDateString("en-US", { month: "long", day: "numeric", year: "numeric" }));
                $("#pxage").text(calculateAge(response.patient.birthday));
                $("#pxcellno").text(response.patient.mobilenumber);
                $("#pxemail").text(response.patient.emailaddress);
                $("#pxaddress").text(response.patient.address);
                $("#pxweight").text(response.patient.weight != null ? response.patient.weight + response.patient.wunit : '');
                $("#pxheight").text(response.patient.height != null ? response.patient.height + response.patient.hunit : '');
                $("#pxtemp").text(response.patient.temp != null ? response.patient.temp + response.patient.tempunit : '');
                $("#pxresprate").text(response.patient.respiratoryrate);
                $("#pxpulserate").text(response.patient.pulserate);
                $("#pxbp").text(response.patient.bpnumerator + response.patient.bpdenominator);
                $("#rfc").val(response.patient.reasonforconsultation ?? '');
            }
        });
    });

    $("#consultation_table").on("deselect.dt", function (e, dt, type, indexes) {
        rowConsultationRefno = null;
        $("#px_photo_preview").prop("src", null);
        $("#pxname, #pxsex, #pxbday, #pxage, #pxcellno, #pxemail, #pxaddress").text("");
        $("#pxweight, #pxheight, #pxtemp, #pxresprate, #pxpulserate, #pxbp").text("");
        $("#rfc").val("");
    });

    function calculateAge(birthday) {
        const birthDate = new Date(birthday);
        const today = new Date();

        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();

        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        return age;
    }

    // Consult patient
    $("#consult").on("click", function () {
        loadConsultModal()
    });

    function loadConsultModal() {
        const consultationModal = new bootstrap.Modal("#consultation_modal");

        $.ajax({
            url: "fetch_patient_data",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: {
                consultationrefno: rowConsultationRefno
            },
            success: function (response) {
                consultationModal.show();

                $("#medical_questions_tab_btn").trigger("click");

                if (response.patient != null) {
                    $("#consultationrefno").val(response.patient.consultationrefno);
                    $("#consulname").text([response.patient.patientname, response.patient.pxmidname, response.patient.pxlastname, response.patient.pxsuffix].filter(v => v).join(' '));
                    $("#consulsex").text(response.patient.gender);
                    $("#consulbday").text(new Date((response.patient.birthday).replace(" ", "T")).toLocaleDateString("en-US", { month: "long", day: "numeric", year: "numeric" }));
                    $("#consulage").text(calculateAge(response.patient.birthday));
                    $("#consulcellno").text(response.patient.mobilenumber);
                    $("#consulemail").text(response.patient.emailaddress);
                    $("#consuladdress").text(response.patient.address);
                    $("#consulweight").text(response.patient.weight != null ? response.patient.weight + response.patient.wunit : '');
                    $("#consulheight").text(response.patient.height != null ? response.patient.height + response.patient.hunit : '');
                    $("#consultemp").text(response.patient.temp != null ? response.patient.temp + response.patient.tempunit : '');
                    $("#consulresprate").text(response.patient.respiratoryrate);
                    $("#consulpulserate").text(response.patient.pulserate);
                    $("#consulbp").text(response.patient.bpnumerator != null && response.patient.bpdenominator != null ? (`${response.patient.bpnumerator}/${response.patient.bpdenominator}`) : '');
                    $("#patient_instructions").val(response.patient.instructions);

                    $("#consultationrefno").val(response.patient.consultationrefno);

                    $("#genname").text([
                        response.patient.patientname,
                        // response.patient.pxmidname,
                        // response.patient.pxlastname,
                        // response.patient.pxsuffix
                    ].filter(v => v).join(' '));

                    $("#gensex").text(response.patient.gender);

                    $("#genbday").text(
                        new Date(response.patient.birthday.replace(" ", "T"))
                            .toLocaleDateString("en-US", {
                                month: "long",
                                day: "numeric",
                                year: "numeric"
                            })
                    );

                    $("#genage").text(calculateAge(response.patient.birthday));

                    $("#gencellno").text(response.patient.mobilenumber);
                    $("#genemail").text(response.patient.emailaddress);
                    $("#genaddress").text(response.patient.address);

                    $("#genweight").text(response.patient.weight != null ? response.patient.weight + response.patient.wunit : '');
                    $("#genheight").text(response.patient.height != null ? response.patient.height + response.patient.hunit : '');
                    $("#gentemp").text(response.patient.temp != null ? response.patient.temp + response.patient.tempunit : '');

                    $("#genresprate").text(response.patient.respiratoryrate);
                    $("#genpulserate").text(response.patient.pulserate);
                    $("#genbp").text(response.patient.bpnumerator != null && response.patient.bpdenominator != null ? (`${response.patient.bpnumerator}/${response.patient.bpdenominator}`) : '');

                    // Detailed Comment: Fix print button URLs to respect application subfolder base path (/kayakapmd_clinic)
                    const basePath = window.location.pathname.startsWith('/kayakapmd_clinic') ? '/kayakapmd_clinic' : '';
                    $("#print_rx_btn").attr("href", `${basePath}/print_pdf?type=rx&consultationrefno=${rowConsultationRefno}`);
                    $("#print_inst_btn").attr("href", `${basePath}/print_pdf?type=instructions&consultationrefno=${rowConsultationRefno}`);

                    $("#reasonforconsultation").val(response.patient.reasonforconsultation);
                    $("#impressions").val(response.patient.impression);
                    $("#diagnosis").val(response.patient.finadiagnosis);
                }

                loadQuestions();
                loadMedicalHistory();
                loadDashboardRx();
            }
        });
    }

    function loadQuestions() {
        $("#patient_questions_answer").DataTable().destroy().clear();
        $("#patient_questions_answer").DataTable({
            columnDefs: [
                {
                    target: 0,
                    width: '1%',
                    orderable: false,
                    searchable: false,
                    className: 'text-nowrap text-center align-middle'
                }
            ],
            language: {
                emptyTable: "No records yet."
            },
            lengthChange: false,
            info: false,
            paging: true,
            searching: false,
            ordering: false,
            responsive: true
        });
    }

    function loadDashboardRx() {
        $("#dashboard_rx_table").DataTable().destroy().clear();
        $("#dashboard_rx_table").DataTable({
            ajax: {
                url: "fetch_medicine_rx",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: {
                    consultationrefno: $("#consultationrefno").val()
                },
                dataSrc: "rx"
            },
            columns: [
                { data: 'medicinename' },
                { data: 'medicinedosage' },
                { data: 'medicineduration' },
                { data: 'medicinequantity' },
            ],
            columnDefs: [
                {
                    targets: [1, 2, 3],
                    width: '10%',
                    orderable: false,
                    searchable: false,
                    className: 'text-nowrap text-center align-middle'
                }
            ],
            language: {
                emptyTable: "No records yet."
            },
            pageLength: 5,
            lengthChange: false,
            info: false,
            paging: true,
            searching: false,
            ordering: false,
            responsive: true,
            initComplete: function (settings, json) {
                $("#pxinstructions").text(json.instructions ? json.instructions[0] : '');
            }
        });
    }

    $("#doctor_questions_btn").on("click", function () {
        let formData = $("#doctor_questions_form").serialize();
        formData += '&consultationrefno=' + encodeURIComponent($("#consultationrefno").val());

        $.ajax({
            url: "add_question_answer",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: formData,
            success: function (response) {
                if (response.success) {
                    loadQuestions();
                }
            }
        })
    });

    $("#add_question_btn").on("click", function () {
        const consulModal = bootstrap.Modal.getInstance(document.getElementById("consultation_modal"));
        consulModal.hide();

        Swal.fire({
            title: "Question",
            html: `
                <input id="mdquestion" class="form-control" placeholder="Question">
                <textarea id="mdanswer" class="form-control mt-2" placeholder="Enter patient answer" rows="10"></textarea>
            `,
            focusConfirm: false,
            showCancelButton: true,
            confirmButtonText: "Save",
            preConfirm: () => {
                const question = document.getElementById("mdquestion").value;
                const answer = document.getElementById("mdanswer").value;

                if (!question || !answer) {
                    Swal.showValidationMessage("All fields are required");
                    return false;
                }

                return { question, answer };
            }
        }).then((result) => {
            consulModal.show();

            if (result.isConfirmed) {
                $.ajax({
                    url: "add_question",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: {
                        consultationrefno: $("#consultationrefno").val(),
                        question: $("#mdquestion").val(),
                        answer: $("#mdanswer").val()
                    }
                })
            }
        });
    });

    $("#masterlist_tab").on("click", function () {
        $("#masterlist_table").DataTable().clear().destroy();
        $("#masterlist_table").DataTable({
            ajax: {
                url: "fetch_consultation_masterlist",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr('content') },
                // dataSrc: 'patients'
            },
            columns: [
                {
                    data: 'consultationrefno',
                    render: function (data) {
                        return `<button class="btn btn-sm btn-primary masterlist-view" value="${data}"><i class="fa-solid fa-eye"></i> View</button>`;
                    }
                },
                {
                    data: 'photo_path',
                    render: function (data) {
                        return `<img src="${data}" alt="Patient photo" style="max-width: 100px;">`;
                    }
                },
                {
                    data: null,
                    render: function (data) {
                        let fullName = data.pxlastname || '';
                        if (data.patientname) fullName += `, ${data.patientname}`;
                        if (data.pxmidname) fullName += ` ${data.pxmidname}`;
                        if (data.pxsuffix) fullName += ` ${data.pxsuffix}`;

                        return fullName.trim().toUpperCase();
                    }
                },
                // { data: 'reasonforconsultation' },
                {
                    data: 'consultation_date',
                    render: function (data) {
                        return new Date(data).toLocaleDateString("en-US", {
                            month: "long",
                            day: "2-digit",
                            year: "numeric"
                        });
                    }
                },
                // { data: 'status' }
            ],
            columnDefs: [
                {
                    target: 0,
                    width: '1%',
                    orderable: false,
                    searchable: false,
                    className: 'text-nowrap text-center align-middle'
                },
                {
                    targets: [1, 3],
                    width: '1%',
                    orderable: false,
                    className: 'text-nowrap align-middle'
                }
            ],
            language: {
                emptyTable: "No patients yet."
            },
            order: [[4, 'asc']],
            pageLength: 5,
            lengthChange: false,
            paging: true,
            searching: true,
            ordering: true,
            responsive: true
        });
    });

    $(document).on("click", ".masterlist-view", function () {
        const infoModal = new bootstrap.Modal("#viewPatientModal");

        $.ajax({
            url: "fetch_patient_data",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
            },
            data: {
                consultationrefno: $(this).val()
            },
            success: function (response) {
                infoModal.show();

                $("#view_memPin").val(response.patient.pincode);
                $("#view_pMemFname").val(response.patient.memFname);
                $("#view_pMemMname").val(response.patient.memMname);
                $("#view_pMemLname").val(response.patient.memLname);
                $("#view_pMemExtname").val(response.patient.memExtname);
                $("#view_pMemDob").val(response.patient.memDob);

                $("#view_pPatientFname").val(response.patient.patientname);
                $("#view_pPatientMname").val(response.patient.pxmidname);
                $("#view_pPatientLname").val(response.patient.pxlastname);
                $("#view_pPatientExtname").val(response.patient.suffix);
                $("#view_pPatientDob").val(response.patient.birthday);
                $("#view_pPatientSex").val(response.patient.birthday);
                $("#view_pPatientMobileNo").val(response.patient.mobilenumber);
                $("#view_email").val(response.patient.emailaddress);
                $("#view_address").val(response.patient.address);
            }
        })
    });

    // RX
    $("#mymed").autocomplete({
        source: function (request, response) {
            $.ajax({
                url: "/api/fetch_medicines",
                type: "POST",
                dataType: "json",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr('content') },
                data: {
                    term: request.term
                },
                success: function (data) {
                    response(data);
                }
            });
        },
        minLength: 1,
        appendTo: "#rx_modal",
        select: function (event, ui) {
            $("#mymed").val(ui.item.value);
            $("#drug_code").val(ui.item.code);
            return false;
        }
    });

    $("#gen_rx_btn").on("click", function () {
        loadRx();
    });

    $("#usemyrx").on("click", function () {
        loadRx();
    });

    $("#createrx").on("click", function () {
        $("#create_new_rx_table").DataTable().destroy().clear();
        $("#create_new_rx_table").DataTable({
            language: {
                emptyTable: "No records yet."
            },
            pageLength: 5,
            info: false,
            lengthChange: false,
            paging: true,
            searching: true,
            ordering: false,
            responsive: true
        });
    });

    $("#add_rx").on("click", function () {
        $.ajax({
            url: "add_medicine",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: {
                consultationrefno: $("#consultationrefno").val(),
                drug_code: $("#drug_code").val(),
                mydosage: $("#mydosage").val(),
                myduration: $("#myduration").val(),
                myquantity: $("#myquantity").val()
            },
            success: function (response) {
                if (response.success) {
                    loadRx();
                }
            }
        });
    });

    $(document).on("click", ".delete_rx", function () {
        $.ajax({
            url: "delete_rx",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: {
                rxreferenceno: $(this).val()
            },
            success: function (response) {
                if (response.success) {
                    var toast = new bootstrap.Toast("#notif");
                    toast.show();
                    loadRx();
                }
            }
        });
    })

    function loadRx() {
        $("#rx_table").DataTable().destroy().clear();
        $("#rx_table").DataTable({
            ajax: {
                url: "fetch_medicine_rx",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: { consultationrefno: $("#consultationrefno").val() },
                dataSrc: 'rx'
            },
            columns: [
                {
                    data: null,
                    render: function (data) {
                        return `<button class="btn btn-sm btn-danger delete_rx" value="${data.rxreferenceno}"><i class="fa-solid fa-trash"></i></button>`;
                    }
                },
                { data: 'medicinename' },
                { data: 'medicinedosage' },
                { data: 'medicineduration' },
                { data: 'medicinequantity' },
            ],
            columnDefs: [
                {
                    targets: [0, 2, 3, 4],
                    width: '10%',
                    orderable: false,
                    searchable: false,
                    className: 'text-nowrap text-center align-middle'
                }
            ],
            language: {
                emptyTable: "No records yet."
            },
            pageLength: 5,
            info: false,
            lengthChange: false,
            paging: true,
            searching: true,
            ordering: false,
            responsive: true
        });
        $("#myrx_form")[0].reset();
    }

    $("#save_consul").on("click", function () {
        Swal.fire({
            title: "Save Consultation?",
            text: "This will mark the session as done.",
            icon: "success",
            confirmButtonText: "Confirm",
            showCancelButton: true,
        }).then((result) => {
            if (result.isConfirmed) {
                let constModal = bootstrap.Modal.getInstance(document.getElementById("consultation_modal"));

                $.ajax({
                    url: "complete_consultation",
                    type: "POST",
                    headers: {
                        "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                    },
                    data: {
                        consultationrefno: $("#consultationrefno").val()
                    },
                    success: function (response) {
                        if (response.success) {
                            constModal.hide();
                            $("#consultation_table").DataTable().ajax.reload();
                        }
                    }
                })
            }
        });
    });

    $("#save_rx_btn").on("click", function () {
        $.ajax({
            url: "save_rx", // Just saves instructions really
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: {
                consultationrefno: $("#consultationrefno").val(),
                pxinstructions: $("#pxinstructions").val()
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title: "Success",
                        text: "Successfully saved Rx.",
                        icon: "success"
                    });
                }
            }
        });
    });

    $(document).on("click", ".return_btn", function () {
        loadDashboardRx();
    });

    // Diagnostics-related
    $("#dReqsTabBtn").on("click", function () {
        $("#diagnostics_table").DataTable().destroy().clear();
        $("#diagnostics_table").DataTable({
            ajax: {
                url: "get_diagnostics",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                },
                data: {
                    consultationrefno: $("#consultationrefno").val()
                },
                dataSrc: "requested"
            },
            columns: [
                {
                    data: null,
                    render: function (data) {
                        return `<button class="btn btn-sm btn-danger remove_request" value="${data.diagnostic_id}"><i class="fa-solid fa-trash"></i> Remove</button>`;
                    }
                },
                { data: 'diagnostic_name' }
            ],
            columnDefs: [
                {
                    target: 0,
                    width: "1%",
                    orderable: false,
                    className: "text-center text-nowrap"
                },
                {
                    target: 1,
                    className: 'text-nowrap align-middle'
                }
            ],
            info: true,
            lengthChange: false,
            searching: false
        });

        $("#print_diagnostics").attr("href", `print_diagnostics?consultationrefno=${$("#consultationrefno").val()}`);
    });

    $("#diag_to_consul").on("click", function () {
        $("#diagnostics_table").DataTable().ajax.reload();
    });

    let diagnostics = [];
    $("#diagnostic_btn").on("click", function () {
        $.ajax({
            url: "fetch_diagnostics_data",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { consultationrefno: $("#consultationrefno").val() },
            success: function (response) {
                let tablist = $("#diagnostic_tablist");
                let tabpane = $("#diagnostic_tabpane");
                let tabindex = 0;

                tablist.empty();
                tabpane.empty();
                response.categories.forEach(e => {
                    const btn = `
                        <li class="nav-item" role="presentation">
                            <button class="nav-link ${tabindex == 0 ? 'active' : ''}"
                                type="button"
                                data-bs-toggle="tab"
                                data-bs-target="#${e.category_refno}"
                                aria-controls="${e.category_refno}tab"
                            >
                                ${e.category_name}
                            </button>
                        </li>
                    `;

                    const pane = `
                        <div class="tab-pane ${tabindex == 0 ? 'active' : ''}"
                            id="${e.category_refno}"
                            role="tabpanel"
                            aria-labelledby="${e.category_refno}tab"
                            tabindex="0"
                        >
                        <div class="d-grid gap-3"
                            style="grid-template-columns: repeat(2, 1fr)"
                            id="${e.category_refno}cb"></div>
                        </div>
                    `;

                    tablist.append(btn);
                    tabpane.append(pane);
                    tabindex++;
                });

                let requestedId = response.requested.map(r => r.diagnosticrefno);
                let allDiagnostics = [...response.available, ...response.requested]

                allDiagnostics.sort((a, b) => a.diagnostic_name.localeCompare(b.diagnostic_name));
                allDiagnostics.forEach(e => {
                    let isReqs = requestedId.includes(e.diagnosticrefno);
                    const entry = `
                        <div class="form-check">
                            <input class="form-check-input diagnostic_check"
                                type="checkbox"
                                value="${e.diagnosticrefno}"
                                id="diag_${e.diagnosticrefno}"
                                name="${e.diagnosticrefno}"
                                ${isReqs ? 'checked disabled' : ''}
                            >
                            <label class="form-check-label" for="${e.diagnosticrefno}">
                                ${e.diagnostic_name}
                            </label>
                        </div>
                    `;

                    $(`#${e.diagnostic_catg}cb`).append(entry);
                });

                // Sync table with requested
                var table = $("#selected_table").DataTable();
                table.clear();

                response.requested.forEach(element => {
                    table.row.add([
                        `<button class="btn btn-sm btn-secondary" disabled><i class="fa-solid fa-floppy-disk"></i> Saved</button>`,
                        element.diagnostic_name
                    ]);
                });

                table.draw();
            }
        });

        $("#selected_table").DataTable().destroy().clear();
        $("#selected_table").DataTable({
            columnDefs: [
                {
                    target: 0,
                    width: "1%",
                    orderable: false,
                    className: "text-nowrap text-center"
                },
                {
                    target: 1,
                    className: "text-nowrap align-middle"
                }
            ],
            pageLength: 10,
            lengthChange: false,
            searching: false,
            info: false
        });
    });

    $(document).on("change", ".diagnostic_check", function () {
        var table = $("#selected_table").DataTable();

        if ($(this).is(":checked")) {
            let value = $(this).attr("name");
            table.row.add([
                `<button class="btn btn-sm btn-danger remove-request-btn" data-id="${value}"><i class="fa-solid fa-trash"></i> Remove</button>`,
                $(this).text()
            ]).draw();

            diagnostics.push(value);
            $(this).prop("disabled", true);
        }
    });

    $(document).on("click", ".remove-request-btn", function () {
        var table = $("#selected_table").DataTable();
        let diagId = $(this).data("id");

        table.row($(this).closest("tr")).remove().draw();
        diagnostics = diagnostics.filter(ref => ref != diagId);
        $(`.diagnostic_check[name='${diagId}']`).prop("checked", false).prop("disabled", false);
    });

    $("#save_requests").on("click", function () {
        if (diagnostics.length === 0) {
            return;
        }

        $.ajax({
            url: "save_diagnostics",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
            },
            data: {
                consultationrefno: $("#consultationrefno").val(),
                diagnostics: diagnostics
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title: "Success",
                        text: "Diagnostics saved successfully.",
                        icon: "success"
                    });
                }
            }
        });
    });

    $(document).on("click", ".remove_request", function () {
        Swal.fire({
            title: "Confirmation",
            text: "Do you want to remove this request?",
            icon: "warning"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "delete_diagnostic",
                    type: "POST",
                    headers: {
                        "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                    },
                    data: {
                        consultationrefno: $("#consultationrefno").val(),
                        requestrefno: $(this).val()
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Status updated',
                                showConfirmButton: false,
                                timer: 2000
                            }).then(() => {
                                $("#diagnostics_table").DataTable().ajax.reload();
                            });
                        }
                    }
                });
            }
        });
    });

    // Detailed Comment: Self-service Doctor Profile modal lifecycle (populate 5 tabs and save updates)
    const doctorProfileModalEl = document.getElementById("doctor_profile_modal");
    if (doctorProfileModalEl) {
        doctorProfileModalEl.addEventListener("show.bs.modal", () => {
            $.ajax({
                url: "/api/fetch_doctor_data",
                type: "POST",
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

    // Append charges modal
    $("#append_charge_btn").on("click", function () {
        appendedCharges = [];
        $("#appended_charges_table tbody").empty();

        $.ajax({
            url: "fetch_charge_categories_dr",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            success: function (response) {
                $("#search_filter").empty();
                $("#search_filter").append(`<option value="">No filter</option>`).prop("selected", true);

                response.categories.forEach(category => {
                    $("#search_filter").append(`<option value="${category.categoryrefno}">${category.categoryname}</option>`);
                });
            }
        });

        $.ajax({
            url: "fetch_patient_charges",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: {
                consultationrefno: $("#consultationrefno").val()
            },
            success: function (response) {
                response.charges.forEach(charge => {
                    appendedCharges.push(charge.servicerefno);

                    const newRow = `
                        <tr data-refno="${charge.servicerefno}">
                            <td class="align-middle text-center text-nowrap">
                                <button type="button"
                                        class="btn btn-sm btn-secondary charge_entry"
                                        data-refno="${charge.servicerefno}"
                                        disabled
                                        title="Appended charges can only be removed from the patient charges tab.">
                                    <i class="fa-solid fa-square-minus"></i>
                                </button>
                            </td>
                            <td>${charge.servicename}</td>
                            <td>${charge.discount}</td>
                            <td>${charge.net_total}</td>
                        </tr>
                    `;

                    $("#appended_charges_table tbody").append(newRow);
                });
            }
        });
    });

    let cache = null;
    let loading = false;
    $("#search_charge").autocomplete({
        minLength: 0,

        source: function (request, response) {
            if (cache) {
                response(
                    cache.filter(i =>
                        i.label.toLowerCase().includes(request.term.toLowerCase())
                    )
                );
                return;
            }

            if (loading) return;
            loading = true;

            $.getJSON("fetch_all_charges", {
                category: $("#search_filter").val() || "",
                consultationrefno: $("#consultationrefno").val()
            }, function (data) {

                cache = data.charges.map(item => ({
                    label: item.charge_name,
                    value: item.chargerefno
                }));

                loading = false;

                response(
                    cache.filter(i =>
                        i.label.toLowerCase().includes(request.term.toLowerCase())
                    )
                );
            });
        },

        select: function (event, ui) {
            $("#search_charge").val(ui.item.label); // show name
            $("#charge_code").val(ui.item.value);  // store ref no
            return false;
        },

        focus: function (event, ui) {
            $("#search_charge").val(ui.item.label);
            return false;
        }
    });

    $("#search_filter").on("change", function () {
        cache = null;
        $("#search_charge").val("");
        $("#charge_code").val("");
    });

    $("#charge_category").on("change", function () {
        cache = null;
    });

    let appendedCharges = [];
    $("#append_to_charges_btn").on("click", function () {
        let form = document.getElementById("appended_charges_form");

        if (!form.checkValidity()) {
            return form.reportValidity();
        }

        const chargeRefno = $("#charge_code").val();
        const chargeName = $("#search_charge").val();
        const chargeAmt = $("#charge_amount").val();
        const chargeDiscount = $("#charge_discount").val();

        if (!chargeRefno || !chargeName) {
            Swal.fire({
                title: "Error",
                text: "Please select a charge to append.",
                icon: "error"
            });
            return;
        }

        if (appendedCharges.some(c => c.refno === chargeRefno)) {
            Swal.fire({
                title: "Error",
                text: "This charge has already been appended.",
                icon: "error"
            });
            return;
        }

        appendedCharges.push({
            refno: chargeRefno,
            discount: chargeDiscount,
            amount: chargeAmt
        });

        const newRow = `
            <tr data-refno="${chargeRefno}">
                <td class="align-middle text-center text-nowrap">
                    <button type="button"
                            class="btn btn-sm btn-danger charge_entry"
                            data-refno="${chargeRefno}">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </td>
                <td>${chargeName}</td>
                <td>${chargeDiscount}</td>
                <td>${chargeAmt}</td>
            </tr>
        `;

        $("#appended_charges_table tbody").append(newRow);

        $("#search_charge").val("");
        $("#charge_code").val("");
    });

    $(document).on("click", ".charge_entry", function () {
        const chargeRefno = $(this).data("refno");

        appendedCharges = appendedCharges.filter(ref => ref !== chargeRefno);

        $(this).closest("tr").remove();
    });

    $("#save_charges_btn").on("click", function () {
        if (appendedCharges.length === 0) {
            Swal.fire({
                title: "Error",
                text: "Please append at least one charge before saving.",
                icon: "error"
            });
            return;
        }

        $.ajax({
            url: "save_patient_charges",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: {
                consultationrefno: $("#consultationrefno").val(),
                chargerefnos: appendedCharges
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title: "Success",
                        text: "Charges successfully saved.",
                        icon: "success"
                    });
                    appendedCharges = [];
                    $("#appended_charges_list").empty();
                }
            }
        });

        $("#patient_charge_tab_btn").trigger("click");
    });

    // Show patient charges tab
    $("#patient_charge_tab_btn").on("click", function () {
        $("#charges_table").DataTable().destroy().clear();
        $("#charges_table").DataTable({
            ajax: {
                url: "fetch_patient_charges",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: {
                    consultationrefno: $("#consultationrefno").val()
                },
                dataSrc: function (response) {
                    let total = 0;

                    response.charges.forEach(charge => {
                        total += parseFloat(charge.net_total);
                    });

                    $("#charges_total").text('₱' + total.toFixed(2));

                    return response.charges;
                }
            },
            columns: [
                {
                    data: null,
                    render: function (data) {
                        return `
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-danger remove_charge_btn" value="${data.pxchargerefno}"><i class="fa-solid fa-trash"></i></button>
                                <button class="btn btn-sm btn-primary edit_charge_btn" value="${data.pxchargerefno}"><i class="fa-solid fa-pen-to-square"></i></button>
                            </div>
                            `;
                    }
                },
                {
                    data: 'servicename'
                },
                {
                    data: 'discount'
                },
                {
                    data: 'net_total'
                }
            ],
            columnDefs: [
                {
                    target: 0,
                    width: '1%',
                    orderable: false,
                    className: 'text-nowrap text-center align-middle'
                }
            ],
            // Detailed Comment: In DataTables 2, layout uses function callbacks for custom DOM elements to prevent "Unknown feature: div" warning.
            layout: {
                bottomStart: 'paging',
                bottomEnd: function () {
                    const el = document.createElement('div');
                    el.className = 'd-flex align-items-center mt-2';
                    el.innerHTML = '<h4 class="fw-bold m-0">Total: ₱<span class="fw-normal ms-2" id="charges_total">0.00</span></h4>';
                    return el;
                }
            },
            lengthChange: false,
            pageLength: 10,
            info: false,
            paging: true,
            searching: false,
            ordering: false,
            responsive: true,
        });
    });

    // Edit and deletion of patient charges
    $(document).on("click", ".remove_charge_btn", function () {
        Swal.fire({
            title: "Remove Charge?",
            text: "Are you sure you want to remove this charge from the patient's consultation?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Confirm"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "delete_patient_charge",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: {
                        chargeid: $(this).val(),
                        consultationrefno: $("#consultationrefno").val()
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                title: "Success",
                                text: "Charge successfully removed.",
                                icon: "success"
                            });
                            $("#patient_charge_tab_btn").trigger("click");
                        }
                    }
                });
            }
        });
    });

    $(document).on("click", ".edit_charge_btn", function () {
        const consulModal = bootstrap.Modal.getInstance(document.getElementById("consultation_modal"));
        consulModal.hide();

        Swal.fire({
            title: "Edit Charge",
            html: `
                <div class="d-flex gap-2">
                    <div class="">
                        <label class="form-label" for="charge_input_sw">Charge Fee</label>
                        <input class="form-control" type="number" name="charge_input_sw" id="charge_input_sw">
                    </div>

                    <div class="">
                        <label class="form-label" for="charge_input_sw">Charge Discount</label>
                        <input class="form-control" type="number" name="discount_input_sw" id="discount_input_sw">
                    </div>
                </div>
            `,
            confirmButtonText: "Update",
            showCancelButton: true,
            preConfirm: () => {
                const charge = document.getElementById("charge_input_sw").value;
                // const discount = document.getElementById("discount_input_sw").value;

                if (!charge) {
                    Swal.showValidationMessage("Input field is empty.");
                    return false;
                }

                return true;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                consulModal.show();

                $.ajax({
                    url: "update_charge",
                    type: "POST",
                    headers: {
                        "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                    },
                    data: {
                        consultationrefno: $("#consultationrefno").val(),
                        pxchargerefno: $(this).val(),
                        charge_fee: $("#charge_input_sw").val(),
                        discount: $("#discount_input_sw").val()
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Charge updated',
                                showConfirmButton: false,
                                timer: 2000
                            });

                            $("#charges_table").DataTable().ajax.reload();
                        }
                    }
                });
            } else {
                consulModal.show();
            }
        });
    });

    // Fetch radiology and laboratory files
    $("#radLabTabBtn").on("click", function () {
        loadMedicalFiles();
    });

    function loadMedicalFiles() {
        $("#radiology_result").val("");
        $("#radiology_hasfile").removeClass("d-inline-block").addClass("d-none");
        $("#preview_radiology").prop("disabled", true);

        $("#laboratory_result").val("");
        $("#laboratory_hasfile").removeClass("d-inline-block").addClass("d-none");
        $("#preview_laboratory").prop("disabled", true);

        $.ajax({
            url: "fetch_radlab_files",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
            },
            data: {
                consultationrefno: $("#consultationrefno").val()
            },
            success: function (response) {
                if (response.files.radiologypath) {
                    $("#radiology_hasfile").removeClass("d-none").addClass("d-inline-block");

                    $("#preview_radiology").prop("disabled", false);
                    $("#preview_radiology").attr("data-filepath", response.files.radiologypath);
                }

                if (response.files.laboratorypath) {
                    $("#laboratory_hasfile").removeClass("d-none").addClass("d-inline-block");

                    $("#preview_laboratory").prop("disabled", false);
                    $("#preview_laboratory").attr("data-filepath", response.files.laboratorypath);
                }
            }
        });
    }

    // Enable preview button on file upload/input
    $("#radiology_result").on("change", function () {
        $("#preview_radiology").prop("disabled", false);
    });

    $("#laboratory_result").on("change", function () {
        $("#preview_laboratory").prop("disabled", false);
    });

    $("[id^=preview_]").on("click", function () {
        const type = this.id.replace("preview_", "");
        const fileInput = document.getElementById(type + "_result");
        const storedPath = this.dataset.filepath;

        let fileURL = "";

        if (fileInput && fileInput.files.length) {
            fileURL = URL.createObjectURL(fileInput.files[0]);
        } else if (storedPath && storedPath.trim() !== "") {
            fileURL = "/preview-file/" + storedPath;
        }

        if (!fileURL) return;

        const consulModal = bootstrap.Modal.getInstance(document.getElementById("consultation_modal"));
        consulModal.hide();

        const previewModalEl = document.getElementById("preview_modal");
        previewModalEl.dataset.src = fileURL;
        const previewModal = bootstrap.Modal.getOrCreateInstance(previewModalEl);
        previewModal.show();
    });

    $("#preview_modal").on("show.bs.modal", function () {
        const src = this.dataset.src;
        document.getElementById("docPreview").src = src;
        document.getElementById("docPreview").style.display = "block";
    });

    $("#preview_modal").on("hidden.bs.modal", function () {
        document.getElementById("docPreview").src = "";
        document.getElementById("docPreview").style.display = "none";

        const consulModal = bootstrap.Modal.getInstance(document.getElementById("consultation_modal"));
        consulModal.show();
    });

    $("#update_files_btn").on("click", function () {
        let formData = new FormData($("#rad_lab_form")[0]);
        formData.append("consultationrefno", $("#consultationrefno").val());

        $.ajax({
            url: "upload_consultation_files",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Status updated',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            }
        });
    });

    $("#save_impressions_diagnosis").on("click", function () {
        $.ajax({
            url: "save_impressions_dignosis",
            type: "POST",
            data: {
                consultationrefno: $("#consultationrefno").val(),
                reasonforconsultation: $("#reasonforconsultation").val(),
                impressions: $("#impressions").val(),
                diagnosis: $("#diagnosis").val()
            },
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Details saved',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            }
        });
    });
});
