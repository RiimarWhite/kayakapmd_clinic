$(function () {
    loadPatients();
    loadSchedules(1);
    loadSchedules(2);

    function loadPatients() {
        loadPatientTable();
        loadUnschedTable();

        if ($("#doctor_id").val() == "") {
            $("#questions_container").empty().append(`<p class="m-0 ms-4">No questions loaded.</p>`);
        } else {
            $.ajax({
                url: "/api/fetch_doctor_questions", type: "POST",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: { docrefno: $("#doctor_id").val() },
                success: function (response) {
                    let index = 1;
                    $("#questions_container").empty();
                    response.docquestions.forEach(element => {
                        $("#questions_container").append(`<div class="ms-4 mb-3"><label class="form-label" for="questions${index}"><span class="fw-bold">${index}.</span> ${element.question}</label><textarea class="form-control" type="text" name="answer[${element.docquestionrefno}]"></textarea></div>`);
                        index++;
                    });
                }
            });
        }
    }

    function loadPatientTable() {
        const table = $("#patients_queue_table");

        if ($.fn.DataTable.isDataTable(table)) table.DataTable().clear().destroy();

        table.DataTable({
            processing: true, serverSide: true,
            ajax: {
                url: "/api/fetch_patients_queue", type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: { consuldate: $("#queuedate").val(), consultime: $("#stime").val(), docrefno: $("#doctor_id").val() }
            },
            columns: [
                { data: "queueno" },
                { data: null, render: function (data) {
                    return `
                        <button class="btn btn-sm btn-primary import-queue" value="${data.pxrefno}" title="Import patient data"><i class="fa-solid fa-share"></i></button>
                        <button class="btn btn-sm btn-danger reschedule" title="Reschedule"><i class="fa-solid fa-calendar-days"></i></button>`;
                }},
                { data: "patientname" },
                { data: "status", render: function (data) {
                    const badges = { WAITING: "bg-warning text-white fs-6", IN_CONSULTATION: "bg-info text-white fs-6", COMPLETED: "bg-success fs-6", UNSCHEDULED: "bg-primary fs-6", CANCELLED: "bg-danger fs-6", NO_SHOW: "bg-danger fs-6" };
                    return `<span class="badge ${badges[data] || 'bg-secondary fs-6'}">${data}</span>`;
                }}
            ],
            columnDefs: [
                { target: 0, width: "1%", orderable: false, searchable: false, className: "text-nowrap text-center align-middle fw-bold" },
                { target: 1, width: "1%", className: "text-nowrap justify-items-center text-center" },
                { target: 2, className: "text-nowrap text-truncate align-middle overflow-hidden" },
                { target: 3, width: "1%", className: "text-nowrap text-center align-middle" }
            ],
            language: { emptyTable: "No patients record yet." },
            pageLength: 10, lengthChange: false, paging: true, ordering: false,
        });
    }

    function loadUnschedTable() {
        const table = $("#patients_unsched_table");
        if ($.fn.DataTable.isDataTable(table)) table.DataTable().clear().destroy();
        table.DataTable({
            processing: true, serverSide: true,
            ajax: {
                url: "/api/fetch_unscheduled_patients", type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: { consuldate: $("#queuedate").val(), consultime: $("#stime").val(), docrefno: $("#doctor_id").val() }
            },
            columns: [
                { data: null, render: function (data) { return `<button class="btn btn-sm btn-primary import-queue" value="${data.pxrefno}" title="Import patient details"><i class="fa-solid fa-share"></i></button>`; } },
                { data: "patientname" },
                { data: "status", render: function (data) {
                    const badges = { WAITING: "bg-warning text-white fs-6", IN_CONSULTATION: "bg-info text-white fs-6", COMPLETED: "bg-success fs-6", UNSCHEDULED: "bg-primary fs-6", CANCELLED: "bg-danger fs-6", NO_SHOW: "bg-danger fs-6" };
                    return `<span class="badge ${badges[data] || 'bg-secondary fs-6'}">${data}</span>`;
                }}
            ],
            columnDefs: [
                { targets: [0, 2], width: "1%", orderable: false, searchable: false, className: "text-nowrap text-center align-middle" },
                { target: 1, className: "text-nowrap align-middle" }
            ],
            language: { emptyTable: "No new/unscheduled patients records yet." },
            pageLength: 10, lengthChange: false, info: true, paging: true, searching: true, ordering: false,
        });
    }

    function loadSchedules(index) {
        const configs = {
            1: { docrefno: $("#doctor_id").val(), consuldate: $("#queuedate").val(), timeEl: "#stime" },
            2: { docrefno: $("#doctor_for_consult").val(), consuldate: $("#sched_date").val(), timeEl: "#sched_time" },
            3: { docrefno: $("#doctor_id").val(), consuldate: $("#resched_date").val(), timeEl: "#resched_time" }
        };
        const cfg = configs[index];
        $.ajax({
            url: "/api/fetch_doctor_schedules_specific", type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { docrefno: cfg.docrefno, consuldate: cfg.consuldate },
            success: function (response) {
                $(cfg.timeEl).empty();
                if (!response.schedules || response.schedules.length === 0) {
                    $(cfg.timeEl).append(`<option selected disabled>No schedules set.</option>`).prop('disabled', true);
                    return;
                }
                $(cfg.timeEl).prop('disabled', false);
                response.schedules.forEach(e => $(cfg.timeEl).append(`<option value="${e.start}">${formatTime(e.start)} - ${formatTime(e.end)}</option>`));
                if (index === 1) $(cfg.timeEl).trigger("change");
            }
        });
    }

    function formatTime(time) {
        let [hours, minutes] = time.split(":").map(Number);
        const ampm = hours >= 12 ? "PM" : "AM";
        hours = hours % 12 || 12;
        return `${hours}:${minutes.toString().padStart(2, "0")} ${ampm}`;
    }

    function getAge(dateString) {
        const today = new Date(), birthDate = new Date(dateString);
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) age--;
        return age;
    }

    function refreshQueue() {
        $.ajax({
            url: "/api/refresh_queue", type: "POST",
            data: { date: $("#queuedate").val(), time: $("#stime").val() },
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
        });
    }

    // Event bindings
    $("#doctor_id").on("change", function () { loadPatients(); loadSchedules(1); });
    $("#doctor_for_consult").on("change", function () { loadSchedules(2); });
    $("#queuedate").on("change", function () { loadSchedules(1); });
    $("#stime").on("change", function () { loadPatientTable(); });
    $("#sched_date").on("change", function () { loadSchedules(2); });
    $("#resched_date").on("change", function () { loadSchedules(3); });

    $("#sprevious").on("click", function () {
        let d = new Date($("#queuedate").val()); d.setDate(d.getDate() - 1);
        $("#queuedate").val(d.toISOString().split("T")[0]); loadPatientTable(); loadSchedules(1);
    });

    $("#snext").on("click", function () {
        let d = new Date($("#queuedate").val()); d.setDate(d.getDate() + 1);
        $("#queuedate").val(d.toISOString().split("T")[0]); loadPatientTable(); loadSchedules(1);
    });

    $("#add_new_patient_btn").on("click", function () { new bootstrap.Modal("#add_patient_modal").show(); });

    $("#clear_form").on("click", function () {
        $("#consultation_form")[0].reset();
        $("#pxconsultationrefno").text("");
        $("#patient_picture_preview").prop("src", "/images/blank_photo.png");
    });

    $("#add_patient_btn").on("click", function () {
        const form = document.getElementById("add_patient_form");

        if (!form.checkValidity()) return form.reportValidity;

        $.ajax({
            url: "/api/add_patient",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token').attr('content') },
            data: $("#add_patient_form").serialize(),
            success: function (response) {
                if (response.success) {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Patient rescheduled!', showConfirmButton: false, timer: 1500 });
                    bootstrap.Modal.getInstance("#add_patient_modal").hide();
                    loadPatients();
                }
            }
        });
    });

    $(document).on("click", ".import-queue", function () {
        $.ajax({
            url: "/api/fetch_consultation", type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { pxrefno: $(this).val() },
            success: function (response) {
                if (response.success) {
                    const p = response.patient;

                    $("#pincode").val(p.pincode);
                    $("#pxconsultationrefno").text(p.consultationrefno);
                    $("#pxidno").val(p.pxrefno);
                    $("#pxfname").val(p.pxfirstname); $("#pxmname").val(p.pxmidname); $("#pxlname").val(p.pxlastname); $("#pxsuffix").val(p.pxsuffix);
                    $("#pxsex").val(p.gender); $("#pxbday").val(p.birthday); $("#pxage").val(getAge(p.birthday));
                    $("#pxcellnumber").val(p.mobilenumber); $("#pxemail").val(p.emailaddress); $("#pxaddress").val(p.address);
                    $("#pxreasonforconsultation").val(p.reasonforconsultation);
                    $("#pxweight").val(p.weight); $("#pxheight").val(p.height); $("#pxtemp").val(p.temp);
                    $("#pxrespiratory").val(p.respiratoryrate); $("#pxpulse").val(p.pulserate);
                    $("#pxbpnumerator").val(p.bpnumerator); $("#pxbpdenominator").val(p.bpdenominator);
                    $("#doctor_for_consult").val(p.docrefno);
                    if (p.consultation_date != "1901-01-01 00:00:00") $("#sched_date").val(p.consultation_date.split(" ")[0]);
                    else $("#sched_date").val("");
                    loadSchedules(2);
                    $("#patient_picture_preview").prop("src", p.photo_path ?? '/images/blank_photo.png');
                    response.answers.forEach(element => {
                        $(`textarea[name="answer[${element.questionrefno}]"]`).val(element.answer);
                    });
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Patient data imported', showConfirmButton: false, timer: 1500 });

                    if (p.hmocode != null) {
                        $("#patient_type").val("hmo");
                        $("#hmo_input").val(p.hmocode);
                        $("#patient_type").trigger("change");
                    }

                    loadPatientCharges();
                }
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

    $("#reschedule_btn").on("click", function () {
        $.ajax({
            url: "/api/reschedule_patient", type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { consultationrefno: $(this).val(), docrefno: $("#doctor_id").val(), date: $("#resched_date").val(), time: $("#resched_time").val() },
            success: function () {
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Patient rescheduled!', showConfirmButton: false, timer: 1500 });
                bootstrap.Modal.getInstance("#reschedule_modal").hide();
                refreshQueue(); loadPatients();
            }
        });
    });

    // Patient charges
    $("#patient_charges_btn").on("click", function () { loadPatientCharges(); });

    function loadPatientCharges() {
        $("#pxcharges_table").DataTable().destroy().clear();
        $("#pxcharges_table").DataTable({
            ajax: {
                url: "/api/fetch_pxcharges", type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: { consultationrefno: $("#pxconsultationrefno").text() },
                dataSrc: function (response) {
                    let total = 0;
                    response.charges.forEach(c => total += parseFloat(c.totalamt));
                    $("#charges_total").text(total.toFixed(2));
                    return response.charges;
                }
            },
            columns: [
                { data: null, render: function (data) { return `<button class="btn btn-sm btn-danger remove_charge" type="button" value="${data.prodcode}"><i class="fa-solid fa-trash"></i></button> <button class="btn btn-sm btn-primary edit_charge_btn_sc" type="button" value="${data.pxchargerefno}"><i class="fa-solid fa-pen-to-square"></i></button>`; } },
                { data: 'item_dscr' }, { data: 'qty' }, { data: 'totalamt' }
            ],
            columnDefs: [{ target: 0, width: '1%', orderable: false, className: 'text-nowrap text-center align-middle' }, { target: '_all', orderable: false, className: 'text-nowrap align-middle' }],
            language: { emptyTable: 'No patient charges yet.' },
            searching: false, lengthChange: false, pageLength: 5, paging: true, order: [[1, 'asc']]
        });
    }

    // Save/Update consultation
    $(document).on("click", ".save_consultation_btn", function () {
        if ($("#pxfname").val().trim() === "") return;
        if ($("#sched_time").is(":disabled")) return Swal.fire({ title: "Error", text: "No valid schedule assigned.", icon: "error" });
        if ($("#doctor_for_consult").val().trim() === "") return Swal.fire({ title: "Error", text: "Please assign a doctor first.", icon: "error" });

        let formData = new FormData($("#consultation_form")[0]);
        formData.append("docrefno", $("#doctor_for_consult").val());
        formData.append("hmo_name", $("#hmo_input").text());
        const image = document.getElementById("patient_image");
        if (image.files.length > 0) formData.append("patient_photo", image.files[0]);

        $.ajax({
            url: "/api/save_patient_consultation", type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: formData, processData: false, contentType: false,
            success: function (response) {
                if (response.success) {
                    Swal.fire({ title: "Success", text: "Record successfully saved.", icon: "success", confirmButtonText: "Okay" })
                        .then((result) => { if (result.isConfirmed) { $("#consultation_form")[0].reset(); refreshQueue(); loadPatients(); loadSchedules(2); $("#pxidno").val(''); } });
                }
            }
        });
    });

    $(document).on("click", ".update_consultation_btn", function () {
        if ($("#pxfname").val().trim() === "") return;
        if ($("#sched_time").is(":disabled")) return Swal.fire({ title: "Error", text: "No valid schedule assigned.", icon: "error" });
        if ($("#doctor_for_consult").val().trim() === "") return Swal.fire({ title: "Error", text: "Please assign a doctor first.", icon: "error" });

        let formData = new FormData($("#consultation_form")[0]);
        formData.append("pxconsultationrefno", $("#pxconsultationrefno").text());
        formData.append("hmo_name", $("#hmo_input").text());

        formData.append("docrefno", $("#doctor_for_consult").val());
        const image = document.getElementById("patient_image");
        if (image.files.length > 0) formData.append("patient_photo", image.files[0]);

        $.ajax({
            url: "/api/update_patient_consultation", type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: formData, processData: false, contentType: false,
            success: function (response) {
                if (response.success) {
                    Swal.fire({ title: "Success", text: "Record successfully updated.", icon: "success", confirmButtonText: "Okay" })
                        .then((result) => { if (result.isConfirmed) { refreshQueue(); $("#consultation_form")[0].reset(); loadPatients(); loadSchedules(2); $("#pxidno").val(''); } });
                }
            }
        });
    });

    // View masterlist
    $("#view_masterlist_btn").on("click", function () {
        const doctor = $("#doctor_id").val();
        if (!doctor) return Swal.fire({ title: "Doctor Required", text: "Please choose a doctor first.", icon: "warning" });

        new bootstrap.Modal("#patientMasterlistModal").show();
        $("#patientMasterlistTable").DataTable().destroy().clear();
        $("#patientMasterlistTable").DataTable({
            processing: true, serverSide: true,
            ajax: {
                url: "/api/fetch_patient_masterlist_sec", type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: { docrefno: doctor },
            },
            columns: [
                { data: null, render: function (data, type, row) { return `<div class="d-flex gap-1"><button class="btn btn-sm btn-primary import-btn" value="${row.casecode}"><i class="fa-solid fa-share"></i> Import</button><button class="btn btn-sm btn-secondary view-btn" value="${row.pincode}"><i class="fa-solid fa-clock-rotate-left"></i> History</button></div>`; } },
                { data: null, render: function (data, type, row) { return `<img src="${row.photo_path}" alt="patient_photo" style="max-height: 100px;">`; } },
                { data: null, render: function (data, type, row) { return [row.pxlastname, row.pxfirstname, row.pxmidname, row.pxsuffix].filter(Boolean).join(', ').toUpperCase(); } },
                { data: 'mobilenumber' }, { data: 'emailaddress' }
            ],
            columnDefs: [{ targets: [0, 1], width: "1%", orderable: false, searchable: false, className: "text-nowrap text-center align-middle" }],
            language: { emptyTable: "No patient records yet." },
        });
    });

    $(document).on("click", ".import-btn", function () {
        $.ajax({
            url: "/api/fetch_consultation", type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { casecode: $(this).val() },
            success: function (response) {
                if (response.success) {
                    const p = response.patient;
                    $("#pincode").val(p.pincode);
                    $("#pxconsultationrefno").text(p.consultationrefno);
                    $("#pxidno").val(p.pxrefno);
                    $("#pxfname").val(p.patientname); $("#pxmname").val(p.pxmidname); $("#pxlname").val(p.pxlastname); $("#pxsuffix").val(p.pxsuffix);
                    $("#pxsex").val(p.gender); $("#pxbday").val(p.birthday); $("#pxage").val(getAge(p.birthday));
                    $("#pxcellnumber").val(p.mobilenumber); $("#pxemail").val(p.emailaddress); $("#pxaddress").val(p.address);
                    $("#pxreasonforconsultation").val(p.reasonforconsultation);
                    $("#pxweight").val(p.weight); $("#pxheight").val(p.height); $("#pxtemp").val(p.temp);
                    $("#pxrespiratory").val(p.respiratoryrate); $("#pxpulse").val(p.pulserate);
                    $("#pxbpnumerator").val(p.bpnumerator); $("#pxbpdenominator").val(p.bpdenominator);
                    $("#doctor_for_consult").val(p.docrefno);
                    $("#sched_date").val(p.consultation_date.split(" ")[0]);
                    loadSchedules(2);
                    $("#patient_picture_preview").prop("src", p.photo_path);
                    response.answers.forEach(element => { $(`textarea[name="answer[${element.questionrefno}]"]`).val(element.answer); });
                    bootstrap.Modal.getInstance(document.getElementById('patientMasterlistModal')).hide();
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Patient data imported', showConfirmButton: false, timer: 1500 });
                }
            }
        });
    });

    // Settlements
    $("#settlement_btn").on("click", function () {
        $.ajax({
            url: "/api/fetch_settlements", type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { consultationrefno: $("#pxconsultationrefno").text() },
            success: function (response) {
                if (response.success) {
                    const r = response.record;
                    $("#sett_net_total").val(r.net_total); $("#sett_cash").val(r.cash);
                    $("#sett_cta").val(r.cta); $("#sett_cta_type").val(r.cta_type);
                    $("#sett_hmo").val(r.hmo); $("#sett_hmo_type").val(r.hmo_type);
                }
            }
        });
    });

    $("#hmo_input").select2({
        width: "100%",
        ajax: {
            url: "/api/fetch_hmo",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            processResults: function (response) {
                return {
                    results: $.map(response.hmo, function (i) {
                        return { id: i.hmocode, text: i.hmoname }
                    })
                }
            }
        },
        placeholder: "Select HMO..."
    });

    $("#patient_type").on("change", function () {
        const s2Container = $("#hmo_input").next(".select2-container");

        if ($(this).val() === "hmo") {
            s2Container.show();
        } else {
            s2Container.hide();
        }
    });
});
