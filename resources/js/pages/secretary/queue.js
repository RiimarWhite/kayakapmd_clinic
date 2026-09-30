import { initAddressCascade } from '../../helpers/address-cascade.js';
// Detailed Comment: Import reusable admin elevation helper for credential protection on fees, queue deletion, and patient deletion
import { requireAdminAuth } from '../../helpers/admin_auth.js';

$(function () {
    /**
     * Detailed Comment: Initialize PSGC address cascade for patient registration in secretary console
     */
    let queuePatientAddressCascade = null;
    if ($("#add_patient_modal").length) {
        queuePatientAddressCascade = initAddressCascade({
            regionSel: '#region',
            provSel: '#province',
            munSel: '#muncity',
            brgySel: '#brgy',
            zipInput: '#zipcode',
            streetInput: '#streetadrs',
            fullAddressInput: '#address'
        });
    }

    /**
     * Detailed Comment: Helper functions to toggle button loading spinners and disabled state.
     * Stores original button HTML in data attribute and restores upon operation completion.
     * Prevents overwriting already active spinners if called consecutively.
     */
    function setBtnLoading($btn, loadingText = "") {
        if (!$btn || $btn.length === 0) return;
        if (!$btn.data('original-html')) {
            $btn.data('original-html', $btn.html());
        }
        $btn.prop('disabled', true);
        const text = loadingText ? ` ${loadingText}` : '';
        $btn.html(`<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>${text}`);
    }

    function resetBtnLoading($btn) {
        if (!$btn || $btn.length === 0) return;
        const originalHtml = $btn.data('original-html');
        if (originalHtml) {
            $btn.html(originalHtml);
            $btn.removeData('original-html');
        }
        $btn.prop('disabled', false);
    }

    const todayStr = new Date().toISOString().split('T')[0];
    if (!$("#queuedate").val()) $("#queuedate").val(todayStr);
    if (!$("#sched_date").val()) $("#sched_date").val(todayStr);

    loadPatients();
    loadSchedules(1);
    loadSchedules(2);
    updateDoctorQueueBadges();

    /**
     * Detailed Comment: Fetches real-time patient queue counts grouped by assigned doctor
     * and decorates each doctor option in #doctor_id with the live waiting queue count badge.
     */
    function updateDoctorQueueBadges() {
        const queueDate = $("#queuedate").val() || new Date().toISOString().split('T')[0];
        $.ajax({
            url: "/api/fetch_doctors_queue_counts",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { date: queueDate },
            success: function (response) {
                if (response && response.counts) {
                    $("#doctor_id option").each(function () {
                        const docref = $(this).val();
                        if (!docref) return;
                        let origText = $(this).data("orig-text");
                        if (!origText) {
                            origText = $(this).text().replace(/\s*\(\d+\s*queued\)/i, '').trim();
                            $(this).data("orig-text", origText);
                        }
                        const count = response.counts[docref] || 0;
                        $(this).text(`${origText} (${count} queued)`);
                    });
                }
            }
        });
    }

    function loadPatients() {
        loadPatientTable();
        // Detailed Comment: Load Patient Masterlist records into the new Patient Masterlist card
        loadPatientMasterlistQueueTable();
        loadUnschedTable();
        updateDoctorQueueBadges();

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

    /**
     * Detailed Comment: Loads patient queue records into the Patient Queue table.
     * Features:
     * 1. Show Info button with eye icon preserving existing form-populating behaviour via .import-queue
     * 2. Merged action dropdown grouping Change Status, Reschedule, and Admin-Protected Delete into one button
     */
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
                        <div class="d-flex gap-1 justify-content-center align-items-center">
                            <!-- Show Info button: preserves .import-queue class and existing behavior with eye icon -->
                            <button class="btn btn-sm btn-primary import-queue" value="${data.pxrefno || ''}" data-pxrefno="${data.pxrefno || ''}" data-consultationrefno="${data.consultationrefno || ''}" title="Show Info"><i class="fa-solid fa-eye"></i></button>
                            
                            <!-- Merged Action Dropdown: Change Status, Reschedule, and Admin-Protected Delete -->
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Actions">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li>
                                        <button class="dropdown-item update-queue text-success" type="button" value="${data.consultationrefno}">
                                            <i class="fa-solid fa-check me-2"></i> Change Status
                                        </button>
                                    </li>
                                    <li>
                                        <button class="dropdown-item reschedule text-primary" type="button" value="${data.consultationrefno}">
                                            <i class="fa-solid fa-calendar-days me-2"></i> Reschedule
                                        </button>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <button class="dropdown-item delete-queue-item text-danger" type="button" value="${data.consultationrefno}">
                                            <i class="fa-solid fa-trash me-2"></i> Delete
                                        </button>
                                    </li>
                                </ul>
                            </div>
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
                { target: 1, width: "1%", className: "text-nowrap justify-items-center text-center align-middle" },
                { target: 2, className: "text-nowrap text-truncate align-middle overflow-hidden" },
                { target: 3, width: "1%", className: "text-nowrap text-center align-middle" }
            ],
            language: { emptyTable: "No patients record yet." },
            pageLength: 10, lengthChange: false, paging: true, ordering: false,
        });
    }

    /**
     * Detailed Comment: Loads patient masterlist records into the Queue page left card.
     * Displays Action column (consultation history, import, edit/delete dropdown) and Patient Name.
     */
    function loadPatientMasterlistQueueTable() {
        const table = $("#patient_masterlist_queue_table");
        if (!table.length) return;

        if ($.fn.DataTable.isDataTable(table)) {
            table.DataTable().clear().destroy();
        }

        table.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "/api/fetch_queue_patient_masterlist",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") }
            },
            columns: [
                {
                    data: null,
                    render: function (data) {
                        return `
                            <div class="d-flex gap-1 justify-content-center align-items-center">
                                <button class="btn btn-sm btn-info text-white btn_px_history" value="${data.pxrefno || ''}" data-pxrefno="${data.pxrefno || ''}" data-pincode="${data.pincode || ''}" data-name="${(data.formatted_name || data.patientname || '').replace(/"/g, '&quot;')}" title="Consultation History">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </button>
                                <button class="btn btn-sm btn-primary btn_px_import" value="${data.pxrefno || ''}" data-pxrefno="${data.pxrefno || ''}" data-pincode="${data.pincode || ''}" title="Import Patient">
                                    <i class="fa-solid fa-file-import"></i>
                                </button>
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="More Actions">
                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <button class="dropdown-item btn_px_edit text-primary" type="button" value="${data.pxrefno || ''}" data-pxrefno="${data.pxrefno || ''}" data-pincode="${data.pincode || ''}">
                                                <i class="fa-solid fa-pen-to-square me-2"></i> Edit
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <button class="dropdown-item btn_px_delete text-danger" type="button" value="${data.pxrefno || ''}" data-pxrefno="${data.pxrefno || ''}" data-pincode="${data.pincode || ''}" data-name="${(data.formatted_name || data.patientname || '').replace(/"/g, '&quot;')}">
                                                <i class="fa-solid fa-trash me-2"></i> Delete
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        `;
                    }
                },
                {
                    data: "formatted_name",
                    render: function (d, type, row) {
                        const name = d || row.patientname || 'N/A';
                        return `
                            <div class="fw-bold text-dark text-truncate" style="max-width: 200px;" title="${name}">${name}</div>
                            <small class="text-muted">PIN: ${row.pincode || 'N/A'}</small>
                        `;
                    }
                }
            ],
            columnDefs: [
                { targets: 0, width: "1%", orderable: false, searchable: false, className: "text-nowrap text-center align-middle" },
                { targets: 1, className: "align-middle" }
            ],
            language: { emptyTable: "No patient masterlist records found." },
            pageLength: 10,
            lengthChange: false,
            info: true,
            paging: true,
            searching: true,
            ordering: false
        });
    }

    function loadUnschedTable() {
        const table = $("#patients_unsched_table");
        if (!table.length) return;
        if ($.fn.DataTable.isDataTable(table)) table.DataTable().clear().destroy();
        table.DataTable({
            processing: true, serverSide: true,
            ajax: {
                url: "/api/fetch_unscheduled_patients", type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: { consuldate: $("#queuedate").val(), consultime: $("#stime").val(), docrefno: $("#doctor_id").val() }
            },
            columns: [
                { data: null, render: function (data) { return `<button class="btn btn-sm btn-primary import-queue" value="${data.pxrefno}" data-consultationrefno="${data.consultationrefno || ''}" title="Show Info"><i class="fa-solid fa-eye"></i></button>`; } },
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
                if (index === 1) {
                    $(cfg.timeEl).trigger("change");
                }
                // Detailed Comment: Match sched_time with stime if available in options
                if (index === 2 && $("#stime").val()) {
                    if ($(`#sched_time option[value="${$("#stime").val()}"]`).length > 0) {
                        $("#sched_time").val($("#stime").val());
                    }
                }
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

    // Detailed Comment: Event bindings with doctor queue synchronization and doctor count badges
    $("#doctor_id").on("change", function () {
        const selectedDoc = $(this).val();
        if (selectedDoc) {
            $("#doctor_for_consult").val(selectedDoc);
        }
        loadPatients();
        loadSchedules(1);
        loadSchedules(2);
    });
    $("#doctor_for_consult").on("change", function () { loadSchedules(2); });
    $("#queuedate").on("change", function () {
        // Detailed Comment: Default "Update or assign new consultation date" to match "Enter consultation date"
        $("#sched_date").val($("#queuedate").val());
        loadPatientTable();
        loadSchedules(1);
        loadSchedules(2);
        updateDoctorQueueBadges();
    });
    $("#stime").on("change", function () { 
        // Detailed Comment: Match the schedule time set with the selected consultation schedule
        if ($("#stime").val()) {
            $("#sched_time").val($("#stime").val());
        }
        loadPatientTable(); 
    });
    $("#sched_date").on("change", function () { loadSchedules(2); });
    $("#resched_date").on("change", function () { loadSchedules(3); });

    $("#sprevious").on("click", function () {
        const $btn = $(this);
        setBtnLoading($btn, "");
        let d = new Date($("#queuedate").val()); d.setDate(d.getDate() - 1);
        const newDateStr = d.toISOString().split("T")[0];
        $("#queuedate").val(newDateStr);
        // Detailed Comment: Default sched_date on previous date navigation
        $("#sched_date").val(newDateStr);
        loadPatientTable();
        loadSchedules(1);
        loadSchedules(2);
        updateDoctorQueueBadges();
        setTimeout(() => resetBtnLoading($btn), 400);
    });

    $("#snext").on("click", function () {
        const $btn = $(this);
        setBtnLoading($btn, "");
        let d = new Date($("#queuedate").val()); d.setDate(d.getDate() + 1);
        const newDateStr = d.toISOString().split("T")[0];
        $("#queuedate").val(newDateStr);
        // Detailed Comment: Default sched_date on next date navigation
        $("#sched_date").val(newDateStr);
        loadPatientTable();
        loadSchedules(1);
        loadSchedules(2);
        updateDoctorQueueBadges();
        setTimeout(() => resetBtnLoading($btn), 400);
    });

    $("#add_new_patient_btn").on("click", function () { new bootstrap.Modal("#add_patient_modal").show(); });

    $("#clear_form").on("click", function () {
        $("#consultation_form")[0].reset();
        $("#pxconsultationrefno").text("").val("");
        $("#pxidno").text("").val("");
        $("#photo_path").val("");
        $("#photo_base64").val("");
        $("#patient_picture_preview").prop("src", "/images/blank_photo.png");
        $("#sec_medhistory_table tbody").html('<tr><td colspan="6" class="text-center text-muted py-3">No patient consultation history loaded yet. Import or select a patient to view medical history.</td></tr>');
        $("#sec_medhistory_count").text('0 records');
    });

    /**
     * Detailed Comment: Patient Photo Upload and Webcam capture integration.
     * Allows uploading an image file from disk with FileReader preview,
     * or capturing real-time camera frames via WebRTC MediaDevices into canvas.
     */
    $("#upload_patient_image").on("click", function () {
        $("#patient_image").trigger("click");
    });

    $("#patient_image").on("change", function () {
        const file = this.files && this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                $("#patient_picture_preview").prop("src", e.target.result);
                $("#photo_base64").val(e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    let cameraStream = null;

    $("#take_photo").on("click", function () {
        const modalEl = document.getElementById("takePhotoModal");
        if (!modalEl) return;
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function (stream) {
                    cameraStream = stream;
                    const video = document.getElementById("camera_preview");
                    if (video) {
                        video.srcObject = stream;
                        video.play();
                    }
                })
                .catch(function (err) {
                    console.error("Camera access error:", err);
                    Swal.fire({
                        title: "Camera Access Error",
                        text: "Could not access camera. Please check device permissions.",
                        icon: "error"
                    });
                });
        }
    });

    function stopCameraStream() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
            cameraStream = null;
        }
    }

    $("#takePhotoModal").on("hidden.bs.modal", function () {
        stopCameraStream();
    });

    $("#save_photo").on("click", function () {
        const video = document.getElementById("camera_preview");
        const canvas = document.getElementById("camera_canvas");
        if (!video || !canvas) return;

        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;
        const ctx = canvas.getContext("2d");
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        const dataUrl = canvas.toDataURL("image/png");

        $("#patient_picture_preview").prop("src", dataUrl);
        $("#photo_base64").val(dataUrl);

        stopCameraStream();
        const modalInstance = bootstrap.Modal.getInstance(document.getElementById("takePhotoModal"));
        if (modalInstance) modalInstance.hide();

        Swal.fire({
            toast: true,
            position: "top-end",
            icon: "success",
            title: "Photo captured!",
            showConfirmButton: false,
            timer: 1500
        });
    });

    /**
     * Detailed Comment: Loads medical and consultation history into the Secretary Medical History tab.
     * Queries /api/fetch_patient_medhistory using multi-key lookup (pincode, pxrefno, consultationrefno).
     */
    function loadSecretaryMedhistory(pincode, pxrefno, consultationrefno) {
        const $tbody = $("#sec_medhistory_table tbody");
        $tbody.html('<tr><td colspan="6" class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Loading medical history...</td></tr>');

        $.ajax({
            url: "/api/fetch_patient_medhistory",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
                pincode: pincode,
                pxrefno: pxrefno,
                consultationrefno: consultationrefno
            },
            success: function (res) {
                $tbody.empty();
                const history = res.medhistory || [];
                $("#sec_medhistory_count").text(`${history.length} records`);

                if (history.length === 0) {
                    $tbody.append('<tr><td colspan="6" class="text-center text-muted py-3">No consultation history records found for this patient.</td></tr>');
                    return;
                }

                const badges = {
                    WAITING: "bg-warning text-white",
                    IN_CONSULTATION: "bg-info text-white",
                    FOR_BILLING: "bg-primary text-white",
                    COMPLETED: "bg-success text-white",
                    UNSCHEDULED: "bg-secondary text-white",
                    CANCELLED: "bg-danger text-white",
                    NO_SHOW: "bg-danger text-white"
                };

                history.forEach(item => {
                    const photo = item.photo_path || '/images/blank_photo.png';
                    const dateStr = item.consultation_date ? item.consultation_date.substring(0, 16) : 'N/A';
                    const reason = item.reasonforconsultation || 'No chief complaint recorded.';
                    const status = item.status || 'N/A';
                    const recordedby = item.recordedby || 'N/A';
                    const recordeddate = item.recordeddate ? item.recordeddate.substring(0, 10) : 'N/A';
                    const badgeClass = badges[status] || "bg-secondary text-white";

                    $tbody.append(`
                        <tr>
                            <td class="text-center">
                                <img src="${photo}" alt="patient" class="rounded border" style="width: 36px; height: 36px; object-fit: cover;" onerror="this.src='/images/blank_photo.png'">
                            </td>
                            <td class="fw-semibold text-nowrap">${dateStr}</td>
                            <td>${reason}</td>
                            <td><span class="badge ${badgeClass}">${status}</span></td>
                            <td class="text-nowrap">${recordedby}</td>
                            <td class="text-nowrap">${recordeddate}</td>
                        </tr>
                    `);
                });
            },
            error: function () {
                $tbody.html('<tr><td colspan="6" class="text-center text-danger py-3">Failed to load medical history.</td></tr>');
                $("#sec_medhistory_count").text('0 records');
            }
        });
    }

    $("#patient_medhistory_tab_btn").on("click", function () {
        const pxrefno = String($("#pxidno").text() || $("#pxidno").val() || "").trim();
        const pincode = String($("#pincode").val() || "").trim();
        const cref = String($("#pxconsultationrefno").text() || $("#pxconsultationrefno").val() || "").trim();
        if (pxrefno || pincode || cref) {
            loadSecretaryMedhistory(pincode, pxrefno, cref);
        }
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

    // Detailed Comment: Import patient queue record with button loading state, tab activation, and multi-key fallback
    $(document).on("click", ".import-queue", function () {
        const $btn = $(this);
        setBtnLoading($btn, "");
        const pxrefno = $btn.val() || $btn.data("pxrefno") || "";
        const consultationrefno = $btn.data("consultationrefno") || "";

        // Detailed Comment: Automatically switch to Consultation Details tab when Show Info is clicked
        const consulTabBtn = document.querySelector('button[data-bs-target="#consul_info"]');
        if (consulTabBtn) {
            bootstrap.Tab.getInstance(consulTabBtn)?.show() || new bootstrap.Tab(consulTabBtn).show();
        }

        $.ajax({
            url: "/api/fetch_consultation", type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { pxrefno: pxrefno, consultationrefno: consultationrefno },
            success: function (response) {
                if (response.success && response.patient) {
                    const p = response.patient;

                    $("#pincode").val(p.pincode || "");
                    $("#hidden_consultationrefno").val(p.consultationrefno || "");
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
                    $("#pxlandlinenumber").val(p.landlinenumber || "");
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
                        // Detailed Comment: Default "Update or assign new consultation date" to match current queue date
                        $("#sched_date").val($("#queuedate").val() || todayStr);
                    }
                    loadSchedules(2);
                    $("#patient_picture_preview").prop("src", p.photo_path ?? '/images/blank_photo.png');
                    $("#photo_path").val(p.photo_path || "");
                    $("#photo_base64").val("");

                    // Detailed Comment: Safely load patient's medical and consultation history into the Medical History tab
                    try {
                        loadSecretaryMedhistory(p.pincode, p.pxrefno, p.consultationrefno);
                    } catch (err) {
                        console.warn("Error loading secretary medhistory:", err);
                    }

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

                    // Detailed Comment: Safely refresh charges table and payment history
                    try {
                        loadPatientCharges();
                    } catch (err) {
                        console.warn("Error loading patient charges:", err);
                    }
                    try {
                        loadPatientPaymentHistory(p.pincode, p.pxrefno, p.consultationrefno);
                    } catch (err) {
                        console.warn("Error loading patient payment history:", err);
                    }
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

    /**
     * Detailed Comment: Load and display patient charges for the selected consultation.
     * Safely checks and destroys any existing DataTable instance before re-initializing.
     * Handles missing consultation reference gracefully by resetting total to 0.00 and clearing records.
     */
    function loadPatientCharges() {
        const refno = String($("#pxconsultationrefno").text() || $("#pxconsultationrefno").val() || "").trim();
        const consulDate = $("#sched_date").val() || $("#queuedate").val() || todayStr;
        $("#pay_tab_consultdate_badge").text(`Date: ${consulDate}`);
        $("#pay_tab_consultref_badge").text(`Ref: ${refno || 'None'}`);

        // Detailed Comment: Safely destroy existing DataTable instance in DataTables 2 (clear then destroy)
        if ($.fn.DataTable.isDataTable("#pxcharges_table")) {
            $("#pxcharges_table").DataTable().clear().destroy();
        }
        $("#pxcharges_table tbody").empty();

        if (!refno) {
            $("#charges_total").text("0.00");
            $("#pxcharges_table").DataTable({
                data: [],
                columns: [
                    { data: null, defaultContent: "" },
                    { data: 'item_dscr', defaultContent: "" },
                    { data: 'qty', defaultContent: "" },
                    { data: 'totalamt', defaultContent: "" }
                ],
                columnDefs: [
                    { target: 0, width: '1%', orderable: false, className: 'text-nowrap text-center align-middle' },
                    { target: '_all', orderable: false, className: 'text-nowrap align-middle' }
                ],
                layout: {
                    bottomStart: 'paging',
                    bottomEnd: null
                },
                searching: false, lengthChange: false, pageLength: 5, paging: true, order: [[1, 'asc']]
            });
            return;
        }

        $("#pxcharges_table").DataTable({
            ajax: {
                url: "/api/fetch_pxcharges", type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: { consultationrefno: refno },
                dataSrc: function (response) {
                    let total = 0;
                    if (response.charges && Array.isArray(response.charges)) {
                        response.charges.forEach(c => total += parseFloat(c.totalamt || 0));
                    }
                    $("#charges_total").text(total.toFixed(2));
                    return response.charges || [];
                }
            },
            columns: [
                {
                    data: null,
                    render: function (data) {
                        const chargeId = data.id || data.pxchargerefno || '';
                        const itemDscr = (data.item_dscr || '').replace(/"/g, '&quot;');
                        const unitPrice = parseFloat(data.sellingprice || (parseFloat(data.totalamt || 0) / Math.max(1, parseFloat(data.qty || 1)))).toFixed(2);
                        const qty = data.qty || 1;
                        const totalAmt = parseFloat(data.totalamt || 0).toFixed(2);
                        return `
                            <div class="d-flex gap-1 justify-content-center">
                                <button class="btn btn-sm btn-outline-primary edit_charge_btn_sc" type="button"
                                    data-id="${chargeId}"
                                    data-prodcode="${data.prodcode || ''}"
                                    data-item="${itemDscr}"
                                    data-price="${unitPrice}"
                                    data-qty="${qty}"
                                    data-total="${totalAmt}"
                                    title="Edit Fee (Admin Protected)">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger remove_charge" type="button"
                                    value="${data.prodcode}"
                                    data-id="${chargeId}"
                                    data-item="${itemDscr}"
                                    title="Delete Fee (Admin Protected)">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        `;
                    }
                },
                { data: 'item_dscr', defaultContent: '' },
                { data: 'qty', defaultContent: '' },
                { data: 'totalamt', defaultContent: '' }
            ],
            columnDefs: [
                { target: 0, width: '1%', orderable: false, className: 'text-nowrap text-center align-middle' },
                { target: '_all', orderable: false, className: 'text-nowrap align-middle' }
            ],
            // Detailed Comment: In DataTables 2, layout uses standard feature keys to avoid "Unknown feature: html" warning.
            layout: {
                bottomStart: 'paging',
                bottomEnd: null
            },
            searching: false, lengthChange: false, pageLength: 5, paging: true, order: [[1, 'asc']]
        });
    }

    /**
     * Detailed Comment: Appended Charges logic for Secretary Queue and Admin Secretary console.
     * Manages client-side queue of charges, Select2 searching with category filtering,
     * HMO/tier price auto-population, and batch submission to /api/save_patient_charges.
     */
    let secPendingCharges = [];

    function renderSecPendingCharges() {
        const $tbody = $("#sec_appended_charges_table tbody");
        $tbody.empty();

        if (secPendingCharges.length === 0) {
            $tbody.append(`
                <tr class="no-charges-placeholder">
                    <td class="align-middle text-center text-muted" colspan="5">No pending charges appended yet.</td>
                </tr>
            `);
            return;
        }

        secPendingCharges.forEach((charge, index) => {
            const total = (parseFloat(charge.unit_price) * parseFloat(charge.qty)).toFixed(2);
            $tbody.append(`
                <tr data-index="${index}">
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-sm btn-danger sec-remove-pending-charge" data-index="${index}">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                    <td class="align-middle fw-semibold">${charge.item_dscr}</td>
                    <td class="text-center align-middle">${charge.qty}</td>
                    <td class="text-end align-middle">PHP ${parseFloat(charge.unit_price).toFixed(2)}</td>
                    <td class="text-end align-middle fw-bold">PHP ${total}</td>
                </tr>
            `);
        });
    }

    // Detailed Comment: Open Append Charges Modal with active consultation validation
    $("#append_pxcharges_btn").on("click", function () {
        const refno = String($("#pxconsultationrefno").text() || $("#pxconsultationrefno").val() || "").trim();
        if (!refno) {
            return Swal.fire({
                title: "Reminder",
                text: "Please select or import a queued consultation record first to append charges.",
                icon: "warning"
            });
        }

        secPendingCharges = [];
        renderSecPendingCharges();
        $("#sec_charge_qty").val(1);
        $("#sec_charge_amount").val("");

        initSecSearchCharge(refno);

        const modal = new bootstrap.Modal(document.getElementById("append_charge_modal"));
        modal.show();
    });

    function initSecSearchCharge(consultationRefno) {
        const $select = $("#sec_search_charge");
        if ($select.hasClass("select2-hidden-accessible")) {
            $select.select2("destroy");
        }

        $select.empty().append('<option value="" selected disabled>Type to search charge...</option>');

        $select.select2({
            dropdownParent: $("#append_charge_modal"),
            width: "100%",
            placeholder: "Search service, supply, procedure, medicine, or diagnostic fee...",
            ajax: {
                url: "/api/fetch_all_charges",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: function (params) {
                    return {
                        search: params.term,
                        category: $("#sec_search_filter").val() || "ALL",
                        consultationrefno: consultationRefno
                    };
                },
                processResults: function (response) {
                    return {
                        results: (response.charges || []).map(item => ({
                            id: item.prodcode,
                            text: `${item.prod_itemdscr} [${item.item_grouping || 'CHARGE'}]`,
                            price: item.price_regular || item.cost_ave || 0,
                            data: item
                        }))
                    };
                }
            }
        });
    }

    // Detailed Comment: Category filter switch inside Append Charges modal re-initializes Select2
    $("#sec_search_filter").on("change", function () {
        const refno = String($("#pxconsultationrefno").text() || $("#pxconsultationrefno").val() || "").trim();
        initSecSearchCharge(refno);
        $("#sec_charge_amount").val("");
    });

    // Detailed Comment: Price auto-fill upon charge selection with HMO / PHIC tier consideration
    $("#sec_search_charge").on("select2:select", function (e) {
        const selectedData = e.params.data;
        const prodcode = selectedData.id;
        const patientType = $("#patient_type").val();

        let initialPrice = selectedData.price || 0;
        $("#sec_charge_amount").val(parseFloat(initialPrice).toFixed(2));

        $.ajax({
            url: "/api/fetch_charge_payments",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { prodcode: prodcode },
            success: function (res) {
                if (res && res.prices) {
                    let unitPrice = res.prices.price_regular || initialPrice;
                    if (patientType === "hmo" && parseFloat(res.prices.price_hmo) > 0) {
                        unitPrice = res.prices.price_hmo;
                    } else if (patientType === "phic" && parseFloat(res.prices.price_phic) > 0) {
                        unitPrice = res.prices.price_phic;
                    }
                    $("#sec_charge_amount").val(parseFloat(unitPrice).toFixed(2));
                }
            }
        });
    });

    // Detailed Comment: Append selected charge to pending queue table
    $("#sec_append_to_charges_btn").on("click", function () {
        const prodcode = $("#sec_search_charge").val();
        const selectData = $("#sec_search_charge").select2("data");
        const itemDscr = selectData && selectData.length > 0 ? selectData[0].text : "";
        const qty = parseFloat($("#sec_charge_qty").val()) || 1;
        const amount = parseFloat($("#sec_charge_amount").val());

        if (!prodcode) {
            return Swal.fire({ title: "Select Charge", text: "Please choose a charge item first.", icon: "warning" });
        }
        if (isNaN(amount) || amount < 0) {
            return Swal.fire({ title: "Invalid Price", text: "Please enter a valid unit price.", icon: "warning" });
        }
        if (qty <= 0) {
            return Swal.fire({ title: "Invalid Quantity", text: "Quantity must be at least 1.", icon: "warning" });
        }

        const existing = secPendingCharges.find(c => c.prodcode === prodcode);
        if (existing) {
            existing.qty += qty;
            existing.unit_price = amount;
        } else {
            secPendingCharges.push({
                prodcode: prodcode,
                item_dscr: itemDscr,
                qty: qty,
                unit_price: amount
            });
        }

        renderSecPendingCharges();

        $("#sec_search_charge").val(null).trigger("change");
        $("#sec_charge_qty").val(1);
        $("#sec_charge_amount").val("");
    });

    // Detailed Comment: Remove item from pending charges queue
    $(document).on("click", ".sec-remove-pending-charge", function () {
        const index = $(this).data("index");
        secPendingCharges.splice(index, 1);
        renderSecPendingCharges();
    });

    // Detailed Comment: Save all appended charges to database with spinner loader
    $("#sec_save_charges_btn").on("click", function () {
        const refno = String($("#pxconsultationrefno").text() || $("#pxconsultationrefno").val() || "").trim();
        if (!refno) {
            return Swal.fire({ title: "Error", text: "No active consultation selected.", icon: "error" });
        }
        if (secPendingCharges.length === 0) {
            return Swal.fire({ title: "Empty Charges", text: "Please append at least one charge item before saving.", icon: "warning" });
        }

        const $btn = $(this);
        setBtnLoading($btn, "Saving...");

        const payload = {
            consultationrefno: refno,
            chargerefnos: secPendingCharges.map(item => ({
                prodcode: item.prodcode,
                quantity: item.qty,
                amount: item.unit_price
            }))
        };

        $.ajax({
            url: "/api/save_patient_charges",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: payload,
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "success",
                        title: "Charges appended and saved successfully!",
                        showConfirmButton: false,
                        timer: 1500
                    });

                    const modalEl = document.getElementById("append_charge_modal");
                    if (modalEl) {
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                    }

                    secPendingCharges = [];
                    renderSecPendingCharges();
                    loadPatientCharges();
                } else {
                    Swal.fire({
                        title: "Failed to Save Charges",
                        html: response.message || "An error occurred while saving charges.",
                        icon: "error"
                    });
                }
            },
            error: function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to save charges.";
                Swal.fire({ title: "Error", html: msg, icon: "error" });
            },
            complete: function () {
                resetBtnLoading($btn);
            }
        });
    });

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
        const photoBase64 = $("#photo_base64").val();
        if (photoBase64) formData.append("photo_base64", photoBase64);

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
                                updateDoctorQueueBadges();
                                $("#pxidno").val('').text('');
                                $("#pxconsultationrefno").text('');
                                $("#photo_path").val('');
                                $("#photo_base64").val('');
                                $("#patient_picture_preview").prop("src", "/images/blank_photo.png");
                                $("#sec_medhistory_table tbody").html('<tr><td colspan="6" class="text-center text-muted py-3">No patient consultation history loaded yet. Import or select a patient to view medical history.</td></tr>');
                                $("#sec_medhistory_count").text('0 records');
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
        const photoBase64 = $("#photo_base64").val();
        if (photoBase64) formData.append("photo_base64", photoBase64);

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
                                updateDoctorQueueBadges();
                                $("#pxidno").val('').text('');
                                $("#pxconsultationrefno").text('');
                                $("#photo_path").val('');
                                $("#photo_base64").val('');
                                $("#patient_picture_preview").prop("src", "/images/blank_photo.png");
                                $("#sec_medhistory_table tbody").html('<tr><td colspan="6" class="text-center text-muted py-3">No patient consultation history loaded yet. Import or select a patient to view medical history.</td></tr>');
                                $("#sec_medhistory_count").text('0 records');
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
        // Detailed Comment: Safely destroy existing DataTable instance in DataTables 2 (clear then destroy)
        if ($.fn.DataTable.isDataTable("#patientMasterlistTable")) {
            $("#patientMasterlistTable").DataTable().clear().destroy();
        }
        $("#patientMasterlistTable tbody").empty();
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
                        // Detailed Comment: Default "Update or assign new consultation date" to match current queue date
                        $("#sched_date").val($("#queuedate").val() || todayStr);
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
        const pincode = $("#mpincode").val() || $(this).data('pincode') || '';
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
                dataSrc: function (json) {
                    return json.history || json.medhistory || json.data || [];
                }
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

        $("#info_total").val(r.net_total ? 'PHP ' + parseFloat(r.net_total).toFixed(2) : 'PHP 0.00');
        $("#info_cash").val(r.cash ? 'PHP ' + parseFloat(r.cash).toFixed(2) : 'PHP 0.00');
        $("#info_cta").val(r.cta ? 'PHP ' + parseFloat(r.cta).toFixed(2) : 'PHP 0.00');
        $("#info_cta_type").val(cardLabel);
        $("#info_hmo").val(r.hmo ? 'PHP ' + parseFloat(r.hmo).toFixed(2) : 'PHP 0.00');
        $("#info_phic").val(r.phic ? 'PHP ' + parseFloat(r.phic).toFixed(2) : 'PHP 0.00');

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
                            $("#info_total").val('PHP ' + totalSettlementAmount.toFixed(2));
                            $("#info_cash").val('PHP 0.00');
                            $("#info_cta").val('PHP 0.00');
                            $("#info_cta_type").val('None');
                            $("#info_hmo").val('PHP 0.00');
                            $("#info_phic").val('PHP 0.00');
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
                    $("#info_total").val('PHP ' + totalSettlementAmount.toFixed(2));
                    $("#info_cash").val('PHP 0.00');
                    $("#info_cta").val('PHP 0.00');
                    $("#info_cta_type").val('None');
                    $("#info_hmo").val('PHP 0.00');
                    $("#info_phic").val('PHP 0.00');
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

    /**
     * Detailed Comment: Remove patient charge fee with administrator elevation protection.
     * Checks if current user/session is elevated; if not, prompts admin credentials modal before calling /api/delete_patient_charge.
     */
    $(document).on("click", ".remove_charge", function () {
        const $btn = $(this);
        const prodcode = $btn.val() || $btn.data("prodcode");
        const chargeId = $btn.data("id");
        const itemDscr = $btn.data("item") || "this charge item";
        const refno = String($("#pxconsultationrefno").text() || $("#pxconsultationrefno").val() || "").trim();

        if (!refno || (!prodcode && !chargeId)) return;

        requireAdminAuth(function () {
            Swal.fire({
                title: "Delete Charge Fee?",
                html: `Are you sure you want to remove <strong>${itemDscr}</strong> from this consultation's charges?`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, delete",
                confirmButtonColor: "#d33"
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.showLoading();
                    setBtnLoading($btn, "");
                    const payload = { consultationrefno: refno, prodcode: prodcode };
                    if (chargeId && chargeId !== 'null' && chargeId !== 'undefined') {
                        payload.chargeid = chargeId;
                    }

                    $.ajax({
                        url: "/api/delete_patient_charge",
                        type: "POST",
                        headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                        data: payload,
                        success: function (response) {
                            if (response.success) {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: 'Charge removed',
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                                loadPatientCharges();
                            } else {
                                Swal.fire({ title: "Error", text: response.message || "Failed to remove charge.", icon: "error" });
                            }
                        },
                        error: function (xhr) {
                            Swal.fire({ title: "Error", text: xhr.responseJSON?.message || "Failed to remove charge.", icon: "error" });
                        },
                        complete: function () {
                            resetBtnLoading($btn);
                        }
                    });
                }
            });
        });
    });

    /**
     * Detailed Comment: Edit patient charge fee (unit price & quantity) with administrator elevation protection.
     * Prompts administrator credentials if not elevated, then presents SweetAlert2 interactive input dialog
     * to modify fee details and submits to /api/update_charge.
     */
    $(document).on("click", ".edit_charge_btn_sc", function () {
        const $btn = $(this);
        const prodcode = $btn.data("prodcode");
        const chargeId = $btn.data("id");
        const itemDscr = $btn.data("item") || "Charge Item";
        const currentPrice = parseFloat($btn.data("price") || 0);
        const currentQty = parseFloat($btn.data("qty") || 1);
        const refno = String($("#pxconsultationrefno").text() || $("#pxconsultationrefno").val() || "").trim();

        if (!refno || (!prodcode && !chargeId)) return;

        requireAdminAuth(function () {
            Swal.fire({
                title: `<i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Fee`,
                html: `
                    <div class="text-start mb-3">
                        <label class="form-label fw-bold small text-muted">Item Description</label>
                        <input class="form-control bg-light" type="text" value="${itemDscr}" readonly>
                    </div>
                    <div class="row g-2 text-start">
                        <div class="col-6">
                            <label class="form-label fw-bold small">Unit Price (PHP) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" class="form-control" id="swal_edit_fee_price" value="${currentPrice}">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">Quantity <span class="text-danger">*</span></label>
                            <input type="number" step="1" min="1" class="form-control" id="swal_edit_fee_qty" value="${currentQty}">
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: "Save Changes",
                confirmButtonColor: "#3085d6",
                preConfirm: () => {
                    const price = parseFloat(document.getElementById('swal_edit_fee_price').value);
                    const qty = parseFloat(document.getElementById('swal_edit_fee_qty').value);
                    if (isNaN(price) || price < 0) {
                        Swal.showValidationMessage('Please enter a valid price (>= 0).');
                        return false;
                    }
                    if (isNaN(qty) || qty < 1) {
                        Swal.showValidationMessage('Quantity must be at least 1.');
                        return false;
                    }
                    return { price: price, qty: qty };
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    setBtnLoading($btn, "");
                    const payload = {
                        consultationrefno: refno,
                        prodcode: prodcode,
                        charge_fee: result.value.price,
                        charge_qty: result.value.qty,
                        discount: 0
                    };
                    if (chargeId && chargeId !== 'null' && chargeId !== 'undefined') {
                        payload.chargeid = chargeId;
                    }

                    $.ajax({
                        url: "/api/update_charge",
                        type: "POST",
                        headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                        data: payload,
                        success: function (res) {
                            if (res.success) {
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: 'Fee updated successfully',
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                                loadPatientCharges();
                            } else {
                                Swal.fire({ title: "Error", text: res.message || "Failed to update fee.", icon: "error" });
                            }
                        },
                        error: function (xhr) {
                            Swal.fire({ title: "Error", text: xhr.responseJSON?.message || "Failed to update fee.", icon: "error" });
                        },
                        complete: function () {
                            resetBtnLoading($btn);
                        }
                    });
                }
            });
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

    /**
     * Detailed Comment: Delete patient queue record with administrator credential elevation protection.
     * Secretary and unauthorized sessions must authorize via admin credentials before deleting queue items.
     */
    $(document).on("click", ".delete-queue-item", function () {
        const consultationrefno = $(this).val();
        if (!consultationrefno) return;

        requireAdminAuth(function () {
            Swal.fire({
                title: "Delete Queue Record?",
                text: "Are you sure you want to remove this consultation from the patient queue?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, delete",
                confirmButtonColor: "#d33"
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.showLoading();
                    $.ajax({
                        url: "/api/delete_patient_queue",
                        type: "POST",
                        headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                        data: { consultationrefno: consultationrefno },
                        success: function (res) {
                            if (res.success) {
                                Swal.fire({
                                    icon: "success",
                                    title: "Deleted",
                                    text: res.message || "Queue record deleted successfully.",
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                loadPatientTable();
                                updateDoctorQueueBadges();
                            } else {
                                Swal.fire({
                                    icon: "error",
                                    title: "Error",
                                    text: res.message || "Failed to delete queue record."
                                });
                            }
                        },
                        error: function (xhr) {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: xhr.responseJSON?.message || "Failed to delete queue record."
                            });
                        }
                    });
                }
            });
        });
    });

    /**
     * Detailed Comment: Loads past consultation payment history for the active patient.
     * Fetches historical settlement records via /api/fetch_patient_payment_history
     * and renders into #px_previous_payments_table. Accepts optional onComplete callback for loader toggling.
     */
    function loadPatientPaymentHistory(pincode, pxrefno, consultationrefno, onComplete = null) {
        const $tbody = $("#px_previous_payments_table tbody");
        if (!pincode && !pxrefno && !consultationrefno) {
            $tbody.html('<tr><td colspan="7" class="text-center text-muted">Select or import a patient consultation to view past payment history.</td></tr>');
            if (typeof onComplete === 'function') onComplete();
            return;
        }

        $tbody.html('<tr><td colspan="7" class="text-center text-muted"><span class="spinner-border spinner-border-sm me-1"></span> Loading past payment history...</td></tr>');

        $.ajax({
            url: "/api/fetch_patient_payment_history",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: {
                pincode: pincode,
                pxrefno: pxrefno,
                consultationrefno: consultationrefno
            },
            success: function (res) {
                $tbody.empty();
                if (res.success && res.payments && res.payments.length > 0) {
                    res.payments.forEach(pay => {
                        const totalBill = parseFloat(pay.net_payable ?? pay.total_gross ?? 0).toFixed(2);
                        const paidCash = parseFloat(pay.payment_cash || 0);
                        const paidCard = parseFloat(pay.payment_card || 0);
                        const totalPaid = (paidCash + paidCard).toFixed(2);
                        const channels = [];
                        if (paidCash > 0) channels.push('Cash');
                        if (paidCard > 0) channels.push(pay.cta_type ? `Card (${pay.cta_type})` : 'Card');
                        if (pay.hmocode || pay.less_hmo > 0) channels.push(`HMO (${pay.hmoname || pay.hmocode || 'Covered'})`);
                        const channelStr = channels.length > 0 ? channels.join(', ') : 'None';

                        const statusBadge = pay.payment_status === 'PAID'
                            ? '<span class="badge bg-success">PAID</span>'
                            : (pay.payment_status === 'PARTIAL'
                                ? '<span class="badge bg-warning text-dark">PARTIAL</span>'
                                : '<span class="badge bg-danger">UNPAID</span>');

                        $tbody.append(`
                            <tr>
                                <td class="align-middle">${pay.consultation_date || 'N/A'}</td>
                                <td class="align-middle fw-semibold">${pay.consultationrefno || 'N/A'}</td>
                                <td class="align-middle">${pay.docname || 'N/A'}</td>
                                <td class="align-middle text-end fw-semibold">PHP ${totalBill}</td>
                                <td class="align-middle text-end text-success fw-bold">PHP ${totalPaid}</td>
                                <td class="align-middle small">${channelStr}</td>
                                <td class="align-middle text-center">${statusBadge}</td>
                            </tr>
                        `);
                    });
                } else {
                    $tbody.html('<tr><td colspan="7" class="text-center text-muted">No past payment or settlement history found for this patient.</td></tr>');
                }
            },
            error: function () {
                $tbody.html('<tr><td colspan="7" class="text-center text-danger">Failed to load payment history.</td></tr>');
            },
            complete: function () {
                if (typeof onComplete === 'function') onComplete();
            }
        });
    }

    // Detailed Comment: Refresh payment history button with icon spin animation feedback
    $("#refresh_payment_history_btn").on("click", function () {
        const $btn = $(this);
        const $icon = $btn.find("i");
        $icon.addClass("fa-spin");
        $btn.prop("disabled", true);

        const pincode = $("#pincode").val();
        const pxrefno = $("#pxidno").text() || $("#pxidno").val();
        const consultationrefno = $("#pxconsultationrefno").text() || $("#pxconsultationrefno").val();
        loadPatientPaymentHistory(pincode, pxrefno, consultationrefno, function () {
            $icon.removeClass("fa-spin");
            $btn.prop("disabled", false);
        });
    });

    // Detailed Comment: Refresh charges and past payments whenever Payment Details tab is shown
    $(document).on('shown.bs.tab', 'button[data-bs-target="#payment_info"]', function () {
        loadPatientCharges();
        const pincode = $("#pincode").val();
        const pxrefno = $("#pxidno").text() || $("#pxidno").val();
        const consultationrefno = $("#pxconsultationrefno").text() || $("#pxconsultationrefno").val();
        loadPatientPaymentHistory(pincode, pxrefno, consultationrefno);
    });

    // Detailed Comment: Open Add Patient Modal from Patient Masterlist card
    $("#btn_add_patient_masterlist").on("click", function () {
        const modalEl = document.getElementById("add_patient_modal");
        if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modal.show();
        }
    });

    // Detailed Comment: View consultation history from Patient Masterlist card
    // Displays button loading feedback, loads right-hand tab, opens #patientMedhistoryModal, and loads #medhistorytable
    $(document).on("click", ".btn_px_history", function () {
        const $btn = $(this);
        const pxrefno = $btn.val() || $btn.data("pxrefno") || '';
        const pincode = $btn.data("pincode") || '';
        const pxName = $btn.data("name") || '';
        if (!pxrefno && !pincode) return;

        setBtnLoading($btn, "");

        // Set patient name badge in modal title
        if (pxName) {
            $("#medhistory_patient_name").text(`- ${pxName}`);
        } else {
            $("#medhistory_patient_name").text('');
        }

        // Set modal data attributes and hidden inputs
        $("#mpincode").val(pincode);
        const $modal = $("#patientMedhistoryModal");
        $modal.data('pxrefno', pxrefno);
        $modal.data('pincode', pincode);

        // Open dedicated consultation history modal
        const modalEl = document.getElementById("patientMedhistoryModal");
        if (modalEl) {
            const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modalInstance.show();
        }

        // Also update inline medical history tab safely
        try {
            loadSecretaryMedhistory(pincode, pxrefno);
        } catch (err) {
            console.warn("Error loading secretary medhistory:", err);
        }

        const medTabBtn = document.getElementById("patient_medhistory_tab_btn");
        if (medTabBtn) {
            bootstrap.Tab.getInstance(medTabBtn)?.show() || new bootstrap.Tab(medTabBtn).show();
        }

        // Restore button state after modal activation
        setTimeout(function () {
            resetBtnLoading($btn);
        }, 400);
    });

    // Detailed Comment: Import patient record from Patient Masterlist card into Consultation Form with loading state feedback
    $(document).on("click", ".btn_px_import", function () {
        const $btn = $(this);
        const pxrefno = $btn.val() || $btn.data("pxrefno") || "";
        const pincode = $btn.data("pincode") || "";
        if (!pxrefno && !pincode) return;

        setBtnLoading($btn, "");

        // Detailed Comment: Activate Consultation Details tab immediately
        const consulTabBtn = document.querySelector('button[data-bs-target="#consul_info"]');
        if (consulTabBtn) {
            bootstrap.Tab.getInstance(consulTabBtn)?.show() || new bootstrap.Tab(consulTabBtn).show();
        }

        $.ajax({
            url: "/api/fetch_consultation",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { pxrefno: pxrefno, pincode: pincode },
            success: function (response) {
                if (response.success && response.patient) {
                    const p = response.patient;
                    $("#pincode").val(p.pincode || "");
                    $("#hidden_consultationrefno").val(p.consultationrefno || "");
                    $("#pxconsultationrefno").text(p.consultationrefno || "").val(p.consultationrefno || "");
                    $("#pxidno").text(p.pxrefno || "").val(p.pxrefno || "");
                    $("#pxfname").val(p.pxfirstname || p.patientname || "");
                    $("#pxmname").val(p.pxmidname || "");
                    $("#pxlname").val(p.pxlastname || "");
                    $("#pxsuffix").val(p.pxsuffix || "");
                    $("#pxsex").val(p.gender || "male");
                    $("#pxbday").val(p.birthday || "");
                    $("#pxage").val(p.birthday ? getAge(p.birthday) : "");
                    $("#pxcellnumber").val(p.mobilenumber || "");
                    $("#pxlandlinenumber").val(p.landlinenumber || "");
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
                    $("#sched_date").val($("#queuedate").val() || todayStr);
                    loadSchedules(2);
                    $("#patient_picture_preview").prop("src", p.photo_path ?? '/images/blank_photo.png');
                    $("#photo_path").val(p.photo_path || "");
                    $("#photo_base64").val("");

                    // Detailed Comment: Safely execute auxiliary loaders
                    try {
                        loadSecretaryMedhistory(p.pincode, p.pxrefno, p.consultationrefno);
                    } catch (err) {
                        console.warn("Error loading secretary medhistory:", err);
                    }
                    try {
                        loadPatientCharges();
                    } catch (err) {
                        console.warn("Error loading patient charges:", err);
                    }
                    try {
                        loadPatientPaymentHistory(p.pincode, p.pxrefno, p.consultationrefno);
                    } catch (err) {
                        console.warn("Error loading patient payment history:", err);
                    }

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

    /**
     * Detailed Comment: Edit Patient Masterlist record with administrator credential elevation protection.
     * Checks session elevation, fetches patient details via /api/fetch_patient_details,
     * populates #editPatientModal tabbed inputs, and shows modal with button loader feedback.
     */
    $(document).on("click", ".btn_px_edit", function () {
        const $btn = $(this);
        const pxrefno = $btn.val() || $btn.data("pxrefno") || "";
        const pincode = $btn.data("pincode");
        if (!pxrefno && !pincode) return;

        requireAdminAuth(function () {
            setBtnLoading($btn, "Loading...");
            $.ajax({
                url: "/api/fetch_patient_details",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: { pxrefno: pxrefno, pincode: pincode },
                success: function (res) {
                    if (res.success && res.patient) {
                        const p = res.patient;
                        $("#edit_pxrefno").val(p.pxrefno || "");
                        $("#edit_display_pxrefno").val(p.pxrefno || "");
                        $("#edit_pincode").val(p.pincode || "");
                        $("#edit_phic_pin").val(p.phic_pin || "");
                        $("#edit_ipd_pincode").val(p.ipd_pincode || "");
                        $("#edit_pxfirstname").val(p.pxfirstname || "");
                        $("#edit_pxmidname").val(p.pxmidname || "");
                        $("#edit_pxlastname").val(p.pxlastname || "");
                        $("#edit_pxsuffix").val(p.pxsuffix || "");
                        $("#edit_gender").val(p.gender || "MALE");
                        $("#edit_birthday").val(p.birthday ? p.birthday.split(' ')[0] : "");
                        $("#edit_religion").val(p.religion || "");
                        $("#edit_nationality").val(p.nationality || "FILIPINO");
                        $("#edit_ispwd").val(p.ispwd ? "1" : "0");
                        $("#edit_senior_idno").val(p.senior_idno || "");
                        $("#edit_mobilenumber").val(p.mobilenumber || "");
                        $("#edit_emailaddress").val(p.emailaddress || "");
                        $("#edit_streetadrs").val(p.streetadrs || "");
                        $("#edit_zipcode").val(p.zipcode || "");
                        $("#edit_address").val(p.address || "");
                        $("#edit_classification").val(p.classification || "");
                        $("#edit_next_follow_up").val(p.next_follow_up || "");
                        $("#edit_medical_history_summary").val(p.medical_history_summary || "");

                        const photoSrc = p.photo_path
                            ? (p.photo_path.startsWith('http') ? p.photo_path : `/patient/photo/${p.photo_path.split('/').pop()}`)
                            : '/images/blank_photo.png';
                        $("#edit_patient_picture_preview").prop("src", photoSrc);
                        $("#edit_photo_path").val(p.photo_path || "");
                        $("#edit_photo_base64").val("");

                        const modalEl = document.getElementById("editPatientModal");
                        if (modalEl) {
                            const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                            modal.show();
                        }
                    } else {
                        Swal.fire({ title: "Error", text: res.message || "Failed to load patient details.", icon: "error" });
                    }
                },
                error: function (xhr) {
                    Swal.fire({ title: "Error", text: xhr.responseJSON?.message || "Failed to load patient details.", icon: "error" });
                },
                complete: function () {
                    resetBtnLoading($btn);
                }
            });
        });
    });

    /**
     * Detailed Comment: Submit handler for saving edited patient masterlist details.
     * Posts updated demographics and address to /api/admin/update_patient.
     */
    $("#editPatientForm").on("submit", function (e) {
        e.preventDefault();
        const $submitBtn = $(this).find('button[type="submit"]');
        setBtnLoading($submitBtn, "Saving...");

        const formData = new FormData(this);

        $.ajax({
            url: "/api/admin/update_patient",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Patient updated successfully',
                        showConfirmButton: false,
                        timer: 1800
                    });
                    const modalEl = document.getElementById("editPatientModal");
                    if (modalEl) {
                        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                        modal.hide();
                    }
                    loadPatientMasterlistQueueTable();
                } else {
                    Swal.fire({ title: "Error", text: res.message || "Failed to update patient.", icon: "error" });
                }
            },
            error: function (xhr) {
                Swal.fire({ title: "Error", text: xhr.responseJSON?.message || "Failed to update patient.", icon: "error" });
            },
            complete: function () {
                resetBtnLoading($submitBtn);
            }
        });
    });

    /**
     * Detailed Comment: Delete Patient Masterlist record with administrator credential elevation protection.
     * Confirms deletion intent and invokes /api/delete_patient_sec, then refreshes table.
     */
    $(document).on("click", ".btn_px_delete", function () {
        const pxrefno = $(this).val() || $(this).data("pxrefno") || "";
        const pxName = $(this).data("name") || "this patient";
        if (!pxrefno) return;

        requireAdminAuth(function () {
            Swal.fire({
                title: "Delete Patient Record?",
                html: `Are you sure you want to permanently delete patient <strong>${pxName}</strong>? All associated consultation records will also be deleted.`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, delete patient",
                confirmButtonColor: "#d33"
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.showLoading();
                    $.ajax({
                        url: "/api/delete_patient_sec",
                        type: "POST",
                        headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                        data: { pxrefno: pxrefno },
                        success: function (res) {
                            if (res.success) {
                                Swal.fire({
                                    icon: "success",
                                    title: "Deleted",
                                    text: res.message || "Patient record deleted successfully.",
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                loadPatientMasterlistQueueTable();
                                loadPatientTable();
                            } else {
                                Swal.fire({ title: "Error", text: res.message || "Failed to delete patient record.", icon: "error" });
                            }
                        },
                        error: function (xhr) {
                            Swal.fire({ title: "Error", text: xhr.responseJSON?.message || "Failed to delete patient record.", icon: "error" });
                        }
                    });
                }
            });
        });
    });

    /**
     * Detailed Comment: Initialize address cascade for Edit Patient modal if loaded
     */
    if ($("#editPatientModal").length && $("#edit_region").length) {
        initAddressCascade({
            regionSel: '#edit_region',
            provSel: '#edit_province',
            munSel: '#edit_muncity',
            brgySel: '#edit_brgy',
            zipInput: '#edit_zipcode',
            streetInput: '#edit_streetadrs',
            fullAddressInput: '#edit_address'
        });
    }
});

