$(function () {
    // Load today's patients table
    $("#todays_patients_table").DataTable().clear().destroy();
    $("#todays_patients_table").DataTable({
        ajax: {
            url: "/api/fetch_todays_patients",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $("meta[name='csrf-token']").attr('content') },
            dataSrc: function (json) {
                $("#patient_count").text("Count: " + json.count);
                return json.patients;
            }
        },
        columns: [
            { data: 'queueno' },
            { data: null, render: function (data) { return `${[data.patientname, data.pxmidname, data.pxlastname, data.pxsuffix].filter(v => v).join(' ')}`; } },
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
        columnDefs: [
            { target: 0, width: '1%', orderable: false, searchable: false, className: 'text-nowrap fw-bold text-center align-middle' },
            { target: 2, width: '1%', className: 'text-nowrap overflow-hidden align-middle text-center' }
        ],
        language: { emptyTable: "No patients yet." },
        pageLength: 30, lengthChange: false, paging: true, searching: false, ordering: false, responsive: true
    });

    // Load schedules
    $.ajax({
        url: "/api/fetch_doctor_schedules_dashboard",
        type: "POST",
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function (response) { renderSchedules(response.schedules); }
    });

    function renderSchedules(schedules) {
        const grouped = schedules.reduce((acc, s) => { if (!acc[s.day]) acc[s.day] = []; acc[s.day].push(s); return acc; }, {});
        const body = $("#schedules_calendar tbody");
        body.empty();
        Object.keys(grouped).forEach(day => {
            body.append(`<tr class="table-success"><td colspan="3"><strong>${day}</strong></td></tr>`);
            grouped[day].forEach(sched => {
                body.append(`<tr class="align-middle"><td class="ps-4">${convert24To12(sched.start)} - ${convert24To12(sched.end)}</td></tr>`);
            });
        });
    }

    function convert24To12(time) {
        const [hour, minute] = time.split(":");
        const h = parseInt(hour, 10);
        return `${h % 12 || 12}:${minute} ${h >= 12 ? 'PM' : 'AM'}`;
    }

    // Doctor profile modal
    const modalEl = document.getElementById("doctor_profile_modal");
    if (modalEl) {
        modalEl.addEventListener("shown.bs.modal", () => {
            $.ajax({
                url: "/api/fetch_doctor_data",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                success: function (response) {
                    const u = response.user;
                    const map = {
                        "#doc_fullname": [u.docfname, u.docmname, u.doclname, u.suffix].filter(Boolean).join(" "),
                        "#doc_title": u.titlename, "#doc_contact": u.cellno, "#doc_email": u.emailadd,
                        "#doc_lic": u.Licno, "#doc_lic_expiry": u.licnoexpiry, "#doc_phic": u.phicno,
                        "#doc_phic_expiry": u.phicexpiry, "#doc_s2": u.S2no
                    };
                    $.each(map, (sel, val) => $(sel).text(val ?? ""));
                }
            });
        });
    }
});
