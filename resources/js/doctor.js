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
                        <td class="ps-4">
                            ${convert24To12(sched.start)} - ${convert24To12(sched.end)}
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

                    $("#print_rx_btn").attr("href", `print_pdf?type=rx&consultationrefno=${rowConsultationRefno}`);
                    $("#print_inst_btn").attr("href", `print_pdf?type=instructions&consultationrefno=${rowConsultationRefno}`);

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

    // Profile-related
    if ($("#doctorPage").length) {
        const modalEl = document.getElementById("doctor_profile_modal");
        modalEl.addEventListener("shown.bs.modal", () => {
            $.ajax({
                url: "fetch_doctor_data",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                success: function (response) {
                    const user = response.user;
                    const map = {
                        "#doc_fullname": [user.docfname, user.docmname, user.doclname, user.suffix].filter(Boolean).join(" "),
                        "#doc_title": user.titlename,
                        "#doc_contact": user.cellno,
                        "#doc_email": user.emailadd,
                        "#doc_lic": user.Licno,
                        "#doc_lic_expiry": user.licnoexpiry,
                        "#doc_phic": user.phicno,
                        "#doc_phic_expiry": user.phicexpiry,
                        "#doc_s2": user.S2no
                    };

                    $.each(map, function (selector, value) {
                        $(selector).text(value ?? "");
                    });
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
            layout: {
                bottomStart: 'paging',
                bottomEnd: {
                    div: {
                        html: `<h4 class="fw-bold">Total:<span class="fw-normal ms-2" id="charges_total"></span></h4>`
                    }
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
