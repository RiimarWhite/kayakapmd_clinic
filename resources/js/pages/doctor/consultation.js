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

    /**
     * Detailed Comment: Suppress intrusive DataTables native alert popups across the consultation page
     * and redirect internal warnings to the console for structured debugging.
     */
    if ($.fn.dataTable) {
        $.fn.dataTable.ext.errMode = function (settings, helpPage, message) {
            console.warn("DataTables warning:", message);
        };
    }

    // Init consultation date and load patients
    $("#consul_date").val(new Date().toISOString().split('T')[0]);
    getPatientsFromDate($("#consul_date").val());

    function getPatientsFromDate(value, $btn = null) {
        if ($btn) setBtnLoading($btn, "");
        if ($.fn.DataTable.isDataTable("#consultation_table")) {
            $("#consultation_table").DataTable().clear().destroy();
            $("#consultation_table tbody").empty();
        }
        $("#consultation_table").DataTable({
            processing: true,
            ajax: {
                url: "/api/fetch_doctor_consultations",
                type: "POST",
                data: { date: value },
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                dataSrc: 'patients',
                complete: function () {
                    if ($btn) resetBtnLoading($btn);
                }
            },
            select: { style: 'single' },
            columns: [
                { data: null, render: function (data, type, row) { return `<span data-consultation="${row.consultationrefno}">${data.patientname}</span>`; } },
                { data: 'status', render: function (data) {
                    let b = 'bg-secondary fs-6';
                    if (data === 'WAITING') b = 'bg-warning text-white text-dark fs-6';
                    if (data === 'IN_CONSULTATION') b = 'bg-info text-white fs-6';
                    if (data === 'FOR_BILLING') b = 'bg-primary text-white fs-6';
                    if (data === 'COMPLETED') b = 'bg-success fs-6';
                    if (data === 'UNSCHEDULED') b = 'bg-primary fs-6';
                    if (data === 'CANCELLED' || data === 'NO_SHOW') b = 'bg-danger fs-6';
                    return `<span class="badge ${b}">${data}</span>`;
                }}
            ],
            columnDefs: [{ target: 1, width: '1%', orderable: false, searchable: false, className: 'text-nowrap text-center align-middle' }],
            language: { emptyTable: "No patients yet." },
            lengthChange: false, paging: true, searching: false, ordering: false, responsive: true, info: false
        });
    }

    $("#consul_date").on("change", function () { getPatientsFromDate($(this).val()); });
    $("#previous").on("click", function () {
        const $btn = $(this);
        let d = new Date($("#consul_date").val()); d.setDate(d.getDate() - 1);
        $("#consul_date").val(d.toISOString().split("T")[0]); getPatientsFromDate($("#consul_date").val(), $btn);
    });
    $("#next").on("click", function () {
        const $btn = $(this);
        let d = new Date($("#consul_date").val()); d.setDate(d.getDate() + 1);
        $("#consul_date").val(d.toISOString().split("T")[0]); getPatientsFromDate($("#consul_date").val(), $btn);
    });

    let rowConsultationRefno = null;
    $("#consultation_table").on("select.dt", function (e, dt, type, indexes) {
        let rowData = dt.row(indexes[0]).data();
        rowConsultationRefno = rowData.consultationrefno;
        $.ajax({
            url: "/api/fetch_patient_data",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr('content') },
            data: { consultationrefno: rowConsultationRefno },
            success: function (response) {
                const p = response.patient;

                $("#px_photo_preview").prop("src", (p.photo_path != null ? p.photo_path : "/images/blank_photo.png"));
                $("#pxname").text(p.patientname);
                $("#pxsex").text(p.gender);
                $("#pxbday").text(new Date(p.birthday.replace(" ", "T")).toLocaleDateString("en-US", { month: "long", day: "numeric", year: "numeric" }));
                $("#pxage").text(calculateAge(p.birthday));
                $("#pxcellno").text(p.mobilenumber);
                $("#pxemail").text(p.emailaddress);
                $("#pxaddress").text(p.address);
                $("#pxweight").text(p.weight != null ? p.weight + p.wunit : '');
                $("#pxheight").text(p.height != null ? p.height + p.hunit : '');
                $("#pxtemp").text(p.temp != null ? p.temp + "°" + p.tempunit : '');
                $("#pxresprate").text(p.respiratoryrate);
                $("#pxpulserate").text(p.pulserate);
                $("#pxbp").text(p.bpnumerator + '/' + p.bpdenominator);
                $("#rfc").val(p.reasonforconsultation ?? '');
            }
        });
    });

    $("#consultation_table").on("deselect.dt", function () {
        rowConsultationRefno = null;
        $("#px_photo_preview").prop("src", "/images/blank_photo.png");
        $("#pxname, #pxsex, #pxbday, #pxage, #pxcellno, #pxemail, #pxaddress").text("");
        $("#pxweight, #pxheight, #pxtemp, #pxresprate, #pxpulserate, #pxbp").text("");
        $("#rfc").val("");
    });

    function calculateAge(birthday) {
        const birthDate = new Date(birthday);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) age--;
        return age;
    }

    // Detailed Comment: Proceed consultation button click handler with loading spinner
    $("#consult").on("click", function () {
        if (!$("#consultation_table").DataTable().rows({ selected: true }).any()) {
            return Swal.fire({
                title: "Missing Patient",
                text: "Please select a patient.",
                icon: "warning"
            });
        }

        const $btn = $(this);
        setBtnLoading($btn, "Loading...");
        loadConsultModal($btn);
    });

    function loadConsultModal($btn = null) {
        if (!$("#consultation_table").DataTable().rows({ selected: true }).any()) {
            if ($btn) resetBtnLoading($btn);
            return;
        }

        const consultationModal = new bootstrap.Modal("#consultation_modal");
        $.ajax({
            url: "/api/fetch_patient_data",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { consultationrefno: rowConsultationRefno },
            success: function (response) {
                if (response.patient != null) {
                    const p = response.patient;
                    $("#consultationrefno").val(p.consultationrefno);
                    $("#consultation_modal").data("pxrefno", p.pxrefno || "");
                    $("#consultation_modal").data("pincode", p.pincode || "");

                    // Detailed Comment: Populate modal header badges with active consultation date and reference
                    const activeConsulDate = p.consultation_date ? p.consultation_date.split(' ')[0] : ($("#consul_date").val() || new Date().toISOString().split('T')[0]);
                    $("#doctor_modal_consultdate_badge").text(`Date: ${activeConsulDate}`);
                    $("#doctor_modal_consultref_badge").text(`Ref: ${p.consultationrefno || 'N/A'}`);

                    // Detailed Comment: Populate Patient Type badge in the Patient Information card
                    const pxType = (p.classification || (p.hmocode ? 'HMO' : (p.phic_pin ? 'PHIC' : 'REGULAR'))).toUpperCase();
                    $("#doctor_modal_patient_type_badge").text(pxType).removeClass("bg-secondary bg-success bg-info bg-warning")
                        .addClass(pxType === 'PHIC' ? 'bg-success text-white' : (pxType === 'HMO' ? 'bg-info text-white' : 'bg-secondary text-white'));

                    $("#consulname").text([p.patientname, p.pxmidname, p.pxlastname, p.pxsuffix].filter(v => v).join(' '));
                    $("#consulsex").text(p.gender);
                    $("#consulbday").text(new Date(p.birthday.replace(" ", "T")).toLocaleDateString("en-US", { month: "long", day: "numeric", year: "numeric" }));
                    $("#consulage").text(calculateAge(p.birthday));
                    $("#consulcellno").text(p.mobilenumber);
                    $("#consulemail").text(p.emailaddress);
                    $("#consuladdress").text(p.address);
                    $("#consulweight").text(p.weight != null ? p.weight + p.wunit : '');
                    $("#consulheight").text(p.height != null ? p.height + p.hunit : '');
                    $("#consultemp").text(p.temp != null ? p.temp + p.tempunit : '');
                    $("#consulresprate").text(p.respiratoryrate);
                    $("#consulpulserate").text(p.pulserate);
                    $("#consulbp").text(p.bpnumerator != null && p.bpdenominator != null ? `${p.bpnumerator}/${p.bpdenominator}` : '');
                    $("#patient_instructions").val(p.instructions);
                    $("#genphoto").prop("src", p.photo_path || '/images/blank_photo.png');
                    const bdayFormatted = p.birthday ? new Date(p.birthday.replace(" ", "T")).toLocaleDateString("en-US", { month: "long", day: "numeric", year: "numeric" }) : 'N/A';
                    const ageCalculated = calculateAge(p.birthday);
                    const weightFormatted = p.weight != null ? p.weight + (p.wunit || '') : '';
                    const heightFormatted = p.height != null ? p.height + (p.hunit || '') : '';
                    const tempFormatted = p.temp != null ? p.temp + (p.tempunit || '') : '';
                    const bpFormatted = (p.bpnumerator != null && p.bpdenominator != null) ? `${p.bpnumerator}/${p.bpdenominator}` : 'N/A';

                    // Detailed Comment: Populate patient demographics in both sidebar and Generate Rx modal accordion
                    $("#genname, #rx_genname").text(p.patientname || '');
                    $("#genpincode").text(p.pincode || 'N/A');
                    $("#genpxrefno").text(p.pxrefno || 'N/A');
                    $("#gensex, #rx_gensex").text(p.gender || '');
                    $("#genbday, #rx_genbday").text(bdayFormatted);
                    $("#genage, #rx_genage").text(ageCalculated);
                    $("#gencellno, #rx_gencellno").text(p.mobilenumber || 'N/A');
                    $("#genlandline").text(p.landlinenumber || 'N/A');
                    $("#genemail, #rx_genemail").text(p.emailaddress || 'N/A');
                    $("#genaddress, #rx_genaddress").text(p.address || 'N/A');
                    $("#genweight, #rx_genweight").text(weightFormatted);
                    $("#genheight, #rx_genheight").text(heightFormatted);
                    $("#gentemp, #rx_gentemp").text(tempFormatted);
                    $("#genresprate, #rx_genresprate").text(p.respiratoryrate || 'N/A');
                    $("#genpulserate, #rx_genpulserate").text(p.pulserate || 'N/A');
                    $("#genbp, #rx_genbp").text(bpFormatted);
                    // Detailed Comment: Fix print button URLs to respect application subfolder base path (/kayakapmd_clinic)
                    const basePath = window.location.pathname.startsWith('/kayakapmd_clinic') ? '/kayakapmd_clinic' : '';
                    $("#print_rx_btn").attr("href", `${basePath}/print_pdf?type=rx&consultationrefno=${rowConsultationRefno}`);
                    $("#print_inst_btn").attr("href", `${basePath}/print_pdf?type=instructions&consultationrefno=${rowConsultationRefno}`);
                    $("#print_admit_btn").attr("href", `${basePath}/print_pdf?type=admission&consultationrefno=${rowConsultationRefno}`);
                    $("#reasonforconsultation").val(p.reasonforconsultation);
                    $("#impressions").val(p.impression);
                    $("#diagnosis").val(p.finadiagnosis);
                    $("#foradmit").prop("checked", p.foradmit == 1);
                    $("#foradmit_instructions").val(p.foradmit_instructions || "");

                    // Detailed Comment: Activate the primary Consultation tab on the card header and Impressions & Diagnosis vertical pill
                    const mainConsulTabEl = document.querySelector("#main_consultation_tab_btn");
                    if (mainConsulTabEl && window.bootstrap && window.bootstrap.Tab) {
                        window.bootstrap.Tab.getOrCreateInstance(mainConsulTabEl).show();
                    }
                    const impDiagBtnEl = document.querySelector("#impDiagBtn");
                    if (impDiagBtnEl && window.bootstrap && window.bootstrap.Tab) {
                        window.bootstrap.Tab.getOrCreateInstance(impDiagBtnEl).show();
                    }
                }
                consultationModal.show();
                loadDashboardRx();
            },
            error: function () {
                Swal.fire({ title: "Error", text: "Failed to load patient consultation data.", icon: "error" });
            },
            complete: function () {
                if ($btn) resetBtnLoading($btn);
            }
        });
    }

    function loadDashboardRx() {
        if ($.fn.DataTable.isDataTable("#dashboard_rx_table")) {
            $("#dashboard_rx_table").DataTable().clear().destroy();
            $("#dashboard_rx_table tbody").empty();
        }
        $("#dashboard_rx_table").DataTable({
            ajax: {
                url: "/api/fetch_medicine_rx", type: "POST",
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
                {
                    data: 'dispensed_status',
                    defaultContent: '<span class="badge bg-secondary">Pending</span>',
                    render: function (data) {
                        return data ? `<span class="badge bg-info text-white">${data}</span>` : '<span class="badge bg-secondary">Pending</span>';
                    }
                }
            ],
            columnDefs: [
                { targets: [2, 3], width: '10%', orderable: false, searchable: false, className: 'text-nowrap text-center align-middle' },
                { targets: [0, 1], className: 'align-middle' }
            ],
            language: { emptyTable: "No prescription records yet." },
            pageLength: 5, lengthChange: false, info: false, paging: true, searching: false, ordering: false, responsive: true,
            initComplete: function (settings, json) { $("#pxinstructions").text(json.instructions ? json.instructions[0] : ''); }
        });
    }
    window.loadDashboardRx = loadDashboardRx;

    // Detailed Comment: Re-sync patient demographics whenever Generate Rx modal opens
    $("#rx_modal").on("show.bs.modal", function () {
        if (!$("#rx_genname").text() || $("#rx_genname").text().trim() === "") {
            $("#rx_genname").text($("#genname").text());
            $("#rx_gensex").text($("#gensex").text());
            $("#rx_genbday").text($("#genbday").text());
            $("#rx_genage").text($("#genage").text());
            $("#rx_gencellno").text($("#gencellno").text());
            $("#rx_genemail").text($("#genemail").text());
            $("#rx_genaddress").text($("#genaddress").text());
            $("#rx_genweight").text($("#genweight").text());
            $("#rx_genheight").text($("#genheight").text());
            $("#rx_gentemp").text($("#gentemp").text());
            $("#rx_genresprate").text($("#genresprate").text());
            $("#rx_genpulserate").text($("#genpulserate").text());
            $("#rx_genbp").text($("#genbp").text());
        }
    });

    $("#rx_modal").on("shown.bs.modal", function () {
        loadRx();
    });

    // Detailed Comment: Auto-refresh Rx table on Rx & Instructions tab whenever Generate Rx modal is closed
    $("#rx_modal").on("hidden.bs.modal", function () {
        loadDashboardRx();
    });

    function loadRx() {
        if ($.fn.DataTable.isDataTable("#rx_table")) {
            $("#rx_table").DataTable().clear().destroy();
            $("#rx_table tbody").empty();
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
                { data: 'qty' }
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
    window.loadRx = loadRx;

    // Detailed Comment: Complete consultation session with button loading state and table refresh
    $("#save_consul").on("click", function () {
        Swal.fire({ title: "Save Consultation?", text: "This will mark the session as done.", icon: "success", confirmButtonText: "Confirm", showCancelButton: true })
            .then((result) => {
                if (result.isConfirmed) {
                    const $btn = $("#save_consul");
                    setBtnLoading($btn, "Saving...");
                    let constModal = bootstrap.Modal.getInstance(document.getElementById("consultation_modal"));
                    $.ajax({
                        url: "/api/complete_consultation", type: "POST",
                        headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                        data: { consultationrefno: $("#consultationrefno").val() },
                        success: function (response) {
                            if (response.success) {
                                constModal.hide();
                                $("#consultation_table").DataTable().ajax.reload();
                                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Consultation completed!', showConfirmButton: false, timer: 1500 });
                            } else {
                                Swal.fire({ title: "Error", text: response.message || "Failed to complete consultation.", icon: "error" });
                            }
                        },
                        error: function (xhr) {
                            const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to complete consultation.";
                            Swal.fire({ title: "Error", text: msg, icon: "error" });
                        },
                        complete: function () {
                            resetBtnLoading($btn);
                        }
                    });
                }
            });
    });

    // Detailed Comment: Self-service Doctor Profile modal lifecycle (populate 5 tabs and save updates)
    const modalEl = document.getElementById("doctor_profile_modal");
    if (modalEl) {
        modalEl.addEventListener("show.bs.modal", () => {
            $.ajax({
                url: "/api/fetch_doctor_data", type: "POST",
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
});
