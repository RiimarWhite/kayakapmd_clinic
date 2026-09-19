$(function () {
    if ($("#secretaryPage").length) {
        loadPatients();
        loadSchedules(1);
        loadSchedules(2);
    }

    function loadPatients() {
        loadPatientTable();
        loadUnschedTable();

        if ($("#doctor_id").val() == "") {
            $("#questions_container").empty();
            $("#questions_container").append(
                `<p class="m-0 ms-4">No questions loaded.</p>`
            );
        } else {
            $.ajax({
                url: "/api/fetch_doctor_questions",
                type: "POST",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: {
                    docrefno: $("#doctor_id").val()
                },
                success: function (response) {
                    var index = 1;

                    $("#questions_container").empty();
                    response.docquestions.forEach(element => {
                        $("#questions_container").append(
                            `<div class=" ms-4 mb-3">
                                <label class="form-label" for="questions${index}"><span class="fw-bold">${index}.</span> ${element.question}</label>
                                <textarea class="form-control" type="text" name="answer[${element.docquestionrefno}]"></textarea>
                            </div>`
                        );

                        index++;
                    });
                }
            });
        }
    }

    function loadPatientTable() {
        const table = $("#patients_queue_table");

        if ($.fn.DataTable.isDataTable(table)) {
            table.DataTable().clear().destroy();
        }

        table.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "fetch_patients_queue",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                },
                data: {
                    consuldate: $("#queuedate").val(),
                    consultime: $("#stime").val(),
                    docrefno: $("#doctor_id").val()
                }
            },
            columns: [
                {
                    data: "queueno"
                },
                {
                    data: null,
                    render: function (data) {
                        const importBtn = `
                            <button class="btn btn-sm btn-primary import-queue" value="${data.pxrefno}" title="Import patient data">
                                <i class="fa-solid fa-share"></i>
                            </button>
                        `;

                        const updateBtn = `
                            <button class="btn btn-sm btn-success update-queue" title="Update patient data">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                        `;

                        const reschedBtn = `
                            <button class="btn btn-sm btn-danger reschedule" title="Reschedule patient consultation">
                                <i class="fa-solid fa-calendar-days"></i>
                            </button>
                        `;

                        return `
                            <div class="input-group text-nowrap align-middle d-flex">
                                ${importBtn}${reschedBtn}
                            </div>
                        `;
                    }
                },
                {
                    data: "patientname"
                },
                {
                    data: "status",
                    render: function (data) {
                        let badge;

                        switch (data) {
                            case "WAITING":
                                badge = "bg-warning text-white fs-6"
                                break;

                            case "IN_CONSULTATION":
                                badge = "bg-info text-white fs-6";
                                break;

                            case "COMPLETED":
                                badge = "bg-success fs-6";
                                break;

                            case "UNSCHEDULED":
                                badge = "bg-primary fs-6";
                                break;

                            case "CANCELLED":
                            case "NO_SHOW":
                                badge = "bg-danger fs-6";
                                break;

                            default:
                                badge = "bg-secondary fs-6"
                                break;
                        }

                        return `<span class="badge ${badge}">${data}</span>`;
                    }
                }
            ],
            columnDefs: [
                {
                    target: 0,
                    width: "1%",
                    orderable: false,
                    searchable: false,
                    className: "text-nowrap text-center align-middle fw-bold"
                },
                {
                    target: 2,
                    className: "text-nowrap text-truncate align-middle overflow-hidden"
                },
                {
                    target: 3,
                    width: "1%",
                    className: "text-nowrap text-center align-middle"
                }
            ],
            language: {
                emptyTable: "No patients record yet."
            },
            pageLength: 10,
            lengthChange: false,
            paging: true,
            ordering: false,
        });
    }

    function loadUnschedTable() {
        const table = $("#patients_unsched_table");

        if ($.fn.DataTable.isDataTable(table)) {
            table.DataTable().clear().destroy();
        }

        table.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "fetch_unscheduled_patients",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                },
                data: {
                    consuldate: $("#queuedate").val(),
                    consultime: $("#stime").val(),
                    docrefno: $("#doctor_id").val()
                }
            },
            columns: [
                {
                    data: null,
                    render: function (data) {
                        return `
                            <button class="btn btn-sm btn-primary import-queue" value="${data.pxrefno}" title="Import patient details">
                                <i class="fa-solid fa-share"></i>
                            </button>
                        `;
                    }
                },
                {
                    data: "patientname"
                },
                {
                    data: "status",
                    render: function (data) {
                        let badge;

                        switch (data) {
                            case "WAITING":
                                badge = "bg-warning text-white fs-6"
                                break;

                            case "IN_CONSULTATION":
                                badge = "bg-info text-white fs-6";
                                break;

                            case "COMPLETED":
                                badge = "bg-success fs-6";
                                break;

                            case "UNSCHEDULED":
                                badge = "bg-primary fs-6";
                                break;

                            case "CANCELLED":
                            case "NO_SHOW":
                                badge = "bg-danger fs-6";
                                break;

                            default:
                                badge = "bg-secondary fs-6"
                                break;
                        }

                        return `<span class="badge ${badge}">${data}</span>`;
                    }
                }
            ],
            columnDefs: [
                {
                    targets: [0, 2],
                    width: "1%",
                    orderable: false,
                    searchable: false,
                    className: "text-nowrap text-center align-middle"
                },
                {
                    target: 1,
                    className: "text-nowrap align-middle"
                }
            ],
            language: {
                emptyTable: "No new/unscheduled patients records yet."
            },
            pageLength: 10,
            lengthChange: false,
            info: true,
            paging: true,
            searching: true,
            ordering: false,
        });
    }

    function loadSchedules(index) {
        switch (index) {
            case 1:
                $.ajax({
                    url: "fetch_doctor_schedules_specific",
                    type: "POST",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: {
                        docrefno: $("#doctor_id").val(),
                        consuldate: $("#queuedate").val()
                    },
                    success: function (response) {
                        $("#stime").empty();

                        if ($("#queuedate").val() == "" || !response.schedules || response.schedules.length === 0) {
                            $("#stime").append(
                                `<option selected disabled>No schedules set.</option>`
                            );
                            $("#stime").prop('disabled', true);
                            return;
                        }

                        $("#stime").prop('disabled', false);
                        response.schedules.forEach(element => {
                            $("#stime").append(
                                `<option value="${element.start}">${formatTime(element.start)} - ${formatTime(element.end)}</option>`
                            );
                        });
                        $("#stime").trigger("change");
                    }
                });
                break;

            case 2:
                $.ajax({
                    url: "fetch_doctor_schedules_specific",
                    type: "POST",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: {
                        docrefno: $("#doctor_for_consult").val(),
                        consuldate: $("#sched_date").val()
                    },
                    success: function (response) {
                        $("#sched_time").empty();

                        if (!response.schedules || response.schedules.length === 0) {
                            $("#sched_time").append(
                                `<option selected disabled>No schedules set.</option>`
                            );
                            $("#sched_time").prop('disabled', true);
                            return;
                        }

                        $("#sched_time").prop('disabled', false);
                        response.schedules.forEach(element => {
                            $("#sched_time").append(
                                `<option value="${element.start}">${formatTime(element.start)} - ${formatTime(element.end)}</option>`
                            );
                        });
                    }
                });
                break;

            case 3:
                $.ajax({
                    url: "fetch_doctor_schedules_specific",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: {
                        docrefno: $("#doctor_id").val(),
                        consuldate: $("#resched_date").val(),
                    },
                    success: function (response) {
                        $("#resched_time").empty();

                        if (!response.schedules || response.schedules.length === 0) {
                            $("#resched_time").append(
                                `<option selected disabled>No schedules set for this date.</option>`
                            );

                            $("#resched_time").prop("disabled", true);
                            return;
                        }

                        $("#resched_time").prop('disabled', false);
                        response.schedules.forEach(element => {
                            $("#resched_time").append(
                                `<option value="${element.start}">${formatTime(element.start)} - ${formatTime(element.end)}</option>`
                            );
                        });
                    }
                })
        }
    }

    function formatTime(time) {
        let [hours, minutes] = time.split(":").map(Number);
        const ampm = hours >= 12 ? "PM" : "AM";
        hours = hours % 12 || 12;

        return `${hours}:${minutes.toString().padStart(2, "0")} ${ampm}`
    }

    $("#doctor_id").on("change", function () {
        loadPatients();
        loadSchedules(1);
        // loadSchedules(2);
    });

    $("#doctor_for_consult").on("change", function () {
        loadSchedules(2);
    });

    $("#sprevious").on("click", function () {
        let dateInp = $("#queuedate").val();

        if (!dateInp) return;

        let date = new Date(dateInp);
        date.setDate(date.getDate() - 1);

        $("#queuedate").val(date.toISOString().split("T")[0]);
        loadPatientTable();
        loadSchedules(1);
    });

    $("#snext").on("click", function () {
        let dateInp = $("#queuedate").val();

        if (!dateInp) return;

        let date = new Date(dateInp);
        date.setDate(date.getDate() + 1);

        $("#queuedate").val(date.toISOString().split("T")[0]);
        loadPatientTable();
        loadSchedules(1);
    });

    $("#queuedate").on("change", function () {
        loadSchedules(1);
    });

    $("#stime").on("change", function () {
        loadPatientTable();
    });

    $("#sched_date").on("change", function () {
        loadSchedules(2);
    });

    // Reschedules
    $("#resched_date").on("change", function () {
        loadSchedules(3);
    });

    $("#reschedule_btn").on("click", function () {
        const reschedModal = bootstrap.Modal.getInstance("#reschedule_modal");

        $.ajax({
            url: "reschedule_patient",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: {
                consultationrefno: $(this).val(),
                docrefno: $("#doctor_id").val(),
                date: $("#resched_date").val(),
                time: $("#resched_time").val()
            },
            success: function (response) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Patient rescheduled!',
                    showConfirmButton: false,
                    timer: 1500
                });

                reschedModal.hide();
                refreshQueue();
                loadPatients();
            }
        });
    });

    // Topbar buttons
    $("#add_new_patient_btn").on("click", function () {
        const addPatientModal = new bootstrap.Modal("#add_patient_modal");
        addPatientModal.show();
    });

    $("#pxsched").on("change", function () {
        if (!$("#doctor_id").val())
            return;

        $.ajax({
            url: "fetch_doctor_schedules_specific",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
            },
            data: {
                docrefno: $("#doctor_id").val(),
                consuldate: $("#pxsched").val()
            },
            success: function (response) {
                $("#pxtime").empty();

                response.schedules.forEach(e => {
                    $("#pxtime").append(
                        `<option value="${e.schedrefno}">${e.start} - ${e.end}</button>`
                    );
                });
            }
        });
    });

    function fetchScheduleTime() {
        if (!$("#doctor_id").val())
            return;

        $.ajax({
            url: "fetch_doctor_schedules_specific",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
            },
            data: {
                docrefno: $("#doctor_id").val(),
                consuldate: $("#pxsched").val()
            },
            success: function (response) {
                $("#pxtime").empty();

                if (response.schedules.length > 0) {
                    response.schedules.forEach(e => {
                        $("#pxtime").append(
                            `<option value="${e.schedrefno}">${e.start} - ${e.end}</button>`
                        );
                    });
                } else {
                    $("#pxtime").append(
                        `<option value="" selected disabled>No schedules.</option>`
                    );
                }
            }
        });
    }

    $("#view_masterlist_btn").on("click", function () {
        // Require doctor for masterlist reference
        const doctor = $("#doctor_id").val();
        if (!doctor) {
            return Swal.fire({
                title: "Doctor Required",
                text: "Please choose a doctor from the select tab first.",
                icon: "warning"
            });
        }

        // Show modal
        const masterlistModal = new bootstrap.Modal("#patientMasterlistModal");
        masterlistModal.show();

        // Load data
        $("#patientMasterlistTable").DataTable().destroy().clear();
        $("#patientMasterlistTable").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "fetch_patient_masterlist_sec",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                },
                data: {
                    docrefno: $("#doctor_id").val()
                },
            },
            columns: [
                {
                    data: null,
                    render: function (data, type, row) {
                        return `
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-primary import-btn" value="${row.casecode}"><i class="fa-solid fa-share"></i> Import</button>
                            <button class="btn btn-sm btn-secondary view-btn" value="${row.pincode}"><i class="fa-solid fa-clock-rotate-left"></i> History</button>
                        </div>
                        `;
                    }
                },
                {
                    data: null,
                    render: function (data, type, row) {
                        return `<img src="${row.photo_path}" alt="patient_photo" style="max-height: 100px;">`;
                    }
                },
                {
                    data: null,
                    render: function (data, type, row) {
                        let fullName = row.pxlastname || '';
                        if (row.patientname)
                            fullName += `, ${row.pxfirstname}`;

                        if (row.pxmidname)
                            fullName += ` ${row.pxmidname}`;

                        if (row.pxsuffix)
                            fullName += ` ${row.pxsuffix}`;

                        return fullName.trim().toUpperCase();
                    }
                },
                { data: 'mobilenumber' },
                { data: 'emailaddress' }
            ],
            columnDefs: [
                {
                    targets: [0, 1],
                    width: "1%",
                    orderable: false,
                    searchable: false,
                    className: "text-nowrap text-center align-middle"
                }
            ],
            language: {
                emptyTable: "No patient records yet."
            },
        });
    });

    $(document).on("click", ".import-btn", function () {
        const refNo = $(this).val();

        $.ajax({
            url: "fetch_consultation",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { casecode: refNo },
            success: function (response) {
                if (response.success) {
                    // Fill the form fields
                    alert(response.patient.consultationrefno);

                    $("#pincode").val(response.patient.pincode);
                    $("#pxconsultationrefno").text(response.patient.consultationrefno);
                    $("#pxidno").val(response.patient.pxrefno);
                    $("#pxfname").val(response.patient.patientname);
                    $("#pxmname").val(response.patient.pxmidname);
                    $("#pxlname").val(response.patient.pxlastname);
                    $("#pxsuffix").val(response.patient.pxsuffix);
                    $("#pxsex").val(response.patient.gender);
                    $("#pxbday").val(response.patient.birthday);
                    $("#pxage").val(getAge(response.patient.birthday));
                    $("#pxcellnumber").val(response.patient.mobilenumber);
                    $("#pxemail").val(response.patient.emailaddress);
                    $("#pxaddress").val(response.patient.address);

                    $("#pxreasonforconsultation").val(response.patient.reasonforconsultation);
                    $("#pxweight").val(response.patient.weight);
                    $("#pxheight").val(response.patient.height);
                    $("#pxtemp").val(response.patient.temp);
                    $("#pxrespiratory").val(response.patient.respiratoryrate);
                    $("#pxpulse").val(response.patient.pulserate);
                    $("#pxbpnumerator").val(response.patient.bpnumerator);
                    $("#pxbpdenominator").val(response.patient.bpdenominator);

                    $("#doctor_for_consult").val(response.patient.docrefno);
                    $("#sched_date").val((response.patient.consultation_date).split(" ")[0]);
                    loadSchedules(2);

                    $("#patient_picture_preview").prop("src", response.patient.photo_path);

                    response.answers.forEach(element => {
                        const selector = `textarea[name="answer\[${element.questionrefno}\]"]`;
                        $(selector).val(element.answer);
                    });

                    // Close the modal after importing
                    bootstrap.Modal.getInstance(document.getElementById('patientMasterlistModal')).hide();

                    // Optional: Show a small toast notification
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Patient data imported',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            },
            error: function () {
                Swal.fire("Error", "Could not fetch patient details.", "error");
            }
        });
    });

    $(document).on('click', '.view-btn', function () {
        const masterlistModalEl = document.getElementById("patientMasterlistModal");
        const masterlistModal = bootstrap.Modal.getInstance(masterlistModalEl);

        const medHistoryModalEl = document.getElementById("patientMedhistoryModal");
        const medHistoryModal = new bootstrap.Modal(medHistoryModalEl);

        $("#mpincode").val($(this).val());

        masterlistModal.hide();
        medHistoryModal.show();

        // const pincode = $(this).val();
        // const masterlistEl = document.getElementById('patientMedhistoryModal');
        // const masterlistModal = bootstrap.Modal.getOrCreateInstance(masterlistEl);

        // $.ajax({
        //     url: "fetch_latest_patient_details",
        //     type: "POST",
        //     headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        //     data: { pincode: pincode },
        //     success: function(response) {
        //         if (!response.data) return Swal.fire("Error", "Patient data not found", "error");

        //         const data = response.data;

        //         // 1. Map PIN
        //         $('#view_memPin').val(data.memPin || '');

        //         // 2. Map Member Data
        //         $('#view_pMemFname').val(data.memFname || '');
        //         $('#view_pMemMname').val(data.memMname || '');
        //         $('#view_pMemLname').val(data.memLname || '');
        //         $('#view_pMemExtname').val(data.memExtname || '');
        //         $('#view_pMemDob').val(data.memDob || '');

        //         // 3. Map Patient Data
        //         $('#view_pPatientFname').val(data.patientname || '');
        //         $('#view_pPatientMname').val(data.pxmidname || '');
        //         $('#view_pPatientLname').val(data.pxlname || '');
        //         $('#view_pPatientExtname').val(data.pxsuffix || '');
        //         $('#view_pPatientSex').val(data.gender || '');
        //         $('#view_pPatientDob').val(data.birthday || '');
        //         $('#view_pPatientMobileNo').val(data.mobilenumber || '');
        //         $('#view_email').val(data.emailaddress || '');
        //         $('#view_address').val(data.address || '');

        //         // 4. Modal Transition
        //         //masterlistModal.hide();
        //         const viewModal = bootstrap.Modal.getOrCreateInstance(document.getElementById('viewPatientModal'));
        //         viewModal.show();
        //     },
        //     error: function() {
        //         Swal.fire("Error", "Could not fetch patient details", "error");
        //     }
        // });
    });

    $("#patientMedhistoryModal").on("show.bs.modal", function () {
        $("#medhistorytable").DataTable().clear().destroy();
        $("#medhistorytable").DataTable({
            ajax: {
                url: "fetch_patient_history",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                },
                data: {
                    pincode: $("#mpincode").val()
                },
                dataSrc: 'history'
            },
            columns: [
                {
                    data: 'photo_path',
                    render: function (data) {
                        return `<img src="${data}" style="height: 100px;" alt="patient_photo">`;
                    }
                },
                {
                    data: 'consultation_date',
                    render: function (data) {
                        return new Date(data).toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });
                    }
                },
                { data: 'reasonforconsultation' },
                { data: 'status' },
                { data: 'recordedby' },
                {
                    data: 'recordeddate',
                    render: function (data) {
                        return new Date(data).toLocaleDateString('en-US', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        });
                    }
                }
            ],
            columnDefs: [
                {
                    targets: [0, 3],
                    width: '1%',
                    orderable: false,
                    className: "text-nowrap text-truncate text-center"
                },
                {
                    targets: [1, 2, 3, 4, 5],
                    orderable: true,
                    className: "text-nowrap text-truncate align-middle"
                }
            ],
            language: {
                emptyTable: "No consultation history."
            },
            order: [[1, 'asc']]
        });
    });

    $("#patientMedhistoryModal").on("hide.bs.modal", function () {
        const masterlistModal = bootstrap.Modal.getInstance("#patientMasterlistModal");
        masterlistModal.show();
    });

    // 1. Handle "Same with Patient" button click
    $("#syncMemberInfo").on("click", function () {
        $('#edit_pMemFname').val($('#edit_pPatientFname').val());
        $('#edit_pMemMname').val($('#edit_pPatientMname').val());
        $('#edit_pMemLname').val($('#edit_pPatientLname').val());
        $('#edit_pMemExtname').val($('#edit_pPatientExtname').val());
        $('#edit_pMemDob').val($('#edit_pPatientDob').val());
    });

    // 2. Open Edit Modal and Fetch Data (similar to your view logic)
    $(document).on('click', '.edit-btn', function () {
        const pincode = $(this).val();
        const masterlistEl = document.getElementById('patientMasterlistModal');
        const masterlistModal = bootstrap.Modal.getOrCreateInstance(masterlistEl);

        $.ajax({
            url: "fetch_latest_patient_details", // Using your existing fetch route
            type: "POST",
            data: { pincode: pincode },
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                const data = response.data;

                // Map data to Edit Modal inputs
                $('#edit_pincode').val(data.pincode);
                $('#edit_doccode').val(data.doccode);

                $('#edit_memPin').val(data.memPin);
                $('#edit_pMemFname').val(data.memFname);
                $('#edit_pMemMname').val(data.memMname);
                $('#edit_pMemLname').val(data.memLname);
                $('#edit_pMemExtname').val(data.memExtname);
                $('#edit_pMemDob').val(data.memDob);

                $('#edit_pPatientFname').val(data.patientname);
                $('#edit_pPatientMname').val(data.pxmidname);
                $('#edit_pPatientLname').val(data.pxlastname);
                $('#edit_pPatientExtname').val(data.pxsuffix);
                $('#edit_pPatientSex').val(data.gender);
                $('#edit_pPatientDob').val(data.birthday);
                $('#edit_pPatientMobileNo').val(data.mobilenumber);
                $('#edit_email').val(data.emailaddress);
                $('#edit_address').val(data.address);

                masterlistModal.hide();
                bootstrap.Modal.getOrCreateInstance(document.getElementById('editPatientModal')).show();
            }
        });
    });

    // 3. Handle Update Form Submission
    $('#editPatientForm').on('submit', function (e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.ajax({
            url: "update_patient_record",
            type: "POST",
            data: formData,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Record created successfully',
                        timer: 1500,
                        showConfirmButton: false
                    });

                    // 1. Get the specific instance that was shown and hide it
                    const editModalEl = document.getElementById('editPatientModal');
                    const editModal = bootstrap.Modal.getInstance(editModalEl); // Get existing instance
                    if (editModal) {
                        editModal.hide();
                    }

                    // 2. Also hide the Masterlist if it's still lingering
                    const masterModalEl = document.getElementById('patientMasterlistModal');
                    const masterModal = bootstrap.Modal.getInstance(masterModalEl);
                    if (masterModal) {
                        masterModal.hide();
                    }

                    // 3. Forced cleanup (The "Nuclear" option for stuck backdrops)
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open').css('padding-right', '');

                    // 4. Run your custom load function
                    loadPatients();
                }
            },
            error: function () {
                Swal.fire("Error", "Failed to process request", "error");
            }
        });
    });

    // Add patient modal toggles
    $("#ismember").on("click", function () {
        $("#ifmember").removeClass("d-flex").addClass("d-none");

        // Principal Member fields are NOT required if the patient IS the member
        $("#pMemFname, #pMemLname, #pMemDob").prop("required", false);

        // Ensure the global PIN is still required
        $("#memPin").prop("required", true);
    });

    $("#isdependent").on("click", function () {
        $("#ifmember").removeClass("d-none").addClass("d-flex");

        // Principal Member fields ARE required if the patient is a dependent
        $("#pMemFname, #pMemLname, #pMemDob").prop("required", true);

        // Ensure the global PIN is still required
        $("#memPin").prop("required", true);
    });

    $("#add_patient_btn").on("click", function () {
        var form = document.getElementById("add_patient_form");

        if (!form.checkValidity()) {
            return form.reportValidity();
        }

        let formData = $("#add_patient_form").serialize();

        $.ajax({
            url: "add_patient",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: formData,
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title: "Success",
                        text: "Patient record saved.",
                        icon: "success"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $("#add_patient_form")[0].reset()
                            // Close the modal
                            bootstrap.Modal.getInstance(document.getElementById('add_patient_modal')).hide();

                            loadPatients();
                        }
                    });
                }
            }
        });
    });

    $("#add_patient_prev").on("click", function () {
        let dateInp = $("#pxsched").val();

        if (!dateInp) return;

        let date = new Date(dateInp);
        date.setDate(date.getDate() - 1);

        $("#pxsched").val(date.toISOString().split("T")[0]);
        fetchScheduleTime();
    });

    $("#add_patient_next").on("click", function () {
        let dateInp = $("#pxsched").val();

        if (!dateInp) return;

        let date = new Date(dateInp);
        date.setDate(date.getDate() + 1);

        $("#pxsched").val(date.toISOString().split("T")[0]);
        fetchScheduleTime();
    });

    // Select patient and importing
    $(document).on("click", ".import-queue", function () {
        $.ajax({
            url: "fetch_consultation",
            type: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                pxrefno: $(this).val()
            },
            success: function (response) {
                if (response.success) {
                    // alert(response.patient.consultationrefno);
                    $("#pincode").val(response.patient.pincode);
                    $("#pxconsultationrefno").text(response.patient.consultationrefno),
                    $("#pxidno").val(response.patient.pxrefno),
                    $("#pxfname").val(response.patient.patientname);
                    $("#pxmname").val(response.patient.pxmidname);
                    $("#pxlname").val(response.patient.pxlastname);
                    $("#pxsuffix").val(response.patient.pxsuffix);
                    $("#pxsex").val(response.patient.gender);
                    $("#pxbday").val(response.patient.birthday);
                    $("#pxage").val(getAge(response.patient.birthday));
                    $("#pxcellnumber").val(response.patient.mobilenumber);
                    $("#pxlandlinenumber");
                    $("#pxemail").val(response.patient.emailaddress);
                    $("#pxaddress").val(response.patient.address);

                    $("#pxreasonforconsultation").val(response.patient.reasonforconsultation);
                    $("#pxweight").val(response.patient.weight);
                    $("#pxheight").val(response.patient.height);
                    $("#pxtemp").val(response.patient.temp);
                    $("#pxrespiratory").val(response.patient.respiratoryrate);
                    $("#pxpulse").val(response.patient.pulserate);
                    $("#pxbpnumerator").val(response.patient.bpnumerator);
                    $("#pxbpdenominator").val(response.patient.bpdenominator);

                    $("#doctor_for_consult").val(response.patient.docrefno);

                    if (response.patient.consultation_date != "1901-01-01 00:00:00") {
                        $("#sched_date").val((response.patient.consultation_date).split(" ")[0]);
                    } else {
                        $("#sched_date").val("");
                    }

                    loadSchedules(2);

                    $("#patient_picture_preview").prop("src", response.patient.photo_path ?? '');

                    response.answers.forEach(element => {
                        const selector = `textarea[name="answer\[${element.questionrefno}\]"]`;
                        $(selector).val(element.answer);
                    });
                }

                if ($("#payment_info").hasClass("show")) {
                    loadPatientCharges();
                }

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Patient data imported',
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        });
    });

    //update queue status
    $(document).on("click", ".update-queue", function () {
        const caseCode = $(this).val();
        const $row = $(this).closest('tr');
        const data = $("#patients_queue_table").DataTable().row($row).data();
        const currentStatus = data.status;
        console.log(currentStatus);


        // Determine the logical "Next Step"
        let nextStep = '';
        if (currentStatus === 'SCHEDULED') nextStep = 'WAITING';
        else if (currentStatus === 'WAITING') nextStep = 'IN_CONSULTATION';
        else if (currentStatus === 'IN_CONSULTATION') nextStep = 'COMPLETED';

        // Define all available options
        const allOptions = {
            'WAITING': 'Waiting',
            'IN_CONSULTATION': 'In Consultation',
            'COMPLETED': 'Completed',
            'CANCELLED': 'Cancelled',
            'NO_SHOW': 'No Show',
            'ON_HOLD': 'On Hold'
        };

        Swal.fire({
            title: "Update Patient Status",
            text: `Current Status: ${currentStatus}`,
            input: 'select',
            didOpen: () => {
                const select = Swal.getInput();
                select.classList.add('form-select');
                select.classList.add('d-flex');
                select.classList.add('w-auto');
            },
            inputOptions: allOptions,
            inputValue: nextStep || currentStatus,
            showCancelButton: true,
            confirmButtonText: 'Update Status',
            confirmButtonColor: '#28a745',
            inputValidator: (value) => {
                if (!value) return 'You need to select a status!';
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "update_queue_status",
                    type: "POST",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: {
                        casecode: caseCode,
                        status: result.value
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
                            });
                            loadPatients(); // Refresh the table
                        }
                    },
                    error: function () {
                        Swal.fire("Error", "Failed to update status.", "error");
                    }
                });
            }
        });
    });

    $(document).on("click", ".reschedule", function () {
        const schedModal = new bootstrap.Modal("#reschedule_modal");

        $("#reschedule_btn").val($(this).val());
        $("#resched_date").val(new Date().toISOString().split('T')[0]);
        loadSchedules(3);

        schedModal.show();
    });

    function getAge(dateString) {
        const today = new Date();
        const birthDate = new Date(dateString);

        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();

        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        return age;
    }

    // Detailed Comment: Save Consultation with null-safe string extraction, correct API endpoint, and doctor validation
    $(document).on("click", ".save_consultation_btn", function () {
        const pxfname = String($("#pxfname").val() || "").trim();
        if (pxfname === "") {
            return Swal.fire({ title: "Validation Error", text: "Patient first name is required.", icon: "warning" });
        }
        if ($("#sched_time").is(":disabled")) {
            return Swal.fire({
                title: "Error",
                text: "No valid schedule assigned.",
                icon: "error"
            });
        }

        const docrefno = String($("#doctor_for_consult").val() || "").trim();
        if (docrefno === "") {
            return Swal.fire({
                title: "Error",
                text: "Please assign a doctor first.",
                icon: "error"
            });
        }

        const $fieldset = $("#patient_info_fieldset");
        $fieldset.prop("disabled", false);

        let formData = new FormData($("#consultation_form")[0]);
        formData.append("type", $(this).val());
        formData.append("docrefno", docrefno);

        const image = document.getElementById("patient_image");
        if (image && image.files.length > 0)
            formData.append("patient_photo", image.files[0]);

        $fieldset.prop("disabled", true);

        $.ajax({
            url: "/api/save_patient_consultation",
            type: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title: "Success",
                        text: "Record successfully saved.",
                        icon: "success",
                        confirmButtonText: "Okay"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $("#consultation_form")[0].reset();
                            refreshQueue();
                            loadPatients();
                            loadSchedules(2);
                            $("#pxidno").val('');
                        }
                    });
                }
            },
            error: function () {
                Swal.fire("Error", "Could not save the record.", "error");
            }
        });
    });

    // Detailed Comment: Update Consultation Details with null-safety and record existence validation
    $(document).on("click", ".update_consultation_btn", function () {
        const pxfname = String($("#pxfname").val() || "").trim();
        if (pxfname === "") {
            return Swal.fire({ title: "Validation Error", text: "Patient first name is required.", icon: "warning" });
        }
        if ($("#sched_time").is(":disabled")) {
            return Swal.fire({
                title: "Error",
                text: "No valid schedule assigned.",
                icon: "error"
            });
        }

        const docrefno = String($("#doctor_for_consult").val() || "").trim();
        if (docrefno === "") {
            return Swal.fire({
                title: "Error",
                text: "Please assign a doctor first.",
                icon: "error"
            });
        }

        const refno = String($("#pxconsultationrefno").text() || "").trim();
        if (!refno) {
            return Swal.fire({
                title: "Error",
                text: "No existing consultation record selected for update.",
                icon: "warning"
            });
        }

        const fieldset = $("#patient_info_fieldset");
        fieldset.prop("disabled", false);

        let formData = new FormData($("#consultation_form")[0]);
        formData.append("pxconsultationrefno", refno);
        formData.append("docrefno", docrefno);

        const image = document.getElementById("patient_image");
        if (image && image.files.length > 0)
            formData.append("patient_photo", image.files[0]);

        fieldset.prop("disabled", true);

        $.ajax({
            url: "/api/update_patient_consultation",
            type: "POST",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title: "Success",
                        text: "Record successfully updated.",
                        icon: "success",
                        confirmButtonText: "Okay"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            refreshQueue();
                            $("#consultation_form")[0].reset();

                            loadPatients();
                            loadSchedules(2);
                            $("#pxidno").val('');
                        }
                    });
                }
            },
            error: function () {
                Swal.fire("Error", "Could not update the record.", "error");
            }
        });
    });

    $(document).on('input', '#pMemExtname, #pPatientExtname', function () {
        let value = $(this).val();

        if (value.length > 10) {
            // Trim to 10 characters and update the input
            $(this).val(value.substring(0, 10));

            // Optional: Show a small toast or visual cue
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });

            Toast.fire({
                icon: 'warning',
                title: 'Suffix limit is 10 characters'
            });
        }
    });

    function refreshQueue() {
        $.ajax({
            url: "refresh_queue",
            type: "POST",
            data: {
                date: $("#queuedate").val(),
                time: $("#stime").val()
            },
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
        });
    }

    $("#clear_form").on("click", function () {
        $("#consultation_form")[0].reset();
        $("#pxconsultationrefno").text("");
        $("#patient_picture_preview").prop("src", "");
    });

    // Patient charges
    $("#patient_charges_btn").on("click", function () {
        loadPatientCharges();
    });

    function loadPatientCharges() {
        $("#pxcharges_table").DataTable().destroy().clear();
        $("#pxcharges_table").DataTable({
            ajax: {
                url: "fetch_pxcharges",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                },
                data: {
                    consultationrefno: $("#pxconsultationrefno").text()
                },
                dataSrc: function (response) {
                    let total = 0;

                    response.charges.forEach(charge => {
                        total += parseFloat(charge.net_total)
                    });

                    $("#charges_total").text(total.toFixed(2));

                    return response.charges;
                }
            },
            columns: [
                {
                    data: null,
                    render: function (data) {
                        return `
                            <button class="btn btn-sm btn-danger remove_charge" type="button" value="${data.pxchargerefno}"><i class="fa-solid fa-trash"></i></button>
                            <button class="btn btn-sm btn-primary edit_charge_btn_sc" type="button" value="${data.pxchargerefno}"><i class="fa-solid fa-pen-to-square"></i></button>
                        `;
                    }
                },
                { data: 'servicename' },
                { data: 'discount' },
                { data: 'net_total' }
            ],
            columnDefs: [
                {
                    target: 0,
                    width: '1%',
                    orderable: false,
                    className: 'text-nowrap text-center align-middle'
                },
                {
                    targets: [1, 2, 3],
                    className: 'text-nowrap text-truncate align-middle'
                }
            ],
            language: {
                emptyTable: 'No patient charges yet.'
            },
            // Detailed Comment: In DataTables 2, layout uses standard feature keys to avoid "Unknown feature: div" warning.
            // Total amount is rendered in the blade view below the table and updated dynamically via dataSrc.
            layout: {
                bottomStart: 'paging',
                bottomEnd: null
            },
            searching: false,
            lengthChange: false,
            pageLength: 5,
            paging: true,
            order: [[1, 'asc']]
        });
    }

    // Charges-related
    let appendedPxcharges = [];
    $("#append_pxcharges_btn").on("click", function () {
        appendedPxcharges = [];

        const chargeModal = new bootstrap.Modal("#append_charge_modal");

        $.ajax({
            url: "fetch_charge_categories_sc",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
            },
            data: {
                consultationrefno: $("#pxconsultationrefno").val()
            },
            success: function (response) {
                chargeModal.show();

                $("#search_filter").empty();
                $("#search_filter").append(`<option value="">No filter</option>`).prop("selected", true);
                response.categories.forEach(category => {
                    $("#search_filter").append(`<option value="${category.categoryrefno}">${category.categoryname}</option>`);
                });

                $("#appended_charges_table tbody").empty();
                response.pxcharges.forEach(charge => {
                    appendedPxcharges.push(charge.servicerefno);

                    const newRow = `
                        <tr data-refno="${charge.servicerefno}">
                            <td class="align-middle text-center text-nowrap">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-secondary charge_entry"
                                    data-refno="${charge.servicerefno}"
                                    title="Appended charges can only be removed from the patient charges tab."
                                    disabled
                                >
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
    $("#search_pxcharge").autocomplete({
        minLength: 0,
        source: function (request, response) {
            if (cache) {
                response(
                    cache.filter(i => i.label.toLowerCase().includes(request.term.toLowerCase()))
                );

                return;
            }

            if (loading) return;

            loading = true;

            $.getJSON("fetch_all_charges", {
                category: $("#search_filter").val() || "",
                consultationrefno: $("#pxconsultationrefno").val()
            }, function (data) {
                cache = data.charges.map(item => ({
                    label: item.charge_name,
                    value: item.chargerefno,
                    amount: item.charge_amount
                }));

                loading = false;

                response(
                    cache.filter(i => i.label.toLowerCase().includes(request.term.toLowerCase()))
                );
            });
        },
        select: function (event, ui) {
            $(this).val(ui.item.label);
            $("#charge_code").val(ui.item.value);
            $("#charge_amount").val(ui.item.amount);

            return false;
        },
    });

    $("#search_filter").on("change", function () {
        cache = null;

        $("#search_charge").val("");
        $("#charge_code").val("");
    });

    $("#charge_category").on("change", function () {
        cache = null;
    });

    $("#append_to_pxcharges_btn").on("click", function () {
        let form = document.getElementById("appended_charges_form");

        if (!form.checkValidity())
            return form.reportValidity();

        const chargeRefno = $("#charge_code").val();
        const chargeName = $("#search_pxcharge").val();
        const chargeAmt = $("#charge_amount").val();
        const chargeDiscount = $("#charge_discount").val();

        if (!chargeRefno || !chargeName) {
            return Swal.fire({
                title: "Error",
                text: "Please select a charge to append.",
                icon: "error"
            });
        }

        if (appendedPxcharges.some(c => c.refno === chargeRefno)) {
            return Swal.fire({
                title: "Error",
                text: "This charge has already been appended.",
                icon: "error"
            });
        }

        appendedPxcharges.push({
            refno: chargeRefno,
            discount: chargeDiscount,
            amount: chargeAmt
        });

        const newRow = `
            <tr data-refno="${chargeRefno}">
                <td class="align-middle text-center text-nowrap">
                    <button
                        type="button"
                        class="btn btn-sm btn-danger charge_entry"
                        data-refno="${chargeRefno}"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </td>
                <td>${chargeName}</td>
                <td>${chargeDiscount ?? 0}</td>
                <td>${chargeAmt}</td>
            </tr>
        `;

        $("#appended_charges_table tbody").append(newRow);
        $("#search_pxcharge").val("");
        $("charge_amount").val(0);
        $("charge_discount").val(0);
        $("#charge_code").val("");
    });

    // Delete charge/row
    $(document).on("click", ".charge_entry", function () {
        const chargeRefno = $(this).data("refno");
        appendedPxcharges = appendedPxcharges.filter(ref => ref !== chargeRefno);

        $(this).closest("tr").remove();
    });

    $("#pxsave_charges_btn").on("click", function () {
        if (appendedPxcharges.length === 0) {
            return Swal.fire({
                title: "No charges!",
                text: "Please append at least one charges before saving.",
                icon: "error"
            });
        }

        $.ajax({
            url: "save_patient_charges",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
            },
            contentType: "application/json",
            data: JSON.stringify({
                consultationrefno: $("#pxconsultationrefno").val(),
                chargerefnos: appendedPxcharges
            }),
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title: "Success",
                        text: "Charges successfully saved.",
                        icon: "success"
                    });

                    appendedPxcharges = [];
                    $("#appended_charges_list").empty();
                }
            }
        });

        $("#patient_charge_tab_btn").trigger("click");
    });

    $(document).on("click", ".remove_charge", function () {
        Swal.fire({
            title: "Confirmation",
            text: "Remove this charge?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Confirm"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "delete_patient_charge",
                    type: "POST",
                    headers: {
                        "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                    },
                    data: {
                        chargerefno: $(this).val(),
                        consultationrefno: $("#pxconsultationrefno").val()
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                title: "Success",
                                text: "Charge have been removed successfully.",
                                icon: "success"
                            });

                            $("#pxcharges_table").DataTable().ajax.reload();
                        }
                    }
                });
            }
        });
    });

    $(document).on("click", ".edit_charge_btn_sc", function () {
        Swal.fire({
            title: "Edit Charge",
            html: `
                <div class="d-flex gap-2">
                    <div class="">
                        <label class="form-label" for="charge_input_sw">Charge Amount</label>
                        <input class="form-control" type="number" name="charge_input_sw" id="charge_input_sw">
                    </div>

                    <div class="">
                        <label class="form-label" for="discount_input_sw">Charge Amount</label>
                        <input class="form-control" type="number" name="discount_input_sw" id="discount_input_sw">
                    </div>
                </div>
            `,
            confirmButtonText: "Update",
            showCancelButton: true,
            preConfirm: () => {
                const charge = $("#charge_input_sw").val();

                if (!charge) {
                    return Swal.showValidationMessage("Charge input field is empty.");
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "update_charge",
                    type: "POST",
                    headers: {
                        "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                    },
                    data: {
                        consultationrefno: $("#pxconsultationrefno").val(),
                        pxchargerefno: $(this).val(),
                        charge_fee: $("#charge_input_sw").val(),
                        discount: $("#discount_input_sw").val()
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: "success",
                                title: "Charge updated!",
                                showConfirmButton: false,
                                timer: 2000
                            });

                            $("#pxcharges_table").DataTable().ajax.reload();
                        }
                    }
                });
            }
        });
    });

    // Patient image
    $("#upload_patient_image").on("click", function () {
        $("#patient_image").trigger("click");
    });

    $("#patient_image").on("change", function (e) {
        const file = e.target.files[0];

        if (!file) return;

        if (!file.type.startsWith("image/")) {
            Swal.fire({
                title: "Error",
                text: "Invalid file image",
                icon: "error"
            });
            $(this).val("");
            return;
        }

        const reader = new FileReader();
        reader.onload = function (event) {
            $("#patient_picture_preview").attr("src", event.target.result);
        };
        reader.readAsDataURL(file);
    });

    $("#take_photo").on("click", function () {
        const photoModal = new bootstrap.Modal("#takePhotoModal");
        photoModal.show();
    });

    let stream = null;
    $("#takePhotoModal").on("shown.bs.modal", async function () {
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: "user" },
                audio: false
            });

            document.getElementById("camera_preview").srcObject = stream;
        } catch (err) {
            Swal.fire({
                title: "No devices found",
                text: "There were no camera devices detected.",
                icon: "error"
            }).then((result) => {
                if (result.isConfirmed) {
                    const photoModal = bootstrap.Modal.getInstance("#takePhotoModal");
                    photoModal.hide();
                }
            });
        }
    });

    $("#takePhotoModal").on("hidden.bs.modal", function () {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
    });

    $("#save_photo").on("click", function () {
        const video = document.getElementById("camera_preview");
        const canvas = document.getElementById("camera_canvas");

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;

        const context = canvas.getContext("2d");
        context.drawImage(video, 0, 0);

        canvas.toBlob(function (blob) {
            const file = new File([blob], "patient_photo.png", {
                type: "image/png"
            });

            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);

            const fileInput = document.getElementById("patient_image");
            fileInput.files = dataTransfer.files;

            const imageData = canvas.toDataURL("image/png");

            $("#patient_picture_preview").attr("src", imageData);
        }, "image/png");
    });

    // Patient charges
    let totalAmount = 0.00;
    $("#settlement_btn").on("click", function () {
        totalAmount = parseFloat($("#charges_total").text()) || 0;

        $("#total_amount").text(totalAmount.toFixed(2));
        $("#remaining").text(totalAmount.toFixed(2));
        $("#sett_consultationrefno").val($("#pxconsultationrefno").val());

        $("#settlement_form")[0].reset();
    });

    $(document).on("click", ".import-total", function () {
        let input = $(this).closest(".input-group").find(".settlement-input");

        let remaining = calculateRemaining();

        input.val(remaining.toFixed(2)).trigger("input");
    });

    $(document).on("input", ".settlement-input", function () {
        let currentInput = $(this);
        let value = parseFloat(currentInput.val()) || 0;

        if (value < 0) value = 0;

        // Calculate remaining EXCLUDING this field first
        let usedExceptCurrent = 0;

        $(".settlement-input").not(currentInput).each(function () {
            usedExceptCurrent += parseFloat($(this).val()) || 0;
        });

        let maxAllowed = totalAmount - usedExceptCurrent;

        if (value > maxAllowed) {
            value = maxAllowed;
        }

        value = Math.round(value * 100) / 100;

        currentInput.val(value.toFixed(2));

        updateRemaining();
    });

    function calculateRemaining() {
        let used = 0;

        $(".settlement-input").each(function () {
            used += parseFloat($(this).val()) || 0;
        });

        used = Math.round(used * 100) / 100;

        let remaining = totalAmount - used;

        if (remaining < 0) remaining = 0;

        return Math.round(remaining * 100) / 100;
    }

    function updateRemaining() {
        let remaining = calculateRemaining();
        $("#remaining").text(remaining.toFixed(2));
    }

    $("#save_settlements").on("click", function () {
        if (calculateRemaining() <= 0) {
            Swal.fire({
                title: "Confirmation",
                text: "Proceed with the details provided?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Confirm"
            }).then((result) => {
                if (result.isConfirmed) {
                    let formData = $("#settlement_form").serialize();
                    formData += "&total=" + encodeURIComponent($("#total_amount").text());

                    $.ajax({
                        url: "save_settlements",
                        type: "POST",
                        headers: {
                            "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                        },
                        data: formData,
                        success: function (response) {
                            if (response.success) {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: 'Details saved successfully!',
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                            } else {
                                Swal.fire({
                                    title: "Error",
                                    text: "An error occurred",
                                    icon: "warning"
                                });
                            }
                        }
                    });
                }
            });
        } else {
            Swal.fire({
                title: "Error",
                text: "Charges not fully dispersed.",
                icon: "warning"
            });
        }
    });

    $("#view_sett_btn").on("click", function () {
        $.ajax({
            url: "fetch_settlements",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
            },
            data: {
                consultationrefno: $("#sett_consultationrefno").val()
            },
            success: function (response) {
                if (response.success) {
                    $("#info_total").val(response.record.net_total);
                    $("#info_cash").val(response.record.cash);
                    $("#info_cta").val(response.record.cta);
                    $("#info_cta_type").val(response.record.cta_type);
                    // $("#info_cash").val(response.record.something);
                    $("#info_hmo").val(response.record.hmo);
                    $("#info_hmo_type").val(response.record.hmo_type);
                }
            }
        })
    });

    $("#complete_consultation_btn").on("click", function () {
        Swal.fire({
            title: "Confirmation",
            text: "Mark this consultation as complete?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Confirm"
        }).then((result) => {
            if (result.isConfirmed) {

            }
        });
    });

    $("#advance_information_btn").on("click", function () {
        const advanceInfoModal = new bootstrap.Modal("#advanceInfoModal");
        advanceInfoModal.show();
    });
});
