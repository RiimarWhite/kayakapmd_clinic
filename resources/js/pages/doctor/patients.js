$(function () {
    loadMasterlist();

    function loadMasterlist() {
        if ($.fn.DataTable.isDataTable("#masterlist_table")) { $("#masterlist_table").DataTable().clear().destroy(); }
        $("#masterlist_table").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "/api/fetch_consultation_masterlist",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr('content') },
            },
            columns: [
                { data: 'consultationrefno', render: function (data) { return `<button class="btn btn-sm btn-primary masterlist-view" value="${data}"><i class="fa-solid fa-eye"></i> View</button>`; } },
                {
                    data: null,
                    render: function (data) {
                        return `<img src="${data.photo_path ?? '/images/blank_photo.png'}" class="rounded rounded-circle" style="max-width: 50px;" alt="patient_photo"> <span>${data.patientname}</span>`
                    }
                },
                { data: 'consultation_date', render: function (data) { return new Date(data).toLocaleDateString("en-US", { month: "long", day: "2-digit", year: "numeric" }); } },
            ],
            columnDefs: [
                { target: 0, width: '1%', orderable: false, searchable: false, className: 'text-nowrap text-center align-middle' },
                { targets: [1, 2], orderable: false, className: 'text-nowrap align-middle' }
            ],
            order: [[1, 'asc']],
            language: { emptyTable: "No patients yet." },
            pageLength: 10, lengthChange: false, paging: true, searching: true, ordering: true, responsive: true
        });
    }

    $(document).on("click", ".masterlist-view", function () {
        const infoModal = new bootstrap.Modal("#viewPatientModal");

        $.ajax({
            url: "/api/fetch_patient_data",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { consultationrefno: $(this).val() },
            success: function (response) {
                infoModal.show();
                const p = response.patient;
                $("#view_memPin").val(p.pincode);
                $("#view_pPatientFname").val(p.pxfirstname);
                $("#view_pPatientMname").val(p.pxmidname);
                $("#view_pPatientLname").val(p.pxlastname);
                $("#view_pPatientExtname").val(p.suffix);
                $("#view_pPatientDob").val(p.birthday);
                $("#view_pPatientSex").val(p.gender);
                $("#view_pPatientMobileNo").val(p.mobilenumber);
                $("#view_email").val(p.emailaddress);
                $("#view_address").val(p.address);
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

    $("#add_patient_modal_btn").on("click", function () {
        const modal = new bootstrap.Modal(("#add_patient_modal"));
        modal.show();
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
                    $("#masterlist_table").DataTable().ajax.reload();
                }
            }
        });
    });
});
