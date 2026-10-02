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
                        return `<button type="button" class="btn btn-sm btn-outline-primary view-past-consultation-btn" data-index="${meta.row}" data-consultationrefno="${row.consultationrefno || ''}" title="View Past Consultation Details">
                            <i class="fa-solid fa-eye me-1"></i> View
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
     * Detailed Comment: Populates and displays the dedicated Consultation Details Viewer modal
     * with complete historical clinical data (demographics, vitals, impressions, Rx, diagnostics, charges).
     */
    function populateAndShowConsultationDetails(record) {
        if (!record) return;

        const dateStr = record.consultation_date ? record.consultation_date.split(' ')[0] : 'N/A';
        const cref = record.consultationrefno || 'N/A';
        const status = (record.status || 'PENDING').toUpperCase();
        const ptype = (record.classification || (record.hmocode ? 'HMO' : (record.phic_pin ? 'PHIC' : 'REGULAR'))).toUpperCase();

        const statusBadges = {
            COMPLETED: "bg-success text-white",
            DONE: "bg-success text-white",
            PENDING: "bg-warning text-dark",
            CANCELLED: "bg-danger text-white"
        };
        const typeBadges = {
            REGULAR: "bg-warning text-dark",
            PHIC: "bg-primary text-white",
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

        // 2. Patient Demographics & Vitals
        $("#vcd_docname").text(record.docname ? `Dr. ${record.docname}` : (record.docrefno || 'None Assigned'));
        $("#vcd_photo").attr('src', record.photo_path || '/images/blank_photo.png');
        $("#vcd_name").text(record.patientname || 'N/A');
        $("#vcd_pincode").text(record.pincode || 'N/A');
        $("#vcd_pxrefno").text(record.pxrefno || 'N/A');
        $("#vcd_sex").text(record.gender || 'N/A');
        $("#vcd_bday").text(record.birthday || 'N/A');
        $("#vcd_age").text(record.age ? `${record.age} yrs` : 'N/A');
        $("#vcd_cellno").text(record.mobilenumber || 'N/A');
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

        if (parseInt(record.foradmit || record.isforadmission) === 1) {
            $("#vcd_admit_badge").text('For Admission').removeClass('bg-secondary').addClass('bg-danger');
        } else {
            $("#vcd_admit_badge").text('Not for Admission').removeClass('bg-danger').addClass('bg-secondary');
        }
        $("#vcd_admit_instructions").val(record.foradmit_instructions || record.instructions || '');

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
        const rxItems = record.rx_items || [];
        if (rxItems.length === 0) {
            $rxTbody.append('<tr><td colspan="4" class="text-center text-muted">No prescriptions recorded.</td></tr>');
        } else {
            rxItems.forEach(item => {
                $rxTbody.append(`
                    <tr>
                        <td class="fw-semibold">${item.item_dscr || item.medname || 'N/A'}</td>
                        <td>${item.remarks || item.sig || 'N/A'}</td>
                        <td class="text-center">${item.qty || 1}</td>
                        <td class="text-center">${item.dispensed_flag == 1 ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>'}</td>
                    </tr>
                `);
            });
        }
        $("#vcd_general_instructions").val(record.instructions || '');

        // 5. Tab 3: Diagnostic Requests
        const $diagTbody = $("#vcd_diagnostics_table tbody");
        $diagTbody.empty();
        const diagItems = record.diagnostic_items || [];
        if (diagItems.length === 0) {
            $diagTbody.append('<tr><td colspan="2" class="text-center text-muted">No diagnostic requests recorded.</td></tr>');
        } else {
            diagItems.forEach(item => {
                $diagTbody.append(`
                    <tr>
                        <td class="fw-semibold">${item.item_dscr || item.requestname || 'N/A'}</td>
                        <td><span class="badge bg-info text-white">${item.item_grouping || item.category || 'DIAGNOSTIC'}</span></td>
                    </tr>
                `);
            });
        }

        // 6. Tab 4: Radiology & Laboratory
        if (record.radiologypath || record.radiology_url) {
            const radUrl = record.radiology_url || record.radiologypath;
            $("#vcd_rad_status").html('<span class="text-success"><i class="fa-solid fa-check-circle me-1"></i> Document attached</span>');
            $("#vcd_rad_preview_btn").attr('href', radUrl).removeClass('d-none');
        } else {
            $("#vcd_rad_status").text('No radiology file uploaded.');
            $("#vcd_rad_preview_btn").attr('href', '#').addClass('d-none');
        }

        if (record.laboratorypath || record.laboratory_url) {
            const labUrl = record.laboratory_url || record.laboratorypath;
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
                const qty = parseFloat(c.qty) || 1;
                const unitPrice = parseFloat(c.cost_ave || c.retails || 0);
                const subTotal = parseFloat(c.totalamt || (qty * unitPrice));
                totalAmt += subTotal;

                $chargesTbody.append(`
                    <tr>
                        <td class="fw-semibold">${c.item_dscr || 'N/A'}</td>
                        <td><span class="badge bg-secondary">${c.item_grouping || 'CHARGE'}</span></td>
                        <td class="text-center">${qty}</td>
                        <td class="text-end">PHP ${unitPrice.toFixed(2)}</td>
                        <td class="text-end fw-bold">PHP ${subTotal.toFixed(2)}</td>
                    </tr>
                `);
            });
        }
        $("#vcd_charges_total").text(totalAmt.toFixed(2));

        // Activate first vertical tab (Impressions & Diagnosis)
        const firstTabEl = document.querySelector('button[data-bs-target="#vcd_pane_impdiag"]');
        if (firstTabEl && window.bootstrap && window.bootstrap.Tab) {
            window.bootstrap.Tab.getOrCreateInstance(firstTabEl).show();
        }

        // Show the details modal
        const modalEl = document.getElementById("view_consultation_details_modal");
        if (modalEl && window.bootstrap && window.bootstrap.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }
    window.populateAndShowConsultationDetails = populateAndShowConsultationDetails;

    /**
     * Detailed Comment: Delegate click handler to open the consultation details viewer modal
     * when clicking "View" on any past consultation row in the Consultation History DataTable.
     */
    $(document).on("click", ".view-past-consultation-btn", function () {
        const idx = $(this).data("index");
        const refno = $(this).data("consultationrefno");
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
                        populateAndShowConsultationDetails(list[0]);
                    }
                }
            });
        }
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
                }
            }
        });
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
            data: function (params) { return { term: params.term, category: $("#search_filter").val() }; },
            processResults: function (response) {
                // Detailed Comment: Differentiate displayed price in charge search by patient type (PHIC, HMO, Others, Regular)
                const pxType = ($("#doctor_modal_patient_type_badge").text() || "REGULAR").trim().toUpperCase();
                return {
                    results: $.map(response.charges, function (i) {
                        let tierPrice = i.price_regular;
                        if (pxType === "PHIC" && parseFloat(i.price_phic) > 0) {
                            tierPrice = i.price_phic;
                        } else if (pxType === "HMO" && parseFloat(i.price_hmo) > 0) {
                            tierPrice = i.price_hmo;
                        } else if (pxType === "OTHERS" && parseFloat(i.price_others) > 0) {
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

        $.ajax({
            url: "/api/get_hmo_price",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { prodcode: prodcode, consultationrefno: $("#consultationrefno").val() },
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
                { data: 'item_dscr' },
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
    // Captures current row data from DataTable, populates Swal form, and posts updates to /api/update_charge
    $(document).on("click", ".edit_charge_btn", function () {
        const $btn = $(this);
        const chargeId = $btn.data("id") || $btn.val() || "";
        const prodcode = $btn.data("prodcode") || "";
        const $row = $btn.closest("tr");
        const rowData = $("#charges_table").DataTable().row($row).data() || {};

        const currentItemName = rowData.item_dscr || "Item / Service";
        const currentQty = parseFloat(rowData.qty || 1) || 1;
        const currentPrice = parseFloat(rowData.cost_ave || rowData.retails || 0) || 0;
        const currentDiscount = parseFloat(rowData.discount || 0) || 0;

        const consulModalEl = document.getElementById("consultation_modal");
        const consulModal = bootstrap.Modal.getInstance(consulModalEl);
        if (consulModal) {
            consulModal.hide();
        }

        Swal.fire({
            title: `Edit Charge`,
            html: `
                <div class="mb-3 text-start">
                    <span class="fw-bold text-primary">${currentItemName}</span>
                </div>
                <div class="row g-2 text-start">
                    <div class="col-4">
                        <label class="form-label fw-bold small" for="charge_qty">Quantity</label>
                        <input class="form-control" type="number" step="any" min="0.01" name="charge_qty" id="charge_qty" value="${currentQty}">
                    </div>

                    <div class="col-4">
                        <label class="form-label fw-bold small" for="charge_input_sw">Unit Price (₱)</label>
                        <input class="form-control" type="number" step="0.01" min="0" name="charge_input_sw" id="charge_input_sw" value="${currentPrice.toFixed(2)}">
                    </div>

                    <div class="col-4">
                        <label class="form-label fw-bold small" for="discount_input_sw">Discount (₱)</label>
                        <input class="form-control" type="number" step="0.01" min="0" name="discount_input_sw" id="discount_input_sw" value="${currentDiscount.toFixed(2)}">
                    </div>
                </div>
            `,
            confirmButtonText: "Update Charge",
            showCancelButton: true,
            cancelButtonText: "Cancel",
            preConfirm: () => {
                const charge = document.getElementById("charge_input_sw").value;
                const qty = document.getElementById("charge_qty").value;

                if (!charge || isNaN(parseFloat(charge)) || parseFloat(charge) < 0) {
                    Swal.showValidationMessage("Please enter a valid unit price/fee.");
                    return false;
                }
                if (!qty || isNaN(parseFloat(qty)) || parseFloat(qty) <= 0) {
                    Swal.showValidationMessage("Please enter a valid quantity greater than zero.");
                    return false;
                }

                return {
                    charge_fee: parseFloat(charge),
                    charge_qty: parseFloat(qty),
                    discount: parseFloat(document.getElementById("discount_input_sw").value || 0)
                };
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                if (consulModal) {
                    consulModal.show();
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

                            $("#charges_table").DataTable().ajax.reload();
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
                }
            }
        });
    });
});
