$(function () {
    loadDoctors();

    function loadDoctors() {
        $("#doctor_table").DataTable().clear().destroy();
        $("#doctor_table").DataTable({
            ajax: {
                url: '/api/fetch_doctors',
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                dataSrc: 'doctors'
            },
            columns: [
                {
                    data: null,
                    render: function (data) {
                        return `<div class="d-flex gap-1">
                            <button class="btn btn-sm btn-primary edit_doctor" value='${data.docrefno}'><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                            <button class="btn btn-sm btn-success manage_doctor" value='${data.docrefno}'><i class="fa-solid fa-rectangle-list"></i> Manage</button>
                            <button class="btn btn-sm btn-danger delete_doctor" value='${data.docrefno}'><i class="fa-solid fa-trash-can"></i> Delete</button>
                        </div>`;
                    }
                },
                { data: 'docname' },
                { data: 'expertise' },
                { data: null, render: function (data) { return data.status ? 'ACTIVE' : 'INACTIVE'; } }
            ],
            columnDefs: [
                { targets: 0, width: '1%', orderable: false, searchable: false, className: 'text-nowrap text-center align-middle' },
                { target: 3, width: '1%', className: 'text-nowrap text-center align-middle' }
            ],
            language: { emptyTable: "No doctors yet." },
            pageLength: 15, lengthChange: false, paging: true, searching: true, ordering: false, responsive: true
        });
    }

    function loadDoctor(button) {
        $.ajax({
            url: '/api/fetch_doctor_details',
            type: 'POST',
            data: { 'docrefno': $(button).val() },
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                if (response.success) {
                    $("#edocfname").val(response.doctor.docfname);
                    $("#edocmname").val(response.doctor.docmname);
                    $("#edoclname").val(response.doctor.doclname);
                    $("#esuffix").val(response.doctor.suffix);
                    $("#etitlename").val(response.doctor.titlename);
                    $("#eemailadd").val(response.doctor.emailadd);
                    $("#ecellno").val(response.doctor.cellno);
                    $("#eadrs").val(response.doctor.adrs);
                    $("#eproftype").val(response.doctor.proftype);
                    $("#eexpertise").val(response.doctor.expertise);
                    $("#etin").val(response.doctor.tin);
                    $("#elicno").val(response.doctor.Licno);
                    $("#elicnoexpiry").val(response.doctor.licnoexpiry);
                    $("#ephicno").val(response.doctor.phicno);
                    $("#ephicexpiry").val(response.doctor.phicexpiry);
                    $("#estatus").val(response.doctor.status ? 'ACTIVE' : 'INACTIVE');
                    $("#estatusreason").val(response.doctor.statusreason);
                    $("#edocrefno").val(response.doctor.docrefno);
                }
            }
        });
    }

    $("#add_doctor_btn").on("click", function () {
        new bootstrap.Modal("#add_doctor_modal").show();
    });

    $("#add_doctor_form_btn").on("click", function () {
        var form = document.getElementById("add_doctor_form");
        if (!form.checkValidity()) { form.reportValidity(); return; }
        $.ajax({
            url: "/api/add_doctor",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: $("#add_doctor_form").serialize(),
            success: function (response) {
                if (response.success) {
                    Swal.fire({ title: 'Success', text: 'Successfully added a doctor.', icon: 'success', confirmButtonText: 'Okay' })
                        .then(() => { $("#add_doctor_form")[0].reset(); loadDoctors(); });
                }
            }
        });
    });

    $(document).on("click", ".edit_doctor", function () {
        loadDoctor(this);
        new bootstrap.Modal('#edit_doctor_modal').show();
    });

    $("#edit_doctor_form_btn").on("click", function () {
        Swal.fire({ title: "Confirmation", text: "Save edited doctor information?", icon: "warning", showCancelButton: true, confirmButtonText: "Yes" })
            .then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "/api/edit_doctor",
                        type: "POST",
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        data: $("#edit_doctor_form").serialize(),
                        success: function (response) {
                            if (response.success) {
                                Swal.fire({ title: "Success", text: "Successfully edited information.", icon: "success", confirmButtonText: "Okay" });
                                $("#doctor_table").DataTable().ajax.reload();
                            }
                        }
                    });
                }
            });
    });

    $(document).on("click", ".manage_doctor", function () {
        new bootstrap.Modal('#manage_doctor_modal').show();
        $("#choose_doctor").val($(this).val());
        $("#questions_tab").trigger("click");
    });

    $(document).on("click", ".delete_doctor", function () {
        Swal.fire({ title: 'Confirmation', text: 'Do you want to delete this doctor record?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Confirm' })
            .then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/api/delete_doctor',
                        type: 'POST',
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        data: { 'docrefno': $(this).val() },
                        success: function (response) {
                            if (response.success) {
                                Swal.fire({ title: 'Success', text: 'Doctor profile deleted successfully.', icon: 'success', confirmButtonText: 'Okay' });
                                loadDoctors();
                            }
                        }
                    });
                }
            });
    });
});
