// This js file is currently not used. For reference only

$(function () {
    $
    // -- Doctors
    // Load doctors
    if ($("#admin_page").length) {
        loadDoctors();
        loadProfile();
    }

    function loadDoctors() {
        $("#doctor_table").DataTable().clear().destroy();
        $("#doctor_table").DataTable({
            ajax: {
                url: 'fetch_doctors',
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                dataSrc: 'doctors'
            },
            columns: [
                {
                    data: null,
                    render: function (data) {
                        return `
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-primary edit_doctor" value='${data.docrefno}'><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                                <button class="btn btn-sm btn-success manage_doctor" value='${data.docrefno}'><i class="fa-solid fa-rectangle-list"></i> Manage</button>
                                <button class="btn btn-sm btn-danger delete_doctor" value='${data.docrefno}'><i class="fa-solid fa-trash-can"></i> Delete</button>
                            </div>`;
                    }
                },
                { data: 'docname' },
                { data: 'expertise' },
                {
                    data: null,
                    render: function (data) {
                        return data.status ? 'ACTIVE' : 'INACTIVE';
                    }
                }
            ],
            columnDefs: [
                {
                    targets: 0,
                    width: '1%',
                    orderable: false,
                    searchable: false,
                    className: 'text-nowrap text-center align-middle'
                },
                {
                    target: 3,
                    width: '1%',
                    className: 'text-nowrap text-center align-middle'
                }
            ],
            language: {
                emptyTable: "No doctors yet."
            },
            pageLength: 15,
            lengthChange: false,
            paging: true,
            searching: true,
            ordering: false,
            responsive: true
        });
    }

    function loadDoctor(button) {
        console.log(button);
        $.ajax({
            url: 'fetch_doctor_details',
            type: 'POST',
            data: {
                'docrefno': $(button).val()
            },
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
                    $("#estatus").prop('selected', response.doctor.status).val(response.doctor.status ? 'ACTIVE' : 'INACTIVE');
                    $("#estatusreason").val(response.doctor.statusreason);

                    // Doctor reference
                    $("#edocrefno").val(response.doctor.docrefno);
                }
            }
        });
    }

    $("#add_doctors_tab").on("click", function () {
        loadDoctors();
    });

    $("#add_doctor_btn").on("click", function () {
        const addDoctorModal = new bootstrap.Modal("#add_doctor_modal");
        addDoctorModal.show();
    });

    $("#add_doctor_form_btn").on("click", function () {
        var form = document.getElementById("add_doctor_form");

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        $.ajax({
            url: "add_doctor",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: $("#add_doctor_form").serialize(),
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Success',
                        text: 'Successfully added a doctor.',
                        icon: 'success',
                        confirmButtonText: 'Okay'
                    }).then(() => {
                        $("#add_doctor_form")[0].reset();
                        loadDoctors();
                    });
                }
            }
        });
    });

    $(document).on("click", ".edit_doctor", function () {
        const editDoctorModal = new bootstrap.Modal('#edit_doctor_modal');
        loadDoctor(this);
        editDoctorModal.show();
    });

    $("#edit_doctor_form_btn").on("click", function () {
        Swal.fire({
            title: "Confirmation",
            text: "Save edited doctor information?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "edit_doctor",
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: $("#edit_doctor_form").serialize(),
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                title: "Success",
                                text: "Successfully editted information.",
                                icon: "success",
                                confirmButtonText: "Okay"
                            });

                            $("#doctor_table").DataTable().ajax.reload();
                        }
                    }
                });
            }
        });
    });

    $(document).on("click", ".manage_doctor", function () {
        const manageDoctorModal = new bootstrap.Modal('#manage_doctor_modal');
        manageDoctorModal.show();

        $("#choose_doctor").val($(this).val());
        $("#questions_tab").trigger("click");
    });

    $(document).on("click", ".delete_doctor", function () {
        Swal.fire({
            title: 'Confirmation',
            text: 'Do you want to delete this doctor record?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Confirm'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'delete_doctor',
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: { 'docrefno': $(this).val() },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Success',
                                text: 'Doctor profile deleted successfully.',
                                icon: 'success',
                                confirmButtonText: 'Okay'
                            });

                            loadDoctors();
                        }
                    }
                });
            }
        })
    });

    // Profile
    $("#save_updates").on("click", function () {
        const form = document.getElementById("profile_form");
        if (!form.checkValidity()) {
            return form.reportValidity();
        }

        let formData = new FormData(form);

        $.ajax({
            url: "update_profile",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    return Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Credentials saved',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            }
        });
    });

    let addresses = [];
    function loadAddressData() {
        $.ajax({
            url: "fetch_address_data",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
            },
            success: function (response) {
                if (response.data != null) {
                    addresses = response.data;
                    addresses.regions.forEach(e => {
                        $("#comp_region").append(
                            `<option value="${e.REGION_CODE}">${e.REGION_DESC}</option>`
                        );
                    });
                }
            }
        });
    }

    function loadProfile() {
        loadAddressData();

        $.ajax({
            url: "load_company_profile",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
            },
            success: function (response) {
                if (response.success) {
                    const profile = response.profile;

                    $("#comp_name").val(profile.HOSP_NAME);

                    // $("#comp_region").val(profile.company_region).trigger("change");

                    // Set default value for address
                    // setTimeout(() => {
                    //     $("#comp_prov").val(profile.company_prov).trigger("change");

                    //     setTimeout(() => {
                    //         $("#comp_mun").val(profile.company_mun).trigger("change");

                    //         setTimeout(() => {
                    //             $("#comp_brgy").val(profile.company_brgy);
                    //             $("#comp_zipcode").val(profile.company_zipcode);
                    //         }, 50);
                    //     }, 50);
                    // }, 50);

                    // $("#comp_contact").val(profile.company_mobilenumber);
                    // $("#comp_tel").val(profile.company_telephone);
                    // $("#comp_email").val(profile.company_email);

                    // $("#ph_username").val(profile.hci_username);
                    // $("#ph_accreno").val(profile.hci_accre_no);
                }
            }
        });
    }

    // Load provinces on region select
    $("#comp_region").on("change", function () {
        const value = $(this).val();
        let region;

        addresses.regions.forEach(e => {
            if (e.REGION_CODE == value) {
                region = e;
            }
        });

        let regionCode = region.PRO_CODE;
        if (parseInt(regionCode) < 10) {
            regionCode = regionCode.padStart(2, "0");
        }

        let provinces = [];
        addresses.provinces.forEach(e => {
            if (e.PROCODE == regionCode) {
                provinces.push(e);
            }
        });

        $("#comp_prov").empty();
        provinces.forEach(e => {
            $("#comp_prov").append(
                `<option value="${e.PROVINCE}">${e.PROV_NAME}</option>`
            );
        });
    });

    // Load municipalities on province select
    $("#comp_prov").on("change", function () {
        const value = $(this).val();
        const province = addresses.provinces.find(e => e.PROVINCE === value);
        const municipalities = addresses.municipalities.filter(
            e => e.PROCODE === province.PROCODE && e.PROVINCE === province.PROVINCE
        );

        $("#comp_mun").empty();
        municipalities.forEach(e => {
            $("#comp_mun").append(
                `<option value="${e.MUNICIPALITY}">${e.MUN_NAME}</option>`
            );
        });
    });

    // Load barangays on municipality change
    $("#comp_mun").on("change", function () {
        const province = $("#comp_prov").val();
        const value = $("#comp_mun").val();

        const municipality = addresses.municipalities.find(
            e => e.MUNICIPALITY === value && e.PROVINCE === province
        );

        const barangay = addresses.barangay.filter(
            e => e.MUNICIPALITY === municipality.MUNICIPALITY && e.PROVINCE === province
        );

        $("#comp_brgy").empty();
        barangay.forEach(e => {
            $("#comp_brgy").append(
                `<option value="${e.BARANGAY}">${e.BRGY_NAME}</option>`
            );
        });
    });

    $("#upload_logo_btn").on("click", function () {
        $("#comp_logo").trigger("click");
    });

    $("#comp_logo").on("change", function () {
        const file = this.files[0];

        if (!file) return;

        $("#company_logo_display").attr("src", URL.createObjectURL(file));
    });

    $("#view_password").on("click", function () {
        const input = document.getElementById("ph_password");

        if (input.type == "password") {
            input.type = "text";
        } else if (input.type == "text") {
            input.type = "password";
        }
    });

    $("#save_comp_credentials").on("click", function () {
        const form = $("#philhealth_account_form");

        $.ajax({
            url: "save_philhealth_profile",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
            },
            data: form.serialize(),
            success: function (response) {
                if (response.success) {
                    return Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Credentials saved',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            }
        });
    });

    // Secretaries
    $("#add_secretary_tab").on("click", function () {
        $("#secretary_table").DataTable().clear().destroy();
        $("#secretary_table").DataTable({
            ajax: {
                url: "fetch_secretaries",
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
            columnDefs: [
                {
                    targets: 0,
                    width: '1%',
                    orderable: false,
                    searchable: false,
                    className: 'text-nowrap text-center align-middle'
                }
            ],
            language: {
                emptyTable: "No secretaries yet."
            },
            pageLength: 10,
            lengthChange: false,
            paging: true,
            searching: true,
            ordering: false,
            responsive: true
        });
    });

    $("#add_secretary_btn").on("click", function () {
        var form = document.getElementById("add_secretary_form");

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        $.ajax({
            url: "add_secretary",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
                secfname: $("#secfname").val(),
                secmname: $("#secmname").val(),
                seclname: $("#seclname").val(),
                secsuffix: $("#secsuffix").val(),
                seccontactno: $("#seccontactno").val(),
                secemail: $("#secemail").val(),
                secpassword: $("#secpassword").val(),
                secgender: $("#secgender").val(),
                secbday: $("#secbday").val(),
                secadrs: $("#secadrs").val(),
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title: "Success",
                        text: "Secretary has been added.",
                        icon: "success",
                        showCancelButton: false,
                        confirmButtonText: "Okay"
                    }).then(() => {
                        $("#add_secretary_tab").trigger("click");
                        $("#add_secretary_form")[0].reset();
                    });
                }
            }
        });
    });

    // Show modal
    $(document).on("click", ".assign_doctor", function () {
        const assignedModal = new bootstrap.Modal("#assigned_doctors_modal");
        var secrefno = $(this).val();

        $.ajax({
            url: "fetch_secretary_doctors",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { secrefno: secrefno },
            success: function (response) {
                if (response.success) {
                    $("#secretary_name").text(response.name);

                    var $select = $("#availdoctors");
                    $select.empty();

                    response.avail_doctors.forEach(element => {
                        $select.append(
                            $("<option>", {
                                value: element.docrefno,
                                text: element.docname
                            })
                        );
                    });

                    var $assigned = $("#assigned_doctors_badge");
                    $assigned.empty();

                    const doctors = response.assigned_doctors?.filter(d => d != null);

                    if (doctors && doctors.length > 0) {
                        doctors.forEach(element => {
                            $assigned.append(
                                `<span class="btn btn-sm btn-primary rounded rounded-pill remove_append" id="${element.docrefno}">
                                    <input type="hidden" name="doctors[]" value="${element.docrefno}">
                                    ${element.docname} ×
                                </span>`
                            );
                        });
                    } else {
                        $assigned.append("<span class='d-flex text-center'>No assigned doctors.</span>")
                    }

                    $("#save_append").val(secrefno);

                    assignedModal.show();
                }
            }
        });
    });

    $(document).on("click", ".delete_secretary", function () {
        Swal.fire({
            title: "Confirmation",
            text: "Are you sure you want to delete this user?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Confirm"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "delete_secretary",
                    type: "POST",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: { secrefno: $(this).val() },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                title: "Success",
                                text: "Secretary has been deleted.",
                                icon: "success",
                                showCancelButton: false,
                                confirmButtonText: "Okay"
                            }).then(() => {
                                $("#add_secretary_tab").trigger("click");
                            });
                        }
                    }
                });
            }
        });
    });

    // Assign modal
    $("#assign_doctor_btn").on("click", function () {
        const assigned_doctors = $("#assigned_doctors_badge");
        const value = $("#availdoctors option:selected").val();
        const name = $("#availdoctors option:selected").text();

        if (value != null) {
            if (assigned_doctors.find(".no-doctors").length > 0) {
                assigned_doctors.empty();
            }

            assigned_doctors.append(
                `<span class="btn btn-sm btn-primary rounded rounded-pill remove_append" id="${value}">
                    <input type="hidden" name="doctors[]" value="${value}"> ${name} ×
                </span>`
            );

            $("#availdoctors option:selected").remove();
        }
    });

    $(document).on("click", ".remove_append", function () {
        const available_doctors = $("#availdoctors");
        const assigned_doctors = $("#assigned_doctors_badge");
        const option = $(this).text();

        $(this).remove();

        available_doctors.append(
            $("<option>", {
                value: $(this).val(),
                text: option.replace('×', '')
            })
        );

        if (assigned_doctors.children().length == 0) {
            assigned_doctors.append(
                $("<span>", {
                    "class": "no-doctors",
                    text: "No assigned doctors.",
                })
            );
        }
    });

    $("#save_append").on("click", function () {
        let formData = $("#append_doctor_form").serialize();
        formData += '&secrefno=' + encodeURIComponent($(this).val());

        $.ajax({
            url: "save_appended_doctors",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: formData,
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title: "Success",
                        text: "Doctors assigned to secretary.",
                        icon: "success",
                        showCancelButton: false,
                        confirmButtonText: "Okay"
                    });
                }
            }
        })
    });

    // Diagnostics
    $("#diagnostics_category_tab").on("click", function () {
        $("#diagnostic_category_table").DataTable().destroy().clear();
        $("#diagnostic_category_table").DataTable({
            ajax: {
                url: "fetch_diagnostic_category",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                },
                dataSrc: 'categories'
            },
            columns: [
                {
                    data: null,
                    render: function (data) {
                        return `<button class="btn btn-sm btn-danger remove_dia_catg" value="${data.category_refno}"><i class="fa-solid fa-trash"></i> Remove</button>`;
                    }
                },
                { data: 'category_name' }
            ],
            columnDefs: [
                {
                    target: 0,
                    width: '1%',
                    orderable: false,
                    className: 'text-nowrap text-center'
                }
            ],
            searching: false,
            info: false,
            pageLength: 20,
            lengthChange: false,
        });
    });

    $("#create_diagnostic_category_btn").on("click", function () {
        Swal.fire({
            title: "Create Diagnostic Category",
            html: `
                <input
                    id="diagnostic_category_input"
                    class="form-control"
                    placeholder="Enter category name"
                >
            `,
            showCancelButton: true,
            confirmButtonText: "Create",
            preConfirm: () => {
                const value = document.getElementById("diagnostic_category_input").value;

                if (!value) {
                    Swal.showValidationMessage("Please enter a category name.");
                }

                return value;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "create_diagnostic_category",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: {
                        categoryname: $("#diagnostic_category_input").val()
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Category created!',
                                showConfirmButton: false,
                                timer: 1500
                            });

                            $("#diagnostic_category_table").DataTable().ajax.reload();
                        }
                    }
                });
            }
        });
    });

    $(document).on("click", ".remove_dia_catg", function () {
        Swal.fire({
            title: "Confirmation",
            text: "Delete this category?",
            icon: "warning",
            confirmButtonText: "Confirm",
            showCancelButton: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "delete_diagnostic_category",
                    type: "POST",
                    headers: {
                        "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                    },
                    data: {
                        refno: $(this).val()
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Category deleted!',
                                showConfirmButton: false,
                                timer: 1500
                            });

                            $("#diagnostic_category_table").DataTable().ajax.reload();
                        }
                    }
                });
            }
        });
    });

    $("#diagnostics_masterlist_tab").on("click", function () {
        $("#diagnostic_table").DataTable().destroy().clear();
        $("#diagnostic_table").DataTable({
            ajax: {
                url: "fetch_diagnostics",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                },
                dataSrc: 'results'
            },
            columns: [
                {
                    data: null,
                    render: function (data) {
                        return `<button class="btn btn-sm btn-danger remove_diagnostic" value="${data.diagnosticrefno}"><i class="fa-solid fa-trash"></i> Remove</button>`
                    }
                },
                { data: 'diagnostic_name' },
                { data: 'category.category_name' }
            ],
            columnDefs: [
                {
                    target: 0,
                    width: '1%',
                    orderable: false,
                    className: 'text-nowrap text-center'
                }
            ],
            info: false,
            lengthChange: false,
            initComplete: function (settings, json) {
                let catg = $("#diagnostic_catg");
                catg.empty();

                json.categories.forEach(element => {
                    catg.append(`<option value="${element.category_refno}">${element.category_name}</option>`);
                });
            }
        });
    });

    $("#create_diagnostic_btn").on("click", function () {
        $.ajax({
            url: "create_diagnostic",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
            },
            data: {
                name: $("#diagnostic_name").val(),
                category: $("#diagnostic_catg").val()
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Record created!',
                        showConfirmButton: false,
                        timer: 1500
                    });

                    $("#diagnostic_table").DataTable().ajax.reload();
                }
            }
        });
    });

    $(document).on("click", ".remove_diagnostic", function () {
        Swal.fire({
            title: "Confimation",
            text: "Do you want to delete this record?",
            icon: "warning",
            confirmButtonText: "Confirm",
            showCancelButton: true,
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "delete_diagnostic",
                    type: "POST",
                    data: {
                        refno: $(this).val()
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
                                title: 'Record deleted!',
                                showConfirmButton: false,
                                timer: 1500
                            });

                            $("#diagnostic_table").DataTable().ajax.reload();
                        }
                    }
                });
            }
        });
    });

    // Medicine-related
    $("#medicine_masterlist_btn").on("click", function () {
        $("#medicine_table").DataTable().destroy().clear();
        $("#medicine_table").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "fetch_medicine",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                },
                dataSrc: 'medicines'
            },
            columns: [
                {
                    data: null,
                    render: function (data) {
                        return `
                            <button class="btn btn-sm btn-danger remove_drug" value="${data.medicine_refno}"><i class="fa-solid fa-trash"></i></button>
                            <button class="btn btn-sm btn-primary edit_drug" value="${data.medicine_refno}"><i class="fa-solid fa-pen-to-square"></i></button>
                        `;
                    }
                },
                { data: "medicine_name" },
                { data: "philhealth_refno" }
            ],
            columnDefs: [
                {
                    target: 0,
                    width: "1%",
                    orderable: false,
                    searchable: false,
                    className: 'text-center align-middle text-nowrap'
                },
                {
                    targets: [1, 2],
                    className: 'align-middle text-nowrap'
                },
                {
                    target: 2,
                    width: "1%"
                }
            ],
            language: {
                emptyTable: "No records yet."
            },
            order: [[1, 'asc']],
            select: {
                style: "single"
            }
        });
    });

    $("#ph_id").on("focus", function () {
        const refModal = new bootstrap.Modal("#reference_modal");
        refModal.show();

        $("#drug_reference_table").DataTable().destroy().clear();
        $("#drug_reference_table").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "fetch_medicine_reference",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": $("meta[name=csrf-token]").attr("content")
                },
            },
            columns: [
                { data: 'drug_code' },
                { data: 'drug_dscr' }
            ],
            language: {
                emptyTable: "No references yet."
            },
            order: [[1, 'asc']],
            select: {
                style: "single"
            }
        });
    });

    $("#clear_reference").on("click", function () {
        $("#ph_id").val("");
    });

    $(document).on("click", "#import_refno", function () {
        const refModal = new bootstrap.Modal("#reference_modal");
        refModal.show();

        $("#drug_reference_table").DataTable().destroy().clear();
        $("#drug_reference_table").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "fetch_medicine_reference",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": $("meta[name=csrf-token]").attr("content")
                },
            },
            columns: [
                { data: 'drug_code' },
                { data: 'drug_dscr' }
            ],
            language: {
                emptyTable: "No references yet."
            },
            order: [[1, 'asc']],
            select: {
                style: "single"
            }
        });
    });

    $("#select_references").on("click", function () {
        const refModal = bootstrap.Modal.getInstance("#reference_modal");
        let row = $("#drug_reference_table").DataTable().row(0).data();

        $("#ph_id").val(row["drug_code"]);

        if ($("#edrug_code").length)
            $("#edrug_code").val(row["drug_code"])

        refModal.hide();
    });

    $("#add_med_btn").on("click", function () {
        let form = document.getElementById("drug_form");
        if (!form.checkValidity())
            return form.reportValidity();

        $.ajax({
            url: "admin_add_medicine",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
            },
            data: {
                med_name: $("#med_name").val(),
                ph_id: $("#ph_id").val()
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Record created!',
                        showConfirmButton: false,
                        timer: 1500
                    });

                    $("#drug_form")[0].reset();
                    $("#medicine_table").DataTable().ajax.reload();
                } else {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'An error occurred!',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            }
        });
    });

    $(document).on("click", ".remove_drug", function () {
        Swal.fire({
            title: "Confirmation",
            text: "Are you sure you want to remove this medicine?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Confirm"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "admin_delete_medicine",
                    type: "POST",
                    headers: {
                        "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                    },
                    data: {
                        medicinerefno: $(this).val()
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Record created!',
                                showConfirmButton: false,
                                timer: 1500
                            });

                            $("#medicine_table").DataTable().ajax.reload();
                        }
                    }
                });
            }
        });
    });

    $(document).on("click", ".edit_drug", function () {
        let row = $("#medicine_table").DataTable().row(0).data();

        Swal.fire({
            title: "Edit Drug",
            html: `
                <div class="d-flex flex-column gap-2 text-start">
                    <div class="">
                        <label class="form-label fw-bold" for="edrug_name">Medicine Name</label>
                        <input class="form-control" type="text" name="edrug_name" id="edrug_name" value="${row["medicine_name"] ?? ''}" required>
                    </div>

                    <div class="">
                        <label class="form-label fw-bold" for="edrug_code">Drug Code</label>
                        <div class="input-group">
                            <input class="form-control" type="text" name="edrug_code" value="${row["philhealth_refno"] ?? ''}" id="edrug_code">
                            <button class="btn btn-secondary" id="import_refno"><i class="fa-solid fa-list"></i></button>
                        </div>
                    </div>
                </div>
            `,
            confirmButtonText: "Update Details",
            showCancelButton: true,
            preConfirm: () => {
                const value = document.getElementById("edrug_name").value;

                if (!value)
                    Swal.showValidationMessage("Drug name must not be empty.");

                return value;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "admin_edit_medicine",
                    type: "POST",
                    headers: {
                        "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                    },
                    data: {
                        medrefno: row["medicine_refno"],
                        med_name: $("#edrug_name").val(),
                        ph_id: $("#edrug_code").val()
                    },
                    success: function (response) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Record edited!',
                            showConfirmButton: false,
                            timer: 1500
                        });

                        $("#medicine_table").DataTable().ajax.reload();
                    }
                })
            }
        });
    });

    // Charges
    $("#charges_masterlist_tab").on("click", function () {
        loadCharges();
    });

    $("#create_charge_btn").on("click", function () {
        let form = document.getElementById("charge_form");

        if (!form.checkValidity()) {
            return form.reportValidity();
        }

        $.ajax({
            url: "create_charge",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: $("#charge_form").serialize(),
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title: "Success",
                        text: "Charge successfully created.",
                        icon: "success"
                    });

                    loadCharges();
                }
            }
        });
    });

    $(document).on("click", ".edit_charge", function () {
        const chargeModal = new bootstrap.Modal("#edit_charge_modal");

        $.ajax({
            url: "fetch_charge_categories",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token'").attr("content") },
            success: function (response) {
                $("#echarge_catg").empty();
                $("#echarge_catg").append(
                    $("<option>", {
                        value: "",
                        text: "Select category",
                        disabled: true,
                        selected: true
                    })
                );

                response.categories.forEach(category => {
                    $("#echarge_catg").append(
                        $("<option>", {
                            value: category.categoryrefno,
                            text: category.categoryname
                        })
                    );
                });
            }
        });

        $.ajax({
            url: "fetch_specific_charges",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token'").attr("content") },
            data: {
                chargerefno: $(this).val()
            },
            success: function (response) {
                $("#echarge_name").val(response.charges[0].charge_name);
                $("#echarge_amt").val(response.charges[0].charge_amount);
                $("#echarge_catg").val(response.charges[0].charge_category);
            }
        });

        $("#update_charge_btn").val($(this).val());
        chargeModal.show();
    });

    $("#update_charge_btn").on("click", function () {
        $.ajax({
            url: "edit_charge",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: {
                chargerefno: $(this).val(),
                charge_name: $("#echarge_name").val(),
                charge_amount: $("#echarge_amt").val(),
                charge_category: $("#echarge_catg").val()
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title: "Success",
                        text: "Charge successfully updated.",
                        icon: "success"
                    });

                    let chargeModal = bootstrap.Modal.getInstance(document.getElementById("edit_charge_modal"));
                    chargeModal.hide();

                    loadCharges();
                }
            }
        });
    });

    $(document).on("click", ".delete_charge", function () {
        Swal.fire({
            title: "Confirmation",
            text: "Are you sure you want to delete this charge?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Confirm"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "delete_charge",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: {
                        chargerefno: $(this).val()
                    },
                    success: function (response) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Category deleted!',
                            showConfirmButton: false,
                            timer: 1500
                        });

                        $("#charges_masterlist_tab").trigger("click");
                    }
                });
            }
        });
    });

    $("#charges_category_tab").on("click", function () {
        $("#charges_category_table").DataTable().clear().destroy();
        $("#charges_category_table").DataTable({
            ajax: {
                url: "fetch_charge_categories",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                dataSrc: 'categories'
            },
            columns: [
                {
                    data: null,
                    render: function (data) {
                        return `
                            <button class="btn btn-sm btn-primary edit-category" value="${data.categoryrefno}"><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                            <button class="btn btn-sm btn-danger delete-category" value="${data.categoryrefno}"><i class="fa-solid fa-trash-can"></i> Delete</button>
                        `;
                    }
                },
                { data: 'categoryname' }
            ],
            columnDefs: [
                {
                    target: 0,
                    width: '1%',
                    className: 'text-nowrap text-truncate text-center align-middle'
                }
            ],
            ordering: false,
            paging: true,
            pageLength: 20,
            lengthChange: false,
            searching: true,
            responsive: true,
        });
    });

    $("#create_charge_category_btn").on("click", function () {
        Swal.fire({
            title: "Create Charge Category",
            html: `
                <input
                    id="charge_category_input"
                    class="form-control"
                    placeholder="Enter category name"
                >
            `,
            showCancelButton: true,
            confirmButtonText: "Create",
            preConfirm: () => {
                const value = document.getElementById("charge_category_input").value;

                if (!value) {
                    Swal.showValidationMessage("Please enter a category name.");
                }

                return value;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "create_charge_category",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: {
                        categoryname: $("#charge_category_input").val()
                    },
                    success: function (response) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Category created!',
                            showConfirmButton: false,
                            timer: 1500
                        });

                        $("#charges_category_tab").trigger("click");
                    }
                });
            }
        });
    });

    $(document).on("click", ".edit-category", function () {
        Swal.fire({
            title: "Edit category",
            html: `
                <input
                    id="edit_category_input"
                    class="form-control"
                    placeholder="Enter new category name"
                >
            `,
            showCancelButton: true,
            confirmButtonText: "Update",
            preConfirm: () => {
                const value = document.getElementById("edit_category_input").value;

                if (!value) {
                    Swal.showValidationMessage("Please enter a category name.");
                }

                return value;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "edit_charge_category",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: {
                        categoryname: $("#edit_category_input").val(),
                        ecategoryrefno: $(this).val()
                    },
                    success: function (response) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Category updated!',
                            showConfirmButton: false,
                            timer: 1500
                        });

                        $("#charges_category_tab").trigger("click");
                    }
                });
            }
        });
    });

    $(document).on("click", ".delete-category", function () {
        Swal.fire({
            title: "Confirmation",
            text: "Are you sure you want to delete this category?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Confirm"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "delete_charge_category",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: {
                        categoryrefno: $(this).val()
                    },
                    success: function (response) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'Category deleted!',
                            showConfirmButton: false,
                            timer: 1500
                        });

                        $("#charges_category_tab").trigger("click");
                    }
                });
            }
        });
    });

    function loadCharges() {
        $.ajax({
            url: "fetch_charge_categories",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token'").attr("content") },
            success: function (response) {
                $("#charge_catg").empty();
                $("#charge_catg").append(
                    $("<option>", {
                        value: "",
                        text: "Select category",
                        disabled: true,
                        selected: true
                    })
                );

                response.categories.forEach(category => {
                    $("#charge_catg").append(
                        $("<option>", {
                            value: category.categoryrefno,
                            text: category.categoryname
                        })
                    );
                });
            }
        });

        $("#charges_table").DataTable().destroy().clear();
        $("#charges_table").DataTable({
            ajax: {
                url: "fetch_charges",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                dataSrc: "charges"
            },
            columns: [
                {
                    data: null,
                    render: function (data) {
                        return `
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-primary edit_charge" value='${data.chargerefno}'><i class="fa-solid fa-pen-to-square"></i> Edit</button>
                                <button class="btn btn-sm btn-danger delete_charge" value='${data.chargerefno}'><i class="fa-solid fa-trash-can"></i> Delete</button>
                            </div>
                            `;

                    }
                },
                { data: 'charge_name' },
                { data: 'categoryname' },
                { data: 'charge_amount' }
            ],
            columnDefs: [
                {
                    target: 0,
                    width: '1%',
                    orderable: false,
                    className: 'text-nowrap text-truncate align-middle'
                },
                {
                    target: 3,
                    width: '10%',
                    className: 'text-nowrap text-end'
                }
            ],
            ordering: true,
            paging: true,
            searching: true,
            responsive: true,
        });
    }

    // Generate codes
    $("#btn_generate_codes").on("click", function () {
        Swal.fire({
            title: "Confirmation",
            text: "Do you want to generate case and pin codes?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Confirm"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "generate_patient_codes",
                    type: "POST",
                    headers: {
                        "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                title: "Success!",
                                text: "Case and pin codes generated.",
                                icon: "success"
                            });
                        }
                    }
                });
            }
        });
    });

    // Render all patients
    $("#philhealth_data_btn").on("click", function () {
        const table = $("#phpatients_table");

        if (table.DataTable().data().any()) {
            table.DataTable().destroy().clear();
        }

        table.DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: "fetch_all_patient_consultation",
                type: "POST",
                data: (d) => {
                    d.filter_start = $("#px_filter_start").val();
                    d.filter_end = $("#px_filter_end").val();
                },
                headers: {
                    "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                }
            },
            columns: [
                {
                    data: null,
                    render: () => {
                        return `<input type="checkbox" class="form-check-input">`
                    }
                },
                {
                    data: null,
                    render: (data) => {
                        return `${data.pxlastname}, ${data.pxmidname} ${data.patientname} ${data.pxsuffix}`.toUpperCase();
                    }
                },
                { data: 'pincode' },
                { data: 'casecode' }
            ],
            columnDefs: [
                {
                    targets: [0, 2, 3],
                    width: "1%",
                    orderable: false,
                    className: "text-center text-nowrap align-middle"
                }
            ],
            language: {
                emptyTable: "No patient data recorded yet."
            },
            order: [
                [1, 'asc']
            ]
        });
    });

    // Patient consultations date filter
    $("#px_filter_start, #px_filter_end").on("change", function () {
        const start = $("#px_filter_start").val();
        const end = $("#px_filter_end").val();

        if (start && end) {
            $("#phpatients_table").DataTable().ajax.reload();
        }
    });

    $("#clear_date_filter").on("click", function () {
        const filters = $("#px_filter_start, #px_filter_end");

        if (filters.length) {
            filters.val("");
        }
    });

    // Select all functionality
    $(document).on("click", "#ph-selectall", function () {
        const isChecked = $(this).is(":checked");
        const nodes = $("#phpatients_table").DataTable().rows().nodes();

        $(nodes).find("input[type='checkbox']").prop("checked", isChecked);
    });

    $("#export_to_masterlist").on("click", function () {
        Swal.fire({
            title: "Confirmation",
            text: "Exporting data may take a while depending on the amount of records, proceed?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Proceed"
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: "Exporting...",
                    text: "Processing data, do not reload the page.",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: "export_consultation_to_masterlist",
                    type: "POST",
                    headers: {
                        "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                title: "Complete",
                                text: `Successfully finished exporting ${response.count} records.`,
                                icon: "success"
                            });
                        }
                    }
                });
            }
        });
    });

    $("#export_to_soap").on("click", function () {
        Swal.fire({
            title: "Confirm Export?",
            text: "Please confirm data exportation.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Confirm"
        }).then((result) => {
            const table = $("#phpatients_table").DataTable();
            const data = table.rows({ search: "applied" }).nodes();
            const selected = [];

            $(data).each(function () {
                const checkbox = $(this).find("input[type='checkbox']").first();

                if (checkbox.is(":checked")) {
                    selected.push(table.row(this).data());
                }
            });

            if (selected.length == 0) {
                Swal.fire({
                    title: "No records selected",
                    text: "There are no selected records to be exported",
                    icon: "danger"
                });
                return;
            }

            if (result.isConfirmed) {
                $.ajax({
                    url: "export_to_soap",
                    type: "POST",
                    headers: {
                        "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                    },
                    data: {
                        records: selected
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Category deleted!',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        }
                    }
                });
            }
        });
    });

    $("#patient_masterlist_btn").on("click", function () {
        $("#patient_masterlist_table").DataTable().destroy().clear();
        $("#patient_masterlist_table").DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: "fetch_patient_masterlist",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                },
                dataSrc: 'patients'
            },
            columns: [
                { data: 'patientname'},
                { data: 'pincode' },
                { data: 'casecode' }
            ],
            columnDefs: [
                {
                    targets: [1, 2],
                    width: '1%',
                    className: 'text-nowrap'
                }
            ],
            language: {
                emptyTable: "No patient records yet."
            }
        });
    });

    // Reports
    $("#filter_start, #filter_end").on("change", function () {
        $("#transactions_table").DataTable().ajax.reload();
    });

    $("#reports-tab").on("click", function () {
        $("#transactions_table").DataTable().destroy().clear();
        $("#transactions_table").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "fetch_transactions",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                },
                data: function (d) {
                    d.docrefno = $("#docrefno").val();
                    d.filter_start = $("#filter_start").val();
                    d.filter_end = $("#filter_end").val();
                }
            },
            columns: [
                { data: 'id' },
                {
                    data: 'created_at',
                    render: function (data) {
                        return new Date(data).toDateString();
                    }
                },
                { data: 'cash' },
                { data: 'cta' },
                { data: 'something' },
                { data: 'hmo' },
                { data: 'net_total' }
            ],
            language: {
                emptyTable: "No records yet."
            }
        });
    });

    $("#print_transactions").on("click", function () {
        if (!$("#docrefno").val()) {
            return Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'warning',
                title: 'No doctor selected',
                showConfirmButton: false,
                timer: 1500
            });
        }

        window.open(`/print_transactions?docrefno=${$("#docrefno").val()}&start=${$("#filter_start").val()}&end=${$("#filter_end").val()}`);
    });

    $("#generateXMLT1").on("show.bs.modal", function () {
        $("#tranche_one_table").DataTable().destroy().clear();
        $("#tranche_one_table").DataTable({
            ajax: {
                url: "fetch_patient_masterlist",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                }
            },
            columns: [
                {
                    data: 'patientname'
                }
            ],
            select: {
                style: "single"
            }
        });
    });

    $("#generate_tranche_one_xml").on("click", function () {
        const table = $("#tranche_one_table").DataTable();

        if (table.rows({ selected: true }).any()) {
            $.ajax({
                url: "generate_xml_first_tranche",
                type: "POST",
                data: {
                    data: table.row({ selected: true })
                },
                headers: {
                    "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                }
            });
        } else {
            Swal.fire({
                title: "No user selected!",
                text: "Select a user from the table first.",
                icon: "warning"
            });
        }
    });

    // $("#generate_tranch_one_xml").on("click", function () {
    //     const table = $('#tranche_one_table').DataTable();

    //     if (table.rows({ selected: true }).any()) {
    //         const data = table.row({ selected: true }).data();
    //         console.log(data);
    //     } else {
    //         Swal.fire({
    //             title: "No user selected!",
    //             text: "Select a user from the table first.",
    //             icon: "warning"
    //         });
    //     }
    // });
});
