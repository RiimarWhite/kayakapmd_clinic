$(function () {
    loadSecretaries();

    function loadSecretaries() {
        $("#secretary_table").DataTable().clear().destroy();
        $("#secretary_table").DataTable({
            ajax: {
                url: "/api/fetch_secretaries",
                type: "POST",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                dataSrc: 'secretaries'
            },
            columns: [
                {
                    data: null,
                    render: function (data) {
                        return `<button class="btn btn-sm btn-primary assign_doctor" value='${data.secrefno}'><i class="fa-solid fa-user-doctor"></i> Assigned Doctors</button>
                            <button class="btn btn-sm btn-danger delete_secretary" value='${data.secrefno}'><i class="fa-solid fa-trash-can"></i> Delete</button>`;
                    }
                },
                { data: null, render: function (data) { return data.seclname + ', ' + data.secfname + ' ' + (data.secmname ?? '') + ' ' + (data.secsuffix ?? '') } },
                { data: 'seccontactno', className: 'text-start' },
                { data: 'secemail' }
            ],
            columnDefs: [{ targets: 0, width: '1%', orderable: false, searchable: false, className: 'text-nowrap text-center align-middle' }],
            language: { emptyTable: "No secretaries yet." },
            pageLength: 10, lengthChange: false, paging: true, searching: true, ordering: false, responsive: true
        });
    }

    $("#add_secretary_btn").on("click", function () {
        var form = document.getElementById("add_secretary_form");
        if (!form.checkValidity()) { form.reportValidity(); return; }

        $.ajax({
            url: "/api/add_secretary",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
                secfname: $("#secfname").val(), secmname: $("#secmname").val(),
                seclname: $("#seclname").val(), secsuffix: $("#secsuffix").val(),
                seccontactno: $("#seccontactno").val(), secemail: $("#secemail").val(),
                secpassword: $("#secpassword").val(), secgender: $("#secgender").val(),
                secbday: $("#secbday").val(), secadrs: $("#secadrs").val(),
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire({ title: "Success", text: "Secretary has been added.", icon: "success", confirmButtonText: "Okay" })
                        .then(() => { loadSecretaries(); $("#add_secretary_form")[0].reset(); });
                }
            }
        });
    });

    $(document).on("click", ".assign_doctor", function () {
        const assignedModal = new bootstrap.Modal("#assigned_doctors_modal");
        var secrefno = $(this).val();

        $.ajax({
            url: "/api/fetch_secretary_doctors",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { secrefno: secrefno },
            success: function (response) {
                if (response.success) {
                    $("#secretary_name").text(response.name);
                    var $select = $("#availdoctors");
                    $select.empty();
                    response.avail_doctors.forEach(element => {
                        $select.append($("<option>", { value: element.docrefno, text: element.docname }));
                    });

                    var $assigned = $("#assigned_doctors_badge");
                    $assigned.empty();
                    const doctors = response.assigned_doctors?.filter(d => d != null);
                    if (doctors && doctors.length > 0) {
                        doctors.forEach(element => {
                            $assigned.append(`<span class="btn btn-sm btn-primary rounded rounded-pill remove_append" id="${element.docrefno}">
                                <input type="hidden" name="doctors[]" value="${element.docrefno}">${element.docname} ×</span>`);
                        });
                    } else {
                        $assigned.append("<span class='d-flex text-center'>No assigned doctors.</span>");
                    }

                    $("#save_append").val(secrefno);
                    assignedModal.show();
                }
            }
        });
    });

    $(document).on("click", ".delete_secretary", function () {
        Swal.fire({ title: "Confirmation", text: "Are you sure you want to delete this user?", icon: "warning", showCancelButton: true, confirmButtonText: "Confirm" })
            .then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "/api/delete_secretary",
                        type: "POST",
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        data: { secrefno: $(this).val() },
                        success: function (response) {
                            if (response.success) {
                                Swal.fire({ title: "Success", text: "Secretary has been deleted.", icon: "success", confirmButtonText: "Okay" })
                                    .then(() => loadSecretaries());
                            }
                        }
                    });
                }
            });
    });

    $("#assign_doctor_btn").on("click", function () {
        const assigned_doctors = $("#assigned_doctors_badge");
        const value = $("#availdoctors option:selected").val();
        const name = $("#availdoctors option:selected").text();
        if (value != null) {
            if (assigned_doctors.find(".no-doctors").length > 0) assigned_doctors.empty();
            assigned_doctors.append(`<span class="btn btn-sm btn-primary rounded rounded-pill remove_append" id="${value}">
                <input type="hidden" name="doctors[]" value="${value}"> ${name} ×</span>`);
            $("#availdoctors option:selected").remove();
        }
    });

    $(document).on("click", ".remove_append", function () {
        const available_doctors = $("#availdoctors");
        const assigned_doctors = $("#assigned_doctors_badge");
        const option = $(this).text();
        $(this).remove();
        available_doctors.append($("<option>", { value: $(this).val(), text: option.replace('×', '') }));
        if (assigned_doctors.children().length == 0) {
            assigned_doctors.append($("<span>", { "class": "no-doctors", text: "No assigned doctors." }));
        }
    });

    $("#save_append").on("click", function () {
        let formData = $("#append_doctor_form").serialize();
        formData += '&secrefno=' + encodeURIComponent($(this).val());
        $.ajax({
            url: "/api/save_appended_doctors",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: formData,
            success: function (response) {
                if (response.success) {
                    Swal.fire({ title: "Success", text: "Doctors assigned to secretary.", icon: "success", confirmButtonText: "Okay" });
                }
            }
        });
    });
});
