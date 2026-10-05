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

    // Detailed Comment: Refresh charges and adjust column widths when the Payment Details tab is opened
    $(document).on('shown.bs.tab', 'button[data-bs-target="#payment_info"], #patient_charges_btn', function () {
        loadPatientCharges();
        if ($.fn.DataTable.isDataTable("#pxcharges_table")) {
            $("#pxcharges_table").DataTable().columns.adjust().draw(false);
        }
    });

    // Patient charges
    $("#patient_charges_btn").on("click", function () {
        loadPatientCharges();
    });

    /**
     * Detailed Comment: Load and display patient charges for current consultation in secretary console.
     * Safely checks for existing DataTable, initializes cleanly with 5 columns (Actions, Description, Quantity, Unit Price, Amount),
     * and uses null-safe row data extractors.
     */
    function loadPatientCharges() {
        const refno = String($("#pxconsultationrefno").text() || $("#pxconsultationrefno").val() || "").trim();

        // Detailed Comment: If DataTable is already initialized, reload cleanly without destroying the table DOM
        if ($.fn.DataTable.isDataTable("#pxcharges_table")) {
            const table = $("#pxcharges_table").DataTable();
            if (!refno) {
                $("#charges_total").text("0.00");
                table.clear().draw();
                return;
            }
            table.ajax.reload(function () {
                table.columns.adjust();
            }, false);
            return;
        }

        $("#pxcharges_table tbody").empty();

        // Detailed Comment: If no consultation is selected, initialize with all 5 matching empty columns
        if (!refno) {
            $("#charges_total").text("0.00");
            $("#pxcharges_table").DataTable({
                data: [],
                columns: [
                    { data: null, defaultContent: "" },
                    { data: 'item_dscr', defaultContent: "" },
                    { data: 'qty', defaultContent: "" },
                    { data: 'cost_ave', defaultContent: "" },
                    { data: 'totalamt', defaultContent: "" }
                ],
                columnDefs: [
                    { targets: 0, width: '10%', orderable: false, className: 'text-nowrap text-center align-middle' },
                    { targets: '_all', orderable: false, className: 'text-nowrap align-middle' }
                ],
                language: {
                    emptyTable: 'Please select a patient consultation to view charges.'
                },
                layout: {
                    bottomStart: 'paging',
                    bottomEnd: null
                },
                searching: false,
                lengthChange: false,
                pageLength: 5,
                paging: true,
                order: []
            });
            return;
        }

        $("#pxcharges_table").DataTable({
            ajax: {
                url: "/api/fetch_pxcharges",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                },
                data: function (d) {
                    d.consultationrefno = String($("#pxconsultationrefno").text() || $("#pxconsultationrefno").val() || "").trim();
                },
                dataSrc: function (response) {
                    let total = 0;
                    const list = (response && response.charges && Array.isArray(response.charges)) ? response.charges : [];
                    list.forEach(c => {
                        const val = parseFloat(c.totalamt || c.cost_ave || 0);
                        if (!isNaN(val)) total += val;
                    });

                    $("#charges_total").text(total.toFixed(2));

                    return list;
                }
            },
            columns: [
                {
                    data: null,
                    render: function (data, type, row) {
                        const r = row || data || {};
                        const chargeId = r.id || r.pxchargerefno || r.prodcode || '';
                        const itemDscr = (r.item_dscr || r.servicename || '').replace(/"/g, '&quot;');
                        const unitPrice = parseFloat(r.cost_ave || r.sellingprice || (parseFloat(r.totalamt || 0) / Math.max(1, parseFloat(r.qty || 1))) || 0).toFixed(2);
                        const qty = r.qty || 1;
                        const totalAmt = parseFloat(r.totalamt || 0).toFixed(2);
                        return `
                            <div class="d-flex gap-1 justify-content-center">
                                <button class="btn btn-sm btn-outline-danger remove_charge" type="button"
                                    value="${chargeId}"
                                    data-id="${chargeId}"
                                    data-prodcode="${r.prodcode || ''}"
                                    data-item="${itemDscr}"
                                    title="Delete Fee">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-primary edit_charge_btn_sc" type="button"
                                    value="${chargeId}"
                                    data-id="${chargeId}"
                                    data-prodcode="${r.prodcode || ''}"
                                    data-item="${itemDscr}"
                                    data-price="${unitPrice}"
                                    data-qty="${qty}"
                                    data-total="${totalAmt}"
                                    title="Edit Fee">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                            </div>
                        `;
                    }
                },
                {
                    data: 'item_dscr',
                    defaultContent: '',
                    render: function (data, type, row) {
                        const r = row || {};
                        const title = (data || r.servicename || r.item_dscr || 'Item / Service').replace(/"/g, '&quot;');
                        // Detailed Comment: Enriched display for Professional Fee showing doctor billing rates (PF, Vatable, +VAT, W/Tax, ROD)
                        if (r.is_pf || r.prodcode === 'PF' || r.item_grouping === 'PROFESSIONAL FEE') {
                            const pfRate = parseFloat(r.pf_rate || r.cost_ave || 0).toFixed(2);
                            const rodRate = parseFloat(r.rod_rate || 0).toFixed(2);
                            const taxPct = parseFloat(r.tax_percent || 0).toFixed(1);
                            const isVatable = Boolean(r.vatable);
                            const isAutoAddVat = Boolean(r.auto_add_vat);
                            return `
                                <div>
                                    <div class="fw-semibold text-dark">${title}</div>
                                    <div class="d-flex flex-wrap gap-1 mt-1 small">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">PF Rate: ₱${pfRate}</span>
                                        <span class="badge ${isVatable ? 'bg-info-subtle text-info-emphasis border border-info-subtle' : 'bg-secondary-subtle text-secondary border'}">${isVatable ? 'Vatable (12%)' : 'Non-VAT'}</span>
                                        ${isAutoAddVat ? '<span class="badge bg-secondary-subtle text-secondary border">+VAT Added</span>' : ''}
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">W/Tax: ${taxPct}%</span>
                                        ${parseFloat(rodRate) > 0 ? `<span class="badge bg-light text-muted border">ROD: ₱${rodRate}</span>` : ''}
                                    </div>
                                </div>
                            `;
                        } else {
                            // Detailed Comment: Enriched display for standard catalog charges showing applied patient tier and comparison with regular price
                            const regPrice = parseFloat(r.regular_price || 0);
                            const curPrice = parseFloat(r.cost_ave || r.sellingprice || 0);
                            const tier = r.posted_tier || 'REGULAR';
                            const tierBadgeClass = tier === 'PHIC' ? 'bg-success-subtle text-success-emphasis border border-success-subtle' :
                                (tier === 'HMO' ? 'bg-info-subtle text-info-emphasis border border-info-subtle' :
                                (tier === 'OTHERS' ? 'bg-warning-subtle text-warning-emphasis border border-warning-subtle' :
                                'bg-light text-dark border'));
                            let diffHtml = '';
                            if (regPrice > 0 && Math.abs(curPrice - regPrice) > 0.01) {
                                if (curPrice < regPrice) {
                                    diffHtml = `<span class="badge bg-success-subtle text-success border border-success-subtle ms-1">Saved ₱${(regPrice - curPrice).toFixed(2)}</span>`;
                                } else {
                                    diffHtml = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-1">+₱${(curPrice - regPrice).toFixed(2)}</span>`;
                                }
                            }
                            return `
                                <div>
                                    <div class="fw-semibold text-dark">${title}</div>
                                    <div class="d-flex flex-wrap align-items-center gap-1 mt-1 small">
                                        <span class="badge ${tierBadgeClass}">${tier} Tier</span>
                                        ${regPrice > 0 ? `<span class="text-muted" style="font-size: 0.78rem;">Reg: ₱${regPrice.toFixed(2)}</span>` : ''}
                                        ${diffHtml}
                                    </div>
                                </div>
                            `;
                        }
                    }
                },
                {
                    data: 'qty',
                    defaultContent: '1',
                    className: 'text-center align-middle',
                    render: function (data) { return parseFloat(data || 1); }
                },
                {
                    data: 'cost_ave',
                    defaultContent: '0.00',
                    className: 'text-end align-middle fw-semibold',
                    render: function (data, type, row) {
                        const r = row || {};
                        const price = parseFloat(data || r.sellingprice || r.current_price || (parseFloat(r.totalamt || 0) / Math.max(1, parseFloat(r.qty || 1))) || 0);
                        return isNaN(price) ? '0.00' : price.toFixed(2);
                    }
                },
                {
                    data: 'totalamt',
                    defaultContent: '0.00',
                    className: 'text-end align-middle fw-bold text-primary',
                    render: function (data, type, row) {
                        const amt = parseFloat(data || (row && row.totalamt) || 0);
                        return isNaN(amt) ? '0.00' : amt.toFixed(2);
                    }
                }
            ],
            columnDefs: [
                { targets: 0, width: '10%', orderable: false, className: 'text-nowrap text-center align-middle' },
                { targets: 1, orderable: false, className: 'align-middle' },
                { targets: 2, width: '10%', orderable: false, className: 'text-center align-middle' },
                { targets: [3, 4], width: '15%', orderable: false, className: 'text-end align-middle' }
            ],
            language: {
                emptyTable: 'No charges recorded for this consultation yet.'
            },
            layout: {
                bottomStart: 'paging',
                bottomEnd: null
            },
            searching: false,
            lengthChange: false,
            pageLength: 5,
            paging: true,
            order: []
        });
    }

    // Charges-related
    let appendedPxcharges = [];
    $("#append_pxcharges_btn").on("click", function () {
        appendedPxcharges = [];

        const chargeModal = new bootstrap.Modal("#append_charge_modal");

        $.ajax({
            url: "/api/fetch_charge_categories_sc",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
            },
            data: {
                consultationrefno: String($("#pxconsultationrefno").text() || $("#pxconsultationrefno").val() || "").trim()
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

            $.getJSON("/api/fetch_all_charges", {
                category: $("#search_filter").val() || "",
                consultationrefno: String($("#pxconsultationrefno").text() || $("#pxconsultationrefno").val() || "").trim()
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
            url: "/api/save_patient_charges",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
            },
            contentType: "application/json",
            data: JSON.stringify({
                consultationrefno: String($("#pxconsultationrefno").text() || $("#pxconsultationrefno").val() || "").trim(),
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
                    loadPatientCharges();
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
                    url: "/api/delete_patient_charge",
                    type: "POST",
                    headers: {
                        "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                    },
                    data: {
                        chargerefno: $(this).val(),
                        consultationrefno: String($("#pxconsultationrefno").text() || $("#pxconsultationrefno").val() || "").trim()
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                title: "Success",
                                text: "Charge have been removed successfully.",
                                icon: "success"
                            });

                            loadPatientCharges();
                        }
                    }
                });
            }
        });
    });

    $(document).on("click", ".edit_charge_btn_sc", function () {
        const $btn = $(this);
        const chargeVal = $btn.val();
        const $row = $btn.closest("tr");
        const rowData = $("#pxcharges_table").DataTable().row($row).data() || {};
        const itemDscr = rowData.item_dscr || rowData.servicename || "Charge Item";
        const currentPrice = parseFloat(rowData.cost_ave || rowData.sellingprice || rowData.net_total || 0);
        const currentQty = parseFloat(rowData.qty || 1);
        const isPf = Boolean(rowData.is_pf || rowData.prodcode === 'PF' || rowData.item_grouping === 'PROFESSIONAL FEE');

        // Detailed Comment: Build contextual information card based on whether the charge is PF or catalog item
        let detailsCardHtml = '';
        if (isPf) {
            const pfRate = parseFloat(rowData.pf_rate || currentPrice || 0).toFixed(2);
            const rodRate = parseFloat(rowData.rod_rate || 0).toFixed(2);
            const taxPercent = parseFloat(rowData.tax_percent || 0).toFixed(1);
            const vatable = Boolean(rowData.vatable);
            const autoAddVat = Boolean(rowData.auto_add_vat);

            detailsCardHtml = `
                <div class="card bg-light border-primary mb-3 text-start">
                    <div class="card-header bg-primary text-white py-1 px-2 fw-bold small">
                        <i class="fa-solid fa-user-doctor me-1"></i> Professional Fee &amp; Billing Rates
                    </div>
                    <div class="card-body p-2 small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Doctor Base PF / Consultation Fee:</span>
                            <strong class="text-primary">₱${pfRate}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">ROD Rate (PHP):</span>
                            <span>₱${rodRate}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Withholding Tax (%):</span>
                            <span>${taxPercent}%</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Tax Status:</span>
                            <div>
                                <input type="checkbox" class="form-check-input me-1" ${vatable ? 'checked' : ''} disabled>
                                <span class="badge ${vatable ? 'bg-info text-dark' : 'bg-secondary'}">${vatable ? 'Vatable (12%)' : 'Non-VAT'}</span>
                                ${autoAddVat ? '<span class="badge bg-secondary ms-1">+VAT Auto-Added</span>' : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        } else {
            const regPrice = parseFloat(rowData.regular_price || currentPrice || 0).toFixed(2);
            const phicPrice = parseFloat(rowData.price_phic || 0).toFixed(2);
            const hmoPrice = parseFloat(rowData.price_hmo || 0).toFixed(2);
            const othersPrice = parseFloat(rowData.price_others || 0).toFixed(2);
            const postedTier = rowData.posted_tier || 'REGULAR';
            const postedTierPrice = parseFloat(rowData.posted_tier_price || currentPrice || 0).toFixed(2);

            detailsCardHtml = `
                <div class="card bg-light border-secondary mb-3 text-start">
                    <div class="card-header bg-secondary text-white py-1 px-2 fw-bold small">
                        <i class="fa-solid fa-tags me-1"></i> Catalog Pricing Comparison
                    </div>
                    <div class="card-body p-2 small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Regular Catalog Price:</span>
                            <strong>₱${regPrice}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">PHIC Tier Price:</span>
                            <span class="${postedTier === 'PHIC' ? 'fw-bold text-success' : ''}">₱${phicPrice}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">HMO Tier Price:</span>
                            <span class="${postedTier === 'HMO' ? 'fw-bold text-info' : ''}">₱${hmoPrice}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Others / Special Price:</span>
                            <span class="${postedTier === 'OTHERS' ? 'fw-bold text-warning' : ''}">₱${othersPrice}</span>
                        </div>
                        <div class="d-flex justify-content-between pt-1 border-top">
                            <span class="text-muted">Applied Classification Tier:</span>
                            <span class="badge bg-primary">${postedTier} (₱${postedTierPrice})</span>
                        </div>
                    </div>
                </div>
            `;
        }

        Swal.fire({
            title: "Edit Charge",
            html: `
                <div class="mb-3 text-start">
                    <span class="fw-bold text-primary fs-6">${itemDscr}</span>
                </div>
                ${detailsCardHtml}
                <div class="text-start">
                    <label class="form-label fw-bold small" for="charge_input_sw">Unit Price (₱)</label>
                    <input class="form-control" type="number" step="0.01" min="0" name="charge_input_sw" id="charge_input_sw" value="${currentPrice.toFixed(2)}">
                </div>
            `,
            confirmButtonText: "Update",
            showCancelButton: true,
            preConfirm: () => {
                const charge = $("#charge_input_sw").val();

                if (!charge || isNaN(parseFloat(charge)) || parseFloat(charge) < 0) {
                    return Swal.showValidationMessage("Please enter a valid unit price.");
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const consulRef = String($("#pxconsultationrefno").text() || $("#pxconsultationrefno").val() || "").trim();
                $.ajax({
                    url: "/api/update_charge",
                    type: "POST",
                    headers: {
                        "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                    },
                    data: {
                        consultationrefno: consulRef,
                        pxchargerefno: chargeVal,
                        chargeid: rowData.id || '',
                        prodcode: rowData.prodcode || '',
                        charge_fee: $("#charge_input_sw").val(),
                        charge_qty: currentQty,
                        discount: 0
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

                            loadPatientCharges();
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
    // Patient settlements
    let totalAmount = 0.00;

    /**
     * Detailed Comment: Computes total deductions (Senior/PWD, PhilHealth, HMO, Other discounts)
     * and calculates Net Billing = max(0, totalGross - totalDeductions).
     */
    function calculateNetBilling() {
        let deductions = 0;
        if ($("#is_srpwd").is(":checked")) {
            deductions += parseFloat($("#less_srpwd").val()) || 0;
        }
        deductions += parseFloat($("#phic").val()) || 0;
        deductions += parseFloat($("#hmo").val()) || 0;
        deductions += parseFloat($("#less_discount").val()) || 0;
        deductions = Math.round(deductions * 100) / 100;

        let netBilling = Math.max(0, totalAmount - deductions);
        return Math.round(netBilling * 100) / 100;
    }

    /**
     * Detailed Comment: Computes remaining balance to be dispersed into Cash and CTA payments.
     */
    function calculateRemaining() {
        const netBilling = calculateNetBilling();
        let payments = 0;
        payments += parseFloat($("#cash").val()) || 0;
        payments += parseFloat($("#cta").val()) || 0;
        payments = Math.round(payments * 100) / 100;

        let remaining = Math.max(0, netBilling - payments);
        return Math.round(remaining * 100) / 100;
    }

    /**
     * Detailed Comment: Updates Net Billing and Remaining balance displays and hidden form inputs.
     */
    function updateRemaining() {
        const netBilling = calculateNetBilling();
        const remaining = calculateRemaining();
        $("#net_billing_display").text(netBilling.toFixed(2));
        $("#net_payable_input").val(netBilling.toFixed(2));
        $("#remaining").text(remaining.toFixed(2));
    }

    // Detailed Comment: Toggle Senior/PWD discount fields and trigger recomputation
    $("#is_srpwd").on("change", function () {
        if ($(this).is(":checked")) {
            $("#srpwd_fields_wrap").removeClass("d-none");
            $("#less_srpwd").focus();
        } else {
            $("#srpwd_fields_wrap").addClass("d-none");
            $("#srpwd_refno").val("");
            $("#less_srpwd").val("");
        }
        updateRemaining();
    });

    // Detailed Comment: Re-adjust Select2 width and attach to modal container upon settlement modal being fully shown
    $("#settlement_modal").on("shown.bs.modal", function () {
        if (!$("#hmo_type").hasClass("select2-hidden-accessible")) {
            $("#hmo_type").select2({
                dropdownParent: $("#settlement_modal"),
                width: "100%",
                placeholder: "-- Select HMO --",
                allowClear: true
            });
        }
    });

    $("#settlement_btn").on("click", function () {
        totalAmount = parseFloat($("#charges_total").text()) || 0;

        $("#total_amount").text(totalAmount.toFixed(2));
        $("#total").val(totalAmount.toFixed(2));
        const refno = $("#pxconsultationrefno").text() || $("#pxconsultationrefno").val();
        $("#sett_consultationrefno").val(refno);

        $("#settlement_form")[0].reset();
        $("#is_srpwd").prop("checked", false);
        $("#srpwd_fields_wrap").addClass("d-none");
        updateRemaining();

        // Detailed Comment: Initialize searchable Select2 dropdown for HMO provider inside settlement modal
        if (!$("#hmo_type").hasClass("select2-hidden-accessible")) {
            $("#hmo_type").select2({
                dropdownParent: $("#settlement_modal"),
                width: "100%",
                placeholder: "-- Select HMO --",
                allowClear: true
            });
        }
        $("#hmo_type").val("").trigger("change");

        // Detailed Comment: Pre-populate existing settlement data from pxsettlements including HMO selection from hmo_masterlist
        if (refno) {
            $.ajax({
                url: "/api/fetch_settlements",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: { consultationrefno: refno },
                success: function (resSett) {
                    if (resSett.success && resSett.record) {
                        const r = resSett.record;
                        if (parseFloat(r.less_srpwd || 0) > 0 || r.srpwd_refno) {
                            $("#is_srpwd").prop("checked", true);
                            $("#srpwd_fields_wrap").removeClass("d-none");
                            if (r.srpwd_refno) $("#srpwd_refno").val(r.srpwd_refno);
                            if (parseFloat(r.less_srpwd || 0) > 0) $("#less_srpwd").val(parseFloat(r.less_srpwd).toFixed(2));
                        }
                        if (r.phic_icd_rvs) $("#phic_icd_rvs").val(r.phic_icd_rvs);
                        if (parseFloat(r.phic || r.less_phic || 0) > 0) $("#phic").val(parseFloat(r.phic || r.less_phic).toFixed(2));

                        // HMO dropdown from hmo_masterlist
                        const hmoVal = r.hmocode || r.hmo_type;
                        if (hmoVal) {
                            $("#hmo_type").val(hmoVal).trigger("change");
                            if (!$("#hmo_type").val()) {
                                $("#hmo_type option").each(function () {
                                    if ($(this).text().trim().toLowerCase() === String(hmoVal).trim().toLowerCase()) {
                                        $("#hmo_type").val($(this).val()).trigger("change");
                                    }
                                });
                            }
                        } else {
                            $("#hmo_type").val("").trigger("change");
                        }
                        if (parseFloat(r.hmo || r.less_hmo || 0) > 0) $("#hmo").val(parseFloat(r.hmo || r.less_hmo).toFixed(2));
                        if (r.discount_description) $("#discount_description").val(r.discount_description);
                        if (parseFloat(r.less_discount || 0) > 0) $("#less_discount").val(parseFloat(r.less_discount).toFixed(2));
                        if (parseFloat(r.cash || r.payment_cash || 0) > 0) $("#cash").val(parseFloat(r.cash || r.payment_cash).toFixed(2));
                        if (parseFloat(r.cta || r.payment_card || 0) > 0) $("#cta").val(parseFloat(r.cta || r.payment_card).toFixed(2));
                        if (r.cta_type) $("#card_type").val(r.cta_type);

                        updateRemaining();
                    }
                }
            });
        }
    });

    // Detailed Comment: Import Net Billing into target settlement payment channel (Cash or CTA)
    $(document).on("click", ".import-net-billing, .import-total", function () {
        let input = $(this).closest(".input-group").find(".settlement-payment-input, .settlement-input");
        const netBilling = calculateNetBilling();
        let otherPayment = 0;
        $(".settlement-payment-input, .settlement-input").not(input).each(function () {
            otherPayment += parseFloat($(this).val()) || 0;
        });

        let remaining = Math.max(0, netBilling - otherPayment);
        input.val(remaining > 0 ? remaining.toFixed(2) : "").trigger("input");
    });

    // Detailed Comment: Dynamic deduction calculation with Gross bounding
    $(document).on("input", ".deduction-input", function () {
        let currentInput = $(this);
        let value = parseFloat(currentInput.val()) || 0;
        if (value < 0) value = 0;

        let usedExceptCurrent = 0;
        $(".deduction-input").not(currentInput).each(function () {
            if ($(this).attr("id") === "less_srpwd" && !$("#is_srpwd").is(":checked")) return;
            usedExceptCurrent += parseFloat($(this).val()) || 0;
        });

        let maxAllowed = Math.max(0, totalAmount - usedExceptCurrent);
        if (value > maxAllowed) {
            value = maxAllowed;
        }
        value = Math.round(value * 100) / 100;
        currentInput.val(value > 0 ? value.toFixed(2) : "");
        updateRemaining();
    });

    // Detailed Comment: Dynamic payment dispersion calculation bounded by Net Billing
    $(document).on("input", ".settlement-payment-input, .settlement-input", function () {
        let currentInput = $(this);
        let value = parseFloat(currentInput.val()) || 0;
        if (value < 0) value = 0;

        const netBilling = calculateNetBilling();
        let otherPayment = 0;
        $(".settlement-payment-input, .settlement-input").not(currentInput).each(function () {
            otherPayment += parseFloat($(this).val()) || 0;
        });

        let maxAllowed = Math.max(0, netBilling - otherPayment);
        if (value > maxAllowed) {
            value = maxAllowed;
        }
        value = Math.round(value * 100) / 100;
        currentInput.val(value > 0 ? value.toFixed(2) : "");
        updateRemaining();
    });

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
                        url: "/api/save_settlements",
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
            url: "/api/fetch_settlements",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
            },
            data: {
                consultationrefno: $("#sett_consultationrefno").val()
            },
            success: function (response) {
                if (response.success && response.record) {
                    const r = response.record;
                    const cardMap = { 'cc': 'Credit Card', 'dc': 'Debit Card' };
                    const cardLabel = cardMap[r.cta_type] || r.cta_type || 'None';

                    let hmoLabel = r.hmoname || r.hmo_type || r.hmocode || 'None';
                    const hmoOption = $(`#hmo_type option[value="${r.hmocode || r.hmo_type}"]`).text();
                    if (hmoOption && hmoOption !== '-- Select HMO --') {
                        hmoLabel = hmoOption;
                    }

                    $("#info_total").val('PHP ' + parseFloat(r.total_gross || totalAmount).toFixed(2));
                    $("#info_srpwd").val('PHP ' + parseFloat(r.less_srpwd || 0).toFixed(2));
                    $("#info_srpwd_ref").val(r.srpwd_refno || 'None');
                    $("#info_phic").val('PHP ' + parseFloat(r.less_phic || r.phic || 0).toFixed(2));
                    $("#info_phic_icd").val(r.phic_icd_rvs || 'None');
                    $("#info_hmo").val('PHP ' + parseFloat(r.less_hmo || r.hmo || 0).toFixed(2));
                    $("#info_hmo_type").val(hmoLabel);
                    $("#info_discount").val('PHP ' + parseFloat(r.less_discount || 0).toFixed(2));
                    $("#info_discount_desc").val(r.discount_description || 'None');
                    $("#info_net_payable").val('PHP ' + parseFloat(r.net_payable || (r.total_gross || 0)).toFixed(2));
                    $("#info_cash").val('PHP ' + parseFloat(r.payment_cash || r.cash || 0).toFixed(2));
                    $("#info_cta").val('PHP ' + parseFloat(r.payment_card || r.cta || 0).toFixed(2));
                    $("#info_cta_type").val(cardLabel);
                }
            }
        });
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
