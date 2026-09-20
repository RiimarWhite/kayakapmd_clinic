$(function () {
    /**
     * Detailed Comment: Helper functions to toggle button loading spinners and disabled state
     * Stores original button HTML in data attribute and restores upon operation completion.
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

    const todayStr = new Date().toISOString().split('T')[0];
    if (!$("#queuedate").val()) $("#queuedate").val(todayStr);
    if (!$("#sched_date").val()) $("#sched_date").val(todayStr);

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
                        <div class="d-flex gap-1 justify-content-center">
                            <button class="btn btn-sm btn-primary import-queue" value="${data.pxrefno}" data-consultationrefno="${data.consultationrefno || ''}" title="Import patient data"><i class="fa-solid fa-share"></i></button>
                            <button class="btn btn-sm btn-success update-queue" value="${data.consultationrefno}" title="Update Queue Status"><i class="fa-solid fa-check"></i></button>
                            <button class="btn btn-sm btn-danger reschedule" value="${data.consultationrefno}" title="Reschedule"><i class="fa-solid fa-calendar-days"></i></button>
                        </div>`;
                }},
                { data: "patientname" },
                { data: "status", render: function (data) {
                    const badges = { WAITING: "bg-warning text-white fs-6", IN_CONSULTATION: "bg-info text-white fs-6", FOR_BILLING: "bg-primary text-white fs-6", COMPLETED: "bg-success fs-6", UNSCHEDULED: "bg-primary fs-6", CANCELLED: "bg-danger fs-6", NO_SHOW: "bg-danger fs-6" };
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
                { data: null, render: function (data) { return `<button class="btn btn-sm btn-primary import-queue" value="${data.pxrefno}" data-consultationrefno="${data.consultationrefno || ''}" title="Import patient details"><i class="fa-solid fa-share"></i></button>`; } },
                { data: "patientname" },
                { data: "status", render: function (data) {
                    const badges = { WAITING: "bg-warning text-white fs-6", IN_CONSULTATION: "bg-info text-white fs-6", FOR_BILLING: "bg-primary text-white fs-6", COMPLETED: "bg-success fs-6", UNSCHEDULED: "bg-primary fs-6", CANCELLED: "bg-danger fs-6", NO_SHOW: "bg-danger fs-6" };
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
        const $btn = $(this);
        setBtnLoading($btn, "");
        let d = new Date($("#queuedate").val()); d.setDate(d.getDate() - 1);
        $("#queuedate").val(d.toISOString().split("T")[0]);
        loadPatientTable();
        loadSchedules(1);
        setTimeout(() => resetBtnLoading($btn), 400);
    });

    $("#snext").on("click", function () {
        const $btn = $(this);
        setBtnLoading($btn, "");
        let d = new Date($("#queuedate").val()); d.setDate(d.getDate() + 1);
        $("#queuedate").val(d.toISOString().split("T")[0]);
        loadPatientTable();
        loadSchedules(1);
        setTimeout(() => resetBtnLoading($btn), 400);
    });

    $("#add_new_patient_btn").on("click", function () { new bootstrap.Modal("#add_patient_modal").show(); });

    $("#clear_form").on("click", function () {
        $("#consultation_form")[0].reset();
        $("#pxconsultationrefno").text("");
        $("#patient_picture_preview").prop("src", "/images/blank_photo.png");
    });

    // Detailed Comment: Add Patient submit handler with button loading spinner and field validation
    $("#add_patient_btn").on("click", function () {
        const form = document.getElementById("add_patient_form");

        if (!form.checkValidity()) return form.reportValidity();

        const $btn = $(this);
        setBtnLoading($btn, "Adding...");

        $.ajax({
            url: "/api/add_patient",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: $("#add_patient_form").serialize(),
            success: function (response) {
                if (response.success) {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Patient registered!', showConfirmButton: false, timer: 1500 });
                    bootstrap.Modal.getInstance(document.getElementById("add_patient_modal")).hide();
                    loadPatients();
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

    // Detailed Comment: Import patient queue record with button loading state and multi-key fallback
    $(document).on("click", ".import-queue", function () {
        const $btn = $(this);
        setBtnLoading($btn, "");
        const pxrefno = $btn.val();
        const consultationrefno = $btn.data("consultationrefno");

        $.ajax({
            url: "/api/fetch_consultation", type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { pxrefno: pxrefno, consultationrefno: consultationrefno },
            success: function (response) {
                if (response.success && response.patient) {
                    const p = response.patient;

                    $("#pincode").val(p.pincode || "");
                    $("#pxconsultationrefno").text(p.consultationrefno || "").val(p.consultationrefno || "");
                    // Detailed Comment: Set both text and val on pxidno span element so patient reference displays in UI and is readable
                    $("#pxidno").text(p.pxrefno || "").val(p.pxrefno || "");
                    $("#pxfname").val(p.pxfirstname || p.patientname || "");
                    $("#pxmname").val(p.pxmidname || "");
                    $("#pxlname").val(p.pxlastname || "");
                    $("#pxsuffix").val(p.pxsuffix || "");
                    $("#pxsex").val(p.gender || "male");
                    $("#pxbday").val(p.birthday || "");
                    $("#pxage").val(p.birthday ? getAge(p.birthday) : "");
                    $("#pxcellnumber").val(p.mobilenumber || "");
                    $("#pxemail").val(p.emailaddress || "");
                    $("#pxaddress").val(p.address || "");
                    $("#pxreasonforconsultation").val(p.reasonforconsultation || "");
                    $("#pxweight").val(p.weight || "");
                    $("#pxheight").val(p.height || "");
                    $("#pxtemp").val(p.temp || "");
                    $("#pxrespiratory").val(p.respiratoryrate || "");
                    $("#pxpulse").val(p.pulserate || "");
                    $("#pxbpnumerator").val(p.bpnumerator || "");
                    $("#pxbpdenominator").val(p.bpdenominator || "");
                    if (p.docrefno) $("#doctor_for_consult").val(p.docrefno);
                    if (p.consultation_date && p.consultation_date !== "1901-01-01 00:00:00") {
                        $("#sched_date").val(p.consultation_date.split(" ")[0]);
                    } else {
                        $("#sched_date").val("");
                    }
                    loadSchedules(2);
                    $("#patient_picture_preview").prop("src", p.photo_path ?? '/images/blank_photo.png');
                    if (response.answers && Array.isArray(response.answers)) {
                        response.answers.forEach(element => {
                            $(`textarea[name="answer[${element.questionrefno}]"]`).val(element.answer);
                        });
                    }
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Patient data imported', showConfirmButton: false, timer: 1500 });

                    // Detailed Comment: Update secretary 1-click printable document links with imported consultation reference
                    const basePath = window.location.pathname.startsWith('/kayakapmd_clinic') ? '/kayakapmd_clinic' : '';
                    const cref = p.consultationrefno || '';
                    if (cref) {
                        $("#sec_print_rx_btn").attr("href", `${basePath}/print_pdf?type=rx&consultationrefno=${cref}`);
                        $("#sec_print_diag_btn").attr("href", `${basePath}/print_pdf?type=diagnostics&consultationrefno=${cref}`);
                        $("#sec_print_admit_btn").attr("href", `${basePath}/print_pdf?type=admission&consultationrefno=${cref}`);
                        $("#sec_print_soa_btn").attr("href", `${basePath}/print_pdf?type=soa&consultationrefno=${cref}`);
                    } else {
                        $("#sec_print_rx_btn, #sec_print_diag_btn, #sec_print_admit_btn, #sec_print_soa_btn").attr("href", "#");
                    }

                    if (p.hmocode != null) {
                        $("#patient_type").val("hmo");
                        $("#hmo_input").val(p.hmocode);
                        $("#patient_type").trigger("change");
                    }

                    loadPatientCharges();
                } else {
                    Swal.fire({ title: "Error", text: (response && response.message) || "Failed to import patient details.", icon: "error" });
                }
            },
            error: function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to import patient details.";
                Swal.fire({ title: "Error", text: msg, icon: "error" });
            },
            complete: function () {
                resetBtnLoading($btn);
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

    // Detailed Comment: Reschedule patient submission with button loading spinner and error feedback
    $("#reschedule_btn").on("click", function () {
        const $btn = $(this);
        setBtnLoading($btn, "Rescheduling...");

        $.ajax({
            url: "/api/reschedule_patient", type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { consultationrefno: $(this).val(), docrefno: $("#doctor_id").val(), date: $("#resched_date").val(), time: $("#resched_time").val() },
            success: function (response) {
                if (response.success) {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Patient rescheduled!', showConfirmButton: false, timer: 1500 });
                    bootstrap.Modal.getInstance(document.getElementById("reschedule_modal")).hide();
                    refreshQueue(); loadPatients();
                } else {
                    Swal.fire({ title: "Error", text: response.message || "Failed to reschedule patient.", icon: "error" });
                }
            },
            error: function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to reschedule patient.";
                Swal.fire({ title: "Error", text: msg, icon: "error" });
            },
            complete: function () {
                resetBtnLoading($btn);
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
            // Detailed Comment: In DataTables 2, layout uses standard feature keys to avoid "Unknown feature: html" warning.
            // Total amount is rendered in the blade view below the table and updated dynamically via dataSrc.
            layout: {
                bottomStart: 'paging',
                bottomEnd: null
            },
            searching: false, lengthChange: false, pageLength: 5, paging: true, order: [[1, 'asc']]
        });
    }

    // Detailed Comment: Save consultation record with defensive null-coalescing string handling and button loading state
    $(document).on("click", ".save_consultation_btn", function () {
        const $btn = $(this);
        const pxfname = String($("#pxfname").val() || "").trim();
        if (pxfname === "") {
            return Swal.fire({ title: "Validation Error", text: "Patient first name is required.", icon: "warning" });
        }
        const docrefno = String($("#doctor_for_consult").val() || "").trim();
        if (docrefno === "") {
            return Swal.fire({ title: "Error", text: "Please assign a doctor first.", icon: "error" });
        }

        const origHtml = $btn.html();
        $btn.prop("disabled", true).html('<i class="fa-solid fa-spinner fa-spin"></i> Saving...');

        let formData = new FormData($("#consultation_form")[0]);
        formData.append("docrefno", docrefno);

        // Detailed Comment: Safely retrieve HMO text from selected option if patient type is HMO
        const isHmo = $("#patient_type").val() === "hmo";
        const hmoName = isHmo ? String($("#hmo_input option:selected").text() || "").trim() : "";
        formData.append("hmo_name", hmoName !== "Select HMO..." ? hmoName : "");

        const pxrefno = String($("#pxidno").text() || $("#pxidno").val() || "").trim();
        if (pxrefno) {
            formData.append("pxrefno", pxrefno);
        }

        const image = document.getElementById("patient_image");
        if (image && image.files.length > 0) formData.append("patient_photo", image.files[0]);

        $.ajax({
            url: "/api/save_patient_consultation",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    Swal.fire({ title: "Success", text: "Record successfully saved.", icon: "success", confirmButtonText: "Okay" })
                        .then((result) => {
                            if (result.isConfirmed) {
                                $("#consultation_form")[0].reset();
                                refreshQueue();
                                loadPatients();
                                loadSchedules(2);
                                $("#pxidno").val('').text('');
                                $("#pxconsultationrefno").text('');
                            }
                        });
                } else {
                    Swal.fire({ title: "Error", text: response.message || "Failed to save record.", icon: "error" });
                }
            },
            error: function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to save consultation.";
                Swal.fire({ title: "Error", text: msg, icon: "error" });
            },
            complete: function () {
                $btn.prop("disabled", false).html(origHtml);
            }
        });
    });

    // Detailed Comment: Update consultation details with defensive null-checks, record existence validation, and button loading state
    $(document).on("click", ".update_consultation_btn", function () {
        const $btn = $(this);
        const pxfname = String($("#pxfname").val() || "").trim();
        if (pxfname === "") {
            return Swal.fire({ title: "Validation Error", text: "Patient first name is required.", icon: "warning" });
        }
        const docrefno = String($("#doctor_for_consult").val() || "").trim();
        if (docrefno === "") {
            return Swal.fire({ title: "Error", text: "Please assign a doctor first.", icon: "error" });
        }

        let refno = String($("#pxconsultationrefno").text() || $("#pxconsultationrefno").val() || "").trim();
        if (!refno) {
            return Swal.fire({ title: "Error", text: "No existing consultation record selected for update.", icon: "warning" });
        }

        const origHtml = $btn.html();
        $btn.prop("disabled", true).html('<i class="fa-solid fa-spinner fa-spin"></i> Updating...');

        let formData = new FormData($("#consultation_form")[0]);
        formData.append("pxconsultationrefno", refno);
        formData.append("docrefno", docrefno);

        // Detailed Comment: Safely retrieve HMO text from selected option if patient type is HMO
        const isHmo = $("#patient_type").val() === "hmo";
        const hmoName = isHmo ? String($("#hmo_input option:selected").text() || "").trim() : "";
        formData.append("hmo_name", hmoName !== "Select HMO..." ? hmoName : "");

        const pxrefno = String($("#pxidno").text() || $("#pxidno").val() || "").trim();
        if (pxrefno) {
            formData.append("pxrefno", pxrefno);
        }

        const image = document.getElementById("patient_image");
        if (image && image.files.length > 0) formData.append("patient_photo", image.files[0]);

        $.ajax({
            url: "/api/update_patient_consultation",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    Swal.fire({ title: "Success", text: "Record successfully updated.", icon: "success", confirmButtonText: "Okay" })
                        .then((result) => {
                            if (result.isConfirmed) {
                                refreshQueue();
                                $("#consultation_form")[0].reset();
                                loadPatients();
                                loadSchedules(2);
                                $("#pxidno").val('').text('');
                                $("#pxconsultationrefno").text('');
                            }
                        });
                } else {
                    Swal.fire({ title: "Error", text: response.message || "Failed to update record.", icon: "error" });
                }
            },
            error: function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to update consultation.";
                Swal.fire({ title: "Error", text: msg, icon: "error" });
            },
            complete: function () {
                $btn.prop("disabled", false).html(origHtml);
            }
        });
    });

    // Detailed Comment: Queue table row status updater handler
    $(document).on("click", ".update-queue", function () {
        const refno = $(this).val();
        const allOptions = {
            'WAITING': 'Waiting',
            'IN_CONSULTATION': 'In Consultation',
            'COMPLETED': 'Completed',
            'CANCELLED': 'Cancelled',
            'NO_SHOW': 'No Show',
            'ON_HOLD': 'On Hold'
        };

        Swal.fire({
            title: "Update Queue Status",
            input: 'select',
            inputOptions: allOptions,
            inputValue: 'IN_CONSULTATION',
            showCancelButton: true,
            confirmButtonText: 'Update Status',
            confirmButtonColor: '#28a745',
            inputValidator: (value) => {
                if (!value) return 'Please select a status.';
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: "Updating Queue...",
                    text: "Please wait.",
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: "/api/update_queue_status",
                    type: "POST",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: {
                        consultationrefno: refno,
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
                                timer: 1500
                            });
                            refreshQueue();
                            loadPatients();
                        } else {
                            Swal.fire({ title: "Error", text: response.message || "Failed to update queue status.", icon: "error" });
                        }
                    },
                    error: function (xhr) {
                        const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to update queue status.";
                        Swal.fire({ title: "Error", text: msg, icon: "error" });
                    }
                });
            }
        });
    });

    // Detailed Comment: View masterlist modal trigger with button loading feedback
    $("#view_masterlist_btn").on("click", function () {
        const doctor = $("#doctor_id").val();
        if (!doctor) return Swal.fire({ title: "Doctor Required", text: "Please choose a doctor first.", icon: "warning" });

        const $btn = $(this);
        setBtnLoading($btn, "Loading...");

        new bootstrap.Modal("#patientMasterlistModal").show();
        $("#patientMasterlistTable").DataTable().destroy().clear();
        $("#patientMasterlistTable").DataTable({
            processing: true, serverSide: true,
            ajax: {
                url: "/api/fetch_patient_masterlist_sec", type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: { docrefno: doctor },
                complete: function () {
                    resetBtnLoading($btn);
                }
            },
            columns: [
                { data: null, render: function (data, type, row) { 
                    return `<div class="d-flex gap-1">
                        <button class="btn btn-sm btn-primary import-btn" value="${row.casecode || ''}" data-pxrefno="${row.pxrefno || ''}" data-consultationrefno="${row.consultationrefno || ''}"><i class="fa-solid fa-share"></i> Import</button>
                        <button class="btn btn-sm btn-secondary view-btn" value="${row.pincode || ''}" data-pxrefno="${row.pxrefno || ''}" data-consultationrefno="${row.consultationrefno || ''}"><i class="fa-solid fa-clock-rotate-left"></i> History</button>
                    </div>`; 
                } },
                { data: null, render: function (data, type, row) { return `<img src="${row.photo_path || '/images/blank_photo.png'}" alt="patient_photo" style="max-height: 80px; max-width: 80px; object-fit: cover;" class="rounded">`; } },
                { data: null, render: function (data, type, row) { return [row.pxlastname, row.pxfirstname, row.pxmidname, row.pxsuffix].filter(Boolean).join(', ').toUpperCase(); } },
                { data: 'mobilenumber' }, { data: 'emailaddress' }
            ],
            columnDefs: [{ targets: [0, 1], width: "1%", orderable: false, searchable: false, className: "text-nowrap text-center align-middle" }],
            language: { emptyTable: "No patient records yet." },
        });
    });

    // Detailed Comment: Import patient from masterlist modal with button loading indicator and multi-key fallback
    $(document).on("click", ".import-btn", function () {
        const $btn = $(this);
        setBtnLoading($btn, "Importing...");
        const casecode = $btn.val();
        const pxrefno = $btn.data('pxrefno');
        const consultationrefno = $btn.data('consultationrefno');

        $.ajax({
            url: "/api/fetch_consultation", type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { casecode: casecode, pxrefno: pxrefno, consultationrefno: consultationrefno },
            success: function (response) {
                if (response.success && response.patient) {
                    const p = response.patient;
                    $("#pincode").val(p.pincode || "");
                    $("#pxconsultationrefno").text(p.consultationrefno || "").val(p.consultationrefno || "");
                    // Detailed Comment: Set both text and val on pxidno span element so patient reference displays in UI and is readable
                    $("#pxidno").text(p.pxrefno || "").val(p.pxrefno || "");
                    $("#pxfname").val(p.pxfirstname || p.patientname || "");
                    $("#pxmname").val(p.pxmidname || "");
                    $("#pxlname").val(p.pxlastname || "");
                    $("#pxsuffix").val(p.pxsuffix || "");
                    $("#pxsex").val(p.gender || "male");
                    $("#pxbday").val(p.birthday || "");
                    $("#pxage").val(p.birthday ? getAge(p.birthday) : "");
                    $("#pxcellnumber").val(p.mobilenumber || "");
                    $("#pxemail").val(p.emailaddress || "");
                    $("#pxaddress").val(p.address || "");
                    $("#pxreasonforconsultation").val(p.reasonforconsultation || "");
                    $("#pxweight").val(p.weight || "");
                    $("#pxheight").val(p.height || "");
                    $("#pxtemp").val(p.temp || "");
                    $("#pxrespiratory").val(p.respiratoryrate || "");
                    $("#pxpulse").val(p.pulserate || "");
                    $("#pxbpnumerator").val(p.bpnumerator || "");
                    $("#pxbpdenominator").val(p.bpdenominator || "");
                    if (p.docrefno) $("#doctor_for_consult").val(p.docrefno);
                    if (p.consultation_date && p.consultation_date !== "1901-01-01 00:00:00") {
                        $("#sched_date").val(p.consultation_date.split(" ")[0]);
                    } else {
                        $("#sched_date").val("");
                    }
                    loadSchedules(2);
                    $("#patient_picture_preview").prop("src", p.photo_path || '/images/blank_photo.png');
                    if (response.answers && Array.isArray(response.answers)) {
                        response.answers.forEach(element => { $(`textarea[name="answer[${element.questionrefno}]"]`).val(element.answer); });
                    }
                    const masterlistModalEl = document.getElementById('patientMasterlistModal');
                    if (masterlistModalEl) {
                        const modalInstance = bootstrap.Modal.getInstance(masterlistModalEl);
                        if (modalInstance) modalInstance.hide();
                    }
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Patient data imported', showConfirmButton: false, timer: 1500 });
                } else {
                    Swal.fire({ title: "Error", text: (response && response.message) || "Failed to import patient details.", icon: "error" });
                }
            },
            error: function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to import patient details.";
                Swal.fire({ title: "Error", text: msg, icon: "error" });
            },
            complete: function () {
                resetBtnLoading($btn);
            }
        });
    });

    // Detailed Comment: Open Patient Consultation History from Patient Masterlist
    $(document).on('click', '.view-btn', function () {
        const pincode = $(this).val();
        const pxrefno = $(this).data('pxrefno') || '';
        const consultationrefno = $(this).data('consultationrefno') || '';

        const masterlistModalEl = document.getElementById("patientMasterlistModal");
        if (masterlistModalEl) {
            const masterlistModal = bootstrap.Modal.getInstance(masterlistModalEl);
            if (masterlistModal) masterlistModal.hide();
        }

        $("#mpincode").val(pincode);
        $("#patientMedhistoryModal").data('pxrefno', pxrefno);
        $("#patientMedhistoryModal").data('consultationrefno', consultationrefno);

        const medHistoryModal = new bootstrap.Modal(document.getElementById("patientMedhistoryModal"));
        medHistoryModal.show();
    });

    // Detailed Comment: Populate Patient Medical History DataTable when modal opens
    $("#patientMedhistoryModal").on("show.bs.modal", function () {
        const pincode = $("#mpincode").val();
        const pxrefno = $(this).data('pxrefno') || '';
        const consultationrefno = $(this).data('consultationrefno') || '';

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
                        return `<img src="${data || '/images/blank_photo.png'}" style="height: 60px; max-width: 60px; object-fit: cover;" class="rounded" alt="patient_photo">`;
                    }
                },
                {
                    data: 'consultation_date',
                    render: function (data) {
                        if (!data) return '<span class="text-muted">N/A</span>';
                        try {
                            return new Date(data).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
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
                            return new Date(data).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
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
    });

    // Detailed Comment: Settlements lifecycle management for secretary queue:
    // Computes patient charge totals, loads HMO choices, manages dynamic payment dispersion,
    // and synchronizes both Generate Settlements and View Settlements tabs.
    let totalSettlementAmount = 0.00;

    function calculateSettlementRemaining() {
        let used = 0;
        $("#settlement_form .settlement-input").each(function () {
            used += parseFloat($(this).val()) || 0;
        });
        used = Math.round(used * 100) / 100;
        let rem = totalSettlementAmount - used;
        if (rem < 0) rem = 0;
        return Math.round(rem * 100) / 100;
    }

    function updateSettlementRemaining() {
        const rem = calculateSettlementRemaining();
        $("#remaining").text(rem.toFixed(2));
    }

    function populateViewSettlements(r) {
        if (!r) return;
        const cardMap = { 'cc': 'Credit Card', 'dc': 'Debit Card' };
        const cardLabel = cardMap[r.cta_type] || r.cta_type || 'None';

        $("#info_total").val(r.net_total ? '₱' + parseFloat(r.net_total).toFixed(2) : '₱0.00');
        $("#info_cash").val(r.cash ? '₱' + parseFloat(r.cash).toFixed(2) : '₱0.00');
        $("#info_cta").val(r.cta ? '₱' + parseFloat(r.cta).toFixed(2) : '₱0.00');
        $("#info_cta_type").val(cardLabel);
        $("#info_hmo").val(r.hmo ? '₱' + parseFloat(r.hmo).toFixed(2) : '₱0.00');
        $("#info_phic").val(r.phic ? '₱' + parseFloat(r.phic).toFixed(2) : '₱0.00');

        let hmoLabel = r.hmo_type || 'None';
        const hmoOption = $(`#hmo_type option[value="${r.hmo_type}"]`).text();
        if (hmoOption && hmoOption !== '-- Select HMO --') {
            hmoLabel = hmoOption;
        }
        $("#info_hmo_type").val(hmoLabel);
    }

    $("#settlement_btn").on("click", function () {
        const refno = String($("#pxconsultationrefno").text() || $("#pxconsultationrefno").val() || "").trim();
        if (!refno) {
            return Swal.fire({
                title: "Reminder",
                text: "Please select a queued consultation record first.",
                icon: "warning"
            });
        }

        const $btn = $(this);
        setBtnLoading($btn, "Loading...");

        // Reset form & set consultation refno
        $("#settlement_form")[0].reset();
        $("#sett_consultationrefno").val(refno);

        // Fetch and populate HMO options
        $.ajax({
            url: "/api/fetch_hmo",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            success: function (response) {
                const $select = $("#hmo_type");
                $select.empty().append('<option value="" selected disabled>-- Select HMO --</option>');
                if (response.hmo && response.hmo.length > 0) {
                    response.hmo.forEach(h => {
                        $select.append(`<option value="${h.hmocode}">${h.hmoname}</option>`);
                    });
                }
            }
        });

        // Fetch patient charges to calculate total and remaining balances
        $.ajax({
            url: "/api/fetch_pxcharges",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { consultationrefno: refno },
            success: function (resCharges) {
                let total = 0;
                if (resCharges.charges && resCharges.charges.length > 0) {
                    resCharges.charges.forEach(c => total += parseFloat(c.totalamt || 0));
                }
                totalSettlementAmount = Math.round(total * 100) / 100;
                $("#total_amount").text(totalSettlementAmount.toFixed(2));
                $("#remaining").text(totalSettlementAmount.toFixed(2));
                $("#total").val(totalSettlementAmount.toFixed(2));

                // Fetch any existing saved settlement to populate form and view tab
                $.ajax({
                    url: "/api/fetch_settlements",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: { consultationrefno: refno },
                    success: function (resSett) {
                        if (resSett.success && resSett.record) {
                            const r = resSett.record;
                            if (parseFloat(r.cash) > 0) $("#cash").val(parseFloat(r.cash).toFixed(2));
                            if (parseFloat(r.cta) > 0) $("#cta").val(parseFloat(r.cta).toFixed(2));
                            if (r.cta_type) $("#card_type").val(r.cta_type);
                            if (parseFloat(r.hmo) > 0) $("#hmo").val(parseFloat(r.hmo).toFixed(2));
                            if (r.hmo_type) $("#hmo_type").val(r.hmo_type);
                            if (parseFloat(r.phic) > 0) $("#phic").val(parseFloat(r.phic).toFixed(2));
                            updateSettlementRemaining();
                            populateViewSettlements(r);
                        } else {
                            $("#info_total").val('₱' + totalSettlementAmount.toFixed(2));
                            $("#info_cash").val('₱0.00');
                            $("#info_cta").val('₱0.00');
                            $("#info_cta_type").val('None');
                            $("#info_hmo").val('₱0.00');
                            $("#info_phic").val('₱0.00');
                            $("#info_hmo_type").val('None');
                        }
                    }
                });
            },
            complete: function () {
                resetBtnLoading($btn);
            }
        });
    });

    // Import total remaining amount into target settlement channel
    $(document).on("click", ".import-total", function () {
        const input = $(this).closest(".input-group").find(".settlement-input");
        const remaining = calculateSettlementRemaining();
        input.val(remaining.toFixed(2)).trigger("input");
    });

    // Dynamic remainder calculation and dispersion bounding
    $(document).on("input", ".settlement-input", function () {
        const currentInput = $(this);
        let value = parseFloat(currentInput.val()) || 0;
        if (value < 0) value = 0;

        let usedExceptCurrent = 0;
        $("#settlement_form .settlement-input").not(currentInput).each(function () {
            usedExceptCurrent += parseFloat($(this).val()) || 0;
        });

        const maxAllowed = Math.max(0, totalSettlementAmount - usedExceptCurrent);
        if (value > maxAllowed) {
            value = maxAllowed;
        }
        value = Math.round(value * 100) / 100;
        currentInput.val(value > 0 ? value.toFixed(2) : "");
        updateSettlementRemaining();
    });

    // Save settlements with dispersion validation and feedback
    $("#save_settlements").on("click", function () {
        const refno = $("#sett_consultationrefno").val();
        if (!refno) {
            return Swal.fire({ title: "Error", text: "No consultation selected.", icon: "error" });
        }

        const remaining = calculateSettlementRemaining();
        if (remaining > 0) {
            return Swal.fire({
                title: "Reminder",
                text: `Charges not fully dispersed. Remaining balance: ₱${remaining.toFixed(2)}`,
                icon: "warning"
            });
        }

        Swal.fire({
            title: "Confirm Settlement",
            text: "Save and finalize settlements for this consultation?",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Confirm",
            cancelButtonText: "Cancel"
        }).then((result) => {
            if (result.isConfirmed) {
                const $btn = $("#save_settlements");
                setBtnLoading($btn, "Saving...");

                let formData = $("#settlement_form").serialize();
                formData += "&total=" + encodeURIComponent($("#total_amount").text());

                $.ajax({
                    url: "/api/save_settlements",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: formData,
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Settlement details saved successfully!',
                                showConfirmButton: false,
                                timer: 2000
                            });
                            $("#view_sett_btn").trigger("click");
                        } else {
                            Swal.fire({
                                title: "Error",
                                text: "Failed to save settlements.",
                                icon: "error"
                            });
                        }
                    },
                    complete: function () {
                        resetBtnLoading($btn);
                    }
                });
            }
        });
    });

    // View settlements tab switch handler
    $("#view_sett_btn").on("click", function () {
        const refno = String($("#pxconsultationrefno").text() || $("#pxconsultationrefno").val() || "").trim();
        if (!refno) return;

        $.ajax({
            url: "/api/fetch_settlements",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { consultationrefno: refno },
            success: function (response) {
                if (response.success && response.record) {
                    populateViewSettlements(response.record);
                } else {
                    $("#info_total").val('₱' + totalSettlementAmount.toFixed(2));
                    $("#info_cash").val('₱0.00');
                    $("#info_cta").val('₱0.00');
                    $("#info_cta_type").val('None');
                    $("#info_hmo").val('₱0.00');
                    $("#info_phic").val('₱0.00');
                    $("#info_hmo_type").val('None');
                }
            }
        });
    });

    // Detailed Comment: Guard secretary printable document buttons to verify a consultation record is selected
    $(document).on("click", "#sec_print_rx_btn, #sec_print_diag_btn, #sec_print_admit_btn, #sec_print_soa_btn", function (e) {
        const href = $(this).attr("href");
        if (!href || href === "#") {
            e.preventDefault();
            Swal.fire({
                title: "Reminder",
                text: "Please select a queued consultation record first to generate printable documents.",
                icon: "warning"
            });
        }
    });

    // Detailed Comment: Remove patient charge handler with button spinner feedback
    // Uses /api/delete_patient_charge to synchronize with DoctorController::deleteCharge
    $(document).on("click", ".remove_charge", function () {
        const $btn = $(this);
        const prodcode = $btn.val();
        const refno = $("#pxconsultationrefno").text();
        if (!refno || !prodcode) return;

        Swal.fire({
            title: "Delete Charge?",
            text: "Are you sure you want to remove this charge item?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete",
            confirmButtonColor: "#d33"
        }).then((result) => {
            if (result.isConfirmed) {
                setBtnLoading($btn, "");
                $.ajax({
                    url: "/api/delete_patient_charge",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: { consultationrefno: refno, prodcode: prodcode },
                    success: function (response) {
                        if (response.success) {
                            loadPatientCharges();
                        } else {
                            Swal.fire({ title: "Error", text: response.message || "Failed to remove charge.", icon: "error" });
                        }
                    },
                    error: function () {
                        Swal.fire({ title: "Error", text: "Failed to remove charge.", icon: "error" });
                    },
                    complete: function () {
                        resetBtnLoading($btn);
                    }
                });
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

    // Detailed Comment: Mark as Complete handler from consultation card footer with validation, confirmation, and button loader
    $(document).on("click", "#mark_as_complete_btn", function () {
        const refno = String($("#pxconsultationrefno").text() || $("#pxconsultationrefno").val() || "").trim();
        if (!refno) {
            return Swal.fire({
                title: "No Patient Selected",
                text: "Please select or import a patient consultation before marking as complete.",
                icon: "warning"
            });
        }

        Swal.fire({
            title: "Mark Consultation as Complete?",
            text: "This will update the status of this consultation to COMPLETED.",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Yes, complete",
            confirmButtonColor: "#28a745"
        }).then((result) => {
            if (result.isConfirmed) {
                const $btn = $("#mark_as_complete_btn");
                setBtnLoading($btn, "Completing...");

                $.ajax({
                    url: "/api/update_queue_status",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: {
                        consultationrefno: refno,
                        status: "COMPLETED"
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                toast: true,
                                position: "top-end",
                                icon: "success",
                                title: "Consultation marked as complete!",
                                showConfirmButton: false,
                                timer: 1500
                            });
                            refreshQueue();
                            loadPatients();
                        } else {
                            Swal.fire({ title: "Error", text: response.message || "Failed to mark consultation as complete.", icon: "error" });
                        }
                    },
                    error: function (xhr) {
                        const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to mark consultation as complete.";
                        Swal.fire({ title: "Error", text: msg, icon: "error" });
                    },
                    complete: function () {
                        resetBtnLoading($btn);
                    }
                });
            }
        });
    });
});
