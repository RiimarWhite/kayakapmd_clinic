$(function () {
    loadDoctors();

    // Detailed Comment: Auto-populate doctor username from lowercase last name
    $("#doclname").on("input blur", function () {
        if (!$("#docusername").val() || $("#docusername").data("auto-generated")) {
            const val = $(this).val().trim().toLowerCase().replace(/\s+/g, '');
            $("#docusername").val(val).data("auto-generated", true);
        }
    });
    $("#docusername").on("input", function () {
        $(this).data("auto-generated", false);
    });

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
                        return `<div class="d-flex gap-1 justify-content-center">
                            <button class="btn btn-sm btn-primary edit_doctor" value='${data.docrefno}' title="Edit Doctor"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                            <button class="btn btn-sm btn-success manage_doctor" value='${data.docrefno}' title="Doctor Management"><i class="fa-solid fa-rectangle-list"></i> Manage</button>
                            <button class="btn btn-sm btn-danger delete_doctor" value='${data.docrefno}' title="Delete Doctor"><i class="fa-solid fa-trash-can"></i> Delete</button>
                        </div>`;
                    }
                },
                { data: 'docname' },
                { data: 'expertise' },
                {
                    data: null,
                    render: function (data) {
                        return data.status ? '<span class="badge bg-success">ACTIVE</span>' : '<span class="badge bg-secondary">INACTIVE</span>';
                    }
                }
            ],
            columnDefs: [
                { targets: 0, width: '1%', orderable: false, searchable: false, className: 'text-nowrap text-center align-middle' },
                { target: 3, width: '1%', className: 'text-nowrap text-center align-middle' }
            ],
            language: { emptyTable: "No doctors yet." },
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            pageLength: 15,
            lengthChange: true,
            paging: true,
            searching: true,
            ordering: false,
            responsive: true
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
                    const doc = response.doctor;
                    // Tab 1: Personal & Credentials
                    $("#edocfname").val(doc.docfname || '');
                    $("#edocmname").val(doc.docmname || '');
                    $("#edoclname").val(doc.doclname || '');
                    $("#esuffix").val(doc.suffix || '');
                    $("#edocusername").val(doc.username || '');
                    $("#etitlename").val(doc.titlename || '');
                    $("#edocfirst").val(doc.docfirst || '');
                    $("#eemailadd").val(doc.emailadd || '');
                    $("#ecellno").val(doc.cellno || '');
                    $("#eadrs").val(doc.adrs || '');
                    $("#epass").val('');

                    // Tab 2: Licenses & Accreditations
                    $("#elicno").val(doc.Licno || '');
                    $("#elicnoexpiry").val(doc.licnoexpiry ? doc.licnoexpiry.split('T')[0].split(' ')[0] : '');
                    $("#etin").val(doc.tin || '');
                    $("#ephicno").val(doc.phicno || '');
                    $("#ephicexpiry").val(doc.phicexpiry ? doc.phicexpiry.split('T')[0].split(' ')[0] : '');
                    $("#ephicname").val(doc.phicname || '');
                    $("#es2no").val(doc.S2no || '');
                    $("#eptr").val(doc.PTR || '');
                    $("#ephicrate").val(doc.phicrate || '');
                    $("#ephicenable").prop('checked', !!(doc.phicenable == 1 || doc.phicenable === true));

                    // Tab 3: Practice & Clinic
                    $("#eproftype").val(doc.proftype || '');
                    $("#eexpertise").val(doc.expertise || '');
                    $("#edepartment").val(doc.department || '');
                    $("#eprofgroup").val(doc.profgroup || '');
                    $("#ecatg").val(doc.catg || '');
                    $("#estation").val(doc.station || '');
                    $("#eclinicroom").val(doc.clinicroom || '');
                    $("#eclinichours").val(doc.clinichours || '');
                    $("#egroupname").val(doc.groupname || '');

                    // Tab 4: Rates, Tax & Billing
                    $("#epfrate").val(doc.pfrate || doc.consultationfee || '');
                    $("#erodrate").val(doc.rodrate || '');
                    $("#etax").val(doc.tax || doc.taxpercent || '');
                    $("#evatrate").val(doc.vatrate || '');
                    $("#ecoacode").val(doc.coacode || doc.slcode || '');
                    $("#eaccountno").val(doc.accountno || doc.bankacct || '');
                    $("#evatable").prop('checked', !!(doc.vatable == 1 || doc.vatable === true));
                    $("#eautoAddVAT").prop('checked', !!(doc.autoAddVAT == 1 || doc.autoAddVAT === true));
                    $("#eissuehospOR").prop('checked', !!(doc.issuehospOR == 1 || doc.issuehospOR === true));

                    // Tab 5: System Settings & Notes
                    $("#estatus").val(doc.status ? 'ACTIVE' : 'INACTIVE');
                    $("#estatusreason").val(doc.statusreason || '');
                    $("#equevisible").prop('checked', !!(doc.quevisible == 1 || doc.quevisible === true || doc.quevisible === undefined));
                    $("#eallowtextresult").prop('checked', !!(doc.allowtextresult == 1 || doc.allowtextresult === true));
                    $("#eallowdocsystem").prop('checked', !!(doc.allowdocsystem == 1 || doc.allowdocsystem === true));
                    $("#edisabletext").prop('checked', !!(doc.disabletext == 1 || doc.disabletext === true));
                    $("#eotherinfo").val(doc.otherinfo || '');
                    $("#ebiodata").val(doc.biodata || '');

                    $("#edocrefno").val(doc.docrefno);
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
                    bootstrap.Modal.getInstance("#add_doctor_modal").hide();
                    Swal.fire({ title: 'Success', text: 'Doctor account added successfully.', icon: 'success', confirmButtonText: 'Okay' })
                        .then(() => {
                            $("#add_doctor_form")[0].reset();
                            $("#docusername").data("auto-generated", false);
                            loadDoctors();
                        });
                }
            },
            error: function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to add doctor.";
                Swal.fire({ title: 'Error', text: msg, icon: 'error' });
            }
        });
    });

    $(document).on("click", ".edit_doctor", function () {
        loadDoctor(this);
        new bootstrap.Modal('#edit_doctor_modal').show();
    });

    $("#edit_doctor_form_btn").on("click", function () {
        var form = document.getElementById("edit_doctor_form");
        if (!form.checkValidity()) { form.reportValidity(); return; }

        Swal.fire({ title: "Confirmation", text: "Save edited doctor information?", icon: "warning", showCancelButton: true, confirmButtonText: "Yes, save" })
            .then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "/api/edit_doctor",
                        type: "POST",
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        data: $("#edit_doctor_form").serialize(),
                        success: function (response) {
                            if (response.success) {
                                bootstrap.Modal.getInstance('#edit_doctor_modal').hide();
                                Swal.fire({ title: "Success", text: "Doctor information updated successfully.", icon: "success", confirmButtonText: "Okay" });
                                $("#doctor_table").DataTable().ajax.reload();
                            }
                        },
                        error: function (xhr) {
                            const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to update doctor.";
                            Swal.fire({ title: 'Error', text: msg, icon: 'error' });
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
        Swal.fire({ title: 'Confirmation', text: 'Do you want to delete this doctor record?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Confirm', confirmButtonColor: '#d33' })
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
                        },
                        error: function (xhr) {
                            const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to delete doctor.";
                            Swal.fire({ title: 'Error', text: msg, icon: 'error' });
                        }
                    });
                }
            });
    });
});
