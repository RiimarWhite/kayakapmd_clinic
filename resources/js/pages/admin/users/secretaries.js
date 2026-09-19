$(function () {
    // Detailed Comment: State variables declared at the top of scope to prevent Temporal Dead Zone (TDZ) ReferenceError
    let secretaryTable = null;
    let currentAccountTypeFilter = '';

    // Detailed Comment: Auto-populate default username from Last Name for Secretary registration
    $("#seclname").on("input blur", function () {
        if (!$("#secusername").val() || $("#secusername").data("auto-generated")) {
            const val = $(this).val().trim().toLowerCase().replace(/\s+/g, '');
            $("#secusername").val(val).data("auto-generated", true);
        }
    });
    $("#secusername").on("input", function () {
        $(this).data("auto-generated", false);
    });

    // Detailed Comment: Auto-populate default username from Last Name for Admin registration
    $("#adminlname").on("input blur", function () {
        if (!$("#adminusername").val() || $("#adminusername").data("auto-generated")) {
            const val = $(this).val().trim().toLowerCase().replace(/\s+/g, '');
            $("#adminusername").val(val).data("auto-generated", true);
        }
    });
    $("#adminusername").on("input", function () {
        $(this).data("auto-generated", false);
    });

    /**
     * Detailed Comment: Helper functions to toggle button loading spinners and disabled state
     * Stores original button HTML in data attribute and restores upon operation completion.
     */
    function setBtnLoading($btn, loadingText) {
        if (!$btn || $btn.length === 0) return;
        const originalHtml = $btn.html();
        $btn.data('original-html', originalHtml).prop('disabled', true);
        $btn.html(`<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> ${loadingText}`);
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
     * Load secretaries and administrators in a unified DataTables instance.
     * Features interactive loading spinner, Account Type column dropdown filter,
     * expanded labeled action buttons, and length dropdown menu.
     */
    function loadSecretaries() {
        if ($.fn.DataTable.isDataTable("#secretary_table")) {
            $("#secretary_table").DataTable().clear().destroy();
        }

        secretaryTable = $("#secretary_table").DataTable({
            processing: true,
            ajax: {
                url: "/api/fetch_secretaries",
                type: "POST",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: function (d) {
                    // Detailed Comment: Pass server-side account_type filter parameter
                    d.account_type = currentAccountTypeFilter;
                },
                dataSrc: 'secretaries'
            },
            columns: [
                {
                    data: null,
                    render: function (data) {
                        const isSecretary = (data.account_type === 'Secretary');
                        const ref = isSecretary ? data.secrefno : (data.adminrefno || data.id);
                        
                        // Detailed Comment: Expanded action buttons with explicit text labels alongside icons
                        let buttons = `<div class="d-inline-flex flex-wrap gap-1 justify-content-center align-items-center">
                            <button class="btn btn-sm btn-primary edit_user text-nowrap" data-type="${data.account_type}" value="${ref}" title="Edit User">
                                <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                            </button>`;

                        // Detailed Comment: Assigned Doctors button appears strictly for Secretary accounts
                        if (isSecretary) {
                            buttons += `<button class="btn btn-sm btn-info text-white assign_doctor text-nowrap" value="${data.secrefno}" title="Assigned Doctors">
                                <i class="fa-solid fa-user-doctor me-1"></i> Assigned Doctors
                            </button>`;
                        }

                        buttons += `<button class="btn btn-sm btn-danger delete_user text-nowrap" data-type="${data.account_type}" value="${ref}" title="Delete User">
                            <i class="fa-solid fa-trash-can me-1"></i> Delete
                        </button>
                        </div>`;

                        return buttons;
                    }
                },
                {
                    data: 'account_type',
                    render: function (data, type) {
                        if (type === 'filter' || type === 'sort') {
                            return data;
                        }
                        if (data === 'Admin') {
                            return `<span class="badge bg-primary px-2 py-1"><i class="fa-solid fa-user-shield me-1"></i> Admin</span>`;
                        }
                        return `<span class="badge bg-info text-dark px-2 py-1"><i class="fa-solid fa-user-nurse me-1"></i> Secretary</span>`;
                    }
                },
                {
                    data: 'source_table',
                    render: function (data, type, row) {
                        const tbl = data || (row.account_type === 'Admin' ? 'adminrights' : 'secretaryrights');
                        return `<span class="badge bg-secondary font-monospace px-2 py-1">${tbl}</span>`;
                    }
                },
                {
                    data: 'username',
                    render: function (data) {
                        return data ? `<code class="text-primary fw-bold">${data}</code>` : '<span class="text-muted">None</span>';
                    }
                },
                {
                    data: null,
                    render: function (data) {
                        const mname = data.secmname ? ` ${data.secmname}` : '';
                        const suffix = data.secsuffix ? ` ${data.secsuffix}` : '';
                        return `<strong>${data.seclname || ''}</strong>, ${data.secfname || ''}${mname}${suffix}`;
                    }
                },
                { data: 'seccontactno', className: 'text-start' },
                { data: 'secemail' }
            ],
            columnDefs: [
                // Detailed Comment: Expanded width to 300px for labeled Action buttons
                { targets: 0, width: '300px', orderable: false, searchable: false, className: 'text-nowrap text-center align-middle' },
                // Detailed Comment: Account Type column with dropdown menu filter
                { targets: 1, width: '210px', orderable: false, className: 'text-center align-middle' },
                // Detailed Comment: Source Table column badge
                { targets: 2, width: '140px', className: 'text-center align-middle' },
                { targets: 3, width: '120px', className: 'align-middle' },
                { targets: [4, 5, 6], className: 'align-middle' }
            ],
            language: {
                // Detailed Comment: Styled loading spinner overlay when table data is fetching/processing
                processing: '<div class="d-flex justify-content-center align-items-center py-2"><div class="spinner-border spinner-border-sm text-success me-2" role="status"></div><span class="text-secondary fw-bold">Loading accounts...</span></div>',
                emptyTable: "No users registered yet."
            },
            // Detailed Comment: Extended max rows dropdown options
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            pageLength: 10,
            lengthChange: true,
            paging: true,
            searching: true,
            ordering: false,
            responsive: true
        });
    }

    // Detailed Comment: Initial table load on page ready
    loadSecretaries();

    // Detailed Comment: Filter table by Account Type using the column header dropdown menu with server-side AJAX reload
    $(document).on("click", ".filter-account-opt", function (e) {
        e.preventDefault();
        const filterVal = $(this).data("filter"); // '', 'Secretary', or 'Admin'
        currentAccountTypeFilter = filterVal || '';
        
        // Update active state and checkmark icon in dropdown
        $(".filter-account-opt").removeClass("active");
        $(".filter-account-opt .filter-check-icon").addClass("d-none");
        $(this).addClass("active");
        $(this).find(".filter-check-icon").removeClass("d-none");

        // Update column header label and badge color
        const $label = $("#filtered_account_type_label");
        if (filterVal === 'Secretary') {
            $label.text("Secretary").removeClass("bg-secondary bg-primary").addClass("bg-info text-dark");
        } else if (filterVal === 'Admin') {
            $label.text("Admin").removeClass("bg-secondary bg-info text-dark").addClass("bg-primary text-white");
        } else {
            $label.text("All").removeClass("bg-info bg-primary text-dark").addClass("bg-secondary text-white");
        }

        // Detailed Comment: Trigger server-side reload with the selected account_type parameter
        if (secretaryTable) {
            secretaryTable.ajax.reload();
        }
    });

    // Detailed Comment: Add Secretary submission handler with loading spinner
    $("#add_secretary_btn").on("click", function () {
        var form = document.getElementById("add_secretary_form");
        if (!form.checkValidity()) { form.reportValidity(); return; }

        const $btn = $(this);
        setBtnLoading($btn, "Adding Secretary...");

        $.ajax({
            url: "/api/add_secretary",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
                secfname: $("#secfname").val(),
                secmname: $("#secmname").val(),
                seclname: $("#seclname").val(),
                secsuffix: $("#secsuffix").val(),
                username: $("#secusername").val(),
                seccontactno: $("#seccontactno").val(),
                secemail: $("#secemail").val(),
                secpassword: $("#secpassword").val(),
                secgender: $("#secgender").val(),
                secbday: $("#secbday").val(),
                secadrs: $("#secadrs").val(),
            },
            success: function (response) {
                resetBtnLoading($btn);
                if (response.success) {
                    Swal.fire({ title: "Success", text: "Secretary account registered successfully.", icon: "success", confirmButtonText: "Okay" })
                        .then(() => {
                            loadSecretaries();
                            $("#add_secretary_form")[0].reset();
                            $("#secusername").data("auto-generated", false);
                        });
                }
            },
            error: function (xhr) {
                resetBtnLoading($btn);
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to register secretary.";
                Swal.fire({ title: "Error", text: msg, icon: "error" });
            }
        });
    });

    // Detailed Comment: Add Admin submission handler with loading spinner
    $("#add_admin_btn").on("click", function () {
        var form = document.getElementById("add_admin_form");
        if (!form.checkValidity()) { form.reportValidity(); return; }

        const $btn = $(this);
        setBtnLoading($btn, "Adding Admin User...");

        $.ajax({
            url: "/api/add_admin",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
                adminfname: $("#adminfname").val(),
                adminmname: $("#adminmname").val(),
                adminlname: $("#adminlname").val(),
                username: $("#adminusername").val(),
                admincontactno: $("#admincontactno").val(),
                adminemail: $("#adminemail").val(),
                password: $("#adminpassword").val(),
            },
            success: function (response) {
                resetBtnLoading($btn);
                if (response.success) {
                    Swal.fire({ title: "Success", text: "Administrator account registered successfully.", icon: "success", confirmButtonText: "Okay" })
                        .then(() => {
                            loadSecretaries();
                            $("#add_admin_form")[0].reset();
                            $("#adminusername").data("auto-generated", false);
                        });
                }
            },
            error: function (xhr) {
                resetBtnLoading($btn);
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to register administrator.";
                Swal.fire({ title: "Error", text: msg, icon: "error" });
            }
        });
    });

    // Detailed Comment: Open Edit Modal for Secretary or Admin user with button loading indicator
    $(document).on("click", ".edit_user", function () {
        const type = $(this).data("type");
        const val = $(this).val();
        const $btn = $(this);
        setBtnLoading($btn, "Loading...");

        if (type === 'Secretary') {
            $.ajax({
                url: "/api/fetch_secretary_details",
                type: "POST",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: { secrefno: val },
                success: function (response) {
                    resetBtnLoading($btn);
                    if (response.success) {
                        const sec = response.secretary;
                        $("#edit_sec_refno").val(sec.secrefno);
                        $("#edit_secfname").val(sec.secfname);
                        $("#edit_secmname").val(sec.secmname || '');
                        $("#edit_seclname").val(sec.seclname);
                        $("#edit_secsuffix").val(sec.secsuffix || '');
                        $("#edit_secusername").val(sec.username || '');
                        $("#edit_secgender").val(sec.secgender ? sec.secgender.toLowerCase() : 'male');
                        $("#edit_secbday").val(sec.secbday ? sec.secbday.split('T')[0] : '');
                        $("#edit_seccontactno").val(sec.seccontactno || '');
                        $("#edit_secemail").val(sec.secemail || '');
                        $("#edit_secadrs").val(sec.secadrs || '');
                        $("#edit_secpassword").val('');

                        new bootstrap.Modal("#edit_secretary_modal").show();
                    }
                },
                error: function () {
                    resetBtnLoading($btn);
                    Swal.fire({ title: "Error", text: "Failed to fetch secretary details.", icon: "error" });
                }
            });
        } else if (type === 'Admin') {
            $.ajax({
                url: "/api/fetch_admin_details",
                type: "POST",
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: { adminrefno: val, id: isNaN(val) ? null : val },
                success: function (response) {
                    resetBtnLoading($btn);
                    if (response.success) {
                        const adm = response.admin;
                        $("#edit_admin_id").val(adm.id);
                        $("#edit_admin_refno").val(adm.adminrefno || '');
                        $("#edit_adminfname").val(adm.adminfname || '');
                        $("#edit_adminmname").val(adm.adminmname || '');
                        $("#edit_adminlname").val(adm.adminlname || '');
                        $("#edit_adminusername").val(adm.username || '');
                        $("#edit_admincontactno").val(adm.admincontactno || '');
                        $("#edit_adminemail").val(adm.adminemail || adm.useremail || '');
                        $("#edit_adminpassword").val('');

                        new bootstrap.Modal("#edit_admin_modal").show();
                    }
                },
                error: function () {
                    resetBtnLoading($btn);
                    Swal.fire({ title: "Error", text: "Failed to fetch administrator details.", icon: "error" });
                }
            });
        }
    });

    // Detailed Comment: Save edited Secretary details with button spinner
    $("#save_edit_secretary_btn").on("click", function () {
        const form = document.getElementById("edit_secretary_form");
        if (!form.checkValidity()) { form.reportValidity(); return; }

        const $btn = $(this);
        setBtnLoading($btn, "Saving...");

        $.ajax({
            url: "/api/edit_secretary",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: $("#edit_secretary_form").serialize(),
            success: function (response) {
                resetBtnLoading($btn);
                if (response.success) {
                    bootstrap.Modal.getInstance("#edit_secretary_modal").hide();
                    Swal.fire({ title: "Success", text: "Secretary updated successfully.", icon: "success", confirmButtonText: "Okay" })
                        .then(() => loadSecretaries());
                }
            },
            error: function (xhr) {
                resetBtnLoading($btn);
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to update secretary.";
                Swal.fire({ title: "Error", text: msg, icon: "error" });
            }
        });
    });

    // Detailed Comment: Save edited Admin details with button spinner
    $("#save_edit_admin_btn").on("click", function () {
        const form = document.getElementById("edit_admin_form");
        if (!form.checkValidity()) { form.reportValidity(); return; }

        const $btn = $(this);
        setBtnLoading($btn, "Saving...");

        $.ajax({
            url: "/api/edit_admin",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: $("#edit_admin_form").serialize(),
            success: function (response) {
                resetBtnLoading($btn);
                if (response.success) {
                    bootstrap.Modal.getInstance("#edit_admin_modal").hide();
                    Swal.fire({ title: "Success", text: "Administrator updated successfully.", icon: "success", confirmButtonText: "Okay" })
                        .then(() => loadSecretaries());
                }
            },
            error: function (xhr) {
                resetBtnLoading($btn);
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to update administrator.";
                Swal.fire({ title: "Error", text: msg, icon: "error" });
            }
        });
    });

    // Detailed Comment: Delete user handler for both Secretary and Admin types with SweetAlert loading animation
    $(document).on("click", ".delete_user", function () {
        const type = $(this).data("type");
        const val = $(this).val();

        Swal.fire({
            title: "Confirmation",
            text: `Are you sure you want to delete this ${type.toLowerCase()} account?`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete",
            confirmButtonColor: "#d33"
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: "Deleting...",
                    text: `Please wait while deleting ${type.toLowerCase()} account.`,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const url = (type === 'Secretary') ? "/api/delete_secretary" : "/api/delete_admin";
                const data = (type === 'Secretary') ? { secrefno: val } : { adminrefno: val, id: isNaN(val) ? null : val };

                $.ajax({
                    url: url,
                    type: "POST",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: data,
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({ title: "Success", text: `${type} account deleted successfully.`, icon: "success", confirmButtonText: "Okay" })
                                .then(() => loadSecretaries());
                        }
                    },
                    error: function (xhr) {
                        const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : `Failed to delete ${type.toLowerCase()}.`;
                        Swal.fire({ title: "Error", text: msg, icon: "error" });
                    }
                });
            }
        });
    });

    // Detailed Comment: Open Assigned Doctors modal for secretary with button loading indicator
    $(document).on("click", ".assign_doctor", function () {
        const assignedModal = new bootstrap.Modal("#assigned_doctors_modal");
        var secrefno = $(this).val();
        const $btn = $(this);
        setBtnLoading($btn, "Loading...");

        $.ajax({
            url: "/api/fetch_secretary_doctors",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { secrefno: secrefno },
            success: function (response) {
                resetBtnLoading($btn);
                if (response.success) {
                    $("#secretary_name").text(response.name);
                    var $select = $("#availdoctors");
                    $select.empty();
                    response.avail_doctors.forEach(element => {
                        if (element && element.docrefno) {
                            $select.append($("<option>", { value: element.docrefno, text: element.docname }));
                        }
                    });

                    var $assigned = $("#assigned_doctors_badge");
                    $assigned.empty();
                    const doctors = response.assigned_doctors?.filter(d => d != null && d.docrefno);
                    if (doctors && doctors.length > 0) {
                        doctors.forEach(element => {
                            $assigned.append(`<span class="btn btn-sm btn-primary rounded rounded-pill remove_append m-1" id="${element.docrefno}">
                                <input type="hidden" name="doctors[]" value="${element.docrefno}"> ${element.docname} ×</span>`);
                        });
                    } else {
                        $assigned.append("<span class='d-flex text-center text-muted'>No assigned doctors.</span>");
                    }

                    $("#save_append").val(secrefno);
                    assignedModal.show();
                }
            },
            error: function () {
                resetBtnLoading($btn);
                Swal.fire({ title: "Error", text: "Failed to fetch assigned doctors.", icon: "error" });
            }
        });
    });

    // Detailed Comment: Append doctor from available dropdown to assigned badges
    $("#assign_doctor_btn").on("click", function () {
        const assigned_doctors = $("#assigned_doctors_badge");
        const value = $("#availdoctors option:selected").val();
        const name = $("#availdoctors option:selected").text();
        if (value != null && value !== "") {
            if (assigned_doctors.find(".text-muted").length > 0) assigned_doctors.empty();
            assigned_doctors.append(`<span class="btn btn-sm btn-primary rounded rounded-pill remove_append m-1" id="${value}">
                <input type="hidden" name="doctors[]" value="${value}"> ${name} ×</span>`);
            $("#availdoctors option:selected").remove();
        }
    });

    // Detailed Comment: Remove assigned doctor badge and return doctor to available dropdown with valid value
    $(document).on("click", ".remove_append", function () {
        const available_doctors = $("#availdoctors");
        const assigned_doctors = $("#assigned_doctors_badge");
        const option = $(this).text().replace('×', '').trim();
        const value = $(this).find('input[name="doctors[]"]').val() || $(this).attr('id');
        $(this).remove();
        if (value) {
            available_doctors.append($("<option>", { value: value, text: option }));
        }
        if (assigned_doctors.children().length === 0) {
            assigned_doctors.append($("<span>", { "class": "text-muted", text: "No assigned doctors." }));
        }
    });

    // Detailed Comment: Synchronize assigned doctors to secretary in database with button loading spinner
    $("#save_append").on("click", function () {
        let formData = $("#append_doctor_form").serialize();
        formData += '&secrefno=' + encodeURIComponent($(this).val());
        const $btn = $(this);
        setBtnLoading($btn, "Saving...");

        $.ajax({
            url: "/api/save_appended_doctors",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: formData,
            success: function (response) {
                resetBtnLoading($btn);
                if (response.success) {
                    Swal.fire({ title: "Success", text: "Doctors assigned to secretary successfully.", icon: "success", confirmButtonText: "Okay" });
                }
            },
            error: function () {
                resetBtnLoading($btn);
                Swal.fire({ title: "Error", text: "Failed to save assigned doctors.", icon: "error" });
            }
        });
    });
});
