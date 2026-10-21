$(function () {
    /**
     * Detailed Comment: Helper functions to toggle button loading spinners and disabled state.
     * Preserves original inner HTML in data-orig-html and restores upon request completion.
     */
    function setBtnLoading($btn, text) {
        if (!$btn || $btn.length === 0) return;
        const origHtml = $btn.html();
        $btn.data('orig-html', origHtml).prop('disabled', true);
        $btn.html(`<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> ${text}`);
    }

    function resetBtnLoading($btn) {
        if (!$btn || $btn.length === 0) return;
        const origHtml = $btn.data('orig-html');
        if (origHtml) $btn.html(origHtml);
        $btn.prop('disabled', false);
    }

    /**
     * Detailed Comment: Suppress intrusive DataTables native alert popups across the consultation page
     * and redirect internal warnings to the console for structured debugging.
     */
    if ($.fn.dataTable) {
        $.fn.dataTable.ext.errMode = function (settings, helpPage, message) {
            console.warn("DataTables warning:", message);
        };
    }

    $("#close-consultation-modal").on("click", function () {
        Swal.fire({
            title: "Close Form?",
            text: "Make sure patient data are saved, the patient will still be marked as PENDING if not yet COMPLETED.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Close"
        }).then((result) => {
            const modal = bootstrap.Modal.getInstance("#consultation_modal");

            if (result.isConfirmed) {
                modal.hide();
            }
        });
    });

    /**
     * Detailed Comment: Tab shown event listener for the card-header Consultation History tab.
     * Triggers loadMedicalHistory() and adjusts table column sizing when the tab becomes visible.
     */
    $(document).on('shown.bs.tab', 'button[data-bs-target="#main_medhistory_pane"], #main_medhistory_tab_btn', function () {
        loadMedicalHistory();
        if ($.fn.DataTable.isDataTable("#medhistory_table")) {
            $("#medhistory_table").DataTable().columns.adjust().responsive.recalc();
        }
    });

    // Detailed Comment: Tab shown event listener for Doctor Permanent Medical History tab
    $(document).on('shown.bs.tab', 'button[data-bs-target="#main_permanent_medhistory_pane"], #main_permanent_medhistory_tab_btn', function () {
        loadDoctorPermanentMedicalHistory();
    });

    // Detailed Comment: Support manual refresh button inside the Consultation History tab pane
    $(document).on("click", "#refresh_medhistory_btn, #medhistory_btn", function () {
        loadMedicalHistory();
    });

    /**
     * Detailed Comment: Cleanly initialises or refreshes the Consultation History DataTable (#medhistory_table).
     * Follows strict DataTables lifecycle (clear then destroy, empty tbody) to prevent 'Cannot reinitialise DataTable' errors.
     * Incorporates safe defaultContent and fallbacks across all 7 columns to prevent 'Requested unknown parameter' warnings.
     */
    function loadMedicalHistory() {
        const consulRef = $("#consultationrefno").val() || "";
        const pxRef = $("#consultation_modal").data("pxrefno") || "";
        const pinCode = $("#consultation_modal").data("pincode") || "";

        if ($.fn.DataTable.isDataTable("#medhistory_table")) {
            $("#medhistory_table").DataTable().clear().destroy();
            $("#medhistory_table tbody").empty();
        }

        // If no patient identifier is loaded yet, stop after clean reset
        if (!consulRef && !pxRef && !pinCode) {
            return;
        }

        $("#medhistory_table").DataTable({
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
                    window._doctorMedhistoryRecords = list;
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
                        return `<button type="button" class="btn btn-sm btn-outline-primary view-past-consultation-btn view-consultation-details-btn btn-view-details" data-index="${meta.row}" data-consultationrefno="${row.consultationrefno || ''}" title="View Past Consultation Details">
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
            select: false,
            language: {
                emptyTable: "No previous consultations recorded for this patient.",
                zeroRecords: "No matching consultations found."
            },
            info: true,
            paging: true,
            pageLength: 10,
            ordering: false,
            responsive: true
        });
    }
    window.loadMedicalHistory = loadMedicalHistory;

    /**
     * Detailed Comment: Fetches and populates permanent medical history for the Doctor Consultation modal.
     * Maps allergies, injections/immunizations, past medical illnesses, surgical history, family history,
     * daily maintenance medications, and special clinical notes from pxmedicalhistory.
     * Immediately toggles the prominent Allergy Alert Banner (#doc_allergy_alert_bar) in the Patient Info card.
     */
    function loadDoctorPermanentMedicalHistory(pxrefno, pincode, consultationrefno) {
        const consulRef = consultationrefno || $("#consultationrefno").val() || "";
        const pxRef = pxrefno || $("#consultation_modal").data("pxrefno") || "";
        const pinCode = pincode || $("#consultation_modal").data("pincode") || "";

        if (!consulRef && !pxRef && !pinCode) {
            $("#doc_allergies").val("");
            $("#doc_injections").val("");
            $("#doc_past_medical_history").val("");
            $("#doc_surgical_history").val("");
            $("#doc_family_history").val("");
            $("#doc_maintenance_meds").val("");
            $("#doc_clinical_notes").val("");
            $("#doc_allergy_alert_bar").addClass("d-none").removeClass("d-flex");
            $("#doc_allergy_alert_text").text("");
            return;
        }

        $.ajax({
            url: "/api/fetch_patient_medical_history",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
                pxrefno: pxRef,
                pincode: pinCode,
                consultationrefno: consulRef
            },
            success: function (res) {
                if (res.success && res.data) {
                    const d = res.data;
                    $("#doc_allergies").val(d.allergies || "");
                    $("#doc_injections").val(d.injections_immunization || "");
                    $("#doc_past_medical_history").val(d.past_medical_history || "");
                    $("#doc_surgical_history").val(d.surgical_history || "");
                    $("#doc_family_history").val(d.family_history || "");
                    $("#doc_maintenance_meds").val(d.maintenance_medications || "");
                    $("#doc_clinical_notes").val(d.notes || "");

                    // Update Allergy Alert Banner
                    if (d.allergies && d.allergies.trim() !== "") {
                        $("#doc_allergy_alert_text").text(d.allergies.trim());
                        $("#doc_allergy_alert_bar").removeClass("d-none").addClass("d-flex");
                    } else {
                        $("#doc_allergy_alert_bar").addClass("d-none").removeClass("d-flex");
                        $("#doc_allergy_alert_text").text("");
                    }
                } else {
                    $("#doc_allergies").val("");
                    $("#doc_injections").val("");
                    $("#doc_past_medical_history").val("");
                    $("#doc_surgical_history").val("");
                    $("#doc_family_history").val("");
                    $("#doc_maintenance_meds").val("");
                    $("#doc_clinical_notes").val("");
                    $("#doc_allergy_alert_bar").addClass("d-none").removeClass("d-flex");
                    $("#doc_allergy_alert_text").text("");
                }
            },
            error: function (err) {
                console.warn("Failed to load doctor permanent medical history:", err);
            }
        });
    }
    window.loadDoctorPermanentMedicalHistory = loadDoctorPermanentMedicalHistory;

    /**
     * Detailed Comment: Event handler for saving permanent medical history from the Doctor Consultation Modal.
     * Persists allergies, injections, maintenance meds, past medical/surgical/family history, and clinical notes to pxmedicalhistory.
     * Updates the doctor allergy alert banner dynamically upon successful persistence.
     */
    $(document).on("click", "#save_doctor_medhistory_btn", function () {
        const $btn = $(this);
        const consulRef = $("#consultationrefno").val() || "";
        const pxRef = $("#consultation_modal").data("pxrefno") || "";
        const pinCode = $("#consultation_modal").data("pincode") || "";

        if (!consulRef && !pxRef && !pinCode) {
            Swal.fire({
                title: "No Patient Selected",
                text: "Please open a patient consultation first before saving medical history.",
                icon: "warning"
            });
            return;
        }

        const originalHtml = $btn.html();
        $btn.prop("disabled", true).html('<i class="fa-solid fa-spinner fa-spin me-1"></i> Saving...');

        const payload = {
            consultationrefno: consulRef,
            pxrefno: pxRef,
            pincode: pinCode,
            allergies: $("#doc_allergies").val(),
            injections_immunization: $("#doc_injections").val(),
            past_medical_history: $("#doc_past_medical_history").val(),
            surgical_history: $("#doc_surgical_history").val(),
            family_history: $("#doc_family_history").val(),
            maintenance_medications: $("#doc_maintenance_meds").val(),
            notes: $("#doc_clinical_notes").val()
        };

        $.ajax({
            url: "/api/save_patient_medical_history",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: payload,
            success: function (res) {
                if (res.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Medical history saved successfully',
                        showConfirmButton: false,
                        timer: 2000
                    });

                    // Update local allergy alert banner dynamically
                    const currentAllergies = ($("#doc_allergies").val() || "").trim();
                    if (currentAllergies) {
                        $("#doc_allergy_alert_text").text(currentAllergies);
                        $("#doc_allergy_alert_bar").removeClass("d-none").addClass("d-flex");
                    } else {
                        $("#doc_allergy_alert_bar").addClass("d-none").removeClass("d-flex");
                        $("#doc_allergy_alert_text").text("");
                    }
                } else {
                    Swal.fire({
                        title: "Error",
                        text: res.message || "Failed to save medical history.",
                        icon: "error"
                    });
                }
            },
            error: function (xhr) {
                console.error("Save doctor medical history error:", xhr);
                Swal.fire({
                    title: "Error",
                    text: xhr.responseJSON?.message || "An error occurred while saving medical history.",
                    icon: "error"
                });
            },
            complete: function () {
                $btn.prop("disabled", false).html(originalHtml);
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
     * Detailed Comment: Populates and displays the dedicated Consultation Details Viewer modal
     * with complete historical clinical data (demographics, vitals, impressions, Rx, diagnostics, charges).
     */
    function populateAndShowConsultationDetails(record) {
        if (!record) return;

        const dateStr = record.consultation_date ? record.consultation_date.substring(0, 16) : 'N/A';
        const cref = record.consultationrefno || record.caseno || 'N/A';
        const status = (record.status || 'PENDING').toUpperCase();
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
            PENDING: "bg-warning text-dark",
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

        // 1. Modal header badges
        $("#vcd_consultdate_badge").text(`Date: ${dateStr}`);
        $("#vcd_consultref_badge").text(`Ref: ${cref}`);
        $("#vcd_status_badge")
            .text(`Status: ${status}`)
            .attr('class', `badge ${statusBadges[status] || 'bg-secondary text-white'} fs-6`);
        $("#vcd_patient_type_badge")
            .text(`Type: ${ptype}`)
            .attr('class', `badge ${typeBadges[ptype] || 'bg-secondary text-white'} fs-6`);

        // Detailed Comment: Populate Patient Classification badge & dynamic fields row (Task R5)
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
        $("#vcd_docname").text(record.docname ? `Dr. ${record.docname}` : (record.docfname ? `Dr. ${record.docfname} ${record.doclname || ''}` : (record.docrefno || 'None Assigned')));
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

        // Detailed Comment: Answered Doctor's Questions rendering in Impressions & Diagnosis tab (Task R5)
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

        // Detailed Comment: 8. Tab 6: Patient Permanent Medical History population (Task R5)
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
        const firstTabEl = document.querySelector('button[data-bs-target="#vcd_pane_impdiag"]');
        if (firstTabEl && window.bootstrap && window.bootstrap.Tab) {
            window.bootstrap.Tab.getOrCreateInstance(firstTabEl).show();
        }

        // Show the details modal
        const modalEl = document.getElementById("view_consultation_details_modal");
        if (modalEl && window.bootstrap && window.bootstrap.Modal) {
            const modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    }
    window.populateAndShowConsultationDetails = populateAndShowConsultationDetails;
    window.openConsultationDetailsModal = populateAndShowConsultationDetails;

    /**
     * Detailed Comment: Delegate click handler to open the consultation details viewer modal
     * when clicking "Details" or "View" on any past consultation row in the Consultation History DataTable.
     * Supports .view-past-consultation-btn, .view-consultation-details-btn, and .btn-view-details trigger selectors.
     */
    $(document).on("click", ".view-past-consultation-btn, .view-consultation-details-btn, .btn-view-details", function () {
        const idx = $(this).data("index");
        const refno = $(this).data("consultationrefno") || $(this).attr("data-consultationrefno") || $(this).val();
        let record = null;
        if (window._doctorMedhistoryRecords && window._doctorMedhistoryRecords[idx]) {
            record = window._doctorMedhistoryRecords[idx];
        }

        if (record) {
            populateAndShowConsultationDetails(record);
        } else if (refno) {
            $.ajax({
                url: "/api/fetch_patient_medhistory",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: { consultationrefno: refno },
                success: function (res) {
                    const list = res.medhistory || res.history || res.data || [];
                    if (list.length > 0) {
                        // Detailed Comment: Exact consultation matching by consultationrefno to prevent naive list[0] bias
                        const matched = list.find(item => item.consultationrefno === refno) || list[0];
                        populateAndShowConsultationDetails(matched);
                    }
                }
            });
        }
    });

    /**
     * Detailed Comment: Elevation of nested modal backdrop z-index.
     * When #view_consultation_details_modal is stacked over another open modal
     * (such as #consultation_modal, #patientMedhistoryModal, or #viewPatientModal),
     * elevate the top backdrop to z-index 1065 so it sits between parent and child modal.
     */
    $(document).on("show.bs.modal shown.bs.modal", "#view_consultation_details_modal", function () {
        const $otherModals = $(".modal.show").not("#view_consultation_details_modal");
        if ($otherModals.length > 0) {
            $(".modal-backdrop").last().css("z-index", 1065);
        }
    });

    /**
     * Detailed Comment: Robust 3-step nested modal dismissal & backdrop cleanup handler.
     * Step a: Restore body modal-open class and overflow lock if any parent modal is still visible.
     * Step b: Ensure visible parent modal (#consultation_modal) maintains overflow-y: auto and focus.
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

    /**
     * Detailed Comment: Focus unblurring on modal show and hide transitions (Task R5).
     * Prevents 'Blocked aria-hidden on an element because its descendant retained focus' console warnings.
     */
    $(document).on("show.bs.modal hide.bs.modal", ".modal", function () {
        if (document.activeElement && typeof document.activeElement.blur === "function") {
            document.activeElement.blur();
        }
    });

    /**
     * Detailed Comment: Helper to open dedicated #preview_modal for Radiology and Laboratory files from view modal (Task R5)
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
     * Detailed Comment: Load and refresh chief complaints, impressions, and diagnosis
     * strictly for the active consultation record whenever the Impressions & Diagnosis tab is opened.
     */
    $("#impDiagBtn").on("click", function () {
        const refno = $("#consultationrefno").val();
        if (!refno) return;

        $.ajax({
            url: "/api/fetch_patient_data",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { consultationrefno: refno },
            success: function (res) {
                if (res && res.patient) {
                    const p = res.patient;
                    $("#reasonforconsultation").val(p.reasonforconsultation || "");
                    $("#impressions").val(p.impression || "");
                    $("#diagnosis").val(p.finadiagnosis || p.diagnosis || "");
                    $("#foradmit").prop("checked", p.foradmit == 1);
                    $("#foradmit_instructions").val(p.foradmit_instructions || "");

                    // Detailed Comment: Render answered doctor questions from fetch_patient_data or fallback fetch_consultation
                    const renderActiveQuestions = (answers) => {
                        if (answers && answers.length > 0) {
                            let aHtml = '';
                            answers.forEach(a => {
                                aHtml += `<div class="p-2 border rounded bg-white">
                                    <strong class="d-block text-dark">${a.question || 'Doctor Question'}</strong>
                                    <span class="text-secondary">${a.answer || 'No answer recorded'}</span>
                                </div>`;
                            });
                            $("#doctor_answered_questions_list").html(aHtml);
                            $("#doctor_answered_questions_container").removeClass('d-none');
                        } else {
                            $("#doctor_answered_questions_list").empty();
                            $("#doctor_answered_questions_container").addClass('d-none');
                        }
                    };

                    if (res.answers && res.answers.length > 0) {
                        renderActiveQuestions(res.answers);
                    } else {
                        $.ajax({
                            url: "/api/fetch_consultation",
                            type: "POST",
                            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                            data: { consultationrefno: refno },
                            success: function (cRes) {
                                renderActiveQuestions(cRes && cRes.answers ? cRes.answers : []);
                            }
                        });
                    }
                }
            }
        });
    });

    // Detailed Comment: Trigger Impressions & Diagnosis data refresh upon Bootstrap tab activation
    $(document).on("shown.bs.tab", "#impDiagBtn, button[data-bs-target='#impDiagTab']", function () {
        $("#impDiagBtn").trigger("click");
    });

    // Detailed Comment: Save chief complaints, impressions, and diagnosis with button loading spinner
    $("#save_impressions_diagnosis").on("click", function () {
        const $btn = $(this);
        setBtnLoading($btn, "Saving...");

        $.ajax({
            url: "/api/save_impressions_diagnosis",
            type: "POST",
            data: {
                consultationrefno: $("#consultationrefno").val(),
                reasonforconsultation: $("#reasonforconsultation").val(),
                impressions: $("#impressions").val(),
                diagnosis: $("#diagnosis").val(),
                foradmit: $("#foradmit").is(":checked") ? 1 : 0,
                foradmit_instructions: $("#foradmit_instructions").val()
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
            },
            complete: function () {
                resetBtnLoading($btn);
            }
        });
    });

    // Generate RX modal
    $("#mymed").select2({
        width: '100%',
        dropdownParent: $("#rx_modal"),
        ajax: {
            url: "/api/fetch_medicines",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: function (params) { return { term: params.term }; },
            processResults: function (response) {
                return {
                    results: $.map(response.rx || response, function (i) {
                        return {
                            id: i.prodcode,
                            text: i.prod_itemdscr
                        }
                    })
                }
            }
        },
        placeholder: "Search for medicine...",
        // minimumInputLength: 1
    });

    // Detailed Comment: Add prescription medicine to patient ledger with specific instructions and button loading state
    $("#add_rx").on("click", function () {
        const prodcode = $("#mymed").val();
        if (!prodcode) {
            return Swal.fire({ title: "Validation Error", text: "Please select a medicine.", icon: "warning" });
        }
        const qty = $("#myquantity").val();
        const instructions = $("#myinstructions").val();
        const consultationrefno = $("#consultationrefno").val();

        const $btn = $(this);
        setBtnLoading($btn, "Adding...");

        $.ajax({
            url: "/api/add_medicine",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: {
                consultationrefno: consultationrefno,
                prodcode: prodcode,
                qty: qty,
                instructions: instructions
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Medicine added!', showConfirmButton: false, timer: 1500 });
                    loadRx();
                    if (typeof window.loadDashboardRx === 'function') {
                        window.loadDashboardRx();
                    }
                    if ($.fn.DataTable.isDataTable("#charges_table")) {
                        $("#charges_table").DataTable().ajax.reload();
                    }
                    $("#myinstructions").val('');
                    $("#myquantity").val('1');
                } else {
                    Swal.fire({ title: "Error", text: response.message || "Failed to add medicine.", icon: "error" });
                }
            },
            error: function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to add medicine.";
                Swal.fire({ title: "Error", text: msg, icon: "error" });
            },
            complete: function () {
                resetBtnLoading($btn);
            }
        });
    });

    // Detailed Comment: Delete prescription medicine with button loading spinner feedback
    $(document).on("click", ".delete_rx", function () {
        const $btn = $(this);
        Swal.fire({
            title: "Confirmation",
            text: "Delete this from the list of patient RX?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Confirm"
        }).then((result) => {
            if (result.isConfirmed) {
                $btn.prop("disabled", true).html('<i class="fa-solid fa-spinner fa-spin"></i>');
                $.ajax({
                    url: "/api/delete_rx",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: {
                        prodcode: $btn.val(),
                        consultationrefno: $("#consultationrefno").val()
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Medicine Deleted',
                                showConfirmButton: false,
                                timer: 1500
                            });
                            loadRx();
                            if (typeof window.loadDashboardRx === 'function') {
                                window.loadDashboardRx();
                            }
                            if ($.fn.DataTable.isDataTable("#charges_table")) {
                                $("#charges_table").DataTable().ajax.reload();
                            }
                        }
                    },
                    complete: function () {
                        $btn.prop("disabled", false).html('<i class="fa-solid fa-trash"></i>');
                    }
                });
            }
        });
    });

    function loadRx() {
        if ($.fn.DataTable.isDataTable("#rx_table")) {
            $("#rx_table").DataTable().destroy().clear();
        }
        $("#rx_table").DataTable({
            ajax: {
                url: "/api/fetch_medicine_rx",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: {
                    consultationrefno: $("#consultationrefno").val()
                },
                dataSrc: 'rx'
            },
            columns: [
                {
                    data: null,
                    render: function (data) {
                        return `<button class="btn btn-sm btn-danger delete_rx" value="${data.prodcode}" title="Delete medicine"><i class="fa-solid fa-trash"></i></button>`;
                    }
                },
                { data: 'item_dscr' },
                {
                    data: 'instructions',
                    defaultContent: '<span class="text-muted fst-italic">None</span>',
                    render: function (data) {
                        return data ? `<span class="fw-semibold text-primary">${data}</span>` : '<span class="text-muted fst-italic">None</span>';
                    }
                },
                { data: 'qty' },
            ],
            columnDefs: [
                {
                    targets: [0, 3],
                    width: '1%',
                    orderable: false,
                    searchable: false,
                    className: 'text-nowrap text-center align-middle'
                },
                {
                    targets: [1, 2],
                    className: 'align-middle'
                }
            ],
            language: {
                emptyTable: "No prescription records yet."
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

    $("#rx_sidebar_btn").on("click", function () {
        loadDashboardRx();
    });

    // Detailed Comment: Fix column count mismatch by defining 4 columns matching table header in consultation_modal
    function loadDashboardRx() {
        if (typeof window.loadDashboardRx === 'function' && window.loadDashboardRx !== loadDashboardRx) {
            return window.loadDashboardRx();
        }

        // Detailed Comment: Refresh instructions for the active consultation record
        const activeRef = $("#consultationrefno").val();
        if (activeRef) {
            $.ajax({
                url: "/api/fetch_patient_data",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: { consultationrefno: activeRef },
                success: function (res) {
                    if (res && res.patient) {
                        $("#patient_instructions").val(res.patient.instructions || "");
                    }
                }
            });
        }

        if ($.fn.DataTable.isDataTable("#dashboard_rx_table")) {
            $("#dashboard_rx_table").DataTable().destroy().clear();
        }
        $("#dashboard_rx_table").DataTable({
            ajax: {
                url: "/api/fetch_medicine_rx",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: { consultationrefno: $("#consultationrefno").val() },
                dataSrc: "rx"
            },
            columns: [
                { data: 'item_dscr' },
                {
                    data: 'instructions',
                    defaultContent: '<span class="text-muted fst-italic">None</span>',
                    render: function (data) {
                        return data ? `<span class="fw-semibold text-primary">${data}</span>` : '<span class="text-muted fst-italic">None</span>';
                    }
                },
                { data: 'qty' },
                { data: 'dispensed_status' },
            ],
            columnDefs: [
                {
                    targets: [0, 1, 2, 3],
                    className: 'text-nowrap align-middle'
                }
            ],
            language: {
                emptyTable: "No prescription records yet."
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

    // Detailed Comment: Save prescription notes and instructions with button loading state
    $("#save_rx_btn").on("click", function () {
        const $btn = $(this);
        setBtnLoading($btn, "Saving...");

        $.ajax({
            url: "/api/save_rx",
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

                    $("#patient_instructions").val(response.instructions);
                }
            },
            complete: function () {
                resetBtnLoading($btn);
            }
        });
    });

    // Diagnostic requests
    $("#dReqsTabBtn").on("click", function () {
        $("#diagnostics_table").DataTable().destroy().clear();
        $("#diagnostics_table").DataTable({
            ajax: {
                url: "/api/get_diagnostics",
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
                        // Detailed Comment: Bind prodcode or diagnosticrefno so deletion operates on the correct stocks_ledger code
                        const code = data.prodcode || data.diagnostic_id || data.diagnosticrefno || '';
                        return `<button class="btn btn-sm btn-danger remove_request" data-code="${code}" value="${code}"><i class="fa-solid fa-trash"></i> Remove</button>`;
                    }
                },
                { data: 'prod_itemdscr' }
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
            searching: false,
            order: [[1, 'asc']]
        });

        // Detailed Comment: Absolute route path for printing diagnostics across doctor, secretary, or admin views
        $("#print_diagnostics").attr("href", `/doctor/print_diagnostics?consultationrefno=${$("#consultationrefno").val()}`);
    });

    $("#diag_to_consul").on("click", function () { $("#diagnostics_table").DataTable().ajax.reload(); });

    let diagnostics = [];
    $("#diagnostic_btn, #diagnostic_btn_2").on("click", function () {
        $.ajax({
            url: "/api/fetch_diagnostics_data",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { consultationrefno: $("#consultationrefno").val() },
            success: function (response) {
                let optionsTab = $("#diagnostic_options");
                let requestedRef = response.requested.map(r => r.prodcode);
                let allDiagnosticOptions = [...response.available, ...response.requested];

                optionsTab.empty();

                allDiagnosticOptions.forEach(e => {
                    let isRequested = requestedRef.includes(e.prodcode);

                    const entry = `
                        <div class="form-check">
                            <input class="form-check-input diagnostic_check"
                                type="checkbox"
                                value="${e.prodcode}"
                                id="diag_${e.prodcode}"
                                name="${e.prodcode}"
                                ${isRequested ? 'checked disabled' : ''}
                            >
                            <label class="form-check-label" for="${e.prodcode}">${e.prod_itemdscr}</label>
                        </div>
                    `;

                    optionsTab.append(entry);
                });

                var table = $("#selected_table").DataTable();
                table.clear();

                response.requested.forEach(e => {
                    table.row.add([
                        `<button class="btn btn-sm btn-secondary" disabled><i class="fa-solid fa-floppy-disk"></i> Saved</button>`,
                        e.prod_itemdscr
                    ]);
                });

                table.draw();
            }
        });

        if ($.fn.DataTable.isDataTable("#selected_table")) {
            $("#selected_table").DataTable().clear().destroy();
        }

        $("#selected_table").DataTable({
            columnDefs: [
                { target: 0, width: "1%", orderable: false, className: "text-nowrap text-center" },
                { target: 1, className: "text-nowrap align-middle" },
            ],
            order: [[1, 'asc']],
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
                $(`label[for="${value}"]`).text().trim()
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

    // Detailed Comment: Save diagnostic requests with button loading spinner
    $("#save_requests").on("click", function () {
        if (diagnostics.length === 0) return Swal.fire({ title: "No updated changes", text: "No changes were made.", icon: "success" });

        const $btn = $(this);
        setBtnLoading($btn, "Saving...");

        $.ajax({
            url: "/api/save_diagnostics",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
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
            },
            complete: function () {
                resetBtnLoading($btn);
            }
        });
    });

    // Detailed Comment: Remove diagnostic request with button spinner feedback
    $(document).on("click", ".remove_request", function () {
        const $btn = $(this);
        Swal.fire({
            title: "Confirmation",
            text: "Do you want to remove this request?",
            icon: "warning"
        }).then((result) => {
            if (result.isConfirmed) {
                $btn.prop("disabled", true).html('<i class="fa-solid fa-spinner fa-spin"></i>');
                $.ajax({
                    url: "/api/delete_diagnostic",
                    type: "POST",
                    headers: {
                        "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                    },
                    data: {
                        consultationrefno: $("#consultationrefno").val(),
                        requestrefno: $btn.data("code") || $btn.val(),
                        prodcode: $btn.data("code") || $btn.val()
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Diagnostic request removed',
                                showConfirmButton: false,
                                timer: 2000
                            }).then(() => {
                                $("#diagnostics_table").DataTable().ajax.reload();
                                if ($.fn.DataTable.isDataTable("#charges_table")) {
                                    $("#charges_table").DataTable().ajax.reload();
                                }
                            });
                        }
                    },
                    complete: function () {
                        $btn.prop("disabled", false).html('<i class="fa-solid fa-trash"></i> Remove');
                    }
                });
            }
        });
    });

    // Radiology and Laboratory Upload
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
            url: "/api/fetch_radlab_files",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
            },
            data: {
                consultationrefno: $("#consultationrefno").val()
            },
            success: function (response) {
                if (response && response.files) {
                    if (response.files.radiologypath) {
                        $("#radiology_hasfile").removeClass("d-none").addClass("d-inline-block");
                        $("#preview_radiology").prop("disabled", false);
                        // Detailed Comment: Use the generated full URL link if available, falling back to relative storage path
                        const radLink = response.files.radiology_url || response.files.radiology_link || (response.links ? response.links.radiology : null) || response.files.radiologypath;
                        $("#preview_radiology").attr("data-filepath", radLink);
                    }

                    if (response.files.laboratorypath) {
                        $("#laboratory_hasfile").removeClass("d-none").addClass("d-inline-block");
                        $("#preview_laboratory").prop("disabled", false);
                        // Detailed Comment: Use the generated full URL link if available, falling back to relative storage path
                        const labLink = response.files.laboratory_url || response.files.laboratory_link || (response.links ? response.links.laboratory : null) || response.files.laboratorypath;
                        $("#preview_laboratory").attr("data-filepath", labLink);
                    }
                }
            }
        });
    }

    // Detailed Comment: Upload consultation diagnostic documents with button loading spinner
    $("#update_files_btn").on("click", function () {
        const $btn = $(this);
        setBtnLoading($btn, "Uploading...");

        let formData = new FormData($("#rad_lab_form")[0]);
        formData.append("consultationrefno", $("#consultationrefno").val());

        $.ajax({
            url: "/api/upload_consultation_files",
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
                        title: 'Files saved successfully',
                        showConfirmButton: false,
                        timer: 2000
                    });
                    // Detailed Comment: Reload medical files metadata to enable preview buttons with updated paths
                    loadMedicalFiles();
                }
            },
            complete: function () {
                resetBtnLoading($btn);
            }
        });
    });

    $("#radiology_result").on("change", function () {
        if (this.files && this.files.length) {
            $("#preview_radiology").prop("disabled", false);
        }
    });

    $("#laboratory_result").on("change", function () {
        if (this.files && this.files.length) {
            $("#preview_laboratory").prop("disabled", false);
        }
    });

    // Detailed Comment: Handle preview modal show event, switching between image preview and iframe document viewer
    $("#preview_modal").on("show.bs.modal", function () {
        const src = this.dataset.src || "";
        const isImage = this.dataset.isimage === "1";
        const fileName = this.dataset.filename || "Document Preview";

        $("#preview_modal .modal-title").text(fileName);

        const docPreview = document.getElementById("docPreview");
        const imgPreview = document.getElementById("imgPreview");
        const downloadBtn = document.getElementById("preview_download_btn");

        if (downloadBtn) {
            downloadBtn.href = src;
            downloadBtn.download = fileName;
            downloadBtn.classList.remove("d-none");
        }

        if (isImage) {
            if (docPreview) {
                docPreview.src = "";
                docPreview.style.display = "none";
            }
            if (imgPreview) {
                imgPreview.src = src;
                imgPreview.classList.remove("d-none");
            }
        } else {
            if (imgPreview) {
                imgPreview.src = "";
                imgPreview.classList.add("d-none");
            }
            if (docPreview) {
                docPreview.src = src;
                docPreview.style.display = "block";
            }
        }
    });

    // Detailed Comment: Reset preview modal contents and restore consultation modal upon closing
    $("#preview_modal").on("hidden.bs.modal", function () {
        const docPreview = document.getElementById("docPreview");
        if (docPreview) {
            docPreview.src = "";
            docPreview.style.display = "none";
        }
        const imgPreview = document.getElementById("imgPreview");
        if (imgPreview) {
            imgPreview.src = "";
            imgPreview.classList.add("d-none");
        }

        const consulModal = bootstrap.Modal.getOrCreateInstance(document.getElementById("consultation_modal"));
        consulModal.show();
    });

    // Detailed Comment: Preview Radiology or Laboratory file (local file or server-stored path)
    $("[id^=preview_]").on("click", function () {
        const type = this.id.replace("preview_", "");
        const fileInput = document.getElementById(type + "_result");
        const storedPath = $(this).attr("data-filepath") || this.dataset.filepath;

        let fileURL = "";
        let fileName = "";
        let isImage = false;

        if (fileInput && fileInput.files && fileInput.files.length > 0) {
            const file = fileInput.files[0];
            fileURL = URL.createObjectURL(file);
            fileName = file.name;
            isImage = (file.type && file.type.startsWith("image/")) || /\.(jpe?g|png|gif|webp|bmp|svg)$/i.test(file.name);
        } else if (storedPath && storedPath.trim() !== "") {
            // Detailed Comment: Detect if the application is running under an Apache project subfolder (e.g. /kayakapmd_clinic)
            const pathSegments = window.location.pathname.split("/").filter(Boolean);
            const knownRoutes = ["doctor", "secretary", "admin", "login", "register", "home", "preview-file"];
            let projectPrefix = "";
            if (pathSegments.length > 0 && !knownRoutes.includes(pathSegments[0])) {
                projectPrefix = "/" + pathSegments[0];
            }

            if (storedPath.startsWith("http://") || storedPath.startsWith("https://")) {
                try {
                    const urlObj = new URL(storedPath);
                    // If on an Apache subfolder and the URL path lacks the project prefix, inject it
                    if (projectPrefix && !urlObj.pathname.startsWith(projectPrefix)) {
                        urlObj.pathname = projectPrefix + urlObj.pathname;
                    }
                    fileURL = urlObj.toString();
                } catch (e) {
                    fileURL = storedPath;
                }
            } else if (storedPath.startsWith("/")) {
                fileURL = (projectPrefix && !storedPath.startsWith(projectPrefix)) ? (projectPrefix + storedPath) : storedPath;
            } else {
                fileURL = (projectPrefix ? projectPrefix : "") + "/preview-file/" + encodeURIComponent(storedPath).replace(/%2F/g, '/');
            }
            fileName = storedPath.split("/").pop().split("?")[0];
            isImage = /\.(jpe?g|png|gif|webp|bmp|svg)$/i.test(fileName);
        }

        if (!fileURL) {
            Swal.fire({
                title: "No Document",
                text: "No file selected or uploaded to preview.",
                icon: "info"
            });
            return;
        }

        const consulModal = bootstrap.Modal.getInstance(document.getElementById("consultation_modal"));
        if (consulModal) {
            consulModal.hide();
        }

        const previewModalEl = document.getElementById("preview_modal");
        previewModalEl.dataset.src = fileURL;
        previewModalEl.dataset.isimage = isImage ? "1" : "0";
        previewModalEl.dataset.filename = fileName;
        const previewModal = bootstrap.Modal.getOrCreateInstance(previewModalEl);
        previewModal.show();
    });

    // Load patient charges modal
    $("#search_charge").select2({
        width: '50%',
        dropdownParent: $("#append_charge_modal"),
        ajax: {
            url: "/api/fetch_all_charges",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: function (params) { 
                return { 
                    term: params.term, 
                    category: $("#search_filter").val(),
                    consultationrefno: $("#consultationrefno").val() 
                }; 
            },
            processResults: function (response) {
                // Detailed Comment: Differentiate displayed price in charge search by patient type (PHIC YAKAP/PHIC, HMO, Others, Regular)
                const pxType = ($("#doctor_modal_patient_type_badge").text() || "REGULAR").trim().toUpperCase();
                const isPhic = pxType.includes("PHIC");
                const isHmo = pxType === "HMO";
                const isOthers = pxType === "OTHERS";

                return {
                    results: $.map(response.charges, function (i) {
                        let tierPrice = i.price_regular;
                        if (isPhic && parseFloat(i.price_phic) > 0) {
                            tierPrice = i.price_phic;
                        } else if (isHmo && parseFloat(i.price_hmo) > 0) {
                            tierPrice = i.price_hmo;
                        } else if (isOthers && parseFloat(i.price_others) > 0) {
                            tierPrice = i.price_others;
                        }

                        return {
                            id: i.prodcode,
                            text: `${i.prod_itemdscr} (₱${parseFloat(tierPrice || 0).toFixed(2)})`,
                            price: tierPrice
                        }
                    })
                }
            }
        },
        placeholder: "Search charges..."
    });

    $("#search_charge").on("select2:select", function () {
        const prodcode = this.value;
        const charge_amt = $("#charge_amount");
        const pxType = ($("#doctor_modal_patient_type_badge").text() || "REGULAR").trim().toUpperCase();
        const pTypeParam = pxType.includes("PHIC") ? "PHIC" : (pxType === "HMO" ? "HMO" : (pxType === "OTHERS" ? "OTHERS" : "REGULAR"));

        // Detailed Comment: Fetch tier price passing explicit patient_type so backend reliably computes PHIC/HMO/Others price
        $.ajax({
            url: "/api/get_hmo_price",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { 
                prodcode: prodcode, 
                consultationrefno: $("#consultationrefno").val(),
                patient_type: pTypeParam
            },
            success: function (response) {
                if (response.success) {
                    charge_amt.val(response.price);
                    return;
                }

                charge_amt.val("");
            }
        });
    });

    $("#append_charge_modal").on("shown.bs.modal", function () {
        $("#charge_amount").val(0);
        $("#charge_qty").val(1);
    });

    // Detailed Comment: Maintain body.modal-open so parent consultation_modal remains scrollable and interactive
    $("#append_charge_modal").on("hidden.bs.modal", function () {
        if ($("#consultation_modal").is(":visible") || $("#consultation_modal").hasClass("show")) {
            $("body").addClass("modal-open");
        } else {
            const consulModal = bootstrap.Modal.getOrCreateInstance(document.getElementById("consultation_modal"));
            consulModal.show();
        }
    });

    // Detailed Comment: Open append charges modal as a stacked child modal, displaying existing charges as reference
    // while keeping appendedCharges array strictly for newly added items
    $("#append_charge_btn, #append_charge_btn_2").on("click", function () {
        $("#search_charge").val(null).trigger("change");
        $("#appended_charges_table tbody").empty();
        appendedCharges = [];

        const appendModalEl = document.getElementById("append_charge_modal");
        const appendModal = bootstrap.Modal.getOrCreateInstance(appendModalEl);
        appendModal.show();

        $.ajax({
            url: "/api/fetch_patient_charges",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $("meta[name='csrf-token']").attr("content") },
            data: { consultationrefno: $("#consultationrefno").val() },
            success: function (response) {
                if (response.charges && response.charges.length > 0) {
                    response.charges.forEach(c => {
                        const lineTotal = parseFloat(c.totalamt || c.amount || 0);
                        const displayTotal = isNaN(lineTotal) ? '0.00' : lineTotal.toFixed(2);

                        const newRow = `
                            <tr data-refno="${c.prodcode}">
                                <td class="align-middle text-center text-nowrap">
                                    <button type="button"
                                        class="btn btn-sm btn-secondary"
                                        disabled
                                        data-refno="${c.prodcode}"
                                        title="Existing charges can only be removed from the patient charges tab."
                                    >
                                        <i class="fa-solid fa-floppy-disk"></i>
                                    </button>
                                </td>

                                <td class="align-middle text-nowrap">${c.item_dscr}</td>
                                <td class="align-middle text-nowrap">${c.qty}</td>
                                <td class="align-middle text-nowrap">${displayTotal}</td>
                            </tr>
                        `;

                        $("#appended_charges_table tbody").append(newRow);
                    });
                } else {
                    $("#appended_charges_table tbody").append(`
                        <tr class="no-charges-placeholder">
                            <td class="align-middle text-center text-nowrap" colspan="4">No charges yet.</td>
                        </tr>
                    `);
                }
            }
        });
    });

    $("#charge_category").on("change", function () { cache = null; });

    let appendedCharges = [];
    // Detailed Comment: Dynamically append new charge entry with calculated line total (qty * unit price)
    $("#append_to_charges_btn").on("click", function () {
        const form = document.getElementById("appended_charges_form");

        if (!form.checkValidity()) return form.reportValidity();

        const search = $("#search_charge");
        const description = $("#search_charge option:selected").text();
        const amount = $("#charge_amount").val();
        const quantity = $("#charge_qty").val();

        if (search.val() == "" || search.val() == null) {
            return Swal.fire({
                title: "Reminder!",
                text: "Please select a charge from the list.",
                icon: "error"
            });
        }

        if (quantity == 0) {
            return Swal.fire({
                title: "Reminder!",
                text: "Quantity must be higher than 0.",
                icon: "error"
            });
        }

        if (appendedCharges.some(item => item && item.prodcode === search.val()) || $(`#appended_charges_table tr[data-refno="${search.val()}"]`).length > 0) {
            return Swal.fire({
                title: "Reminder!",
                text: "This charge has already been appended or is already listed.",
                icon: "error"
            });
        }

        let table = $("#appended_charges_table tbody");
        table.find(".no-charges-placeholder").remove();
        if (appendedCharges.length == 0 && table.find("tr[data-refno]").length === 0) {
            table.empty();
        }

        const unitPrice = parseFloat(amount || 0);
        const qty = parseFloat(quantity || 1);
        const lineTotal = (unitPrice * qty).toFixed(2);

        appendedCharges.push({
            prodcode: search.val(),
            quantity: quantity,
            amount: unitPrice
        });

        const newRow = `
            <tr data-refno="${search.val()}">
                <td class="align-middle text-center text-nowrap">
                    <button type="button" class="btn btn-sm btn-danger charge_entry" data-refno="${search.val()}">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </td>
                <td class="align-middle">${description}</td>
                <td>${quantity}</td>
                <td>${lineTotal}</td>
            </tr>
        `;

        table.append(newRow);
        search.val(null).trigger("change");

        $("#charge_amount").val(0);
        $("#charge_qty").val(1);
    });

    $(document).on("click", ".charge_entry", function () {
        const prodCode = $(this).data("refno");

        appendedCharges = appendedCharges.filter(item => item && item.prodcode !== prodCode);

        $(this).closest("tr").remove();

        if (appendedCharges.length === 0 && $("#appended_charges_table tbody tr").length === 0) {
            $("#appended_charges_table tbody").append(`
                <tr class="no-charges-placeholder">
                    <td class="align-middle text-center text-nowrap" colspan="4">No charges yet.</td>
                </tr>
            `);
        }
    });

    // Detailed Comment: Save all appended charges with button loading spinner, restore consultation_modal state,
    // and reload the patient charges DataTable
    $("#save_charges_btn").on("click", function () {
        if (appendedCharges.length === 0) {
            return Swal.fire({
                title: "Error",
                text: "Please append at least one new charge before saving.",
                icon: "error"
            });
        }

        const $btn = $(this);
        setBtnLoading($btn, "Saving...");

        $.ajax({
            url: "/api/save_patient_charges",
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
                    $("#charges_table").DataTable().ajax.reload();
                    loadRx();

                    const appendModal = bootstrap.Modal.getInstance(document.getElementById("append_charge_modal"));
                    if (appendModal) appendModal.hide();

                    // Maintain consultation_modal visibility and scrolling
                    $("body").addClass("modal-open");
                    const consulModal = bootstrap.Modal.getOrCreateInstance(document.getElementById("consultation_modal"));
                    consulModal.show();
                    $("#patientChargeTab-tab").trigger("click");
                } else {
                    Swal.fire({
                        title: "Error",
                        html: response.message,
                        icon: "error"
                    });
                }
            },
            complete: function () {
                resetBtnLoading($btn);
            }
        });
    });

    // Detailed Comment: Load Patient Charges tab with defensive null-safety and NaN prevention on total sums
    $("#patient_charge_tab_btn").on("click", function () {
        $("#charges_table").DataTable().destroy().clear();
        $("#charges_table").DataTable({
            ajax: {
                url: "/api/fetch_patient_charges",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: { consultationrefno: $("#consultationrefno").val() },
                dataSrc: function (response) {
                    let total = 0;

                    if (response && response.charges) {
                        response.charges.forEach(charge => {
                            const val = parseFloat(charge.totalamt || charge.amount || 0);
                            if (!isNaN(val)) {
                                total += val;
                            }
                        });
                    }

                    // Detailed Comment: Set total formatted to 2 decimals without prepending ₱ symbol, as the header template already provides ₱
                    $("#charges_total").text(total.toFixed(2));

                    return response.charges || [];
                }
            },
            columns: [
                {
                    data: null,
                    render: function (data) {
                        // Detailed Comment: Pass both sanitized charge id and prodcode to prevent string 'null' issues
                        const chargeId = (data.id && data.id !== 'null') ? data.id : '';
                        const prodcode = (data.prodcode && data.prodcode !== 'null') ? data.prodcode : '';
                        return `
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-danger remove_charge_btn" 
                                    data-id="${chargeId}" 
                                    data-prodcode="${prodcode}" 
                                    value="${chargeId}"><i class="fa-solid fa-trash"></i></button>
                                <button class="btn btn-sm btn-primary edit_charge_btn" 
                                    data-id="${chargeId}" 
                                    data-prodcode="${prodcode}" 
                                    value="${chargeId}"><i class="fa-solid fa-pen-to-square"></i></button>
                            </div>
                            `;
                    }
                },
                {
                    data: 'item_dscr',
                    render: function (data, type, row) {
                        const title = (data || 'Item / Service').replace(/"/g, '&quot;');
                        // Detailed Comment: Enriched display for Professional Fee showing doctor billing rates (PF, Vatable, +VAT, W/Tax, ROD)
                        if (row.is_pf || row.prodcode === 'PF' || row.item_grouping === 'PROFESSIONAL FEE') {
                            const pfRate = parseFloat(row.pf_rate || row.cost_ave || 0).toFixed(2);
                            const rodRate = parseFloat(row.rod_rate || 0).toFixed(2);
                            const taxPct = parseFloat(row.tax_percent || 0).toFixed(1);
                            const isVatable = Boolean(row.vatable);
                            const isAutoAddVat = Boolean(row.auto_add_vat);
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
                            const regPrice = parseFloat(row.regular_price || 0);
                            const curPrice = parseFloat(row.cost_ave || row.sellingprice || 0);
                            const tier = row.posted_tier || 'REGULAR';
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
                { data: 'qty' },
                {
                    data: 'cost_ave',
                    render: function (data) {
                        const price = parseFloat(data || 0);
                        return isNaN(price) ? '0.00' : price.toFixed(2);
                    }
                },
                {
                    data: 'totalamt',
                    render: function (data) {
                        const amt = parseFloat(data || 0);
                        return isNaN(amt) ? '0.00' : amt.toFixed(2);
                    }
                }
            ],
            columnDefs: [
                {
                    target: 0,
                    width: '1%',
                    orderable: false,
                    className: 'text-nowrap text-center align-middle'
                },
                {
                    targets: [1, 2, 3, 4],
                    className: 'align-middle'
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

    // Detailed Comment: Remove charge with button loading state and multi-identifier fallback
    $(document).on("click", ".remove_charge_btn", function () {
        const $btn = $(this);
        const chargeId = $btn.data("id") || $btn.val();
        const prodcode = $btn.data("prodcode");
        const consultationrefno = $("#consultationrefno").val();

        Swal.fire({
            title: "Remove Charge?",
            text: "Are you sure you want to remove this charge from the patient's consultation?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Confirm"
        }).then((result) => {
            if (result.isConfirmed) {
                $btn.prop("disabled", true).html('<i class="fa-solid fa-spinner fa-spin"></i>');

                const payload = { consultationrefno: consultationrefno };
                if (chargeId && chargeId !== 'null' && chargeId !== 'undefined') {
                    payload.chargeid = chargeId;
                }
                if (prodcode && prodcode !== 'null' && prodcode !== 'undefined') {
                    payload.prodcode = prodcode;
                }

                $.ajax({
                    url: "/api/delete_patient_charge",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: payload,
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                title: "Success",
                                text: "Charge successfully removed.",
                                icon: "success"
                            }).then(() => {
                                $("#charges_table").DataTable().ajax.reload();
                                loadRx();
                            });
                        }
                    },
                    complete: function () {
                        $btn.prop("disabled", false).html('<i class="fa-solid fa-trash"></i>');
                    }
                });
            }
        });
    });

    // Detailed Comment: Edit Patient Charge (Professional Fee, procedure, supplies, etc.)
    // Displays doctor fee billing rates or catalog price tiers comparison without discount field
    $(document).on("click", ".edit_charge_btn", function () {
        const $btn = $(this);
        const chargeId = $btn.data("id") || $btn.val() || "";
        const prodcode = $btn.data("prodcode") || "";
        const $row = $btn.closest("tr");
        const rowData = $("#charges_table").DataTable().row($row).data() || {};

        const currentItemName = rowData.item_dscr || "Item / Service";
        const currentQty = parseFloat(rowData.qty || 1) || 1;
        const currentPrice = parseFloat(rowData.cost_ave || rowData.retails || 0) || 0;
        const isPf = Boolean(rowData.is_pf || rowData.prodcode === 'PF' || rowData.item_grouping === 'PROFESSIONAL FEE');

        const consulModalEl = document.getElementById("consultation_modal");
        const consulModal = bootstrap.Modal.getInstance(consulModalEl);
        if (consulModal) {
            consulModal.hide();
        }

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
            title: `Edit Charge`,
            html: `
                <div class="mb-3 text-start">
                    <span class="fw-bold text-primary fs-6">${currentItemName}</span>
                </div>
                ${detailsCardHtml}
                <div class="text-start">
                    <label class="form-label fw-bold small" for="charge_input_sw">Unit Price (₱)</label>
                    <input class="form-control form-control-lg" type="number" step="0.01" min="0" name="charge_input_sw" id="charge_input_sw" value="${currentPrice.toFixed(2)}">
                </div>
            `,
            confirmButtonText: "Update Charge",
            showCancelButton: true,
            cancelButtonText: "Cancel",
            preConfirm: () => {
                const charge = document.getElementById("charge_input_sw").value;

                if (!charge || isNaN(parseFloat(charge)) || parseFloat(charge) < 0) {
                    Swal.showValidationMessage("Please enter a valid unit price/fee.");
                    return false;
                }

                return {
                    charge_fee: parseFloat(charge),
                    charge_qty: currentQty || 1,
                    discount: 0
                };
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                if (consulModal) {
                    consulModal.show();
                    $("#patientChargeTab-tab").trigger("click");
                    $("body").addClass("modal-open");
                }

                $.ajax({
                    url: "/api/update_charge",
                    type: "POST",
                    headers: {
                        "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                    },
                    data: {
                        consultationrefno: $("#consultationrefno").val(),
                        chargeid: chargeId,
                        prodcode: prodcode,
                        charge_fee: result.value.charge_fee,
                        charge_qty: result.value.charge_qty,
                        discount: result.value.discount
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Charge updated successfully',
                                showConfirmButton: false,
                                timer: 2000
                            });

                            $("#charges_table").DataTable().ajax.reload(null, false);
                            $("#patientChargeTab-tab").trigger("click");
                            loadRx();
                        } else {
                            Swal.fire({
                                title: "Update Failed",
                                text: response.message || "Failed to update charge.",
                                icon: "error"
                            });
                        }
                    },
                    error: function (xhr) {
                        Swal.fire({
                            title: "Error",
                            text: (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "An error occurred while updating charge.",
                            icon: "error"
                        });
                    }
                });
            } else {
                if (consulModal) {
                    consulModal.show();
                    $("#patientChargeTab-tab").trigger("click");
                    $("body").addClass("modal-open");
                }
            }
        });
    });
});
