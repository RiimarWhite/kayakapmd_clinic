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

    // Init consultation date and load patients
    $("#consul_date").val(new Date().toISOString().split('T')[0]);
    getPatientsFromDate($("#consul_date").val());

    function getPatientsFromDate(value, $btn = null) {
        if ($btn) setBtnLoading($btn, "");
        $("#consultation_table").DataTable().clear().destroy();
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
                consultationModal.show();
                $("#medical_questions_tab_btn").trigger("click");
                if (response.patient != null) {
                    const p = response.patient;
                    $("#consultationrefno").val(p.consultationrefno);
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
                    $("#genname").text(p.patientname);
                    $("#gensex").text(p.gender);
                    $("#genbday").text(new Date(p.birthday.replace(" ", "T")).toLocaleDateString("en-US", { month: "long", day: "numeric", year: "numeric" }));
                    $("#genage").text(calculateAge(p.birthday));
                    $("#gencellno").text(p.mobilenumber);
                    $("#genemail").text(p.emailaddress);
                    $("#genaddress").text(p.address);
                    $("#genweight").text(p.weight != null ? p.weight + p.wunit : '');
                    $("#genheight").text(p.height != null ? p.height + p.hunit : '');
                    $("#gentemp").text(p.temp != null ? p.temp + p.tempunit : '');
                    $("#genresprate").text(p.respiratoryrate);
                    $("#genpulserate").text(p.pulserate);
                    // Detailed Comment: Fix print button URLs to respect application subfolder base path (/kayakapmd_clinic)
                    const basePath = window.location.pathname.startsWith('/kayakapmd_clinic') ? '/kayakapmd_clinic' : '';
                    $("#print_rx_btn").attr("href", `${basePath}/print_pdf?type=rx&consultationrefno=${rowConsultationRefno}`);
                    $("#print_inst_btn").attr("href", `${basePath}/print_pdf?type=instructions&consultationrefno=${rowConsultationRefno}`);
                    $("#reasonforconsultation").val(p.reasonforconsultation);
                    $("#impressions").val(p.impression);
                    $("#diagnosis").val(p.finadiagnosis);
                }
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
        $("#dashboard_rx_table").DataTable().destroy().clear();
        $("#dashboard_rx_table").DataTable({
            ajax: {
                url: "/api/fetch_medicine_rx", type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: { consultationrefno: $("#consultationrefno").val() },
                dataSrc: "rx"
            },
            columns: [{ data: 'item_dscr' }, { data: 'qty' }, { data: 'dispensed_status' }],
            columnDefs: [{ targets: [1, 2], width: '10%', orderable: false, searchable: false, className: 'text-nowrap text-center align-middle' }],
            language: { emptyTable: "No records yet." },
            pageLength: 5, lengthChange: false, info: false, paging: true, searching: false, ordering: false, responsive: true,
            initComplete: function (settings, json) { $("#pxinstructions").text(json.instructions ? json.instructions[0] : ''); }
        });
    }

    $("#rx_modal").on("shown.bs.modal", function () {
        loadRx();
    });

    function loadRx() {
        $("#rx_table").DataTable().destroy().clear();
        $("#rx_table").DataTable({
            ajax: {
                url: "/api/fetch_medicine_rx",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: { consultationrefno: $("#consultationrefno").val() },
                dataSrc: 'rx'
            },
            columns: [
                {
                    data: null,
                    render: function (data) {
                        return `<button class="btn btn-sm btn-danger delete_rx" value="${data.prodcode}"><i class="fa-solid fa-trash"></i></button>`;
                    }
                },
                { data: 'item_dscr' },
                { data: 'qty' }
            ],
            columnDefs: [
                {
                    targets: [0, 2],
                    width: '1%',
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
