$(function () {
    // Init consultation date and load patients
    $("#consul_date").val(new Date().toISOString().split('T')[0]);
    getPatientsFromDate($("#consul_date").val());

    function getPatientsFromDate(value) {
        $("#consultation_table").DataTable().clear().destroy();
        $("#consultation_table").DataTable({
            ajax: {
                url: "/api/fetch_doctor_consultations",
                type: "POST",
                data: { date: value },
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                dataSrc: 'patients'
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
        let d = new Date($("#consul_date").val()); d.setDate(d.getDate() - 1);
        $("#consul_date").val(d.toISOString().split("T")[0]); getPatientsFromDate($("#consul_date").val());
    });
    $("#next").on("click", function () {
        let d = new Date($("#consul_date").val()); d.setDate(d.getDate() + 1);
        $("#consul_date").val(d.toISOString().split("T")[0]); getPatientsFromDate($("#consul_date").val());
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

    $("#consult").on("click", function () {
        if (!$("#consultation_table").DataTable().rows({ selected: true }).any()) {
            return Swal.fire({
                title: "Missing Patient",
                text: "Please select a patient.",
                icon: "warning"
            });
        }

        loadConsultModal();
    });

    function loadConsultModal() {
        if (!$("#consultation_table").DataTable().rows({ selected: true }).any()) return;

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
                    $("#genbp").text(p.bpnumerator != null && p.bpdenominator != null ? `${p.bpnumerator}/${p.bpdenominator}` : '');
                    $("#print_rx_btn").attr("href", `/print_pdf?type=rx&consultationrefno=${rowConsultationRefno}`);
                    $("#print_inst_btn").attr("href", `/print_pdf?type=instructions&consultationrefno=${rowConsultationRefno}`);
                    $("#reasonforconsultation").val(p.reasonforconsultation);
                    $("#impressions").val(p.impression);
                    $("#diagnosis").val(p.finadiagnosis);
                }
                loadDashboardRx();
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

    $("#save_consul").on("click", function () {
        Swal.fire({ title: "Save Consultation?", text: "This will mark the session as done.", icon: "success", confirmButtonText: "Confirm", showCancelButton: true })
            .then((result) => {
                if (result.isConfirmed) {
                    let constModal = bootstrap.Modal.getInstance(document.getElementById("consultation_modal"));
                    $.ajax({
                        url: "/api/complete_consultation", type: "POST",
                        headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                        data: { consultationrefno: $("#consultationrefno").val() },
                        success: function (response) {
                            if (response.success) { constModal.hide(); $("#consultation_table").DataTable().ajax.reload(); }
                        }
                    });
                }
            });
    });

    // Doctor profile modal
    const modalEl = document.getElementById("doctor_profile_modal");
    if (modalEl) {
        modalEl.addEventListener("shown.bs.modal", () => {
            $.ajax({
                url: "/api/fetch_doctor_data", type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                success: function (response) {
                    const u = response.user;
                    const map = { "#doc_fullname": [u.docfname, u.docmname, u.doclname, u.suffix].filter(Boolean).join(" "), "#doc_title": u.titlename, "#doc_contact": u.cellno, "#doc_email": u.emailadd, "#doc_lic": u.Licno, "#doc_lic_expiry": u.licnoexpiry, "#doc_phic": u.phicno, "#doc_phic_expiry": u.phicexpiry, "#doc_s2": u.S2no };
                    $.each(map, (sel, val) => $(sel).text(val ?? ""));
                }
            });
        });
    }
});
